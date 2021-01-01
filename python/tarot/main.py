import requests
from bs4 import BeautifulSoup
import json
import mysql.connector
import time
from lxml import etree
from io import StringIO, BytesIO
import os

#detail: https://hoctarot.com/y-nghia-la-bai-seven-of-pentacles-trong-tarot/

domain = 'https://hoctarot.com/'

list_type = ['cups', 'pentacles', 'swords', 'wands']

map_name_type = {
	'cups': 'cốc',
	'pentacles': 'tiền',
	'swords': 'kiếm',
	'wands': 'gậy'
}

map_name_level = {
	'ace': '1',
	'two': '2',
	'three': '3',
	'four': '4',
	'five': '5',
	'six': '6',
	'seven': '7',
	'eight': '8',
	'nine': '9',
	'ten': '10'
}

def get_detail_card(link_card):
	res = requests.get(link_card)
	data = res.text

	soup = BeautifulSoup(data, features="lxml")
	find_image = soup.find('img', {'class': 'wp-post-image'})
	link_image = find_image['src']

	find_title = soup.find('h1', {'class': 'entry-title'})
	title = find_title.getText().replace('Ý Nghĩa Lá Bài ', '').replace(' Trong Tarot', '').lower()

	type_card = 'SPECIAL'
	for _type in list_type:
		if _type in title:
			type_card = _type

	level = ''
	vi_name = ''
	if ' of ' in title:
		split_title = title.split(' ')
		print(split_title)
		level = split_title[0]

		if level in map_name_level:
			vi_level = map_name_level[level]
			vi_type = map_name_type[type_card]

			vi_name = vi_level + ' ' + vi_type

	find_content = soup.find('div', {'class': 'entry-content'})
	list_child_content = find_content.findChildren(recursive=False)

	content_summary = ''
	content_xuoi = ''
	content_xuoi_1 = ''
	content_xuoi_2 = ''
	content_xuoi_3 = ''
	content_xuoi_4 = ''
	content_nguoc = ''
	content_other = ''

	title_tag = ''
	title_sub_tag = ''
	for child in list_child_content:
		tag_name = child.name
		if tag_name == 'h2':
			title_tag = child.getText()
			title_sub_tag = ''
		else:
			if tag_name == 'h3':
				title_sub_tag = child.getText()
			else:
				if title_tag == 'Ý nghĩa tổng quan':
					content_summary += child.getText() + '\n'

				if title_tag == 'Khi lá bài xuôi':
					if title_sub_tag == '':
						content_xuoi += child.getText() + '\n'
					else:
						if title_sub_tag == 'Trong công việc':
							content_xuoi_1 += child.getText() + '\n'
						if title_sub_tag == 'Trong tình yêu':
							content_xuoi_2 += child.getText() + '\n'
						if title_sub_tag == 'Trong vấn đề tài chính':
							content_xuoi_3 += child.getText() + '\n'
						if title_sub_tag == 'Trong khía cạnh sức khỏe':
							content_xuoi_4 += child.getText() + '\n'
				if title_tag == 'Khi lá bài ngược':
					content_nguoc += child.getText() + '\n'

				if title_tag == 'Khi phối hợp với các lá khác':
					content_other += child.getText() + '\n'

	return (title, vi_name, content_summary, None, content_xuoi, content_xuoi_1, content_xuoi_2, content_xuoi_3, content_xuoi_4, content_nguoc, content_other, link_image, type_card, level, link_card)

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

for page in range(1,7):
	print(page)
	link_list_card = domain + 'category/78-l-bi/page/' + str(page)
	res = requests.get(link_list_card)
	data = res.text

	soup = BeautifulSoup(data, features="lxml")
	cards = soup.findAll('a', {"class": "post-image"})
	val_tarots = []
	for card in cards:
		link_detail = card['href']
		if 'y-nghia-la-bai' in link_detail:
			print(link_detail)
			detail = get_detail_card(link_detail)
			val_tarots.append(detail)

	sql_tarots = "INSERT INTO tarot (name, name_translate, summary, attributes, meaning_summary, meaning_job, meaning_love, meaning_money, meaning_heath, meaning_reverse, meaning_other, image, type, level, link_origin) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"
	mycursor.executemany(sql_tarots, val_tarots)
	mydb.commit()