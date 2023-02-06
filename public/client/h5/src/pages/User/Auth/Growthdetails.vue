<template>
	<div class="log_info">
		<div class="store_info" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<template v-if="accountlog_list.length > 0">

			<div class="ny-item" v-for="(item,index) in accountlog_list" :key="index">
				<div class="card-div">
					<div class="tit">{{ item.short_change_desc }}</div>
					<div class="time_price">
						<div class="time">{{ item.change_time }}</div>
						<div class="red" v-if="item.type=='+'">{{item.type}}{{ item.rank_points }}</div>
						<div class="green" v-else>{{item.type}}{{ item.rank_points }}</div>
					</div>
				</div>
			</div>
		</template>
		<template v-else-if="!isLoading">
			<NotCont></NotCont>
		</template>
		<CommonNav></CommonNav>
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
				accountlog_list: [],
				page: 1,
				size: 10,
				isLoading: false
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
					this.isLoading = true;
					if (page) {
						this.page = page
						this.size = Number(page) * 10
					}
					let o = {
						page: this.page,
						size: this.size,
						type: 'rank_points'
					}
					this.$http.get(`${window.ROOT_URL}api/account/paypoints`, {
						params: o
					}).then(res => {
						this.isLoading = false;
						if(res.data.status == 'success'){
							if (this.page == 1) {
								this.accountlog_list = res.data.data
							}else{
								this.accountlog_list = this.accountlog_list.concat(res.data.data); //������ƴ����һ��
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
	.log_info {background-color: #FFFFFF;}
	.ny-item {font-size: 1.4rem;padding: 1rem 1.5rem;margin: 0 1remx;border-bottom: 1px solid #f6f6f9;}
	.ny-item div {margin-top: 0.5rem;}
	.time_price {display: flex;justify-content: space-between;}
	.time {font-size: 1.2rem;color: #999;}
	.red {font-size: 1.4rem;color: red;font-weight: 600;}
	.green {color: #33CC66;font-weight: 600;font-size: 1.4rem;}
</style>
