<template>
	<view class="container fixed-bottom-padding">
		<goods-nav-bar :navIconRgba="navIconRgba" :navOpacity="navOpacity" :rgba="rgba"></goods-nav-bar>
		<scroll-view class="scrollList" scroll-y>
			<view class="goods-wrap">
				<view class="banner">
					<swiper indicator-dots="true" @change="swiperChange">
						<swiper-item v-if="goodsInfo.goods_video">
							<view class="goods-video" v-if="poster">
								<video :src="goodsInfo.goods_video" @error="videoErrorCallback" controls
									show-center-play-btn="true" autoplay="true"></video>
							</view>
							<view class="goods-img-first" v-else>
								<image :src="gallerylList[0]" class="imgbox" @click="onPlay" v-if="gallerylList">
								</image>
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
							<swiper-item>
								<image :src="goodsInfo.goods_img" class="imgbox"></image>
							</swiper-item>
						</block>
					</swiper>
					<view class="bargain-time">
						<text>{{$t('lang.upgrade_beford')}}</text>
						<view class="data">
							<block v-if="isTimeEnd">
								<uni-countdown fontColor="#FFFFFF" splitorColor="#FFFFFF" borderColor="#f23157"
									bgrColor="#f23157" :timer="dateTime" v-if="dateTime"></uni-countdown>
							</block>
							<block v-else>
								<text class="end">{{$t('lang.activity_end')}}</text>
							</block>
						</view>
					</view>
				</view>
				<view class="cont-box">
					<view class="title">
						<view class="uni-flex-common uni-space-between">
							<view class="goods-name twolist-hidden flex_1">
								<uni-tag text="砍" size="small" type="error"></uni-tag>
								<text>{{goodsInfo.goods_name}}</text>
							</view>
							<view class="goods_share" @click="mpShare">
								<text class="iconfont icon-share" style="line-height: 1;"></text>
								<text class="share_txt">{{ $t('lang.share') }}</text>
							</view>
						</view>
						<view class="goods-price">
							<block v-if="bargainDetailData.bargain_info.final_price">
								<text>{{$t('lang.label_bargain_end')}}</text>
								<text class="price-original">{{ bargainDetailData.bargain_info.final_price }}</text>
							</block>
							<block v-else>
								<text>{{$t('lang.original_price')}}：</text>
								<text class="price-original">{{ goodsInfo.goods_price }}</text>
							</block>
						</view>
						<view class="bargain-progressbar">
							<view class="plan-left plan-common"></view>
							<view class="wrap" style="border-radius: 0">
								<view class="bar" :style="{width:goodsInfo.bargain_bar + '%'}">
									<view class="color"></view>
								</view>
							</view>
							<view class="plan-right plan-common"></view>
						</view>
						<view class="goods_outer">
							<view class="text-left" v-if="goodsInfo.target_price">
								{{$t('lang.base_price')}}{{ goodsInfo.target_price }}</view>
							<view class="text-right" v-if="goodsInfo.shop_price">
								{{$t('lang.original_price')}}{{ goodsInfo.shop_price }}</view>
						</view>
						<view class="bargain-tip">
							<view class="join">{{$t('lang.already_have')}}<text class="number"
									v-if="goodsInfo.total_num">{{goodsInfo.total_num}}</text>{{$t('lang.bargain_propmt_1')}}
							</view>
							<view class="triangle"></view>
							<view class="order">
								<swiper autoplay="true" circular="true" interval="5000">
									<swiper-item v-for="(item, index) in bargainDetailData.bargain_list" :key="index">
										<view class="cont">
											<text class="uni-red">{{ item.user_name }}</text>
											{{$t('lang.bargain_propmt_2')}} <text
												class="uni-red">{{ item.subtract_price }}</text>
										</view>
									</swiper-item>
								</swiper>
							</view>
						</view>
					</view>
				</view>
				<!--砍完价后的列表-->
				<view class="bargian-user" v-if="bargainJoin">
					<view class="left">
						<image v-if="bargainDetailData.bargain_info.user_picture"
							:src="bargainDetailData.bargain_info.user_picture" mode="widthFix"></image>
						<image v-else :src="imagePath.userDefaultImg" mode="widthFix"></image>
					</view>
					<view class="right">
						<view class="tit">{{bargainDetailData.bargain_info.user_name}}<text
								v-if="bargainDetailData.bargain_info.rank">{{$t('lang.label_cur_bargain_rank')}}{{bargainDetailData.bargain_info.rank}}</text>
						</view>
						<view class="subtit">
							<block v-if="addBargain == 1">{{$t('lang.bargain_propmt_3')}}</block>
							<block v-else>{{$t('lang.bargain_propmt_4')}}</block>
							<text class="uni-red"
								v-if="bargainDetailData.bargain_info.subtract_price">{{bargainDetailData.bargain_info.subtract_price}}{{$t('lang.yuan')}}</text>
						</view>
					</view>
				</view>
				<!--活动规则-->
				<view class="uni-card uni-card-not">
					<view class="uni-list">
						<view class="uni-list-cell uni-list-cell-title" @click="handleRule">
							<view class="uni-list-cell-navigate">
								<text class="title">{{$t('lang.bargaining_rules')}}</text>
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
				<!--亲友帮-->
				<view class="qinyou">
					<view class="uni-common-mt">
						<uni-segmented-control :current="rankCurrent" :values="rankTabs" v-on:clickItem="onRankItem"
							styleType="text" activeColor="#f92028"></uni-segmented-control>
					</view>
					<view class="qinyou-cont">
						<block v-if="rankCurrent === 0">
							<block v-if="bargainDetailData.bargain_ranking != ''">
								<view class="item" v-for="(item, index) in bargainDetailData.bargain_ranking"
									:key="index" v-if="index < lengthNum">
									<view class="icon">
										<image src="../../../static/rank-1.png" mode="widthFix" v-if="item.rank===1">
										</image>
										<image src="../../../static/rank-2.png" mode="widthFix" v-if="item.rank===2">
										</image>
										<image src="../../../static/rank-3.png" mode="widthFix" v-if="item.rank===3">
										</image>
									</view>
									<view class="left">
										<image :src="item.user_picture" class="img" v-if="item.user_picture"></image>
										<image :src="imagePath.userDefaultImg" class="img" v-else></image>
									</view>
									<view class="con">{{item.user_name}}</view>
									<view class="right">{{$t('lang.chop_off')}}<text
											class="uni-red">{{item.money}}</text>{{$t('lang.yuan')}}</view>
								</view>
								<view class="showMore" @click="showMore">{{ lengthMore }}</view>
							</block>
							<block v-else>
								<dsc-not-content></dsc-not-content>
							</block>
						</block>
						<block v-if="rankCurrent === 1">
							<block v-if="bargainDetailData.bargain_list != ''">
								<view class="item" v-for="(item, index) in bargainDetailData.bargain_list" :key="index">
									<view class="left">
										<image :src="item.user_picture" class="img" v-if="item.user_picture"></image>
										<image :src="imagePath.userDefaultImg" class="img" v-else></image>
									</view>
									<view class="con">
										<text>{{item.user_name}}</text>
										<text>{{item.add_time}}</text>
									</view>
									<view class="right">{{$t('lang.chop_off')}}<text
											class="uni-red">{{item.subtract_price}}</text>{{$t('lang.yuan')}}</view>
								</view>
							</block>
							<block v-else>
								<dsc-not-content></dsc-not-content>
							</block>
						</block>
					</view>
				</view>
				<view class="uni-card uni-card-not"
					v-if="bargainDetailData.new_goods && bargainDetailData.new_goods.length > 0">
					<view class="uni-list">
						<view class="uni-list-cell uni-list-cell-title">
							<view class="uni-list-cell-navigate">
								<text class="title">{{$t('lang.team_rec')}}</text>
								<scroll-view class="scroll-view scroll-view-product" scroll-x="true" scroll-left="0">
									<view class="scroll-view-item" v-for="(item,index) in bargainDetailData.new_goods"
										:key="index">
										<image :src="item.goods_thumb" mode="widthFix"></image>
										<text class="name uni-ellipsis">{{ item.goods_name }}</text>
										<view class="price uni-flex">
											<view class="price-original">{{ item.team_price }}</view>
										</view>
									</view>
								</scroll-view>
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class="goods-desc">
				<view class="uni-common-mt">
					<uni-segmented-control :current="current" :values="tabs" v-on:clickItem="onClickItem"
						styleType="text" activeColor="#f92028"></uni-segmented-control>
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
									<view class="td">{{$t('lang.label_brand')}}</view>
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
			<view class="btn-bar">
				<block v-if="goodsInfo.bs_id > 0">
					<block v-if="addBargain == 1">
						<block v-if="bargainJoin == 1">
							<!-- #ifdef MP-WEIXIN -->
							<button class="btn btn-org" :disabled="disabled" :data-id="goodsInfo.bs_id"
								open-type="share">{{$t('lang.bargain_text_1')}}</button>
							<!-- #endif -->
							<!-- #ifdef APP-PLUS -->
							<button class="btn btn-org" :disabled="disabled" :data-id="goodsInfo.bs_id"
								@tap="appShare">{{$t('lang.bargain_text_1')}}</button>
							<!-- #endif -->
							<button class="btn btn-red" @click="onBuyClicked">{{$t('lang.drp_apply_btn_2')}}</button>
						</block>
						<block v-else>
							<button class="btn btn-red" :disabled="disabled"
								@click="bargainBid">{{$t('lang.bargain_text_2')}}</button>
						</block>
					</block>
					<block v-else>
						<button class="btn btn-org" :disabled="disabled" @click="cutDown"
							v-if="bargainJoin == 1">{{$t('lang.bargain_text_4')}}</button>
						<button class="btn btn-org" :disabled="disabled" @click="bargainBid"
							v-else>{{$t('lang.bargain_text_3')}}</button>

						<button class="btn btn-red" :disabled="disabled" @click="myBargain"
							v-if="goodsInfo.is_add_bargain == 1">{{$t('lang.my_activity')}}</button>
						<button class="btn btn-red" :disabled="disabled" @click="bargainLog"
							v-else>{{$t('lang.my_participation')}}</button>
					</block>
				</block>
				<block v-else>
					<view class="btn btn-red" @click="bargainLog">
						<text class="mt10">{{$t('lang.my_participation')}}</text>
					</view>
				</block>
			</view>
		</view>

		<dsc-common-nav>
			<navigator url="../bargain" class="nav-item" slot="right">
				<view class="iconfont icon-bargain"></view>
				<text>{{$t('lang.bargaining_channel')}}</text>
			</navigator>
		</dsc-common-nav>

		<dsc-loading :dscLoading="dscLoading"></dsc-loading>

		<!--拼团规则弹框-->
		<uni-popup :show="ruleShow" type="bottom" v-on:hidePopup="handleClose('rule')">
			<view class="title">
				<view class="txt">{{$t('lang.reles_detail')}}</view>
				<uni-icons type="closeempty" size="36" color="#999999" @click="handleClose('rule')"></uni-icons>
			</view>
			<view class="rule">
				<view>{{$t('lang.bargain_rele_propmt_1')}}</view>
				<view>{{$t('lang.bargain_rele_propmt_2')}}</view>
				<view>{{$t('lang.bargain_rele_propmt_3')}}</view>
			</view>
		</uni-popup>

		<uni-popup :show="bidShow" type="middle" v-on:hidePopup="handleClose('bid')">
			<view class="bargain-friends">
				<view class="tit">{{$t('lang.bargain_hint')}}</view>
				<view class="image">
					<image :src="imagePath.bargainsImg" mode="widthFix"></image>
				</view>
				<view class="cont">{{$t('lang.bargain_propmt_5')}}<text class="uni-red"
						v-if="bargainBidData.subtract_price">{{ currency_format }}{{bargainBidData.subtract_price}}</text>{{$t('lang.yuan')}}
				</view>
				<view class="footer" @click="confirmBid(bs_id)">{{$t('lang.i_see')}}</view>
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
						<view class="attr-name twolist-hidden">{{goodsInfo.goods_name}}</view>
						<view class="attr-price" v-if="goodsAttrOper.goods_price">
							<text>{{$t('lang.label_original_price')}}</text>{{goodsAttrOper.goods_price}}</view>
						<view class="attr-stock" v-if="goodsAttrOper.target_price">
							{{$t('lang.label_base_price')}}{{goodsAttrOper.target_price}}</view>
						<view class="attr-stock" v-if="goodsAttrOper.stock !== 'undefined'">
							{{$t('lang.label_stock')}}{{ goodsAttrOper.stock }}</view>
					</view>
				</view>
				<view class="attr-goods-content">
					<view class="sku-item" v-for="(item,index) in attr" :key="index">
						<text class="sku-tit">{{ item.attr_name }}</text>
						<view class="sku-list"><text v-for="(option,listIndex) in item.attr_key" :key="listIndex"
								:class="{'active':goodsAttrInit[index] == option.goods_attr_id}"
								@click="attrChange(index,option.goods_attr_id)">{{ option.attr_value }}</text></view>
					</view>
				</view>
				<view class="attr-goods-number">
					<text class="tit">{{$t('lang.number')}}</text>
					<view class="stepper">
						<uni-number-box :value="num" :min="1" :max="goodsInfo.astrict_num" :disabled="true">
						</uni-number-box>
					</view>
				</view>
				<view class="btn-bar">
					<view class="btn btn-red" @click="bargainLogCheckout" v-if="stock">{{$t('lang.confirm')}}</view>
					<view class="btn btn-disabled" v-else>{{$t('lang.understock')}}</view>
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
					<view class="mp-share-img">
						<image :src="mpShareImg" mode="heightFix" class="img" @tap="previewImage"></image>
					</view>
					<view class="btn-bar btn-bar-radius"><button class="btn btn-red"
							@click="downloadImg">{{$t('lang.save_picture')}}</button></view>
				</view>
			</uni-popup>
		</view>

		<!--自定义分享-->
		<uni-popups id="popupPoster" ref="popupPoster" :animation="true" type="bottom">
			<view class="popup-poster">
				<view class="poster-image">
					<image :src="mpShareImg" mode="widthFix" class="img"></image>
				</view>
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
	import {
		mapState
	} from 'vuex'

	import uniSegmentedControl from '@/components/uni-segmented-control.vue';
	import uniNumberBox from '@/components/uni-number-box.vue';
	import uniCountdown from "@/components/uni-countdown.vue";
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniTag from "@/components/uni-tag.vue";
	import uniPopup from '@/components/uni-popup.vue';
	import uniPopups from '@/components/uni-popup/uni-popup.vue';

	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import universal from '@/common/mixins/universal.js';
	import goodsNavBar from '@/components/goods-nav-bar/goods-nav-bar.vue';
	import dscCopyright from '@/components/dsc-copyright/dsc-copyright.vue';

	export default {
		mixins: [universal],
		data() {
			return {
				winHeight: 0,
				tabs: [this.$t('lang.goods_detail_info'), this.$t('lang.specification_parameter')],
				current: 0,
				rankTabs: [this.$t('lang.toprank'), this.$t('lang.friend_bang')],
				rankCurrent: 0,
				poster: false,
				id: '',
				bs_id: '',
				onceTeamType: false,
				steps: [{
						title: this.$t('lang.click_bargain')
					},
					{
						title: this.$t('lang.invite_help_bargain')
					},
					{
						title: this.$t('lang.bargain_base_price')
					},
					{
						title: this.$t('lang.place_order_buy')
					}
				],
				showBase: false,
				ruleShow: false,
				num: 1,
				addCartClass: false,
				dscLoading: true,
				bidShow: false,
				lengthNum: 10,
				isLengthShow: true,
				lengthMore: this.$t('lang.view_more'),
				disabled: false,
				shareImgShow: false,
				mpShareImg: '',
				rgba: 'rgba(0,0,0,0)',
				navIconRgba: 'rgba(0,0,0,0.4)',
				navOpacity: 0,
				currency_format: uni.getStorageSync('configData').currency_format || '¥',
				//微信小程序客服
				wxappChat:uni.getStorageSync("configData").wxapp_chat || 0,
				wx_template_id:'',
			}
		},
		components: {
			uniSegmentedControl,
			uniIcons,
			uniTag,
			uniPopup,
			uniPopups,
			uniCountdown,
			uniNumberBox,
			dscNotContent,
			dscCommonNav,
			goodsNavBar,
			dscCopyright
		},
		onShareAppMessage(res) {
			if (res.from === 'button') { // 来自页面内分享按钮
				return {
					title: this.$t('lang.please_give_me_knife'),
					path: '/pagesA/bargain/detail/detail?id=' + this.id + '&bs_id=' + res.target.dataset.id
				}
			} else {
				return {
					title: this.goodsInfo.goods_name,
					path: "/pagesA/bargain/detail/detail?id=" + this.id + '&bs_id=' + this.goodsInfo.bs_id
				}
			}
		},
		//用户点击右上角分享朋友圈
		onShareTimeline: function() {
			return {
				title: this.goodsInfo.goods_name,
				query: {
					key: 'id=' + this.id + '&bs_id=' + this.goodsInfo.bs_id
				},
				imageUrl: this.goodsInfo.goods_img
			}
		},
		onNavigationBarButtonTap(e) {
			if (e.type == 'share') {
				let shareInfo = {
					href: this.$websiteUrl + 'bargain/detail/' + this.id + '?bs_id=' + this.goodsInfo.bs_id +
						'&platform=APP',
					title: this.goodsInfo.goods_name,
					summary: this.goodsInfo.bargain_desc ? this.goodsInfo.bargain_desc : this.goodsInfo.goods_brief,
					imageUrl: this.goodsInfo.goods_thumb
				};
				this.shareInfo(shareInfo)
			}
		},
		computed: {
			...mapState({
				bargainDetailData: state => state.bargain.bargainDetailData
			}),
			goodsInfo() {
				return this.bargainDetailData.goods_info
			},
			gallerylList() {
				return this.bargainDetailData.goods_img ? this.bargainDetailData.goods_img : []
			},
			attr() {
				return this.bargainDetailData.goods_properties //商品属性
			},
			goodsAttrInit() {
				return this.$store.state.bargain.goodsAttrInit //商品属性id
			},
			goodsAttrOper() {
				return this.$store.state.bargain.goodsAttrOper
			},
			stock() {
				return this.attr != '' ? this.goodsAttrOper.stock : this.goodsInfo.goods_number
			},
			isTimeEnd() {
				return this.goodsInfo.end_time > this.goodsInfo.current_time ? true : false
			},
			dateTime() {
				let dataTime = this.goodsInfo.end_time

				if (dataTime != '') {
					return this.$formatDateTime(dataTime)
				}
			},
			goodsDesc() {
				let result = this.goodsInfo.goods_desc;
				const reg = /style\s*=(['\"\s]?)[^'\"]*?\1/gi;
				const regex = new RegExp('<img', 'gi');
				const regex2 = new RegExp('<p', 'gi');

				if (result) {
					result = result.replace(reg, '');
					result = result.replace(regex, '<img style="width: 100% !important; height:auto;vertical-align:top;"');
					result = result.replace(regex2, '<p style="margin:0;padding:0;"');
				}
				return result
			},
			addBargain: {
				get() {
					return this.$store.state.bargain.addBargain
				},
				set(val) {
					this.$store.state.bargain.addBargain = val
				}
			},
			bargainBidData: {
				get() {
					return this.$store.state.bargain.bargainBidData
				},
				set(val) {
					this.$store.state.bargain.bargainBidData = val
				}
			},
			bargainJoin: {
				get() {
					return this.$store.state.bargain.bargainJoin
				},
				set(val) {
					this.$store.state.bargain.bargainJoin = val
				}
			},
		},
		onLoad(e) {
			let that = this

			//#ifdef MP-WEIXIN
			uni.showShareMenu({
				withShareTicket: true,
				menus: ['shareAppMessage', 'shareTimeline']
			})
			//#endif

			that.id = e.id;
			that.bs_id = e.bs_id ? e.bs_id : 0;

			//小程序扫码
			if (e.scene) {
				let scene = decodeURIComponent(e.scene);
				let fristParameter = scene.split('_')[0];
				let lastParameter = scene.split('_')[1];

				// 判断分割第一个参数是否有 "."
				that.id = fristParameter.indexOf('.') > 0 ? fristParameter.split('.')[0] : fristParameter;

				if (lastParameter) {
					uni.setStorageSync('parent_id', lastParameter);
				}
			}

			let difHeight = 105
			if (uni.getSystemInfoSync().model == 'Redmi Note 7') {
				difHeight = 26
			}

			this.winHeight = uni.getSystemInfoSync().windowHeight - difHeight;
			
			// #ifdef MP-WEIXIN
			// 获取小程序订阅消息 砍价进度通知 模板id
			this.getTemplate('4875');
			// #endif
		},
		onShow() {
			this.goodsDetail(this.bs_id)
		},
		watch: {
			bargainDetailData() {
				this.dscLoading = false

				//活动结束
				this.disabled = this.goodsInfo.bargain_end == 1 ? true : false;
			},
			goodsAttrInit() {
				this.changeAttr();
			},
			sharePoster() {
				if (this.sharePoster) {
					this.$refs.popupPoster.open();
				}
			},
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
		methods: {
			goodsDetail(bsId) {
				this.$store.dispatch('setBargainDetail', {
					id: this.id,
					bs_id: bsId
				})
			},
			closeSku() {
				this.showBase = false;
				this.bargainBidData.bargain_join = ''
			},
			changeAttr() {
				this.$store.dispatch('setBargainProperty', {
					id: this.id,
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
				let newAttr = []

				if (this.attr.length > 0) {
					newAttr = this.goodsAttrInit
				}

				if (this.$isLogin()) {
					if (this.goodsAttrOper.stock > 0) {
						this.$store.dispatch('setBargainLog', {
							id: this.id,
							num: this.num,
							attr_id: newAttr,
							warehouse_id: 1,
							area_id: 1
						}).then(res => {
							uni.showToast({
								title: res.data.msg,
								icon: 'none'
							});
							this.showBase = false
							if (res.data.error == 0) {
								this.addBargain = res.data.add_bargain
								this.goodsDetail(res.data.bs_id)
							}
						})
					} else {
						uni.showToast({
							title: this.$t('lang.understock'),
							icon: 'none'
						});
					}
				} else {
					uni.showModal({
						content: this.$t('lang.not_login_bargain'),
						success: (res) => {
							if (res.confirm) {
								uni.navigateTo({
									url: '/pagesB/login/login?delta=1'
								})
							}
						}
					})
				}
			},
			//去砍一刀
			bargainBid() {
				let that = this;
				if (this.$isLogin()) {
					let tmplId = this.wx_template_id || '';
					
					if(tmplId){
						// 小程序订阅消息 弹窗
						uni.requestSubscribeMessage({
						  tmplIds: [tmplId],
						  async success (res) {
							console.log('success',JSON.stringify(res))
					
							that.$store.dispatch('setBargainBid', {
								id: that.goodsInfo.id,
								bs_id: that.goodsInfo.bs_id,
								platform: uni.getStorageSync('platform')
							}).then(res => {
								if (res.data.error == 0) {
									that.bargainBidData = res.data
									that.bargainJoin = res.data.bargain_join
									that.bidShow = true
								} else {
									uni.showToast({
										title: res.data.msg,
										icon: 'none'
									});
								}
							})
							
						  }
						});
					} else {
					
						this.$store.dispatch('setBargainBid', {
							id: this.goodsInfo.id,
							bs_id: this.goodsInfo.bs_id,
							platform: uni.getStorageSync('platform')
						}).then(res => {
							if (res.data.error == 0) {
								this.bargainBidData = res.data
								this.bargainJoin = res.data.bargain_join
								this.bidShow = true
							} else {
								uni.showToast({
									title: res.data.msg,
									icon: 'none'
								});
							}
						})
					}

					
				} else {
					uni.showModal({
						content: this.$t('lang.not_login_bang'),
						success: (res) => {
							if (res.confirm) {
								uni.navigateTo({
									url: '/pagesB/login/login?delta=1'
								})
							}
						}
					})
				}
			},
			//属性切换
			attrChange(index, id) {
				this.goodsAttrInit.splice(index, 1, id)
			},
			async onBuyClicked() {
				//判断是否绑定手机号
				if (!uni.getStorageSync('bindMobilePhone')) {
					let roles = await this.$store.dispatch('setUserId', {
						type: true
					});
					if (!roles.data.mobile_phone) {
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

				this.$store.dispatch('setBargainBuy', {
					id: this.goodsInfo.id,
					bs_id: this.goodsInfo.bs_id,
					goods_id: this.goodsInfo.goods_id,
					num: this.num
				}).then(res => {
					if (res.data.error == 0) {
						uni.navigateTo({
							url: '/pages/checkout/checkout?rec_type=8&type_id=' + res.data.bs_id
						})
					} else {
						uni.showToast({
							title: res.data.msg,
							icon: 'none'
						});
					}
				})
			},
			bindChange(e) {
				this.num = e
			},
			onCart() {
				uni.switchTab({
					url: '/pages/cart/cart'
				});
			},
			//banner滚动
			swiperChange(e) {
				this.poster = false
			},
			//视频自动播放
			onPlay() {
				this.poster = true
			},
			//视频播放错误
			videoErrorCallback(e) {
				uni.showModal({
					content: e.target.errMsg,
					showCancel: false
				})
			},
			handleRule() {
				this.ruleShow = true
			},
			//关闭Popup
			handleClose(val) {
				if (val == 'rule') {
					this.ruleShow = false
				} else if (val == 'base') {
					this.showBase = false
				} else if (val == 'bid') {
					this.bidShow = false
				}
			},
			confirmBid(bs_id) {
				this.bidShow = false
				this.goodsDetail(bs_id);
			},
			onClickItem(index) {
				if (this.current !== index) {
					this.current = index;
				}
			},
			onRankItem(index) {
				if (this.rankCurrent !== index) {
					this.rankCurrent = index;
				}
			},
			myBargain() {
				uni.navigateTo({
					url: '../mylist/mylist'
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
			// 分享
			appShare() {
				let shareInfo = {
					href: `${this.$websiteUrl}bargain/detail/${this.id}?bs_id=${this.goodsInfo.bs_id}&parent_id=${uni.getStorageSync("user_id")}&platform=APP`,
					title: this.goodsInfo.goods_name,
					summary: this.goodsInfo.bargain_desc ? this.goodsInfo.bargain_desc : this.goodsInfo.goods_brief,
					imageUrl: this.goodsInfo.goods_thumb
				};
				this.shareInfo(shareInfo, 'poster');
			},
			mpShare() {
				this.onGoodsShare();
			},
			onGoodsShare() {
				if (this.$isLogin()) {
					uni.showLoading({
						title: this.$t('lang.loading')
					});
					let price = this.goodsInfo.target_price;
					let o = {}

					// #ifdef MP-WEIXIN
					o = {
						goods_id: this.goodsInfo.goods_id,
						ru_id: 0,
						price: price,
						share_type: 0,
						type: 0,
						platform: 'MP-WEIXIN',
						extension_code: 'bargain',
						code_url: 'pagesA/bargain/detail/detail',
						scene: `${this.id}`,
						thumb: this.gallerylList[0],
					}
					// #endif

					// #ifdef APP-PLUS
					o = {
						goods_id: this.goodsInfo.goods_id,
						price: price,
						share_type: 0,
						platform: 'APP',
						extension_code: 'bargain',
						title: this.goodsInfo.goods_name,
						code_url: `${this.$websiteUrl}bargain/detail/${this.id}?bs_id=${this.goodsInfo.bs_id}&parent_id=${uni.getStorageSync("user_id")}&platform=APP`,
						thumb: this.gallerylList[0],
					}
					// #endif

					this.$store.dispatch('setGoodsShare', o).then(res => {
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
						itemList: [this.$t('lang.send_to_friend'), this.$t('lang.save_picture'), this.$t(
							'lang.collect')],
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
			showMore() {
				this.isLengthShow = !this.isLengthShow;
				this.lengthNum = this.isLengthShow ? 10 : this.bargainDetailData.bargain_ranking.length;
				this.lengthMore = this.isLengthShow ? this.$t('lang.view_more') : this.$t('lang.pack_up');
			},
			getTemplate(code) {
				// 小程序订阅消息 模板列表
				uni.request({
					url: this.websiteUrl + '/api/wxapp/get_template',
					method: 'POST',
					data: {
						code: code,
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						if (res.data.status == 'success') {
							let result = res.data.data
							console.log(result)
							if (result.length > 0) {
								this.wx_template_id = result[0]['wx_template_id'] || ''
							}
						}
					}
				})
			}
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
	.banner {
		position: relative;
	}

	.cont-box .title .goods-price {
		padding: 0;
	}

	.goods_outer {
		padding: 0;
		margin-top: 10upx;
	}

	.uni-list-cell-navigate {
		justify-content: flex-start;
	}

	.uni-list-cell-navigate .title {
		min-width: 100upx;
		color: #999999;
		margin-right: 15upx;
	}

	.uni-list-cell .iconfont {
		color: #f92028;
		margin-right: 10upx;
	}

	.uni-list-cell-title .uni-list-cell-navigate {
		flex-direction: column;
	}

	.uni-list-cell-title .uni-list-cell-navigate.uni-navigate-right:after {
		top: 35%;
	}

	.uni-list-cell-title .uni-list-cell-navigate .title {
		width: 100%;
		color: #333333;
		font-size: 30upx;
		margin-bottom: 20upx;
	}

	.uni-list-cell-title .uni-list-cell-navigate .value {
		display: flex;
		align-items: center;
		color: #999999;
	}

	.steps {
		display: flex;
		flex-direction: row;
		width: 100%;
		justify-content: center;
		align-items: center;
	}

	.steps .item {
		position: relative;
		width: 25%;
		color: #777;
		text-align: center;
		margin: 20upx 0;
	}

	.steps .item .num {
		position: relative;
		width: 40upx;
		height: 40upx;
		line-height: 40upx;
		font-size: 24upx;
		border-radius: 50%;
		border: 1px solid #e7e8ef;
		margin: 0 auto;
		z-index: 2;
		background: #FFFFFF;
	}

	.steps .item .tit {
		font-size: 24upx;
		margin-top: 20upx;
	}

	.steps .item .n-list-xian {
		position: absolute;
		border-top: 1px solid #e7e8ef;
		height: 1px;
		z-index: 1;
		width: 100%;
		top: 22%;
	}

	.steps .item:first-child .n-list-xian {
		left: 50%;
		width: 60%;
	}

	.steps .item:last-child .n-list-xian {
		right: 50%;
		width: 60%;
	}

	.rule {
		padding: 20upx;
		border-top: 1px solid #e7e8ef;
	}

	.rule view {
		text-align: left;
		font-size: 25upx;
		color: #999;
	}

	.scroll-view {
		white-space: nowrap;
		width: 100%;
	}

	.scroll-view-product {
		overflow: hidden;
	}

	.scroll-view-product .scroll-view-item {
		width: 238upx;
		line-height: 1.8;
		padding-bottom: 15upx;
		display: inline-block;
		margin-left: -1px;
		background-color: #FFFFFF;
	}

	.scroll-view-product .scroll-view-item:first-child {
		margin-left: 0;
	}

	.scroll-view-product .scroll-view-item image {
		width: 238upx;
		height: 238upx;
	}

	.scroll-view-product .scroll-view-item .name {
		width: 100%;
		font-size: 25upx;
		display: block;
		padding: 0 10upx;
		box-sizing: border-box;
	}

	.scroll-view-product .scroll-view-item .price {
		display: flex;
		align-items: center;
		padding: 0 10upx;
	}

	.scroll-view-product .scroll-view-item .price-original {
		color: #fd0225;
		font-size: 25upx;
	}

	.scroll-view-product .scroll-view-item .price-favour {
		color: #888888;
		text-decoration: line-through;
		font-size: 20upx;
		margin-left: 10upx;
	}

	.attr-goods-box {
		position: relative;
	}

	.attr-goods-box .attr-goods-header {
		padding: 20upx;
	}

	.attr-goods-box .attr-goods-header .attr-img {
		position: absolute;
		box-shadow: 2px 2px 15px rgba(46, 58, 76, .17) !important;
		width: 200upx;
		height: 200upx;
		border-radius: 10upx;
		top: -40upx;
		overflow: hidden;
	}

	.attr-goods-box .attr-goods-header .attr-img image {
		width: 100%;
	}

	.attr-goods-box .attr-goods-header .attr-info {
		margin-left: 220upx;
		text-align: left;
	}

	.attr-goods-box .attr-goods-header .attr-info .attr-price {
		font-size: 35upx;
		color: #f92028;
		height: 60upx;
	}

	.attr-goods-box .attr-goods-header .attr-info .attr-price text {
		color: #888;
		font-size: 26upx;
	}

	.attr-goods-box .attr-goods-header .attr-info .attr-stock {
		color: #888;
		font-size: 26upx;
	}

	.attr-goods-box .attr-goods-header .attr-info .attr-other {
		color: #888;
		font-size: 26upx;
	}

	.attr-goods-box .attr-goods-content {
		min-height: 240upx;
		max-height: 480upx;
		overflow-y: auto;
		padding: 0 20upx;
		text-align: left;
	}

	.attr-goods-box .attr-goods-content .sku-item {
		padding-bottom: 20upx;
	}

	.attr-goods-box .attr-goods-content .sku-item .sku-tit {
		color: #888888;
	}

	.attr-goods-box .attr-goods-content .sku-item .sku-list {
		display: flex;
		flex-direction: row;
		justify-content: flex-start;
		flex-wrap: wrap;
	}

	.attr-goods-box .attr-goods-content .sku-item .sku-list text {
		padding: 5upx 30upx;
		border: 1px solid #e6e6e6;
		background: #FFFFFF;
		margin: 10upx 20upx 10upx 0;
		font-size: 26upx;
		color: #333;
		border-radius: 8upx;
	}

	.attr-goods-box .attr-goods-content .sku-item .sku-list text.active {
		border-color: #f92028;
		color: #f92028;
	}

	.attr-goods-box .attr-goods-number {
		display: flex;
		flex-direction: row;
		justify-content: space-between;
		align-items: center;
		padding: 20upx 20upx 30upx 20upx;
	}

	.attr-goods-box .attr-goods-number .tit {
		width: 200upx;
		text-align: left;
		color: #888;
	}

	.attr-goods-box .attr-goods-number .stepper {
		flex: 1 1 0%;
		display: flex;
		justify-content: flex-end;
	}

	/*砍价*/
	.bargain-progressbar {
		position: relative;
	}

	.bargain-progressbar .plan-common {
		width: 12upx;
		height: 12upx;
		border-radius: 100%;
		position: absolute;
		border: 6upx solid #efeeee;
		top: -8upx;
	}

	.bargain-progressbar .plan-left {
		border-color: #f92028;
		left: 0;
	}

	.bargain-progressbar .plan-right {
		border-color: #efeeee;
		right: 0;
	}

	.bargain-progressbar .wrap {
		height: 10upx;
		background-color: #efeeee;
		margin: 0 24upx;
	}

	.bargain-progressbar .wrap .bar,
	.bargain-progressbar .wrap .bar .color {
		height: 100%;
		display: block;
		border-radius: 8upx;
	}

	.bargain-progressbar .wrap .bar .color {
		width: 100%;
		background: #ffa538;
	}

	.bargain-tip {
		position: relative;
		margin-top: 20upx;
		display: flex;
		flex-direction: row;
	}

	.bargain-tip .join {
		display: inline-block;
		background: #f92028;
		color: #FFFFFF;
		text-align: center;
		width: 44%;
		font-size: 25upx;
		height: 50upx;
		line-height: 50upx;
	}

	.bargain-tip .triangle {
		border: 25upx solid transparent;
		border-left-color: #f92028;
		overflow: hidden;
		height: 0;
		width: 0;
		z-index: 1;
	}

	.bargain-tip .order {
		flex: 1 1 0%;
		margin-left: -45upx;
	}

	.bargain-tip .order swiper {
		height: 40upx;
		background: #ffcc33;
		margin-top: 5upx;
		line-height: 40upx;
		padding-left: 30upx;
	}

	.bargain-tip .order .cont {
		font-size: 25upx;
	}

	.bargain-tip .order .cont text {
		margin: 0 5upx;
	}

	.bargain-time {
		display: flex;
		flex-direction: row;
		padding: 20upx;
		color: #FFFFFF;
		justify-content: space-between;
		position: absolute;
		left: 0;
		right: 0;
		bottom: 0;
		z-index: 1;
		background: rgba(0, 0, 0, .6);
	}

	.qinyou {
		background: #FFFFFF;
		margin-top: 20upx;
	}

	.qinyou-cont .item {
		display: flex;
		flex-direction: row;
		padding: 20upx;
		border-bottom: 1px solid #e3e8ee;
	}

	.qinyou-cont .item .icon {
		width: 72upx;
		display: flex;
		flex-direction: row;
		align-items: center;
	}

	.qinyou-cont .item .icon image {
		width: 60%;
	}

	.qinyou-cont .item .left {
		width: 120upx;
		height: 120upx;
		margin-right: 20upx;
		display: flex;
		flex-direction: row;
		align-items: center;
	}

	.qinyou-cont .item .left image {
		width: 100%;
		border-radius: 100%;
		overflow: hidden;
	}

	.qinyou-cont .item .con {
		display: flex;
		justify-content: flex-start;
		align-items: center;
		flex: 1 1 0%;
	}

	.qinyou-cont .item .right {
		display: flex;
		flex-direction: row;
		align-items: center;
		color: #999999;
		font-size: 25upx;
	}

	.bargian-user {
		display: flex;
		padding: 20upx 20upx 0 20upx;
	}

	.bargian-user .left {
		width: 100upx;
		height: 100upx;
		margin-right: 20upx;
	}

	.bargian-user .left image {
		width: 100%;
		border-radius: 100%;
		overflow: hidden;
	}

	.bargian-user .right {
		flex: 1 1 0%;
	}

	.bargian-user .right .tit {
		font-size: 30upx;
	}

	.bargian-user .right .tit text {
		font-size: 25upx;
		padding: 0 20upx;
		border: 1px solid #f92028;
		color: #f92028;
		border-radius: 20upx;
		margin-left: 10upx;
	}

	.bargian-user .right .subtit {
		font-size: 25upx;
		color: #999999;
	}

	.bargain-friends {
		background: #FFFFFF;
		width: 100%;
	}

	.bargain-friends .tit {
		text-align: center;
		font-size: 30upx;
		border-bottom: 1px solid #eee;
	}

	.bargain-friends .image {
		width: 180upx;
		margin: 30upx auto;
	}

	.bargain-friends .image image {
		width: 100%;
	}

	.bargain-friends .cont {
		padding: 0 20upx;
		text-align: center;
		font-size: 25upx;
	}

	.bargain-friends .cont text {
		margin: 0 5upx;
	}

	.bargain-friends .footer {
		width: 100%;
		text-align: center;
		background: #ff495e;
		color: #FFFFFF;
		font-size: 25upx;
		padding: 5upx 0;
		border-radius: 8upx;
		margin-top: 20upx;
	}

	.showMore {
		display: flex;
		justify-content: center;
		align-items: center;
		padding: 20upx 0;
		color: #999;
	}

	/* 小程序分享  start*/
	.show-popup-shareImg /deep/ .uni-popup-bottom {
		height: 80%;
	}

	/* 小程序分享 end*/
</style>
