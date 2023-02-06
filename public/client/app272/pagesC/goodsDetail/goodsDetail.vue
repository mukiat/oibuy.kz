<template>
	<view class="goods_detail_content">
		<!-- 导航栏 start -->
		<view class="nav_bar" :style="{backgroundColor: rgba}">
			<!-- #ifdef APP-PLUS -->
			<!-- 这里是状态栏 -->
			<view class="status_bar"></view>
			<view class="nav_list">
				<view class="nav_l icon_wrap" :style="{backgroundColor: navIconRgba, color: navOpacity == 1 ? '#000' : '#fff'}" @click="goBack">
					<text class="iconfont icon-find-fanhui"></text>
				</view>
				<view class="nav_c" :style="{opacity: navOpacity}">
					<view :class="['nav_item', ind == currNav ? 'active_nav' : '']" v-for="(nav, ind) in navBar" :key="ind" @click="changeNav(ind)">{{nav.title}}</view>
				</view>
				<view class="nav_r">
					<view class="icon_wrap" :style="{backgroundColor: navIconRgba, color: navOpacity == 1 ? '#000' : '#fff'}" @click="collection" v-if="collectStatus == 0"><text class="iconfont icon-guanzhu"></text></view>
					<view class="icon_wrap" :style="{backgroundColor: followIconRgba, color: navOpacity == 1 ? '#F91F28' : '#fff'}" @click="collection" v-else><text class="iconfont icon-guanzhu"></text></view>
					<view class="icon_wrap" :style="{backgroundColor: navIconRgba, color: navOpacity == 1 ? '#000' : '#fff'}" @click="showShortcut = !showShortcut">
						<text class="iconfont icon-gengduo1"></text>
						<view class="shortcut" v-if="showShortcut">
							<view :class="['shortcut_item', index > 0 ? 'u-border-top' : '']" @click="$outerHref(item.path, 'app')" v-for="(item, index) in shortcutData" :key="index">
								<text :class="[item.ico]"></text>
								<text>{{ item.name }}</text>
							</view>
						</view>
					</view>
				</view>
			</view>
			<!-- #endif -->
			<!-- #ifdef MP-WEIXIN -->
			<view class="wx_nav_bar" :style="{paddingTop: `${menuButtonInfo.top}px`}">
				<view class="wx_nav_left" :style="{left: `${vw - menuButtonInfo.width - menuButtonInfo.left}px`, width: `${menuButtonInfo.width}px`, height: `${menuButtonInfo.height}px`, borderRadius: `${menuButtonInfo.height / 2}px`}">
					<text class="iconfont icon-find-fanhui" @click="goBack"></text>
					<text class="line"></text>
					<uni-icons type="bars" size="30" @click="showShortcut = !showShortcut"></uni-icons>
					<view class="shortcut" v-if="showShortcut">
						<view :class="['shortcut_item', index > 0 ? 'u-border-top' : '']" @click="$outerHref(item.path, 'app')" v-for="(item, index) in shortcutData" :key="index">
							<text :class="[item.ico]"></text>
							<text>{{ item.name }}</text>
						</view>
					</view>
				</view>
				<view class="nav_c" :style="{opacity: navOpacity}">
					<view :class="['nav_item', ind == currNav ? 'active_nav' : '']" v-for="(nav, ind) in navBar" :key="ind" @click="changeNav(ind)">{{nav.title}}</view>
				</view>
			</view>
			<!-- #endif -->
		</view>
		<!-- 导航栏 start -->
		
		<!-- 轮播图 start -->
		<view class="swiper_warp">
			<view v-show="!showVideo">
				<view class="swiper_warp_layer">
					<swiper class="swiper" :circular="true" :current="current" :interval="3000" :duration="1000" @change="changeSwiper">
						<swiper-item v-for="(item, index) in swiperData" :key="index">
							<view @click="previewImg(index, swiperData.map(val => val.img_url))">
							<image class="swiper_img" :src="item.img_url"></image>
							<view class="goods-label-suspension" v-if="index == 0 && goodsDetail.goods_label_suspension && goodsDetail.goods_label_suspension.formated_label_image">
								<image :src="goodsDetail.goods_label_suspension.formated_label_image" class="img"></image>
							</view>
							</view>
						</swiper-item>
					</swiper>
					<view class="indicator_dots">
						<text class="curr_dots">{{ currSwiperPic + 1 }}</text>/{{swiperData.length}}
					</view>
					<view class="video_ico" @click="play" v-if="goodsDetail.goods_video">
						<text class="iconfont icon-video2 play_ico"></text>
						<view class="play_txt">{{$t('lang.goods_watchvideo')}}</view>
					</view>
				</view>
			</view>
			
			<!-- 视频 start -->
			<template v-if="goodsDetail.goods_video">
				<view class="video_wrap" v-show="showVideo">
					<video class="video" id="goodsVideo" :src="goodsDetail.goods_video" @error="videoErrorCallback"></video>
					<view class="controls_pause_wrap" @click="pause">
						<view class="controls_pause">{{$t('lang.goods_outvideo')}}</view>
					</view>
				</view>
			</template>
			<!-- 视频 end -->
		</view>
		<!-- 轮播图 end -->
		
		<!--属性图片相册切换-->
		<view class="goods-attr-img-list" v-if="attrColorList.length > 0">
			<view class="describe">{{ attrColorList.length }}{{$t('lang.goods_color_option')}}</view>
			<scroll-view class="scroll-view" scroll-x="true" scroll-left="0" :current="attrColorIndex">
				<view class="scroll-view-item" :class="{'active':attrColorIndex == index}" v-for="(item, index) in attrColorList" :key="index" v-if="item.img_flie">
					<image class="img" mode="aspectFill" :lazy-load="true" :src="item.img_flie" @click="onSelectImg(item,index)"></image>
				</view>
			</scroll-view>
		</view>
		
		<!-- 商品基本信息 start -->
		<view class="activity_goods" v-if="goodsDetail.promote_end_date > 0 && goodsDetail.promote_start_date > 0">
			<view class="activity_goods_pic"><image :src="imagePath.activity_goods_01" class="img" mode="widthFix"></image></view>
			<view class="activity_goods_left">
				<currency-price
					:price="goodsDetail.shop_price"
					:delPrice="goodsDetail.marketPrice"
					:size="25"
					:delSize="16"
					color="#fff"
					delColor="#fff"
					v-if="goodsDetail.shop_price"
				></currency-price>
			</view>
			<view class="activity_goods_right">
				<view class="count_down_txt">{{$t('lang.promote_end')}}</view>
				<uni-countdown fontColor="#FFFFFF" :mini="true" :show-colon="false" splitorColor="#FF3616" borderColor="#FF3616" bgrColor="#FF3616" :timer="dateTime" v-if="dateTime"></uni-countdown>
			</view>
		</view>
		<view class="goods_module_wrap goods_base_info">
			<view class="goods_price" v-if="goodsDetail.promote_end_date == 0 && goodsDetail.promote_start_date == 0">
				<view class="wrap_box">
					<currency-price
						:price="goodsDetail.shop_price"
						:delPrice="goodsDetail.marketPrice"
						:size="24"
						:delSize="14"
						v-if="goodsDetail.shop_price"
					></currency-price>
					<text class="yishou color_999">{{$t('lang.goods_soltout')}} {{ goodsDetail.sales_volume }} {{$t('lang.jian')}}</text>
				</view>
				
				<!-- 分享 -->
				<block v-if="controlVersion">
					<view class="right">
						<view class="goods_share" v-if="goodsDetail.is_show_drp == 1 && goodsDetail.commission_money != 0 && goodsDetail.is_distribution == 1" @click="mpShare">
							<text class="iconfont icon-share" style="line-height: 1;"></text>
							<text class="share_txt">{{$t('lang.share_zhuan_alt')}} {{ goodsDetail.commission_money }}</text>
						</view>
						<view class="goods_share" v-else @click="mpShare">
							<text class="iconfont icon-share" style="line-height: 1;"></text>
							<text class="share_txt">{{ $t('lang.share') }}</text>
						</view>
						<!-- #ifdef MP-WEIXIN -->
						<view class="collection" @click="collection">
							<text class="iconfont icon-guanzhu" style="line-height: 1;" :class="{'uni-red':collectStatus == 1}"></text>
							<text class="share_txt" :style="{'color':collectStatus == 1 ? '#f92028' : '#2E2E2E'}">{{ collectStatus == 1 ? '已收藏' :'收藏'}}</text>
						</view>
						<!-- #endif -->
					</view>
				</block>
			</view>
			
			<view class="goods_price goods_rate" v-if="goodsDetail.ru_id > 0 && goodsDetail.is_kj == 1">
				<view class="wrap_box" v-if="goodsDetail.goods_rate > 0">
					<span class="sold_out rate">{{$t('lang.import_tax')}}</span>
					<currency-price
						:price="goodsRate"
						:size="12"
					></currency-price>
				</view>
				<view class="wrap_box" v-else>
					<span class="sold_out rate">{{$t('lang.import_tax')}}：{{$t('lang.goods_tax_included')}}</span>
				</view>
			</view>	
			
			<!-- 商品标签 -->
			<view :class="['label-list', goodsDetail.promote_end_date > 0 && goodsDetail.promote_start_date > 0 ? 'padding_top20' : '']" v-if="goodsDetail.goods_label && goodsDetail.goods_label.length > 0">
				<view class="label-img" v-for="(item, index) in goodsDetail.goods_label" :key="index"><image :src="item.formated_label_image" mode="heightFix"></image></view>
			</view>
			
			<!-- 商品名称 -->
			<view class="goods_name_wrap">
				<view class="goods_name">
					<text class="ziying" v-if="goodsDetail.is_kj == 1" style="background:#7a45e5">{{$t('lang.cross_goods')}}</text>
					<view class="country_icon_text"><image class="country_icon" :src="goodsDetail.country_icon" :lazy-load="true" v-if="goodsDetail.country_icon"></image><text class="icon_text_name">{{goodsDetail.country_name}}</text></view>
					<view>
						<text class="ziying" v-if="goodsDetail.user_id == 0">{{$t('lang.self_support')}}</text>
						{{goodsDetail.goods_name}}
					</view>
				</view>
				<!-- 分享 -->
				<block v-if="controlVersion && goodsDetail.promote_end_date > 0 && goodsDetail.promote_start_date > 0">
					<view class="right">
						<view class="goods_share" v-if="goodsDetail.is_show_drp == 1 && goodsDetail.commission_money != 0 && goodsDetail.is_distribution == 1" @click="mpShare">
							<text class="iconfont icon-share" style="line-height: 1;"></text>
							<text class="share_txt">{{$t('lang.share_zhuan_alt')}} {{ goodsDetail.commission_money }}</text>
						</view>
						<view class="goods_share" v-else @click="mpShare">
							<text class="iconfont icon-share" style="line-height: 1;"></text>
							<text class="share_txt">{{ $t('lang.share') }}</text>
						</view>
						<!-- #ifdef MP-WEIXIN -->
						<view class="collection" @click="collection">
							<text class="iconfont icon-guanzhu" style="line-height: 1;" :class="{'uni-red':collectStatus == 1}"></text>
							<text class="share_txt" :style="{'color':collectStatus == 1 ? '#f92028' : '#2E2E2E'}">{{ collectStatus == 1 ? '已收藏' :'收藏'}}</text>
						</view>
						<!-- #endif -->
					</view>
				</block>
			</view>
			
			
			<!-- 商品说明 -->
			<view class="goods_explain size_24 color_999" v-if="goodsDetail.goods_brief">{{goodsDetail.goods_brief}}</view>
			
			<!-- vip 开通 -->
			<view class="open_vip" v-if="goodsDetail.is_show_drp == 1 && goodsDetail.is_drp && goodsDetail.drp_shop == 0 && controlVersion">
				<image class="vip_ico" src="/static/vip/icon-vip.png"></image>
				<view class="vip_content" v-if="goodsDetail.membership_card_discount_price > 0">{{ $t('lang.advanced_vip') }}<text class="vip_price">{{ goodsDetail.membership_card_discount_price_formated }}</text></view>
				<view class="vip_content" v-else>{{ $t('lang.advanced_vip_on') }}</view>
				<view 
				class="go_open"
				v-if="goodsDetail.drp_shop_membership_card_id && goodsDetail.drp_shop_membership_card_id > 0"
				@click="$outerHref('/pagesA/drp/register/register?apply_status=repeat&membership_card_id=' + goodsDetail.drp_shop_membership_card_id, $isLogin())"
				>{{ $t('lang.re_purchase') }}<text class="iconfont icon-more size_24"></text></view>
				<view class="go_open" @click="$outerHref('/pagesA/drp/register/register', $isLogin())" v-else>{{ $t('lang.immediately_opened') }}<text class="iconfont icon-more size_24"></text></view>
			</view>
		</view>
		<!-- 商品基本信息 end -->
		
		<!-- 优惠与活动 start -->
		<view class="goods_module_wrap mt_20" v-if="formatDiscountsData.length > 0 || activityData.length > 0">
			<view class="activity_wrap" @click="showPopup('discounts')" v-if="formatDiscountsData.length > 0">
				<view class="activity_title">{{$t('lang.goods_discounts')}}</view>
				<view class="activity_main">
					<view class="activity_list">
						<view class="activity_item">
							<view class="title_ico"><image class="img" src="/static/lower_price.png" mode="widthFix"></image></view>
							<text>{{$t('lang.activities_option')}}</text>
						</view>
						<view class="activity_item activity_item_flex" v-for="(activityItem, activityIndex) in formatDiscountsData" :key="activityIndex">
							<block v-if="activityIndex < 2">
								<text class="activity_bg">{{activityItem.label}}</text>
								<text class="activity_tips" v-if="activityItem.acttype != 0">{{activityItem.value}}</text>
								<block v-if="activityItem.acttype == 0">
									<text class="coupon_bg_wrap" v-for="(item, index) in activityItem.value" :key="index"><text class="coupon_bg">{{$t('lang.man')}}{{ item.cou_man }}{{$t('lang.minus')}}{{item.cou_money}}</text></text>
								</block>
							</block>
							<block v-else>
								<text class="activity_bg" v-for="(item, index) in activityItem" :key="index">{{item.label}}</text>
							</block>
						</view>
					</view>
					<text class="iconfont icon-gengduo2"></text>
				</view>
			</view>
			<view class="activity_wrap u-border-top" @click="showPopup('activity')" v-if="activityData.length > 0">
				<view class="activity_title">{{$t('lang.goods_activity')}}</view>
				<view class="activity_main">
					<view class="activity_list">
						<view class="activity_item activity_item_flex" v-for="(item, index) in activityData" :key="index">
							<text :class="['activity_bg', index == 0 ? 'margin_top0' : '']">{{item.label}}</text>
							<text class="activity_tips">{{item.value}}</text>
						</view>
					</view>
					<text class="iconfont icon-gengduo2"></text>
				</view>
			</view>
		</view>
		<!-- 优惠与活动 end -->
		
		<!-- 已选-服务 statr -->
		<view class="goods_module_wrap mt_20">
			<view class="activity_wrap" @click="skuLink">
				<view class="activity_title">{{$t('lang.label_selected2')}}</view>
				<view class="activity_main">
					<view class="activity_list">
						<view class="activity_item activity_item_flex">
							<text class="activity_tips">{{selectAttr}}</text>
						</view>
					</view>
					<text class="iconfont icon-gengduo2"></text>
				</view>
			</view>
			
			<view class="activity_wrap u-border-top" v-if="goodsDetail.is_kj == 1 && goodsDetail.cross_warehouse_name != ''">
				<view class="activity_title">{{$t('lang.place_of_shipment')}}</view>
				<view class="activity_main">{{goodsDetail.cross_warehouse_name}}</view>
			</view>
			
			<view class="activity_wrap u-border-top" @click="showPopup('address')">
				<view class="activity_title">{{$t('lang.label_send_to_2')}}</view>
				<view class="activity_main">
					<view class="activity_list">
						<view class="activity_item">
							<image class="location_ico" src="/static/location_ico.png" mode="heightFix"></image>
							{{regionSplic}}
						</view>
					</view>
					<text class="iconfont icon-gengduo2"></text>
				</view>
			</view>
			<view class="activity_wrap u-border-top" @click="handleStore" v-if="shipping_fee.store_count > 0 && shipping_fee.store_info">
				<view class="activity_title">{{$t('lang.store')}}</view>
				<view class="activity_main">
					<view class="activity_list">
						<view class="activity_item">
							<image class="location_ico" src="/static/store_ico.png" mode="heightFix"></image>
							{{shipping_fee.store_info.stores_name}}
							<view class="address_text">{{shipping_fee.store_info.stores_address}}</view>
						</view>
					</view>
					<text class="iconfont icon-more size_24"></text>
				</view>
			</view>
			<view class="activity_wrap u-border-top">
				<view class="activity_title">{{$t('lang.label_freight2')}}</view>
				<view class="activity_main">
					<view class="activity_list">
						<view class="activity_item activity_item_flex">
							<text class="activity_tips" v-if="goodsDetail.is_shipping == 1 || freeShipping == 1" style="color: #AF743A;">{{$t('lang.pinkage')}}</text>
							<text class="activity_tips" v-else>{{freight}}<text class="uni-red" v-if="shipping_fee.shipping_title && shipping_fee.shipping_title != 0">({{ shipping_fee.shipping_title }})</text></text>
						</view>
					</view>
				</view>
			</view>
			<view class="activity_wrap server_wrap u-border-top" v-if="goodsDetail.goods_services_label && goodsDetail.goods_services_label.length" @click="showPopup('server')">
				<view class="activity_title">{{$t('lang.service')}}</view>
				<view class="activity_main">
					<view class="activity_list">
						<view class="activity_item activity_item_flex">
							<view class="server_item" v-for="(item, index) in goodsDetail.goods_services_label" :key="index">
								<image class="location_ico" :src="item.formated_label_image" mode="heightFix"></image>
								<text class="text">{{item.label_name}}</text>
							</view>
						</view>
					</view>
					<text class="iconfont icon-gengduo2"></text>
				</view>
			</view>
		</view>
		<!-- 已选-服务 end -->
		
		<!-- 商品评价 start -->
		<view class="goods_module_wrap mt_20 comment_el" id="comment_id">
			<view class="title_box" @click="$outerHref(`/pagesC/comment/comment?id=${goodsId}`, 'app')">
				<view class="title_left">
					<text>{{commentTotal.total > 0 ? $t('lang.comment') : $t('lang.no_comment')}}</text>
					<text class="comment_count" v-if="commentTotal.total > 0">{{commentTotal.total}}{{$t('lang.tiao')}}</text>
				</view>
				<view class="title_right">
					<text class="drgree_of_praise color_999" v-if="commentTotal.total > 0">{{$t('lang.high_praise')}}{{commentTotal.good}}</text>
					<text class="iconfont icon-more size_24"></text>
				</view>
			</view>
			<view class="comment-items" v-if="goodsComment.length > 0">
				<view class="comitem" v-for="(item, index) in goodsComment" :key="index">
					<view class="item_header">
						<image class="head_l" :lazy-load="true" :src="item.user_picture" v-if="item.user_picture"></image>
						<image class="head_l" src="/static/get_avatar.jpg" v-else></image>
						<view class="head_r">
							<view class="com_name">{{item.user_name}}</view>
							<view class="com_time">
								<view class="rate_wrap">
									<text :class="['iconfont', 'icon-collection-alt', 'size_24', rIndex < item.rank ? 'color_red' : '']" v-for="(rate, rIndex) in 5" :key="rIndex"></text>
								</view>
								<text class="comment_time">{{ item.add_time }}</text>
							</view>
						</view>
					</view>
					<view class="item_body">
						<view class="comment_con">{{ item.content }}</view>
						<scroll-view class="scroll-view" scroll-x="true" v-if="item.comment_img">
							<view class="imgs_scroll">
								<block v-for="(val, ind) in item.comment_img" :key="ind">
									<image class="com_img" mode="aspectFill" :lazy-load="true" :src="val" @click="previewImg(ind, item.comment_img)" v-if="val"></image>
									<image class="com_img" mode="aspectFill" src="/static/no_image.jpg" v-else></image>
								</block>
							</view>
						</scroll-view>
					</view>
					<view class="item_footer" v-if="item.goods_attr">{{item.goods_attr}}</view>
				</view>
				<view class="flex_box jc_center">
					<view class="goods_module_btn" @click="$outerHref(`/pagesC/comment/comment?id=${goodsId}`, 'app')">{{$t('lang.view_all_comments')}}</view>
				</view>
			</view>
			<!-- #ifdef APP-PLUS -->
			<view class="title_box u-border-top" @click="$outerHref(`/pagesC/discover/list/list?id=${goodsId}`, 'app')">
				<view class="title_left">
					<text>{{$t('lang.discuss_circle')}}</text>
				</view>
				<view class="title_right">
					<text class="drgree_of_praise color_999">{{$t('lang.view_all')}}</text>
					<text class="iconfont icon-more size_24"></text>
				</view>
			</view>
			<view class="friend_discuss" v-if="friendDis.length > 0">
				<view class="discuss_item" v-for="(item, index) in friendDis" :key="index">
					<text class="dis_label" v-if="item.dis_type == 1">{{$t('lang.discuss')}}</text>
					<text class="dis_label" v-else>{{$t('lang.interlocution')}}</text>
					<text class="dis_value">{{item.dis_title}}</text>
					<text class="dis_time">{{item.add_time}}</text>
				</view>
			</view>
			<view class="no_dis" v-else>
				<view class="no_dis_title">{{$t('lang.no_discuss_tops')}}</view>
				<view class="goods_module_btn" @click="$outerHref(`/pagesC/discover/list/list?id=${goodsId}`, 'app')">{{$t('lang.initiate_a_topic')}}</view>
			</view>
			<!-- #endif -->
		</view>
		<!-- 商品评价 end -->
		
		<!-- 店铺 start -->
		<view class="goods_module_wrap mt_20" v-if="shopDetail.shop_name && controlVersion">
			<view class="store_hade" @click="$outerHref(`/pages/shop/shopHome/shopHome?ru_id=${shopDetail.ru_id}`,'app')">
				<image class="store_logo" :src="shopDetail.shop_logo"></image>
				<view class="store_name_rate">
					<view class="sto_name">{{shopDetail.shop_name}} <text v-if="goodsDetail.cross_source" style="color: #AF743A; font-weight: normal; font-size: 24rpx; padding-left: 10rpx;">{{ goodsDetail.cross_source }}</text></view>
					<view class="sto_rate_wrap">
						<view class="sto_rate">
							<text>{{$t('lang.store_star')}}</text>
							<text :class="['iconfont', 'icon-collection-alt', 'size_24', rIndex < storeRate ? 'color_red' : '']" v-for="(rate, rIndex) in 5" :key="rIndex"></text>
						</view>
					</view>
				</view>
				<text class="iconfont icon-more size_24"></text>
			</view>
			<view class="store_body">
				<view class="count_item">
					<view class="count_text">{{shopDetail.count_gaze}}</view>
					<view>{{$t('lang.follow_number')}}</view>
				</view>
				<view class="line_wrap u-border-left"></view>
				<view class="count_item">
					<view class="count_text">{{shopDetail.count_goods}}</view>
					<view>{{$t('lang.all_goods')}}</view>
				</view>
				<view class="line_wrap u-border-left"></view>
				<view class="count_item">
					<view class="serve_rate">
						<text>{{$t('lang.comment')}}</text>
						<text :class="[shopDetail.commentrank.slice(0, 1) < 3 ? 'color_green' : 'color_red']">{{shopDetail.commentrank}} {{shopDetail.commentrank_font}}</text>
					</view>
					<view class="serve_rate">
						<text>{{$t('lang.logistics')}}</text>
						<text :class="[shopDetail.commentdelivery.slice(0, 1) < 3 ? 'color_green' : 'color_red']">{{shopDetail.commentdelivery}} {{shopDetail.commentdelivery_font}}</text>
					</view>
					<view class="serve_rate">
						<text>{{$t('lang.after_sales')}}</text>
						<text :class="[shopDetail.commentserver.slice(0, 1) < 3 ? 'color_green' : 'color_red']">{{shopDetail.commentserver}} {{shopDetail.commentserver_font}}</text>
					</view>
				</view>
			</view>
			<view class="store_footer">
				<view class="store_btn" :class="{'active':followShopStatus == 1}" @click.stop="collectHandle(shopDetail.ru_id)">
					<image class="store_btn_ico" :src="followShopStatus == 1 ? '/static/store_follow2.png' : '/static/store_follow.png'" mode="heightFix"></image>
					{{followShopStatus == 1 ? $t('lang.followed') : $t('lang.attention_store')}}
				</view>
				<view class="store_btn" @click="$outerHref(`/pages/shop/shopHome/shopHome?ru_id=${shopDetail.ru_id}`,'app')">
					<image class="store_btn_ico" src="/static/into_shop.png" mode="heightFix"></image>
					{{$t('lang.go_shopping')}}
				</view>
			</view>
		</view>
		<!-- 店铺 end -->
		
		<!-- 搜索 start -->
		<view class="goods_module_wrap mt_20 recomment_el" id="recomment_id">
			<view class="search_wrap" @click="$outerHref('/pages/search/search', 'app')">
				<view class="search_input">
					<text class="iconfont icon-home-search"></text>
					{{$t('lang.search_goods_placeholder')}}
				</view>
				<view class="search_btn">{{$t('lang.search')}}</view>
			</view>
			<view class="top_search" v-if="search_keywords.length > 0">
				<view class="top_search_lable">{{$t('lang.hot_search')}}：</view>
				<view class="top_search_keyword" v-for="(item, index) in search_keywords" :key="index" @click="$outerHref(`/pages/goodslist/goodslist?keywords=${item}`, 'app')">{{item}}</view>
			</view>
			<view class="recomment u-border-top" v-if="recommentTabs.length > 0">
				<view class="re_tabs">
					<view :class="['re_tabs_item', currGoodsList == index ? 'active_tab' : '']" v-for="(item, index) in recommentTabs" :key="index" @click="changeGoodsTab(index)">{{item.title}}</view>
				</view>
				<view class="goods_list_wrap"  v-for="(item, index) in goodsGuessList" :key="index">
					<block v-if="currGoodsList == index">
						<swiper :class="['swiper', item[0].length > 3 ? 'swiper_740' : 'swiper_370']" @change="onChangeRecomment">
							<swiper-item v-for="(pageItem, pageIndex) in item" :key="pageIndex">
								<view class="glist">
									<view class="gitem" @click="detailLink(goodsItem)" v-for="(goodsItem, goodsIndex) in pageItem" :key="goodsIndex">
										<image class="goods_pic" :src="goodsItem.goods_thumb" :lazy-load="true" v-if="goodsItem.goods_thumb"></image>
										<image class="goods_pic" src="/static/no_image.jpg" v-else></image>
										<view class="goods_name_price">
											<view class="goods_name"><image class="country_icon_image" :src="goodsItem.country_icon" :lazy-load="true" v-if="goodsItem.country_icon"></image>{{goodsItem.goods_name}}</view>
											<currency-price :price="goodsItem.shop_price" v-if="goodsItem.shop_price"></currency-price>
										</view>
									</view>
								</view>
							</swiper-item>
						</swiper>
						<view class="indicator_dots">
							<view :class="['indicator', curIndex == reCurSwipeItem ? 'active_indicator' : '']" v-for="(curIndicator, curIndex) in item" :key="curIndex"></view>
						</view>
					</block>
				</view>
			</view>
		</view>
		<!-- 搜索 end -->
		
		<!-- 推荐文章 start -->
		<view class="goods_module_wrap mt_20" v-if="goodsDetail.goods_article_list && goodsDetail.goods_article_list.length > 0">
			<view class="title_box">
				<view class="title_left">
					<text>{{$t('lang.Recommended_articles')}}</text>
				</view>
			</view>
			<view class="art_content">
				<view :class="['article_item', index > 0 ? 'u-border-top' : '']" @click="$outerHref('/pagesC/article/detail/detail?id=' + item.article_id, 'app')" v-for="(item, index) in goodsDetail.goods_article_list" :key="index">
					<image class="article_img" :src="item.file_url" :lazy-load="true" v-if="item.file_url"></image>
					<view class="article_content">
						<view class="art_title">{{item.title}}</view>
						<text class="art_time">{{item.add_time}}</text>
					</view>
				</view>
			</view>
		</view>
		<!-- 推荐文章 end -->
		
		<!-- 图文详情 start -->
		<view class="goods_module_wrap mt_20 padding_b_30 detail_el" id="detail_id">
			<view class="title_box">
				<view class="title_left">
					<text>{{$t('lang.detail')}}</text>
				</view>
			</view>
			<block v-if="goodsDetail.goods_desc">
				<view class="image_text_title">
					<image class="title_ico" src="/static/img_txt_ico.png"></image>
					<text>{{$t('lang.goods_img_txt')}}</text>
				</view>
				<view class="goods_desc">
					<jyf-parser :html="goodsDetail.goods_desc" :tag-style="{video: 'width: 100%;',img: 'float:left;'}"></jyf-parser>
				</view>
			</block>
			<block v-if="goodsDetail.attr_parameter">
				<view class="image_text_title">
					<image class="title_ico" src="/static/goods_param.png"></image>
					<text>{{$t('lang.commodity_parameters')}}</text>
				</view>
				<view class="goods_attr_parameter u-border-left u-border-right u-border-top" :class="[isViewMore ? '' : 'attr_wrap']">
					<view class="tr u-border-bottom" v-if="goodsDetail.show_goodssn == 1">
						<view class="td u-border-right">{{$t('lang.goods_sn')}}</view>
						<view class="td">{{goodsDetail.goods_sn}}</view>
					</view>
					<view class="tr u-border-bottom">
						<view class="td color_58">{{$t('lang.basic_info')}}</view>
					</view>
					<view class="tr u-border-bottom" v-if="goodsDetail.show_brand == 1">
						<view class="td u-border-right">{{$t('lang.brand')}}</view>
						<view class="td">{{goodsDetail.brand_name}}</view>
					</view>
					<view class="tr u-border-bottom" v-if="goodsDetail.show_goodsweight == 1">
						<view class="td u-border-right">{{$t('lang.goods_weight')}}</view>
						<view class="td">{{goodsDetail.goods_weight}}kg</view>
					</view>
					<view class="tr u-border-bottom" v-if="goodsDetail.show_addtime == 1">
						<view class="td u-border-right">{{$t('lang.add_time')}}</view>
						<view class="td">{{goodsDetail.add_time_format}}</view>
					</view>
					<view class="tr u-border-bottom" v-for="(item, index) in goodsDetail.attr_parameter" :key="index">
						<view class="td u-border-right">{{ item.attr_name }}</view>
						<view class="td">{{ item.attr_value }}</view>
					</view>
				</view>
				<view class="open_goods_attr" v-if="moreAttr">
					<view class="view_more_btn" @click="isViewMore = !isViewMore">{{isViewMore ? $t('lang.pack_up') : $t('lang.zhankai')}}<text :class="['iconfont', isViewMore ? 'icon-less' : 'icon-moreunfold', 'size_24']"></text></view>
				</view>
			</block>
		</view>
		<!-- 图文详情 end -->
		
		<!-- 底部猜你喜欢 start -->
		<view class="goods-detail-guess related_el" id="related_id" v-if="guessList.length > 0">
			<view class="title"><text>{{$t('lang.same_style')}}</text></view>
			<view class="product-list-lie">
				<dsc-product-list :list="guessList" :productOuter="true" :guessList="true"></dsc-product-list>
				<uni-load-more :status="loadMoreStatus" :content-text="contentText" :type="false" v-if="guessList.length >= 10 && !isOver" />
			</view>
		</view>
		<!-- 底部猜你喜欢 end -->
		
		<!-- 底部版权 -->
		<dsc-copyright></dsc-copyright>
		
		<!-- 当前地区无货时，展示提示语展位 -->
		<view class="stockout_zhanwei" v-if="isShowStockout"></view>
		
		<!-- 提交订单栏 start -->
		<view class="submit_bar u-border-top">
			<!-- 当前地区无货时，展示提示语 -->
			<view class="stockout" v-if="is_alone_sale || goodsDetail.goods_number <= 0 || (goodsDetail.is_show_drp == 1 && goodsDetail.drp_shop > 0 && goodsDetail.is_distribution == 1)">
				<text class="stockout_txt" v-if="is_alone_sale">{{$t('lang.no_alone_sale')}}</text>
				<text class="stockout_txt" v-else-if="goodsDetail.goods_number <= 0">{{$t('lang.no_goods_tips')}}</text>
				<text class="stockout_txt" v-else-if="goodsDetail.is_show_drp == 1 && goodsDetail.drp_shop > 0 && goodsDetail.is_distribution == 1">{{$t('lang.share_goods_tips')}}</text>
			</view>
			<view class="function_wrap">
				<view class="function_item" @click="$outerHref('/pages/index/index','app')" v-if="goodsDetail.user_id == 0">
					<text class="iconfont icon-home-sy" style="font-size: 22px; margin-bottom: 10rpx;"></text>
					<text>{{$t('lang.home')}}</text>
				</view>
				<view class="function_item" @click="$outerHref('/pages/shop/shopHome/shopHome?ru_id='+goodsDetail.user_id,'app')" v-if="goodsDetail.user_id > 0 && controlVersion">
					<image class="title_ico" src="/static/store_ico.png" mode="heightFix"></image>
					<text>{{$t('lang.shop')}}</text>
				</view>
				<!-- #ifndef MP-WEIXIN -->
				<view class="function_item" @click="onChat(goodsId, goodsDetail.user_id)">
					<image class="title_ico" src="/static/service_ico.png" mode="heightFix"></image>
					<text>{{$t('lang.customer_service')}}</text>
				</view>
				<!-- #endif -->
				<!-- #ifdef MP-WEIXIN -->
				<block v-if="wxappChat > 0">
					<button open-type="contact" type="default" size="5" session-from="weapp" hover-class="none" class="function_item btn-cantact">
						<image class="title_ico" src="/static/service_ico.png" mode="heightFix"></image>
						<text>{{$t('lang.customer_service')}}</text>
					</button>
				</block>
				<block v-else>
					<block v-if="isLogin===true">
						<view class="function_item" open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile">
							<image class="title_ico" src="/static/service_ico.png" mode="heightFix"></image>
							<text>{{$t('lang.customer_service')}}</text>
						</view>
						
						<view class="function_item" @click="goUserProfile()" v-else>
							<image class="title_ico" src="/static/service_ico.png" mode="heightFix"></image>
							<text>{{$t('lang.customer_service')}}</text>
						</view>
					</block>
					<block v-else>
						<view class="function_item" @click="onChat(goodsId, goodsDetail.user_id)">
							<image class="title_ico" src="/static/service_ico.png" mode="heightFix"></image>
							<text>{{$t('lang.customer_service')}}</text>
						</view>
					</block>
				</block>
				<!-- #endif -->
				<view class="function_item badge_parent" @click="onCart">
					<view class="info_num" v-if="goodsDetail.cart_number && goodsDetail.cart_number > 0">{{goodsDetail.cart_number}}</view>
					<image class="title_ico" src="/static/cart_ico.png" mode="heightFix"></image>
					<text>{{$t('lang.cart')}}</text>
				</view>
			</view>
			<block v-if="is_on_sale">
				<button class="u-reset-button sub_btn yellow_btn">{{$t('lang.goods_sold_out')}}</button>
			</block>
			<block v-else-if="is_alone_sale">
				<button class="u-reset-button sub_btn yellow_btn" @click="goodsAloneSale">{{$t('lang.goods_alone_sale')}}</button>
			</block>
			<block v-else>
				<block v-if="goodsDetail.goods_number">
					<button class="u-reset-button sub_btn yellow_btn" @click="onSku(0)" v-if="scene != 1177">{{$t('lang.add_cart')}}</button>
					<!-- #ifdef MP-WEIXIN -->
					<block v-if="isLogin===true">
						<button class="u-reset-button sub_btn red_btn" open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile">
							<text>{{ goodsDetail.best_price && goodsDetail.best_price.cou_id > 0 ? $t('lang.coupon_purchase') : $t('lang.button_buy') }}</text>
						</button>
						<button class="u-reset-button sub_btn red_btn"  @click="getUserProfile" v-else>
							<text>{{ goodsDetail.best_price && goodsDetail.best_price.cou_id > 0 ? $t('lang.coupon_purchase') : $t('lang.button_buy') }}</text>
						</button>						
					</block>
					<block v-else>						
						<button class="u-reset-button sub_btn red_btn" @click="onSku(1)">
							<text>{{ goodsDetail.best_price && goodsDetail.best_price.cou_id > 0 ? $t('lang.coupon_purchase') : $t('lang.button_buy') }}</text>
							<text class="jiage" v-if="goodsDetail.best_price.price >= 0">{{$t('lang.on_hand_price')}} {{ goodsDetail.best_price.formated_price }}</text>
						</button>
					</block>
					<!-- #endif -->
					<!-- #ifdef APP-PLUS -->					
					<button class="u-reset-button sub_btn red_btn" @click="onSku(1)">
						<text>{{ goodsDetail.best_price && goodsDetail.best_price.cou_id > 0 ? $t('lang.coupon_purchase') : $t('lang.button_buy') }}</text>
						<text class="jiage" v-if="goodsDetail.best_price.price >= 0">{{$t('lang.on_hand_price')}} {{ goodsDetail.best_price.formated_price }}</text>
					</button>
					<!-- #endif -->
				</block>
				<block v-else>
					<button class="u-reset-button sub_btn yellow_btn">{{$t('lang.understock')}}</button>
				</block>
			</block>
		</view>
		<!-- 提交订单栏 end -->
		
		<!--视频号-->
		<view class="channels_live" @click="liveHref" v-if="isChannelsLive">
			<image src="../../static/media-live.gif" class="live-img"></image>
			<text class="text">{{$t('lang.live_explanation')}}</text>
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
		
		<!-- 优惠弹框 start -->
		<uni-popups ref="discounts" type="bottom">
			<view class="pop_content" :style="{height: `${popHeight.hd}px`}">
				<view class="pop_header">
					<text class="pop_title">{{$t('lang.goods_discounts')}}</text>
					<image class="close_img" src="/static/close1.png" @click="closePop('discounts')"></image>
				</view>
				<scroll-view scroll-y="true" :style="{height: `${popHeight.bd}px`}">
					<view class="pop_scroll_view">
						<view class="label_text">{{$t('lang.promotion')}}</view>
						<view class="list_wrap">
							<view class="pop_activity_item" v-for="(activityItem, activityIndex) in discountsData" :key="activityIndex">
								<block v-if="activityItem.acttype != 0">
									<text class="activity_bg">{{activityItem.label}}</text>
									<text class="activity_tips">{{activityItem.value}}</text>
								</block>
							</view>
						</view>
						<block v-if="goodsCouponList.length > 0">
							<view class="label_text">{{$t('lang.coupons_available')}}</view>
							<view class="coupons_list">
								<view class="coupons_item" v-for="(item,index) in goodsCouponList" :key="index">
									<view class="coupons_left">
										<view class="coupon_price">{{ currency_format }}{{item.cou_money}}</view>
										<view class="coupon_desc">{{$t('lang.man')}}{{ item.cou_man }}{{$t('lang.available_full')}}</view>
									</view>
									<view class="coupons_right">
										<view class="coupon_title">
											<text class="coupon_tag">{{$t('lang.coupon_tab_2')}}</text>{{$t('lang.limit')}}{{item.shop_name}}{{$t('lang.usable')}}[{{item.cou_goods_name}}]
										</view>
										<view class="get_coupon_time">
											<text class="coupon_time">{{ item.cou_start_time }} - {{ item.cou_end_time }}</text>
											<button class="u-reset-button coupon_btn u-reset-disabled" v-if="item.cou_is_receive == 1 && $isLogin()">{{ $t('lang.receive_hove') }}</button>
											<button class="u-reset-button coupon_btn u-reset-disabled" v-else-if="item.enable_ling > 0">{{item.brought_up}}</button>
											
											<button class="u-reset-button coupon_btn" v-else @click="handelReceive(item.cou_id)">{{$t('lang.click_to_collect')}}</button>
										</view>
									</view>
								</view>
							</view>
						</block>
					</view>
				</scroll-view>
			</view>
		</uni-popups>
		<!-- 优惠弹框 end -->
		
		<!-- 活动弹框 start -->
		<uni-popups ref="activity" type="bottom">
			<view class="pop_content activity_pup" :style="{height: `${popHeight.hd}px`}">
				<view class="pop_header">
					<text class="pop_title">{{$t('lang.goods_activity')}}</text>
					<image class="close_img" src="/static/close1.png" @click="closePop('activity')"></image>
				</view>
				<scroll-view scroll-y="true" :style="{height: `${popHeight.bd}px`}">
					<view class="pop_scroll_view">
						<view class="act_item" @click="$outerHref(item.path, 'app')" v-for="(item, index) in activityData" :key="index">
							<view class="act_main">
								<view class="act_left">
									<view class="act_label">{{item.label}}</view>
									<view class="act_value">{{item.value}}</view>
								</view>
								<text class="iconfont icon-more size_24"></text>
							</view>
							<view class="package_wrap" v-if="item.acttype == 2">
								<view class="recommend_title">{{$t('lang.recommended_accessories')}} {{$t('lang.hig_discount')}} <text class="activity_price">{{spare_price}}</text> {{$t('lang.yuan')}}</view>
								<view class="img_list">
									<view class="img_wrap">
										<image class="goods_pic" :src="goodsDetail.goods_thumb"></image>
									</view>
									<block v-for="(packageItem, packageIndex) in goodsDetail.fittings" :key="packageIndex">
										<view class="img_wrap" v-if="packageIndex < 3">
											<image class="goods_pic" :src="packageItem.goods_thumb"></image>
										</view>
									</block>
									<text class="iconfont icon-gengduo1" v-if="goodsDetail.fittings.length > 3"></text>
								</view>
							</view>
						</view>
					</view>
				</scroll-view>
			</view>
		</uni-popups>
		<!-- 活动弹框 end -->
		
		<!-- 商品属性弹框 start -->
		<uni-popups ref="goodsattr" type="bottom">
			<view class="pop_content">
				<view class="goods_attr_pop">
					<image class="close_img" src="/static/close1.png" @click="closePop('goodsattr')"></image>
					<view class="goods_info">
						<image class="goods_pic" :src="goodsDetail.goods_thumb" @click="previewImg(0, goodsDetail.goods_img)"></image>
						<view class="info_right">
							<currency-price
								:price="goodsDetail.shop_price"
								:size="24"
								v-if="goodsDetail.shop_price"
							></currency-price>
							<view class="selece_atrr">{{$t('lang.label_selected2')}}：{{selectAttr}}</view>
							<view class="inventory">
								<view class="inventory_num" v-if="goodsDetail.goods_number && goodsDetail.show_goodsnumber > 0">
									<text class="label">{{$t('lang.stock')}}：</text>
									<text>{{goodsDetail.goods_number}}</text>
								</view>
								<view class="inventory_sn" v-if="goodsDetail.goods_sn && goodsDetail.show_goodssn == 1">
									<text class="label">{{$t('lang.commodity_number')}}：</text>
									<text>{{goodsDetail.goods_sn}}</text>
								</view>
							</view>
						</view>
					</view>
				</view>
				<scroll-view scroll-y="true" :style="{height: `${popHeight.center}px`}">
					<view class="goods_attr_pop">
						<view class="goods_attr_wrap">
							<view class="attr_type" v-for="(item, index) in goodsAttr" :key="index">
								<view class="attr_title">{{item.name}}</view>
								<view class="attr_list">
									<view :class="['attr_item', seleceGoodsAtrrArray.includes(attrItem.id) ? 'active_attr' : '']" @click="selectGoodsAttr(attrItem, item)" v-for="(attrItem, attrIndex) in item.attr_key" :key="attrIndex">{{attrItem.attr_value}}</view>
								</view>
							</view>
							<view class="attr_type goods_num_wrap">
								<view class="attr_title">{{$t('lang.number')}}</view>
								<view class="limit_and_mun">
									<text class="limit_txt" v-if="goodsDetail.is_minimum > 0">（ {{goodsDetail.minimum}} {{goodsDetail.goods_unit}}{{$t('lang.label_minimum_2')}}）</text>
									<text class="limit_txt" v-if="goodsDetail.xiangou_num > 0 && goodsDetail.xiangou_end_date > goodsDetail.current_time">（{{$t('lang.purchase_only')}} {{goodsDetail.xiangou_num}} {{goodsDetail.goods_unit}}）</text>
									<view class="add_num_wrap">
										<text :class="['iconfont', 'icon-jian', goodsNum > minNum ? '' : 'limit_1']" @click="changeGoodsNum('subtract')"></text>
										<input class="add_input" type="number" :value="goodsNum" @blur="changeGoodsNumByInput" />
										<text :class="['iconfont', 'icon-jia', goodsNum < xiangouNum ? '' : 'limit_1']" @click="changeGoodsNum('add')"></text>
									</view>
								</view>
							</view>
						</view>
					</view>
				</scroll-view>
				<view class="footer_btn u-border-top">
					<block v-if="!storeBtn">
						<block v-if="is_on_sale">
							<button class="u-reset-button sub_btn yellow_btn">{{$t('lang.goods_sold_out')}}</button>
						</block>
						<block v-else-if="is_alone_sale">
							<button class="u-reset-button sub_btn yellow_btn" @click="goodsAloneSale">{{$t('lang.goods_alone_sale')}}</button>
						</block>
						<block v-else>
							<block v-if="goodsDetail.goods_number">
								<button class="u-reset-button sub_btn yellow_btn" @click="onAddCartClicked(0)" v-if="addType == 1 || addType == ''">{{$t('lang.add_cart')}}</button>
								<button class="u-reset-button sub_btn red_btn" @click="onAddCartClicked(10)" v-if="addType == 2 || addType == ''">
								<text>{{ goodsDetail.best_price && goodsDetail.best_price.cou_id > 0 ? $t('lang.coupon_purchase') : $t('lang.button_buy') }}</text>
								<text class="jiage" v-if="goodsDetail.best_price.price >= 0">{{$t('lang.on_hand_price')}} {{ goodsDetail.best_price.formated_price }}</text>
								</button>
								
								
							</block>
							<block v-else>
								<button class="u-reset-button sub_btn yellow_btn">{{$t('lang.understock')}}</button>
							</block>
						</block>
					</block>
					<block v-else>
						<button class="u-reset-button sub_btn pup_btn red_btn" @click="onStoreClicked">{{ $t('lang.private_store') }}</button>
					</block>
				</view>
			</view>
		</uni-popups>
		<!-- 商品属性弹框 end -->
		
		<!-- 配送弹框 start -->
		<uni-popups ref="address" type="bottom">
			<view class="pop_content" :style="{height: `${popHeight.hd}px`}">
				<view class="pop_header">
					<text class="iconfont icon-find-fanhui" @click="currAddressSwiper = 0" v-if="currAddressSwiper == 1"></text>
					<text class="pop_title">{{$t('lang.delivery_to_the')}}</text>
					<image class="close_img" src="/static/close1.png" @click="closePop('address')"></image>
				</view>
				<block v-if="currAddressSwiper == 0">
					<scroll-view scroll-y="true" :style="{height: `${popHeight.center}px`}">
						<view class="pop_scroll_view">
							<view class="address_item" v-for="(item, index) in addressList" :key="index" @click="changeAddressRadio(item)">
								<view class="clicked_ico">
									<uni-icons type="checkbox-filled" size="20" color="#F91F28" v-if="selectAddress == item.id"></uni-icons>
									<uni-icons type="circle" size="20" color="#c8c9cc" v-else></uni-icons>
								</view>
								<text class="address_val">{{ item.province_name }} {{ item.city_name }} {{item.district_name}} {{item.street_name}} {{item.address}}</text>
							</view>
						</view>
					</scroll-view>
					<view class="btn_wrap u-border-top">
						<button class="u-reset-button sub_btn address_btn red_btn" @click="selectOtherAddress">{{$t('lang.choose_another_address')}}</button>
					</view>
				</block>
				<block v-else>
					<!--地区选择-->
					<dsc-region :display="regionShow" regionType="goods" :regionOptionData="regionData" @updateDisplay="getRegionShow" @updateRegionDate="getRegionOptionDate" v-if="regionLoading"></dsc-region>
				</block>
			</view>
		</uni-popups>
		<!-- 配送弹框 end -->
		
		<!-- 服务弹框 start -->
		<uni-popups ref="server" type="bottom">
			<view class="pop_content" :style="{height: `${popHeight.hd}px`}">
				<view class="pop_header">
					<text class="pop_title">{{$t('lang.service')}}</text>
					<image class="close_img" src="/static/close1.png" @click="closePop('server')"></image>
				</view>
				<scroll-view scroll-y="true" :style="{height: `${popHeight.center}px`}">
					<view class="pop_scroll_view">
						<view class="server" v-for="(item,index) in goodsDetail.goods_services_label" :key="index">
							<view class="server_item">
								<image class="location_ico" :src="item.formated_label_image" mode="heightFix"></image>
								<text>{{item.label_name}}</text>
							</view>
							<view class="color_999">{{item.label_explain}}</view>
						</view>
					</view>
				</scroll-view>
				<view class="btn_wrap u-border-top">
					<button class="u-reset-button sub_btn address_btn red_btn" @click="closePop('server')">{{$t('lang.confirm')}}</button>
				</view>
			</view>
		</uni-popups>
		<!-- 服务弹框 end -->
		
		<!--小程序分享-->
		<view class="show-popup-shareImg">
			<uni-popup :show="shareImgShow" type="bottom" animation="true" v-on:hidePopup="shareImgShow = false">
				<view class="mp-share-warp">
					<view class="title">
						<text>{{$t('lang.save_xaingce')}}</text>
						<uni-icon type="closeempty" size="30" color="#8f8f94" @click="shareImgShow = false"></uni-icon>
					</view>
					<view class="mp-share-img"><image :src="mpShareImg" mode="heightFix" class="img" @tap="previewImage"></image></view>
					<view class="btn-bar btn-bar-radius">
						<button class="btn btn-red mlr50" @click="downloadImg">{{$t('lang.save_picture')}}</button>
						<button class="btn btn-red mlr50"  open-type="share">{{$t('lang.forward_link')}}</button>
					</view>
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
		
		<!-- 首次加载 loading -->
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
		
		<!--返回顶部-->
		<dsc-filter-top :scrollState="scrollState" outerClass="true"></dsc-filter-top>
	</view>
</template>

<script>
	import { mapState } from 'vuex';
	
	import jyfParser from "@/components/jyf-parser/jyf-parser";
	import uniPopup from '@/components/uni-popup.vue';
	
	import uniPopups from '@/components/uni-popup/uni-popup.vue';
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniCountdown from '@/components/uni-countdown.vue';
	import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
	
	/*地区选择组件*/
	import dscRegion from '@/components/dsc-region.vue';
	
	//返回顶部
	import dscFilterTop from '@/components/dsc-filter-top'
	
	// 猜你喜欢组件
	import dscProductList from '@/components/dsc-product-list.vue';
	
	import dscCopyright from '@/components/dsc-copyright/dsc-copyright.vue'
	
	let timerId = null;
	
	export default {
		onShareAppMessage(res) {
			if (res.from === 'button') {
				// 来自页面内分享按钮
				return {
					title: this.goodsDetail.goods_name,
					path: '/pagesC/goodsDetail/goodsDetail?id=' + this.goodsDetail.goods_id +'&parent_id=' + uni.getStorageSync("user_id")
				};
			} else {
				return {
					title: this.goodsDetail.goods_name,
					path: '/pagesC/goodsDetail/goodsDetail?id=' + this.goodsDetail.goods_id +'&parent_id=' + uni.getStorageSync("user_id")
				};
			}
		},
		//用户点击右上角分享朋友圈
		onShareTimeline: function() {
			return {
				title: this.goodsDetail.goods_name,
				query: {
					key: 'id=' + this.goodsDetail.goods_id
				},
				imageUrl: this.goodsDetail.goods_img
			};
		},
		components: {
		    jyfParser,
			uniPopup,
			uniPopups,
			uniIcons,
			uniCountdown,
			uniLoadMore,
			dscRegion,
			dscFilterTop,
			dscProductList,
			dscCopyright
		},
		data() {
			return {
				scopeUserInfo: {},
				scope: 'userInfo',
				scopeSessions: {},
				canIUseProfile:false,
				getPhoneNumberShow:false,
				goodsId: '',
				currNav: 0,
				currSwiperPic: 0,
				old: {
					scrollTop: 0
				},
				popHeight: {
					hd: 0,
					bd: 0,
					center: 0
				},
				vw: 375,
				menuButtonInfo: {},
				currGoodsList: 0,
				reCurSwipeItem: 0,
				rgba: 'rgba(0,0,0,0)',
				navIconRgba: 'rgba(0,0,0,0.4)',
				followIconRgba: 'rgba(249,31,40,0.4)',
				navOpacity: 0,
				showShortcut: false,
				isShowStockout: true,
				dscLoading: true,
				isViewMore: false,
				showVideo: false,
				flag: true,
				currAddressSwiper: 0,
				collectStatus: 0,
				followShopStatus: 0,
				goodsDetail: {},
				goodsCouponList: [],
				goodsComment: [],
				addressList: [],
				selectRegionArr: [],
				seleceGoodsAtrrArray: [],
				regionList: [],
				friendDis: [],
				recommentTabs: [],
				goodsGuessList: [],
				guessList: [],
				loadMoreStatus:'loading',
				isOver: false,
				contentText: {
					contentdown: this.$t('lang.no_more'),
					contentrefresh: this.$t('lang.loading'),
					contentnomore: this.$t('lang.no_more')
				},
				commentTotal: {},
				shopDetail: {},
				search_keywords: [],
				storeRate: 0,
				goodsNum: 1,
				minNum: 1,
				xiangouNum: 1,
				scrollTopObj: {},
				navBar: [
					{title: this.$t('lang.goods')},
					{title: this.$t('lang.comment')},
					{title: this.$t('lang.detail')},
					{title: this.$t('lang.recommend')}
				],
				shortcutData: [
					{
						name: this.$t('lang.home'),
						ico: 'iconfont icon-home-sy',
						path: '/pages/index/index'
					},
					{
						name: this.$t('lang.search'),
						ico: 'iconfont icon-sousuo',
						path: '/pages/search/search'
					},
					{
						name: this.$t('lang.category'),
						ico: 'iconfont icon-home-fenlei',
						path: '/pages/category/category'
					},
					{
						name: this.$t('lang.cart'),
						ico: 'iconfont icon-home-shop',
						path: '/pages/cart/cart'
					},
					{
						name: this.$t('lang.personal_center'),
						ico: 'iconfont icon-home-mine',
						path: '/pages/user/user'
					}
				],
				shareImgShow:false,
				mpShareImg: '',
				regionShow:false,
				regionLoading:false,
				addressRadio: '',
				regionGoodsPirceFormated: '',
				regionGoodsNumberFormated: '',
				storeBtn: false,
				addType:'',
				addCartClass:false,
				attrColorIndex:0,
				current:0,
				scrollState:false,
				scene: uni.getStorageSync("scene"),
				isChannelsLive: false,
				currency_format: uni.getStorageSync('configData').currency_format || '¥',
				//微信小程序客服
				wxappChat:uni.getStorageSync("configData").wxapp_chat || 0
			};
		},
		computed: {
			...mapState({
				shipping_fee: state => state.shopping.shipping_fee, //配送运费信息
			}),
			selectAddress: {
				get() {
					return this.addressRadio
				},
				set(value) {
					this.addressRadio = value
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
			is_on_sale() {
				return this.goodsDetail.is_on_sale == 0 ? true : false;
			},
			is_alone_sale() {
				return this.goodsDetail.is_alone_sale == 0 ? true : false
			},
			attr() {
				return this.goodsDetail.attr ? this.goodsDetail.attr : [];
			},
			attrColorList(){
				let attr = this.attr.filter(item => item.is_checked === 1);
				let imgFile = [];
				
				if(attr.length > 0){
					imgFile = attr[0].attr_key.filter(item => item.img_flie !== '');
				}
				
				return imgFile.length > 0 && attr[0].attr_key.length > 0 ? attr[0].attr_key : []
			},
			regionGoodsNumber: {
				get() {
					return this.shipping_fee.goods ? this.shipping_fee.goods.stock : 0;
				},
				set(val) {
					if (this.shipping_fee.goods) {
						this.regionGoodsNumberFormated = val;
					}
				}
			},
			swiperData: function () {
				const imglist = this.goodsDetail.gallery_list || [];
				if (imglist.length == 0) imglist.push({img_url: this.goodsDetail.goods_img});
				return imglist;
			},
			goodsQrcodDaTa: function () {
				const qrcod_list = this.goodsDetail.goods_qrcod_list || [];
				return qrcod_list;
			},
			videoPoster: function () {
				return this.swiperData.length > 0 ? this.swiperData[0].img_url : '';
			},
			goodsAttr: function () {
				return this.goodsDetail.attr ? this.goodsDetail.attr : [];
			},
			selectAttr: {
				get() {
					return this.goodsAttr.length > 0 ? this.goodsDetail.attr_name + ' ' + this.goodsNum + this.goodsDetail.goods_unit : this.goodsNum + this.goodsDetail.goods_unit;
				},
				set(val) {
					this.goodsDetail.attr_name = val;
				}
			},
			goodsRate() {
				return this.goodsDetail.goods_rate
			},
			discountsData: function () {
				let arr = [];
				
				if (this.goodsCouponList && this.goodsCouponList.length > 0) {
					arr.push({
						acttype: 0,
						label: this.$t('lang.get_coupon_2'),
						value: this.goodsCouponList
					})
				}
				if (this.goodsDetail.consumption && this.goodsDetail.consumption.length > 0) {
				    let consumption = ''
				    let str = ''
				    this.goodsDetail.consumption.forEach(v=>{
				        str = this.$t('lang.man') + v.cfull + this.$t('lang.minus') + v.creduce
				        consumption += str + ','
				    });
					arr.push({
						acttype: 1,
						label: this.$t('lang.full_reduction'),
						value: consumption.substr(0, consumption.length - 1)
					})
				};
				if (this.goodsDetail.goods_promotion && this.goodsDetail.goods_promotion.length > 0) {
					this.goodsDetail.goods_promotion.forEach(item => {
						if (item.act_type == 0) {
							item.label = this.$t('lang.with_a_gift');
							item.value = item.act_name;
							item.acttype = 3;
							arr.push(item)
						} else if (item.act_type == 1) {
							item.label = this.$t('lang.full_reduction');
							item.value = item.act_name;
							item.acttype = 1;
							arr.push(item);
						} else if (item.act_type == 2) {
							item.label = this.$t('lang.discount');
							item.value = item.act_name;
							item.acttype = 2;
							arr.push(item);
						}
					})
				};
				if (this.goodsDetail.give_integral > 0 && this.goodsDetail.show_give_integral == 1) {
					arr.push({
						acttype: 4,
						label: this.$t('lang.label_give_integral_2'),
						value: `${this.$t('lang.buy_the_product_as_a_gift')}${this.goodsDetail.give_integral}${this.$t('lang.integral')}`
					})
				};
				if (this.goodsDetail.integral > 0) {
					arr.push({
						acttype: 5,
						label: this.$t('lang.points_deduction'),
						value: `${this.$t('lang.you_can_use_up_to')}${this.goodsDetail.integral}${this.$t('lang.integral')}`
					})
				};
				if (this.goodsDetail.volume_price_list && this.goodsDetail.volume_price_list.length > 0) {
					let volumePrice = '';
					this.goodsDetail.volume_price_list.forEach(item => {
						volumePrice += `${this.$t('lang.the_purchase_of_this_product_is_full')}${item.number}${this.$t('lang.jian')}，${this.$t('lang.the_purchase_of_this_product_is_full_2')} ${item.format_price} \n`
					});
					arr.push({
						acttype: 6,
						label: this.$t('lang.buy_more_discount'),
						value: volumePrice
					});
				};
				return arr;
			},
			formatDiscountsData: function () {
				if (this.discountsData.length > 2) {
					return [...this.discountsData.slice(0, 2), this.discountsData.slice(2)]
				} else {
					return this.discountsData
				}
			},
			spare_price: function () {
				if (this.goodsDetail.fittings && this.goodsDetail.fittings[0]) return this.goodsDetail.fittings[0].spare_price
                else return this.currency_format + '0.00'
            },
			activityData: function () {
				let arr = [];
				if (this.goodsDetail.goods_promotion && this.goodsDetail.goods_promotion.length > 0) {
					this.goodsDetail.goods_promotion.forEach(item => {
						if (item.type == 'group_buy') {
							item.label = this.$t('lang.group_buy');
							item.value = this.$t('lang.in_group_buying_activities');
							item.acttype = 0;
							item.path = `/pagesA/groupbuy/detail/detail?id=${item.act_id}`;
							arr.push(item)
						} else if (item.type == 'auction') {
							item.label = this.$t('lang.auction');
							item.value = this.$t('lang.the_goods_are_being_auctioned');
							item.acttype = 1;
							item.path = `/pagesA/auction/detail/detail?act_id=${item.act_id}`;
							arr.push(item);
						} else if (item.type == 'team') {
							item.label = this.$t('lang.team');
							item.value = this.$t('lang.participating_in_group_activities');
							item.acttype = 3;
							item.path = `/pagesA/team/detail/detail?goods_id=${item.goods_id}`
							arr.push(item);
						} else if (item.type == 'bargain') {
							item.label = this.$t('lang.bargain');
							item.value = this.$t('lang.participating_in_bargaining_activities');
							item.acttype = 4;
							item.path = `/pagesA/bargain/detail/detail?id=${item.bargain_id}`;
							arr.push(item);
						}
					})
				};
				
				if (this.goodsDetail.fittings) {
					// 暂不开发
					
					arr.push({
						acttype: 2,
						label: this.$t('lang.discount_package'),
						value: `${this.$t('lang.the_goods_are_in_common')}${this.tabList}${this.$t('lang.special_package')}`,
						path: `/pagesC/goodsDetail/setmeal?id=${this.goodsId}`
					})
				};
				return arr;
			},
			tabList: function () {
				let i = 0, a = 0,length = 0,arr=[];
				if (!this.goodsDetail.fittings) return 0;
				this.goodsDetail.fittings.forEach(v=>{
					if(v.group_id == 1){
						i++
					}else{
						a++
					}
				});
				
				arr = [i,a]
				arr.forEach(v=>{
					if(v > 0){
						length ++ 
					}
				})
				return length
			},
			moreAttr: function () {
				let num = 0;
				if (this.goodsDetail.attr_parameter) {
					if (this.goodsDetail.show_brand == 1) num += 1;
					if (this.goodsDetail.show_goodsweight == 1) num += 1;
					if (this.goodsDetail.show_addtime == 1) num += 1;
					num += this.goodsDetail.attr_parameter.length;
				}
				return num > 5 ? true : false;
			},
			freight() {
				return this.shipping_fee != null && this.shipping_fee.is_shipping > 0 ? this.shipping_fee.shipping_fee_formated : this.$t('lang.is_shipping_area');
			},
			freeShipping() {
				return this.shipping_fee != null && this.shipping_fee.free_shipping ? this.shipping_fee.free_shipping : 0;
			},
			dateTime: function () {
				let dataTime = this.goodsDetail.promote_end_date;
				if (dataTime != '') {
					return this.$formatDateTime(dataTime);
				} else {
					return '';
				}
			},
			
		},
		onLoad(e) {
			let that = this;
			that.goodsId = e.id || '';
			
			//用户没有登录地区选择不显示收货地址
			if(that.$isLogin()){
				that.getAddressList();
			}else{
				that.regionShow = true;
				that.currAddressSwiper = 1;
			}
			
			// #ifdef MP-WEIXIN
			that.menuButtonInfo = uni.getMenuButtonBoundingClientRect();
			// #endif
			
			//默认地区
			that.regionData = that.getRegionData;
			
			//直播间商品
			if (e.room_id) {
				that.goodsId = e.id;
			}
			
			//小程序扫码
			if (e.scene) {
				let scene = decodeURIComponent(e.scene);
				let fristParameter = scene.split('_')[0];
				let lastParameter = scene.split('_')[1];
				
				// 判断分割第一个参数是否有 "."
				that.goodsId = fristParameter.indexOf('.') > 0 ? fristParameter.split('.')[0] : fristParameter;
				
				if(lastParameter){
					uni.setStorageSync('parent_id',lastParameter);
				}
			}
			
			if(e.parent_id){
				uni.setStorageSync('parent_id',e.parent_id);
			}
			
			//视频号商品判断
			if(uni.getStorageSync('mediaLive')){
				let media_goods_id = uni.getStorageSync('mediaLive').goods_id;
				media_goods_id = typeof(media_goods_id) != 'string' ? media_goods_id.toString() : media_goods_id;
				that.isChannelsLive = (media_goods_id.indexOf(that.goodsId) > -1) && (uni.getStorageSync('mediaLive').is_live == 1) ? true : false
			}
			
			that.getGoodsDetailById();
		},
		onReady() {
			const { windowHeight, windowWidth } = uni.getSystemInfoSync();
			this.vw = windowWidth;
			this.$set(this.popHeight, 'hd', parseInt(windowHeight * 0.7));
			this.$set(this.popHeight, 'bd', parseInt(windowHeight * 0.7) - uni.upx2px(90));
			this.$set(this.popHeight, 'center', parseInt(windowHeight * 0.7) - uni.upx2px(190));
		},
		onPageScroll(e) {
			this.scrollState = e.scrollTop > 800 ? true : false
			
			this.old.scrollTop = e.scrollTop;
			// 导航栏背景渐变
			const ratio = (e.scrollTop / uni.upx2px(375)).toFixed(1);
			if (ratio >= 1) {
				this.rgba = 'rgba(251,251,251,1)';
				this.navIconRgba = 'rgba(251,251,251,1)';
				this.followIconRgba = 'rgba(251,251,251,1)';
				this.navOpacity = 1;
			} else if (ratio > 0) {
				this.rgba = `rgba(251,251,251,${ratio})`;
				if (ratio > 0.5) {
					this.navIconRgba = `rgba(0,0,0,${1 - ratio})`;
					this.followIconRgba = `rgba(249,31,40,${1 - ratio})`;
				};
				this.navOpacity = ratio;
			} else {
				this.rgba = 'rgba(0,0,0,0)';
				this.navIconRgba = 'rgba(0,0,0,0.4)';
				this.followIconRgba = 'rgba(249,31,40,0.4)';
				this.navOpacity = 0;
			}
			
			if (this.showShortcut) this.showShortcut = false;
			
			// this.navigationTracking();
			
			if (this.flag) this.computedElTop();
			
		},
		onReachBottom() {
			console.log('daodile');
			if (this.isOver) return this.loadMoreStatus = 'more';
			this.loadMoreStatus = 'loading';
			this.getGuessList()
		},
		watch:{
			goodsDetail(){
				
			},
			regionShow() {
				if (this.regionShow) {
					this.regionLoading = true
				}
			},
			regionSplic() {
				this.shipping_region = {
					province_id: this.regionData.province.id,
					city_id: this.regionData.city.id,
					district_id: this.regionData.district.id,
					street_id: this.regionData.street.id
				};
				
				//运费
				if(this.goodsDetail){
					this.shippingFee(this.shipping_region);
				}
			},
			shipping_fee() {
				if (this.shipping_fee.goods) {
					this.regionGoodsPirce = this.shipping_fee.goods.shop_price;
					this.regionGoodsPirceFormated = this.shipping_fee.goods.shop_price_formated;
					this.regionGoodsNumber = this.shipping_fee.goods.stock;
				}
			},
			sharePoster() {
				if (this.sharePoster) {
					this.$refs.popupPoster.open();
				}
			},
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
			                console.log(sessionRes);
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
			getUserProfile(e){
				uni.getUserProfile({
					desc : this.$t('lang.user_login_desc'),  
					lang : 'zh_CN',  
					success: (profileRes) => {
						uni.setStorage({ key: 'scopeUserInfo', data: profileRes.userInfo });
						this.scopeUserInfo = profileRes.userInfo;						
						this.scope = 'phoneNumber';
						this.getPhoneNumberShow = true;
						
						//类型
						uni.setStorage({
							key: 'scopeType',
							data: e
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
								type:"goods",
								scopeSessions: this.scopeSessions,
								phoneNumber: obj.data.phoneNumber,
								userInfo: this.scopeUserInfo,
								delta: this.delta,
								parent_id: this.parent_id,
								platform: uni.getStorageSync('platform'),
							}
						} else {
							o = {
								type:"goods",
								scopeSessions: this.scopeSessions,
								phoneNumber: obj.data.phoneNumber,
								userInfo: this.scopeUserInfo,
								parent_id: this.parent_id,
								platform: uni.getStorageSync('platform'),
							}
						}						
						this.$store.dispatch('getScopeAppLogin', o).then(()=>{
							
							let scopeType = uni.getStorageSync('scopeType') || 0;
							
							if(scopeType == 1){
								//延迟获取token
								setTimeout(() => {
									this.onChat(this.goodsId, this.ru_id);
								}, 800);
							}else{
								//延迟获取token
								setTimeout(() => {
									this.onAddCartClicked(10);
								}, 1000);
							}
						});
						
					}
				});
			},
			goUserProfile(){
				this.getUserProfile(1);
			},
			
			goBack() {
				uni.navigateBack()
			},
			showToast(msg = '请稍后重试！') {
				uni.showToast({
					title: msg,
					icon: 'none'
				})
			},
			// 导航跟踪
			navigationTracking() {
				if (timerId) {
					clearTimeout(timerId);
					timerId = null;
				};
				timerId = setTimeout(() => {
					this.computedElTop()
				}, 5);
			},
			// 图片预览
			previewImg(i, imgs) {
				let arr = []
				if (imgs){
					if(typeof imgs == 'string') arr.push(imgs);
					
					uni.previewImage({
						current: i,
						urls: arr.length > 0 ? arr : imgs
					})
				}
			},
			// 开始播放
			play(event) {
				this.showVideo = true;
                uni.createVideoContext('goodsVideo').play();
            },
			// 暂停播放
            pause(event) {
                uni.createVideoContext('goodsVideo').pause();
				this.showVideo = false;
            },
			//视频播放错误
			videoErrorCallback(e) {
				uni.showModal({
					content: this.$t('lang.video_error_callback'),
					showCancel: false
				});
			},
			// 获取一个目标元素的高度
			getElRect(elClass) {
				return new Promise((resolve, reject) => {
					const query = uni.createSelectorQuery().in(this);
					query.select('.' + elClass).boundingClientRect(res => {
						// 如果节点尚未生成，res值为null，循环调用执行
						if(!res) {
							setTimeout(() => {
								this.getElRect(elClass);
							}, 10);
							return ;
						}
						resolve(res)
					}).exec();
				})
			},
			async computedElTop() {
				let statusHeight = uni.getSystemInfoSync().statusBarHeight || 0;
				// #ifdef APP-PLUS
				statusHeight = 44 + statusHeight + uni.upx2px(20);
				// #endif
				
				// #ifdef MP-WEIXIN
				statusHeight = 44 + this.menuButtonInfo.height + this.menuButtonInfo.top + uni.upx2px(20);
				// #endif
				const topArr = [];
				const comment_el = await this.getElRect('comment_el');
				topArr.push(comment_el);
				const detail_el = await this.getElRect('detail_el');
				topArr.push(detail_el);
				const related_el = await this.getElRect('related_el');
				topArr.push(related_el);
				topArr.forEach((item, index) => {
					if (item.top < statusHeight && item.bottom > statusHeight) {
						if (item.id == 'comment_id') this.currNav = 1
						else if (item.id == 'detail_id') this.currNav = 2
						else if (item.id == 'related_id') this.currNav = 3;
					}
				});
				if (topArr[0].top > statusHeight) this.currNav = 0;
			},
			// 获取商品详情
			async getGoodsDetailById() {
				
				let parent_id = uni.getStorageSync('parent_id') ? uni.getStorageSync('parent_id') : 0;
				const { data, status } = await this.$store.dispatch('getGoodsDetail', {
					goods_id: this.goodsId,
					parent_id: parent_id,
					warehouse_id: 0,
					area_id: 0,
					is_delete: 0,
					is_on_sale: 1
				});
				
				if (status != 'success') {
					return uni.showToast({
						title: '请稍候重试！',
						icon: 'none',
						complete: () => {
							uni.reLaunch({url:"/pages/index/index"})
						}
					});
				}
				
				//店铺信息
				if (data.user_id > 0) {
					const { data: res, status: sta } = await this.$store.dispatch('getShopDetailById', {ru_id: data.user_id});
					if (sta != 'success') {
						this.showToast();
					} else {
						this.shopDetail = res;
						
						this.followShopStatus = res.is_collect_shop;
						
						let num = 0;
						if (res.commentrank) num += parseInt(res.commentrank.slice(0, 1)) || 0;
						if (res.commentdelivery) num += parseInt(res.commentdelivery.slice(0, 1)) || 0;
						if (res.commentserver) num += parseInt(res.commentserver.slice(0, 1)) || 0;
						
						num = num / 3;
						this.storeRate = Math.round(num);
					}
				};
				
				this.goodsDetail = data;
				
				// 默认勾选的属性
				if (data.attr) {
					data.attr.forEach(item => {
						item.attr_key.forEach(val => {
							if (val.attr_checked == 1) {
								this.seleceGoodsAtrrArray.push(val.id);
							};
						});
					});
				};
				
				// 起购数量
				if (data.minimum > 1) this.goodsNum = data.minimum;
				this.minNum = data.minimum || 1;
				
				//限购
				if(data.xiangou_num > 0 && data.xiangou_end_date > data.current_time) this.xiangouNum = data.xiangou_num;
				this.xiangouNum = data.goods_number;
				
				//浏览历史
				if(this.$isLogin()) this.historyAdd();
				
				//如果是秒杀商品跳转到秒杀详情
				if (this.goodsDetail.seckill_id) {
					uni.redirectTo({
						url: '/pagesA/seckill/detail/detail?id=' + this.goodsDetail.seckill_id + '&tomorrow=0&delta=2'
					});
				}

				// 初始化价格
				this.changeAttr(true);
				
				this.collectStatus = data.is_collect;
				
				this.$nextTick(function(){
					this.getGoodsCoupon();
					this.getCommentList();
					this.getGoodsList();
					this.getGuessList();
				});
			},
			// 收藏
			async collection() {
				if (this.$isLogin()) {
					const { data, status } = await this.$store.dispatch('collectgoodsById', {
						goods_id: this.goodsId,
						status: this.collectStatus
					});
					if (data.error == 0) this.collectStatus = status;
				} else {
					uni.showModal({
						content: this.$t('lang.fill_in_user_collect_goods'),
						success: res => {
							if (res.confirm) {
								uni.navigateTo({
									url: '/pagesB/login/login?delta=1'
								});
							}
						}
					});
				}
			},
			// 店铺关注
			async collectHandle(val){
				if(this.$isLogin()){
					const { data, status } = await this.$store.dispatch('followCollectShopById',{
						ru_id:val,
						status: this.followShopStatus
					});
					if (data.error == 0) {
						this.followShopStatus = status;
						if (status == 1) this.shopDetail.count_gaze = this.shopDetail.count_gaze + 1
						else this.shopDetail.count_gaze = this.shopDetail.count_gaze - 1;
					}
				}else{
					uni.showModal({
						content:this.$t('lang.fill_in_user_collect_shop'),
						success:(res)=>{
							if(res.confirm){
								uni.navigateTo({
									url:'/pagesB/login/login?delta=1'
								})
							}
						}
					})
				}
			},
			// 获取优惠券
			async getGoodsCoupon() {
				const { data, status } = await this.$store.dispatch('getGoodsCouponListById', {
					goods_id: this.goodsId,
					ru_id: this.goodsDetail.user_id
				});
				
				if (status != 'success') return this.showToast();
				
				this.goodsCouponList = data;
			},
			//领取优惠券
			handelReceive(val) {
				if(this.$isLogin()){
					this.$store.dispatch('setGoodsCouponReceive', {
						cou_id: val
					}).then(({ data: data }) => {
						uni.showToast({
							title: data.msg,
							icon: 'none'
						});
						
						this.getGoodsCoupon();
					});
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
			},
			// 搜索热词
			getHotKeywords() {
				uni.request({
					url:this.websiteUrl + '/api/shop/config',
					method:'GET',
					data:{},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: ({data:{data}}) => {
						if(data.search_keywords){
							this.search_keywords = data.search_keywords.split(',')
						}
					}
				});
			},
			// 获取商品列表
			async getGoodsList() {
				let that = this;
				
				that.getHotKeywords();
				
				const { ru_id, basic_info, get_seller_shop_info } = that.goodsDetail;
				
				if(ru_id > 0){
					const { data } = await that.$store.dispatch('getShopGoodsListById', {
						store_best: 1,
						store_id: ru_id,
						page: 1,
						size: 30
					});
					
					if (Array.isArray(data) && data.length > 0) {
						that.recommentTabs.push({
							title: that.$t('lang.recommended_by_our_shop'),
							id: 1
						});
						const arr = [];
						for (let i = 0; i < Math.ceil(data.length / 6); i++) {
							arr.push(data.slice(i * 6, (i + 1) * 6))
						};
						that.goodsGuessList.push(arr);
					};
				}else{
					const { data } = await that.$store.dispatch('setGoodsList', {
						type: true,
						intro:'best',
						page: 1,
						size: 30
					});
					
					if (Array.isArray(data) && data.length > 0) {
						that.recommentTabs.push({
							title: that.$t('lang.recommended_by_our_shop'),
							id: 1
						});
						const arr = [];
						for (let i = 0; i < Math.ceil(data.length / 6); i++) {
							arr.push(data.slice(i * 6, (i + 1) * 6))
						};
						that.goodsGuessList.push(arr);
					};
				}
 				
				//关联商品
				const { data: res } = await that.$store.dispatch('getLinkGoodsById', {
					goods_id: that.goodsId,
					page: 1,
					size: 30
				});
				
				if (Array.isArray(res) && res.length > 0) {
					that.recommentTabs.push({
						title: that.$t('lang.guess_love'),
						id: 2
					});
					const arr = [];
					for (let i = 0; i < Math.ceil(res.length / 6); i++) {
						arr.push(res.slice(i * 6, (i + 1) * 6))
					};
					that.goodsGuessList.push(arr);
				};
			},
			// 底部猜你喜欢模块
			async getGuessList() {
				let page = this.guessList.length / 10;
				
				page = Math.ceil(page) + 1;
				
				const { data: result } = await this.$store.dispatch('getGoodsGuessList', {
					warehouse_id: 0,
					area_id: 0,
					page: page,
					size: 10
				});
				
				if (Array.isArray(result) && result.length > 0) {
					this.guessList = [...this.guessList, ...result];
				};
				
				if (result.length < 10) this.isOver = true;
			},
			// 获取商品评论
			async getCommentList() {
				const { data, status } = await this.$store.dispatch('getCommentTotalById', {goods_id: this.goodsId});
				if (status != 'success') return this.showToast();
				this.commentTotal = {
					total: data.all || 0,
					good: parseInt(data.good / data.all * 100) + '%'
				};
				
				const { data: res } = await this.$store.dispatch('getGoodsCommentById', {
					goods_id: this.goodsId,
					rank: 'all',
					page: 1,
					size: 2
				});
				if (Array.isArray(res)) this.goodsComment = res;
				this.getDiscussions();
			},
			// 获取网友讨论圈
			async getDiscussions() {
				const { data, status } = await this.$store.dispatch('getDiscoverListById', {
					goods_id: this.goodsId,
					dis_type: 'all',
					page: 1,
					size: 2,
					id: 0
				});
				
				if (status == 'success') this.friendDis = data;
			},
			// 获取收货地址
			async getAddressList() {
				const { data, status } = await this.$store.dispatch('getuserAddressList');
				if (status == 'success') this.addressList = data;
			},
			// 获取地区数据
			async getRegionListData(region = 1, level = 1) {
				this.regionList = [];
				const { data } = await this.$store.dispatch('getRegionList', {
					region: region, 
					level: level
				});
				if (Array.isArray(data)) this.regionList = data;
			},
			// 修改商品属性
			async changeAttr(load = false) {
				const { data, status } = await this.$store.dispatch('setGoodsAttrOperById', {
					goods_id: this.goodsId,
					num: this.goodsNum,
					attr_id: this.seleceGoodsAtrrArray
				});
				if (status != 'success') return this.showToast();
				this.selectAttr = data.attr_name;
				this.goodsDetail.goods_number = data.stock;
				this.goodsDetail.shop_price = data.shop_price;
				this.goodsDetail.marketPrice = data.market_price;
				
				if(data.attr_img) this.goodsDetail.goods_thumb = data.attr_img;
				
				if(load) setTimeout(()=>{this.dscLoading = false},300);
			},
			selectOtherAddress() {
				this.currAddressSwiper = 1;
			},
			// 选择地址
			selectRegion(item) {
				if (item.level > 4) {
					this.$set(this.selectRegionArr, 3, item);
					this.closePop('address')
				} else {
					this.selectRegionArr.push(item);
					this.getRegionListData(item.id, item.level);
				}
			},
			// 重新选择地址
			changeSelect(i) {
				this.selectRegionArr = this.selectRegionArr.slice(0, i) || [];
				if (i == 0) {
					this.getRegionListData();
				} else {
					const { id, level } = this.selectRegionArr[i - 1];
					this.getRegionListData(id, level);
				}
			},
			// 点击头部导航
			changeNav(i) {
				if (i == this.currNav) return;
				this.currNav = i;
				
				if (i > 0) {
					let el = '';
					if (i == 1) el = '.comment_el'
					else if (i == 2) el = '.detail_el'
					else el = '.related_el';
					let statusHeight = uni.getSystemInfoSync().statusBarHeight || 0;
					// #ifdef APP-PLUS
					statusHeight = 44 + statusHeight + uni.upx2px(20);
					// #endif
					
					// #ifdef MP-WEIXIN
					statusHeight = 44 + this.menuButtonInfo.height + this.menuButtonInfo.top + uni.upx2px(20);
					// #endif
							
					const query = uni.createSelectorQuery().in(this);
					query.select(el).boundingClientRect(res => {
						const val = this.old.scrollTop;
						this.flag = false;
						uni.pageScrollTo({
						    scrollTop: res.top + val - statusHeight + 2,
						    duration: 150
						});
					}).exec();
					
				} else {
					this.flag = false;
					uni.pageScrollTo({
					    scrollTop: 0,
					    duration: 150
					});
				}
				if (!this.flag) {
					setTimeout(() => {
						this.flag = true;
					}, 150);
				}
			},
			// 按钮修改商品数量 
			changeGoodsNum(type) {
				if (type == 'add') {
					if(this.goodsNum < this.xiangouNum){
						this.goodsNum ++;
					}
				} else if (type == 'subtract') {
					if (this.goodsNum > this.minNum) {
						this.goodsNum --;
					}
				}
			},
			// 输入修改商品数量
			changeGoodsNumByInput(e) {
				if (e.detail.value < this.minNum) {
					this.goodsNum = this.minNum;
					this.showToast(`${this.$t('lang.zhishao')}${this.minNum}${this.$t('lang.jian')}`);
				} else {
					this.goodsNum = e.detail.value;
				}
			},
			// 切换推荐商品列表
			changeGoodsTab(i) {
				if (this.currGoodsList == i) return;
				this.currGoodsList = i;
				this.reCurSwipeItem = 0;
			},
			// 商品列表滑动分页
			onChangeRecomment(e) {
				this.reCurSwipeItem = e.detail.current || 0;
			},
			// 商品轮播图
			changeSwiper(e) {
				this.currSwiperPic = e.detail.current || 0;
			},
			// 显示底部弹框
			showPopup(res) {
				this.$refs[res].open()
				
				if(res == 'address'){
					this.regionShow = true;
				}
			},
			// 关闭底部弹框
			closePop(res) {
				this.$refs[res].close()
				
				if(res == 'goodsattr'){
					this.storeBtn = false;
				}
			},
			// 跳转商品详情
			detailLink(item) {
				if(item.get_presale_activity){
					uni.navigateTo({
						url:'/pagesA/presale/detail/detail?act_id='+item.get_presale_activity.act_id
					})
 				}else{
					uni.navigateTo({
						url:'/pagesC/goodsDetail/goodsDetail?id='+item.goods_id
					})
				}
			},
			// 勾选商品属性
			selectGoodsAttr(res, val) {
				if (this.seleceGoodsAtrrArray.includes(res.id)) {
					if (val.attr_type == 1) {
						// 单选属性
					} else {
						// 多选属性
						const seleceArr = [];
						val.attr_key.forEach(item => {
							if (this.seleceGoodsAtrrArray.includes(item.id)) {
								seleceArr.push(item.id);
							}
						});
						if (seleceArr.length > 1) this.seleceGoodsAtrrArray = this.seleceGoodsAtrrArray.filter(id => id != res.id);
						this.changeAttr();
					}
				} else {
					if (val.attr_type == 1) { 
						// 单选属性
						val.attr_key.forEach(item => {
							if (this.seleceGoodsAtrrArray.includes(item.id)) {
								this.seleceGoodsAtrrArray = this.seleceGoodsAtrrArray.filter(id => id != item.id);
							}
						});
						this.seleceGoodsAtrrArray.push(res.id);
					} else {
						// 多选属性
						this.seleceGoodsAtrrArray.push(res.id);
					}
					this.changeAttr();
				}
			},
			appShare() {
				let shareInfo = {
					href: this.$websiteUrl + 'goods/' + this.goodsId + '?parent_id=' + this.user_id + '&platform=APP',
					title: this.goodsDetail.goods_name,
					summary: this.goodsDetail.goods_brief,
					imageUrl: this.goodsDetail.goods_thumb
				};
				this.shareInfo(shareInfo, 'poster');
			},
			mpShare() {
				this.onGoodsShare();
			},
			onGoodsShare() {
				if (this.$isLogin()) {
					uni.showLoading({ title: this.$t('lang.loading') });
					let price = this.goodsDetail.goods_price || this.goodsDetail.shop_price;
					let o = {}
					
					// #ifdef MP-WEIXIN
					o = {
						goods_id: this.goodsId,
						ru_id: 0,
						price: price,
						share_type: this.goodsDetail.is_distribution,
						type: 0,
						platform: 'MP-WEIXIN',
						code_url:'pagesC/goodsDetail/goodsDetail',
						scene:`${this.goodsId}`,
						thumb:this.goodsDetail.gallery_list[0].img_url || this.goodsDetail.goods_img
					}
					// #endif
					
					// #ifdef APP-PLUS
					o = {
						goods_id: this.goodsId,
						price: price,
						share_type: this.goodsDetail.is_distribution,
						platform: 'APP',
						code_url:`${this.$websiteUrl}goods/${this.goodsId}`,
						thumb:this.goodsDetail.gallery_list[0].img_url || this.goodsDetail.goods_img
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
			//选中其他地址
			getRegionShow(){
				this.addressRadio = '';
				
				//关闭弹窗
				this.regionShow = false;
				this.closePop('address');
			},
			//运费
			shippingFee(val) {
				this.$store.dispatch('setShippingFee', {
					goods_id: this.goodsId,
					position: JSON.stringify(val)
				});
			},
			handleStore() {
				this.$refs['goodsattr'].open();
				this.storeBtn = true;
			},
			changeAddressRadio(res){
				let address = res.province_name == res.city_name ? `${res.province_name}${res.district_name}${res.street_name}${res.address}` : `${res.province_name}${res.city_name}${res.district_name}${res.street_name}${res.address}`;
				
				let o = {
					province:{ id:res.province, name:res.province_name },
					city:{ id:res.city, name:res.city_name },
					district:{ id:res.district, name:res.district_name },
					street:{ id:res.street, name:res.street_name },
					regionSplic:address
				}

				//替换mixins/form-processing this.regionOptionDate
				this.regionSplic = o;

				//选中的收货地址id
				this.addressRadio = res.id;

				//关闭收货地址弹窗
				this.closePop('address');
			},
			onSku(type) {
				this.addType = type == 0 ? 1 : 2;
				
				if (this.attr.length > 0) {
					this.$refs['goodsattr'].open();
				} else {
					if(this.addType == 2){
						this.onAddCartClicked(10)
					}else{
						this.onAddCartClicked(0)
					}
				}
			},
			async onAddCartClicked(type) {
				let newAttr = [];
				this.addCartClass = false;
			
				if (this.attr.length > 0) {
					newAttr = this.seleceGoodsAtrrArray;
				}
				
				//判断是否绑定手机号
				if(!uni.getStorageSync('bindMobilePhone') && this.$isLogin()){
					let roles = await this.$store.dispatch('setUserId',{type:true});
					if(roles.status == 'success'){
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
				}
				
				uni.showLoading({
					title: this.$t('lang.loading_submit'),
					mask: true
				});
				
				
				this.$store.dispatch('setAddCart', {
					goods_id: this.goodsId,
					num: this.goodsNum,
					spec: newAttr,
					warehouse_id: '0',
					area_id: '0',
					parent_id: '0',
					rec_type: type,
					cou_id: this.goodsDetail.best_price.cou_id > 0 ? this.goodsDetail.best_price.cou_id : 0
				}).then(res => {
					uni.hideLoading();
					let status = res.data.uc_id >= 0 ? res.data.status : res.data
					if (status == true) {
						if (type == 10) {
							if (this.$isLogin()) {
								uni.navigateTo({
									url: '/pages/checkout/checkout?rec_type=10&uc_id='+res.data.uc_id
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
						} else {
							this.addCartClass = true;
			
							uni.showToast({
								title: this.$t('lang.added_to_cart'),
								icon: 'success',
								duration: 1000
							});
			
							this.goodsDetail.cart_number = parseInt(this.goodsDetail.cart_number) + this.goodsNum;
			
							//获取购物车数量
							this.$store.dispatch('setCommonCartNumber');
							
							this.closePop('goodsattr');
						}
					} else {
						uni.showToast({
							title: status.msg,
							icon: 'none',
							duration: 1000
						});
					}
				});
			},
			//门店自提
			onStoreClicked() {
				if (this.$isLogin()) {
					uni.navigateTo({
						url: '/pagesC/store/store?id=' + this.goodsId + '&attr_id=' + this.seleceGoodsAtrrArray + '&num=' + this.goodsNum + '&isSingle=goods'
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
			},
			skuLink(){
				this.$refs['goodsattr'].open();
				this.addType = '';
			},
			onCart() {
				uni.switchTab({
					url: '/pages/cart/cart'
				});
			},
			//关闭Popup
			handelClose(val) {
				if (val == 'coupon') {
					this.couponShow = false;
				} else if (val == 'promotion') {
					this.promotionShow = false;
				} else if (val == 'base') {
					this.showBase = false;
					this.storeBtn = false;
				} else if (val == 'volume') {
					this.volumePriceShow = false;
				} else if (val == 'mpshare') {
					this.mpShareShow = false;
				} else if (val == 'mpshareImg') {
					this.shareImgShow = false;
				}
			},
			//属性图片切换相册图
			onSelectImg(item,index){
				let gallery_index = this.swiperData.findIndex(v => v.img_url == item.attr_gallery_flie);
				this.attrColorIndex = index;

				if(gallery_index != undefined){
					this.current = gallery_index;
				}
				
				if(item.attr_img_site){
					this.$outerHref(item.attr_img_site);
				}
			},
			historyAdd(){
				let time = new Date().getTime();
				let price = this.goodsDetail.shop_price_formated;
				this.$store.dispatch('setHistoryAdd', {
					id: this.goodsDetail.goods_id,
					name: this.goodsDetail.goods_name,
					img: this.goodsDetail.goods_thumb,
					price: price,
					addtime: time
				});
			},
			goodsAloneSale(){
				uni.navigateTo({
					url:`/pages/goodslist/goodslist?id=${this.goodsDetail.cat_id}`
				})
			},
			//视频号直播
			liveHref(){
				//视频号直播跳转
				uni.openChannelsLive({
					finderUserName:uni.getStorageSync('configData').wxapp_media_id || 'sphpSsGkwkBzWJI',
					feedId:uni.getStorageSync('channelsLive').feedId || '',
					nonceId:uni.getStorageSync('channelsLive').nonceId || '',
				})
			},
			preview(url){
				
				let urlArr = [];
				urlArr.push(url);
				
				uni.previewImage({
					current:1,
					urls:urlArr,
					longPressActions:{
						itemList:[this.$t('lang.save_picture'),this.$t('lang.recognition_qr_code'),this.$t('lang.qr_code')],
						success:(res)=>{
							console.log(res)
						}
					}
				})
			},
		}
	}
</script>

<style lang="scss" scoped>
.mlr50{
 margin-left: 15px;
 margin-right: 15px;
}
.goods_detail_content {
	padding-bottom: 120rpx;
	/* start--Retina 屏幕下的 1px 边框--start */
	.u-border,
	.u-border-bottom,
	.u-border-left,
	.u-border-right,
	.u-border-top,
	.u-border-top-bottom {
		position: relative
	}
	
	.u-border-bottom:after,
	.u-border-left:after,
	.u-border-right:after,
	.u-border-top-bottom:after,
	.u-border-top:after,
	.u-border:after {
		/* #ifndef APP-NVUE */
		content: ' ';
		/* #endif */
		position: absolute;
		left: 0;
		top: 0;
		pointer-events: none;
		box-sizing: border-box;
		-webkit-transform-origin: 0 0;
		transform-origin: 0 0;
		// 多加0.1%，能解决有时候边框缺失的问题
		width: 199.8%;
		height: 199.7%;
		transform: scale(0.5, 0.5);
		border: 0 solid #F9F9F9;
		z-index: 2;
	}
	
	.u-border-top:after {
		border-top-width: 1px
	}
	
	.u-border-left:after {
		border-left-width: 1px
	}
	
	.u-border-right:after {
		border-right-width: 1px
	}
	
	.u-border-bottom:after {
		border-bottom-width: 1px
	}
	
	.u-border-top-bottom:after {
		border-width: 1px 0
	}
	
	.u-border:after {
		border-width: 1px
	}
	/* end--Retina 屏幕下的 1px 边框--end */
	
	/* start--去除button的所有默认样式--start */
	.u-reset-button {
		padding: 0;
		font-size: inherit;
		line-height: inherit;
		background-color: transparent;
		color: inherit;
	}
	
	.u-reset-button::after {
	   border: none;
	}
	/* end--去除button的所有默认样式--end */
	
	/* 导航 start */
	.nav_bar {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		background-color: rgba(0,0,0,0);
		z-index: 96;
		.status_bar {
			height: var(--status-bar-height);
			width: 100%;
		}
		.wx_nav_bar {
			width: 100%;
			.wx_nav_left {
				position: relative;
				display: flex;
				justify-content: center;
				align-items: center;
				height: 100%;
				border: 1px solid rgba(0,0,0, 0.1);
				background-color: rgba(255,255,255, 0.6);
				box-sizing: border-box;
				z-index: 96;
				.icon-find-fanhui {
					font-size: 15px;
				}
				.line {
					width: 1px;
					height: 18px;
					margin: 0 8px;
					background-color: rgba(252,252,252, 0.3);
				}
				.shortcut {
					position: absolute;
					left: 0;
					top: 44px;
					display: flex;
					flex-direction: column;
					border-radius: 12rpx;
					background-color: rgba(255,255,255, 0.95);
					z-index: 101;
					&:before {
						content: '';
						position: absolute;
						left: 44px;
						top: -26rpx;
						transform: translateX(50%);
						width: 0;
						height: 0;
						line-height: 0;
						font-size: 0;
						border: 14rpx solid transparent;
						border-bottom-color: rgba(255,255,255, 0.95);
					} 
				}
				.shortcut_item {
					width: 290rpx;
					height: 95rpx;
					line-height: 95rpx;
					padding: 0 36rpx;
					font-size: 30rpx;
					text-align: left;
					color: #000;
					box-sizing: border-box;
					.iconfont {
						margin-right: 12rpx;
						font-size: 44rpx;
						color: #000!important;
						vertical-align: -6rpx;
					}
				}
			}
			.nav_c {
				justify-content: space-around;
				height: 44px;
			}
		}
		.nav_list {
			display: flex;
			justify-content: space-between;
			align-items: center;
			height: 44px;
			.iconfont {
				padding-top: 2px;
				font-size: 14px;
			}
		}
		.icon_wrap {
			display: flex;
			justify-content: center;
			align-items: center;
			width: 26px;
			height: 26px;
			border-radius: 50%;
			color: #fff;
			background-color: rgba(0,0,0,0.4);
		}
		.nav_l {
			width: 26px;
			height: 26px;
			margin-left: 10px;
			.iconfont {
				padding-top: 1px;
			}
		}
		.nav_c {
			display: flex;
			justify-content: space-between;
			align-items: center;
			opacity: 0;
			.nav_item {
				margin: 0 7px;
				font-size: 15px;
			}
			.active_nav {
				position: relative;
				font-size: 16px;
				font-weight: 700;
				&:after {
					content: '';
					position: absolute;
					bottom: 0;
					left: 0;
					width: 100%;
					height: 3px;
					background: linear-gradient(90deg, #F91F28 0%, rgba(255, 79, 46, 0.35) 100%);
				}
			}
		}
		.nav_r {
			display: flex;
			justify-content: space-between;
			align-items: center;
			height: 26px;
			margin-right: 10px;
			.icon_wrap:last-child {
				position: relative;
				margin-left: 10px;
				.shortcut {
					position: absolute;
					right: 0;
					top: 44px;
					display: flex;
					flex-direction: column;
					border-radius: 12rpx;
					background-color: rgba(255,255,255, 0.95);
					z-index: 101;
					&:before {
						content: '';
						position: absolute;
						right: 26rpx;
						top: -26rpx;
						transform: translateX(50%);
						width: 0;
						height: 0;
						line-height: 0;
						font-size: 0;
						border: 14rpx solid transparent;
						border-bottom-color: rgba(255,255,255, 0.95);
					} 
				}
				.shortcut_item {
					width: 290rpx;
					height: 95rpx;
					line-height: 95rpx;
					padding: 0 36rpx;
					font-size: 30rpx;
					text-align: left;
					color: #000;
					box-sizing: border-box;
					.iconfont {
						margin-right: 12rpx;
						font-size: 44rpx;
						color: #000!important;
						vertical-align: -6rpx;
					}
				}
			}
		}
	}
	/* 导航 end */
	
	/* 商品轮播 start */
	.swiper_warp {
		position: relative;
		.swiper {
			width: 100%;
			height: 750rpx;
			.swiper_img {
				width: 100%;
				height: 750rpx;
			}
		}
		.indicator_dots {
			position: absolute;
			bottom: 60rpx;
			right: 0;
			min-width: 100rpx;
			height: 50rpx;
			padding: 0 10rpx;
			line-height: 50rpx;
			border-top-left-radius: 25rpx;
			border-bottom-left-radius: 25rpx;
			text-align: center;
			box-sizing: border-box;
			color: #fff;
			z-index:10;
			background-color: rgba(41, 47, 54, 0.4);
			.curr_dots {
				font-size: 34rpx;
			}
		}
		.video_ico {
			position: absolute;
			bottom: 60rpx;
			left: 50%;
			transform: translateX(-50%);
			display: flex;
			align-items: center;
			height: 60rpx;
			padding: 0 24rpx 0 8rpx;
			border-radius: 30rpx;
			background-color: rgba(255,255,255, 0.8);
			z-index:10;
			.play_ico {
				flex: none;
				font-size: 50rpx;
				color: #FF5E4D;
			}
			.play_txt {
				margin-left: 12rpx;
				height: 60rpx;
				line-height: 60rpx;
				font-size: 24rpx;
				color: #262626;
			}
		}
	}
	/* 商品轮播 end */
	
	/* 视频 start */
	.video_wrap {
		width: 100%;
		height: 750rpx;
		.video {
			display: block;
			width: 100%;
			height: 650rpx;
		}
		.controls_pause_wrap {
			display: flex;
			justify-content: center;
			align-items: center;
			width: 100%;
			height: 100rpx;
			background-color: #000;
		}
		.controls_pause {
			height: 52rpx;
			line-height: 52rpx;
			padding: 0 30rpx;
			border-radius: 26rpx;
			text-align: center;
			font-size: 24rpx;
			background-color: rgba(255,255,255, 0.8);
		}
	}
	/* 视频 end */
	
	.goods_module_wrap {
		border-radius: 22rpx;
		background-color: #fff;
	}
	
	.mt_20 {
		margin-top: 20rpx;
	}
	
	.goods_module_btn {
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 0 40rpx;
		min-width: 228rpx;
		height: 62rpx;
		line-height: 58rpx;
		border-radius: 33rpx;
		border: 2rpx solid #F9F9F9;
		text-align: center;
	}
	
	.stockout_zhanwei {
		height: 70rpx;
	}
	
	/* 商品基本信息 start */
	.activity_goods {
		position: relative;
		overflow: hidden;
		.activity_goods_pic {
			width: 100%;
			display: block;
			height: 100rpx;
		}
		.activity_goods_left {
			position: absolute;
			top: 0;
			left: 20rpx;
			height: 100%;
			display: flex;
			align-items:center;
		}
		.activity_goods_right {
			position: absolute;
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			top: 0;
			right: 108rpx;
			transform: translateX(50%);
			height: 100%;
			.count_down_txt {
				margin-bottom: 10rpx;
				font-size: 20rpx;
				line-height: 1;
				color: #FF3616;
			}
		}
	}
	
	.goods_base_info {
		border-top-right-radius: 0;
		border-top-left-radius: 0;
		padding: 10rpx 30rpx 30rpx;
		.goods_price {
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			padding-top: 20rpx;
		}
		.wrap_box {
			display: flex;
			align-items: baseline;
			flex: 1;
			.yishou {
				margin-left: 20rpx;
			}
			.rate{
				margin-left: 4rpx;
				padding-right: 10rpx;
				font-size: 24rpx;
				color: #999;
			}
		}
		
		.goods_rate {
			margin-top: -30rpx;
		}
		
		.right{
			display: flex;
		}
		
		.goods_share,.collection {
			display: flex;
			flex-direction: column;
			align-items: center;
			.share_txt {
				font-size: 24rpx;
				line-height: 1;
				margin-top: 20rpx;
				color: #2E2E2E;
			}
		}
		.collection{
			margin-left: 30rpx;
		}
		
		.goods_tag {
			display: flex;
			image {
				display: block;
				height: 34rpx;
				margin-right: 9rpx;
			}
		}
		.padding_top20 {
			padding-top: 20rpx;
		}
		.goods_name_wrap {
			display: flex;
			align-items: flex-start;
			padding-top: 20rpx;
			.goods_share {
				flex: none;
				margin:0 0 0 20rpx;
			}
			.country_icon{
				width: 40rpx;
				height: 28rpx;
				position: relative;
				top: 5rpx;
			}
			.country_icon_text{
				display: inline-block;
				font-size: 22rpx;
				font-weight: normal;
				color: #666;
				.icon_text_name{
					padding-left: 7rpx;
				}
			}
		}
		.goods_name {
			flex: auto;
			font-size: 30rpx;
			font-weight: 700;
			line-height: 40rpx;
			.ziying {
				display: inline-block;
				padding: 0 10rpx;
				margin-right: 10rpx;
				height: 32rpx;
				line-height: 32rpx;
				border-radius: 16rpx;
				text-align: center;
				background: linear-gradient(270deg, #FF4F2E 0%, #F91F28 100%);
				font-size: 20rpx;
				font-weight: 400;
				color: #fff;
				vertical-align: 4rpx;
			}
		}
		.goods_explain {
			margin-top: 10rpx;
			line-height: 40rpx;
		}
		.open_vip {
			display: flex;
			justify-content: space-between;
			align-items: center;
			height: 84rpx;
			padding: 0 30rpx;
			margin-top: 20rpx;
			border-radius: 42rpx;
			background-color: #FEF3E4;
			.vip_ico {
				width: 48rpx;
				height: 48rpx;
			}
			.vip_content {
				flex: auto;
				padding: 0 14rpx;
				white-space: nowrap;
				overflow: hidden;
				text-overflow: ellipsis;
				.vip_price {
					padding-left: 10rpx;
					color: #F22E20;
				}
			}
		}
	}
	/* 商品基本信息 end */
	
	/* 优惠与活动 start */
	.activity_wrap {
		display: flex;
		align-items: baseline;
		padding: 30rpx 0;
		&:nth-child(n + 2) {
			.activity_main {
				// border-top: 2rpx solid #ccc;
			}
		}
		.activity_title {
			flex: none;
			margin: 0 30rpx;
			font-weight: 700;
		}
		.activity_main {
			flex: auto;
			display: flex;
			justify-content: space-between;
			align-items: flex-start;
			padding-right: 30rpx;
			.activity_list {
				flex: auto;
			}
			.activity_item_flex {
				display: flex;
				flex-wrap: wrap;
				align-items: baseline;
			}
			.activity_item {
				.title_ico {
					width: 34rpx;
					vertical-align: sub;
					display: inline-block;
				}
				.location_ico {
					flex: none;
					height: 30rpx;
					margin-right: 8rpx;
				}
				.address_text {
					width: 540rpx;
					white-space: nowrap;
					overflow: hidden;
					text-overflow: ellipsis;
					color: #999;
				}
				.server_item {
					display: flex;
					align-items: center;
					margin-right: 25rpx;
					// line-height: 2;
					min-width: 0;					
					// &:last-child{
					// 	margin-right: 0;
					// }
					.location_ico {
						height: 34rpx;
					}
					.text {
						overflow: hidden;
						display: -webkit-box;
						-webkit-line-clamp: 1;
						-webkit-box-orient: vertical; 
					}
				}
				.activity_bg {
					padding: 0 10rpx;
					height: 40rpx;
					line-height: 40rpx;
					text-align: center;
					font-size: 26rpx;
					color: #F44C36;
					background: #FEE9E6;
					border-radius: 4rpx;
					margin: 22rpx 16rpx 0 0;
				}
				.margin_top0 {
					margin-top: 0;
				}
				.activity_tips {
					white-space: pre-line;
				}
				.coupon_bg_wrap {
					overflow: hidden;
					display: flex;
					margin: 22rpx 16rpx 0 0;
					.coupon_bg {
						flex: auto;
						position: relative;
						display: flex;
						align-items: center;
						justify-content: center;
						min-width: 138rpx;
						height: 36rpx;
						line-height: 36rpx;
						padding: 0 10rpx;
						text-align: center;
						border-radius: 4rpx;
						border: 2rpx solid #F44C36;
						color: #F44C36;
						&:before {
							content: '';
							position: absolute;
							top: 50%;
							left: -10rpx;
							transform: translateY(-50%);
							width: 12rpx;
							height: 12rpx;
							border-radius: 50%;
							border: 2rpx solid #F44C36;
							background-color: #fff;
							z-index: 3;
						}
						&:after {
							content: '';
							position: absolute;
							top: 50%;
							right: -10rpx;
							transform: translateY(-50%);
							width: 12rpx;
							height: 12rpx;
							border-radius: 50%;
							border: 2rpx solid #F44C36;
							background-color: #fff;
							z-index: 3;
						}
					}
				}
			}
			.iconfont {
				flex: none;
				margin-left: 30rpx;
			}
		}
	}
	/* 优惠与活动 end */
	.server_wrap {
		align-items: stretch;
	}
	/* 商品评价 start */
	.title_box {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 30rpx;
		.title_left {
			position: relative;
			font-weight: 700;
			padding-left: 16rpx;
			&:before {
				position: absolute;
				top: 50%;
				left: 0;
				transform: translateY(-50%);
				content: '';
				width: 6rpx;
				height: 32rpx;
				background: linear-gradient(180deg, #F91F28 0%, rgba(255, 79, 46, 0.35) 100%);
			}
			.comment_count {
				margin-left: 30rpx;
				font-weight: normal;
			}
		}
		.drgree_of_praise {
			margin-right: 8rpx;
		}
	}
	.comment-items {
		padding-bottom: 30rpx;
		.comitem {
			padding: 30rpx 0;
			&:nth-child(n + 2) {
				position: relative;
				&:after {
					/* #ifndef APP-NVUE */
					content: ' ';
					/* #endif */
					position: absolute;
					left: 0;
					top: 0;
					pointer-events: none;
					box-sizing: border-box;
					-webkit-transform-origin: 0 0;
					transform-origin: 0 0;
					// 多加0.1%，能解决有时候边框缺失的问题
					width: 199.8%;
					height: 199.7%;
					transform: scale(0.5, 0.5);
					border: 0 solid #F9F9F9;
					border-top-width: 1px;
					z-index: 2;
				}
			}
		}
		.item_header {
			display: flex;
			align-items: center;
			padding: 0 30rpx;
			.head_l {
				flex: none;
				width: 68rpx;
				height: 68rpx;
				border-radius: 50%;
			}
			.head_r {
				flex: 1;
				display: flex;
				flex-direction: column;
				justify-content: space-between;
				height: 68rpx;
				margin-left: 20rpx;
			}
			.com_name {
				line-height: 1;
			}
			.com_time {
				display: flex;
				justify-content: space-between;
				.comment_time {
					color: #999;
					line-height: 1;
				}
			}
			.rate_wrap {
				line-height: 1;
				.icon-collection-alt {
					margin-right: 10rpx;
					line-height: 1;
					color: #DDD;
				}
				.color_red {
					color: #E93B3D;
				}
			}
		}
		.item_body {
			padding-top: 30rpx;
			.comment_con {
				margin: 0 30rpx;
				line-height: 40rpx;
				word-break:break-all;
				display:-webkit-box;
				-webkit-line-clamp:2;
				-webkit-box-orient:vertical;
				overflow:hidden;
			}
			.imgs_scroll {
				padding: 30rpx 30rpx 0;
				.com_img {
					width: 204rpx;
					height: 204rpx;
					border-radius: 10rpx;
					&:nth-child(n + 2) {
						margin-left: 10rpx;
					}
				}
			}
		}
		.item_footer {
			margin: 20rpx 30rpx 0;
			line-height: 40rpx;
			word-break:break-all;
			display:-webkit-box;
			-webkit-line-clamp:2;
			-webkit-box-orient:vertical;
			font-size: 26rpx;
			overflow:hidden;
			color: #999;
		}
	}
	.friend_discuss {
		padding: 0 30rpx 30rpx;
		.discuss_item {
			display: flex;
			align-items: baseline;
			&:nth-child(n + 2) {
				margin-top: 24rpx;
			}
			.dis_label {
				height: 38rpx;
				line-height: 38rpx;
				font-size: 24rpx;
				padding: 0 10rpx;
				text-align: center;
				border-radius: 4rpx;
				margin-right: 10rpx;
				color: #fff;
				background-color: #FF320D;
			}
			.dis_value {
				flex: 1;
				line-height: 1;
				white-space: nowrap;
				overflow: hidden;
				text-overflow: ellipsis;
			}
			.dis_time {
				margin-left: 10rpx;
				line-height: 1;
				font-size: 24rpx;
				color: #999;
			}
		}
	}
	.no_dis {
		display: flex;
		flex-direction: column;
		align-items: center;
		padding-bottom: 40rpx;
		.no_dis_title {
			margin: 30rpx 0;
		}
	}
	/* 商品评价 end */
	
	/* 店铺 start */
	.store_hade {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 30rpx;
		.store_logo {
			width: 80rpx;
			height: 80rpx;
			border-radius: 6rpx;
			background-color: #E93B3D;
		}
		.store_name_rate {
			flex: 1;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			height: 80rpx;
			margin-left: 22rpx;
			.sto_name {
				flex: auto;
				font-weight: 700;
				line-height: 1;
				color: #282828;
			}
			.sto_rate_wrap {
				display: flex;
			}
			.sto_rate {
				display: flex;
				justify-content: center;
				align-items: center;
				height: 42rpx;
				line-height: 42rpx;
				padding: 0 12rpx;
				border-radius: 20rpx;
				font-size: 24rpx;
				background-color: #F6F6F6;
				.icon-collection-alt {
					margin-left: 10rpx;
					color: #DDD;
				}
				.color_red {
					color: #E93B3D;
				}
			}
		}
	}
	.store_body {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 30rpx;
		.count_item {
			flex: auto;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			align-items: center;
			text-align: center;
			.count_text {
				font-weight: 700;
			}
			.serve_rate {
				font-size: 24rpx;
				text:nth-child(n + 2) {
					margin-left: 10rpx;
				}
			}
		}
		.line_wrap {
			flex: none;
			height: 44rpx;
		}
		.color_red {
			color: #EC3937;
		}
		.color_green {
			color: #18C461;
		}
	}
	.store_footer {
		display: flex;
		justify-content: center;
		padding-bottom: 30rpx;
		.store_btn {
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 0 24rpx;
			height: 62rpx;
			line-height: 58rpx;
			border-radius: 33rpx;
			border: 2rpx solid #F9F9F9;
			text-align: center;
			&:nth-child(n + 2) {
				margin-left: 35rpx;
			}
			
			&.active{
				border-color: #F91F28;
				color: #F91F28;
			}
			
			.store_btn_ico {
				height: 32rpx;
				margin-right: 8rpx;
				vertical-align: middle;
			}
		}
	}
	/* 店铺 end */
	
	/* 搜索 start */
	.search_wrap {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 30rpx 0 30rpx 30rpx;
		.search_input {
			flex: auto;
			display: flex;
			align-items: center;
			justify-content: flex-start;
			height: 66rpx;
			padding-left: 16rpx;
			border-radius: 33rpx;
			color: #A2A2A2;
			background-color: #F2F2F2;
			.icon-home-search {
				margin-right: 10rpx;
				font-size: 34rpx;
			}
		}
		.search_btn {
			margin: 0 30rpx;
		}
	}
	.top_search {
		display: flex;
		flex-wrap: wrap;
		align-items: baseline;
		padding: 0 30rpx;
		.top_search_lable {
			color: #000;
		}
		.top_search_keyword {
			height: 54rpx;
			line-height: 54rpx;
			padding: 0 25rpx;
			margin: 0 15rpx 30rpx 0;
			border-radius: 27rpx;
			color: #272727;
			background-color: #F2F2F2;
			
			&:last-child{
				margin-right: 0;
			}
		}
	}
	.recomment {
		.re_tabs {
			display: flex;
			justify-content: space-around;
			align-items: center;
			padding: 30rpx 0;
			.re_tabs_item {
				flex: none;
				text-align: center;
				height: 50rpx;
			}
			.active_tab {
				position: relative;
				font-weight: 700;
				&:after {
					content: '';
					position: absolute;
					left: 50%;
					bottom: 0;
					transform: translateX(-50%);
					width: 50%;
					height: 6rpx;
					background: linear-gradient(90deg, #F91F28 0%, rgba(255, 79, 46, 0.35) 100%);
				}
			}
		}
		.goods_list_wrap {
			position: relative;
			padding: 0 20rpx 36rpx;
			.swiper {
				.glist {
					display: flex;
					flex-wrap: wrap;
					align-content: flex-start;
				}
				.gitem {
					width: 33.33%;
					padding: 0 10rpx;
					box-sizing: border-box;
					.goods_pic {
						display: block;
						width: 100%;
						height: 216.66rpx;
					}
					.goods_name_price {
						display: flex;
						flex-direction: column;
						justify-content: space-between;
						height: 155rpx;
						padding: 20rpx 0;
						box-sizing: border-box;
						.country_icon_image{
							width: 43rpx;
							height: 30rpx;
							padding-right: 7rpx;
							position: relative;
							top: 5rpx;
						}
					}
					.goods_name {
						word-break: break-all;
						display: -webkit-box;
						-webkit-line-clamp: 2;
						-webkit-box-orient: vertical;
						line-height: 38rpx;
						color: #272727;
						overflow: hidden;
					}
				}
			}
			.swiper_370 {
				height: 370rpx;
			}
			.swiper_740 {
				height: 740rpx;
			}
			.indicator_dots {
				position: absolute;
				bottom: 30rpx;
				left: 50%;
				transform: translateX(-50%);
				display: flex;
				height: 6rpx;
				background: #F1F1F1;
				border-radius: 3rpx;
				.indicator {
					width: 33rpx;
					height: 6rpx;
					border-radius: 3rpx;
				}
				.active_indicator {
					background-color: #F22E20;
				}
			}
		}
	}
	/* 搜索 end */
	
	/* 推荐文章 start */
	.article_item {
		display: flex;
		padding: 30rpx;
		&:first-child {
			padding-top: 0!important;
		}
		.article_img {
			flex: none;
			width: 136rpx;
			height: 136rpx;
			margin-right: 22rpx;
			border-radius: 10rpx;
		}
		.article_content {
			flex: auto;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			padding-bottom: 20rpx;
			.art_title {
				word-break: break-all;
				display: -webkit-box;
				-webkit-line-clamp: 2;
				-webkit-box-orient: vertical;
				overflow: hidden;
				line-height: 40rpx;
			}
			.art_time {
				color: #999;
			}
		}
	}
	/* 推荐文章 end */
	
	/* 图文详情 start */
	.padding_b_30 {
		padding-bottom: 30rpx;
	}
	.image_text_title {
		display: flex;
		align-items: center;
		padding: 0 30rpx 30rpx;
		color: #272727;
		.title_ico {
			width: 28rpx;
			height: 28rpx;
			margin-right: 12rpx;
		}
	}
	.goods_desc {
		padding: 0 10rpx;
	}
	.goods_attr_parameter {
		margin: 0 10rpx;
		.tr {
			display: flex;
			justify-content: flex-start;
			color: #939393;
			.td {
				flex: auto;
				display: flex;
				flex-wrap: wrap;
				align-items: center;
				min-height: 80rpx;
				line-height: 40rpx;
				padding: 0 40rpx;
				&:first-child {
					flex: none;
					width: 120rpx;
				}
			}
			.color_27 {
				color: #272727;
			}
		}
	}
	.attr_wrap {
		.tr {
			&:nth-child(n + 7) {
				display: none;
			}
		}
	}
	.open_goods_attr {
		display: flex;
		justify-content: center;
		padding-top: 30rpx;
		.view_more_btn {
			color: #939393;
			.iconfont {
				margin-left: 20rpx;
			}
		}
	}
	/* 图文详情 end */
	
	/* 提交订单栏 start */
	.submit_bar {
		position: fixed;
		left: 0;
		bottom: 0;
		width: 100%;
		height: 100rpx;
		display: flex;
		align-items: center;
		padding-bottom: 0;  
		padding-bottom: constant(safe-area-inset-bottom);  
		padding-bottom: env(safe-area-inset-bottom);
		background-color: #fff;
		z-index: 96;
		.stockout {
			position: absolute;
			top: 0;
			left: 0;
			transform: translateY(-100%);
			width: 100%;
			min-height: 70rpx;
			padding: 15rpx;
			color: #E17B32;
			background-color: #FCF9DA;
			box-sizing: border-box;
			z-index: 101;
			.stockout_txt {
				line-height: 40rpx;
				text-align: center;
				display: block;
			}
		}
		.function_wrap {
			display: flex;
			align-items: center;
			padding-left: 30rpx;
		}
		.function_item {
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			margin-right: 40rpx;
			line-height: 1;
			font-size: 24rpx;
			color: #666;
			.title_ico {
				height: 37rpx;
				margin-bottom: 14rpx;
			}
		}
		.badge_parent {
			position: relative;
			.info_num {
				position: absolute;
				top: -8rpx;
				left: 50%;
				transform: translateX(8rpx);
				min-width: 24rpx;
				height: 24rpx;
				text-align: center;
				line-height: 24rpx;
				border-radius: 50%;
				border: 2rpx solid #F91F28;
				font-size: 20rpx;
				color: #F91F28;
				background-color: #fff;
				z-index: 1;
				padding: 2rpx;
			}
		}
	}
	.sub_btn {
		flex: auto;
		display: flex;
		justify-content: center;
		align-items: center;
		flex-direction: column;
		height: 80rpx;
		border-radius: 40rpx;
		margin-right: 20rpx;
		font-weight: 700;
		color: #fff;
		line-height: 1.3;
		font-size: 15px;
		
		.jiage{
			font-weight: normal;
			font-size: 12px;
		}
	}
	.yellow_btn {
		background-color: #FFC707;
	}
	.red_btn {
		background-color: #F91F28;
	}
	/* 提交订单栏 end */
	
	/* 优惠弹框 start */
	.pop_content {
		padding-bottom: env(safe-area-inset-bottom);
		border-top-left-radius: 30rpx;
		border-top-right-radius: 30rpx;
		background-color: #fff;
		.pop_header {
			position: relative;
			padding: 0 30rpx;
			text-align: center;
			height: 90rpx;
			line-height: 90rpx;
			.icon-find-fanhui {
				position: absolute;
				top: 50%;
				left: 30rpx;
				transform: translateY(-50%);
				font-size: 28rpx;
			}
			.pop_title {
				font-size: 32rpx;
				font-weight: 700;
				line-height: 90rpx;
				color: #282828;
			}
			.close_img {
				position: absolute;
				top: 50%;
				right: 30rpx;
				transform: translateY(-50%);
				width: 44rpx;
				height: 44rpx;
			}
		}
		.pop_scroll_view {
			padding: 0 30rpx 30rpx;
			.label_text {
				// margin-bottom: 0.2rem;
				font-weight: 700;
				color: #000;
			}
			.list_wrap {
				margin-bottom: 20rpx;
			}
			.pop_activity_item {
				display: flex;
				align-items: baseline;
				&:nth-child(n + 2) {
					margin-top: 20rpx;
				}
				.activity_bg {
					flex: none;
					height: 38rpx;
					line-height: 38rpx;
					padding: 0 10rpx;
					text-align: center;
					border-radius: 4rpx;
					margin-right: 16rpx;
					font-size: 24rpx;
					color: #F44C36;
					background: #FEE9E6;
				}
				.activity_tips {
					flex: auto;
					line-height: 40rpx;
				}
			}
		}
		.coupons_list {
			.coupons_item {
				display: flex;
				margin-top: 20rpx;
			}
			.coupons_left {
				position: relative;
				flex: none;
				display: flex;
				flex-direction: column;
				align-items: center;
				width: 200rpx;
				height: 200rpx;
				border-radius: 20rpx 0 0 20rpx;
				color: #fff;
				background-color: #f52923;
				.coupon_price {
					margin-top: 30px;
					font-size: 52rpx;
					line-height: 1;
				}
				.coupon_desc {
					margin-top: 10px;
					padding: 0 20rpx;
					text-align: center;
					font-size: 26rpx;
				}
				&:after {
					content: '';
					position: absolute;
					bottom: 0;
					right: 0;
					width: 40rpx;
					height: 40rpx;
					transform: translate(50%, 50%);
					border-radius: 50%;
					background-color: #fff;
					z-index: 2;
				}
				&:before {
					content: '';
					position: absolute;
					top: 0;
					right: 0;
					width: 40rpx;
					height: 40rpx;
					transform: translate(50%, -50%);
					border-radius: 50%;
					background-color: #fff;
					z-index: 2;
				}
			}
			.coupons_right {
				overflow: hidden;
				position: relative;
				flex: auto;
				display: flex;
				flex-direction: column;
				justify-content: space-between;
				max-width: calc(100% - 200rpx);
				border-radius: 0 20rpx 20rpx 0;
				border: 1px solid #f2d2d2;
				border-left-width: 0;
				background-color: #fbf2f2;
				.coupon_title {
					margin: 16rpx 20rpx 0;
					font-size: 26rpx;
					color: #999;
					word-break: break-all;
					display: -webkit-box;
					-webkit-line-clamp: 2;
					-webkit-box-orient: vertical;
					overflow: hidden;
					.coupon_tag {
						display: inline-block;
						padding: 0 16rpx;
						height: 48rpx;
						line-height: 48rpx;
						border-radius: 10rpx;
						margin-right: 20rpx;
						text-align: center;
						font-size: 26rpx;
						color: #fff;
						background-color: rgba(245,41,35,.6);
					}
				}
				.get_coupon_time {
					display: flex;
					justify-content: space-between;
					align-items: center;
					padding-bottom: 20rpx;
					.coupon_time {
						flex: 1;
						margin: 0 20rpx;
						font-size: 26rpx;
						white-space: nowrap;
						overflow: hidden;
						text-overflow: ellipsis;
						color: #999;
					}
					.coupon_btn {
						// min-width: 160rpx;
						height: 48rpx;
						border-radius: 26rpx;
						text-align: center;
						padding: 0 15rpx;
						font-size: 24rpx;
						margin-right: 20rpx;
						border: 2rpx solid #F52923;
						color: #fff;
						background-color: #F52923;
					}
					
					.u-reset-disabled{
						background: #999999;
						color: #FFFFFF;
						border-color: #999999;
					}
					
					.is_get_btn {
						color: #F52923;
						background-color: transparent;
					}
				}
				&:after {
					content: '';
					position: absolute;
					bottom: -24rpx;
					left: -22rpx;
					width: 42rpx;
					height: 42rpx;
					// transform: translate(-50%, -50%);
					border-radius: 50%;
					background-color: #f2d2d2;
				}
				&:before {
					content: '';
					position: absolute;
					top: -24rpx;
					left: -22rpx;
					width: 42rpx;
					height: 42rpx;
					// transform: translate(-50%, -50%);
					border-radius: 50%;
					background-color: #f2d2d2;
				}
			}
		}
	}
	/* 优惠弹框 end */
	
	/* 活动弹框 start */
	.activity_pup {
		background-color: #F4F4F4;
	}
	.act_item {
		padding: 30rpx;
		border-radius: 20rpx;
		background-color: #fff;
		&:nth-child(n + 2) {
			margin-top: 20rpx;
		}
		.act_main {
			display: flex;
			justify-content: space-between;
			align-items: center;
			.act_left {
				flex: auto;
			}
			.act_label {
				display: inline-block;
				height: 38rpx;
				line-height: 38rpx;
				padding: 0 10rpx;
				text-align: center;
				border-radius: 4rpx;
				margin-right: 16rpx;
				font-size: 24rpx;
				color: #F44C36;
				background: #FEE9E6;
			}
		}
		.recommend_title {
			.activity_price {
				color: #F22E20;
			}
		}
		.img_list {
			display: flex;
			align-items: center;
			margin-top: 16rpx;
			.img_wrap {
				&:nth-child(n + 2) {
					position: relative;
					padding-left: 56rpx;
					&:before {
						position: absolute;
						top: 50%;
						left: 28rpx;
						transform: translate(-50%, -50%);
						content: '+';
						font-size: 40rpx;
					}
				}
				.goods_pic {
					display: block;
					width: 110rpx;
					height: 110rpx;
					border-radius: 10rpx;
				}
			}
			.iconfont {
				margin-left: 10rpx;
			}
		}
	}
	/* 活动弹框 end */
	
	.media_qrcod_pic{
		flex: none;
		width: 100%;
		height: 100%;
	}
	
	/* 商品属性弹框 start */
	.goods_attr_pop {
		position: relative;
		padding: 30rpx;
		.close_img {
			position: absolute;
			top: 30rpx;
			right: 30rpx;
			width: 44rpx;
			height: 44rpx;
		}
		.goods_info {
			display: flex;
			.goods_pic {
				flex: none;
				width: 200rpx;
				height: 200rpx;
				border-radius: 10rpx;
			}
			.info_right {
				flex: auto;
				display: flex;
				flex-direction: column;
				justify-content: flex-end;
				margin-left: 20rpx;
				.selece_atrr {
					margin-top: 10rpx;
					line-height: 40rpx;
				}
			}
		}
		.inventory {
			display: flex;
			justify-content: space-between;
			margin-top: 10rpx;
			color: #999;
			.inventory_num {
				flex: none;
				margin-right: 20rpx;
				line-height: 40rpx;
			}
			.inventory_sn {
				flex: auto;
				display: flex;
				white-space:normal;
				word-break:break-all;
				word-wrap:break-word;
				line-height: 40rpx;
				.label {
					flex: none;
				}
			}
		}
		.goods_attr_wrap {
			.attr_title {
				font-weight: 700;
			}
			.attr_type {
				padding-top: 30rpx;
			}
			.attr_list {
				display: flex;
				flex-wrap: wrap;
				width: 720rpx;
				.attr_item {
					padding: 0 30rpx;
					height: 62rpx;
					line-height: 62rpx;
					text-align: center;
					border-radius: 30rpx;
					margin: 30rpx 30rpx 0 0;
					background-color: #F2F2F2;
				}
				.active_attr {
					color: #F22E20;
					background-color: #FCEDEB;
				}
			}
			.goods_num_wrap {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-top: 20rpx;
				.limit_and_mun {
					display: flex;
					align-items: center;
					.limit_txt {
						font-size: 24rpx;
						color: #F22E20;
					}
					.add_num_wrap {
						display: flex;
						justify-content: center;
						align-items: center;
						.iconfont {
							flex: none;
							width: 50rpx;
							height: 50rpx;
							line-height: 50rpx;
							font-size: 28rpx;
							font-weight: 700;
							text-align: center;
						}
						.limit_1 {
							color: #EAEAEA;
						}
					}
					.add_input {
						width: 100rpx;
						height: 50rpx;
						line-height: 50rpx;
						text-align: center;
						font-weight: 400;
						background-color: #F2F2F2;
					}
				}
			}
		}
	}
	.footer_btn {
		display: flex;
		justify-content: center;
		align-items: center;
		background-color: #fff;
		padding: 15rpx 30rpx;
		
		.sub_btn:last-child{
			margin-right: 0;
		}
	}
	.pup_btn {
		flex: 1;
		margin: 0;
		&:nth-child(n + 2) {
			margin-left: 30rpx;
		}
	}
	/* 商品属性弹框 end */
	
	/* 配送弹框 start */
	.address_item {
		display: flex;
		align-items: flex-start;
		padding: 30rpx 0;
		.clicked_ico {
			flex: none;
			height: 20px;
			line-height: 1;
		}
		.address_val {
			flex: auto;
			margin-left: 30rpx;
			line-height: 40rpx;
		}
	}
	.btn_wrap {
		display: flex;
		justify-content: center;
		align-items: center;
		height: 100rpx;
		padding: 0 30rpx;
		.address_btn {
			flex: auto;
			margin: 0;
		}
	}
	.select_title {
		display: flex;
		justify-content: flex-start;
		align-items: center;
		height: 100rpx;
		padding-right: 30rpx;
		.select_item {
			flex: none;
			height: 100%;
			line-height: 100rpx;
			margin-left: 30rpx;
		}
		.curr_title {
			position: relative;
			font-size: 30rpx;
			font-weight: 700;
			&:after {
				content: '';
				position: absolute;
				bottom: 20rpx;
				left: 50%;
				transform: translateX(-50%);
				width: 90%;
				height: 6rpx;
				background: linear-gradient(90deg, #F91F28 0%, rgba(255, 79, 46, 0.35) 100%);
			}
		}
	}
	.region_item {
		height: 80rpx;
		line-height: 40rpx;
	}
	/* 配送弹框 end */
	
	/* 服务弹框 start */
	.server {
		&:nth-child(n + 2) {
			margin-top: 40rpx;
		}
		.server_item {
			line-height: normal;
		}
		.location_ico {
			height: 34rpx;
			margin-right: 8rpx;
			vertical-align: -6rpx;
		}
		.color_999 {
			margin-top: 16rpx;
			line-height: 1.5;
			font-size: 24rpx;
		}
	}
	/* 服务弹框 end */
}

/* 小程序分享  start*/
.show-popup-shareImg /deep/ .uni-popup-bottom{ height: 80%; }
/* 小程序分享 end*/

.swiper_warp_layer{
	position: relative;
}
.goods-attr-img-list{
	display: flex;
	flex-direction: row;
	background: #fff;
	justify-content: flex-start;
	align-items: center;
	
	.describe{
		font-size: 25rpx;
		width: 150rpx;
		text-align: center;
	}
	
	.scroll-view{
		width: calc(100% - 150rpx);
		padding: 20rpx 0;
		border-bottom: 1px solid #F9F9F9;

		.scroll-view-item{
			width: 100rpx;
			height: 100rpx;
			margin-right: 30rpx;
			border: 1px solid #dfdfdf;
			opacity: .6;
			display: inline-block;

			&.active{
				border-color: #F91F28;
				opacity: 1;
			}
		}
	}
}

.channels_live{
	position: fixed;
	width: 60px;
	height: 60px;
	background-color: #FFFFFF;
	border-radius: 5px;
	top: 25%;
	right: 20rpx;
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	
	.live-img{
		width: 40px;
		height: 40px;
	}
	.text{
		font-size: 25rpx;
	}
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
