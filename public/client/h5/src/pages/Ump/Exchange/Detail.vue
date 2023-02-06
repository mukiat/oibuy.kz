<template>
	<div class="con_main">
		<div class="goods exchage-detail">
			<!--轮播图-->
			<van-swipe :autoplay="3000" class="swipe" :height="swipe_height">
                <template v-if="goodsInfo.gallery_list && goodsInfo.gallery_list.length>0">
                    <van-swipe-item v-for="(item, index) in goodsInfo.gallery_list" :key="index">
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
			<!--title-->
			<div class="cont-box">
				<div class="title bg-color-write f-06 color-3 padding-all">
					<div class="flex-common ai-start">
                        <div class="flex1">
							<van-tag type="danger" class="m-r05 f-03" v-if="goodsInfo.user_id == 0">{{$t('lang.self_support')}}</van-tag>
							<span class="f-05 color-3 f-weight">{{goodsInfo.goods_name}}</span>
						</div>
						<div class="drp-share" @click="onGoodsShare">
                            <i class="iconfont icon-share"></i>
                            <span>{{$t('lang.share')}}</span>
                        </div>
					</div>
					<!-- <div class="color-red f-03 m-top04">{{detail.title_subtitle}}</div> -->
					<div class="goods-price f-06 color-red m-top06">{{goodsInfo.exchange_integral}}<span class="f-03 p-l05 color-9">{{$t('lang.integral')}}</span></div>
					<div class="f-03 color-9">{{$t('lang.market_price')}}
						<del v-html="goodsInfo.market_price"></del>
					</div>
					<div class="dis-box color-9 f-03 m-top06">
						<div class="box-flex">{{$t('lang.sales_volume')}}{{goodsInfo.sales_volume}}</div>
						<div class="box-flex">
							<p class="text-right">{{$t('lang.stock')}}{{goodsInfo.goods_number}}</p>
						</div>
					</div>
				</div>
			</div>

			<div class="ect-group dis-box m-top08 ect-server p-r" @click="goodsService">
				<div class="left-cell">{{$t('lang.label_service')}}</div>
				<div class="box-flex right-cell">
					<i class="iconfont icon-102 goods-min-icon p-a f-04"></i>
					<p class="f-04 color-3">{{$t('lang.you')}}{{goodsInfo.rz_shop_name}}{{$t('lang.deliver_aftersale_service')}}</p>
					<div class="dis-box m-top08 g-r-rule">
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
			<!--详情-->
			<van-tabs v-model="active" class="m-top08">
				<van-tab v-for="(item,index) in tabs" :title="item" :key="index">
					<template v-if="index == 0">
                        <template v-if="goodsInfo.goods_desc">
                            <div v-html="goodsInfo.goods_desc" class="goods_desc"></div>
                        </template>
                        <template v-else>
                            <NotCont></NotCont>
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
                            <NotCont></NotCont>
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
			<van-goods-action-big-btn :text="$t('lang.immediately_change')" primary @click="onExchange" />
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
                        <div class="attr-price">{{ goodsInfo.exchange_integral }}<em>{{$t('lang.integral')}}</em></div>
                        <div class="attr-stock">{{$t('lang.label_stock')}}{{ goodsAttrOper.stock }}</div>
                        <div class="attr-other">{{$t('lang.label_selected')}}{{ goodsAttrOper.attr_name }} {{ num }}{{$t('lang.jian')}}</div>
                    </div>
                </template>
                <template v-else>
                    <div class="attr-img">
                        <img :src="goodsInfo.goods_img" class="img"/>
                    </div>
                    <div class="attr-info">
                        <div class="attr-name twolist-hidden">{{ goodsInfo.goods_name }}</div>
                        <div class="attr-price" v-html="goodsInfo.goods_price"></div>
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
                        v-model="num"
                        integer
                        :min="1"
                        :max="stock"
                    />
                </div>
            </div>
            <div class="van-sku-actions">
                <van-button type="default" class="van-button--bottom-action" @click="onExchange">{{$t('lang.immediately_change')}}</van-button>
            </div>
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
		Row,
		Col,
		Tag,
		Popup,
		RadioGroup,
		Radio,
		Stepper,
		Button,
		Dialog,
		Toast
	} from 'vant'

	import {
		swiper,
		swiperSlide
	} from 'vue-awesome-swiper'

	import CommonNav from '@/components/CommonNav'
	import NotCont from '@/components/NotCont'
	import CountDown from '@/components/CountDown'
	import formProcessing from '@/mixins/form-processing'

	export default {
		name: "exchange-detail",
		mixins: [formProcessing],
		data() {
			return {
				//轮播图滑动
				swiperOption: {
					autoplay: 2500,
					direction: 'vertical',
					freeMode: true,
					roundLengths: true,
					slidesPerView: 1,
					mousewheel: true,
				},
				tabs: [this.$t('lang.goods_detail_info'), this.$t('lang.specification_parameter')],
				content: '',
				serviceShow: false,
				active: 2,
				num:1,
				showBase: false,
				goods_id: this.$route.params.id,
				shareImgShow:false,
				shareImg:''
			}
		},
		components: {
			CommonNav,
			swiper,
			swiperSlide,
			CountDown,
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
			[Row.name]: Row,
			[Col.name]: Col,
			[Tag.name]: Tag,
			[Popup.name]: Popup,
			[RadioGroup.name]: RadioGroup,
			[Radio.name]: Radio,
			[Stepper.name]: Stepper,
			[Button.name]: Button,
			[Dialog.name] : Dialog,
			[Toast.name] : Toast,
			NotCont,
		},
		//初始化加载数据
		created() {
			this.$store.dispatch('setExchangeDetail',{
				id: this.$route.params.id,
				warehouse_id: 1,
				area_id: 2
			})
		},
		computed: {
			...mapState({
				goodsInfo: state => state.other.exchangeDetailData,
			}),
			attr() {
                return this.$store.state.other.exchangeDetailData.exchange_goods_attr
            },
            goodsAttrInit() {
                return this.$store.state.other.goodsAttrInit
            },
            goodsAttrOper() {
                return this.$store.state.goods.goodsAttrOper
            },
            stock() {
                return this.attr != '' ? this.goodsAttrOper.stock : this.goodsInfo.goods_number
            },
			isLogin(){
				return localStorage.getItem('token') == null ? false : true
			},
			goodsCollectStatue(){
				return this.$store.state.user.goodsCollectStatue
			},
			attr_name: {
                get() {
                    return this.attr != '' ? this.$store.state.other.exchangeDetailData.attr_name : this.num
                },
                set(val) {
                    this.$store.state.other.exchangeDetailData.attr_name = val
                }
            },
			is_collect:{
				get(){
					return this.$store.state.other.exchangeDetailData.is_collect
				},
				set(val){
					this.$store.state.other.exchangeDetailData.is_collect = val
				}
			},
			collect_icon(){
				return this.is_collect == 1 ? 'like' : 'like-o'
			},
		},
		methods: {
			//立即兑换
			onExchange() {
				let newAttr = []
                this.addCartClass = false

                if (this.attr.length > 0) {
                    newAttr = this.goodsAttrInit
                }

				this.$store.dispatch('setExchangeBuy',{
					goods_id: this.goods_id,
					num: this.num,
					attr: newAttr
				}).then(({data})=>{
					if(data.error == 0){
						this.$router.push({
		                    name: 'checkout',
		                    query: {
		                        rec_type: 4
		                    }
		                })
					}else{
						Toast(data.msg)
					}
				})
			},
			//收藏
			collection(){
				if (this.isLogin) {
                    this.$store.dispatch('setCollectGoods', {
                        goods_id: this.goods_id,
                        status: this.is_collect
                    })
                } else {
                    let msg = this.$t('lang.fill_in_user_collect_goods')
                    this.notLogin(msg)
                }
			},
			goodsService() {
				this.serviceShow = !this.serviceShow
			},
			skuLink() {
                this.showBase = true
                this.changeAttr()
            },
            closeSku() {
                this.showBase = false
            },
            changeAttr() {
                this.$store.dispatch('setGoodsAttrOper', {
                    goods_id: this.goods_id,
                    num: this.num,
                    attr_id: this.goodsAttrInit
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
                        		name: 'goods', 
                        		params: {id: this.goods_id},
                        		url:url
                        	}
                        }
                    })
                }).catch(() => {

                })
            },
            //分享海报
            onGoodsShare(){
                if (this.isLogin) {
                    Toast.loading({ duration: 0, mask: true, forbidClick: true, message: this.$t('lang.loading') })
                    let price = this.goodsInfo.exchange_integral

                    this.$store.dispatch('setGoodsShare',{
                        goods_id:this.goods_id,
                        price: price,
                        share_type:0,
                        extension_code:'exchange',
                        code_url:window.location.href,
                        thumb:this.goodsInfo.gallery_list[0].img_url || this.goodsInfo.goods_img
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
			goodsAttrOper() {
                if (this.goodsAttrOper) {
                    this.attr_name = this.goodsAttrOper.attr_name
                }
            },
            goodsCollectStatue(){
                this.goodsCollectStatue.forEach((v) => {
                    if (v.id == this.goods_id) {
                        this.is_collect = v.status
                    }
                })
            },
            goodsInfo(){
            	//设置title
                document.title = this.goodsInfo.goods_name;

                //单独设置微信分享信息
                this.$wxShare.share({
                    title:this.goodsInfo.goods_name,
                    desc:this.goodsInfo.goods_brief,
                    link:`${window.ROOT_URL}mobile#/exchange/detail/` + this.goods_id,
                    imgUrl:this.goodsInfo.goods_thumb
                })
            }
        }
	}
</script>
