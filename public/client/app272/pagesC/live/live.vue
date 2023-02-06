<template>
	<view class="live">
		<view class="live-list">
			<view class="live-item" :class="{'active':item.live_status == 101}" v-for="(item,index) in liveList" :key="index" @click="liveHref(item.roomid)">
				<view class="left">
					<image :src="item.cover_img" class="image"></image>
					<view class="tips" v-if="item.live_status == 101"><image :src="imagePath.liveBg" mode="widthFix" class="liveimg"></image></view>
				</view>
				<view class="right">
					<view class="name twolist-hidden">{{item.name}}</view>
					<block v-if="item.live_status == 101">
						<scroll-view class="scroll-view scroll-view-product" scroll-x="true" scroll-left="0">
							<view class="goods-item" v-for="(goodsItem,goodsIndex) in item.goods" :key="goodsIndex">
								<image :src="goodsItem.cover_img" class="image"></image>
								<view class="price">{{goodsItem.price}}</view>
							</view>
						</scroll-view>
					</block>
					<block v-else>
						<view class="info">
							<view class="time">{{item.start_time}}</view>
							<view class="time">{{item.end_time}}</view>
							<view class="btn">
								<block v-if="item.live_status == 102">{{$t('lang.not_start')}}</block>
								<block v-if="item.live_status == 103">{{$t('lang.has_ended')}}</block>
								<block v-if="item.live_status == 104">{{$t('lang.live_104')}}</block>
								<block v-if="item.live_status == 105">{{$t('lang.live_105')}}</block>
								<block v-if="item.live_status == 106">{{$t('lang.live_106')}}</block>
								<block v-if="item.live_status == 107">{{$t('lang.have_expired')}}</block>
							</view>
						</view>
					</block>
				</view>
			</view>
		</view>
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				liveList:[],
				page:1,
				size:10,
				dscLoading:true,
				parent_id: uni.getStorageSync('user_id'),
			}
		},
		methods: {
			liveHref(roomId){
				let parent_id = this.parent_id;
				let customParams = encodeURIComponent(JSON.stringify({ path: 'pages/index/index', pid: 1, parent_id:parent_id }))
				uni.navigateTo({
					url:'plugin-private://'+ this.liveAppid + '/pages/live-player-plugin?room_id='+roomId + '&custom_params=' + customParams
				})
			},
			load(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}

				uni.request({
					url: this.websiteUrl + '/api/wxapp/live',
					method: 'POST',
					data:{
						page:this.page,
						size:this.size
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						if(res.data.status == 'success'){
							if(this.page > 0){
								this.liveList = this.liveList.concat(res.data.data);
							}else{
								this.liveList = res.data.data
							}
						}
					}
				})
			}
		},
		onLoad() {
			this.load(1)
		},
		onReachBottom(){
			if(this.page * this.size == this.liveList.length){
				this.page ++
				this.load()
			}
		},
		watch:{
			liveList(){
				this.dscLoading = false
			}
		}
	}
</script>

<style>
.live-list{  }
.live-list .live-item{ margin:20upx 20upx 40upx; background: #fafafa; border-radius: 10upx; overflow: hidden;}
.live-list .live-item.active{ background: #FFFFFF;}
.live-list .live-item .left{ width: 280upx; height: 280upx; position: relative; border-radius: 10upx 0 0 10upx; overflow: hidden; float: left;}
.live-list .live-item .left .image{ width: 280upx; height: 280upx;}
.live-list .live-item .left .tips{ position: absolute; top: 20upx; left: 10upx; right: 0; font-size: 25upx; display: flex; flex-direction: row; width: 100upx; border-radius: 5upx; overflow: hidden;}
.live-list .live-item .left .tips .liveimg{ }
.live-list .live-item .right{ width: calc(100% - 320upx); padding: 0  0 0 20upx; float: left;}
.live-list .live-item .right .name{ font-size: 26upx; margin-top: 20upx;}
.live-list .live-item .right .scroll-view-product{ height: 150upx; margin-top: 15upx;}
.live-list .live-item .right .goods-item{ position: relative; width: 150upx; height: 150upx; margin-right: 20upx; border-radius: 10upx; overflow: hidden; display: inline-block;}
.live-list .live-item .right .goods-item .image{ width: 150upx; height: 150upx;}
.live-list .live-item .right .goods-item .price{ position: absolute; bottom: 0; left: 0; right: 0; text-align: center; line-height: 2; font-size: 25upx; background: rgba(0,0,0,.3); color: #FFFFFF;}
.live-list .live-item .right .time{ line-height: 1.5;}
.live-list .live-item .right .info .btn{ display: inline-block; background: #FFFFFF; border-radius: 5upx; padding: 0 20upx 0 40upx; position: relative; color: #333; margin-top: 10upx;}
.live-list .live-item .right .info .btn:after{ content: ""; position: absolute; width: 15upx; height: 15upx; border-radius: 50%; background: #f92e28; top: 18upx; left: 15upx;}
</style>
