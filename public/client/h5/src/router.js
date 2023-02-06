import Vue from 'vue'
import axios from 'axios'
import qs from 'qs'
import store from './store/index'
import Router from 'vue-router'
import wxShare from './filters/wxShare'

//语言包
import i18n from '@/locales'

Vue.use(Router)

const router = new Router({
	//mode: 'history',
	base: process.env.BASE_URL,
	routes: [{
			path: '/',
			redirect: '/home'
		},
		{
			path: '/home',
			name: 'home',
			component: resolve => require(['@/pages/Home/Home'], resolve),
			meta: {
				title: i18n.t('lang.home'),
				keepAlive: true
			}
		},
		{
			path: '/catalog',
			name: 'catalog',
			component: resolve => require(['@/pages/Catalog/Catalog'], resolve),
			meta: {
				title: i18n.t('lang.category')
			}
		},
		{
			path: '/list/:id',
			name: 'list',
			component: resolve => require(['@/pages/Goods/List'], resolve),
			meta: {
				title: i18n.t('lang.product_list')
			}			
		},
		{
			path: '/goods/:id',
			name: 'goods',
			component: resolve => require(['@/pages/Goods/Detail'], resolve),
			meta: {
				title: i18n.t('lang.goods_detail_info')
			}
		},
		{
			path: '/goods/comment/:id',
			name: 'goodsComment',
			component: resolve => require(['@/pages/Goods/GoodsComment'], resolve),
			meta: {
				title: i18n.t('lang.goods_comments')
			}
		},
		{
			path: '/goods/setmeal/:id',
			name: 'goodsSetmeal',
			props: true,
			component: resolve => require(['@/pages/Goods/setMeal'], resolve),
			meta: {
				title: i18n.t('lang.discount_package')
			}
		},
		{
			path: '/cart',
			name: 'cart',
			component: resolve => require(['@/pages/Cart/Cart'], resolve),
			meta: {
				title: i18n.t('lang.cart')
			}
		},
		{
			path: '/coudan',
			name: 'coudan',
			component: resolve => require(['@/pages/Cart/Coudan'], resolve),
			meta: {
				title: i18n.t('lang.coudan')
			}
		},
		{
			path: '/checkout',
			name: 'checkout',
			component: resolve => require(['@/pages/Trade/Checkout'], resolve),
			meta: {
				title: i18n.t('lang.checkout_page')
			}
		},
		{
			path: '/checkoutone',
			name: 'checkoutone',
			component: resolve => require(['@/pages/Trade/Checkoutone'], resolve),
			meta: {
				title: i18n.t('lang.checkout_page')
			}
		},
		{
			path: '/done',
			name: 'done',
			component: resolve => require(['@/pages/Trade/Done'], resolve),
			meta: {
				title: i18n.t('lang.order_pay')
			}
		},
		{
			path: '/respond',
			name: 'respond',
			component: resolve => require(['@/pages/Trade/Respond'], resolve),
			meta: {
				title: ''
			}
		},
		{
			path: '/article',
			name: 'article',
			component: resolve => require(['@/pages/Article/List'], resolve),
			meta: {
				title: i18n.t('lang.article_page')
			}
		},
		{
			path: '/articleDetail/:id',
			name: 'articleDetail',
			component: resolve => require(['@/pages/Article/Detail'], resolve),
			meta: {
				title: i18n.t('lang.article_detail_page')
			}
		},
		{
			path: '/ArticleCommentList/:id',
			name: 'articleCommentList',
			component: resolve => require(['@/pages/Article/CommentList'], resolve),
			meta: {
				title: i18n.t('lang.article_comment_list_page')
			}
		},
		{
			path: '/wechatMedia/:id',
			name: 'wechatMedia',
			component: resolve => require(['@/pages/Article/WechatMedia'], resolve),
			meta: {
				title: ''
			}
		},
		{
			path: '/brand',
			name: 'brand',
			component: resolve => require(['@/pages/Brand/Index'], resolve),
			meta: {
				title: i18n.t('lang.brand')
			}
		},
		{
			path: '/brandList',
			name: 'brandList',
			component: resolve => require(['@/pages/Brand/List'], resolve),
			meta: {
				title: i18n.t('lang.brand_list_page')
			}
		},
		{
			path: '/brandDetail/:id',
			name: 'brandDetail',
			component: resolve => require(['@/pages/Brand/Detail'], resolve),
			meta: {
				title: i18n.t('lang.brand_detail_page')
			}
		},
		{
			path: '/discover',
			name: 'discover',
			component: resolve => require(['@/pages/Discover/Index'], resolve),
			meta: {
				title: i18n.t('lang.discover_page')
			}
		},
		{
			path: '/discoverList',
			name: 'discoverList',
			component: resolve => require(['@/pages/Discover/List'], resolve),
			meta: {
				title: i18n.t('lang.discover_page')
			}
		},
		{
			path: '/discoverListType',
			name: 'discoverListType',
			component: resolve => require(['@/pages/Discover/ListType'], resolve),
			meta: {
				title: i18n.t('lang.discover_page')
			}
		},
		{
			path: '/discoverDetail',
			name: 'discoverDetail',
			component: resolve => require(['@/pages/Discover/Detail'], resolve),
			meta: {
				title: ''
			}
		},
		{
			path: '/myDiscover',
			name: 'myDiscover',
			component: resolve => require(['@/pages/Discover/Me'], resolve),
			meta: {
				title: ''
			}
		},
		{
			path: '/shopHome/:id',
			name: 'shopHome',
			component: resolve => require(['@/pages/Shop/Home'], resolve),
			meta: {
				keepAlive: true
			}
		},
		{
			path: '/shop',
			name: 'shop',
			component: resolve => require(['@/pages/Shop/Index'], resolve),
			meta: {
				title: i18n.t('lang.shop_street')
			}
		},
		{
			path: '/shopDetail/:id',
			name: 'shopDetail',
			component: resolve => require(['@/pages/Shop/Detail'], resolve),
			meta: {
				title: i18n.t('lang.shop_details')
			}
		},
		{
			path: '/ShopGoodsList',
			name: 'shopGoodsList',
			component: resolve => require(['@/pages/Shop/Goods'], resolve),
			meta: {
				title: i18n.t('lang.shop_goods_list_page')
			}
		},

		{
			path: '/storeGoods',
			name: 'storeGoods',
			component: resolve => require(['@/pages/Store/Goods'], resolve),
			meta: {
				title: i18n.t('lang.store_goods_page')
			}
		},

		//user auth
		{
			path: '/register',
			name: 'register',
			component: resolve => require(['@/pages/User/Auth/Register'], resolve),
			meta: {
				title: i18n.t('lang.register_page')
			}
		},
		{
			path: '/login',
			name: 'login',
			component: resolve => require(['@/pages/User/Auth/Login'], resolve),
			meta: {
				title: i18n.t('lang.account_pwd_login')
			}
		},
		{
			path: '/loginMobile',
			name: 'loginMobile',
			component: resolve => require(['@/pages/User/Auth/LoginMobile'], resolve),
			meta: {
				title: i18n.t('lang.login_mobile_page')
			}
		},
		{
			path: '/forget',
			name: 'forget',
			component: resolve => require(['@/pages/User/Auth/Forget'], resolve),
			meta: {
				title: i18n.t('lang.forget_password')
			}
		},
		{
			path: '/forgetpwd',
			name: 'forgetpwd',
			component: resolve => require(['@/pages/User/Auth/ForgetPwd'], resolve),
			meta: {
				title: i18n.t('lang.forget_password')
			}
		},
		{
			path: '/bindphone',
			name: 'bindphone',
			component: resolve => require(['@/pages/User/Auth/Bindphone'], resolve),
			meta: {
				title: i18n.t('lang.bind_phone')
			}
		},
		{
			path: '/relationphone',
			name: 'relationphone',
			component: resolve => require(['@/pages/User/Auth/Relationphone'], resolve),
			meta: {
				title: i18n.t('lang.bind_phone')
			}
		},
		{
			path: '/resetphone',
			name: 'resetphone',
			component: resolve => require(['@/pages/User/Auth/Resetphone'], resolve),
			meta: {
				title: i18n.t('lang.set_phone')
			}
		},
		{
			path: '/growthdetails',
			name: 'growthdetails',
			component: resolve => require(['@/pages/User/Auth/Growthdetails'], resolve),
			meta: {
				title: i18n.t('lang.forget_password')
			}
		},
		{
			path: '/user/reset',
			name: 'reset',
			component: resolve => require(['@/pages/User/Auth/Reset'], resolve),
			meta: {
				title: i18n.t('lang.reset_pwd')
			}
		},
		{
			path: '/callback',
			name: 'callback',
			component: resolve => require(['@/pages/User/Auth/Callback'], resolve),
			meta: {
				title: i18n.t('lang.authorization_page')
			}
		},
		{
			path: '/binduser',
			name: 'binduser',
			component: resolve => require(['@/pages/User/Auth/BindUser'], resolve),
			meta: {
				title: i18n.t('lang.bind_user')
			}
		},
		{
			path: '/user',
			name: 'user',
			component: resolve => require(['@/pages/User/Index'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.user_center_page')
			}
		},
		// user center
		{
			path: '/user/account',
			name: 'account',
			component: resolve => require(['@/pages/User/Account/Account'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.account_manage')
			}
		},
		{
			path: '/user/account/detail',
			name: 'accountDetail',
			component: resolve => require(['@/pages/User/Account/Detail'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.account_manage_detail_page')
			}
		},
		{
			path: '/user/account/integra',
			name: 'integra',
			component: resolve => require(['@/pages/User/Account/Integra'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.my_points')
			}
		},
		{
			path: '/user/account/log',
			name: 'accountLog',
			component: resolve => require(['@/pages/User/Account/AccountLog'], resolve),
			meta: {
				title: i18n.t('lang.application_record')
			},
			children: [{
				path: ':id',
				component: resolve => require(['@/pages/User/Account/AccountLogDetail'], resolve),
			}]
		},
		{
			path: '/user/account/deposit',
			name: 'deposit',
			component: resolve => require(['@/pages/User/Account/Deposit'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.recharge')
			}
		},
		{
			path: '/user/account/accountRaply',
			name: 'accountRaply',
			component: resolve => require(['@/pages/User/Account/AccountRaply'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.deposit')
			}
		},
		{
			path: '/user/account/valuecard',
			name: 'valueCard',
			component: resolve => require(['@/pages/User/Account/ValueCard/ValueCard'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.value_card')
			}
		},
		{
			path: '/user/account/addValueCard',
			name: 'addValueCard',
			component: resolve => require(['@/pages/User/Account/ValueCard/AddValueCard'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.bind_value_card')
			}
		},
		{
			path: '/user/account/detail/:id',
			name: 'valueCardDetail',
			component: resolve => require(['@/pages/User/Account/ValueCard/Detail'], resolve),
			meta: {
				requireAuth: true
			}
		},
		{
			path: '/user/account/bonus',
			name: 'bonus',
			component: resolve => require(['@/pages/User/Account/Bonus/Bonus'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.bonus')
			}
		},
		{
			path: '/user/account/addBonus',
			name: 'addBonus',
			component: resolve => require(['@/pages/User/Account/Bonus/AddBonus'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.add_bonus')
			}
		},
		{
			path: '/user/account/invForm',
			name: 'invForm',
			component: resolve => require(['@/pages/User/Account/InvForm'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.vat_tax_invoice')
			}
		},
		{
			path: '/user/address',
			name: 'address',
			component: resolve => require(['@/pages/User/Address/Index'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.address')
			}
		},
		{
			path: '/user/addAddress',
			name: 'addAddressForm',
			component: resolve => require(['@/pages/User/Address/Form'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.add_address')
			}
		},
		{
			path: '/user/communityPost',
			name: 'communityPost',
			component: resolve => require(['@/pages/User/Address/CommunityPost'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.community_post')
			}
		},
		{
			path: '/user/editAddress/:id',
			name: 'editAddressForm',
			component: resolve => require(['@/pages/User/Address/Form'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.edit_address')
			}
		},
		{
			path: '/user/affiliate',
			name: 'affiliate',
			component: resolve => require(['@/pages/User/Affiliate/Affiliate'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.affiliate')
			}
		},
		{
			path: '/user/affiliateIndex',
			name: 'affiliateIndex',
			component: resolve => require(['@/pages/User/Affiliate/Index'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.affiliate')
			}
		},
		{
			path: '/user/affiliateTeam',
			name: 'affiliateTeam',
			component: resolve => require(['@/pages/User/Affiliate/Team'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.my_team_alt')
			}
		},
		{
			path: '/user/affiliateProtection',
			name: 'affiliateProtection',
			component: resolve => require(['@/pages/User/Affiliate/Protection'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.equity_detail')
			}
		},
		{
			path: '/user/refound',
			name: 'refound',
			component: resolve => require(['@/pages/User/AfterSales/Index'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.return_list')
			}
		},
		{
			path: '/user/refoundDetail',
			name: 'refoundDetail',
			component: resolve => require(['@/pages/User/AfterSales/Detail'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.return_detail')
			}
		},
		{
			path: '/user/rpplyReturn',
			name: 'rpplyReturn',
			component: resolve => require(['@/pages/User/AfterSales/ApplyReturn'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.return_apply')
			}
		},
		{
			path: '/user/collectionGoods',
			name: 'collectionGoods',
			component: resolve => require(['@/pages/User/Collection/Goods'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.collection_goods')
			}
		},
		{
			path: '/user/collectionShop',
			name: 'collectionShop',
			component: resolve => require(['@/pages/User/Collection/Shop'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.collection_shop')
			}
		},
		{
			path: '/user/comment',
			name: 'comment',
			component: resolve => require(['@/pages/User/Comment/Comment'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.assessment_center')
			}
		},
		{
			path: '/user/commentDetail/:id/:type',
			name: 'commentDetail',
			component: resolve => require(['@/pages/User/Comment/Detail'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.evaluation_detail')
			}
		},
		{
			path: '/user/profile',
			name: 'profile',
			component: resolve => require(['@/pages/User/Profile/Profile'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.profile')
			}
		},
		{
			path: '/user/realname',
			name: 'realname',
			component: resolve => require(['@/pages/User/Profile/Realname'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.real_name')
			}
		},
		{
			path: '/user/uploadcard',
			name: 'uploadcard',
			component: resolve => require(['@/pages/User/Profile/UploadCard'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.upload_id_card_pic')
			}
		},
		{
			path: '/user/accountsafe',
			name: 'accountsafe',
			component: resolve => require(['@/pages/User/Profile/Accountsafe'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.account_security')
			}
		},
		{
			path: '/user/Profile/operationlog/operationlog',
			name: 'operationlog',
			component: resolve => require(['@/pages/User/Profile/Operationlog/Operationlog'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.operation_log')
			}
		},
		{
			path: '/user/paypwd',
			name: 'paypwd',
			component: resolve => require(['@/pages/User/Profile/Paypwd/Index'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.pay_pwd')
			}
		},
		{
			path: '/user/order',
			name: 'order',
			component: resolve => require(['@/pages/User/Order/Index'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.my_order')
			}
		},
		{
			path: '/user/orderDetail/:id',
			name: 'orderDetail',
			component: resolve => require(['@/pages/User/Order/Detail'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.order_detail')
			}
		},
		{
			path: '/user/booking',
			name: 'booking',
			component: resolve => require(['@/pages/User/Booking/Booking'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.booking')
			}
		},
		{
			path: '/user/help',
			name: 'help',
			component: resolve => require(['@/pages/User/Help/Help'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.help_center')
			}
		},
		{
			path: '/user/message',
			name: 'message',
			component: resolve => require(['@/pages/User/Message/Message'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.my_message')
			}
		},
		
		{
			path: '/user/messagelist',
			name: 'messagelist',
			component: resolve => require(['@/pages/User/Messagelist/Messagelist'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.my_message')
			}
		},
		{
			path: '/user/history',
			name: 'history',
			component: resolve => require(['@/pages/User/History/Index'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.browsing_history')
			}
		},
		{
			path: '/user/coupon',
			name: 'userCoupon',
			component: resolve => require(['@/pages/User/Coupon/Coupon'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.my_coupons')
			}
		},
		{
			path: '/user/auction',
			name: 'userAuction',
			component: resolve => require(['@/pages/User/Auction/Index'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.my_auction')
			}
		},

		{
			path: '/user/merchants',
			name: 'merchants',
			component: resolve => require(['@/pages/User/Merchants/Index'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.merchants_store')
			}
		},

		{
			path: '/user/merchants/reviewProgress',
			name: 'reviewProgress',
			component: resolve => require(['@/pages/User/Merchants/reviewProgress'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.review_progress')
			}
		},

		{
			path: '/user/merchants/applyInfo',
			name: 'applyInfo',
			component: resolve => require(['@/pages/User/Merchants/applyInfo'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.apply_info')
			}
		},

		// activity
		{
			path: '/activity',
			name: 'activity',
			component: resolve => require(['@/pages/Ump/Activity/Index'], resolve),
			meta: {
				title: i18n.t('lang.activity')
			}
		},
		{
			path: '/activity/detail/:act_id',
			name: 'activity-detail',
			component: resolve => require(['@/pages/Ump/Activity/Detail'], resolve),
			meta: {
				title: i18n.t('lang.activity_detail')
			}
		},

		// auction
		{
			path: '/auction',
			name: 'auction',
			component: resolve => require(['@/pages/Ump/Auction/Index'], resolve),
			meta: {
				title: i18n.t('lang.auction')
			}
		},
		{
			path: '/auction/detail/:act_id',
			name: 'auction-detail',
			component: resolve => require(['@/pages/Ump/Auction/Detail'], resolve),
			meta: {
				title: i18n.t('lang.auction_detail')
			}
		},
		{
			path: '/auction/log/:act_id',
			name: 'auction-log',
			component: resolve => require(['@/pages/Ump/Auction/Log'], resolve),
			meta: {
				title: i18n.t('lang.auction_log')
			}
		},
		// bonus
		{
			path: '/bonus',
			name: 'bonusReceive',
			component: resolve => require(['@/pages/Ump/Bonus/Bonus'], resolve),
			meta: {
				title: i18n.t('lang.bonus_receive')
			}
		},
		// bargain
		{
			path: '/bargain',
			name: 'bargain',
			component: resolve => require(['@/pages/Ump/Bargain/Index'], resolve),
			meta: {
				title: i18n.t('lang.bargain')
			}
		},
		{
			path: '/bargain/detail/:id',
			name: 'bargain-detail',
			component: resolve => require(['@/pages/Ump/Bargain/Detail/Detail'], resolve),
			meta: {
				title: i18n.t('lang.bargain_detail')
			}
		},
		{
			path: '/bargain/myList',
			name: 'bargain-mylist',
			component: resolve => require(['@/pages/Ump/Bargain/Detail/MyList'], resolve),
			meta: {
				title: i18n.t('lang.my_bargain')
			}
		},
		// coupon
		{
			path: '/coupon',
			name: 'coupon',
			component: resolve => require(['@/pages/Ump/Coupon/Coupon'], resolve),
			meta: {
				title: i18n.t('lang.coupons_receive')
			}
		},
		// crowdfunding
		{
			path: '/crowdfunding',
			name: 'crowdfunding',
			component: resolve => require(['@/pages/Ump/CrowdFunding/Index'], resolve),
			meta: {
				title: i18n.t('lang.crowdfunding')
			}
		},
		{
			path: '/crowdfunding/detail/:id',
			name: 'crowdfunding-detail',
			component: resolve => require(['@/pages/Ump/CrowdFunding/Detail/Detail'], resolve),
			meta: {
				title: i18n.t('lang.crowdfunding_detail')
			}
		},
		{
			path: '/crowdfunding/checkout',
			name: 'crowdfunding-checkout',
			component: resolve => require(['@/pages/Ump/CrowdFunding/Detail/Checkout'], resolve),
			meta: {
				title: i18n.t('lang.crowdfunding_checkout')
			}
		},
		{
			path: '/crowdfunding/done',
			name: 'crowdfunding-done',
			component: resolve => require(['@/pages/Ump/CrowdFunding/Detail/Done'], resolve),
			meta: {
				title: i18n.t('lang.crowdfunding_done')
			}
		},
		{
			path: '/crowdfunding/order',
			name: 'crowdfunding-order',
			component: resolve => require(['@/pages/Ump/CrowdFunding/Detail/Order'], resolve),
			meta: {
				title: i18n.t('lang.square_order')
			}
		},
		{
			path: '/crowdfunding/orderDetail/:id',
			name: 'crowdfunding-orderdetail',
			component: resolve => require(['@/pages/Ump/CrowdFunding/Detail/OrderDetail'], resolve),
			meta: {
				title: i18n.t('lang.crowdfunding_order_detail')
			}
		},
		{
			path: '/crowdfunding/user',
			name: 'crowdfunding-user',
			component: resolve => require(['@/pages/Ump/CrowdFunding/Detail/User'], resolve),
			meta: {
				title: i18n.t('lang.crowdfunding_user')
			}
		},
		{
			path: '/crowdfunding/topic',
			name: 'crowdfunding-topic',
			component: resolve => require(['@/pages/Ump/CrowdFunding/Detail/Topic'], resolve),
		},
		{
			path: '/crowdfunding/buy',
			name: 'crowdfunding-buy',
			component: resolve => require(['@/pages/Ump/CrowdFunding/Detail/Buy'], resolve),
		},
		{
			path: '/crowdfunding/focus',
			name: 'crowdfunding-focus',
			component: resolve => require(['@/pages/Ump/CrowdFunding/Detail/Focus'], resolve),
		},
		// drp
		{
			path: '/drp',
			name: 'drp',
			component: resolve => require(['@/pages/Ump/Drp/Index'], resolve),
			meta: {
				title: i18n.t('lang.drp')
			}
		},
		{
			path: '/drp/register',
			name: 'drp-register',
			component: resolve => require(['@/pages/Ump/Drp/Detail/Register'], resolve),
			meta: {
				title: i18n.t('lang.vip')
			}
		},
		{
			path: '/drp/finish',
			name: 'drp-finish',
			component: resolve => require(['@/pages/Ump/Drp/Detail/Finish'], resolve),
		},
		{
			path: '/drp/withdraw',
			name: 'drp-withdraw',
			component: resolve => require(['@/pages/Ump/Drp/Detail/Withdraw'], resolve),
		},
		{
			path: '/drp/withdraw_log',
			name: 'drp-withdraw-log',
			component: resolve => require(['@/pages/Ump/Drp/Detail/WithdrawLog'], resolve),
		},
		{
			path: '/drp/set',
			name: 'drp-set',
			component: resolve => require(['@/pages/Ump/Drp/Detail/Set'], resolve),
			meta: {
				title: i18n.t('lang.drp_set')
			}
		},
		{
			path: '/drp/order',
			name: 'drp-order',
			component: resolve => require(['@/pages/Ump/Drp/Detail/Order'], resolve),
			meta: {
				title: i18n.t('lang.drp_order')
			}
		},
		{
			path: '/drp/orderDetail/:order_id',
			name: 'drp-orderdetail',
			component: resolve => require(['@/pages/Ump/Drp/Detail/OrderDetail'], resolve),
			meta: {
				title: i18n.t('lang.drp_order_detail')
			}
		},
		{
			path: '/drp/card',
			name: 'drp-card',
			component: resolve => require(['@/pages/Ump/Drp/Detail/Card'], resolve),
			meta: {
				title: i18n.t('lang.drp_qrcode')
			}
		},
		{
			path: '/drp/new',
			name: 'drp-new',
			component: resolve => require(['@/pages/Ump/Drp/Detail/New'], resolve),
		},
		{
			path: '/drp/shop',
			name: 'drp-shop',
			component: resolve => require(['@/pages/Ump/Drp/Detail/Shop'], resolve),
			meta: {
				title: i18n.t('lang.drp_shop')
			}
		},
		{
			path: '/drp/category',
			name: 'drp-category',
			component: resolve => require(['@/pages/Ump/Drp/Detail/Category'], resolve),
			meta: {
				title: i18n.t('lang.drp_category')
			}
		},
		{
			path: '/drp/rank',
			name: 'drp-rank',
			component: resolve => require(['@/pages/Ump/Drp/Detail/Rank'], resolve),
			meta: {
				title: i18n.t('lang.drp_rank')
			}
		},
		{
			path: '/drp/team/:user_id',
			name: 'drp-team',
			component: resolve => require(['@/pages/Ump/Drp/Detail/Team'], resolve),
			meta: {
				title: i18n.t('lang.drp_team')
			}
		},
		{
			path: '/drp/teamDetail/:user_id',
			name: 'drp-teamdetail',
			component: resolve => require(['@/pages/Ump/Drp/Detail/TeamDetail'], resolve),
			meta: {
				title: i18n.t('lang.drp_team_detail')
			}
		},
		{
			path: '/drp/drplog',
			name: 'drp-drplog',
			component: resolve => require(['@/pages/Ump/Drp/Detail/Drplog'], resolve),
			meta: {
				title: i18n.t('lang.drp_commission')
			}
		},
		{
			path: '/drp/drpgoods',
			name: 'drp-drpgoods',
			component: resolve => require(['@/pages/Ump/Drp/Detail/Drpgoods'], resolve),
			meta: {
				title: i18n.t('lang.drp_goods')
			}
		},
		{
			path: '/drp/drplist',
			name: 'drp-drplist',
			component: resolve => require(['@/pages/Ump/Drp/Detail/Drplist'], resolve),
			meta: {
				title: i18n.t('lang.drp_list')
			}
		},
		{
			path: '/drp/drpPurchase',
			name: 'drp-purchase',
			component: resolve => require(['@/pages/Ump/Drp/Detail/Purchase'], resolve),
			meta: {
				title: i18n.t('lang.vip_title_pay'),
				requireAuth: true
			}
		},
		{
			path: '/drp/drpDone',
			name: 'drp-done',
			component: resolve => require(['@/pages/Ump/Drp/Detail/Done'], resolve),
			meta: {
				title: i18n.t('lang.drp_done')
			}
		},
		{
			path: '/drp/drpinfo',
			name: 'drp-info',
			component: resolve => require(['@/pages/Ump/Drp/Detail/Drpinfo'], resolve),
			meta: {
				title: i18n.t('lang.high_grade_vip')
			}
		},
		{
			path: '/drp/protection',
			name: 'drp-protection',
			component: resolve => require(['@/pages/Ump/Drp/Detail/Protection'], resolve),
			meta: {
				title: i18n.t('lang.equity_detail')
			}
		},
		{
			path: '/drp/apply',
			name: 'drp-apply',
			component: resolve => require(['@/pages/Ump/Drp/Detail/DrpApply'], resolve),
			meta: {
				title: i18n.t('lang.equity_apply')
			}
		},

		// exchange
		{
			path: '/exchange',
			name: 'exchange',
			component: resolve => require(['@/pages/Ump/Exchange/Index'], resolve),
			meta: {
				title: i18n.t('lang.exchange')
			}
		},
		{
			path: '/exchange/detail/:id',
			name: 'exchange-detail',
			component: resolve => require(['@/pages/Ump/Exchange/Detail'], resolve),
			meta: {
				title: i18n.t('lang.exchange_detail')
			}
		},
		// groupbuy
		{
			path: '/groupbuy',
			name: 'groupbuy',
			component: resolve => require(['@/pages/Ump/GroupBuy/Index'], resolve),
			meta: {
				title: i18n.t('lang.group_buy')
			}
		},
		{
			path: '/groupbuy/detail/:group_buy_id',
			name: 'groupbuy-detail',
			component: resolve => require(['@/pages/Ump/GroupBuy/Detail'], resolve),
			meta: {
				title: i18n.t('lang.group_buy_detail')
			}
		},
		// package
		{
			path: '/package',
			name: 'package',
			component: resolve => require(['@/pages/Ump/Package/Index'], resolve),
			meta: {
				title: i18n.t('lang.package_box')
			}
		},
		// presale
		{
			path: '/presale',
			name: 'presale',
			component: resolve => require(['@/pages/Ump/Presale/Index'], resolve),
			meta: {
				title: i18n.t('lang.presale')
			}
		},
		{
			path: '/presale/detail/:act_id',
			name: 'presale-detail',
			component: resolve => require(['@/pages/Ump/Presale/Detail'], resolve),
			meta: {
				title: i18n.t('lang.presale_detail')
			}
		},
		{
			path: '/presale/list/:cat_id',
			name: 'presale-list',
			component: resolve => require(['@/pages/Ump/Presale/List'], resolve),
			meta: {
				title: i18n.t('lang.presale_list')
			}
		},
		// seckill
		{
			path: '/seckill',
			name: 'seckill',
			component: resolve => require(['@/pages/Ump/Seckill/Index'], resolve),
			meta: {
				title: i18n.t('lang.seckill')
			}
		},
		{
			path: '/seckill/detail',
			name: 'seckill-detail',
			component: resolve => require(['@/pages/Ump/Seckill/Detail'], resolve),
			meta: {
				title: i18n.t('lang.seckill_detail')
			}
		},
		// team
		{
			path: '/team',
			name: 'team',
			component: resolve => require(['@/pages/Ump/Team/Index'], resolve),
			meta: {
				title: i18n.t('lang.team')
			}
		},
		{
			path: '/team/list',
			name: 'team-list',
			component: resolve => require(['@/pages/Ump/Team/Detail/List'], resolve),
			meta: {
				title: i18n.t('lang.team_list')
			}
		},
		{
			path: '/team/rank',
			name: 'team-rank',
			component: resolve => require(['@/pages/Ump/Team/Detail/Rank'], resolve),
			meta: {
				title: i18n.t('lang.team_rank')
			}
		},
		{
			path: '/team/detail/',
			name: 'team-detail',
			component: resolve => require(['@/pages/Ump/Team/Detail/Detail'], resolve),
			meta: {
				title: i18n.t('lang.team_detail')
			}
		},
		{
			path: '/team/order',
			name: 'team-order',
			component: resolve => require(['@/pages/Ump/Team/Detail/Order'], resolve),
			meta: {
				title: i18n.t('lang.team_order')
			}
		},
		{
			path: '/team/wait',
			name: 'team-wait',
			component: resolve => require(['@/pages/Ump/Team/Detail/Wait'], resolve),
			meta: {
				title: i18n.t('lang.team_schedule')
			}
		},
		{
			path: '/team/user',
			name: 'team-user',
			component: resolve => require(['@/pages/Ump/Team/Detail/User'], resolve),
			meta: {
				title: i18n.t('lang.team_user')
			}
		},
		// topic
		{
			path: '/topic',
			name: 'topic',
			component: resolve => require(['@/pages/Ump/Topic/Index'], resolve),
			meta: {
				title: i18n.t('lang.topic')
			}
		},
		{
			path: '/topicHome/:id',
			name: 'topicHome',
			component: resolve => require(['@/pages/Ump/Topic/Home'], resolve),
			meta: {
				title: i18n.t('lang.topic_detail'),
				keepAlive: true
			}
		},
		// supplier
		{
			path: '/supplier',
			name: 'supplier',
			component: resolve => require(['@/pages/Ump/Supplier/Index'], resolve),
			meta: {
				requireAuth: true,
				supplierAuth: true,
				title: i18n.t('lang.suppliers')
			}
		},
		{
			path: '/supplier/search',
			name: 'supplier-search',
			component: resolve => require(['@/pages/Ump/Supplier/Detail/Search'], resolve),
			meta: {
				requireAuth: true,
				supplierAuth: true,
				title: i18n.t('lang.supplier_search')
			}
		},
		{
			path: '/supplier/detail/:id',
			name: 'supplier-detail',
			component: resolve => require(['@/pages/Ump/Supplier/Detail/Detail'], resolve),
			meta: {
				requireAuth: true,
				supplierAuth: true,
				title: i18n.t('lang.supplier_detail')
			}
		},
		{
			path: '/supplier/list',
			name: 'supplier-list',
			component: resolve => require(['@/pages/Ump/Supplier/Detail/List'], resolve),
			meta: {
				requireAuth: true,
				supplierAuth: true,
				title: i18n.t('lang.supplier_list')
			}
		},
		{
			path: '/supplier/cart',
			name: 'supplier-cart',
			component: resolve => require(['@/pages/Ump/Supplier/Detail/Cart'], resolve),
			meta: {
				requireAuth: true,
				supplierAuth: true,
				title: i18n.t('lang.supplier_cart')
			}
		},
		{
			path: '/supplier/checkout',
			name: 'supplier-checkout',
			component: resolve => require(['@/pages/Ump/Supplier/Detail/Checkout'], resolve),
			meta: {
				requireAuth: true,
				supplierAuth: true,
				title: i18n.t('lang.supplier_checkout')
			}
		},
		{
			path: '/supplier/done',
			name: 'supplier-done',
			component: resolve => require(['@/pages/Ump/Supplier/Detail/Done'], resolve),
			meta: {
				requireAuth: true,
				supplierAuth: true,
				title: i18n.t('lang.supplier_done')
			}
		},
		{
			path: '/supplier/buy',
			name: 'supplier-buy',
			component: resolve => require(['@/pages/Ump/Supplier/Detail/Buy'], resolve),
			meta: {
				requireAuth: true,
				supplierAuth: true,
				title: i18n.t('lang.supplier_buy')
			}
		},
		{
			path: '/supplier/buyinfo',
			name: 'supplier-buyinfo',
			component: resolve => require(['@/pages/Ump/Supplier/Detail/Buyinfo'], resolve),
			meta: {
				requireAuth: true,
				supplierAuth: true
			}
		},
		{
			path: '/supplier/orderlist',
			name: 'supplier-orderlist',
			component: resolve => require(['@/pages/User/Supplier/Order'], resolve),
			meta: {
				requireAuth: true,
				supplierAuth: true,
				title: i18n.t('lang.supplier_order_list')
			}
		},
		{
			path: '/supplier/applyreturn',
			name: 'supplier-applyreturn',
			component: resolve => require(['@/pages/User/Supplier/ApplyReturn'], resolve),
			meta: {
				requireAuth: true,
				supplierAuth: true,
				title: i18n.t('lang.supplier_applyreturn')
			}
		},
		{
			path: '/supplier/returndetail',
			name: 'supplier-returndetail',
			component: resolve => require(['@/pages/User/Supplier/ReturnDetail'], resolve),
			meta: {
				requireAuth: true,
				supplierAuth: true,
				title: i18n.t('lang.supplier_applyreturn_detail')
			}
		},
		{
			path: '/supplier/returnindex',
			name: 'supplier-returnindex',
			component: resolve => require(['@/pages/User/Supplier/ReturnIndex'], resolve),
			meta: {
				requireAuth: true,
				supplierAuth: true,
				title: i18n.t('lang.supplier_applyreturn')
			}
		},
		{
			path: '/supplier/suppliershop/:id',
			name: 'supplier-shop',
			component: resolve => require(['@/pages/Ump/Supplier/Detail/Shop'], resolve),
			meta: {
				requireAuth: true,
				supplierAuth: true,
				title: i18n.t('lang.supplier_shop')
			}
		},
		{
			path: '/supplier/apply',
			name: 'supplier-apply',
			component: resolve => require(['@/pages/User/Supplier/Apply'], resolve),
			meta: {
				requireAuth: true,
				title: i18n.t('lang.supplier_apply')
			}
		},
		// giftCard
		{
			path: '/giftCard',
			name: 'giftCard',
			component: resolve => require(['@/pages/Ump/GiftCard/Index'], resolve),
			meta: {
				title: i18n.t('lang.my_gift_card'),
				requireAuth: true
			}
		},
		{
			path: '/giftCardResult',
			name: 'giftCardResult',
			component: resolve => require(['@/pages/Ump/GiftCard/Result'], resolve),
			meta: {
				title: i18n.t('lang.gift_card_list_page'),
				requireAuth: true
			}
		},
		{
			path: '/giftCardAddress/:id',
			name: 'giftCardAddress',
			component: resolve => require(['@/pages/Ump/GiftCard/Address'], resolve),
			meta: {
				title: i18n.t('lang.gift_card_th_page'),
				requireAuth: true
			}
		},
		{
			path: '/giftCardOrder',
			name: 'giftCardOrder',
			component: resolve => require(['@/pages/Ump/GiftCard/Order'], resolve),
			meta: {
				title: i18n.t('lang.my_th_page'),
				requireAuth: true
			}
		},
		{
			path: '/search',
			name: 'search',
			component: resolve => require(['@/pages/Goods/Search'], resolve),
			meta: {
				title: i18n.t('lang.search'),
				keepAlive: true
			}
		},
		// 视频列表
		{
			path: '/videoList',
			name: 'videoList',
			component: resolve => require(['@/pages/Goods/VideoList'], resolve),
			meta: {
				title: i18n.t('lang.video_list')
			}
		},
		{
			path: '/searchList',
			name: 'searchList',
			component: resolve => require(['@/pages/Goods/SearchList'], resolve),
			meta: {
				title: i18n.t('lang.search_list')
			}
		},
		//扫码支付
		{
			path: '/qrpay',
			name: 'qrpay',
			component: resolve => require(['@/pages/Ump/Qrpay/Qrpay'], resolve),
			meta: {
				title: i18n.t('lang.qrpay')
			}
		},
		{
			path: '/WebView',
			name: 'webView',
			component: resolve => require(['@/pages/WebView/WebView'], resolve),
		},
		//1.6.2新增发现页面
		{
			path: '/integration',
			name: 'integration',
			component: resolve => require(['@/pages/Integration/Integration'], resolve),
		},
		{
			path: '/conmmentlist/:id',
			name: 'conmmentlist',
			component: resolve => require(['@/pages/Integration/conmment-list'], resolve),
			props: true
		},
		{
			path: '*',
			redirect: '/'
		},
		//Custom 二次开发路由
		//shouqianba 收钱吧电子发票
        {
        	path: '/user/invoiceDetail/:order_id',
        	name: 'invoiceDetail',
        	component: resolve => require(['@/pages/Custom/Shouqianba/InvoiceDetail'], resolve),
        	meta: {
        		title: '查看电子发票',
        		requireAuth: true
        	}
        },
        {
        	path: '/user/invoiceReapply',
        	name: 'invoiceReapply',
        	component: resolve => require(['@/pages/Custom/Shouqianba/InvoiceReapply'], resolve),
        	meta: {
        		title: '补开发票',
        		requireAuth: true
        	}
        },
	],
	scrollBehavior(to, from, savedPosition) {
		if(from.meta.keepAlive){
			// setTimeout(() => {
			// 	window.scrollTo(savedPosition.x, savedPosition.y)
			// }, 200)
		}else{
			if (to.name !== 'home' && to.name !== 'shopHome' && from.name !== 'goods') {
				setTimeout(() => {
					window.scrollTo(0, 0)
				}, 200)
			}
		}
	}
})

//全局前置守卫
router.beforeEach(async (route, redirect, next) => {
	//统计代码 百度
	if (typeof _hmt !== 'undefined') {
		if (route.path) {
			_hmt.push(['_trackPageview'], `${window.ROOT_URL}mobile/#` + route.fullPath);
		}
	}

	//获取本地存储的token
	store.state.token = localStorage.getItem('token')

	//路由跳转如果headers token为空添加token
	if(localStorage.getItem('token')){
		axios.defaults.headers.common['token'] = localStorage.getItem('token')
	}

	if (route.query.parent_id) {
		localStorage.setItem('parent_id', route.query.parent_id)
	}

	//判断是否登录
	if (localStorage.getItem('token')) {
		//获取userid
		if (!localStorage.getItem('user_id')) {
			const data = await store.dispatch('userId')
			const parent_id = localStorage.getItem('parent_id')

			if (data.status == 'success') {
				if (data.data.id && (parent_id != data.data.id)) {
					localStorage.setItem('user_id', data.data.id)
				}
			} else {
				localStorage.removeItem('user_id')
				localStorage.removeItem('token')
			}
		}
	}

	window.ecjiahash = route.query.ecjiahash ? route.query.ecjiahash : ''

	if (window.ecjiahash) {
		store.dispatch('userLogout')

		const roles = await store.dispatch('userRegister', {
			ecjiahash: route.query.ecjiahash
		})

		if (roles.status == 'success') {
			localStorage.setItem('token', roles.data)
			store.state.token = roles.data
			axios.defaults.headers.common['token'] = roles.data
		}
	}

	// 后续开发预留
	// await uni.getEnv(function(res){
	//     console.log(JSON.stringify(res))
	//     if(res.plus || res.miniprogram){
	//         uni.redirectTo({
	//             url:'../../pages/goodsDetail/goodsDetail?id=' + that.goods_id
	//         })
	//     }
	// })

	if (route.meta.requireAuth) { // 判断该路由是否需要登录权限
		if (store.state.token !== null) { //判断是否登录,已登录
			//供应链权限验证
			if (route.meta.supplierAuth) {
				store.dispatch('setSupplier').then(res => {
					if (res.status == 'success') {
						store.state.supplierLoading = !res.data;
					}
				})
			}
			next()
		} else {
			next({
				path: '/login',
				query: {
					redirect: route.fullPath
				}
			})
		}
	} else {
		next()
	}

	/* 路由发生变化修改页面title */
	if (route.query.title) {
		document.title = route.query.title
	} else {
		if (route.meta.title) {
			document.title = route.meta.title ? route.meta.title : window.pageTitle
		}
	}
})

//全局后置钩子
router.afterEach(async (route, from) => {
	let u = navigator.userAgent.toLowerCase();
	let url = location.origin + '/mobile/#' + route.fullPath;
	let configUrl = window.location.href.split('#')[0];
	let configData = JSON.parse(sessionStorage.getItem('configData')); //获取后台配置
	let userRegion = JSON.parse(localStorage.getItem('userRegion')); //获取定位地址

	//全局微信分享
	wxShare.initConfig(configUrl)

	//获取定位地址
	if (!userRegion) {
		// navigator.geolocation.getCurrentPosition(async function(postion){
		//     //谷歌定位到
		//     let lat = postion.coords.latitude;
		//     let lng = postion.coords.longitude;
		//     let position = await store.dispatch('setPosition',{
		//         lat:lat,
		//         lng:lng
		//     });
		//     curPosition(postion,lat,lng)
		// },async function(err){
		let lat = 31.23037;
		let lng = 121.4737;
		let position = await store.dispatch('setPosition');
		//谷歌定位不到默认定位到后台填写地址
		curPosition(position, lat, lng)
		// })
	}

	//获取shopConfig后台配置
	if (!configData) {
		axios.get(`${window.ROOT_URL}api/shop/config`).then(({
			data: {
				data
			}
		}) => {
			let title = route.query.title ? route.query.title : (route.meta.title ? route.meta.title : data.shop_title);
			wxShare.share({
				title: title,
				desc: route.meta.desc || data.shop_desc,
				link: url,
				imgUrl: route.meta.imgUrl || data.wap_logo
			})

			sessionStorage.setItem('configData', JSON.stringify(data));
		})
	} else {
		let title = route.query.title ? route.query.title : route.meta.title;
		wxShare.share({
			title: title,
			desc: route.meta.desc || configData.shop_desc,
			link: url,
			imgUrl: route.meta.imgUrl || configData.wap_logo
		})
	}

	// if(u.match(/MicroMessenger/i) == 'micromessenger'){
	//     if(route.path !== location.pathname){
	//         location.replace(url)
	//     }
	// }

	if (route.path !== '/') {
		if (route.name !== 'shopHome' && from.name !== 'goods') document.body.scrollTop = 0
	} else {
		Vue.nextTick(() => {
			document.body.scrollTop = 0
		})
	}
})

function curPosition(position, lat, lng) {
	let data = position.data

	let itemsBak = {
    province:{ id:data.province_id,name:data.province },
    city:{ id:data.city_id,name:data.city },
    district:{ id:data.district_id, name:data.district},
    street: {id:data.street_id || '',name:data.street || ''},
    postion:{ lat:lat,lng:lng }
  }

  itemsBak.regionSplic = `${data.province} ${data.city} ${data.district} ${data.street}`;

	//设置本地地区存储
	localStorage.setItem('userRegion', JSON.stringify(itemsBak))
	store.state.userRegion = itemsBak
}

export default router
