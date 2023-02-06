<template>
    <view class="enter_type_content">
        <image class="bg_img" :src="imagePath.merchantIco" mode="widthFix"></image>
        <view class="items">
            <view class="item" @click="selectHandle(1)">
                <image src="../../../static/merchant_ico1.png" mode="widthFix"></image>
                <view class="title_box">
                    <view class="tit">{{$t('lang.merchants_maintitle6')}}</view>
                    <text>{{$t('lang.merchants_maintitle7')}}</text>
                </view>
                <text class="iconfont icon-more"></text>
            </view>
            <view class="item" @click="selectHandle(2)">
                <image src="../../../static/merchant_ico2.png" mode="widthFix"></image>
                <view class="title_box">
                    <view class="tit">{{$t('lang.merchants_storetype1')}}</view>
                    <text>{{$t('lang.merchants_maintitle8')}}</text>
                </view>
                <text class="iconfont icon-more"></text>
            </view>
        </view>
    </view>
</template>

<script>
export default {
    props: {
        userInfo: {
            type: Object,
            default: function () {
                return {
                    enterType: ''
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
        selectHandle(enterType){
            this.$set(this.uInfo, 'step', 3);
            this.$set(this.uInfo, 'enterType', enterType);
            this.$emit('go-next', this.uInfo)
        }
    }
}
</script>

<style lang="scss" scoped>
.enter_type_content {
    .bg_img {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
    }
    .items {
        position: relative;
        margin-top: 250upx;
        z-index: 1;
        padding: 0 20upx;
    }
    .item {
        display: flex;
        align-items: center;
        padding: 40upx;
        border-radius: 20upx;
        background-color: #fff;
        image {
            width: 100upx;
			height: 100upx;
        }
        .title_box {
            flex: 1;
            font-size: 14px;
            margin-left: 40upx;
            h3 {
                font-size: 15px;
                font-weight: bold;
                margin-bottom: 20upx;
            }
            text {
                color: #999;
            }
        }
        .iconfont {
            font-size: 15px;
            color: #999;
        }
        &:last-child {
            margin-top: 20upx;
        }
    }
}
</style>