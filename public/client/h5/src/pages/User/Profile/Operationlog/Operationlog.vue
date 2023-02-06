<template>
	<div class="main">
		<div class="store_info" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
			<template v-if="accountlog_list.length>0">
				<div class="ny-item" v-for="(item,index) in accountlog_list" :key="index">
					<div class="card-div">
						<div class="tit">{{item.login_type}}<i>{{item.from | forme}}</i>{{item.change_city}}</div>
						<div class="time_price">
							<div class="time">{{item.change_time}}</div>
						</div>
						<div class="red">{{item.ip}}</div>
					</div>
				</div>
			</template>
			<template v-else-if="!isLoading">
				<NotCont />
			</template>
			<CommonNav></CommonNav>
		</div>
	</div>
</template>

<script>
	import {
		mapState
	} from 'vuex'

	import {
		Waterfall,
	} from 'vant'

	import NotCont from '@/components/NotCont'
	import CommonNav from '@/components/CommonNav'
	import arrRemove from '@/mixins/arr-remove'

	export default {
		data() {
			return {
				disabled: false,
				loading: true,
				page: 1,
				size: 10,
				accountlog_list: [],
				isLoading: false
			}
		},
		components: {
			NotCont,
			CommonNav
		},
		created() {
			this.setShopList(1)
		},
		directives: {
		    WaterfallLower: Waterfall('lower')
		},
		filters: {
			forme: function(value) {
				// �������ַ����Сд
				value = value.toLowerCase();
				// ��ȡ����ĸת��Ϊ��д�����������ĸƴ������(3�ַ���)
				value = value.charAt(0).toUpperCase() + value.slice(1);
				return value
			}
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
					size: this.size
				}
				this.$http.get(`${window.ROOT_URL}api/user/user_log`, {
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
			collectHandle(val) {
				this.$store.dispatch('setCollectShop', {
					ru_id: val,
					status: 1
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
	.main {
		height: 100%;
		background: #FFFFFF;
	}

	.tit {
		font-size: 1.4rem;
		color: #333;
	}
   .tit i{
	   padding-left: 1rem;
	   padding-right: 1rem;
   }
	.ny-item {
		padding: 1rem 1.4rem;
		margin: 0 1remx;
		border-bottom: 1px solid #f6f6f9;
	}

	.ny-item div {
		margin-top: 0.5rem;
	}

	.time {
		font-size: 1.2rem;
		color: #999;
	}

	.red {
		font-size: 1.4rem;
		color: red;
		font-weight: 600;
	}
</style>
