<template>
	<view class="container fixed-bottom-padding">
		<goods-nav-bar :navIconRgba="navIconRgba" :navOpacity="navOpacity" :rgba="rgba"></goods-nav-bar>
		<scroll-view class="scrollList" scroll-y>
			<view class="goods-wrap">
				<view class="banner">
					<swiper indicator-dots="true" @change="swiperChange">
						<swiper-item v-if="goodsInfo.goods_video">
							<view class="goods-video" v-if="poster">
								<video :src="goodsInfo.goods_video" @error="videoErrorCallback" controls show-center-play-btn="true" autoplay="true"></video>
							</view>
							<view class="goods-img-first" v-else>
								<image :src="gallerylList[0]" class="imgbox" @click="onPlay" v-if="gallerylList"></image>
								<view class="playicon" @click="onPlay">
									<view class="iconfont icon-play"></view>
								</view>
							</view>
						</swiper-item>
						<block v-if="gallerylList.length > 0">
							<swiper-item v-for="(item,index) in gallerylList" :key="index">
								<image :src="item" class="imgbox"></image>
							</swiper-item>
						</block>
						<block v-else>
							<swiper-item><image :src="goodsInfo.goods_img" class="imgbox"></image></swiper-item>
						</block>
					</swiper>
				</view>
				<view class="cont-box">
					<view class="title">
						<view class="goods-price">
							<text class="price-original">{{ goodsInfo.team_price }}</text>
							<text class="price-favour">{{ goodsInfo.shop_price }}</text>
						</view>
						<view class="uni-flex-common uni-space-between">
							<view class="goods-name twolist-hidden flex_1"><text>{{goodsInfo.goods_name}}</text></view>
							<view class="goods_share" @click="mpShare">
								<text class="iconfont icon-share" style="line-height: 1;"></text>
								<text class="share_txt">{{ $t('lang.share') }}</text>
							</view>
						</view>
						<view class="goods_outer">
							<view class="text-left">{{$t('lang.sales_volume')}} {{ goodsInfo.sales_volume }}</view>
							<view class="text-right">{{$t('lang.current_stock')}} {{ goodsInfo.goods_number }}</view>
						</view>
					</view>
				</view>
				<view class="uni-card uni-card-not" v-if="goodsInfo.goods_extend && (goodsInfo.goods_extend.is_reality || goodsInfo.goods_extend.is_return || goodsInfo.goods_extend.is_fast)">
					<view class="uni-list">
						<view class="uni-list-cell" v-if="goodsInfo.goods_extend && (goodsInfo.goods_extend.is_reality || goodsInfo.goods_extend.is_return || goodsInfo.goods_extend.is_fast)">
							<view class="uni-list-cell-navigate">
								<text class="title">{{$t('lang.service')}}</text>
								<view class="value">
									<text v-if="goodsInfo.goods_extend && goodsInfo.goods_extend.is_reality">{{$t('lang.is_reality')}}</text>
									<text v-if="goodsInfo.goods_extend && goodsInfo.goods_extend.is_return">{{$t('lang.is_return')}}</text>
									<text v-if="goodsInfo.goods_extend && goodsInfo.goods_extend.is_fast">{{$t('lang.is_fast')}}</text>
								</view>
							</view>
						</view>
					</view>
				</view>
				<view class="uni-card uni-card-not">
					<view class="uni-list">
						<view class="uni-list-cell uni-list-cell-title" @click="handleRule">
							<view class="uni-list-cell-navigate">
								<text class="title">{{$t('lang.team_rule')}}</text>
								<view class="steps">
									<view class="item" v-for="(item,index) in steps" :key="index">
										<view class="num">{{index+1}}</view>
										<view class="tit">{{item.title}}</view>
										<view class="n-list-xian"></view>
									</view>
								</view>
								<uni-icons type="arrowdown" size="24" color="#777777"></uni-icons>
							</view>
						</view>
					</view>
				</view>
				<view class="team-log-box" v-if="teamDetailData.team_log && teamDetailData.team_log.length > 0">
					<view class="title">{{$t('lang.team_propmt_1')}}</view>
					<view class="team-list-box" v-for="(item,index) in teamDetailData.team_log" :key="index">
						<view class="left">
							<image :src="item.user_picture" v-if="item.user_picture" mode="widthFix"></image>
							<image :src="imagePath.userDefaultImg" mode="widthFix" v-else></image>
						</view>
						<view class="con">
							<view class="header">
								<view class="tit">{{ item.user_name }}</view>
								<view class="txt">{{$t('lang.short')}}<text class="uni-red">{{item.surplus}}</text>{{$t('lang.one_cheng_group')}}</view>
							</view>
							<view class="time">
								<block v-if="dateTime[index]">
									{{$t('lang.residue')}}<uni-countdown notStyle="true" :timer="dateTime[index]"></uni-countdown>{{$t('lang.end')}}
								</block>
								<block v-else>
									<text class="end">{{$t('lang.activity_end')}}</text>
								</block>
							</view>
						</view>
						<view class="right" @click="onTeamCheckout(item.team_id)">{{$t('lang.to_tuxedo')}}</view>
					</view>
				</view>
				<view class="uni-card uni-card-not" v-if="teamDetailData.new_goods && teamDetailData.new_goods.length > 0">
					<view class="uni-list">
						<view class="uni-list-cell uni-list-cell-title">
							<view class="uni-list-cell-navigate">
								<text class="title">{{$t('lang.team_rec')}}</text>
								<scroll-view class="scroll-view scroll-view-product" scroll-x="true" scroll-left="0">
									<navigator :url="'/pagesA/team/detail/detail?goods_id='+ item.goods_id + '&team_id=0'" class="scroll-view-item" v-for="(item,index) in teamDetailData.new_goods" :key="index">
										<image :src="item.goods_thumb" mode="widthFix"></image>
										<text class="name uni-ellipsis">{{ item.goods_name }}</text>
										<view class="price uni-flex">
											<view class="price-original">{{ item.team_price }}</view>
										</view>
									</navigator>
								</scroll-view>
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class="goods-desc">
				<view class="uni-common-mt">
					<uni-segmented-control :current="current" :values="tabs" v-on:clickItem="onClickItem" styleType="text" activeColor="#f92028"></uni-segmented-control>
				</view>
				<view class="content">
					<view v-show="current === 0">
						<block v-if="goodsDesc">
							<rich-text :nodes="goodsDesc"></rich-text>
						</block>
						<block v-else>
							<dsc-not-content></dsc-not-content>
						</block>
					</view>
					<view v-show="current === 1">
						<block v-if="goodsInfo.attr_parameter">
							<view class="table">
								<view class="tr">
									<view class="td">{{$t('lang.goods_sn')}}</view>
									<view class="td">{{goodsInfo.goods_sn}}</view>
								</view>
								<view class="tr colspan">
									<view class="td">{{$t('lang.subject')}}</view>
								</view>
								<view class="tr">
									<view class="td">{{$t('lang.brand')}}</view>
									<view class="td">{{ goodsInfo.brand_name }}</view>
								</view>
								<view class="tr">
									<view class="td">{{$t('lang.goods_weight')}}</view>
									<view class="td">{{ goodsInfo.goods_weight }}kg</view>
								</view>
								<view class="tr colspan">
									<view class="td">{{$t('lang.attr_parameter')}}</view>
								</view>
								<view class="tr" v-for="(item,index) in goodsInfo.attr_parameter" :key="index">
									<view class="td">{{item.attr_name}}</view>
									<view class="td">{{item.attr_value}}</view>
								</view>
							</view>
						</block>
						<block v-else>
							<dsc-not-content></dsc-not-content>
						</block>
					</view>
				</view>
			</view>
		</scroll-view>
		<!-- 底部版权 -->
		<dsc-copyright></dsc-copyright>
		<view class="dsc-safe-area-inset-bottom"></view>
		<view class="btn-goods-action">
			<!-- #ifdef MP-WEIXIN -->
			<button open-type="contact" type="default" size="5" session-from="weapp" hover-class="none" class="item kefu-cantact" v-if="wxappChat > 0">
				<view class="iconfont icon-service"></view>
				<text class="txt">{{$t('lang.customer_service')}}</text>
			</button>
			<view class="item" @click="onChat(goodsInfo.goods_id,goodsInfo.user_id)" v-else>
				<view class="iconfont icon-service"></view>
				<text class="txt">{{$t('lang.customer_service')}}</text>
			</view>
			<!-- #endif -->
			<!-- #ifndef MP-WEIXIN -->
			<view class="item" @click="onChat(goodsInfo.goods_id,goodsInfo.user_id)">
				<view class="iconfont icon-service"></view>
				<text class="txt">{{$t('lang.customer_service')}}</text>
			</view>
			<!-- #endif -->
			<view class="item" @click="collection">
				<block v-if="is_collect == 1"><view class="iconfont icon-collection-alt"></view></block>
				<block v-else><view class="iconfont icon-collection"></view></block>
				<text class="txt">{{$t('lang.collect')}}</text>
			</view>
			<view class="item pr" @click="onCart">
				<view class="iconfont icon-cart"><text class="icon">{{ cart_number }}</text></view>
				<text class="txt">{{$t('lang.cart')}}</text>
				<view class="add_num" :class="{'show':addCartClass == true}">+{{ num }}</view>
			</view>
			<view class="btn-bar">
				<block v-if="team_id > 0">
					<view class="btn btn-org" @click="onceTeam"><view>{{$t('lang.immediately_open_group')}}</view></view>
					<view class="btn btn-red" @click="onTeamSku('team')"><view>{{$t('lang.to_tuxedo')}}</view></view>
				</block>
				<block v-else>
					<view class="btn btn-org" @click="onTeamSku('ordinary')">
						<text class="mt10">{{$t('lang.buy_separately')}}</text>
						<text>{{goodsInfo.shop_price}}</text>
					</view>
					<view class="btn btn-red" @click="onTeamSku('team')">
						<text class="mt10">{{goodsInfo.team_num}}{{$t('lang.one_group')}}</text>
						<text>{{goodsInfo.team_price}}</text>
					</view>
				</block>
			</view>
		</view>
		
		<dsc-common-nav>
			<navigator url="../team" class="nav-item" slot="right">
				<view class="iconfont icon-team"></view>
				<text>{{$t('lang.my_team')}}</text>
			</navigator>
		</dsc-common-nav>
		
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
		
		<uni-popup :show="ruleShow" type="bottom" v-on:hidePopup="handleClose('rule')">
			<view class="title">
				<view class="txt">{{$t('lang.team_rule')}}</view>
				<uni-icons type="closeempty" size="36" color="#999999" @click="handleClose('rule')"></uni-icons>
			</view>
			<view class="rule">
				<view>{{$t('lang.team_rule_con_1')}}</view>
				<view>{{$t('lang.team_rule_con_2')}}</view>
				<view>{{$t('lang.team_rule_con_3')}}</view>
				<view>{{$t('lang.team_rule_con_4')}}</view>
			</view>
		</uni-popup>
		
		<uni-popup :show="showBase" type="bottom" v-on:hidePopup="handleClose('base')">
			<view class="attr-goods-box">
				<view class="attr-goods-header">
					<view class="attr-img">
						<image :src="goodsAttrOper.attr_img" mode="widthFix" v-if="goodsAttrOper.attr_img"></image>
						<image :src="goodsInfo.goods_thumb" mode="widthFix"></image>
					</view>
					<view class="attr-info">
						<block v-if="attr">
							<view class="attr-price" v-if="purchaseType == 'team'">{{goodsAttrOper.goods_price_formated}}</view>
							<view class="attr-price" v-else>{{goodsAttrOper.spec_price_formated}}</view>
							<view class="attr-stock">{{$t('lang.label_stock')}}<block v-if="goodsAttrOper.stock !== 'undefined'">{{ goodsAttrOper.stock }}</block><text class="uni-red" v-if="purchaseType == 'team'">({{$t('lang.gb_limited')}}:{{goodsInfo.astrict_num}})</text></view>
							<view class="attr-other">{{$t('lang.label_selected')}}<block v-if="goodsAttrOper.attr_name">{{ goodsAttrOper.attr_name }}</block> {{ num }}{{$t('lang.jian')}}</view>
						</block>
						<block v-else>
							<view class="attr-name twolist-hidden">{{goodsInfo.goods_name}}</view>
							<view class="attr-price" v-if="purchaseType == 'team'">{{goodsAttrOper.goods_price_formated}}</view>
							<view class="attr-price" v-else>{{goodsAttrOper.spec_price_formated}}</view>
							<view class="attr-stock">{{$t('lang.label_stock')}}<block v-if="goodsAttrOper.stock !== 'undefined'">{{ goodsAttrOper.stock }}</block><text class="uni-red" v-if="purchaseType == 'team'">({{$t('lang.gb_limited')}}:{{goodsInfo.astrict_num}})</text></view>
						</block>
					</view>
				</view>
				<view class="attr-goods-content">
					<view class="sku-item" v-for="(item,index) in attr" :key="index">
						<text class="sku-tit">{{ item.attr_name }}</text>
						<view class="sku-list"><text v-for="(option,listIndex) in item.attr_key" :key="listIndex" :class="{'active':goodsAttrInit[index] == option.goods_attr_id}" @click="attrChange(index,option.goods_attr_id)">{{ option.attr_value }}</text></view>
					</view>
				</view>
				<view class="attr-goods-number">
					<text class="tit">{{$t('lang.number')}}</text>
					<view class="stepper"><uni-number-box :value="num" :min="1" :max="goodsInfo.astrict_num" @change="bindChange"></uni-number-box></view>
				</view>
				<view class="btn-bar">
					<block v-if="purchaseType == 'team'">
						<view class="btn btn-red" @click="onTeamClicked" v-if="stock">{{$t('lang.confirm')}}</view>
						<view class="btn btn-disabled" v-else>{{$t('lang.understock')}}</view>
					</block>
					<block v-else>
						<block v-if="stock">
							<view class="btn btn-org" @click="onBuyClicked">{{$t('lang.button_buy')}}</view>
							<view class="btn btn-red" @click="onAddCartClicked(0)">{{$t('lang.add_cart')}}</view>
						</block>
						<block v-else>
							<view class="btn btn-disabled">{{$t('lang.understock')}}</view>
						</block>
					</block>
				</view>
			</view>
		</uni-popup>
		
		<!--小程序分享-->
		<view class="show-popup-shareImg">
			<uni-popup :show="shareImgShow" type="bottom" animation="true" v-on:hidePopup="shareImgShow = false">
				<view class="mp-share-warp">
					<view class="title">
						<text>{{$t('lang.save_xaingce')}}</text>
						<uni-icon type="closeempty" size="30" color="#8f8f94" @click="shareImgShow = false"></uni-icon>
					</view>
					<view class="mp-share-img"><image :src="mpShareImg" mode="heightFix" class="img" @tap="previewImage"></image></view>
					<view class="btn-bar btn-bar-radius"><button class="btn btn-red" @click="downloadImg">{{$t('lang.save_picture')}}</button></view>
				</view>
			</uni-popup>
		</view>
		
		<!--自定义分享-->
		<uni-popups id="popupPoster" ref="popupPoster" :animation="true" type="bottom">
			<view class="popup-poster">
				<view class="poster-image"><image :src="mpShareImg" mode="widthFix" class="img"></image></view>
				<view class="poster-btn">
					<view class="tit">{{$t('lang.share_to')}}</view>
					<view class="lists">
						<!-- #ifdef MP-WEIXIN -->
						<button class="list" open-type="share">
							<image src="@/static/sharemenu/weix.png" mode="widthFix"></image>
							<text>{{ $t('lang.share_with_friends') }}</text>
						</button>
						<!-- #endif -->
						<!-- #ifdef APP-PLUS -->
						<view class="list" @click="posterAppShare('weixin')">
							<image src="@/static/sharemenu/weix.png" mode="widthFix"></image>
							<text>{{ $t('lang.share_with_friends') }}</text>
						</view>
						<view class="list" @click="posterAppShare('pyq')">
							<image src="@/static/sharemenu/pengy.png" mode="widthFix"></image>
							<text>{{ $t('lang.generate_sharing_poster') }}</text>
						</view>
						<!-- #endif -->
						<view class="list" @click="downloadImg">
							<image src="@/static/sharemenu/baocun.png" mode="widthFix"></image>
							<text>{{ $t('lang.save_picture') }}</text>
						</view>
					</view>
					<view class="cancel" @click="popupPosterCancel">{{$t('lang.cancel')}}</view>
				</view>
			</view>
		</uni-popups>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	
	import uniSegmentedControl from '@/components/uni-segmented-control.vue';
	import uniNumberBox from '@/components/uni-number-box.vue';
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniTag from "@/components/uni-tag.vue";
	import uniPopup from '@/components/uni-popup.vue';
	import uniPopups from '@/components/uni-popup/uni-popup.vue';
	import uniCountdown from "@/components/uni-countdown.vue";
	
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import universal from '@/common/mixins/universal.js';
	import goodsNavBar from '@/components/goods-nav-bar/goods-nav-bar.vue';
	import dscCopyright from '@/components/dsc-copyright/dsc-copyright.vue';
	
	export default {
		mixins:[universal],
		data() {
			return {
				winHeight: 0,
				tabs: [this.$t('lang.goods_detail_info'), this.$t('lang.specification_parameter')],
				poster:false,
				goods_id:'',
				team_id:'',
				parent_id:'',
				onceTeamType:false,
				steps:[
					{title: this.$t('lang.team_rule_tit_1')},
					{title: this.$t('lang.team_rule_tit_2')},
					{title: this.$t('lang.team_rule_tit_3')},
					{title: this.$t('lang.team_rule_tit_4')}
				],
				showBase:false,
				ruleShow:false,
				current: 0,
				num:1,
				addCartClass:false,
				purchaseType:'',
				dscLoading:true,
				shareImgShow:false,
				mpShareImg: '',
				rgba: 'rgba(0,0,0,0)',
				navIconRgba: 'rgba(0,0,0,0.4)',
				navOpacity: 0,
				//微信小程序客服
				wxappChat:uni.getStorageSync("configData").wxapp_chat || 0
			}
		},
		components: {
			uniSegmentedControl,
			uniIcons,
			uniTag,
			uniPopup,
			uniPopups,
			uniNumberBox,
			uniCountdown,
			dscNotContent,
			dscCommonNav,
			goodsNavBar,
			dscCopyright
		},
		onPageScroll(e) {
			// 导航栏背景渐变
			const ratio = (e.scrollTop / uni.upx2px(375)).toFixed(1);
			if (ratio >= 1) {
				this.rgba = 'rgba(251,251,251,1)';
				this.navIconRgba = 'rgba(251,251,251,1)';
				this.navOpacity = 1;
			} else if (ratio > 0) {
				this.rgba = `rgba(251,251,251,${ratio})`;
				if (ratio > 0.5) {
					this.navIconRgba = `rgba(0,0,0,${1 - ratio})`;
				};
				this.navOpacity = ratio;
			} else {
				this.rgba = 'rgba(0,0,0,0)';
				this.navIconRgba = 'rgba(0,0,0,0.4)';
				this.navOpacity = 0;
			}
		},
		onShareAppMessage(res){
			return {
			  title: this.goodsInfo.goods_name,
			  path: '/pagesA/team/detail/detail?goods_id='+this.goods_id + '&team_id='+this.team_id
			}
		},
		//用户点击右上角分享朋友圈
		onShareTimeline: function() {
			return {
		      title: this.goodsInfo.goods_name,
		      query: {
		        key: "goods_id="+this.goods_id + '&team_id='+this.team_id
		      },
			   imageUrl: this.goodsInfo.goods_thumb
			  
		    }
		},
		onNavigationBarButtonTap(e){
			if(e.type == 'share'){
				let shareInfo = {
					href:this.$websiteUrl + 'team/detail?goods_id=' + this.goods_id + '&team_id=' + this.team_id + '&platform=APP',
					title:this.goodsInfo.goods_name,
					summary:this.goodsInfo.goods_brief,
					imageUrl:this.goodsInfo.goods_thumb
				};
				this.shareInfo(shareInfo)
			}
		},
		computed: {
			...mapState({
				teamDetailData: state => state.team.teamDetailData
			}),
			goodsInfo(){
				return this.teamDetailData.goods_info
			},
			gallerylList(){
				return this.teamDetailData.goods_img ? this.teamDetailData.goods_img : []
			},
			attr(){
				return this.teamDetailData.goods_properties    //商品属性 
			},
			goodsAttrInit(){
				return this.$store.state.team.goodsAttrInit    //商品属性id 
			},
			goodsAttrOper(){
				return this.$store.state.team.goodsAttrOper
			},
			stock() {
				return this.attr != '' ? this.goodsAttrOper.stock : this.goodsInfo.goods_number
			},
			dateTime(){
				let arr = []
				if(this.teamDetailData.team_log){
					this.teamDetailData.team_log.forEach(res=>{
						arr.push(this.$formatDateTime(res.end_time));
					})
				}
				return arr;
			},
			goodsDesc(){
				let result = ''
				if(this.goodsInfo.desc_mobile != ''){
					result = this.goodsInfo.desc_mobile
				}else{
					result = this.goodsInfo.goods_desc;
				}
				
				const reg = /style\s*=(['\"\s]?)[^'\"]*?\1/gi;
				const regex = new RegExp('<img', 'gi');
				const regex2 = new RegExp('<p', 'gi');
				const regex3 = new RegExp('<div class="tools"', 'gi');
				
				if(result){
					result = result.replace(reg, '');
					result = result.replace(regex, '<img style="width: 100% !important; height:auto;vertical-align:top;"');
					result = result.replace(regex3, '<div class="tools" style="display:none;"');
				}
				return result
			},
			//此商品在购物车中数量
			cart_number: {
			    get() {
			        return this.goodsInfo.cart_number
			    },
			    set(val) {
			        this.goodsInfo.cart_number = val
			    }
			},
			goodsCollectStatue() {
			    return this.$store.state.user.goodsCollectStatue
			},
			is_collect: {
			    get() {
			        return this.goodsInfo.is_collect
			    },
			    set(val) {
			        this.goodsInfo.is_collect = val
			    }
			},
        },
		onLoad(e){
			//#ifdef MP-WEIXIN
			uni.showShareMenu({
			  withShareTicket: true,
			  menus: ['shareAppMessage', 'shareTimeline']
			})
			//#endif
			
			let that = this;
			
			that.goods_id = e.goods_id;
			that.team_id = e.team_id ? e.team_id : 0;
			that.parent_id = e.parent_id ? e.parent_id : 0;
			
			//小程序扫码
			if (e.scene) {
				let scene = decodeURIComponent(e.scene);
				let fristParameter = scene.split('_')[0];
				let lastParameter = scene.split('_')[1];
				
				// 判断分割第一个参数是否有 "."
				that.goods_id = fristParameter.indexOf('.') > 0 ? fristParameter.split('.')[0] : fristParameter;
				that.team_id = fristParameter.indexOf('.') > 0 ? fristParameter.split('.')[1] : 0;
				
				if(lastParameter){
					uni.setStorageSync('parent_id',lastParameter);
				}
			}
			
			that.loadGoodsInfo();
			
			let difHeight = 105
			if(uni.getSystemInfoSync().model == 'Redmi Note 7'){
				difHeight = 26
			}
			that.winHeight = uni.getSystemInfoSync().windowHeight - difHeight;
		},
		onShow(){
			let that = this;
			if(that.parent_id > 0){
				uni.setStorage({
					key:'parent_id',
					data:that.parent_id
				})
			}
		},
		watch:{
			teamDetailData(){
				this.dscLoading = false
				
				if(this.teamDetailData.error == 1){
					uni.showModal({
						content: this.teamDetailData.msg,
						showCancel: false,
						success:function(res){
							if (res.confirm) {
								uni.reLaunch({
									url:'../team'
								})
							}
						}
					});
				}
			},
			goodsCollectStatue() {
			    this.goodsCollectStatue.forEach((v) => {
			        if (v.id == this.goods_id) {
			            this.is_collect = v.status
			        }
			    })
			},
			goodsAttrInit(){
				this.changeAttr();
			},
			sharePoster() {
				if (this.sharePoster) {
					this.$refs.popupPoster.open();
				}
			}
		},
		methods: {
			loadGoodsInfo(){
				this.$store.dispatch('setTeamDetail',{
					goods_id: this.goods_id,
					team_id: this.team_id
				})
			},
			onTeamSku(type){
				this.showBase = true;
				this.purchaseType = type;
				this.changeAttr();
			},
			onceTeam(){
				this.showBase = true;
				this.onceTeamType = true;
				this.changeAttr();
			},
			changeAttr(){
				this.$store.dispatch('setTeamProperty',{
					goods_id: this.goods_id,
					num: this.num,
					attr_id:this.goodsAttrInit,
					warehouse_id: 0,
					area_id: 0
				})
			},
			//属性切换
			attrChange(index,id){
				this.goodsAttrInit.splice(index,1,id)
			},
			async onAddCartClicked(type){
				this.addCartClass = false
				let newAttr = []

				if (this.attr.length > 0) {
					newAttr = this.goodsAttrInit
				}
				
				//判断是否绑定手机号
				if(!uni.getStorageSync('bindMobilePhone')){
					let roles = await this.$store.dispatch('setUserId',{type:true});
					if(!roles.data.mobile_phone){
						uni.showModal({
							content: this.$t('lang.is_user_bind_mobile_phone'),
							success: res => {
								if (res.confirm) {
									uni.navigateTo({
										url: '/pagesB/accountsafe/bindphone/bindphone?delta=1'
									});
								}
							}
						});
						
						return false
					}
				}
				
				this.$store.dispatch('setAddCart',{
					goods_id:this.goods_id,
					num:this.num,
					spec:newAttr,
					warehouse_id:'0',
					area_id:'0',
					parent_id:'0',
					rec_type:type
				}).then(res => {
					if (res.data == true) {
					    if (type == 10) {
							uni.navigateTo({
								url:'/pages/checkout/checkout?rec_type='+type
							});
					    } else {
					        this.addCartClass = true
							
							uni.showToast({
								title: this.$t('lang.added_to_cart'),
								icon:'success',
								duration:1000
							})
							
					        this.cart_number = parseInt(this.cart_number) + this.num
					    }
					} else {
						uni.showToast({
							title:res.data.msg,
							icon:'none',
							duration:1000
						})
					}
				})
			},
			onBuyClicked(){
				//立即购买
				this.onAddCartClicked(10)
			},
			//确定购买
			onTeamClicked() {
				uni.showLoading({ title: this.$t('lang.go_checkout'), mask:true })
				let newAttr = []
				
				if (this.attr.length > 0) {
					newAttr = this.goodsAttrInit
				}
				
				let team_id = this.onceTeamType === false ? this.team_id : 0
				this.$store.dispatch('setTeamBuy',{
					goods_id:this.goods_id,
					team_id:team_id,
					t_id:this.goodsInfo.id,
					num:this.num,
					attr_id:newAttr
				}).then(res =>{
					if(res.status == 'success'){
						let data = res.data
						if(data.error == 0){
							uni.navigateTo({
								url:'/pages/checkout/checkout?rec_type=' + data.rec_type + '&type_id=' + data.t_id + '&team_id=' + data.team_id,
								complete: () => {
									uni.hideLoading();
								}
							});
						}else{
							uni.hideLoading();
							uni.showToast({
								title:data.msg,
								icon:'none',
							})
						}
					}
				})
			},
			onTeamCheckout(id){
			  this.team_id = id
			  this.onTeamSku('team')
			},
			bindChange(e){
				this.num = e
			},
			onCart(){
				uni.switchTab({
					url: '/pages/cart/cart'
				});
			},
			//banner滚动
			swiperChange(e){
				this.poster = false
			},
			//视频自动播放
			onPlay(){
				this.poster = true
			},
			//视频播放错误
			videoErrorCallback(e) {
				uni.showModal({
					content: e.target.errMsg,
					showCancel: false
				})
			},
			handleRule(){
				this.ruleShow = true
			},
			//关闭Popup
			handleClose(val){
				if(val == 'rule'){
			        this.ruleShow = false
			    }else if(val == 'base'){
					this.showBase = false
				}
			},
			onClickItem(index) {
				if (this.current !== index) {
					this.current = index;
				}
			},
			//收藏
			collection(){
				if(this.$isLogin()){
					this.$store.dispatch('setCollectGoods', {
						goods_id: this.goods_id,
						status: this.is_collect
					})
				}else{
					uni.showModal({
						content: this.$t('lang.fill_in_user_collect_goods'),
						success:(res)=>{
							if(res.confirm){
								uni.navigateTo({
									url: '/pagesB/login/login?delta=1'
								});
							}
						}
					})
				}
			},
			// 分享
			appShare() {
				let shareInfo = {
					href: `${this.$websiteUrl}team/detail?goods_id=${this.goods_id}&team_id=${this.team_id}&parent_id=${uni.getStorageSync("user_id")}&platform=APP`,
					title: this.goodsInfo.goods_name,
					summary: this.goodsInfo.goods_brief,
					imageUrl: this.goodsInfo.goods_thumb
				};

				this.shareInfo(shareInfo, 'poster');
			},
			mpShare() {
				this.onGoodsShare();
			},
			onGoodsShare() {
				if (this.$isLogin()) {
					uni.showLoading({ title: this.$t('lang.loading') });
					let price = this.goodsInfo.team_price;
					let o = {}
					
					// #ifdef MP-WEIXIN
					o = {
						goods_id: this.goodsInfo.goods_id,
						ru_id: 0,
						price: price,
						share_type: 0,
						type: 0,
						platform: 'MP-WEIXIN',
						extension_code:'team',
						code_url:'pagesA/team/detail/detail',
						scene:`${this.goods_id}.${this.team_id}`,
						thumb:this.gallerylList[0] || this.goodsInfo.goods_img,
					}
					// #endif
					
					// #ifdef APP-PLUS
					o = {
						goods_id: this.goodsInfo.goods_id,
						price: price,
						share_type: 0,
						platform: 'APP',
						extension_code:'team',
						code_url:`${this.$websiteUrl}team/detail?goods_id=${this.goods_id}&team_id=${this.team_id}`,
						thumb:this.gallerylList[0] || this.goodsInfo.goods_img,
					}
					// #endif
			
					this.$store
						.dispatch('setGoodsShare', o)
						.then(res => {
							if (res.status == 'success') {
								this.mpShareImg = res.data;
			
								// #ifdef MP-WEIXIN
								this.shareImgShow = true;
								// #endif
			
								// #ifdef APP-PLUS
								this.appShare();
								// #endif
			
								uni.hideLoading();
							}
						});
				} else {
					uni.showModal({
						content: this.$t('lang.login_user_not'),
						success: res => {
							if (res.confirm) {
								uni.navigateTo({
									url: '/pagesB/login/login?delta=1'
								});
							}
						}
					});
				}
			
				// #ifdef APP-PLUS
				this.sharePoster = false;
				// #endif
			},
			popupPosterCancel() {
				this.$refs.popupPoster.close();
				// #ifdef APP-PLUS
				this.sharePoster = false;
				// #endif
			},
			previewImage() {
				let that = this;
				let arr = [];
				arr.push(that.mpShareImg);
				uni.previewImage({
					current: 1,
					urls: arr,
					indicator: 'number',
					longPressActions: {
						itemList: [this.$t('lang.send_to_friend'), this.$t('lang.save_picture'), this.$t('lang.collect')],
						success: function(data) {
							console.log('选中了第' + (data.tapIndex + 1) + '个按钮,第' + (data.index + 1) + '张图片');
						},
						fail: function(err) {
							console.log(err.errMsg);
						}
					}
				});
			},
			downloadImg() {
				var that = this;
				uni.downloadFile({
					url: that.mpShareImg,
					success: res => {
						uni.saveImageToPhotosAlbum({
							filePath: res.tempFilePath,
							success: function() {
								uni.showToast({
									title: that.$t('lang.picture_saved_success'),
									icon: 'none',
									duration: 1000,
									success: () => {
										that.$refs.popupPoster.close();
										that.sharePoster = false;
									}
								});
							}
						});
					}
				});
			},
			posterAppShare(type) {
				let that = this;
				let scene = type == 'weixin' ? 'WXSceneSession' : 'WXSenceTimeline';
				uni.share({
					provider: 'weixin',
					scene: scene,
					type: 2,
					imageUrl: that.mpShareImg
				});
			
				that.$refs.popupPoster.close();
				that.sharePoster = false;
			},
		}
	}
</script>

<style scoped>
	.fixed-bottom-padding {
		padding: 0;
	}
	.dsc-safe-area-inset-bottom {
	  height: 100rpx;
	  padding-bottom: 0;  
	  padding-bottom: constant(safe-area-inset-bottom);  
	  padding-bottom: env(safe-area-inset-bottom);  
	}
.cont-box .title .goods-price{ padding: 0;}

.goods_outer{ padding: 0; margin-top: 10upx;}

.uni-list-cell-navigate{ justify-content: flex-start; }
.uni-list-cell-navigate .title{ min-width: 100upx; color: #999999; margin-right: 15upx;}
.uni-list-cell .iconfont{ color: #f92028; margin-right: 10upx;}
.uni-list-cell-title .uni-list-cell-navigate{ flex-direction: column; }
.uni-list-cell-title .uni-list-cell-navigate.uni-navigate-right:after{ top: 35%;}
.uni-list-cell-title .uni-list-cell-navigate .title{ width: 100%; color: #333333; font-size: 30upx; margin-bottom: 20upx;}
.uni-list-cell-title .uni-list-cell-navigate .value{ display: flex; align-items: center; color: #999999;}

.steps{ display: flex; flex-direction: row; width: 100%; justify-content: center; align-items: center;}
.steps .item{ position: relative; width: 25%; color: #777; text-align: center; margin: 20upx 0;}
.steps .item .num{ position: relative; width: 40upx; height: 40upx; line-height: 40upx; font-size: 24upx; border-radius: 50%; border:1px solid #e7e8ef; margin: 0 auto; z-index: 2; background: #FFFFFF;}
.steps .item .tit{ font-size: 24upx; margin-top: 20upx;}
.steps .item .n-list-xian{ position: absolute; border-top: 1px solid #e7e8ef; height: 1px; z-index: 1; width: 100%; top: 22%;}
.steps .item:first-child .n-list-xian{ left: 50%; width: 60%; }
.steps .item:last-child .n-list-xian{ right: 50%; width: 60%; }

.rule{ padding: 20upx; border-top: 1px solid #e7e8ef; }
.rule view{ text-align: left; font-size: 25upx; color: #999;}

.scroll-view { white-space: nowrap;width: 100%; }
.scroll-view-product{ overflow: hidden; }
.scroll-view-product .scroll-view-item{ width:238upx; line-height: 1.8; padding-bottom: 15upx; display: inline-block; margin-left: -1px; background-color: #FFFFFF;}
.scroll-view-product .scroll-view-item:first-child{ margin-left: 0;}
.scroll-view-product .scroll-view-item image{ width: 238upx; height: 238upx;}
.scroll-view-product .scroll-view-item .name{ width: 100%; font-size:25upx; display: block; padding:0 10upx; box-sizing: border-box;}
.scroll-view-product .scroll-view-item .price{ display: flex; align-items: center; padding:0 10upx;}
.scroll-view-product .scroll-view-item .price-original{ color: #fd0225; font-size:25upx;}
.scroll-view-product .scroll-view-item .price-favour{ color: #888888; text-decoration: line-through; font-size:20upx; margin-left: 10upx;}

.btn-goods-action .btn-bar .btn{ line-height: normal; }
.btn-goods-action .btn-bar .btn text{ display: block;}

.attr-goods-box{ position: relative; }
.attr-goods-box .attr-goods-header{ padding: 20upx; }
.attr-goods-box .attr-goods-header .attr-img{ position: absolute; box-shadow: 2px 2px 15px rgba(46,58,76,.17)!important; width: 200upx; height: 200upx; border-radius: 10upx; top: -40upx; overflow: hidden;}
.attr-goods-box .attr-goods-header .attr-img image{ width: 100%;}
.attr-goods-box .attr-goods-header .attr-info{ margin-left: 220upx; text-align: left;}
.attr-goods-box .attr-goods-header .attr-info .attr-price{ font-size: 35upx; color: #f92028; height: 60upx;}
.attr-goods-box .attr-goods-header .attr-info .attr-stock{ color: #888; font-size: 26upx;}
.attr-goods-box .attr-goods-header .attr-info .attr-other{ color: #888; font-size: 26upx;}
.attr-goods-box .attr-goods-content{ min-height: 240upx; max-height: 480upx; overflow-y: auto; padding: 0 20upx; text-align: left;}
.attr-goods-box .attr-goods-content .sku-item{ padding-bottom: 20upx;}
.attr-goods-box .attr-goods-content .sku-item .sku-tit{ color: #888888;}
.attr-goods-box .attr-goods-content .sku-item .sku-list{ display: flex; flex-direction: row; justify-content: flex-start; flex-wrap: wrap;}
.attr-goods-box .attr-goods-content .sku-item .sku-list text{ padding: 5upx 30upx; border:1px solid #e6e6e6; background: #FFFFFF; margin: 10upx 20upx 10upx 0; font-size: 26upx; color: #333; border-radius: 8upx;}
.attr-goods-box .attr-goods-content .sku-item .sku-list text.active{ border-color: #f92028; color: #f92028;}
.attr-goods-box .attr-goods-number{ display: flex; flex-direction: row; justify-content: space-between; align-items: center; padding:20upx 20upx 30upx 20upx;}
.attr-goods-box .attr-goods-number .tit{ width: 200upx; text-align: left; color: #888;}
.attr-goods-box .attr-goods-number .stepper{ flex: 1 1 0%; display: flex; justify-content: flex-end;}

.team-log-box{ padding: 20upx; background: #FFFFFF;}
.team-log-box .title{ color: #999999; font-size: 30upx; }
.team-list-box{ display: flex; flex-direction: row; margin-top: 10upx; border: 1px solid #f92028; border-radius: 50upx;}
.team-list-box .left{ width: 100upx; height: 100upx; border-radius: 100%; overflow: hidden;}
.team-list-box .left image{ width: 100%;}
.team-list-box .con{ flex: 1 1 0%; margin: 0 10upx;}
.team-list-box .con .header{ display: flex; flex-direction: row; justify-content: space-between;}
.team-list-box .con .time{ color: #999999; font-size: 25upx; display: flex; flex-direction: row;}
.team-list-box .right{ line-height: 100upx; background: #f92028; padding: 0 20upx; text-align: center; color: #FFFFFF; font-size: 30upx; border-radius: 0 50upx 50upx 0; overflow: hidden; margin-right: -1px;}

/* 小程序分享  start*/
.show-popup-shareImg /deep/ .uni-popup-bottom{ height: 80%; }
/* 小程序分享 end*/
</style>
