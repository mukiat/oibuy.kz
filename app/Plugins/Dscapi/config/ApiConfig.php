<?php

namespace App\Plugins\Dscapi\config;

class ApiConfig
{
    public function getConfig()
    {
        $api_data['zh-CN'] = array(
            array(
                'name' => '用户',
                'cat' => 'user',
                'list' => array(
                    array(
                        'name' => '获取会员列表',
                        'val' => 'dsc.user.list.get'
                    ),
                    array(
                        'name' => '获取单条会员信息',
                        'val' => 'dsc.user.info.get'
                    ),
                    array(
                        'name' => '插入会员信息',
                        'val' => 'dsc.user.insert.post'
                    ),
                    array(
                        'name' => '更新会员信息',
                        'val' => 'dsc.user.update.post'
                    ),
                    array(
                        'name' => '删除会员信息',
                        'val' => 'dsc.user.del.get'
                    ),
                    array(
                        'name' => '获取会员收货地址列表',
                        'val' => 'dsc.user.address.list.get'
                    ),
                    array(
                        'name' => '获取单条会员收货地址信息',
                        'val' => 'dsc.user.address.info.get'
                    ),
                    array(
                        'name' => '插入会员收货地址信息',
                        'val' => 'dsc.user.address.insert.post'
                    ),
                    array(
                        'name' => '更新会员收货地址信息',
                        'val' => 'dsc.user.address.update.post'
                    ),
                    array(
                        'name' => '删除会员收货地址信息',
                        'val' => 'dsc.user.address.del.get'
                    )
                )
            ),
            array(
                'name' => '用户等级',
                'cat' => 'user_rank',
                'list' => array(
                    array(
                        'name' => '获取会员等级列表',
                        'val' => 'dsc.user.rank.list.get'
                    ),
                    array(
                        'name' => '获取单条会员等级信息',
                        'val' => 'dsc.user.rank.info.get'
                    ),
                    array(
                        'name' => '插入会员等级信息',
                        'val' => 'dsc.user.rank.insert.post'
                    ),
                    array(
                        'name' => '更新会员等级信息',
                        'val' => 'dsc.user.rank.update.post'
                    ),
                    array(
                        'name' => '删除会员等级信息',
                        'val' => 'dsc.user.rank.del.get'
                    )
                )
            ),
            array(
                'name' => '平台类目',
                'cat' => 'category',
                'list' => array(
                    array(
                        'name' => '获取分类列表',
                        'val' => 'dsc.category.list.get'
                    ),
                    array(
                        'name' => '获取单条分类信息',
                        'val' => 'dsc.category.info.get'
                    ),
                    array(
                        'name' => '插入分类信息',
                        'val' => 'dsc.category.insert.post'
                    ),
                    array(
                        'name' => '更新分类信息',
                        'val' => 'dsc.category.update.post'
                    ),
                    array(
                        'name' => '删除分类信息',
                        'val' => 'dsc.category.del.get'
                    )
                )
            ),
            array(
                'name' => '商家类目',
                'cat' => 'category_seller',
                'list' => array(
                    array(
                        'name' => '获取商家分类列表',
                        'val' => 'dsc.category.seller.list.get'
                    ),
                    array(
                        'name' => '获取单条商家分类信息',
                        'val' => 'dsc.category.seller.info.get'
                    ),
                    array(
                        'name' => '插入商家分类信息',
                        'val' => 'dsc.category.seller.insert.post'
                    ),
                    array(
                        'name' => '更新商家分类信息',
                        'val' => 'dsc.category.seller.update.post'
                    ),
                    array(
                        'name' => '删除商家分类信息',
                        'val' => 'dsc.category.seller.del.get'
                    )
                )
            ), array(
                'name' => '商品',
                'cat' => 'goods',
                'list' => array(
                    array(
                        'name' => '获取商品列表',
                        'val' => 'dsc.goods.list.get'
                    ),
                    array(
                        'name' => '获取单条商品信息',
                        'val' => 'dsc.goods.info.get'
                    ),
                    array(
                        'name' => '插入商品信息',
                        'val' => 'dsc.goods.insert.post'
                    ),
                    array(
                        'name' => '更新商品信息',
                        'val' => 'dsc.goods.update.post'
                    ),
                    array(
                        'name' => '删除商品信息',
                        'val' => 'dsc.goods.del.get'
                    ),
                    array(
                        'name' => '获取商品仓库列表',
                        'val' => 'dsc.goods.warehouse.list.get'
                    ),
                    array(
                        'name' => '获取单条商品仓库信息',
                        'val' => 'dsc.goods.warehouse.info.get'
                    ),
                    array(
                        'name' => '插入商品仓库信息',
                        'val' => 'dsc.goods.warehouse.insert.post'
                    ),
                    array(
                        'name' => '更新商品仓库信息',
                        'val' => 'dsc.goods.warehouse.update.post'
                    ),
                    array(
                        'name' => '删除商品仓库信息',
                        'val' => 'dsc.goods.warehouse.del.get'
                    ),
                    array(
                        'name' => '获取商品地区列表',
                        'val' => 'dsc.goods.area.list.get'
                    ),
                    array(
                        'name' => '获取单条商品地区信息',
                        'val' => 'dsc.goods.area.info.get'
                    ),
                    array(
                        'name' => '插入商品地区信息',
                        'val' => 'dsc.goods.area.insert.post'
                    ),
                    array(
                        'name' => '更新商品地区信息',
                        'val' => 'dsc.goods.area.update.post'
                    ),
                    array(
                        'name' => '删除商品地区信息',
                        'val' => 'dsc.goods.area.del.get'
                    ),
                    array(
                        'name' => '获取商品相册列表',
                        'val' => 'dsc.goods.gallery.list.get'
                    ),
                    array(
                        'name' => '获取单条商品相册信息',
                        'val' => 'dsc.goods.gallery.info.get'
                    ),
                    array(
                        'name' => '插入商品相册信息',
                        'val' => 'dsc.goods.gallery.insert.post'
                    ),
                    array(
                        'name' => '更新商品相册信息',
                        'val' => 'dsc.goods.gallery.update.post'
                    ),
                    array(
                        'name' => '删除商品相册信息',
                        'val' => 'dsc.goods.gallery.del.get'
                    ),
                    array(
                        'name' => '获取商品属性列表',
                        'val' => 'dsc.goods.attr.list.get'
                    ),
                    array(
                        'name' => '获取单条商品属性信息',
                        'val' => 'dsc.goods.attr.info.get'
                    ),
                    array(
                        'name' => '插入商品属性信息',
                        'val' => 'dsc.goods.attr.insert.post'
                    ),
                    array(
                        'name' => '更新商品属性信息',
                        'val' => 'dsc.goods.attr.update.post'
                    ),
                    array(
                        'name' => '删除商品属性信息',
                        'val' => 'dsc.goods.attr.del.get'
                    ),
                    array(
                        'name' => '获取商品运费模板列表',
                        'val' => 'dsc.goods.freight.list.get'
                    ),
                    array(
                        'name' => '获取单条商品运费模板信息',
                        'val' => 'dsc.goods.freight.info.get'
                    ),
                    array(
                        'name' => '插入商品运费模板信息',
                        'val' => 'dsc.goods.freight.insert.post'
                    ),
                    array(
                        'name' => '更新商品运费模板信息',
                        'val' => 'dsc.goods.freight.update.post'
                    ),
                    array(
                        'name' => '删除商品运费模板信息',
                        'val' => 'dsc.goods.freight.del.get'
                    ),
                )
            ),
            array(
                'name' => '商品货品',
                'cat' => 'product',
                'list' => array(
                    array(
                        'name' => '【默认】获取货品列表',
                        'val' => 'dsc.product.list.get'
                    ),
                    array(
                        'name' => '【默认】获取单条货品信息',
                        'val' => 'dsc.product.info.get'
                    ),
                    array(
                        'name' => '【默认】插入货品信息',
                        'val' => 'dsc.product.insert.post'
                    ),
                    array(
                        'name' => '【默认】更新货品信息',
                        'val' => 'dsc.product.update.post'
                    ),
                    array(
                        'name' => '【默认】删除货品信息',
                        'val' => 'dsc.product.del.get'
                    ),
                    array(
                        'name' => '【仓库】获取货品列表',
                        'val' => 'dsc.product.warehouse.list.get'
                    ),
                    array(
                        'name' => '【仓库】获取单条货品信息',
                        'val' => 'dsc.product.warehouse.info.get'
                    ),
                    array(
                        'name' => '【仓库】插入货品信息',
                        'val' => 'dsc.product.warehouse.insert.post'
                    ),
                    array(
                        'name' => '【仓库】更新货品信息',
                        'val' => 'dsc.product.warehouse.update.post'
                    ),
                    array(
                        'name' => '【仓库】删除货品信息',
                        'val' => 'dsc.product.warehouse.del.get'
                    ),
                    array(
                        'name' => '【地区】获取货品列表',
                        'val' => 'dsc.product.area.list.get'
                    ),
                    array(
                        'name' => '【地区】获取单条货品信息',
                        'val' => 'dsc.product.area.info.get'
                    ),
                    array(
                        'name' => '【地区】插入货品信息',
                        'val' => 'dsc.product.area.insert.post'
                    ),
                    array(
                        'name' => '【地区】更新货品信息',
                        'val' => 'dsc.product.area.update.post'
                    ),
                    array(
                        'name' => '【地区】删除货品信息',
                        'val' => 'dsc.product.area.del.get'
                    )
                )
            ),
            array(
                'name' => '品牌',
                'cat' => 'brand',
                'list' => array(
                    array(
                        'name' => '获取品牌列表',
                        'val' => 'dsc.brand.list.get'
                    ),
                    array(
                        'name' => '获取单条品牌信息',
                        'val' => 'dsc.brand.info.get'
                    ),
                    array(
                        'name' => '插入品牌信息',
                        'val' => 'dsc.brand.insert.post'
                    ),
                    array(
                        'name' => '更新品牌信息',
                        'val' => 'dsc.brand.update.post'
                    ),
                    array(
                        'name' => '删除品牌信息',
                        'val' => 'dsc.brand.del.get'
                    )
                )
            ),
            array(
                'name' => '交易',
                'cat' => 'order',
                'list' => array(
                    array(
                        'name' => '获取订单列表',
                        'val' => 'dsc.order.list.get'
                    ),
                    array(
                        'name' => '获取单条订单信息',
                        'val' => 'dsc.order.info.get'
                    ),
                    array(
                        'name' => '插入订单信息',
                        'val' => 'dsc.order.insert.post'
                    ),
                    array(
                        'name' => '更新订单信息',
                        'val' => 'dsc.order.update.post'
                    ),
                    array(
                        'name' => '删除订单信息',
                        'val' => 'dsc.order.del.get'
                    ),
                    array(
                        'name' => '获取订单商品列表',
                        'val' => 'dsc.order.goods.list.get'
                    ),
                    array(
                        'name' => '获取单条订单商品信息',
                        'val' => 'dsc.order.goods.info.get'
                    ),
                    array(
                        'name' => '插入订单商品信息',
                        'val' => 'dsc.order.goods.insert.post'
                    ),
                    array(
                        'name' => '更新订单商品信息',
                        'val' => 'dsc.order.goods.update.post'
                    ),
                    array(
                        'name' => '删除订单商品信息',
                        'val' => 'dsc.order.goods.del.get'
                    )
                )
            ),
            array(
                'name' => '属性类型',
                'cat' => 'goodstype',
                'list' => array(
                    array(
                        'name' => '获取属性类型列表',
                        'val' => 'dsc.goodstype.list.get'
                    ),
                    array(
                        'name' => '获取单条属性类型信息',
                        'val' => 'dsc.goodstype.info.get'
                    ),
                    array(
                        'name' => '插入属性类型信息',
                        'val' => 'dsc.goodstype.insert.post'
                    ),
                    array(
                        'name' => '更新属性类型信息',
                        'val' => 'dsc.goodstype.update.post'
                    ),
                    array(
                        'name' => '删除属性类型信息',
                        'val' => 'dsc.goodstype.del.get'
                    ),
                    array(
                        'name' => '获取属性列表',
                        'val' => 'dsc.attribute.list.get'
                    ),
                    array(
                        'name' => '获取单条属性信息',
                        'val' => 'dsc.attribute.info.get'
                    ),
                    array(
                        'name' => '插入属性信息',
                        'val' => 'dsc.attribute.insert.post'
                    ),
                    array(
                        'name' => '更新属性信息',
                        'val' => 'dsc.attribute.update.post'
                    ),
                    array(
                        'name' => '删除属性信息',
                        'val' => 'dsc.attribute.del.get'
                    )
                )
            ),
            array(
                'name' => '地区',
                'cat' => 'region',
                'list' => array(
                    array(
                        'name' => '获取地区列表',
                        'val' => 'dsc.region.list.get'
                    ),
                    array(
                        'name' => '获取单条地区信息',
                        'val' => 'dsc.region.info.get'
                    ),
                    array(
                        'name' => '插入地区信息',
                        'val' => 'dsc.region.insert.post'
                    ),
                    array(
                        'name' => '更新地区信息',
                        'val' => 'dsc.region.update.post'
                    ),
                    array(
                        'name' => '删除地区信息',
                        'val' => 'dsc.region.del.get'
                    )
                )
            ),
            array(
                'name' => '仓库地区',
                'cat' => 'warehouse',
                'list' => array(
                    array(
                        'name' => '获取仓库地区列表',
                        'val' => 'dsc.warehouse.list.get'
                    ),
                    array(
                        'name' => '获取单条仓库地区信息',
                        'val' => 'dsc.warehouse.info.get'
                    ),
                    array(
                        'name' => '插入仓库地区信息',
                        'val' => 'dsc.warehouse.insert.post'
                    ),
                    array(
                        'name' => '更新仓库地区信息',
                        'val' => 'dsc.warehouse.update.post'
                    ),
                    array(
                        'name' => '删除仓库地区信息',
                        'val' => 'dsc.warehouse.del.get'
                    )
                )
            ),
        );

        $api_data['zh-TW'] = array(
            array(
                'name' => '用戶',
                'cat' => 'user',
                'list' => array(
                    array(
                        'name' => '獲取會員列表',
                        'val' => 'dsc.user.list.get'
                    ),
                    array(
                        'name' => '獲取單條會員信息',
                        'val' => 'dsc.user.info.get'
                    ),
                    array(
                        'name' => '插入會員信息',
                        'val' => 'dsc.user.insert.post'
                    ),
                    array(
                        'name' => '更新會員信息',
                        'val' => 'dsc.user.update.post'
                    ),
                    array(
                        'name' => '刪除會員信息',
                        'val' => 'dsc.user.del.get'
                    ),
                    array(
                        'name' => '獲取會員收貨地址列表',
                        'val' => 'dsc.user.address.list.get'
                    ),
                    array(
                        'name' => '獲取單條會員收貨地址信息',
                        'val' => 'dsc.user.address.info.get'
                    ),
                    array(
                        'name' => '插入會員收貨地址信息',
                        'val' => 'dsc.user.address.insert.post'
                    ),
                    array(
                        'name' => '更新會員收貨地址信息',
                        'val' => 'dsc.user.address.update.post'
                    ),
                    array(
                        'name' => '刪除會員收貨地址信息',
                        'val' => 'dsc.user.address.del.get'
                    )
                )
            ),
            array(
                'name' => '用戶等級',
                'cat' => 'user_rank',
                'list' => array(
                    array(
                        'name' => '獲取會員等級列表',
                        'val' => 'dsc.user.rank.list.get'
                    ),
                    array(
                        'name' => '獲取單條會員等級信息',
                        'val' => 'dsc.user.rank.info.get'
                    ),
                    array(
                        'name' => '插入會員等級信息',
                        'val' => 'dsc.user.rank.insert.post'
                    ),
                    array(
                        'name' => '更新會員等級信息',
                        'val' => 'dsc.user.rank.update.post'
                    ),
                    array(
                        'name' => '刪除會員等級信息',
                        'val' => 'dsc.user.rank.del.get'
                    )
                )
            ),
            array(
                'name' => '平台類目',
                'cat' => 'category',
                'list' => array(
                    array(
                        'name' => '獲取分類列表',
                        'val' => 'dsc.category.list.get'
                    ),
                    array(
                        'name' => '獲取單條分類信息',
                        'val' => 'dsc.category.info.get'
                    ),
                    array(
                        'name' => '插入分類信息',
                        'val' => 'dsc.category.insert.post'
                    ),
                    array(
                        'name' => '更新分類信息',
                        'val' => 'dsc.category.update.post'
                    ),
                    array(
                        'name' => '刪除分類信息',
                        'val' => 'dsc.category.del.get'
                    )
                )
            ),
            array(
                'name' => '商家類目',
                'cat' => 'category_seller',
                'list' => array(
                    array(
                        'name' => '獲取商家分類列表',
                        'val' => 'dsc.category.seller.list.get'
                    ),
                    array(
                        'name' => '獲取單條商家分類信息',
                        'val' => 'dsc.category.seller.info.get'
                    ),
                    array(
                        'name' => '插入商家分類信息',
                        'val' => 'dsc.category.seller.insert.post'
                    ),
                    array(
                        'name' => '更新商家分類信息',
                        'val' => 'dsc.category.seller.update.post'
                    ),
                    array(
                        'name' => '刪除商家分類信息',
                        'val' => 'dsc.category.seller.del.get'
                    )
                )
            ), array(
                'name' => '商品',
                'cat' => 'goods',
                'list' => array(
                    array(
                        'name' => '獲取商品列表',
                        'val' => 'dsc.goods.list.get'
                    ),
                    array(
                        'name' => '獲取單條商品信息',
                        'val' => 'dsc.goods.info.get'
                    ),
                    array(
                        'name' => '插入商品信息',
                        'val' => 'dsc.goods.insert.post'
                    ),
                    array(
                        'name' => '更新商品信息',
                        'val' => 'dsc.goods.update.post'
                    ),
                    array(
                        'name' => '刪除商品信息',
                        'val' => 'dsc.goods.del.get'
                    ),
                    array(
                        'name' => '獲取商品倉庫列表',
                        'val' => 'dsc.goods.warehouse.list.get'
                    ),
                    array(
                        'name' => '獲取單條商品倉庫信息',
                        'val' => 'dsc.goods.warehouse.info.get'
                    ),
                    array(
                        'name' => '插入商品倉庫信息',
                        'val' => 'dsc.goods.warehouse.insert.post'
                    ),
                    array(
                        'name' => '更新商品倉庫信息',
                        'val' => 'dsc.goods.warehouse.update.post'
                    ),
                    array(
                        'name' => '刪除商品倉庫信息',
                        'val' => 'dsc.goods.warehouse.del.get'
                    ),
                    array(
                        'name' => '獲取商品地區列表',
                        'val' => 'dsc.goods.area.list.get'
                    ),
                    array(
                        'name' => '獲取單條商品地區信息',
                        'val' => 'dsc.goods.area.info.get'
                    ),
                    array(
                        'name' => '插入商品地區信息',
                        'val' => 'dsc.goods.area.insert.post'
                    ),
                    array(
                        'name' => '更新商品地區信息',
                        'val' => 'dsc.goods.area.update.post'
                    ),
                    array(
                        'name' => '刪除商品地區信息',
                        'val' => 'dsc.goods.area.del.get'
                    ),
                    array(
                        'name' => '獲取商品相冊列表',
                        'val' => 'dsc.goods.gallery.list.get'
                    ),
                    array(
                        'name' => '獲取單條商品相冊信息',
                        'val' => 'dsc.goods.gallery.info.get'
                    ),
                    array(
                        'name' => '插入商品相冊信息',
                        'val' => 'dsc.goods.gallery.insert.post'
                    ),
                    array(
                        'name' => '更新商品相冊信息',
                        'val' => 'dsc.goods.gallery.update.post'
                    ),
                    array(
                        'name' => '刪除商品相冊信息',
                        'val' => 'dsc.goods.gallery.del.get'
                    ),
                    array(
                        'name' => '獲取商品屬性列表',
                        'val' => 'dsc.goods.attr.list.get'
                    ),
                    array(
                        'name' => '獲取單條商品屬性信息',
                        'val' => 'dsc.goods.attr.info.get'
                    ),
                    array(
                        'name' => '插入商品屬性信息',
                        'val' => 'dsc.goods.attr.insert.post'
                    ),
                    array(
                        'name' => '更新商品屬性信息',
                        'val' => 'dsc.goods.attr.update.post'
                    ),
                    array(
                        'name' => '刪除商品屬性信息',
                        'val' => 'dsc.goods.attr.del.get'
                    ),
                    array(
                        'name' => '獲取商品運費模板列表',
                        'val' => 'dsc.goods.freight.list.get'
                    ),
                    array(
                        'name' => '獲取單條商品運費模板信息',
                        'val' => 'dsc.goods.freight.info.get'
                    ),
                    array(
                        'name' => '插入商品運費模板信息',
                        'val' => 'dsc.goods.freight.insert.post'
                    ),
                    array(
                        'name' => '更新商品運費模板信息',
                        'val' => 'dsc.goods.freight.update.post'
                    ),
                    array(
                        'name' => '刪除商品運費模板信息',
                        'val' => 'dsc.goods.freight.del.get'
                    ),
                )
            ),
            array(
                'name' => '商品貨品',
                'cat' => 'product',
                'list' => array(
                    array(
                        'name' => '【默認】獲取貨品列表',
                        'val' => 'dsc.product.list.get'
                    ),
                    array(
                        'name' => '【默認】獲取單條貨品信息',
                        'val' => 'dsc.product.info.get'
                    ),
                    array(
                        'name' => '【默認】插入貨品信息',
                        'val' => 'dsc.product.insert.post'
                    ),
                    array(
                        'name' => '【默認】更新貨品信息',
                        'val' => 'dsc.product.update.post'
                    ),
                    array(
                        'name' => '【默認】刪除貨品信息',
                        'val' => 'dsc.product.del.get'
                    ),
                    array(
                        'name' => '【倉庫】獲取貨品列表',
                        'val' => 'dsc.product.warehouse.list.get'
                    ),
                    array(
                        'name' => '【倉庫】獲取單條貨品信息',
                        'val' => 'dsc.product.warehouse.info.get'
                    ),
                    array(
                        'name' => '【倉庫】插入貨品信息',
                        'val' => 'dsc.product.warehouse.insert.post'
                    ),
                    array(
                        'name' => '【倉庫】更新貨品信息',
                        'val' => 'dsc.product.warehouse.update.post'
                    ),
                    array(
                        'name' => '【倉庫】刪除貨品信息',
                        'val' => 'dsc.product.warehouse.del.get'
                    ),
                    array(
                        'name' => '【地區】獲取貨品列表',
                        'val' => 'dsc.product.area.list.get'
                    ),
                    array(
                        'name' => '【地區】獲取單條貨品信息',
                        'val' => 'dsc.product.area.info.get'
                    ),
                    array(
                        'name' => '【地區】插入貨品信息',
                        'val' => 'dsc.product.area.insert.post'
                    ),
                    array(
                        'name' => '【地區】更新貨品信息',
                        'val' => 'dsc.product.area.update.post'
                    ),
                    array(
                        'name' => '【地區】刪除貨品信息',
                        'val' => 'dsc.product.area.del.get'
                    )
                )
            ),
            array(
                'name' => '品牌',
                'cat' => 'brand',
                'list' => array(
                    array(
                        'name' => '獲取品牌列表',
                        'val' => 'dsc.brand.list.get'
                    ),
                    array(
                        'name' => '獲取單條品牌信息',
                        'val' => 'dsc.brand.info.get'
                    ),
                    array(
                        'name' => '插入品牌信息',
                        'val' => 'dsc.brand.insert.post'
                    ),
                    array(
                        'name' => '更新品牌信息',
                        'val' => 'dsc.brand.update.post'
                    ),
                    array(
                        'name' => '刪除品牌信息',
                        'val' => 'dsc.brand.del.get'
                    )
                )
            ),
            array(
                'name' => '交易',
                'cat' => 'order',
                'list' => array(
                    array(
                        'name' => '獲取訂單列表',
                        'val' => 'dsc.order.list.get'
                    ),
                    array(
                        'name' => '獲取單條訂單信息',
                        'val' => 'dsc.order.info.get'
                    ),
                    array(
                        'name' => '插入訂單信息',
                        'val' => 'dsc.order.insert.post'
                    ),
                    array(
                        'name' => '更新訂單信息',
                        'val' => 'dsc.order.update.post'
                    ),
                    array(
                        'name' => '刪除訂單信息',
                        'val' => 'dsc.order.del.get'
                    ),
                    array(
                        'name' => '獲取訂單商品列表',
                        'val' => 'dsc.order.goods.list.get'
                    ),
                    array(
                        'name' => '獲取單條訂單商品信息',
                        'val' => 'dsc.order.goods.info.get'
                    ),
                    array(
                        'name' => '插入訂單商品信息',
                        'val' => 'dsc.order.goods.insert.post'
                    ),
                    array(
                        'name' => '更新訂單商品信息',
                        'val' => 'dsc.order.goods.update.post'
                    ),
                    array(
                        'name' => '刪除訂單商品信息',
                        'val' => 'dsc.order.goods.del.get'
                    )
                )
            ),
            array(
                'name' => '屬性類型',
                'cat' => 'goodstype',
                'list' => array(
                    array(
                        'name' => '獲取屬性類型列表',
                        'val' => 'dsc.goodstype.list.get'
                    ),
                    array(
                        'name' => '獲取單條屬性類型信息',
                        'val' => 'dsc.goodstype.info.get'
                    ),
                    array(
                        'name' => '插入屬性類型信息',
                        'val' => 'dsc.goodstype.insert.post'
                    ),
                    array(
                        'name' => '更新屬性類型信息',
                        'val' => 'dsc.goodstype.update.post'
                    ),
                    array(
                        'name' => '刪除屬性類型信息',
                        'val' => 'dsc.goodstype.del.get'
                    ),
                    array(
                        'name' => '獲取屬性列表',
                        'val' => 'dsc.attribute.list.get'
                    ),
                    array(
                        'name' => '獲取單條屬性信息',
                        'val' => 'dsc.attribute.info.get'
                    ),
                    array(
                        'name' => '插入屬性信息',
                        'val' => 'dsc.attribute.insert.post'
                    ),
                    array(
                        'name' => '更新屬性信息',
                        'val' => 'dsc.attribute.update.post'
                    ),
                    array(
                        'name' => '刪除屬性信息',
                        'val' => 'dsc.attribute.del.get'
                    )
                )
            ),
            array(
                'name' => '地區',
                'cat' => 'region',
                'list' => array(
                    array(
                        'name' => '獲取地區列表',
                        'val' => 'dsc.region.list.get'
                    ),
                    array(
                        'name' => '獲取單條地區信息',
                        'val' => 'dsc.region.info.get'
                    ),
                    array(
                        'name' => '插入地區信息',
                        'val' => 'dsc.region.insert.post'
                    ),
                    array(
                        'name' => '更新地區信息',
                        'val' => 'dsc.region.update.post'
                    ),
                    array(
                        'name' => '刪除地區信息',
                        'val' => 'dsc.region.del.get'
                    )
                )
            ),
            array(
                'name' => '倉庫地區',
                'cat' => 'warehouse',
                'list' => array(
                    array(
                        'name' => '獲取倉庫地區列表',
                        'val' => 'dsc.warehouse.list.get'
                    ),
                    array(
                        'name' => '獲取單條倉庫地區信息',
                        'val' => 'dsc.warehouse.info.get'
                    ),
                    array(
                        'name' => '插入倉庫地區信息',
                        'val' => 'dsc.warehouse.insert.post'
                    ),
                    array(
                        'name' => '更新倉庫地區信息',
                        'val' => 'dsc.warehouse.update.post'
                    ),
                    array(
                        'name' => '刪除倉庫地區信息',
                        'val' => 'dsc.warehouse.del.get'
                    )
                )
            ),
        );

        $api_data['en'] = array(
            array(
                'name' => 'User',
                'cat' => 'user',
                'list' => array(
                    array(
                        'name' => 'Get member list',
                        'val' => 'dsc.user.list.get'
                    ),
                    array(
                        'name' => 'Get single member information',
                        'val' => 'dsc.user.info.get'
                    ),
                    array(
                        'name' => 'Insert member information',
                        'val' => 'dsc.user.insert.post'
                    ),
                    array(
                        'name' => 'Update member information',
                        'val' => 'dsc.user.update.post'
                    ),
                    array(
                        'name' => 'Delete member information',
                        'val' => 'dsc.user.del.get'
                    ),
                    array(
                        'name' => 'Get the list of member receiving address',
                        'val' => 'dsc.user.address.list.get'
                    ),
                    array(
                        'name' => 'Get single member receiving address information',
                        'val' => 'dsc.user.address.info.get'
                    ),
                    array(
                        'name' => 'Insert member shipping address information',
                        'val' => 'dsc.user.address.insert.post'
                    ),
                    array(
                        'name' => 'Update member receiving address information',
                        'val' => 'dsc.user.address.update.post'
                    ),
                    array(
                        'name' => 'Delete member receiving address information',
                        'val' => 'dsc.user.address.del.get'
                    )
                )
            ),
            array(
                'name' => 'User',
                'cat' => 'user_rank',
                'list' => array(
                    array(
                        'name' => 'Get rank member list',
                        'val' => 'dsc.user.rank.list.get'
                    ),
                    array(
                        'name' => 'Get single member level information',
                        'val' => 'dsc.user.rank.info.get'
                    ),
                    array(
                        'name' => 'Insert member level information',
                        'val' => 'dsc.user.rank.insert.post'
                    ),
                    array(
                        'name' => 'Update member level information',
                        'val' => 'dsc.user.rank.update.post'
                    ),
                    array(
                        'name' => 'Delete member level information',
                        'val' => 'dsc.user.rank.del.get'
                    )
                )
            ),
            array(
                'name' => 'Platform category',
                'cat' => 'category',
                'list' => array(
                    array(
                        'name' => 'Get category list',
                        'val' => 'dsc.category.list.get'
                    ),
                    array(
                        'name' => 'Get single category information',
                        'val' => 'dsc.category.info.get'
                    ),
                    array(
                        'name' => 'Insert category information',
                        'val' => 'dsc.category.insert.post'
                    ),
                    array(
                        'name' => 'Update classification information',
                        'val' => 'dsc.category.update.post'
                    ),
                    array(
                        'name' => 'Delete classification information',
                        'val' => 'dsc.category.del.get'
                    )
                )
            ),
            array(
                'name' => 'Business category',
                'cat' => 'category_seller',
                'list' => array(
                    array(
                        'name' => 'Get merchant classification list',
                        'val' => 'dsc.category.seller.list.get'
                    ),
                    array(
                        'name' => 'Access to the classification information of a single merchant',
                        'val' => 'dsc.category.seller.info.get'
                    ),
                    array(
                        'name' => 'Insert merchant classification information',
                        'val' => 'dsc.category.seller.insert.post'
                    ),
                    array(
                        'name' => 'Update merchant classification information',
                        'val' => 'dsc.category.seller.update.post'
                    ),
                    array(
                        'name' => 'Delete merchant classification information',
                        'val' => 'dsc.category.seller.del.get'
                    )
                )
            ), array(
                'name' => 'Product',
                'cat' => 'goods',
                'list' => array(
                    array(
                        'name' => 'Get product list',
                        'val' => 'dsc.goods.list.get'
                    ),
                    array(
                        'name' => 'Get single product information',
                        'val' => 'dsc.goods.info.get'
                    ),
                    array(
                        'name' => 'Insert product information',
                        'val' => 'dsc.goods.insert.post'
                    ),
                    array(
                        'name' => 'Update product information',
                        'val' => 'dsc.goods.update.post'
                    ),
                    array(
                        'name' => 'Delete product information',
                        'val' => 'dsc.goods.del.get'
                    ),
                    array(
                        'name' => 'Get goods warehouse list',
                        'val' => 'dsc.goods.warehouse.list.get'
                    ),
                    array(
                        'name' => 'Get warehouse information of single commodity',
                        'val' => 'dsc.goods.warehouse.info.get'
                    ),
                    array(
                        'name' => 'Insert goods warehouse information',
                        'val' => 'dsc.goods.warehouse.insert.post'
                    ),
                    array(
                        'name' => 'Update commodity warehouse information',
                        'val' => 'dsc.goods.warehouse.update.post'
                    ),
                    array(
                        'name' => 'Delete goods warehouse information',
                        'val' => 'dsc.goods.warehouse.del.get'
                    ),
                    array(
                        'name' => 'Get product region list',
                        'val' => 'dsc.goods.area.list.get'
                    ),
                    array(
                        'name' => 'Get regional information of single commodity',
                        'val' => 'dsc.goods.area.info.get'
                    ),
                    array(
                        'name' => 'Insert product region information',
                        'val' => 'dsc.goods.area.insert.post'
                    ),
                    array(
                        'name' => 'Update product area information',
                        'val' => 'dsc.goods.area.update.post'
                    ),
                    array(
                        'name' => 'Delete product region information',
                        'val' => 'dsc.goods.area.del.get'
                    ),
                    array(
                        'name' => 'Get product album list',
                        'val' => 'dsc.goods.gallery.list.get'
                    ),
                    array(
                        'name' => 'Get the information of single product photo album',
                        'val' => 'dsc.goods.gallery.info.get'
                    ),
                    array(
                        'name' => 'Insert photo album information',
                        'val' => 'dsc.goods.gallery.insert.post'
                    ),
                    array(
                        'name' => 'Update photo album information',
                        'val' => 'dsc.goods.gallery.update.post'
                    ),
                    array(
                        'name' => 'Delete photo album information',
                        'val' => 'dsc.goods.gallery.del.get'
                    ),
                    array(
                        'name' => 'Get product attribute list',
                        'val' => 'dsc.goods.attr.list.get'
                    ),
                    array(
                        'name' => 'Get the attribute information of a single product',
                        'val' => 'dsc.goods.attr.info.get'
                    ),
                    array(
                        'name' => 'Insert product attribute information',
                        'val' => 'dsc.goods.attr.insert.post'
                    ),
                    array(
                        'name' => 'Update product attribute information',
                        'val' => 'dsc.goods.attr.update.post'
                    ),
                    array(
                        'name' => 'Delete item attribute information',
                        'val' => 'dsc.goods.attr.del.get'
                    ),
                    array(
                        'name' => 'Get commodity freight template list',
                        'val' => 'dsc.goods.freight.list.get'
                    ),
                    array(
                        'name' => 'Get the freight template information of single commodity',
                        'val' => 'dsc.goods.freight.info.get'
                    ),
                    array(
                        'name' => 'Insert commodity freight template information',
                        'val' => 'dsc.goods.freight.insert.post'
                    ),
                    array(
                        'name' => 'Update commodity freight template information',
                        'val' => 'dsc.goods.freight.update.post'
                    ),
                    array(
                        'name' => 'Delete commodity freight template information',
                        'val' => 'dsc.goods.freight.del.get'
                    ),
                )
            ),
            array(
                'name' => 'Commodity goods',
                'cat' => 'product',
                'list' => array(
                    array(
                        'name' => '[default] get item list',
                        'val' => 'dsc.product.list.get'
                    ),
                    array(
                        'name' => '[default] get single item information',
                        'val' => 'dsc.product.info.get'
                    ),
                    array(
                        'name' => '[default] insert item information',
                        'val' => 'dsc.product.insert.post'
                    ),
                    array(
                        'name' => '[default] update product information',
                        'val' => 'dsc.product.update.post'
                    ),
                    array(
                        'name' => '[default] delete item information',
                        'val' => 'dsc.product.del.get'
                    ),
                    array(
                        'name' => '[warehouse] get goods list',
                        'val' => 'dsc.product.warehouse.list.get'
                    ),
                    array(
                        'name' => '[warehouse] to obtain single item information',
                        'val' => 'dsc.product.warehouse.info.get'
                    ),
                    array(
                        'name' => '[warehouse] insert item information',
                        'val' => 'dsc.product.warehouse.insert.post'
                    ),
                    array(
                        'name' => '[warehouse] update goods information',
                        'val' => 'dsc.product.warehouse.update.post'
                    ),
                    array(
                        'name' => '[warehouse] delete item information',
                        'val' => 'dsc.product.warehouse.del.get'
                    ),
                    array(
                        'name' => '[region] get list of product',
                        'val' => 'dsc.product.area.list.get'
                    ),
                    array(
                        'name' => '[region] access to single product information',
                        'val' => 'dsc.product.area.info.get'
                    ),
                    array(
                        'name' => '[region] insert product information',
                        'val' => 'dsc.product.area.insert.post'
                    ),
                    array(
                        'name' => '[region] update product information',
                        'val' => 'dsc.product.area.update.post'
                    ),
                    array(
                        'name' => '[region] delete product information',
                        'val' => 'dsc.product.area.del.get'
                    )
                )
            ),
            array(
                'name' => 'Brand',
                'cat' => 'brand',
                'list' => array(
                    array(
                        'name' => 'Get brand list',
                        'val' => 'dsc.brand.list.get'
                    ),
                    array(
                        'name' => 'Get single brand information',
                        'val' => 'dsc.brand.info.get'
                    ),
                    array(
                        'name' => 'Insert brand information',
                        'val' => 'dsc.brand.insert.post'
                    ),
                    array(
                        'name' => 'Update brand information',
                        'val' => 'dsc.brand.update.post'
                    ),
                    array(
                        'name' => 'Delete brand information',
                        'val' => 'dsc.brand.del.get'
                    )
                )
            ),
            array(
                'name' => 'Transaction',
                'cat' => 'order',
                'list' => array(
                    array(
                        'name' => 'Get order list',
                        'val' => 'dsc.order.list.get'
                    ),
                    array(
                        'name' => 'Get single order information',
                        'val' => 'dsc.order.info.get'
                    ),
                    array(
                        'name' => 'Insert order information',
                        'val' => 'dsc.order.insert.post'
                    ),
                    array(
                        'name' => 'Update order information',
                        'val' => 'dsc.order.update.post'
                    ),
                    array(
                        'name' => 'Delete order information',
                        'val' => 'dsc.order.del.get'
                    ),
                    array(
                        'name' => 'Get order item list',
                        'val' => 'dsc.order.goods.list.get'
                    ),
                    array(
                        'name' => 'Get single order item information',
                        'val' => 'dsc.order.goods.info.get'
                    ),
                    array(
                        'name' => 'Insert order line information',
                        'val' => 'dsc.order.goods.insert.post'
                    ),
                    array(
                        'name' => 'Update order item information',
                        'val' => 'dsc.order.goods.update.post'
                    ),
                    array(
                        'name' => 'Delete order item information',
                        'val' => 'dsc.order.goods.del.get'
                    )
                )
            ),
            array(
                'name' => 'Property type',
                'cat' => 'goodstype',
                'list' => array(
                    array(
                        'name' => 'Gets a list of property types',
                        'val' => 'dsc.goodstype.list.get'
                    ),
                    array(
                        'name' => 'Get single attribute type information',
                        'val' => 'dsc.goodstype.info.get'
                    ),
                    array(
                        'name' => 'Insert property type information',
                        'val' => 'dsc.goodstype.insert.post'
                    ),
                    array(
                        'name' => 'Update property type information',
                        'val' => 'dsc.goodstype.update.post'
                    ),
                    array(
                        'name' => 'Delete attribute type information',
                        'val' => 'dsc.goodstype.del.get'
                    ),
                    array(
                        'name' => 'Get property list',
                        'val' => 'dsc.attribute.list.get'
                    ),
                    array(
                        'name' => 'Get single attribute information',
                        'val' => 'dsc.attribute.info.get'
                    ),
                    array(
                        'name' => 'Insert attribute information',
                        'val' => 'dsc.attribute.insert.post'
                    ),
                    array(
                        'name' => 'Update attribute information',
                        'val' => 'dsc.attribute.update.post'
                    ),
                    array(
                        'name' => 'Delete attribute information',
                        'val' => 'dsc.attribute.del.get'
                    )
                )
            ),
            array(
                'name' => 'Region',
                'cat' => 'region',
                'list' => array(
                    array(
                        'name' => 'Get region list',
                        'val' => 'dsc.region.list.get'
                    ),
                    array(
                        'name' => 'Get single area information',
                        'val' => 'dsc.region.info.get'
                    ),
                    array(
                        'name' => 'Insert region information',
                        'val' => 'dsc.region.insert.post'
                    ),
                    array(
                        'name' => 'Update regional information',
                        'val' => 'dsc.region.update.post'
                    ),
                    array(
                        'name' => 'Delete region information',
                        'val' => 'dsc.region.del.get'
                    )
                )
            ),
            array(
                'name' => 'Warehouse area',
                'cat' => 'warehouse',
                'list' => array(
                    array(
                        'name' => 'Get warehouse area list',
                        'val' => 'dsc.warehouse.list.get'
                    ),
                    array(
                        'name' => 'Get single warehouse area information',
                        'val' => 'dsc.warehouse.info.get'
                    ),
                    array(
                        'name' => 'Get single warehouse area information',
                        'val' => 'dsc.warehouse.insert.post'
                    ),
                    array(
                        'name' => 'Update warehouse area information',
                        'val' => 'dsc.warehouse.update.post'
                    ),
                    array(
                        'name' => 'Delete warehouse area information',
                        'val' => 'dsc.warehouse.del.get'
                    )
                )
            ),
        );

        return $api_data;
    }
}
