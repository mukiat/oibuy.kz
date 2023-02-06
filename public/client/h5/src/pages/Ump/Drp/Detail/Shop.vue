<template>
	<div class="con">
		<div class="drp-shop-box" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="200">
			<div class="header p-r">
				<div class="bg-img p-r">
					<img v-if="drpShopData.shop_info.shop_img" class="img" :src="drpShopData.shop_info.shop_img" />
					<img v-else class="img" src="../../../../assets/img/user-shop.png" />
					<div class="model-box"></div>
					<span class="btn-share tag-gradients-color f-03 color-white" @click="shareHandle">{{$t('lang.share')}}</span>
				</div>
				<div class="cont dis-box">
					<div class="left">
						<div class="img">
							<img v-if="drpShopData.shop_info.shop_portrait" class="img" :src="drpShopData.shop_info.shop_portrait" />
							<img v-else class="img" src="../../../../assets/img/user_default.png" />
						</div>
					</div>
					<div class="box-flex right">
						<div class="f-05 color-white admin m-top4">{{drpShopData.shop_info.shop_name}}</div>
					</div>
				</div>
			</div>
			<!--内容-->
			<van-row class="tab-common tab-shop f-05 bg-color-write text-center">
				<van-col span="8">
					<div class="item" :class="{'active':status === 1}" @click="tabNav(1)">
						<i class="iconfont icon-pailie"></i>
						<div class="f-03 m-top04">{{$t('lang.all')}}</div>
					</div>
				</van-col>
				<van-col span="8">
					<div class="item" :class="{'active':status === 2}" @click="tabNav(2)">
						<i class="iconfont icon-remenzhuanti "></i>
						<div class="f-03 m-top04">{{$t('lang.new')}}</div>
					</div>
				</van-col>
				<van-col span="8">
					<div class="item" :class="{'active':status === 3}" @click="tabNav(3)">
						<i class="iconfont icon-cuxiao1"></i>
						<div class="f-03 m-top04">{{$t('lang.promotion')}}</div>

					</div>
				</van-col>
				<!-- <van-col span="6">
					<div class="item" @click="filtrate">
						<i class="iconfont icon-caidan"></i>
						<div class="f-03 m-top04">{{$t('lang.category_filter')}}</div>
					</div>
				</van-col> -->
			</van-row>
			<div class="tabCon ">
				<div class="goods-li" v-if="drpGoodsList!=''">
					<router-link :to="{name:'goods', params:{id:item.goods_id}}" class="bg-color-write li active" v-for='(item,index) in drpGoodsList'
					    :key="index">
						<div class="left">
							<img v-if="item.goods_thumb" class="img" :src="item.goods_thumb" />
							<img v-else class="img" src="../../../../assets/img/not_goods.png" />
						</div>
						<div class="right bg-color-write">
							<h4 class="f-05 color-3 twolist-hidden"> {{item.goods_name}}</h4>
							<div class="dis-box cont">
								<div class="f-06 color-red" v-html="item.shop_price_formated"></div>
							</div>
							<div class="f-02 m-top04 drp-commission-tag dis-box" v-if="item.commission == 1">
								<div class="num">{{item.dis_commission}}%</div>
								<div class="cont-tag">{{$t('lang.dis_commission')}}</div>
							</div>
						</div>
					</router-link>
				</div>
				<div v-else>
					<NotCont/>
				</div>
			</div>
			<!--筛选-->
			<van-popup v-model="filtrateShow" position="right">
				<section class="close-filter-div j-close-filter-div">
					<div class="close-f-btn">
						<i class="iconfont icon-fanhui"></i>
						<span>{{$t('lang.close')}}</span>
					</div>
				</section>
				{{$t('lang.content')}}
			</van-popup>
		</div>
		<div class="shopping-prompt ts-2" :class="{'active':drpShareActive}" @click="shoppingPromptHandle"><img src="../../../../assets/img/fengxiang.png" /></div>
		<CommonNav :routerName="routerName">
	         <li slot="aloneNav">
				<router-link :to="{name: 'drp'}">
					<i class="iconfont icon-fenxiao"></i>
					<p>{{$t('lang.drp_center')}}</p>
				</router-link>
			</li>
	    </CommonNav>
		<template v-if="loading">
        	<van-loading type="spinner" color="black" />
    	</template>
	</div>
</template>
<script>
	import { mapState } from 'vuex'
	
	import {
		Row,
		Col,
		Popup,
		Toast,
		Loading,
		Waterfall
	} from 'vant'

	import CommonNav from '@/components/CommonNav'
	import NotCont from '@/components/NotCont'
	import arrRemove from '@/mixins/arr-remove'

	export default {
		name: "drp-shop",
		components: {
			CommonNav,
			NotCont,
			[Row.name]: Row,
			[Col.name]: Col,
			[Popup.name]: Popup,
			[Toast.name]: Toast,
			[Loading.name]: Loading,
		},
		directives: {
			WaterfallLower: Waterfall('lower')
		},
		data() {
			return {
				routerName:'drp',
				filtrateShow: false,
				status:1,
				disabled:false,
				loading:true,
				size:10,
				page:1,
				model:0,
				shop_id:this.$route.query.shop_id,
				parent_id:null,
				shop_user_id: 0,
				drpShareActive:false
			}
		},
		created() {
			this.parent_id = this.$route.query.parent_id ? this.$route.query.parent_id : null;

			this.$store.dispatch('setDrpShop',{
				shop_id:this.shop_id
			})
		},
		computed: {
			...mapState({
				drpShopData: state => state.drp.drpShopData,
			}),
			drpGoodsList:{
				get(){
					return this.$store.state.drp.drpGoodsList
				},
				set(val){
					this.$store.state.drp.drpGoodsList = val
				}
			}
		},
		methods: {
			//商品列表
			drpGoods(page) {
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}

				this.$store.dispatch({
					type: 'setDrpGoodsList',
					id: this.shop_id,
					uid: this.shop_user_id,
					size: this.size,
					page: this.page,
					status: this.status,
					model: this.model
				});
			},
			//分类切换
			tabNav(val) {
				this.status = val
				this.drpGoods(1)
			},
			//筛选
			filtrate() {
				this.filtrateShow = !this.filtrateShow
			},
			detailClick(item) {
				this.$router.push({
					name: "goods",
					params: {
						goods_id: item.goods_id
					}
				})
			},
			loadMore(){
				setTimeout(() => {
					this.disabled = true
					if(this.page * this.size == this.drpGoodsList.length){
						this.page ++
						this.drpGoods()
					}
				},200);
			},
			shareHandle(){
				this.drpShareActive = this.drpShareActive == true ? false : true
			},
			shoppingPromptHandle(){
				this.drpShareActive = false
			}
		},
		watch:{
			drpGoodsList(){
				if(this.page * this.size == this.drpGoodsList.length){
					this.disabled = false
					this.loading = true
				}else{
					this.loading = false
				}

				this.drpGoodsList = arrRemove.trimSpace(this.drpGoodsList)
			},
			drpShopData(){
				this.model = this.drpShopData.shop_info.type
				this.shop_user_id = this.drpShopData.shop_info.user_id
				this.drpGoods(1)
				//单独设置微信分享信息
                this.$wxShare.share({
					title:this.drpShopData.shop_info.shop_name,
					desc:this.drpShopData.shop_info.shop_name,
					link: `${window.ROOT_URL}/mobile#/drp/shop?shop_id=` + this.shop_id,
					imgUrl:this.drpShopData.shop_info.shop_img
				})
			}
		}
	};
</script>
