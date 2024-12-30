<template>
     <van-tabs :active="active">
        <van-tab v-for="(item,index) in data" :key="index" >
            <div class="nav_active" slot="title" @click="tabsFilter(index)">{{item}}</div>
        </van-tab>
      </van-tabs>
      
</template>
<script>
    import {
         Tab,
        Tabs,
    } from "vant";
    export default {
        	props:['data'],
            components: {
                [Tab.name]: Tab,
                [Tabs.name]: Tabs,
        },
        data() {
            return {
                active: 0,
            }
        },
        methods:{
           tabsFilter(index) {
               this.$emit('child-event',index)
            },
	    }
    }
</script>