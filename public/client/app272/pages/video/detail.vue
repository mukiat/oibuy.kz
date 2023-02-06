<template>
	<view class="page" :style="{ height: height }">
		<block v-if="controlVersion">
			<view class="swiper" v-if="goodsInfo">
				<view class="swiper-item">
					<view class="video-box">
						<chunlei-video class="video" :src="goodsInfo.goods_video" :height="height" :width="width" :controls="false" :play="play" @pause="pauseVideo" @playEnd="playEnd" @playClick="playClick" @timeupdate="timeupdate"></chunlei-video>

						<cover-view class="goods-info" @click="hrefClick('/pagesC/goodsDetail/goodsDetail?id='+goodsInfo.goods_id)">
							<cover-image :src="goodsInfo.goods_thumb" class="goods-image"></cover-image>
							<cover-view class="text">
								<cover-view class="tit">{{goodsInfo.goods_name}}</cover-view>
								<cover-view class="price">{{goodsInfo.shop_price_formated}}</cover-view>
							</cover-view>
						</cover-view>
						<cover-image class="video_close_btn" src="../../static/video/close.png" @click="goBackList"></cover-image>
						<cover-view class="cover-view-right">
							<cover-image :src="goodsInfo.is_collect == 1 ? '../../static/video/love-filled.png' : '../../static/video/love.png'" class="icon" @click.stop="tapLove(goodsInfo.is_collect)"></cover-image>
							<cover-view class="right-text">{{goodsInfo.user_collect}}</cover-view>
							<cover-image src="../../static/video/comment-alt.png" class="icon" @click.stop="tapMsg"></cover-image>
							<cover-view class="right-text">{{goodsInfo.comment_num}}</cover-view>
							<!-- #ifdef APP-PLUS -->
							<cover-image src="../../static/video/share.png" class="icon" @click.stop="appShare(item)"></cover-image>
							<cover-view class="right-text">{{$t('lang.share')}}</cover-view>
							<!-- #endif -->
							<!-- #ifdef MP-WEIXIN -->
							<button class="button-share" open-type="share">
								<cover-image src="../../static/video/share.png" class="icon"></cover-image>
							</button>
							<cover-view class="right-text">{{$t('lang.share')}}</cover-view>
							<!-- #endif -->
						</cover-view>
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
	import chunleiVideo from '@/components/chunlei-video/chunlei-video'
	import universal from '@/common/mixins/universal.js';
	import dscNotContent from '@/components/dsc-not-content.vue';
	export default{
		mixins:[universal],
		components:{
			chunleiVideo,
			dscNotContent
		},
		data() {
		    return {
				index:0,
				height:'667px',
				width:'',
				play:true,
				duration:'',
				videoList:[],
				goods_id:0,
				goodsInfo:''
			}
		},
		onLoad(e){
			this.goods_id = e.goods_id ? e.goods_id : 0;

			this.getList();
		},
		created(){
			let height = uni.getSystemInfoSync().windowHeight;
			let width = uni.getSystemInfoSync().windowWidth;
			this.height = `${height}px`;
			this.width = `${width}px`;

			if(!this.controlVersion){
				uni.setNavigationBarTitle({
				    title: this.$t('lang.detail')
				});
			}
		},
		methods:{
			getList(){
				uni.request({
					url: this.websiteUrl + '/api/goods/videoinfo',
					method: 'POST',
					data: {
						goods_id:this.goods_id
					},
					header: {
						'Content-Type': 'application/json',
						'X-Client-Hash':uni.getStorageSync('client_hash'),
						'token': uni.getStorageSync('token')
					},
					success: (res) => {
						this.goodsInfo = res.data.data;
					},
					fail: (err) => {
						console.error(err)
					}
				})
			},
			animationFinish(){},
			pauseVideo(e){},
			playEnd(){
				this.play = false
			},
			//点击视频区域暂停当前视频
			playClick(e){
				this.play = e;
			},
			timeupdate(e){
			},
			collectionNumber(){
				let that = this
				uni.request({
					url: this.websiteUrl + '/api/collect/collectnum',
					method: 'GET',
					data: {
						goods_id: this.goods_id
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash'),
					},
					success: (res) => {
						this.goodsInfo.user_collect = res.data.data
					},
					fail: (err) => {
						console.error(err)
					}
				})
			},
			tapLove(is_collect){
				let that = this
				console.log(that.$isLogin())
				if(that.$isLogin()){
					uni.request({
						url: that.websiteUrl + '/api/collect/collectgoods',
						method: 'POST',
						data: {
							goods_id: this.goods_id,
				            status: is_collect == 1 ? true : false
						},
						header: {
							'Content-Type': 'application/json',
							'token': uni.getStorageSync('token'),
							'X-Client-Hash':uni.getStorageSync('client_hash'),
						},
						success: (res) => {
							let data = res.data.data

				        	if(data.error == 0){
				        		uni.showToast({
									title:data.msg,
									icon:"none"
								})

				            	that.goodsInfo.is_collect = !that.goodsInfo.is_collect

								that.collectionNumber();
				        	}
						}
					})
				}else{
					uni.showModal({
						content:this.$t('lang.fill_in_user_collect_goods'),
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
			tapMsg(){
				this.$outerHref(this.$websiteUrl + 'goods/comment/'+ this.goods_id);
			},
			appShare(item){
				// #ifdef APP-PLUS
				let shareInfo = {
					href:this.$websiteUrl + 'goods/' + item.goods_id + '?platform=APP',
					title:item.goods_name,
					summary:'',
					imageUrl:item.goods_thumb
				};

				this.shareInfo(shareInfo)
				// #endif
			},
			hrefClick(url){

				uni.navigateTo({
					url:url
				})
			},
			goBackList() {
				uni.navigateBack({
					delta: 1
				})
			}
		}
	}
</script>

<style scoped>
	.page{
		/* #ifndef APP-PLUS-NVUE */
		display: flex;
		flex-direction: column;
		/* #endif */
		flex: 1;
	}
	.swiper{
		flex: 1;
		background-color: #000;
	}
	.swiper-item {
		flex: 1;
	}
	.video {
		flex: 1;
	}
	.video-box{
		flex: 1;
		width: 750rpx;
	}

	/* 商品信息 */
	.goods-info{
		position: absolute;
		margin-left: 20upx;
		width: calc(100% - 180upx);
		bottom: 120upx;
		z-index: 9999;
		color: #FFFFFF;
		background-color: #FFFFFF;
		border-radius: 10upx;
		display: flex;
		flex-direction: row;
		padding: 5upx;

		/* #ifndef APP-PLUS */
		white-space: pre-wrap;
		text-overflow:ellipsis;
		overflow:hidden;
		/* #endif */
	}
	.goods-info .goods-image{
		width: 150upx;
		height: 150upx;
		border-radius: 10upx;
		overflow: hidden;
	}
	.goods-info .text{
		flex: 1;
		padding: 20upx;
	}
	.goods-info .text .tit{
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		font-size: 28upx;
		color: #333;
	}
	.goods-info .text .price{
		color: #FD2F2F;
		font-size: 32upx;
		margin-top: 10upx;
	}

	.cover-view-right{
		position: absolute;
		bottom: 360upx;
		right: 30upx;
		/* #ifndef APP-PLUS-NVUE */
		display: flex;
		flex-direction: column;
		/* #endif */
		z-index: 9999;
	}

	.right-text-avater{
		position: absolute;
		font-size: 14px;
		top: 80upx;
		left: 30upx;
		height: 40upx;
		width: 40upx;
		background-color: #DD524D;
		color: #FFFFFF;
		border-radius: 50%;
		text-align: center;
		line-height: 40upx;
		z-index: 999;
	}

	.avater-icon{
		height: 40upx;
		width: 40upx;
		color: #fff;
		background-color: #DD524D;
		border-radius: 50%;
		position: absolute;
		left: 30upx;
		top:-20upx;
	}

	.right-text{
		text-align: center;
		font-size: 14px;
		color: #FFFFFF;
		margin: 10upx 0 20upx;
		height: 20px;
	}

	.icon{
		height: 64upx;
		width: 64upx;
	}

	.button-share{background: none !important;border: 0 !important; border-radius: 0 !important; margin: 0 !important; padding: 0 !important; font-size: 28upx !important;}
	.button-share::after { border-radius: 0 !important; border:0 !important; background: none !important;}
	.button-share.button-hover{ background: none !important;}

	.video_close_btn {
		position: absolute;
		top: calc(var(--status-bar-height) + 55px);
		right: 47upx;
		width: 30upx;
		height: 30upx;
	}
</style>
