<template>
	<div class="con team" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<van-tabs :active="active">
			<van-tab v-for="(item,index) in tabs" :key="index">
				<div class="nav_active" slot="title" @click="CommonTabs(index)">{{item}}</div>
			</van-tab>
		</van-tabs>
		<div class="coupon-select" v-if="couponData!=''">
			<div class="li dis-box bg-color-write p-r" :class="coupons_index != 0 ? 'active':''" v-for="(item,index) in couponData" :key="index">
				<div class="left box-flex">
					<div class="dis-box">
						<div class="">
							<h4 class="f-weight">
								<template v-if="item.cou_type == 5">{{$t('lang.free_shipping')}}</template>
								<template v-else>
									<template v-if="!item.order_sn">
										<template v-if="item.uc_money > 0">
											{{ item.uc_money }}
										</template>
										<template v-else>
											{{item.cou_money}}
										</template>
									</template>
									<template v-else>
										{{item.order_coupons}}
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
				</div>
				<img v-if="item.staic==='1'" class="p-a" src="../../../assets/img/coupons-print-end.png" />
				<img v-if="item.staic==='2'" class="p-a" src="../../../assets/img/coupons-print-enddata.png" />
				<template v-if="coupons_index == 0">
					<a href="javascript:;" class="show right text-center f-04" @click="couponLink(item.cou_id)">{{$t('lang.immediately')}}<br>{{$t('lang.use')}}</a>
				</template>
				<template v-else-if="coupons_index == 1">
					<a href="javascript:void(0);" class="show right text-center f-04">{{$t('lang.have_been_used')}}</a>
				</template>
				<template v-else-if="coupons_index == 2">
					<a href="javascript:void(0);" class="show right text-center f-04">{{$t('lang.have_expired')}}</a>
				</template>
			</div>
		</div>
		<div v-else>
			<NotCont/>
		</div>
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
		Loading,
		Waterfall
	} from 'vant'

	import NotCont from '@/components/NotCont'
	import arrRemove from '@/mixins/arr-remove'

	export default {
		name: "coupon",
		components: {
			NotCont,
			[Tab.name]: Tab,
			[Tabs.name]: Tabs,
			[Toast.name]: Toast,
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
				coupons_index:0,
				tabs: [this.$t('lang.unused'), this.$t('lang.have_been_used'), this.$t('lang.have_expired')]
			};
		},
		//初始化加载数据
		created() {
			this.couponClick()
		},
		computed: {
			couponData:{
				get(){
					return this.$store.state.user.couponData
				},
				set(val){
					this.$store.state.user.couponData = val
				}
			}
		},
		methods: {
			CommonTabs(val) {
				this.coupons_index = val
				this.couponClick(1)
			},
			couponClick(page) {
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}

				this.$store.dispatch('setUserCoupon',{
					page: this.page,
					size: this.size,
					type: this.coupons_index
				})
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
						this.couponClick()
					}
				},200);
			}
		},
	   watch:{
			couponData(){
				if(this.page * this.size == this.couponData.length){
					this.disabled = false
					this.loading = true
				}else{
					this.loading = false
				}

				this.couponData = arrRemove.trimSpace(this.couponData)
			}
		}
	};
</script>
