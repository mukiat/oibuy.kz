<template>
	<div class="auction tab-colse " >
		<section class="filter_tab">
			<div class="pro_filter_items dis-box">
				<div class="item" :class="{'active':filter.sort === 'goods_id'}" @click="handleFilter('goods_id')">
					<span>{{$t('lang.default')}}</span>
				</div>
				<div class="item" :class="[{'active':filter.sort === 'sales_volume','a-change':filter.order === 'ASC' && filter.sort === 'sales_volume'}]"
				    @click="handleFilter('sales_volume',filter.order)">
					<span>{{$t('lang.change_amount')}}</span>
					<i class="iconfont icon-xiajiantou"></i>
				</div>
				<div class="item" :class="[{'active':filter.sort === 'exchange_integral','a-change':filter.order === 'ASC' && filter.sort === 'exchange_integral'}]"
				    @click="handleFilter('exchange_integral',filter.order)">
					<span>{{$t('lang.integral_value')}}</span>
					<i class="iconfont icon-xiajiantou"></i>
				</div>
			</div>
		</section>
		<div class="goods-li" v-if="exchangeIndexData!=''" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300"> 
			<router-link :to="{name:'exchange-detail',params:{id:item.goods_id}}" class="show bg-color-write li" v-for='(item,index) in exchangeIndexData'
			    :key="index">
				<div class="left">
					<img v-if="item.goods_thumb" class="img" :src="item.goods_thumb" />
					<img v-else class="img" src="../../../assets/img/not_goods.png" />
				</div>
				<div class="right">
						<h4 class="f-04 color-3 twolist-hidden m-top02">{{item.goods_name}}</h4>
						<div class="cont auction dis-box">
							<div class="box-flex">
								<div class="f-06 color-red box-flex m-top04">
									<span class="f-02 color-red">{{$t('lang.integral')}}</span>
									<span  v-html="item.exchange_integral"></span>
									
								</div>
								<div class="f-02 color-9 m-top02">{{$t('lang.label_market_price')}}
									<span v-html="item.market_price"></span>
								</div>
							</div>
							<div class="m-top10">
								<span class="min-btn tag-gradients-color color-white br-5 f-03">{{$t('lang.immediately_change')}}</span>
							</div>
						</div>
					</div>
			</router-link>
		</div>
		<div v-else>
			<NotCont/>
		</div>
		<CommonNav/>
		<template v-if="loading">
			<van-loading type="spinner" color="black" />
		</template>
	</div>
</template>
<script>
	import { mapState } from 'vuex'
	import {
		Toast,
		Loading,
		Waterfall
	} from 'vant'

	import CommonNav from '@/components/CommonNav'
	import NotCont from '@/components/NotCont'
	import arrRemove from '@/mixins/arr-remove'

	export default {
		name: "exchange",
		components: {
			CommonNav,
			NotCont,
			[Toast.name]: Toast,
			[Loading.name] : Loading
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
				active: 0,
				filter: {
					sort: 'goods_id',
					order: 'DESC',
				},
			};
		},
		//初始化加载数据
		created() {
			this.goodsList(this.filter.sort, this.filter.order);
		},
		computed: {
			exchangeIndexData:{
				get(){
					return this.$store.state.other.exchangeIndexData
				},
				set(val){
					this.$store.state.other.exchangeIndexData = val
				}
			}
		},
		methods: {
			//数据分页
			loadMore(){
				setTimeout(() => {
					this.disabled = true
					if(this.page * this.size == this.exchangeIndexData.length){
						this.page ++
						this.goodsList(this.filter.sort, this.filter.order)	
					}
				},200);
			},
			goodsList(sort, order) {
				this.$store.dispatch({
					type: 'setExchangeIndex',
					children: 0,
					min: 0,
					max: 0,
					size:this.size,
					page:this.page,
					sort: sort,
					order: order,
				});
			},
			handleFilter(val, order) {
				this.page = 1
				if (order && this.filter.sort == val) {
					this.filter.order = order == 'DESC' ? 'ASC' : 'DESC'
				}
				this.filter.sort = val;
				this.$store.state.other.exchangeIndexData=[]
				this.goodsList(this.filter.sort, this.filter.order);
			}
		},
		watch:{
			exchangeIndexData(){
				if(this.page * this.size == this.exchangeIndexData.length){
					this.disabled = false
					this.loading = true
				}else{
					this.loading = false
				}

				this.exchangeIndexData = arrRemove.trimSpace(this.exchangeIndexData)
			}
		}
	};
</script>
