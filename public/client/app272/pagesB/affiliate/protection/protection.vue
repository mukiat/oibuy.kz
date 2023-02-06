<template>
	<view class="container">
		<view class="drp-protection">
			<swiper class="swiper-thumbs" previous-margin="120" next-margin="120" display-multiple-items="1" :duration="300" :current="current" @change="swiperTopChange">
				<swiper-item v-for="(item,index) in protectionList.list" :key="index">
					<view class="swiper-item-warp">
					<icon class="icon"><image :src="item.icon" class="img" mode="widthFix" /></icon>
					<text>{{item.name}}</text>
					</view>
				</swiper-item>
			</swiper>
			<swiper class="swiper-top" previous-margin="60" :duration="300" :current="current" :style="{height:winHeight + 'px'}" @change="swiperTopChange">
				<swiper-item class="swiper-item" :class="{'swiper-item-active':(current == index),'swiper-item-next':(current+1 == index),'swiper-item-prev':(current-1 == index)}" v-for="(item,index) in protectionList.list" :key="index">
					<view class="protection-con">
						<view class="title">{{item.name}}</view>
						<view class="value">
							<view class="tit">{{$t('lang.vip_protection')}}</view>
							<view class="text">{{item.description}}</view>
							<view class="tit" v-if="item.rights_configure_format">{{$t('lang.preferential_content')}}</view>
							<view class="text" v-if="item.rights_configure_format">{{item.rights_configure_format}}</view>
							<block v-if="item.code == 'customer' && item.rights_configure[0].value">
								<view class="tit">{{$t('lang.equity_rule')}}</view>
								<view class="text">{{item.rights_configure[0].label}}:<text style="color: #007aff;" @click="makePhone(item.rights_configure[0].value)">{{item.rights_configure[0].value}}</text></view>
							</block>
						</view>
					</view>
				</swiper-item>
			</swiper>
		</view>

		<dsc-common-nav>
			<navigator url="../drp" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.my_drp')}}</text>
			</navigator>
		</dsc-common-nav>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';

	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';

	export default {
		data() {
			return {
				rank_id:'',
				current:0,
				winHeight:'',
				protectionList:[]
			}
		},
		components:{
			uniIcons,
			dscCommonNav,
			dscNotContent
		},
		onLoad(e) {
			this.rank_id = e.rank_id;
			this.current = e.index ? Number(e.index) : 0;

			this.load();

			this.winHeight = uni.getSystemInfoSync().windowHeight * .6;
		},
		computed: {

		},
		methods: {
			load(){
				uni.request({
					url:this.websiteUrl + '/api/user/rank_rights_list',
					method:'POST',
					data:{
						rank_id: this.rank_id
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						this.protectionList = res.data.data;
					}
				})
			},
			swiperTopChange(e){
				this.current = e.target.current
			},
			makePhone(val){
				if(val){
					uni.makePhoneCall({
					    phoneNumber: val
					});
				}else{
					uni.showToast({ title:this.$t('lang.customer_service_phone'), icon:'none' });
				}
			},
		}
	}
</script>

<style scoped>
.container{ height: 100%; }
.drp-protection{ background: #151515; height: 100%;}
.swiper-thumbs{ padding: 60upx 0 0;}
.swiper-thumbs .swiper-item-warp{ width: 65%; margin: 0 auto; display: flex; flex-direction: column; justify-content: center; align-items: center;}
.swiper-thumbs .icon{ width:100%;}
.swiper-thumbs .icon .img{ border-radius: 50%; }
.swiper-thumbs text{ color: #FFFFFF; margin-top: 10upx;}
.swiper-top .swiper-item{ width: 80% !important;}
.swiper-top .swiper-item .protection-con{transform-style: preserve-3d; width: 80%; height: calc(100% - 100upx); overflow-y: auto; margin: 20upx auto; padding: 40upx; background: #FFFFFF; border-radius: 20upx; font-size: 28upx;}
.swiper-top .swiper-item .protection-con .title{ font-size: 36upx; text-align: center; font-weight: 700;}
.swiper-top .swiper-item .protection-con .value .tit{ margin: 30upx 0 10upx; font-size: 30upx; font-weight: 700;}

.swiper-item-next .protection-con,.swiper-item-prev .protection-con{
	transform: scale(.8) !important;
	opacity: 0.5;
	position: relative;
}
.swiper-item-active .protection-con::before{
	position: absolute;
	content: " ";
	width:0;
	height:0;
	border-right:16upx solid transparent;
	border-left:16upx solid transparent;
	border-bottom:20upx solid white;
	top: 0;
	left: 50%;
	margin-left: -8upx;
}
</style>
