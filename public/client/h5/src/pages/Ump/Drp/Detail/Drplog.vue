<template>
	<div class="drp-drplog tab-colse" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="200">
		<van-tabs :active="active">
			<van-tab><div class="nav_active" slot="title" @click="tabNav(2)">{{$t('lang.all')}}</div></van-tab>
			<van-tab><div class="nav_active" slot="title" @click="tabNav(1)">{{$t('lang.has_been_divide')}}</div></van-tab>
			<van-tab><div class="nav_active" slot="title" @click="tabNav(0)">{{$t('lang.not_into')}}</div></van-tab>
		</van-tabs>
		<template v-if="drpLogData!=''">
			<div class="list bg-color-write" v-for="(item,index) in drpLogData" :key="index">
				<div class="list-box">
					<div class="left">
						<div class="price" v-html="item.money_format"></div>
						<div class="log-tag" :class="{'active':item.drp_is_separate == 1}">{{item.is_separate_format}}</div>
					</div>
					<div class="right">{{$t('lang.commission_into')}}</div>
				</div>
				<div class="log">
					<p>{{$t('lang.order_time')}}：{{item.time_format}}</p>

                    <template v-if="item.log_type == 0 || item.log_type == 2">
                        <p>{{$t('lang.order_sn_into')}}：{{item.order_sn}}</p>
                    </template>
                    <template v-else-if="item.log_type == 1">
                        <p>{{$t('lang.label_buyer')}} {{item.buy_user_name}}</p>
                    </template>

				</div>
			</div>
		</template>
		<template v-else>
			<NotCont/>
		</template>
		<CommonNav :routerName="routerName">
	         <li slot="aloneNav">
				<router-link :to="{name: 'drp'}">
					<i class="iconfont icon-fenxiao"></i>
					<p>{{$t('lang.drp_center')}}</p>
				</router-link>
			</li>
	    </CommonNav>
		<template v-if="loading">
            <van-loading type="spinner" color="black" />
        </template>
	</div>
</template>
<script>
	import { mapState } from 'vuex'
	import {
		Tab,
		Tabs,
		Toast,
		Waterfall,
		Loading
	} from 'vant'

	import CommonNav from '@/components/CommonNav'
	import NotCont from '@/components/NotCont'
	import arrRemove from '@/mixins/arr-remove'

	export default {
		name: "drp-drplog",
		components: {
			CommonNav,
			NotCont,
			[Toast.name]: Toast,
			[Tab.name]: Tab,
			[Tabs.name]: Tabs,
			[Loading.name]: Loading,
		},
		directives: {
			WaterfallLower: Waterfall('lower')
		},
		data() {
			return {
				routerName:'drp',
				active: 0,
				status:2,
				disabled:false,
				loading:false,
				size:10,
				page:1,
			};
		},
		created() {
			//初始化加载全部列表 status = 2
			this.loadingData(this.orderList(this.status))
		},
		computed: {
			drpLogData:{
				get(){
					return this.$store.state.drp.drpLogData
				},
				set(val){
					this.$store.state.drp.drpLogData = val
				}
			}
		},
		methods: {
			//状态切换
			tabNav(val) {
				let that = this
				that.status = val
				that.loadingData(this.orderList(val))
			},
			loadMore(){
				setTimeout(() => {
					this.disabled = true
					if(this.page * this.size == this.drpLogData.length){
						this.page ++
						this.orderList(this.status)
					}
				},200);
			},
			//定义列表数据方法
			orderList(status) {
				this.$store.dispatch({
					type: 'setDrpLog',
					page: this.page,
					size: this.size,
					status: status
				})
			},
			//加载动画
			loadingData(url) {
				let that = this
				Toast.loading({
					duration: 500,
					mask: true,
					message: this.$t('lang.loading')
				}, url);
			},
		},
		watch:{
			drpLogData(){
				if(this.page * this.size == this.drpLogData.length){
					this.disabled = false
					this.loading = true
				}else{
					this.loading = false
				}

				this.drpLogData = arrRemove.trimSpace(this.drpLogData)
			}
		}
	};
</script>
