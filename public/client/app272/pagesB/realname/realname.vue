<template>
	<view>
		<form @submit="formSubmit">
			<view class="uni-card uni-card-not">
				<view class="uni-list">
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right" hover-class="uni-list-cell-hover" @click="uploadCard">
						<view class="uni-list-cell-navigate uni-navigate-right">
							<view class="title">{{$t('lang.upload_photoid')}}</view>
							<view class="value">
								<view v-if="!isCardImg">{{$t('lang.haven_uploaded')}}</view>
								<view v-else>{{$t('lang.have_already_uploaded')}}</view>
							</view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.truename')}}</text>
							<view class="value"><input :placeholder="$t('lang.fill_in_real_name')" name="real_name" v-model="real_name" :disabled="isDisabled"></view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.id_number')}}</text>
							<view class="value"><input :placeholder="$t('lang.fill_in_id_number')" name="self_num" v-model="self_num" :disabled="isDisabled"></view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.bank_name')}}</text>
							<view class="value"><input :placeholder="$t('lang.fill_in_bank_name')" name="bank_name" v-model="bank_name" :disabled="isDisabled"></view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.credit_card_numbers')}}</text>
							<view class="value"><input :placeholder="$t('lang.fill_in_card_number')" name="bank_card" v-model="bank_card" :disabled="isDisabled"></view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right" v-if="real_type">
						<view class="uni-list-cell-navigate">
							<view class="title">{{$t('lang.certification_time')}}</view>
							<view class="value">{{add_time}}</view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right" v-if="review_status !== ''">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.audit_status')}}</text>
							<view class="value">
								<text class="uni-red" v-if="review_status == 0">{{$t('lang.audit_status_01')}}</text>
								<text class="uni-red" v-else-if="review_status == 1">{{$t('lang.audit_status_02')}}</text>
								<text class="uni-red" v-else="review_status == 2">{{$t('lang.audit_status_03')}}</text>
							</view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right" v-if="review_status == 2">
						<view class="uni-list-cell-navigate" style="align-items: flex-start;">
							<text class="title">{{$t('lang.audit_review_content')}}</text>
							<view class="value">
								<text class="uni-red">{{ review_content }}</text>
							</view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.label_phone_number')}}</text>
							<view class="value"><input :placeholder="$t('lang.phone_card_1')" name="bank_mobile" v-model="bank_mobile" :disabled="isDisabled"></view>
						</view>
					</view>
					<block v-if="button_edit_type == false">
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.pic_code')}}</text>
							<view class="value value-items">
								<input :placeholder="$t('lang.captcha_img')" name="imgverifyValue" v-model="imgverifyValue">
								<view class="code-box" @click="clickCaptcha"><image :src="captcha" class="img"></image></view>
							</view>
						</view>
					</view>
					<view class="uni-list-cell uni-list-cell-title uni-list-cell-right">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.label_sms_code')}}</text>
							<view class="value value-items">
								<input :placeholder="$t('lang.get_sms_code')" name="sms" v-model="sms">
								<button size="mini" type="warn" @click="sendVerifyCode" v-if="button_type">{{$t('lang.get_code')}}</button>
								<button size="mini" type="default" v-else>{{ button_text }}</button>
							</view>
						</view>
					</view>
					</block>
				</view>
			</view>
			<view class="btn-bar">
				<block v-if="real_id > 0">
					<button class="btn btn-red" type="primary" @click="editRealname" v-if="button_edit_type">{{$t('lang.edit_certification_info')}}</button>
					<button class="btn btn-red" formType="submit" type="primary" v-else>{{ button_edit }}</button>
				</block>
				<block v-else>
					<button class="btn btn-red" formType="submit" type="primary">{{$t('lang.consent_certification_confirm')}}</button>
				</block>
			</view>
		</form>
		<uni-popup :show="cardShow" type="right" v-on:hidePopup="handelClose('card')">
			<view class="card-main-warp">
				<view class="containTop">
					<view class="title">{{$t('lang.fill_in_upload_id_number')}}</view>
					<view class="photoBox">
						<view class="photo">
							<view class="card cardA" @tap="chooseImage('front_of_id_card')">
								<view class="preShow" v-if="front_of_id_card && newChooseImage.front">
									<image :src="front_of_id_card" class="img"></image>
								</view>
								<image :src="imagePath.cardA" class="img" v-else></image>
							</view>
							<view class="text">{{$t('lang.fill_in_upload_portrait')}}</view>
						</view>
						<view class="photo">
							<view class="card cardB" @tap="chooseImage('reverse_of_id_card')">
								<view class="preShow" v-if="reverse_of_id_card && newChooseImage.reverse">
									<image :src="reverse_of_id_card" class="img"></image>
								</view>
								<image :src="imagePath.cardB" class="img" v-else></image>
							</view>
							<view class="text">{{$t('lang.fill_in_upload_national_emblem')}}</view>
						</view>
					</view>
				</view>
				<view class="containBottom">
					<view class="title" @click="prompt">
						<view class="tit">{{$t('lang.fill_in_shoot_original')}}：</view>
						<view class="more"><text class="iconfont icon-sigh"></text>{{$t('lang.fill_in_upload_photoid')}}</view>
					</view>
					<view class="tip-image"><image :src="imagePath.imageFalseSrc" class="img"></image></view>
					<view class="btn-bar btn-bar-radius">
						<block v-if="newChooseImage.front && newChooseImage.front">
							<view class="btn btn-red" @click="handelClose('card')">{{$t('lang.confirm_upload')}}</view>
						</block>
						<block v-else>
							<view class="btn btn-disabled">{{$t('lang.confirm_upload')}}</view>
						</block>
					</view>
				</view>
			</view>
		</uni-popup>

		<uni-popup :show="promptShow" type="bottom" v-on:hidePopup="handelClose('prompt')">
			<view class="title">
				<view class="txt">{{$t('lang.fill_in_upload_photoid')}}</view>
				<uni-icons type="closeempty" size="36" color="#999999" @click="handelClose('prompt')"></uni-icons>
			</view>
			<view class="rule-layer">
				<view class="p">{{$t('lang.upload_card_propmt_1')}}</view>
				<view class="p">{{$t('lang.upload_card_propmt_2')}}</view>
				<view class="p">{{$t('lang.upload_card_propmt_3')}}</view>
				<view class="p">{{$t('lang.upload_card_propmt_4')}}</view>
				<view class="p">{{$t('lang.upload_card_propmt_5')}}</view>
				<view class="p">{{$t('lang.upload_card_propmt_6')}}</view>
			</view>
		</uni-popup>

		<dsc-common-nav></dsc-common-nav>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniPopup from '@/components/uni-popup.vue';
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';

	import { pathToBase64, base64ToPath } from '@/common/image-tools/index.js'
	import { compressImage } from '@/common/compressImage.js'

	var graceChecker = require("@/common/graceChecker.js");

	export default {
		components: {
			uniPopup,
			uniIcons,
			dscCommonNav
		},
		data() {
			return {
				cardShow:false,
				promptShow:false,
				real_type: false,
                real_id: 0,
                real_name: '',
                self_num: '',
                bank_name: '',
                bank_card: '',
				review_content:'',
                front_of_id_card: 0,
                reverse_of_id_card: 0,
                add_time: '',
                review_status: '',
                bank_mobile: '',
                imgverifyValue: '',
                sms: '',
                button_text: this.$t('lang.send_again_60'),
                button_type: true,
                button_edit_type : false,
                button_edit: this.$t('lang.subimt'),
                isDisabled:true,
				newChooseImage:{
					front:false,
					reverse:false
				},
				imageSrc:''
			};
		},
		computed: {
			...mapState({
				captcha: state => state.common.imgVerify.captcha,
				client: state => state.common.imgVerify.client,
			}),
            isCardImg() {
                return this.front_of_id_card != 0 && this.reverse_of_id_card != 0 ? true : false
            },
        },
		methods: {
			realnameLoad(val){
				this.newChooseImage.front = false;
				this.newChooseImage.reverse = false;
				uni.request({
					url:this.websiteUrl + '/api/realname',
					method:'GET',
					data:{},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						let data = res.data.data
						if (data != '') {
						    this.real_type = true
						    this.real_id = data.real_id
						    this.real_name = data.real_name
						    this.self_num = data.self_num
						    this.bank_name = data.bank_name
						    this.bank_card = data.bank_card
						    this.bank_mobile = data.bank_mobile
						    this.front_of_id_card = data.front_of_id_card
						    this.reverse_of_id_card = data.reverse_of_id_card
						    this.review_status = data.review_status
						    this.add_time = data.add_time
							this.review_content = data.review_content
						}

						if(data == '' && val != 'add'){
						    this.isDisabled = false
						    this.button_edit_type = false
						}else{
						    this.isDisabled = true
						    this.button_edit_type = true
						}
					}
				})

                this.$store.dispatch('setImgVerify')
            },
			formSubmit(e){
				var rule = [
					{name:"real_name", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.truename_not_null')},
					{name:"self_num", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.id_number_not_null')},
					{name:"bank_name", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.bank_name_not_null')},
					{name:"bank_card", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.card_number_not_null')},
					{name:"imgverifyValue", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.captcha_img_not_null')},
					{name:"sms", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.get_sms_code_notic')},
					{name:"bank_mobile", checkType : "phoneno", checkRule:"",  errorMsg:this.$t('lang.mobile_not_null')},
				];

				if(!this.isCardImg){
					uni.showToast({ title: this.$t('lang.please_upload_photoid'), icon: "none" });
					return
				}

				//进行表单检查
				var formData = e.detail.value;
				var checkRes = graceChecker.check(formData, rule);

				if(checkRes){
					let o = {
						real_id: this.real_id,
						real_name: this.real_name,
						self_num: this.self_num,
						bank_name: this.bank_name,
						bank_card: this.bank_card,
						bank_mobile: this.bank_mobile,
						front_of_id_card: this.front_of_id_card,
						reverse_of_id_card: this.reverse_of_id_card
					}
					this.$store.dispatch('setSmsVerify', {
                        client: this.client,
                        code: this.sms,
                        mobile:this.bank_mobile
                    }).then(res => {
                        if (res.status == 'success') {
							if(this.real_id > 0){
								uni.request({
									url:this.websiteUrl + '/api/realname/update',
									method:'PUT',
									data:o,
									header: {
										'Content-Type': 'application/json',
										'token': uni.getStorageSync('token'),
										'X-Client-Hash':uni.getStorageSync('client_hash')
									},
									success: (sRes) => {
										if (sRes.data.status == 'success') {
											uni.showToast({ title: this.$t('lang.edit_submit_audit'), icon: "none" });
											this.button_edit_type = true

											uni.redirectTo({
												url:'../profile/profile'
											})
										} else {
											uni.showToast({ title: sRes.data.errors.message, icon: "none" });
										}
									}
								})
							}else{
								uni.request({
									url:this.websiteUrl + '/api/realname/store',
									method:'POST',
									data:o,
									header: {
										'Content-Type': 'application/json',
										'token': uni.getStorageSync('token'),
										'X-Client-Hash':uni.getStorageSync('client_hash')
									},
									success: (sRes) => {
										if (sRes.data.status == 'success') {
											uni.showToast({ title: this.$t('lang.certification_submit_audit'), icon: "none" });
											this.button_edit_type = true
											uni.redirectTo({
												url:'../profile/profile'
											})
										} else {
											uni.showToast({ title: sRes.data.errors.message, icon: "none" });
										}
									}
								})
							}
						}else{
							uni.showToast({ title: res.errors.message, icon: "none" });
						}
					})
				}else{
					uni.showToast({ title: graceChecker.error, icon: "none" });
				}
			},
			// 改变可修改
			editRealname() {
                this.isDisabled = false
                this.button_edit_type = false
            },
			clickCaptcha(){
				this.$store.dispatch('setImgVerify')
			},
			sendVerifyCode() {
                let o = {
                    captcha: this.imgverifyValue,
                    client: this.client,
                    mobile: this.bank_mobile
                }

                this.$store.dispatch('setSendVerify', o).then(res => {
                    if (res == 'success') {
                        this.button_type = false
                        let second = 60
                        const timer = setInterval(() => {
                            second--
                            if (second) {
                                this.button_text = this.$t('lang.send_again')+'(' + second + 's)'
                            } else {
                                this.button_type = true
                                clearInterval(timer);
                            }
                        }, 1000)
                    }
                })
            },
			uploadCard() {
                if(this.button_edit_type == false) {
                   this.cardShow = true
                }else{
					uni.showToast({ title: this.$t('lang.xiugai_card_1'), icon: "none" });
				}
            },
			handelClose(val){
				if(val == 'card'){
					this.cardShow = false
				}else if(val == 'prompt'){
					this.promptShow = false
				}
			},
			chooseImage(val){
				let that = this
				uni.chooseImage({
					count:1,
					sizeType: ['original', 'compressed'],
					sourceType: ['album', 'camera'],
					success:(res)=>{
						that.imageSrc = res.tempFilePaths[0];

						// #ifdef APP-PLUS
						that.uploadImage(val);
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
									that.uploadImage(val);
								},
								fail:(result) => {
									console.log(result)
								}
							})
						}else{
							that.uploadImage(val);
						}
						// #endif
					}
				})
			},
			async uploadImage(val){
				let that = this;
				uni.showLoading({ mask:true, title: this.$t('lang.shang_chu')});

				//app压缩图片
				// #ifdef APP-PLUS
				that.imageSrc = await compressImage(that.imageSrc);
				// #endif

				pathToBase64(that.imageSrc).then(base64 => {
					that.$store.dispatch('setMaterial',{
						file:{content:base64},
						type:'touxian'
					}).then(data=>{
						if(data.status == 'success'){
							uni.hideLoading();
							if(val == 'front_of_id_card'){
								that.front_of_id_card = data.data[0];
								that.newChooseImage.front = true;
							}else{
								that.reverse_of_id_card = data.data[0];
								that.newChooseImage.reverse = data.data[0];
							}
						}
					})
				}).catch(error => {
					console.error(error,5);
				});
			},
			prompt(){
				this.promptShow = this.promptShow == false ? true : false
			},
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/realname/realname'
			}
		},
		onLoad(){
			this.realnameLoad()
		}
	}
</script>

<style>
.btn-bar{ margin:30upx;}

.value-items{ display: flex; align-items: center; justify-content: space-between;}
.value-items button[size=mini]{ margin: 0; padding: 0 14px;}

.card-main-warp .containTop{ background-color: #ebebed; padding-bottom: 100upx;}
.card-main-warp .containTop .title{ padding: 20upx 0; display: flex; justify-content: center;}
.card-main-warp .containTop .photoBox .photo{ position: relative; border: 2px solid #359df5; background: #FFFFFF; border-radius: 10upx; width: 65%; margin: 0 auto 20upx; padding: 60upx 0 30upx; box-sizing: border-box;}
.card-main-warp .containTop .photoBox .photo:last-child{ margin-bottom: 0;}
.card-main-warp .containTop .photoBox .photo .card{ width: 270upx; height: 162upx; position: relative; margin: 0 auto;}
.card-main-warp .containTop .photoBox .photo .card .preShow{ width: 100%; height: 100%;}
.card-main-warp .containTop .photoBox .photo .text{ text-align: center; margin-top: 10upx; color: #359df5;}
.card-main-warp .containBottom{ background: #FFFFFF;}
.card-main-warp .containBottom .title{ padding: 20upx 0 10upx; margin: 0 45upx; border-bottom: 1px solid #e0e0e0;}
.card-main-warp .containBottom .title .more{ color: #359df5;}
.card-main-warp .containBottom .tip-image{ width: 100%; height: 130upx; margin-top: 20upx;}
.card-main-warp .containBottom .btn-bar{ margin: 50upx 40upx;}

.rule-layer{ border-top: 1px solid #e0e0e0; padding: 20upx; display: flex; flex-direction: column; justify-content: flex-start; text-align: left;}
.rule-layer .p{ line-height: 2.5;}
</style>
