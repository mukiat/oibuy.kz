<template>
    <footer class='tab-down' :style="oPosition">
        <ul>
            <template v-if="authority == 'view'">
                <li v-for="(item,index) in list" :key="index" :class="{active:routeName == item.url}" @click.stop="outerHref(item.url)">
                    <i><img :src="routeName == item.url ? item.icon_cur : item.icon" class="img"></i>
                    <span>{{ item.desc }}</span>
                </li>
            </template>
            <template v-else>
                <li v-for="(item,index) in list" :key="index" :class="{active:aActive[index]}">
                    <i></i>
                    <span>{{ item.desc }}</span>
                </li>
            </template>
        </ul>
    </footer>
</template>

<script>
//nodes library
import qs from 'qs'

export default {
    name: 'tab-down',
    props: ['data', 'preview'],
    mixins: [],
    components: {

    },
    data() {
        return {
            list:[{
                url:"home",
                desc:this.$t('lang.home'),
                icon:require("@/assets/img/footer/icon1.png"),
                icon_cur:require("@/assets/img/footer/icon1_cur.png"),
            },{
                url:"catalog",
                desc:this.$t('lang.category'),
                icon:require("@/assets/img/footer/icon2.png"),
                icon_cur:require("@/assets/img/footer/icon2_cur.png"),
            },{
                url:"integration",
                desc:this.$t('lang.discover'),
                icon:require("@/assets/img/footer/icon3.png"),
                icon_cur:require("@/assets/img/footer/icon3_cur.png"),
            },{
                url:"cart",
                desc:this.$t('lang.cart'),
                icon:require("@/assets/img/footer/icon4.png"),
                icon_cur:require("@/assets/img/footer/icon4_cur.png"),
            },{
                url:"user",
                desc:this.$t('lang.my_alt'),
                icon:require("@/assets/img/footer/icon5.png"),
                icon_cur:require("@/assets/img/footer/icon5_cur.png"),
            }]
        }
    },
    created() {

    },
    mounted() {
    },
    methods: {
        
    },
    computed: {
        aActive() {
            let arr = []
            this.list.forEach(v => {
                arr.push(false)
            })
            arr[0] = true
            return arr
        },
        routeName() {
            return this.authority == 'view' ? this.$route.name : ''
        },
        aImgList() {

        },
        oPosition() {
            let o = {}
            this.preview ? o.position = "relative" : o.position = "fixed"
            return o
        },
        authority(){
            return window.apiAuthority
        }
    },
    methods:{
        outerHref(val){
            let that = this
            if(that.authority == 'view'){
                if(val == 'home' || val == 'catalog' || val == 'search' || val == 'user'){
                    setTimeout(() => {
                        uni.getEnv(function(res){
                            if(res.plus || res.miniprogram){
                                if(val == 'home'){
                                    uni.reLaunch({  
                                        url: '../../pages/index/index'
                                    })
                                }else if(val == 'catalog'){
                                    uni.reLaunch({  
                                        url: '../../pages/category/category'
                                    })
                                }else if(val == 'search'){
                                    uni.reLaunch({  
                                        url: '../../pages/search/search'
                                    })
                                }else if(val == 'user'){
                                    uni.reLaunch({  
                                        url: '../../pages/user/user'
                                    })
                                }
                            }else{
                                that.$router.push({
                                    name:val
                                })
                            }
                        })
                    },100)
                }else{
                    that.$router.push({
                        name:val
                    })
                }
            }
        }
    }
}
</script>

<style lang="scss" scoped>
@import '@/assets/style/config.scss';
@import '@/assets/style/mixins/common.scss';

.tab-down{
    position: fixed;
    width: 100%;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1999;
    background: #fff;
    border-top: 1px solid $border-color-split;
    padding-bottom: env(safe-area-inset-bottom);
    height: 5rem;
    box-sizing: content-box;
}
.tab-down ul{
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
    height: 100%;
}
.tab-down ul li{
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
}
.tab-down ul li i{
    display: block;
    width: 2.5rem;
    height: 2.5rem;
}
.tab-down ul li span{
     font-size: 1.2rem;
     color: #333;
}
.tab-down ul li.active span{
    color: #F20E28;
}
</style>