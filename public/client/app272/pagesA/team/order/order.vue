<template>
	<view class="fixed-bottom-padding">
		<view class="comment-tabs">
			<uni-segmented-control :current="status" :values="items" style-type="text" active-color="#f92028" @clickItem="onClickItem" />
		</view>
		<block v-if="!showLoadMoreTab">
			<view class="goods-list">
				<block v-if="teamOrderData && teamOrderData.length > 0">
					<view class="goods-item" v-for="(item,index) in teamOrderData" :key="index">
						<view class="item-bd" @click="goodsLink(item.goods_id)">
							<view class="goods-left"><image :src="item.goods_thumb" class="img" /></view>
							<view class="goods-right">
								<view class="goods-name twolist-hidden">{{item.goods_name}}</view>
								<view class="plan-box"><view class="shop-price">{{$t('lang.already_have')}}{{item.limit_num}}{{$t('lang.men_tuxedo')}}</view></view>
								<view class="plan-box"><view class="price">{{item.team_price}}</view></view>
							</view>
						</view>
						<view class="item-fd">
							<view class="btn-bar-min">
								<view class="btn btn-bor-red" v-if="item.is_pay == 0 && item.status == 0" @click="onlinepay(item.order_sn)">{{$t('lang.immediate_pay')}}</view>
								<view class="btn" @click="detailLink(item.order_id)">{{$t('lang.view_order')}}</view>
								<view class="btn btn-bor-red" v-if="item.is_pay == 1 || item.status == 2" @click="waitHandle(item.team_id,item.user_id)">{{$t('lang.team_schedule')}}</view>
							</view>
						</view>
						<view class="icon" v-if="status == 1"><image :src="imagePath.sBg" class="img" mode="widthFix" /></view>
						<view class="icon" v-if="status == 2"><image :src="imagePath.sbBg" class="img" mode="widthFix" /></view>
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
					this.$t('lang.teaminfo_ing'),
					this.$t('lang.teaminfo_success'),
					this.$t('lang.teaminfo_fail')
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
					index:2,
					curpage:''
				},
				team_id:0				
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
			if(this.page * this.size == this.teamOrderData.length){
				this.page ++
				this.teamOrder()
			}else{
				this.loadMoreStatus = "noMore"
				return;
			}
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesA/team/order/order'
			}
		},
		computed: {
            teamOrderData:{
                get(){
                    return this.$store.state.team.teamOrderData
                },
                set(val){
                    this.$store.state.team.teamOrderData = val
                }
            }
        },
		methods: {
			onClickItem(index) {
				if (this.status !== index) {
					this.status = index
				}
				
				this.teamOrder();
			},
			teamOrder() {
				this.showLoadMore = true;
				this.showLoadMoreTab = true;
				this.loadMoreStatus = 'loading';
                this.$store.dispatch('setTeamOrder',{
                    status: this.status,
                    size:this.size,
					page:this.page,
                });
            },
			//立即支付
            onlinepay(id){
				uni.navigateTo({
					url:'/pages/done/done?order_sn='+id
				})
            },
			goodsLink(id){
				uni.navigateTo({
					url:'/pagesA/team/detail/detail?goods_id='+id+'&team_id'+this.team_id
				})
			},
            detailLink(order_id){
				uni.navigateTo({
					url:"/pagesB/orderDetail/orderDetail?id="+order_id
				})
            },
            //拼团进度
            waitHandle(team_id,user_id){
                uni.navigateTo({
                	url:"/pagesA/team/wait/wait?team_id="+team_id+'&user_id='+user_id
                })
            },
		},
		onLoad(e){
			let pages = getCurrentPages()
			this.tabbar.curpage = pages[pages.length - 1].route
			
			this.teamOrder();
		},
		watch:{
			teamOrderData(){
				this.dscLoading = false
				this.showLoadMoreTab = false
				if(this.page * this.size > this.teamOrderData.length * this.page){
					this.showLoadMore = false
				}
			}
		}
	}
</script>

<style scoped>
.goods-list{ margin-top: 20upx;}
.goods-list .goods-item{ margin-bottom: 20upx; flex-direction: column; padding: 0; position: relative;}
.item-bd{ display: flex; flex-direction: row; padding: 20upx;}
.item-fd{ border-top: 1px solid #f4f4f4; }
.icon{ position: absolute; width: 250upx; height: 180upx; top: 20upx; right: 0;}
</style>
