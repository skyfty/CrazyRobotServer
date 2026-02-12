---
title: 本地赛博炮手
language_tabs:
  - shell: Shell
  - http: HTTP
  - javascript: JavaScript
  - ruby: Ruby
  - python: Python
  - php: PHP
  - java: Java
  - go: Go
toc_footers: []
includes: []
search: true
code_clipboard: true
highlight_theme: darkula
headingLevel: 2
generator: "@tarslib/widdershins v4.0.30"

---

# 本地赛博炮手

Base URLs:

# Authentication

# Default

## POST 抽奖

POST /v1/RandomEquipment.php

> Body 请求参数

```yaml
num: "2"
openid: oMZEm7Qkghj7kVHN75VXV_86G8VE

```

### 请求参数

|名称|位置|类型|必选|说明|
|---|---|---|---|---|
|body|body|object| 是 |none|
|» num|body|string| 是 |抽奖数量|
|» openid|body|string| 是 |用户标识|

> 返回字段解释

|字段|说明|
|---|---|
|code|状态码|
|msg|状态描述|
|draw_results|抽奖结果|
> 200 Response

```json
{
    "code": 1,
    "msg": "抽奖并添加完成",
    "draw_results": [
        {
            "id": 3,
            "name": "灼烧炮弹",
            "description": "升级可增加伤害时间。",
            "buffid": 1,
            "prefabname": "Bullet_Blue",
            "upgradegold": 13,
            "upgradeatk": 0,
            "attack": 1,
            "range": 3,
            "boomprefabname": "HitBig",
            "UIImageName": "BarrelIconBlue",
            "maxLevel": 10,
            "level": 1
        },
        {
            "id": 1,
            "name": "基础炮弹",
            "description": "升级可增加伤害数值。",
            "buffid": 5,
            "prefabname": "Bullet_Standard",
            "upgradegold": 10,
            "upgradeatk": 1,
            "attack": 1,
            "range": 3,
            "boomprefabname": "HitBig",
            "UIImageName": "BarrelIconBase",
            "maxLevel": 10,
            "level": 1
        }
    ],
    "added_count": 1,
    "duplicate_count": 1,
    "added_equipments": [
        {
            "id": 3,
            "level": 1
        }
    ]
}
```

### 返回结果

|状态码|状态码含义|说明|数据模型|
|---|---|---|---|
|200|[OK](https://tools.ietf.org/html/rfc7231#section-6.3.1)|none|Inline|

### 返回数据结构

# 数据模型

