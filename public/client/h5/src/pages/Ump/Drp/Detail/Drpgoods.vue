<template>
	<div v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="200">
		<div class="goods-li" v-if="drpGoodsList.length > 0">
			<div class="show bg-color-write li" v-for='(item,index) in drpGoodsList' :key="index" @click="drpGoodsHref(item.url)">
				<div class="left">
					<img class="img" :src="item.goods_thumb" />
				</div>
				<div class="right">
					<h4 class="f-05 color-3 twolist-hidden">{{item.goods_name}}</h4>
					<div class="dis-box cont">
						<div class="f-06 color-red box-flex" v-html="item.shop_price_formated"></div>
					</div>
					<div class="f-02 m-top02 drp-commission-tag dis-box" v-if="item.commission == 1 && item.commission_money">
						<div class="num">{{$t('lang.commission_money')}}</div>
						<div class="cont-tag">{{item.commission_money_formated}}</div>
					</div>
				</div>
			</div>
		</div>
		<template v-else>
			<NotCont></NotCont>
		</template>
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
		Toast,
		Waterfall,
		Loading
	} from 'vant'

	import CommonNav from '@/components/CommonNav'
	import NotCont from '@/components/NotCont'
	import arrRemove from '@/mixins/arr-remove'

	export default {
		name: "drp-drpgoods",
		components: {
			CommonNav,
			NotCont,
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
				disabled:false,
				loading:true,
				status:1,
				model:this.$route.query.model,
				size:10,
				page:1,
			}
		},
		created() {
			this.drpGoods()
		},
		computed: {
			drpGoodsList:{
				get(){
					return this.$store.state.drp.drpGoodsList
				},
				set(val){
					this.$store.state.drp.drpGoodsList = val	
				}
			}
		},
		mounted() {},
		methods: {
			drpGoods() {
				let parent_id = this.$route.query.parent_id ? this.$route.query.parent_id : null
				
				this.$store.dispatch('setDrpGoodsList',{
					id: this.$route.query.shop_id,
					uid: parent_id,
					page: this.page,
					size: this.size,
					status: this.status,
					model: this.model
				})
			},
			drpGoodsHref(url){
				window.location.href = url
			},
			loadMore(){
				setTimeout(() => {
					this.disabled = true
					if(this.page * this.size == this.drpGoodsList.length){
						this.page ++
						this.drpGoods()
					}
				},200)
			},
			//加载动画
			loadingData(url) {
				let that = this
				Toast.loading({
					duration: 500,
					mask: true,
					message: this.$t('lang.loading')
				}, url);
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
			}
		}
	};
</script>
