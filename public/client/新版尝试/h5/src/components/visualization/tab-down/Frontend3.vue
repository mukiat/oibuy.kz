<template>
    <footer class='tab-down' :style="oPosition">
        <ul>
            <template v-if="authority == 'view'">
                <li v-for="(item,index) in list" :key="index" :class="{active:routeName == item.url}" @click.stop="outerHref(item.url)">
                    <i></i>
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

// custom components

// third party components

//mixins

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
                desc:this.$t('lang.home')
            },{
                url:"catalog",
                desc:this.$t('lang.category')
            },{
                url:"drp-info",
                desc:this.$t('lang.high_grade_vip')
            },{
                url:"cart",
                desc:this.$t('lang.cart')
            },{
                url:"user",
                desc:this.$t('lang.my_alt')
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
    z-index: 111;
    background: #fff;
    border-top: 1px solid $border-color-split;
    padding-bottom: env(safe-area-inset-bottom);
    height: 6rem;
    box-sizing: content-box;
}

.tab-down ul {
    height: 6rem;
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
}

.tab-down ul li {
    width: 25%;
    height: 6rem;
    position: relative;
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;

    a{
        @include urlAbsolute();
    }

    i{
        display: block;
        margin: 0 auto;
        width: 2.5rem;
        height: 2.5rem;
        background-size: 100% 100%;
        background-repeat: no-repeat;
    }

    span {
        font-size: 1.2rem;
        display: block;
        margin-top: .5rem;
    }

    &.active {
        span{
            color: $color;
        }
    }

    &:nth-child(1){
        i{
            background-image: url("@/assets/img/tabBar/tabBar_1.png");
        }
    }
    &:nth-child(2){
        i{
            background-image: url("@/assets/img/tabBar/tabBar_2.png");
        }
    }
    &:nth-child(3){
        i{
            /*width: 5rem;
            height: 5rem;
            margin: -2.5rem 0 0 0;*/
            background-image: url("@/assets/img/tabBar/tabBar_vip.png");
        }
        span{
            color: #b88f56;
        }
    }
    &:nth-child(4){
        i{
            background-image: url("@/assets/img/tabBar/tabBar_3.png");
        }
    }
    &:nth-child(5){
        i{
            background-image: url("@/assets/img/tabBar/tabBar_4.png");
        }
    }
    &:nth-child(1).active{
        i{
            background-image: url("@/assets/img/tabBar/tabBar_cur_1.png");
        }
    }
    &:nth-child(2).active{
        i{
            background-image: url("@/assets/img/tabBar/tabBar_cur_2.png");
        }
    }
    &:nth-child(4).active{
        i{
            background-image: url("@/assets/img/tabBar/tabBar_cur_3.png");
        }
    }
    &:nth-child(5).active{
        i{
            background-image: url("@/assets/img/tabBar/tabBar_cur_4.png");
        }
    }
}
</style>