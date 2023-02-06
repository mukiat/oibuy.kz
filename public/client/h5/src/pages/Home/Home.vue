<template>
	<div class='home' ref="home">
		<app-down :configData="configData" @localShow="localShow" v-if="configData && configData.wap_index_pro == 1"></app-down>
		<component v-for="(item,index) in modules" :key="index" :is="'ec-'+item.module" :data="item.data" :preview="false"
		 :modules-index="index" :shop-id="bStore" v-if="item.module != 'live'" :scrollFixed="scrollFixed" :localShowVal="localShowVal" :scrollState="scrollState" :scrollPickOpen="scrollPickOpen" :fristBackgroundColor="fristBackgroundColor" v-show="item.isShow">
			{{ item }}
		</component>
		<ec-tab-down></ec-tab-down>
		<ec-filter-top :scrollState="scrollState"></ec-filter-top>

		<!--顶级分类页-->
		<ec-top-catalog v-if="topCategoryCatid > 0"></ec-top-catalog>

		<!--咨询客服-->
		<service @flaghanlde="prentflaghanlde" v-if="configData && configData.consult.consult_set_state == 1" :consult="configData.consult"></service>

		<!--天降红包-->
		<van-popup v-model="bonusShow" class="bonus-show" v-if="bonusData" style="width: 80%;">
			<a :href="bonusData.ad_link"><img :src="bonusData.popup_ads" class="img" v-if="bonusData.popup_ads" /></a>
		</van-popup>

		<!--分享海报-->
		<van-popup v-model="serviceShow" class="bonus-show" style="width: 80%;">
			<img src="../../assets/img/video/close.png" class="close" @click="handelClose('service')" />
			<img :src="configData.consult.consult_share_img" class="img" v-if="configData && configData.consult.consult_share_img" />
			<div class="confirm-save">{{$t('lang.picture_to_save')}}<i></i></div>
		</van-popup>
	</div>
</template>

<script>
	//node library
	import url from 'url'
	import qs from 'qs'

	// mapActions mapState
	import {
		mapActions,
		mapState
	} from 'vuex'

	// custom components
	import EcSlide from '@/components/visualization/slide/Frontend'
	import EcTitle from '@/components/visualization/title/Frontend'
	import EcAnnouncement from '@/components/visualization/announcement/Frontend'
	import EcNav from '@/components/visualization/nav/Frontend'
	import EcLine from '@/components/visualization/line/Frontend'
	import EcBlank from '@/components/visualization/blank/Frontend'
	import EcJigsaw from '@/components/visualization/jigsaw/Frontend'
	import EcProduct from '@/components/visualization/product/Frontend'
	import EcCoupon from '@/components/visualization/coupon/Frontend'
	import EcCountDown from '@/components/visualization/count-down/Frontend'
	import EcButton from '@/components/visualization/button/Frontend'
	import EcSearch from '@/components/visualization/search/Frontend'
	import EcStore from '@/components/visualization/store/Frontend'
	import EcShopSigns from '@/components/visualization/shop-signs/Frontend'
	import EcShopMenu from '@/components/visualization/shop-menu/Frontend'
	import EcTabDown from '@/components/visualization/tab-down/Frontend'
	import EcLive from '@/components/visualization/live/Frontend'
	import EcFilterTop from '@/components/visualization/element/FilterTop'
	import AppDown from '@/components/visualization/element/AppDown'
	import CommonNav from '@/components/CommonNav'
	import service from "@/components/customer-service/customer-service";

	//新增可视化分类组件
	import EcCategoryNav from '@/components/visualization/category-nav/Frontend'
	import EcVisualTeam from '@/components/visualization/visual-team/Frontend'
	import EcVisualAdv from '@/components/visualization/visual-adv/Frontend'
	import EcProductPick from '@/components/visualization/product-pick/Frontend'

	//新增顶级分类组件
	import EcTopCatalog from '@/components/topcatalog/TopCatalog'

	// third party components
	import {
		Button
	} from 'element-ui'

	import {
		Toast,
		Popup
	} from 'vant'

	//data-conversion
	import {
		conversion
	} from '@/assets/js/data-conversion'

	export default {
		name: 'home',
		components: {
			"EcButton": Button,
			EcSlide,
			EcTitle,
			EcAnnouncement,
			EcNav,
			EcLine,
			EcBlank,
			EcJigsaw,
			EcProduct,
			EcCoupon,
			EcCountDown,
			EcButton,
			EcSearch,
			EcStore,
			EcShopSigns,
			EcShopMenu,
			EcTabDown,
			EcLive,
			EcFilterTop,
			EcCategoryNav,
			EcVisualTeam,
			EcVisualAdv,
			EcTopCatalog,
			EcProductPick,
			AppDown,
			CommonNav,
			service,
			[Popup.name]: Popup,
		},
		data() {
			return {
				fromId: '',
				share: '',
				shop_title: '',
				initial: '',
				bonusShow: false,
				serviceShow: false,
				bonusData: '',
				configData:"",
				scrollState:false,
				scrollFixed:false,
				jumpHeight:100,
				localShowVal:"",
				scrollTop: 0,
      			docTitle: ''
			}
		},
		created() {
			let load = ''
			if (this.bType == 'index') {
				load = localStorage.getItem('modules') ? 0 : ''
			} else {
				load = this.$route.params.id
			}

			let modulesType = JSON.parse(localStorage.getItem('modulesType'));
			if (modulesType == null) {
				this.setModulesType();
			}

			this.init(load)
		},
		activated () {
			if (this.docTitle) {
				document.title = this.docTitle
			}
			if (this.scrollTop > 0) {
				window.scrollTo({
					top: this.scrollTop,
					behavior: "instant"
				})
			}
			var passiveSupported = false;

			try {
				var options = Object.defineProperty({}, "passive", {
					get: function() {
						passiveSupported = true;
					}
				});

				window.addEventListener('scroll', this.handleScroll, passiveSupported ? { passive: true } : false);
			} catch(err) {
				window.addEventListener('scroll', this.handleScroll);
			}
		},
		deactivated () {
		    window.removeEventListener('scroll', this.handleScroll);
		},
		mounted() {
			if (this.bType == 'index') {
				this.shopConfig()
			} else {
				let configData = JSON.parse(sessionStorage.getItem('configData'));
				this.configData = JSON.parse(sessionStorage.getItem('configData'));
				this.$wxShare.share({
					title: this.$route.query.title ? this.$route.query.title : '',
					desc: '',
					link: window.location.href,
					imgUrl: configData.wap_logo ? configData.wap_logo : ''
				})
			}

			this.$nextTick(() => {
	    		window.addEventListener('scroll', this.onScroll)
	    	})
		},
		methods: {
			handleScroll(e) {
				this.scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
			},
			prentflaghanlde(flag) {
				this.serviceShow = true
			},
			handelClose(o) {
				if (o == 'service') {
					this.serviceShow = false
				}
			},
			init(id) {
				this.getModule({
					ru_id: this.bStore,
					type: this.bType,
					device: window.shopInfo.device	// device 设备  h5 app wxapp
				})

				this.$store.dispatch('updateIsShow', {
					type: true,
					cat_id: 0
		        });
			},
			setModulesType() {
				let newModulesType = {
					type: window.shopInfo.type,
					name: this.bType
				}

				localStorage.setItem('modulesType', JSON.stringify(newModulesType))
			},
			getModule(o) {
				this.modules = []
				this.$http.post(`${window.ROOT_URL}api/visual/default`, qs.stringify(o)).then(({
					data
				}) => {
					if (data.data) {
						this.$store.dispatch('setModuleInfo', {
							id: data.data,
							type: o.type,
							device: window.shopInfo.device	// device 设备  h5 app wxapp
						})
					} else {
						if(data.errors){
							Toast({
								message: data.errors.message,
								duration: 1000
							});
						}
						this.$router.push({
							name: 'home'
						})
					}
				})
			},
			shopConfig() {
				let configData = JSON.parse(sessionStorage.getItem('configData'));
				this.configData= JSON.parse(sessionStorage.getItem('configData'));
				if (!configData) {
					this.$http.get(`${window.ROOT_URL}api/shop/config`).then(({
						data: {
							data
						}
					}) => {
						//首页天降红包广告
						this.bonusData = data.bonus_ad;

						//单独设置微信分享信息
						this.$wxShare.share({
							title: data.shop_title,
							desc: data.shop_desc,
							link: window.location.href,
							imgUrl: data.wap_logo
						})

						//设置title
						document.title = data.shop_title
						sessionStorage.setItem('configData', JSON.stringify(data));
						this.configData= JSON.parse(sessionStorage.getItem('configData'));
					})
				} else {
					//单独设置微信分享信息
					this.$wxShare.share({
						title: configData.shop_title,
						desc: configData.shop_desc,
						link: window.location.href,
						imgUrl: configData.wap_logo
					})

					//设置title
					document.title = configData.shop_title
				}
			},
			onScroll(e) {
				let scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
				if(scrollTop > this.jumpHeight){
					this.scrollFixed = true
				}else{
					this.scrollFixed = false
				}

				if (scrollTop > 800) {
       	 			this.scrollState = true
		        } else {
		          	this.scrollState = false
		        }

		        //滚动到底部300距离 瀑布流展示推荐商品
		        if(scrollTop + 1000 > this.$refs.home.getBoundingClientRect().height){
		        	this.$store.dispatch('updateScrollPickOpen', {
		        		type:true
		        	})
		        }
			},
			localShow(e){
				this.localShowVal = e
			}
		},
		computed: {
			...mapState({
				searchStoreData: state => state.shopInfo.searchStoreData,
				lineData: state => state.shopInfo.lineData,
				titleData: state => state.shopInfo.titleData,
				productData: state => state.shopInfo.productData,
				topCategoryCatid: state => state.topCategoryCatid,
				scrollPickOpen: state => state.scrollPickOpen
			}),
			bStore() {
				return 0
			},
			bType() {
				return 'index'
			},
			bMoudles() {
				return 0 < this.modules.length ? true : false
			},
			modules: {
				get() {
					return this.$store.state.modules
				},
				set(val) {
					this.$store.state.modules = val
				}
			},
			fristBackgroundColor(){
				let slidemodule = this.modules.find((item,index)=>{
					return item.module == "slide" && item.data.isStyleSel == '2'
				})
				
	      		return slidemodule && slidemodule.data.list.length > 0 ? slidemodule.data.list[0].bgColor : '#f34646'
	      	},
		},
		destroyed() {
			window.removeEventListener("scroll",  this.onScroll);
		},
		watch: {
			// '$route'(to, from) {
			// 	console.log(from.name == 'shopHome' || from.name == 'topicHome')
			// 	if(from.name == 'shopHome' || from.name == 'topicHome'){
			// 		this.fromId = from.params.id ? parseInt(from.params.id) : 0;
			// 		console.log(111111)
			// 		//this.init(this.fromId)
			// 	}
			// },
			bonusData() {
				if (this.bonusData && this.bonusData.open == 1) {
					this.bonusShow = true
				}
			}
		}
	}
</script>

<style lang="scss" scoped>
	.bonus-show {
		background: none;
	}

	.confirm-save {
		position: relative;
		color: #fff;
		font-size: 14px;
		width: 162px;
		height: 36px;
		text-align: center;
		line-height: 36px;
		margin: auto;
		margin-top: 10px;
	}

	.confirm-save i {
		display: inline-block;
		width: 11px;
		height: 11px;
		background: url(../../assets/img/service/up.png) no-repeat;
		background-size: 100% 100%;
		position: absolute;
		right: 19px;
		top: 13px;
	}

	.close {
		display: block;
		width: 21px;
		height: 21px;
		border-radius: 50%;
		border: 1px solid #fff;
		float: right;
		margin-bottom: 15px;
	}
</style>
