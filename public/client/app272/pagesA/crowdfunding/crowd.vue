<template>
    <view class="container">
		<!--头部-->
		<view class="header">
			<uni-nav-bar color="#333333" background-color="#FFFFFF" shadow="false" :statusBar="false" fixed="false" leftState="false" titleNView="false">
				<view class="input-view">
					<uni-icons type="search" size="20" color="#666666"></uni-icons>
					<input confirm-type="search" class="input" type="text" v-model="value" :placeholder="placeholder" @input="KeyInput" @confirm="confirm" />
				</view>
				<view slot="right">
					<button type="warn" size="mini" @click="confirm" style="padding: 0; width: 96upx;">{{$t('lang.search')}}</button>
				</view>
			</uni-nav-bar>
			<view class="filter-tab">
				<view class="filter-items">
					<view class="item uni-flex-item" :class="{'active':filter === 'sort'}" @click="handleFilter('sort')">
						<text class="txt">{{$t('lang.comprehensive_rank')}}</text>
						<text class="iconfont icon-arrow-down"></text>
					</view>
					<view class="item uni-flex-item" :class="{'active':filter === 'all'}" @click="handleFilter('all')">
						<text class="txt">{{$t('lang.all_goods')}}</text>
						<text class="iconfont icon-arrow-down"></text>
					</view>
					<view class="item item-icon" @click="listModeClick">
						<view v-if="listMode === 'active'">
						    <text class="iconfont icon-grid"></text>
						</view>
						<view v-else>
						    <text class="iconfont icon-list"></text>
						</view>
					</view>
				</view>
			</view>
			<view class="tabCon" v-show="tabMode === 'active'">
			    <view class="cont-max-height uni-flex-common uni-flex-wrap" v-show="filter == 'sort'">
					<view class="filter-nav-radio" v-for="(item,index) in filterSort" :key="index" :class="{'active':filter_sort === item.cat_id}">
						<text class="filter-nav-radio-control" @click="sortingClick(item)">{{item.cat_name}}</text>
					</view>
			    </view>
			    <view class="cont-max-height uni-flex-common uni-flex-wrap" v-show="filter == 'all'">
					<view class="filter-nav-radio" :class="{'active':cur_id == 0}" >
						<text class="filter-nav-radio-control" @click="navCheck(0)"> {{$t('lang.all')}}</text>
					</view>
					<view class="filter-nav-radio" v-for="(item,index) in crowdIndexData" :key="index" :class="{'active':cur_id == item.cat_id}">
						<text class="filter-nav-radio-control" @click="navCheck(item)"> {{item.cat_name}}</text>
					</view>
			    </view>
			    <view class="filter-nav-close" @click="bgClick">关闭</view>
			</view>
			<view class="bg-cont" v-show="tabMode === 'active'"></view>
		</view>
		
        <!--活动列表-->
        <scroll-view class="scrollList" scroll-y :lower-threshold="100" @scrolltolower="loadMore" :style="{height:winHeight + 'px'}">
			<block v-if="crowdGoodsData && crowdGoodsData.length > 0">
				<view class="goods-list" :class="{'goods-list-lie':listMode === 'active'}">
					<view class="goods-item" v-for="(item,index) in crowdGoodsData" :key="index" @click="$outerHref('/pagesA/crowdfunding/detail/detail?id='+item.id,'app')">
						<view class="goods-left uni-flex-common">
							<image :src="item.title_img" class="img" mode="widthFix" v-if="item.title_img" />
							<image src="../../static/not_goods.png" class="img" mode="widthFix" v-else />
							<view class="raise-cate-tag uni-flex-common"><text class="iconfont icon-gerenzhongxin icon-geren"></text>{{item.join_num}}</view>
						</view>
						<view class="goods-right">
							<view class="goods-name twolist-hidden">{{item.title}}</view>
							<view class="goods-cont uni-flex-common uni-space-between" :class="{'uni-column uni-items-start':listMode === 'active'}">
								<text class="text">{{$t('lang.support_number')}}<text class="uni-red">{{item.join_num}}</text>{{$t('lang.ren')}}</text>
								<text class="text">{{$t('lang.time_remaining')}}{{item.shenyu_time}}{{$t('lang.tian')}}</text>
							</view>
							<view class="ect-progress">
								<progress :percent="item.baifen_bi" show-info="true" border-radius="3" stroke-width="6" font-size="12" active="true" activeColor="#f92028"></progress>
							</view>
							<view class="goods-cont uni-flex-common uni-space-between" :class="{'uni-column uni-items-start':listMode === 'active'}">
								<text class="text">{{$t('lang.label_has_crowdfunding')}}<text class="uni-red">{{item.join_money}}</text></text>
								<text class="text">{{$t('lang.label_target')}}<text class="uni-red">{{item.amount}}</text></text>
							</view>
						</view>
					</view>
				</view>
				<uni-load-more :status="loadMoreStatus" :content-text="contentText" v-if="page > 1" />
			</block>
			<dsc-not-content v-else></dsc-not-content>
        </scroll-view>		
		
        <dsc-tabbar :tabbar="tabbar"></dsc-tabbar>
		
		<dsc-common-nav></dsc-common-nav>
    </view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniNavBar from '@/components/uni-nav-bar.vue';
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
	
	import dscTabbar from '@/components/dsc-tabbar.vue';
	import dscNotContent from '@/components/dsc-not-content.vue'; 
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import universal from '@/common/mixins/universal.js';
	
	export default {
		name: "auction",
		mixins:[universal],
		components: {
			uniNavBar,
			uniIcons,
			uniLoadMore,
			dscTabbar,
			dscNotContent,
			dscCommonNav
		},
		data() {
			return {
				filterSort:[
					{
						cat_id:'id',
						cat_name: this.$t('lang.comprehensive_rank')
					},
					{
						cat_id:'new',
						cat_name: this.$t('lang.crowdfunding_new')
					},
					{
						cat_id:'amount',
						cat_name: this.$t('lang.crowdfunding_amount')
					},
					{
						cat_id:'join_num',
						cat_name: this.$t('lang.crowdfunding_join_num')
					}
				],
				filter_sort:'id',
				cur_id:0,
				listMode: "active", //模式列表模式
				tabMode: "",
				filter: "", //默认选中值
				value: "", //搜索默认值
				size:10,
				page:1,
				loadMoreStatus:'more',
				contentText: {
					contentdown: this.$t('lang.view_more'),
					contentrefresh: this.$t('lang.loading'),
					contentnomore: this.$t('lang.no_more')
				},
				tabbar:{
					type:'crowdfunding',
					index:0,
					curpage:''
				},
				placeholder:this.$t('lang.enter_search_keywords'),
			};
		},
		onShareAppMessage(res){
			return {
			  title: '众筹广场',
			  path: '/pagesA/crowdfunding/crowd'
			}
		},
		//初始化加载数据
		onLoad() {
			this.$store.dispatch('setCrowdfunding')
			this.crowdGoods()
		},
		computed: {
			...mapState({
				crowdIndexData: state => state.crowdfunding.crowdIndexData
			}),
			crowdGoodsData:{
				get(){
					return this.$store.state.crowdfunding.crowdGoodsData
				},
				set(val){
					this.$store.state.crowdfunding.crowdGoodsData = val
				}
			},
			winHeight(){
				return uni.getSystemInfoSync().windowHeight - 141
			}
		},
		methods: {
			//综合排序
			navCheck(item){
				this.cur_id = item === 0 ? item : item.cat_id;
				this.crowdGoods(1)
			},
			//全部商品
			sortingClick(item){
				this.filter_sort = item.cat_id
				this.crowdGoods(1)
			},
			onSearch() {
				this.crowdGoods(1)
			},
			crowdGoods(page) {
				if (page) {
					this.page = page
					this.size = Number(page) * 10
				}
				
				this.$store.dispatch('setCrowdfundingGoods',{
					status: this.filter_sort,
					cat_id:this.cur_id,
					keyword: this.value,
					page:this.page,
					size:this.size
				})
			},
			//菜单模式切换
			handleFilter(val) {
				this.filter = val
				this.tabMode = this.tabMode === "" ? "active" : "active"
			},
			//商品列表模式
			listModeClick() {
				this.listMode = this.listMode === "active" ? "" : "active"
				this.tabMode = this.tabMode === "active" ? "" : ""
			},
			//菜单显示影藏
			bgClick() {
				this.tabMode = this.tabMode === "active" ? "" : "active"
			},
			loadMore(){
				this.showLoadMore = true
				this.loadMoreStatus = 'loading';
				if(this.page * this.size == this.crowdGoodsData.length){
					this.page ++
					this.crowdGoods(this.filter_sort,this.cur_id,this.value)
				}else{
					this.showLoadMore = false
					return;
				}
			},
			KeyInput(e){
				this.value = e.detail.value;
			},
			confirm(){
				this.crowdGoods(1)
			}
		}
	}
</script>

<style scoped>
	/*header*/
	.header{ position: fixed; top: 0; left: 0; right: 0; z-index: 1;}
	.header >>> .uni-navbar { border-bottom: solid 1px #e6e6e6; }
	.header >>> .uni-navbar-header{ height: 50px;}
	.header >>> .uni-navbar-container{ margin-left: 20upx; width: auto; flex: 1;}
	.header >>> .uni-navbar-container .input-view{ background-color: #FFFFFF; border:1px solid #e6e6e6; margin: 9px 0; line-height: 30px;}
	.header >>> .uni-navbar-header .uni-navbar-header-btns:last-child{ width: 120upx; position: relative;}
	.header >>> .uni-navbar-header .uni-navbar-header-btns button{position: absolute; top: 9px;}
	
	/*filter*/
	.filter-tab{ background: #FFFFFF; border-bottom: 2upx solid #f1f1f1;}
	.filter-items{ display: flex; flex-direction: row; height: 80upx;}
	.filter-items .item{ display: flex; justify-content: center; align-items: center;}
	.filter-items .item .iconfont{ font-size: 20upx; margin-left: 5upx;}
	.filter-items .item .icon-filter{ margin: 3px 8upx 0 0;}
	.filter-items .item.active,
	.filter-items .item.active .iconfont{ color: #f92028; }
	
	.filter-items .item-icon{ width: 20%; }
	.filter-items .item-icon .iconfont{ font-size: 30upx;}
	
	/*filter-nav*/
	.tabCon{ position: fixed; top: 180upx; left: 0; right: 0; background-color: #FFFFFF; z-index: 2;border-top: 2upx solid #f1f1f1;}
	.bg-cont{ background: rgba(0,0,0,.4); position: fixed; right: 0; left: 0; top: 220upx; bottom: 0; z-index: 1;}
	.cont-max-height{ padding: 20upx;}
	.cont-max-height .filter-nav-radio{ width: 30%; padding: 10upx 1.5%; }
	.cont-max-height .filter-nav-radio .filter-nav-radio-control{ border: 2upx solid #f1f1f1; font-size: 28upx; padding: 5upx 0; text-align: center; display: block; border-radius: 10upx; color: #666666;}
	.cont-max-height .filter-nav-radio.active .filter-nav-radio-control{ border-color: #f92028; color: #f92028;}
	.filter-nav-close{ border-top: 2upx solid #f1f1f1; padding: 10upx 0; text-align: center; color: #666666;}
	
	/*活动列表 */
	.scrollList{ padding-top: 180upx; }
	.goods-list .goods-item{ border-bottom: 2upx solid #F1F1F1;}
	.goods-list .goods-left{ position: relative;}
	.goods-list .goods-left .raise-cate-tag{ position: absolute; background: rgba(0,0,0,.5); padding: 5upx 15upx; top: 10upx; right: 10upx; color: rgba(255,255,255,.8); font-size: 25upx; border-radius: 20upx; line-height: 1;}
	.goods-list .goods-left .raise-cate-tag .iconfont{ font-size: 20upx;line-height: 1; margin-right: 5upx;}
	
	.goods-list .goods-cont{ font-size: 25upx; color: #999; line-height: 1.5;}
	.goods-list .goods-cont .uni-red{ margin: 0 5upx;}
	
	.goods-list.goods-list-lie .goods-item{ background-color: #FFFFFF; width: calc(50% - 10upx); padding: 0; border-radius: 20upx; overflow: hidden;}
	.goods-list.goods-list-lie .goods-item .goods-left,.goods-list.goods-list-lie .goods-item .goods-left .img{ width: 100%; height: auto;}
	.goods-list.goods-list-lie .goods-item .goods-right{ padding: 20upx; }
</style>