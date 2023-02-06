import * as localConfig from '../local/config'
import request from '@/common/request.js'

const webUrl = localConfig.websiteUrl

/* scopeApp - session */
function scopeAppSession(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/user/wxapp/session',o).then(res=>{
			reslove(res)
		})
	})
}

/* scopeApp - user-profile*/
function scopeUserProfile(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/user/wxapp/user-profile/decrypt',o).then(res=>{
			reslove(res)
		})
	})
}

/* scopeApp - user-info */
function scopeUserInfo(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/user/wxapp/user-info/decrypt',o).then(res=>{
			reslove(res)
		})
	})
}

/* scopeApp - phonenumber */
function scopePhoneNumber(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/user/wxapp/phone-number/decrypt',o).then(res=>{
			reslove(res)
		})
	})
}

/* scopeApp - login */
function scopeAppLogin(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/user/wxapp/login',o).then(res=>{
			if(o.delta){
				reslove({
					data:res,
					delta:o.delta
				})
			}else{
				reslove({
					data:res
				})
			}
		})
	})
}


/* 客服 */
function getChat(o){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/chat',o).then(res=>{
			reslove(res)
		})
	})
}

/* 获取定位 */
function setLocation(){
	return new Promise((reslove, reject) => {
		uni.getLocation({
			type: 'wgs84',
			success: function(res){
				let lat = res.latitude;
				let lng = res.longitude;

				request.get(webUrl + '/api/misc/position',{
					lat:lat,
					lng:lng
				}).then(res=>{
					let data = res.data;
					let itemsBak;
					if(data){
						itemsBak = {
							province:{
								id:data.province_id,
								name:data.province
							},
							city:{
								id:data.city_id,
								name:data.city
							},
							district:{
								id:data.district_id,
								name:data.district
							},
							postion:{
								lat:lat,
								lng:lng
							}
						}
					}

					reslove({
						data:itemsBak
					})
				})
			},
			fail(res) {
				console.log(res)
			}
		})
	})
}

/* 地区 */
function getRegion(o){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/misc/region',o).then(res=>{
			if(res.length > 0){
			    reslove({
			        id:o.region,
			        level:o.level,
			        data:res,
			        status:false
			    })
			}else{
			    reslove({
			        status:true
			    })
			}
		})
	})
}

/* 图片验证码 */
function getImgVerify(o){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/misc/captcha',o).then(res=>{
			reslove(res)
		})
	})
}

/* 发送短信验证码 */
function getSendVerify(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/misc/sms/send',o).then(res=>{
			if(res.status == 'success'){
			    reslove(res.data.status)
			}else{
				uni.showToast({ title: res.errors.message, icon: "none" });
			}
		})
	})
}

/* 验证填写的短信验证码是否正确 */
function getSmsVerify(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/misc/sms/verify',o).then(res=>{
			reslove(res)
		})
	})
}

/* 获取后台设置shopcofig */
function getShopConfig(){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/shop/config').then(res=>{
			reslove(res)
		})
	})
}

function getSplashAdPosition(){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/app/ad_position').then(res=>{
			reslove(res)
		})
	})
}

/* 购物车公共数量 */
function getCommonCartNumber(){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/cart/cartNum').then(res=>{
			reslove(res)
		})
	})
}

/* app版本更新 */
function getAppUpdate(o){
	return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/app/auto_update',o).then(res=>{
			reslove(res)
		})
	})
}

/* 视频号直播 */
function getMediaLivePlay(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/media/live/play',o).then(res=>{
			reslove(res)
		})
	})
}

export default{
	scopeAppSession,
	scopeUserProfile,
	scopeUserInfo,
	scopePhoneNumber,
	scopeAppLogin,
	setLocation,
	getChat,
	getRegion,
    getImgVerify,
    getSendVerify,
    getSmsVerify,
	getShopConfig,
	getSplashAdPosition,
	getCommonCartNumber,
	getAppUpdate,
	getMediaLivePlay
}
