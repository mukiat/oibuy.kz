<template>
    <div class="discover_content">
        <van-tabs v-model="active" color="#000000" sticky @change="onChange">
            <van-tab :title="item.nav_name" v-for="(item,index) in list" :key="index">
                <dsc-community v-if="item.nav_name == 'Пост'" />
                <dsc-shop v-if="item.nav_name == 'Дүкен'" />
                <dsc-video-list v-if="item.nav_name == 'Видео'" />
            </van-tab>
        </van-tabs>
        <ec-tab-down></ec-tab-down>
    </div>
</template>

<script>
import qs from 'qs'
import dscTabs from '@/components/dsc-tabs/dsc-tabs.vue';
import {
    Tab,
    Tabs
} from 'vant'

import EcTabDown from "@/components/visualization/tab-down/Frontend";

import community from '@/components/dsc-community/community.vue';

import dscSearch from '@/pages/Goods/Search.vue'
import dscShop from '@/pages/Shop/Index.vue'
import dscVideoList from '@/pages/Goods/VideoList.vue'

export default{
    data(){
        return {
            list:[],
            active:this.$route.query.type ? (this.$route.query.type < 4 ? this.$route.query.type : 0) : 0
        }
    },
    components:{
        [Tab.name] : Tab,
        [Tabs.name] : Tabs,
        'dsc-community': community,
        'ec-tab-down': EcTabDown,
        dscShop,
        dscVideoList
    },
    created(){
        this.onTabList();
    },
    methods:{
        onTabList(){
            this.$http.get(`${window.ROOT_URL}api/shop/page-nav`, {params:{
                device:'h5'
            }}).then(({data})=>{
                if(data.status == 'success'){
                    this.list = data.data;
                }
            })
        },
        onChange(e){
            this.$router.push({
                name:'integration',
                query:{
                    type:e
                }
            })
        }
    }
}    
</script>
<style>
#app .discover_content .van-tabs__content{ background-color: #f4f4f4; }
.discover_content {
    padding-bottom: 5rem;
    padding-bottom: calc(env(safe-area-inset-bottom) + 5rem);
}
#app .discover_content .van-tab {
    font-size:1.6rem;
    font-family:PingFang SC;
    font-weight:500;
}
#app .discover_content .van-tab--active span:after {
    content: " ";
    position: absolute;
    display: block;
    width: 1.9rem;
    height: 0.4rem;
    border-radius: 0.2rem;
    background: #FA2A29;
    left: 50%;
    margin: 0;
    transform: translateX(-50%);
    bottom: 0px;
}
</style>
