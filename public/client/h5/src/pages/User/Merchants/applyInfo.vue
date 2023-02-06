<template>
    <div class="apply-content">
        <section class="info_list">
            <div class="info_item info_title">{{$t('lang.basic_info')}}</div>
            <div class="info_item">
                <span class="item_label">{{$t('lang.truename')}}</span>
                <span>{{applyInfo.is_personal == 0 ? applyInfo.contactName : applyInfo.name}}</span>
            </div>
            <div class="info_item">
                <span class="item_label">{{$t('lang.phone_number')}}</span>
                <span class="item_val">{{applyInfo.is_personal == 0 ? applyInfo.contactPhone : applyInfo.mobile}}</span>
            </div>
        </section>

        <section class="info_list">
            <div class="info_item info_title">{{$t('lang.main_info')}}</div>
            <div class="info_item">
                <span class="item_label">{{$t('lang.merchants_storetype')}}</span>
                <span>{{applyInfo.is_personal == 0 ? $t('lang.merchants_compan') : $t('lang.person')}}</span>
            </div>
            <div class="info_item" v-if="applyInfo.is_personal == 0">
                <span class="item_label">{{$t('lang.sup_company_name')}}</span>
                <span class="item_val">{{applyInfo.company}}</span>
            </div>
            <div class="info_item" v-if="applyInfo.is_personal == 0">
                <span class="item_label">{{$t('lang.merchants_companytel')}}</span>
                <span class="item_val">{{applyInfo.company_contactTel}}</span>
            </div>
            <div class="info_item">
                <span class="item_label">{{$t('lang.merchants_cartinfo')}}</span>
                <img class="item_img" :src="img" alt="" v-for="(img, ind) in imgList" :key="ind" @click="previewImg(ind)">
            </div>
        </section>

        <section class="info_list">
            <div class="info_item info_title">{{$t('lang.shop_detail')}}</div>
            <div class="info_item">
                <span class="item_label">{{$t('lang.merchants_storetype10')}}</span>
                <span>{{storeType}}</span>
            </div>
            <div class="info_item">
                <span class="item_label">{{$t('lang.shop_name')}}</span>
                <span class="item_val">{{shopInfo.rz_shop_name}}</span>
            </div>
            <div class="info_item">
                <span class="item_label">{{$t('lang.merchants_loginname')}}</span>
                <span class="item_val">{{shopInfo.hope_login_name}}</span>
            </div>
            <div class="info_item">
                <span class="item_label">{{$t('lang.merchants_catename')}}</span>
                <span class="item_val">{{$t('lang.merchants_seletecate')}}</span>
            </div>
            <section class="manage_type" v-for="(item, index) in allCate" :key="index">
                <div class="manage_item">
                    <span class="item_label">{{$t('lang.merchants_catetype1')}}</span>
                    <span class="item_val">{{item.parent_name}}</span>
                </div>
                <div class="manage_item">
                    <span class="item_label">{{$t('lang.merchants_catetype2')}}</span>
                    <span class="item_val">{{item.cat_name}}</span>
                </div>
            </section>
        </section>
        <DscLoading :dscLoading="isLoading"></DscLoading>
    </div>
</template>

<script>
import Vue from 'vue';
import DscLoading from '@/components/DscLoading';
import { Toast, ImagePreview } from 'vant';
Vue.use(Toast).use(ImagePreview);
export default {
    components: {
        DscLoading
    },
    data() {
        return {
            applyInfo: {},
            shopInfo: {},
            allCate: [],
            imgList: [],
            storeType: '',
            isLoading: false
        }
    },
    created() {
        this.getInfo()
    },
    methods: {
        previewImg(i) {
            ImagePreview({
                images: this.imgList,
                startPosition: i
            });
        },
        async getInfo() {
            this.isLoading = true;

            const { data, status } = await this.$store.dispatch('getMerchantsInfo')

            if (status == 'success') {
                this.isLoading = false;

                if (!data.steps) this.$router.go(-1);

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
                Toast(this.$t('lang.jk_error'))
            }
        }
    }
}
</script>

<style lang="scss" scoped>
.apply-content {
    padding: 1rem;
    font-size: 14px;
    .info_list {
        padding: 1.5rem;
        border-radius: 1rem;
        background-color: #fff;
        &:nth-child(n + 2) {
            margin-top: 1rem;
        }
        .info_title {
            font-size: 15px;
            font-weight: bold;
        }
        .info_item {
            display: flex;
            .item_label {
                min-width: 8rem;
                align-self: flex-start;
            }
            .item_val {
                flex: 1;
            }
            .item_img {
                width: 5rem;
                height: 5rem;
                object-fit: contain;
                margin-right: 1rem;
                border-radius: 0.5rem;
            }
            &:nth-child(n + 2) {
                margin-top: 1.5rem;
            }
        }
    }
    .manage_type {
        padding: 1.5rem 1rem;
        border-radius: 0.5rem;
        margin-top: 1rem;
        background-color: #FAFAFA;
        .manage_item {
            display: flex;
            &:nth-child(n + 2) {
                margin-top: 1.5rem;
            }
            .item_label {
                min-width: 7rem;
            }
            .item_val {
                flex: 1;
            }
        }
    }
}
</style>