<template>
    <view class="shop-menu">
		<view class="items">
			<view class="item" @click="$outerHref('/pages/shop/shopDetail/shopDetail?id='+shopId,'app')">
				<view class="info">
					<text class="iconfont icon-dianpu1"></text>
					<text>店铺详情</text>
				</view>
			</view>
			<view class="item" :class="{'active':bCategorySubmenu}" @click.stop="showCategory">
				<view class="info">
					<text class="iconfont icon-fenlei1"></text>
					<text>热门分类</text>
				</view>
				<view class="sub-menu">
					<view v-for="(item,index) in aCategory" :key="index" @click="getCatUrl(item.cat_id)" class="menu-item">
						{{item.cat_name}}
					</view>
				</view>
			</view>
			<!-- #ifdef MP-WEIXIN -->
			<block v-if="wxappChat > 0">
				<button open-type="contact" type="default" size="5" session-from="weapp" hover-class="none" class="item kefu-cantact">
					<view class="info">
						<text class="iconfont icon-kefu"></text>
						<text>客服</text>
					</view>
				</button>
			</block>
			<block v-else>
				<block v-if="isLogin===true">
					<view class="item" open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile">
						<view class="info">
							<text class="iconfont icon-kefu"></text>
							<text>客服</text>
						</view>
					</view>
					<view class="item" @click="getUserProfile('')" v-else>
						<view class="info">
							<text class="iconfont icon-kefu"></text>
							<text>客服</text>
						</view>
					</view>
				</block>
				<block v-else>
					<view class="item" @click="onChat(0,shopId)">
						<view class="info">
							<text class="iconfont icon-kefu"></text>
							<text>客服</text>
						</view>
					</view>
				</block>
			</block>
			<!-- #endif -->
			<!-- #ifdef APP-PLUS -->
			<view class="item" @click="onChat(0,shopId)">
				<view class="info">
					<text class="iconfont icon-kefu"></text>
					<text>客服</text>
				</view>
			</view>
			<!-- #endif -->
			<view class="item item-follow" @click.stop="isFollow()">
				<view class="info">
				<text class="iconfont" :class="[bFollow ? 'icon-collection-alt' : 'icon-collection']"></text>
				<text>{{ sFollow }}</text>
				</view>
			</view>
		</view>
		
		<!-- 会员登录获取手机号码 start -->
		<uni-popup :show="getPhoneNumberShow" class="phone_box"  :animation="true">
			<view class="pop_content pop_phone_number">
				<view class="pop_header" style="padding:0;">					
					<text class="pop_title">{{$t('lang.Authorized_Phone_Number')}}</text>
				</view>
				<view class="pop_phone_content">
					<view class="logo">
					  <image src="../../../static/logo.png" class="img"></image>
					</view>
					<text>{{$t('lang.user_bind_phone')}}</text>
				</view>
				<view class="btn">					
					<button type="primary"  style="border-radius: 20px;height: 70rpx; line-height: 70rpx;font-size:16px;" open-type="getPhoneNumber" @getphonenumber="getPhoneNumber" >{{$t('lang.confirm_on')}}</button>
				</view>
			</view>
		</uni-popup>
		<!-- 会员登录获取手机号码 end -->
	</view>
</template>

<script>
import { mapState } from 'vuex'

import uniPopup from '@/components/uni-popup.vue';
import universal from '@/common/mixins/universal.js';

export default{
	props: ['shopId'],
	mixins:[universal],
	data(){
		return {
			scopeUserInfo: {},
			scope: 'userInfo',
			scopeSessions: {},
			canIUseProfile:false,
			getPhoneNumberShow:false,
			isLoginData:false,
			bCategorySubmenu: false,
			aCategory: [],
			sKf:'',
			//微信小程序客服
			wxappChat:uni.getStorageSync("configData").wxapp_chat || 0
		}
	},
	components: {
		uniPopup
	},
	created(){
		let that = this

		that.$store.dispatch('setVisualStorein',{
			ru_id: that.shopId
		});

		uni.request({
			url: that.websiteUrl + '/api/visual/storedown',
			method: 'POST',
			data: {
				ru_id:that.shopId
			},
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				let data = res.data.data
				const {
				  shop_category: shopCategory,
				  shop_about: shopAbout,
				  kf,
				} = data[0]

				that.sKf = kf
				that.aCategory = shopCategory.slice(0,9)
			},
			fail: (res) => {
				console.log('api/visual/storedown request fail')
			}
		})
	},
	computed:{
		...mapState({
			shopInfo: state => state.shop.shopInfo,
		}),
		bFollow(){
			return this.shopInfo && this.shopInfo.count_gaze == 1 ? true : false
		},
		sFollow(){
			return this.shopInfo && this.shopInfo.count_gaze != 1 ? '关注' : '已关注'
		},
		isLogin(){
			let isLogin = uni.getStorageSync('bindMobilePhone') && this.$isLogin() ? false : true;
			//#ifdef MP-WEIXIN
			if(isLogin){					
				this.scopeSession();					
				// 判断小程序是否存在getUserProfile
				if(uni.getUserProfile) this.canIUseProfile = true;
			}
			//#endif	
						
			//#ifdef APP-PLUS
			isLogin = false;
			//#endif
						
			return isLogin
		},
	},
	methods:{
		
		scopeSession() {
			uni.login({
				provider: 'weixin',
				success: (res) => {
					this.$store.dispatch('getScopeAppSession', {
						code: res.code
					}).then(sessionRes => {
						uni.setStorage({
							key: 'scopeSession',
							data: sessionRes.data
						});
						this.scopeSessions = sessionRes.data;
					});						
				},
				fail: (res) => {
					uni.showToast({
						title: res.errMsg,
						icon: 'none'
					});
				}
			});
		},
		getUserProfile(toUrl){
			
			//优化兼容已登录则跳转相应地址
			if(this.$isLogin()){
				
				if(toUrl == ''){
					uni.redirectTo({
						url: '/pagesB/profile/profile'
					});
				}else{
					uni.redirectTo({
						url: toUrl
					});
				}
				
				return false;
			}
			
			uni.getUserProfile({
				desc : this.$t('lang.user_login_desc'),  
				lang : 'zh_CN',  
				success: (profileRes) => {
					uni.setStorage({ key: 'scopeUserInfo', data: profileRes.userInfo });
					this.scopeUserInfo = profileRes.userInfo;						
					this.scope = 'phoneNumber';
					this.getPhoneNumberShow = true;
					
					//跳转链接
					uni.setStorage({
						key: 'scopeUrl',
						data: toUrl
					});
				},
				fail: (infoRes) => {
					uni.showToast({ title: this.$t('lang.user_login_fail'), icon: 'none' });
				},
			});
		},
		getUserInfo(e) {				
			uni.getUserInfo({
				provider: 'weixin',
				success: (infoRes) => {
					console.log(infoRes);
					let session = {data:this.scopeSessions};
					this.$store.dispatch('getScopeUserInfo', {
						sessionKey: session.data.session_key,
						iv: infoRes.iv,
						encryptData: infoRes.encryptedData
					}).then(res => {
						this.scopeSessions = {
							openid: res.data.openId,
							unionid: res.data.unionId,
							session_key: session.data.session_key,
							expires_in: session.data.expires_in
						}
					})
					
					uni.setStorage({ key: 'scopeUserInfo', data: infoRes.userInfo });
					this.scopeUserInfo = infoRes.userInfo;
					this.scope = 'phoneNumber';
				},
				fail: (infoRes) => {
					uni.showToast({
						title: this.$t('lang.authorization_fail_notic'),
						icon: 'none'
					});
				}
			})
		},
		getPhoneNumber(e) {
			
			let that = this
			
			//读取本地scopeSession
			let res = {data:this.scopeSessions};
			this.getPhoneNumberShow = false;
			this.$store.dispatch('getScopePhoneNumber', {
				sessionKey: res.data.session_key,
				iv: e.detail.iv,
				encryptData: e.detail.encryptedData
			}).then(obj => {				
				if (obj.status == 'success') {
					let o = {}
					
					if (this.delta) {
						o = {
							type:"user",
							scopeSessions: this.scopeSessions,
							phoneNumber: obj.data.phoneNumber,
							userInfo: this.scopeUserInfo,
							delta: this.delta,
							parent_id: this.parent_id,
							platform: uni.getStorageSync('platform'),
						}
					} else {
						o = {
							type:"user",
							scopeSessions: this.scopeSessions,
							phoneNumber: obj.data.phoneNumber,
							userInfo: this.scopeUserInfo,
							parent_id: this.parent_id,
							platform: uni.getStorageSync('platform'),
						}
					}						
					this.$store.dispatch('getScopeAppLogin', o).then(()=>{
						setTimeout(()=>{
							
							let scopeUrl = uni.getStorageSync('scopeUrl');
							
							if(scopeUrl == ''){
								//延迟获取token
								setTimeout(() => {
									this.onChat(0, that.shopId);
								}, 800);
							}else{
								uni.redirectTo({
									url: scopeUrl
								});
							}
							
						},1000)
					});
				}
			});
		},
		
		isFollow() {
			let that = this
			if(that.$isLogin()){
				that.$store.dispatch('stVisualAddcollect',{
					ru_id: that.shopId
				})
			}else{
				uni.showModal({
					content:'请登录会员',
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
		showCategory(){
			this.bCategorySubmenu = !this.bCategorySubmenu
		},
		getCatUrl(str){
			uni.navigateTo({
				url:"/pages/shop/shopGoods/shopGoods?ru_id="+this.shopId + "&cat_id=" + str
			})
		}
	}
}
</script>

<style scoped>
.shop-menu{ position: fixed; bottom: 0; width: 100%; background: #FFFFFF; border-top: 2upx solid #e3e8ee;}
.shop-menu .items{ display: flex; }
.shop-menu .items .item{ width: 25%; flex: 1;  border-right: 2upx solid #e3e8ee; position: relative;}
.shop-menu .items .item .info{ display: flex; flex-direction: column; justify-content: center; align-items: center;padding: 15upx 0 10upx;color: #333333;}

.shop-menu .items .item:last-child{ border-right: 0; }
.shop-menu .items .item .info .iconfont{ line-height: 1; color: #333333; }
.shop-menu .items .item.item-follow .info{ background: #f92028; color: #FFFFFF; flex-direction: row; height: calc(100% - 25upx);}
.shop-menu .items .item.item-follow .info .iconfont{ color: #FFFFFF; margin-right: 10upx;}

.sub-menu{ position: absolute; bottom: 0; left: 0; width: 100%; border-left: 2upx solid #e3e8ee;transform: translateY(100%);transition: all .2s;}
.sub-menu .menu-item{ width: 100%;background: #fff;border-top: 1px solid #e3e8ee; text-align: center;}
.shop-menu .items .item.active .sub-menu{ display: block; transition: all .2s; transform: translateY(0); bottom: 106upx;}

.pop_content {
	overflow: hidden;
	width: 675rpx;
	border-radius: 12rpx;
	background-color: #fff;
}

.pop_content .pop_header{
	text-align: center;
}
.pop_content .pop_header .pop_title {
	font-size: 32rpx;
	font-weight: 700;
	line-height: 90rpx;
	color: #282828;
}

.pop_phone_number{
	position: relative;
	width: 420rpx;
	height: 400rpx;
}
.pop_phone_number .pop_header{
	width: 100%;
	border-bottom: 1px solid #ECECEC;
}
.pop_phone_number .logo{
	padding-top:20rpx;
	width: 100rpx;
	height: 100rpx;
	margin: 0 auto;
}
.pop_phone_number .logo .img{
	border-radius: 50%;
}
.pop_phone_number .pop_phone_content{
	margin: 0 auto;
	text-align: center;
	line-height: 45px;	
}
.pop_phone_number .btn{
	margin: 0 auto;
	width: 70%;
}
</style>
