<template>
    <div class="con">
        <div class="con_main goods">
            <!--轮播图-->
            <van-swipe :autoplay="3000" class="swipe" :height="swipe_height">
                <van-swipe-item v-for="(item, index) in goodsInfo.pictures" :key="index" v-if="goodsInfo.pictures.length > 0">
                    <img class="imgalt" :src="item.img_url" />
                </van-swipe-item>
                <van-swipe-item v-else><img :src="goodsInfo.goods_img" class="imgalt"></van-swipe-item>
            </van-swipe>

            <!--title-->
            <div class="cont-box">
                <div class="dis-box price-box">
                    <div class="left box-flex">
                        <h4 class="color-white">
                            <div class="f-weight">
								{{ goodsInfo.sec_price_format }}
								<span style="font-size: 1rem; font-weight: normal; margin-left: 0.5rem;" v-if="goodsInfo.goods_rate > 0 && goodsInfo.is_kj == 1">{{$t('lang.import_tax')}} {{ goodsInfo.formated_goods_rate }}</span>
								<span style="font-size: 1rem; font-weight: normal; margin-left: 0.5rem;" v-else-if="goodsInfo.is_kj == 1">{{$t('lang.import_tax')}}：{{$t('lang.goods_tax_included')}}</span>
							</div>
                            <div class="tag-price dis-box">
                                <div class="left-tag dis-box">
                                    <div class="left-icon">
                                        <i class="iconfont icon-lightning f-01"></i>
                                    </div>
                                    <div class="box-flex tag-right-cont f-01">{{$t('lang.seckill')}}</div>
                                </div>
                                <div class="box-flex">
                                    <label class="p-l05 f-01">{{$t('lang.market_price')}}
                                        <del v-html="goodsInfo.market_price"></del>
                                    </label>
                                </div>
                            </div>
                        </h4>
                    </div>
                    <div class="right">
                        <template v-if="goodsInfo.status">
                        <div class="time-title f-02 text-center m-top06">{{$t('lang.were_still_end')}}</div>
                        </template>
                        <template v-else>
                        <div class="time-title f-02 text-center m-top06">{{$t('lang.from_start')}}</div>
                        </template>
                        <div class="f-02 color-white time m-top02">
                            <div v-if="goodsInfo.formated_start_date != undefined && goodsInfo.formated_end_date != undefined">
                                <count-down :endTime="goodsInfo.formated_end_date" :endText="$t('lang.activity_end')"></count-down>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="title bg-color-write f-06 color-3 padding-all">
                    <div class="flex-common ai-start">
                        <div class="flex1">
							<span class="span_block" v-if="goodsInfo.is_kj == 1"><em class="em-promotion ziying" style="background:#7a45e5">{{$t('lang.cross_goods')}}</em><img v-if="goodsInfo.country_icon != ''" class="country_icon" :src="goodsInfo.country_icon" /><em class="em_font">{{goodsInfo.country_name}}</em></span>
                            <van-tag type="danger" class="m-r05 f-03" v-if="goodsInfo.rz_shop_name">{{goodsInfo.rz_shop_name}}</van-tag>
                            <span class="f-05 color-3 f-weight">{{goodsInfo.goods_name}}</span>
                        </div>
                        <div class="drp-share" @click="onGoodsShare">
                            <i class="iconfont icon-share"></i>
                            <span>{{$t('lang.share')}}</span>
                        </div>
                    </div>
                    <div class="color-red f-03 m-top04">{{goodsInfo.goods_shipai}}</div>
                    <div class="dis-box color-9 f-03 m-top08">
                        <div class="box-flex">{{$t('lang.sales_volume')}} {{goodsInfo.sales_volume}}</div>
                        <div class="box-flex text-center">{{$t('lang.stock')}} {{goodsInfo.sec_num}}</div>
                        <div class="box-flex" v-if="goodsInfo.sec_limit">
                            <p class="text-right">{{$t('lang.gb_limited')}}{{goodsInfo.sec_limit}}</p>
                        </div>
                    </div>
                </div>
            </div>
			
            <van-cell-group class="van-cell-noleft m-top08">
                <van-cell :title="$t('lang.label_send_to')" v-model="regionSplic" class="my-cell-nobottom" is-link @click="handelRegionShow"/>
				
				<van-cell :title="$t('lang.seckill_shipment')" v-if="goodsInfo.is_kj == 1 && goodsInfo.cross_warehouse_name" class="my-cell-nobottom">
				    {{goodsInfo.cross_warehouse_name}}
				</van-cell>
				
                <van-cell :title="$t('lang.label_freight')">
					<span v-if="goodsInfo.is_shipping == 1 || freeShipping == 1" style="color: #AF743A;">{{$t('lang.pinkage')}}</span>
                    <span v-else v-html="freight"></span>
                    <span class="color-red" v-if="shipping_fee.shipping_title && shipping_fee.shipping_title != 0">({{ shipping_fee.shipping_title }})</span>
                </van-cell>
            </van-cell-group>
            <van-cell-group class="van-cell-noleft m-top08">
                <van-cell :title="$t('lang.label_selected')" v-model="attr_name" is-link @click="skuLink"/>
            </van-cell-group>
            <!--详情-->
            <van-tabs v-model="active" class="m-top08">
                <van-tab v-for="(item,index) in tabs" :title="item" :key="index">
                    <template v-if="index == 0">
                        <template v-if="goodsInfo.goods_desc">
                            <div v-html="goodsInfo.goods_desc" class="goods_desc"></div>
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
            <van-goods-action>
                <van-goods-action-mini-btn icon="chat" :text="$t('lang.customer_service')" @click="onChat(goodsInfo.goods_id,goodsInfo.user_id)" />
                <van-goods-action-mini-btn :icon="collect_icon" :text="$t('lang.collect')" :class="{'curr':is_collect == 1}" @click="collection" />
                <van-goods-action-big-btn v-if="goodsInfo.status && goodsInfo.is_end ===0" :text="$t('lang.snapped_immediately')" primary @click="onSku" />
                <van-goods-action-big-btn v-if="!goodsInfo.status && goodsInfo.is_end ===0" :text="$t('lang.begin_minute')" />
                <van-goods-action-big-btn v-if="!goodsInfo.status && goodsInfo.is_end !=0" :text="$t('lang.has_ended')" />
            </van-goods-action>
        </div>
        <!-- 属性弹窗 -->
        <van-popup v-model="showBase" position="bottom" class="attr-goods-box">
            <div class="attr-goods-header">
                <template v-if="attr != ''">
					<div class="attr-img">
						<img :src="goodsAttrOper.attr_img || goodsInfo.goods_thumb || goodsInfo.goods_img" class="img">
					</div>
					<div class="attr-info">
						<div class="attr-price" v-html="goodsAttrOper.spec_price_formated"></div>
						<div class="attr-other">{{$t('lang.label_selected')}}{{ goodsAttrOper.attr_name }} {{ num }}{{ goodsInfo.goods_unit }}</div>
						<div class="attr-stock">{{$t('lang.label_stock')}}{{ goodsAttrOper.stock }}</div>
					</div>
                </template>
                <template v-else>
                    <div class="attr-img">
                        <img :src="goodsInfo.goods_img" class="img"/>
                    </div>
                    <div class="attr-info">
                        <div class="attr-name twolist-hidden">{{ goodsInfo.goods_name }}</div>
                        <div class="attr-price" v-html="goodsInfo.sec_price_format">{{ goodsInfo.sec_price_format }}</div>
                        <div class="attr-stock">{{$t('lang.label_stock')}}{{ goodsInfo.goods_number }}</div>
                    </div>
                </template>
                <i class="iconfont icon-close" @click="closeSku"></i>
            </div>
            <div class="attr-goods-content">
                <van-radio-group class="sku-item" v-model="goodsAttrInit[index]" v-for="(item,index) in attr" :key="index">
                    <div class="sku-tit">{{ item.name }}</div>
                    <div class="sku-list">
                        <template v-for="(option,listIndex) in item.attr_key">
                            <van-radio class="option" :class="{'active':goodsAttrInit[index] == option.goods_attr_id}" :name="option.goods_attr_id" :key="listIndex">{{ option.attr_value }}
                            </van-radio>
                        </template>
                    </div>
                </van-radio-group>
            </div>
            <div class="attr-goods-number dis-box">
                <span class="tit box-flex">{{$t('lang.number')}}</span>
                <div class="stepper">
                    <van-stepper
						:disabled="goodsAttrOper.stock == 0 || goodsAttrOper.sec_limit == 0 || goodsAttrOper.spec_disable == 1"
                        v-model="number"
                        integer
                        :min="1"
                        :max="secLimit"
                    />
                </div>
            </div>
            <div class="van-sku-actions">
                <van-button :disabled="goodsAttrOper.stock == 0 || goodsAttrOper.sec_limit == 0 || goodsAttrOper.spec_disable == 1" type="primary" class="van-button--bottom-action" @click="seckillCheckout">{{$t('lang.confirm')}}</van-button>
            </div>
        </van-popup>

        <!--分享海报-->
        <van-popup v-model="shareImgShow" class="shareImg" overlay-class="shareImg-overlay">
            <img :src="shareImg" v-if="shareImg" class="img" />
            <span v-else>{{$t('lang.error_generating_image')}}</span>
        </van-popup>

        <Region :display="regionShow" :regionOptionDate="regionOptionDate" @updateDisplay="getRegionShow" @updateRegionDate="getRegionOptionDate"></Region>

        <CommonNav/>
    </div>
</template>
<script>
	import { mapState } from 'vuex'
	import {
		swiper,
		swiperSlide
	} from 'vue-awesome-swiper'
    
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
        Dialog,
        Popup,
        Checkbox,
        CheckboxGroup,
        RadioGroup,
        Radio,
        Button,
        Stepper
    } from 'vant'

    import CommonNav from '@/components/CommonNav'
    import NotCont from '@/components/NotCont'
    import CountDown from '@/components/CountDown'
    import formProcessing from '@/mixins/form-processing'

    export default {
        name: "seckill-detail",
        mixins: [formProcessing],
        data() {
            return {
                tabs: [this.$t('lang.goods_detail_info'), this.$t('lang.specification_parameter')],
                num:1,
                content: '',
                serviceShow: false,
                processShow: false,
                PriceShow: false,
                active: 2,
				//轮播图滑动
				swiperOption: {
					scrollbarHide: true,
					slidesPerView: 'auto',
					centeredSlides: false,
					grabCursor: true,
					autoplay: 2500,
				},
                seckill_id:this.$route.query.seckill_id,
                tomorrow:this.$route.query.tomorrow,
                back:this.$route.query.back,
                showBase: false,
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
            [Toast.name]: Toast,
            [Dialog.name] : Dialog,
            [Popup.name]: Popup,
            [RadioGroup.name]: RadioGroup,
            [Radio.name]: Radio,
            [Checkbox.name]: Checkbox,
            [Button.name]: Button,
            [CheckboxGroup.name]: CheckboxGroup,
            [Stepper.name]: Stepper
        },
        //初始化加载数据
		async created() {
            
			this.loadSeckillInfo(this.seckill_id, this.tomorrow);
			
			if(this.getRegionData){
			    this.regionOptionDate = this.getRegionData;
			}else{
				let { data } = await this.$store.dispatch('setPosition');
				let itemsBak = {
					province:{ id:data.province_id,name:data.province },
					city:{ id:data.city_id,name:data.city },
					district:{ id:data.district_id, name:data.district},
					street: {id:data.street_id || '',name:data.street || ''},
					postion:{}
				}
			    itemsBak.regionSplic = `${data.province} ${data.city} ${data.district} ${data.street}`;
			
			    this.regionOptionDate = itemsBak;
			}
		},
        mounted(){
            if (window.history && window.history.pushState && this.back) {
                // 向历史记录中插入了当前页
                history.pushState(null, null, document.URL);
                window.addEventListener('popstate', this.goBack, false);
            }
        },
        destroyed(){
            window.removeEventListener('popstate', this.cancel, false);
        },
		computed: {
			...mapState({
                goodsInfo: state => state.other.seckillDetailData,
                shipping_fee: state => state.shopping.shipping_fee
            }),
            attr() {
                return this.$store.state.other.seckillDetailData.attr
            },
            goodsAttrOper() {
                return this.$store.state.other.goodsAttrOper
            },
            stock() {
                return this.goodsInfo.sec_num
            },
			secLimit() {
				if (this.goodsAttrOper.sec_limit && this.goodsAttrOper.stock) {
					return this.goodsAttrOper.stock < this.goodsAttrOper.sec_limit ? this.goodsAttrOper.stock : this.goodsAttrOper.sec_limit;
				}
				return this.goodsAttrOper.stock;
			},
            goodsAttrInit: {
                get() {
                    return this.$store.state.other.goodsAttrInit ? this.$store.state.other.goodsAttrInit : ''
                },
                set(val) {
                    this.$store.state.other.goodsAttrInit = val
                }
            },
            number:{
                get(){
                    return this.goodsInfo.is_minimum > 0 ? this.goodsInfo.minimum : 1
                },
                set(val){
                    this.num = val
                },
            },
            isLogin(){
 			    return localStorage.getItem('token') == null ? false : true
            },
            goodsCollectStatue(){
                return this.$store.state.user.goodsCollectStatue
            },
            is_collect:{
                get(){
                    return this.$store.state.other.seckillDetailData.is_collect
                },
                set(val){
                    this.$store.state.other.seckillDetailData.is_collect = val
                }
            },
            collect_icon(){
                return this.is_collect == 1 ? 'like' : 'like-o'
            },
            //运费
            freight() {
                return this.shipping_fee != null && this.shipping_fee.is_shipping > 0 ? this.shipping_fee
                .shipping_fee_formated : "<span class='color-red'>"+this.$t('lang.is_shipping_area')+"</span>"
            },
			freeShipping() {
				return this.shipping_fee != null && this.shipping_fee.free_shipping ? this.shipping_fee.free_shipping : 0;
			},
            attr_name: {
                get() {
                    return this.attr != '' ? this.goodsInfo.attr_name : this.num
                },
                set(val) {
                    this.goodsInfo.attr_name = val
                }
            },
		},
        methods: {
			async loadSeckillInfo(seckill_id, tomorrow){
			    await this.$store.dispatch('setSeckillDetail',{
			        seckill_id: seckill_id,
			        tomorrow: tomorrow
			    });
			},
            goBack(){
                this.$router.replace({
                    path: this.back
                })
            },
            //选择属性
            skuLink() {
                this.showBase = true
                this.changeAttr()
            },
            onSku(e) {
                if (this.attr.length > 0) {
                    this.showBase = true
                    this.changeAttr()
                } else {
                    this.seckillCheckout();
                }
            },
            //立即购买
            seckillCheckout(){
                let cur_number = Number(this.goodsInfo.order_number) + Number(this.num)
                if(this.goodsInfo.sec_limit === 0 || (this.goodsInfo.sec_limit >= cur_number)){
                    let newAttr = []
                    if (this.attr.length > 0) {
                        newAttr = this.goodsAttrInit
                    }

                    this.$store.dispatch('setSeckillBuy',{
                        sec_goods_id: this.seckill_id,
                        number:this.num,
                        spec: newAttr,
                        warehouse_id:0,
                        area_id:0,
                        area_city:0,
                        goods_spec:0
                    }).then(({ data:data })=>{
                        if(data.error == 1){
                            Toast(data.mesg)
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
                    if(this.goodsInfo.order_number > 0){
                        Toast(this.$t('lang.groupbuy_propmt_1') + this.goodsInfo.order_number +this.$t('lang.groupbuy_propmt_2'))
                    }else{
                        Toast(this.$t('lang.groupbuy_propmt_4'))
                    }
                }
            },
            auctionProcess() {
                this.processShow = !this.processShow
            },
            auctionPrice() {
                this.PriceShow = !this.PriceShow
            },
            closeSku() {
                this.showBase = false
                this.storeBtn = false
            },
            //数量加
            addGoodsNum() {
                var num = this.num
                if(this.goodsInfo.sec_limit === 0 || num < this.goodsInfo.sec_limit){
                    this.num ++
                }else{
                    Toast(this.$t('lang.limit_cannot_exceeded'))
                }
            },
            //数量减
            reduceGoodsNum() {
                var num = this.num
                if (num > 1) {
                    this.num--
                } else {
                    Toast(this.$t('lang.min_can_less_than'))
                }
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
                        path: '/login',
                        query: {
                            redirect: {
                                name: 'seckill-detail',
                                query: {
                                    seckill_id: this.seckill_id,
                                    tomorrow: this.tomorrow
                                },
                                url:url
                            }
                        }
                    })
                }).catch(() => {

                })
            },
            changeAttr() {
                this.$store.dispatch('setSeckillAttrPrice', {
                    goods_id: this.goodsInfo.goods_id,
                    num: this.num,
                    attr_id: this.goodsAttrInit,
					seckill_goods_id: this.seckill_id
                })
            },
            handelRegionShow() {
                this.regionShow = this.regionShow ? false : true
            },
            //运费计算
            shippingFee(val,id) {
                this.$store.dispatch('setShippingFee', {
                    goods_id: this.goodsInfo.goods_id,
                    position: val,
                    goods_attr_id: id,
                    is_price: this.isPrice
                })
            },
            //分享海报
            onGoodsShare(){
                if (this.isLogin) {
                    Toast.loading({ duration: 0, mask: true, forbidClick: true, message: this.$t('lang.loading') })
                    let price = this.goodsInfo.sec_price

                    this.$store.dispatch('setGoodsShare',{
                        goods_id:this.goodsInfo.goods_id,
                        price: price,
                        share_type:0,
                        extension_code:'seckill',
                        code_url:window.location.href,
                        thumb:this.goodsInfo.pictures[0].img_url || this.goodsInfo.goods_img
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
            goodsCollectStatue(){
                //关注跟踪变化
                this.goodsCollectStatue.forEach((v)=>{
                    if(v.id == this.goodsInfo.goods_id){
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
                    link:`${window.ROOT_URL}mobile#/seckill/detail?seckill_id=` + this.seckill_id +'&tomorrow='+this.tomorrow,
                    imgUrl:this.goodsInfo.goods_thumb
                })
            },
            goodsAttrInit(){
                this.changeAttr();
            },
            regionSplic(){
                this.shipping_region = {
                    province_id: this.regionOptionDate.province.id,
                    city_id: this.regionOptionDate.city.id,
                    district_id: this.regionOptionDate.district.id,
                    street_id: this.regionOptionDate.street.id
                }

				setTimeout(()=>{
					//运费
					if(this.goodsInfo) this.shippingFee(this.shipping_region,this.goodsInfo.goods_attr_id);
				},1000)
            }
        }
    }
</script>

<style lang="scss" scoped>
	.cont-box{
		.flex1{
			.country_icon{
				width: 2rem;
				position: relative;
				top: 0.25rem;
				display: inline-block;
				margin-left: 0.5rem;
			}
			.span_block{
				display: block;
				padding-bottom: 0.2rem;
				
				.em_font{
					font-size: 1.2rem;
					font-weight: normal;
					color: #666;
					padding-left: 0.4rem;
				}
				.self_support{
					position: relative;
					top: -0.2rem;
				}
			}
		}
	}
</style>
