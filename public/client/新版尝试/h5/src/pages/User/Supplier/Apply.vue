<template>
    <div class="merchants" v-if="supplierInfo">
        <div class="step" :class="'step_'+step">
            <template v-if="step == 1">
                <div class="header">
                    <div class="title">{{$t('lang.supplier_apply')}}</div>
                    <div class="subtitle"><i>{{$t('lang.supplier_apply_subtitle_1')}}</i></div>
                </div>
                <div class="content">
                    <van-cell-group class="van-cell-noleft m-top08">
                        <van-field :label="$t('lang.supplier_name')" v-model="suppliers_name" required :placeholder="$t('lang.please_input')+$t('lang.supplier_name')" />
                        <van-field :label="$t('lang.supplier_desc')" v-model="suppliers_desc" :placeholder="$t('lang.please_input')+$t('lang.supplier_desc')" type="textarea" rows="2" autosize/>
                        <van-field :label="$t('lang.truename')" v-model="real_name" required :placeholder="$t('lang.please_input')+$t('lang.truename')" />
                        <van-field :label="$t('lang.contact_mobile')" v-model="mobile_phone" required :placeholder="$t('lang.please_input')+$t('lang.contact_mobile')" />
                        <van-field :label="$t('lang.service_qq')" v-model="kf_qq" :placeholder="$t('lang.please_input')+$t('lang.service_qq')" type="textarea" rows="2" autosize/>
                        <van-field :label="$t('lang.contact_email')" v-model="email" required :placeholder="$t('lang.please_input')+$t('lang.contact_email')" />
                    </van-cell-group>
                </div>
            </template>
            <template v-else-if="step == 2">
                <div class="header">
                    <div class="title">{{$t('lang.supplier_apply')}}</div>
                    <div class="subtitle"><i>{{$t('lang.supplier_apply_subtitle_2')}}</i></div>
                </div>
                <div class="content">
                    <van-cell-group class="van-cell-noleft m-top08">
                        <van-field :label="$t('lang.id_number')" v-model="self_num" required :placeholder="$t('lang.please_input')+$t('lang.id_number')" />
                        <van-cell :title="$t('lang.id_front')" class="after-read">
                            <van-uploader :after-read="afterRead('front_of_id_card')">
                                <i class="iconfont icon-uploader"></i>
                                <span>{{$t('lang.upload')}}{{$t('lang.id_front')}}</span>
                            </van-uploader>
                            <i class="iconfont icon-picture" @click="previewImage('front_of_id_card')" v-if="front_of_id_card"></i>
                        </van-cell>
                        <van-cell :title="$t('lang.id_back')" class="after-read">
                            <van-uploader :after-read="afterRead('reverse_of_id_card')">
                                <i class="iconfont icon-uploader"></i>
                                <span>{{$t('lang.upload')}}{{$t('lang.id_back')}}</span>
                            </van-uploader>
                            <i class="iconfont icon-picture" @click="previewImage('reverse_of_id_card')" v-if="reverse_of_id_card"></i>
                        </van-cell>
                        <van-cell :title="$t('lang.supplier_logo')" class="after-read">
                            <van-uploader :after-read="afterRead('suppliers_logo')">
                                <i class="iconfont icon-uploader"></i>
                                <span>{{$t('lang.upload')}}{{$t('lang.supplier_logo')}}</span>
                            </van-uploader>
                            <i class="iconfont icon-picture" @click="previewImage('suppliers_logo')" v-if="suppliers_logo"></i>
                        </van-cell>
                        <van-field :label="$t('lang.sup_company_name')" v-model="company_name" required :placeholder="$t('lang.please_input')+$t('lang.sup_company_name')" />
                        <div class="text-all dis-box text-all-region">
                            <label>{{ $t('lang.region_alt') }}<em>*</em></label>
                            <div class="input-text">
                                <div class="address-box" @click="handelRegionShow">
                                    <span class="text-all-span">{{ regionSplic }}</span>
                                    <span class="user-more"><i class="iconfont icon-more"></i></span>
                                </div>
                            </div>
                        </div>
                        <van-field :label="$t('lang.company_address')" v-model="company_address" required :placeholder="$t('lang.please_input')+$t('lang.company_address')" />
                    </van-cell-group>
                </div>
            </template>
            <template v-else-if="step == 3 && supplier">
                <div class="audit-pic">
                    <template v-if="supplier.review_status == 1"><img src="../../../assets/img/merchants_audit_3.png" class="img" /></template>
                    <template v-if="supplier.review_status == 2"><img src="../../../assets/img/merchants_audit_2.png" class="img" /></template>
                    <template v-if="supplier.review_status == 3"><img src="../../../assets/img/merchants_audit_1.png" class="img" /></template>
                </div>
                <div class="audit-type">
                    <template v-if="supplier.review_status == 1">
                        <div class="title">{{$t('lang.merchants_audit_0')}}</div>
                        <div class="subtitle">{{$t('lang.supplier_audit_0_desc')}}</div>
                    </template>
                    <template v-if="supplier.review_status == 2">
                        <div class="title">{{$t('lang.merchants_audit_2')}}</div>
                        <div class="subtitle">{{$t('lang.label_platform_reply')}}222</div>
                    </template>
                    <template v-if="supplier.review_status == 3">
                        <div class="title">{{$t('lang.merchants_audit_1')}}</div>
                        <div class="subtitle">{{$t('lang.supplier_audit_1_desc')}}</div>
                    </template>
                </div>
                <template v-if="supplier.review_status == 2">
                <div class="audit-update">
                    <button type="button" @click="backStep(2)" class="audit-shopname" v-if="1">{{$t('lang.new_registration')}}</button>
                </div>
                </template>
            </template>

            <div class="btn-bar btn-bar-radius">
                <template v-if="step == 1">
                    <button type="button" @click="merchantStep(1)" class="btn btn-lg-red">{{$t('lang.supplier_next_1')}}</button>
                </template>
                <template v-else-if="step == 2">
                    <button type="button" @click="backStep(2)" class="btn btn-lg-white">{{$t('lang.last_step')}}</button>
                    <button type="button" @click="merchantStep(2)" class="btn btn-lg-red">{{$t('lang.supplier_next_2')}}</button>
                </template>
                <template v-else>
                    <button type="button" @click="linkHref('user')" class="btn btn-lg-red">{{$t('lang.enter_user_center')}}</button>
                    <button type="button" @click="linkHref('home')" class="btn btn-lg-white">{{$t('lang.home_back')}}</button>
                </template>
            </div>
        </div>
        <!--初始化loading-->
        <DscLoading :dscLoading="dscLoading"></DscLoading>

        <Region :display="regionShow" :regionOptionDate="regionOptionDate" @updateDisplay="getRegionShow" @updateRegionDate="getRegionOptionDate"></Region>
    </div>
</template>

<script>
import qs from 'qs'
import { mapState } from 'vuex'
import { Select, Option, OptionGroup, } from 'element-ui'

import DscLoading from '@/components/DscLoading'
import formProcessing from '@/mixins/form-processing'

import { 
    Checkbox,
    CheckboxGroup,
    Popup,
    Cell,
    CellGroup,
    Radio,
    RadioGroup,
    Field,
    Uploader,
    Button,
    Icon,
    Toast,
    ImagePreview
} from 'vant'

export default{
    mixins: [formProcessing],
    data(){
        return{
            step: 1,
            suppliers_name:'',
            suppliers_desc:'',
            real_name:'',
            mobile_phone:'',
            kf_qq:'',
            email:'',
            self_num:'',
            front_of_id_card:'',
            reverse_of_id_card:'',
            suppliers_logo:'',
            company_name:'',
            company_address:'',
            front_of_id_card_image:'',
            reverse_of_id_card_image:'',
            suppliers_logo_image:'',
            checked: false,
            dscLoading:true,
        }
    },
    components: {
        'EcSelect': Select,
        'EcOptionGroup': OptionGroup,
        'EcOption': Option,
        [Checkbox.name]: Checkbox, 
        [CheckboxGroup.name]: CheckboxGroup,
        [Popup.name]: Popup,
        [Cell.name]: Cell, 
        [CellGroup.name]: CellGroup,
        [Radio.name]: Radio, 
        [RadioGroup.name]: RadioGroup,
        [Field.name]: Field,
        [Uploader.name]: Uploader,
        [Button.name]: Button,
        [Icon.name]: Icon,
        [Toast.name]: Toast,
        DscLoading,
    },
    computed:{
        ...mapState({
            supplierInfo: state => state.user.supplierInfo.supplier,
        }),
        supplier(){
            return this.supplierInfo.supplier
        }
    },
    created(){
        this.merchants();
    },
    methods:{
        merchants(val){
            let that = this
            that.$store.dispatch('setSupplierApplyInfo',{
                step_id:val
            })
        },
        addShop(val){
            let that = this
            that.$store.dispatch('setSupplierApply',{
                suppliers_name: that.suppliers_name,
                suppliers_desc: that.suppliers_desc,
                real_name: that.real_name,
                mobile_phone: that.mobile_phone,
                kf_qq: that.kf_qq,
                email: that.email,
                self_num: that.self_num,
                front_of_id_card: that.front_of_id_card,
                reverse_of_id_card: that.reverse_of_id_card,
                suppliers_logo: that.suppliers_logo,
                company_name: that.company_name,
                country:1,
                province:this.regionOptionDate.province.id,
                city:this.regionOptionDate.city.id,
                district:this.regionOptionDate.district.id,
                company_address: that.company_address,
            }).then(res=>{
                if(res.status == "success"){
                    Toast(res.data.msg)
                    if(res.data.code == 0){
                        this.dscLoading = true
                        this.merchants(val);
                        this.step = val + 1
                    }
                }else{
                    Toast(this.$t('lang.jk_error'))
                }
            })
        },
        merchantStep(val){
            switch(val){
                case 1:
                    if(this.suppliers_name == ''){ Toast(this.$t('lang.please_input')+this.$t('lang.supplier_name')); return false;}
                    if(this.real_name == ''){ Toast(this.$t('lang.please_input')+this.$t('lang.truename')); return false;}
                    if(this.mobile_phone == ''){ Toast(this.$t('lang.please_input')+this.$t('lang.contact_mobile')); return false;}
                    if(!this.checkMobile()){ Toast(this.$t('lang.phone_number_format')); return false;}
                    if(this.email == ''){ Toast(this.$t('lang.please_input')+this.$t('lang.contact_email')); return false;}
                    if(!this.checkEmail()){ Toast(this.$t('lang.email_number_format')); return false;}
                    this.step = val + 1;
                    break;
                case 2:
                    if(this.self_num == ''){Toast(this.$t('lang.please_input')+this.$t('lang.id_number')); return false;}
                    if(!this.checkId()){ Toast(this.$t('lang.id_card_format')); return false;}
                    if(this.front_of_id_card == ''){Toast(this.$t('lang.please_upload')+this.$t('lang.id_front')); return false;}
                    if(this.reverse_of_id_card == ''){Toast(this.$t('lang.please_upload')+this.$t('lang.id_back')); return false;}
                    if(this.suppliers_logo == ''){Toast(this.$t('lang.please_upload')+this.$t('lang.supplier_logo')); return false;}
                    if(this.company_name == ''){Toast(this.$t('lang.please_input')+this.$t('lang.sup_company_name')); return false;}
                    if(this.company_address == ''){Toast(this.$t('lang.please_input')+this.$t('lang.company_address')); return false;}
                    this.addShop(val);
                    break;
                default:
                    break
            }
        },
        backStep(val){
            this.step = val - 1  
        },
        afterRead(item){
            return file => {
                if(file.length > 1){
                    Toast(this.$t('lang.only_one_image_can_be_selected'))
                }else{
                    this.$store.dispatch('setMaterial',{
                        file:file,
                        type:item
                    }).then(res=>{
                        if(item == 'front_of_id_card'){
                            this.front_of_id_card = res.data[0]
                        }else if(item == 'reverse_of_id_card'){
                            this.reverse_of_id_card = res.data[0]
                        }else if(item == 'suppliers_logo'){
                            this.suppliers_logo = res.data[0]
                        }
                    })
                }
            }    
        },
        checkMobile() {
            let rule = /^(\d{10})$/
            if (rule.test(this.mobile_phone)) {
                return true
            } else {
                return false
            }
        },
        checkEmail() {
            let rule = /^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/
            if (rule.test(this.email)) {
                return true
            } else {
                return false
            }
        },
        checkId(){
            let rule = /^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$|^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/
            if (rule.test(this.self_num)) {
                return true
            } else {
                return false
            }
        },
        previewImage(item){
            if(item == 'front_of_id_card'){
                this.front_of_id_card_image = ImagePreview({
                    images: [this.front_of_id_card],
                })
            }else if(item == 'reverse_of_id_card'){
                this.reverse_of_id_card_image = ImagePreview({
                    images: [this.reverse_of_id_card],
                })
            }else if(item == 'suppliers_logo'){
                this.suppliers_logo_image = ImagePreview({
                    images: [this.suppliers_logo],
                })
            }
        },
        linkHref(name){
            this.$router.push({
                name:name
            })
        },
        handelRegionShow(){
            this.regionShow = this.regionShow ? false : true
            this.$store.dispatch('setRegion',{
                region:1,
                level:1
            })
        },
    },
    watch:{
        supplierInfo(){
            this.dscLoading = false
        },
        supplier(){
            if(this.supplier){
                // 表单赋值
                this.suppliers_name = this.supplier.suppliers_name;
                this.suppliers_desc = this.supplier.suppliers_desc;
                this.real_name = this.supplier.real_name;
                this.mobile_phone = this.supplier.mobile_phone;
                this.kf_qq = this.supplier.kf_qq;
                this.email = this.supplier.email;
                this.self_num = this.supplier.self_num;
                this.front_of_id_card = this.supplier.front_of_id_card;
                this.reverse_of_id_card = this.supplier.reverse_of_id_card;
                this.suppliers_logo = this.supplier.suppliers_logo;
                this.company_name = this.supplier.company_name;
                this.company_address = this.supplier.company_address;
                this.dscLoading = false
                // 地区赋值
                this.regionOptionDate.province.id = this.supplierInfo.region_level[1].region_id
                this.regionOptionDate.city.id = this.supplierInfo.region_level[2].region_id
                this.regionOptionDate.district.id = this.supplierInfo.region_level[3] ? this.supplierInfo.region_level[3].region_id : 0;

                this.regionOptionDate.regionSplic = this.supplierInfo.region_level[1].region_name + ' ' + this.supplierInfo.region_level[2].region_name + ' ' + (this.supplierInfo.region_level[3] ? this.supplierInfo.region_level[3].region_name : '');
                // 第三步，审核状态
                if(this.supplier.review_status){
                    this.step = 3
                }
            }
        }
    }
}
</script>

<style scoped>
.merchants{ background: #ffffff; padding-bottom: 3rem; }
.surface{ }

.btn-bar{ margin-top: 2rem; justify-content: center;}
.btn-bar .btn{ display: inline-block; width: auto; padding: .8rem 2rem; margin-left:1rem; }
.btn-bar .btn:first-child{ margin-left: 0; }

.btn-min{ display: inline-block; width: auto; padding: .6rem 1.5rem; border-radius: .4rem; }

.merchants_article{ width: 90%; height: 90%;}
.merchants_article .title{ text-align: center; font-size: 2rem; color: #000; padding: 2rem 0; position: relative; }
.merchants_article .title .van-icon{ position: absolute; top: 1rem; right: 1rem; }
.merchants_article .content{ padding: 0 2rem 2rem; font-size: 1.5rem; height: 88%; overflow: auto; line-height: 2;word-wrap: break-word; word-break: normal;}

.step .header{ text-align: center; }
.step .header .title{ font-size: 3rem; color: #333; padding-top: 1.5rem;}
.step .header .subtitle{ font-size: 1.8rem; padding: 0 2rem; color: #666; padding-top: 1rem; display: inline-block; margin: 0 auto; position: relative;}
.step .header .subtitle i{ overflow: hidden; position: relative; z-index: 2; background: #ffffff; padding: 0 .5rem; }
.step .header .subtitle:before{ content: " "; position: absolute; left: 0; right: 0; height: 1px; background: #E6E6E6; top: 66%; }
.step .content{ margin-top: 1.4rem; }
.step .content .van-uploader{ color: #666666; }
.step .content .van-uploader .icon-uploader{ font-size: 14px; color: #D2D4D9; margin-right: 5px; }

.category-popup{ width: 90%; height: 60%; }
.category-popup .title{text-align: center; font-size: 2rem; color: #000; padding: 2rem 0; position: relative; }

.category-list{ margin: 3rem 3rem 0; height: 24rem; overflow: auto; }
.category-list .item{ margin-bottom: 1rem; border: 1px solid #E6E6E6;}
.category-list .item:last-child{ margin-bottom: 0; }
.category-list .item .tit{ background: #F2F3F7; padding: 5px 20px; color: #666666;}
.category-list .item .lie{ padding: 5px 20px; color: #999999;}
.category-list .item .lie span{ margin:0 10px 5px 0; display: inline-block;}
.category-list .item .lie .iconfont{ font-size: 12px; margin-left: 5px; }

.step_6{ text-align: center; }
.audit-pic{ padding: 5rem 0 2rem; }
.audit-pic .img{ width: 70%; margin: 0 auto; }
.audit-type{ text-align: center; }
.audit-type .title{ font-size: 3rem; color: #333333; margin-bottom: 1.5rem; }
.audit-type .subtitle{ font-size: 1.8rem; color: #666666; padding: 0 20px;}
.audit-shopname{ display: inline-block; padding: 1.2rem 1.5rem; background: linear-gradient(-90deg,rgba(45,123,249,1),rgba(61,174,253,1)); margin: 0 auto; color: #ffffff; font-size: 1.5rem; border-radius: 3rem; margin-top: 3rem;}
.audit-update{ text-align: center; }
.audit-update .audit-shopname{ border-radius: 0; padding: 1.2rem 3rem; }

.text-all-region{ padding: 13px 15px; }
.text-all-region label{ width: 90px; text-align: right; color: #999; font-size: 14px;}
.text-all-region label em{ position: absolute; left: 7px }
</style>
<style>
.van-cell{ padding: 7px 15px; }
</style>