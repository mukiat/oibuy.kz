<template>
	<div class="main">
		<div class="store_info" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<div class="main_box">
			<div class="integral_t">{{$t('lang.current_shop')}}<i>{{type}}</i>{{$t('lang.current_shop1')}}</div>
			<div class="integral_n">{{$t('lang.current_shop2')}}</div>
		</div>
		<div class="integral_title">{{$t('lang.my_points')}}</div>
		<div class="ny-item" v-for="(item,index) in accountlog_list" :key="index">
			<div class="card-div">
				<div class="tit">{{ item.short_change_desc }}</div>
				<div class="time_price">
					<div class="time">{{ item.change_time }}</div>
					<div class="red" v-if="item.type=='+'">{{item.type}}{{ item.pay_points }}</div>
					<div class="green" v-else>{{item.type}}{{ item.pay_points }}</div>
				</div>
			</div>
		</div>
</div>
	</div>
</template>

<script>
	import {
		List,
		Cell,
	Waterfall,
	} from 'vant'

	import CommonNav from '@/components/CommonNav'
	import NotCont from '@/components/NotCont'
	import arrRemove from '@/mixins/arr-remove'

	export default {
		data() {
			return {
				loading: false,
				type: this.$route.query.type,
				accountlog_list: [],
				page: 1,
				size: 10,
			}
		},
		components: {
			[List.name]: List,
			[Cell.name]: Cell,
			NotCont,
			CommonNav
		},
		created() {
			this.setShopList(1)
		},
		directives: {
		    WaterfallLower: Waterfall('lower')
		},
		methods: {
			setShopList(page) {
				if (page) {
					this.page = page
					this.size = Number(page) * 10
				}
				let o = {
					page: this.page,
					size: this.size,
					type: 'pay_points'
				}
				this.$http.get(`${window.ROOT_URL}api/account/paypoints`, {
					params: o
				}).then(res => {
					if(res.data.status == 'success'){
						if (this.page == 1) {
							this.accountlog_list = res.data.data
						}else{
							this.accountlog_list = this.accountlog_list.concat(res.data.data); //将数据拼接在一起
						}

					}
				})
			},
			loadMore() {
				setTimeout(() => {
					this.disabled = true
					if (this.page * this.size == this.accountlog_list.length) {
						this.page++
						this.setShopList()
					}
				}, 200);
			},
		},
		watch: {
			accountlog_list() {
				if (this.page * this.size == this.accountlog_list.length) {
					this.disabled = false
					this.loading = true
				} else {
					this.loading = false
				}
				this.accountlog_list = arrRemove.trimSpace(this.accountlog_list)
			}
		}
	}
</script>
<style scoped>
	.main {height: 100%;background: #FFFFFF;}
	.main_box {background: linear-gradient(to right, #6A7ED5, #83A9F1, #83A9F1, #6A7ED5);padding-top: 3.5rem;padding-bottom: 2.5rem;}
	.integral_t {text-align: center;color: #ffff;font-size: 1.5rem;}
	.integral_t i {font-size: 1.7rem;font-weight: 600;}
	.integral_n {font-size: 1.2rem;text-align: center;color: #fff;margin-top: 1rem;}
	.integral_title {font-size: 1.5rem;text-align: center;margin: 0 1.5rem;padding: 1rem;border-bottom: 1px solid #f6f6f9;}
	.tit {font-size: 1.5rem;color: #333;}
	.ny-item {padding: 1rem 1.5rem;margin: 0 1remx;border-bottom: 1px solid #f6f6f9;}
	.ny-item div {margin-top: 0.5rem;}
	.time_price {display: flex;justify-content: space-between;}
	.time {font-size: 1.2rem;color: #999;}
	.red {font-size: 1.5rem;color: red;font-weight: 600;}
	.green {font-size: 1.5rem;color: #33CC66;font-weight: 600;}
</style>
