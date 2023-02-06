<template>
    <div class="con seckill-box">
        <template v-if="seckillTimeData.list">
            <swiper class="tab-common seckill-tab f-05 bg-color-write text-center tag-bg-color" :options="swiperOption" ref="mySwiper">
                <swiper-slide class="swiper-slide-item" v-for="(item,index) in seckillTimeData.list" :key="index">
                    <div class="item" :class="{'active':firstId == item.id}" @click="handleFilter(item)">
                        <h4>{{item.title}}</h4>
                        <div class="f-02 title" v-if="item.status && !item.soon && !item.is_end">{{$t('lang.panic_buying_underway')}}</div>
                        <div class="f-02 title" v-if="!item.status && item.soon && !item.is_end">{{$t('lang.begin_minute')}}</div>
                        <div class="f-02 title" v-if="!item.status && !item.soon && item.is_end">{{$t('lang.has_ended')}}</div>
                    </div>
                </swiper-slide>
            </swiper>
            <van-swipe :autoplay="3000" class="swipe" style="margin-top:5.2rem">
                <van-swipe-item v-for="(item, index) in seckillTimeData.banner_ads" :key="index" style="position: relative;">
                    <a :href="item.ad_link ? item.ad_link : 'javascript:;'"><img :src="item.ad_code" class="img" v-if="item.ad_code" width="item.ad_width" height="item.ad_height"/>
                    <img class="img" src="../../../assets/img/seckill.jpg" v-else />
                    </a>
                </van-swipe-item>
            </van-swipe>
            <div class="dis-box seckill-heaer f-03" v-if="seckillTime">
                <div class="header-left color-3 box-flex f-weight f-06">{{$t('lang.seckill_limit_more')}}</div>
                <div class="text-right">
                    <div class="dis-box m-top02">
                        <span v-if="status">{{$t('lang.from_end')}}</span><span v-else>{{$t('lang.from_start')}}</span>
                        <template v-if="seckillTime">
                        <count-down class="seckill-time" :endTime="status ? seckillTime.end_time : seckillTime.begin_time" :endText="$t('lang.activity_end')"></count-down>
    					</template>
                    </div>
                </div>
            </div>
            <div class="goods-li" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300" v-if="seckillData && seckillData.length > 0">
                <div class="show bg-color-write li" v-for='(item,index) in seckillData' :key="index" @click="detailClick(item)">
                    <div class="left">
                        <img class="img" :src="item.goods_thumb" />
                    </div>
                        <div class="right bg-color-write">
                        <h4 class="f-04 color-3 twolist-hidden">{{item.goods_name}}</h4>
                        <!--进行中-->
                        <div class="plan-box m-top08 dis-box" v-if="item.status">
                            <div class="box-flex">
                                <div class="dis-box">
                                    <div class="left-title ">
                                        <span class="color" :style="{width:(item.percent > 100 ? 100 : item.percent) + '%'}"></span>
                                    </div>
                                    <em class="f-01 color-7">{{item.percent > 100 ? 100 : item.percent}}%</em>
                                </div>
                            </div>
                            <div class="right-title f-03 color-9 p-l05">{{$t('lang.has_been_robbed')}}{{item.sales_volume}}{{$t('lang.jian')}}</div>
                        </div>
                        <div class="dis-box p-r" :class="{'soon_active':item.soon}">
                            <div class="box-flex m-top08">
                                <div class="color-red f-06">
                                    <span v-html="item.sec_price_formated"></span>
                                    <del class="f-02 color-9" v-html="item.market_price_formated"></del>
                                </div>
                            </div>  
                            <div class="btn-right">
                                <div class="box-flex">
                                    <span v-if="item.status" class="btn-seckill tag-bg-color br-5 f-05 color-white p-a">{{$t('lang.immediately_grab')}}</span>
                                    <span v-if="item.soon" class="btn-seckill btn-low br-5 f-05 color-white p-a">{{$t('lang.begin_minute')}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <template v-else>
                <NotCont></NotCont>
            </template>
            <template v-if="loading">
                <van-loading type="spinner" color="black" />
            </template>
        </template>
        <template v-else>
            <NotCont></NotCont>
        </template>
        <CommonNav/>
    </div>
</template>
<script>
	import { mapState } from 'vuex'

    import {
        Swipe,
        SwipeItem,
        Tab,
        Tabs,
        Row,
        Col,
        Popup,
        Toast,
        Loading,
        Waterfall
    } from 'vant'

    import CountDown from '@/components/CountDown'

    import CommonNav from '@/components/CommonNav'
    import NotCont from '@/components/NotCont'
    import arrRemove from '@/mixins/arr-remove'

    import {
        swiper,
        swiperSlide
    } from 'vue-awesome-swiper'
    
    export default {
        name: "drp-shop",
        components: {
            CommonNav,
            CountDown,
            NotCont,
            [Swipe.name]: Swipe,
            [SwipeItem.name]: SwipeItem,
            [Tab.name]: Tab,
            [Tabs.name]: Tabs,
            [Row.name]: Row,
            [Col.name]: Col,
            [Popup.name]: Popup,
            [Toast.name]: Toast,
            [Loading.name] : Loading,
            swiper,
            swiperSlide,
        },
        directives: {
			WaterfallLower: Waterfall('lower')
		},
        data() {
            return {
                tomorrow:0,     //当前选择时间段tomorrow,0表示今天,1表示明天
                index: '',
                disabled:false,
			    loading:true,
				size:10,
                page:1,
                swiperOption:{
                    notNextTick: true,
                    watchSlidesProgress: true,
                    watchSlidesVisibility: true,
                    slidesPerView: 'auto',
                    lazyLoading: true,
                },
            }
        },
        //初始化加载数据
		created() {
            localStorage.setItem('tomorrow', 0)
            this.time()
		},
		computed: {
			...mapState({
                seckillTimeData: state => state.other.seckillTimeData,
                seckillTime: state => state.other.seckillTime
			}),
            seckillData:{
                get(){
                    return this.$store.state.other.seckillData
                },
                set(val){
                  this.$store.state.other.seckillData = val
                }
            },
            firstId:{
                get(){
                    return this.$store.state.other.firstId
                },
                set(val){
                    this.$store.state.other.firstId = val  
                }
            },
            status:{
                get(){
                    return this.$store.state.other.firstStatus
                },
                set(val){
                    this.$store.state.other.firstStatus = val
                }
            }
		},
        methods: {
            time(){
                this.$store.dispatch('setSeckillTime')
            },
            handleFilter(item) {
                this.tomorrow = item.tomorrow || 0 
                this.firstId = item.id
                this.$store.state.other.seckillTime =''
                this.$store.state.other.seckillData =[]
                this.status = item.status
                this.seckillClick(1)
                localStorage.setItem('tomorrow', this.tomorrow)
            },
            seckillClick(page) {
                this.loading = true
                if(page){
                    this.page = page
                    this.size = Number(page) * 10
                }

				this.$store.dispatch('setSeckill',{
                    id:this.firstId,
                    tomorrow: this.tomorrow,
                    page: this.page,
                    size: this.size,
                })
            },
            detailClick(item) {
                this.$router.push({
                    name: "seckill-detail",
                    query: {
                        seckill_id: item.id,
                        tomorrow:localStorage.getItem('tomorrow')
                    }
                })
            },
            //数据分页
            loadMore(){
                setTimeout(() => {
                    this.disabled = true
                    if(this.page * this.size == this.seckillData.length){
                        this.page ++
                        this.seckillClick()
                    }
                },200)
            }
        },
        watch:{
			seckillData(){
                this.loading = false
				if(this.page * this.size == this.seckillData.length){
					this.disabled = false
					this.loading = true
				}else{
					this.loading = false
				}

                this.seckillData = arrRemove.trimSpace(this.seckillData)
			},
            firstId(){
                this.tomorrow = this.status ? 0 : 1
                this.seckillClick(1)
            }
		}
    };
</script>
