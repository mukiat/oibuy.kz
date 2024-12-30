<template>
    <div class="con" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
        <TabSwitch @child-event="CommonTabs" :data='tabs'></TabSwitch>
        <template v-if="!loading">
            <div class="team-order">
                <template v-if="teamOrderData && teamOrderData.length > 0">
                    <div class="goods-li">
                        <div v-for='(item,index) in teamOrderData' :key="index" class="m-top08  p-r">
                            <a href="javascript:;" @click="detailLink(item.goods_id)" class="show li">
                                <div class="left">
                                    <img class="img" :src="item.goods_thumb" />
                                </div>
                                <div class="right bg-color-write">
                                    <h4 class="f-05 color-3 twolist-hidden"> {{item.goods_name}}</h4>
                                    <div class="dis-box cont">
                                        <div class="f-02 color-9 box-flex">{{$t('lang.already_have')}}{{item.limit_num}}{{$t('lang.men_tuxedo')}}</div>
                                    </div>
                                    <div class="f-06 color-red f-weight m-top06" v-html="item.team_price"></div>
                                </div>
                            </a>    
                            <div class="team-order-btn bg-color-write of-hidden ">
                                <div class="fr">
                                    <a v-if="item.is_pay == 0 && item.status == 0" href="javascript:void(0)" @click="onlinepay(item.order_sn)"
                                    class="plain-btn plain-btn-main">{{$t('lang.immediate_pay')}}</a>
                                    <router-link :to="{name:'orderDetail',params:{id:item.order_id}}" class="plain-btn plain-btn-default">{{$t('lang.view_order')}}</router-link>
                                    <a v-if="item.is_pay == 1 || item.status == 2" href="javascript:;" @click="waitHandle(item.team_id,item.user_id)" class="plain-btn plain-btn-main">{{$t('lang.team_schedule')}}</a>
                                </div>
                            </div>
                            <img v-if="contIndex == 1" class="tag-img" src="../../../../assets/img/s-bg.png">
                            <!--失败的拼团-->
                            <img v-if="contIndex == 2" class="tag-img" src="../../../../assets/img/sb-bg.png">
                        </div>
                    </div>
                </template>
                <template v-else>
                    <NotCont/>
                </template>
            </div>
        </template>
        <template v-else>
            <van-loading type="spinner" color="black" />
        </template>
        <TeamTabbar />
        <CommonNav :routerName="routerName"></CommonNav>
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
        Waterfall
    } from 'vant'

    import CommonNav from '@/components/CommonNav'
    import TabSwitch from '@/components/filter/TabSwitch'
    import NotCont from '@/components/NotCont'
    import TeamTabbar from './TeamTabbar'
    import arrRemove from '@/mixins/arr-remove'
    
    export default {
        name: "team-order",
        components: {
            CommonNav,
            TabSwitch,
            NotCont,
            TeamTabbar,
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
                routerName:'team',
                disabled:false,
			    loading:false,
				size:1,
                page:10,
                status:0,
                active:0,
                contIndex:'',
                tabs: [this.$t('lang.teaminfo_ing'), this.$t('lang.teaminfo_success'), this.$t('lang.teaminfo_fail')],
                team_id:0
            };
        },
        created() {
            setTimeout(() => {
              uni.getEnv(function(res){
                  if(res.plus || res.miniprogram){
                      uni.redirectTo({  
                          url:'../../pagesA/team/order/order'
                      })
                  }
              })
            },100)

            this.teamOrder(1)
        },
        computed: {
            teamOrderData:{
                get(){
                    return this.$store.state.team.teamOrderData
                },
                set(val){
                    this.$store.state.team.teamOrderData = val
                }
            }
        },
        methods: {
            CommonTabs(index) {
                this.status = index
                this.contIndex = index
                this.loading = true                
                this.teamOrder(1)
            },
            teamOrder(page) {
                if(page){
                    this.page = page
                    this.size = Number(page) * 10
                }

                this.$store.dispatch('setTeamOrder',{
                    status: this.status,
                    size:this.size,
                    page:this.page,
                });
            },
            //立即支付
            onlinepay(id){
              this.$router.push({
                name:'done',
                query:{
                  order_sn:id
                }
              })
            },
            detailLink(goods_id){
                this.$router.push({
                    name:'team-detail',
                    query:{
                        team_id:this.team_id,
                        goods_id:goods_id
                    }
                })
            },
            //拼团进度
            waitHandle(team_id,user_id){
                this.$router.push({
                    name:'team-wait',
                    query:{
                        team_id:team_id,
                        user_id:user_id
                    }
                })
            },
            loadMore(){
                setTimeout(() => {
                    this.disabled = true
                    if(this.page * this.size == this.teamOrderData.length){
                        this.page ++
                        this.teamOrder()
                    }
                },200)
            },
        },
        watch:{
			teamOrderData(){
				if(this.page * this.size == this.teamOrderData.length){
					this.disabled = false
					this.loading = true
				}else{
					this.loading = false
				}

                this.teamOrderData = arrRemove.trimSpace(this.teamOrderData)
			}
		}
    };
</script>
