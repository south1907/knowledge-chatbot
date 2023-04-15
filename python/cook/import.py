import json
import mysql.connector

with open('results.json', 'r') as f:
	data = json.loads(f.read())

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

val_recipes = []
for item in data:
	val_recipes.append([item['name'], item['image'], item['link'], item['summary'], json.dumps((item['ingredients'])), json.dumps(item['equipments']), item['step_by_step']])

sql_recipes = "INSERT INTO recipes (name, image, link, summary, ingredients, equipments, step_by_step) VALUES (%s, %s, %s, %s, %s, %s, %s)"
mycursor.executemany(sql_recipes, val_recipes)
mydb.commit()