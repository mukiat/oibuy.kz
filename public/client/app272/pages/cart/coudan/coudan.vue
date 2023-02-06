<template>
	<view class="container">
		<view class="header">
			<uni-nav-bar color="#333333" background-color="#FFFFFF" shadow="false" :statusBar="false" fixed="false" leftState="false" @click-right="rightClick">
				<view class="input-view">
					<input confirm-type="search" disabled="true" @click="searchFocus" class="input" type="text" :placeholder="placeholder" />
					<uni-icons type="search" size="22" color="#666666" class="right-icon" @click="searchFocus"></uni-icons>
				</view>
				<view slot="right"><text class="iconfont" @click="handleViewSwitch" :class="[mode === 'grid' ? 'icon-grid' : 'icon-list']"></text></view>
			</uni-nav-bar>
			<dsc-filter :filter="filter" :filterStyle="filterStyle" :isPopupVisible="isPopupVisible" v-on:getFilter='handleFilter' @setPopupVisible="setPopupVisible"></dsc-filter>
		</view>
		<view class="product-list-lie">
			<dsc-product-list :list="coudanGoodsList" :mode="mode" v-if="coudanGoodsList"></dsc-product-list>
		</view>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	
	import uniNavBar from '@/components/uni-nav-bar.vue'
	import uniIcons from '@/components/uni-icons/uni-icons.vue'
	import uniDrawer from '@/components/uni-drawer.vue'
	import dscFilter from '@/components/dsc-filter.vue'
	import dscProductList from '@/components/dsc-product-list.vue'
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	
	export default {
		components: {
			uniNavBar,
			uniIcons,
			uniDrawer,
			dscFilter,
			dscProductList,
			dscCommonNav
		},
		data() {
			return {
				mode:'grid',
				isFilter:true,
				filter:{
					sort:'goods_id',
					order:'desc',
					goods_num:'0',
					promote:'0',
					min:'',
					max:'',
					brand_id:[],
					brandResult:[],
					brandResultName:'',
					self:'0',
				},
				filterStyle:'coudan',
				isPopupVisible:false,
				routerName:'goods',
				act_id:0,
				page:1,
				size:10,
				placeholder:this.$t('lang.enter_search_keywords')
			}
		},
		computed:{
			coudanGoodsList:{
				get(){
					return this.$store.state.shopping.coudanGoodsList
				},
				set(val){
					this.$store.state.shopping.coudanGoodsList = val
				}
			},
			coudanInfo:{
				get(){
					return this.$store.state.shopping.coudanInfo
				},
				set(val){
					this.$store.state.shopping.coudanInfo = val
				}
			}
		},
		methods: {
			getGoodsList(page){
				let sort = 0
	
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}
	
				if(this.filter.sort == 'goods_id'){
					sort = 0
				}else if(this.filter.sort == 'shop_price'){
					sort = 1
				}else if(this.filter.sort == 'sales_volume'){
					sort = 2
				}
				this.$store.dispatch('setCoudanGoodsList',{
					act_id:this.act_id,
					page:this.page,
					size:this.size,
					sort:sort,
					order:this.filter.order
				})
			},
			onCoudanInfo(){
				this.$store.dispatch('setCoudan',{
					act_id:this.act_id
				})
			},
			handleFilter(o){
				this.filter.sort = o.sort
				this.filter.order = o.order
	
				this.getGoodsList(1)
			},
			setPopupVisible(val){
				this.isPopupVisible = val;
			},
			handleViewSwitch(val){
				this.mode = this.mode === 'grid' ? 'list' : 'grid';
			},
			searchFocus(){
				//全局变量integration赋值
				//getApp().globalData.integration = 0;
				
				uni.navigateTo({
					url: '/pages/search/search'
				});
			},
			leftClick(){
				
			},
			rightClick(){
				
			},
			confirm(){
				
			}
		},
		onLoad(e){
			this.act_id = e.act_id
			this.getGoodsList()
			this.onCoudanInfo()
		}
	}
</script>

<style>
	/*header*/
	.header .uni-navbar { border-bottom: solid 1px #e6e6e6;}
	.header .uni-navbar view{ line-height: 50px;}
	.header .uni-navbar-header{ height: 50px;}
	.header .uni-navbar-header .uni-navbar-header-btns{ padding: 0;}
	.header .uni-navbar-container{ margin: 0 20upx;}
	.header .uni-navbar .input-view{background-color: #FFFFFF; border:1px solid #e6e6e6; margin: 9px 0; display: flex; flex-direction: row; align-items: center;}
	
	/*popop*/
	.con-filter-div{ padding-bottom: 50px; }
	.mod_list{ background: #ffffff; margin-bottom: 20upx;}
	.mod_list .item .li_line{ display: flex; flex-direction: row; justify-content: space-between; align-items: center; padding: 20upx;}
	.mod_list .item.radio-switching{ padding: 20upx; }
	.mod_list .item.radio-switching .li_line{ padding: 0;}
	.tags_selection{ background: #FFFFFF; margin-bottom: 20upx; padding: 20upx 0 0 20upx; display: flex; flex-direction:row;}
	.tags_selection text{  padding: 5upx 30upx; background: #f7f7f7; border-radius: 10upx; margin: 0 20upx 20upx 0; border:1px solid #f7f7f7;}
	.tags_selection .active{ background: #FFFFFF; border:1px solid #e93b3d; color: #e93b3d;}
	
	.filterlayer_price{ padding: 20upx; position: relative;}
	.filterlayer_price:before{ content: "";position: absolute;z-index: 1;pointer-events: none;background-color: #e5e5e5;height: 1px;left: 0;right: 0;top: 0;}
	.filterlayer_price .filterlayer_price_area{ display: flex;}
	.filterlayer_price_area_input{ flex: 1; background: #f7f7f7; color: #333333; padding: 10upx; text-align: center; }
	.filterlayer_price_hang{ width: 50upx; position: relative;}
	.filterlayer_price_hang:before{ content: "";position: absolute;top: 50%;left: 50%;margin-left: -5px;width: 10px;height: 1px;background-color: #f1f1f1;}
	
	.filterlayer_bottom_buttons{ display: flex; flex-direction: row; position:absolute; bottom: 0; width: 100%; background: #FFFFFF; z-index: 9;}
	.filterlayer_bottom_button{ height: 49px; line-height: 49px; flex: 1; text-align: center; box-shadow: 0 -1px 2px 0 rgba(0, 0, 0, 0.07);}
	.filterlayer_bottom_button.active{ background-color: #e93b3d; color: #ffffff;}
	
	.sf_layer{ background: #FFFFFF; height: 100%;}
	.sf_layer .sf_layer_sub_title{ display: flex; flex-direction: row; align-items: center; padding: 20upx; background-color: #FFFFFF;}
	.sf_layer .sf_layer_sub_title .tit{ width: 150upx;}
	.sf_layer .sf_layer_sub_title text{ flex: 1 1 0%;}
	
	.center-box{ width: 100%; background: #FFFFFF;}
</style>

