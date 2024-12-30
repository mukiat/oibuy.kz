<template>
    <div class="basic_info_content">
        <div class="steps">
            <div class="step_item active_item">
                <div class="index_box">1</div>
                <span>{{$t('lang.basic_info')}}</span>
            </div>
            <div class="line_box"></div>
            <div class="step_item active_item">
                <div class="index_box">2</div>
                <span>{{$t('lang.main_info')}}</span>
            </div>
            <div class="line_box"></div>
            <div class="step_item">
                <div class="index_box">3</div>
                <span>{{$t('lang.shop_detail')}}</span>
            </div>
        </div>

        <template v-if="uInfo.enterType == 1">
            <div class="main_box">
                <div class="title item">{{$t('lang.merchants_applytitle')}}</div>
                <div class="input_box item">
                    <label>{{$t('lang.sup_company_name')}}</label>
                    <input type="text" class="flex_1" v-model="uInfo.company" :placeholder="$t('lang.enter_contact_name')">
                </div>
                <div class="input_box item border_top">
                    <label>{{$t('lang.merchants_companytel')}}</label>
                    <input type="text" class="flex_1" v-model="uInfo.companyTel" :placeholder="$t('lang.enter_contact_tel')">
                </div>
            </div>

            <div class="upload_box">
                <div class="title">{{$t('lang.merchants_maintitle5')}}</div>
                <div class="padding_15">
                    <div class="upload_item">
                        <van-uploader :after-read="afterRead('id_card')">
                            <div class="warp">
                                <template v-if="uInfo.id_card">
                                    <img :src="uInfo.id_card" alt="">
                                </template>
                                <template v-else>
                                    <div class="add_ico"></div>
                                    <span>{{$t('lang.merchants_maintitle4')}}</span>
                                </template>
                            </div>
                        </van-uploader>
                    </div>
                    <div class="upload_item">
                        <van-uploader :after-read="afterRead('business_license')">
                            <div class="warp">
                                <template v-if="uInfo.business_license">
                                    <img :src="uInfo.business_license" alt="">
                                </template>
                                <template v-else>
                                    <div class="add_ico"></div>
                                    <span>{{$t('lang.upload_business_license')}}</span>
                                </template>
                            </div>
                        </van-uploader>
                    </div>
                </div>
            </div>
        </template>

        <div class="upload_box" v-if="uInfo.enterType == 2">
            <div class="title">{{$t('lang.merchants_maintitle3')}}</div>
            <div class="id_cart">
                <div class="upload_item">
                    <van-uploader :after-read="afterRead('id_card_one')">
                        <div class="warp">
                            <template v-if="uInfo.id_card_one">
                                <img :src="uInfo.id_card_one" alt="">
                            </template>
                            <template v-else>
                                <div class="add_ico"></div>
                                <span>{{$t('lang.merchants_maintitle2')}}</span>
                            </template>
                        </div>
                    </van-uploader>
                </div>
                <div class="upload_item">
                    <van-uploader :after-read="afterRead('id_card_two')">
                        <div class="warp">
                            <template v-if="uInfo.id_card_two">
                                <img :src="uInfo.id_card_two" alt="">
                            </template>
                            <template v-else>
                                <div class="add_ico"></div>
                                <span>{{$t('lang.merchants_maintitle1')}}</span>
                            </template>
                        </div>
                    </van-uploader>
                </div>
                <div class="upload_item">
                    <van-uploader :after-read="afterRead('id_card_three')">
                        <div class="warp">
                            <template v-if="uInfo.id_card_three">
                                <img :src="uInfo.id_card_three" alt="">
                            </template>
                            <template v-else>
                                <div class="add_ico"></div>
                                <span>{{$t('lang.merchants_maintitle')}}</span>
                            </template>
                        </div>
                    </van-uploader>
                </div>
            </div>
            <img src="../../assets/img/false-1.png" class="bottom_imgs" alt="">
        </div>

        <div class="tips">{{$t('lang.merchants_tips')}}</div>

        <div class="btns">
            <div class="btn_item gray_btn" @click="goBack">{{$t('lang.merchants_goback')}}</div>
            <div class="btn_item red_btn" @click="goNext">{{$t('lang.next_step')}}</div>
        </div>
    </div>
</template>

<script>
import Vue from 'vue';
import { Uploader, Toast } from 'vant';
import commonGet from '@/mixins/common-get'

Vue.use(Uploader).use(Toast);
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
                    id_card_three:''
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
        afterRead(item){
            return file => {
                if(file.length > 1){
                    Toast(this.$t('lang.only_one_image_can_be_selected'))
                }else{
                    Toast.loading({
                        duration: 0,
                        forbidClick: true,
                        message: this.$t('lang.loading') + '...'
                    });
                    this.imgPreview(file.file,item);
                }
            }    
        },
        imgPreview(file,item) {
          let that = this;
          if (/^image/.test(file.type)) {
            // 创建一个reader
            let reader = new FileReader();
            // 将图片2将转成 base64 格式
            reader.readAsDataURL(file);
            // 读取成功后的回调
            reader.onloadend = function () {
              let result = this.result;
              let img = new Image();
              img.src = result;
              //判断图片是否大于500K,是就直接上传，反之压缩图片
              if (this.result.length <= 500 * 1024) {
                that.updataImage(this.result,item);
              } else {
                img.onload = function () {
                  let data = commonGet.compress(img, orientation);
                  console.log(data)
                  that.updataImage(data,item)
                };
              }
            };
          }
        },
        updataImage(file,item){
            this.$store.dispatch('setMaterial',{
                file:{
                    content:file
                },
                type:item
            }).then(res=>{
                Toast.clear();
                if(item == 'business_license'){
                    this.uInfo.business_license = res.data[0]
                }else if(item == 'id_card'){
                    this.uInfo.id_card = res.data[0]
                }else if(item == 'id_card_one'){
                    this.uInfo.id_card_one = res.data[0]
                }else if(item == 'id_card_two'){
                    this.uInfo.id_card_two = res.data[0]
                }else if(item == 'id_card_three'){
                    this.uInfo.id_card_three = res.data[0]
                }
            })
        },
        goBack() {
            this.$set(this.uInfo, 'step', 3)

            this.$emit('go-next', this.uInfo)
        },
        goNext() {
            // let rule = /^(\d{10})$/;

            // let isPhone = /^([0-9]{3,4}-)?[0-9]{7,8}$/;

            // let isPhone = /^0\d{2,3}-?\d{7,8}$/;

            if (this.uInfo.enterType == 1) {
                if (!this.uInfo.company.trim()) return Toast(this.$t('lang.merchants_maintoast1'));
                if (!this.uInfo.companyTel.trim()) return Toast(this.$t('lang.merchants_maintoast2'));
                if (!this.uInfo.id_card.trim() || !this.uInfo.business_license.trim()) return Toast(this.$t('lang.merchants_maintoast3'));

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

                // this.getMerchantsAgree()
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

                // this.getMerchantsAgreePersonal()
            }

            // this.$router.push({name: 'mainInfo', params: {type: this.type}})
        },
        isUpdate(arr = []) {
            let flag = false;

            const merchantsData = JSON.parse(window.localStorage.getItem('merchantsData')) || {};

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
    font-size: 14px;
    padding: 1rem;
    .steps {
        display: flex;
        justify-content: space-between;
        padding: 2rem;
        border-radius: 1rem;
        color: #fff;
        background: linear-gradient(to bottom,#FF2C2D,#FD5E29);
        .step_item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #FEA694;
        }
        .index_box {
            width: 2rem;
            height: 2rem;
            line-height: 2rem;
            text-align: center;
            border-radius: 50%;
            margin-bottom: 1rem;
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
            margin-top: 1rem;
            background-color: #fff;
        }
    }
    .main_box {
        border-radius: 1rem;
        margin-top: 1rem;
        background-color: #fff;
        .item {
            display: flex;
            align-items: center;
            height: 5rem;
        }
        .title {
            font-size: 15px;
            font-weight: bold;
            padding: 0 1.5rem;
            border-bottom: 1px solid #f6f6f9;
        }
        .input_box {
            margin-left: 1.5rem;
            label {
                margin-right: 2rem;
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
        .btn_item {
            width: 40%;
            height: 34px;
            text-align: center;
            border-radius: 0.5rem;
            color: #333;
        }
        .gray_btn {
            border: 1px solid #f6f6f9;
            line-height: 32px;
        }
        .red_btn {
            color: #fff;
            line-height: 34px;
            background-color: #f44;
        }
    }

    .upload_box {
        border-radius: 1rem;
        margin-top: 1rem;
        background-color: #fff;
        padding-bottom: 1.5rem;
        .title {
            width: 100%;
            height: 4rem;
            line-height: 4rem;
            font-size: 15px;
            font-weight: bold;
            padding: 0 1.5rem;
        }
        .padding_15 {
            padding: 0 1.5rem;
        }
        .bottom_imgs {
            display: block;
            width: 100%;
            margin-top: 1.5rem;
        }
        .upload_item {
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 12rem;
            border-radius: 1rem;
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
                width: 2rem;
                height: 2rem;
                margin-bottom: 2rem;
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
                margin-top: 1.5rem;
            }
        }
    }
    .id_cart {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        padding: 0 1.5rem;
        .upload_item {
            width: 48%;
        }
    }
    .tips {
        color: #999;
        padding: 1rem 0;
    }
    
}
</style>