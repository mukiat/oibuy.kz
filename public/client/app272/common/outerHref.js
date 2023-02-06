import store from '../store'
import isLogin from '@/common/mixins/isLogin'

let timer = ''

export default async function outerHref(url,built,mobile){
	if(mobile === ''){
		uni.showModal({
			content: this.$t('lang.is_user_bind_mobile_phone'),
			success: res => {
				if (res.confirm) {
					uni.navigateTo({
						url: '/pagesB/accountsafe/bindphone/bindphone?delta=1'
					});
				}
			}
		});
		
		return
	}
	
	if(built == 'app'){
		let reg = RegExp(/index\/index|category\/category|cart\/cart|user\/user|integration\/integration/);
		let isUrl = reg.test(url);
		
		if(isUrl){
			//2.1.2新功能开发增加
			if(url.indexOf('integration') != -1){
				if(url.indexOf('type')){
					let parr = url.split('?')[1].split('=')[1];
					
					//全局变量integration赋值
					getApp().globalData.integration = parseInt(parr);
				}
				
				uni.switchTab({
					url:'/pages/integration/integration',
					success: () => {
						uni.hideLoading();
						store.commit('toggleHrefType',false)
					}
				})
			}else{
				uni.reLaunch({
					url:url
				})
			}
		}else{
			uni.navigateTo({
				url:url
			})
		}
		
		return
	}
	
	if(built != undefined && built != 'undefined' && built != 'onback'){
		if(built == true || built == 'app'){
			let reg = RegExp(/pages\/index\/index|category\/category|cart\/cart|user\/user|integration\/integration/);
			let isUrl = reg.test(url);
			clearTimeout(timer);
			uni.showLoading({
				title: '跳转中...',
				mask:true
			});

			timer = setTimeout(()=>{
				if(isUrl){
					//1.6.2新功能开发增加
					if(url.indexOf('integration') != -1){
						if(url.indexOf('type')){
							let parr = url.split('?')[1].split('=')[1];
							
							//全局变量integration赋值
							getApp().globalData.integration = parseInt(parr);
						}
						
						uni.switchTab({
							url:'/pages/integration/integration',
							success: () => {
								uni.hideLoading();
								store.commit('toggleHrefType',false)
							}
						})
					}else{
						uni.reLaunch({
							url:url,
							success: () => {
								uni.hideLoading();
								store.commit('toggleHrefType',false)
							}
						})
					}
				}else{
					uni.navigateTo({
						url:url,
						success: () => {
							uni.hideLoading();
							store.commit('toggleHrefType',false)
						}
					})
				}
			},500)
		}else{
			uni.showModal({
				content: "您需要登录会员!",
				success:(res)=>{
					if(res.confirm){
						uni.navigateTo({
							url:'/pagesB/login/login?delta=1'
						})
					}
					
					store.commit('toggleHrefType',false)
				}
			})
		}
	}else{
		let roles = await store.dispatch('setShopConfig',{type:true});
		uni.setStorage({key:'configData',data:roles.data})
		
		// #ifdef APP-PLUS
		let wgtinfo = JSON.parse(uni.getStorageSync('wgtinfo'));
		let version = Number(wgtinfo.version.replace(/\./g, ''));
		
		if((version == roles.data.app_in_review) || url === ''){
			store.commit('toggleHrefType',true)
			return false
		}
		// #endif
		
		// #ifdef MP-WEIXIN
		if((getApp().globalData.mpVersionCode == roles.data.weapp_in_review) || url === ''){
			store.commit('toggleHrefType',true)
			return false
		}
		// #endif
		
		//if(controlVersion){
			// #ifdef MP-WEIXIN
			uni.navigateTo({
				url:"/pagesC/web-view/web-view?url="+ encodeURIComponent(url)
			});
			// #endif
			
			// #ifdef APP-PLUS
			let like = ''
			if(built != 'onback'){
				like = "/pagesC/web-view/web-view?url="+ encodeURIComponent(url)
			}else{
				store.commit('toggleSplashType',false)
				
				like = "/pagesC/web-view/web-view?url="+ encodeURIComponent(url) + '&type=onback'
			}
			uni.navigateTo({
				url:like
			});
			// #endif
		//}
	}
}