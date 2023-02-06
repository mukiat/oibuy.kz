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
			<view class="filter-tab" v-if="type != 'new'">
				<view class="filter-items">
					<view class="item" :class="{'active':filter.sort === 'goods_id'}" @click="handleFilter('goods_id',filter.order)">
						<text class="txt">{{$t('lang.comprehensive')}}</text>
						<text class="iconfont" :class="[(filter.order === 'ASC' && filter.sort === 'goods_id') ? 'icon-arrow-up' : 'icon-arrow-down']"></text>
					</view>
					<view class="item" :class="{'active':filter.sort === 'last_update'}" @click="handleFilter('last_update')">
						<text class="txt">{{$t('lang.new')}}</text>
					</view>
					<view class="item" :class="{'active':filter.sort === 'shop_price'}" @click="handleFilter('shop_price',filter.order)">
						<text class="txt">{{$t('lang.price')}}</text>
						<text class="iconfont" :class="[(filter.order === 'ASC' && filter.sort === 'shop_price') ? 'icon-arrow-up' : 'icon-arrow-down']"></text>
					</view>
				</view>
			</view>
		</view>
		<view class="presale-list">
			<view class="goods-list goods-list-lie" v-if="presaleListData && presaleListData.length > 0">
				<view class="goods-item" v-for="(goodsItem,goodsIndex) in presaleListData" :key="goodsIndex" @click="detailHref(goodsItem)">
					<view class="goods-img"><image :src="goodsItem.goods_thumb" class="img" mode="widthFix" /></view>
					<view class="goods-info">
						<view class="goods-name twolist-hidden">{{goodsItem.goods_name}}</view>
						<view class="plan-box">
							<view class="price">{{goodsItem.format_shop_price}}</view>
							<text class="market">{{goodsItem.format_market_price}}</text>
						</view>
					</view>
					<view class="already-over" v-if="goodsItem.already_over == 1"><image src="../../../static/preslae-end.png" class="img"></image></view>
				</view>
			</view>
			<dsc-not-content v-else></dsc-not-content>
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
				status:0,
				active: 0,
				keyValue: "",
				placeholder: this.$t('lang.enter_search_keywords'),
				filter: {
					sort: 'act_id',
					order: 'DESC',
				},
				cat_id:0,
				dscLoading:true,
				type:''
			}
		},
		computed: {
			presaleListData:{
				get(){
					return this.$store.state.ump.presaleListData
				},
				set(val){
					this.$store.state.ump.presaleListData = val
				}
			},
			presaleNewList:{
				get(){
					return this.$store.state.ump.presaleNewList
				},
				set(val){
					this.$store.state.ump.presaleNewList = val
				}
			},
		},
		methods: {
			KeyInput(e){
				this.keyword = e.detail.value;
			},
			handleFilter(val, order) {
				if (order && this.filter.sort == val) {
					this.filter.order = order == 'DESC' ? 'ASC' : 'DESC'
				}
				this.filter.sort = val;
				this.presaleList(1);
			},
			presaleList(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}
				
				let presale = {
					status:this.status,
					page: this.page,
					size: this.size,
					cat_id: this.cat_id,
					sort:this.filter.sort,
					order:this.filter.order,
					keywords:this.keyValue,
				}
				
				this.$store.dispatch('setPresaleList', presale)
			},
			presaleNew(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}
				
				this.$store.dispatch('setPresaleNew',{
					cat_id:0,
					page: this.page,
					size: this.size,
					keywords:this.keyValue,
				})
			},
			confirm(){
				this.presaleList(1);
			},
			detailHref(item){
				uni.navigateTo({
					url:'/pagesA/presale/detail/detail?act_id='+item.act_id
				})
			}
		},
		onLoad(e){
			this.cat_id = e.cat_id ? e.cat_id : 0;
			this.type = e.type ? e.type : '';
			
			if(this.type == 'new'){
				this.presaleNew();
			}else{
				this.presaleList();
			}
		},
		onReachBottom(){
			if(this.page * this.size == this.presaleListData.length){
				this.page ++
				this.presaleList()
			}
		},
		watch:{
			presaleListData(){
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

.presale-list .goods-list .plan-box{ display: flex; justify-content: flex-start;}
.presale-list .goods-list .plan-box .market{ text-decoration: line-through; font-size: 25upx; margin-left: 10upx; }

.goods-item{ position: relative; }
.already-over{ width: 100upx; height: 100upx; position: absolute; bottom: 10upx; right: 20upx; z-index: 9;}
</style>
