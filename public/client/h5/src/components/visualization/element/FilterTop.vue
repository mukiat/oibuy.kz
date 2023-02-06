<template>
    <div class="commom-nav" :class="{'commom-nav-outer': outerClass}" id="commom-nav" v-show="scrollState" @click="backTop">
        <i class="iconfont icon-dingbu"></i>
    </div>
</template>

<script>
    export default {
        name: 'filter-top',
        props:{
            scrollState:{
                type:Boolean,
                default:false
            },
            outerClass:{
                type:String,
                default:''
            }
        },
        methods:{
            backTop(){
                document.body.scrollTop = 0
                document.documentElement.scrollTop = 0
            }
        }
    }
</script>

<style scoped>
.commom-nav{
    width: 5rem;
    height: 5rem;
    border-radius: 50%;
    /*border: 1px solid #ccc;*/
    position: fixed;
    bottom: 10%;
    background-color: #fff;
    right: 16px;
    z-index: 1999;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 1px 1px 2px 0 rgba(0,0,0,.05);
}
.commom-nav .iconfont{
    color: #333;
    font-size: 1.6rem;
    margin-top: 3px;
}
.commom-nav-outer{
    width: 4.5rem;
    height: 4.5rem;
    right: 1rem;
    bottom: 12%;
}
</style>