<template>
	<view class="shop-detail" v-if="shopDetail">
		<dsc-shop-header :shopInfo="shopInfo" :shopIndex="shopIndex" :shopScore="shopScore" :shopCollect="shopCollect" @update="updateInfo"></dsc-shop-header>
		<view class="shopping-info-nums">
			<view class="item" @click="$outerHref('/pages/shop/shopGoods/shopGoods?ru_id='+shopDetail.ru_id + '&type=goods_id','app')">
				<text class="tit">{{ shopDetail.count_goods }}</text>
				<text class="text">{{$t('lang.all_goods')}}</text>
			</view>
			<view class="item" @click="$outerHref('/pages/shop/shopGoods/shopGoods?ru_id='+shopDetail.ru_id + '&type=store_new','app')">
				<text class="tit">{{ shopDetail.count_goods_new }}</text>
				<text class="text">{{$t('lang.new')}}</text>
			</view>
			<view class="item" @click="$outerHref('/pages/shop/shopGoods/shopGoods?ru_id='+shopDetail.ru_id + '&type=is_promote','app')">
				<text class="tit">{{ shopDetail.count_goods_promote }}</text>
				<text class="text">{{$t('lang.promotion')}}</text>
			</view>
		</view>
		<view class="uni-card uni-card-not uni-card-contact">
			<view class="uni-list uni-collapse-left">
				<!-- #ifndef MP-WEIXIN -->
				<view class="uni-list-cell" @click="onChat(0,shopDetail.ru_id)">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.online_service')}}</text>
						<view class="value"><icon class="iconfont icon-kefu"></icon></view>
					</view>
				</view>
				<!-- #endif -->
				
				<!-- #ifdef MP-WEIXIN -->
				<block v-if="wxappChat > 0">
					<button open-type="contact" type="default" size="5" session-from="weapp" hover-class="none" class="uni-list-cell kefu-cantact">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.online_service')}}</text>
							<view class="value"><icon class="iconfont icon-kefu"></icon></view>
						</view>
					</button>
				</block>
				<block v-else>
					<block v-if="isLogin===true">
						<view class="uni-list-cell" open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile">
							<view class="uni-list-cell-navigate">
								<text class="title">{{$t('lang.online_service')}}</text>
								<view class="value"><icon class="iconfont icon-kefu"></icon></view>
							</view>
						</view>
						<view class="uni-list-cell" @click="getUserProfile('')" v-else>
							<view class="uni-list-cell-navigate">
								<text class="title">{{$t('lang.online_service')}}</text>
								<view class="value"><icon class="iconfont icon-kefu"></icon></view>
							</view>
						</view>
					</block>
					<block v-else>
						<view class="uni-list-cell" @click="onChat(0,shopDetail.ru_id)">
							<view class="uni-list-cell-navigate">
								<text class="title">{{$t('lang.online_service')}}</text>
								<view class="value"><icon class="iconfont icon-kefu"></icon></view>
							</view>
						</view>
					</block>
				</block>
				<!-- #endif -->
				<view class="uni-list-cell" @click="handleCode">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.shop_qr_code')}}</text>
						<view class="value"><icon class="iconfont icon-904anquansaoma"></icon></view>
					</view>
				</view>
				<view class="uni-list-cell" @click="makePhone(shopDetail.kf_tel)" v-if="shopDetail.is_ru_tel == 1">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.shop_tel')}}</text>
						<view class="value"><icon class="iconfont icon-a"></icon></view>
					</view>
				</view>
				<view class="uni-list-cell" @click="telShow=true" v-else>
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.shop_tel')}}</text>
						<view class="value"><icon class="iconfont icon-a"></icon></view>
					</view>
				</view>
			</view>
		</view>
		<view class="uni-card uni-card-not uni-card-address">
			<view class="uni-list uni-collapse-left">
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.company_profile')}}</text>
					</view>
				</view>
				<view class="uni-list-cell" @click="seeLicense">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.certificate_info')}}</text>
						<view class="value"><icon class="iconfont icon-yanzheng" style="color: #21ba45;"></icon></view>
					</view>
				</view>
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.label_corporate_name')}}{{ shopDetail.shop_name }}</text>
					</view>
				</view>
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.label_region')}}{{ shopDetail.shop_address }}</text>
					</view>
				</view>
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.label_main_brand')}}{{ shopDetail.shoprz_brand_name }}</text>
					</view>
				</view>
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.label_seller_Grade')}}<image :src="shopDetail.grade_img" mode="widthFix" v-if="shopDetail.grade_img"></image> {{ shopDetail.grade_name }}</text>
					</view>
				</view>
			</view>
		</view>
		
		<uni-popup :show="show" type="middle" @hidePopup="handelClose('code')">
			<view class="code-popup">
				<view class="tit">{{ shopDetail.shop_name }}</view>
				<view class="qrode"><image :src="shopDetail.shop_qrcode" mode="widthFix" class="img"></image></view>
				<text>{{$t('lang.rm_wd_info_zz')}}</text>
			</view>
		</uni-popup>
		
		<uni-popup :show="telShow" type="middle" @hidePopup="handelClose('tel')">
			<view class="tel-popup">
				<view class="tip">{{$t('lang.is_tel_tip_1')}}</view>
				<view class="desc">{{$t('lang.is_tel_tip_2')}}</view>
				<view class="btn" @click="$outerHref('/pagesA/drp/register/register','app')">{{$t('lang.is_tel_tip_3')}}</view>
				
				<view class="close" @click="handelClose('tel')">
					<image src="../../../static/close.png" mode="widthFix" class="img"></image>
				</view>
			</view>
		</uni-popup>
		
		<uni-popup :show="LicenseShow" type="right" @hidePopup="handelClose('license')">
			<scroll-view class="scroll-view license-popup" style="overflow: hidden; height:1350upx" scroll-y>
				<view class="tit">{{$t('lang.rm_prompt_info')}}<text class="iconfont icon-close1"></text></view>
				<view class="license-content" v-if="basic_info">
					<view>{{$t('lang.label_companyName')}}{{ basic_info.company_name }}</view>
					<view>{{$t('lang.label_business_license_id')}}{{ basic_info.business_license_id }}</view>
					<view>{{$t('lang.label_legal_person')}}{{ basic_info.legal_person }}</view>
					<view>{{$t('lang.label_license_comp_adress')}}{{ basic_info.license_comp_adress }}</view>
					<view>{{$t('lang.label_registered_capital')}}{{ basic_info.registered_capital }}</view>
					<view>{{$t('lang.label_business_term')}}{{ basic_info.business_term }}</view>
					<view>{{$t('lang.label_busines_scope')}}{{ basic_info.busines_scope }}</view>
					<view>{{$t('lang.label_company_located')}}{{ basic_info.company_adress }}</view>
					<view>{{$t('lang.label_shop_name')}}{{ shopDetail.shop_name }}</view>
					<view v-if="basic_info.license_fileImg">{{$t('lang.label_license_img')}}</view>
					<view v-if="basic_info.license_fileImg">
						<image :src="basic_info.license_fileImg" @click="previewImg(basic_info.license_fileImg)" class="img" mode="widthFix"></image>
					</view>
					<view class="help">{{$t('lang.rm_prompt_help')}}</view>
				</view>
			</scroll-view>
		</uni-popup>
		
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
	
	import dscShopHeader from '@/components/dsc-shop-header.vue';
	import uniPopup from '@/components/uni-popup.vue';
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import universal from '@/common/mixins/universal.js';
	
	export default {
		mixins:[universal],
		data() {
			return {
				scopeUserInfo: {},
				scope: 'userInfo',
				scopeSessions: {},
				canIUseProfile:false,
				getPhoneNumberShow:false,
				isLoginData:false,
				ru_id: 0,
				show: false,
				shopScore:true,
				shopCollect:true,
				shopIndex: 0,
				LicenseShow:false,
				//微信小程序客服
				wxappChat:uni.getStorageSync("configData").wxapp_chat || 0,
				telShow:false
			}
		},
		components:{
			uniPopup,
			uniIcons,
			dscShopHeader
		},
		computed:{
			...mapState({
				shopDetail: state => state.shop.shopDetail
			}),
			basic_info(){
				return this.shopDetail.basic_info
			},
			is_collect_shop:{
				get(){
					return this.shopDetail.is_collect_shop
				},
				set(val){
					this.shopDetail.is_collect_shop = val
				}
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
			count_gaze:{
				get(){
					return this.shopDetail.count_gaze
				},
				set(val){
					this.shopDetail.count_gaze = val
				}
			},
			shopInfo(){
				let arr = []

				arr[this.shopIndex] = {
					shopName:this.shopDetail.shop_name,
					logo:this.shopDetail.logo_thumb,
					ru_id:this.shopDetail.ru_id,
					commentdelivery:this.shopDetail.commentdelivery,
					commentdelivery_font:this.shopDetail.commentdelivery_font,
					commentrank:this.shopDetail.commentrank,
					commentrank_font:this.shopDetail.commentrank_font,
					commentserver:this.shopDetail.commentserver,
					commentserver_font:this.shopDetail.commentserver_font,
					count_gaze:this.count_gaze,
					is_collect_shop:this.is_collect_shop,
				}

				return arr
			},
		},
		methods: {
			
			previewImg(imgs) {
				let arr = []
				if (imgs){
					if(typeof imgs == 'string') arr.push(imgs);
					
					uni.previewImage({
						urls: arr.length > 0 ? arr : imgs,
					})
				}
			},
			
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
										this.onChat(0, that.ru_id);
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
			
			updateInfo(obj){
				this.is_collect_shop = obj.is_collect_shop
				this.count_gaze = this.is_collect_shop == 1 ? this.count_gaze + 1 : this.count_gaze - 1 
			},
			handleCode(){
				this.show = true;
			},
			makePhone(val){
				if(val){
					uni.makePhoneCall({
					    phoneNumber: val
					});
				}else{
					uni.showToast({ title:this.$t('lang.shop_not_set_kefu_tel'), icon:'none' });
				}
			},
			handelClose(val){
				if(val == 'code'){
					this.show = false
				}else if(val == 'license'){
					this.LicenseShow = false
				}else if(val == 'tel'){
					this.telShow = false
				}
			},
			seeLicense(){
			  this.LicenseShow = true
			},
		},
		onLoad(e){
			this.ru_id = e.id ? e.id : 0;
			
			let platform = 'H5';
			
			// #ifdef MP-WEIXIN
			platform = 'MP-WEIXIN'
			// #endif
	
			this.$store.dispatch('setShopDetail',{
				ru_id:this.ru_id,
				platform:platform
			})
		},
	}
</script>

<style>
.shop-detail{ height: 100vh;}
.shopping-info-nums{ display: flex; flex-direction: row; align-items: center; justify-content:center; background: #FFFFFF; border-top: 2upx solid #f6f6f9; padding: 10upx 0;}
.shopping-info-nums .item{ flex: 1; width: 33.3%; display: flex; flex-direction: column; justify-content: center; align-items: center;}
.shopping-info-nums .item .tit{ font-size: 35upx; font-weight: 600;}
.shopping-info-nums .item .text{ font-size: 25upx; color: #999999;}

.uni-card .uni-list-cell .title{ font-size: 30upx; color: #333333; }
.uni-card .uni-list-cell .value .iconfont{ font-size: 36upx;}
.uni-card .uni-list-cell .title image{ width: 40upx; height: 40upx;}

.shop-detail .uni-popup-middle{ width: 74%; padding: 0; height: auto; border-radius: 0; background: #f7f7f7; }
.shop-detail .code-popup{ width: 100%;}
.shop-detail .code-popup .tit{ padding: 20upx; color: #444444; border-bottom: 2upx solid #eee; text-align: center; font-size: 30upx; }
.shop-detail .code-popup .qrode{ width: 450upx; height: 450upx; padding: 20upx; background: #FFFFFF; margin: 20upx auto;}
.shop-detail .code-popup text{ color: #777777; text-align: center; padding: 15upx; border-top: 2upx solid #eee; display: block;}

.shop-detail .license-popup .tit{ padding: 20rpx; font-size: 30upx; display: flex; justify-content: space-between; align-items: center;}
.shop-detail .license-popup .tit .iconfont{ margin-right: 20rpx; font-size: 14px; }
.shop-detail .license-popup .license-content{ padding: 0 20rpx; }
.shop-detail .license-popup .license-content .help{ margin-top: 20upx; font-weight: 600;}
.shop-detail .license-popup .uni-icon{ margin: 50upx auto; display: flex; justify-content: center; align-items: center; }

.shop-detail /deep/ .uni-popup{ border-radius: 10rpx;}
.shop-detail .tel-popup{ padding: 30rpx 0 50rpx; text-align: center;}
.shop-detail .tip{ font-size: 32rpx; line-height: 60rpx;}
.shop-detail .desc{ font-size: 30rpx; margin-bottom: 20rpx;line-height: 60rpx;}
.shop-detail .btn{ color: #e93b3d; font-size: 30rpx;}

.shop-detail .uni-popup-middle .close{
	width: 20upx;
	height: 20upx;
	line-height: 20upx;
	position: absolute;
	top: -20upx;
	right: -15upx;
	border-radius: 50%;
	background: rgba(0,0,0,.3);
	padding: 10rpx;
}

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
