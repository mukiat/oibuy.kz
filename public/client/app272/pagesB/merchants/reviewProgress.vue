<template>
    <view class="review_progress_content">
        <view class="review_progress_info">
            <block v-if="shop.merchants_audit == 0">
                <image :src="imagePath.merchantIco3" mode="widthFix"></image>
                <view class="tit">{{$t('lang.merchants_awaitapply')}}...</view>
                <view class="tips">{{$t('lang.merchants_awaitapply1')}}</view>
            </block>
            <block v-if="shop.merchants_audit == 1">
               <image :src="imagePath.merchantIco5" mode="widthFix"></image>
                <view class="tit">{{$t('lang.merchants_audit_1')}}</view>
                <view class="style_red">{{$t('lang.label_shop_name')}}{{shop.rz_shop_name}}</view>
				<view class="tips">{{$t('lang.merchants_awaitapply2')}}</view>
            </block>
            <block v-if="shop.merchants_audit == 2">
                <image :src="imagePath.merchantIco4" mode="widthFix"></image>
                <view class="tit">{{$t('lang.merchants_awaitapply3')}}</view>
                <view class="style_red">{{$t('lang.merchants_awaitapply3')}}</view>
                <view class="tips">{{$t('lang.refusal_cause')}}：{{shop.merchants_message}}</view>
            </block>
        </view>
        <view class="btns" v-if="shop.merchants_audit == 0">
            <view class="btn_item gray_btn" @click="replacePage">{{$t('lang.home_back')}}</view>
            <view class="btn_item red_btn" @click="linkHref">{{$t('lang.apply_info')}}</view>
        </view>
        <view class="btns" v-if="shop.merchants_audit == 1">
            <view class="btn_item red_btn" @click="replacePage">{{$t('lang.home_back')}}</view>
        </view>
        <view class="btns" v-if="shop.merchants_audit == 2">
            <view class="btn_item gray_btn" @click="replacePage">{{$t('lang.home_back')}}</view>
            <view class="btn_item red_btn" @click="reapply" v-if="shop.steps_audit != 1">{{$t('lang.new_registration')}}</view>
        </view>

        <dsc-loading :dscLoading="dscLoading"></dsc-loading>
    </view>
</template>

<script>
export default {
    components: {
        
    },
    data() {
        return {
            shop: {},
            dscLoading: false
        }
    },
    created() {
		const merchantInfo = uni.getStorageSync("merchantInfo") || {};
        
		merchantInfo.step = 1;
		
		uni.setStorageSync('merchantInfo',merchantInfo);
        
		this.audit();
    },
    methods: {
        async audit(){
            this.dscLoading = true;
            const { data, status } = await this.$store.dispatch('getMerchantsAuditHandle');
            this.dscLoading = false;
            if (status == 'success') {
                if (data) {
                    this.shop = data.shop
                } else {
					uni.removeStorageSync("merchantInfo");
					uni.removeStorageSync("merchantsData");
					//返回用户中心
					uni.reLaunch({ url:'/pages/user/user' });
                }
            } else {
				uni.showToast({ title: this.$t('lang.post_server_busy'), icon: 'none' });
            }
        },
        linkHref() {
			uni.navigateTo({ url:'/pagesB/merchants/applyInfo' });
        },
		//返回首页
        replacePage() {
			uni.reLaunch({ url:'/pages/index/index' });
        },
        reapply() {
            const merchantInfo = uni.getStorageSync("merchantInfo") || {};

            merchantInfo.step = 2;

			uni.setStorageSync('merchantInfo',merchantInfo);
			uni.removeStorageSync("merchantsData");
			
			//跳转重新申请
			uni.reLaunch({ url:'/pagesB/merchants/merchants' });
        }
    }
}
</script>

<style lang="scss" scoped>
.review_progress_content {
    height: 100vh;
    background-color: #fff;
    .review_progress_info {
        display: flex;
        flex-direction: column;
        align-items: center;
        image {
            width: 50%;
        }
        .tit {
            font-size: 32upx;
            font-weight: bold;
        }
        .style_red {
            margin-top: 30upx;
            text-align: center;
            color: #FF1D36;
        }
        .tips {
            max-width: 75%;
            margin: 30upx 0 40upx;
            text-align: center;
            color: #999;
        }
    }
    .btns {
        display: flex;
        justify-content: space-around;
        padding: 20upx;
        font-size: 15px;
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