<template>
	<view class="container">
		<view class="nav_bar">
			<!-- #ifdef APP-PLUS -->
			<view class="status_bar">
				<!-- 这里是状态栏 -->
			</view>
			<!-- #endif -->
			<!-- #ifdef MP-WEIXIN -->
			<view :style="{paddingTop: `${menuButtonInfo.top - 4}px`, height: `${menuButtonInfo.height + 8}px`}">
				
			</view>
			<!-- #endif -->
		</view>
		<view v-if="goodsCartList.length > 0">
			<view class="header-address">
				<view class="address-box" @click="handleRegionShow">
					<view class="iconfont icon-location"></view>
					<text>{{ regionSplic }}</text>
				</view>
				<view class="address-box"></view>
				<view class="h-edit uni-red" @click="onEdit">
					<text v-if="!batchEdit">{{$t('lang.edit')}}</text>
					<text v-else>{{$t('lang.accomplish')}}</text>
				</view>
			</view>
			<view class="card-goods">
				<view class="card-goods-group" v-for="(storeList,listIndex) in goodsCartList" :key="listIndex">
					<view class="card-shop-head">
						<view class="head-left">
							<view class="checkbox" :class="{'checked':checkedShop[listIndex],'checkbox-disabled':shopInvalidArr[listIndex]}"
							 @click="checkShop(checkedShop[listIndex],storeList,listIndex,shopInvalidArr[listIndex])">
								<view class="checkbox-icon">
									<uni-icons type="checkmarkempty" size="16" color="#ffffff" v-if="!shopInvalidArr[listIndex]"></uni-icons>
								</view>
							</view>
							<view class="checkbox-con" @click="$outerHref('/pages/shop/shopHome/shopHome?ru_id=' + storeList.store_id,'app')"
							 v-if="storeList.store_id > 0">{{ storeList.store_name }}
								<uni-icons type="arrowright" size="16" color="#333333"></uni-icons>
							</view>
							<view class="checkbox-con" v-else>{{ storeList.store_name }}</view>
						</view>
						<view class="head-right uni-red" @click="handleCoupon(storeList.store_id)" v-if="storeList.coupuns_num > 0">{{$t('lang.receive_coupon')}}</view>
					</view>
					<view class="card-shop-box">
						<view class="card-act-goods" v-for="(actItem,actIndex) in storeList.new_list" :key="actIndex">
							<view class="cart-act-title" v-if="actItem.act_type != null">
								<view class="txt">
									<view class="em-icon" :class="{'active-zeng':actItem.act_type == 0,'active-zhekou':actItem.act_type == 2}">{{ actItem.act_type_txt }}</view>
									<view class="act-name" v-if="actItem.act_type == 0">
										<view v-if="actItem.available">{{$t('lang.filled_in')}}{{ actItem.min_amount }} <text class="uni-red" @click="receiveGift(actItem.act_id,false)">{{$t('lang.get_gift')}}</text></view>
										<view v-else>{{$t('lang.shopping_with')}}{{ actItem.min_amount }},{{$t('lang.are_get_gift')}}, <text class="uni-red" @click="receiveGift(actItem.act_id,true)">{{$t('lang.view_gift')}}</text></view>
									</view>
									<view class="act-name" v-else-if="actItem.act_type == 1">
										<view v-if="actItem.available">{{$t('lang.filled_in')}}{{ actItem.min_amount }}（{{$t('lang.stand_minus')}}{{actItem.act_type_ext_format}}）</view>
										<view v-else>{{$t('lang.man')}}{{ actItem.min_amount }}{{$t('lang.minus')}}{{ actItem.act_type_ext_format }}</view>
									</view>
									<view class="act-name" v-else>
										<view v-if="actItem.available">{{$t('lang.filled_in')}}{{ actItem.min_amount }}（{{$t('lang.enjoy')}}{{ actItem.act_type_ext_format }}{{$t('lang.percent_off_Discount')}}）</view>
										<view v-else>{{$t('lang.shopping_with')}}{{ actItem.min_amount }}{{$t('lang.enjoy')}}{{ actItem.act_type_ext_format }}{{$t('lang.percent_off_Discount')}}</view>
									</view>
								</view>
								<view class="more" @click="$outerHref('/pages/cart/coudan/coudan?act_id='+actItem.act_id,'app')">
									<view v-if="actItem.available">{{$t('lang.visit_again')}}</view>
									<view v-else>{{$t('lang.gather_together')}}</view>
									<uni-icons type="arrowright" size="16" color="#f92028"></uni-icons>
								</view>
							</view>
							<block v-for="(item,index) in actItem.act_goods_list" :key="index">
								<uni-swipe-action>
									<uni-swipe-action-item
										:right-options="options"
										@click="deleteCartGoods(item.rec_id)"
									>
										<view class="scroll-view-item scroll-view-item-left">
											<view class="uni-flex">
												<view class="checkbox" :class="{'checked':checkedGoods[listIndex].includes(item.rec_id),'checkbox-disabled':item.is_invalid == 1 || item.product_number == 0}" @click="checkGoods(item.rec_id,listIndex,item.is_invalid,item.product_number)">
													<view class="checkbox-icon">
														<uni-icons type="checkmarkempty" size="16" color="#ffffff" v-if="item.is_invalid != 1"></uni-icons>
													</view>
												</view>
												<view class="checkbox-con" style="flex: 1 1 0%;">
													<view class="cart-goods-info">
														<view class="goods-img">
															<navigator :url="'/pagesC/goodsDetail/goodsDetail?id='+item.goods_id" hover-class="none">
																<image :src="item.goods_thumb" v-if="item.goods_thumb" class="image"></image>
															</navigator>
															<block v-if="item.is_invalid == 0 && item.product_number == 0">
																<view class="mask"></view>
																<view class="mash-text">{{$t('lang.no_goods')}}</view>
															</block>
															<block v-if="item.is_invalid == 1">
																<view class="mask"></view>
																<view class="mash-text">{{$t('lang.lost_effectiveness')}}</view>
															</block>
															<block v-if="item.xiangou_error > 0 && !(item.is_invalid == 0 && item.product_number == 0)">
																<view class="mask"></view>
																<view class="mash-text">{{$t('lang.man_gb_limited')}}</view>
															</block>
															
															<!--商品标签-->
															<view class="goods-label-suspension" v-if="item.goods_label_suspension && item.goods_label_suspension.formated_label_image">
																<image :src="item.goods_label_suspension.formated_label_image" class="img"></image>
															</view>
														</view>
														<view class="cart-goods-content">
															<view class="goods-title twolist-hidden">
																	<navigator :url="'/pagesC/goodsDetail/goodsDetail?id='+item.goods_id" hover-class="none">
																		<image class="country_icon" :src="item.country_icon" :lazy-load="true" v-if="item.country_icon"></image>{{ item.goods_name }}
																	</navigator>
															</view>
															<view class="goods-attr twolist-hidden" sv-if="item.goods_attr">{{ item.goods_attr }}</view>
															<view class="goods-info">
																<view class="price">{{ item.goods_price_format }}</view>
																<view class="oper">
																	<view class="uni-number-min">
																		<uni-number-box 
																		:value="item.goods_number" 
																		:min="item.is_minimum > 0 ? item.minimum : 1" 
																		:max="(item.xiangou_num > 0 && item.xiangou_end_date > item.current_time) ? item.xiangou_num  : item.product_number"
																		@change="goodsNumberhandle($event,item.rec_id,item.act_id,storeList.store_id)" 
																		:disabled="stepDisabled.length > 0 ? stepDisabled[listIndex][index].type : false"
																		>
																		</uni-number-box>
																	</view>
																	<view class="oper-icon"></view>
																</view>
															</view>
															<view class="goods-other" v-if="actItem.act_type != null" @click="onFavourableList(item.goods_id,item.ru_id,item.act_id,item.rec_id)">
																<view class="em-icon" :class="{'active-zeng':actItem.act_type == 0,'active-zhekou':actItem.act_type == 2}">{{ actItem.act_type_txt }}</view>
																<view class="act-name uni-ellipsis">{{ actItem.act_name }}</view>
																<uni-icons type="arrowdown" size="18" color="#999999"></uni-icons>
															</view>
															
															<view class="goods-other" v-if="item.is_kj == 1">
																  <view v-if="item.rate_price > 0" style="font-size: 24rpx; color:#B78D5A;">{{$t('lang.import_tax')}}：{{item.format_rate_price}}</view>
																  <view v-else style="font-size: 24rpx; color:#B78D5A;">{{$t('lang.import_tax')}}：{{$t('lang.goods_tax_included')}}</view>
															</view>
														</view>
													</view>
													<view class="goods-store" v-if="item.store_count > 0">
														{{$t('lang.support')}}<text class="uni-red">{{$t('lang.private_store')}}</text>{{$t('lang.service')}}
													</view>
												</view>
											</view>
										</view>
									</uni-swipe-action-item>
								</uni-swipe-action>
								
								<!-- 配件商品 -->
								<view class="cart-goods-item" v-for="(partsItem,partsIndex) in item.parts" :key="partsIndex">
									<scroll-view scroll-x scroll-y scroll-anchoring class="scroll-view-G" :scroll-left="scrollLeft">
										<view class="scroll-view-item scroll-view-item-left">
											<view class="uni-flex">
												<view class="checkbox" style="opacity: 0;">
													<view class="checkbox-icon">
														<uni-icons type="checkmarkempty" size="16" color="#ffffff"></uni-icons>
													</view>
												</view>
												<view class="checkbox-con" style="flex: 1 1 0%;">
													<view class="cart-goods-info" style="background-color: #fff9f8;" @click="productLink(partsItem)">
														<view class="goods-img">
															<image :src="partsItem.goods_thumb" v-if="partsItem.goods_thumb" class="image"></image>
															<block v-if="partsItem.is_invalid == 0 && partsItem.product_number == 0">
																<view class="mask"></view>
																<view class="mash-text">{{$t('lang.no_goods')}}</view>
															</block>
															<block v-if="partsItem.is_invalid == 1">
																<view class="mask"></view>
																<view class="mash-text">{{$t('lang.lost_effectiveness')}}</view>
															</block>
															<block v-if="partsItem.xiangou_error > 0">
																<view class="mask"></view>
																<view class="mash-text">{{$t('lang.man_gb_limited')}}</view>
															</block>
															
															<!--商品标签-->
															<view class="activity-tag" v-if="partsItem.extension_code == 'package_buy'">
																<image src="/static/package.png" class="img" mode="widthFix"></image>
															</view>
															<view class="activity-tag parts-tag" v-if="partsItem.group_id && partsItem.parent_id != 0">
																<image src="/static/parts-icon.png" class="img" mode="widthFix"></image>
															</view>
														</view>
														<view class="cart-goods-content">
															<view class="goods-title twolist-hidden">
																{{ partsItem.goods_name }}
															</view>
															<view class="goods-attr twolist-hidden" v-if="partsItem.goods_attr">{{ partsItem.goods_attr }}</view>
															<view class="goods-info">
																<view class="price">{{ partsItem.goods_price_format }}</view>
																<view class="oper">
																	<view class="uni-number-min" style="width: 220rpx; text-align: center;">
																		{{ partsItem.goods_number }}
																	</view>
																</view>
															</view>
														</view>
													</view>
												</view>
											</view>
										</view>
										<view class="scroll-view-item scroll-view-item-right" @click="deleteCartGoods(partsItem.rec_id)">
											<view>{{$t('lang.delete')}}</view>
										</view>
									</scroll-view>
								</view>
							</block>
							<!-- 赠品 -->
							<view class="cart-goods-item cart-goods-item-outer" v-for="(item,index) in actItem.act_cart_gift" :key="index">
								<view class="checkbox-con">
									<view class="cart-goods-info">
										<view class="goods-img">
											<image :src="item.goods_thumb" v-if="item.goods_thumb" class="image"></image>
											<image src="../../static/gift-icon.png" class="icon"></image>
										</view>
										<view class="cart-goods-content">
											<view class="goods-title twolist-hidden">{{ item.goods_name }}</view>
											<view class="goods-attr" v-if="item.goods_attr">{{ item.goods_attr }}</view>
											<view class="goods-info">
												<view class="price">{{ item.goods_price_format }}</view>
												<view class="oper">
													<view class="stepper stepper_sum">x{{ item.goods_number }}</view>
												</view>
											</view>
										</view>
									</view>
								</view>
							</view>
						</view>
					</view>
					<view class="shop-store-cart" v-if="shopStore.length > 0 && shopStore[listIndex][0]">
						<view class="store-btn" @click="shopStoreCart(listIndex)">
							<icon class="iconfont icon-store-alt"></icon>{{$t('lang.private_store')}}
						</view>
					</view>
				</view>
			</view>
			<view class="cart-submit">
				<view class="checkall">
					<view class="checkbox" :class="{'checked':checkedAll,'checkbox-disabled':checkedAllDisabled}" @click="checkAll(checkedAllDisabled)">
						<view class="checkbox-icon">
							<uni-icons type="checkmarkempty" size="16" color="#ffffff" v-if="!checkedAllDisabled"></uni-icons>
						</view>
						<label>{{$t('lang.checkd_all')}}</label>
					</view>
				</view>
				<view class="submit-bar-text">
					<view>
						<text>{{$t('lang.total_flow')}}:</text>
						<view class="submit-bar-price">{{totalPriceTip}}</view>
					</view>
					<view class="submit-bar-sub" v-if="ratePrice > 0">({{$t('lang.taxes_dues')}} {{ ratePriceTip }})</view>
					<view class="submit-bar-sub" v-if="favourablePrice != 0">({{$t('lang.already_save')}} {{ favourableTip }})</view>
				</view>
				<view class="btn-bar">
					<block v-if="isLogin===true">
						<block v-if="!batchEdit">
							<button class="btn" :class="[disabled ? 'btn-disabled' : 'btn-red']" :disabled="disabled" open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile">{{ submitBarText }}</button>
							 <button class="btn" :class="[disabled ? 'btn-disabled' : 'btn-red']" :disabled="disabled" @click="onSubmit(true)">{{ submitBarText }}</button>
						</block>
					</block>
					<block v-else>
						<block v-if="!batchEdit">
							<button class="btn" :class="[disabled ? 'btn-disabled' : 'btn-red']" :disabled="disabled"
							 @click="onSubmit">{{ submitBarText }}</button>
						</block>
					</block>
					<button class="btn" :class="[length > 0 ? 'btn-red' : 'btn-disabled']" :disabled="!length > 0" @click="onBatchDelete"
					 v-else>{{$t('lang.batch_delete')}}</button>
				</view>
			</view>
		</view>
		<view class="flow-no-cart" v-else>
			<view class="gwc-bg">
				<view class="iconfont icon-cart"></view>
			</view>
			<text>{{$t('lang.shopping_cart_notice')}}</text>
			<view class="card-btn">
				<navigator url="/pages/index/index" open-type="switchTab" hover-class="none"><button type="default" plain="true">{{$t('lang.go_around')}}</button></navigator>
				<button type="default" plain="true" @click="$outerHref('../../pagesB/collectionGoods/collectionGoods',$isLogin())">{{$t('lang.view_follow')}}</button>
			</view>
		</view>

		<view class="goods-detail-guess" v-if="goodsGuessList">
			<view class="title"><text>{{$t('lang.guess_love')}}</text></view>
			<view class="product-list-lie">
				<dsc-product-list :list="goodsGuessList" :productOuter="productOuter"></dsc-product-list>
				<uni-load-more :status="loadMoreStatus" :content-text="contentText" v-if="page > 1" />
			</view>
		</view>
		
		<!-- 会员登录获取手机号码 start -->
		<uni-popup :show="getPhoneNumberShow" class="phone_box"  :animation="true">
			<view class="pop_content pop_phone_number">
				<view class="pop_header" style="padding:0;">					
					<text class="pop_title">{{$t('lang.Authorized_Phone_Number')}}</text>
				</view>
				<view class="pop_phone_content">
					<view class="logo">
					  <image src="../../static/logo.png" class="img"></image>
					</view>
					<text>{{$t('lang.user_bind_phone')}}</text>
				</view>
				<view class="btn">					
					<button type="primary"  style="border-radius: 20px;height: 70rpx; line-height: 70rpx;font-size:16px;" open-type="getPhoneNumber" @getphonenumber="getPhoneNumber" >{{$t('lang.confirm_on')}}</button>
				</view>
			</view>
		</uni-popup>
		<!-- 会员登录获取手机号码 end -->

		<!-- 领取赠品列表 -->
		<uni-popup :show="giftShow" type="bottom" v-on:hidePopup="handelClose('gift')">
			<view class="activity-popup">
				<view class="title">
					<view class="txt">{{$t('lang.amount_max_available')}}{{ act_type_ext }}{{$t('lang.goods_letter')}}
						<button type="warn" size="mini" @click="submitGift" v-if="!giftDisabled">{{$t('lang.subimt')}}</button>
					</view>
					<uni-icons type="closeempty" size="36" color="#999999" @click="handelClose('gift')"></uni-icons>
				</view>
				<view class="content">
					<scroll-view scroll-y style="height:250px;">
						<view class="cart-goods-item" v-for="(item,index) in giftList" :key="index">
							<view class="checkbox" :class="{'checked':item.is_checked}" @click="checkGift(item.id)">
								<view class="checkbox-icon">
									<uni-icons type="checkmarkempty" size="16" color="#ffffff"></uni-icons>
								</view>
							</view>
							<view class="checkbox-con">
								<view class="cart-goods-info">
									<view class="goods-img">
										<image :src="item.thumb" v-if="item.thumb" class="image"></image>
									</view>
									<view class="cart-goods-content">
										<view class="goods-title twolist-hidden">{{ item.name }}</view>
										<view class="goods-info">
											<view class="price">{{ item.price_formated }}</view>
										</view>
									</view>
								</view>
							</view>
						</view>
					</scroll-view>
				</view>
			</view>
		</uni-popup>

		<!-- 促销活动 -->
		<uni-popup :show="favourableShow" type="bottom" v-on:hidePopup="handelClose('favourable')">
			<view class="activity-popup">
				<view class="title">
					<view class="txt">{{$t('lang.optional_promotions')}}</view>
					<uni-icons type="closeempty" size="36" color="#999999" @click="handelClose('favourable')"></uni-icons>
				</view>
				<view class="content">
					<scroll-view scroll-y style="height:200px;">
						<view class="cart-goods-item" v-for="(item,index) in favourableList" :key="index">
							<view class="checkbox" :class="{'checked':item.is_checked}" @click="onFavourableTab(item.act_id,item.rec_id)">
								<view class="checkbox-icon">
									<uni-icons type="checkmarkempty" size="16" color="#ffffff"></uni-icons>
								</view>
							</view>
							<view class="act-item-right">
								<view class="em-icon active-zhekou" v-if="item.act_type == 0">{{$t('lang.with_a_gift')}}</view>
								<view class="em-icon active-jian" v-else-if="item.act_type == 1">{{$t('lang.full_reduction')}}</view>
								<view class="em-icon active-zhekou" v-else-if="item.act_type == 2">{{$t('lang.discount')}}</view>
								<view class="act-name uni-ellipsis">{{ item.act_name }}</view>
							</view>
						</view>
					</scroll-view>
				</view>
			</view>
		</uni-popup>

		<!-- 优惠券 -->
		<uni-popup :show="couponShow" type="bottom" v-on:hidePopup="handelClose('coupon')">
			<view class="title">
				<view class="txt">{{$t('lang.receive_coupon')}} ({{ coupuns_num }})</view>
				<uni-icons type="closeempty" size="36" color="#999999" @click="handelClose('coupon')"></uni-icons>
			</view>
			<view class="content">
				<scroll-view scroll-y style="height:300px;">
					<view class="items" v-if="cartCouponsList.length > 0">
						<view class="item" v-for="(item,index) in cartCouponsList" :key="index">
							<view class="left">
								<view class="price">
									<view class="em">{{ currency_format }}</view>
									<view class="strong-text">{{ item.cou_money }}</view>
									<view class="couons-text">{{$t('lang.man')}}{{ item.cou_man }}{{$t('lang.available_full')}}</view>
								</view>
								<view class="con">
									<view class="range-item">{{ item.cou_name }}</view>
									<view class="range-item">{{ item.cou_start_time }} {{$t('lang.zhi')}} {{ item.cou_end_time }}<text class="uni-red">({{$t('lang.cou_user_receive_hove')}}{{ item.cou_user_num}}{{$t('lang.zhang')}})</text></view>
								</view>
								<image :src="imagePath.couponsPrint" v-if="item.cou_is_receive == 1"></image>
							</view>
							<view class="right" @click="handelReceive(item.cou_id)">
								<text v-if="item.cou_is_receive == 1 && isCartLogin">{{$t('lang.receive_hove')}}</text>
								<text v-else>{{$t('lang.receive')}}</text>
							</view>
						</view>
					</view>
				</scroll-view>
			</view>
		</uni-popup>

		<!--地区选择-->
		<dsc-region :display="regionShow" :regionOptionData="regionData" @updateDisplay="getRegionShow" @updateRegionDate="customUpdateRegion" v-if="regionLoading"></dsc-region>

		<dsc-loading :dscLoading="dscLoading"></dsc-loading>

		<!-- <tabbar :curpage="curpage"></tabbar> -->
		
		<div class="loading-transparent" v-show="loading">
		  <uni-load-more-new status="loading" :type="false" color="#F20E28" iconType="circle"></uni-load-more-new>
		  <div class="loading-overlay"></div>
		</div>
		
		<!--返回顶部-->
		<dsc-filter-top :scrollState="scrollState" outerClass="true"></dsc-filter-top>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniNumberBox from "@/components/uni-number-box.vue";
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniPopup from '@/components/uni-popup.vue';
	import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
	import uniLoadMoreNew from '@/components/uni-load-more-new/uni-load-more.vue';
	
	import universal from '@/common/mixins/universal.js';
	
	import dscProductList from '@/components/dsc-product-list.vue';
	
	/*地区选择组件*/
	import dscRegion from '@/components/dsc-region.vue';
	
	//返回顶部
	import dscFilterTop from '@/components/dsc-filter-top'
	
	export default {
		mixins: [universal],
		components: {
			uniNumberBox,
			uniIcons,
			uniPopup,
			uniLoadMore,
			uniLoadMoreNew,
			dscProductList,
			dscRegion,
			dscFilterTop
		},
		data() {
			return {
				scopeUserInfo: {},
				scope: 'userInfo',
				scopeSessions: {},
				canIUseProfile:false,
				getPhoneNumberShow:false,
				isLoginData:false,
				loading: false,
				disabled: true,
				default: true,
				checkedAll: true,
				checkedGoodsId: [],
				totalPrice: 0,
				ratePrice: 0,
				ratePriceTip: '',
				favourablePrice: 0,
				count: 0,
				coupuns_num: 0,
				productOuter: true,
				batchEdit: false,
				couponShow: false,
				favourableShow: false,
				favourableList: [],
				giftShow: false,
				giftList: [],
				act_type_ext: 0,
				checkGiftStr: '',
				checkGiftArr: [],
				giftDisabled: false,
				scrollLeft: 0,
				dscLoading: true,
				stepDisabled: [],
				curpage: '',
				shopStore: [],
				timer: '',
				page: 1,
				size: 10,
				currency_format: uni.getStorageSync('configData').currency_format || '¥',
				loadMoreStatus: 'more',
				contentText: {
					contentdown: this.$t('lang.view_more'),
					contentrefresh: this.$t('lang.loading'),
					contentnomore: this.$t('lang.no_more')
				},
				shopInvalidArr: [],
				checkedAllDisabled: false,
				scrollState:false,
				vw: 375,
				menuButtonInfo: {},
				options:[
					{
						text: '删除',
						style: {
							backgroundColor: '#f92028'
						}
					}
				],
			};
		},
		computed: {
			...mapState({
				checkedGoods: state => state.shopping.checkedGoods, //购物车商品列表
				cartCouponsList: state => state.shopping.cartCouponsList, //优惠券列表
				allGoodsListId: state => state.shopping.allGoodsListId
			}),
			goodsGuessList: {
				get() {
					return this.$store.state.shopping.goodsGuessList
				},
				set(val) {
					this.$store.state.shopping.goodsGuessList = val
				}
			},
			goodsCartList: {
				get() {
					return this.$store.state.shopping.goodsCartList
				},
				set(val) {
					this.$store.state.shopping.goodsCartList = val
				}
			},
			checkedShop: {
				get() {
					return this.$store.state.shopping.checkedShop
				},
				set(val) {
					this.$store.state.shopping.checkedShop = val
				}
			},
			isLogin(){
				let isLogin = uni.getStorageSync('bindMobilePhone') && this.$isLogin() ? false : true;
				//#ifdef MP-WEIXIN
				if(isLogin){					
					this.scopeSession();					
					// 判断小程序是否存在getUserProfile
					if(uni.getUserProfile) this.canIUseProfile = true;
				}
				//#endif
								
				//#ifdef APP-PLUS
				isLogin = false;
				//#endif
								
				return isLogin
			},
			submitBarText() {
				return this.$t('lang.go_checkout') + (this.count ? `(${this.count})` : '')
			},
			totalPriceTip() {
				return this.totalPrice
			},
			favourableTip() {
				return this.favourablePrice
			},
			isCartLogin() {
				return this.$isLogin()
			},
			length() {
				return this.checkedGoodsId ? this.checkedGoodsId.length : 0
			}
		},
		onPullDownRefresh() {
			this.initData();
		},
		onReachBottom() {
			this.showLoadMore = true
			this.loadMoreStatus = 'loading';
			if (this.page * this.size == this.goodsGuessList.length) {
				this.page++
				this.goodsGuessHandle()
			} else {
				this.showLoadMore = false
				return;
			}
		},
		onPageScroll(e) {
			this.scrollState = e.scrollTop > 800 ? true : false
		},
		methods: {
			
			scopeSession() {
				uni.login({
					provider: 'weixin',
					success: (res) => {
						this.$store.dispatch('getScopeAppSession', {
							code: res.code
						}).then(sessionRes => {
							uni.setStorage({
								key: 'scopeSession',
								data: sessionRes.data
							});
							this.scopeSessions = sessionRes.data;
						});						
					},
					fail: (res) => {
						uni.showToast({
							title: res.errMsg,
							icon: 'none'
						});
					}
				});
			},
			getUserProfile(toUrl){
				
				//优化兼容已登录则跳转相应地址
				if(this.$isLogin()){
					
					if(toUrl == ''){
						uni.redirectTo({
							url: '/pagesB/profile/profile'
						});
					}else{
						uni.redirectTo({
							url: toUrl
						});
					}
					
					return false;
				}
				
				uni.getUserProfile({
					desc : this.$t('lang.user_login_desc'),  
					lang : 'zh_CN',  
					success: (profileRes) => {
						uni.setStorage({ key: 'scopeUserInfo', data: profileRes.userInfo });
						this.scopeUserInfo = profileRes.userInfo;						
						this.scope = 'phoneNumber';
						this.getPhoneNumberShow = true;
						
						//跳转链接
						uni.setStorage({
							key: 'scopeUrl',
							data: toUrl
						});
					},
					fail: (infoRes) => {
						uni.showToast({ title: this.$t('lang.user_login_fail'), icon: 'none' });
					},
				});
			},
			getUserInfo(e) {				
				uni.getUserInfo({
					provider: 'weixin',
					success: (infoRes) => {
						console.log(infoRes);
						let session = {data:this.scopeSessions};
						this.$store.dispatch('getScopeUserInfo', {
							sessionKey: session.data.session_key,
							iv: infoRes.iv,
							encryptData: infoRes.encryptedData
						}).then(res => {
							this.scopeSessions = {
								openid: res.data.openId,
								unionid: res.data.unionId,
								session_key: session.data.session_key,
								expires_in: session.data.expires_in
							}
						})
						
						uni.setStorage({ key: 'scopeUserInfo', data: infoRes.userInfo });
						this.scopeUserInfo = infoRes.userInfo;
						this.scope = 'phoneNumber';
					},
					fail: (infoRes) => {
						uni.showToast({
							title: this.$t('lang.authorization_fail_notic'),
							icon: 'none'
						});
					}
				})
			},
			getPhoneNumber(e) {
				//读取本地scopeSession
				let res = {data:this.scopeSessions};
				this.getPhoneNumberShow = false;
				this.$store.dispatch('getScopePhoneNumber', {
					sessionKey: res.data.session_key,
					iv: e.detail.iv,
					encryptData: e.detail.encryptedData
				}).then(obj => {				
					if (obj.status == 'success') {
						let o = {}
						
						if (this.delta) {
							o = {
								type:"user",
								scopeSessions: this.scopeSessions,
								phoneNumber: obj.data.phoneNumber,
								userInfo: this.scopeUserInfo,
								delta: this.delta,
								parent_id: this.parent_id,
								platform: uni.getStorageSync('platform'),
							}
						} else {
							o = {
								type:"user",
								scopeSessions: this.scopeSessions,
								phoneNumber: obj.data.phoneNumber,
								userInfo: this.scopeUserInfo,
								parent_id: this.parent_id,
								platform: uni.getStorageSync('platform'),
							}
						}						
						this.$store.dispatch('getScopeAppLogin', o).then(()=>{
							setTimeout(()=>{
								
								let scopeUrl = uni.getStorageSync('scopeUrl');
								
								if(scopeUrl == ''){
									this.loadShowCart();
								}else{
									uni.redirectTo({
										url: scopeUrl
									});
								}
								
							},1000)
						});
					}
				});
			},
			
			//默认onShow加载
			async loadShowCart() {
				
				// 解决首次跳转购物车先跳转首页的BUG
				getApp().globalData.isShowCart = true;
				
				this.goodsList();
				
				this.default = true;
				this.batchEdit = false;
				
				//猜你喜欢
				this.goodsGuessHandle(1);
			},
			
			initData() {
				setTimeout(() => {
					this.goodsList();
					uni.stopPullDownRefresh();
				}, 300);
			},
			//购物车列表
			goodsList() {
				this.$store.dispatch('setGoodsCart', {
					warehouse_id: 0,
					area_id: 0,
					district_id:this.regionData.district.id
				})
			},
			//选择店铺
			checkShop(checked, shop, index, shopInvalid) {
				//是否有失效商品
				if (shopInvalid) return

				this.checkedShop.splice(index, 1, !checked)
				
				if (!checked) {
					shop.new_list.forEach((act) => {
						act.act_goods_list.forEach((g) => {
							this.checkedGoods[index].push(g.rec_id)
						})
					})
				} else {
					this.checkedGoods[index] = []
				}
				this.changeGoods()
			},
			//选择购物车商品
			checkGoods(rec_id, index, is_invalid, product_number) {
				let clength = []

				//是否有失效商品
				if (is_invalid == 1 || product_number == 0) return

				this.checkedGoods.forEach((v, i) => {
					if (i == index) {
						if (v.indexOf(rec_id) > -1) {
							v.splice(v.indexOf(rec_id), 1)
						} else {
							v.push(rec_id)
						}
					}
				})

				//获取店铺下商品数量
				this.goodsCartList.forEach((v, index) => {
					let arr = []
					v.new_list.forEach((act) => {
						act.act_goods_list.forEach((g) => {
							arr.push(g)
						})
					})

					clength.push(arr.length)
				})

				//商品勾选状态 改变店铺勾选状态
				this.checkedShop[index] = (clength[index] == this.checkedGoods[index].length)
			
				this.changeGoods()
			},
			//商品勾选变化更新购物车列表
			changeGoods() {
				this.checkedGoodsId = [];

				this.checkedGoods.forEach((v, i) => {
					v.forEach(i => {
						this.checkedGoodsId.push(i)
					})
				})

				this.checkAllOper();

				this.loading = true
				this.disabled = true

				//更新购物车商品列表
				this.$store.dispatch('setCartChecked', {
					rec_id: this.checkedGoodsId,
					sel_flag: 'cart_sel_flag',
					district_id:this.regionData.district.id
				}).then(({
					data: data
				}) => {
					this.totalPrice = data.goods_amount_formated //商品价格
					this.favourablePrice = data.discount_formated //购物车优惠价格
					this.count = data.cart_number //购物车勾选商品数量

					if (data.cross_border) { //跨境
						this.ratePrice = data.rate_price //税费
						this.ratePriceTip = data.rate_formated //税费格式化
					}

					if (data.cart_fav_box) { //数量改变触发优惠活动条件，更新购物车
						this.goodsCartList = data.cart_fav_box
						this.loading = false
						this.disabled = false
					}
				})
			},
			//默认进购物车全部勾选商品
			changeDefaultGooods() {
				this.default = false;
				this.checkAllOper();
				this.$store.dispatch('setCartDefault', {
					rec_id: this.checkedGoodsId
				}).then(({
					data: data
				}) => {
					this.totalPrice = data.goods_amount_formated //商品价格
					this.favourablePrice = data.discount_formated //购物车优惠价格
					this.count = data.cart_number //购物车勾选商品数量

					//跨境
					if (data.cross_border) {
						this.ratePrice = data.rate_price //税费
						this.ratePriceTip = data.rate_formated //税费格式化
					}

					this.loading = false
				})
			},
			//更新购物车全选状态
			checkAllOper() {
				let cAll = 0

				this.checkedShop.forEach(v => {
					if (!v) {
						cAll++
					}
				})
				if (cAll > 0) {
					this.checkedAll = false
				} else {
					this.checkedAll = true
				}
			},
			//全选
			checkAll(invalid) {
				//是否有失效商品
				if (invalid) return

				this.checkedAll = this.checkedAll ? false : true

				this.goodsCartList.forEach((v, i) => {
					this.checkedShop.splice(i, 1, this.checkedAll)
					this.checkedGoods[i] = []
					if (this.checkedAll) {
						v.new_list.forEach((act) => {
							act.act_goods_list.forEach((g) => {
								this.checkedGoods[i].push(g.rec_id)
							})
						})
					}
				})

				this.changeGoods()
			},
			//修改购物车商品数量
			goodsNumberhandle(e, id, act_id, store_id) {
				// clearTimeout(this.timer);
				// this.timer = setTimeout(() => {
					this.stepDisabled.forEach((v, i) => {
						v.forEach((s, d) => {
							if (s.id == id) {
								this.stepDisabled[i][d].type = true
							}
						})
					})

					this.$store.dispatch('setUpdateNumber', {
						rec_id: id,
						num: e,
						district_id:this.regionData.district.id
					}).then(({
						data: data
					}) => {
						this.stepDisabled.forEach((v, i) => {
							v.forEach((s, d) => {
								if (s.id == id) {
									this.stepDisabled[i][d].type = false
								}
							})
						});
						
						if(data.error == 0){
							this.totalPrice = data.goods_amount_formated //商品价格
							this.favourablePrice = data.discount_formated //购物车优惠价格
							this.count = data.cart_number //购物车勾选商品数量

							//跨境
							if (data.cross_border) {
								this.ratePrice = data.rate_price //税费
								this.ratePriceTip = data.rate_formated //税费格式化
							}

							if (data.cart_fav_box) { //数量改变触发优惠活动条件，更新购物车
								this.goodsCartList.forEach((v) => {
									if (v.store_id == store_id) {
										v.new_list.forEach((g, i) => {
											if (g.act_id == act_id) {
												v.new_list[i] = data.cart_fav_box
											}
										})
									}
								})
							}
						}else{
							this.goodsCartList.forEach((v)=>{
								v.new_list.forEach(g=>{
									g.act_goods_list.forEach(i=>{
										if(i.rec_id == id){
											console.log(data.number)
											i.goods_number = data.number
										}
									})
								})
							})
							
							uni.showToast({ title: data.msg,icon: 'none' });
						}
					})
				//}, 1000)
			},
			//优惠活动列表
			onFavourableList(goods_id, ru_id, act_id, rec_id) {
				this.$store.dispatch('setFavourable', {
					goods_id: goods_id,
					ru_id: ru_id,
					act_id: act_id,
					rec_id: rec_id
				}).then(({
					data: data
				}) => {
					this.favourableList = data.favourable
					this.favourableShow = true
					this.favourableList.forEach((v) => {
						if (v.is_checked) {
							this.checkFavourable = v.act_id
						}
					})
				})
			},
			//选择优惠活动
			onFavourableTab(act_id, rec_id) {
				this.favourableList.forEach(v => {
					v.is_checked = false
					if (act_id == v.act_id) {
						v.is_checked = true
					}
				})

				this.$store.dispatch('setChangefav', {
					act_id: act_id,
					rec_id: rec_id
				}).then(({
					data: data
				}) => {
					uni.showToast({
						title: data.msg,
						icon: 'none'
					})

					if (data.error == 0) {
						this.goodsList()
						this.changeDefaultGooods()
						this.favourableShow = false
					}
				})
			},
			//赠品弹出层
			receiveGift(act_id, type) {
				this.giftShow = true
				this.giftDisabled = type
				this.$store.dispatch('setGiftList', {
					act_id: act_id
				}).then(({
					data: data
				}) => {
					this.giftList = data.giftlist
					this.act_type_ext = parseInt(data.act_type_ext)
					this.giftList.forEach((v) => {
						if (v.is_checked) {
							if (this.act_type_ext > 1) {
								this.checkGiftArr.push(v.id)
							} else {
								this.checkGiftStr = v.id
							}
						}
					})
				})
			},
			//选择赠品
			checkGift(id) {
				if (!this.giftDisabled) {
					this.giftList.forEach((v) => {
						if (this.act_type_ext > 1) {
							if (id == v.id) {
								v.is_checked = v.is_checked ? false : true
								if (v.is_checked) {
									this.checkGiftArr.push(v.id)
								} else {
									var index = this.checkGiftArr.indexOf(v.id)
									if (index > -1) {
										this.checkGiftArr.splice(index, 1)
									}
								}
							}
						} else {
							v.is_checked = false
							if (id == v.id) {
								v.is_checked = true
								this.checkGiftStr = v.id
							}
						}
					})
				} else {
					uni.showToast({
						title: this.$t('lang.not_receive_gift'),
						icon: 'none'
					})
				}
			},
			//提交赠品
			submitGift() {
				let act_id = 0
				let ru_id = 0
				let select_gift
				this.giftList.forEach(v => {
					act_id = v.act_id
					ru_id = v.ru_id
				})

				if (this.act_type_ext > 1) {
					select_gift = this.checkGiftArr
				} else {
					select_gift = this.checkGiftStr
				}

				this.$store.dispatch('setGiftChecked', {
					act_id: act_id,
					ru_id: ru_id,
					select_gift: select_gift
				}).then(({
					data: data
				}) => {
					uni.showToast({
						title: data.message,
						icon: 'none'
					})
					if (data.error == 0) {
						this.goodsList()
						this.giftShow = false
					}
				})
			},
			//优惠券列表
			handleCoupon(ru_id) {
				this.couponShow = true

				this.goodsCartList.forEach(v => {
					if (v.store_id == ru_id) {
						this.coupuns_num = v.coupuns_num
					}
				})

				this.$store.dispatch('setCoupons', {
					ru_id: ru_id
				})
			},
			//优惠券领取
			handelReceive(val, ru_id) {
				this.$store.dispatch('setGoodsCouponReceive', {
					cou_id: val
				}).then(({
					data: data
				}) => {
					uni.showToast({
						title: data.msg,
						icon: 'none'
					})

					this.$store.dispatch('setCoupons', {
						ru_id: ru_id
					})
				})
			},
			//猜你喜欢
			goodsGuessHandle(page) {
				if (page) {
					this.page = page
					this.size = Number(page) * 10
				}

				this.$store.dispatch('setGoodsGuess', {
					warehouse_id: 0,
					area_id: 0,
					page: this.page,
					size: this.size
				})
			},
			//删除购物车商品
			deleteCartGoods(val, is_gift) {
				let that = this
				if (is_gift) {
					that.checkGift = ''
					that.checkGiftArr = []
				}

				uni.showModal({
					content: that.$t('lang.confirm_delete_goods'),
					success: function(res) {
						that.scrollLeft = 1
						if (res.confirm) {
							that.$store.dispatch('setCartGoodsDelete', {
								rec_id: val
							}).then(res => {
								if (res.error == 0) {
									uni.showToast({
										title: that.$t('lang.delete_success'),
										icon: 'success'
									})
									that.goodsList();
									that.changeGoods();
								} else {
									uni.showToast({
										title: that.$t('lang.delete_fail'),
										icon: 'fail'
									})
								}
							})
						}
					}
				})

				this.scrollLeft = 0;
			},
			//编辑
			onEdit() {
				this.batchEdit = this.batchEdit ? false : true
			},
			//批量删除
			onBatchDelete() {
				this.deleteCartGoods(this.checkedGoodsId);
				// this.$store.dispatch('setCartBatchDelete', {
				// 	rec_id: this.checkedGoodsId
				// }).then(res => {
				// 	if (res.error == 0) {
				// 		uni.showToast({
				// 			title: this.$t('lang.delete_success'),
				// 			icon: 'success'
				// 		});
				// 		this.goodsList()
				// 	} else {
				// 		uni.showToast({
				// 			title: this.$t('lang.delete_fail'),
				// 			icon: 'none'
				// 		})
				// 	}
				// })
			},
			//关闭弹出层
			handelClose(val) {
				if (val == 'gift') {
					this.giftShow = false
				} else if (val == 'favourable') {
					this.favourableShow = false
				} else if (val == 'coupon') {
					this.couponShow = false
				}
			},
			// 点击配件商品
			productLink(item){
				  if(item.extension_code == 'package_buy'){
					uni.navigateTo({
						url: '/pagesA/package/package'
					})
				  }else{
					uni.navigateTo({
						url: `/pagesC/goodsDetail/goodsDetail?id=${item.goods_id}`
					})
				  }
			},
			//提交购物车到结算页面
			onSubmit(val) {
				
				let subLogin = val ? true : false
				let fald = true
				
				//验证库存是否不足
				this.goodsCartList.forEach((f) => {
					f.new_list.forEach((act) => {
						act.act_goods_list.forEach((g) => {
							if (g.product_number == 0 && g.is_checked == true) {
								uni.showToast({
									title: g.goods_name + this.$t('lang.understock_not_submit'),
									icon: 'none'
								});
								fald = false
								return false
							}

							if (g.is_invalid == 1 && g.is_checked == true) {
								uni.showToast({
									title: g.goods_name + this.$t('lang.goods_soldout_not_submit'),
									icon: 'none'
								});
								fald = false
								return false
							}

							if (g.xiangou_error > 0 && g.is_checked == true) {
								let msg = ''
								if (g.xiangou_can_buy_num > 0) {
									msg = g.goods_name + this.$t('lang.purchase_only') + g.xiangou_can_buy_num + '件'
								} else {
									msg = g.goods_name + this.$t('lang.restriction_is_full')
								}
								uni.showToast({
									title: msg,
									icon: 'none'
								});
								fald = false
								return false
							}
						})
					})
				})

				if (this.checkedGoodsId.length > 0 && fald) {
					let rec_id = ''
					let rec_id_first = ''
					let arr = []
					
					this.goodsCartList.forEach((v, i) => {
						arr[i] = [];
						v.new_list.forEach((act) => {
							act.act_goods_list.forEach(goods => {
								if(goods.store_count > 0 && goods.is_checked > 0){
									arr[i].push(goods.rec_id)
								}
							})
						})
					})
					
					if(arr.length > 0){
						arr.forEach((v,i)=>{
							if(v.length == 0){
								arr.splice(i,1)
							}
						})
			
						if(arr[0]){
							arr[0].forEach(v=>{
								rec_id_first += v + ','
							})
						}

						rec_id = rec_id_first.substr(0, rec_id_first.length - 1)
					}

					if (this.$isLogin()) {
						if (rec_id) {
							uni.navigateTo({
								url: '/pages/checkout/checkout?type=true&rec_id='+rec_id
							})
						} else {
							uni.navigateTo({
								url: '/pages/checkout/checkout'
							})
						}
					} else {
						if(subLogin == true){
							this.getUserProfile('');
						}else{
							uni.showModal({
								content: this.$t('lang.login_user_not'),
								success: (res) => {
									if (res.confirm) {
										uni.navigateTo({
											url: '/pagesB/login/login?delta=1'
										})
									}
								}
							})
						}
					}
				}
			},
			shopStoreCart(index) {
				let rec_id = ''
				if (this.$isLogin()) {
					this.goodsCartList.forEach((v, i) => {
						if (index == i) {
							v.new_list.forEach((act) => {
								act.act_goods_list.forEach(goods => {
									if (goods.store_count > 0 && goods.is_checked > 0) {
										rec_id += goods.rec_id + ','
									}
								})
							})
						}
					})

					rec_id = rec_id.substr(0, rec_id.length - 1)

					if (rec_id.length > 0) {
						uni.navigateTo({
							url: '/pagesC/store/store?rec_id=' + rec_id
						})
					} else {
						uni.showToast({
							title: this.$t('lang.select_store_goods'),
							icon: 'none'
						});
					}
				} else {
					uni.showModal({
						content: this.$t('lang.login_user_not'),
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
			//自定义更新地区信息
			customUpdateRegion(e){
				this.regionData = e;
				
				//更新列表
				this.goodsList();
			},
			bindClick(e){
				console.log(e)
			}
		},
		onShow() {
			this.loadShowCart();
		},
		onLoad(e) {
			this.regionData = this.getRegionData;
			// #ifdef MP-WEIXIN
			this.menuButtonInfo = uni.getMenuButtonBoundingClientRect();
			// #endif
		},
		watch: {
			goodsCartList() {
				let obj = {}
				let arr = []
				let shopArr = []
				let shopArr2 = []
				let shopState = false
				let shopInvalid = false
				this.dscLoading = false

				if (this.default) {
					this.checkedGoodsId = []
					this.checkedGoods.forEach((v, index) => {
						v.forEach(i => {
							this.checkedGoodsId.push(i)
						})
					})

					this.allGoodsListId.forEach((v, index) => {
						arr[index] = []

						v.forEach(i => {
							obj = {
								id: i,
								type: false
							}

							arr[index].push(obj)
						})
					})

					this.stepDisabled = arr

					//初始化选中商品
					this.changeDefaultGooods()
				}

				this.checkedAllDisabled = false
				
				this.goodsCartList.forEach((v, index) => {
					shopArr[index] = []
					shopState = false
					let cont = 0
					v.new_list.forEach((act) => {
						act.act_goods_list.forEach(goods => {
							//选中门店
							if (goods.store_count > 0) {
								shopState = true
							}
							
							if (goods.is_invalid > 0) {
								cont++
								this.checkedAllDisabled = true
							}
						})
					})
					shopArr[index].push(shopState);
					shopArr2.push(cont > 0 ? true : false);
				})

				//门店自提按钮
				this.shopStore = shopArr
				//店铺下商品是否失效
				this.shopInvalidArr = shopArr2
			},
			count() {
				//获取购物车数量
				//this.$store.dispatch('setCommonCartNumber');

				if (this.count > 0) {
					this.disabled = false
				} else {
					this.disabled = true
				}
			},
			regionShow() {
				if (this.regionShow) {
					this.regionLoading = true
				}
			}
		}
	}
</script>

<style lang="scss">
	/* 导航 start */
		.nav_bar {
			.status_bar {
				height: var(--status-bar-height);
				width: 100%;
			}
		}
	/* 导航 end */
	
	::-webkit-scrollbar{
		display: none;
	}
	
	.activity-tag {
		position: absolute;
		top: 0;
		left: 0;
		width: 50%;
	}
	
	.parts-tag {
		left: -6rpx;
	}
	
	/* .container{ padding-bottom: 300upx;} */
	.header-address {
		display: flex;
		flex-direction: row;
		justify-content: space-between;
		padding: 20upx;
	}

	.header-address .address-box {
		display: flex;
		flex-direction: row;
	}

	.header-address .address-box .iconfont {
		margin-right: 10upx;
	}

	.card-goods {}

	.card-goods-group {
		margin: 0 20rpx 20rpx;
		padding: 20rpx 0 20rpx 20rpx;
		background: #FFFFFF;
		border-radius: 20rpx;
	}

	.card-shop-head {
		display: flex;
		flex-direction: row;
		padding: 0 0 20upx;
		justify-content: space-between;
	}

	.card-shop-head .head-left,
	.card-shop-head .head-right {
		display: flex;
		flex-direction: row;
	}


	.checkbox-icon .uni-icon {
		display: block;
	}

	.card-shop-box {
		
	}

	.card-act-goods {
		display: flex;
		flex-direction: column;
		margin-bottom: 20upx;
	}

	.card-act-goods:last-child {
		margin-bottom: 0;
	}

	.cart-act-title {
		display: flex;
		flex-direction: row;
		justify-content: space-between;
		align-items: center;
		padding: 0 20rpx 20upx 60upx;
	}

	.cart-act-title .txt {
		display: flex;
		flex-direction: row;
		align-items: center;
		color: #333333;
	}

	.cart-act-title .txt .em-icon {
		margin-right: 20upx;
	}

	.cart-act-title .txt .act-name view {
		font-size: 25upx;
		overflow: hidden;
		white-space: nowrap;
		text-overflow: ellipsis;
	}

	.cart-act-title .more {
		display: flex;
		flex-direction: row;
		color: #f92028;
		align-items: center;
	}

	.cart-goods-item {
		display: flex;
		flex-direction: row;
		overflow: hidden;
		width: 100%;
	}

	.cart-goods-item .checkbox-con {
		// border-bottom: 1px solid #E5E5E5;
	}

	.cart-goods-item:last-child .checkbox-con {
		border-bottom: 0;
	}

	.cart-goods-info {
		background-color: #FFFFFF;
		padding: 20upx 0;
		display: flex;
		flex-direction: row;
	}

	.cart-goods-info .goods-img {
		width: 180upx;
		height: 180upx;
		position: relative;
		border-radius: 10rpx;
		overflow: hidden;
	}

	.cart-goods-info .image {
		width: 180upx;
		height: 180upx;
	}

	.cart-goods-info .goods-img .icon {
		position: absolute;
		width: 80upx;
		height: 80upx;
		top: 0;
		left: 0;
	}

	.cart-goods-info .goods-img .icon image {
		width: 100%;
		height: 100%;
	}

	.cart-goods-info .goods-img .mask {
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		position: absolute;
		z-index: 99;
		background: rgba(0, 0, 0, .7);
	}

	.cart-goods-info .goods-img .mash-text {
		width: 100%;
		color: #fff;
		text-align: center;
		z-index: 100;
		position: absolute;
		top: 0;
		bottom: 0;
		line-height: 180upx;
	}

	.cart-goods-item-outer .cart-goods-info {
		background: #fff9f8;
	}

	.cart-goods-item-outer .checkbox-con {
		padding-left: 60upx;
	}

	.cart-goods-content {
		flex: 1 1 0%;
		margin-left: 20upx;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
	}

	.cart-goods-content .goods-title {
		font-size: 28upx;
		margin-bottom: 10upx;
	}

	.cart-goods-content .goods-attr {
		color: #999999;
		font-size: 25upx;
	}

	.cart-goods-content .goods-info {
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: space-between;
	}

	.cart-goods-content .goods-info .price {
		color: #f92028;
	}

	.cart-goods-content .goods-info .stepper_sum {
		color: #999999;
	}

	.cart-goods-content .goods-other {
		width: 100%;
		display: flex;
		flex-direction: row;
		align-items: center;
		margin-top: 15upx;
		position: relative;
	}

	.cart-goods-content .goods-other .em-icon {
		padding: 0 3px;
		margin-right: 2px;
	}

	.cart-goods-content .goods-other .act-name {
		width: calc(100% - 45px);
		font-size: 25upx;
		color: #999999;
	}

	.cart-goods-content .goods-other .uni-icon {
		position: absolute;
		right: 0;
		top: 2px;
	}

	.scroll-view-G {
		display: flex;
		align-items: center;
		width: 100%;
		white-space: nowrap;
		overflow: hidden;
	}

	.scroll-view-item {
		width: 100%;
		height: 100%;
		display: inline-block;
		vertical-align: middle;
		box-sizing: border-box;
	}

	.scroll-view-item-right {
		width: 20%;
		margin-left: 10px;
		height: 100%;
		line-height: 100%;
		text-align: center;
		background: #f92028;
		color: #FFFFFF;
		position: relative;
	}

	.scroll-view-item-right view {
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.em-icon {
		width: 25px;
		height: 20px;
		line-height: 20px;
		padding: 0 5px;
		border-radius: 10px;
		color: #FFFFFF;
		background: #f92028;
		font-size: 22upx;
		text-align: center;
	}

	.em-icon.active-zeng {
		background-color: #9251e5;
	}

	.em-icon.active-zhekou {
		background-color: #fc9b1f;
	}

	.cart-submit {
		width: 100%;
		display: flex;
		flex-direction: row;
		background-color: #FFFFFF;
		position: fixed;
		bottom: 0;
		left: 0;
		z-index: 99;
		align-items: center;
		border-top: 1px solid #e3e8ee;
	}

	.cart-submit .checkall {
		padding: 0 20upx;
	}

	.cart-submit .checkall .checkbox .checkbox-icon {
		margin-right: 20upx;
	}

	.cart-submit .submit-bar-text {
		text-align: right;
		width: 350upx;
		margin-right: 20upx;
	}

	.cart-submit .submit-bar-text view {
		line-height: 1.5;
	}

	.cart-submit .submit-bar-text text {
		margin-right: 10upx;
	}

	.cart-submit .submit-bar-price {
		display: inline-block;
		color: #f92028;
		font-weight: 500;
		font-size: 32upx;
	}

	.cart-submit .submit-bar-sub {
		font-size: 25upx;
		color: #999999;
	}

	.flow-no-cart {
		background: #FFFFFF;
		margin: 20upx;
		border-radius: 10upx;
		text-align: center;
		overflow: hidden;
	}

	.flow-no-cart .gwc-bg {
		display: flex;
		width: 180upx;
		height: 180upx;
		border-radius: 50%;
		background: #fcfcfc;
		justify-content: center;
		align-items: center;
		border: 1px solid #d9d9d9;
		margin: 40px auto 20px;
	}

	.flow-no-cart .gwc-bg .iconfont {
		font-size: 40px;
		color: #d9d9d9;
	}

	.flow-no-cart text {
		color: #333333;
		font-size: 30upx;
	}

	.flow-no-cart .card-btn {
		display: flex;
		flex-direction: row;
		justify-content: center;
		align-items: center;
		margin: 20px 0 40px;
	}

	.flow-no-cart .card-btn button {
		margin: 0 5px;
		border-color: #d9d9d9;
		font-size: 16px;
	}

	.goods-detail-guess {
		padding-bottom: 120upx;
	}

	.act-item-right {
		display: flex;
		flex-direction: row;
		align-content: center;
		padding: 10upx 20upx 10upx 0;
	}

	.act-item-right .em-icon {
		margin-right: 20upx;
	}

	.act-item-right .act-name {
		font-size: 25upx;
		color: #999999;
	}

	.activity-popup .cart-goods-item {
		min-height: initial;
	}

	.goods-store {
		background: #FFFFFF;
		padding: 10upx 0;
		text-align: right;
		font-size: 25upx;
	}

	.shop-store-cart {
		padding: 0 20rpx 0;
		display: flex;
		justify-content: flex-end;
		align-items: flex-end;
	}

	.shop-store-cart .store-btn {
		border: 1px solid #f42424;
		color: #f42424;
		line-height: normal;
		display: flex;
		flex-direction: row;
		justify-content: center;
		align-items: center;
		padding: 10upx 20upx;
	}

	.shop-store-cart .store-btn .iconfont {
		vertical-align: top;
		margin: -16upx 10upx 0 0;
	}

	/* #ifdef APP-PLUS */
	.app-header-top {
		padding-top: 80upx;
	}
	/* #endif */
	/* #ifdef MP-WEIXIN */
	.app-header-top {
		padding-top: calc(var(--status-bar-height) - 20rpx + 44px);
	}
	/* #endif */
	
	/*loading*/
	.loading-transparent{ position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;}
	.loading-transparent /deep/ .uni-load-more{ z-index: 999; top: 50%; left: 50%; position: absolute; margin: -20px 0 0 -20px; width: 40px;}
	.loading-transparent .loading-overlay{ position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 998;}
	
	.cart-submit .btn-bar .btn-disabled{ background: linear-gradient(178deg,#f91f28,#ff4f2e); opacity: .5; color: #fff;}
	
	.country_icon{
		width: 43rpx;
		height: 30rpx;
		padding-right: 7rpx;
		position: relative;
		top: 5rpx;
	}
	
	uni-swipe-action{
		margin-bottom: 10px;
	}
	.uni-swipe_text--center{
		margin-right: 10px;
	}
	
	.pop_content .pop_header{
		text-align: center;
	}
	.pop_content .pop_header .pop_title {
		font-size: 32rpx;
		font-weight: 700;
		line-height: 90rpx;
		color: #282828;
	}
	
	.pop_phone_number{
		position: relative;
		width: 420rpx;
		height: 400rpx;
	}
	.pop_phone_number .pop_header{
		width: 100%;
		border-bottom: 1px solid #ECECEC;
	}
	.pop_phone_number .logo{
		padding-top:20rpx;
		width: 100rpx;
		height: 100rpx;
		margin: 0 auto;
	}
	.pop_phone_number .logo .img{
		border-radius: 50%;
	}
	.pop_phone_number .pop_phone_content{
		margin: 0 auto;
		text-align: center;
		line-height: 45px;	
	}
	.pop_phone_number .btn{
		margin: 0 auto;
		width: 70%;
	}
</style>
