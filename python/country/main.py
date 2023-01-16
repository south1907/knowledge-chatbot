import requests
from bs4 import BeautifulSoup
import json
import mysql.connector
import time
from lxml import etree
from io import StringIO, BytesIO
import os

#list country: https://vi.wikipedia.org/wiki/Danh_s%C3%A1ch_qu%E1%BB%91c_gia_theo_d%C3%A2n_s%E1%BB%91
#detail: https://vi.wikipedia.org/wiki/Vi%E1%BB%87t_Nam

domain = 'https://vi.wikipedia.org'

def get_value_from_raw_string(raw_string, from_str, end_str):
	index_start = raw_string.find(from_str)
	if index_start == -1:
		return ''

	index_start += len(from_str)
	index_end = raw_string.find(end_str, index_start)
	if index_end == -1:
		return ''
	return raw_string[index_start:index_end]

def get_detail_country(link_country):
	res = requests.get(link_country)
	html = res.text
	soup = BeautifulSoup(html, features="html.parser")

	table_info = soup.find('table', {"class": "infobox"})
	

	find_flag = table_info.find('div', {"style": "display:table; width:100%;"})

	if find_flag is None:
		find_flag = table_info.find('table', {"style": "width: 100%; background-color: none; text-align: center"})
	
	list_img = table_info.findAll('img')

	link_flag = 'https:' + list_img[0]['src']
	link_coat_of_arms = 'https:' + list_img[1]['src']
	link_location = 'https:' + list_img[2]['src']

	trs = table_info.findAll('tr')

	area = ''
	population = ''
	capital = ''
	timezone = ''
	for tr in trs:
		if tr.find('th'):
			th = tr.find('th').getText()
			th = th.replace(' ', ' ').replace('• ', '')
			# print(th)
			if tr.find('td') and tr.find('td').getText() != None:
				td = tr.find('td').getText()
				if th == 'Diện tích' or th == 'Tổng cộng':
					split_td = td.split(' km2')
					split_td = split_td[0].split(' km²')
					area = split_td[0]

				if 'Điều tra' in th or 'Dân số ước lượng' in th:
					split_td = td.split(' người')
					split_td = split_td[0].split('[')
					population = split_td[0].strip()

				if th == 'Thủ đô':
					if tr.find('td').find('a'):
						capital = tr.find('td').find('a').getText()
					else:
						capital = tr.find('td').getText()

				if th == 'Múi giờ':
					timezone = td

	return (link_country, link_flag, link_coat_of_arms, link_location, area, population, capital, timezone)

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

link_list_country = 'https://vi.wikipedia.org/wiki/Danh_s%C3%A1ch_qu%E1%BB%91c_gia_theo_d%C3%A2n_s%E1%BB%91'

print(link_list_country)

res = requests.get(link_list_country)
data = res.text

# print(data)

soup = BeautifulSoup(data, features="lxml")
countries = soup.find('table', {"class": "wikitable"}).findAll('tr')

val_countries = []
countries = countries[2:]
for country in countries:
	tds = country.findAll('td')
	i_link = tds[1].find('a')['href']
	name = tds[1].find('a').getText()
	print(name)
	link_country = domain + i_link
	# link_country = 'https://vi.wikipedia.org/wiki/Myanmar'
	if link_country == 'https://vi.wikipedia.org/wiki/Guyane_thu%E1%BB%99c_Ph%C3%A1p' or link_country == 'https://vi.wikipedia.org/wiki/Qu%E1%BA%A7n_%C4%91%E1%BA%A3o_Eo_Bi%E1%BB%83n':
		continue
	detail = get_detail_country(link_country)

	list_val = list(detail)
	list_val.insert(0, name)
	detail = tuple(list_val)
	val_countries.append(detail)

sql_countries = "INSERT INTO countries (name, link_country, link_flag, link_coat_of_arms, link_location, area, population, capital, timezone) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)"
mycursor.executemany(sql_countries, val_countries)
mydb.commit()
