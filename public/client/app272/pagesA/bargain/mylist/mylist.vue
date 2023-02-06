<template>
	<view class="container">
		<view class="goods-list">
			<block v-if="bargainMyBuyData && bargainMyBuyData.length > 0">
				<view class="goods-item" v-for="(item,index) in bargainMyBuyData" :key="index" @click="detailClick(item)">
					<view class="goods-left"><image :src="item.goods_thumb" class="img" /></view>
					<view class="goods-right">
						<view class="goods-name twolist-hidden">{{item.goods_name}}</view>
						<view class="plan-box">
							<view class="uni-flex"><view class="iconfont icon-renshu"></view>{{item.total_num}}{{$t('lang.participation_number')}}</view>
						</view>
						<view class="plan-box">
							<view><text>{{$t('lang.label_original_price')}}</text><text class="uni-red">{{item.shop_price}}</text>{{$t('lang.yuan')}}</view>
							<view><text>{{$t('lang.label_base_price')}}</text><text class="uni-red">{{item.target_price}}</text>{{$t('lang.yuan')}}</view>
						</view>
					</view>
				</view>
			</block>
			<block v-else>
				<dsc-not-content></dsc-not-content>
			</block>
			<uni-load-more :status="loadMoreStatus" :content-text="contentText" v-if="showLoadMore" />
		</view>
		
		<dsc-tabbar :tabbar="tabbar"></dsc-tabbar>
		
		<dsc-common-nav>
			<navigator url="../bargain" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.bargaining_channel')}}</text>
			</navigator>
		</dsc-common-nav>
		
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
	</view>	
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
	
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscTabbar from '@/components/dsc-tabbar.vue';
	
	export default {
		data() {
			return {
				size:10,
				page:1,
				dscLoading:true,
				loadMoreStatus:'more',				
				contentText: {
					contentdown: this.$t('lang.view_more'),
					contentrefresh: this.$t('lang.loading'),
					contentnomore: this.$t('lang.no_more')
				},
				tabbar:{
					type:'bargain',
					index:1,
					curpage:''
				}
			}
		},
		components:{
			uniIcons,
			uniLoadMore,
			dscCommonNav,
			dscNotContent,
			dscTabbar
		},
		onLoad() {
			let pages = getCurrentPages()
			this.tabbar.curpage = pages[pages.length - 1].route
			
			this.mylist();
		},
		onUnload(){
			this.loadMoreStatus = 'more';
		},
		onReachBottom(){
			this.loadMoreStatus = 'loading';
			if(this.page * this.size == this.bargainMyBuyData.length){
				this.page ++
				this.mylist()
			}else{
				this.loadMoreStatus = "noMore"
				return;
			}
		},
		computed: {
			bargainMyBuyData:{
				get(){
					return this.$store.state.bargain.bargainMyBuyData
				},
				set(val){
					this.$store.state.bargain.bargainMyBuyData = val
				}
			}
		},
		methods: {
			mylist(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}

				this.$store.dispatch('setBargainMyBuy',{
					page: this.page,
					size: this.size,
				})
			},
			detailClick(item){
				uni.navigateTo({
					url:'/pagesA/bargain/detail/detail?id='+item.id
				})
			}
		},
		watch:{
			bargainMyBuyData(){
				this.dscLoading = false
				if(this.page * this.size > this.bargainMyBuyData.length * this.page){
					this.showLoadMore = false
				}
			}
		}
	}
</script>

<style scoped>
.plan-box view{ font-size: 24upx;}
</style>
