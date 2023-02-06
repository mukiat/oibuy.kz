<template>
	<view class="tabbar safe-area-inset-bottom">
		<block v-if="tabbar.type == 'team'">
			<view class="tabbar-item" :class="{'active':tabbar.index == 0}" @click="changeNav('pagesA/team/team')">
				<view class="iconfont icon-team"></view>
				<view class="txt">{{$t('lang.my_team')}}</view>
			</view>
			<view class="tabbar-item" :class="{'active':tabbar.index == 1}" @click="changeNav('pagesA/team/rank/rank')">
				<view class="iconfont icon-rank"></view>
				<view class="txt">{{$t('lang.toprank')}}</view>
			</view>
			<view class="tabbar-item" :class="{'active':tabbar.index == 2}" @click="changeNav('pagesA/team/order/order')">
				<view class="iconfont icon-user-portrait"></view>
				<view class="txt">{{$t('lang.my_pin_team')}}</view>
			</view>
		</block>
		<block v-if="tabbar.type == 'bargain'">
			<view class="tabbar-item" :class="{'active':tabbar.index == 0}" @click="changeNav('pagesA/bargain/bargain')">
				<view class="iconfont icon-team"></view>
				<view class="txt">{{$t('lang.home_bargain')}}</view>
			</view>
			<view class="tabbar-item" :class="{'active':tabbar.index == 1}" @click="changeNav('pagesA/bargain/mylist/mylist')">
				<view class="iconfont icon-user-portrait"></view>
				<view class="txt">{{$t('lang.my_bargain')}}</view>
			</view>
		</block>
		<block v-if="tabbar.type == 'presale'">
			<view class="tabbar-item" :class="{'active':tabbar.index == 0}" @click="changeNav('pagesA/presale/list/list?cat_id=0')">
				<view class="iconfont icon-cuxiao1"></view>
				<view class="txt">{{$t('lang.first_order')}}</view>
			</view>
			<view class="tabbar-item" :class="{'active':tabbar.index == 1}" @click="categoryPopup">
				<view class="iconfont icon-pailie"></view>
				<view class="txt">{{$t('lang.popular_category')}}</view>
			</view>
			<view class="tabbar-item" :class="{'active':tabbar.index == 2}" @click="changeNav('pagesA/presale/list/list?cat_id=0&type=new')">
				<view class="iconfont icon-remenzhuanti"></view>
				<view class="txt">{{$t('lang.new_product_release')}}</view>
			</view>
		</block>
		<block v-if="tabbar.type == 'crowdfunding'">
			<view class="tabbar-item" :class="{'active':tabbar.index == 0}" @click="changeNav('pagesA/crowdfunding/crowd')">
				<view class="iconfont icon-home-sy"></view>
				<view class="txt">{{$t('lang.square')}}</view>
			</view>
			<view class="tabbar-item" :class="{'active':tabbar.index == 1}" @click="changeNav('pagesA/crowdfunding/user/order')">
				<view class="iconfont icon-dingdan1"></view>
				<view class="txt">{{$t('lang.square_order')}}</view>
			</view>
			<view class="tabbar-item" :class="{'active':tabbar.index == 2}" @click="changeNav('pagesA/crowdfunding/user/user')">
				<view class="iconfont icon-home-mine"></view>
				<view class="txt">{{$t('lang.centre')}}</view>
			</view>
		</block>
	</view>
</template>

<script>
export default{
	props:['tabbar'],
	data(){
		return{
			
		}
	},
	components:{
	},
	methods:{
		changeNav(url){
			if(url != this.tabbar.curpage){
				uni.navigateTo({
					url: '/' + url
				})
			}
		},
		categoryPopup(){
			let val = this.tabbar.showBase == false ? true : false;
			this.$emit('setPopupVisible',val);
		}
	}
}
</script>

<style scoped>
.tabbar{ display: flex; flex-direction: row; height: 60px; background: #FFFFFF; position: fixed; right: 0; left: 0; bottom: 0; box-shadow: 0 0 10px 0 rgba(154, 141, 141, 0.6);}
.tabbar .tabbar-item{ flex: 1 1 0%; display: flex; flex-direction: column; justify-content: center; align-items: center;}
.tabbar .tabbar-item .iconfont{ font-size: 40upx;line-height: 1.3; color: #999999;}
.tabbar .tabbar-item .icon-team{ font-size: 46upx;}
.tabbar .tabbar-item .txt{ font-size: 24upx;line-height: 1.3; color: #999999;}
.tabbar .tabbar-item.active .iconfont,
.tabbar .tabbar-item.active .txt{ color: #f92028;}
</style>
