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
						<text class="iconfont" :class="[(filter.order === 'ASC' && filter.sort === 'start_time') ? 'icon-arrow-up' : 'icon-arrow-down']"></text>
					</view>
					<view class="item" :class="{'active':filter.sort === 'comments_number'}" @click="handleFilter('comments_number',filter.order)">
						<text class="txt">{{$t('lang.comment_number')}}</text>
						<text class="iconfont" :class="[(filter.order === 'ASC' && filter.sort === 'comments_number') ? 'icon-arrow-up' : 'icon-arrow-down']"></text>
					</view>
				</view>
			</view>
		</view>
		<view class="groupbuy-list">
			<view class="goods-list goods-list-lie" v-if="groupbuyIndexData && groupbuyIndexData.length > 0">
				<view class="goods-item" v-for="(goodsItem,goodsIndex) in groupbuyIndexData" :key="goodsIndex" @click="detailHref(goodsItem)">
					<view class="goods-img"><image :src="goodsItem.goods_thumb" class="img" mode="widthFix" /></view>
					<view class="goods-info">
						<view class="time">
							<text v-if="goodsItem.end_date_day == 1">{{$t('lang.has_ended')}}</text>
							<text class="active" v-else>{{$t('lang.residue')}}{{goodsItem.end_date_day}}</text>
						</view>
						<view class="goods-name twolist-hidden">{{goodsItem.goods_name}}</view>
						<view class="goods-cont">
							<view class="plan-box">
								<text class="price">{{goodsItem.price}}</text>
								<text class="shop-price">{{ goodsItem.cur_amount }}{{$t('lang.cur_amount')}}</text>
							</view>
							<view class="cart"><icon class="iconfont icon-cart"></icon></view>
						</view>
					</view>
					<text class="groupbuy-tag" v-if="goodsItem.zhekou < 10">{{goodsItem.zhekou}}{{$t('lang.zhe')}}</text>
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
			...mapState({
				groupbuyIndexData: state => state.ump.groupbuyIndexData,
			})
		},
		methods: {
			KeyInput(e){
				this.keyValue = e.detail.value;
			},
			handleFilter(val, order) {
				this.page = 1
				if (order && this.filter.sort == val) {
					this.filter.order = order == 'DESC' ? 'ASC' : 'DESC'
				}
				this.filter.sort = val
				this.goodsList()
			},
			goodsList(page){
				this.$store.dispatch('setGroupbuyIndex',{
					size: this.size,
					page: this.page,
					sort: this.filter.sort,
					order: this.filter.order,
					keywords: this.keyValue
				})
			},
			confirm(){
				this.goodsList(1);
			},
			detailHref(item){
				uni.navigateTo({
					url:"/pagesA/groupbuy/detail/detail?id="+item.group_buy_id
				})
			}
		},
		onLoad(e){
			this.goodsList();
		},
		onReachBottom(){
			if(this.page * this.size == this.groupbuyIndexData.length){
				this.page ++
				this.goodsList()
			}
		},
		watch:{
			groupbuyIndexData(){
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

.goods-item{ position: relative; border-radius: 10upx; overflow: hidden;}

.goods-cont{ display: flex; flex-direction: row; justify-content: space-between; align-items: center;}
.goods-cont .cart{background: linear-gradient(to right, #FF010C, #FF7E0C); width: 60upx; height: 60upx; border-radius: 50%; text-align: center; display: flex; align-items: center; justify-content: center; margin: 0 20upx;}
.goods-cont .cart .iconfont{ color: #FFFFFF;}
.groupbuy-list .goods-list .plan-box{ line-height: 1.5; flex: 1; display: flex; flex-direction: column; justify-content: flex-start; align-items: flex-start;}
.groupbuy-list .goods-list.goods-list-lie .plan-box .price{ margin: 0;}
.groupbuy-list .goods-list .goods-name{ height: 44px;}

.groupbuy-tag{ position: absolute; top: 0; left: 0; background: #f92028; color: #FFFFFF; padding: 0 10upx; font-size: 25upx;}

.goods-info{ position: relative;}
.goods-info .time{ position: absolute; top: -42upx; left: 0; right: 0; text-align: center; }
.goods-info .time text{ background: #999;  color: #fff; padding: 5upx 20upx; border-radius: 40upx; display: inline-block; font-size: 25upx;}
.goods-info .time text.active{ background: linear-gradient(to right, #FA2829, #FE522C); }
</style>
