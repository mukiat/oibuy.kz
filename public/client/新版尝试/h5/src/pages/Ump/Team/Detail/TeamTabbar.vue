<template>
    <div class="footer-nav">
        <van-tabbar class="ect-tabbar" v-model="active" :z-index="999" fixed>
            <van-tabbar-item  to="/team">
                <i class="iconfont icon-wodetuandui show text-center tab-com-font"/>
                {{$t('lang.my_team')}}
            </van-tabbar-item>
            <van-tabbar-item  to="/team/rank">
                <i class="iconfont icon-paixing show text-center tab-com-font"/>
                {{$t('lang.toprank')}}
            </van-tabbar-item>
            <van-tabbar-item  to="/team/order">
             <i class="iconfont icon-gerenzhongxin show text-center tab-com-font"/>
            {{$t('lang.my_pin_team')}}</van-tabbar-item>
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
            let nav = ['team', 'team/rank', 'team/order']
            this.active = nav.indexOf(current)
        }
    }
</script>
