<template>
    <view class="container fixed-bottom-padding">
		<view class="drp-select">
			<view class="header">
				<view class="header-warp">
					<view class="header-img">
						<image :src="crowdUserData.user_picture" v-if="crowdUserData.user_picture" />
						<image :src="imagePath.userDefaultImg" v-else></image>
					</view>
					<view class="header-flex">
						<view class="shop-name">{{crowdUserData.user_name}}</view>
						<view class="shop-rank">{{$t('lang.user_rank')}}{{crowdUserData.rank_name ? crowdUserData.rank_name : ''}}</view>
					</view>
				</view>
				<view class="header-bg"><image :src="imagePath.wdBg" class="img"></image></view>
			</view>
		</view>
		
		<view class="my-nav-box">
			<navigator @click="$outerHref('/pagesA/crowdfunding/user/order','app')" hover-class="none" class="item">
				<view class="tit"><text class="iconfont icon-personal-money uni-red"></text>{{$t('lang.my_order')}}</view>
				<uni-icons type="arrowright" size="18" color="#999999"></uni-icons>
			</navigator>
			<navigator @click="$outerHref('/pagesA/crowdfunding/user/buy','app')" hover-class="none" class="item">
				<view class="tit"><text class="iconfont icon-find-zan uni-red"></text>{{$t('lang.my_support')}}</view>
				<uni-icons type="arrowright" size="18" color="#999999"></uni-icons>
			</navigator>
			<navigator @click="$outerHref('/pagesA/crowdfunding/user/focus','app')" hover-class="none" class="item">
				<view class="tit"><text class="iconfont icon-guanzhu uni-red"></text>{{$t('lang.my_interest')}}</view>
				<uni-icons type="arrowright" size="18" color="#999999"></uni-icons>
			</navigator>
		</view>   
		
		<view class="my-nav-box">
			<navigator hover-class="none" class="item">
				<view class="tit"><text class="iconfont icon-find-live-time uni-red"></text>{{$t('lang.item_recommendation')}}</view>
			</navigator>
		</view>
		
		<!--商品列表-->
		<view class="goods-list" v-if="crowdBestData && crowdBestData.length > 0">
			<view class="goods-item" v-for='(item,index) in crowdBestData' :key="index" @click="$outerHref('/pagesA/crowdfunding/detail/detail?id='+item.id,'app')">
				<view class="goods-left uni-flex-common">
					<image :src="item.title_img" class="img" mode="widthFix" v-if="item.title_img" />
					<image src="../../../static/not_goods.png" class="img" mode="widthFix" v-else />
				</view>
				<view class="goods-right">
					<view class="goods-name twolist-hidden">{{item.title}}</view>
					<view class="goods-cont uni-flex-common uni-space-between">
						<view class="text">{{$t('lang.support_number')}}<text class="uni-red">{{item.join_num}}</text>{{$t('lang.ren')}}</view>
						<view class="text">{{$t('lang.time_remaining')}}{{item.shenyu_time}}{{$t('lang.tian')}}</view>
					</view>
					<view class="ect-progress">
						<progress :percent="item.baifen_bi" show-info="true" border-radius="3" stroke-width="6" font-size="12" active="true" activeColor="#f92028"></progress>
					</view>
					<view class="goods-cont uni-flex-common uni-space-between">
						<view class="text">{{$t('lang.has_crowdfunding')}}<text class="uni-red">{{item.join_money_formated}}</text></view>
						<view class="text">{{$t('lang.target')}}<text class="uni-red">{{item.amount_formated}}</text></view>
					</view>
				</view>
			</view>
		</view>
		<view v-else>
			<dsc-not-content></dsc-not-content>
		</view>
		<dsc-tabbar :tabbar="tabbar"></dsc-tabbar>
		<dsc-common-nav></dsc-common-nav>		
    </view>
</template>
<script>
	import { mapState } from 'vuex'
	import dscTabbar from '@/components/dsc-tabbar.vue';
	import dscNotContent from '@/components/dsc-not-content.vue'; 
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';

    export default {
        name: "auction-detail",
        components: {
            dscTabbar,
			dscNotContent,
			uniIcons,
			dscCommonNav
        },
        data() {
            return {
                routerName:'crowd_funding',
                disabled:false,
                loading:true,
                size:10,
                page:1,
				tabbar:{
					type:'crowdfunding',
					index:2,
					curpage:''
				}
            }
        },
        //初始化加载数据
        created() {
            this.$store.dispatch('setCrowdfundingUser')
            this.crowdGoods()
        },
        computed: {
            ...mapState({
                crowdUserData: state => state.crowdfunding.crowdUserData,
            }),
            crowdBestData:{
                get(){
                    return this.$store.state.crowdfunding.crowdBestData
                },
                set(val){
                    this.$store.state.crowdfunding.crowdBestData = val
                }
            }
        },
        methods: {
            loadMore(){
                setTimeout(() => {
                    this.disabled = true
                    if(this.page * this.size == this.crowdGoodsData.length){
                        this.page ++
                        this.crowdGoods()		
                    }
                },200);
            },
            crowdGoods() {
                this.$store.dispatch('setCrowdfundingBest',{
                    page:this.page,
                    size:this.size
                })
            },
            detailClick(item){
                this.$router.push({
                    name:'crowdfunding-detail',
                    params:{
                        id:item.id
                    }
                })
            }
        },
		onReachBottom(){
			if(this.page * this.size == this.crowdBestData.length){
				this.page ++
				this.crowdGoods()
			}
		}
    };
</script>
<style scoped>
	.drp-select .header{ background-size: 100% 100%; padding: 40upx 20upx; color: #FFFFFF; height: 230upx; box-sizing: border-box;}
	.drp-select .header-warp{ display: flex; flex-direction: row; align-items: center; position: relative; z-index: 2;}
	.drp-select .header-warp .header-img{ width: 140upx; height: 140upx; overflow: hidden; border-radius: 100%; border:2px solid rgba(255,255,255,.6); }
	.drp-select .header-warp .header-img image{ width: 100%; height: 100%;}
	.drp-select .header-warp .header-flex{ flex: 1 1 0%; padding: 0 20upx;}
	.drp-select .header-warp .header-flex .shop-name{ font-size: 32upx;}
	.drp-select .header-warp .header-flex .shop-rank{ font-size: 26upx; color: rgba(255,255,255,.9);}
	.drp-select .header .header-bg{ position: absolute; top: 0; left: 0; right: 0; width: 100%; height: 230upx; z-index: 1;}

	/* 导航 */
	.my-nav-box {margin-top:20rpx;border-radius:10rpx;background:#FFFFFF;}
	.my-nav-box .item {display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;padding:20rpx;border-bottom:1px solid #f6f6f9;-ms-flex-pack:justify;
	justify-content:space-between;-webkit-box-align:center;-webkit-align-items:center;-ms-flex-align:center;align-items:center;}
	.my-nav-box .item .tit .iconfont {margin-right:10rpx;font-size:14px;}
	
	.goods-list .goods-cont{ font-size: 25upx; color: #999; line-height: 1.5;}
	.goods-list .goods-cont .uni-red{ margin: 0 5upx;}
</style>