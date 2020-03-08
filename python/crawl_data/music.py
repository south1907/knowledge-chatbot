from bs4 import BeautifulSoup
import requests
import json
import mysql.connector
import time

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

url_feed = 'https://hopamchuan.com/partial/home_partial/song_feed'

for page in range(1,10000):
	print(page)
	data = {
	  'page': str(page),
	  'filter': 'month'
	}

	response = requests.post(url_feed, data=data)
	data = response.text

	soup = BeautifulSoup(data, 'html.parser')

	list_item = soup.findAll('a', {'class': 'song-title'})

	if len(list_item) == 0:
		print('het r')
		break

	val_musics = []
	for item in list_item:

		name = item.getText().strip()
		name_origin = name
		link_origin = item['href']

		temp_music = (name, name_origin, link_origin)
		val_musics.append(temp_music)

	sql_musics = "INSERT INTO musics (name, name_origin, link_origin) VALUES (%s, %s, %s)"
	mycursor.executemany(sql_musics, val_musics)
	mydb.commit()

	# break
end_time = time.time()
print('total time: ' + str(end_time - start_time))
# total time: 1070.4251668453217