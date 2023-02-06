<template>
	<view class="dsc-loading" :style="{height:clientHeight + 'px'}" v-if="dscLoading">
		<view class="image"><image :src="loadUrl" mode="widthFix" class="img"></image></view>
		<view class="mask"></view>
	</view>
</template>

<script>
	export default {
		props:{
			dscLoading:{
				type:Boolean,
				default:false
			}
		},
		data() {
			return {
				clientHeight:''
			};
		},
		onLoad(e){
			this.clientHeight = uni.getSystemInfoSync().windowHeight
		},
		computed:{
			loadUrl(){
				return this.websiteUrl + '/img/loading.gif'
			}
		}
	}
</script>

<style scoped>
.dsc-loading{ position: fixed; top: 0; right: 0; bottom: 0; left: 0; width: 100%; z-index: 99999; overflow-y: hidden; display: flex; flex-direction: row; justify-content: center; align-items: center;}
.dsc-loading .mask{ position: absolute; top: 0; right: 0; bottom: 0; left: 0; width: 100%; height: 100%; z-index: 99999; background: #FFFFFF; }
.dsc-loading .image{ width: 100upx; height: 100upx; position: absolute; z-index: 100000;}
.dsc-loading .image .img{ width: 100%; height: 100%;}
</style>
