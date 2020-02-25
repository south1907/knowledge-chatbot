import json
import mysql.connector
import requests
from bs4 import BeautifulSoup
from decode import decode_html

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

start = 1
end = 33
for lesson in range(start, end):
	print(lesson)
	language = "JA"

	response = requests.get('https://www.vnjpclub.com/kanji-look-and-learn/bai-'+ str(lesson) +'.html')

	# decode html
	data = decode_html(response.text)

	soup = BeautifulSoup(data)

	words = soup.findAll('table')
	synonyms = soup.findAll('div', {"class": "khungngoai1"})

	# if same same then process
	if len(words) == len(synonyms):

		val_words = []
		for i in range(0, len(words)):

			word = words[i]
			synonym = synonyms[i]

			main_word = word.find('span', {'class': 'hantu'}).getText()

			name_word = word.find('center').find('font').getText()

			mean_word = word.find('td', {'width': '200px'}).find('font', {'size': 4}).getText().strip()

			pronounce = word.find('td', {'width': '200px'}).find('font', {'size': 5}).getText().strip()

			image = 'https://www.vnjpclub.com' + word.find('img')['src']

			tip_memory = word.findAll('td')[3].findAll('font')[1].getText()

			word_synonyms = synonym.findAll('ruby')
			list_syn = []
			for j in range(0, len(word_synonyms)):
				word_syn = word_synonyms[j]

				name_syn = word_syn.getText()
				str_tag_word_syn = str(word_syn)
				index_tag = data.find(str_tag_word_syn)
				end_tag = data.find('<br />', index_tag)

				mean_syn = ''
				if data[index_tag + len(str_tag_word_syn): index_tag + len(str_tag_word_syn) + 1] == ' ':
					mean_syn = data[index_tag + len(str_tag_word_syn) + 1:end_tag].replace(':', '').strip()

				elif data[index_tag + len(str_tag_word_syn): index_tag + len(str_tag_word_syn) + 1] == '<':
					j += 1
					word_syn = word_synonyms[j]
					name_syn += " " + word_syn.getText()
					str_tag_word_syn = str(word_syn)
					index_tag = data.find(str_tag_word_syn)

					mean_syn = data[index_tag + len(str_tag_word_syn) + 1:end_tag].replace(':', '').strip()

				if mean_syn != '':
					row_syn = name_syn + ': ' + mean_syn
					list_syn.append(row_syn)
			
			addition = ";".join(list_syn)
			temp_word = (main_word, name_word, mean_word, tip_memory, pronounce, image, addition, lesson, language)
			val_words.append(temp_word)

		# insert
		sql_words = "INSERT INTO words (word, name_word, means, tip_memory, pronounce, image, addition, lesson, language) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)"
		mycursor.executemany(sql_words, val_words)
		mydb.commit()
		print(mycursor.rowcount, "answers was inserted")

	else:
		print('not match')