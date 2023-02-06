<template>
	<view class="content status_bar">
		<swiper :indicator-dots="true" :autoplay="autoplay" :interval="5000" :duration="500" class="swiper" :style="{'height':windowHeight}" @animationfinish="animationfinish">
			<swiper-item v-for="(item,index) in splashList.list" :key="index" @click="$outerHref(item.ad_link,'onback')"><view class="swiper-item" :style="{'height':windowHeight}"><image :src="item.ad_code" class="img"></image></view></swiper-item>
		</swiper>
		<view class="backhome" @click="skipClick" v-if="!autoplay">{{$t('lang.goto_home')}}</view>
		<view class="skip" @click="skipClick" v-if="autoplay">
			<view>{{$t('lang.skip')}}</view>
			<uni-icons type="arrowright" size="18" color="#FFF9F8"></uni-icons>
		</view>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue'
	export default {
		components: {
			uniIcons
		},
		data() {
			return {
				windowHeight:'603px',
				autoplay:true,
				timer:""
			};
		},
		onLoad(){
			var that = this;
			uni.getSystemInfo({
				success: (res) => {
					this.windowHeight = res.windowHeight + 'px';
				}
			})
			
			this.adv();
		},
		computed:{
			...mapState({
				splashList: state => state.common.splashList
			}),
			listLength(){
				return this.splashList.list ? this.splashList.list.length : 0
			}
		},
		methods:{
			animationfinish(e){
				if((e.detail.current == this.listLength-1) && this.listLength > 1){
					this.autoplay = false
					if(this.$store.state.splashType){
						this.timer = setTimeout(()=>{
							uni.switchTab({
								url:'/pages/index/index'
							})
							uni.setStorageSync('splash',true);
						},3000)
					}
				}
			},
			adv(){
				this.$store.dispatch('setSplashAdPosition')
			},
			skipClick(){
				this.$store.commit('toggleSplashType',false)
				clearTimeout(this.timer)
				
				uni.switchTab({
					url:'/pages/index/index'
				})
				uni.setStorageSync('splash',true);
			}
		}
	}
</script>

<style>
.skip{
	position: absolute;
	top: 40px;
	right: 10px;
	padding: 3px 10px 3px 20px;
	background:rgba(153,153,153,.9);
	color: #FFF9F8;
	border-radius: 20px;
	display: flex;
	flex-direction: row;
	align-items: center;
}
.skip .uni-icon{
	vertical-align: middle;
	margin-top: 1px;
}

.backhome{
	position: absolute;
	bottom: 50px;
	left: 50%;
	padding: 5px 20px;
	width: 60px;
	text-align: center;
	background: rgba(153,153,153,.9);
	border-radius: 20px;
	color: #fff;
	margin-left: -50px;
}
</style>
