<template>
    <div class="basic_info_content">
        <div class="steps">
            <div class="step_item active_item">
                <div class="index_box">1</div>
                <span>{{$t('lang.basic_info')}}</span>
            </div>
            <div class="line_box"></div>
            <div class="step_item">
                <div class="index_box">2</div>
                <span>{{$t('lang.main_info')}}</span>
            </div>
            <div class="line_box"></div>
            <div class="step_item">
                <div class="index_box">3</div>
                <span>{{$t('lang.shop_detail')}}</span>
            </div>
        </div>

        <div class="main_box">
            <div class="title item">{{$t('lang.merchants_applytitle')}}</div>
            <div class="input_box item">
                <label>{{$t('lang.truename')}}</label>
                <input type="text" class="flex_1" v-model="uInfo.uname" :placeholder="$t('lang.merchants_toast5')">
            </div>
            <div class="input_box item border_top">
                <label>{{$t('lang.phone_number')}}</label>
                <input type="number" class="flex_1" v-model="uInfo.mobile" :placeholder="$t('lang.enter_contact_phone')">
            </div>
        </div>

        <div class="btns">
            <div class="btn_item gray_btn" @click="goBack">{{$t('lang.merchants_goback')}}</div>
            <div class="btn_item red_btn" @click="goNext">{{$t('lang.next_step')}}</div>
        </div>
    </div>
</template>

<script>
import Vue from 'vue';
import { Toast } from 'vant';

Vue.use(Toast);
export default {
    props: {
        userInfo: {
            type: Object,
            default: function () {
                return {
                    uname: '',
                    mobile: ''
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
        goBack() {
            this.$set(this.uInfo, 'step', 2)

            this.$emit('go-next', this.uInfo)
        },
        goNext() {
            let rule = /^(\d{10})$/;

            if (!this.uInfo.uname.trim()) return Toast(this.$t('lang.merchants_toast6'));
            if (!this.uInfo.mobile.trim()) return Toast(this.$t('lang.merchants_toast7'));
            if (!rule.test(this.uInfo.mobile)) return Toast(this.$t('lang.merchants_toast8'));

            this.$set(this.uInfo, 'step', 4)

            this.$emit('go-next', this.uInfo)

        }
    }
}
</script>

<style lang="scss" scoped>
.basic_info_content {
    font-size: 14px;
    padding: 1rem;
    padding-bottom: 6rem;
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
}
</style>