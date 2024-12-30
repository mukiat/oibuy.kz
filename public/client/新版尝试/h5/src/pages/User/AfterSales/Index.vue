<template>
  <div>
    <div class="tab-con" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
    	<div class="swiper-wrapper">
            <section class="swiper-slide">
                <template v-if="order_id == 0">
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
                                            <div class="reture-footer">
                                                <div class="price">
                                                    <span v-if="itemList.get_return_goods">{{$t('lang.number')}}×{{ itemList.get_return_goods.return_number }}</span>
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
                        <div class="item-hd">
                            <div class="subHead">
                                <h4>
                                    <label>{{$t('lang.order_sn')}}：</label>
                                    <span>{{ orderRefound.order_sn }}</span>
                                </h4>
                                <p>
                                    <span>{{$t('lang.apply_time')}}： </span>
                                    <span class="m-r05">{{ orderRefound.formated_add_time }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="flow-checkout-pro">
                            <div class="item-bd">
                                <div class="list-bd-box m-b10" v-for="(item,index) in orderRefound.goods" :key="index">
                                    <ul class="dis-box">
                                        <li class="reture-checkbox" @click="onCheck(item.rec_id,index)" v-if="orderRefound.all_refound != 1 && item.is_refound == 0 && (item.goods_cause && item.goods_cause.length > 0) && item.extension_code != 'package_buy'">
                                            <div class="checkbox" :class="{'checked':rec_id[index] == item.rec_id}"><i class="iconfont icon-gou"></i></div>
                                        </li>
										<li class="reture-checkbox" v-else>
										    <div class="checkbox disabled"><i class="iconfont icon-gou"></i></div>
										</li>
                                        <li class="reture-left-img">
                                            <div class="img-box">
                                                <img :src="item.goods_thumb" class="img" v-if="item.goods_thumb" />
                                            </div>
                                        </li>
                                        <li class="reture-right-cont">
                                            <h4 class="ellipsis-one">{{ item.goods_name }}</h4>
											<div class="price">
                                                <label v-html="item.goods_price" class="color-red"></label>
											    <span>×{{ item.goods_number }}</span>
											</div>
                                            <div class="reture-footer">
												<div class="goods-cause color-red" v-if="item.goods_cause_formated != '' ">{{ item.goods_cause_formated }}</div>
                                                <div class="goods-operation">
                                                    <template v-if="item.is_refound">
                                                        <span class="color-red f-06">{{$t('lang.applied')}}</span>
                                                    </template>
                                                    <template v-else>
                                                        <template v-if="(item.goods_cause && item.goods_cause.length == 0) || item.extension_code == 'package_buy'">
                                                            <a href="javascript:void(0);" class="btn-default-new disabled">{{$t('lang.apply_return')}}</a>
                                                        </template>
                                                        <template v-else>
                                                            <a v-if="orderRefound.all_refound != 1" href="javascript:void(0);" class="btn-default-new" @click="applyRefound(item.rec_id,orderRefound.order_id)">{{$t('lang.apply_return')}}</a>
                                                        </template>
                                                    </template>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="padding-all" v-if="orderRefound.all_refound == 1">
                                <van-button type="danger" size="large" plain tag="a" @click="onApply('all')">{{$t('lang.apply_return_all')}}</van-button>
                            </div>
                            <div class="padding-all" v-else>
                                <van-button type="danger" :disabled="btndisabled" size="large" plain tag="a" @click="onApply">{{$t('lang.apply_return_all')}}</van-button>
                            </div>
                        </div>
                    </section>
                </template>
            </section>
    	</div>
    </div>
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
    Dialog,
    Button
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
            btndisabled:false,
            rec_id:[]
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
        [Dialog.name]:Dialog,
        [Button.name]:Button,
    },
    created(){
        this.setRefoundList()
    },
    computed:{
        ...mapState({
            orderRefound: state => state.user.orderRefound
        }),
        refoundList:{
            get(){
                return this.$store.state.user.refoundList
            },
            set(val){
                this.$store.state.user.refoundList = val
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
                this.$store.dispatch('setRefoundList',{
                    page:this.page,
                    size:this.size,
                    order_id:this.order_id
                })
            }else{
                this.$store.dispatch('setOrderRefound',{
                    order_id:this.order_id
                })
            }
        },
        onCheck(id,key){
            if(this.rec_id.indexOf(id) == -1){
                this.rec_id.splice(key,1,id)
            }else{
                var index;
                this.rec_id.forEach((r,i)=>{
                    if(r == id){
                        index = i
                    }
                })

                 this.rec_id.splice(index,1,'')
            }


        },
        onApply(type){
            let rec_id;

            if(type == 'all'){
                let rec_all = this.orderRefound.goods.map(function(val,index){ return val.rec_id});
                rec_id = rec_all.join(',');
            }else{
                rec_id = arrRemove.trimSpace(this.rec_id).join(',');
            }

            //判断结尾是否是','
            if(rec_id[rec_id.length-1] == ','){
                rec_id = rec_id.substring(0,rec_id.length-1);
            }

            this.applyRefound(rec_id,this.order_id)
        },
        applyRefound(rec_id,order_id){
            this.$router.push({
                name:'rpplyReturn',
                query:{
                    rec_id:rec_id,
                    order_id:order_id
                }
            })
        },
        applyRefoundView(ret_id){
            this.$router.push({
                name:'refoundDetail',
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
        },
        orderRefound(){
            this.orderRefound.goods.forEach((item)=>{
                this.rec_id.push('');
            })
        },
        rec_id(){
            let length = 0;
            this.rec_id.forEach((item)=>{
                if(item == ''){
                   length++
                }
            })

            this.btndisabled = length == this.rec_id.length;
        }
    }
}
</script>
