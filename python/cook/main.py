import requests
from bs4 import BeautifulSoup
import json
import mysql.connector
import time
from lxml import etree
from io import StringIO, BytesIO
import os

# http://www.foodstf.com/recipes?page=1

domain = 'http://www.foodstf.com'

def get_detail(link_detail):
	res = requests.get(link_detail)
	data = res.text

	soup = BeautifulSoup(data, features="lxml")
	image = soup.find('div', {"class": "post-thumb"}).find('img')['src']

	find_content = soup.find('div', {"class": "post-content"})
	name = find_content.find('h2').getText()

	children = find_content.findChildren(recursive=False)
	tmp_check = None
	summary = ''
	ingredients = []
	equipments = []
	str_step = ''

	for child in children:
		child_text = child.getText().strip()
		if child_text == name:
			tmp_check = 'summary'
			continue

		if child_text == 'Ingredients:':
			tmp_check = 'ingredients'
			continue

		if child_text == 'Equipment:':
			tmp_check = 'equipment'
			continue

		if child_text == 'Step by step:':
			tmp_check = 'step'
			continue

		if child_text == 'Nutrition Information:':
			tmp_check = None
			continue

		if tmp_check == 'summary':
			summary = child_text
			tmp_check = None

		if tmp_check == 'ingredients':
			find_list = child.findAll('div', {"class": "col-lg-3"})
			for item in find_list:
				ingredients.append({
					'name': item.getText().strip(),
					'image': item.find('img')['src']
					})
			tmp_check = None

		if tmp_check == 'equipment':
			find_list = child.findAll('div', {"class": "col-lg-3"})
			for item in find_list:
				equipments.append({
					'name': item.getText().strip(),
					'image': item.find('img')['src']
					})
			tmp_check = None

		if tmp_check == 'step':
			if child_text != '':
				str_step += child_text + '\n'

	return {
		'link': link_detail,
		'name': name,
		'image': image,
		'summary': summary,
		'ingredients': ingredients,
		'equipments': equipments,
		'step_by_step': str_step
	}

results = []
for page in range(1, 10000):
	print(page)
	url_recipes = 'http://www.foodstf.com/recipes?page=' + str(page)

	res = requests.get(url_recipes)
	data = res.text

	soup = BeautifulSoup(data, features="lxml")
	recipes = soup.findAll('div', {"class": "list-blog"})

	if len(recipes) == 0:
		print('done')
		break
	for recipe in recipes:
		try:
			link_detail = domain + recipe.find('div', {"class": "post-content"}).find('a', recursive=False)['href']
			print('get detail: ' + link_detail)
			detail = get_detail(link_detail)
			results.append(detail)
		except Exception as e:
			pass

with open('results.json', 'w+') as fileToSave:
	json.dump(results, fileToSave, ensure_ascii=True, indent=4, sort_keys=True)