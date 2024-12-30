<template>
	<div class="auction tab-colse  groupbuy-box">
		<div class="search-nav">
			<div class="search dis-box">
				<div class="f-04 box-flex">
					<input class="f-03" type="text" v-model="value" placeholder="Шара аты" autocomplete="off">
				</div>
				<div class="icon-search-box" @click="onSearch"><i class="iconfont icon-sousuo1 f-05 color-7"/></div>
			</div>
		</div>
		<section class="filter_tab">
			<div class="pro_filter_items dis-box">
				<div class="item" :class="{'active':filter.sort === 'act_id'}" @click="handleFilter('act_id')">
					<span>{{$t('lang.default')}}</span>
				</div>
				<div class="item" :class="[{'active':filter.sort === 'start_time','a-change':filter.order === 'ASC' && filter.sort === 'start_time'}]"
				    @click="handleFilter('start_time',filter.order)">
					<span>{{$t('lang.start_time')}}</span>
					<i class="iconfont icon-xiajiantou"></i>
				</div>
				<div class="item" :class="[{'active':filter.sort === 'comments_number','a-change':filter.order === 'ASC' && filter.sort === 'comments_number'}]"
				    @click="handleFilter('comments_number',filter.order)">
					<span>{{$t('lang.comment_number')}}</span>
					<i class="iconfont icon-xiajiantou"></i>
				</div>
			</div>
		</section>
		<div class="goods-li groupbuy-li" v-if="groupbuyIndexData.length > 0" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
			<router-link class="show li active" :to="{name:'groupbuy-detail',params:{group_buy_id:item.group_buy_id}}" v-for='(item,index) in groupbuyIndexData' :key="index">
				<div class="left p-r">
					<span v-if="item.zhekou < 10" class="p-a btn-submit color-white groupbuy-tag f-04">{{item.zhekou}}<em class="f-02">{{$t('lang.zhe')}}</em></span>
					<img class="img" v-if="item.activity_thumb != ''" :src="item.activity_thumb" />
					<img class="img" src="../../../assets/img/not_goods.png" v-else/>
				</div>
				<div class="right bg-color-write p-r">
					<div class="time">	 
						<div v-if="item.end_date_day==1" class="cont btn-default color-white">{{$t('lang.has_ended')}}</div> 
						<div v-else class="cont tag-bg-color color-white">{{$t('lang.residue')}}{{item.end_date_day}}</div>
					</div>
					<h4 class="f-05 color-3 twolist-hidden m-top10">{{item.goods_name}}</h4>
					<div class="cont p-r">
						<div class="m-top08 dis-box">
							<div class="box-flex">
								<p class="f-06 color-red" v-html="item.price"></p>
								<div class="f-02 color-9">{{ item.cur_amount }}{{$t('lang.cur_amount')}}</div>
							</div>
							<div class="box-flex f-02 text-right activity-datail groupbuy-cart">
								<div class="cart p-a tag-gradients-color"><i class="iconfont icon-cart color-white"></i></div>
							</div>
						</div>
					</div>
				</div>
			</router-link>
		</div>
		<div v-else>
			<NotCont/>
		</div>
		<template v-if="loading">
			<van-loading type="spinner" color="black" />
		</template>
		<CommonNav/>
	</div>
</template>

<script>
	import { mapState } from 'vuex'

	import CommonNav from '@/components/CommonNav'
	import CountDown from '@/components/CountDown'
	import NotCont from '@/components/NotCont'
	import arrRemove from '@/mixins/arr-remove'

	import {
		Row,
		Col,
		Toast,
		Loading,
		Waterfall
	} from 'vant'

	export default {
		name: "auction",
		components: {
			CommonNav,
			CountDown,
			NotCont,
			[Row.name]: Row,
			[Col.name]: Col,
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
				value: '',
				filter: {
					sort: 'act_id',
					order: 'DESC',
				}
			}
		},
		created() {
			this.goodsList()
		},
		computed: {
			groupbuyIndexData:{
				get(){
					return this.$store.state.other.groupbuyIndexData
				},
				set(val){
					this.$store.state.other.groupbuyIndexData = val	
				}
			}
		},
		methods: {
			goodsList() {
				this.$store.dispatch('setGroupbuyIndex',{
					size: this.size,
					page: this.page,
					sort: this.filter.sort,
					order: this.filter.order,
					keywords: this.value
				})
			},
			onSearch() {
				this.goodsList()
			},
			handleFilter(val, order) {
				this.page = 1
				if (order && this.filter.sort == val) {
					this.filter.order = order == 'DESC' ? 'ASC' : 'DESC'
				}
				this.filter.sort = val
				this.goodsList()
			},
			loadMore(){
				//数据分页
				setTimeout(() => {
					this.disabled = true
					if(this.page * this.size == this.groupbuyIndexData.length){
						this.page ++
						this.goodsList(this.filter.sort, this.filter.order,this.value)
					}
				},200);
			}
		},
		watch:{
			groupbuyIndexData(){
				if(this.page * this.size == this.groupbuyIndexData.length){
					this.disabled = false
					this.loading = true
				}else{
					this.loading = false
				}

				this.groupbuyIndexData = arrRemove.trimSpace(this.groupbuyIndexData)
			}
		}
	};
</script>
