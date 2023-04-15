with open('name.txt', 'r') as f:
	data = f.read().split('\n')

map_data = {}
for item in data:
	if item not in map_data:
		map_data[item] = 0

	map_data[item] += 1

for i in map_data:
	print(i + '\t' + str(map_data[i]))