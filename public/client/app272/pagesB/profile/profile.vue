<template>
	<view class="profile">
		<view class="uni-card uni-card-not">
			<view class="s-user-top">
				<view class="user-bg-box-1"><image :src="imagePath.user_profile_1" class="img"></image></view>
				<view class="user-bg2-box-1"><image :src="imagePath.user_profile_2" class="img"></image></view>
				<view class="user_profile_box">
					<view class="user-img" @click="chooseImage"><image :src="userInfo.avatar" class="img" v-if="userInfo.avatar"></image></view>
					<view class="profile-index-top">
						<text v-if="userInfo.name != ''">{{ userInfo.name }}</text>
						<text v-else>{{ userInfo.username }}</text>
						<view class="username">{{$t('lang.username')}} {{ userInfo.username }}</view>
					</view>
				</view>
			</view>
			<view class="uni-list">
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="isShow('name')">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<text class="title">{{$t('lang.nickname')}}</text>
						<view class="value">{{userInfo.name}}</view>
					</view>
				</view>
			</view>
			<view class="uni-list">
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="isShow('isSex')">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<text class="title">{{$t('lang.sex')}}</text>
						<view class="value">{{isSex}}</view>
					</view>
				</view>
			</view>
			<view class="uni-list">
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="isShow('birthday')">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<text class="title">{{$t('lang.birthday')}}</text>
						<view class="value">{{userInfo.birthday}}</view>
					</view>
				</view>
			</view>
			<view class="uni-list">
				<navigator url="../address/address" hover-class="none">
				<view class="uni-list-cell" hover-class="uni-list-cell-hover">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<text class="title">{{$t('lang.address')}}</text>
					</view>
				</view>
				</navigator>
			</view>
		</view>
		<view class="uni-card uni-card-not">
			<view class="uni-list" @click="$outerHref('/pagesB/realname/realname',$isLogin(),userInfo.mobile)">
				<view class="uni-list-cell" hover-class="uni-list-cell-hover">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<text class="title">{{$t('lang.real_name')}}</text>
					</view>
				</view>
			</view>
		</view>
		<view class="uni-card uni-card-not">
			<view class="uni-list">
				<navigator url="../accountsafe/accountsafe" hover-class="none">
				<view class="uni-list-cell" hover-class="uni-list-cell-hover">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<text class="title">{{$t('lang.account_security')}}</text>
					</view>
				</view>
				</navigator>
			</view>
		</view>
		<view class="uni-card uni-card-not">
			<view class="uni-list">
				<navigator url="../help/help" hover-class="none">
				<view class="uni-list-cell" hover-class="uni-list-cell-hover">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<text class="title">{{$t('lang.use_help')}}</text>
					</view>
				</view>
				</navigator>
			</view>
			<!-- #ifdef APP-PLUS -->
			<view class="uni-list" v-if="versionData">
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="updateAppStore">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<text class="title">{{$t('lang.ban_new')}}</text>
						<view class="value">{{$t('lang.zui_new')}}{{ versionData.content.version_id }}</view>
					</view>
				</view>
			</view>
			<!-- #endif -->
		</view>
		<view class="uni-card uni-card-not">
			<view class="uni-list">
				<navigator url="../logout/logout" hover-class="none">
				<view class="uni-list-cell" hover-class="uni-list-cell-hover">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<text class="title">用户注销</text>
					</view>
				</view>
				</navigator>
			</view>
		</view>
		<!-- #ifdef APP-PLUS -->
		<view class="uni-card uni-card-not">
			<view class="uni-list">
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="onClearCache">
					<view class="uni-list-cell-navigate uni-navigate-badge">
						<text class="title">{{$t('lang.clear_cache')}}</text>
					</view>
				</view>
			</view>
		</view>
		<!-- #endif -->
		<view class="profile-btn">
			<button type="warn" plain="true" @click="handelLogout">{{$t('lang.drop_out')}}</button>
		</view>
		<view class="copyright">
			<view class="onelist-hidden">{{configData.copyright_text_mobile ? configData.copyright_text_mobile : 'Copyright©2015-2021'}}</view>
			<view>{{wgtinfo.name}} {{$t('lang.ban_quan')}}</view>
			<!-- #ifdef APP-PLUS -->
			<view>{{$t('lang.ban')}}{{wgtinfo.version}}</view>
			<!-- #endif -->
			<text class="link" @click="linkHref">{{$t('lang.ban_yinsi')}}</text>
		</view>

		<!--popup-->
		<uni-popup :show="show" type="right" v-on:hidePopup="handelClose()">
			<view class="my-box">
				<view class="uni-card uni-card-not">
					<view class="uni-list" v-if="typeState == 'name'">
						<view class="uni-list-cell uni-list-cell-last">
							<view class="uni-list-cell-navigate">
								<text class="title">{{$t('lang.nickname')}}</text>
								<view class="value uni-cell-input"><input v-model="nickName" :placeholder="$t('lang.nickname')"></view>
							</view>
						</view>
						<view class="uni-list-cell-tip">{{$t('lang.ban_yinsi_1')}}</view>
					</view>
					<view class="uni-list" v-else-if="typeState == 'isSex'">
						<view class="user-sex">
							<view class="left" :class="{'active':isSexNum == 1}" @click="handleSex(1)">
								<view class="iconfont icon-nan"></view>
								<text>{{$t('lang.male')}}</text>
							</view>
							<view class="right" :class="{'active':isSexNum == 2}" @click="handleSex(2)">
								<view class="iconfont icon-nv"></view>
								<text>{{$t('lang.woman')}}</text>
							</view>
						</view>
					</view>
					<view class="uni-list" v-else>
						<view class="uni-list-cell">
							<view class="uni-list-cell-navigate">
								<text class="title">{{$t('lang.birthday')}}</text>
								<view class="value uni-cell-input">
									<picker mode="date" :value="birthday" :start="startDate" :end="endDate" @change="bindDateChange" class="picker">
										<view class="uni-input">{{birthday}}</view>
									</picker>
								</view>
							</view>
						</view>
					</view>
				</view>
				
				<view class="btn-bar">
					<view class="btn" :class="[nicknameDisabled > 0 ? 'btn-disabled' : 'btn-red']" @click="updateInfo">{{$t('lang.confirm_on')}}</view>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniPopup from '@/components/uni-popup.vue';
	import { pathToBase64, base64ToPath } from '@/common/image-tools/index.js'
	import { compressImage } from '@/common/compressImage.js'

	export default {
		components: {
			uniPopup,
		},
		data() {
			return {
				show:false,
				logoutState:true,
				typeState:'',
				imageSrc:'',
				wgtinfo:{},
				timer:'',
				nickName:'',
				nicknameDisabled:0,
				versionData:'',
				privacy_article_id:'',
				configData: uni.getStorageSync('configData')
			};
		},
		computed:{
			...mapState({
				userInfo: state => state.user.userInfo
			}),
			isSexNum:{
				get(){
					return this.userInfo.sex;
				},
				set(val){
					this.$store.dispatch('userUpdateText',{
						type:'sex',
						val:val
					});
				}
			},
			nick_name:{
				get(){
					return this.userInfo.name;
				},
				set(val){
					this.nickName = val
				}
			},
			birthday:{
				get(){
					return this.userInfo.birthday;
				},
				set(val){
					this.$store.dispatch('userUpdateText',{
						type:'birthday',
						val:val
					});
				}
			},
			isSex(){
				let sexArr = [this.$t('lang.secrecy'), this.$t('lang.male'), this.$t('lang.woman')];
				return sexArr[this.isSexNum];
			},
			startDate() {
				return this.getDate('start');
			},
			endDate() {
				return this.getDate('end');
			}
		},
		methods:{
			getStringLen(str){
				let i,len,code;
				if(str == null || str == '') return 0;
				len = str.length;

				for(i = 0; i < len; i++){
					code = str.charCodeAt(i);
					if(code > 255){
						len++;
					}
				}
				return len;
			},
			chooseImage(){
				let that = this
				uni.chooseImage({
					count:1,
					sizeType: ['original', 'compressed'],
					success:(res)=>{
						that.imageSrc = res.tempFilePaths[0];
						// #ifdef APP-PLUS
						that.uploadImage();
						// #endif

						// #ifdef MP-WEIXIN
						let size = res.tempFiles[0].size; //上传图片大小
						let maxSize = 1024 * 1024 * 2; //最大可上传2mb
						if(size > maxSize){
							uni.compressImage({
								src:res.tempFilePaths[0],
								quality:10,
								success:(result) => {
									that.imageSrc = result.tempFilePath;
									that.uploadImage();
								},
								fail:(result) => {
									console.log(result)
								}
							})
						}else{
							that.uploadImage();
						}
						// #endif
					}
				})
			},
			async uploadImage(){
				let that = this;
				uni.showLoading({ mask:true, title:this.$t('lang.shang_chu')});

				//app压缩图片
				// #ifdef APP-PLUS
				that.imageSrc = await compressImage(that.imageSrc);
				// #endif

				pathToBase64(that.imageSrc).then(base64 => {
					this.$store.dispatch('setMaterial',{
						file:{content:base64},
						type:'touxian'
					}).then(data=>{
						if(data.status == 'success'){
							uni.hideLoading();
							this.$store.dispatch('userUpdateAvatar',{
								pic:data.data[0]
							});
						}
					})
				}).catch(error => {
					console.error(error,5);
				});
			},
			isShow(val){
				this.show = true;
				if(val == 'name'){
					this.nickName = this.userInfo.name
				}

				this.typeState = val;
			},
			handelClose(){
				this.show = false
				this.nicknameDisabled = 0;
				this.nickName = this.userInfo.name
			},
			handleSex(val){
				this.isSexNum = val
			},
			bindDateChange: function(e){
				this.birthday = e.target.value
			},
			getDate(type) {
				const date = new Date();

				let year = date.getFullYear();
				let month = date.getMonth() + 1;
				let day = date.getDate();

				if (type === 'start') {
					year = year - 60;
				} else if (type === 'end') {
					year = year + 2;
				}
				month = month > 9 ? month : '0' + month;;
				day = day > 9 ? day : '0' + day;
				return `${year}-${month}-${day}`;
			},
			updateInfo(){
				if(this.nicknameDisabled == 1){
					uni.showToast({
						title:this.$t('lang.ban_yinsi_2'),
						icon:'none'
					})
					return
				}else if(this.nicknameDisabled == 2){
					uni.showToast({
						title:this.$t('lang.up_to_20_characters_can_be_entered'),
						icon:'none'
					})
					return
				}

				let o = {
					sex:this.isSexNum,
					name:this.nickName ? this.nickName : this.nick_name,
					birthday:this.birthday
				}

				this.$store.dispatch('updateProfile',o);
				this.show = false
			},
			handelLogout(){
				let that = this
				uni.showModal({
					content:this.$t('lang.user_logout'),
					success:function(res){
						if(res.confirm){
							that.$store.dispatch('userLogout');
							
							//清除入驻信息
							uni.removeStorageSync("merchantInfo");
							uni.removeStorageSync("merchantsData");
						}
					}
				})
			},
			onClearCache(){
				uni.showModal({
					content: this.$t('lang.is_clear_cache'),
					success: res => {
						if (res.confirm) {
							uni.clearStorageSync();
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
							uni.setStorageSync('platform',platform);
							plus.runtime.getProperty(plus.runtime.appid,(wgtinfo)=>{
								uni.setStorageSync('wgtinfo',JSON.stringify(wgtinfo))
							})
							uni.showLoading({
							    title: '清除缓存中。。。'
							});
							setTimeout(function(){
								uni.hideLoading();
								self.$outerHref('/pages/index/index','app');
							}, 1000);
						}
					}
				});
			},
			async appUpdate(){
				const {data,status} = await this.$store.dispatch('setAppUpdate',{
					appid:this.updateAppid
				});

				if(status == 'success'){
					this.versionData = data
				}
			},
			updateAppStore(){
				let wgtinfo = JSON.parse(uni.getStorageSync('wgtinfo'));
				if(this.versionData.content.version_id !== wgtinfo.version){
					plus.runtime.openURL(this.versionData.content.download_url)
				}else{
					uni.showToast({ title:this.$t('lang.ban_yinsi_3'), icon:'none' });
				}
			},
			shopConfig(){
				uni.request({
					url:this.websiteUrl + '/api/shop/config',
					method:'GET',
					data:{},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: ({data:{data}}) => {
						if(data.privacy.article_id){
							this.privacy_article_id = data.privacy.article_id
						}
					}
				});
			},
			linkHref(){
				if(this.privacy_article_id){
					uni.navigateTo({
						url:'/pagesC/article/detail/detail?id=' + this.privacy_article_id + '&show=false'
					})
				}else{
					uni.showToast({ title:this.$t('lang.ban_yinsi_4'), icon:"none" })
				}
			}
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/profile/profile'
			}
		},
		onLoad(){
			this.wgtinfo = JSON.parse(uni.getStorageSync('wgtinfo'))

			// #ifdef APP-PLUS
			this.appUpdate();
			// #endif

			this.shopConfig();
		},
		onShow(){
			this.$store.dispatch('userProfile');
		},
		watch:{
			nickName(){
				let length = this.getStringLen(this.nickName);
				if(length < 2){
					this.nicknameDisabled = 1;
				}else if(length > 20){
					this.nicknameDisabled = 2;
				}else{
					this.nicknameDisabled = 0;
				}
			}
		}
	}
</script>

<style>
.s-user-top{ padding: 22upx 20upx; position: relative;}
.s-user-top .user-bg-box-1{ width: 90upx; height: 32upx; position: absolute; right: 0; top: 20upx;}
.s-user-top .user-bg2-box-1{ width: 91upx; height: 36upx; position: absolute; bottom: 0; left: 20upx;}
.s-user-top .user_profile_box{ display: flex; flex-direction: row;}
.s-user-top .user_profile_box .user-img{ width: 108upx; height: 108upx; border-radius: 50%; border:2px solid #EEEEEE;}
.s-user-top .user_profile_box .user-img .img{ border-radius: 50%; }
.s-user-top .user_profile_box .profile-index-top{ flex: 1; margin-left: 30upx;}
.s-user-top .user_profile_box .profile-index-top text{ font-size: 32upx; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;}
.s-user-top .user_profile_box .profile-index-top .username{ font-size: 25upx; color: #777777;}

.my-box .uni-card{ margin: 0;}
.my-box .uni-list-cell-navigate{ justify-content: flex-start;}
.my-box .uni-list-cell-navigate .title{ padding: 20upx 0; font-size: 32upx; margin-right: 20upx; color: #333333;}
.my-box .uni-list-cell-navigate .value{ font-size:28upx; color: #666;}
.my-box .btn-bar{ padding: 0 30upx; margin-top: 20upx;}

.user-sex{ display: flex; flex-direction: row; padding: 50upx 30upx; justify-content: center; text-align: center;}
.user-sex .left,
.user-sex .right{ flex: 1 1 0%; color: #b2b2b2;}
.user-sex .iconfont{ font-size: 130upx; line-height: normal;}
.user-sex .left.active{ color: #3fc6ff;}
.user-sex .right.active{ color: #f36ab7;}

.picker{ width: 100%;}

.copyright{ text-align: center; margin-top: 50px;}
.copyright view{ font-size: 25upx; color: #999999;}
.copyright .link{ color: #4b89dc; font-size: 25upx;}

.profile-btn{ margin: 40upx 30upx 0;}
.profile-btn button{ font-size: 32upx;}

.uni-popup-right{ width: 80%;}
</style>
