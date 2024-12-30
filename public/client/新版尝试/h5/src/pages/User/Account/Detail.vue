<template>
	<div class="account_detail" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<div class="has-mgtb">
			<input class="j-input-text" type="number" name="keyword" autocomplete="off" v-model="keyword" :placeholder="$t('lang.search_shopid')">
			<i class="sou" @click="search_order_sn">{{$t('lang.search')}}</i>
		</div>
		<template v-if="accountlog_list.length > 0">
			<div class="ny-list" v-for="(date,index) in accountlog_list" :key="index">
				<div class="time_list">
					<div>{{date.ymd}}</div>
					<div @click="screen" v-if="index==0">
						{{$t('lang.filter')}}
					</div>
				</div>
				<div class="ny-item" v-for="(item,index1) in date.source_data" :key="index1">
					<div class="card-div">
						<template v-if="item.short_change_desc_part1">
							<p>{{ item.short_change_desc_part1}}</p>
							<p>{{ item.short_change_desc_part2}}</p>
						</template>
						<template v-else>
							<p>{{ item.short_change_desc}}</p>
						</template>
						<div class="time_price">
							<span class="time">{{ item.change_time }}</span>
							<span class="red" v-if="item.amount > 0">+{{ item.amount }}</span>
							<span class="black" v-else>{{ item.amount }}</span>
						</div>
					</div>
				</div>
			</div>
		<div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
		<template v-if="loading">
			<van-loading type="spinner" color="black" />
		</template>
		</template>
		<template v-else>
			<NotCont></NotCont>
		</template>
		<van-popup v-model="show" position="bottom" :style="{ height: '40%' }">
			<van-datetime-picker v-model="currentDate" type="year-month" @confirm="confirm" @cancel="cancel"/>
		</van-popup>

		<!--初始化loading-->
  	<DscLoading :dscLoading="dscLoading"></DscLoading>

		<CommonNav></CommonNav>
	</div>
</template>

<script>
	import {
		List,
		Cell,
		DatetimePicker,
		Popup,
		Loading,
		Waterfall
	} from 'vant'
	import Vue from 'vue';

	Vue.use(Popup);
	Vue.use(DatetimePicker);

	import CommonNav from '@/components/CommonNav'
	import NotCont from '@/components/NotCont'
	import arrRemove from '@/mixins/arr-remove'
	import DscLoading from '@/components/DscLoading'

	export default {
		components: {
			[List.name]: List,
			[Cell.name]: Cell,
			[Loading.name]: Loading,
			NotCont,
			CommonNav,
			DscLoading
		},
		directives: {
    	WaterfallLower: Waterfall('lower')
		},
		data() {
			return {
				loading: false,
				accountlog_list: [],
				account_log_list_bak:[],
				currentDate: new Date(),
				show:false,
				page: 1,
				size: 10,
				keyword:'',
				footerCont:false,
				dscLoading:true,
				ismonth_on:false
			}
		},
		created() {
			this.accountdetails()
		},
		methods: {
			search_order_sn(){
				this.accountdetails(1)
			},
			screen(){
				this.show = true
			},
			confirm(){
				this.accountdetails(1)
				this.show = false
				this.ismonth_on=true
			},
			cancel(){
				this.show = false
			},
			p(s) {
	      return s < 10 ? '0' + s : s
	    },
			accountdetails(page){
				if (page) {
					this.page = page
					this.size = Number(page) * 10
				}

				let o = {
					page: this.page,
					size: this.size,
					order_sn:this.keyword,
					month:this.ismonth_on ? this.currentDate.getFullYear() + '-' +this.p((this.currentDate.getMonth() + 1)) : ''
				}

				this.$http.get(`${window.ROOT_URL}api/account/accountlog`,{ params:o }).then(res => {
					if (res.data.status == 'success') {
						let data = res.data.data;
						if(this.page == 1){
							this.accountlog_list = data
						}else{
							let copyList = JSON.parse(JSON.stringify(this.accountlog_list))
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
							this.accountlog_list = [...copyList, ...list]
						}
					}
				})
			},
			loadMore() {
				setTimeout(() => {
					this.disabled = true
					if (this.page * this.size == this.account_log_list_bak) {
						this.page++
						this.accountdetails()
					}
				}, 200);
			},
		},
		watch: {
			accountlog_list() {
				this.account_log_list_bak = 0
				this.dscLoading = false
				//数组格式化
				let accountlog_list = JSON.parse(JSON.stringify(this.accountlog_list))

				accountlog_list.forEach((v,i)=>{
					this.account_log_list_bak+=v.source_data.length
					// v.source_data.forEach((s,k)=>{
					// 	this.account_log_list_bak.push(s)
					// })
				})
			},
			account_log_list_bak(){
				if (this.page * this.size == this.account_log_list_bak) {
					this.disabled = false
					this.loading = true
				} else {
					this.loading = false
					this.footerCont = this.page > 1 ? true : false
				}
			}
		}
	}
</script>
<style scoped>
	.account_detail{
		background: #fff;
	}
	.has-mgtb {
		display: flex;
		justify-content: space-around;
		width: 92%;
		-webkit-flex-wrap: nowrap;
		flex-wrap: nowrap;
		margin: auto;
		padding-top: 1rem;
	}

	.has-mgtb input {
		background-color: #f6f6f6;
		border-radius: 2rem;
		width: 85%;
		height: 3rem;
		font-size: 12px;
		line-height: 3rem;
		padding: 0 4%;

	}

	.has-mgtb .sou {
		font-size: 1.4rem;
		color: #A0A0A0;
		line-height: 3rem;

	}

	.ny-item {
		padding: 1rem 1.5rem;
		margin: 0 1rem;
		border-bottom: 1px solid #f6f6f6;
	}
	.ny-item:last-child{
		border-bottom: 0;
	}

	.ny-item p,
	.time_price {
		margin-top: 0.5rem;
	}

	.time_price {
		display: flex;
		justify-content: space-between;
	}

	.red {
		color: red;
		font-size: 1.6rem;
		font-weight: 600;
	}

	.black{
		color: #333;
		font-size: 1.6rem;
		font-weight: 600;
	}

	.time_list {
		background-color: #f6f6f6;
		padding: 0.75rem;
		margin: 0 1rem;
		margin-top: 1rem;
		font-size: 16px;
		font-weight: bold;
		display: flex;
		justify-content: space-between;
	}
</style>
