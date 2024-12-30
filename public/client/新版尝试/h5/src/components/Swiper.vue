<template>
    <van-swipe :autoplay="autoplay" class="swipe">
        <van-swipe-item v-for="(item, index) in data" :key="index">
            <a :href="item.link"><img class="img" :src="item.pic" /></a>
        </van-swipe-item>
    </van-swipe>
</template>
<script>
    import {
        Swipe,
        SwipeItem,
    } from "vant";
    export default {
        	props:['data','swipeUrl','autoplay'],
            components: {
            [Swipe.name]: Swipe,
            [SwipeItem.name]: SwipeItem,
        },
        data() {
            return {
            }
        },
        computed: {
        },

    }
</script>