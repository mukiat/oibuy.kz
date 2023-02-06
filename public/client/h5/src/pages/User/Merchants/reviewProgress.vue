<template>
    <div class="review_progress_content">
        <div class="review_progress_info">
            <template v-if="shop.merchants_audit == 0">
                <img src="../../../assets/img/merchant_ico3.png" alt="">
                <h3>{{$t('lang.merchants_awaitapply')}}...</h3>
                <p class="tips">{{$t('lang.merchants_awaitapply1')}}</p>
            </template>
            <template v-if="shop.merchants_audit == 1">
                <img src="../../../assets/img/merchant_ico5.png" alt="">
                <h3>{{$t('lang.merchants_audit_1')}}</h3>
                <p class="style_red">{{$t('lang.label_shop_name')}}{{shop.rz_shop_name}}</p>
                <p class="tips">{{$t('lang.merchants_awaitapply2')}}</p>
            </template>
            <template v-if="shop.merchants_audit == 2">
                <img src="../../../assets/img/merchant_ico4.png" alt="">
                <h3>{{$t('lang.merchants_awaitapply3')}}</h3>
                <p class="style_red">{{$t('lang.merchants_awaitapply3')}}</p>
                <p class="tips">{{$t('lang.refusal_cause')}}：{{shop.merchants_message}}</p>
            </template>
        </div>
        <div class="btns" v-if="shop.merchants_audit == 0">
            <div class="btn_item gray_btn" @click="replacePage('home')">{{$t('lang.home_back')}}</div>
            <div class="btn_item red_btn" @click="linkHref('applyInfo')">{{$t('lang.apply_info')}}</div>
        </div>
        <div class="btns" v-if="shop.merchants_audit == 1">
            <div class="btn_item red_btn" @click="replacePage('home')">{{$t('lang.home_back')}}</div>
        </div>
        <div class="btns" v-if="shop.merchants_audit == 2">
            <div class="btn_item gray_btn" @click="replacePage('home')">{{$t('lang.home_back')}}</div>
            <div class="btn_item red_btn" @click="reapply('merchants')" v-if="shop.steps_audit != 1">{{$t('lang.new_registration')}}</div>
        </div>

        <DscLoading :dscLoading="isLoading"></DscLoading>
    </div>
</template>

<script>
import Vue from 'vue';
import { Toast } from 'vant';

import DscLoading from '@/components/DscLoading';

Vue.use(Toast);
export default {
    components: {
        DscLoading
    },
    data() {
        return {
            shop: {},
            isLoading: false
        }
    },
    created() {
        const merchantInfo = JSON.parse(window.localStorage.getItem('merchantInfo')) || {};

        merchantInfo.step = 1;

        const data = JSON.stringify(merchantInfo);

        window.localStorage.setItem('merchantInfo', data);
        
        this.audit();
    },
    methods: {
        async audit(){
            
            this.isLoading = true;

            const { data, status } = await this.$store.dispatch('getMerchantsAuditHandle');

            this.isLoading = false;

            if (status == 'success') {
                
                if (data) {
                    this.shop = data.shop
                } else {
                    window.localStorage.removeItem('merchantInfo');
                    window.localStorage.removeItem('merchantsData');
                    this.replacePage('user');
                }
                
            } else {
                Toast(this.$t('lang.post_server_busy'))
            }
        },
        linkHref(name) {
            this.$router.push({name})
        },
        replacePage(name) {
            this.$router.replace({name})
        },
        reapply(cName) {
            const merchantInfo = JSON.parse(window.localStorage.getItem('merchantInfo')) || {};

            merchantInfo.step = 2;

            const data = JSON.stringify(merchantInfo);

            window.localStorage.setItem('merchantInfo', data);

            window.localStorage.removeItem('merchantsData');

            this.replacePage(cName);
        }
    }
}
</script>

<style lang="scss" scoped>
.review_progress_content {
    height: 100vh;
    font-size: 14px;
    background-color: #fff;
    .review_progress_info {
        display: flex;
        flex-direction: column;
        align-items: center;
        img {
            width: 50%;
        }
        h3 {
            font-size: 16px;
            font-weight: bold;
        }
        .style_red {
            margin-top: 1.5rem;
            text-align: center;
            color: #FF1D36;
        }
        .tips {
            max-width: 75%;
            margin: 1.5rem 0 2rem;
            text-align: center;
            color: #999;
        }
    }
    .btns {
        display: flex;
        justify-content: space-around;
        padding: 1rem;
        font-size: 15px;
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