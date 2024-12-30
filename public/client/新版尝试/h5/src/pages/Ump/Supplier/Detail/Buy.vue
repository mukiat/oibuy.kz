<template>
    <div class="con con_main" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
        <div class="wholesale-buy">
            <van-tabs v-model="active" @click="onClick">
                <van-tab v-for="(item,index) in tabs" :title="item" :key="index">
                    <template v-if="supplierPurchaseList && supplierPurchaseList.length > 0">
                        <div class="who-list" v-for='(item, index) in supplierPurchaseList' :key="index">
                            <div class="goods-li" @click="detailClick(item.purchase_id)">
                                <div class="li">
                                    <div class="left left-img p-r">
                                        <img class="img goods-img" :src="item.img" v-if="item.img" />
                                        <img class="img" src="../../../../assets/img/no_image.jpg" v-else>
                                    </div>
                                    <div class="right bg-color-write">
                                        <h4 class="f-05 color-3 twolist-hidden"> {{item.subject}}</h4>
                                        <div class="f-02 color-9 m-top04">{{$t('lang.label_purchase_quantity')}}<span class="color-red">{{item.goods_number}}</span>{{$t('lang.jian')}}</div>
                                        <div class="f-02 color-9">{{$t('lang.label_release_time')}}<span class="color-3">{{item.add_time_complete}}</span></div>
                                        <div class="f-02 color-9">{{$t('lang.time_remaining')}}
                                            <span class="color-3" v-if="item.left_day > 0">{{item.left_day}}{{$t('lang.tian')}}</span>
                                            <span class="color-3" v-else-if="item.left_day < 0">{{$t('lang.less_than_day')}}</span>
                                            <span class="color-3" v-else>{{$t('lang.has_ended')}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="who-footer bg-color-write">
                                <ul class="dis-box">
                                    <div class="box-flex">
                                        <h5 class="f-05 color-3">{{item.shop_name}}</h5>
                                        <p class="f-02 color-7">{{item.area_info}}</p>
                                    </div>
                                    <div>
                                        <a href="javascirpt:;" class="who-btn" @click="onChat(item.purchase_id,item.user_id)">
                                            <van-button type="danger">{{$t('lang.immediately_contact')}}</van-button>
                                        </a>
                                    </div>
                                </ul>
                            </div>
                        </div>
                    </template>
                    <template v-else>
						<NotCont/>
                    </template>
                    <template v-if="loading || tabLoading">
                        <van-loading type="spinner" color="black" />
                    </template>
                </van-tab>
            </van-tabs>
            <WhoTabbar />
        </div>
    </div>
</template>
<script>
    import { mapState } from 'vuex'

    import WhoTabbar from "./WhoTabbar.vue"
    import NotCont from '@/components/NotCont'
    import formProcessing from '@/mixins/form-processing'
    import arrRemove from '@/mixins/arr-remove'
    import {
        Button,
        Tab,
        Tabs,
        Row,
        Col,
        Waterfall,
        Loading,
    } from 'vant'
    export default {
        name: "wholesale-buy",
        mixins: [formProcessing],
        components: {
            NotCont,
            WhoTabbar,
            [Tab.name]: Tab,
            [Tabs.name]: Tabs,
            [Row.name]: Row,
            [Col.name]: Col,
            [Button.name]: Button,
            [Loading.name]:Loading
        },
        directives: {
            WaterfallLower: Waterfall('lower')
        },
        data() {
            return {
                active: 0,
                tabs: [this.$t('lang.all_ask_buy'), this.$t('lang.ask_buy_zhong'), this.$t('lang.ask_buy_jiezhi')],
                page:1,
                size:10,
                disabled:false,
                loading:true,
                is_finished:-1,
                tabLoading:false
            };
        },
        created(){
            this.purchaseList()
        },
        computed: {
            supplierPurchaseList:{
                get(){
                    return this.$store.state.other.supplierPurchaseList
                },
                set(val){
                    this.$store.state.other.supplierPurchaseList = val
                }
            }
        },
        methods: {
            purchaseList(page){
                if(page){
                    this.page = page
                    this.size = Number(page) * 10
                }

                this.$store.dispatch('setSupplierPurchaseList',{
                    page:this.page,
                    size:this.size,
                    is_finished:this.is_finished
                })
            },
            detailClick(id) {
                this.$router.push({
                    name: "supplier-buyinfo",
                    query: {
                        purchase_id: id
                    }
                });
            },
            onClick(index){
                this.is_finished = index - 1;
                this.supplierPurchaseList = [];
                this.tabLoading = true;
                this.purchaseList(1);
            },
            loadMore(){
                setTimeout(() => {
                    this.disabled = true
                    if(this.page * this.size == this.supplierPurchaseList.length){
                        this.page ++
                        this.purchaseList() 
                    }
                },200);
            },
        },
        watch:{
            supplierPurchaseList(){
                if(this.page * this.size == this.supplierPurchaseList.length){
                    this.disabled = false
                }else{
                    this.loading = false
                }
                this.supplierPurchaseList = arrRemove.trimSpace(this.supplierPurchaseList)
            },
        }
    };
</script>
