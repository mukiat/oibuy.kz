<template>
    <view class="announcement" :class="{'new-announ' : bNewStyle}">
		<view class="content uni-flex uni-flex-item">
			<view class="icon">
				<image :src="module.allValue.img" mode="widthFix"></image>
			</view>
			<block v-if="bNews">
				<swiper class="swiper" autoplay="true" circular="true" vertical="true" interval="5000">
					<swiper-item class="swiper-item" v-for="(item, index) in announcementList" :key="index" @click="link(item.id)">
						<view class="tit uni-ellipsis">{{item.title}}</view>
						<view class="last" v-if="bDateSel">{{item.date}}</view>
					</swiper-item>
				</swiper>
				<view class="more-link" v-if="!bNewStyle">
					<view class="txt" @click="$outerHref('/pagesC/article/article','app')">更多</view>
					<uni-icons type="arrowright" size="20"></uni-icons>
				</view>
				<view class="more-link-new2" @click="$outerHref('/pagesC/article/article','app')" v-else>
					<icon class="iconfont icon-home-more"></icon>
				</view>
			</block>
			<block v-else>
				<view class="seamless">
					<view class="seamless-scroll" :style="{left: marqueeDistance + 'px'}">{{ module.allValue.announContent }}</view>
				</view>
			</block>
		</view>
    </view>
</template>

<script>
import uniIcons from '@/components/uni-icons/uni-icons.vue';
import universal from '@/common/mixins/universal.js';
export default{
	mixins:[universal],
	props: ['module', 'preview', 'modulesIndex','shopId'],
	data(){
		return {
			scroll: true,
			winScrollObj: 'announ' + this.modulesIndex,
			announcementList: [],
			hideNotice: false,
			marqueePace: 1,//滚动速度
			marqueeDistance: 10,//初始滚动距离
			size: 12,
			interval: 20, // 时间间隔
			countTime: '',
			length:'',
			windowWidth:0
		}
	},
	components:{
		uniIcons
	},
	created(){
		if(this.bNews){
			uni.request({
				url: this.websiteUrl + '/api/visual/article',
				method: 'POST',
				data: {
					cat_id: this.catId || 0,
					num: this.nNumber
				},
				header: {
					'Content-Type': 'application/json',
					'token': uni.getStorageSync('token'),
					'X-Client-Hash':uni.getStorageSync('client_hash')
				},
				success: (res) => {
					this.announcementList = res.data.data
				},
				fail: (err) => {
					console.error(err)
				}
			})
		}else{
			this.run1();
		}
	},
	computed:{
		catId() {
            let arr = [], len = 0

            this.module.allValue.optionCascaderVal ? (arr = this.module.allValue.optionCascaderVal.split(',')) : (arr = [])
            len = arr.length

            return arr[len - 1]
        },
        nNumber() {
            return this.module.allValue.number
        },
        bNews() {
			return this.module.isStyleSel == '0' && this.shopId == 0 ? true : false
        },
        bDateSel() {
            return this.module.isDateSel == '0' ? true : false
        },
		bNewStyle() {
			return this.module.isNewStyleSel == '1' ? true : false
		},
		notice(){
			return this.module.allValue.announContent
		}
	},
	methods:{
		run1(){
			var that = this;
			var length = that.notice.length * that.size; //文字长度
			var windowWidth = uni.getSystemInfoSync().windowWidth; // 屏幕宽度

			that.length = length;
			that.windowWidth = windowWidth;
			that.marqueeDistance = windowWidth;

			that.countTime = setInterval(function(){
				if (-that.marqueeDistance < that.length){
					that.marqueeDistance = that.marqueeDistance - that.marqueePace;
				} else {
					clearInterval(that.countTime);
					that.marqueeDistance = that.windowWidth;
					that.run1();
				}
			}, that.interval);
		},
		link(id){
			uni.navigateTo({
				url:'/pagesC/article/detail/detail?id='+id
			})
		}
	}
}
</script>

<style>
.announcement{ padding: 0 20upx 0 30upx; background: #FFFFFF; height: 100upx; display: flex; flex-direction: row; overflow: hidden;}
.announcement .content{ height: 100%; flex: 1 1 0%;}
.announcement .icon{ width: 120upx; vertical-align: middle; display: flex; align-items: center; margin: 10upx 20upx 10upx 0;}
.announcement .swiper{ flex: 1 1 0%; height: 100upx;}
.announcement .swiper-item{ display: flex; flex-direction: column; justify-content: center;}
.announcement .swiper-item .tit{ margin-top: 6upx; font-size: 28upx; line-height: 1.5;}
.announcement .swiper-item .last{ color: #999999; line-height: 1.5; font-size: 25upx;}

.seamless{ position: relative; width: 100%; height: 50upx; padding: 25upx 0; overflow: hidden;}
.seamless-scroll{white-space: nowrap; position: absolute;}

/*新版新增样式*/
.new-announ{ margin: 0 20upx; border-radius: 0 0 20upx 20upx; position: relative;}
.new-announ::after{
	content: ' ';
	position: absolute;
	left: 20upx;
	right: 20upx;
	height: 2upx;
	top: 2upx;
	background-color: #EDEDED;
}
.new-announ .more-link-new2{ display: flex; flex-direction: row; justify-content: flex-end; align-items: center; color: #F10D23; width: 3rem; cursor: pointer;}
</style>
