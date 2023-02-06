<template>
    <div class=" con" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
        <div class="team-order con_main cro_funing">
            <van-tabs :active="active" class="team-tabs">
                <van-tab v-for="(item,index) in tabs" :key="index">
                    <div slot="title" class="nav_active" @click="teamNav(item)">{{item}}</div>
                </van-tab>
            </van-tabs>
            <!--商品列表-->
            <div class="goods-li" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300" v-if="crowdMyFocusData!=''">
                <router-link :to="{name:'crowdfunding-detail', params:{id:item.id}}" class="show bg-color-write li"  v-for='(item,index) in crowdMyFocusData' :key="index"
                    @click="detailClick(item)">
                    <div class="left p-r">
                        <img class="img" :src="item.title_img" v-if="item.title_img" />
                        <img class="img" src="../../../../assets/img/not_goods.png" v-else />
                        <div class="raise-cate-tag"><i class="iconfont icon-gerenzhongxin icon-geren"></i>{{item.join_num}}</div>
                    </div>
                    <div class="right">
                        <h4 class="f-05 color-3">{{item.title}}</h4>
                        <div class="goods-num">
                            <div class="list f-02 color-9">{{$t('lang.support_number')}}
                                <span class="color-red">{{item.join_num}}</span>{{$t('lang.ren')}}
                            </div>
                            <div class="list f-02 color-9">{{$t('lang.time_remaining')}}{{item.shenyu_time}}{{$t('lang.tian')}}</div>
                        </div>
                        <div class="ect-progress dis-box">
                            <p class="wrap box-flex">
                                <span class="bar" :style="{width:item.baifen_bi + '%'}">
                                    <i class="color"></i>
                                </span>
                            </p>
                            <p class="txt f-02">{{item.baifen_bi}}%</p>
                        </div>
                        <div class="goods-cont">
                            <div class="list f-02 color-9">{{$t('lang.label_has_crowdfunding')}}
                                <span class="color-red">{{item.join_money}}</span>
                            </div>
                            <div class="list f-02 color-9">{{$t('lang.label_target')}}
                                <span class="color-red">{{item.amount}}</span>
                            </div>
                        </div>
                    </div>
                </router-link>
            </div>
            <div v-else>
                <NotCont/>
            </div>
            <template v-if="loading">
                <van-loading type="spinner" color="black" />
            </template>
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
    import { mapState } from 'vuex'
    
    import {
        Tab,
        Tabs,
        Row,
        Col,
        Loading,
        Waterfall,
    } from 'vant'

    import CommonNav from '@/components/CommonNav'
    import NotCont from '@/components/NotCont'
    import arrRemove from '@/mixins/arr-remove'

    export default {
        name: "bargain-order",
        components: {
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
                tabs: [this.$t('lang.all_attention'), this.$t('lang.underway'), this.$t('lang.have_succeeded')],
            };
        },
        //初始化加载数据
        created() {
             this.Buy(this.status)
        },
        computed: {
            crowdMyFocusData:{
                get(){
                    return this.$store.state.crowdfunding.crowdMyFocusData
                },
                set(val){
                    this.$store.state.crowdfunding.crowdMyFocusData = val
                }
            }
        },
        methods: {
            teamNav(item) {
                if(item ==this.$t('lang.all_attention')){
                    this.status = 0
                }else if(item==this.$t('lang.underway')){
                    this.status = 1
                }else{
                    this.status = 2
                }
                this.Buy(this.status)
            },
            loadMore(){
				setTimeout(() => {
					this.disabled = true
					if(this.page * this.size == this.crowdMyFocusData.length){
						this.page ++
                        this.Buy(this.status)	
					}
				},200)
			},
            Buy(status,page) {
                if(page){
                    this.page = page
                    this.size = Number(page) * 10
                }

                this.$store.dispatch('setCrowdfundingMyFocus',{
                    status: status,
                    size:this.size,
                    page:this.page,
                });
            },
        },
        watch:{
			crowdMyFocusData(){
				if(this.page * this.size == this.crowdMyFocusData.length){
					this.disabled = false
					this.loading = true
				}else{
					this.loading = false
				}

                this.crowdMyFocusData = arrRemove.trimSpace(this.crowdMyFocusData)
			}
		}
    };
</script>
