<template>
    <div class="tab-con" :class="{'user-view':order_id == 0}" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
    <template v-if="order_id == 0">
        <TabNav>
            <template slot="tabItem">
            <ul>
              <li :class="{'active':status == 0}" @click="orderStatusHandle(0)">{{$t('lang.purchase_note')}}</li>
              <li :class="{'active':status == 1}" @click="orderStatusHandle(1)">{{$t('lang.supplier_return')}}</li>
            </ul>
          </template>
        </TabNav>
        <template v-if="refoundList && refoundList.length > 0">
        <section class="user-item" v-for="(itemList,index) in refoundList" :key="index">
            <div class="item-hd">
                <div class="subHead">
                    <h4>
                        <label>{{$t('lang.return_sn_user')}}：</label>
                        <span>{{ itemList.return_sn }}</span>
                    </h4>
                    <p>
                        <span>{{$t('lang.apply_time')}}： </span>
                        <span class="m-r05">{{ itemList.apply_time }}</span>
                        <span class="color-red m-r05" v-if="itemList.agree_apply == 0">{{$t('lang.to_be_agreed')}}</span>
                        <span class="color-red m-r05" v-else-if="itemList.agree_apply == 1">{{$t('lang.has_agreed_to')}}</span>
                        <span class="color-red m-r05" v-else>{{$t('lang.denied')}}</span>
                        <span class="color-red">{{ itemList.reimburse_status }}</span>
                    </p>
                </div>
            </div>
            <div class="flow-checkout-pro">
                <div class="item-bd">
                    <div class="list-bd-box">
                        <ul class="dis-box">
                            <li class="reture-left-img">
                                <div class="img-box">
                                    <img :src="itemList.goods_thumb" class="img" v-if="itemList.goods_thumb" />
                                </div>
                            </li>
                            <li class="reture-right-cont">
                                <h4 class="ellipsis-one">{{ itemList.goods_name }}</h4>
                                <div class="p-attr">{{ itemList.goods_attr }}</div>
                                <div class="reture-footer">
                                    <div class="price">
                                        <label class="color-red" v-html="itemList.goods_price"></label>
                                        <span>{{$t('lang.number')}}×{{ itemList.return_number }}</span>
                                    </div>
                                    <div class="fr">
                                        <template v-if="itemList.refound_cancel">
                                        <a href="javascript:void(0);" class="btn-default-new" @click="cancelRefound(itemList.ret_id)">{{$t('lang.cancel_request')}}</a>
                                        </template>
                                        <template v-if="itemList.activation_type">
                                            <a href="javascript:void(0);" class="btn-default-new" @click="applyRefoundjihuo(itemList.ret_id)">{{$t('lang.activate')}}</a>
                                        </template>
                                        <a href="javascript:void(0);" class="btn-default-new" @click="applyRefoundView(itemList.ret_id)">{{$t('lang.view_detail')}}</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
        </template>
        <template v-else>
            <NotCont></NotCont>
        </template>
        <template v-if="loading">
            <van-loading type="spinner" color="black" />
        </template>
    </template>
    <template v-else>
        <section class="user-item" v-if="orderRefound">
            <div class="flow-checkout-pro" v-for="(item,index) in orderRefound.goods_list" :key="index">
                <div class="item-bd">
                    <div class="list-bd-box">
                        <ul class="dis-box">
                            <li class="reture-left-img">
                                <div class="img-box">
                                    <img :src="item.goods_thumb" class="img" v-if="item.goods_thumb" />
                                </div>
                            </li>
                            <li class="reture-right-cont">
                                <h4 class="ellipsis-one">{{ item.goods_name }}</h4>
                                <div class="p-attr">{{ item.goods_attr }}</div>
                                <div class="reture-footer">
                                    <div class="price">
                                        <label class="color-red" v-html="item.goods_price"></label>
                                        <span>×{{ item.goods_number }}</span>
                                    </div>
                                    <div class="fr">
                                        <template v-if="item.is_refound">
                                            <span class="color-red f-06">{{$t('lang.applied')}}</span>
                                        </template>
                                        <template v-else>
                                            <template v-if="item.goods_cause.length == 0 || item.extension_code == 'virtual_card'">
                                                <a href="javascript:void(0);" class="btn-default-new disabled">{{$t('lang.apply_return')}}</a>
                                            </template>
                                            <template v-else>
                                            <a href="javascript:void(0);" class="btn-default-new" @click="applyRefound(item.rec_id,orderRefound.order_id)">{{$t('lang.apply_return')}}</a>
                                            </template>
                                        </template>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </template>
        <CommonNav></CommonNav>
    </div>
</template>
<script>
import qs from 'qs'
import { mapState } from 'vuex'

import {
    Waterfall,
    Loading,
    Toast,
    Dialog
} from 'vant'

import NotCont from '@/components/NotCont'
import TabNav from '@/components/user/TabNav'
import arrRemove from '@/mixins/arr-remove'
import CommonNav from '@/components/CommonNav'

export default{
    data(){
        return {
            order_id:this.$route.query.id ? this.$route.query.id : 0,
            disabled:false,
            loading:true,
            page:1,
            size:10,
            status:1
        }
    },
    directives: {
        WaterfallLower: Waterfall('lower')
    },
    components:{
        TabNav,
        NotCont,
        CommonNav,
        [Loading.name]:Loading,
        [Toast.name]:Toast,
        [Dialog.name]:Dialog
    },
    created(){
        this.setRefoundList()
    },
    computed:{
        ...mapState({
            orderRefound: state => state.other.supplierOrderRefound
        }),
        refoundList:{
            get(){
                return this.$store.state.other.supplierRefoundList
            },
            set(val){
                this.$store.state.other.supplierRefoundList = val
            }
        }
    },
    methods:{
        setRefoundList(page){
            if(page){
                this.page = page
                this.size = Number(page) * 10
            }

            if(this.order_id == 0){
                this.$store.dispatch('setSupplierRefoundList',{
                    page:1,
                    size:10
                })
            }else{
                this.$store.dispatch('setSupplierOrderRefound',{
                    order_id:this.order_id
                })
            }
        },
        applyRefound(rec_id,order_id){
            this.$router.push({
                name:'supplier-applyreturn',
                query:{
                    rec_id:rec_id,
                    order_id:order_id
                }
            })
        },
        applyRefoundView(ret_id){
            this.$router.push({
                name:'supplier-returndetail',
                query:{
                    ret_id:ret_id
                }
            })
        },
        applyRefoundjihuo(ret_id){
            Dialog.confirm({
                message:this.$t('lang.confirm_activate_return'),
                className:'text-center'
            }).then(()=>{
                this.$http.post(`${window.ROOT_URL}api/refound/active_return_order`,qs.stringify({
                    ret_id:ret_id
                })).then(({data:{data}})=>{
                    if(data.code == 0){
                        Toast(this.$t('lang.return_order_activate'))
                        this.setRefoundList()
                    }else{
                        Toast(data.msg)
                    }
                })
            }).catch(()=>{

            })
        },
        cancelRefound(ret_id){
            Dialog.confirm({
                message:this.$t('lang.confirm_cancel_request'),
                className:'text-center'
            }).then(()=>{
                this.$http.post(`${window.ROOT_URL}api/refound/cancel`,qs.stringify({
                    ret_id:ret_id
                })).then(({data:{data}})=>{
                    if(data.code == 0){
                        Toast(this.$t('lang.return_order_cancel'))
                        this.setRefoundList()
                    }else{
                        Toast(data.msg)
                    }
                })
            }).catch(()=>{

            })
        },
        loadMore(){
            setTimeout(() => {
                this.disabled = true
                if(this.page * this.size == this.refoundList.length){
                    this.page ++
                    this.setRefoundList()
                }
            },200)
        },
        orderStatusHandle(val){
            if(val == 0){
                this.$router.push({
                    name:'supplier-orderlist'
                })
            }
        }
    },
    watch:{
        refoundList(){
            if(this.page * this.size == this.refoundList.length){
                this.disabled = false
                this.loading = true
            }else{
                this.loading = false
            }

            this.refoundList = arrRemove.trimSpace(this.refoundList)
        }
    }
}
</script>
