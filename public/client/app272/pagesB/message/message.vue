<template>
	<view class="fixed-bottom-padding">
		<view class="user-consult">
			<view v-for="(item,index) in list" :key="index">
				<view class="cons-list">
					<view class="content">
						<view class="name">{{ item.msg_time }}</view>
						<view class="msg"><view class="cons-cont">{{ item.msg_content }}</view></view>
					</view>
					<view class="cons-head-img"><image :src="info.user_picture" v-if="info.user_picture" class="img"></image></view>
				</view>
				<view class="cons-list cons-list-2" v-if="item.re_msg_content">
					<view class="cons-head-img"><view class="iconfont icon-kefu"></view></view>
					<view class="content">
						<view class="name">{{item.re_user_name}}{{item.re_msg_time}}</view>
						<view class="msg"><view class="cons-cont">{{ item.re_msg_content }}</view></view>
					</view>
				</view>
			</view>
		</view>
		<view class="btn-goods-action">
			<view class="submit-bar-text submit-bar-text-left"><input name="msg_title" v-model="msg_title" :placeholder="$t('lang.message_placeholder')"/></view>
			<view class="btn-bar">
				<view class="btn btn-red" @click="onSubmit">{{$t('lang.subimt')}}</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				msg_title:'',
				info:'',
				list:[],
			};
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/message/message'
			}
		},
		methods:{
			default(){
				uni.request({
					url:this.websiteUrl + '/api/feedback',
					method:'GET',
					data:{
						page:1,
						size:10
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						this.info = res.data.data.info
						this.list = res.data.data.message_list
					}
				})
			},
			onSubmit(){
				if(this.msg_title != ''){
					var that = this;
					uni.request({
						url:this.websiteUrl + '/api/feedback/create',
						method:'POST',
						data:{
							msg_title:this.msg_title
						},
						header: {
							'Content-Type': 'application/json',
							'token': uni.getStorageSync('token'),
							'X-Client-Hash':uni.getStorageSync('client_hash')
						},
						success: (res) => {
							if(res.data.status=='success'){
								uni.showToast({ title: this.$t('lang.add_success'), icon: "success" });
								that.msg_title = "";
								this.default();
							}
						}
					})
				}else{
					uni.showToast({ title: this.$t('lang.message_not_null'), icon: "none" });
				}
			}
		},
		onLoad(){
			this.default()
		}
	}
</script>

<style>
.user-consult{ padding:20upx;}
.cons-list{ display: flex; flex-direction: row; margin-bottom: 50upx;}
.cons-list .content{ flex: 1; text-align: right; margin-right: 20upx;}
.cons-list .content .name{ font-size: 25upx; color: #666666;}
.cons-list .content .msg{ float: right;}
.cons-list .content .cons-cont{ background: #e7e8ef; color: #333333; border-radius: 10upx; padding: 10upx 20upx; position: relative;}
.cons-list .content .cons-cont:after{ content: " "; display: block; position: absolute; width: 20upx; height: 20upx; top: 20upx; transform: rotate(45deg);}
.cons-list .cons-head-img{ width: 100upx; height: 100upx; border-radius: 100%; overflow: hidden;}

.cons-list-2 .cons-head-img{ background: #e7e8ef; display: flex; justify-content: center; align-items: center; margin-right: 20upx;}
.cons-list-2 .cons-head-img .iconfont{ font-size: 42upx;color: #a6a6a6;}
.cons-list-2 .content{ text-align: left; }
.cons-list-2 .content .msg{ float: left; }
.cons-list-2 .content .cons-cont{ background: #ec5151; color: #FFFFFF;}
</style>
