<template>
	<block v-if="accountlog_list.length>0">
		<view class="growth">
			<view class="ny-item" v-for="(item,index) in accountlog_list" :key="index">
				<view class="card-div">
					<view>{{ item.short_change_desc }}</view>
				</view>
				<view class="growth_time">
					<view class="time">{{ item.change_time }}</view>
					<view class="uni-red" v-if="item.type=='+'">{{item.type}}<text>{{ item.rank_points }}</text></view>
					<view class="uni-red green" v-else>{{item.type}}<text>{{ item.rank_points }}</text></view>
				</view>
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
		onLoad() {
			this.setRefoundList(1)
		},
		methods: {
			//�б�
			setRefoundList(page) {
				this.isLoading = true;
				if (page) {
					this.page = page
					this.size = Number(page) * 10
				}
				uni.request({
					url: this.websiteUrl + '/api/account/paypoints',
					method: 'GET',
					data: {
						page: this.page,
						size: this.size,
						type: 'rank_points'
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
								this.accountlog_list = this.accountlog_list.concat(res.data.data); //������ƴ����һ��
							}

						}
					}
				})


			},
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
				path: '/pagesB/growthvalue/growthvalue'
			}
		},
	}
</script>

<style scoped>
	.growth {
		background: #fff;
		height: 100%;
	}

	.growth_time {
		display: flex;
		justify-content: space-between;
		margin-top: 10upx;
	}

	.ny-item {
		margin-bottom: 0upx;
		position: relative;
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

	.green {
		color: #33CC66;
		font-weight: 600;
	}
</style>
