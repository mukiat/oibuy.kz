<template>
	<view class="shop" v-if="shopInfo[shopIndex]">
		<view class="shop-top">
			<view class="left">
				<image :src="shopInfo[shopIndex].logo" class="image"></image>
				<view class="info">
					<view class="name uin-ellipsis">{{ shopInfo[shopIndex].shopName }}</view>
					<view class="out" v-if="shopInfo[shopIndex].count_gaze">{{ shopInfo[shopIndex].count_gaze }}人关注</view>
				</view>
			</view>
			<view class="right">
				<text class="collect" v-if="shopCollect" :class="{'active':shopInfo[shopIndex].is_collect_shop == 1}" @click="collectHandle(shopInfo[shopIndex].ru_id)">
					<block v-if="shopInfo[shopIndex].is_collect_shop == 1">{{$t('lang.followed')}}</block>
					<block v-else>{{$t('lang.did_not_concern')}}</block>
				</text>
				<text class="btn" @click="linkHref(shopInfo[shopIndex].ru_id)" v-else>{{$t('lang.shop_in')}}</text>
			</view>
		</view>
		<view class="shop-score" v-if="shopScore">
			<view class="text-left">
				<text class="tit">{{$t('lang.goods')}}</text>
				<text class="score" :style="{color:rankObj.color}">{{shopInfo[shopIndex].commentrank}}</text>
				<uni-icons :type="rankObj.icon" size="18" :color="rankObj.color"></uni-icons>
			</view>
			<view class="text-center">
				<text class="tit">{{$t('lang.service')}}</text>
				<text class="score" :style="{color:serverObj.color}">{{shopInfo[shopIndex].commentserver}}</text>
				<uni-icons :type="serverObj.icon" size="18" :color="serverObj.color"></uni-icons>
			</view>
			<view class="text-right">
				<text class="tit">{{$t('lang.commentdelivery')}}</text>
				<text class="score" :style="{color:deliveryObj.color}">{{shopInfo[shopIndex].commentdelivery}}</text>
				<uni-icons :type="deliveryObj.icon" size="18" :color="deliveryObj.color"></uni-icons>
			</view>
		</view>
	</view>
</template>

<script>
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import universal from '@/common/mixins/universal.js';
	
	export default {
		mixins:[universal],
		props:['shopInfo','shopIndex','shopScore','shopCollect'],
		components:{
			uniIcons
		},
		data() {
			return {
				distance:false,
			};
		},
		computed:{
			shopCollectStatue(){
				return this.$store.state.user.shopCollectStatue
			},
			is_collect_shop(){
				return this.shopInfo[this.shopIndex].is_collect_shop
			},
			ru_id(){
				return this.shopInfo[this.shopIndex].ru_id
			},
			rankObj(){
				let obj = {
					icon:'',
					color:''
				};
				if(this.shopInfo[this.shopIndex].commentrank_font == this.$t('lang.high')){
					obj.icon = 'arrowthinup';
					obj.color = '#13ab53';
				}else if(this.shopInfo[this.shopIndex].commentrank_font == this.$t('lang.low')){
					obj.icon = 'arrowthindown';
					obj.color = '#f92028';
				}else{
					obj.icon = 'arrowthinright';
					obj.color = '#fba534';
				}
				return obj
			},
			serverObj(){
				let obj = {
					icon:'',
					color:''
				};
				if(this.shopInfo[this.shopIndex].commentserver_font == this.$t('lang.high')){
					obj.icon = 'arrowthinup';
					obj.color = '#13ab53';
				}else if(this.shopInfo[this.shopIndex].commentserver_font == this.$t('lang.low')){
					obj.icon = 'arrowthindown';
					obj.color = '#f92028';
				}else{
					obj.icon = 'arrowthinright';
					obj.color = '#fba534';
				}
				return obj
			},
			deliveryObj(){
				let obj = {
					icon:'',
					color:''
				};
				if(this.shopInfo[this.shopIndex].commentdelivery_font == this.$t('lang.high')){
					obj.icon = 'arrowthinup';
					obj.color = '#13ab53';
				}else if(this.shopInfo[this.shopIndex].commentdelivery_font == this.$t('lang.low')){
					obj.icon = 'arrowthindown';
					obj.color = '#f92028';
				}else{
					obj.icon = 'arrowthinright';
					obj.color = '#fba534';
				}
				return obj
			}
		},
		methods:{
			collectHandle(val){
				if(this.$isLogin()){
					this.$store.dispatch('setCollectShop',{
						ru_id:val,
						status:this.is_collect_shop
					})
				}else{
					uni.showModal({
						content:this.$t('lang.fill_in_user_collect_shop'),
						success:(res)=>{
							if(res.confirm){
								uni.navigateTo({
									url:'/pagesB/login/login?delta=1'
								})
							}
						}
					})
				}
			},
			linkHref(ru_id){
				uni.redirectTo({
					url:'/pages/shop/shopHome/shopHome?ru_id='+ru_id
				})
			}
		},
		watch:{
			shopCollectStatue(){
				this.shopCollectStatue.forEach((v)=>{
					if(v.id == this.ru_id){
						this.$emit('update',{
							is_collect_shop:v.status
						})
					}
				})
			}
		}
	}
</script>

<style>
	.shop{ background-color: #FFFFFF; display: flex; flex-direction: column; padding: 20upx;}
	.shop-top{ display: flex; flex-direction: row; justify-content: space-between; align-items: center;}
	.shop-top .left{ display: flex; flex-direction: row; justify-content: flex-start; align-items: center; }
	.shop-top .left .image{ width: 105upx; height: 105upx;}
	.shop-top .left .info{ margin-left: 20upx;}
	.shop-top .left .info .name{ width: 100%; font-weight: 700;line-height: 1.6;}
	.shop-top .left .info .out{ color: #999999;line-height: 1.6;}
	.shop-top .right{ display: flex; flex-direction: row; }
	.shop-top .right .collect{ padding: 0 16upx; border:1px solid #f92028; color: #f92028; border-radius: 30upx; font-size: 14px;}
	.shop-top .right .collect.active{ color: #ccc; border-color: #ddd; }
	.shop-top .right .btn{ padding: 5upx 20upx; border: 1px solid #ddd; margin-right: 10upx;}
	.shop-top .right .btn:last-child{ margin-right: 0;}
	
	.shop-score{ display: flex; flex-direction: row; align-items: center; padding: 0 20upx;}
	.shop-score .text-left,
	.shop-score .text-center,
	.shop-score .text-right{ flex: 1; justify-content: center; margin-top: 10upx;}
	.shop-score .tit{ color: #999999; margin-right: 20upx;}
	.shop-score .score{ color: #f92028;}
</style>
