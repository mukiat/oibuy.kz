<template>
	<view class="fixed-bottom-padding">
		<!--banner-->
		<swiper class="swiper" :autoplay="autoplay" :interval="interval" :duration="duration" v-if="bargainIndexData && bargainIndexData.length>0" :style="{height:imgheights + 'px'}">
			<swiper-item v-for="(item,index) in bargainIndexData" :key="index">
				<view class="swiper-item"><image :src="item.pic" mode="widthFix" @load="imageLoad"></image></view>
			</swiper-item>
		</swiper>
		<view class="product-list-lie">
			<dsc-product-list :list="bargainGoodsData" :routerName="routerName" v-if="bargainGoodsData"></dsc-product-list>
		</view>
		
		<dsc-tabbar :tabbar="tabbar"></dsc-tabbar>
		
		<dsc-common-nav>
			<navigator url="./bargain" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.bargaining_channel')}}</text>
			</navigator>
		</dsc-common-nav>
		
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	
	import uniLoadMore from '@/components/uni-load-more.vue';
	
	import dscProductList from '@/components/dsc-product-list.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscTabbar from '@/components/dsc-tabbar.vue';
	
	export default {
		data() {
			return {
				loadMoreText: this.$t('lang.loading'),
				showLoadMore: false,
				autoplay: true,
				interval: 5000,
				duration: 500,
				dscLoading:true,
				size:10,
				page:1,
				imgheights:'',
				routerName: "bargain",
				tabbar:{
					type:'bargain',
					index:0,
					curpage:''
				}
			}
		},
		components: {
			uniLoadMore,
			dscProductList,
			dscNotContent,
			dscCommonNav,
			dscTabbar
		},
		onUnload(){
			this.loadMoreText = this.$t('lang.load_more'),
			this.showLoadMore = false;
		},
		onReachBottom(){
			this.showLoadMore = true
			if(this.page * this.size == this.bargainGoodsData.length){
				this.page ++
				this.getGoodsList()
			}else{
				this.loadMoreText = this.$t('lang.no_more');
				return;
			}
		},
		onShareAppMessage(res){
			return {
			  title: this.$t('lang.home_bargain'),
			  path: '/pagesA/bargain/bargain'
			}
		},
		onLoad() {
			let pages = getCurrentPages()
			this.tabbar.curpage = pages[pages.length - 1].route
			
			this.load();
		},
		computed:{
			...mapState({
                bargainIndexData: state => state.bargain.bargainIndexData
            }),
            bargainGoodsData:{
                get(){
                    return this.$store.state.bargain.bargainGoodsData
                },
                set(val){
                    this.$store.state.bargain.bargainGoodsData = val
                }
            }
		},
		methods:{
			load(){
				this.$store.dispatch('setBargainIndex');
				this.getGoodsList();
			},
			getGoodsList(page) {
                if(page){
                    this.page = page
                    this.size = Number(page) * 10
                }

                this.$store.dispatch('setBargainGoods',{
                    size: this.size,
                    page: this.page,
                });
            },
			imageLoad(e){
				let imgwidth = e.detail.width,
					imgheight = e.detail.height,
					//设备宽度
					windowWidth = uni.getSystemInfoSync().windowWidth;
				
				this.imgheights = (windowWidth/imgwidth) * imgheight
			}
		},
		watch:{
			bargainIndexData(){
				this.dscLoading = false
			}
		}
	}
</script>

<style>
</style>
