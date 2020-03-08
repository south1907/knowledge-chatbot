from bs4 import BeautifulSoup
import re, string
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

mycursor.execute("SELECT id, link_origin FROM musics WHERE is_runed = 0")
musics_indb = mycursor.fetchall()

# print(musics_indb[0])

val_update_music = []
current_count = 0
sql_update_music = "UPDATE musics SET name = %s, name_short = %s, content = %s, content_chord = %s, note_chord = %s, rhythm = %s, youtube = %s, is_runed = %s WHERE id = %s "

for music in musics_indb:

	try:
		current_count += 1

		id_music = music[0]
		print(id_music)

		url_detail = music[1]

		response = requests.get(url_detail)
		data = response.text

		soup = BeautifulSoup(data, 'html.parser')

		youtube = soup.find('a', {'class': 'play-button'})
		if youtube:
			youtube = youtube['href']
		else:
			youtube = ''

		rhythm = soup.find('span', {'id': 'display-rhythm'})
		rhythm = rhythm.getText().strip()

		song_title = soup.find('h1', {'id': 'song-title'})
		song_title = song_title.getText().strip()

		name = re.sub("\(.*\)", "", song_title)
		split_name = name.lower().split(' ')

		name_short = ''
		for i_name in split_name:
			if i_name != '':
				name_short += i_name[0]

		# print(name_short)

		note_chord = soup.find('div', {'class': 'song-lyric-note'})

		note_chord_str = ''
		notes = []

		if note_chord:
			# neu co note
			notes = note_chord.findAll('div', {'class': 'chord_lyric_line'})
			for note in notes:
				note_chord_str += note.getText().strip() + ' '

		# print(note_chord_str)

		sentences = soup.findAll('div', {'class': 'chord_lyric_line'})

		arr_sentence = []
		arr_sentence_no_chord = []

		count = 0
		len_notes = len(notes)

		for item in sentences:

			count += 1

			# remove note
			if count <= len_notes:
				continue


			class_item = item['class']

			if 'empty_line' in class_item or 'text_only' in class_item:
				continue

			sentence = item.getText().strip()

			sentence_no_chord = re.sub("\[.{1,5}\]", "", sentence)
			arr_sentence.append(sentence)

			# lower
			sentence_no_chord = sentence_no_chord.lower()

			# remove 1. 2. in first sentence
			sentence_no_chord = re.sub("(\d)+\.", "", sentence_no_chord)

			# remove punctuation
			sentence_no_chord = re.sub('[%s]' % re.escape(string.punctuation), '', sentence_no_chord)

			# replace two space
			sentence_no_chord = sentence_no_chord.replace('  ', ' ')
			arr_sentence_no_chord.append(sentence_no_chord.strip())

		temp_val = (name, name_short, "\n".join(arr_sentence_no_chord), "\n".join(arr_sentence), note_chord_str, rhythm, youtube, 1, id_music)
		val_update_music.append(temp_val)

		if current_count > 10:

			mycursor.executemany(sql_update_music, val_update_music)
			mydb.commit()

			# reset 
			
			val_update_music = []
			current_count = 0
			
	except Exception as e:
		print('loi roi, sleep 5')
		val_update_music = []
		current_count = 0
		time.sleep(5)
	
# update last
if current_count != 0:
	mycursor.executemany(sql_update_music, val_update_music)
	mydb.commit()

end_time = time.time()
print('total time: ' + str(end_time - start_time))