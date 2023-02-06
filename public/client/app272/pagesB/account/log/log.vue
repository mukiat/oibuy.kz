<template>
	<view>
		<block v-if="accountlog_list.length > 0">
		<view class="ny-list">
			<view class="ny-item" v-for="(item,index) in accountlog_list" :key="index">
				<view class="ny-log">
					<view>{{ item.type }}</view>
					<view>{{ item.amount }}</view>
				</view>
				<view class="ny-log">
					<view class="time">{{ item.add_time }}</view>
					<view class="uni-red">{{ item.pay_status }}</view>
				</view>
			</view>
		</view>
		</block>
		<block v-else-if="!isloading">
			<dsc-not-content></dsc-not-content>
		</block>
	</view>
</template>

<script>
	import dscNotContent from '@/components/dsc-not-content.vue';
	export default {
		data() {
			return {
				accountlog_list:[],
				isloading: true
			};
		},
		components:{
			dscNotContent
		},
		onLoad(){
			uni.request({
				url:this.websiteUrl + '/api/account/replylog',
				method:'GET',
				data:{},
				header: {
					'Content-Type': 'application/json',
					'token': uni.getStorageSync('token'),
					'X-Client-Hash':uni.getStorageSync('client_hash')
				},
				success: (res) => {
					this.isloading = false;
					if(res.data.status == 'success'){
						this.accountlog_list = res.data.data
					}
				}
			})
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/account/log/log'
			}
		},
	}
</script>

<style>

</style>
