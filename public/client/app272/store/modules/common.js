import {
	LOCATION,
	REGION,
	IMG_VERIFY,
	SCOPE_APP_LOGIN,
	SHOP_CONFIG,
	SPLASH_AD_POSITION,
	COMMON_CART_NUMBER,
	UPDATE_TEXT,
	UPDATE_GLOBAL_BGCOLOR,
	UPDATE_IS_SHOW,
} from '../mutation-type.js'

import { apiCommon } from '@/config'
import store from '@/store'

const state = {
	region:{
		data:{
			provinceData:[],
			cityData:[],
			districtData:[],
			streetData:[],
		},
		id:1,
		level:1,
		status:false
	},
	imgVerify:{
		captcha:'',
		client:''
	},
	phoneNumber:'',
	bonusData:'',
	shopConfig:'',
	splashList:[],
	trade_pwd:'',
	cartNumber:0,
	versionData:''
}

const mutations = {
	[SCOPE_APP_LOGIN](state,o){		
		let data = o.data
		if(data.status == "failed"){
			if(data.errors){
				uni.showToast({
					title:data.errors.message ? data.errors.message : '该手机号已被注册或绑定',
					icon:'none'
				})
				
				//默认跳也会员中心
				if(data.extend.type==''){
					setTimeout(()=>{
						uni.switchTab({
							url:'/pages/user/user'
						});
					},1000)
				}else if(data.extend.type != 'user'){
					//跳转页
				}
			}else{
				console.log(JSON.stringify(data))
			}
		}else{
			//存本地token
			uni.setStorage({
				key:'token',
				data:data.data,
				success: (res) => {
					//记录user_id
					store.dispatch('setUserId');
					
					if(o.delta == 1){
						uni.navigateBack({
							delta:1
						});
					}else if(o.delta == 'index'){
						uni.switchTab({
							url:'/pages/index/index'
						});
					}else{
						//默认跳也会员中心
						if(data.extend.type == ''){
							uni.switchTab({ 
								url:'/pages/user/user'
							});
						}else if(data.extend.type != ''){
							//跳转页
							console.log(69999)
						}
					}
				}
			});
			
			//获取购物车数量
			store.dispatch('setCommonCartNumber');
		}
	},
	[LOCATION](state,o){
		//存本地缓存
		let obj = o.data;
		
		if(obj.street == undefined){
			obj.street = {id:'',name:''};
		}
		
		obj.regionSplic = obj.province.name + ' ' + obj.city.name + ' ' + obj.district.name;
		
		uni.setStorageSync("userRegion",obj);
	},
	[REGION](state,o){
        if(o.status != true){
            switch(o.level){
                case 1:
                    state.region.data.provinceData = o.data
                    break
                case 2:
                    state.region.data.cityData = o.data
                    break
                case 3:
                    state.region.data.districtData = o.data
                    break
                case 4:
                    state.region.data.streetData = o.data
                    break
                default:
                    break
            }
			
			state.region.id = o.id
			state.region.level = o.data[0].level
        }
        state.region.status = false
    },
    [IMG_VERIFY](state,o){
        state.imgVerify.captcha = o.data.captcha
        state.imgVerify.client = o.data.client
    },
	[SHOP_CONFIG](state,{res,o}){
		state.bonusData = res.data.bonus_ad
		state.shopConfig = res.data
		
		uni.setStorage({
			key:'configData',
			data:res.data
		})
		
		//视频号直播
		// #ifdef MP-WEIXIN
		if(!uni.getStorageSync('channelsLive')){
			if(res.data.wxapp_media_id){
				uni.getChannelsLiveInfo({
					finderUserName: res.data.wxapp_media_id,
					success(res){
						uni.setStorageSync('channelsLive',res);
					},
					fail(fail){
						console.log(fail)
					}
				})
				
				//获取购物车数量
				store.dispatch('setMediaLivePlay').then(({data,status})=>{
					if(status == 'success'){
						uni.setStorageSync('mediaLive',data);
					}
				});
			}else{
				uni.removeStorageSync('channelsLive');
				uni.removeStorageSync('mediaLive');
			}
		}
		// #endif
	},
	[SPLASH_AD_POSITION](state,o){
		state.splashList = o.data
	},
	[COMMON_CART_NUMBER](state,o){
		let cartNumber = Number(o.data.cart_number)
		state.cartNumber = cartNumber
		uni.setStorage({
			key:'cartNumber',
			data:cartNumber
		})
	},
	//公共方法修改text
	[UPDATE_TEXT](state, o){
		state[o.attrName] = o.newValue
	},
	[UPDATE_GLOBAL_BGCOLOR](state,{ rootState, o }){
		rootState.shop.modules.find((v)=>{
			if(v.globalbg && o.bgColor){
				v.data.allValue.bgColor = o.bgColor
			}
		})
	},
	[UPDATE_IS_SHOW](state,{rootState, o}){
		rootState.shop.modules.find((v)=>{
			if(typeof v.fixed === "undefined"){
				v.isShow = o.type
			}
		})
		rootState.topCategoryCatid = o.cat_id
	}
}

const actions = {
	//scopeApp
	getScopeAppSession({ commit },o){
		return apiCommon.scopeAppSession(o);
	},
	getScopeUserProfile({ commit }, o){
		return apiCommon.scopeUserProfile(o);
	},
	getScopeUserInfo({ commit }, o){
		return apiCommon.scopeUserInfo(o);
	},
	getScopePhoneNumber({ commit }, o){
		return apiCommon.scopePhoneNumber(o);
	},
	getScopeAppLogin({ commit }, o){
		apiCommon.scopeAppLogin(o).then(res=>{
			commit(SCOPE_APP_LOGIN,res)
		})
	},
	//定位
	getLocation({ commit } ,o){
		apiCommon.setLocation(o).then(res=>{
			commit(LOCATION,res)
		})
	},
	//客服
    setChat({ commit }, o){
        return apiCommon.getChat(o)
    },
	//地区
	setRegion({ commit }, o){
        apiCommon.getRegion(o).then(res=>{
            commit(REGION, res)
        })
    },
	getRegionList({ commit }, o){
	    return apiCommon.getRegion(o)
	},
	//图像验证码
    setImgVerify({ commit }, o){
        apiCommon.getImgVerify(o).then(res=>{
            commit(IMG_VERIFY, res)
        })
    },
	//发送短信
    async setSendVerify({ commit }, o){
        return apiCommon.getSendVerify(o)
    },
	//短信验证码验证
    async setSmsVerify({ commit }, o){
        return apiCommon.getSmsVerify(o)
    },
	//获取shopconfig
	setShopConfig({ commit },o){
		if(o && o.type){
			return apiCommon.getShopConfig()
		}else{
			apiCommon.getShopConfig().then(res=>{
				commit(SHOP_CONFIG,{res,o})
			})
		}
	},
	//splash广告
	setSplashAdPosition({ commit },o){
		if(o && o.type){
			return apiCommon.getSplashAdPosition()
		}else{
			apiCommon.getSplashAdPosition().then(res=>{
				commit(SPLASH_AD_POSITION,res)
			})
		}
	},
	//公共方法修改text
	updateText({ commit }, o) {
        commit(UPDATE_TEXT, o);
    },
	//导航栏显示购物车数量
	setCommonCartNumber({ commit }, o){
		apiCommon.getCommonCartNumber().then(res=>{
			commit(COMMON_CART_NUMBER, res)
		})
	},
	//app版本更新
	setAppUpdate({ commit }, o){
		return apiCommon.getAppUpdate(o)
	},
	//新开发轮播颜色切换
	updateGlobalBgColor({ commit,rootState }, o){
		commit(UPDATE_GLOBAL_BGCOLOR, { rootState,o })
	},
	//新开发首页顶级分类切换
	updateIsShow({ commit,rootState}, o){
		commit(UPDATE_IS_SHOW, { rootState,o })  
	},
	//视频号直播
	setMediaLivePlay({commit},o){
		return apiCommon.getMediaLivePlay(o);
	}
}

export default {
    state,
    mutations,
    actions,
}