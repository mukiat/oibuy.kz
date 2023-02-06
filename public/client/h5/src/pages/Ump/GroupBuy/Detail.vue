<template>
	<div class="con_main">
		<div class="goods goods_info">
            <div class="goods_photo">
                <div class="goods_photo_warp">
        			<!--轮播图-->
        			<van-swipe :autoplay="3000" class="swipe" :height="swipe_height">
                        <template v-if="gallery_list && gallery_list.length > 0">
            				<van-swipe-item v-for="(item, index) in gallery_list" :key="index" style="position: relative;">
            					<img class="imgalt" :src="item.img_url" />
            				</van-swipe-item>
                        </template>
                        <template v-else>
                            <van-swipe-item style="position: relative;">
                                <img :src="goodsInfo.goods_img" class="imgalt" v-if="goodsInfo.goods_img"/>
                                <img src="../../../assets/img/no_image.jpg" class="imgalt" v-else/>
                            </van-swipe-item>
                        </template>
        			</van-swipe>
                    <div class="videoBox" v-if="groupbuyDetailData.goods_video_path">
                        <a href="javascript:void(0);" @click="handleVideo">
                            <van-icon name="play" size="1rem"/>
                            {{$t('lang.video')}}
                        </a>
                    </div>
                </div>
            </div>
			<!--title-->
			<div class="cont-box">
				<div class="dis-box price-box">
					<div class="left box-flex">
						<h4 class="color-white">
							<div class="f-weight">
								<span v-html="groupbuyDetailData.formated_cur_price"></span>
								<van-tag mark type="danger" class="f-01" v-if="groupbuyDetailData.zhekou < 10">{{groupbuyDetailData.zhekou}}{{$t('lang.zhe')}}</van-tag>
							</div>
							<div class="tag-price dis-box">
								<div class="left-tag dis-box">
									<div class="left-icon">
										<i class="iconfont icon-shehuituanti f-01"></i>
									</div>
									<div class="box-flex tag-right-cont f-01">{{$t('lang.group_buy')}}</div>
								</div>
								<div class="box-flex">
									<label class="p-l05 f-01">{{$t('lang.market_price')}}<del v-html="groupbuyDetailData.goods.market_price"></del></label>
								</div>
							</div>
						</h4>
					</div>
					<div class="right">
						<div class="time-title f-02 text-center m-top06" v-if="groupbuyDetailData.is_end == 0">{{$t('lang.were_still_end')}}</div>
                        <div class="time-title f-02 text-center m-top14" v-else>{{$t('lang.activities_end')}}</div>
						<div class="f-02 color-white time m-top02" v-if="groupbuyDetailData.is_end == 0">
							<div v-if="groupbuyDetailData.xiangou_start_date!=undefined && groupbuyDetailData.xiangou_end_date!=undefined">
							<count-down :endTime="groupbuyDetailData.xiangou_end_date" :endText="$t('lang.activity_end')"></count-down>
							</div>
						</div>
					</div>
				</div>
				<div class="title bg-color-write f-06 color-3 padding-all">
					<div class="flex-common ai-start">
                        <div class="flex1">
							<van-tag type="danger" class="m-r05 f-03" v-if="groupbuyDetailData.user_id == 0">{{$t('lang.self_support')}}</van-tag>
							<span class="f-05 color-3 f-weight">{{groupbuyDetailData.goods_name}}</span>
						</div>
						<div class="drp-share" @click="onGoodsShare">
                            <i class="iconfont icon-share"></i>
                            <span>{{$t('lang.share')}}</span>
                        </div>
					</div>
					<div class="color-red f-03 m-top04">{{groupbuyDetailData.title_subtitle}}</div>
					<div class="f-03 color-9 m-top06">{{$t('lang.gb_deposit')}} <span v-html="groupbuyDetailData.formated_deposit"></span></div>
					<div class="dis-box color-9 f-03 m-top06">
						<div class="box-flex">{{$t('lang.sales_volume')}}{{groupbuyDetailData.goods.sales_volume}}</div>
						<div class="box-flex text-center">{{$t('lang.stock')}}{{groupbuyDetailData.goods.goods_number}}</div>
						<div class="box-flex">
							<p class="text-right" v-if='groupbuyDetailData.restrict_amount > 0'>{{$t('lang.gb_limited')}}{{groupbuyDetailData.restrict_amount}}</p>
							<p class="text-right" v-else>{{$t('lang.not_gb_limited')}}</p>
						</div>
					</div>
				</div>
			</div>
			<!--阶梯价格-->
			<div class="auction-process">
				<van-cell-group class="m-top08 common-cell">
					<van-cell class="f-04" :title="$t('lang.tiered_price')" is-link :value="$t('lang.view')" @click="auctionProcess" />
				</van-cell-group>
				<van-actionsheet class="auction-box" v-model="processShow" :title="$t('lang.tiered_price')">
					<div class="padding-all groupbuy-price-list">
						<div class="li dis-box text-center color-red f-04">
							<div class="box-flex">{{$t('lang.number')}}</div>
							<div class="box-flex">{{$t('lang.price')}}</div>
						</div>
						<div class="li dis-box text-center color-3 f-04 m-top06" v-for="(item , index) in groupbuyDetailData.price_ladder" :key="index">
							<div class="box-flex">{{item.amount}}</div>
							<div class="box-flex"><span v-html="item.formated_price"></span></div>
						</div>
					</div>
				</van-actionsheet>
			</div>
			<div class="ect-group dis-box m-top08 ect-server p-r" @click="goodsService">
				<div class="left-cell">{{$t('lang.label_service')}}</div>
				<div class="box-flex right-cell" style="align-items: center;">
					<p class="f-04 color-3">{{$t('lang.you')}} <span class="color-red" v-if="groupbuyDetailData.basic_info">{{ groupbuyDetailData.basic_info.shop_name }}</span> {{$t('lang.deliver_aftersale_service')}}</p>
					<div class="dis-box m-top08 g-r-rule" v-if="goodsInfo.goods_extend && (goodsInfo.goods_extend.is_reality || goodsInfo.goods_extend.is_return || goodsInfo.goods_extend.is_fast)">
						<div class="f-02 color-9" v-if="goodsInfo.goods_extend && goodsInfo.goods_extend.is_reality">
							<em class="fl em-promotion">
								<i class="iconfont icon-daifukuan f-01"></i>
							</em>
							<span class="fl p-l05">{{$t('lang.pay_delivery')}}</span>
						</div>
						<div class="f-02 color-9" v-if="goodsInfo.goods_extend && goodsInfo.goods_extend.is_return">
							<em class="fl em-promotion">
								<i class="iconfont icon-7tianwuliyoutuihuo f-01"></i>
							</em>
							<span class="fl p-l05">{{$t('lang.is_return')}}</span>
						</div>
						<div class="f-02 color-9" v-if="goodsInfo.goods_extend && goodsInfo.goods_extend.is_fast">
							<em class="fl em-promotion">
								<i class="iconfont icon-tixingnaozhong f-01"></i>
							</em>
							<span class="fl p-l05">{{$t('lang.is_fast')}}</span>
						</div>
					</div>
				</div>
			</div>
			<!--服务弹框-->
			<van-actionsheet class="auction-box" v-model="serviceShow" :title="$t('lang.service_note')">
				<ul>
					<li class="padding-all" v-if="goodsInfo.goods_extend && goodsInfo.goods_extend.is_reality">
						<p class="dis-box">
							<label class="box-flex">
								<i class="iconfont icon-daifukuan color-red f-06"></i>
								<span class="p-l05 f-05 color-3">{{$t('lang.pay_delivery')}}</span>
							</label>
						</p>
						<p class="f-03 color-7 m-top04">{{$t('lang.service_note_propmt_1')}}</p>
					</li>
					<li class="b-color-f padding-all" v-if="goodsInfo.goods_extend && goodsInfo.goods_extend.is_return">
						<p class="dis-box ">
							<label class="box-flex">
								<i class="iconfont icon-7tianwuliyoutuihuo color-red f-06"></i>
								<span class="p-l05 f-05 color-3">{{$t('lang.is_return')}}</span>
							</label>
						</p>
						<p class="f-03 color-7 m-top04">{{$t('lang.service_note_propmt_2')}}</p>
					</li>
					<li class="b-color-f padding-all" v-if="goodsInfo.goods_extend && goodsInfo.goods_extend.is_fast">
						<p class="dis-box">
							<label class="box-flex">
								<i class="iconfont icon-tixingnaozhong color-red f-06"></i>
								<span class="p-l05 f-05 color-3">{{$t('lang.is_fast')}}</span>
							</label>
						</p>
						<p class="f-03 color-7 m-top04">{{$t('lang.service_note_propmt_3')}}</p>
					</li>
				</ul>
			</van-actionsheet>
			<van-cell-group class="van-cell-noleft m-top08">
				<van-cell :title="$t('lang.label_selected')" v-model="attr_name" is-link @click="skuLink" />
			</van-cell-group>
			<van-cell-group class="m-top08 common-cell" v-if="groupbuyDetailData.merchant_group && groupbuyDetailData.merchant_group.length>0">
				<van-cell class="f-04" :title="$t('lang.rec_groupbuy')" />
			</van-cell-group>
			<div class="padding-all swiper-x-box bg-color-write" v-if="groupbuyDetailData.merchant_group && groupbuyDetailData.merchant_group.length>0">
				<swiper class="swiper" :options="swiperOption" ref="mySwiper">
					<swiper-slide v-for="(item, index) in groupbuyDetailData.merchant_group" :key="index">
						<router-link :to="{name:'groupbuy-detail',params:{group_buy_id:item.act_id}}">
							<div class="img-box">
								<img class="img" :src="item.goods_thumb" />
							</div>
							<h4 class="twolist-hidden f-04 color-3 m-top08"> {{ item.goods_name }}</h4>
							<div class="f-05 color-red m-top06" v-html="item.shop_price"></div>
						</router-link>
					</swiper-slide>
				</swiper>
			</div>
			<!--详情-->
			<van-tabs v-model="active" class="m-top08 bg-color-write">
				<van-tab v-for="(item,index) in tabs" :title="item" :key="index">
					<template v-if="index == 0">
                        <template v-if="goodsInfo.goods_desc">
                            <div v-html="goodsInfo.goods_desc" class="goods_desc"></div>
                        </template>
                        <template v-else>
                            <NotCont />
                        </template>
                    </template>
                    <template v-else-if="index == 1">
                        <template v-if="groupbuyDetailData.act_desc">
                            <div class="goods_desc padding-all">
                            	<p>{{ groupbuyDetailData.act_desc }}</p>
                            </div>
                        </template>
                        <template v-else>
                            <NotCont />
                        </template>
                    </template>
                    <template v-else>
                        <template v-if="goodsInfo.attr_parameter">
                            <div class="goods_attr_parameter">
                            <table cellpadding="0" cellspacing="1" width="100%" border="0" class="Ptable param_table">
                                <tbody>
                                    <tr><td>{{$t('lang.goods_sn')}}</td><td>{{ goodsInfo.goods_sn }}</td></tr>
                                </tbody>
                                <tbody>
                                    <tr><th class="tdTitle" colspan="2">{{$t('lang.subject')}}</th></tr>
                                    <tr><td>{{$t('lang.brand')}}</td><td>{{ goodsInfo.brand_name }}</td></tr>
                                    <tr><td>{{$t('lang.goods_weight')}}</td><td>{{ goodsInfo.goods_weight }}kg</td></tr>
                                    <tr><th class="tdTitle" colspan="2">{{$t('lang.attr_parameter')}}</th></tr>
                                    <tr v-for="item in goodsInfo.attr_parameter"><td>{{ item.attr_name }}</td><td>{{ item.attr_value }}</td></tr>
                                </tbody>
                            </table>
                            </div>
                        </template>
                        <template v-else>
                            <NotCont />
                        </template>
                    </template>
				</van-tab>
			</van-tabs>
			<!-- 底部版权 -->
			<dsc-copyright></dsc-copyright>
		</div>
		<van-goods-action>
			<van-goods-action-mini-btn icon="chat" :text="$t('lang.customer_service')" @click="onChat(goodsInfo.goods_id,goodsInfo.user_id)" />
			<van-goods-action-mini-btn :icon="collect_icon" :text="$t('lang.collect')" :class="{'curr':is_collect == 1}" @click="collection" />
			<van-goods-action-mini-btn icon="shop" :text="$t('lang.shop')" v-if="groupbuyDetailData.user_id" :to="{name:'shopHome',params:{id:groupbuyDetailData.user_id}}" />
			<van-goods-action-big-btn v-if="groupbuyDetailData.is_end == 1" :text="$t('lang.has_ended')" />
			<van-goods-action-big-btn v-else :text="$t('lang.immediately_regiment')" primary @click="skuLink" />
		</van-goods-action>
		<CommonNav/>

		<!-- 属性弹窗 -->
		<van-popup v-model="showBase" position="bottom" class="attr-goods-box">
            <div class="attr-goods-header">
                <template v-if="attr != ''">
                    <div class="attr-img">
                        <img :src="goodsAttrOper.attr_img" class="img" v-if="goodsAttrOper.attr_img" />
                        <img :src="goodsInfo.goods_thumb" class="img" v-else>
                    </div>
                    <div class="attr-info">
                        <div class="attr-price" v-html="ladderPrice"></div>
                        <div class="attr-stock">{{$t('lang.label_stock')}}{{ goodsAttrOper.stock }}<em class="color-red p-l05">({{$t('lang.gb_limited')}}:{{groupbuyDetailData.restrict_amount}})</em></div>
                        <div class="attr-other">{{$t('lang.label_selected')}}{{ goodsAttrOper.attr_name }} {{ num }}{{$t('lang.jian')}}</div>
                    </div>
                </template>
                <template v-else>
                    <div class="attr-img">
                        <img :src="goodsInfo.goods_img" class="img"/>
                    </div>
                    <div class="attr-info">
                        <div class="attr-name twolist-hidden">{{ goodsInfo.goods_name }}</div>
                        <div class="attr-price" v-if="goodsInfo.goods_price"></div>
                        <div class="attr-stock">{{$t('lang.label_stock')}}{{ goodsInfo.goods_number }}</div>
                    </div>
                </template>
                <i class="iconfont icon-close" @click="closeSku"></i>
            </div>
            <div class="attr-goods-content" v-if="attr != ''">
                <van-radio-group class="sku-item" v-model="goodsAttrInit[index]" v-for="(item,index) in attr" :key="index" @change="changeAttr">
                    <div class="sku-tit">{{ item.attr_name }}</div>
                    <div class="sku-list">
                        <template v-for="(option,listIndex) in item.attr_key">
                            <van-radio class="option" :class="{'active':goodsAttrInit[index] == option.goods_attr_id}" :name="option.goods_attr_id">{{ option.attr_value }}
                            </van-radio>
                        </template>
                    </div>
                </van-radio-group>
            </div>
            <div class="attr-goods-number dis-box">
                <span class="tit box-flex">{{$t('lang.number')}}</span>
                <div class="stepper">
                    <van-stepper
                        v-model="number"
                        integer
                        :min="ladderFristNum"
                        :max="stock"
                    />
                </div>
            </div>
            <div class="van-sku-actions">
                <van-button type="default" class="van-button--bottom-action" @click="onGroupBuy">{{$t('lang.button_buy')}}</van-button>
            </div>
        </van-popup>

        <!-- 视频 -->
        <van-popup v-model="videoShow" style="width: 100%;">
            <video-player 
                class="video-player-box"
                ref="videoPlayer"
                :options="playerOptions"
                :playsinline="true">
            </video-player>
        </van-popup>

        <!--分享海报-->
        <van-popup v-model="shareImgShow" class="shareImg" overlay-class="shareImg-overlay">
            <img :src="shareImg" v-if="shareImg" class="img" />
            <span v-else>{{$t('lang.error_generating_image')}}</span>
        </van-popup>
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
		Popup,
		RadioGroup,
		Radio,
		Stepper,
		Button,
		Dialog,
		Toast,
        Icon,
	} from 'vant'

	import {
		swiper,
		swiperSlide
	} from 'vue-awesome-swiper'

	import CommonNav from '@/components/CommonNav'
	import NotCont from '@/components/NotCont'
	import CountDown from '@/components/CountDown'
	import formProcessing from '@/mixins/form-processing'

    import 'video.js/dist/video-js.css'
    import {videoPlayer} from 'vue-video-player'

	export default {
		name: "groupbuy-detail",
		mixins: [formProcessing],
		data() {
			return {
				tabs: [this.$t('lang.goods_detail_info'), this.$t('lang.groupbuy_explain') ,this.$t('lang.specification_parameter')],
				ladderFristNum:1,
				num:1,
				content: '',
				showBase: false,
				serviceShow: false,
				processShow: false,
				PriceShow: false,
				active: 0,
				//轮播图滑动
				swiperOption: {
					scrollbarHide: true,
					slidesPerView: 'auto',
					centeredSlides: false,
					grabCursor: true,
					autoplay: 2500,
				},
                videoShow: false,
                playerOptions: {
                    autoplay: false, // 如果true,浏览器准备好时开始回放。
                    muted: true, // 默认情况下将会消除任何音频。
                    loop: true, // 导致视频一结束就重新开始。
                    language: 'zh-CN',
                    fluid: true, // 当true时，Video.js player将拥有流体大小。换句话说，它将按比例缩放以适应其容器。
                    playbackRates: [0.7, 1.0, 1.5, 2.0], // 播放速度
                    sources: [{
                        type: "video/mp4",
                        src: null
                    }],
                },
                shareImgShow:false,
                shareImg:''
			};
		},
		components: {
			CommonNav,
			NotCont,
			CountDown,
			swiper,
			swiperSlide,
            videoPlayer,
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
			[Popup.name]: Popup,
			[RadioGroup.name]: RadioGroup,
			[Radio.name]: Radio,
			[Stepper.name]: Stepper,
			[Button.name]: Button,
			[Dialog.name] : Dialog,
			[Toast.name] : Toast,
            [Icon.name] : Icon
		},
		//初始化加载数据
		created() {
			this.load(this.$route.params.group_buy_id)
		},
		computed: {
			...mapState({
				groupbuyDetailData: state => state.other.groupbuyDetailData,
			}),
			goodsInfo(){
				return this.groupbuyDetailData.goods
			},
            gallery_list() {
                return this.goodsInfo.gallery_list
            },
			attr(){
				return this.goodsInfo.group_buy_attr
			},
			goodsAttrInit(){
				return this.$store.state.other.goodsAttrInit
			},
			goodsAttrOper(){
				return this.$store.state.goods.goodsAttrOper
			},
			stock() {
				let number = 0
				if(this.groupbuyDetailData.restrict_amount > 0){
					number = this.groupbuyDetailData.restrict_amount
				}else{
					number = this.attr != '' ? this.goodsAttrOper.stock : this.goodsInfo.goods_number
				}

                return number
            },
			isLogin(){
 			    return localStorage.getItem('token') == null ? false : true
	       	},
			goodsCollectStatue(){
				return this.$store.state.user.goodsCollectStatue
			},
			is_collect:{
				get(){
					return this.$store.state.other.groupbuyDetailData.goods.is_collect
				},
				set(val){
					this.$store.state.other.groupbuyDetailData.goods.is_collect = val
				}
			},
			collect_icon(){
	 			return this.is_collect == 1 ? 'like' : 'like-o'
	 		},
	 		attr_name: {
                get() {
                    return this.attr != '' ? this.$store.state.other.groupbuyDetailData.goods.attr_name : this.number
                },
                set(val) {
                    this.$store.state.other.groupbuyDetailData.goods.attr_name = val
                }
            },
            //数量变化是阶梯价格变化
            ladderPrice(){
            	let price = this.groupbuyDetailData.formated_cur_price

            	this.groupbuyDetailData.price_ladder.forEach((v)=>{
            		if(this.number >= v.amount){
            			price = v.formated_price
            			return false
            		}
            	})

            	return price
            },
			number:{
				get(){
					let number = 0
					this.groupbuyDetailData.price_ladder.forEach((v,i)=>{
						if(i == 0){
							number = v.amount
							this.num = v.amount
							this.ladderFristNum = v.amount
						}
					})
					return number
				},
				set(val){
					this.num = val
				}
			}
		},
		methods: {
			load(group_buy_id){
				this.$store.dispatch('setGroupbuyDetail',{
					group_buy_id: group_buy_id,
					warehouse_id: 1,
					area_id: 2
				})
			},
			onGroupBuy(){
				let newAttr = []

				if (this.attr.length > 0) {
                    newAttr = this.goodsAttrInit
                }

                if(this.isLogin){//判断是否登录
                	let xianggou = Number(this.groupbuyDetailData.order_number) + Number(this.num)

                	if(this.groupbuyDetailData.restrict_amount > 0){
                		if(xianggou <= this.groupbuyDetailData.restrict_amount){ //判断是否超出限购
	                        this.$store.dispatch('setGroupBuy',{
	                            group_buy_id:this.$route.params.group_buy_id,
	                            number: this.num,
	                            spec: newAttr,
	                        }).then(({ data:data })=>{
	                            if(data.error == 1){
	                                Toast(data.message)
	                            }else{
	                                this.$router.push({
	                                    name:'checkout',
	                                    query:{
	                                        rec_type:data.flow_type,
	                                        type_id:data.extension_id
	                                    }
	                                })
	                            }
	                        })
	                    }else{
	                        Toast(this.$t('lang.groupbuy_propmt_4'))
	                    }
                	}else{
                		this.$store.dispatch('setGroupBuy',{
                            group_buy_id:this.$route.params.group_buy_id,
                            number: this.num,
                            spec: newAttr,
                        }).then(({ data:data })=>{
                            if(data.error == 1){
                                Toast(data.message)
                            }else{
                                this.$router.push({
                                    name:'checkout',
                                    query:{
                                        rec_type:data.flow_type,
                                        type_id:data.extension_id
                                    }
                                })
                            }
                        })
                	}
				}else{
					let msg = this.$t('lang.groupbuy_propmt_3')
                    this.notLogin(msg)
				}
			},
			closeSku(){
				this.showBase = false
			},
			goodsService() {
				this.serviceShow = !this.serviceShow
			},
			skuLink() {
                this.showBase = true
                this.changeAttr()
            },
            changeAttr() {
                this.$store.dispatch('setGoodsAttrOper', {
                    goods_id: this.goodsInfo.goods_id,
                    num: this.num,
                    attr_id: this.goodsAttrInit
                })
            },
			auctionProcess() {
				this.processShow = !this.processShow
			},
			auctionPrice() {
				this.PriceShow = !this.PriceShow
			},
			//收藏
           	collection(){
				if(this.isLogin){
					this.$store.dispatch('setCollectGoods',{
	                    goods_id:this.goodsInfo.goods_id,
	                    status:this.is_collect
	                })
				}else{
					let msg = this.$t('lang.fill_in_user_collect_goods')
                    this.notLogin(msg)
				}
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
                        		name: 'groupbuy-detail',
                        		params: {group_buy_id: this.groupbuyDetailData.act_id},
                        		url:url
                        	}
                        }
                    })
                }).catch(() => {

                })
            },
            handleVideo(){
                this.playerOptions.sources[0].src = this.groupbuyDetailData.goods_video_path
                this.videoShow = true
            },
            //分享海报
            onGoodsShare(){
                if (this.isLogin) {
                    Toast.loading({ duration: 0, mask: true, forbidClick: true, message: this.$t('lang.loading') })
                    let price = this.groupbuyDetailData.formated_cur_price

                    this.$store.dispatch('setGoodsShare',{
                        goods_id:this.groupbuyDetailData.goods_id,
                        price: price,
                        share_type:0,
                        extension_code:'group_buy',
                        code_url:window.location.href,
                        thumb:this.gallery_list[0].img_url || this.goodsInfo.goods_img
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
	            this.load(to.params.group_buy_id)
	        },
			goodsAttrOper() {
                if (this.goodsAttrOper) {
                    this.attr_name = this.goodsAttrOper.attr_name
                }
            },
            goodsCollectStatue(){
                this.goodsCollectStatue.forEach((v) => {
                    if (v.id == this.goodsInfo.goods_id) {
                        this.is_collect = v.status
                    }
                })
            },
            groupbuyDetailData(){
            	//设置title
                document.title = this.groupbuyDetailData.goods_name;

                //单独设置微信分享信息
                this.$wxShare.share({
                    title:this.groupbuyDetailData.act_name,
                    desc:this.groupbuyDetailData.act_desc,
                    link:`${window.ROOT_URL}mobile#/groupbuy/detail/` + this.groupbuyDetailData.act_id,
                    imgUrl:this.groupbuyDetailData.goods.goods_thumb
                })
            }
        }
	}
</script>
