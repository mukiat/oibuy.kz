<template>
	<view class="visual-adv" :class="[bStyleSel == 0 ? 'visual-adv-swiper' : 'visual-adv-lie']" :style="{'background-color':backgroundColor}">
		<view class="bg-img" @click="link(module.allValue)">
			<image :src="module.allValue.titleImg" mode="widthFix" class="img" v-if="module.allValue.titleImg"></image>
		</view>
		<view class="adv-goods-list">
			<block v-if="bStyleSel == 0">
				<scroll-view class="scroll-view" scroll-x="true" scroll-left="0">
					<view class="scroll-view-item" v-for="(item,index) in previewProlist" :key="index" @click="linkHref(item.goods_id)">
						<view class="goods-top">
							<image :src="item.goods_thumb" class="img" mode="widthFix"></image>
							<view class="goods-label-suspension" v-if="item.goods_label_suspension && item.goods_label_suspension.formated_label_image">
								<image :src="item.goods_label_suspension.formated_label_image" class="img"></image>
							</view>
						</view>
						<view class="goods-info">
							<text class="goods-name twolist-hidden">{{item.title || item.goods_name}}</text>
							<currency-price :price="item.shop_price"></currency-price>
						</view>
					</view>
				</scroll-view>
			</block>
			<block v-else>
				<view class="scroll-view">
					<view class="scroll-view-item" v-for="(item,index) in previewProlist" :key="index" @click="linkHref(item.goods_id)">
						<view class="goods-top">
							<image :src="item.goods_thumb" class="img" mode="widthFix"></image>
							<view class="goods-label-suspension" v-if="item.goods_label_suspension && item.goods_label_suspension.formated_label_image">
								<image :src="item.goods_label_suspension.formated_label_image" class="img"></image>
							</view>
						</view>
						<view class="goods-info">
							<text class="goods-name twolist-hidden">{{item.title || item.goods_name}}</text>
							<currency-price :price="item.shop_price"></currency-price>
						</view>
					</view>
				</view>
			</block>
		</view>
	</view>
</template>

<script>
export default {
	name: 'visual-adv',
	props: ['module', 'preview', 'modulesIndex','shopId'],
	data() {
		return{
			previewProlist: [
				{
					title: '第一张图片',
					sale: '0',
					stock: '0',
					price: '¥238.00',
					marketPrice: '¥413.00'
				},
				{
					title: '第二张图片',
					sale: '0',
					stock: '0',
					price: '¥38.00',
					marketPrice: '¥43.00'
				},
				{
					title: '第三张图片',
					sale: '0',
					stock: '0',
					price: '¥38.00',
					marketPrice: '¥43.00'
				}
			]
		}
	},
	created() {
		uni.request({
			url: this.websiteUrl + '/api/visual/checked',
			method: 'POST',
			data: {
				number:this.selectGoodsId.length,
				goods_id:this.selectGoodsId.toString()
			},
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				this.previewProlist = res.data.data
			},
			fail: (err) => {
				console.error(err)
			}
		})
	},
	mounted() {

	},
	computed: {
		selectGoodsId() {
			return this.module.allValue.selectGoodsId || []
		},
		bStyleSel(){
			return this.module.isStyleSel
		},
		nNumber() {
			return this.module.allValue.number
		},
		backgroundColor(){
			return this.module.allValue.bgColor
		},
	},
	methods:{
		linkHref(goods_id){
			uni.navigateTo({
				url:'/pagesC/goodsDetail/goodsDetail?id='+goods_id
			})
		},
		link(item){
			// #ifdef APP-PLUS
			let page = item.appPage ? item.appPage : item.url
			let built = item.appPage ? 'app' : 'undefined'
			// #endif
			
			// #ifdef MP-WEIXIN
			let page = item.appletPage ? item.appletPage : item.url
			let built = item.appletPage ? 'app' : 'undefined'
			// #endif
			
			this.$outerHref(page,built)
		}
	}
}
</script>

<style scoped>
::-webkit-scrollbar{
	display: none;
}
.visual-adv{
    padding: 20upx 20upx 0 20upx;
    position: relative;
}
.visual-adv .bg-img{
	width: 100%;
}
.visual-adv .bg-img .img{
    border-radius: 20upx;
}

.adv-goods-list{
    margin-top: -20%;
}
.adv-goods-list .scroll-view{
    overflow: visible;
}
.adv-goods-list .scroll-view-item{
    display: inline-block;
    width: 30%;
	margin: 0 0 0 2%;
    background-color: #fff;
    overflow: hidden;
    border-radius: 20upx;
    /* box-shadow:1upx 5upx 20upx 0 rgba(108,108,108,0.2); */
}
.adv-goods-list .scroll-view-item .goods-top{ width: 100%; line-height: 0; position: relative;}
.adv-goods-list .scroll-view-item .goods-info{
    padding: 20upx 16upx 10upx;
}
.adv-goods-list .scroll-view-item .goods-info .goods-name{
    font-size: 26upx;
    color: #000;
	height: 38px;
	line-height: 19px;
}
.adv-goods-list .scroll-view-item .goods-info .price{
    font-size: 32upx;
    color: #F20E28;
    font-weight: 500;
}
.adv-goods-list .scroll-view-item .goods-info .price .em{
	font-size: 25upx;
	margin-right: 3upx;
}
.visual-adv-swiper .adv-goods-list .scroll-view-item:last-child{
    margin-right: 0;
}
.visual-adv-lie{
    margin:20upx 20upx 0 20upx;
    border-radius: 20upx;
    padding:0 0 30upx;
}
.visual-adv-lie .scroll-view{
	white-space:inherit;
	line-height: 0;
}
.visual-adv-lie .adv-goods-list{
    padding: 0;
}
.visual-adv-lie .adv-goods-list .scroll-view-item{
    margin-bottom: 2.5%;
}
.visual-adv-lie .adv-goods-list .scroll-view-item:nth-child(3n){
    margin-right: 0;
}
.visual-adv-lie .adv-goods-list .scroll-view-item:nth-child(3n + 1){
    margin-left: 3.5%;
}
.visual-adv-lie .adv-goods-list .swiper-slide:last-child,
.visual-adv-lie .adv-goods-list .swiper-slide:nth-last-child(2),
.visual-adv-lie .adv-goods-list .swiper-slide:nth-last-child(3){
    margin-bottom: 0;
}
</style>
