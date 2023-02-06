<template>
	<div class="con team" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<van-tabs>
			<van-tab v-for="(item,index) in tabs" :key="index">
				<div class="nav_active" slot="title" @click="taskTabs(index)">{{item}}</div>
			</van-tab>
		</van-tabs>
		<div class="coupon-nav">
			<div class="coupon-nav-cont" v-if="active == 0">
				<span v-for="(item,index) in tabs2" @click="CommonTabs(index)" :class="{'active': coupons_index == index}"><a href="javascript:;">{{item}}</a></span>
			</div>
		</div>
		<div class="coupon-select" v-if="couponData && couponData.length > 0">
			<div class="li dis-box bg-color-write p-r" :class="{'active':item.enable_ling == 1 && item.cou_is_receive != 1}" v-for="(item,index) in couponData" :key="index">
				<div class="left box-flex">
					<div class="dis-box">
						<div class="">
							<h4 class="f-weight">
								<template v-if="item.cou_type == 5">
									{{$t('lang.free_shipping')}}
								</template>
								<template v-else>
									<template v-if="!item.order_sn">
										<template v-if="item.uc_money > 0">
											<em>{{currency}}</em>{{ item.uc_money }}
										</template>
										<template v-else>
											<em>{{currency}}</em>{{item.cou_money}}
										</template>
									</template>
									<template v-else>
										<em>{{currency}}</em>{{item.order_coupons}}
									</template>
								</template>
							</h4>
						</div>
						<div class="box-flex p-l1 f-02">
							<p class="name m-top02">{{item.cou_type_name}}</p>
							<div class="color-3">{{$t('lang.man')}}{{item.cou_man}}{{$t('lang.usable')}}</div>
						</div>
					</div>
					<div class="f-02 color-9 m-top08">{{item.cou_title}}</div>
					<div class="f-02 color-9">{{ item.store_name }}</div>
					<div class="f-02 color-9">{{item.begintime}}{{$t('lang.zhi')}}{{item.endtime}}</div>
					<b class="semi-circle"></b>
					<div class="print-img" v-if="item.cou_is_receive == 1"><img src="../../../assets/img/coupons-print.png" /></div>
				</div>
				<template v-if="item.cou_is_receive == 1">
					<a href="javascript:;" class="show right text-center f-04" @click="couponLink(item.cou_id)">{{$t('lang.immediately')}}<br>{{$t('lang.use')}}</a>
				</template>
				<template v-else-if="item.enable_ling == 1">
					<a href="javascript:;" class="show right text-center f-04">{{$t('lang.take_up')}}</a>
				</template>
				<template v-else-if="item.cou_type == 2">
					<a href="javascript:;" class="show right text-center f-04" @click="couponLink(item.cou_id, item.cou_type)">{{$t('lang.up_original_price')}}<br>{{$t('lang.accomplish')}}</a>
				</template>
				<template v-else>
					<a href="javascript:;" class="show right text-center f-04" @click="drawHandle(item.cou_id)">{{$t('lang.immediately')}}<br>{{$t('lang.receive')}}</a>
				</template>
			</div>
		</div>
		<div v-else>
			<NotCont/>
		</div>
		<template v-if="loading">
			<van-loading type="spinner" color="black" />
		</template>

		<!-- 快捷菜单 -->
        <CommonNav></CommonNav>
	</div>
</template>

<script>
	import qs from 'qs'
	import { mapState } from 'vuex'

	import {
		Tab,
		Tabs,
		Toast,
		Dialog,
		Loading,
		Waterfall
	} from 'vant'

	import NotCont from '@/components/NotCont'
	import arrRemove from '@/mixins/arr-remove'
	import CommonNav from '@/components/CommonNav'

	export default {
		name: "coupon",
		components: {
			NotCont,
			CommonNav,
			[Tab.name]: Tab,
			[Tabs.name]: Tabs,
			[Toast.name]: Toast,
			[Dialog.name]: Dialog,
			[Loading.name] : Loading
		},
		directives: {
			WaterfallLower: Waterfall('lower')
		},
		data() {
			return {
				disabled:false,
				loading:true,
				page:1,
				size:10,
				active: 0,
				currency:'￥',
				coupons_index: this.$route.query.status ? this.$route.query.status : 0,
				cou_id:this.$route.query.cou_id ? this.$route.query.cou_id : 0,
				tabs:[this.$t('lang.coupon_market'),this.$t('lang.task_market')],
				tabs2: [this.$t('lang.coupon_tab_1'), this.$t('lang.coupon_tab_2'), this.$t('lang.coupon_tab_3')],
			}
		},
		//初始化加载数据
		created() {
			let configData = JSON.parse(sessionStorage.getItem('configData'));
			if(configData){
			  this.currency = configData.currency_format.replace('%s', '');
			}
			
			this.couponClick(this.active,1)
		},
		computed: {
			couponData:{
				get(){
					return this.$store.state.other.couponData
				},
				set(val){
					this.$store.state.other.couponData = val
				}
			},
			isLogin(){
	 			return localStorage.getItem('token') == null ? false : true
	 		},
		},
		methods: {
			CommonTabs(val) {
				this.coupons_index = val
				this.couponClick(this.active,1)
			},
			couponClick(state,page) {
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}
				if(state == 0){
					this.$store.dispatch('setWebCoupon',{
						page: this.page,
						size: this.size,
						status: this.coupons_index,
						cou_id: this.cou_id
					})
				}else{
					this.$store.dispatch('setWebTaskCoupon',{
						page: this.page,
						size: this.size,
						status: this.coupons_index,
						cou_id: this.cou_id
					})
				}
			},
			taskTabs(val){
				this.active = val
				this.couponClick(this.active,1)
			},
			couponLink(id){
				this.$router.push({
					name:'searchList',
					query:{
						cou_id:id
					}
				})
			},
			loadMore(){
				//瀑布流分页
				setTimeout(() => {
					this.disabled = true
					if(this.page * this.size == this.couponData.length){
						this.page ++
						this.couponClick(this.active)
					}
				},200);
			},
			drawHandle(id){
				if(this.isLogin){
					this.$http.post(`${window.ROOT_URL}api/coupon/receive`,qs.stringify({
						cou_id:id
					})).then(({data})=>{
						if(data.data.error == 0){
							Toast(data.data.msg)
						}else{
							Toast.success({
								duration:500,
								message:data.data.msg
							})
							this.couponClick(this.active,1)
						}
					})
				}else{
					let msg = this.$t('lang.login_user_not')
					this.notLogin(msg)
				}
			},
			notLogin(msg){
				Dialog.confirm({
					message:msg,
					className:'text-center'
				}).then(()=>{
					this.$router.push({
						path: '/login',
						query:{ redirect: {name:'coupon'}}
					})
				}).catch(()=>{

				})
			},
		},
	  watch:{
			couponData(){
				if(this.couponData && this.couponData.length > 0){
					if(this.page * this.size == this.couponData.length){
						this.disabled = false
						this.loading = true
					}else{
						this.loading = false
					}

					this.couponData = arrRemove.trimSpace(this.couponData)
				}else{
					this.loading = false
				}
			}
		}
	}
</script>
