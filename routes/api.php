<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

$apiRoute = function () {
    // home
    Route::get('home', 'IndexController@index')->name('home.index');
    //
    Route::get('app/home', 'IndexController@home')->name('app.home');
    // 网店配置
    Route::get('shop/config', 'IndexController@shopConfig')->name('shop.config');
    // 发现页面配置
    Route::get('shop/page-nav', 'IndexController@pageNav')->name('shop.page_nav');
    // 语言包
    Route::get('shop/lang', 'IndexController@shopLang')->name('shop.lang');
    // 客服
    Route::get('chat', 'ChatController@index')->name('chat');

    // account 会员账户
    Route::prefix('account')->group(function () {
        // 账户概要
        Route::get('/', 'AccountController@index')->name('account.index');
        // 申请记录
        Route::get('replylog', 'AccountController@replylog')->name('account.replylog');
        // 账户明细
        Route::get('accountlog', 'AccountController@accountlog')->name('account.accountlog');
        // 资金提现
        Route::get('reply', 'AccountController@reply')->name('account.reply');
        // 账户充值
        Route::get('deposit', 'AccountController@deposit')->name('account.deposit');
        // 充值提现操作
        Route::post('account', 'AccountController@account')->name('account.account')->middleware('limit_form_repeat');
        // 个人积分明细
        Route::get('paypoints', 'AccountController@paypoints')->name('account.paypoints');
    });

    //realname 实名认证
    Route::prefix('realname')->group(function () {
        // 实名认证详情
        Route::get('/', 'CertificationController@index')->name('realname.index');
        // 新增实名认证
        Route::post('store', 'CertificationController@store')->name('realname.store');
        // 更新实名认证
        Route::put('{realname}', 'CertificationController@update')->name('realname.update');
    });

    // accountsafe 账户安全
    Route::prefix('accountsafe')->group(function () {
        // 账户安全首页
        Route::get('/', 'AccountSafeController@index')->name('accountsafe.index');
        // 启用支付密码
        Route::get('add_paypwd', 'AccountSafeController@addPaypwd')->name('accountsafe.addpaypwd');
        // 修改支付密码
        Route::post('edit_paypwd', 'AccountSafeController@editPaypwd')->name('accountsafe.editpaypwd');
        // 绑定手机号码
        Route::post('bind_mobile', 'AccountSafeController@bind_mobile')->name('accountsafe.bind_mobile');
        // 更换手机号码
        Route::post('change_mobile', 'AccountSafeController@change_mobile')->name('accountsafe.change_mobile');
    });

    // bank 用户银行账号
    Route::prefix('bank-card')->group(function () {
        // 银行账号详情
        Route::get('/', 'BankCardController@index')->name('bankcard.index');
        // 添加银行账号
        Route::post('store', 'BankCardController@store')->name('bankcard.store');
        // 更新银行账号
        Route::put('{bank}', 'BankCardController@update')->name('bankcard.update');
    });

    // activity 优惠活动
    Route::prefix('activity')->group(function () {
        // 优惠活动列表
        Route::get('/', 'ActivityController@index')->name('activity.index');
        // 优惠活动详情
        Route::get('show', 'ActivityController@show')->name('activity.show');
        // 优惠活动商品
        Route::post('goods', 'ActivityController@goods')->name('activity.goods');
        // 优惠活动凑单
        Route::get('coudan', 'ActivityController@coudan')->name('activity.coudan');
    });

    // address 收货地址
    Route::prefix('address')->group(function () {
        // 收货地址列表
        Route::get('/', 'AddressController@index')->name('address.index');
        // 同步微信收货地址
        Route::post('wximport', 'AddressController@wximport')->name('address.wximport');
        // 添加收货地址
        Route::post('store', 'AddressController@store')->name('address.store');
        // 收货地址详情
        Route::post('show', 'AddressController@show')->name('address.show');
        // 更新收货地址
        Route::put('update', 'AddressController@update')->name('address.update');
        // 删除收货地址
        Route::get('destroy', 'AddressController@destroy')->name('address.destroy');
        // 设置默认收货地址
        Route::post('default', 'AddressController@setDefault')->name('address.default');
        // 匹配收货地址
        Route::post('match', 'AddressController@match')->name('address.match');
    });

    // article 文章
    Route::prefix('article')->group(function () {
        // 分类列表
        Route::post('/', 'ArticleController@index')->name('article.index');
        // 文章列表
        Route::post('list', 'ArticleController@list')->name('article.list');
        // 详情
        Route::post('show', 'ArticleController@show')->name('article.show');
        // 分类详情
        Route::post('category', 'ArticleController@category')->name('article.category');
        // 提交评论
        Route::post('comment', 'ArticleController@comment')->name('article.comment');
        // 评论列表
        Route::get('commentlist', 'ArticleController@commentlist')->name('article.commentlist');
        // 点赞
        Route::get('like', 'ArticleController@like')->name('article.like');
        // 文章搜索
        Route::get('search', 'ArticleController@search')->name('article.search');
    });

    // auction 拍卖活动
    Route::prefix('auction')->group(function () {
        // 拍卖列表
        Route::get('/', 'AuctionController@index')->name('auction.index');
        // 拍卖详情
        Route::get('detail', 'AuctionController@detail')->name('auction.detail');
        // 拍卖记录
        Route::get('log', 'AuctionController@log')->name('auction.log');
        // 拍卖出价
        Route::get('bid', 'AuctionController@bid')->name('auction.bid');
        // 拍卖购买
        Route::get('buy', 'AuctionController@buy')->name('auction.buy');
        // 参与拍卖列表
        Route::get('auction_list', 'AuctionController@auctionList')->name('auction.auctionList');


    });

    // bargain 砍价活动
    Route::prefix('bargain')->group(function () {
        // 砍价首页
        Route::get('/', 'BargainController@index')->name('bargain.index');
        // 砍价商品列表
        Route::get('goods', 'BargainController@goods')->name('bargain.goods');
        // 砍价详情
        Route::get('detail', 'BargainController@detail')->name('bargain.detail');
        // 砍价商品属性
        Route::post('property', 'BargainController@property')->name('bargain.property');
        // 发起砍价
        Route::get('log', 'BargainController@log')->name('bargain.log');
        // 参与砍价
        Route::get('bid', 'BargainController@bid')->name('bargain.bid');
        // 砍价购买
        Route::get('buy', 'BargainController@buy')->name('bargain.buy');
        // 我参与的砍价
        Route::get('my_buy', 'BargainController@myBuy')->name('bargain.mybuy');

    });

    // bonus 红包活动
    Route::prefix('bonus')->group(function () {
        // 红包首页
        Route::get('/', 'BonusController@index')->name('bonus.index');
        // 领取红包
        Route::post('receive', 'BonusController@receive')->name('bonus.receive');
        // 兑换红包
        Route::post('store', 'BonusController@store')->name('bonus.store');
        // 会员中心红包
        Route::get('bonus', 'BonusController@bonus')->name('bonus.bonus');
        // 提交订单页红包列表
        Route::get('flowbonus', 'BonusController@flowbonus')->name('bonus.flowbonus');
        // 二维码红包
        Route::get('qrcode_bonus', 'BonusController@qrcode_bonus')->name('bonus.qrcode_bonus');
    });

    // brand 品牌
    Route::prefix('brand')->group(function () {
        // 品牌首页
        Route::post('/', 'BrandController@index')->name('brand.index');
        // 品牌详情
        Route::post('detail', 'BrandController@detail')->name('brand.detail');
        // 品牌商品列表
        Route::post('goodslist', 'BrandController@goodslist')->name('brand.goods');
        // 品牌列表
        Route::post('brandlist', 'BrandController@brandlist')->name('brand.list');
    });

    // cart 购物车
    Route::prefix('cart')->group(function () {
        // 加入购物车
        Route::post('add', 'CartController@add')->name('cart.add');
        // 加入购物车（超值礼包）
        Route::post('addpackage', 'CartController@addPackage')->name('cart.addpackage');
        // 优惠活动（赠品）列表
        Route::post('giftlist', 'CartController@giftList')->name('cart.giftlist');
        // 添加优惠活动（赠品）到购物车
        Route::post('addGiftCart', 'CartController@addGiftCart')->name('cart.addGiftCart');
        // 购物车商品列表
        Route::post('goodslist', 'CartController@goodsList')->name('cart.goodslist');
        // 更新购物车
        Route::put('{goods}', 'CartController@update')->name('cart.update');
        // 购物车总价
        Route::post('amount', 'CartController@amount')->name('cart.amount');
        // 删除购物车商品
        Route::post('deletecart', 'CartController@deleteCart')->name('cart.deletecart');
        // 清空购物车
        Route::post('clearcart', 'CartController@clearCart')->name('cart.clearcart');
        // 购物车选中商品
        Route::post('cartvalue', 'CartController@cartValue')->name('cart.cartvalue');
        // 选择购物车商品
        Route::post('checked', 'CartController@checked')->name('cart.checked');
        // 更新购物车
        Route::post('update', 'CartController@update')->name('cart.update');
        // 购物车选择促销活动
        Route::post('getfavourable', 'CartController@getFavourable')->name('cart.getfavourable');
        // 购物车切换可选促销
        Route::post('changefav', 'CartController@changefav')->name('cart.changefav');
        // 购物车领取优惠券列表
        Route::post('getCoupons', 'CartController@getCoupons')->name('cart.getCoupons');
        // 加入购物车（临时套餐组合商品）
        Route::post('addToCartCombo', 'CartController@addToCartCombo')->name('cart.addToCartCombo');
        // 删除购物车（临时套餐组合商品）
        Route::post('delInCartCombo', 'CartController@delInCartCombo')->name('cart.delInCartCombo');
        // 加入购物车（最终套餐组合商品）
        Route::post('addToCartGroup', 'CartController@addToCartGroup')->name('cart.addToCartGroup');
        // 购物车商品数量
        Route::get('cartNum', 'CartController@cartNum')->name('cart.cartNum');

        // 更新到店购物车
        Route::post('offline/update', 'CartOfflineController@update')->name('cart.offline.update');

    });

    // cashier desk 收银台
    Route::prefix('cashier_desk')->group(function () {

    });

    // catalog 分类
    Route::prefix('catalog')->group(function () {
        // 分类首页
        Route::get('list/{catalog?}', 'CatalogController@index')->name('catalog.index');
        // 分类详情
        Route::get('{catalog}/detail', 'CatalogController@show')->name('catalog.show');
        // 价格区间
        Route::get('grade', 'CatalogController@grade')->name('catalog.grade');
        // 属性
        Route::get('attribute', 'CatalogController@attribute')->name('catalog.attribute');
        // 分类商品
        Route::post('goodslist', 'CatalogController@goodslist')->name('catalog.goodslist');
        // 分类品牌
        Route::post('brandlist', 'CatalogController@brandlist')->name('catalog.brandlist');
        // 店铺分类
        Route::post('shopcat', 'CatalogController@shopcat')->name('catalog.shopcat');
    });

    // collect 收藏
    Route::prefix('collect')->group(function () {
        // 收藏店铺列表
        Route::get('shop', 'CollectController@shop')->name('collect.shop');
        // 收藏店铺
        Route::post('collectshop', 'CollectController@collectshop')->name('collect.collectshop');
        // 收藏商品列表
        Route::get('goods', 'CollectController@goods')->name('collect.goods');
        // 收藏商品
        Route::post('collectgoods', 'CollectController@collectgoods')->name('collect.collectgoods');
        // 收藏商品数量
        Route::get('collectnum', 'CollectController@collectnum')->name('collect.collectnum');
    });

    // comment 评论
    Route::prefix('comment')->group(function () {
        // 商品评论数量
        Route::post('title', 'CommentController@title')->name('comment.title');
        // 商品评论列表
        Route::post('goods', 'CommentController@goods')->name('comment.goods');
        // 文章评论
        //Route::get('article', 'CommentController@article')->name('comment.article');
        // 评论文章
        //Route::post('article/create', 'CommentController@articleComment')->name('comment.articleComment');
        // 订单商品评论数量
        Route::post('order_goods_title', 'CommentController@order_goods_title')->name('comment.order_goods_title');
        // 订单商品待评论列表
        Route::post('commentlist', 'CommentController@commentlist')->name('comment.commentlist');
        // 订单商品评论页
        Route::post('addcomment', 'CommentController@addcomment')->name('comment.addcomment');
        // 订单商品评论提交
        Route::post('addgoodscomment', 'CommentController@addgoodscomment')->name('comment.addgoodscomment');
    });

    // coupon 优惠券
    Route::prefix('coupon')->group(function () {
        // 前台领取列表 - 好券集市
        Route::get('/', 'CouponController@index')->name('coupon.index');
        // 前台领取列表 - 任务集市
        Route::get('couponsgoods', 'CouponController@couponsGoods')->name('coupon.couponsGoods');
        // 领取优惠券
        Route::post('receive', 'CouponController@receive')->name('coupon.receive');
        // 商品详情优惠券列表
        Route::get('goods', 'CouponController@goods')->name('coupon.goods');
        // 会员中心优惠券列表
        Route::get('coupon', 'CouponController@coupon')->name('coupon.coupon');
    });

    // crowd funding 众筹活动
    Route::prefix('crowd_funding')->group(function () {
        // 众筹首页
        Route::get('/', 'CrowdFundingController@index')->name('crowdfunding.index');
        // 众筹列表
        Route::get('goods', 'CrowdFundingController@goods')->name('crowdfunding.goods');
        // 众筹详情
        Route::get('show', 'CrowdFundingController@show')->name('crowdfunding.show');
        // 众筹关注
        Route::get('focus', 'CrowdFundingController@focus')->name('crowdfunding.focus');
        // 发布话题
        Route::post('topic', 'CrowdFundingController@topic')->name('crowdfunding.topic');
        // 选择方案
        Route::get('property', 'CrowdFundingController@property')->name('crowdfunding.property');
        // 众筹描述
        Route::get('properties', 'CrowdFundingController@properties')->name('crowdfunding.properties');
        // 众筹话题
        Route::get('topic_list', 'CrowdFundingController@topicList')->name('crowdfunding.topicList');
        // 众筹订单确认
        Route::get('checkout', 'CrowdFundingController@checkout')->name('crowdfunding.checkout');
        // 众筹提交订单
        Route::get('done', 'CrowdFundingController@done')->name('crowdfunding.done');
        // 众筹会员中心
        Route::get('user', 'CrowdFundingController@user')->name('crowdfunding.user');
        // 众筹中心项目推荐
        Route::get('crowd_best', 'CrowdFundingController@crowdBest')->name('crowdfunding.crowdBest');
        // 众筹中心我的关注
        Route::get('my_focus', 'CrowdFundingController@myFocus')->name('crowdfunding.myFocus');
        // 众筹中心我的支持
        Route::get('crowd_buy', 'CrowdFundingController@crowdBuy')->name('crowdfunding.crowdBuy');
        // 众筹中心我的订单
        Route::get('order', 'CrowdFundingController@order')->name('crowdfunding.order');
        // 众筹中心订单详情
        Route::get('detail', 'CrowdFundingController@detail')->name('crowdfunding.detail');
    });

    // discover 讨论圈
    Route::prefix('discover')->group(function () {
        // 网友讨论圈首页
        Route::post('/', 'DiscoverController@index')->name('discover.index');
        // 网友讨论圈列表
        Route::post('list', 'DiscoverController@list')->name('discover.list');
        // 我的帖子列表
        Route::post('mylist', 'DiscoverController@mylist')->name('discover.mylist');
        // 帖子详情
        Route::post('detail', 'DiscoverController@detail')->name('discover.detail');
        // 帖子评论列表
        Route::post('commentlist', 'DiscoverController@commentlist')->name('discover.commentlist');
        // 提交评论
        Route::post('comment', 'DiscoverController@comment')->name('discover.comment');
        // 我的帖子
        Route::post('my', 'DiscoverController@my')->name('discover.my');
        // 回复我的
        Route::post('reply', 'DiscoverController@reply')->name('discover.reply');
        // 发帖信息
        Route::post('show', 'DiscoverController@show')->name('discover.show');
        // 发帖
        Route::post('create', 'DiscoverController@create')->name('discover.create')->middleware('limit_form_repeat');
        // 帖子点赞
        Route::post('like', 'DiscoverController@like')->name('discover.like');
        // 帖子删除
        Route::delete('delete', 'DiscoverController@delete')->name('discover.delete');
        // 讨论圈发现列表
        Route::get('find_list', 'DiscoverController@findList')->name('discover.find_list');
        // 讨论圈发现详情
        Route::get('find_detail', 'DiscoverController@findDetail')->name('discover.find_detail');
        // 讨论圈发现评论列表
        Route::get('find_reply_comment', 'DiscoverController@findReplyComment')->name('discover.find_reply_comment');
    });

    // exchange 积分商城
    Route::prefix('exchange')->group(function () {
        // 积分商城首页
        Route::get('/', 'ExchangeController@index')->name('exchange.index');
        // 积分商城详情
        Route::get('detail', 'ExchangeController@detail')->name('exchange.detail');
        // 积分商品购买
        Route::get('buy', 'ExchangeController@buy')->name('exchange.buy');
    });

    // feedback 留言
    Route::prefix('feedback')->group(function () {
        // 留言列表
        Route::get('/', 'FeedbackController@index')->name('feedback.index');
        // 提交留言
        Route::post('create', 'FeedbackController@store')->name('feedback.create')->middleware('limit_form_repeat');
    });

    // goods 商品
    Route::prefix('goods')->group(function () {
        // 列表
        Route::get('/', 'GoodsController@index')->name('goods.index');
        // 商品详情
        Route::post('show', 'GoodsController@show')->name('goods.show');
        // 促销商品
        Route::post('promotegoods', 'GoodsController@promotegoods')->name('goods.promoteGoods');
        // 切换属性价格
        Route::post('attrprice', 'GoodsController@attrprice')->name('goods.attrprice');
        // 猜你喜欢
        Route::post('goodsguess', 'GoodsController@goodsguess')->name('goods.goodsguess');
        // 关联商品
        Route::post('linkgoods', 'GoodsController@linkgoods')->name('goods.linkgoods');
        // 分享海报
        Route::post('shareposter', 'GoodsController@shareposter')->name('goods.shareposter');
        // 组合套餐配件
        Route::post('fittings', 'GoodsController@fittings')->name('goods.fittings');
        // 组合套餐配件属性价格
        Route::post('fittingprice', 'GoodsController@fittingprice')->name('goods.fittingprice');
        // 商品视频列表
        Route::post('goodsvideo', 'GoodsController@goodsVideo')->name('goods.goodsvideo');
        // 商品视频详情
        Route::post('videoinfo', 'GoodsController@videoinfo')->name('goods.videoinfo');
        // 更新商品视频点击量
        Route::get('videolooknum', 'GoodsController@videolooknum')->name('goods.videolooknum');
        // 商品列表
        Route::get('type_list', 'GoodsController@typeList')->name('goods.type_list');
    });

    // group buy 团购活动
    Route::prefix('group_buy')->group(function () {
        // 团购商品列表
        Route::get('/', 'GroupBuyController@index')->name('groupbuy.index');
        // 团购商品详情
        Route::get('detail', 'GroupBuyController@detail')->name('groupbuy.detail');
        // 价格
        Route::get('price', 'GroupBuyController@price')->name('groupbuy.price');
        // 团购商品购买
        Route::get('buy', 'GroupBuyController@buy')->name('groupbuy.buy');
    });

    // history 最近浏览历史
    Route::prefix('history')->group(function () {
        // 浏览历史列表
        Route::get('/', 'HistoryController@index')->name('history.index');
        // 浏览历史列表
        Route::get('index', 'HistoryController@index')->name('history.index');
        // 添加浏览历史
        Route::post('store', 'HistoryController@store')->name('history.store');
        // 清空浏览历史
        Route::delete('destroy', 'HistoryController@destroy')->name('history.destroy');
    });

    // invite 我的分享
    Route::prefix('invite')->group(function () {
        Route::get('/', 'InviteController@index')->name('invite.index');
    });

    // invoice 我的发票
    Route::prefix('invoice')->group(function () {
        //个人发票详情
        Route::get('/', 'InvoiceController@index')->name('invoice.index');
        // 添加个人发票
        Route::post('store', 'InvoiceController@store')->name('invoice.store')->middleware('limit_form_repeat');
        // 更新个人发票
        Route::put('update', 'InvoiceController@update')->name('invoice.update');
        // 删除个人发票
        Route::delete('destroy', 'InvoiceController@destroy')->name('invoice.destroy');
    });

    // misc 杂项
    Route::prefix('misc')->group(function () {
        // 验证码 captcha
        Route::get('captcha', 'CaptchaController@index')->name('misc.captcha');
        // 地区列表 region
        Route::get('region', 'RegionController@index')->name('misc.region');
        // 发送短信 sms
        Route::post('sms/send', 'SmsController@index')->name('misc.sms.send');
        // 短信验证码校验
        Route::post('sms/verify', 'SmsController@verify')->name('misc.sms.verify');
        // 地区定位 position
        Route::get('position', 'PositionController@index')->name('misc.position');
        // ip
        Route::get('ip', 'PositionController@ip')->name('misc.ip');
        // 根据地质获取坐标
        Route::get('address2location', 'PositionController@address2location')->name('misc.address2location');
    });

    // OAuth 授权登录
    Route::prefix('oauth')->group(function () {
        // 授权登录获取code
        Route::get('code', 'OAuthController@code')->name('oauth.code');
        // 授权登录回调
        Route::match(['get', 'post'], 'callback', 'OAuthController@callback')->name('oauth.callback');
        // 授权登录、注册
        Route::post('bind_register', 'OAuthController@bind_register')->name('oauth.bind_register')->middleware('limit_form_repeat');
        // 用户授权列表
        Route::get('bindList', 'OAuthController@bindList')->name('oauth.bindList');
        // 用户授权解绑
        Route::post('unbind', 'OAuthController@unbind')->name('oauth.unbind');
        // 重新绑定授权
        Route::post('rebind', 'OAuthController@rebind')->name('oauth.rebind');
    });

    // 门店
    Route::prefix('offline-store')->group(function () {
        // 线下门店列表
        Route::post('list', 'OfflineStoreController@index')->name('offline-store.list');
    });

    // order 订单
    Route::prefix('order')->group(function () {
        // 订单列表
        Route::post('list', 'OrderController@list')->name('order.list');
        // 订单详情
        Route::post('detail', 'OrderController@detail')->name('order.detail');
        // 订单确认
        Route::post('confirm', 'OrderController@confirm')->name('order.confirm');
        // 订单取消
        Route::post('cancel', 'OrderController@cancel')->name('order.cancel');
        // 延迟收货申请
        Route::post('delay', 'OrderController@delay')->name('order.delay');
        // 订单删除
        Route::post('delete', 'OrderController@delete')->name('order.delete');
        // 订单还原
        Route::post('restore', 'OrderController@restore')->name('order.restore');
        // 上传汇款凭证
        Route::post('upload_pay_document', 'OrderController@upload_pay_document')->name('order.upload_pay_document');
        // 订单跟踪 eg: http://dscmall.test/api/order/tracker?type=shentong&postid=3372277341133
        Route::any('tracker', 'OrderController@tracker')->name('order.tracker');
        // 发货单信息
        Route::get('tracker_order', 'OrderController@tracker_order')->name('order.tracker_order');
        // 地图跟踪
        Route::get('map_track', 'OrderController@mapTrack')->name('order.map_track');
    });

    // refound 退换货
    Route::prefix('refound')->group(function () {
        // 退换货列表
        Route::get('/', 'RefoundController@index')->name('refound.index');
        // 退换货商品列表
        Route::get('returngoods', 'RefoundController@returngoods')->name('refound.returngoods');
        // 详情
        // Route::get('info', 'RefoundController@info')->name('refound.info');
        // 申请退换货
        Route::get('applyreturn', 'RefoundController@applyreturn')->name('refound.applyreturn');
        // 退换货详情
        Route::get('returndetail', 'RefoundController@returndetail')->name('refound.returndetail');
        // 提交退换货申请
        Route::post('submit_return', 'RefoundController@submit_return')->name('refound.submit_return')->middleware('limit_form_repeat');
        // 取消退换货
        Route::post('cancel', 'RefoundController@cancel')->name('refound.cancel');
        // 编辑退换货快递信息
        Route::post('edit_express', 'RefoundController@edit_express')->name('refound.edit_express');
        // 确认收货退换货订单
        Route::post('affirm_receive', 'RefoundController@affirm_receive')->name('refound.affirm_receive');
        // 激活退换货订单
        Route::post('active_return_order', 'RefoundController@active_return_order')->name('refound.active_return_order');
        // 删除退换货订单
        // Route::post('delete_return_order', 'RefoundController@delete_return_order')->name('refound.delete_return_order');
    });

    // package 超值礼包
    Route::prefix('package')->group(function () {
        // 超值礼包列表
        Route::get('list', 'PackageController@index')->name('package.list');
    });

    // payment 支付
    Route::prefix('payment')->group(function () {
        // 支付方式列表
        Route::get('list', 'PaymentController@index')->name('payment.list');
        // 支付按钮
        Route::get('code', 'PaymentController@code')->name('payment.code');
        // 支付通知（同步）
        Route::get('callback', 'PaymentController@callback')->name('payment.callback');
        // 支付通知（异步）
        Route::post('notify/{code?}/{type?}', 'PaymentController@notify')->name('payment.notify');
        // 合单支付通知（异步）
        Route::post('notify_combine/{code?}/{type?}', 'PaymentController@notify_combine')->name('payment.notify_combine');
        // 退款通知（异步）
        Route::post('notify_refound/{code?}', 'PaymentController@notify_refound')->name('payment.notify_refound');
        // 合单支付退款通知（异步）
        Route::post('notify_combine_refound/{code?}', 'PaymentController@notify_combine_refound')->name('payment.notify_combine_refound');
        // PC+h5收银台切换支付
        Route::get('change_payment', 'PaymentController@change_payment')->name('payment.change_payment');
        // App收银台切换支付
        Route::get('change_app_payment', 'PaymentController@changeAppPayment')->name('payment.change_app_payment');
        // wxapp收银台切换支付
        Route::get('wxapp_change_app_payment', 'PaymentController@wxappChangeAppPayment')->name('payment.wxappChangeAppPayment');
    });

    // presale 预售活动
    Route::prefix('presale')->group(function () {
        // 预售首页
        Route::get('/', 'PresaleController@index')->name('presale.index');
        // 预售列表页
        Route::get('list', 'PresaleController@list')->name('presale.list');
        // 预售详情
        Route::get('detail', 'PresaleController@detail')->name('presale.detail');
        // 预售商品价格
        Route::get('price', 'PresaleController@price')->name('presale.price');
        // 预售商品购买
        Route::get('buy', 'PresaleController@buy')->name('presale.buy');
        // 新品发布
        Route::get('new', 'PresaleController@new')->name('presale.new');
    });

    // seckill 秒杀活动
    Route::prefix('seckill')->group(function () {
        // 秒杀商品列表
        Route::get('/', 'SeckillController@index')->name('seckill.index');
        // 秒杀时间列表
        Route::get('time', 'SeckillController@time')->name('seckill.time');
        // 秒杀详情
        Route::get('detail', 'SeckillController@detail')->name('seckill.detail');
        // 切换属性价格
        Route::post('attr_price', 'SeckillController@attr_price')->name('seckill.attr_price');
        // 秒杀购买
        Route::get('buy', 'SeckillController@buy')->name('seckill.buy');
    });

    // shipping 配送
    Route::prefix('shipping')->group(function () {
        // 配送列表
        Route::get('/', 'ShippingController@index')->name('shipping.index');
        // 配送运费
        Route::get('amount', 'ShippingController@amount')->name('shipping.amount');
        // 商品运费
        Route::get('goodsshippingfee', 'ShippingController@goodsshippingfee')->name('shipping.goodsshippingfee');
    });

    // shop 店铺
    Route::prefix('shop')->group(function () {
        // 店铺街分类列表
        Route::post('catlist', 'ShopController@catList')->name('shop.catlist');
        // 分类店铺列表
        Route::post('catshoplist', 'ShopController@catShopList')->name('shop.catshoplist');
        // 店铺商品列表
        Route::post('shopgoodslist', 'ShopController@shopGoodsList')->name('shop.shopgoodslist');
        // 店铺详情
        Route::post('shopdetail', 'ShopController@shopDetail')->name('shop.shopdetail');
        // 店铺品牌列表
        Route::post('shopbrand', 'ShopController@shopBrand')->name('shop.shopbrand');
        // 店铺附近地图
        Route::get('map', 'ShopController@map')->name('shop.map');
        /** 关注店铺优惠券 */
        Route::post('coupons', 'ShopController@coupons')->name('shop.coupons');

        //店铺关注二维码
        Route::post('follow', 'ShopController@follow')->name('shop.follow');
    });

    // team 拼团
    Route::prefix('team')->group(function () {
        // 拼团首页
        Route::get('/', 'TeamController@index')->name('team.index');
        // 首页，频道下拼团商品
        Route::get('goods', 'TeamController@goods')->name('team.goods');
        // 频道商品列表
        Route::get('categories', 'TeamController@categories')->name('team.categories');
        // 下单提示轮播
        Route::get('virtual_order', 'TeamController@virtualOrder')->name('team.virtualOrder');
        // 拼团子频道商品列表
        Route::get('goods_list', 'TeamController@goodsList')->name('team.goodsList');
        // 拼团排行
        Route::get('team_ranking', 'TeamController@teamRanking')->name('team.teamRanking');
        // 拼团商品详情
        Route::get('detail', 'TeamController@detail')->name('team.detail');
        // 拼团商品属性价格
        Route::get('property', 'TeamController@property')->name('team.property');
        // 加入购物车
        Route::get('team_buy', 'TeamController@teamBuy')->name('team.teamBuy');
        // 等待成团
        Route::get('team_wait', 'TeamController@teamWait')->name('team.teamWait');
        // 拼团成员
        Route::get('team_user', 'TeamController@teamUser')->name('team.teamUser');
        // 我的拼团
        Route::get('team_order', 'TeamController@teamOrder')->name('team.teamOrder');

    });

    // APP 广告
    Route::prefix('app')->group(function () {
        // APP 启动页广告
        Route::get('ad_position', 'AppController@ad_position')->name('app.ad_position');
        Route::get('auto_update', 'AppController@auto_update')->name('app.auto_update');
    });

    // app 授权登录
    Route::prefix('appqrcode')->group(function () {
        // App扫码登录回调信息
        Route::post('appuser', 'AppController@appuser')->name('appqrcode.appuser');
        // App扫码登录确认扫码
        Route::post('scancode', 'AppController@scancode')->name('appqrcode.scancode');
        // App扫码登录取消授权登录
        Route::post('cancel', 'AppController@cancel')->name('appqrcode.cancel');
    });


    // value_card 储值卡
    Route::prefix('valuecard')->group(function () {
        // 储值卡列表
        Route::get('/', 'ValueCardController@index')->name('valuecard.index');
        // 储值使用详情
        Route::get('detail', 'ValueCardController@detail')->name('valuecard.detail');
        // 储值卡充值
        Route::post('deposit', 'ValueCardController@deposit')->name('valuecard.deposit');
        // 绑定储值卡
        Route::post('addvaluecard', 'ValueCardController@addvaluecard')->name('valuecard.addvaluecard');
    });


    // topic 专题
    Route::prefix('topic')->group(function () {
        // 专题列表
        Route::get('/', 'TopicController@index')->name('topic.index');
        // 专题详情
        Route::get('detail', 'TopicController@detail')->name('topic.detail');
    });

    // trade 交易
    Route::prefix('trade')->group(function () {
        // 订单结算
        Route::post('orderinfo', 'TradeController@orderinfo')->name('trade.orderinfo');
        // 选择收货地址
        Route::post('change_consignee', 'TradeController@change_consignee')->name('trade.change_consignee');
        // 使用红包
        Route::post('changebon', 'TradeController@changebon')->name('trade.changebon');
        // 使用优惠券
        Route::post('changecou', 'TradeController@changecou')->name('trade.changecou');
        // 储值卡
        Route::post('changecard', 'TradeController@changecard')->name('trade.changecard');
        // 消费积分
        Route::post('changeint', 'TradeController@changeint')->name('trade.changeint');
        // 使用余额
        Route::post('changesurplus', 'TradeController@changesurplus')->name('trade.changesurplus');
        // 商品税费
        Route::post('changetax', 'TradeController@changetax')->name('trade.changetax');
        // 切换开通购买权益卡
        Route::post('change_membership_card', 'TradeController@changeMembershipCard')->name('trade.change_membership_card');
        // 包装
        //Route::get('pack', 'TradeController@pack')->name('trade.pack');
        // 使用余额支付
        Route::any('balance', 'TradeController@balance')->name('trade.balance');
        // 自提点
        //Route::get('pick', 'TradeController@pick')->name('trade.pick');
        // 发票
        //Route::get('invoice', 'TradeController@invoice')->name('trade.invoice');
        // 提交
        Route::post('done', 'TradeController@done')->name('trade.done')->middleware('limit_form_repeat');
        // 选择在线支付方式
        Route::post('paycheck', 'TradeController@paycheck')->name('trade.paycheck');
        // 余额支付
        Route::post('balance', 'TradeController@balance')->name('trade.balance');
        // 再次购买
        Route::get('buyagain', 'TradeController@buyagain')->name('trade.buyagain');
    });

    // user 会员中心
    Route::prefix('user')->group(function () {
        // 注册
        Route::post('register', 'PassportController@register')->name('user.register')->middleware('limit_form_repeat');
        // 登录
        Route::post('login', 'PassportController@login')->name('user.login');
        // 获取注册配置
        Route::get('login_config', 'PassportController@loginConfig')->name('user.loginConfig');
        // 手机号码快捷登录
        Route::post('fast-login', 'PassportController@fastLogin')->name('user.login.fast');
        // 社会化登录
        Route::get('oauth_list', 'PassportController@oauth_list')->name('user.oauth_list');
        // 重置密码
        Route::post('reset', 'PassportController@reset')->name('user.reset');
        // 聚合
        Route::get('home', 'UserController@index')->name('user.home');
        // 用户资料
        Route::get('profile/basic', 'UserController@basicProfileByMobile')->name('user.profile.basic');
        // 保存用户资料
        Route::get('profile', 'UserController@profile')->name('user.profile');
        // 保存资料
        Route::put('profile', 'UserController@update')->name('user.update');
        // 设置头像
        Route::post('avatar', 'UserController@avatar')->name('user.avatar');
        // 素材
        Route::post('material', 'MaterialController@uploads')->name('user.material');
        // 视频
        Route::post('material_video', 'MaterialController@video')->name('user.video');
        // 生成ecjia哈希值
        Route::post('ecjia-hash', 'UserController@ecjiaHash')->name('user.ecjia.hash');
        // 帮助
        Route::post('help', 'UserController@help')->name('user.help');
        // 获取会员id
        Route::post('get-userid', 'UserController@getUserId')->name('user.getuserid');
        // 推广中心
        Route::post('affiliate_info', 'UserController@affiliateInfo')->name('user.affiliate_info');
        // 会员等级权益列表
        Route::post('rank_rights_list', 'UserController@rankRightsList')->name('user.rank_rights_list');
        // 下级会员
        Route::post('child_list', 'UserController@childList')->name('user.child_list');
        // 注册推荐奖励列表
        Route::post('affiliate_list', 'UserController@affiliateList')->name('user.affiliate_list');
        // 用户日志
        Route::get('user_log', 'UserController@userLog')->name('user.user_log');
        // 自定义工具栏
        Route::post('touch_nav', 'UserController@touch_nav')->name('user.touch_nav');
        // 忘记密码
        Route::post('forget', 'UserController@forget')->name('user.forget');
        // 重置密码发送邮件
        Route::post('reset_email', 'UserController@resetEmail')->name('user.reset_email');
        // 验证邮件
        Route::post('verification_email', 'UserController@verificationEmail')->name('user.verification_email');
        // 重置用户密码
        Route::post('reset_password', 'UserController@resetPassword')->name('user.reset_password');
        // 验证短信
        Route::post('verification_sms', 'UserController@verificationSms')->name('user.verification_sms');

    });

    // visual 可视化
    Route::prefix('visual')->group(function () {
        // 可视化首页
        Route::post('/', 'VisualController@index')->name('visual.index');
        // 可视化默认
        Route::post('default', 'VisualController@default')->name('visual.default');
        // 头部APP广告
        Route::post('appnav', 'VisualController@appnav')->name('visual.appnav');
        // 可视化文章模块
        Route::post('article', 'VisualController@article')->name('visual.article');
        //分类
        Route::post('product', 'VisualController@product')->name('visual.product');
        // 选中的商品
        Route::post('checked', 'VisualController@checked')->name('visual.checked');
        // 秒杀
        Route::post('seckill', 'VisualController@seckill')->name('visual.seckill');
        // 店铺街
        Route::post('store', 'VisualController@store')->name('visual.store');
        // 店铺街详情
        Route::post('storein', 'VisualController@storein')->name('visual.storein');
        // 店铺街底部详情
        Route::post('storedown', 'VisualController@storedown')->name('visual.storedown');
        // 店铺街关注
        Route::post('addcollect', 'VisualController@addcollect')->name('visual.addcollect');
        // 展示
        Route::post('view', 'VisualController@view')->name('visual.view');
        // 首页顶级分类
        Route::get('visual_category', 'VisualController@visualCategory')->name('visual.visual_category');
        // 首页二级分类
        Route::get('visual_second_category', 'VisualController@visualSecondCategory')->name('visual.visual_second_category');
        // 首页秒杀
        Route::get('visual_seckill', 'VisualController@visualSeckill')->name('visual.visual_seckill');
        // 首页拼团商品
        Route::get('visual_team_goods', 'VisualController@visualTeamGoods')->name('visual.visual_team_goods');
    });

    // 过滤词
    Route::prefix('filter')->name('filter.')->group(function () {
        // 记录
        Route::any('updatelogs', 'FilterWordsController@updatelogs')->name('updatelogs');
    });

    // 礼品卡
    Route::prefix('gift_gard')->group(function () {
        // 验证是否存在礼品卡
        Route::get('/', 'GiftGardController@index')->name('giftgard.index');
        // 礼品卡查询
        Route::get('check_gift', 'GiftGardController@checkGift')->name('giftgard.checkGift');
        // 礼品卡兑换列表
        Route::get('gift_list', 'GiftGardController@giftList')->name('giftgard.giftList');
        // 退出礼品卡
        Route::get('exit_gift', 'GiftGardController@exitGift')->name('giftgard.exitGift');
        // 提货
        Route::get('check_take', 'GiftGardController@checkTake')->name('giftgard.checkTake');
        // 我的提货
        Route::get('take_list', 'GiftGardController@takeList')->name('giftgard.takeList');
        // 确认收货
        Route::get('confim_goods', 'GiftGardController@confimGoods')->name('giftgard.confimGoods');

    });

    // 商家入驻
    Route::prefix('merchants')->group(function () {
        // 入驻商家信息
        Route::post('/', 'MerchantsController@index')->name('merchants.index');
        // 入驻须知
        Route::post('guide', 'MerchantsController@guide')->name('merchants.guide');
        // 同意协议
        Route::post('agree', 'MerchantsController@agree')->name('merchants.agree');
        // 入驻店铺信息
        Route::get('shop', 'MerchantsController@shop')->name('merchants.shop');
        // 提交入驻店铺信息
        Route::post('add_shop', 'MerchantsController@add_shop')->name('merchants.add_shop');
        // 获取下级类目
        Route::post('get_child_cate', 'MerchantsController@get_child_cate')->name('merchants.get_child_cate');
        // 添加详细类目
        Route::post('add_child_cate', 'MerchantsController@add_child_cate')->name('merchants.add_child_cate');
        // 删除详细类目
        Route::post('delete_child_cate', 'MerchantsController@delete_child_cate')->name('merchants.delete_child_cate');
        // 等待审核
        Route::get('audit', 'MerchantsController@audit')->name('merchants.audit');
        // 同意协议
        Route::post('agree_personal', 'MerchantsController@agree_personal')->name('merchants.agree_personal');
        // 查看申请信息
        Route::post('applyInfo', 'MerchantsController@applyInfo')->name('merchants.applyInfo');
    });

    // 二维码
    Route::prefix('qrcode')->group(function () {
        // 二维码
        Route::any('/', 'QrcodeController@index')->name('qrcode.index');
        // 获取二维码
        Route::get('qrcodeurl', 'QrcodeController@qrcodeUrl')->name('qrcode.qrcodeurl');
    });

    // 初始化seeder数据(安装器请求)
    Route::get('seeder', 'IndexController@install');
};

Route::namespace('App\Api\Controllers')->prefix('v4')->name('api.')->group($apiRoute);

Route::namespace('App\Api\Controllers')->name('api.')->group($apiRoute);
