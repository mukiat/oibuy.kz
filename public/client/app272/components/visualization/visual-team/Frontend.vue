<template>
	<view class="visual-team" v-if="list">
		<view class="floor-header-title" @click="$outerHref('/pagesA/team/team','app')">
			<view class="title">拼团专区</view>
			<text class="text">{{spikeDesc}}</text>
			<view class="image"><image src="../../../static/more.png" class="img"></image></view>
		</view>
		<view class="floor-content">
			<scroll-view class="scroll-view" scroll-x="true" scroll-left="0">
				<view class="scroll-view-item" v-for="(item,index) in list" :key="index" @click="linkHref(item)">
					<view class="goods-top"><image :src="item.goods_thumb" class="img" mode="widthFix"></image></view>
					<view class="goods-info">
						<text class="goods-name onelist-hidden">{{ item.goods_name }}</text>
						<currency-price :price="item.team_price" style="display: block; margin-top: 5px;">
							<image src="../../../static/shopping-icon.png" class="shopping-icon"></image>
						</currency-price>
						<currency-price :price="item.shop_price" :del="true" style="display: block; margin-top: 3px;"></currency-price>
					</view>
				</view>
			</scroll-view>
		</view>
	</view>
</template>

<script>
import uniIcons from '@/components/uni-icons/uni-icons.vue';
export default{
	name: 'visual-team',
	props: ['module', 'preview'],
	data(){
		return {
			list:[]
		}
	},
	components:{
		uniIcons,
	},
	created() {
		uni.request({
			url: this.websiteUrl + '/api/visual/visual_team_goods',
			method: 'GET',
			data: {},
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				this.list = res.data.data;
			},
			fail: (err) => {
				console.error(err)
			}
		})
    },
	computed:{
		spikeDesc(){
			return this.module.allValue.spikeDesc ? this.module.allValue.spikeDesc : '拼着买更实惠'
		}
	},
	methods:{
		linkHref(item){
			uni.navigateTo({
				url:"/pagesA/team/detail/detail?goods_id="+item.goods_id+"&team_id=0"
			})
		}
	}
}
</script>

<style scoped>
::-webkit-scrollbar{
	display: none;
}

.visual-team{
	margin: 20upx 20upx 0;
	background-color: #fff;
	border-radius: 20upx;
}
.floor-header-title{
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: flex-start;
    padding: 20upx;
	position: relative;
}
.floor-header-title:after{
	position: absolute;
	content: " ";
	height: 1px;
	background-color: #eee;
	left: 20upx;
	right: 20upx;
	bottom: 0;
}
.floor-header-title .title{
    font-size: 36upx;
    color: #000;
    font-weight: 600;
	margin-right: 20upx;
	position: relative;
}

.floor-header-title .text{
    color: #888;
    font-size: 30upx;
	margin-right: 10upx;
}
.floor-header-title .image{
	width: 30upx;
	height: 30upx;
	line-height: 30upx;
}
.floor-header-title .image .img{
	width: 100%;
	height: 100%;
}

.floor-content{
    padding: 30upx 0 0 15upx;
}
.scroll-view-item{
    padding: 0 6upx;
	display: inline-block;
	width: 33%;
	box-sizing: border-box;
}
.scroll-view-item .goods-top{
    position: relative;
	line-height: 0;
}
.scroll-view-item .goods-info{
    padding: 20upx 10upx;
    text-align: center;
    line-height: 1.5;
}
.scroll-view-item .goods-info .goods-name{
    font-size: 26upx;
    color: 000;
    width: 100%;
    display: block;
}
</style>
