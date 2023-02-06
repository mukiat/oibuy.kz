<template>
	<view class="commom-nav-warp" :class="{'show':scrollState}">
		<view class="commom-nav" :class="{'commom-nav-outer': outerClass,'show':scrollState}" id="commom-nav" @click="backTop">
			<i class="iconfont icon-dingbu"></i>
		</view>
	</view>
</template>

<script>
	export default {
		name: 'filter-top',
		props:{
			isScrollView:{
				type:Boolean,
				default:false
			},
			scrollState:{
				type:Boolean,
				default:false
			},
			outerClass:{
				type:String,
				default:''
			}
		},
		methods:{
			backTop(){
				if(this.isScrollView){
					this.$emit("fileterScrollTop", 0);
				}else{
					uni.pageScrollTo({scrollTop: 0,duration: 100});
				}
			}
		}
	}
</script>

<style scoped>
.commom-nav{
    width: 95rpx;
    height: 95rpx;
    border-radius: 50%;
    position: fixed;
    bottom: 45rpx;
    background-color: #fff;
    right: 50rpx;
    z-index: 1999;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 1px 1px 2px 0 rgba(0,0,0,.05);
}
.commom-nav .iconfont{
    color: #333;
    font-size: 32rpx;
    margin-top: 3px;
}
.commom-nav-outer{
    right: 6rpx;
    bottom: 12%;
}

.commom-nav-warp{
	display: none;
}
.commom-nav-warp.show{
	display: block;
}
</style>
