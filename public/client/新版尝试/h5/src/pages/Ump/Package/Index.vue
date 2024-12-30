<template>
    <div class="con" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
        <div class="package-box">
            <template v-if="packageData.length > 0">
                <div class="li bg-color-write" v-for="(item, index) in packageData" :key="index">
                    <div class="padding-all swiper-x-box">
                        <swiper class="swiper" :options="swiperOption" ref="mySwiper">
                            <swiper-slide v-for="(item, index) in item.goods_list" :key="index">
                                <router-link :to="{name:'goods', params:{id:item.goods_id}}">
                                    <div class="img-box">
                                        <img v-if="item.goods_thumb!=''" class="img" :src="item.goods_thumb" />
                               	        <img v-else class="img" src="../../../assets/img/not_goods.png" />   
                                    </div>
                                    <h4 class="twolist-hidden f-04 color-3 m-top08"> {{ item.goods_name }}</h4>
                                    <p class="f-03 color-9 m-top04">×{{item.goods_number}}</p>
                                    <div class="f-05 color-red m-top04" v-html="item.rank_price_formatted"></div>
                                </router-link>
                            </swiper-slide>
                        </swiper>
                    </div>
                    <!--内容-->
                    <div class="package-cont ">
                        <div class="nav-cont padding-all">
                            <h4 class="f-05 color-3">{{item.act_name}} (
                                <em class="color-red">{{item.package_number}}{{$t('lang.jian')}}</em>)
                            </h4>
                            <p class="f-03  color-9 m-top06">{{$t('lang.label_original_price')}}
                                <del v-html="item.subtotal_formatted"></del>
                            </p>
                            <p class="f-03 color-3 m-top06">
                                <em class="color-9">{{$t('lang.label_package_price')}}</em>
                                <em class="color-red" v-html="item.package_price_formatted"></em> <em class="color-9" v-if="item.saving" v-html="'('+$t('lang.is_discount') + item.saving +')'"></em>
                            </p>
                        </div>
                        <div class="cont border-t-common">
                            <p class="f-03 color-9"><em>{{$t('lang.label_start_end_time')}}</em>{{item.end_time}}</p>
                            <p class="f-03 color-9 m-top06"><em>{{$t('lang.label_brief_desc')}}</em>{{item.act_desc}}</p>
                        </div>
                    </div>
                    <div class="padding-all">
                        <van-button class="br-5 f-06" type="primary" bottom-action @click="onAddCartClicked(item.act_id)">{{$t('lang.button_buy')}}</van-button>
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
        </div>

        <!--初始化loading-->
        <DscLoading :dscLoading="dscLoading"></DscLoading>

        <CommonNav/>
    </div>
</template>
<script>
	import { mapState } from 'vuex'
    
    import {
        Collapse,
        CollapseItem,
        Button,
        Waterfall,
        Loading,
        Toast,
		Dialog
    } from 'vant'

    import {
        swiper,
        swiperSlide
    } from 'vue-awesome-swiper'

    import CommonNav from '@/components/CommonNav'
    import NotCont from '@/components/NotCont'
    import DscLoading from '@/components/DscLoading'
    import arrRemove from '@/mixins/arr-remove'

    export default {
        name: "auction",
        components: {
            swiper,
            swiperSlide,
            CommonNav,
            NotCont,
            DscLoading,
            [Collapse.name]: Collapse,
            [CollapseItem.name]: CollapseItem,
            [Button.name]: Button,
            [Loading.name]: Loading,
            [Toast.name]: Toast,
			[Dialog.name]: Dialog
        },
        data() {
            return {
				//轮播图滑动
                swiperOption: {
                    scrollbarHide: true,
                    slidesPerView: 'auto',
                    centeredSlides: false,
                    grabCursor: true,
                    autoplay: 2500,
                },
				activeNames: ["1"],
                loading:true,
                page:1,
                size:10,
                footerCont:false,
                dscLoading:true,
            }
        },
        directives: {
            WaterfallLower: Waterfall('lower')
        },
        //初始化加载数据
		created() {
			this.packageList(1)
		},
		computed: {
            packageData:{
                get(){
                    return this.$store.state.other.packageData
                },
                set(val){
                    this.$store.state.other.packageData = val
                }
            },
			isLogin() {
			    return localStorage.getItem('token') == null ? false : true
			},
		},
        methods: {
            packageList(page){
                if(page){
                    this.page = page
                    this.size = Number(page) * 10
                }

                this.$store.dispatch('setPackageList',{
                    page:this.page,
                    size:this.size
                })
            },
            onAddCartClicked(id){
    			this.$store.dispatch('setAddPackageCart',{
    				package_id:id,
    				number:1,
    				warehouse_id:'0',
    				area_id:'0',
    				parent_id:'0',
                    confirm_type:3
    			}).then(res => {
                    if (this.isLogin) {
						if(res.error == 0){
							this.$router.push({
								name: 'checkout',
								query: {
									rec_type: res.rec_type
								}
							})
						}else{
                            Toast(res.message)
                        }
					}else{
						let msg = this.$t('lang.login_user_invalid')
						this.notLogin(msg)
					}
    			})
    		},
			notLogin(msg) {
			    let url = window.location.href;
			    Dialog.confirm({
			        message: msg,
			        className: 'text-center'
			    }).then(() => {
			        this.$router.push({
			            name: 'login',
			            query: {
			                redirect: {
			                    name: 'package',
			                    url:url
			                }
			            }
			        })
			    }).catch(() => {
			
			    })
			},
            loadMore(){
                setTimeout(() => {
                    this.disabled = true
                    if(this.page * this.size == this.packageData.length){
                        this.page ++
                        this.packageList()
                    }
                },200)
            },
        },
        watch:{
            packageData(){
                this.dscLoading = false
                if(this.page * this.size == this.packageData.length){
                    this.disabled = false
                    this.loading = true
                }else{
                    this.loading = false
                    this.footerCont = this.page > 1 ? true : false
                }

                this.packageData = arrRemove.trimSpace(this.packageData)
            }
        }
    }
</script>
