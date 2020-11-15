import requests
from bs4 import BeautifulSoup
import json
import mysql.connector
import time
from lxml import etree
from io import StringIO, BytesIO
import os

#list charactor: https://naruto.fandom.com/wiki/Category:Characters
#detail: https://naruto.fandom.com/wiki/Akamaru

domain = 'https://naruto.fandom.com'

def get_value_from_raw_string(raw_string, from_str, end_str):
	index_start = raw_string.find(from_str)
	if index_start == -1:
		return ''

	index_start += len(from_str)
	index_end = raw_string.find(end_str, index_start)
	if index_end == -1:
		return ''
	return raw_string[index_start:index_end]

def get_detail_character(link_character):
	list_attribute = ['Birthdate', 'Sex', 'Affiliation', 'Blood type', 'Clan', 'Ninja Rank', 'Family']
	res = requests.get(link_character)
	html = res.text

	link_video = get_value_from_raw_string(html, '406p"},{"file":"', '"')

	parser = etree.HTMLParser()
	tree   = etree.parse(StringIO(html), parser)
	html = etree.tostring(tree.getroot(), pretty_print=True, method="html").decode('utf-8')

	soup = BeautifulSoup(html, features="html.parser")

	fullname = soup.find('h1').getText()

	div_content = soup.find('div', {"id": "mw-content-text"})
	all_p = div_content.findAll("p")
	summary = ''

	if "(" in fullname:
		if 'Raikage' in fullname:
			fullname = get_value_from_raw_string(fullname, '(', ')')
		else:
			fullname = fullname.split('(')[0].strip()

	for p in all_p:
		if fullname in p.getText():
			summary = p.getText()
			break
	
	nickname = get_value_from_raw_string(summary, 'Literally meaning: ', ')')

	fullname_addition = get_value_from_raw_string(summary, fullname + ' (', ')')

	if fullname_addition != '':
		fullname_addition_split = fullname_addition.split(',')
		if len(fullname_addition_split) == 1:
			fullname_japan = ''
			fullname_2 = fullname_addition_split[0].strip()
		else:
			fullname_japan = fullname_addition_split[0]
			fullname_2 = fullname_addition_split[1].strip()
	else:
		fullname_japan = ''
		fullname_2 = ''

	attributes = {}
	for att in list_attribute:
		attributes[att] = ''

	info_addtion = soup.find('table', {'class': 'type-character'})
	avatar = ''
	if info_addtion:
		avatar_el = info_addtion.find('a', {'class': 'image image-thumbnail'})
		if avatar_el:
			avatar = info_addtion.find('a', {'class': 'image image-thumbnail'})['href']

		trs = info_addtion.findAll('tr')
		for tr in trs:
			th = tr.find('th')
			if th:
				title_attr = th.getText().strip()
				if title_attr in list_attribute:
					td = tr.find('td')
					if td:
						array_data = td.getText().split("\n")
						array_data = [x.strip() for x in array_data if x and x != "\t" and x.strip()]

						if title_attr == 'Family':
							arr_family = []
							for i in array_data:
								if '(' in i:
									name_member = i.split('(')[0]
									role_member = get_value_from_raw_string(i, '(', ')')
									arr_family.append(role_member + ': ' + name_member)

							attributes[title_attr] = "\n".join(arr_family)
						elif title_attr == 'Ninja Rank':
							attributes[title_attr] = "\n".join(array_data)
						else:
							if len(array_data) > 0:
								attributes[title_attr] = array_data[0]
							

	return (link_character, summary, fullname, fullname_2, fullname_japan, nickname, link_video, avatar, attributes['Birthdate'], attributes['Sex'], attributes['Affiliation'], attributes['Blood type'], attributes['Clan'], attributes['Ninja Rank'], attributes['Family'])

start_time = time.time()

with open('../files/config.json') as f:
	config = json.loads(f.read())
	f.close()

mysql_config = config['mysql']

mydb = mysql.connector.connect(
	host=mysql_config['host'],
	user=mysql_config['username'],
	passwd=mysql_config['password'],
	database=mysql_config['database']
)

mycursor = mydb.cursor()

link_list_character = 'https://naruto.fandom.com/wiki/Category:Characters'

while True:
	print(link_list_character)
	
	res = requests.get(link_list_character)
	data = res.text

	soup = BeautifulSoup(data, features="lxml")
	characters = soup.findAll('a', {"class": "category-page__member-link"})

	val_characters = []
	for character in characters:
		os.system('cls')
		fullname = character.getText()
		print(fullname)
		link_character = domain + character['href']
		detail = get_detail_character(link_character)
		val_characters.append(detail)

	if len(val_characters) == 0:
		print('END')
		break
	sql_characters = "INSERT INTO naruto_characters (link_origin, summary, fullname, fullname_2, fullname_japan, nickname, link_video, avatar, birthday, sex, affiliation, blood_type, clan, ninja_rank, family) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"
	mycursor.executemany(sql_characters, val_characters)
	mydb.commit()

	next_page = soup.find('a', {"class": "category-page__pagination-next wds-button wds-is-secondary"})
	if next_page:
		link_list_character = next_page['href']
	else:
		break