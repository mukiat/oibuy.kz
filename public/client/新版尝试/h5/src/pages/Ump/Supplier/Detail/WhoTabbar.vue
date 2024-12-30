<template>
    <div class="footer-nav">
        <van-tabbar class="ect-tabbar" v-model="active" fixed>
            <van-tabbar-item icon="home-o" to="/supplier">{{$t('lang.supplier_home_page')}}</van-tabbar-item>
            <van-tabbar-item icon="apps-o" to="/supplier/list">{{$t('lang.supplier_category')}}</van-tabbar-item>
            <van-tabbar-item icon="cart" to="/supplier/cart">{{$t('lang.purchase_order')}}</van-tabbar-item>
			<van-tabbar-item icon="idcard" to="/supplier/buy">{{$t('lang.purchase_info')}}</van-tabbar-item>
            <van-tabbar-item icon="orders-o" to="/supplier/orderlist">{{$t('lang.purchase_note')}}</van-tabbar-item>
        </van-tabbar>
    </div>
</template>

<script>
    import { Tabbar, TabbarItem } from 'vant';
    export default {
        name: "who-tabbar",

        components: {
            [Tabbar.name]: Tabbar, 
            [TabbarItem.name]: TabbarItem
        },

        data() {
            return {
                active: 0
            }
        },
        mounted: function () {
            let current = this.$route.path.substr(1)
            let nav = ['supplier', 'supplier/list', 'supplier/cart', 'supplier/buy']
            this.active = nav.indexOf(current)
        }
    }
</script>