<template>
	<view class="com-nav-footer">
		<view class="com-list-footer">
			<view class="lie link" @click="changeNav" :class="{'active':type == 'index'}">
				<icon class="iconfont icon-medal tm-icon-size"></icon>
				<text class="txt">{{$t('lang.discover_home')}}</text>
			</view>
			<view class="lie j-community-btn" @click="onPutDiscover" v-if="!myMode">
				<view class="btn-bg"></view>
				<view class="btn-wy"></view>
				<view class="btn-icon"><icon class="iconfont icon-jia"></icon></view>
			</view>
			<view class="lie link" @click="onMyDiscover" :class="{'active':type == 'my'}">
				<icon class="iconfont icon-geren"></icon>
				<text class="txt">{{$t('lang.my_post')}}</text>
			</view>
		</view>
	</view>
</template>

<script>
export default{
	props:{
		mode:{
			type:Boolean,
			Default:false
		},
		type:{
			type:String,
			Default:''
		}
	},
	data(){
		return {
			myMode:this.mode
		}
	},
	onLoad(e) {
		this.model = e.model;
	},
	computed:{
		isLogin(){
			return this.$isLogin()
		}
	},
	methods:{
		onMyDiscover(){
			if(this.$isLogin()){
				uni.navigateTo({
					url:'/pagesC/discover/me/me'
				})
			}else{
				uni.showModal({
					content:'您需要登录会员！',
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
		onPutDiscover(){
			if(this.$isLogin()){
				this.myMode = this.myMode === false ? true : false
				this.$emit('getState',this.myMode)
			}else{
				uni.showModal({
					content:'您需要登录会员！',
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
		changeNav(url){
			uni.navigateTo({
				url:'/pagesC/discover/index'
			})
		}
	}
}
</script>

<style>
.com-nav-footer{ position: fixed; left: 0; right: 0; bottom: 0; background: #FFFFFF; box-shadow: 2upx 10upx 30upx rgba(50, 50, 50, 0.3); z-index: 10; padding-bottom: env(safe-area-inset-bottom);}
.com-list-footer{ display: flex; position: relative; height: 110upx; justify-content: center; align-items: center;}
.com-list-footer .lie{ width: 33.3%; flex: 1; }
.com-list-footer .link{ display: flex; flex-direction: column; justify-content: center; align-items: center;}
.com-list-footer .link .iconfont{ font-size: 40upx; height: 60upx; line-height: 60upx; color: #888;}
.com-list-footer .link .txt{ font-size: 25upx; color: #888;}
.com-list-footer .active .iconfont,.com-list-footer .active .txt{ color: #f92028;}

.com-list-footer .j-community-btn{position: relative; height: 100%;}
.com-list-footer .j-community-btn .btn-bg{ background: #FFFFFF; position: absolute; left: 0; right: 0; top: 0; bottom: 0; z-index: 12;}
.com-list-footer .j-community-btn .btn-wy{ width: 100upx; height: 100upx; border-radius: 100%; position: absolute; top: -30upx; left: 25%; z-index: 10; box-shadow: 2upx 4upx 30upx rgba(50, 50, 50, 0.3); border: 8upx solid #FFFFFF;}
.com-list-footer .j-community-btn .btn-icon{ width: 100upx; height: 100upx; border-radius: 100%; background: #ec5151; position: absolute; top: -30upx; left: 25%; z-index: 10; z-index: 13; border: 8upx solid #FFFFFF; display: flex; justify-content: center; align-items: center;}
.com-list-footer .j-community-btn .btn-icon .iconfont{ font-size: 32upx; color: #FFFFFF;}
</style>