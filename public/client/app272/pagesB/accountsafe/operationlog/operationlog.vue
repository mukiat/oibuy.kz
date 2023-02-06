<template>
	<block v-if="accountlog_list.length>0">
		<view class="growth">
			<view class="ny-item" v-for="(item,index) in accountlog_list" :key="index">
				<view class="card-div">
					<view>{{item.login_type}}<text class="card_t">{{item.from}}</text>{{item.change_city}}</view>
				</view>
				<view class="time">{{item.change_time}}</view>
				<view class="uni-red">{{item.ip}}</view>
			</view>
		</view>
	</block>
	<block v-else-if="!isLoading">
		<dsc-not-content></dsc-not-content>
	</block>
</template>

<script>
	import dscNotContent from '@/components/dsc-not-content.vue';
	export default {
		data() {
			return {
				accountlog_list: [],
				page: 1,
				size: 10,
				isLoading: false
			};
		},
		components: {
			dscNotContent
		},
		methods: {
			//列表
			setRefoundList(page) {
				this.isLoading = true;
				if (page) {
					this.page = page
					this.size = Number(page) * 10
				}
				uni.request({
					url:this.websiteUrl + '/api/user/user_log',
					method:'GET',
					data:{
						page:this.page,
						size:this.size
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: (res) => {
						this.isLoading = false;
						if (res.data.status == 'success') {
							if (this.page == 1) {
								this.accountlog_list = res.data.data
							} else {
								this.accountlog_list = this.accountlog_list.concat(res.data.data); //将数据拼接在一起
							}

						}
					}
				})


			},
		},
		onLoad() {
			this.setRefoundList(1)
		},
		onReachBottom() {
			if (this.page * this.size == this.accountlog_list.length) {
				this.page++
				this.setRefoundList()
			}
		},
		onShareAppMessage(res) {
			return {
				title: this.$store.state.common.shopConfig.shop_title,
				path: '/pagesB/operationlog/operationlog'
			}
		},
		watch: {
			accountlog_list() {
				let value = ''
				this.accountlog_list.forEach((v, i) => {
					value = v.from.toLowerCase();
					value = value.charAt(0).toUpperCase() + value.slice(1);
					v.from = value
				})
			},
		},

	}
</script>

<style scoped>
	.growth {
		height: 100%;
		background: #fff;
	}

	.ny-item {
		margin-bottom: 0upx;
		position: relative;
	}

	.card_t {
		padding-left: 10upx;
		padding-right: 10upx;
	}

	.ny-item:after {
		content: "";
		left: 20upx;
		right: 20upx;
		bottom: 0;
		height: 1px;
		background-color: #E5E5E5;
		position: absolute;

}
</style>
