<template>
    <div :class="['merchants', queryInfo.step == 1 ? '' : 'padding_bottom']">
        <template v-if="queryInfo.step == 1">
            <img class="merchant_bg" src="../../../assets/img/merchant_bg.png" alt="">
            <div class="footer_box">
                <van-checkbox v-model="checked" checked-color="#FF6233">{{$t('lang.merchants_read')}}<span class="color-blue" @click.stop="show = true">《{{$t('lang.merchants_agreement')}}》</span></van-checkbox>
                <button :class="['now_btn', checked ? 'red_btn' : 'disabled_btn']" @click="nowOpen">{{$t('lang.merchants_nowStore')}}</button>
            </div>
            <van-popup v-model="show" position="bottom">
                <div class="pop_content">
                    <i class="iconfont icon-close" @click="show = false"></i>
                    <div class="title">{{$t('lang.merchants_agreement')}}</div>
                    <div class="scorll_box">
                        <div class="main_content" v-html="protocol.article_content"></div>
                    </div>
                    <button class="havered now_btn red_btn" @click="haveRed">{{$t('lang.merchants_isread')}}</button>
                </div>
            </van-popup>
        </template>
        <template v-if="queryInfo.step == 2">
            <EnterType :user-info="queryInfo" @go-next="setStep"></EnterType>
        </template>
        <template v-if="queryInfo.step == 3">
            <BasicInfo :user-info="queryInfo" @go-next="setStep"></BasicInfo>
        </template>
        <template v-if="queryInfo.step == 4">
            <MainInfo :user-info="queryInfo" @go-next="setStep" @set-main="setMainInfo"></MainInfo>
        </template>
        <template v-if="queryInfo.step == 5">
            <StoreInfo :user-info="queryInfo" @go-next="setStep" @submit-apply="addCateById"></StoreInfo>
        </template>

        <DscLoading :dscLoading="isLoading"></DscLoading>
    </div>
</template>

<script>
import qs from 'qs'
import { mapState } from 'vuex'

import DscLoading from '@/components/DscLoading'

import EnterType from '@/components/Merchants/enterType.vue'
import BasicInfo from '@/components/Merchants/basicInfo.vue'
import MainInfo from '@/components/Merchants/mainInfo.vue'
import StoreInfo from '@/components/Merchants/storeInfo.vue'

import Vue from 'vue';
import { Button, Checkbox, Icon, Popup, Toast } from 'vant';

Vue.use(Button).use(Checkbox).use(Popup).use(Icon).use(Toast);

export default{
    components: {
        DscLoading,
        EnterType,
        BasicInfo,
        MainInfo,
        StoreInfo
    },
    data(){
        return{
            checked: false,
            show: false,
            isLoading: false,
            protocol: {},
            steps: {},
            queryInfo: {
                uname: '',
                mobile: '',
                step: 1,
                fid: 0,
                enterType: '',
                company: '',
                companyTel: '',
                business_license:'',
                id_card:'',
                id_card_one:'',
                id_card_two:'',
                id_card_three:'',
                columns: [this.$t('lang.flagship_store'), this.$t('lang.exclusive_shop'), this.$t('lang.franchised_store')],
                shopName: '',
                stopLoginName: '',
                cateId: '',
                storeTypeKey: '',
                storeTypeVal: '',
                allCheckedCate: {},
                allCheckedSubCate: [],
                allParentCate: []
            }
        }
    },
    computed: {
        ...mapState({
            merchantsCategory: state => state.user.merchantsCategory
        })
    },
    beforeRouteEnter(to, from, next) {
        next((vm) => {
            // 通过 `vm` 访问组件实例
            if (from.name == "reviewProgress") {
                vm.merchants(2);
            } else if (from.name == "user") {
                vm.merchants(1);
            } else {
                vm.merchants(0);
            }
        });
    },
    beforeRouteLeave (to, from, next) {
        console.log(to, from);
        if (to.name == 'reviewProgress') {
            next()
        } else {
            next(false)
            const curStep = this.queryInfo.step;
            if (curStep > 1) {
                this.$set(this.queryInfo, 'step', curStep - 1);
                this.setStep(this.queryInfo)
            } else {
                next()
            }
        }
    },
    created(){
        
    },
    methods:{
        async merchants(i){
            this.isLoading = true;
            const { data, status } = await this.$store.dispatch('getMerchantsHandle')

            if (status == 'success') {
                this.getMerchantsGuide();

                const stepsObj = data.steps || {};
                const shopObj = data.shop || {};

                const newObj = Object.assign(stepsObj, shopObj);

                const newObj2 = {};

                const { merchants_audit, steps_audit } = newObj;

                const mapObj = {
                    uname: 'contactName',
                    mobile: 'contactPhone',
                    step: 'step_id',
                    fid: 'fid',
                    enterType: 'is_personal',
                    company: 'company',
                    companyTel: 'company_contactTel',
                    business_license:'license_fileImg',
                    id_card:'legal_person_fileImg',
                    id_card_one:'id_card_img_one_fileImg',
                    id_card_two:'id_card_img_two_fileImg',
                    id_card_three:'id_card_img_three_fileImg',
                    shopName: 'rz_shop_name',
                    stopLoginName: 'hope_login_name',
                    storeTypeKey: 'shoprz_type',
                    storeTypeVal: 'shopNameSuffix'
                }

                for (const key in mapObj) {
                    const item = newObj[mapObj[key]];
                    if (mapObj.hasOwnProperty(key) && item) {
                        if (mapObj[key] == 'is_personal') newObj2[key] = parseInt(item) + 1;
                        else newObj2[key] = item;
                    }
                }

                if (merchants_audit == 2 && steps_audit == 0 && i == 2) newObj2.step = 2;

                if (i == 0) {
                    const merchantInfo = JSON.parse(window.localStorage.getItem('merchantInfo')) || {};

                    switch (merchantInfo.step) {
                        case 2:
                            delete newObj2.step;
                            break;
                        case 3:
                            delete newObj2.step;
                            delete newObj2.enterType;
                            break;
                        case 4:
                            delete newObj2.step;
                            delete newObj2.enterType;
                            delete newObj2.uname;
                            delete newObj2.mobile;
                            break;
                        case 5:
                            delete newObj2.step;
                            delete newObj2.enterType;
                            delete newObj2.uname;
                            delete newObj2.mobile;
                            delete newObj2.company;
                            delete newObj2.companyTel;
                            delete newObj2.business_license;
                            delete newObj2.id_card;
                            delete newObj2.id_card_one;
                            delete newObj2.id_card_two;
                            delete newObj2.id_card_three;
                            break;
                        default:
            
                            break;
                    }
                    
                    this.setStep(newObj2);
                } else {
                    this.setStep(newObj2);
                } 

            }

        },
        async getMerchantsGuide(){
            
           const { data, status } = await this.$store.dispatch('getMerchantsGuideHandle')

           if (status == 'success') {
                if (data.article_content) {
                    this.protocol = data;
                    this.isLoading = false;
                }
            }
            
        },
        getMerchantsShop(){
            this.$store.dispatch('setMerchantsShop')
        },
        haveRed() {
            this.checked = true;

            this.show = false
        },
        nowOpen() {
            if (this.checked) {
                this.setStep({step: 2})
            }
        },
        setStep(obj = {}) {
            const merchantInfo = JSON.parse(window.localStorage.getItem('merchantInfo')) || {};
            for (const key in obj) {
                if (obj.hasOwnProperty(key)) {
                    merchantInfo[key] = obj[key]
                }
            }
            
            const data = JSON.stringify(merchantInfo);

            window.localStorage.setItem('merchantInfo', data);

            this.getStep();
        },
        getStep() {
            const merchantInfo = JSON.parse(window.localStorage.getItem('merchantInfo')) || {};
            const queryData = JSON.parse(JSON.stringify(this.queryInfo));
            for (const key in merchantInfo) {
                if (merchantInfo.hasOwnProperty(key)) {
                    queryData[key] = merchantInfo[key]
                }
            }

            this.queryInfo = queryData;

            switch (this.queryInfo.step) {
                case 1:
                    
                    break;
                case 2:
                    document.title = this.$t('lang.merchants_type');
                    break;
                case 3:
                    document.title = this.$t('lang.basic_info');
                    break;
                case 4:
                    document.title = this.$t('lang.main_info');
                    break;
                case 5:
                    document.title = this.$t('lang.shop_detail');
                    if (this.merchantsCategory.length == 0) this.getMerchantsShop();
                    break;
                case 6:
                    this.$router.replace({name: 'reviewProgress'});
                    
                    break;
                default:
                    break;
            }
        },
        setMainInfo(res) {
            if (res.type == 1 && res.flag) {
                this.getMerchantsAgree();
            }
            
            if (res.type == 2 && res.flag) {
                this.getMerchantsAgreePersonal();
            }
        },
        getMerchantsAgree(){
            Toast.loading({
                duration: 0,
                forbidClick: true,
                message: this.$t('lang.loading') + '...'
            });

            this.$store.dispatch('setMerchantsAgree',{
                fid: this.queryInfo.fid,
                agree: 1,
                contactName: this.queryInfo.uname,
                contactPhone: this.queryInfo.mobile,
                contactXinbie: '',
                contactEmail: '',
                companyName: this.queryInfo.company,
                legal_person_fileImg: this.queryInfo.id_card,
                license_fileImg: this.queryInfo.business_license,
                company_contactTel: this.queryInfo.companyTel,
                huoyuan: ''
            }).then(res=>{
                Toast.clear();
                if(res.status == 'success'){
                    if(res.data.code == 0){
                        const obj = {
                            uname: this.queryInfo.uname,
                            mobile: this.queryInfo.mobile,
                            id_card_one: this.queryInfo.id_card_one,
                            id_card_two: this.queryInfo.id_card_two,
                            id_card_three: this.queryInfo.id_card_three,
                            company: this.queryInfo.company,
                            id_card: this.queryInfo.id_card,
                            business_license: this.queryInfo.business_license,
                            companyTel: this.queryInfo.companyTel
                        };
                        this.saveMainData(obj);

                        this.setStep({step: 5})
                    }else{
                        Toast(res.data.msg);
                        return false
                    }
                }
            })
        },
        getMerchantsAgreePersonal(){
            Toast.loading({
                duration: 0,
                forbidClick: true,
                message: this.$t('lang.loading') + '...'
            });

            this.$store.dispatch('setMerchantsAgreePersonal',{
                fid: this.queryInfo.fid,
                agree: 1,
                name: this.queryInfo.uname,
                mobile: this.queryInfo.mobile,
                id_card: '',
                business_address: '',
                business_category: '',
                id_card_img_one_fileImg: this.queryInfo.id_card_one,
                id_card_img_two_fileImg: this.queryInfo.id_card_two,
                id_card_img_three_fileImg: this.queryInfo.id_card_three,
                commitment_fileImg: ''
            }).then(res=>{
                Toast.clear();
                if(res.status == 'success'){
                    if(res.data.code == 0){
                        const obj = {
                            uname: this.queryInfo.uname,
                            mobile: this.queryInfo.mobile,
                            id_card_one: this.queryInfo.id_card_one,
                            id_card_two: this.queryInfo.id_card_two,
                            id_card_three: this.queryInfo.id_card_three,
                            company: this.queryInfo.company,
                            id_card: this.queryInfo.id_card,
                            business_license: this.queryInfo.business_license,
                            companyTel: this.queryInfo.companyTel
                        };
                        this.saveMainData(obj);

                        this.setStep({step: 5})
                    }else{
                        Toast(res.data.msg);
                        return false
                    }
                }
            })
        },
        saveMainData(obj = {}) {
            const merchantsData = JSON.parse(window.localStorage.getItem('merchantsData')) || {};
            for (const key in obj) {
                if (obj.hasOwnProperty(key)) {
                    merchantsData[key] = obj[key]
                }
            }

            const data = JSON.stringify(merchantsData);

            window.localStorage.setItem('merchantsData', data);
        },
        async addCateById() {
            
            const allParentCateId = Object.keys(this.queryInfo.allCheckedCate);

            const arr = [];

            allParentCateId.forEach(item => {
                if (this.queryInfo.allCheckedCate[item].length > 0) {
                    const subArr = [item, this.queryInfo.allCheckedCate[item].join()];
                    arr.push(subArr);
                } else {
                    const subArr = [item, ''];
                    arr.push(subArr);
                }
            })

            if (arr.length > 0) {
                Toast.loading({
                    duration: 0,
                    forbidClick: true,
                    message: this.$t('lang.loading') + '...'
                });

                const { data, status } = await  this.$store.dispatch('setMerchantsAddCate',{
                    data: arr
                });

                if (status != 'success') {
                    Toast.clear();

                    return Toast(this.$t('lang.save_fail'));
                }

                this.addShop()
            }
        },
        addShop(){

            const allParentCateId = Object.keys(this.queryInfo.allCheckedCate);

            this.$store.dispatch('setMerchantsAddShop',{
                data:{
                    rz_shop_name: this.queryInfo.shopName,
                    hope_login_name: this.queryInfo.stopLoginName,
                    shoprz_type: this.queryInfo.storeTypeKey,
                    sub_shoprz_type: '',
                    shop_category_main: allParentCateId.join()
                }
            }).then(res=>{
                Toast.clear();
                if(res.status == "success"){
                    if(res.data.code == 0){
                        // window.localStorage.removeItem('merchantInfo');

                        this.setStep({step: 6});
                       
                    } else {
                        Toast(res.data.msg)
                    }
                }else{
                    Toast(this.$t('lang.jk_error'))
                }
            })
        }
    }
}
</script>

<style scoped>
.merchants {
    padding-bottom: 10rem;
    font-size: 14px;
}
.padding_bottom {
    padding-bottom: 5.5rem;
}
.merchant_bg {
    width: 100%;
}
.color-blue {
    color: #4291FF;
}
.footer_box {
    position: fixed;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: 1.5rem;
    background-color: #fff;
}
.now_btn {
    width: 100%;
    height: 4rem;
    line-height: 4rem;
    margin-top: 1.5rem;
    font-size: 15px;
    font-weight: bold;
    color: #fff;
    text-align: center;
    border-radius: 0.6rem;
}
.red_btn {
    background-color: #f44;
}
.disabled_btn {
    background-color: #ccc;
}
.van-popup {
    height: 98%;
    border-top-left-radius: 1.5rem;
    border-top-right-radius: 1.5rem;
}
.pop_content {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
    padding: 1.5rem 0;
    background-color: #fff;
}
.pop_content .title {
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 1rem;
}
.pop_content .iconfont {
    position: absolute;
    top: 1.5rem;
    right: 1.5rem;
}
.scorll_box {
    flex: 1;
    overflow-y: auto;
    padding: 0 1.5rem;
    -webkit-overflow-scrolling: touch; /*这句是为了滑动更顺畅*/
}

.havered {
    width: 90%;
}

</style>