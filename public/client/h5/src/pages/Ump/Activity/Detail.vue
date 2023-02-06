<template>
	<div class="activity-datail">
		<img v-if="activityShowsData.activity_thumb!=''" class="img" :src="activityShowsData.activity_thumb" />
		<img v-else class="img" src="../../../assets/img/not_goods.png" />
		<div class="dis-box title padding-all bg-color-write">
			<div>
				<span class="f-02 tag-gradients-color">{{activityShowsData.act_type}}</span>
			</div>
			<div class="box-flex f-04 color-3 tag-cont" >{{activityShowsData.act_name}}</div>
		</div>
		<div class="m-top10 rule-box bg-color-write">
			<van-cell-group class="common-cell">
				<van-cell :title="$t('lang.activity_rules')" />
			</van-cell-group>
			<ul class="padding-all">
				<li>{{$t('lang.label_activity_time')}}
					<span class="color-9">{{activityShowsData.start_time}}{{$t('lang.zhi')}}{{activityShowsData.end_time}}</span>
				</li>
				<li>{{$t('lang.label_min_amount')}}
					<span class="color-3">{{activityShowsData.min_amount}}</span>
				</li>
				<li>{{$t('lang.label_user_rank')}}
					<span class="color-3">{{activityShowsData.user_rank}} </span>
				</li>
				<li>{{$t('lang.label_activity_type')}}
					<span class="color-red">{{activityShowsData.activity_name}} </span>
				</li>
				<li>{{$t('lang.label_act_range_type')}}
					<span class="color-289">{{activityShowsData.act_range}}</span>
				</li>
			</ul>
		</div>
		
		<!-- <div class="m-top10 act_catagory">
			<van-cell-group class="common-cell">
				<van-cell title="优惠分类" />
			</van-cell-group>
			<div class="catagory-list">
				<div class="item" v-for=""></div>
			</div>
		</div> -->

		<div class="m-top10" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
			<van-cell-group class="common-cell">
				<van-cell :title="$t('lang.activity_goods')" />
			</van-cell-group>
			<div class="goods-li">
				<router-link :to="{name:'goods', params:{id:item.goods_id}}" class="show bg-color-write li  p-r" v-for="(item,index) in activityGoodsData"
				    :key="index">
					<div class="left">
						<img class="img" :src="item.goods_thumb" />
					</div>
					<div class="right">
						<h4 class="f-05 color-3 twolist-hidden m-top02">{{item.goods_name}}</h4>
						<div class="dis-box cont auction">
							<div class="f-06 color-red box-flex m-top10" ><span v-html="item.shop_price_formated"></span>
								<del class="f-02 color-9 p-l05" v-html="item.market_price_format"></del>
							</div>
						</div>
						<div class="cart p-a tag-gradients-color">
							<i class="iconfont icon-cart color-white"></i>
						</div>
					</div>
				</router-link>
			</div>
			<div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
			<template v-if="loading">
				<van-loading type="spinner" color="black" />
			</template>
		</div>

		<div v-if="activityShowsData.gift!=''">
			<van-cell-group class="common-cell">
				<van-cell :title="$t('lang.complimentary')" />
			</van-cell-group>
			<div class="goods-li">
				<router-link :to="{name:'goods', params:{id:item.id}}" class="bg-color-write li active p-r" v-for="(item,index) in activityShowsData.gift"
				    :key="index">
					<div class="left">
						<img class="img" :src="item.thumb" />
					</div>
					<div class="right">
						<h4 class="f-05 color-3 twolist-hidden">{{item.name}}</h4>
						<div class="dis-box cont">
							<span class="f-06 color-red" v-html="item.price_formated"></span>
						</div>
					</div>
				</router-link>
			</div>
		</div>

		<CommonNav/>
	</div>
</template>
<script>
import { mapState } from 'vuex'
import {
	Cell,
	CellGroup,
	Toast,
	Waterfall,
	Loading
} from 'vant'

import commonGet from '@/mixins/common-get'
import CommonNav from '@/components/CommonNav'
import arrRemove from '@/mixins/arr-remove'

export default {
	name: "activity-detail",
	components: {
		CommonNav,
		[Toast.name]: Toast,
		[Cell.name]: Cell,
		[CellGroup.name]: CellGroup,
		[Loading.name] : Loading
	},
	directives: {
		WaterfallLower: Waterfall('lower')
	},
	data() {
		return {
			disabled:false,
		  	loading:true,
			page:1,
			size:10,
			footerCont:false,
			actRangeShow:true
		};
	},
	created() {
		//loading
		commonGet.loadingData()

		//初始化加载数据
		setTimeout(() => {
			this.activityShow()
		},600)
	},
	computed: {
		...mapState({
			activityShowsData: state => state.activity.activityShowsData
		}),
		activityGoodsData:{
			get(){
				return this.$store.state.activity.activityGoodsData
			},
			set(val){
				this.$store.state.activity.activityGoodsData = val
			}
		}
	},
	methods: {
		activityShow() { //详情
			this.$store.dispatch('setActivityShow',{
				act_id: this.$route.params.act_id,
			})

			this.activityGoods()
		},
		activityGoods(page) { //列表
			if(page){
				this.page = page
				this.size = Number(page) * 10
			}

			this.$store.dispatch('setActivityGoods',{
				act_id: this.$route.params.act_id,
				page: this.page,
				size: this.size,
				sort: 0,
				order:'desc'
			})
		},
		loadMore(){ //数据分页
			setTimeout(() => {
				this.disabled = true
				if(this.page * this.size == this.activityGoodsData.length){
					this.page ++
					this.activityGoods()	
				}
			},200)
		}
	},
	watch:{
		activityGoodsData(){
			if(this.page * this.size == this.activityGoodsData.length){
				this.disabled = false
				this.loading = true
			}else{
				this.footerCont = this.page > 1 ? true : false
				this.loading = false
			}

			this.activityGoodsData = arrRemove.trimSpace(this.activityGoodsData)
		}
	}
}
</script>
