<script>
	import * as localConfig from './config/local/config'
	// #ifdef MP-WEIXIN
	let livePlayer = requirePlugin('live-player-plugin')
	// #endif
	export default {
		globalData:{
			mpName:"大商创",
			mpVersionName:"2.7.2",
			mpVersionCode:272,
			integration:0,
			isShowCart: false,
			navigationBarHeight: 0
		},
		onLaunch: async function (e) {

			const wgtinfo = uni.getStorageSync('wgtinfo');
			//获取shopConfig
			this.$store.dispatch('setShopConfig');
			
			//获取购物车数量
			//this.$store.dispatch('setCommonCartNumber');
			
			let platform = '';
			//#ifdef APP-PLUS
			platform = 'APP';
			//#endif
			//#ifdef H5
			platform = 'H5';
			//#endif
			//#ifdef MP-WEIXIN
			platform = 'MP-WEIXIN';
			//#endif
			uni.setStorageSync('platform',platform)
			
			// #ifdef APP-PLUS
			plus.runtime.getProperty(plus.runtime.appid,(wgtinfo)=>{
				uni.setStorageSync('wgtinfo',JSON.stringify(wgtinfo))
			})
			
			//运行环境
			// if(process.env.NODE_ENV !== 'development'){
			// 	//隐私声明和引导广告
			// 	let roles,configData,configPrivacy,privacy;
				
			// 	//接口返回config
			// 	configData = await this.$store.dispatch('setShopConfig',{type:true});
				
			// 	//后台设置隐私协议文章id和版本号
			// 	configPrivacy = configData.data.privacy;
				
			// 	//获取本地存储隐私协议
			// 	privacy = uni.getStorageSync('privacy') ? JSON.parse(uni.getStorageSync('privacy')) : '';
				
			// 	//获取设备信息
			// 	const platform = uni.getSystemInfoSync().platform;
				
			// 	if(platform == 'ios'){
			// 		if(privacy.article_id == configPrivacy.article_id && privacy.version_code == configPrivacy.version_code){
			// 			uni.redirectTo({ url:"/pages/guide/guide" });
			// 		}else{
			// 			uni.redirectTo({ url:"/pagesC/privacy/privacy" });
			// 		}
			// 	}
			// }
			// #endif
			
			//#ifdef MP-WEIXIN
			if(!uni.getStorageSync('userRegion')) this.$store.dispatch('getLocation')
			uni.setStorageSync('wgtinfo',JSON.stringify({name:'大商创',version:'2.6.1'}));
			// #endif
		},
		onShow: async function (e) {
			// #ifdef MP-WEIXIN
			console.log(JSON.stringify(e) + 'onshow');
			// 小程序直播分享
			if (e.scene == 1007 || e.scene == 1008 || e.scene == 1044){
				livePlayer.getShareParams().then(res=>{
					uni.setStorageSync("parent_id", res.custom_params.parent_id);
				})
			}
			
			// 场景值
			uni.setStorageSync("scene", e.scene);
			// #endif
			
			//#ifdef APP-PLUS
			let args= plus.runtime.arguments;
			
			if(args){
				let url = args.split('//')[1];
				if(url){
					let obj = {}, index = url.indexOf('?'), params = url.substr(index + 1);
					
					//url转换为json对象
					if(index != -1) {
						let parr = params.split('&');
						for(let i of parr) {
							let arr = i.split('=');
							obj[arr[0]] = arr[1];
						}
					}
				}
			}
			//#endif
		},
		onHide: function () {
			console.log('App Hide')
		}
	}
</script>

<style lang="scss">
	/*每个页面公共css */
	@import "./common/css/iconfont.css";
	@import "./common/css/uni.css";
	@import "./common/css/common.css";
	
	/* start--Retina 屏幕下的 1px 边框--start */
	.u-border,
	.u-border-bottom,
	.u-border-left,
	.u-border-right,
	.u-border-top,
	.u-border-top-bottom {
		position: relative
	}
	
	.u-border-bottom:after,
	.u-border-left:after,
	.u-border-right:after,
	.u-border-top-bottom:after,
	.u-border-top:after,
	.u-border:after {
		/* #ifndef APP-NVUE */
		content: ' ';
		/* #endif */
		position: absolute;
		left: 0;
		top: 0;
		pointer-events: none;
		box-sizing: border-box;
		-webkit-transform-origin: 0 0;
		transform-origin: 0 0;
		// 多加0.1%，能解决有时候边框缺失的问题
		width: 199.8%;
		height: 199.7%;
		transform: scale(0.5, 0.5);
		border: 0 solid #e4e7ed;
		z-index: 2;
	}
	
	.u-border-top:after {
		border-top-width: 1px
	}
	
	.u-border-left:after {
		border-left-width: 1px
	}
	
	.u-border-right:after {
		border-right-width: 1px
	}
	
	.u-border-bottom:after {
		border-bottom-width: 1px
	}
	
	.u-border-top-bottom:after {
		border-width: 1px 0
	}
	
	.u-border:after {
		border-width: 1px
	}
	/* end--Retina 屏幕下的 1px 边框--end */
</style>
