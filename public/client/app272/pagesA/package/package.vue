<template>
	<view class="package-box">
		<block v-if="packageData.length > 0">
			<view class="li" v-for="(item, index) in packageData" :key="index">
				<view class="package-goods">
					<scroll-view class="scroll-view scroll-view-product" scroll-x="true" scroll-left="0">
						<view class="scroll-view-item" v-for="(goodsItem,goodsIndex) in item.goods_list" :key="goodsIndex" @click="linkHref(goodsItem.goods_id)">
							<image :src="goodsItem.goods_thumb" mode="widthFix" v-if="goodsItem.goods_thumb"></image>
							<text class="name twolist-hidden">{{goodsItem.goods_name}}</text>
							<text class="number">×{{goodsItem.goods_number}}</text>
							<currency-price :price="goodsItem.rank_price"></currency-price>
						</view>
					</scroll-view>
				</view>
				<view class="package-cont">
					<view class="nav-cont">
						<view class="tit">{{item.act_name}} (<text class="uni-red">{{item.package_number}}{{$t('lang.jian')}}</text>)</view>
						<view class="text">{{$t('lang.label_original_price')}}<text class="del">{{item.subtotal_formatted}}</text></view>
						<view class="taocan">{{$t('lang.label_package_price')}}<text class="uni-red">{{item.package_price_formatted}}</text><text class="dis" v-if="item.saving">({{$t('lang.is_discount')}}{{item.saving}})</text></view>
					</view>
					<view class="cont">
						<view class="cont-text">{{$t('lang.label_start_end_time')}}{{item.end_time}}</view>
						<view class="cont-text">{{$t('lang.label_brief_desc')}}{{item.act_desc}}</view>
					</view>
				</view>
				<view class="btn-bar btn-bar-radius">
					<view class="btn btn-btn" @click="onAddCartClicked(item.act_id)">{{$t('lang.button_buy')}}</view>
				</view>
			</view>
		</block>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	
	export default {
		data() {
			return {
				page:1,
				size:10
			}
		},
		computed: {
			...mapState({
				packageData: state => state.ump.packageData
			})
		},
		onLoad() {
			this.packageList(1)
		},
		onReachBottom(){
			if(this.page * this.size == this.packageData.length){
				this.page ++
				this.packageList()
			}
		},
		methods: {
			packageList(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}
				
				this.$store.dispatch('setPackageList',{
					page:this.page,
					size:this.size
				})
			},
			linkHref(goods_id){
				uni.navigateTo({
					url:'/pagesC/goodsDetail/goodsDetail?id='+goods_id
				})
			},
			onAddCartClicked(id){
				this.$store.dispatch('setAddPackageCart',{
					package_id:id,
					number:1,
					warehouse_id:'0',
					area_id:'0',
					parent_id:'0',
					confirm_type:3
				}).then(res => {
					if (this.$isLogin()) {
						uni.navigateTo({
							url: '/pages/checkout/checkout?rec_type=' + res.rec_type
						});
					} else {
						uni.showModal({
							content: this.$t('lang.login_user_not'),
							success: res => {
								if (res.confirm) {
									uni.navigateTo({
										url: '/pagesB/login/login?delta=1'
									});
								}
							}
						});
					}
				})
			}
		}
	}
</script>

<style scoped>
.package-box{}
.package-box .li{ margin-bottom: 20upx; background-color: #FFFFFF; padding: 20upx;}
.package-box .li .number{ font-size: 25upx; color: #999; }

.package-cont{ margin-top: 20upx; }
.package-cont .nav-cont{ padding-bottom: 20upx; margin-bottom: 20upx; border-bottom: 1px solid #f3f3f3; }
.package-cont .nav-cont .tit{ font-size: 32upx; }
.package-cont .nav-cont .text{ text-decoration: line-through;  color: #999999;}
.package-cont .nav-cont .taocan{ color: #999999;}
.package-cont .cont{ margin-bottom: 20upx;}
.package-cont .cont .cont-text{ color: #999999;}

.scroll-view-product .scroll-view-item{ border: 0; padding-right: 20upx; width: 160upx; line-height: 1.5;}
.scroll-view-product .scroll-view-item image{ width: 100%; height: 100%;}
.scroll-view-product .scroll-view-item .name{ display: -webkit-box; }
</style>
