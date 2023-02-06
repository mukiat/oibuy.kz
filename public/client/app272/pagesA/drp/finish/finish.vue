<template>
	<view class="drp-warp">
		<view class="header">
			<view class="title">{{$t('lang.drp_status_propmt_4')}}</view>
			<view class="subtitle">{{$t('lang.drp_status_propmt_5')}}</view>
		</view>
		<view class="finish-detail">
			<view class="finish-detail-warp">
				<view class="left">
					<view class="icon"><view class="iconfont icon-dianpu"></view></view>
				</view>
				<view class="con">
					<view class="name">{{drpRegendData.shop_name}}</view>
					<view class="mobile">{{drpRegendData.mobile}}</view>
				</view>
				<view class="more">
					<text>{{$t('lang.go_drp_center')}}</text>
					<uni-icons type="forward" size="16" color="#FFFFFF"></uni-icons>
				</view>
			</view>
			<view class="tag">{{$t('lang.shop_detail')}}</view>
		</view>
		<view class="tips">
			<view class="tit">{{$t('lang.must_be_read')}}</view>
			<view class="con">
				<rich-text :nodes="drpRegendData.novice"></rich-text>
			</view>
		</view>
		
		<dsc-common-nav>
			<navigator url="../drp" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.drp_center')}}</text>
			</navigator>
		</dsc-common-nav>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	
	export default {
		data() {
			return {
				
			}
		},
		components:{
			uniIcons,
			dscNotContent,
			dscCommonNav,
		},
		onLoad() {
			this.load()
		},
		computed: {
			...mapState({
				drpRegendData: state => state.drp.drpRegendData,
			})
		},
		methods: {
			load(){
				this.$store.dispatch('setDrpRegend')
			}
		}
	}
</script>

<style>
.drp-warp{ padding: 30upx;}
.drp-warp .header{ padding: 20upx 0 40upx;}
.drp-warp .header .title{ font-size: 40upx; color: #333333; font-weight: 700; }
.drp-warp .header .subtitle{ font-size: 25upx; color: #777777; }

.finish-detail{ background: linear-gradient(90deg,#ffb95e,#ec5151); padding: 80upx 0; border-radius: 10upx; position: relative;}
.finish-detail-warp{ display: flex; flex-direction: row; }
.finish-detail-warp .left{ width: 180upx; display: flex; flex-direction: row; justify-content: center; }
.finish-detail-warp .left .icon{ width: 100upx; height: 100upx; border: 1px solid #fff; border-radius: 100%; display: flex; flex-direction: row; justify-content: center; align-items: center; }
.finish-detail-warp .left .iconfont{ font-size: 55upx; color: #FFFFFF; }
.finish-detail-warp .con{ flex: 1 1 0%; color: #FFFFFF; }
.finish-detail-warp .con .name{ font-size: 32upx; }
.finish-detail-warp .con .mobile{ font-size: 25upx;}
.finish-detail-warp .more{ color: #FFFFFF; display: flex; flex-direction: row; justify-content: center; align-items: center;}
.finish-detail-warp .more text{ height: 40upx; line-height: 34upx;}
.tag{ position: absolute; background: linear-gradient(90deg,#ffb95e,#ec5151); padding: 4upx 20upx; right: 0; top: 0; border-radius: 0 8upx 0 12upx; color: #ffffff; font-size: 24upx;}

.tips{ margin-top: 40upx;}
.tips .tit{ color: #777777; font-size: 30upx;}
.tips .con{ color: #777777; font-size: 25upx;}
</style>
