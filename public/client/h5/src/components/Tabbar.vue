<template>
    <van-tabbar class="tabbar" v-model="active" fixed>
        <van-tabbar-item icon="wap-home" to="/home">{{$t('lang.home')}}</van-tabbar-item>
        <van-tabbar-item icon="wap-nav" to="/catalog">{{$t('lang.category')}}</van-tabbar-item>
        <van-tabbar-item icon="search" to="/search">{{$t('lang.discover')}}</van-tabbar-item>
        <van-tabbar-item icon="cart" to="/cart" info="99">{{$t('lang.cart')}}</van-tabbar-item>
        <van-tabbar-item icon="contact" to="/user" dot>{{$t('lang.my')}}</van-tabbar-item>
    </van-tabbar>
</template>

<script>
import { Tabbar, TabbarItem } from 'vant';
export default {
    name: "tabbar",

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
        let nav = ['home', 'catalog', 'search', 'cart', 'user']
        this.active = nav.indexOf(current)
    },
    computed:{
        token(){
            return localStorage.getItem('token')
        }
    }
}
</script>

<style scoped>
    .tabbar {
        -webkit-box-shadow: 0 0 10px 0 hsla(0, 6%, 58%, .6);
        box-shadow: 0 0 10px 0 hsla(0, 6%, 58%, .6);
    }
</style>