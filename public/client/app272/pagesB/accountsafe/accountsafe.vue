<template>
	<view>
		<view class="uni-card uni-card-not">
			<view class="uni-list">
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="$outerHref('/pagesB/login/forget/forget','app')">
					<view class="uni-list-cell-navigate uni-navigate-right">
						<view>
							<text>{{$t('lang.reset_password')}}</text>
							<view class="title">{{$t('lang.open_pwds1')}}</view>
						</view>
						<view class="value"></view>
					</view>
				</view>
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="paypwd">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<view>
							<text>{{$t('lang.open_password')}}</text>
							<view class="title">{{$t('lang.open_pwds')}}</view>
						</view>
						<view class="value uni-red" v-if="accountsafe.users_paypwd == 1">{{$t('lang.open_xiu')}}</view>
						<view class="value uni-red" v-else>{{$t('lang.open_not')}}</view>
					</view>
				</view>
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="$outerHref('/pagesB/accountsafe/bindphone/bindphone','app')" v-if="accountsafe.mobile_phone==''">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<view>
							<text>{{$t('lang.bind_phone')}}</text>
							<view class="title">{{$t('lang.open_pwds3')}}</view>
						</view>
					<view class="value"></view>
					</view>
				</view>
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="resetphone" v-else>
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<view>
							<text>{{$t('lang.set_phone')}}</text>
							<view class="title">{{$t('lang.open_pwds3')}}</view>
						</view>
					<view class="value"></view>
					</view>
				</view>
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="$outerHref('../../pagesB/accountsafe/operationlog/operationlog',$isLogin())">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<view>
							<text>{{$t('lang.operation_log')}}</text>
							<view class="title">{{$t('lang.open_pwds4')}}</view>
						</view>
					<view class="value"></view>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	import universal from '@/common/mixins/universal.js';
	export default {
		mixins:[universal],
		data() {
			return {
				accountsafe:{
					is_connect_user:'',
					is_validated:'',
					users_paypwd:'',
					mobile_phone:''
				}
			}
		},
		methods: {
			resetphone(){
				uni.navigateTo({
					url:'/pagesB/accountsafe/resetphone/resetphone?id='+this.accountsafe.mobile_phone
				})
			},
			paypwd(){
				uni.navigateTo({
					url:'paypwd/paypwd'
				})
			}
		},
		onShow(){
			uni.request({
				url:this.websiteUrl + '/api/accountsafe',
				method:'GET',
				data:{},
				header: {
					'Content-Type': 'application/json',
					'token': uni.getStorageSync('token'),
					'X-Client-Hash':uni.getStorageSync('client_hash')
				},
				success: (res) => {
					if(res.data.status == 'success'){
						this.accountsafe = res.data.data;
					}else{
						uni.showToast({
							title:this.$t('lang.get_lod_url'),
							icon:'none'
						})
					}
				}
			})
		}
	}
</script>

<style scoped>
.title{ font-size: 25upx; }
</style>
