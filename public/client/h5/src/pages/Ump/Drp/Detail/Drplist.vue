<template>
	<div v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="200">
		<div class="goods-li" v-if="drpGoodsList.length > 0">
			<div class="show bg-color-write li" v-for='(item,index) in drpGoodsList' :key="index">
				<div class="left">
					<router-link :to="{name:'goods', params:{id:item.goods_id}}"><img class="img" :src="item.goods_thumb" /></router-link>
				</div>
				<div class="right">
					<h4 class="f-05 color-3 twolist-hidden"><router-link :to="{name:'goods', params:{id:item.goods_id}}">{{item.goods_name}}</router-link></h4>
					<div class="dis-box cont">
						<div class="f-06 color-red box-flex" v-html="item.shop_price_formated"></div>
					</div>
					<div class="f-02 m-top02 drp-commission-tag flex flex-h">
						<div class="flex flex-h flex1" v-if="item.commission == 1">
							<div class="num">{{item.dis_commission}}%</div>
							<div class="cont-tag">{{$t('lang.dis_commission')}}</div>
						</div>
						<div class="btn-dy" :class="{'btn-disabled': item.drp_type}" @click="submitBtn(item.goods_id,item.drp_type)">{{ item.drp_type == false ? $t('lang.want_represent') : $t('lang.cancel_represent') }}</div>
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
		name: "drp-drplist",
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
				disabled:false,
				loading:true,
				size:10,
				page:1,
				keyword: this.$route.query.keyword ? this.$route.query.keyword : ''
			}
		},
		created() {
			this.drpGoods()
		},
		computed: {
			drpGoodsList:{
				get(){
					return this.$store.state.drp.dapList
				},
				set(val){
					this.$store.state.drp.dapList = val	
				}
			}
		},
		mounted() {},
		methods: {
			drpGoods() {
				this.$store.dispatch('setDrpList',{
					page:this.page,
					size:this.size,
					keywords: this.keyword,
					cat_id: this.$route.query.id,
				})
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
			submitBtn(id){
				this.$store.dispatch('setDrpGoodsAdd',{
					goods_id: id,
				})
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
