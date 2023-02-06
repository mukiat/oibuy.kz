<template>
	<view class="container">
		<view class="header">
			<uni-nav-bar color="#333333" background-color="#FFFFFF" shadow="false" :statusBar="false" fixed="false" leftState="false" titleNView="false">
				<view class="input-view">
					<uni-icons type="search" size="20" color="#666666"></uni-icons>
					<input confirm-type="search" class="input" type="text" v-model="keyValue" :placeholder="placeholder" @input="KeyInput" @confirm="confirm" />
				</view>
				<view slot="right">
					<button type="warn" size="mini" @click="confirm" style="padding: 0; width: 96upx;">{{$t('lang.search')}}</button>
				</view>
			</uni-nav-bar>
			<view class="filter-tab">
				<view class="filter-items">
					<view class="item" :class="{'active':filter.sort === 'act_id'}" @click="handleFilter('act_id')">
						<text class="txt">{{$t('lang.default')}}</text>
					</view>
					<view class="item" :class="{'active':filter.sort === 'start_time'}" @click="handleFilter('start_time',filter.order)">
						<text class="txt">{{$t('lang.start_time')}}</text>
						<text class="iconfont" :class="[(filter.order === 'asc' && filter.sort === 'start_time') ? 'icon-arrow-up' : 'icon-arrow-down']"></text>
					</view>
					<view class="item" :class="{'active':filter.sort === 'comments_number'}" @click="handleFilter('comments_number',filter.order)">
						<text class="txt">{{$t('lang.comment_number')}}</text>
						<text class="iconfont" :class="[(filter.order === 'asc' && filter.sort === 'comments_number') ? 'icon-arrow-up' : 'icon-arrow-down']"></text>
					</view>
				</view>
			</view>
		</view>
		<!--goodslist-->
		<view class="goods-list">
			<block v-if="auctionIndexData && auctionIndexData.length > 0">
				<view class="goods-item" v-for="(item,index) in auctionIndexData" :key="index" @click="detailHref(item)">
					<view class="goods-left"><image :src="item.goods_thumb" class="img" /></view>
					<view class="goods-right">
						<view class="goods-name twolist-hidden">{{item.goods_name}}</view>
						<view class="plan-box">
							<view class="shop-price" v-if="item.no_top == 0">{{$t('lang.label_au_end_price')}}{{item.formated_end_price}}</view>
							<view class="shop-price" v-else>{{$t('lang.uncapped')}}</view>
						</view>
						<view class="plan-box">
							<view class="price">{{$t('lang.starting_price')}}:{{item.formated_start_price}}</view>
							<view class="btn">{{$t('lang.me_bid')}}</view>
						</view>
					</view>
				</view>
			</block>
			<block v-else>
				<dsc-not-content></dsc-not-content>
			</block>
		</view>
		
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
		<dsc-common-nav></dsc-common-nav>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniNavBar from '@/components/uni-nav-bar.vue';
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import universal from '@/common/mixins/universal.js';
	
	export default {
		mixins:[universal],
		components:{
			uniNavBar,
			uniIcons,
			dscNotContent,
			dscCommonNav
		},
		data() {
			return {
				size:10,
				page:1,
				keyValue: "",
				placeholder: this.$t('lang.enter_search_keywords'),
				filter: {
					sort: 'act_id',
					order: 'desc',
				},
				dscLoading:true,
			}
		},
		computed: {
			auctionIndexData:{
				get(){
					return this.$store.state.ump.auctionIndexData
				},
				set(val){
					this.$store.state.ump.auctionIndexData = val
				}
			}
		},
		methods: {
			KeyInput(e){
				this.keyValue = e.detail.value;
			},
			handleFilter(val, order) {
				if (order && this.filter.sort == val) {
					this.filter.order = order == 'desc' ? 'asc' : 'desc'
				}
				this.filter.sort = val;
				this.goodsList(1);
			},
			goodsList(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}
				
				this.$store.dispatch('setAuctionIndex',{
					size: this.size,
					page: this.page,
					sort: this.filter.sort,
					order: this.filter.order,
					keyword: this.keyValue
				})
			},
			confirm(){
				this.goodsList(1);
			},
			detailHref(item){
				uni.navigateTo({
					url:'/pagesA/auction/detail/detail?act_id='+item.act_id
				})
			}
		},
		onLoad(e){
			this.goodsList()
		},
		onReachBottom(){
			if(this.page * this.size == this.auctionIndexData.length){
				this.page ++
				this.goodsList()
			}
		},
		watch:{
			auctionIndexData(){
				this.dscLoading = false
			},
		}
	}
</script>

<style>
/*header*/
.header .uni-navbar { border-bottom: solid 1px #e6e6e6; }
.header .uni-navbar-header{ height: 50px;}
.header .uni-navbar-container{ margin-left: 20upx; width: auto; flex: 1;}
.header .uni-navbar-container .input-view{ background-color: #FFFFFF; border:1px solid #e6e6e6; margin: 9px 0; line-height: 30px;}
.header .uni-navbar-header .uni-navbar-header-btns:last-child{ width: 120upx; position: relative;}
.header .uni-navbar-header .uni-navbar-header-btns button{position: absolute; top: 9px;}

/*filter*/
.filter-tab{ background: #FFFFFF;}
.filter-items{ display: flex; flex-direction: row; height: 80upx;}
.filter-items .item{ display: flex; flex: 1 1 0%; justify-content: center; align-items: center;}
.filter-items .item .iconfont{ font-size: 20upx; margin-left: 5upx;}
.filter-items .item .icon-filter{ margin: 3px 8upx 0 0;}
.filter-items .item.active,.filter-items .item.active .iconfont{ color: #f92028; }
.filter-items .item.a-change .iconfont{ transform: rotate(-180deg); }

</style>
