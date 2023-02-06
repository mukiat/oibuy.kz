<template>
	<view>
		<block v-if="accountlog_list.length > 0">
		<view class="ny-list">
			<view class="ny-item" v-for="(item,index) in accountlog_list" :key="index">
				<view class="ny-log">
					<view>{{ item.deposit_type_format }}</view>
					<view>{{ item.money_format }}</view>
				</view>
				<view class="ny-log">
					<view class="time">{{ item.add_time_formats }}</view>
					<view class="uni-red"><block v-if="item.check_status_format">{{ item.check_status_format }} - </block>{{ item.deposit_status_format }}</view>
				</view>
			</view>
		</view>
		</block>
		<block v-else>
			<dsc-not-content></dsc-not-content>
		</block>
	</view>
</template>

<script>
	import dscNotContent from '@/components/dsc-not-content.vue';
	export default {
		data() {
			return {
				accountlog_list:[]
			};
		},
		components:{
			dscNotContent
		},
		onLoad(){
			uni.request({
				url:this.websiteUrl + '/api/drp/transfer_list',
				method:'POST',
				data:{},
				header: {
					'Content-Type': 'application/json',
					'token': uni.getStorageSync('token'),
					'X-Client-Hash':uni.getStorageSync('client_hash')
				},
				success: (res) => {
					if(res.data.status == 'success'){
						this.accountlog_list = res.data.data
					}
				}
			})
		}
	}
</script>

<style>

</style>
