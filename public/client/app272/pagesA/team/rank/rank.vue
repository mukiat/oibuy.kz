<template>
	<view class="fixed-bottom-padding">
		<view class="comment-tabs">
			<uni-segmented-control :current="status" :values="items" style-type="text" active-color="#f92028" @clickItem="onClickItem" />
		</view>
		<block v-if="!showLoadMoreTab">
			<view class="goods-list">
				<block v-if="teamRankData && teamRankData.length > 0">
					<view class="goods-item" v-for="(item,index) in teamRankData" :key="index" @click="detailClick(item.goods_id)">
						<view class="goods-left"><image :src="item.goods_thumb" class="img" /></view>
						<view class="goods-right">
							<view class="goods-name twolist-hidden">{{item.goods_name}}</view>
							<view class="plan-box"><view class="shop-price">{{$t('lang.single_purchase_price')}}{{item.shop_price}}</view></view>
							<view class="plan-box">
								<view class="num">{{item.team_num}}{{$t('lang.one_group')}}</view>
								<view class="price">{{item.team_price}}</view>
								<view class="btn">{{$t('lang.up_group')}}</view>
							</view>
						</view>
					</view>
				</block>
				<block v-else>
					<dsc-not-content></dsc-not-content>
				</block>
				<uni-load-more :status="loadMoreStatus" :content-text="contentText" v-if="showLoadMore" />
			</view>
		</block>
		<uni-load-more status="loading" :tab-type="true" :content-text="contentText" v-else />
		
		<dsc-tabbar :tabbar="tabbar"></dsc-tabbar>
		
		<dsc-common-nav></dsc-common-nav>
		
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
	</view>
</template>

<script>
	import uniSegmentedControl from '@/components/uni-segmented-control/uni-segmented-control.vue'
	import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscTabbar from '@/components/dsc-tabbar.vue';
	export default {
		data() {
			return {
				items: [
					this.$t('lang.hot_alt'),
					this.$t('lang.new'),
					this.$t('lang.optimization')
				],
				status:0,
				page:1,
				size:10,
				showLoadMore:true,
				showLoadMoreTab:true,
				loadMoreStatus:'more',				
				contentText: {
					contentdown: this.$t('lang.view_more'),
					contentrefresh: this.$t('lang.loading'),
					contentnomore: this.$t('lang.no_more')
				},
				dscLoading:true,
				tabbar:{
					type:'team',
					index:1,
					curpage:''
				}
			}
		},
		components: {
			uniSegmentedControl,
			uniLoadMore,
			dscNotContent,
			dscCommonNav,
			dscTabbar
		},
		onUnload(){
			this.loadMoreStatus = 'more';
		},
		onReachBottom(){
			this.loadMoreStatus = 'loading';
			if(this.page * this.size == this.teamRankData.length){
				this.page ++
				this.teamRank()
			}else{
				this.loadMoreStatus = "noMore"
				return;
			}
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesA/team/rank/rank'
			}
		},
		computed: {
            teamRankData:{
                get(){
                    return this.$store.state.team.teamRankData      
                },
                set(val){
                    this.$store.state.team.teamRankData = val
                }
            }
        },
		methods: {
			onClickItem(index) {
				if (this.status !== index) {
					this.status = index
				}
				
				this.teamRank();
			},
			teamRank() {
				this.showLoadMore = true;
				this.showLoadMoreTab = true;
				this.loadMoreStatus = 'loading';
                this.$store.dispatch('setTeamRank',{
                    status: this.status,
                    size: this.size,
                    page: this.page,
                });
            },
			detailClick(id){
				uni.navigateTo({
					url:'../detail/detail?goods_id='+id+'&team_id=0'
				})
			}
		},
		onLoad(e){
			let pages = getCurrentPages()
			this.tabbar.curpage = pages[pages.length - 1].route
			
			this.teamRank();
		},
		watch:{
			teamRankData(){
				this.dscLoading = false;
				this.showLoadMoreTab = false;
				if(this.page * this.size > this.teamRankData.length * this.page){
					this.showLoadMore = false
				}
			}
		}
	}
</script>

<style>
</style>
