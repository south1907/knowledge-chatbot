import json
import mysql.connector

with open('files/config.json') as f:
	config = json.loads(f.read())
	f.close()
with open('files/data.csv') as f:
	data = f.read().split("\n")
	del data[0]
	f.close()

mysql_config = config['mysql']

mydb = mysql.connector.connect(
	host=mysql_config['host'],
	user=mysql_config['username'],
	passwd=mysql_config['password'],
	database=mysql_config['database']
)

mycursor = mydb.cursor()

mycursor.execute("SELECT name FROM intents")
intents_indb = mycursor.fetchall()
intents_indb = [row[0] for row in intents_indb]

for item in intents_indb:
	print(item)

current_intent = ''
patterns = []
sentences = []
answers = []
for row in data:
	row_split = row.split("\t")

	# if len == 6 --> enough field --> process
	if len(row_split) == 6:
		row_intent = row_split[0]
		row_pattern = row_split[1]
		row_sentence = row_split[2]
		row_answer = row_split[3]

		if row_intent != '':
			# finish current_intent and insert to db if current_intent != ''
			if current_intent != '':
				print(patterns)
				print(answers)
				print(sentences)

				# set null list
				patterns = []
				answers = []
				sentences = []

				#TODO: insert to db

			current_intent = row_intent
		
		if row_pattern != '':
			# add pattern to list
			patterns.append(row_pattern)

		if row_sentence != '':
			# add sentence to list
			sentences.append(row_sentence)
		
		if row_answer != '':
			# add answer to list
			temp = {
				"answer": row_answer,
				"gender": row_split[4],
				"positive": row_split[5],
			}
			answers.append(temp)

#TODO: insert last intent
print(patterns)
print(answers)
print(sentences)