<template>
	<div class="con">
		<template v-if="drpdata.error == 0 || drpdata.audit == 1">
			<div class="drp-select">
				<!--店铺信息-->
				<header class="header">
					<div class="header-img">
						<router-link :to="{ name: 'drp-set' }">
							<img :src="drpdata.shop_info.user_picture" alt="" class="img" v-if="drpdata.shop_info.user_picture">
							<img src="../../../assets/img/user_default.png" alt="" class="img" v-else>
						</router-link>
					</div>
					<div class="header-time">{{$t('lang.label_setup_time')}}{{drpdata.shop_info.open_time_formated}}</div>
				</header>
				<div class="drp-content" v-if="!isLoading">
					<div class="drp-items">
						<a href="javascript:;" @click="drpgoodsLink" class="drp-item">
							<label><i class="user-icon"><img src="../../../assets/img/newuser/wd_icon1.png" class="img"></i></label>
							<p>{{pageDrpStore.drp_goods ? pageDrpStore.drp_goods : $t('lang.my_represent')}}</p>
						</a>
						<router-link :to="{name:'drp-order'}" class="drp-item">
							<label><i class="user-icon"><img src="../../../assets/img/newuser/wd_icon2.png" class="img"></i></label>
							<p>{{pageDrpStore.drp_order ? pageDrpStore.drp_order : $t('lang.represent_order')}}</p>
						</router-link>
						<a href="javascript:;" @click="drpshopLink" class="drp-item">
							<label><i class="user-icon"><img src="../../../assets/img/newuser/wd_icon3.png" class="img"></i></label>
							<p>{{pageDrpStore.drp_store_index ? pageDrpStore.drp_store_index : $t('lang.view_drp_shop')}}</p>
						</a>
						<router-link :to="{name:'drp-set'}" class="drp-item">
							<label><i class="user-icon"><img src="../../../assets/img/newuser/wd_icon4.png" class="img"></i></label>
							<p>{{pageDrpStore.drp_store_manage ? pageDrpStore.drp_store_manage : $t('lang.drp_set')}}</p>
						</router-link>
					</div>
					<div class="btn-bar btn-bar-radius" v-if="drpdata.shop_info.type > 0">
            <button @click="userDrp" class="btn btn-lg-red" v-if="drpdata.shop_info.type == 1">{{$t('lang.select_represent_class')}}</button>
            <button @click="userDrp" class="btn btn-lg-red" v-else>{{$t('lang.select_represent_goods')}}</button>
        	</div>
				</div>

				<!--热门商品-->
				<section class="drp-list" v-if="drpGoodsList!=''" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="200">
					<div class="drp-list-title">
						<i><img src="../../../assets/img/newuser/ico_tit_left.png" class="img" /></i>
						<h3>{{$t('lang.hot_goods')}}</h3>
						<i><img src="../../../assets/img/newuser/ico_tit_right.png" class="img" /></i>
					</div>
					<div class="goods-li">
						<div class="li" v-for='(item,index) in drpGoodsList' :key="index" @click="drpGoodsHref(item.url)">
							<div class="left">
								<div class="img-box">
									<img v-if="item.goods_thumb" class="img" :src="item.goods_thumb" />
									<img v-else class="img" src="../../../assets/img/not_goods.png" />
								</div>
							</div>
							<div class="right">
								<div class="cont">
									<h4 class="twolist-hidden">{{item.goods_name}}</h4>
									<div class="shop-price" v-html="item.shop_price_formated"></div>
								</div>
								<div class="drp-commission-tag" v-if="item.commission == 1 && item.commission_money">
									<div class="num">{{$t('lang.commission_money')}}</div>
									<div class="cont-tag">{{item.commission_money_formated}}</div>
								</div>
							</div>
						</div>
					</div>
				</section>

				<CommonNav :routerName="routerName">
			         <li slot="aloneNav">
						<router-link :to="{name: 'drp'}">
							<i class="iconfont icon-fenxiao"></i>
							<p>{{$t('lang.drp_center')}}</p>
						</router-link>
					</li>
			    </CommonNav>

				<template v-if="loading"><van-loading type="spinner" color="black" /></template>
			</div>
		</template>
		<template v-else>
			<div class="ectouch-notcont">
				<div class="img">
					<img class="img" src="../../../assets/img/no_content.png" />
				</div>
				<template v-if="viewStatus == 1">
					<template v-if="viewAudit == 0">
						<span class="cont">{{$t('lang.drp_status_propmt_1')}}</span>
					</template>
					<template v-if="viewAudit == 2">
						<span class="cont">{{$t('lang.drp_status_propmt_7')}}</span>
					</template>
				</template>
				<template v-if="viewStatus == 2">
					<span class="cont">{{$t('lang.drp_status_propmt_3')}}<router-link :to="{name:'drp-register'}" class="color-red">{{$t('lang.to_apply')}}</router-link></span>
				</template>
				<template v-else-if="viewStatus == 3">
					<span class="cont">{{$t('lang.drp_status_propmt_8')}} <router-link :to="{name:'drp-info'}" class="color-red">{{$t('lang.back')}}</router-link></span>
				</template>
			</div>
		</template>

		 <!--初始化loading-->
        <DscLoading :dscLoading="dscLoading"></DscLoading>
	</div>
</template>
<script>
	import { mapState } from 'vuex'
	import {
		Button,
		Toast,
		Waterfall,
		Loading,
	} from 'vant'

	import CommonNav from '@/components/CommonNav'
	import arrRemove from '@/mixins/arr-remove'
	import DscLoading from '@/components/DscLoading'

	export default {
		name: "drp",
		components: {
			CommonNav,
			[Button.name]: Button,
			[Toast.name]: Toast,
			[Loading.name]:Loading,
			DscLoading
		},
		directives: {
			WaterfallLower: Waterfall('lower')
		},
		data() {
			return {
				loading:false,
				viewStatus:0,
				disabled:false,
				loading:true,
				dscLoading:true,
				status:4,
				size:10,
				page:1,
				routerName:'drp',
				pageDrpStore: {},
				isLoading: false
			};
		},
		//初始化加载数据
		async created() {
			await this.getCustomText();
			this.$store.dispatch('setMyDrp')
		},
		//计算属性
		computed: {
			...mapState({
				drpdata: state => state.drp.myDrpData
			}),
			drpGoodsList:{
				get(){
					return this.$store.state.drp.drpGoodsList
				},
				set(val){
					this.$store.state.drp.drpGoodsList = val
				}
			},
			model(){
				if(this.drpdata.shop_info){
					return this.drpdata.shop_info.type ? this.drpdata.shop_info.type : 0
				}else{
					return 0
				}
			}
		},
		methods: {
			//商品列表
			drpGoods(shop_id, user_id) {
				this.$store.dispatch('setDrpGoodsList',{
					id: shop_id,
					uid: user_id,
					size: this.size,
					page: this.page,
					status: this.status,
					model: this.model
				});
			},
			//提现金额
			Withdraw() {
				let that = this
				that.$router.push({
					name: 'drp-withdraw'
				});
			},
			drpgoodsLink(){
				this.$router.push({
					name:'drp-drpgoods',
					query:{
						shop_id:this.drpdata.shop_info.id,
						parent_id:this.drpdata.shop_info.user_id,
						model:this.model
					}
				})
			},
			drpshopLink(){
				this.$router.push({
					name:'drp-shop',
					query:{
						shop_id:this.drpdata.shop_info.id,
						parent_id:this.drpdata.shop_info.user_id
					}
				})
			},
			//加载动画
			loadingData(url) {
				let that = this
				Toast.loading({
					duration: 600,
					mask: true,
					message: this.$t('lang.loading')
				}, url);
			},
			teamClick() {
				let that = this
				that.$router.push({
					name: "drp-team",
					params: {
						user_id: that.$store.state.drp.drpData.shop_info.user_id
					}
				});
				//存储当前点击
				localStorage.setItem("viewName", "team");
			},
			//分销商品选择
			userDrp() {
				let that = this
				that.$router.push({
					name: "drp-category"
				});
			},
			loadMore(){
				setTimeout(() => {
					this.disabled = true
					if(this.page * this.size == this.drpGoodsList.length){
						this.page ++
						this.drpGoods(this.$store.state.drp.drpData.shop_info.id, this.$store.state.drp.drpData.shop_info.user_id)
					}
				},200);
			},
			drpGoodsHref(url){
				window.location.href = url
			},
			// 分销管理-自定义设置数据
			async getCustomText() {
				this.isLoading = true;
                const {data: {status, data: {page_drp_store}}} = await this.$http.post(`${window.ROOT_URL}api/drp/custom_text`, {code: 'page_drp_store'});
                if (status == 'success') {
					this.pageDrpStore = page_drp_store || {};
					this.isLoading = false;
				}
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
			drpdata(){
                setTimeout(()=>{
                    this.dscLoading = false
                },1000)

                this.viewStatus = this.drpdata.error
                this.viewAudit = this.drpdata.audit
                if(this.viewStatus == 0 || (this.viewStatus == 1 && this.viewAudit == 1)){
					this.drpGoods(this.drpdata.shop_info.id,this.drpdata.shop_info.user_id)
				}else if(this.viewStatus == 2){
                    this.$router.replace({
                        name:'drp-register'
                    });
                }
            }
		}
	};
</script>
