<template>
	<div class="con" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="200">
		<div class="team-order con_main">
			<div class="goods-li of-hidden" v-if="bargainMyBuyData!=''"> 
				<router-link :to="{name:'bargain-detail',params:{id:item.id}}" class="show li" v-for='(item,index) in bargainMyBuyData' :key="index">
					<div class="left">
						<img class="img" :src="item.goods_thumb" />
					</div>
					<div class="right bg-color-write">
						<h4 class="f-05 color-3 twolist-hidden"> {{item.goods_name}}</h4>
						<div class="color-9 m-top02 f-02">
							<i class="iconfont icon-renshu"></i>{{item.total_num}}{{$t('lang.participation_number')}}</div>
						<div class="color-3 f-02 m-top10 dis-box">
							<div class="box-flex color-9">
								<em>{{$t('lang.label_original_price')}}</em>
								<em class="color-red" v-html="item.shop_price"></em>{{$t('lang.yuan')}}
							</div>
							<div class="box-flex">
								<em class="fr color-9 f-02">{{$t('lang.label_base_price')}}
									<em class="color-red" v-html="item.target_price"></em>{{$t('lang.yuan')}}</em>
							</div>
						</div>
					</div>
				</router-link>
			</div>
			<div v-else>
				<NotCont/>
			</div>
			<BargainTabbar />
		</div>
		<CommonNav/>
		<div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
		<template v-if="loading">
	    	<van-loading type="spinner" color="black" />
	    </template>
	</div>
</template>
<script>
	import { mapState } from 'vuex'
	import {
		Loading,
		Waterfall
	} from "vant"
	
	import CommonNav from '@/components/CommonNav'
	import NotCont from '@/components/NotCont'
	import BargainTabbar from './BargainTabbar'
	import arrRemove from '@/mixins/arr-remove'

	export default {
		name: "bargain-mylist",
		components: {
			CommonNav,
			BargainTabbar,
			NotCont,
			[Loading.name]: Loading,
		},
		directives: {
			WaterfallLower: Waterfall('lower')
		},
		data() {
			return {
				disabled:false,
			  	loading:true,
				size:10,
				page:1,
				footerCont:false
			};
		},
		created() {
			this.mylist()
		},
		computed: {
			bargainMyBuyData:{
				get(){
					return this.$store.state.bargain.bargainMyBuyData
				},
				set(val){
					this.$store.state.bargain.bargainMyBuyData = val
				}
			}
		},
		methods: {
			mylist(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}

				this.$store.dispatch({
					type: 'setBargainMyBuy',
					page: this.page,
					size: this.size,
				})
			},
			loadMore(){
				setTimeout(() => {
					this.disabled = true
					if(this.page * this.size == this.bargainMyBuyData.length){
						this.page ++
						this.mylist()
					}
				},200);
			},
		},
		watch:{
			bargainMyBuyData(){
				if(this.page * this.size == this.bargainMyBuyData.length){
					this.disabled = false
					this.loading = true
				}else{
					this.loading = false
					this.footerCont = this.page > 1 ? true : false
				}

				this.bargainMyBuyData = arrRemove.trimSpace(this.bargainMyBuyData)
			}
		}
	};
</script>
