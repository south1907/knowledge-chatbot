 # -*- coding: utf-8 -*-
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

mycursor.execute("SELECT id, name, content FROM musics LIMIT 1000")
musics_indb = mycursor.fetchall()

# print(musics_indb[0])

val_update_music = []
current_count = 0
sql_update_music = "UPDATE musics SET emotion = %s WHERE id = %s"

one_word = ['vui', 'buồn', 'giận', 'chán', 'ghét', 'sợ', 'thích', 'yêu', 'ghê', 'khó', 'khóc', 'nhớ']
two_word = ['đau khổ', 'chán ghét', 'bực mình', 'hận đời', 'chia tay', 'hạnh phúc', 'ngạc nhiên', 'ghê tởm', 'sợ hãi', 'cô đơn', 'một mình', 'đang yêu', 'giận giữ', 'bội bạc', 'phản bội', 'thương', 'từng yêu', 'bối rối', 'lo lắng', 'lo âu', 'khó chịu', 'thất vọng', 'tội lỗi', 'hi vọng', 'tổn thương', 'mong nhớ', 'oán hận', 'buồn rầu', 'hối hận', 'hối tiếc', 'tạm biệt']

for music in musics_indb:
	result = {}
	title = music[1].lower()
	id_music = music[0]
	print(title)
	split_title = title.split(" ")
	for i in range(0, len(split_title)):
		one = split_title[i]
		if one in one_word:
			if one == 'khóc':
				one = 'buồn'
			if one not in result:
				result[one] = 0
			result[one] += 10

		if i < len(split_title) - 1:
			two = split_title[i] + " " + split_title[i + 1]
			if two in two_word:
				if two == 'tạm biệt':
					two = 'chia tay'
				if two not in result:
					result[two] = 0
				result[two] += 20
	

	sentences = music[2].split("\n")
	for sentence in sentences:
		# print(sentence)
		split_sentence = sentence.split(" ")
		for i in range(0, len(split_sentence)):
			one = split_sentence[i]
			if one in one_word:
				if one == 'khóc':
					one = 'buồn'
				if one not in result:
					result[one] = 0
				result[one] += 2

			if i < len(split_sentence) - 1:
				two = split_sentence[i] + " " + split_sentence[i + 1]
				if two in two_word:
					if two == 'tạm biệt':
						two = 'chia tay'
					if two not in result:
						result[two] = 0
					result[two] += 10


	# tạm biệt = chia tay, khóc = buồn
	result = sorted(result.items(), key=lambda x: x[1], reverse=True)
	curr_max = 0
	word_emotions = []

	for it in result:
		emo = it[0]
		point = it[1]
		if len(word_emotions) > 0 and curr_max == 0:
			curr_max = point

		if curr_max == 0:
			word_emotions.append(emo)
		elif curr_max == point:
			word_emotions.append(emo)
		else:
			break
	
	str_emotion = ",".join(word_emotions)

	temp_val = (str_emotion, id_music)
	val_update_music.append(temp_val)

mycursor.executemany(sql_update_music, val_update_music)
mydb.commit()

end_time = time.time()
print('total time: ' + str(end_time - start_time))