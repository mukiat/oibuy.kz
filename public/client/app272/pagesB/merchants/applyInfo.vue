<template>
    <view class="apply-content">
        <view class="info_list">
            <view class="info_item info_title">{{$t('lang.basic_info')}}</view>
            <view class="info_item">
                <text class="item_label">{{$t('lang.truename')}}</text>
                <text>{{applyInfo.is_personal == 0 ? applyInfo.contactName : applyInfo.name}}</text>
            </view>
            <view class="info_item">
                <text class="item_label">{{$t('lang.phone_number')}}</text>
                <text class="item_val">{{applyInfo.is_personal == 0 ? applyInfo.contactPhone : applyInfo.mobile}}</text>
            </view>
        </view>

        <view class="info_list">
            <view class="info_item info_title">{{$t('lang.main_info')}}</view>
            <view class="info_item">
                <text class="item_label">{{$t('lang.merchants_storetype')}}</text>
                <text>{{applyInfo.is_personal == 0 ? $t('lang.merchants_compan') : $t('lang.person')}}</text>
            </view>
            <view class="info_item" v-if="applyInfo.is_personal == 0">
                <text class="item_label">{{$t('lang.sup_company_name')}}</text>
                <text class="item_val">{{applyInfo.company}}</text>
            </view>
            <view class="info_item" v-if="applyInfo.is_personal == 0">
                <text class="item_label">{{$t('lang.merchants_companytel')}}</text>
                <text class="item_val">{{applyInfo.company_contactTel}}</text>
            </view>
            <view class="info_item">
                <text class="item_label">{{$t('lang.merchants_cartinfo')}}</text>
                <image class="item_img" :src="img" v-for="(img, ind) in imgList" :key="ind" @click="previewImage(ind)"></image>
            </view>
        </view>

        <view class="info_list">
            <view class="info_item info_title">{{$t('lang.shop_detail')}}</view>
            <view class="info_item">
                <text class="item_label">{{$t('lang.merchants_storetype10')}}</text>
                <text>{{storeType}}</text>
            </view>
            <view class="info_item">
                <text class="item_label">{{$t('lang.shop_name')}}</text>
                <text class="item_val">{{shopInfo.rz_shop_name}}</text>
            </view>
            <view class="info_item">
                <text class="item_label">{{$t('lang.merchants_loginname')}}</text>
                <text class="item_val">{{shopInfo.hope_login_name}}</text>
            </view>
            <view class="info_item">
                <text class="item_label">{{$t('lang.merchants_catename')}}</text>
                <text class="item_val">{{$t('lang.merchants_seletecate')}}</text>
            </view>
            <view class="manage_type" v-for="(item, index) in allCate" :key="index">
                <view class="manage_item">
                    <text class="item_label">{{$t('lang.merchants_catetype1')}}</text>
                    <text class="item_val">{{item.parent_name}}</text>
                </view>
                <view class="manage_item">
                    <text class="item_label">{{$t('lang.merchants_catetype2')}}</text>
                    <text class="item_val">{{item.cat_name}}</text>
                </view>
            </view>
        </view>
		
		<dsc-common-nav></dsc-common-nav>
		
        <dsc-loading :dscLoading="dscLoading"></dsc-loading>
    </view>
</template>

<script>
import dscCommonNav from '@/components/dsc-common-nav.vue';
export default {
    components: {},
    data() {
        return {
            applyInfo: {},
            shopInfo: {},
            allCate: [],
            imgList: [],
            storeType: '',
            dscLoading: false
        }
    },
	components:{
		dscCommonNav
	},
    created() {
        this.getInfo()
    },
    methods: {
		previewImage() {
			let that = this;
			uni.previewImage({
				current: 1,
				urls: that.imgList,
				indicator: 'number',
				longPressActions: {
					itemList: ['发送给朋友', '保存图片', '收藏'],
					success: function(data) {
						console.log('选中了第' + (data.tapIndex + 1) + '个按钮,第' + (data.index + 1) + '张图片');
					},
					fail: function(err) {
						console.log(err.errMsg);
					}
				}
			});
		},
        async getInfo() {
            this.dscLoading = true;

            const { data, status } = await this.$store.dispatch('getMerchantsInfo')

            if (status == 'success') {
                this.dscLoading = false;

                //if (!data.steps) this.$router.go(-1);

                this.applyInfo = data.steps;

                this.shopInfo = data.shop;

                const arr = [];

                if (this.applyInfo.is_personal == 0) {
                    const img1 = this.applyInfo.legal_person_fileImg;
                    const img2 = this.applyInfo.license_fileImg;
                    if (img1) arr.push(img1);
                    if (img2) arr.push(img2);
                } else {
                    const img1 = this.applyInfo.id_card_img_one_fileImg;
                    const img2 = this.applyInfo.id_card_img_two_fileImg;
                    const img3 = this.applyInfo.id_card_img_three_fileImg;
                    if (img1) arr.push(img1);
                    if (img2) arr.push(img2);
                    if (img3) arr.push(img3);
                }

                this.imgList = arr;

                if (data.shop.shoprz_type == 1) this.storeType = this.$t('lang.flagship_store');
                else if (data.shop.shoprz_type == 2) this.storeType = this.$t('lang.exclusive_shop');
                else if (data.shop.shoprz_type == 3) this.storeType = this.$t('lang.franchised_store');

                const cateList = [];

                const category = data.category || {};

                for (const key in category) {
                    if (category.hasOwnProperty(key)) {
                        cateList.push(category[key])
                    }
                }

                this.allCate = cateList;
            } else {
				uni.showToast({ title: this.$t('lang.jk_error'), icon: 'none' });
            }
        }
    }
}
</script>

<style lang="scss" scoped>
.apply-content {
    padding: 20upx;
    .info_list {
        padding: 30upx;
        border-radius: 20upx;
        background-color: #fff;
        &:nth-child(n + 2) {
            margin-top: 20upx;
        }
        .info_title {
            font-size: 15px;
            font-weight: bold;
        }
        .info_item {
            display: flex;
            .item_label {
                min-width: 160upx;
                align-self: flex-start;
            }
            .item_val {
                flex: 1;
            }
            .item_img {
                width: 100upx;
                height: 100upx;
                object-fit: contain;
                margin-right: 20upx;
                border-radius: 10upx;
            }
            &:nth-child(n + 2) {
                margin-top: 30upx;
            }
        }
    }
    .manage_type {
        padding: 30upx 20upx;
        border-radius: 10upx;
        margin-top: 20upx;
        background-color: #FAFAFA;
        .manage_item {
            display: flex;
            &:nth-child(n + 2) {
                margin-top: 30upx;
            }
            .item_label {
                min-width: 140upx;
            }
            .item_val {
                flex: 1;
            }
        }
    }
}
</style>