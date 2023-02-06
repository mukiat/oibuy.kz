<template>
    <view class="basic_info_content">
        <view class="steps">
            <view class="step_item active_item">
                <view class="index_box">1</view>
                <text>{{$t('lang.basic_info')}}</text>
            </view>
            <view class="line_box"></view>
            <view class="step_item">
                <view class="index_box">2</view>
                <text>{{$t('lang.main_info')}}</text>
            </view>
            <view class="line_box"></view>
            <view class="step_item">
                <view class="index_box">3</view>
                <text>{{$t('lang.shop_detail')}}</text>
            </view>
        </view>

        <view class="main_box">
            <view class="title item">{{$t('lang.merchants_applytitle')}}</view>
            <view class="input_box item">
                <text class="text">{{$t('lang.truename')}}</text>
                <input type="text" class="input flex_1" v-model="uInfo.uname" :placeholder="$t('lang.merchants_toast5')">
            </view>
            <view class="input_box item border_top">
                <text class="text">{{$t('lang.phone_number')}}</text>
                <input type="number" class="input flex_1" v-model="uInfo.mobile" :placeholder="$t('lang.enter_contact_phone')">
            </view>
        </view>

        <view class="btns">
            <view class="btn_item gray_btn" @click="goBack">{{$t('lang.merchants_goback')}}</view>
            <view class="btn_item red_btn" @click="goNext">{{$t('lang.next_step')}}</view>
        </view>
    </view>
</template>

<script>
import Vue from 'vue';
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
			
            if (!this.uInfo.uname.trim()) return uni.showToast({ title: this.$t('lang.merchants_toast6'), icon: 'none' });
            if (!this.uInfo.mobile.trim()) return uni.showToast({ title: this.$t('lang.merchants_toast7'), icon: 'none' });
            if (!rule.test(this.uInfo.mobile)) return uni.showToast({ title: this.$t('lang.merchants_toast8'), icon: 'none' });

            this.$set(this.uInfo, 'step', 4)

            this.$emit('go-next', this.uInfo)

        }
    }
}
</script>

<style lang="scss" scoped>
.basic_info_content {
    padding: 20upx;
    padding-bottom: 100upx;
    .steps {
        display: flex;
        justify-content: space-between;
        padding: 40upx 40upx 30upx;
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
}
</style>