import json
import mysql.connector
import time
from unidecode import unidecode

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
delete_mashup = "DELETE from musics where name like '%mashup%'"
mycursor.execute(delete_mashup)

mycursor.execute("SELECT id, name FROM musics WHERE name in ( \
				SELECT name FROM `musics` \
				GROUP by name \
				HAVING count(1) > 1)"
				)
musics_indb = mycursor.fetchall()
id_list = []
list_name = []

for music in musics_indb:
	id_music = music[0]
	name = unidecode(music[1].strip().lower())
	if name not in list_name:
		list_name.append(name)
	else:
		id_list.append(str(id_music))

in_id = "(" + ",".join(id_list) + ")"

if in_id != '()':
	delete_query = "DELETE FROM musics WHERE id in " + in_id
	mycursor.execute(delete_query)
mydb.commit()

# update name_short
query_all = "SELECT id, name_short FROM `musics`"
mycursor.execute(query_all)

musics_all = mycursor.fetchall()

sql_update_nameshort = "UPDATE musics SET name_short = %s WHERE id = %s "

val_update_nameshort = []
for music in musics_all:
	id_music = music[0]
	name_short = unidecode(music[1])

	temp_val = (name_short, id_music)
	val_update_nameshort.append(temp_val)

mycursor.executemany(sql_update_nameshort, val_update_nameshort)
mydb.commit()

# replace nbsp
query_nbsp = "SELECT id, content, content_chord FROM `musics` WHERE content like '%nbsp%'"
mycursor.execute(query_nbsp)

musics_nbsp = mycursor.fetchall()

sql_update_nbsp = "UPDATE musics SET content = %s, content_chord = %s WHERE id = %s "

val_update_nbsp = []
for music in musics_nbsp:
	id_music = music[0]
	content = music[1].replace('nbsp', ' ')
	content_chord = music[2].replace('&nbsp;', '')

	temp_val = (content, content_chord, id_music)
	val_update_nbsp.append(temp_val)

mycursor.executemany(sql_update_nbsp, val_update_nbsp)
mydb.commit()
