<template>
    <view class="page-section" :class="{'page-section-new':isStyleSel == 2}" v-if="isStyleSel != 2 || (isStyleSel == 2 && liveList.length > 0)">
		<block v-if="isStyleSel != 2">
			<view class="separat-show" :class="{mini: bMiniList}">
				<view v-for="(item,index) in module.list" :key="index" class="item" @click="link(item)">
					<view class="desc" v-if="item.desc != ''">{{ item.desc }}</view>
					<image :src="item.img || item.goods_img" mode="widthFix" class="img"></image>
				</view>
			</view>
		</block>
		<block v-else>
			<view class="floor-header-title" @click="$outerHref('/pages/integration/integration?type=3','app')">
				<view class="title">{{$t('lang.on_line_live')}}</view>
				<text class="text">{{spikeDesc}}</text>
				<view class="image"><image src="../../../static/more.png" class="img"></image></view>
			</view>
			<scroll-view class="scroll-view" scroll-x="true" scroll-left="0">
				<view class="live_item" v-for="(live, index) in liveList" :key="index" @click="liveHref(live)">
					<image :src="live.share_img" class="live_bg" mode="aspectFill"></image>
					<view class="live_item_bgc"></view>
					<view class="status_views flex_box ai_center" v-if="!live.wxapp_media_id">
						<view :class="['live_status', 'size_22', 'color_fff', liveStatus(live.live_status).class]"><text :class="['iconfont', liveStatus(live.live_status).icon, 'size_22', 'color_fff']"></text>{{liveStatus(live.live_status).text}}</view>
					</view>
					<view class="status_views flex_box ai_center" v-else>
						<view class="live_status size_22 color_fff playback" v-if="live.is_live == 0">
							<text class="iconfont icon-home-live-back size_22 color_fff"></text>{{$t('lang.has_ended')}}
						</view>
						<view class="live_status size_22 color_fff live_ing" v-else-if="live.is_live == 1">
							<text class="iconfont icon-find-broadcast size_22 color_fff"></text>{{$t('lang.video_umber_live')}}
						</view>
						<view class="live_status size_22 color_fff advance_notice" v-else-if="live.is_live == 2">
							<text class="iconfont icon-home-live-back size_22 color_fff"></text>{{$t('lang.not_start')}}
						</view>
					</view>
					<view class="live_info">
						<view class="live_logo"><image class="img" :src="live.cover_img"></image></view>
						<text class="size_22 color_fe live_user">{{ live.anchor_name }}</text>
						<view class="live_explain">
							<text class="text size_34 twolist-hidden color_fff">{{ live.name }}</text>
						</view>
					</view>
				</view>
			</scroll-view>
		</block>
    </view>
</template>

<script>
export default{
	props:['module', 'preview', 'modulesIndex'],
	data(){
		return {
			autoplay: true,
			interval: 5000,
			duration: 500,
			imgheights:'',
			current:0,
			page:1,
			size:10,
			liveList:[],
			videoList:[]
		}
	},
	computed:{
		isStyleSel(){
			return this.module.isStyleSel
		},
		bSeparateShow() {
            return '1' == this.module.isStyleSel ? true : false
        },
        bMiniList() {
            return '1' == this.module.isSizeSel ? true : false
        },
		indicatorDots(){
			return this.module.list.length > 1 ? true : false
		},
		spikeDesc(){
			return this.module.allValue.spikeDesc ? this.module.allValue.spikeDesc : '主播好物推荐'
		}
	},
	mounted() {
		if(this.isStyleSel == 2){
			this.load();
		}
		
		if(uni.getStorageSync('mediaLive')){
			this.videoList.push(uni.getStorageSync('mediaLive'));
		}
	},
	methods:{
		imageLoad(e){
			let imgwidth = e.detail.width,
				imgheight = e.detail.height,
				//设备宽度
				windowWidth = uni.getSystemInfoSync().windowWidth;

			this.imgheights = (windowWidth/imgwidth) * imgheight
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
						this.liveList = res.data.data
						
						//视频号直播
						if(uni.getStorageSync('mediaLive')){
							this.liveList = [...this.videoList,...this.liveList];
						}
					}
				}
			})
		},
		liveStatus(status) {
			//101 直播中,102 未开始,103 已结束,104 禁播,105 暂停中,106 异常,107 已过期
			let live_status = {
				class: '',
				icon: '',
				text: ''
			};
			if (status == 101) {
				live_status.class = 'live_ing';
				live_status.icon = 'icon-home-live-ing';
				live_status.text = '直播中';
			} else if (status == 102) {
				live_status.class = 'advance_notice';
				live_status.icon = 'icon-find-live-time';
				live_status.text = '未开始';
			} else if(status == 103) {
				live_status.class = 'playback';
				live_status.icon = 'icon-home-live-back';
				live_status.text = '看回放';
			}else if(status == 104) {
				live_status.class = 'playback';
				live_status.icon = 'icon-home-live-back';
				live_status.text = '禁播';
			}else if(status == 105) {
				live_status.class = 'playback';
				live_status.icon = 'icon-home-live-back';
				live_status.text = '暂停';
			}else if(status == 106) {
				live_status.class = 'playback';
				live_status.icon = 'icon-home-live-back';
				live_status.text = '异常';
			}else if(status == 107) {
				live_status.class = 'playback';
				live_status.icon = 'icon-home-live-back';
				live_status.text = '已过期';
			}
			return live_status
		},
		liveHref(live){
			if(!live.wxapp_media_id){
				let customParams = { path: 'pages/index/index', pid: 1 }
				this.customParams = encodeURIComponent(JSON.stringify(customParams))

				uni.navigateTo({
					url:'plugin-private://'+ this.liveAppid + '/pages/live-player-plugin?room_id='+live.roomid + '&customParams=' + this.customParams
				})
			}else{
				//视频号正在直播
				uni.openChannelsLive({
					finderUserName:uni.getStorageSync('configData').wxapp_media_id || '',
					feedId:uni.getStorageSync('channelsLive').feedId || '',
					nonceId:uni.getStorageSync('channelsLive').nonceId || '',
				})
			}
		},
	}
}
</script>

<style>
.page-section{ overflow: hidden;}
.page-section .swiper-item{ position: relative; line-height: 0; font-size: 0;}
.page-section .swiper-item .image{ width: 100%; }
.page-section .swiper-item .desc{box-sizing: border-box; position: absolute; left: 10upx; right: 10upx; bottom: 0; background: rgba(0, 0, 0, .5); color: #FFFFFF;}
.separat-show{ display: flex; flex-direction: column;}
.separat-show .item{ width: 100%; position: relative; line-height: 0; font-size: 0;}
.separat-show .desc{ box-sizing: border-box; position: absolute; left: 10upx; right: 10upx; bottom: 0; background: rgba(0, 0, 0, .5); color: #FFFFFF; }
.separat-show.mini{ display: block;}
.separat-show.mini .item{ width: 50%; float: left;}

/*新版样式*/
.page-section-new{
	margin: 20upx 20upx 0;
	background-color: #fff;
	border-radius: 20upx;
	padding-bottom: 20upx;
}

.floor-header-title{
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: flex-start;
    padding: 30upx 20upx;
}
.floor-header-title .title{
    font-size: 36upx;
    color: #000;
    font-weight: 600;
	margin-right: 20upx;
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

.live_list {
	padding: 14upx 16upx;
}

.live_item {
	position: relative;
	width: 47%;
	height: 475upx;
	margin-left: 2%;
	border-radius: 10upx;
	overflow: hidden;
	display: inline-block;
}

.live_bg {
	width: 100%;
	height: 100%;
}

.live_item_bgc {
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(0, 0, 0, .2);
	border-radius: 10upx;
}

.status_views {
	position: absolute;
	top: 20upx;
	left: 22upx;
	height: 44upx;
	background: rgba(0, 0, 0, .4);
	border-radius: 22upx;
	overflow: hidden;
}

.live_status {
	height: 44upx;
	line-height: 44upx;
	padding: 0 20upx 0 15upx;
	border-radius: 22upx;
	display: flex;
	flex-direction: row;
	justify-content: center;
	align-items: center;
}

.live_ing {
	background: linear-gradient(-90deg, rgba(255, 59, 82, 1), rgba(239, 18, 22, 1));
}
.advance_notice {
	background:linear-gradient(-90deg,rgba(8,174,117,1),rgba(64,207,131,1));
}
.playback {
	background:linear-gradient(90deg,rgba(133,159,183,1),rgba(109,129,165,1));
}

.live_status .iconfont {
	margin-right: 8upx;
}

.views_number {
	padding: 0 20upx 0 10upx;
}

.live_info {
	position: absolute;
	bottom: 0;
	left: 0;
	width: 100%;
	height: 155upx;
	background: rgba(0, 0, 0, .5);
	border-radius: 0 0 10upx 10upx;
	line-height: 1;
}

.live_logo {
	position: absolute;
	left: 23upx;
	top: 0;
	transform: translateY(-50%);
	width: 62upx;
	height: 62upx;
	border-radius: 50%;
	overflow: hidden;
}
.live_logo .img{ width: 100%; height: 100%;border-radius: 50%;}

.live_user {
	line-height: 1;
	margin-left: 96upx;
	display: block;
	margin-top: 10upx;
}

.live_explain {
	margin: 20upx 23upx 0;
}
.live_explain .text {
	line-height: 1.2;
}
</style>
