# LBS 服务

- IP定位，通过终端设备IP地址获取其当前所在地理位置，精确到市级。

```
return app('lbs')->ip('58.32.3.36');
```

返回结果

```
{
    "code": "310000",
    "province": "上海市",
    "city": "上海市",
    "district": "",
    "lat": 31.23037,
    "lng": 121.4737
}
```

- 地址解析（地址转坐标）

```
return app('lbs')->address2location('上海市伸大厦');
```

返回结果

```
{
    "code": "310107",
    "province": "上海市",
    "city": "上海市",
    "district": "普陀区",
    "lat": 31.229013,
    "lng": 121.409668
}
```

- 逆地址解析（坐标位置描述），输入坐标返回地理位置信息和附近poi列表。

```
return app('lbs')->location2address('31.229013', '121.409668');
```

返回结果

```
{
    "code": "310107",
    "province": "上海市",
    "city": "上海市",
    "district": "普陀区",
    "address": "上海市普陀区中山北路"
}
```

- 获取省市区列表，用于获取全部省市区三级行政区划

```
return app('lbs')->district('310000');
```

返回结果

```
[
    {
    "code": "310101",
    "name": "黄浦区"
    },
    {
    "code": "310104",
    "name": "徐汇区"
    },
    {
    "code": "310105",
    "name": "长宁区"
    },
    // ...
]
```
