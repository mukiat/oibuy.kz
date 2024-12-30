<template>
    <div class="con ">
        <div class="con_main goods presale-detail" v-if="presaleDetailData">
            <!--轮播图-->
            <van-swipe :autoplay="3000" class="swipe" :height="swipe_height">
                <van-swipe-item v-for="(item, index) in presaleDetailData.goods_img" :key="index" v-if="presaleDetailData.goods_img.length > 0">
                    <img class="imgalt" :src="item.img_url" />
                </van-swipe-item>
                <van-swipe-item v-else><img :src="goodsInfo.goods_img" class="imgalt"></van-swipe-item>
            </van-swipe>
            <!--title-->
            <div class="cont-box">
                <div class="dis-box price-box">
                    <div class="left box-flex">
                        <h4 class="color-white" v-if="goodsInfo">
                            <currency-price 
                            :price="goodsInfo.shop_price_original" 
                            :size="24"
                            color="#fff"
                            ></currency-price>
                            <div class="tag-price dis-box">
                                <div class="left-tag dis-box">
                                    <div class="left-icon">
                                        <i class="iconfont icon-tixingnaozhong f-01"></i>
                                    </div>
                                    <div class="box-flex tag-right-cont f-01">{{$t('lang.presale')}}</div>
                                </div>
                                <div class="box-flex">
                                    <label class="p-l05 f-01">{{$t('lang.market_price')}}
                                        <del v-html="goodsInfo.market_price_formated"></del>
                                    </label>
                                </div>
                            </div>
                        </h4>
                    </div>
                    <div class="right">
                        <div class="time-title f-02 text-center m-top14" v-if="presaleData.status == 0">{{$t('lang.activities_not_started')}}</div>
                        <div class="time-title f-02 text-center m-top06" v-if="presaleData.status == 1">{{$t('lang.were_still_end')}}</div>
                        <div class="time-title f-02 text-center m-top14" v-if="presaleData.status == 2">{{$t('lang.activities_end')}}</div>
                        <div class="f-02 color-white time m-top02" v-if="presaleData.status == 1">
                         <div v-if="presaleDetailData.presale.gmt_start_date != undefined && presaleDetailData.presale.gmt_end_date != undefined">
                            <count-down :endTime="presaleDetailData.presale.gmt_end_date" :endText="$t('lang.activity_end')"></count-down>
							</div>
                        </div>
                    </div>
                </div>

                <div class="title bg-color-write f-06 color-3 padding-all">
                    <div class="flex-common ai-start">
                        <div class="flex1">
                            <van-tag type="danger" class="m-r05 f-03" v-if="goodsInfo.user_id">{{$t('lang.self_support')}}</van-tag>
                            <span class="f-05 color-3 f-weight">{{goodsInfo.goods_name}}</span>
                        </div>
                        <div class="drp-share" @click="onGoodsShare" v-if="presaleData.status != 2">
                            <i class="iconfont icon-share"></i>
                            <span>{{$t('lang.share')}}</span>
                        </div>
                    </div>
                    <div class="dis-box color-9 f-03 m-top08">
                        <div class="box-flex">{{$t('lang.sales_volume')}}{{goodsInfo.sales_volume}}</div>
                        <div class="box-flex text-center">{{$t('lang.stock')}}{{goodsInfo.goods_number}}</div>
                        <span class="box-flex text-right" v-if="basic_info">
                            <template v-if="basic_info && basic_info.province_name">
                                {{ basic_info.province_name }}
                            </template>
                            <template v-if="basic_info && basic_info.city_name">
                                {{ basic_info.city_name }}
                            </template>
                        </span>
                    </div>
                </div>
            </div>
            <!--预售规则-->
            <div class="ect-group dis-box m-top08 ect-server" @click="processClick">
                <div class="left-cell">{{$t('lang.label_presale_rules')}}</div>
                <div class="box-flex right-cell">
                    <i class="iconfont icon-sigh goods-min-icon p-a f-04"></i>
                    <van-tag type="danger" class="m-r05 f-02 br-100 tag-min">{{$t('lang.down_payment')}}</van-tag>
                    <span class="f-05 color-7" v-html="presaleDetailData.presale.formated_deposit"></span>&nbsp;
                    <van-tag type="danger" class="m-r05 f-02 br-100 tag-min">{{$t('lang.balance_payment')}}</van-tag>
                    <span class="f-05 color-7" v-html="presaleDetailData.presale.formated_final_payment"></span>
                    <p class="m-top08">
                        <van-tag type="danger" class="m-r05 f-02 br-100 tag-min">{{$t('lang.presale_number')}}</van-tag>
                        <span class="f-05 color-7">{{presaleData.total_order}}人</span>
                    </p>
                </div>
            </div>
            <van-actionsheet class="auction-box" v-model="processShow" :title="$t('lang.presale_rules')">
                <div class="padding-all f-04 color-7">
                    <div class="li ">
                        <p>{{$t('lang.presale_propmt_1')}}</p>
                    </div>
                    <div class="li m-top06">
                        <p>{{$t('lang.presale_propmt_2')}}</p>
                    </div>
                    <div class="li m-top06">
                        <p>{{$t('lang.presale_propmt_3')}}</p>
                    </div>
                    <div class="li m-top06">
                        <p>{{$t('lang.presale_propmt_4')}}</p>
                    </div>
                    <div class="li m-top06">
                        <p>{{$t('lang.presale_propmt_5')}}</p>
                    </div>
                    <div class="li m-top06">
                        <p>{{$t('lang.presale_propmt_6')}}</p>
                    </div>
                </div>
            </van-actionsheet>
            <van-cell-group class="van-cell-noleft m-top08">
                <van-cell :title="$t('lang.label_send_to')" v-model="regionSplic" class="my-cell-nobottom" is-link @click="handelRegionShow"/>
                <van-cell :title="$t('lang.label_freight')">
                    <span v-html="freight"></span>
                    <span class="color-red" v-if="shipping_fee.shipping_title && shipping_fee.shipping_title != 0">({{ shipping_fee.shipping_title }})</span>
                </van-cell>
            </van-cell-group>
            <van-cell-group class="van-cell-noleft m-top08">
                <van-cell :title="$t('lang.label_selected')" v-model="attr_name" is-link @click="skuLink"/>
            </van-cell-group>
            <div class="ect-group dis-box m-top08 ect-server p-r" @click="goodsService">
				<div class="left-cell">{{$t('lang.label_service')}}</div>
				<div class="box-flex right-cell">
					<i class="iconfont icon-sigh goods-min-icon p-a f-04"></i>
					<p class="f-04 color-3" v-if ="basic_info">{{$t('lang.you')}}<span class="color-red">{{ basic_info.shop_name }}</span>{{$t('lang.deliver_aftersale_service')}}</p>
					<div class="dis-box m-top08 g-r-rule">
						<div class="f-02 color-9 m-r05" v-if="goodsInfo.get_goods_extend && goodsInfo.get_goods_extend.is_reality">
							<em class="fl em-promotion">
								<i class="iconfont icon-daifukuan f-01"></i>
							</em>
							<span class="fl p-l05">{{$t('lang.is_reality')}}</span>
						</div>
						<div class="f-02 color-9 m-r05" v-if="goodsInfo.get_goods_extend && goodsInfo.get_goods_extend.is_return">
							<em class="fl em-promotion">
								<i class="iconfont icon-7tianwuliyoutuihuo f-01"></i>
							</em>
							<span class="fl p-l05">{{$t('lang.is_return')}}</span>
						</div>
						<div class="f-02 color-9" v-if="goodsInfo.get_goods_extend && goodsInfo.get_goods_extend.is_fast">
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
					<li class="padding-all" v-if="goodsInfo.get_goods_extend && goodsInfo.get_goods_extend.is_reality">
						<p class="dis-box">
							<label class="box-flex">
								<i class="iconfont icon-daifukuan color-red f-06"></i>
								<span class="p-l05 f-05 color-3">{{$t('lang.is_reality')}}</span>
							</label>
						</p>
						<p class="f-03 color-7 m-top04">{{$t('lang.service_note_propmt_4')}}</p>
					</li>
					<li class="b-color-f padding-all" v-if="goodsInfo.get_goods_extend && goodsInfo.get_goods_extend.is_return">
						<p class="dis-box ">
							<label class="box-flex">
								<i class="iconfont icon-7tianwuliyoutuihuo color-red f-06"></i>
								<span class="p-l05 f-05 color-3">{{$t('lang.is_return')}}</span>
							</label>
						</p>
						<p class="f-03 color-7 m-top04">{{$t('lang.service_note_propmt_2')}}</p>
					</li>
					<li class="b-color-f padding-all" v-if="goodsInfo.get_goods_extend && goodsInfo.get_goods_extend.is_fast">
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
            <!--详情-->
            <van-tabs v-model="active" class="m-top08">
                <van-tab v-for="(item,index) in tabs" :title="item" :key="index">
                    <template v-if="index == 0">
                        <div class="padding-all bg-color-write goods-detail" v-if="goodsInfo.goods_desc!=''" v-html="goodsInfo.goods_desc"></div>
                        <div v-else>
                            <NotCont />
                        </div>
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
            <van-goods-action>
                <van-goods-action-mini-btn icon="chat" :title="$t('lang.customer_service')" @click="onChat(goodsInfo.goods_id,goodsInfo.user_id)" />
                <template v-if="presaleData.status == 0">
                    <van-goods-action-big-btn :text="$t('lang.activities_not_started')" class="van-button--disabled" disabled />
                </template>
                <template v-if="presaleData.status == 1">
                    <van-goods-action-big-btn @click="onSku" :text="$t('lang.make_appointment_now')" primary />
                </template>
                <template v-if="presaleData.status == 2">
                    <van-goods-action-big-btn :text="$t('lang.activities_end')" class="van-button--disabled" disabled />
                </template>
            </van-goods-action>
        </div>
        <CommonNav :routerName="routerName">
             <li slot="aloneNav">
                <router-link :to="{name: 'presale'}">
                    <i class="iconfont icon-fenxiao"></i>
                    <p>{{$t('lang.presale_activity')}}</p>
                </router-link>
            </li>
        </CommonNav>
        <van-popup v-model="showBase" position="bottom" class="attr-goods-box">
            <div class="attr-goods-header">
                <template v-if="attr != ''">
                    <div class="attr-img">
                        <img :src="goodsAttrOper.attr_img" class="img" v-if="goodsAttrOper.attr_img" />
                        <img :src="goodsInfo.goods_thumb" class="img" v-else>
                    </div>
                    <div class="attr-info">
                        <div class="attr-price" v-html="goodsAttrOper.goods_price_formated"></div>
                        <div class="attr-stock">{{$t('lang.label_stock')}}{{ goodsAttrOper.stock }}</div>
                        <div class="attr-other">{{$t('lang.label_selected')}}{{ goodsAttrOper.attr_name }} {{ num }} тал</div>
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
                <van-button type="default" class="van-button--bottom-action" @click="onCheckoutClicked">{{$t('lang.button_buy')}}</van-button>
            </div>
        </van-popup>

        <!--分享海报-->
        <van-popup v-model="shareImgShow" class="shareImg" overlay-class="shareImg-overlay">
            <img :src="shareImg" v-if="shareImg" class="img" />
            <span v-else>{{$t('lang.error_generating_image')}}</span>
        </van-popup>

        <Region :display="regionShow" :regionOptionDate="regionOptionDate" @updateDisplay="getRegionShow" @updateRegionDate="getRegionOptionDate"></Region>
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
        Toast,
        Popup,
        Stepper,
        Button,
        RadioGroup,
        Radio,
        Dialog
    } from 'vant'

    import CommonNav from '@/components/CommonNav'
    import NotCont from '@/components/NotCont'
    import CountDown from '@/components/CountDown'
    import formProcessing from '@/mixins/form-processing'

    export default {
        name: "presale-detail",
        mixins: [formProcessing],
        data() {
            return {
                tabs: [this.$t('lang.goods_detail_info'), this.$t('lang.specification_parameter')],
                serviceShow: false,
                num:1,
                showBase:false,//属性
                processShow: false,//预售规则
                active: 0,
                act_id:this.$route.params.act_id,
                storeBtn: false,
                routerName:'presale',
                shipping_region:'',
                shareImgShow:false,
                shareImg:''
            }
        },
        components: {
            CommonNav,
            CountDown,
            NotCont,
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
            [Toast.name]: Toast,
            [Popup.name]: Popup,
            [Stepper.name]: Stepper,
            [Button.name]: Button,
            [RadioGroup.name]: RadioGroup,
            [Radio.name]: Radio,
            [Dialog.name]: Dialog,
        },
        created() {
            if(this.getRegionData){
                this.regionOptionDate = this.getRegionData;
            }

            this.loadPresaleInfo()
		},
		computed: {
			...mapState({
				presaleDetailData: state => state.other.presaleDetailData,
                shipping_fee: state => state.shopping.shipping_fee
			}),
            presaleData(){
                return this.presaleDetailData.presale ? this.presaleDetailData.presale : ''
            },
            goodsInfo(){
                return this.presaleDetailData.goods ? this.presaleDetailData.goods : ''
            },
            goods_id(){
                return this.presaleDetailData.goods_id ? this.presaleDetailData.goods_id : ''
            },
            attr(){
                return this.goodsInfo.attr
            },
            goodsAttrInit() {
                return this.$store.state.other.goodsAttrInit
            },
            goodsAttrOper() {
                return this.$store.state.other.presalePropertData
            },
            stock() {
                return this.attr != '' ? this.goodsAttrOper.stock : this.goodsInfo.goods_number
            },
            //运费
            freight() {
                return this.shipping_fee != null && this.shipping_fee.is_shipping > 0 ? this.shipping_fee
                .shipping_fee_formated : "<span class='color-red'>"+this.$t('lang.is_shipping_area')+"</span>"
            },
            basic_info(){
                return this.presaleDetailData.basic_info
            },
            attr_name: {
                get() {
                    return this.attr != '' ? this.goodsInfo.attr_name : this.num
                },
                set(val) {
                    this.goodsInfo.attr_name = val
                }
            },
            isLogin(){
                return localStorage.getItem('token') == null ? false : true
            },
		},
        methods: {
            loadPresaleInfo(){
                this.$store.dispatch('setPresaleDetail',{
                    act_id: this.act_id
                })
            },
            onCheckoutClicked(){
                let newAttr = []

                if (this.attr.length > 0) {
                    newAttr = this.goodsAttrInit
                }

                this.$store.dispatch('setPresaleBuy',{
                    act_id:this.act_id,
                    number:this.num,
                    goods_spec:newAttr,
                }).then(({ data })=>{
                    if(data.error == 0){
                        this.$router.push({
                            name: "checkout",
                            query: {
                                rec_type:5,
                                type_id:data.presale_id
                            }
                        })
                    }else{
                        Toast(data.message)
                    }
                })
            },
            //选择属性
            skuLink() {
                this.showBase = true
                this.changeAttr()
            },
            onSku(){
               //if(this.attr.length > 0){
                    this.showBase = true
                    this.changeAttr()
                // }else{
                //     this.onCheckoutClicked()
                // }
            },
            handelClose(){
                this.show = false
            },
            closeSku() {
                this.showBase = false
                this.storeBtn = false
            },
            changeAttr(){
    			this.$store.dispatch('setPresalePropert',{
    				gid:this.goods_id,
    				number:this.num,
                    act_id:this.act_id,
                    attr:this.goodsAttrInit
    			})
            },
            handelRegionShow() {
                this.regionShow = this.regionShow ? false : true
            },
            //运费计算
            shippingFee(val) {
                this.$store.dispatch('setShippingFee', {
                    goods_id: this.presaleDetailData.goods_id,
                    position: val
                })
            },
            //加载动画
            loading(url) {
                let that = this
                Toast.loading({
                    duration: 600,
                    mask: true,
                    message: this.$t('lang.loading')
                }, url)
            },
            // 规则
            processClick() {
                this.processShow = !this.processShow
            },
            // 服务
            goodsService() {
    			this.serviceShow = !this.serviceShow
    		},
            notLogin(msg) {
                let url = window.location.href;
                Dialog.confirm({
                    message: msg,
                    className: 'text-center'
                }).then(() => {
                    this.$router.push({
                        path: '/login',
                        query: {
                            redirect: {
                                name: 'presale-detail',
                                params: {
                                    act_id: this.act_id
                                },
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
                    let price = this.goodsInfo.shop_price_original

                    this.$store.dispatch('setGoodsShare',{
                        goods_id:this.goodsInfo.goods_id,
                        price: price,
                        share_type:0,
                        extension_code:'presale',
                        code_url:window.location.href,
                        thumb:this.presaleDetailData.goods_img[0].img_url || this.goodsInfo.goods_img
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
            presaleDetailData(){
                //设置title
                document.title = this.goodsInfo.goods_name;
                
                //单独设置微信分享信息
                this.$wxShare.share({
                    title:this.goodsInfo.goods_name,
                    desc:this.goodsInfo.goods_brief,
                    link:`${window.ROOT_URL}mobile#/presale/detail/` + this.act_id,
                    imgUrl:this.goodsInfo.goods_thumb
                })
            },
            goodsAttrInit(){
                //运费
                this.shippingFee(this.shipping_region)
            },
            regionSplic(){
                this.shipping_region = {
                    province_id: this.regionOptionDate.province.id,
                    city_id: this.regionOptionDate.city.id,
                    district_id: this.regionOptionDate.district.id,
                    street_id: this.regionOptionDate.street.id
                }

                //运费
                if(this.presaleDetailData){
                    this.shippingFee(this.shipping_region)
                }
            }
        }
    }
</script>
