<template>
	<div class="user-default" ref="userdefault">
		<template v-if="!isget">
			<!--头部-->
			<header class="user-header-box" :class="{'user-drpshop': configData.is_show_drp == 1 && data.is_drp > 0 && (data.drp_shop.membership_status != 0 ? data.drp_shop.audit == 1 : true),'user-is-drpshop': data.is_drp > 0}">
				<div class="user-header dis-box">
					<div class="header-img">
						<router-link :to="{ name: 'profile' }">
							<img :src="data.avatar" alt="" class="img" v-if="data.avatar">
							<img src="../../../assets/img/user_default.png" alt="" class="img" v-else>
						</router-link>
					</div>
					<div class="header-admin box-flex">
						<div class="header-admin-box dis-box">
							<h4 class="box-flex" v-if="data.name !=''">{{ data.name }}</h4>
							<h4 class="box-flex" v-else>{{ data.username }}</h4>
							<!--会员码-->
							<div class="user_qrcode" @click="onUserQrcode" v-if="data">
								<i class="iconfont icon-erweima"></i>
								<span>QR-Код</span>
							</div>
						</div>
						<div class="growth">
							<div class="top">
								<p v-if="data.user_rank">{{ data.user_rank }}</p>
								<p v-if="data.drp_shop == 0 && data.user_rank_progress" @click="linkouter('growthdetails')">{{$t('lang.growth_value')}}{{ data.user_rank_progress.progress_format}}<i class="iconfont icon-more" style="font-size: 1rem;"></i></p>
								<p @click="linkouter('growthdetails')" v-else>{{$t('lang.growth_value')}}{{ data.rank_points ? data.rank_points : 0 }}<i class="iconfont icon-more" style="font-size: 1rem;"></i></p>
							</div>
							<van-progress v-if="data.drp_shop == 0 && data.user_rank_progress" color="#ffffff" :percentage=data.user_rank_progress.percentage :show-pivot=false />
						</div>
					</div>
					<div class="header-icon">
						<router-link :to="{ name: 'messagelist' }" class="message" v-if="mobile_kefu"><i class="mess" v-if="isUnread"></i><i class="iconfont icon-home-xiaoxi"></i></router-link>
						<router-link :to="{ name: 'message' }" class="message" v-else><i class="mess" v-if="isUnread"></i><i class="iconfont icon-home-xiaoxi"></i></router-link>
						<router-link :to="{ name: 'profile' }" class="set"><i class="iconfont icon-personal-shezhi"></i></router-link>
					</div>
				</div>
				<div class="itemize">
					<router-link :to="{name:'collectionGoods'}" class="itemize_collection">
						<div class="num">{{data.collect_goods_num}}</div>
						<div :class="{'user_vip': data.is_drp > 0 && data.drp_shop.membership_status != 0 ? data.drp_shop.audit == 1 : true}">{{$t('lang.my_collection')}}</div>
					</router-link>
					<router-link :to="{name:'history'}" class="itemize_collection itemizes">
						<div class="num">{{data.history_goods_num}}</div>
						<div :class="{'user_vip': data.is_drp > 0 && data.drp_shop.membership_status != 0 ? data.drp_shop.audit == 1 : true}">{{$t('lang.my_tracks')}}</div>
					</router-link>
					<router-link :to="{name:'collectionShop'}" class="itemize_collection">
						<div class="num">{{data.collect_store_num}}</div>
						<div :class="{'user_vip': data.is_drp > 0 && data.drp_shop.membership_status != 0 ? data.drp_shop.audit == 1 : true}">{{$t('lang.store_attention')}}</div>
					</router-link>
				</div>
				<div class="exclusive" :class="{'mar-vip': configData.is_show_drp == 1 && data.is_drp > 0 && data.drp_shop.membership_status != 0 ? data.drp_shop.audit == 1 : true}"
				v-if="configData.is_show_drp == 1 && data.is_drp > 0">
					<div class="left">
						<i class="icon_vip"><img src="../../../assets/img/newuser/icon-vip.png" class="img"></i>
						<span>{{pageUserDrp.vip_name ? pageUserDrp.vip_name : $t('lang.high_grade_vip')}}</span>
					</div>
					<div class="center" v-if="data.drp_shop.membership_status != 0 ? data.drp_shop.audit == 1 : true">{{pageUserDrp.vip_title_2 ? pageUserDrp.vip_title_2 : $t('lang.store_price')}}{{data.drp_affiliate.total_drp_log_money}}</div>
					<div class="center" v-else>{{pageUserDrp.vip_title ? pageUserDrp.vip_title : $t('lang.shopping_saves_money_shares')}}</div>
					<div class="right" v-if="data.drp_shop.membership_status != 0 ? data.drp_shop.audit == 1 : true">
						<router-link :to="{ name: 'drp-info' }"><span>{{pageUserDrp.my_vip ? pageUserDrp.my_vip : $t('lang.my_vip')}}</span><i class="iconfont icon-more"></i></router-link>
					</div>
					<div class="right" v-else>
						<router-link :to="{ name: 'drp-register' }"><span>{{pageUserDrp.immediately_opened ? pageUserDrp.immediately_opened : $t('lang.immediately_opened')}}</span><i class="iconfont icon-more"></i></router-link>
					</div>
				</div>
			</header>
			<!--我的推广-->
			<section class="user-function-list1" :class="{'user-function-frist1': data && configData.is_show_drp == 1 && data.is_drp > 0}">
				<div class="user-items-value">
					<div class="item">
						<p>{{ data.drp_affiliate ? data.drp_affiliate.user_child_num : data.user_affiliate.user_child_num }}</p>
						<span>{{pageUserDrp.drp_team ? pageUserDrp.drp_team : $t('lang.my_team_alt')}}</span>
					</div>
					<div class="item">
						<p>{{ data.drp_affiliate ? data.drp_affiliate.register_affiliate_money : data.user_affiliate.register_affiliate_money }}</p>
						<span>{{pageUserDrp.register_money ? pageUserDrp.register_money : $t('lang.registration_award')}}</span>
					</div>
					<div class="item tom">
						<p>{{ data.drp_affiliate ? data.drp_affiliate.total_drp_log_money : data.user_affiliate.order_affiliate_money }}</p>
						<span>{{pageUserDrp.order_money ? pageUserDrp.order_money : $t('lang.sale_reward')}}</span>
					</div>
				</div>

				<template v-if="configData.is_show_drp == 1 && data.drp_affiliate">
					<router-link :to="{name:'drp-info'}" class="broadcasting">
						<div class="icon"><i class="iconfont icon-personal-share" style="font-size:2.4rem;"></i></div>
						<div class="textit">{{pageUserDrp.my_promotion ? pageUserDrp.my_promotion : $t('lang.growth_tuiguang')}}</div>
					</router-link>
				</template>
				<template v-else>
					<router-link :to="{name:'affiliateIndex'}" class="broadcasting">
						<div class="icon"><i class="iconfont icon-personal-share" style="font-size: 2.4rem;"></i></div>
						<div class="textit">{{pageUserDrp.my_promotion ? pageUserDrp.my_promotion : $t('lang.growth_tuiguang')}}</div>
					</router-link>
				</template>
			</section>

			<!--我的订单-->
			<section class="user-function-list">
				<div class="user-item-title">
					<div class="dis-box padding-all">
						<h3 class="box-flex">{{$t('lang.my_order')}}</h3>
						<div class="user-more">
							<router-link :to="{name:'order'}" class="box-flex">{{$t('lang.all_order')}}<i class="iconfont icon-more"></i></router-link>
						</div>
					</div>
				</div>
				<ul class="user-item-list user-order-list">
					<li>
						<a href="javascript:;" class="box-flex" @click="orderLink(1)">
							<h4><i class="user-icon"><img src="../../../assets/img/user/icon-1.png" class="img"></i></h4>
							<p>{{$t('lang.order_status_01')}}</p>
							<div class="user-list-num" v-if="data.pay_count > 0">{{ data.pay_count | capitalize }}</div>
						</a>
					</li>
					<template v-if="data.is_team > 0">
						<li>
							<router-link :to="{name:'team-order'}" class="box-flex">
								<h4><i class="user-icon"><img src="../../../assets/img/user/icon-2.png" class="img"></i></h4>
								<p>{{$t('lang.order_status_02')}}</p>
								<div class="user-list-num" v-if="data.team_num > 0">{{ data.team_num | capitalize}}</div>
							</router-link>
						</li>
					</template>
					<li>
						<a href="javascript:;" class="box-flex" @click="orderLink(2)">
							<h4><i class="user-icon"><img src="../../../assets/img/user/icon-3.png" class="img"></i></h4>
							<p>{{$t('lang.order_status_03')}}</p>
							<div class="user-list-num" v-if="data.confirmed_count > 0">{{ data.confirmed_count | capitalize}}</div>
						</a>
					</li>
					<li v-if="data.shop_can_comment > 0">
						<router-link :to="{name:'comment'}" class="box-flex">
							<h4><i class="user-icon"><img src="../../../assets/img/user/icon-4.png" class="img"></i></h4>
							<p>{{$t('lang.order_status_04')}}</p>
							<div class="user-list-num" v-if="data.not_comment > 0">{{ data.not_comment | capitalize}}</div>
						</router-link>
					</li>
					<li>
						<router-link :to="{name:'refound'}" class="box-flex">
							<h4><i class="user-icon"><img src="../../../assets/img/user/icon-5.png" class="img"></i></h4>
							<p>{{$t('lang.order_status_05')}}</p>
							<div class="user-list-num" v-if="data.return_count > 0">{{ data.return_count | capitalize}}</div>
						</router-link>
					</li>
				</ul>
			</section>
			<!--我的钱包-->

			<section class="user-function-list1">
				<div class="user-items-value">
					<router-link :to="{name:'account'}" class="item items">
						<p>{{ data.user_money }}</p>
						<span>{{$t('lang.money')}}</span>
					</router-link>
					<router-link :to="{name:'bonus'}" class="item items">
						<p>{{ data.bonus }}</p>
						<span>{{$t('lang.bonus')}}</span>
					</router-link>
					<router-link :to="{name:'account'}" class="item items tom">
						<p>{{ data.pay_points }}</p>
						<span>{{$t('lang.integral')}}</span>
					</router-link>
				</div>

				<template>
					<router-link :to="{name:'account'}" class="broadcasting">
						<div class="icon red"><i class="iconfont icon-personal-money" style="font-size:2.4rem;"></i></div>
						<div class="textit">{{$t('lang.account_manage')}}</div>
					</router-link>
				</template>
				<!-- 	<template v-else>
					<router-link :to="{name:'affiliateIndex'}" class="broadcasting"><div class="popup"></div>
				<div class="icon"><i class="iconfont icon-personal-money" style="font-size: 2.4rem;"></i></div>
				<div class="textit">{{$t('lang.growth_tuiguang')}}</div></router-link>
				</template> -->
			</section>

			<section class="user-function-list" v-if="data.coupons_num!==0">
				<div class="user-item-title">
					<div class="dis-box padding-all ">
						<h3 class="box-flex">{{$t('lang.my_coupons')}}({{ data && data.coupons_num || 0 }})</h3>
						<div class="user-more">
							<router-link :to="{name:'userCoupon'}" class="box-flex">{{$t('lang.more')}}<i class="iconfont icon-more"></i></router-link>
						</div>
					</div>
				</div>
				<div class="user-item_on">
					<swiper class="swiper" :options="swiperOption" ref="mySwiper">
						<swiper-slide class="my_coupons" v-for="(item,index) in couponData" :key="index">
							<div class="my">
								<div class="my_coupons_left">
									<div class="price">
										<template v-if="item.cou_type == 5">{{$t('lang.free_shipping')}}</template>
										<template v-else>
											<template v-if="!item.order_sn">
												<template v-if="item.uc_money > 0">
													<i class="cou">{{ currency }}</i>{{ item.uc_money }}
												</template>
												<template v-else>
													<i class="cou">{{ currency }}</i>{{item.cou_money}}
												</template>
											</template>
											<template v-else>
												<i class="cou">{{ currency }}</i>{{item.order_coupons}}
											</template>
										</template>
									</div>
									<i class="reduction">{{$t('lang.man')}}{{item.cou_man}}{{$t('lang.usable')}}</i>
									<div class="platform onelist-hidden">{{ item.store_name }}</div>
								</div>
								<div class="my_coupons_right" @click="couponLink(item.cou_id)">
									{{$t('lang.store_price1')}}
								</div>
							</div>
						</swiper-slide>
					</swiper>
					
				</div>
			</section>

			<section class="user-function-list" v-for="(item,index) in customNav" :key="index">
				<div class="user-item-title">
					<div class="dis-box padding-all ">
						<h3 class="box-flex">{{item.name}}</h3>
					</div>
				</div>
				<div class="user-nav-item">
					<a href="javascript:;" class="user-item" @click="linkHref(itemChild.url)" v-for="(itemChild,indexChild) in item.child_nav" :key="indexChild">
						<label><i class="user-icon"><img :src="itemChild.pic" class="img"></i></label>
						<p>{{itemChild.name}}</p>
					</a>
				</div>
			</section>

			<!-- <ec-product-pick></ec-product-pick> -->

			<!--猜你喜欢-->
			<section class="goods-detail-guess text-center" v-if="goodsGuessList">
				<h5 class="title-hrbg"><span>{{$t('lang.guess_love')}}</span><hr></h5>
				<section class="product-list product-list-medium">
					<ProductList :data="goodsGuessList" routerName="goods" :productOuter="true"></ProductList>
					<div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
					<template v-if="loading">
						<van-loading type="spinner" color="black" />
					</template>
				</section>
			</section>
			
			<!-- 底部版权 -->
			<dsc-copyright></dsc-copyright>
		</template>
		
		<ec-filter-top :scrollState="scrollState"></ec-filter-top>

		<van-popup v-model="show" class="merchantsPopup">
			<div class="title">{{$t('lang.my_merchants')}}</div>
			<div class="content">
				<van-radio-group v-model="result">
					<van-radio name="0">{{$t('lang.merchants_store')}}</van-radio>
					<van-radio name="1">{{$t('lang.supplier_apply')}}</van-radio>
				</van-radio-group>
				<van-button type="danger" @click="onConfirm">{{$t('lang.confirm')}}</van-button>
			</div>
		</van-popup>

		<!--会员码-->
		<van-popup v-model="userQrcodeShow" class="user-qrcode-popup">
			<div class="header">
				<div class="header-warp">
					<div class="logo">
						<img :src="data.avatar" alt="" class="img" v-if="data.avatar">
						<img src="../../../assets/img/user_default.png" alt="" class="img" v-else>
					</div>
					<div class="name">{{ data.name !='' ? data.name : data.username }}</div>
					<div class="rank">{{ data.user_rank }}</div>
				</div>
				<span class="close" @click="closeUserQrcode">Х</span>
			</div>
			<div class="content">
				<template v-if="data.mobile">
					<div class="ms">Сатушыға QR-кодты көрсетіп жеңілдік алып төлем жасаңыз</div>
					<div class="qrcode" ref="qrCodeUrl"></div>
					<div class="total">
						<div class="total-item">
							<div class="text">Бонус</div>
							<div class="number">{{ data.drp_shop == 0 && data.user_rank_progress ? data.user_rank_progress.progress_format : data.rank_points }}</div>
						</div>
						<div class="total-item">
							<div class="text">Теңгерім</div>
							<div class="number">{{ data.user_money }}</div>
						</div>
						<div class="total-item">
							<div class="text">Конверт</div>
							<div class="number">{{ data.bonus }}</div>
						</div>
					</div>
				</template>
				<div class="not-mobile" v-else>
					<p>QR-кодты қолдану үшін телефон нөмірін растау керек</p>
					<button class="button" @click="bindphoneHref">Растау</button>
				</div>
			</div>
		</van-popup>
	</div>
</template>

<script>
	import { mapState } from 'vuex'

	import formProcessing from '@/mixins/form-processing'

	import {
		Radio,
		RadioGroup,
		Popup,
		Button,
		Progress,
		loading
	} from 'vant'

	import EcProductPick from '@/components/visualization/product-pick/Frontend'
	import EcFilterTop from '@/components/visualization/element/FilterTop'
	import ProductList from '@/components/ProductList'
	import arrRemove from '@/mixins/arr-remove'

	import {
	    swiper,
	    swiperSlide
	} from 'vue-awesome-swiper'

	//二维码生成
	import QRCode from 'qrcodejs2'

	export default {
		name: 'user-default',
		props: ['data'],
		mixins: [formProcessing],
		components: {
			[Popup.name]: Popup,
			[Radio.name]: Radio,
			[RadioGroup.name]: RadioGroup,
			[Button.name]: Button,
			[Progress.name]: Progress,
			[loading.name]: loading,
			EcProductPick,
			EcFilterTop,
			ProductList,
			swiper,
        	swiperSlide
		},
		data() {
			return {
				show: false,
				result: '0',
				page: 1,
				size: 10,
				scrollState:false,
				footerCont:false,
				loading:false,
				isget: false,
				currency:'￥',
				pageUserDrp: {},
				liststatus:[],
				customNav:[],
				userQrcodeShow:false,
				swiperOption:{
					notNextTick: true,
			        watchSlidesProgress: true,
			        watchSlidesVisibility: true,
			        slidesPerView: 'auto',
			        lazyLoading: true,
				},
			}
		},
		filters: {
		  capitalize: function (value) {
		    if (value>99) return '99+'
			 return value
			}
		},
		computed: {
			goodsGuessList:{
        		get() {
					return this.$store.state.shopping.goodsGuessList
				},
				set(val) {
					this.$store.state.shopping.goodsGuessList = val
				}
			},
			couponData: {
				get() {
					return this.$store.state.user.couponData
				},
				set(val) {
					this.$store.state.user.couponData = val
				}
			},
			isUnread(){
				let i = 0
				this.liststatus.forEach((res)=>{
					if(res.unread){
						i++
					}
				})
				
				return i > 0 ? true : false
			}
		},
		//初始化加载数据
		created() {
			let configData = JSON.parse(sessionStorage.getItem('configData'));
			if(configData){
			  this.currency = configData.currency_format.replace('%s', '');
			}
			
			if (this.data.is_drp > 0){
				this.getCustomText();
			}
			
			this.getCustomNav();

			this.couponClick()

			//猜你喜欢列表
			this.goodsGuessHandle(1)

			//判断是否安装im客服
			if(this.mobile_kefu){
				this.default()
			}
		},
		mounted() {
			this.$nextTick(() => {
				window.addEventListener('scroll', this.onScroll)
			})
		},
		methods: {
			//猜你喜欢
			goodsGuessHandle(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}

				this.$store.dispatch('setGoodsGuess',{
					page:this.page,
					size:this.size
				})
			},
			onScroll(e) {
				let scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;

				if (scrollTop > 800) {
					this.scrollState = true
				} else {
					this.scrollState = false
				}

				//滚动到底部300距离 瀑布流展示推荐商品
				if(scrollTop + 300 > (this.$refs.userdefault.getBoundingClientRect().height - document.documentElement.clientHeight)){
					// this.$store.dispatch('updateScrollPickOpen', {
					// 	type:true
					// })

					if(this.page * this.size == this.goodsGuessList.length){
			  			this.page ++
			  			this.goodsGuessHandle()
			  		}
				}
			},
			orderLink(val) {
				this.$router.push({
					name: 'order',
					query: {
						tab: val
					}
				})
			},
			couponClick(page) {
				if (page) {
					this.page = page
					this.size = Number(page) * 10
				}

				this.$store.dispatch('setUserCoupon', {
					page: this.page,
					size: this.size,
					type: 0
				})
			},
			couponLink(id) {
				this.$router.push({
					name: 'searchList',
					query: {
						cou_id: id
					}
				})
			},
			merchantsChange() {
				if (this.data.is_suppliers > 0) {
					this.show = true;
				} else {
					this.$router.push({
						name: 'merchants'
					})
				}
			},
			onConfirm() {
				if (this.result == 0) {
					this.$router.push({
						name: 'merchants'
					})
				} else {
					this.$router.push({
						name: 'supplier-apply'
					})
				}
			},
			//消息通知
			default (page) {
				let o = {
					page: 1,
					size: 10
				}
				this.$http.get(`${window.ROOT_URL}api/chat/sessions`, {
					params: o
				}).then(res => {
					if (res.data.status == 'success') {
						this.liststatus = res.data.data
					}
				})
			},
			// 分销管理-自定义设置数据
			async getCustomText() {
				this.isget = true;
        const {data: {status, data: {page_user_drp}}} = await this.$http.post(`${window.ROOT_URL}api/drp/custom_text`, {code: 'page_user_drp'});

				if (status == 'success') {
					this.pageUserDrp = page_user_drp || {};
				}

				this.isget = false;
			},
			// 工具栏自定义
			async getCustomNav(){
				const {data: {status, data}} = await this.$http.post(`${window.ROOT_URL}api/user/touch_nav`,{
					device:'h5'
				})

				if (status == 'success') {
					this.customNav = data
				}
			},
			linkHref(url){
				window.location.href = url
			},
			linkouter(){
				this.$router.push({
					name:'growthdetails'
				})
			},
			//会员码
			onUserQrcode(){
				this.userQrcodeShow = true
			},
			closeUserQrcode(){
				this.userQrcodeShow = false
			},
			//二维码生成
			creatQrCode() {
				this.$nextTick(()=>{
					setTimeout(()=>{
						let qrcode = new QRCode(this.$refs.qrCodeUrl, {
							text: this.data.mobile, // 需要转换为二维码的内容
							width: 150,
							height: 150,
							colorDark: '#000000',
							colorLight: '#ffffff',
							correctLevel: QRCode.CorrectLevel.H
				        })
					},500)
				})
    		},
    		bindphoneHref(){
    			this.$router.push({
    				name:'bindphone',
    				query:{
    					type:'reset'
    				}
    			})
    		}
		},
		destroyed() {
			window.removeEventListener("scroll",  this.onScroll);
		},
		watch:{
			goodsGuessList(){
				if(this.page * this.size == this.goodsGuessList.length){
					this.loading = true
				}else{
					this.loading = false
					this.footerCont = this.page > 1 ? true : false
				}

				this.goodsGuessList = arrRemove.trimSpace(this.goodsGuessList)
			},
			userQrcodeShow(){
				if(this.userQrcodeShow){
					//二维码生成
					if(this.data.mobile){
						this.creatQrCode();
					}
				}else{
					//关闭删除二维码
					this.$refs.qrCodeUrl.innerHTML = '';
				}
			}
		}
	}
</script>
<style scoped>
	.merchantsPopup {
		width: 70%;
		border-radius: .5rem;
		padding: 2rem;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
	}

	.merchantsPopup .title {
		display: flex;
		align-items: center;
		width: 100%;
		justify-content: center;
		color: #000;
		font-size: 20px;
		border-bottom: 1px solid #000000;
		padding: 0 0 2rem;
	}

	.merchantsPopup .content {
		width: 100%;
		display: flex;
		flex-direction: column;
	}

	.merchantsPopup .van-radio-group {
		display: flex;
		flex-direction: row;
		justify-content: space-between;
		padding: 3rem 0;
	}

	.merchantsPopup .van-radio {
		margin-right: 10px;
	}

	.merchantsPopup .van-radio__input {
		font-size: 18px;
	}

	.user_vip {
		color: rgba(247, 247, 247, 0.6) !important;
	}

	.mar-vip {
		background: linear-gradient(90deg, rgba(252, 231, 202, 1), rgba(241, 215, 181, 1)) !important;
	}

	.my_coupons:before {
		content: '';
		position: absolute;
		z-index: 0;
		left: 73%;
		top: -7.5px;
		width: 15px;
		height: 15px;
		border-radius: 100%;
		background: #fff;
		border: 1px solid rgba(242, 210, 210, 1);
		/* margin-top: -10px; */
	}

	.my_coupons:after {
		content: '';
		position: absolute;
		z-index: 0;
		left: 73%;
		bottom: -7.5px;
		width: 15px;
		height: 15px;
		border-radius: 100%;
		background: #fff;
		border: 1px solid rgba(242, 210, 210, 1);
		/* margin-top: -10px; */
	}

	.my_coupons:last-child{
		margin-right: 0;
	}

	.my {
		display: flex;
		width: 100%;
		height: 100%;
		border: 1px solid rgba(242, 210, 210, 1);
		border-radius: 0.5rem;
		background: rgba(251, 242, 242, 1);
	}

	.itemizes {
		position: relative;
	}

	.itemizes:after {
		content: "";
		position: absolute;
		top: 0.3rem;
		bottom: 0.3rem;
		left: 0;
		content: '';
		width: 0;
		border-left: solid rgba(255, 255, 255, 0.5) 1px;
	}

	.itemizes::before {
		content: "";
		position: absolute;
		top: 0.3rem;
		bottom: 0.3rem;
		right: 0;
		content: '';
		width: 0;
		border-left: solid rgba(255, 255, 255, 0.5) 1px;
	}

	.user-item-title {
		position: relative;
	}
	
	.user-item-title .user-more {
		padding-right: 0;
	}

	.user-item-title:after {
		content: "";
		position: absolute;
		bottom: 0;
		height: 1px;
		left: 1.1rem;
		right: 1.1rem;
		background: rgba(235, 235, 235, 1);
	}

	.user-items-value {
		position: relative;
	}

	.user-items-value:after {
		position: absolute;
		z-index: 1;
		top: 0;
		right: -10px;
		width: 15px;
		height: 100%;
		background: url(../../../assets/img/user/icon-6.png);
		background-size: 100% 100%;
		content: "";
	}

 	.message {
		position: relative;
		width: 4rem;
		height: 4rem;
		margin-right: 0.3rem;
	}
	
 	.message .mess{
		position: absolute;
		width: 0.5rem;
		height: 0.5rem;
		background-color: #fff;
		border-radius: 50%;
		right: -0.7rem;
		z-index: 999999;
	}

	.user-drpshop .message .mess{
		background-color: red;
	}

	/*会员码*/
	.user_qrcode{ position: absolute; color: #FFF; background: rgba(0, 0, 0, 0.1); font-size: 1.2rem; padding: .25rem .8rem; display: flex; flex-direction: row; justify-content: center; align-items: center; border-radius: 2rem 0 0 2rem; right: -1.7rem; top: 0;}
	.user_qrcode .iconfont{ height: 14px; }
	.user-header-box.user-drpshop .user_qrcode{ background: linear-gradient(90deg, rgba(235, 212, 181, 0.2), rgba(203, 169, 128, 0.2)); }
	.user-header-box.user-drpshop .user_qrcode,
	.user-header-box.user-drpshop .user_qrcode .iconfont{ color: #E9D1B2; }
	.user-qrcode-popup{ width: 80%; overflow: visible; background:transparent;}
	.user-qrcode-popup .header{ background: url(../../../assets/img/userqrcodebg.png) no-repeat; background-size: cover; position: relative; border-radius: 2rem 2rem 0 0;}
	.user-qrcode-popup .header-warp{ text-align: center; padding: 2.5rem 0 1rem;}
	.user-qrcode-popup .header-warp .logo{ width: 5rem; height: 5rem; position: absolute; top: -2.5rem; left: calc(50% - 2.5rem);border:3px solid #ec5857; border-radius: 50%; overflow: hidden;}
	.user-qrcode-popup .header-warp .logo{ border-radius: 50%; }
	.user-qrcode-popup .header-warp .name{ color: #fff; font-size: 1.5rem;}
	.user-qrcode-popup .header-warp .rank{ padding: 0 1rem; background:rgba(0, 0, 0, 0.1); color: #fff; border-radius: 2rem; display: inline-block; height: 1.8rem; line-height: 1.8rem; font-size: 1.1rem; margin-top: .5rem;}
	.user-qrcode-popup .close{ position: absolute; right: 1rem; top: 1rem; color: #fff; font-size: 1.2rem; }
	.user-qrcode-popup .content{ background: #fff; padding: 2rem 2rem 3rem; font-size: 1.2rem; border-radius: 0 0 2rem 2rem;}
	.user-qrcode-popup .content .ms{ text-align: center; }
	.user-qrcode-popup .content .total{ display: flex; flex-direction: row; margin-top: 2rem;}
	.user-qrcode-popup .content .total .total-item{ width: 33.3%; text-align: center; position: relative;}
	.user-qrcode-popup .content .total .total-item .number{ margin-top: 1rem; }
	.user-qrcode-popup .content .total .total-item:after{ position: absolute; content:""; width: 1px; background:#ecebeb; right: 0; top: .2rem; bottom: .2rem; }
	.user-qrcode-popup .content .total .total-item:last-child:after{ height: 0; }
	.user-qrcode-popup .content .qrcode{ display: flex; justify-content: center; margin: 3rem 0; height: 150px; }

	.user-qrcode-popup .not-mobile{ height: 200px; text-align: center; line-height: 50px;}
	.user-qrcode-popup .not-mobile p{ font-size: 1.5rem; padding-top: 50px;}
	.user-qrcode-popup .not-mobile .button{ background:#eb5354; color: #fff; height: 2.8rem; line-height: 2.8rem; text-align: center; border-radius: 2rem; padding: 0 2.5rem; font-size: 1.3rem;}
</style>
