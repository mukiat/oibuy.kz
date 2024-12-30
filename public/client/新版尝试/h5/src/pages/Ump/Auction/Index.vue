<template>
	<div class="auction tab-colse" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="100">
		<div class="search-nav">
			<div class="search dis-box">
				<div class="f-04 box-flex">
					<input class="f-03" type="text" v-model="value" :placeholder="$t('lang.activitie_name')" autocomplete="off">
				</div>
				<div class="icon-search-box" @click="onSearch"><i class="iconfont icon-sousuo1 f-05 color-7"/></div>
			</div>
		</div>
		<section class="filter_tab">
			<div class="pro_filter_items dis-box">
				<div class="item" :class="{'active':filter.sort === 'act_id'}" @click="handleFilter('act_id')">
					<span>{{$t('lang.default')}}</span>
				</div>
				<div class="item" :class="[{'active':filter.sort === 'start_time','a-change':filter.order === 'asc' && filter.sort === 'start_time'}]"
				    @click="handleFilter('start_time',filter.order)">
					<span>{{$t('lang.start_time')}}</span>
					<i class="iconfont icon-xiajiantou"></i>
				</div>
				<div class="item" :class="[{'active':filter.sort === 'end_time','a-change':filter.order === 'asc' && filter.sort === 'end_time'}]"
				    @click="handleFilter('end_time',filter.order)">
					<span>{{$t('lang.end_time')}}</span>
					<i class="iconfont icon-xiajiantou"></i>
				</div>
			</div>
		</section>
		<div class="tabCon">
			<div class="goods-li" v-if="auctionIndexData!=''">
				<router-link :to="{name:'auction-detail',params:{act_id:item.act_id}}" class="show bg-color-write li" v-for='(item,index) in auctionIndexData'
				    :key="index">
					<div class="left">
						<img class="img" :src="item.goods_thumb" />
					</div>
					<div class="right">
						<h4 class="f-04 color-3 twolist-hidden m-top02">{{item.goods_name}}</h4>
						<div class="cont auction dis-box">
							<div class="box-flex">
								<div class="f-06 color-red box-flex m-top04">
									<span class="f-02 color-red ">{{$t('lang.starting_price')}}</span>
									<span v-html="item.formated_start_price"></span>
									
								</div>
								<div class="f-02 color-9 m-top02">{{$t('lang.label_au_end_price')}}
									<template v-if="item.no_top == 0">
										<span v-html="item.formated_end_price"></span>
									</template>
									<template v-else>
										<span>{{$t('lang.uncapped')}}</span>
									</template>
								</div>
							</div>
							<div class="m-top10">
								<span class="min-btn tag-gradients-color color-white br-5 f-03">{{$t('lang.me_bid')}}</span>
							</div>
						</div>
					</div>
				</router-link>
			</div>
			<div v-else>
				<NotCont/>
			</div>
		</div>
		<div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
		<template v-if="loading">
			<van-loading type="spinner" color="black" />
		</template>
		<CommonNav />
	</div>
</template>

<script>
import { mapState } from 'vuex'
import {
	Row,
	Col,
	Toast,
	Waterfall,
	Loading
} from 'vant'

import commonGet from '@/mixins/common-get'
import arrRemove from '@/mixins/arr-remove'
import CommonNav from '@/components/CommonNav'
import NotCont from '@/components/NotCont'

export default {
	name: 'auction',
	components: {
		CommonNav,
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
			value: '',
			size:10,
			page:1,
			filter: {
				sort: 'act_id',
				order: 'desc',
			},
			footerCont:false
		};
	},
	//初始化加载数据
	created() {
		//loading
		commonGet.loadingData()

		//初始化加载数据
		this.goodsList()
	},
	computed: {
		auctionIndexData:{
			get(){
				return this.$store.state.auction.auctionIndexData
			},
			set(val){
				this.$store.state.auction.auctionIndexData = val
			}
		}
	},
	methods: {
		goodsList(page) {
			if(page){
				this.page = page
				this.size = Number(page) * 10
			}
			
			this.$store.dispatch('setAuctionIndex',{
				size: this.size,
				page: this.page,
				sort: this.filter.sort,
				order: this.filter.order,
				keyword: this.value
			})
		},
		onSearch() {
			this.goodsList(1)
		},
		handleFilter(val, order) {
			if (order && this.filter.sort == val) {
				this.filter.order = order == 'desc' ? 'asc' : 'desc'
			}
			this.filter.sort = val
			this.goodsList(1)
		},
		loadMore(){
			setTimeout(() => {
				this.disabled = true
				if(this.page * this.size == this.auctionIndexData.length){
					this.page ++
					this.goodsList()
				}
			},200)
		}
	},
	watch:{
		auctionIndexData(){
			if(this.page * this.size == this.auctionIndexData.length){
				this.disabled = false
				this.loading = true
			}else{
				this.loading = false
				this.footerCont = this.page > 1 ? true : false
			}

			this.auctionIndexData = arrRemove.trimSpace(this.auctionIndexData)
		}
	}
}
</script>
