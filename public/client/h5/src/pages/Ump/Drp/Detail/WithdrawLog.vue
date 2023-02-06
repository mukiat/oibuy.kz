<template>
    <div class="log_info">
        <template v-if="accountlog_list.length > 0">
            <van-cell class="detail-list" v-for="(item,index) in accountlog_list" :key="index">
                <div class="card-div dis-box">
                    <div>
                        <p>{{ item.deposit_type_format }}</p>
                        <span>{{ item.add_time_format }}</span>
                    </div>
                    <div class="box-flex text-right">
                        <p v-html="item.money_format"></p>
                        <span class="color-red"><template v-if="item.check_status_format">{{ item.check_status_format }} - </template>{{ item.deposit_status_format }}</span>
                    </div>
                </div>
            </van-cell>
        </template>
        <template v-else>
            <NotCont></NotCont>
        </template>
        <CommonNav></CommonNav>
    </div>
</template>

<script>
import {
    List,
    Cell
} from 'vant'

import CommonNav from '@/components/CommonNav'
import NotCont from '@/components/NotCont'

export default{
    data(){
        return {
            loading:false,
            accountlog_list:[]
        }
    },
    components:{
        [List.name]:List,
        [Cell.name]:Cell,
        NotCont,
        CommonNav
    },
    created(){
        this.$http.post(`${window.ROOT_URL}api/drp/transfer_list`).then(res =>{
            if(res.data.status == 'success'){
                this.accountlog_list = res.data.data
            }
        })
    },
    methods:{
        onLoad(){

        }
    }
}
</script>
