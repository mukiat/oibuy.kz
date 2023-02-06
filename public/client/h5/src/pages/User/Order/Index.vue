<template>
    <div id="userOrder" class="user-view" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
    <section class="secrch">
        <div class="secrch-warp">
            <template>
                <div class="input-text">
                    <template>
                        <input class="j-input-text" type="search" name="keywords" v-model="keywords" :placeholder="$t('lang.search_order')" autocomplete="off">
                    </template>
                    <label class="search-check" @click="search"><i class="iconfont icon-search"></i></label>
                </div>
            </template>
        </div>
    </section>
    <TabNav>
        <template slot="tabItem">
            <swiper :options="swiperOption" ref="mySwiper">
                <swiper-slide v-for="(item, index) in orderStatus" :key="index" :class="{'active':index == status}">
                    <div class="name" @click="orderStatusHandle(index)">{{ item.status }}({{item.count}})</div>
                </swiper-slide>
            </swiper>
        </template>
    </TabNav>
    
    <section class="store_info">
        <template v-if="tabStatus">
            <template v-if="orderList && orderList.length > 0">
          		<div class="user-item user-order-item" v-for="(item,index) in orderList" :key="index">
            		<div class="item-hd">
            			<div class="head">
            				<h3>{{ item.shop_name }}</h3>
            				<em>{{ item.order_status }}</em>
            			</div>
            		</div>
            		<div class="item-bd">
                        <router-link :to="{name:'orderDetail',params:{ id: item.order_id}}">
            			<div class="subHead">
            				<h4><label>{{$t('lang.order_sn')}}：</label><span>{{ item.order_sn }}</span></h4>
            				<p>
                                <span>{{$t('lang.order_time')}}：{{ item.add_time }} </span>
                                <em class="em-promotion b-tag" v-if="item.activity_lang">{{ item.activity_lang }}</em>
                            </p>
            			</div>
                        <template v-if="item.order_goods">
                            <template v-if="item.order_goods.length == 1">
                    			<div class="list-bd-box list-order-box" v-for="(goodsItem,goodsIndex) in item.order_goods" :key="goodsIndex">
                                    <ul class="dis-box">
                                        <li class="reture-left-img"><div class="img-box"><img :src="goodsItem.goods_thumb" class="img"></div></li>
                                        <li class="reture-right-cont"><h4 class="twolist-hidden"><img v-if="goodsItem.country_icon != ''" class="country_icon" :src="goodsItem.country_icon" />{{ goodsItem.goods_name }}</h4></li>
                                        <li class="reture-right-san"><span class="user-more"><em>{{$t('lang.gong')}} {{item.order_goods_num}}{{$t('lang.kuan')}}</em><i class="iconfont icon-more"></i></span></li>
                        			</ul>
                    			</div>
                            </template>
                            <template v-else>
                                <div class="list-bd-box list-order-box">
                                    <div class="list-bd-left">
                                        <swiper :options="swiperOption" ref="mySwiper">
                                            <swiper-slide class="reture-left-img" v-for="(goodsItem,goodsIndex) in item.order_goods" :key="goodsIndex"  :class="{'active':index == status}">
                                              <div class="img-box">
                                                <img :src="goodsItem.goods_thumb" class="img" v-if="goodsItem.goods_thumb">
                                                <img class="img" src="../../../assets/img/no_image.jpg" v-else>
                                                <em class="activity-tag" v-if="goodsItem.is_gift"><img src="../../../assets/img/gift-icon.png" class="img" /></em>
                                                <em class="activity-tag" v-if="goodsItem.parent_id"><img src="../../../assets/img/parts-icon.png" class="img" /></em>
                                              </div>
                                            </swiper-slide>
                                        </swiper>
                                    </div>
                                    <div class="align-items list-bd-right"><span class="user-more"><em>{{$t('lang.gong')}} {{item.order_goods_num}}{{$t('lang.kuan')}}</em><i class="iconfont icon-more"></i></span></div>
                                </div>
                            </template>
                        </template>
        	    		<div class="list-item-box">{{$t('lang.gong')}}{{item.order_goods_num}}{{$t('lang.total_amount_propmt')}}：<span v-html="item.total_amount_formated" class="color-red"></span></div>
                        </router-link>
            		</div>
            		<div class="item-fd">
            			<h4></h4>
            			<div class="ect-button-more">
                            <template v-if="item.invoice_see == true && item.invoice_type == 2">
                                <a href="javascript:void(0)"  @click="invoiceDetail(item.order_id)" class="btn-default-new">Чек көру</a>
                            </template>
                            <template v-if="item.delay === 1">
                              <a href="javascript:void(0)" @click="delayOrder(item.order_id)" class="btn-default-new">{{$t('lang.delay_in_receiving')}}</a>
                            </template>
                            <template v-if="item.order_del > 0">
                              <a href="javascript:void(0)" @click="delOrder(item.order_id)" class="btn-default-new">{{$t('lang.delete_order')}}</a>
                            </template>
                            <template v-if="item.is_restore === 1">
                                <a href="javascript:void(0)" @click="delRestore(item.order_id)" class="btn-default-new">{{$t('lang.restore_order')}}</a>
                            </template>
                            <template v-if="item.handler_return && item.extension_code != 'package_buy'">
                              <a href="javascript:void(0)" @click="refoundHandle(item.order_id)" class="btn-default-new" v-if="!item.is_return">{{$t('lang.apply_return')}}</a>
                              <a href="javascript:void(0)" @click="refoundHandle(item.order_id)" class="btn-default-new" v-else>{{$t('lang.already_apply_return')}}</a>
                            </template>
                            <template v-if="item.handler === 1 && item.is_restore === 0">
                              <a href="javascript:void(0)" @click="cancelOrder(item.order_id)" class="btn-default-new">{{$t('lang.cancel_order')}}</a>
                            </template>
                            <template v-else-if="item.handler === 2 && item.is_restore === 0">
                              <a href="javascript:void(0)" @click="receivedOrder(item.order_id)" class="btn-default-new">{{$t('lang.received')}}</a>
                            </template>
                            <template v-else-if="item.handler === 0 && item.is_restore === 0">
                              <router-link :to="{name:'comment'}" class="btn-default-new">{{$t('lang.evaluation_list')}}</router-link>
                            </template>
                            <template v-if="item.online_pay && item.failure == 0 && item.is_restore === 0">
                              <a href="javascript:void(0)" @click="onlinepay(item.order_sn,item.pay_code)" class="btn-default-new">{{$t('lang.immediate_pay')}}</a>
                            </template>
                            <template v-if="item.order_status === $t('lang.ss_received')">
                                <template v-if="item.is_comment == 0 && item.shop_can_comment > 0">
                                    <a href="javascript:void(0)" @click="commentOrder(item.order_id)" class="btn-default-new">{{$t('lang.evaluation_list')}}</a>
                                </template>
								<template v-if="item.is_store_order == 0 && item.is_delete == 0">
                                <a href="javascript:void(0)" @click="buyAgain(item.order_id)" class="btn-default-new">{{$t('lang.buy_again')}} </a>
								</template>
                            </template>
        				</div>
            		</div>
            	</div>
                <div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
                <template v-if="loading">
                    <van-loading type="spinner" color="black" />
                </template>
            </template>
            <template v-else>
                <NotCont></NotCont>
            </template>
        </template>
        <template v-else>
            <van-loading type="spinner" color="black" size="3rem" class="loading-mar-5" />
        </template>
    </section>
    <CommonNav></CommonNav>

    <!--初始化loading-->
    <DscLoading :dscLoading="dscLoading"></DscLoading>
    </div>
</template>

<script>
import { mapState } from 'vuex'

import {
    Waterfall,
    Loading,
    Dialog,
    Toast,
} from 'vant'

import {
    swiper,
    swiperSlide
} from 'vue-awesome-swiper'

import CommonNav from '@/components/CommonNav'
import NotCont from '@/components/NotCont'
import TabNav from '@/components/user/TabNav'
import DscLoading from '@/components/DscLoading'
import arrRemove from '@/mixins/arr-remove'

export default{
	data(){
        return {
            disabled:false,
            loading:true,
            page:1,
            size:10,
            status:0,
            type:'type',
            keywords:'',
            tabStatus:true,
            dscLoading:true,
            footerCont:false,
            swiperOption:{
                notNextTick: true,
                watchSlidesProgress: true,
                watchSlidesVisibility: true,
                slidesPerView: 'auto',
                lazyLoading: true,
            },
            orderStatus:[]
		}
	},
    directives: {
        WaterfallLower: Waterfall('lower')
    },
	components:{
        swiper,
        swiperSlide,
        TabNav,
        CommonNav,
        NotCont,
        DscLoading,
        [Dialog.name]: Dialog,
        [Loading.name]: Loading,
	},
    created(){
        let val = this.$route.query.tab
        if(val){
            this.orderStatusHandle(val)
        }else{
            this.setOrderList()
        }
    },
    computed:{
        ...mapState({
            orderCount: state => state.user.userorderCount
        }),
        orderList:{
            get(){
                return this.$store.state.user.userOrderList
            },
            set(val){
                this.$store.state.user.userOrderList = val
            }
        }
    },
	methods:{
        setOrderList(page){
            if(page){
                this.page = page
                this.size = Number(page) * 10
            }

            this.$store.dispatch('setOrderList',{
                page:this.page,
                size:this.size,
                status:this.status,
                type:this.type,
                keywords:this.keywords
            })
        },
        search(){
            this.setOrderList(1)
        },
		orderStatusHandle(val){
            this.status = val
            this.tabStatus = false
            this.setOrderList(1)
        },
        loadMore(){
            setTimeout(() => {
                this.disabled = true
                if(this.page * this.size == this.orderList.length){
                    this.page ++
                    this.setOrderList()
                }
            },200);
        },
        //立即支付
        onlinepay(id,code){
            if(code == 'balance'){
                this.$router.push({
                    name:'done',
                    query:{
                        order_sn:id,
                        pay_code:'balance'
                    }
                })
            }else{
                this.$router.push({
                    name:'done',
                    query:{
                        order_sn:id
                    }
                })
            }
        },
        //申请退换货
        refoundHandle(id){
            this.$router.push({
                name:'refound',
                query:{
                    id:id
                }
            })
        },
        //删除订单
        delOrder(id){
            this.$store.dispatch('setDeleteOrder',{
                order_id:id
            }).then(res=>{
                if(res.data == true){
                    Toast(this.$t('lang.order_delete_success'))
                    this.setOrderList(1)
                }
            })
        },
        //还原订单
        delRestore(id){
            this.$store.dispatch('setRestoreOrder',{
                order_id:id
            }).then(res=>{
                if(res.data == true){
                    Toast(this.$t('lang.order_restore_success'))
                    this.setOrderList(1)
                }
            })
        },
        //取消订单
        cancelOrder(id){
            this.$store.dispatch('setOrderHandler',{
                order_id:id
            }).then(res=>{
                if(res.data == true){
                    Toast(this.$t('lang.order_cancel'))
                    this.setOrderList(1)
                }
            })
        },
        //延迟收货
        delayOrder(id){
            this.$store.dispatch('setDelayOrder',{
                order_id:id
            }).then(res=>{
                Toast(res.data.msg)

                if(res.data.error == 0){
                    this.setOrderList(1)
                }
            })
        },
        //确认收货
        receivedOrder(id){
            this.$store.dispatch('setReceivedOrder',{
                order_id:id
            }).then(res=>{
                if(res.data == true){
                    Toast(this.$t('lang.order_confirm_receipt'))
                    this.setOrderList(1)
                }
            })
        },
        //再次购买
        buyAgain(id){
            this.$store.dispatch('setbuyAgain',{
                id:id
            }).then(res=>{
                if(res.data.error == 0){
                    if(res.data.cant_buy_goods.length == 0){
                        this.$router.push({
                            name:'cart'
                        })
                    }else{
                        Dialog.confirm({
                            message: this.$t('lang.order_buy_again_propmt'),
                            className: 'text-center',
                            confirmButtonText:this.$t('lang.go_cart'),
                            cancelButtonText:this.$t('lang.stay_on_page')
                        }).then(() => {
                            this.$router.push({
                                name:'cart'
                            })
                        }).catch(() => {

                        })
                    }
                }else{
                    Toast(this.$t('lang.parameter_error'))
                    this.setOrderList(1)
                }
            })
        },
        //评论订单内商品
        commentOrder(id){
            this.$router.push({
                name:'comment',
                query:{
                    id:id
                }
            })
        },
        // 查看电子发票
        invoiceDetail(id){
            this.$router.push({
                name:'invoiceDetail',
                params: {
                    order_id: id
                }
            })
        }
	},
    watch:{
        orderList(){
            this.dscLoading = false
            this.tabStatus = true
            if(this.page * this.size == this.orderList.length){
                this.disabled = false
                this.loading = true
            }else{
                this.loading = false
                this.footerCont = this.page > 1 ? true : false
            }
            this.orderList = arrRemove.trimSpace(this.orderList)
        },
        orderCount(){
            this.orderStatus = [{
                status:this.$t('lang.all_order'),
                count:this.orderCount.all
            },{
                status:this.$t('lang.order_status_01'),
                count:this.orderCount.nopay
            },{
                status:this.$t('lang.order_status_03'),
                count:this.orderCount.nogoods
            },{
                status:this.$t('lang.ss_received'),
                count:this.orderCount.isfinished
            },{
                status:this.$t('lang.order_status_11'),
                count:this.orderCount.isdelete
            }]
        }
    }
}
</script>
<style scoped>
.user-view{ padding-top: 8.5rem; }
.secrch{ position: fixed; top: 0; z-index: 11}
.tab-title{ top: 4.5rem; }
.user-more .iconfont {
    position: static;
}

.country_icon{
	width: 2.4rem;
	padding-right: 0.3rem;
	position: relative;
	top: 0.2rem;
	display: inline-block;
}
</style>