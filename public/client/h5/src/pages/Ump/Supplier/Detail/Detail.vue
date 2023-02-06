<template>
    <div class="con">
        <div class="goods con_main">
            <!--轮播图-->
            <van-swipe :autoplay="3000" class="swipe" :height="swipe_height">
                <van-swipe-item v-for="(item, index) in supplierGoodsDetail.goods_img" :key="index">
                    <img class="imgalt" :src="item" />
                </van-swipe-item>
            </van-swipe>

            <!--title-->
            <div class="cont-box">
                <div class="title bg-color-write f-06 color-3 padding-all">
                   <span class="f-05 color-3 f-weight">{{supplierGoodsDetail.goods_name}}</span>
                    <div class="dis-box color-9 f-04 m-top08" v-if="supplierGoodsDetail.volume_price && supplierGoodsDetail.volume_price.length > 0">
                        <div class="volume_price_tr box-flex">
                            <div class="f-03 color-9">{{$t('lang.trade_price')}}</div>
                            <div class="f-03 color-9">
                                <del>{{$t('lang.original_price')}}</del>
                            </div>
                            <div class="f-03 color-9">{{$t('lang.volume_number')}}</div>
                        </div>
                        <div class="volume_price_tr box-flex text-center" v-for="(item, index) in supplierGoodsDetail.volume_price" :key="index">
                            <div class="f-06 color-red">{{ currency }}{{item.volume_price}}</div>
                            <div class="f-03 color-9">
                                <del>{{ currency }}{{supplierGoodsDetail.retail_price}}</del>
                            </div>
                            <template v-if="item.range_number">
                                <div class="f-03 color-9">{{item.volume_number}}~{{item.range_number}}</div>
                            </template>
                            <template v-else>
                                <div class="f-03 color-9">≥{{item.volume_number}}</div>
                            </template>
                        </div>
                    </div>
                    <div v-else class="f-06 color-red m-top06" v-html="supplierGoodsDetail.goods_price_formatted"></div>
                </div>
            </div>
            <section class="padding-all bg-color-write border-t-common who_server" v-if="supplierGoodsDetail.get_wholesale_extend && (supplierGoodsDetail.get_wholesale_extend.is_reality || supplierGoodsDetail.get_wholesale_extend.is_return || supplierGoodsDetail.get_wholesale_extend.is_fast)">
                <div class="dis-box">
                    <label class="f-04 color-7">{{$t('lang.power_security')}}</label>
                    <div class="box-flex">
                        <div class="help g-r-rule f-03 color-9">
                            <p class="p-l05" v-if="supplierGoodsDetail.get_wholesale_extend.is_reality">
                                <em class="fl em-promotion">48</em>
                                <span class="fl p-l05">{{$t('lang.shippment')}}</span>
                            </p>
                            <p class="p-l05" v-if="supplierGoodsDetail.get_wholesale_extend.is_free">
                                <em class="fl em-promotion">{{$t('lang.mail')}}</em>
                                <span class="fl p-l05">{{$t('lang.pinkage')}}</span>
                            </p>
                            <p class="p-l05" v-if="supplierGoodsDetail.get_wholesale_extend.is_return">
                                <em class="fl em-promotion">{{$t('lang.refund')}}</em>
                                <span class="fl p-l05">{{$t('lang.can_back')}}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="attr-goods-box m-top08">
                <div class="attr-goods-content" v-if="propertiesSecon">
                    <van-radio-group class="sku-item" v-model="supplierGoodsAttrInit[index]" v-for="(item,index) in propertiesSecon" :key="index">
                        <div class="sku-tit">{{ item.name }}</div>
                        <div class="sku-list">
                            <template v-for="(option,listIndex) in item.values">
                                <van-radio class="option" :class="{'active':supplierGoodsAttrInit[index] == option.id}" :name="option.id">{{ option.label }}
                                </van-radio>
                            </template>
                        </div>
                    </van-radio-group>
                    <div class="wholesale-property">
                        <div class="property-attr" v-if="propertiesAll">
                            <div class="t-remark title">{{ propertiesAll.name }}</div>
                            <div class="li dis-box" v-for="(value,valueIndex) in propertiesAll.values" :key="valueIndex">
                                <div class="left box-flex">
                                    <div class="f-04 col-6">{{ value.label }}</div>
                                    <div class="box-flex col-9 unit_price">{{ currency }}{{ volumePrice }}</div>
                                </div>
                                <div class="content box-flex">
                                    <div class="box-flex col-9" v-if="mianStock && mianStock.length > 0">{{ mianStock[valueIndex].product_number }}{{supplierGoodsDetail.goods_unit}}{{$t('lang.vendibility')}}</div>
                                    <div class="box-flex col-9" v-else>{{ value.product_number }}{{supplierGoodsDetail.goods_unit}}{{$t('lang.vendibility')}}</div>
                                </div>
                                <div class="right box-flex">
                                    <div class="stepper">
                                        <van-stepper
                                            v-model="number[valueIndex]"
                                            integer
                                            :min="0"
                                            :max="mianStock && mianStock.length > 0 ? mianStock[valueIndex].product_number : value.product_number"
                                            @change="onChange(number[valueIndex],valueIndex)"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="property-attr" v-else>
                            <div class="t-remark title">{{$t('lang.specification')}}</div>
                            <div class="li dis-box">
                                <div class="left box-flex">
                                    <div class="f-04 col-6">{{$t('lang.not_specification')}}</div>
                                    <div class="box-flex col-9 unit_price">{{ volumePrice }}</div>
                                </div>
                                <div class="content box-flex">
                                    <div class="box-flex col-9">{{ supplierGoodsDetail.goods_number }}{{supplierGoodsDetail.goods_unit}}{{$t('lang.vendibility')}}</div>
                                </div>
                                <div class="right box-flex">
                                    <div class="stepper">
                                        <van-stepper
                                            v-model="notAttrNumber"
                                            integer
                                            :min="0"
                                            :max="supplierGoodsDetail.goods_number"
                                            @change="onChange(notAttrNumber)"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <van-tabs v-model="active" class="goods_detail m-top08">
                <van-tab v-for="(tab,index) in tabs" :key="index" :title="tab">
                    <template v-if="index == 0">
                        <template v-if="supplierGoodsDetail.goods_desc">
                            <div v-html="supplierGoodsDetail.goods_desc" class="bg-color-write goods_desc"></div>
                        </template>
                        <template v-else>
                            <NotCont></NotCont>
                        </template>
                    </template>
                    <template v-else>
                        <template v-if="supplierGoodsDetail.goods_pro">
                            <div class="goods_attr_parameter">
                            <table cellpadding="0" cellspacing="1" width="100%" border="0" class="Ptable param_table">
                                <tbody>
                                    <tr><td>{{$t('lang.goods_sn')}}</td><td>{{ supplierGoodsDetail.goods_sn }}</td></tr>
                                </tbody>
                                <tbody>
                                    <tr><th class="tdTitle" colspan="2">{{$t('lang.subject')}}</th></tr>
                                    <tr><td>{{$t('lang.brand')}}</td><td>{{ supplierGoodsDetail.brand_name }}</td></tr>
                                    <tr><td>{{$t('lang.goods_weight')}}</td><td>{{ supplierGoodsDetail.goods_weight }}kg</td></tr>
                                    <tr><th class="tdTitle" colspan="2">{{$t('lang.attr_parameter')}}</th></tr>
                                    <tr v-for="item in supplierGoodsDetail.goods_pro"><td>{{ item.name }}</td><td>{{ item.value }}</td></tr>
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

            <div class="selector-info-tip" v-if="amountNum > 0">
                <div class="info-right">
                    <div class="total-amount">
                        <span>{{$t('lang.gong')}}</span>
                        <span class="amount-num">{{amountNum}}</span>
                        <span>{{supplierGoodsDetail.goods_unit}}</span>
                    </div>
                    <div class="total-price">
                        <span class="price-unit">{{ currency }}</span>
                        <span class="price-num">{{totalPrice}}</span>
                    </div>
                </div>
            </div>
            <van-goods-action>
                <van-goods-action-mini-btn icon="chat" :text="$t('lang.customer_service')" @click="onChat(goods_id,supplierGoodsDetail.suppliers_id,'supplier')" />
                <van-goods-action-mini-btn icon="shop" :to="{name:'supplier-shop',params:{id:supplierGoodsDetail.suppliers_id}}">{{$t('lang.supplier')}}</van-goods-action-mini-btn>
                <van-goods-action-mini-btn icon="cart" :info="cart_number" :to="{name:'supplier-cart'}">
                    {{$t('lang.purchase_order')}}
                    <span class="add_num" :class="{'show':addCartClass == true}" id="popone">+{{ quantity }}</span>
                </van-goods-action-mini-btn>
                <template v-if="supplierGoodsDetail.review_status == 3 && supplierGoodsDetail.suppliers_review_status == 3 && supplierGoodsDetail.enabled == 1">
					<van-goods-action-big-btn :text="$t('lang.add_purchase_order')" @click="addCart" />
					<van-goods-action-big-btn :text="$t('lang.order_immediately')" @click="addCart(1)" primary />
				</template>
				<template v-else>
					<van-goods-action-big-btn :text="$t('lang.add_purchase_order')" class="van-button--disabled" disabled />
					<van-goods-action-big-btn :text="$t('lang.order_immediately')" class="van-button--disabled" disabled />
				</template>
            </van-goods-action>
        </div>
        <CommonNav :routerName="routerName"></CommonNav>
    </div>
</template>

<script>
    import qs from 'qs'
	import { mapState } from 'vuex'
    import {
        Swipe,
        SwipeItem,
        GoodsAction,
        GoodsActionBigBtn,
        GoodsActionMiniBtn,
        Tab,
        Tabs,
        Tag,
        Toast,
        RadioGroup,
        Radio,
        Checkbox,
        CheckboxGroup,
        Stepper,
    } from 'vant'

    import NotCont from '@/components/NotCont'
    import CountDown from '@/components/CountDown'
    import formProcessing from '@/mixins/form-processing'
    import CommonNav from '@/components/CommonNav'
    export default {
        name: "wholesale-detail",
        mixins: [formProcessing],
        components: {
            NotCont,
            CountDown,
            [Swipe.name]: Swipe,
            [SwipeItem.name]: SwipeItem,
            [GoodsAction.name]: GoodsAction,
            [GoodsActionBigBtn.name]: GoodsActionBigBtn,
            [GoodsActionMiniBtn.name]: GoodsActionMiniBtn,
            [Tab.name]: Tab,
            [Tabs.name]: Tabs,
            [Tag.name]: Tag,
            [Toast.name]:Toast,
            [RadioGroup.name]: RadioGroup,
            [Radio.name]: Radio,
            [Checkbox.name]: Checkbox,
            [CheckboxGroup.name]: CheckboxGroup,
            [Stepper.name]: Stepper,
            CommonNav
        },
        data() {
            return {
                tabs: [this.$t('lang.goods_detail_info'), this.$t('lang.specification_parameter')],
                content: '',
				currency:'￥',
                num:[],
                quantity:1,
                addCartClass:false,
                processShow: false,
                PriceShow: false,
                active: 2,
                attrData:['0'],
                numDate:['0'],
                mianStock:[],
                seconAttrStr:'',
                attr_array:[],
                amountNum:0,
                totalPrice:0,
                notAttrNumber:0,
                goods_id:this.$route.params.id,
                routerName:'supplier'
            };
        },
        created() {
			let configData = JSON.parse(sessionStorage.getItem('configData'));
			if(configData){
			  this.currency = configData.currency_format.replace('%s', '');
			}
			
            this.$store.dispatch('setSupplierDetail',{
                goods_id: this.$route.params.id
            })
		},
		computed: {
			...mapState({
                supplierGoodsDetail: state => state.other.supplierGoodsDetail,
			}),
            properties(){
                return this.$store.state.other.supplierGoodsDetail.properties
            },
            //规格
            propertiePro(){
                return this.properties !== undefined ? this.properties.pro : ''
            },
            //属性
            propertieSpe(){
                return this.properties !== undefined ? this.properties.spe : ''
            },
            //主属性 唯一
            propertiesAll(){
               return this.propertieSpe[this.propertieSpe.length-1]
            },
            //副属性 可以多个
            propertiesSecon(){
                let arr = []
                if(this.propertieSpe){
                    arr = this.propertieSpe.slice(0,-1);
                }

                return arr
            },
            volumePrice(){
                let price = 0
                if(this.supplierGoodsDetail.volume_price && this.supplierGoodsDetail.volume_price.length>0){
                    this.supplierGoodsDetail.volume_price.forEach((v,i)=>{
                        if(i === 0){
                            price = v.volume_price
                        }
                    })
                }else{
                    price = this.supplierGoodsDetail.goods_price
                }
                return price
            },
            supplierGoodsAttrInit:{
                get(){
                    return this.$store.state.other.supplierGoodsAttrInit
                },
                set(val){
                    this.$store.state.other.supplierGoodsAttrInit = val
                }
            },
            number:{
                get(){
                    let number = []
                    if(this.propertiesAll){
                        this.propertiesAll.values.forEach(v=>{
                            number.push(0)
                        })
                    }

                    this.num = number;

                    return number
                },
                set(val){
                    this.num = val;
                }
            },
            cart_number: {
                get() {
                    return this.$store.state.other.supplierGoodsDetail.cart_number
                },
                set(val) {
                    this.$store.state.other.supplierGoodsDetail.cart_number = val
                }
            },
		},
        methods: {
            //加入供求购物车
            addCart(type){
                let o = {}

                if(this.supplierGoodsAttrInit.length > 0 || this.propertiesAll != undefined){
                    o = {
                        goods_id: this.$route.params.id,
                        attr_array:this.attr_array,
                        num_array:this.num
                    }
                }else{
                    o = {
                        goods_id:this.$route.params.id,
                        goods_number:this.notAttrNumber
                    }
                }

                this.$store.dispatch('setSupplierAddtocart',o).then(res=>{
                    if(res.status == 'success'){
                        //立即购买
                        if(type == 1){
                            if(parseInt(res.data.total_number) >= parseInt(this.supplierGoodsDetail.moq)){
                                this.$router.push({
                                    name:'supplier-cart',
                                    query:{
                                        goods_id:this.goods_id
                                    }
                                })
                            }else{
                                Toast(this.$t('lang.min_batch_not_reached'))
                            }
                        }else{
                            if(parseInt(res.data.total_number) > 0){
                                this.addCartClass = true
                                Toast.success({
                                    duration: 1000,
                                    message: this.$t('lang.is_add_purchase_order')
                                })
                                this.cart_number = res.data.cart_number
                            }else{
                                Toast(this.$t('lang.fill_in_goods_number'))
                            }
                        }
                    }else{
                        Toast(this.$t('lang.add_purchase_order_fail'))
                    }
                })
            },
            changeAttr(){
                this.$http.post(`${window.ROOT_URL}api/suppliers/changenum`,qs.stringify({
                    goods_id:this.$route.params.id,
                    attr:this.supplierGoodsAttrInit
                })).then(res=>{
                    this.mianStock = res.data.data
                })
            },
            onChange(value,index){
                let o = {}
                if(index != undefined){
                    let number = this.number;
                    number[index] = value;
                    this.number = number;

                    o = {
                        goods_id:this.$route.params.id,
                        attr_array:this.attr_array,
                        num_array:this.num
                    }
                }else{
                    o = {
                        goods_id:this.$route.params.id,
                        goods_number:this.notAttrNumber
                    }
                }

                this.$http.post(`${window.ROOT_URL}api/suppliers/changeprice`,qs.stringify(o)).then(({data:data})=>{
                    let res = data;
                    if(res.status === 'success'){
                        this.amountNum = res.data.data.total_number
                        this.totalPrice = res.data.data.total_price_formatted
                    }else{
                        Toast(this.$t('lang.attr_price_back_fail'))
                    }
                })
            }
        },
        watch:{
            supplierGoodsAttrInit(){
                if(this.supplierGoodsAttrInit.length > 0){
                    this.changeAttr();

                    //主属性初始化
                    this.seconAttrStr = [];
                    this.attr_array = [];

                    //主属性转字符串
                    this.supplierGoodsAttrInit.forEach(v=>{
                        this.seconAttrStr += v + ',';
                    })

                    //主次拼接
                    this.propertiesAll.values.forEach((v,i)=>{
                        this.attr_array[i] = this.seconAttrStr + v.id;
                    })
                }
            },
            propertiesAll(){
                if(this.supplierGoodsAttrInit.length == 0){
                    this.propertiesAll.values.forEach((v,i)=>{
                        this.attr_array[i] = v.id
                    })
                }
            }
		}
    }
</script>

<style scoped>
.volume_price_tr div{ line-height: 2rem; height: 2rem; }
.help p{ margin-left: 1rem; overflow: hidden; display: inline-block;}

.attr-goods-box{ background-color: #ffffff; }
.attr-goods-box .attr-goods-content .sku-item .sku-tit{ color: #333; }

.selector-info-tip{ display: flex; position: fixed; left: 0; right: 0; bottom: 50px; z-index: 101; border-top: 1px solid #eee;}
.selector-info-tip .info-right{ display: flex; flex-direction: row; justify-content: flex-end; align-items: center; width: 100%; background-color: #ffffff; padding: 1rem;}
.selector-info-tip .info-right .total-amount{ margin-right: 1.5rem; color: #999; font-size: 1.4rem; margin-top: 2px;}
.selector-info-tip .info-right .total-amount .amount-num{ color:#f44; padding: 0 3px; }
.selector-info-tip .info-right .total-price{ color:#f44;}
.selector-info-tip .info-right .total-price .price-unit{ font-size: 1.4rem; }
.selector-info-tip .info-right .total-price .price-num{ font-size: 2rem; }
</style>
