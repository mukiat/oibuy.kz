<template>
    <view class="basic_info_content">
        <view class="steps">
            <view class="step_item active_item">
                <view class="index_box">1</view>
                <text>{{$t('lang.basic_info')}}</text>
            </view>
            <view class="line_box"></view>
            <view class="step_item active_item">
                <view class="index_box">2</view>
                <text>{{$t('lang.main_info')}}</text>
            </view>
            <view class="line_box"></view>
            <view class="step_item">
                <view class="index_box">3</view>
                <text>{{$t('lang.shop_detail')}}</text>
            </view>
        </view>

        <template v-if="uInfo.enterType == 1">
            <view class="main_box">
                <view class="title item">{{$t('lang.merchants_applytitle')}}</view>
                <view class="input_box item">
                    <text class="text">{{$t('lang.sup_company_name')}}</text>
                    <input type="text" class="flex_1 input" v-model="uInfo.company" :placeholder="$t('lang.enter_contact_name')">
                </view>
                <view class="input_box item border_top">
                    <text class="text">{{$t('lang.merchants_companytel')}}</text>
                    <input type="text" class="flex_1 input" v-model="uInfo.companyTel" :placeholder="$t('lang.enter_contact_tel')">
                </view>
            </view>

            <view class="upload_box">
                <view class="title">{{$t('lang.merchants_maintitle5')}}</view>
                <view class="padding_15">
                    <view class="upload_item" @click="chooseImage('id_card')">
                        <view class="warp">
                            <block v-if="uInfo.id_card">
                                <image :src="uInfo.id_card"></image>
                            </block>
                            <block v-else>
                                <view class="add_ico"></view>
                                <text>{{$t('lang.merchants_maintitle4')}}</text>
                            </block>
                        </view>
                    </view>
                    <view class="upload_item" @click="chooseImage('business_license')">
						<view class="warp">
							<block v-if="uInfo.business_license">
								<image :src="uInfo.business_license"></image>
							</block>
							<block v-else>
								<view class="add_ico"></view>
								<text>{{$t('lang.upload_business_license')}}</text>
							</block>
						</view>
                    </view>
                </view>
            </view>
        </template>

        <view class="upload_box" v-if="uInfo.enterType == 2">
            <view class="title">{{$t('lang.merchants_maintitle3')}}</view>
            <view class="id_cart">
                <view class="upload_item" @click="chooseImage('id_card_one')">
					<view class="warp">
						<block v-if="uInfo.id_card_one">
							<image :src="uInfo.id_card_one"></image>
						</block>
						<block v-else>
							<view class="add_ico"></view>
							<text>{{$t('lang.merchants_maintitle2')}}</text>
						</block>
					</view>
                </view>
                <view class="upload_item" @click="chooseImage('id_card_two')">
					<view class="warp">
						<block v-if="uInfo.id_card_two">
							<image :src="uInfo.id_card_two"></image>
						</block>
						<block v-else>
							<view class="add_ico"></view>
							<text>{{$t('lang.merchants_maintitle1')}}</text>
						</block>
					</view>
                </view>
                <view class="upload_item" @click="chooseImage('id_card_three')">
					<view class="warp">
						<block v-if="uInfo.id_card_three">
							<image :src="uInfo.id_card_three"></image>
						</block>
						<block v-else>
							<view class="add_ico"></view>
							<text>{{$t('lang.merchants_maintitle')}}</text>
						</block>
					</view>
                </view>
            </view>
            <image :src="imagePath.imageFalseSrc" mode="widthFix" class="bottom_imgs"></image>
        </view>

        <view class="tips">{{$t('lang.merchants_tips')}}</view>

        <view class="btns">
            <view class="btn_item gray_btn" @click="goBack">{{$t('lang.merchants_goback')}}</view>
            <view class="btn_item red_btn" @click="goNext">{{$t('lang.next_step')}}</view>
        </view>
    </view>
</template>

<script>
import { pathToBase64, base64ToPath } from '@/common/image-tools/index.js'
import { compressImage } from '@/common/compressImage.js'

export default {
    props: {
        userInfo: {
            type: Object,
            default: function () {
                return {
                    enterType: '',
                    company: '',
                    companyTel: '',
                    business_license:'',
                    id_card:'',
                    id_card_one:'',
                    id_card_two:'',
                    id_card_three:'',
					imageSrc:''
                }
            }
        }
    },
    data() {
        return {
            uInfo: this.userInfo
        }
    },
    created() {
        
    },
    methods: {
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
						if(val == 'business_license'){
							this.uInfo.business_license = data.data[0]
						}else if(val == 'id_card'){
							this.uInfo.id_card = data.data[0];
						}else if(val == 'id_card_one'){
							this.uInfo.id_card_one = data.data[0];
						}else if(val == 'id_card_two'){
							this.uInfo.id_card_two = data.data[0];
						}else if(val == 'id_card_three'){
							this.uInfo.id_card_three = data.data[0];
						}
					}
				})
			}).catch(error => {
				console.error(error,5);
			});
		},
        goBack() {
            this.$set(this.uInfo, 'step', 3)
            this.$emit('go-next', this.uInfo)
        },
        goNext() {
            let rule = /^(\d{10})$/;
            let isPhone = /^([0-9]{3,4}-)?[0-9]{7,8}$/;

            if (this.uInfo.enterType == 1) {
                if (!this.uInfo.company.trim()) return uni.showToast({ title: this.$t('lang.merchants_maintoast1'), icon: 'none' });
                if (!this.uInfo.companyTel.trim()) return uni.showToast({ title: this.$t('lang.merchants_maintoast2'), icon: 'none' });
                if (!this.uInfo.id_card.trim() || !this.uInfo.business_license.trim()) return uni.showToast({ title: this.$t('lang.merchants_maintoast3'), icon: 'none' });
				
                this.$set(this.uInfo, 'step', 4)
                
				const arr = [
					'uname',
					'mobile',
					'company',
					'companyTel',
					'business_license',
					'id_card'
				]

				const flag = this.isUpdate(arr)

				if (!flag) this.$set(this.uInfo, 'step', 5)

				this.$emit('go-next', this.uInfo)

				this.$emit('set-main', {type: 1, flag: flag})
            } else {
                if (!this.uInfo.id_card_one.trim() || !this.uInfo.id_card_two.trim() || !this.uInfo.id_card_three.trim()) return Toast(this.$t('lang.merchants_maintoast3'));
                
				this.$set(this.uInfo, 'step', 4)

				const arr = [
					'uname',
					'mobile',
					'id_card_one',
					'id_card_two',
					'id_card_three'
				]

				const flag = this.isUpdate(arr)
				
				if (!flag) this.$set(this.uInfo, 'step', 5)

				this.$emit('go-next', this.uInfo)

				this.$emit('set-main', {type: 2, flag: flag})
            }
        },
        isUpdate(arr = []) {
            let flag = false;
			
            const merchantsData = uni.getStorageSync("merchantsData") || {};

            arr.some(item => {
                if (this.uInfo[item] != merchantsData[item]) {
                    flag = true;
                    return true;
                }
            })
            
            return flag
        }
    }
}
</script>

<style lang="scss" scoped>
.basic_info_content {
    padding: 20upx;
    .steps {
        display: flex;
        justify-content: space-between;
        padding: 40upx 40upx 30upx;;
        border-radius: 20upx;
        color: #fff;
        background: linear-gradient(to bottom,#FF2C2D,#FD5E29);
        .step_item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #FEA694;
			font-size: 25upx;
			line-height: 1.5;
        }
        .index_box {
            width: 40upx;
            height: 40upx;
            line-height: 40upx;
            text-align: center;
            border-radius: 50%;
            margin-bottom: 10upx;
            color: #FE3F30;
            background-color: #FE9E94;
        }
        .active_item {
            color: #fff;
            .index_box {
                color: #FF4335;
                background-color: #fff;
            }
        }
        .line_box {
            flex: 1;
            height: 1px;
            margin-top: 20upx;
            background-color: #fff;
        }
    }
    .main_box {
        border-radius: 20upx;
        margin-top: 20upx;
        background-color: #fff;
        .item {
            display: flex;
            align-items: center;
            height: 100upx;
        }
        .title {
            font-size: 30upx;
            font-weight: bold;
            padding: 0 30upx;
            border-bottom: 1px solid #f6f6f9;
        }
        .input_box {
            margin-left: 30upx;
            .text {
                margin-right: 20upx;
            }
            
            .input{
            	padding: 2.5px 0 0;
            }
        }
        .border_top {
            border-top: 1px solid #f6f6f9;
        }
    }

    .btns {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        display: flex;
        justify-content: space-around;
        border-top: 1px solid #f6f6f9;
        padding: 1rem;
        font-size: 15px;
        background-color: #fff;
        box-sizing: border-box;
		
        .btn_item {
            width: 40%;
            height: 34px;
            text-align: center;
            border-radius: 10upx;
            color: #333;
        }
        .gray_btn {
            border: 1px solid #cdcdcf;
            line-height: 32px;
        }
        .red_btn {
            color: #fff;
            line-height: 34px;
            background-color: #f44;
        }
    }

    .upload_box {
        border-radius: 20upx;
        margin-top: 20upx;
        background-color: #fff;
        padding-bottom: 20upx;
        .title {
            width: 100%;
            height: 100upx;
            line-height: 100upx;
            font-size: 30upx;
            font-weight: bold;
            padding: 0 30upx;
			box-sizing: border-box;
        }
        .padding_15 {
            padding: 0 30upx;
        }
        .bottom_imgs {
            display: block;
            width: 100%;
            margin-top: 30upx;
        }
        .upload_item {
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 250upx;
            border-radius: 20upx;
            border: 1px dashed #53A3FF;
            color: #53A3FF;
            background-color: #F6FAFF;
            .van-uploader {
                width: 100%;
                height: 100%;
            }
            .warp {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                height: 100%;
                img {
                    width: 100%;
                    height: 100%;
                    object-fit: contain;
                }
            }
            .add_ico {
                position: relative;
                width: 40upx;
                height: 40upx;
                margin-bottom: 20upx;
                &:after {
                    position: absolute;
                    top: 0;
                    left: 50%;
                    transform: translateX(-50%);
                    content: '';
                    width: 4px;
                    height: 100%;
                    border-radius: 2px;
                    background-color: #53A3FF;
                }
                &:before {
                    position: absolute;
                    top: 50%;
                    left: 0;
                    transform: translateY(-50%);
                    content: '';
                    width: 100%;
                    height: 4px;
                    border-radius: 2px;
                    background-color: #53A3FF;
                }
            }
            &:last-child {
                margin-top: 20upx;
            }
        }
    }
    .id_cart {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        padding: 0 20upx;
        .upload_item {
            width: 48%;
        }
    }
    .tips {
        color: #999;
        padding: 20upx 10upx;
		font-size: 25upx;
    }
    
}
</style>