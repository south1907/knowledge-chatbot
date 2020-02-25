import json
import mysql.connector
import requests

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

response = requests.get('https://tuhoconline.net/tong-hop-chu-han-n4.html/2')

print(response.text)