<template>
    <div class=" con" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
        <div class="team-order con_main cro_funing">
            <van-tabs :active="active" class="team-tabs">
                <van-tab v-for="(item,index) in tabs" :key="index">
                    <div slot="title" class="nav_active" @click="teamNav(item)">{{item}}</div>
                </van-tab>
            </van-tabs>
            <section class="store_info" v-if="crowdOrderData!=''">
                <div class="user-item user-order-item" v-for="(item,index) in crowdOrderData" :key="index">
                    <div class="item-hd">
                        <div class="head">
                            <h3>{{$t('lang.order_status')}}</h3>
                            <em>{{ item.orderstatus}}</em>
                        </div>	
                    </div>
                    <div class="item-bd">
                        <router-link :to="{name:'crowdfunding-orderdetail',params:{ id: item.order_id}}">
                            <div class="subHead">
                                <h4><label>{{$t('lang.label_order')}}</label><span>{{ item.order_sn }}</span></h4>
                                <p><span>{{$t('lang.order_time')}}：{{ item.add_time }} </span></p>
                            </div>
                            <div class="list-bd-box list-order-box">
                                <ul class="dis-box">
                                    <li class="reture-left-img">
                                        <div class="img-box" v-if="item.title_img != ''">
                                            <img :src="item.title_img" class="img">
                                        </div>
                                        <div class="img-box" v-else>
                                            <img class="img" src="../../../../assets/img/not_goods.png"/>
                                        </div>
                                    </li>
                                    <li class="reture-right-cont">
                                        <h5 class="twolist-hidden f-06">{{ item.title }}</h5>
                                        <p class="f-03 color-7 m-top04">{{$t('lang.label_support_money')}}<em class="color-red" v-html="item.total_fee"></em>{{$t('lang.yuan')}}</p>
                                        <div class="f-03 color-9 m-top02">{{item.content}}</div>
                                    </li>
                                </ul>
                            </div>
                        </router-link>
                    </div>
                    <div class="item-fd dis-box">
                        <h4 class="box-flex f-04"><label class="t-remark">{{$t('lang.total_flow')}}：</label><span class="color-red" v-html="item.total_fee"></span></h4>
                        <div class="ect-button-more">
                            <template v-if="item.handler === 2">
                                <a href="javascript:void(0)" @click="receivedOrder(item.order_id)" class="btn-default-new">{{$t('lang.received')}}</a>
                            </template>
                            <template v-if="item.total_amount > 0 && item.handler != 7 && item.handler != 8">
                                <a href="javascript:void(0)"  @click="onlinepay(item.order_sn)" class="btn-default-new">{{$t('lang.immediate_payment')}}</a>
                            </template>
                            <router-link :to="{name:'crowdfunding-orderdetail',params:{ id: item.order_id}}" class="btn-default-new">{{$t('lang.view_order')}}</router-link>


                        </div>
                    </div>
                </div>
            </section>
            <div v-else>
                <NotCont/>
            </div>
            <CroTabbar />
        </div>
        <!-- <template v-if="loading">
            <van-loading type="spinner" color="black" />
        </template> -->
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
    import { mapState } from 'vuex'
    
    import {
        Tab,
        Tabs,
        Row,
        Col,
        Loading,
        Waterfall,
        Toast,
    } from 'vant'

    import CommonNav from '@/components/CommonNav'
    import NotCont from '@/components/NotCont'
    import CroTabbar from './CroTabbar'
    import arrRemove from '@/mixins/arr-remove'

    export default {
        name: "bargain-order",
        components: {
            CroTabbar,
            CommonNav,
            NotCont,
            [Tab.name]: Tab,
            [Tabs.name]: Tabs,
            [Row.name]: Row,
            [Col.name]: Col,
            [Loading.name]: Loading,
        },
        directives: {
			WaterfallLower: Waterfall('lower')
		},
        data() {
            return {
                routerName:'crowd_funding',
                disabled:false,
			    loading:true,
				size:10,
                page:1,
                status:0,
                active: 0,
                tabs: [this.$t('lang.all_order'), this.$t('lang.order_status_08'), this.$t('lang.order_status_09'), this.$t('lang.order_status_03'), this.$t('lang.order_status_10')],
            }
        },
        //初始化加载数据
        created() {
            this.Order(this.status)
        },
        computed: {
            crowdOrderData:{
                get(){
                    return this.$store.state.crowdfunding.crowdOrderData
                },
                set(val){
                    this.$store.state.crowdfunding.crowdOrderData = val
                }
            }
        },
        methods: {
            teamNav(item) {
                if(item ==this.$t('lang.all_order')){
                    this.status = 0
                }else if(item==this.$t('lang.order_status_08')){
                    this.status = 1
                }else if(item==this.$t('lang.order_status_09')){
                    this.status = 2
                }else if(item ==this.$t('lang.order_status_03')){
                    this.status = 3
                }else{
                    this.status = 4
                }
                this.Order(this.status)
            },
            loadMore(){
				setTimeout(() => {
					this.disabled = true
					if(this.page * this.size == this.crowdOrderData.length){
						this.page ++
                        this.Order(this.status)
					}
				},200)
			},
            Order(status,page) {
                if(page){
                    this.page = page
                    this.size = Number(page) * 10
                }
                this.$store.dispatch('setCrowdfundingOrder',{
                    status: status,
                    size:this.size,
                    page:this.page,
                })
            },
            //确认收货
            receivedOrder(id){
                this.$store.dispatch('setReceivedOrder',{
                    order_id:id
                }).then(res=>{
                    if(res.data == true){
                        Toast(this.$t('lang.order_confirm_receipt'))
                        this.Order(this.status)
                    }
                })
            },
            //立即支付
            onlinepay(id,pay){
                this.$router.push({
                    name:'done',
                    query:{
                        order_sn:id
                    }
                })
            },
        },
        watch:{
			crowdOrderData(){
				if(this.page * this.size == this.crowdOrderData.length){
					this.disabled = false
					this.loading = true
				}else{
					this.loading = false
				}

                this.crowdOrderData = arrRemove.trimSpace(this.crowdOrderData)
			}
		}
    };
</script>
