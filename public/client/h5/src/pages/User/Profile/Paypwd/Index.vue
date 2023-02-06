<template>
	<div class="con con_main">
		<van-panel :title="$t('lang.edit_pay_pwd')" class="my-bottom" v-if="paypwd_id > 0"/>
		<van-panel :title="$t('lang.open_pay_pwd')" class="my-bottom" v-else/>
		<van-field type="tel" :label="$t('lang.label_phone_number')" :placeholder="$t('lang.enter_mobile')" v-model="mobile" maxlength="11" v-bind:disabled="isDisabled"></van-field>
		<van-field v-model="imgverifyValue" center clearable :label="$t('lang.label_pic_code')" maxlength="4" :placeholder="$t('lang.enter_pic_code')" v-if="captcha">
            <span class="code-box" slot="button" @click="clickCaptcha"><img :src="captcha"></span>
        </van-field>
        <van-field v-model="sms" type="tel" center clearable :label="$t('lang.label_sms_code')" :placeholder="$t('lang.get_sms_code')">
            <van-button slot="button" size="small" type="primary" @click="sendVerifyCode" v-if="button_type">{{$t('lang.send_sms_code')}}
            </van-button>
            <van-button slot="button" size="small" type="disabled" v-else>{{ button_text }}</van-button>
        </van-field>
        <van-field type="number" :label="$t('lang.label_pay_pwd')" :placeholder="$t('lang.enter_pay_pwd')" v-model="pwd" maxlength="6"></van-field>
        <div class="tips">{{ notice }}</div>
        <div class="filter-btn">
        	<van-button type="primary" bottom-action @click="submitBtn" v-if="paypwd_id > 0">{{$t('lang.mod')}}</van-button>
        	<van-button type="primary" bottom-action @click="submitBtn" v-else>{{$t('lang.enabled')}}</van-button>
        </div>
        <CommonNav></CommonNav>
	</div>
</template>

<script>
import qs from 'qs'
import {
    Cell,
    CellGroup,
    Button,
    Popup,
    Field,
    Uploader,
    Icon,
    Toast,
    Panel,
} from 'vant'
import CommonNav from '@/components/CommonNav'

export default{
	data() {
		return{
			mobile:'',
			is_mobile:0,
			imgverifyValue: '',
	        sms: '',
	        button_text: this.$t('lang.send_again_60'),
            send_again:this.$t('lang.send_again'),
	        button_type: true,
	        isDisabled:true,
	        pwd:'',
	        paypwd_id:0,
	        notice:''
		}
	},
    components: {
        [Cell.name]: Cell,
        [CellGroup.name]: CellGroup,
        [Button.name]: Button,
        [Popup.name]: Popup,
        [Field.name]: Field,
        [Uploader.name]: Uploader,
        [Icon.name]: Icon,
        [Toast.name]: Toast,
        [Panel.name]: Panel,
        CommonNav
    },
    created() {
    	this.$store.dispatch('setImgVerify')

    	this.$http.get(`${window.ROOT_URL}api/accountsafe/add_paypwd`).then(res => {
    		let data = res.data.data;

            if(data.code == 0){
                if(data.user_info.mobile_phone){
                    this.mobile = data.user_info.mobile_phone
                    this.isDisabled = true
                }else{
                    this.isDisabled = false
                    this.is_mobile = 1
                }

                this.paypwd_id = data.users_paypwd && data.users_paypwd.paypwd_id ? data.users_paypwd.paypwd_id : 0
                this.notice = data.notice
            }else{
                this.isDisabled = false
                this.is_mobile = 1
            }
    	})
    },
    computed: {
    	captcha() {
            return this.$store.state.imgVerify.captcha
        },
        client() {
            return this.$store.state.imgVerify.client
        }
    },
    methods: {
    	clickCaptcha() {
            this.$store.dispatch('setImgVerify')
        },
        sendVerifyCode() {
            let o = {
                captcha: this.imgverifyValue,
                client: this.client,
                mobile: this.mobile,
                is_mobile: this.is_mobile,
            }

            this.$store.dispatch('setSendVerify', o).then(res => {
                if (res == 'success') {
                    this.button_type = false
                    let second = 60
                    const timer = setInterval(() => {
                        second--
                        if (second) {
                            this.button_text = this.send_again + '('+ second +'s)'
                        } else {
                            this.button_type = true
                            clearInterval(timer);
                        }
                    }, 1000)
                }
            })
        },
        checkMobile() {
            let rule = /^(\d{10})$/
            if (rule.test(this.mobile)) {
                return true
            } else {
                return false
            }
        },
        submitBtn(){
            if(this.sms == ''){
                Toast(this.$t('lang.get_sms_code_notic'))
                return false
            }

            if(!this.checkMobile()){
                Toast(this.$t('lang.phone_number_format'))
                return false
            }

            if(this.client == ''){
                Toast(this.$t('lang.captcha_img'))
                return false
            }

            if(this.pwd.length < 6){
                Toast(this.$t('lang.pay_pwd_6'));
                return false
            }

        	let o = {
        		paypwd_id:this.paypwd_id,
        		pay_paypwd:this.pwd,
        		client: this.client,
                code: this.sms,
                mobile:this.mobile
        	}

        	this.$http.post(`${window.ROOT_URL}api/accountsafe/edit_paypwd`,qs.stringify(o)).then(res => {
        		let data = res.data;
                if(data.status == 'success'){
                    Toast(data.data.msg);
                    if (data.data.code == 0) {
                        this.$router.push({
                            name:'accountsafe'
                        })
                    }
                }else{
                    Toast(data.errors.message);
                }
        	})
        }
    }
}
</script>
<style>
.tips{
	font-size: 1.3rem;
    color: #888;
    line-height: 1.5;
    padding: 1.1rem;
}
</style>
