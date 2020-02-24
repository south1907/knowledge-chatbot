Normal chat from facebook webhook
```
{
	"entry": [
		{
			"id": 110620843802028,
			"time": 1578845572975,
			"messaging"	: [
				{
					"sender": {
						"id": 2649817768468983
					},
					"recipient": {
						"id": 110620843802028
					},
					"message": {
						"mid":"m_DpPFPZUG8cxwINwnoHj67_UAOK5hAqEOwb_qpbfvVeWtKwhp3y8pvfbF-OxlKXnCdiEMARCgASozvDcRqtmYDQ",
						"text": "Xin chao"
					}
				}	
			]
		}
	]
}
```


Sample like (image) from facebook webhook

```{
	"entry": [
		{
			"id": 110620843802028,
			"time": 1578845572975,
			"messaging"	: [
				{
					"sender": {
						"id": 2649817768468983
					},
					"recipient": {
						"id": 110620843802028
					},
					"message": {
						"mid":"m_DpPFPZUG8cxwINwnoHj67_UAOK5hAqEOwb_qpbfvVeWtKwhp3y8pvfbF-OxlKXnCdiEMARCgASozvDcRqtmYDQ",
						"attachments": [
							{
								"type": "image",
								"payload": {
									"url": "https://scontent.xx.fbcdn.net/v/t39.1997-6/39178562_1505197616293642_5411344281094848512_n.png?_nc_cat=1&_nc_oc=AQkvhykv1AUw44bJWysYBbiCI2oR6UgvlkDu9XhY83a-pBKSHHmXstGvKy8Bwy6NwgMz3WgXl-WrRZJ-qNYEqNi1&_nc_ipfwd=1&_nc_ad=z-m&_nc_cid=0&_nc_zor=9&_nc_ht=scontent.xx&oh=0ca1a53ba860345aaebd5090f74ba4be&oe=5E91FC75",
									"sticker_id": 369239263222822
								}
							}
						],

						"sticker_id": 369239263222822
					}
				}	
			]
		}
	]
}
```

Sample post to send user

```
{
  "recipient":{
    "id":"{{PSID}}"
  },
  "messaging_type": "response",
  "message":{
	"text": "Hello, world!"
  }
}
```

###  pattern 

/(anh|em).*(yêu|thương|thích|mến) (anh|em)/
(anh|em).*(yêu|thương|thích|mến) (anh|em).(?!không|chứ)
--> don't have không, chứ in last sentence

làm vợ anh nhé
lấy anh nhé
lấy anh đi
anh muốn nói điều này quan trọng với em
anh bảo cái này
biến đi
cút đi
biến mẹ mày đi
tạm biệt nhé
bye
mai gặp lại
thằng chó
vì sao chứ
có lẽ  mình không hợp nhau
là mình không hợp nhau
quên em đi anh
quên anh đi em
chia tay đi
chúc em ngủ ngon
anh ngủ đi mai còn đi làm
chào em
em có yêu anh không
em có yêu anh chứ
em yêu anh
anh yêu em nhiều lắm


==============

NOTE: 

remove null value json encode to post API
```
$json_str = json_encode($obj);
$json_str = preg_replace('/,\s*"[^"]+":null|"[^"]+":null,?/', '', $json_str);
print_r($json_str);
```