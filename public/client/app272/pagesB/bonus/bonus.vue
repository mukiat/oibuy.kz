<template>
	<view class="container-tab-bar">
		<view class="tab-bar">
			<view v-for="(tab,index) in tabBars" :key="index" :class="['tab-item',status == index ? 'active' : '']" @click="CommonTabs(index)">
				<text>{{ tab }}</text>
			</view>
		</view>
		<block v-if="bonusData.length > 0">
		<view class="bonus-items">
			<view class="bonus-item" v-for="(item,index) in bonusData" :key="index">
				<view class="cont">
					<view class="bonus-left">
						<view class="bonus-money">{{ currency_format }}{{item.type_money}}</view>
					</view>
					<view class="bonus-right">
						<view class="bonus-tit">{{item.type_name}}</view>
						<view class="bonus-desc">{{$t('lang.man')}}{{item.min_goods_amount}}{{$t('lang.is_money_use')}}</view>
						<view class="bonus-desc">{{item.shop_name}}</view>
					</view>
					<view class="bonus-btn" v-if="status == 0"><button size="mini" type="warn" @click="bonusHandle">{{$t('lang.to_use_the')}}</button></view>
					<view class="bonus-image" v-else>
						<image src="../../static/coupons-print-end.png" class="img" v-if="status == 1"></image>
						<image src="../../static/coupons-print-enddata.png" v-if="status == 2" class="img"></image>
					</view>
				</view>
				<view class="time">{{$t('lang.service_life')}}：{{item.use_start_date}} {{$t('lang.zhi')}} {{item.use_end_date}}</view>
			</view>
		</view>
		</block>
		<block v-else>
			<dsc-not-content></dsc-not-content>
		</block>
		<view class="btn-bar btn-bar-fixed">
			<view class="btn" @click="onClickBigBtn">{{$t('lang.convert_bonus')}}</view>
		</view>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import dscNotContent from '@/components/dsc-not-content.vue';
	export default {
		data() {
			return {
				tabBars:[this.$t('lang.unused'),this.$t('lang.have_been_used'),this.$t('lang.have_expired')],
				status:0,
				page:1,
				size:10,
				currency_format: uni.getStorageSync('configData').currency_format || '¥',
			};
		},
		components:{
			dscNotContent
		},
		computed: {
			bonusData:{
				get(){
					return this.$store.state.user.bonusData
				},
				set(val){
					this.$store.state.user.bonusData = val
				}
			}
		},
		methods:{
			CommonTabs(val) {
				this.status = val
				this.bonusClick(1)
			},
			bonusClick(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}

				this.$store.dispatch('setBonusList',{
					page: this.page,
					size: this.size,
					type: this.status
				})
			},
			onClickBigBtn(){
				uni.navigateTo({
					url:'/pagesB/bonus/addBonus/addBonus'
				})
			},
			bonusHandle(){
				uni.switchTab({
					url:'/pages/index/index'
				})
			}
		},
		onLoad(){
			this.bonusClick()
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/bonus/bonus'
			}
		},
		onReachBottom(){
			if(this.page * this.size == this.bonusData.length){
				this.page ++
				this.bonusClick()
			}
		}
	}
</script>

<style>

</style>
