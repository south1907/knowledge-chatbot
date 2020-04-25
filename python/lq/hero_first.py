
import requests
from bs4 import BeautifulSoup
import json
import mysql.connector
import time

#main info: https://lienquan.garena.vn/tuong
#detail: https://lienquan.garena.vn/tuong-chi-tiet/19

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

def insert_hero(cursor, data):
	sql_hero = "INSERT INTO heros (name, image, story, hero_type_id, hero_type_name, link_origin) VALUES (%s, %s, %s, %s, %s, %s)"
	cursor.execute(sql_hero, data)
	return cursor.lastrowid

def insert_skills(cursor, data):
	sql_skill = "INSERT INTO hero_skills (name, image, description, hero_id) VALUES (%s, %s, %s, %s)"
	cursor.executemany(sql_skill, data)

def insert_skins(cursor, data):
	sql_skin = "INSERT INTO hero_skins (image, hero_id, hero_name) VALUES (%s, %s, %s)"
	cursor.executemany(sql_skin, data)

def insert_attrs(cursor, data):
	sql_attr = "INSERT INTO hero_info_details (dame_physical, dame_mage, hp, armor, armor_mage, attack_speed, cooldown, critical, speed, healthy_regenerate, mana_regenerate, around, data_increate, hero_id) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"
	cursor.execute(sql_attr, data)

res = requests.get('https://lienquan.garena.vn/tuong')
data =res.text

domain = 'https://lienquan.garena.vn'

soup = BeautifulSoup(data)
heros = soup.findAll('li', {"class": "list-champion"})

arr_attr = ['dame_physical', 'dame_mage', 'hp', 'armor', 'armor_mage', 'attack_speed', 'cooldown', 'critical', 'speed', 'healthy_regenerate', 'mana_regenerate', 'around', 'data_increate', 'hero_id']

arr_type_hero = ['', 'Đấu Sĩ', 'Pháp Sư', 'Trợ Thủ', 'Đỡ Đòn', 'Sát Thủ', 'Xạ Thủ']

for hero in heros:

	name = hero.find('p', {'class': 'name'})
	hero_name = name.getText()
	print(hero_name)

	type_id = int(name['data-type'])
	type_name = arr_type_hero[type_id]
	image = domain + hero.find('img')['src']
	
	link_origin = hero.find('a')['href']

	res = requests.get(link_origin)
	detail_data = res.text

	soup_detail = BeautifulSoup(detail_data)
	
	# print(attr_val)
	infos = soup_detail.findAll('div', {"class": "tabs-content"})
	story = infos[1].getText()

	data_hero = (hero_name, image, story, type_id, type_name, link_origin)

	# insert hero
	hero_id = insert_hero(mycursor, data_hero)

	skills = infos[2].find('table').findAll('tr')
	data_skill = []
	for skill in skills:
		skill_image = domain + skill.find('img')['src']
		name_skill = skill.find('strong').getText()
		desc_skill = skill.findAll('td')[1].getText().strip()

		tmp = (name_skill, skill_image, desc_skill, hero_id)
		data_skill.append(tmp)
	
	#insert skill
	insert_skills(mycursor, data_skill)

	skins = soup_detail.find('div', {"class": "cont-skin"}).findAll('img')
	data_skin = []
	for skin in skins:
		if skin['src'] != '':
			link_skin = domain + skin['src']

			tmp = (link_skin, hero_id, hero_name)
			data_skin.append(tmp)

	#insert skin
	insert_skins(mycursor, data_skin)

	attributes = soup_detail.find('div', {"class": "cont"}).findAll('p')

	attr_val = []
	data_increate = []
	for attr in attributes:
		val = attr.find('span').getText().strip()
		attr_val.append(val)
		
		try:
			incr = attr.find('span')['data-increase']
			data_increate.append(val)

		except Exception as e:
			pass

	data_increate = ";".join(data_increate)
	attr_val.append(data_increate)
	attr_val.append(hero_id)

	attr_val = tuple(attr_val)
	insert_attrs(mycursor, attr_val)

end_time = time.time()
total_time = end_time - start_time

mydb.commit()
print("total_time: " + str(total_time))