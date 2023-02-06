`<template>
	<div class="con_main bargain-detail">
		<div class="goods">
			<!--砍价之前-->
			<div class="p-r">
				<!--轮播图-->
				<van-swipe :autoplay="3000" class="swipe" :height="swipe_height">
					<van-swipe-item v-for="(item, index) in bargainDetailData.goods_img" :key="index">
						<img class="imgalt" :src="item" />
					</van-swipe-item>
				</van-swipe>
				<div class="goods-bargain-img dis-box f-03 color-white p-a">
					<div class="box-flex">{{$t('lang.were_still_end')}}</div>
					<div v-if="goodsInfo.start_time!=undefined && goodsInfo.end_time!=undefined">
						<count-down :endTime="goodsInfo.end_time" :endText="$t('lang.activity_end')"></count-down>
					</div>
				</div>
			</div>
			<!--title-->
			<div class="cont-box">
				<div class="title bg-color-write f-06 color-3 padding-all">
					<!--砍价之前-->
					<div class="flex-common ai-start">
                        <div class="flex1">
							<van-tag type="danger" class="m-r05 f-03">{{$t('lang.kan')}}</van-tag>
							<span class="f-05 color-3 f-weight">{{goodsInfo.goods_name}}</span>
						</div>
						<div class="drp-share" @click="onGoodsShare">
                            <i class="iconfont icon-share"></i>
                            <span>{{$t('lang.share')}}</span>
                        </div>
					</div>
					<div class="goods-price f-06 color-red m-top06" v-if="bargainDetailData.bargain_info.final_price">
						<span class="color-9 f-03">{{$t('lang.label_bargain_end')}}</span><span v-html="bargainDetailData.bargain_info.final_price"></span></div>
					<div v-else class="goods-price f-06 color-red m-top06">
						<span class="color-9 f-03">{{$t('lang.label_original_price')}}</span><span v-html="goodsInfo.goods_price"></span></div>

					<div class="bargain-progressbar dis-box p-r">
						<div class="plan-left plan-common"></div>
						<p class="wrap box-flex" style="border-radius: 0">
							<span class="bar" :style="{width:goodsInfo.bargain_bar + '%'}">
								<i class="color"></i>
							</span>
						</p>
						<div class="plan-right plan-common"></div>
					</div>
					<div class="dis-box color-9 f-03 m-top06">
						<div class="box-flex">{{$t('lang.base_price')}}<span v-html="goodsInfo.target_price"></span></div>
						<div class="box-flex">
							<p class="text-right">{{$t('lang.original_price')}} <span v-html="goodsInfo.shop_price"></span></p>
						</div>
					</div>
					<div class="bargain-tip dis-box" v-if="goodsInfo.total_num > 0">
						<div class="join">{{$t('lang.already_have')}}
							<span class="number">{{goodsInfo.total_num}}</span>{{$t('lang.bargain_propmt_1')}}</div>
						<span class="triangle"></span>
						<div class="order box-flex">
							<van-swipe :autoplay="3800" :duration="2000" style="height: 1.6rem; line-height:1.6rem; background: #fc3; color: #000; font-size: 1.2rem;margin-top:.2rem">
								<van-swipe-item v-for="(item,index) in bargainDetailData.bargain_list" :key="index">
									<div class="cont color-3">
										<span class="color-red f-03">{{ item.user_name }}</span> {{$t('lang.bargain_propmt_2')}}
										<span class="color-red f-03"><span v-html="item.subtract_price"></span></span>
									</div>
								</van-swipe-item>
							</van-swipe>
						</div>
					</div>
				</div>
			</div>
			<!--砍完价后的列表-->
			<div class="bargian-user dis-box" v-if="bargainJoin">
				<div class="left">
					<div class="img-box">
						<img v-if="bargainDetailData.bargain_info.user_picture" class="img" :src="bargainDetailData.bargain_info.user_picture" />
						<img v-else class="img" src="../../../../assets/img/get_avatar.png" />
					</div>
				</div>
				<div class="box-flex right">
					<h4 class="f-05 color-3 box-flex">{{bargainDetailData.bargain_info.user_name}}
						<label>{{$t('lang.label_cur_bargain_rank')}}{{bargainDetailData.bargain_info.rank}}</label>
					</h4>
					
					<div class="f-02 color-9 m-top04">
						<template v-if="addBargain == 1">{{$t('lang.bargain_propmt_3')}}</template>
						<template v-else>{{$t('lang.bargain_propmt_4')}}</template>
						<em class="color-red" v-html="bargainDetailData.bargain_info.subtract_price"></em>{{$t('lang.yuan')}}
					</div>
				</div>
			</div>
			<!--活动规则-->
			<div class="team-rule bg-color-write m-top10">
				<div class="title  f-05 color-3 ">{{$t('lang.activity_rules')}}</div>
				<div class="dis-box bargain-detail-cont f-03 text-center color-9 p-r  ">
					<div class="box-flex p-r">
						<label class="num-left bg-color-write p-r">
							<em>1</em>
							<i class="iconfont icon-dianji"></i>
						</label>
						<p class="">{{$t('lang.click_bargain')}}</p>
						<div class="n-list-xian-first p-a"></div>
					</div>
					<div class="box-flex p-r">
						<label class="num-left bg-color-write p-r">
							<em>2</em>
							<i class="iconfont icon-shehuituanti"></i>
						</label>
						<p class="">{{$t('lang.invite_help_bargain')}}</p>
						<div class="n-list-xian p-a"></div>
					</div>
					<div class="box-flex p-r">
						<label class="num-left bg-color-write p-r">
							<em>3</em>
							<i class="iconfont icon-daojianfu"></i>
						</label>
						<p class="">{{$t('lang.bargain_base_price')}}</p>
						<div class="n-list-xian p-a"></div>
					</div>
					<div class="box-flex p-r">
						<label class="num-left bg-color-write p-r">
							<em>4</em>
							<i class="iconfont icon-wancheng1"></i>
						</label>
						<p class="">{{$t('lang.place_order_buy')}}</p>
						<div class="n-list-xian-last p-a"></div>
					</div>

				</div>
				<div class="team-jiantou p-r" @click="goodsRule">
					<i class="iconfont icon-moreunfold p-a color-9"></i>
				</div>
				<!--拼团规则弹框-->
				<van-actionsheet class="auction-box" v-model="ruleShow" :title="$t('lang.reles_detail')">
					<div class="padding-all">
						<div class="f-03 color-7">{{$t('lang.bargain_rele_propmt_1')}}</div>
						<div class="f-03 color-7 m-top06">{{$t('lang.bargain_rele_propmt_2')}}</div>
						<div class="f-03 color-7 m-top06">{{$t('lang.bargain_rele_propmt_3')}}</div>
					</div>
				</van-actionsheet>
			</div>
			<div v-if="bargainDetailData.bargain_list!=''||bargainDetailData.bargain_ranking!=''">
				<!--亲友帮-->
				<van-tabs class="m-top08" :active="active">
					<van-tab v-for="(item,index) in rankTabs" :key="index">
						<div class="nav_active" slot="title" @click="handleFilter(index)">{{item}}</div>
					</van-tab>
				</van-tabs>
				<div v-show="filter == 0" class="qinyou-cont">
					<div v-if="bargainDetailData.bargain_ranking!=''">
						<div class="li dis-box bg-color-write" v-for="(item, index) in bargainDetailData.bargain_ranking" :key="index" v-if="bargainDetailData.bargain_ranking!=''">
							<div class="rank-box">
								<img v-if="item.rank===1" class="m-top06" src="../../../../assets/img/rank-1.png" />
								<img v-if="item.rank===2" class="m-top06" src="../../../../assets/img/rank-2.png" />
								<img v-if="item.rank===3" class="m-top06" src="../../../../assets/img/rank-3.png" />
								<span class="f-04 color-3" v-if="item.rank>3">{{item.rank}}</span>
							</div>
							<div class="left">
								<div class="img-box">
									<img v-if="item.user_picture" class="img" :src="item.user_picture" />
									<img v-else class="img" src="../../../../assets/img/get_avatar.png" />
								</div>
							</div>
							<div class="box-flex">
								<div class="dis-box m-top10">
									<h4 class="f-05 color-3 box-flex">{{item.user_name}}</h4>
									<div class="color-9 f-02 text-right">{{$t('lang.chop_off')}}
										<span class="color-red " v-html="item.money"></span>{{$t('lang.yuan')}}</div>
								</div>
							</div>
						</div>
					</div>
					<div v-else>
						<NotCont/>
					</div>
				</div>
				<!--亲友帮-->
				<div v-show="filter == 1" class="qinyou-cont">
					<div v-if="bargainDetailData.bargain_list!=''">
						<div class="li dis-box bg-color-write" v-for="(item, index) in bargainDetailData.bargain_list" :key="index">
							<div class="left">
								<div class="img-box">
									<img v-if="item.user_picture" class="img" :src="item.user_picture" />
									<img v-else class="img" src="../../../../assets/img/get_avatar.png" />
								</div>
							</div>
							<div class="box-flex">
								<div class="dis-box m-top02">
									<h4 class="f-05 color-3 box-flex">{{item.user_name}}</h4>
									<div class="color-9 f-02 text-right">{{$t('lang.chop_off')}}
										<span class="color-red" v-html="item.subtract_price"></span>{{$t('lang.yuan')}}</div>
								</div>
								<p class="color-9 f-02 m-top04">{{item.add_time}}</p>
							</div>
						</div>
					</div>
					<div v-else>
						<NotCont/>
					</div>
				</div>
			</div>
			<!--详情-->
			<van-tabs class="m-top08" :active="active">
				<van-tab v-for="(item,index) in detailTabs" :key="index" :title="item">
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
		</div>
		<!--参与砍价-->
		<van-goods-action>
			<van-goods-action-mini-btn icon="chat" :text="$t('lang.customer_service')" @click="onChat(goodsInfo.goods_id,goodsInfo.user_id)" />
			<template v-if="goodsInfo.bs_id > 0">
				<template v-if="addBargain == 1">
					<template v-if="bargainJoin == 1">
						<van-goods-action-big-btn :disabled="disabled" :text="$t('lang.bargain_text_1')" @click="shareClick" />
						<van-goods-action-big-btn :text="$t('lang.button_buy')" primary @click="onBuyClicked" />
					</template>
					<template v-else>
						<van-goods-action-big-btn :disabled="disabled" :text="$t('lang.bargain_text_2')" primary @click="bargainBid" />
					</template>
				</template>
				<template v-else>
					<template v-if="bargainJoin == 1">
						<van-goods-action-big-btn :disabled="disabled" :text="$t('lang.bargain_text_4')" @click="cutDown" />
						<template v-if="goodsInfo.is_add_bargain == 1">
							<van-goods-action-big-btn :disabled="disabled" :text="$t('lang.my_activity')" primary @click="myBargain" />
						</template>
						<template v-else>
							<van-goods-action-big-btn :disabled="disabled" :text="$t('lang.my_participation')" primary @click="bargainLog" />
						</template>
					</template>
					<template v-else>
						<van-goods-action-big-btn :disabled="disabled" :text="$t('lang.bargain_text_3')" @click="bargainBid" />
						<template v-if="goodsInfo.is_add_bargain == 1">
							<van-goods-action-big-btn :disabled="disabled" :text="$t('lang.my_activity')" primary @click="myBargain" />
						</template>
						<template v-else>
							<van-goods-action-big-btn :disabled="disabled" :text="$t('lang.my_participation')" primary  @click="bargainLog" />
						</template>
					</template>
				</template>
			</template>
			<template v-else>
				<van-goods-action-big-btn :disabled="disabled" :text="$t('lang.my_participation')" primary @click="bargainLog" />
			</template>
		</van-goods-action>
		<!--属性弹框-->
		<van-popup v-model="showBase" position="bottom" class="attr-goods-box">
			<div class="attr-goods-header">
				<div class="attr-img bg-color-write">
					<img :src="goodsAttrOper.attr_img" class="img" v-if="goodsAttrOper.attr_img">
					<img :src="goodsInfo.goods_thumb" class="img" v-else>
				</div>
				<div class="attr-info">
					<div class="f-05 twolist-hidden title">{{goodsInfo.goods_name}}</div>
					<div class="attr-price m-top02"><span class="f-03 color-9">{{$t('lang.label_original_price')}}</span>{{goodsAttrOper.goods_price}}</div>
					<div class="attr-stock f-03">{{$t('lang.label_base_price')}}{{ goodsAttrOper.target_price }}</div>
					<div class="attr-stock f-03">{{$t('lang.label_stock')}}{{ goodsAttrOper.stock }}</div>
				</div>
				<i class="iconfont icon-guanbi" @click="closeSku"></i>
			</div>
			<div class="attr-goods-content" v-if="attr != ''">
				<van-radio-group class="sku-item" v-model="goodsAttrInit[index]" v-for="(item,index) in attr" :key="index" @change="changeAttr">
					<div class="sku-tit">{{ item.attr_name }}</div>
					<div class="sku-list">
						<div v-for="(option,listIndex) in item.attr_key" :key="listIndex">
							<van-radio class="option f-03" :class="{'active':goodsAttrInit[index] == option.goods_attr_id}" :name="option.goods_attr_id">{{ option.attr_value }}</van-radio>
						</div>
					</div>
				</van-radio-group>
			</div>
			<div class="attr-goods-number dis-box">
				<span class="tit box-flex f-03">{{$t('lang.number')}}</span>
				<div class="stepper">
					<van-stepper v-model="num" integer :min="1" :max="1" />
				</div>
			</div>
			<div class="van-sku-actions">
				<van-button type="primary" class="van-button--bottom-action" @click="bargainLogCheckout">{{$t('lang.confirm')}}</van-button>
			</div>
		</van-popup>
		<!--砍价成功提示-->
		<div v-if="bargainJoin === 1 && bargain_bid">
			<div class="van-modal" :class="{'hide':!mask}" @click="close(bs_id)" style="z-index:1000">
				<div class="bargain-friends">
					<div class="header f-05 color-3">{{$t('lang.bargain_hint')}}</div>
					<img class="img" src="../../../../assets/img/bargain_s.jpg" />
					<div class="cont f-03 text-center">{{$t('lang.bargain_propmt_5')}}
						<span class="color-red">{{ currency }}{{bargainBidData.subtract_price}}</span> {{$t('lang.yuan')}}</div>
					<div class="footer f-05 color-3" @click="close(bs_id)">{{$t('lang.i_see')}}</div>
				</div>
			</div>
		</div>
		
		<!--分享-->
		<div class="bargain-share van-modal" :class="{'hide':!shareState}" style="z-index:1000">
			<div class="bargain-friends">
				<div class="header f-05 color-3">{{$t('lang.share_hint')}}</div>
				<div class="cont f-03 color-3 text-center">{{$t('lang.share_toast_hint')}}</div>
				<div class="footer f-05 color-3" @click="close(bs_id)">{{$t('lang.i_see')}}</div>
			</div>
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
		Toast,
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
		Dialog
	} from 'vant'

	import NotCont from '@/components/NotCont'
	import CommonNav from '@/components/CommonNav'
	import CountDown from '@/components/CountDown'
	import formProcessing from '@/mixins/form-processing'
	import isApp from '@/mixins/is-app'

	export default {
		mixins: [formProcessing,isApp],
		name: "team-detail",
		components: {
			CommonNav,
			NotCont,
			CountDown,
			[Toast.name]: Toast,
			[Popup.name]: Popup,
			[RadioGroup.name]: RadioGroup,
			[Radio.name]: Radio,
			[Stepper.name]: Stepper,
			[Button.name]: Button,
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
			[Dialog.name]: Dialog,
		},
		data() {
			return {
				active: 0,
				num: 1,
				currency:'¥',
				rankTabs: [this.$t('lang.toprank'), this.$t('lang.friend_bang')],
				detailTabs: [this.$t('lang.goods_detail_info'), this.$t('lang.specification_parameter')],
				filter: 0, //默认显示亲友帮内容
				mask: false,
				shareState: false,
				showBase: false,
				ruleShow: false, //拼团规则
				serviceShow: false, //服务
				bargain_bid:false,
				bs_id:this.$route.query.bs_id ? this.$route.query.bs_id : 0,
				disabled: false,
				shareImgShow:false,
				shareImg:''
			}
		},
		created() {
			let that = this
			setTimeout(() => {
                uni.getEnv(function(res){
                    if(res.plus || res.miniprogram){
                        uni.redirectTo({  
                            url:'../../pagesA/bargain/detail/detail?id='+that.bs_id
                        })
                    }
                })
            },100)
			
			let configData = JSON.parse(sessionStorage.getItem('configData'));
			if (configData) {
				this.currency = configData.currency_format.replace('%s', '');
			}

			this.goodsDetail(this.bs_id)
		},
		computed: {
			...mapState({
				bargainDetailData: state => state.bargain.bargainDetailData
			}),
			goodsInfo(){
				return this.bargainDetailData.goods_info
			},
			//商品属性
			attr(){
				return this.bargainDetailData.goods_properties
			},
			//商品属性id
			goodsAttrInit(){
				return this.$store.state.bargain.goodsAttrInit
			},
			goodsAttrOper(){
				return this.$store.state.bargain.goodsAttrOper
			},
			stock() {
                return this.attr != '' ? this.goodsAttrOper.stock : this.goodsInfo.goods_number
            },
			isLogin(){
				return localStorage.getItem('token') == null ? false : true
		    },
			addBargain:{
				get(){
					return this.$store.state.bargain.addBargain
				},
				set(val){
					this.$store.state.bargain.addBargain = val
				}
			},
			bargainBidData:{
				get(){
					return this.$store.state.bargain.bargainBidData
				},
				set(val){
					this.$store.state.bargain.bargainBidData = val
				}
			},
			bargainJoin:{
				get(){
					return this.$store.state.bargain.bargainJoin
				},
				set(val){
					this.$store.state.bargain.bargainJoin = val
				}
			},
		},
		methods: {
			//关闭属性弹框
			closeSku() {
				this.showBase = false;
				this.bargainBidData.bargain_join =''
			},
			//属性选择
			changeAttr() {
				this.$store.dispatch('setBargainProperty',{
					id: this.$route.params.id,
					num: this.num,
					attr_id: this.goodsAttrInit,
					warehouse_id: 0,
					area_id: 0
				})
			},
			//参与砍价
			bargainLog() {
				this.showBase = true
				this.changeAttr()
			},
			//确定参与
			bargainLogCheckout() {
				if(this.isLogin){
					if(this.goodsAttrOper.stock > 0){
						this.$store.dispatch('setBargainLog',{
							id: this.$route.params.id,
							num: this.num,
							attr_id: this.goodsAttrInit,
							warehouse_id: 1,
							area_id: 1
						}).then(({ data:data }) =>{
							Toast(data.msg)
							this.showBase = false
							if(data.error == 0){
								this.addBargain = data.add_bargain
								this.goodsDetail(data.bs_id)
							}
						})
					}else{
						Toast(this.$t('lang.understock'))
					}
				}else{
					let msg = this.$t('lang.not_login_bargain')
                    this.notLogin(msg)
				}
			},
			//去砍一刀
			bargainBid() {
				if(this.isLogin){
					this.$store.dispatch('setBargainBid',{
						id: this.goodsInfo.id,
						bs_id: this.goodsInfo.bs_id
					}).then(({ data:data })=>{
						if(data.error == 0){
							this.bargainBidData = data
							this.bargainJoin = data.bargain_join
							this.bargain_bid = true
							this.mask = true
						}else{
							Toast(data.msg)
						}
					})
				}else{
					let msg = this.$t('lang.not_login_bang')
                    this.notLogin(msg)
				}
			},
			//立即购买
			onBuyClicked() {
				this.$store.dispatch('setBargainBuy',{
					id: this.goodsInfo.id,
					bs_id: this.goodsInfo.bs_id,
					goods_id: this.goodsInfo.goods_id,
					num: this.num
				}).then(({data})=>{
					if(data.error == 0){
						this.$router.push({
			              name: "checkout",
			              query: {
			                rec_type:8,
			                type_id:data.bs_id
			              }
			            })
					}else{
						Toast(data.msg)
					}
				})
			},
			myBargain(){
				this.$router.push({
					name:'bargain-mylist'
				})
			},
			//详情数据
			goodsDetail(bsId) {
				this.$store.dispatch('setBargainDetail',{
					id: this.$route.params.id,
					bs_id: bsId
				})
			},
			//亲友帮、排行榜
			handleFilter(index) {
				this.filter = index
			},
			//拼团规则
			goodsRule() {
				this.ruleShow = !this.ruleShow
			},
			//分享
			shareClick() {
				this.shareState = true
			},
			//关闭蒙板
			close(id) {
				this.mask = false
				this.shareState = false
				this.goodsDetail(id)
			},
			cutDown(){
				Toast(this.$t('lang.bargain_text_5'))
			},
			notLogin(msg) {
                Dialog.confirm({
                    message: msg,
                    className: 'text-center'
                }).then(() => {
            		let url = window.location.href
            		this.$router.push({
                        name: 'login',
                        query: {
                        	redirect: {
                        		name: 'bargain-detail', 
                        		params: {id: this.goodsInfo.id},
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
                    let price = this.goodsInfo.target_price

                    this.$store.dispatch('setGoodsShare',{
                        goods_id:this.goodsInfo.goods_id,
                        price: price,
                        share_type:0,
                        extension_code:'bargain',
                        code_url:window.location.href,
                        thumb:this.bargainDetailData.goods_img[0]
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
			bargainDetailData(){
				let bs_id = this.$route.query.bs_id;
				let url = window.location.href.split('#')[0] + '#';
				let link;
				let parent_id = this.$route.query.parent_id ? this.$route.query.parent_id : null;

				//活动结束
				this.disabled = this.goodsInfo.bargain_end == 1 ? true : false;

				//if(bs_id){
				if(parent_id){
					link = url + this.$route.path +'?parent_id='+ parent_id + '&bs_id=' + this.goodsInfo.bs_id
				}else{
					link = url + this.$route.path +'?bs_id='+ this.goodsInfo.bs_id
				}
				//}else{
				//	link = window.location.href + '&bs_id=' + this.goodsInfo.bs_id
				//}
				if(this.goodsInfo.bs_id > 0){
	                //单独设置微信分享信息
                	this.$wxShare.share({
                		title:this.goodsInfo.goods_name,
	                    desc:this.goodsInfo.bargain_desc,
	                    link:link,
	                    imgUrl:this.goodsInfo.goods_thumb
                	})
                }

                //确定参与砍价
                if(this.goodsInfo.bs_id > 0 && this.goodsInfo.bs_id != bs_id){
                	this.$router.replace({
                        name: 'bargain-detail',
                        params:{
                        	id:this.goodsInfo.id
                        },
                        query:{
                        	bs_id:this.goodsInfo.bs_id
                        }
                    })
                }
			},
			'$route'(to,form){
				this.goodsDetail(0)
			}
		}
	}
</script>
