<template>
	<div class="con">
		<div class="con_main goods">
			<!--轮播图-->
			<van-swipe :autoplay="3000" class="swipe" :height="swipe_height">
				<van-swipe-item v-for="(item, index) in auctionGoodsData.goods_img" :key="index">
					<img class="imgalt" :src="item" />
				</van-swipe-item>
			</van-swipe>
			<!--title-->
			<div class="cont-box">
				<div class="dis-box price-box">
					<div class="left box-flex">
						<h4 class="color-white">
							<div class="f-weight" v-html="auction.formated_current_price"></div>
							<div class="tag-price dis-box">
								<div class="left-tag dis-box">
									<div class="left-icon">
										<i class="iconfont icon-xidi-paimai f-01"></i>
									</div>
									<div class="box-flex tag-right-cont f-01">{{$t('lang.auction')}}</div>
								</div>
								<div class="box-flex">
									<label class="p-l05 f-01">{{$t('lang.label_au_end_price')}}
										<template v-if="auction.no_top == 0">
											<span v-html="auction.formated_end_price"></span>
										</template>
										<template v-else>
											<span>{{$t('lang.uncapped')}}</span>
										</template>
									</label>
								</div>
							</div>
						</h4>
					</div>
					<div class="right">
						<div class="time-title f-02 text-center m-top06">
							<template v-if="auction.status_no == 0">{{$t('lang.activities_not_started')}}</template>
							<template v-else-if="auction.status_no == 1">{{$t('lang.were_still_end')}}:</template>
							<template v-else>{{$t('lang.activities_end')}}</template>
						</div>
						<div class="f-02 color-white time m-top02">
							<template v-if="auction.status_no == 1">
								<div v-if="auction.startTime!=undefined && auction.endTime!=undefined">
									<count-down class="seckill-time" :endTime="auction.endTime" :endText="$t('lang.activities_end')"></count-down>
								</div>
							</template>
							<template v-else-if="auction.status_no == 2">
								<span class="color-red">{{auction.bid_time}}</span>
							</template>
						</div>
					</div>
				</div>
				<div class="title bg-color-write  padding-all">
					<div class="flex-common ai-start">
                        <div class="flex1">
							<span class="f-05 color-3 f-weight">{{auction.goods_name}}</span>
							<div class="f-03 color-9 m-top08">{{$t('lang.gb_deposit')}} <span v-html="auction.formated_deposit"></span></div>
						</div>
						<div class="drp-share" @click="onGoodsShare">
                            <i class="iconfont icon-share"></i>
                            <span>{{$t('lang.share')}}</span>
                        </div>
					</div>
					<div class="dis-box color-9 f-03 m-top04">
						<div class="box-flex">{{$t('lang.au_amplitude')}} <span v-html="auction.formated_amplitude"></span></div>
						<div class="box-flex">
							<p class="text-right">{{$t('lang.au_mechanism')}} {{auctionGoodsData.auction_goods.rz_shop_name}}</p>
						</div>
					</div>
				</div>

				<div class="goods_number padding-all bg-color-write">
					<template v-if="auction.status_no == 0">
						<div class="f-03 color-9">{{$t('lang.not_start')}}</div>
					</template>
					<template v-else-if="auction.status_no == 1">
						<div class="f-03 color-9">{{$t('lang.label_min_fare')}}<span class="color-red">{{auction.amplitude}} * N</span></div>
						<div class=" m-top08 stepper">
							<van-stepper class="of-hidden" v-model="price" integer :min="auction.current_price" :step="auction.amplitude" :max="end_price" />
						</div>
					</template>
					<template v-else>
						<div class="f-03 color-red" v-if="auction.is_winner">{{$t('lang.is_winner_one')}}{{auction.last_bid.formated_bid_price}}{{$t('lang.is_winner_two')}}</div>
						<div class="f-03 color-red" v-else>{{$t('lang.has_ended')}}</div>
					</template>
				</div>
			</div>
			<!--竞拍流程-->
			<div class="auction-process">
				<van-cell-group class="m-top10 common-cell">
					<van-cell class="f-04" :title="$t('lang.bidding_process')" is-link :value="$t('lang.view')" @click="auctionProcess" />
				</van-cell-group>
				<van-actionsheet class="auction-box" v-model="processShow" :title="$t('lang.bidding_process')">
					<div class="cont padding-all">
						<div class="li ">
							<h4 class="f-05 color-3">{{$t('lang.bidding_process_1')}}</h4>
							<p class="f-03 color-7">{{$t('lang.bidding_process_1_propmt')}}</p>
						</div>
						<div class="li m-top06">
							<h4 class="f-05 color-3">{{$t('lang.bidding_process_2')}}</h4>
							<p class="f-03 color-7">{{$t('lang.bidding_process_2_propmt')}} </p>
						</div>
						<div class="li m-top06">
							<h4 class="f-05 color-3">{{$t('lang.bidding_process_3')}}</h4>
							<p class="f-03 color-7">{{$t('lang.bidding_process_3_propmt')}}</p>
						</div>
						<div class="li m-top06">
							<h4 class="f-05 color-3">{{$t('lang.bidding_process_4')}}</h4>
							<p class="f-03 color-7">{{$t('lang.bidding_process_4_propmt')}} </p>
						</div>
						<div class="li m-top06">
							<h4 class="f-05 color-3">{{$t('lang.bidding_process_5')}}</h4>
							<p class="f-03 color-7">{{$t('lang.bidding_process_5_propmt')}} </p>
						</div>
					</div>
				</van-actionsheet>
			</div>
			<!--出价记录-->
			<div class="auction-price" v-if="auctionGoodsData.auction_count!=0">
				<router-link :to="{name:'auction-log',params:{act_id:auction.act_id}}">
					<van-cell-group class="m-top10 common-cell">
						<van-cell class="f-04" :title="$t('lang.bid_record')" is-link :value="auctionGoodsData.auction_count +'人'" />
					</van-cell-group>
				</router-link>
				<!--list-->
				<div class="list bg-color-write" v-for="(item,index) in auctionGoodsData.auction_log" :key="index">
					<div class="dis-box ">
						<div class="box-flex f-03 color-3">
							<van-tag type="danger" class="m-r05  br-100 btn-submit" v-if="index==0">{{$t('lang.au_bid_ok')}}</van-tag>
							<van-tag type="danger" class="m-r05  br-100 btn-default" v-else>{{$t('lang.out')}}</van-tag>
							<span class="f-04 color-3">{{item.user_name}}</span>
						</div>
						<div class="f-02 color-9">{{item.bid_time}}</div>
					</div>
					<div class="f-04 color-red" v-html="item.formated_bid_price"></div>
				</div>
				<router-link :to="{name:'auction-log',params:{act_id:auction.act_id}}" v-if="auctionGoodsData.auction_count>2"
				    class="show aut-btn padding-all bg-color-write">
					<van-button size="large" class="f-05 color-7">{{$t('lang.view_more')}}</van-button>
				</router-link>
			</div>
			<van-cell-group class="m-top08 common-cell">
				<van-cell class="f-04" :title="$t('lang.rec_au')" />
			</van-cell-group>
			<div class="padding-all swiper-x-box bg-color-write">
				<swiper class="swiper" :options="swiperOption" ref="mySwiper">
					<swiper-slide v-for="(item, index) in auctionGoodsData.hot_goods" :key="index">
						<router-link :to="{name:'auction-detail',params:{act_id:item.act_id}}">
							<div class="img-box">
								<img v-if="item.goods_thumb" class="img" :src="item.goods_thumb" />
								<img v-else class="img" src="../../../assets/img/no_image.jpg" />
							</div>
							<h4 class="twolist-hidden f-04 color-3 m-top08"> {{ item.goods_name }}</h4>
							<div class="f-05 color-red m-top06" v-html="item.formated_start_price"></div>
						</router-link>
					</swiper-slide>
				</swiper>
			</div>
			<!--详情-->
			<van-tabs v-model="active" class="m-top08">
				<van-tab v-for="(item,index) in tabs" :title="item" :key="index">
					<template v-if="index == 0">
						<div v-html="auction.act_desc" v-if="auction.act_desc"></div>
						<NotCont v-else />
					</template>
					<template v-else-if="index == 1">
						<div v-html="auction.act_promise" v-if="auction.act_promise"></div>
						<NotCont v-else />
					</template>
					<template v-else>
						<div v-html="auction.act_ensure" v-if="auction.act_ensure"></div>
						<NotCont v-else />
					</template>
				</van-tab>
			</van-tabs>
			<!-- 底部版权 -->
			<dsc-copyright></dsc-copyright>
			<van-goods-action>
				<van-goods-action-mini-btn icon="chat" :text="$t('lang.customer_service')" @click="onChat(auction.goods_id,auction.user_id)" />
				<van-goods-action-mini-btn :icon="collect_icon" :text="$t('lang.collect')" :class="{'curr':is_collect == 1}" @click="collection" />
				<van-goods-action-mini-btn icon="shop" :text="$t('lang.shop')" v-if="goodsInfo.user_id" :to="{name:'shopHome',params:{id:goodsInfo.user_id}}" />
				<template v-if="auction.status_no === 0">
					<van-goods-action-big-btn :text="$t('lang.not_start')"/>
				</template>
				<template v-else-if="auction.status_no === 1">
					<van-goods-action-big-btn :text="$t('lang.me_bid')" primary  @click="auctionBid"/>
				</template>
				<template v-else>
					<van-goods-action-big-btn v-if="auction.is_winner === 1" :text="$t('lang.button_buy')" primary  @click="auctionBuy"/>
          			<van-goods-action-big-btn v-else :text="$t('lang.has_ended')" />
				</template>
			</van-goods-action>
		</div>

		<!--分享海报-->
        <van-popup v-model="shareImgShow" class="shareImg" overlay-class="shareImg-overlay">
            <img :src="shareImg" v-if="shareImg" class="img" />
            <span v-else>{{$t('lang.error_generating_image')}}</span>
        </van-popup>

		<CommonNav/>
	</div>
</template>

<script>
import { mapState } from 'vuex'
import {
	Swipe,
	SwipeItem,
	GoodsAction,
	GoodsActionBigBtn,
	GoodsActionMiniBtn,
	Actionsheet,
	Cell,
	CellGroup,
	Tab,
	Tabs,
	Tag,
	Button,
	Toast,
	Popup,
	RadioGroup,
	Radio,
	Stepper,
	Dialog
} from 'vant'

import {
	swiper,
	swiperSlide
} from 'vue-awesome-swiper'

import commonGet from '@/mixins/common-get'
import CommonNav from '@/components/CommonNav'
import NotCont from '@/components/NotCont'
import CountDown from '@/components/CountDown'
import formProcessing from '@/mixins/form-processing'

export default {
	name: "auction-detail",
	mixins: [formProcessing],
	data() {
		return {
			viewStatus:true,
			tabs: [this.$t('lang.au_introduce'), this.$t('lang.service_guarantee'), this.$t('lang.au_raiders')],
			processShow: false,
			active: 0,
			//轮播图滑动
			swiperOption: {
				scrollbarHide: true,
				slidesPerView: 'auto',
				centeredSlides: false,
				grabCursor: true,
				autoplay: 2500,
			},
			value:1,
			act_id:this.$route.params.act_id,
			shareImgShow:false,
			shareImg:''
		}
	},
	components: {
		CommonNav,
		CountDown,
		NotCont,
		swiper,
		swiperSlide,
		[Swipe.name]: Swipe,
		[SwipeItem.name]: SwipeItem,
		[GoodsAction.name]: GoodsAction,
		[GoodsActionBigBtn.name]: GoodsActionBigBtn,
		[GoodsActionMiniBtn.name]: GoodsActionMiniBtn,
		[Actionsheet.name]: Actionsheet,
		[Cell.name]: Cell,
		[CellGroup.name]: CellGroup,
		[Tab.name]: Tab,
		[Tabs.name]: Tabs,
		[Tag.name]: Tag,
		[Button.name]: Button,
		[Toast.name]: Toast,
		[Popup.name]: Popup,
		[RadioGroup.name]: RadioGroup,
		[Radio.name]: Radio,
		[Stepper.name]: Stepper,
		[Dialog.name] : Dialog,
	},
	//初始化加载数据
	created() {
		//loading
		commonGet.loadingData()

		//初始化加载数据
		this.detail(this.act_id)
	},
	computed: {
		...mapState({
			auctionGoodsData: state => state.auction.auctionGoodsData,
		}),
		goodsInfo(){
			return this.auctionGoodsData.auction_goods
		},
		auction(){
			return this.auctionGoodsData.auction
		},
		auction_log(){
			return this.auctionGoodsData.auction_log
		},
		isLogin(){
	    	return localStorage.getItem('token') == null ? false : true
	    },
	    goodsCollectStatue(){
	    	return this.$store.state.user.goodsCollectStatue
	    },
	    is_collect:{
			get(){
				return this.auction.is_collect
			},
			set(val){
				this.auction.is_collect = val
			}
	    },
	    collect_icon(){
			return this.is_collect == 1 ? 'like' : 'like-o'
		},
		price:{
			get(){
				return this.auction.current_price_int
			},
			set(val){
				this.auction.current_price_int = val
			}
		},
		end_price(){
			let end_price
			if(this.auction.end_price > 0){
				end_price = this.auction.end_price
			}

			return end_price
		}
	},
	methods: {
		//详情数据
		detail(act_id){
			this.$store.dispatch('setAuctionGoods',{
				id: act_id
			})
		},
		//出价
		auctionBid(){
			this.$store.dispatch('setAuctionBid',{
				id: this.act_id,
				price_times:this.price
			}).then(({ data })=>{
				if(data.error == 0){
					Toast.success(this.$t('lang.button_bid_succeed'))
					this.detail(this.act_id);
				}else{
					Toast({
						message: data.msg
					})
				}
			})
		},
		//立即购买
		auctionBuy(){
			this.$store.dispatch('setAuctionBuy',{
				id: this.act_id,
			}).then(({data})=>{
				if(data.error == 0){
					this.$router.push({
	                    name: 'checkout',
	                    query: {
	                        rec_type: data.flow_type,
	                        type_id: data.extension_id,
	                    }
	                })
				}else{
					Toast(data.msg)
				}
			})
		},
		//竞拍流程
		auctionProcess() {
			this.processShow = !this.processShow
		},
		//收藏
	    collection(){
			if(this.isLogin){
				this.$store.dispatch('setCollectGoods',{
					goods_id:this.auction.goods_id,
					status:this.is_collect
				})
			}else{
				let msg = this.$t('lang.fill_in_user_collect_goods')
				this.notLogin(msg)
			}
		},
		notLogin(msg){
			//未登录提示
			Dialog.confirm({
				message:msg,
				className:'text-center'
			}).then(()=>{
				this.$router.push({
					path: '/login',
					query:{ 
						redirect: {
							name:'team-detail',
							params:{ 
								id:this.$route.query.id,
								goods_id:this.goodsInfo.goods_id 
							}
						}
					}
				})
			}).catch(()=>{

			})
		},
		//分享海报
        onGoodsShare(){
            if (this.isLogin) {
                Toast.loading({ duration: 0, mask: true, forbidClick: true, message: this.$t('lang.loading') })
                let price = this.auction.current_price

                this.$store.dispatch('setGoodsShare',{
                    goods_id:this.auction.goods_id,
                    price: price,
                    share_type:0,
                    extension_code:'auction',
                    code_url:window.location.href,
                    thumb:this.auctionGoodsData.goods_img[0]
                }).then(res => {
                    if(res.status == 'success'){
                        this.shareImg = res.data
                        this.shareImgShow = true
                        Toast.clear()
                    }
                })
            } else {
                let msg = this.$t('lang.login_user_not')
                this.notLogin(msg)
            }
        }
	},
	watch:{
		'$route'(to, from) {
            this.detail(to.params.act_id)
        },
	    goodsCollectStatue(){
			//关注跟踪变化
			this.goodsCollectStatue.forEach((v)=>{
				if(v.id == this.auction.goods_id){
					this.is_collect = v.status
				}
			})
	    },
	    auctionGoodsData(){
	    	//设置title
            document.title = this.auction.act_name;

            //单独设置微信分享信息
            this.$wxShare.share({
                title:this.auction.goods_name,
                desc:this.auction.act_desc,
                link:`${window.ROOT_URL}mobile#/auction/detail/` + this.auction.act_id,
                imgUrl:this.auctionGoodsData.goods_img[0]
            })
	    }
	}
}
</script>
