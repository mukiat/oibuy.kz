<template>
    <div class="drp-order drp-orderdetail tab-colse">
            <van-cell-group>
		    	<van-cell :title="crowdDetailData.consignee  +  crowdDetailData.mobile" :label='crowdDetailData.detail_address' icon="location"></van-cell>
            </van-cell-group>
            <section class="store_info">
                <div class="user-item user-order-item">
                    <div class="item-bd">
                        <router-link :to="{name:'crowdfunding-orderdetail',params:{ id: crowdDetailData.order_id}}">
                            <div class="subHead">
                                <h4><label>{{$t('lang.label_order')}}</label><span>{{ crowdDetailData.order_sn }}</span></h4>
                                <p><span>{{$t('lang.order_time')}}：{{ crowdDetailData.add_time }} </span></p>
                            </div>
                            <div class="list-bd-box list-order-box">
                                <ul class="dis-box">
                                    <li class="reture-left-img">
                                        <div class="img-box" v-if="crowdDetailData.title_img != ''">
                                            <img :src="crowdDetailData.title_img" class="img">
                                        </div>
                                        <div class="img-box" v-else>
                                            <img class="img" src="../../../../assets/img/not_goods.png"/>
                                        </div>
                                    </li>
                                    <li class="reture-right-cont">
                                        <h5 class="twolist-hidden f-06">{{ crowdDetailData.title }}</h5>
                                        <p class="f-03 color-7 m-top04">{{$t('lang.label_support_money')}}<em class="color-red" v-html="crowdDetailData.formated_goods_amount"></em>{{$t('lang.yuan')}}</p>
                                        <div class="f-03 color-9 m-top02">{{crowdDetailData.content}}</div>
                                    </li>
                                </ul>
                            </div>
                        </router-link>
                    </div>
                </div>
            </section>
        <div class="f-03 padding-all border-t-common bg-color-write text-right color-7">{{$t('lang.gong')}}1{{$t('lang.total_amount_propmt_alt')}}:
            <span class="color-red" v-html="crowdDetailData.formated_goods_amount"></span>{{$t('lang.yuan')}}</div>
        <van-cell-group class="m-top10">
            <van-cell class="f-05 color-3" :title="$t('lang.shipping_mode')" :value="crowdDetailData.shipping_name" />
            <van-cell class="f-05 color-3" :title="$t('lang.payment_mode')" :value="crowdDetailData.pay_name" />
        </van-cell-group>
        <van-cell-group class="m-top10">
            <van-cell class="f-05 color-3" :title="$t('lang.goods_amout')">
                <span class="color-red" v-html="crowdDetailData.formated_goods_amount"></span>
            </van-cell>
            <van-cell class="f-03 color-9" :title="$t('lang.amount_paid')">
                    <span class="color-red" v-html="crowdDetailData.formated_goods_amount"></span>
            </van-cell>
        </van-cell-group>
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
        ContactCard,
        Cell,
        CellGroup
    } from 'vant'
    export default {
        name: "team-orderdetail",
        data() {
            return {
                routerName:'crowd_funding',
            };
        },
        components: {
            CommonNav,
            [ContactCard.name]: ContactCard,
            [Cell.name]: Cell,
            [CellGroup.name]: CellGroup
        },
        //初始化加载数据
        created() {
            this.$store.dispatch({
                    type: 'setCrowdfundingDetail',
                    order_id: this.$route.params.id,
                })
        },
        computed: {
            ...mapState({
                crowdDetailData: state => state.crowdfunding.crowdDetailData,
            }),
        }
    };
</script>
