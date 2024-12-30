<template>
  <div class="flow-done">
        <div class="header">
            <i class="iconfont icon-qian"></i>
            <p class="m-top06 f-03 color-9">{{$t('lang.payment_amount')}}</p>
            <p class="done-price" v-html="crowdDoneData.total.amount_formated"></p>
        </div>
        <van-cell-group class="list">
            <van-cell :title="$t('lang.order_sn')" :value="crowdDoneData.order.order_sn" />
            <van-cell :title="$t('lang.shipping_mode')" :value="crowdDoneData.order.shipping_name" />
        </van-cell-group>
        <div class="padding-all">
            <van-button class="br-5 f-06" type="primary" bottom-action @click="payClicked">{{$t('lang.goto_payment')}}</van-button>
        </div>
        <CommonNav :routerName="routerName">
	      	<li slot="aloneNav">
                <router-link :to="{name: 'crowdfunding'}">
					<i class="iconfont icon-shequ2"></i>
					<p>{{$t('lang.square')}}</p>
				</router-link>
			</li>
			<li slot="aloneNav">
				<router-link :to="{name: 'crowdfunding-user'}">
					<i class="iconfont icon-gerenzhongxin"></i>
					<p>{{$t('lang.centre')}}</p>
				</router-link>
			</li>
	    </CommonNav>
    </div>   
</template>
<script>
import CommonNav from "@/components/CommonNav.vue"; 
	import { mapState } from 'vuex'
    import {
        Cell,
        CellGroup,
        Button
    } from 'vant'
    export default{
        data(){
            return {
                routerName:'crowd_funding',
                value:'',
                apart:'apart'
            }
        },
        components:{
            CommonNav,
            [Cell.name] : Cell,
            [CellGroup.name] : CellGroup,
            [Button.name]: Button,
        },
        //初始化加载数据
        created() {
            let done = {
                pid: this.$route.params.pid,
                id:this.$route.params.id,
                number: this.$route.params.number,
                pay_id:this.$route.params.pay_id,
            }
            this.$store.dispatch('setCrowdfundingDone',done)
        },
        computed: {
            ...mapState({
                crowdDoneData: state => state.crowdfunding.crowdDoneData,
            }),
        },
        methods:{
            payClicked(){
                this.$router.push({
                    name: "crowdfunding-done",
                    params: {
                        pid: this.pid,
                        id:this.id,
                        number: this.number,
                    }
                });
            },
        }
    }
</script>
