<template>
	<view class="container-tab-bar">
		<view class="tab-bar">
			<view v-for="(tab,index) in tabBars" :key="index" :class="['tab-item',status == index ? 'active' : '']" @click="CommonTabs(index)">
				<text>{{ tab }}</text>
			</view>
		</view>

		<view class="uni-padding-wrap" v-if="status == 0">
			<uni-segmented-control :current="active" :values="tabs" :style-type="styleType" :active-color="activeColor" @clickItem="onClickItem" />
		</view>

		<block v-if="couponData && couponData.length>0">
			<view class="coupon-items">
				<view class="coupon-item" v-for="(item,index) in couponData" :key="index" :class="{'g-gay':status != 0}">
					<view class="left">
						<view class="coupon-item-con">
							<view class="q-type">
								<view class="b-r-a-price">
									<text class="coupons-yan">{{ currency_format }}</text>
									<text class="coupons-money">
										<block v-if="item.cou_type == 5">{{$t('lang.freight_free')}}</block>
										<block v-else>
											<block v-if="!item.order_sn">
												<block v-if="item.uc_money > 0">{{ item.cou_money }}</block>
												<block v-else>{{item.cou_money}}</block>
											</block>
											<block v-else>
												{{item.order_coupons}}
											</block>
										</block>
									</text>
									<view class="couons-text">
										<view>{{item.cou_type_name}}</view>
										<text>{{$t('lang.man')}}{{ item.cou_man }}{{$t('lang.available_full')}}</text>
									</view>
								</view>
								<view class="b-r-a-con">
									<view class="range-item">{{ item.cou_title }}</view>
									<view class="range-item">{{ item.store_name }}</view>
									<view class="range-item">{{item.begintime}}{{$t('lang.zhi')}}{{item.endtime}}</view>
								</view>
							</view>
							<view class="coupon-image" v-if="item.cou_is_receive == 1">
								<image :src="imagePath.couponsPrint" class="img"></image>
							</view>
						</view>
					</view>
					<view class="right">
						<view v-if="item.cou_is_receive == 1" @click="couponLink(item.cou_id)">{{$t('lang.immediately')}}<br>{{$t('lang.use')}}</view>
						<view v-else-if="item.enable_ling == 1">{{$t('lang.take_up')}}</view>
						<view v-else-if="item.cou_type == 2">{{$t('lang.up_original_price')}}<br>{{$t('lang.accomplish')}}</view>
						<view v-else @click="drawHandle(item.cou_id)">{{$t('lang.immediately')}}<br>{{$t('lang.receive')}}</view>
					</view>
				</view>
			</view>
		</block>
		<block v-else>
			<dsc-not-content></dsc-not-content>
		</block>
	</view>
</template>

<script>
	import { mapState } from 'vuex'

	import uniSegmentedControl from '@/components/uni-segmented-control/uni-segmented-control.vue'
	import dscNotContent from '@/components/dsc-not-content.vue';

	export default {
		data() {
			return {
				tabBars:[this.$t('lang.coupon_market'), this.$t('lang.task_market')],
				tabs:[this.$t('lang.coupon_tab_1'), this.$t('lang.coupon_tab_2'), this.$t('lang.coupon_tab_3')],
				active:0,
				status:0,
				cou_id:0,
				page:1,
				size:10,
				currency_format: uni.getStorageSync('configData').currency_format || '¥',
				colorIndex: 0,
				activeColor: '#ec5151',
				styleType: 'button'
			};
		},
		components:{
			dscNotContent,
			uniSegmentedControl
		},
		onShareAppMessage(res){
			return {
			  title: this.$t('lang.receive_coupon'),
			  path: '/pagesA/coupon/coupon'
			}
		},
		computed: {
			couponData:{
				get(){
					return this.$store.state.ump.couponData
				},
				set(val){
					this.$store.state.ump.couponData = val
				}
			},
		},
		methods: {
			CommonTabs(val) {
				this.status = val
				this.couponClick(val,1)
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
						status: this.active,
						cou_id: this.cou_id
					})
				}else{
					this.$store.dispatch('setWebTaskCoupon',{
						page: this.page,
						size: this.size,
						status: this.active,
						cou_id: this.cou_id
					})
				}
			},
			onClickItem(index){
				if (this.active !== index) {
					this.active = index
				}

				this.couponClick(this.status,1)
			},
			couponLink(id){
				uni.navigateTo({
					url:'/pages/goodslist/goodslist?cou_id='+id
				})
			},
			drawHandle(id){
				uni.request({
					url:this.websiteUrl + '/api/coupon/receive',
					method:'POST',
					data:{
						cou_id:id
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						let data = res.data.data
						uni.showToast({
							title:data.msg,
							icon:"none"
						});

						if(data.error == 1){
							this.couponClick(this.active,1)
						}
					}
				});
			},
		},
		onLoad(e){
			this.status = e.status ? e.status : 0;
			this.cou_id = e.cou_id ? e.cou_id : 0;

			this.couponClick(this.active,1)
		},
		onReachBottom(){
			if(this.page * this.size == this.couponData.length){
				this.page ++
				this.couponClick()
			}
		}
	}
</script>

<style>
.uni-padding-wrap{ margin-top: 20upx;}
</style>
