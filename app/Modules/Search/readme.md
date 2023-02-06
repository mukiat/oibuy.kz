# 一、安装ES（可选）

如果使用公用云的ES服务，此步骤可跳过。

### 1、安装 elasticsearch 服务端

```
sudo apt install gnupg2
wget https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-7.11.2-amd64.deb
wget https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-7.11.2-amd64.deb.sha512
shasum -a 512 -c elasticsearch-7.11.2-amd64.deb.sha512 
sudo dpkg -i elasticsearch-7.11.2-amd64.deb
```

https://www.elastic.co/guide/en/elasticsearch/reference/7.11/install-elasticsearch.html

- 配置目录

/etc/elasticsearch/

### 2、安装分词插件

```
sudo /usr/share/elasticsearch/bin/elasticsearch-plugin install https://github.com/medcl/elasticsearch-analysis-ik/releases/download/v7.11.2/elasticsearch-analysis-ik-7.11.2.zip

sudo /usr/share/elasticsearch/bin/elasticsearch-plugin install https://github.com/medcl/elasticsearch-analysis-pinyin/releases/download/v7.11.2/elasticsearch-analysis-pinyin-7.11.2.zip
```

### 3、测试分词

关于ik分词器的分词类型【analyzer】（可以根据需求进行选择）：

- ik_max_word：会将文本做最细粒度的拆分，比如会将“中华人民共和国国歌”拆分为“中华人民共和国,中华人民,中华,华人,人民共和国,人民,人,民,共和国,共和,和,国国,国歌”，会穷尽各种可能的组合；

- ik_smart：会做最粗粒度的拆分，比如会将“中华人民共和国国歌”拆分为“中华人民共和国,国歌”。如下：

```
curl http://127.0.0.1:9200/_analyze -X POST -H 'Content-Type: application/json' -d '{"analyzer": "ik_max_word","text":"中华人民共和国"}'
```

# 二、配置 es 搜索服务

### 1、编辑 .env 文件

```
SCOUT_DRIVER=elasticsearch
ELASTICSEARCH_HOST=http://127.0.0.1:9200 # ES服务器地址
ELASTICSEARCH_USERNAME=elastic # 登录账号
ELASTICSEARCH_PASSWORD=secret # 登录密码
ELASTICSEARCH_INDEX=goods_index  # 索引（认为是mysql的表）的名称
```

### 2、执行 `php artisan optimize` 更新配置缓存

# 三、Kibana dev_tools 中手动创建索引（可理解为建表）

```
PUT hk_goods_index
{
  "mappings": {
    "dynamic_templates": [
      {
        "message_full": {
          "match": "message_full",
          "mapping": {
            "fields": {
              "keyword": {
                "ignore_above": 2048,
                "type": "keyword"
              }
            },
            "type": "text"
          }
        }
      },
      {
        "message": {
          "match": "message",
          "mapping": {
            "type": "text"
          }
        }
      },
      {
        "strings": {
          "match_mapping_type": "string",
          "mapping": {
            "type": "text"
          }
        }
      }
    ],
    "properties": {
      "brand_id": {
        "type": "long"
      },
      "brand_name": {
        "type": "text"
      },
      "goods_id": {
        "type": "long"
      },
      "goods_name": {
        "type": "text"
      },
      "goods_sn": {
        "type": "text"
      },
      "goods_thumb": {
        "type": "keyword"
      },
      "is_alone_sale": {
        "type": "long"
      },
      "is_delete": {
        "type": "long"
      },
      "is_on_sale": {
        "type": "long"
      },
      "is_show": {
        "type": "long"
      },
      "keywords": {
        "type": "text"
      },
      "rz_shopName": {
        "type": "text"
      },
      "sales_volume": {
        "type": "long"
      },
      "shop_price": {
        "type": "keyword"
      },
      "user_id": {
        "type": "long"
      }
    }
  }
}
```

# 四、导入数据及更新

### 1、启用 scout.php 队列（可选）

```
'queue' => true,
```

### 2、导入数据

Scout 提供了 Artisan 命令 import 用来导入所有已存在的记录到搜索索引：

```
php artisan scout:import "App\Modules\Search\Models\Goods"
```

### 3、清空数据

flush 命令可用于从搜索索引中删除所有模型的记录：

```
php artisan scout:flush "App\Modules\Search\Models\Goods"
```

# 五、其他

推荐启用 Elasticsearch IK 分词插件

腾讯云 Elasticsearch Service 提供了包括开源 ES 支持插件和自研插件在内的10余款插件，为您提供丰富的插件功能。当您购买了腾讯云 ES
实例后，您可以根据需求在插件列表页面安装或者卸载这些插件。本文介绍安装或卸载腾讯云 ES 插件的方法。

https://cloud.tencent.com/document/product/845/46249
