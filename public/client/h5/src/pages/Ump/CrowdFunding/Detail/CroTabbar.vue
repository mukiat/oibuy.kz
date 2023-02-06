<template>
    <div class="footer-nav">
        <van-tabbar class="ect-tabbar" v-model="active" fixed>
            <van-tabbar-item icon="home-o" to="/crowdfunding">{{$t('lang.square')}}</van-tabbar-item>
            <van-tabbar-item icon="records" to="/crowdfunding/order">{{$t('lang.square_order')}}</van-tabbar-item>
            <van-tabbar-item icon="contact" to="/crowdfunding/user">{{$t('lang.centre')}}</van-tabbar-item>
        </van-tabbar>
    </div>
</template>
<script>
    import {
        Tabbar,
        TabbarItem
    } from 'vant';
    export default {
        name: "team-tabbar",
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
            let nav = ['crowdfunding', 'crowdfunding/order', 'crowdfunding/user']
            this.active = nav.indexOf(current)
        }
    }
</script>
