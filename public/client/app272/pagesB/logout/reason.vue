<template>
	<view class="container">
		<view class="reason-items">
			<view class="reason-item" v-for="(item,index) in reasons" :key="index">
				<view class="checkbox" :class="{'checked':item.id == isChecked}" @click="checked(item.id)">
					<view class="checkbox-icon">
						<uni-icons type="checkmarkempty" size="18" color="#ffffff"></uni-icons>
					</view>
				</view>
				<view class="reason-content">
					<view class="left" @click="checked(item.id)">
						<view class="reason">{{ item.reason_name }}</view>
					</view>
				</view>
			</view>
		</view>
		<view class="btn-goods-action">
			<view class="btn-bar">
				<view class="btn btn-red" @click="logout()">确定注销</view>
			</view>
		</view>
	</view>
</template>

<script>
	import { mapState } from 'vuex';
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniPopup from '@/components/uni-popup.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import mpvuePicker from '@/components/mpvue-picker/mpvuePicker.vue';
	
	var  graceChecker = require("@/common/graceChecker.js");
	import * as localConfig from '@/config/local/config.js'
	import request from '@/common/request.js'
	
	const webUrl = localConfig.websiteUrl
	
	export default {
		components: {
			uniIcons,
			uniPopup,
			mpvuePicker,
			dscCommonNav
		},
		data() {
			return {
				reasons: [],
				isChecked:0,
				noClick:true,
			};
		},
		methods:{
			reasonList(){
				let that = this;
				request.post(webUrl + '/api/custom/user/reason').then(res=>{
							that.reasons = res.data
						})
			},
			checked(val){
				this.isChecked = val;
			},
			logout(){
				if (this.isChecked === 0) {
					uni.showToast({
						title:'请选择注销原因',
						icon:'none'
					})
				} else {
					if (this.noClick === true) {
						this.noClick = false;
						request.post(webUrl + '/api/custom/user/logout', {
							id:this.isChecked
						}).then(res=>{
							if (res.status == 'success') {
								uni.showToast({
									title: '您的账号已注销',
									icon:'none'
								})
								
								// 注销成功 清空token并返回首页
								uni.removeStorageSync("token");
								
								setTimeout(()=>{
									uni.switchTab({
										url:'/pages/index/index'
									});
								}, 1000);
							}else{
								uni.showToast({
									title: res.errors.message,
									icon:'none'
								})
							}
						})
						setTimeout(()=>{
							this.noClick = true;
						}, 3000);
					}
				}
			}
		},
		onLoad(e){
			this.reasonList();
		}
	}
</script>

<style>
.reason-items{ padding-bottom: 120upx; }
.reason-item{ background: #FFFFFF; padding: 20upx; display: flex; flex-direction: row; margin-bottom: 20upx;}
.reason-item:last-child{ margin-bottom: 0; }
.reason-item .reason-content{ display: flex; flex-direction: row; flex: 1; justify-content: space-between; align-items: center;}
.reason-item .reason-content .left{ flex: 1;}
.reason-item .reason-content .left view{ line-height: 1.5; font-size: 26upx;}
.reason-item .reason-content .title{ font-weight: 700;}
.reason-item .reason-content .title .name{ margin-right: 15upx;}
.reason-item .reason-content .reason{ color: #666666;}
.reason-item .reason-content .right{ display: flex; flex-direction: row;}
.reason-item .reason-content .right view{ line-height: 1.5; font-size: 26upx; padding: 20upx;}

.reason-info-show .uni-card{ margin: 0;}
.reason-info-show .uni-card .uni-list-cell-navigate{ padding: 0;}
.reason-info-show .uni-card .uni-list-cell-navigate .title{ padding: 20upx 30upx; min-width: 100upx;}
.reason-info-show .uni-card .uni-list-cell-navigate .value text{ width: 100%;}
.reason-info-show .btn-bar{ margin: 30upx 40upx; display: flex; flex-direction: column;}
.reason-info-show .btn-bar .btn{ width: 100%; margin-bottom: 30upx;}

.select_post {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 20upx;
	background-color: #FFFFFF;
	margin-bottom: 20upx;
}
</style>
