<template>
	<block v-if="accountlog_list.length>0">
	<view class="main">
		<view class="has-mgtb">
			<input type="number" maxlength="11" v-model="ding_d" placeholder="请输入订单号搜索" class="is-input1 " />
			<text class="sou" @click="search">搜索</text>
		</view>
			<view class="ny-list" v-for="(date,index) in accountlog_list" :key="index">
				<view class="time_list">
					<view>{{date.ymd}}</view>
					<view>
						<picker mode="date" fields="month" :value="birthday" :start="startDate" :end="endDate" @change="bindDateChange"
						 class="picker">
							<view class="uni-input" v-if="index==0">筛选</view>
						</picker>
					</view>
				</view>
				<view class="ny-item" v-for="(item,indexs) in date.source_data" :key="indexs">
					<view class="card-div">
						<block v-if="item.short_change_desc_part1">
							<view>{{ item.short_change_desc_part1}}</view>
							<view>{{ item.short_change_desc_part2}}</view>
						</block>
						<block v-else>
							<view>{{ item.short_change_desc}}</view>
						</block>
						<view class="time_price">
							<view class="time">{{ item.change_time }}</view>
							<view class="red" v-if="item.amount > 0">+{{ item.amount }}</view>
							<view class="black" v-else>{{ item.amount }}</view>
						</view>
						<!-- <view class="time">{{ item.change_time }}</view> -->
					</view>
					<!-- <view class="uni-red">{{ item.type }}{{ item.amount }}</view> -->
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
		components: {
			dscNotContent
		},
		data() {
			const currentDate = this.getDate({
				format: true
			})
			return {
				accountlog_list: [],
				birthday: currentDate,
				account_log_list_bak: [],
				ding_d: '',
				page: 1,
				size: 10,
				isLoading: true,
				ismonth_on:false,
			};
		},
		computed: {
			startDate() {
				return this.getDate('start');
			},
			endDate() {
				return this.getDate('end');
			}
		},
		methods: {
			search() {
				this.accountdetails()
			},
			bindDateChange: function(e) {
				this.birthday = e.target.value
				this.accountdetails(1)
				this.ismonth_on=true
			},
			accountdetails(page) {
				this.isLoading = true;
				
				if (page) {
					this.page = page
					this.size = Number(page) * 10
				}
				uni.request({
					url: this.websiteUrl + '/api/account/accountlog',
					method: 'GET',
					data: {
						order_sn: this.ding_d,
						month: this.ismonth_on ? this.birthday : '',
						page: this.page,
						size: this.size
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: (res) => {
						this.isLoading = false;
						if (res.data.status == 'success') {
							let data = res.data.data;

							let copyList = JSON.parse(JSON.stringify(this.accountlog_list))
							let newArr = []
							if (this.page == 1) {
								this.accountlog_list = data
							} else {	let copyList = JSON.parse(JSON.stringify(this.accountlog_list))
							let newArr = []
							let nameList = []
							copyList.map(item => {
								if (nameList.indexOf(item.ymd) == -1) {
									nameList.push(item.ymd)
								}
							})
							let list = [];
							data.map(key => {
								if (nameList.indexOf(key.ymd) == -1) {
									list.push(key)
								}
							})
							data.forEach((item, index) => {
								copyList.forEach((v, i) => {
									if (item.ymd == v.ymd) v.source_data = [...v.source_data, ...item.source_data]

									// else newArr.push(item)
								})

							})
							this.accountlog_list = [...copyList, ...list]}
						}
					}
				})


			},
			getDate(type) {
				const date = new Date();

				let year = date.getFullYear();
				let month = date.getMonth() + 1;
				// let day = date.getDate();

				if (type === 'start') {
					year = year - 60;
				} else if (type === 'end') {
					year = year + 2;
				}
				month = month > 9 ? month : '0' + month;;
				// day = day > 9 ? day : '0' + day;
				return `${year}-${month}`;
			},
		},
		onLoad() {
			this.accountdetails(1)
		},
		onReachBottom() {
			if (this.page * this.size == this.account_log_list_bak) {
				this.page++
				
				this.accountdetails()
			}
		},
	watch: {
		accountlog_list() {
			this.account_log_list_bak = 0
			//数组格式化
			let accountlog_list = JSON.parse(JSON.stringify(this.accountlog_list))
	
			accountlog_list.forEach((v,i)=>{
				this.account_log_list_bak+=v.source_data.length
				// v.source_data.forEach((s,k)=>{
				// 	this.account_log_list_bak.push(s)
				// })
			})
		},
	},
		onShareAppMessage(res) {
			return {
				title: this.$store.state.common.shopConfig.shop_title,
				path: '/pagesB/account/detail/detail'
			}
		},
	}
</script>

<style scoped>
	.time_list .uni-input {
		padding: 0 15upx !important;
	    background: #F6F6F6;

	}

	.main {
		height: 100%;
		background: #fff;
	}

	.has-mgtb {
		display: flex;
		justify-content: space-around;
		width: 92%;
		-webkit-flex-wrap: nowrap;
		flex-wrap: nowrap;
		margin: auto;
		padding-top: 20upx;
	}

	.has-mgtb input {
		background-color: #F6F6F6;
		border-radius: 20px;
		width: 80%;
		height: 30px;
		line-height: 30px;
		padding: 0 4%;

	}

	.has-mgtb .sou {
		font-size: 30upx;
		color: #A0A0A0;

	}

	.ny-item {
		padding: 20upx 30upx;
		margin: 0 20upx;
		border-bottom: 1px solid #F6F6F6;
	}

	.ny-item view {
		margin-top: 6upx;
	}

	.time_price {
		display: flex;
		justify-content: space-between;
	}

	.red {
		color: red;
		font-size: 32upx;
		font-weight: 600;
	}

	.black {
		color: #333;
		font-size: 32upx;
		font-weight: 600;
	}

	.time_list {
		background-color: #F6F6F6;
		padding: 15upx;
		margin: 0 20upx;
		margin-top: 20upx;
		font-size: 32upx;
		font-weight: bold;
		display: flex;
		justify-content: space-between;
	}
</style>
