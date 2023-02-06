<template>
	<scroll-view class="user-default" scroll-y :lower-threshold="100" @scrolltolower="loadMore">
		<view class="user-header" :class="{'mp-header':controlVersion && userInfo && userInfo.is_drp > 0 && (userInfo.drp_shop.membership_status != 0 ? userInfo.drp_shop.audit == 1 : true),'no-login': !userInfo}">
			<!-- #ifdef MP-WEIXIN -->
			<view :style="{height: statusHeight + 'px'}">
				<!-- 这里是状态栏 -->
			</view>
			<!-- #endif -->
			<view class="user-header-box">
				<block v-if="isLogin===true">
					<view class="head-img" open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile">
						<image :src="userInfo.avatar" v-if="userInfo && userInfo.avatar" class="img"></image>
						<image :src="imagePath.userDefaultImg" v-else class="img"></image>
					</view>
					<view class="head-img" @click="getUserProfile('')" v-else>
						<image :src="userInfo.avatar" v-if="userInfo && userInfo.avatar" class="img"></image>
						<image :src="imagePath.userDefaultImg" v-else class="img"></image>
					</view>
				</block>
				<block v-else>
					<view class="head-img" @click="$outerHref('/pagesB/profile/profile', 'app')">
						<image :src="userInfo.avatar" v-if="userInfo && userInfo.avatar" class="img"></image>
						<image :src="imagePath.userDefaultImg" v-else class="img"></image>
					</view>
				</block>
				<view class="head-admin">
					<view class="header-admin-box">
						<view class="left">
							<block v-if="userInfo">
								<view class="name" v-if="userInfo.name !=''">{{ userInfo.name }}</view>
								<view class="name" v-else>{{ userInfo.username }}</view>
							</block>
							<block v-else>
								<navigator url="/pagesB/login/login" hover-class="none" class="name">{{$t('lang.not_login')}}</navigator>
							</block>
						</view>
						
						<!--会员码-->
						<view class="user_qrcode" @click="onUserQrcode" v-if="userInfo">
							<text class="iconfont icon-erweima"></text>
							<text>会员码</text>
						</view>
					</view>
					<view class="growth" v-if="userInfo">
						<block v-if="isLogin===true">
							<view class="top">
								<view class="header-rank uni-ellipsis" v-if="userInfo.user_rank">{{ userInfo.user_rank }}</view>
								<block v-if="userInfo.drp_shop == 0 && userInfo.user_rank_progress">
									<view class="header-rank uni-ellipsis" open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile">{{$t('lang.growth_value')}} {{ userInfo.user_rank_progress.progress_format }}<uni-icons type="arrowright" size="11" color="#fff"></uni-icons></view>
									<view class="header-rank uni-ellipsis" @click="getUserProfile('/pagesB/growthvalue/growthvalue')" v-else>{{$t('lang.growth_value')}} {{ userInfo.user_rank_progress.progress_format }}<uni-icons type="arrowright" size="11" color="#fff"></uni-icons></view>
								</block>
								<block v-else>
									<view class="header-rank uni-ellipsis" open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile">{{$t('lang.growth_value')}} {{ userInfo.rank_points ? userInfo.rank_points : 0 }}<uni-icons type="arrowright" size="11" color="#fff"></uni-icons></view>
									<view class="header-rank uni-ellipsis"@click="getUserProfile('/pagesB/growthvalue/growthvalue')" v-else>{{$t('lang.growth_value')}} {{ userInfo.rank_points ? userInfo.rank_points : 0 }}<uni-icons type="arrowright" size="11" color="#fff"></uni-icons></view>
								</block>
							</view>
						</block>
						<block v-else>
							<view class="top">
								<view class="header-rank uni-ellipsis" v-if="userInfo.user_rank">{{ userInfo.user_rank }}</view>
								<view class="header-rank uni-ellipsis" @click="$outerHref('../../pagesB/growthvalue/growthvalue',$isLogin())" v-if="userInfo.drp_shop == 0 && userInfo.user_rank_progress">{{$t('lang.growth_value')}} {{ userInfo.user_rank_progress.progress_format }}<uni-icons type="arrowright" size="11" color="#fff"></uni-icons></view>
								<view class="header-rank uni-ellipsis" @click="$outerHref('../../pagesB/growthvalue/growthvalue',$isLogin())" v-else>{{$t('lang.growth_value')}} {{ userInfo.rank_points ? userInfo.rank_points : 0 }}<uni-icons type="arrowright" size="11" color="#fff"></uni-icons></view>
							</view>
						</block>
						
						<view class="bot" v-if="userInfo.drp_shop == 0 && userInfo.user_rank_progress">
							<progress :percent="userInfo.user_rank_progress.percentage" border-radius="3" stroke-width="2" activeColor="#FFFFFF" backgroundColor="rgba(255,255,255,.5)"></progress>
						</view>
					</view>
				</view>
				<view class="header-icon">
					<block v-if="isLogin===true">
						<view open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile" class="goods-img">
							<text v-if="isUnread"></text>
							<view class="iconfont icon-home-xiaoxi"></view>
						</view>
						<view @click="getUserProfile('/pagesB/messagelist/messagelist')" v-else class="goods-img">
							<text v-if="isUnread"></text>
							<view class="iconfont icon-home-xiaoxi"></view>
						</view>
						
						<view open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile" class="iconfont icon-personal-shezhi"></view>
						<view @click="getUserProfile('/pagesB/profile/profile')" v-else class="iconfont icon-personal-shezhi"></view>
					</block>
					<block v-else>
						<view @click="$outerHref('/pagesB/messagelist/messagelist',$isLogin())" class="goods-img">
							<text v-if="isUnread"></text>
							<view class="iconfont icon-home-xiaoxi"></view>
						</view>
						<view @click="$outerHref('/pagesB/profile/profile',$isLogin())" class="iconfont icon-personal-shezhi"></view>
					</block>
					
					<!-- #ifdef MP-WEIXIN -->
					<view @click="onClearCache" class="iconfont icon-close"></view>
					<!-- #endif -->
				</view>
			</view>
			<view class="itemize" v-if="userInfo">
				<block v-if="isLogin===true">
					<view class="itemize_collection" open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile">
						<view class="num">{{userInfo.collect_goods_num}}</view>
						<view class="text">{{$t('lang.my_collection')}}</view>
					</view>
					<view class="itemize_collection" @click="getUserProfile('/pagesB/collectionGoods/collectionGoods')" v-else>
						<view class="num">{{userInfo.collect_goods_num}}</view>
						<view class="text">{{$t('lang.my_collection')}}</view>
					</view>
					
					<view class="itemize_collection" open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile">
						<view class="num">{{userInfo.history_goods_num}}</view>
						<view class="text">{{$t('lang.my_tracks')}}</view>
					</view>
					<view class="itemize_collection" @click="getUserProfile('/pagesB/history/history')" v-else>
						<view class="num">{{userInfo.history_goods_num}}</view>
						<view class="text">{{$t('lang.my_tracks')}}</view>
					</view>
					
					<view class="itemize_collection" open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile">
						<view class="num">{{userInfo.collect_store_num}}</view>
						<view class="text">{{$t('lang.store_attention')}}</view>
					</view>
					<view class="itemize_collection" @click="getUserProfile('/pagesB/collectionShop/collectionShop')" v-else>
						<view class="num">{{userInfo.collect_store_num}}</view>
						<view class="text">{{$t('lang.store_attention')}}</view>
					</view>
				</block>
				<block v-else>
					<view class="itemize_collection" @click="$outerHref('/pagesB/collectionGoods/collectionGoods',$isLogin())">
						<view class="num">{{userInfo.collect_goods_num}}</view>
						<view class="text">{{$t('lang.my_collection')}}</view>
					</view>
					<view class="itemize_collection" @click="$outerHref('/pagesB/history/history',$isLogin())">
						<view class="num">{{userInfo.history_goods_num}}</view>
						<view class="text">{{$t('lang.my_tracks')}}</view>
					</view>
					<view class="itemize_collection" @click="$outerHref('/pagesB/collectionShop/collectionShop',$isLogin())">
						<view class="num">{{userInfo.collect_store_num}}</view>
						<view class="text">{{$t('lang.store_attention')}}</view>
					</view>
				</block>
			</view>
			<view class="itemize" v-else>
				<block v-if="isLogin===true">
					<view class="itemize_collection" open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile">
						<view class="icon"><text class="iconfont icon-shoucang"></text></view>
						<view class="text">{{$t('lang.my_collection')}}</view>
					</view>
					<view class="itemize_collection" @click="getUserProfile('/pagesB/collectionGoods/collectionGoods')" v-else>
						<view class="icon"><text class="iconfont icon-shoucang"></text></view>
						<view class="text">{{$t('lang.my_collection')}}</view>
					</view>
					
					<view class="itemize_collection" open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile">
						<view class="icon"><text class="iconfont icon-zuji-alt"></text></view>
						<view class="text">{{$t('lang.my_tracks')}}</view>
					</view>
					<view class="itemize_collection" @click="getUserProfile('/pagesB/history/history')" v-else>
						<view class="icon"><text class="iconfont icon-zuji-alt"></text></view>
						<view class="text">{{$t('lang.my_tracks')}}</view>
					</view>
					
					<view class="itemize_collection" open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile">
						<view class="icon"><text class="iconfont icon-guanzhu"></text></view>
						<view class="text">{{$t('lang.store_attention')}}</view>
					</view>
					<view class="itemize_collection" @click="getUserProfile('/pagesB/collectionShop/collectionShop')" v-else>
						<view class="icon"><text class="iconfont icon-guanzhu"></text></view>
						<view class="text">{{$t('lang.store_attention')}}</view>
					</view>
				</block>
				<block v-else>
					<view class="itemize_collection" @click="$outerHref('/pagesB/collectionGoods/collectionGoods',$isLogin())">
						<view class="icon"><text class="iconfont icon-shoucang"></text></view>
						<view class="text">{{$t('lang.my_collection')}}</view>
					</view>
					<view class="itemize_collection" @click="$outerHref('/pagesB/history/history',$isLogin())">
						<view class="icon"><text class="iconfont icon-zuji-alt"></text></view>
						<view class="text">{{$t('lang.my_tracks')}}</view>
					</view>
					<view class="itemize_collection" @click="$outerHref('/pagesB/collectionShop/collectionShop',$isLogin())">
						<view class="icon"><text class="iconfont icon-guanzhu"></text></view>
						<view class="text">{{$t('lang.store_attention')}}</view>
					</view>
				</block>
			</view>
			<view class="exclusive_on" v-if="controlVersion && (configData.is_show_drp == 1 && userInfo && userInfo.is_drp > 0)">
				<view class="exclusive">
					<view class="left">
						<view class="icon_vip">
							<image src="../../static/vip/icon-vip.png" mode="widthFix"></image>
						</view>
						<text>{{pageUserDrp.vip_name ? pageUserDrp.vip_name : $t('lang.high_grade_vip')}}</text>
					</view>
					<view class="center" v-if="userInfo.drp_shop.membership_status != 0 ? userInfo.drp_shop.audit == 1 : true">{{pageUserDrp.vip_title_2 ? pageUserDrp.vip_title_2 : $t('lang.store_price')}}{{userInfo.drp_affiliate.total_drp_log_money}}</view>
					<view class="center" v-else>{{pageUserDrp.vip_title ? pageUserDrp.vip_title : $t('lang.shopping_saves_money_shares')}}</view>
					<block v-if="userInfo">
						<view class="right" @click="mydrplink('info')" v-if="userInfo.drp_shop.membership_status != 0 ? userInfo.drp_shop.audit == 1 : true">
							<text>{{pageUserDrp.my_vip ? pageUserDrp.my_vip : $t('lang.my_vip')}}</text>
							<uni-icons type="arrowright" size="16" color="#E3C49E"></uni-icons>
						</view>
						<view class="right" @click="mydrplink('register')" v-else>
							<text>{{pageUserDrp.immediately_opened ? pageUserDrp.immediately_opened : $t('lang.immediately_opened')}}</text>
							<uni-icons type="arrowright" size="16" color="#E3C49E"></uni-icons>
						</view>
					</block>
				</view>
			</view>
		</view>
		<view class="user-function-ward" :class="{'mp-header':controlVersion && configData.is_show_drp == 1 && userInfo && userInfo.is_drp > 0}">
			<!--我的推广-->
			<view class="user-function-list user-function-list1" v-if="controlVersion && userInfo && (userInfo.drp_affiliate || userInfo.user_affiliate)">
				<view class="items">
					<view class="item">
						<text class="txt">{{ userInfo.drp_affiliate ? userInfo.drp_affiliate.user_child_num : userInfo.user_affiliate.user_child_num }}</text>
						<text class="span">{{pageUserDrp.drp_team ? pageUserDrp.drp_team : $t('lang.my_team_alt')}}</text>
					</view>
					<view class="item">
						<text class="txt">{{ userInfo.drp_affiliate ? userInfo.drp_affiliate.register_affiliate_money : userInfo.user_affiliate.register_affiliate_money }}</text>
						<text class="span">{{pageUserDrp.register_money ? pageUserDrp.register_money : $t('lang.registration_award')}}</text>
					</view>
					<view class="item tom">
						<text class="txt">{{ userInfo.drp_affiliate ? userInfo.drp_affiliate.total_drp_log_money : userInfo.user_affiliate.order_affiliate_money }}</text>
						<text class="span">{{pageUserDrp.order_money ? pageUserDrp.order_money : $t('lang.sale_reward')}}</text>
					</view>
				</view>
				<view class="broadcasting" v-if="userInfo.drp_affiliate" @click="mydrplink('info')">
					<view class="icon"><text class="iconfont icon-personal-share" style="font-size: 48upx;"></text></view>
					<view class="textit">{{pageUserDrp.my_promotion ? pageUserDrp.my_promotion : $t('lang.growth_tuiguang')}}</view>
				</view>
				<view class="broadcasting" @click="mydrplink('affiliate')" v-else>
					<view class="icon"><text class="iconfont icon-personal-share" style="font-size: 48upx;"></text></view>
					<view class="textit">{{pageUserDrp.my_promotion ? pageUserDrp.my_promotion : $t('lang.growth_tuiguang')}}</view>
				</view>
			</view>
			<view class="user-function-list">
				<view class="user-item-title">
					<view class="tit">{{$t('lang.my_order')}}</view>
					<block v-if="isLogin===true">
						<view open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile" class="more">
							<text>{{$t('lang.all_order')}}</text>
							<uni-icons type="arrowright" size="16" color="#999999"></uni-icons>
						</view>
						<view @click="getUserProfile('/pagesB/order/order')" v-else class="more">
							<text>{{$t('lang.all_order')}}</text>
							<uni-icons type="arrowright" size="16" color="#999999"></uni-icons>
						</view>
					</block>
					<block v-else>
						<view @click="$outerHref('/pagesB/order/order',$isLogin())" class="more">
							<text>{{$t('lang.all_order')}}</text>
							<uni-icons type="arrowright" size="16" color="#999999"></uni-icons>
						</view>
					</block>
				</view>
				<view class="user-items">
					<block v-if="isLogin===true">
						<view open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile" class="user-item">
							<view class="tit">
								<image src="../../static/user/icon-1.png"></image>
								<view class="num" v-if="userInfo && userInfo.pay_count > 0">{{ userInfo.pay_count < 99 ? userInfo.pay_count : capitalize }}</view>
							</view>
							<view class="text">{{$t('lang.order_status_01')}}</view>
						</view>
						<view @click="getUserProfile('/pagesB/order/order?tab=1')" v-else class="user-item">
							<view class="tit">
								<image src="../../static/user/icon-1.png"></image>
								<view class="num" v-if="userInfo && userInfo.pay_count > 0">{{ userInfo.pay_count < 99 ? userInfo.pay_count : capitalize }}</view>
							</view>
							<view class="text">{{$t('lang.order_status_01')}}</view>
						</view>
						
						<view open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile" class="user-item">
							<view class="tit">
								<image src="../../static/user/icon-2.png"></image>
								<view class="num" v-if="userInfo && userInfo.team_num > 0">{{ userInfo.team_num < 99 ? userInfo.team_num : capitalize }}</view>
							</view>
							<view class="text">{{$t('lang.order_status_02')}}</view>
						</view>
						<view @click="getUserProfile('/pagesA/team/order/order')" v-else class="user-item">
							<view class="tit">
								<image src="../../static/user/icon-2.png"></image>
								<view class="num" v-if="userInfo && userInfo.team_num > 0">{{ userInfo.team_num < 99 ? userInfo.team_num : capitalize }}</view>
							</view>
							<view class="text">{{$t('lang.order_status_02')}}</view>
						</view>
						
						<view open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile" class="user-item">
							<view class="tit">
								<image src="../../static/user/icon-3.png"></image>
								<view class="num" v-if="userInfo && userInfo.confirmed_count > 0">{{ userInfo.confirmed_count < 99 ? userInfo.confirmed_count : capitalize }}</view>
							</view>
							<view class="text">{{$t('lang.order_status_03')}}</view>
						</view>
						<view @click="getUserProfile('/pagesB/order/order?tab=2')" v-else class="user-item">
							<view class="tit">
								<image src="../../static/user/icon-3.png"></image>
								<view class="num" v-if="userInfo && userInfo.confirmed_count > 0">{{ userInfo.confirmed_count < 99 ? userInfo.confirmed_count : capitalize }}</view>
							</view>
							<view class="text">{{$t('lang.order_status_03')}}</view>
						</view>
						
						<view open-type="getUserInfo" @getuserinfo="getUserInfo" class="user-item" v-if="userInfo && userInfo.shop_can_comment > 0 && !canIUseProfile">
							<view class="tit">
								<image src="../../static/user/icon-4.png"></image>
								<view class="num" v-if="userInfo && userInfo.not_comment > 0">{{ userInfo.not_comment < 99 ? userInfo.not_comment : capitalize }}</view>
							</view>
							<view class="text">{{$t('lang.order_status_04')}}</view>
						</view>
						<view @click="getUserProfile('/pagesB/comment/comment')" v-else-if="userInfo && userInfo.shop_can_comment > 0" class="user-item">
							<view class="tit">
								<image src="../../static/user/icon-4.png"></image>
								<view class="num" v-if="userInfo && userInfo.not_comment > 0">{{ userInfo.not_comment < 99 ? userInfo.not_comment : capitalize }}</view>
							</view>
							<view class="text">{{$t('lang.order_status_04')}}</view>
						</view>
						
						<view open-type="getUserInfo" @getuserinfo="getUserInfo" v-if="!canIUseProfile" class="user-item">
							<view class="tit">
								<image src="../../static/user/icon-5.png"></image>
								<view class="num" v-if="userInfo && userInfo.return_count > 0">{{ userInfo.return_count < 99 ? userInfo.return_count : capitalize }}</view>
							</view>
							<view class="text">{{$t('lang.order_status_05')}}</view>
						</view>
						<view @click="getUserProfile('/pagesB/afterSales/afterSales')" v-else class="user-item">
							<view class="tit">
								<image src="../../static/user/icon-5.png"></image>
								<view class="num" v-if="userInfo && userInfo.return_count > 0">{{ userInfo.return_count < 99 ? userInfo.return_count : capitalize }}</view>
							</view>
							<view class="text">{{$t('lang.order_status_05')}}</view>
						</view>
					</block>
					<block v-else>
						<view @click="$outerHref('/pagesB/order/order?tab=1',$isLogin())" class="user-item">
							<view class="tit">
								<image src="../../static/user/icon-1.png"></image>
								<view class="num" v-if="userInfo && userInfo.pay_count > 0">{{ userInfo.pay_count < 99 ? userInfo.pay_count : capitalize }}</view>
							</view>
							<view class="text">{{$t('lang.order_status_01')}}</view>
						</view>
						<view @click="$outerHref('/pagesA/team/order/order',$isLogin())" class="user-item">
							<view class="tit">
								<image src="../../static/user/icon-2.png"></image>
								<view class="num" v-if="userInfo && userInfo.team_num > 0">{{ userInfo.team_num < 99 ? userInfo.team_num : capitalize }}</view>
							</view>
							<view class="text">{{$t('lang.order_status_02')}}</view>
						</view>
						<view @click="$outerHref('/pagesB/order/order?tab=2',$isLogin())" class="user-item">
							<view class="tit">
								<image src="../../static/user/icon-3.png"></image>
								<view class="num" v-if="userInfo && userInfo.confirmed_count > 0">{{ userInfo.confirmed_count < 99 ? userInfo.confirmed_count : capitalize }}</view>
							</view>
							<view class="text">{{$t('lang.order_status_03')}}</view>
						</view>
						<view @click="$outerHref('/pagesB/afterSales/afterSales',$isLogin())" class="user-item">
							<view class="tit">
								<image src="../../static/user/icon-5.png"></image>
								<view class="num" v-if="userInfo && userInfo.return_count > 0">{{ userInfo.return_count < 99 ? userInfo.return_count : capitalize }}</view>
							</view>
							<view class="text">{{$t('lang.order_status_05')}}</view>
						</view>
						<view @click="$outerHref('/pagesB/comment/comment',$isLogin())" v-if="userInfo && userInfo.shop_can_comment > 0" class="user-item">
							<view class="tit">
								<image src="../../static/user/icon-4.png"></image>
								<view class="num" v-if="userInfo && userInfo.not_comment > 0">{{ userInfo.not_comment < 99 ? userInfo.not_comment : capitalize }}</view>
							</view>
							<view class="text">{{$t('lang.order_status_04')}}</view>
						</view>
					</block>
				</view>
			</view>
			<view class="user-function-list user-function-list1">
				<view class="items">
					<view @click="$outerHref('/pagesB/account/account','app')" class="item">
						<view class="txt tite">{{ userInfo && userInfo.user_money || 0 }}</view>
						<view class="span tite">{{$t('lang.money')}}</view>
					</view>
					<view @click="$outerHref('/pagesB/bonus/bonus','app')" class="item">
						<view class="txt tite">{{ userInfo && userInfo.bonus || 0 }}</view>
						<view class="span tite">{{$t('lang.bonus')}}</view>
					</view>
					<view @click="$outerHref('/pagesB/account/account','app')" class="item tom">
						<view class="txt tite">{{ userInfo && userInfo.pay_points || 0 }}</view>
						<view class="span tite">{{$t('lang.integral')}}</view>
					</view>
				</view>
				<view class="broadcasting" @click="$outerHref('/pagesB/account/account','app')">
					<view class="icon red"><text class="iconfont icon-personal-money" style="font-size: 48upx;"></text></view>
					<view class="textit">{{$t('lang.account_manage')}}</view>
				</view>
			</view>
			<view class="user-function-list" v-if="userInfo && userInfo.coupons_num != 0">
				<view class="user-item-title">
					<view class="tit">{{$t('lang.my_coupons')}}({{ userInfo && userInfo.coupons_num || 0 }})</view>
					<view @click="$outerHref('/pagesB/coupon/coupon',$isLogin())" class="more">
						<text>{{$t('lang.more')}}</text>
						<uni-icons type="arrowright" size="16" color="#999999"></uni-icons>
					</view>
				</view>
				<view class="user-item_on">
					<view class="my_coupons" v-for="(item,index) in couponData" :key="index">
						<view class="my">
							<view class="my_coupons_left">
								<view class="price">
									<block v-if="item.cou_type == 5">{{$t('lang.freight_free')}}</block>
									<block v-else>
										<block v-if="!item.order_sn">
											<block v-if="item.uc_money > 0"><text>{{ currency_format }}</text>{{ item.cou_money }}</block>
											<block v-else><text>{{ currency_format }}</text>{{item.cou_money}}</block>
										</block>
										<block v-else>
											{{item.order_coupons}}
										</block>
									</block>
								</view>
								<view class="reduction"><text>{{$t('lang.man')}}{{ item.cou_man }}{{$t('lang.yuan')}}</text></view>
								<view class="platform">{{ item.store_name }}</view>
							</view>
							<view class="my_coupons_right" @click="couponLink(item.cou_id)">
								{{$t('lang.store_price1')}}
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class="user-function-list" v-for="(item,index) in customNav" :key="index">
				<view class="user-item-title">
					<view class="tit">{{item.name}}</view>
				</view>
				<view class="user-items user-nav-items">
					<view @click="linkHref(itemChild.url)" class="user-item" v-for="(itemChild,indexChild) in item.child_nav" :key="indexChild">
						<view class="tit">
							<image :src="itemChild.pic"></image>
						</view>
						<view class="text">{{itemChild.name}}</view>
					</view>
				</view>
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
		
		<!--会员码弹窗-->
		<view class="user-qrcode-popup">
			<uni-popup :show="userQrcodeShow" mode="fixed" v-on:hidePopup="closeUserQrcode">
				<view class="header" :style="{'background-image':'url('+imagePath.userqrcodeBg +')'}">
					<view class="header-warp">
						<view class="logo">
							<image :src="userInfo.avatar" v-if="userInfo && userInfo.avatar" class="img"></image>
							<image :src="imagePath.userDefaultImg" v-else class="img"></image>
						</view>
						<view class="name">{{ userInfo.name !='' ? userInfo.name : userInfo.username }}</view>
						<view class="rank">{{ userInfo.user_rank }}</view>
					</view>
					<text class="close" @click="closeUserQrcode">关闭</text>
				</view>
				<view class="content">
					<block v-if="userInfo.mobile">
						<view class="ms">请向营业员出示会员码进行优惠结算及付款</view>
						<view class="qrcode">
							<canvas canvas-id="qrcode" style="height: 150px; width: 150px;" />
						</view>
						<view class="total">
							<view class="total-item">
								<view class="text">积分</view>
								<view class="number">{{ userInfo.drp_shop == 0 && userInfo.user_rank_progress ? userInfo.user_rank_progress.progress_format : userInfo.rank_points }}</view>
							</view>
							<view class="total-item">
								<view class="text">余额</view>
								<view class="number">{{ userInfo.user_money }}</view>
							</view>
							<view class="total-item">
								<view class="text">红包</view>
								<view class="number">{{ userInfo.bonus }}</view>
							</view>
						</view>
					</block>
					<view class="not-mobile" v-else>
						<view class="p">会员码功能需绑定手机号方可使用</view>
						<view class="button" @click="bindphoneHref">去绑定</view>
					</view>
				</view>
			</uni-popup>
		</view>
		
		<view class="goods-detail-guess" v-if="guessList.length > 0">
			<view class="title"><text>{{$t('lang.guess_love')}}</text></view>
			<view class="product-list-lie">
				<dsc-product-list :list="guessList" :productOuter="true" :guessList="true"></dsc-product-list>
				<uni-load-more :status="loadMoreStatus" :content-text="contentText" :type="false" v-if="guessList.length >= 10 && !isOver" />
			</view>
		</view>
		
		<!-- 底部版权 -->
		<dsc-copyright></dsc-copyright>

		<!-- <dsc-product-pick :scrollPickOpen="scrollPickOpen" @updateScrollPickOpen="updateScrollPickOpen"></dsc-product-pick> -->

		<!-- <tabbar :curpage="curpage"></tabbar> -->
	</scroll-view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import tabbar from "@/components/tabbar/tabbar.vue";
	import uniPopup from '@/components/uni-popup.vue';
	import universal from '@/common/mixins/universal.js';

	import dscProductPick from '@/components/visualization/product-pick/Frontend'
	import dscProductList from '@/components/dsc-product-list.vue';
	import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';
	import uniStatusBar from '@/components/uni-status-bar.vue';
	import dscCopyright from '@/components/dsc-copyright/dsc-copyright.vue'
		
	import uQRCode from '@/common/uqrcode.js'
	
	export default {
		mixins: [universal],
		components: {
			tabbar,
			dscProductPick,
			dscProductList,
			uniIcons,
			uniPopup,
			uniLoadMore,
			uniStatusBar,
			dscCopyright
		},
		data() {
			return {
				scopeUserInfo: {},
				scope: 'userInfo',
				scopeSessions: {},
				canIUseProfile:false,
				getPhoneNumberShow:false,
				isLoginData:false,
				token: '',
				show: false,
				curpage: '',
				status: 0,
				page: 1,
				size: 10,
				scrollPickOpen:false,
				loadMoreStatus:'more',
				contentText: {
					contentdown: this.$t('lang.view_more'),
					contentrefresh: this.$t('lang.loading'),
					contentnomore: this.$t('lang.no_more')
				},
				liststatus:[],
				guessList: [],
				capitalize:'99+',
				isLoading: true,
				pageUserDrp: {},
				customNav:[],
				userQrcodeShow:false,
				statusHeight: 56,
				isOver: false,
				userInfo:'',
				configData: uni.getStorageSync('configData'),
				currency_format: uni.getStorageSync('configData').currency_format || '¥',
			};
		},
		computed: {
			...mapState({
				//data: state => state.user.userInfo,
				goodsGuessList: state => state.shopping.goodsGuessList, //猜你喜欢
			}),
			couponData: {
				get() {
					return this.$store.state.user.couponData
				},
				set(val) {
					this.$store.state.user.couponData = val
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
			isUnread(){
				let i = 0
				this.liststatus.forEach((res)=>{
					if(res.unread){
						i++
					}
				})
				
				return i > 0 ? true : false
			}
		},
		watch: {
			userInfo: {
				handler: function (val, oldVal) {
					this.getCustomNav();
					if(val){
						//分销自定义
						if(val.is_drp > 0){
							this.getCustomTextByCode();
						}
						
						//会员优惠券
						this.userCouponList();
						
						//留言信息
						this.sessions();
					}
				},
				deep: true
			}
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
									this.loadShowUser();
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
			
			//猜你喜欢
			async goodsGuessHandle(){
				let page = this.guessList.length / this.size;
				
				page = Math.ceil(page) + 1;
				
				const { data: result } = await this.$store.dispatch('getGoodsGuessList', {
					warehouse_id: 0,
					area_id: 0,
					page: page,
					size: this.size
				});
				
				if (Array.isArray(result) && result.length > 0) {
					this.guessList = [...this.guessList, ...result];
				};
				
				if (result.length < 10) this.isOver = true;
			},
			initData() {
				setTimeout(() => {
					this.loadUserProfile();

					uni.stopPullDownRefresh();
				}, 300);
			},
			userCouponList(page) {
				if (page) {
					this.page = page
					this.size = Number(page) * 10
				}

				this.$store.dispatch('setUserCoupon', {
					page: this.page,
					size: this.size,
					type: this.status
				})
			},
			couponLink(id) {
				uni.navigateTo({
					url: '/pages/goodslist/goodslist?cou_id=' + id
				})
			},
			async loadUserProfile() {
				const { data } = await this.$store.dispatch('userProfile',{ type:true });
				this.userInfo = data ? data : ''
			},
			merchantsChange() {
				this.$outerHref(this.$websiteUrl + 'user/merchants');
			},
			//默认onShow加载
			async loadShowUser() {
				//用户信息
				this.loadUserProfile();
				
				//猜你喜欢
				this.goodsGuessHandle(1);
			},
			mydrplink(type) {
				if(this.userInfo.mobile === ''){
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
					
					return
				}
				
				if (type == 'info') {
					uni.navigateTo({
						url: "/pagesA/drp/drpinfo/drpinfo"
					})
				} else if(type == 'register') {
					uni.navigateTo({
						url: "/pagesA/drp/register/register"
					})
				}else if(type == 'affiliate'){
					uni.navigateTo({
						url: "/pagesB/affiliate/index/index"
					})
				}else if(type == 'account'){
					uni.navigateTo({
						url: "/pagesB/account/account"
					})
				}
			},
			loadMore(){
				this.scrollPickOpen = true

				this.showLoadMore = true
				
				if (this.isOver) return this.loadMoreStatus = 'more';
				this.loadMoreStatus = 'loading';
				this.goodsGuessHandle()
			},
			//顶部是否消息通知
			sessions(page) {
				uni.request({
					url: this.websiteUrl + '/api/chat/sessions',
					method: 'GET',
					data: {
						page: 1,
						size: 10
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: (res) => {
						if (res.data.status == 'success') {
							this.liststatus = res.data.data
						}
					}
				})
			},
			updateScrollPickOpen(){
				this.scrollPickOpen = false
			},
			// 分销管理-自定义设置数据
			async getCustomTextByCode() {
				//this.isLoading = true;
				const {data: { page_user_drp }, status} = await this.$store.dispatch('getCustomText',{code: 'page_user_drp'});
			    if (status == 'success') {
			        this.pageUserDrp = page_user_drp || {};
			    };
				//this.isLoading = false;
			},
			// 工具栏自定义
			getCustomNav(){
				let referer = uni.getStorageSync('platform').toLowerCase()
				if(uni.getStorageSync('platform') == 'MP-WEIXIN'){
					referer = 'wxapp'
				}

				uni.request({
					url: this.websiteUrl + '/api/user/touch_nav',
					method: 'POST',
					data:{
						device:referer
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						this.customNav = res.data.data
					}
				})
			},
			linkHref(url){
				if(url.indexOf('http') != -1){
					this.$outerHref(url)
				}else{
					this.$outerHref(url,'app')
				}
			},
			//会员码
			onUserQrcode(){
				this.userQrcodeShow = true
				this.creatQrCode();
			},
			closeUserQrcode(){
				this.userQrcodeShow = false
			},
			//二维码生成
			async creatQrCode() {
				let mobile = this.userInfo.mobile;
				await uQRCode.make({
					canvasId: 'qrcode',
					componentInstance: this,
					text: mobile,
					size: 150,
					margin: 10,
					backgroundColor: '#ffffff',
					foregroundColor: '#000000',
					fileType: 'jpg',
					errorCorrectLevel: uQRCode.errorCorrectLevel.H
				})
			},
			bindphoneHref(){
				uni.navigateTo({
					url:"/pagesB/accountsafe/bindphone/bindphone?delta=1&step=1"
				})
				this.userQrcodeShow = false
			},
			onClearCache(){
				let self = this;
				uni.showModal({
					content: this.$t('lang.is_clear_cache'),
					success: res => {
						if (res.confirm) {
							uni.clearStorageSync();
							let platform = '';
							//#ifdef APP-PLUS
							platform = 'APP';
							//#endif
							//#ifdef H5
							platform = 'H5';
							//#endif
							//#ifdef MP-WEIXIN
							platform = 'MP-WEIXIN';
							//#endif
							uni.setStorageSync('platform',platform);
							plus.runtime.getProperty(plus.runtime.appid,(wgtinfo)=>{
								uni.setStorageSync('wgtinfo',JSON.stringify(wgtinfo))
							})
							uni.showLoading({
							    title: '清除缓存中。。。'
							});
							setTimeout(function(){
								uni.hideLoading();
								self.$outerHref('/pages/index/index','app');
							}, 1000);
							
						}
					}
				});
			}
		},
		onLoad() {
			//修改导航栏颜色
			// #ifdef APP-PLUS
			uni.setNavigationBarColor({
			    frontColor: '#ffffff',
			    backgroundColor: '#FFFFFF',
			    animation: {
			        duration: 400,
			        timingFunc: 'easeIn'
			    }
			})
			// #endif
			
			// #ifdef MP-WEIXIN
			const { top, height } = uni.getMenuButtonBoundingClientRect();
			this.statusHeight = top + height;
			// #endif
		},
		onReady() {
		},
		onShow() {
			this.loadShowUser();
		},
		onPullDownRefresh() {
			this.initData();
		}
	}
</script>

<style>
	.user-default {
		/* padding-bottom: 100upx; */
		height: 100%;
		position: relative;
	}

	.user-header {
		position: relative;
		background: linear-gradient(-88deg, rgba(255, 76, 48, 1), rgba(234, 10, 33, 1));
		/* #ifdef MP-WEIXIN */
		padding-bottom: 80rpx;
		height: auto!important;
		/* #endif */

		/* #ifdef APP-PLUS */
		height: 430upx;
		padding-top: 40px;
		/* #endif */
	}

	.user-header.no-login {
		height: 430upx;
	}

	/*分销会员样式*/
	.user-header.mp-header {
		height: 430upx;
		background: linear-gradient(-88deg, rgba(29, 6, 2, 1), rgba(59, 36, 10, 1));
	}

	.user-header.mp-header .growth .header-rank {
		color: #E9D1B2;
		font-size: 24upx;
        background: linear-gradient(90deg, rgba(235, 213, 183, 0.1), rgba(203, 170, 131, 0.1));
	}
	.user-default .user-header.mp-header .itemize{
		  margin-top: 35upx;
	  }
  .user-default .user-header.mp-header .user-header-box .header-admin-box .name{
	   color: #E9D1B4;
   }
   .user-default .user-header.mp-header .user-header-box .growth .header-rank{
	  color: #E9D2B4;
	    background: linear-gradient(90deg, rgba(235, 213, 183, 0.2), rgba(203, 170, 131, 0.2));
   }
	.user-header.mp-header .itemize .itemize_collection .text {
		color: rgba(247, 247, 247, .6);
	}

	.user-header.mp-header .exclusive_on .exclusive {
		background: linear-gradient(90deg, rgba(252, 230, 202, 1), rgba(241, 215, 181, 1));
	}

	.mp-header.user-function-ward {
		padding-top: 69.5upx;
	}

	/* #ifdef APP-PLUS */
	.user-default .user-header {
		position: relative;
		height: 460upx;
	}

	.user-default .user-header.mp-header {
		height: 430upx;
	}

	/* #endif */

	.user-default .user-header-box {
		display: flex;
		flex-direction: row;
		position: relative;
		z-index: 1;
		align-items: center;
		padding: 90upx 30upx 0 30upx;
	}

	.user-default .user-header-box .head-img {
		width: 130upx;
		height: 130upx;
		margin-right: 30upx;
		border-radius: 50%;
		border: 5upx solid rgba(255, 255, 255, 0.29);
	}

	.user-default .user-header-box .head-img .img {
		border-radius: 50%;
	}

	.user-default .user-header-box .head-admin {
		flex: 1;
	}

	.user-default .user-header-box .header-admin-box {
		display: flex;
		flex-direction: row;
		justify-content: space-between;
		color: #FFFFFF;
		font-size: 30upx;
		position: relative;
	}

	.user-default .user-header-box .header-admin-box .left {
		display: flex;
		flex-direction: row;
		justify-content: flex-start;
		align-items: center;
	}

	.user-default .user-header-box .header-admin-box .name {
		overflow: hidden;
		white-space: nowrap;
		text-overflow: ellipsis;
		font-size: 32upx;
		font-weight: 500;
		color: rgba(255, 255, 255, 1);
		margin-right: 15upx;
		line-height: 1.5;
	}

	.user-default .user-header-box .growth .header-rank {
		background: rgba(0, 0, 0, 0.1);
		/* color: #E9D1B2; */
		color: #FFF;
		font-size: 24upx;
		padding: 0 24upx;
		border-radius: 30upx;
		display: inline-block;
		height: 50upx;
		line-height: 50upx;
		margin-right: 5upx;
	}

	.user-default .header-icon {
		display: flex;
		flex-direction: row;
		position: absolute;
		top: 20upx;
		right: 0;
		z-index: 99;
	}

	.user-default .header-icon .iconfont {
		font-size: 18px;
		color: #FFFFFF;
		margin-right: 30upx;
	}
	
	.user-default .header-icon .goods-img .iconfont{
		margin-right: 30upx;
	}

	.user-default .user-header-box .growth .top {
		display: flex;
		flex-direction: row;
		justify-content: flex-start;
		align-items: center;
	}
	
	.user-default .user-header-box .growth .top view{
		margin-bottom: 10upx;
	}
	
	.user-default .user-header-box .growth .top view:last-child{
		/* margin-bottom: 0; */
	}

	.user-default .user-header-box .growth .top .text {
		color: #FFFFFF;
		margin-right: 20upx;
		font-size: 24upx;
	}

	.user-default .user-header-box .growth .bot {
		margin-top: 15upx;
		width: 80%;
	}

	.exclusive_on {
		width: 100%;
		position: absolute;
		left: 0upx;
		right: 0upx;
		bottom: -45upx;
		z-index: 990;
	}

	.exclusive {
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: space-between;
		padding: 20upx 20upx 20upx 20upx;
		font-size: 28upx;
		border-radius: 20upx;
		margin: 0 20upx;
		z-index: 990;
		background: rgba(255, 255, 255, 1);
		box-shadow: 0px 5upx 20upx 0px rgba(108, 108, 108, 0.1);
	}

	.exclusive .left {
		display: flex;
		flex-direction: row;
		justify-content: flex-start;
		align-items: center;
	}

	.exclusive .left .icon_vip,
	.exclusive .left .icon_vip image {
		width: 43upx;
		height: 43upx;
	}

	.exclusive .left text {
		margin-left: 10upx;
		font-weight: 500;
	}

	.exclusive .center {
		flex: 1;
		position: relative;
		padding: 0 0 0 20upx;
		/* margin-left: 20upx; */
		color: #805223;
		font-size: 26upx;
		line-height: 40upx;
		height: 40upx;
	}

	/* 	.exclusive .center::after {
		content: " ";
		position: absolute;
		z-index: 2;
		background-color: #885E2B;
		width: 2upx;
		height: 20upx;
		left: 0;
		top: 50%;
		margin-top: -10upx;
	} */

	.exclusive .right {
		background: #000000;
		border-radius: 30upx;
		padding: 0 15upx 0 20upx;
		display: flex;
		flex-direction: row;
		justify-content: center;
		align-items: center;
		position: relative;
		z-index: 33;
	}

	.exclusive .right text {
		color: #E3C49E;
		font-size: 25upx;
	}

	.exclusive .right .uni-icon {
		display: block;
	}

	.user-function-ward {
		padding: 20upx 20upx 0;
	}

	.user-function-list {
		background: #FFFFFF;
		margin-bottom: 25upx;
		border-radius: 15upx;
	}

	.user-function-list .user-item-title {
		padding: 20upx;
		display: flex;
		flex-direction: row;
		justify-content: space-between;
		align-items: center;
		position: relative;
	}

	.user-function-list .user-item-title:after {
		content: " ";
		left: 20upx;
		right: 20upx;
		bottom: 0;
		height: 1px;
		background-color: #E5E5E5;
		position: absolute;
	}

	.user-function-list .user-item-title .tit {
		font-size: 28upx;
		font-weight: 500;
		color: #333;
	}

	.item .tite {
		color: #333 !important;
	}

	.user-function-list .user-item-title .more {
		font-size: 26upx;
		color: #888888;
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: center;
		line-height: normal;
	}

	.user-function-list .user-item-title .more .uni-icon {
		display: block;
		color: #888888;
	}

	.user-function-list .user-items {
		display: flex;
		flex-direction: row;
	}

	.user-function-list .user-items .user-item {
		overflow: hidden;
		flex: 1 1 0%;
		padding: 30upx 20upx;
		text-align: center;
	}

	.user-function-list .user-items .user-item .tit {
		font-size: 32upx;
		color: #AC8054;
		position: relative;
	}

	.user-function-list .user-items .user-item .tit image {
		width: 60upx;
		height: 60upx;
	}

	.user-function-list .user-items .user-item .text {
		font-size: 25upx;
		color: #333333;
	}

	.user-function-list .user-account-items .user-item .tit {
		overflow: hidden;
		white-space: nowrap;
		text-overflow: ellipsis;
	}

	.user-function-list .user-items .user-item .num {
		position: absolute;
		font-size: 20upx;
		min-width: 22upx;
		height: 30upx;
		line-height: 30upx;
		border-radius:30upx;
		/* background: #f92028; */
		background: #fff;
		color: #f92028;
		border: 1px solid #f92028;
		text-align: center;
		top: -13upx;
		right: 8%;
		padding: 0 5upx;
	}

	.user-function-list .user-nav-items {
		flex-wrap: wrap;
	}

	.user-function-list .user-nav-items .user-item {
		width: 25%;
		flex: none;
		padding: 40upx 0 25upx;
	}

	.user-function-list1 {
		display: flex;
		background: rgba(255, 255, 255, 1);
		border-radius: 15upx;
	}

	.user-function-list1 .items {
		display: flex;
		flex-direction: row;
		justify-content: flex-start;
		align-items: center;
		width: 79%;
	}

	.user-function-list1 .item {
		flex: 1;
		width: 25%;
		padding: 35upx 0;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
	}

	.user-function-list1 .item .span {
		color: rgba(175, 116, 58, 1);
		font-weight: 400;
		font-size: 24upx;
	}

	.user-function-list1 .item .txt {
		color: rgba(175, 116, 58, 1);
		width: 140upx;
		text-align: center;
		font-size: 33upx;
		font-weight: 500;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	.growth {
		margin-top: 5upx;
		position: relative;
	}

	.growth_vip {
		width: 146upx;
		height: 44upx;
		line-height: 44upx;
		background: rgba(0, 0, 0, 0.1);
		text-align: center;
		border-radius: 19upx;
		font-size: 26upx;
		font-family: PingFang SC;
		font-weight: 400;
		color: rgba(255, 255, 255, 1);
	}

	.growth_vip_v {
		color: rgba(233, 209, 179, 1);
		background: linear-gradient(90deg, rgba(235, 212, 182, 0.4), rgba(203, 169, 130, 0.4));
	}

	.itemize {
		display: flex;
		align-items: center; //子元素垂直居中
		justify-content: space-around;
		margin-top: 40upx;
	}

	.itemize .itemize_collection {
		width: 33.3333333%;
		position: relative;
	}
	.itemize .itemize_collection .icon{
		text-align: center;
		color: #fff;
	}
    .itemize .itemize_collection .icon .iconfont{
		font-size: 18px;
	}
	.itemize .itemize_collection .num,
	.itemize .itemize_collection .text {
		text-align: center;
		font-size: 24upx;
		color: rgba(247, 247, 247, 1);
	}

	.itemize .itemize_collection .num {
		font-size: 32upx;
		font-weight: 500;
	}

	.itemize .itemize_collection:after {
		content: ' ';
		position: absolute;
		width: 1upx;
		height: 70%;
		background: rgba(255, 255, 255, .4);
		right: 0;
		top: 15%;
	}
	.user-header.mp-header .itemize .itemize_collection:after{
		content: ' ';
		position: absolute;
		width: 1upx;
		height: 70%;
		background: rgba(255, 255, 255, .2);
		right: 0;
		top: 15%;
	}

	.itemize .itemize_collection:last-child:after {
		width: 0;
	}

	.broadcasting {
		width: 21%;
		position: relative;
		padding: 35upx 0;
		margin-left: 2upx;
	/* 	box-shadow: 0px 5px 10px 0px rgba(24, 24, 25, 0.1); */
	}

	.broadcasting .icon {
		text-align: center;
		color: #AF743A;
		height: 55upx;
		line-height: 55upx;
		margin-bottom: 5upx;
	}

	.broadcasting .icon .iconfont {
		color: #AF743A;
	}

	.broadcasting .red .iconfont {
		background-image: linear-gradient(-30deg,rgba(239,0,34,1),rgba(251,87,32,1));
		-webkit-background-clip: text;
		color:transparent;
	}

	.broadcasting .textit {
		font-size: 24upx;
		margin-top: 14upx;
		text-align: center;
		color: rgba(0, 0, 0, 1);
	}

	.user-item_on {
		padding: 19upx;
		display: flex;
		flex-wrap: nowrap;
		overflow-x: auto;
	}

	.my_coupons {
		display: flex;
		flex-shrink: 0;
		margin-right: 20upx;
		margin-top: 10upx;
		border-radius: 10upx;
		position: relative;
		overflow: hidden;
	}

	.my {
		display: flex;
		width: 320upx;
		height: 155upx;
		border: 1px solid rgba(242, 210, 210, 1);
		border-radius: 10upx;
		background: rgba(251, 242, 242, 1);
	}

	.my_coupons .my_coupons_left {
		width: 258upx;
		padding-left: 22upx;
	}

	.my_coupons .my_coupons_left .price {
		font-size: 49upx;
		line-height: 1.5;
		font-weight: 600;
		color: rgba(245, 4, 11, 1);
	}

	.my_coupons .my_coupons_left .price text {
		font-size: 29upx;
	}

	.my_coupons .my_coupons_left .reduction {
	  line-height: 1.4;
	}
	.my_coupons .my_coupons_left .reduction text{
		padding: 3upx 15upx;
		text-align: center;
		font-size: 20upx;
		font-weight: 600;
		color: rgba(255, 255, 255, 1);
		background: linear-gradient(123deg, rgba(245, 4, 11, 1), rgba(246, 71, 53, 1));
		border-radius: 5upx;
	}

	.my_coupons .my_coupons_left .platform {
		font-size: 22upx;
		font-weight: 400;
		color: rgba(136, 136, 136, 1);
		margin: 5upx 0;
	}

	.my_coupons_right {
		width: 62upx;
		display: flex;
		text-align: center;
		font-size: 24upx;
		padding-right: 15upx;
		font-weight: 600;
		color: rgba(255, 255, 255, 1);
		background: linear-gradient(123deg, rgba(245, 4, 12, 1), rgba(246, 72, 54, 1));
		-webkit-display: flex;
		flex-direction: column;
		writing-mode: vertical-rl;
		border-radius: 10upx;
		/*从左向右 从右向左是 writing-mode: vertical-rl;*/



	}

	.mp-color {
		color: rgba(232, 209, 179, 0.2) !important;
	}

	.my_coupons:before {
		content: '';
		position: absolute;
		z-index: 0;
		left: 73%;
		top: -15upx;
		width: 30upx;
		height: 30upx;
		border-radius: 100%;
		background: #fff;
		border: 1px solid rgba(242, 210, 210, 1);
		/* margin-top: -10px; */
	}

	.my_coupons:after {
		content: '';
		position: absolute;
		z-index: 0;
		left: 73%;
		bottom: -15upx;
		width: 30upx;
		height: 30upx;
		border-radius: 100%;
		background: #fff;
		border: 1px solid rgba(242, 210, 210, 1);
		/* margin-top: -10px; */
	}
	.items {
		position: relative;
	}

	.items:after {
		position: absolute;
		    z-index: 1;
		    top: 0;
		    right: -16upx;
		    width: 30upx;
		    height: 100%;
		    background: url(../../static/user/icon-6.png);
		    background-size: 100% 100%;
		    content: "";
	}
	.goods-img {
		/* width: 80upx;
		height: 80upx;
		border-radius: 10upx; */
		position: relative;
		/* border: 1px solid #666666; */
	}
	.goods-img text{
		background: #fff;
		height: 10upx;
		width: 10upx;
		position: absolute;
		right:20upx;
		top: 6upx;
		z-index: 99;
		border-radius: 50%;
	}
	
	.mp-header .goods-img text{
		background: red;
	}
	
	/*会员码*/
	.user_qrcode{ position: absolute; background: rgba(0, 0, 0, 0.1); color: #FFF; font-size: 25upx; height: 50upx; line-height: 50upx; display: flex; flex-direction: row; justify-content: center; align-items: center; border-radius: 30upx 0 0 30upx; right: -30upx; top: 0; padding: 0 20upx;}
	.user_qrcode .iconfont{ font-size: 16px; height: 16px; line-height: 20px; }
	.user-header.mp-header .user_qrcode{ background: linear-gradient(90deg, rgba(235, 212, 181, 0.2), rgba(203, 169, 128, 0.2)); }
	.user-header.mp-header .user_qrcode,.user-header.mp-header .user_qrcode .iconfont{ color: #E9D2B4;}
	.user-qrcode-popup .uni-popup-middle{ width: 80%; overflow: visible; background:transparent; padding: 0;}
	.user-qrcode-popup .uni-popup-middle .content{ min-height: auto; width: 100%; box-sizing: border-box;}
	.user-qrcode-popup .header{ background-size: cover; background-repeat: no-repeat; position: relative; border-radius: 40upx 40upx 0 0; width: 100%;}
	
	.user-qrcode-popup .header-warp{ text-align: center; padding: 50upx 0 20upx;}
	.user-qrcode-popup .header-warp .logo{ width: 100upx; height: 100upx; position: absolute; top: -50upx; left: calc(50% - 50upx);border:3px solid #ec5857; border-radius: 50%; overflow: hidden;}
	.user-qrcode-popup .header-warp .logo{ border-radius: 50%; }
	.user-qrcode-popup .header-warp .name{ color: #fff; font-size: 28upx;}
	.user-qrcode-popup .header-warp .rank{ padding: 0 20upx; background:rgba(0, 0, 0, 0.1); color: #fff; border-radius: 40upx; display: inline-block; height: 40upx; line-height: 40upx; font-size: 25upx; margin-top: 10upx;}
	.user-qrcode-popup .close{ position: absolute; right: 20upx; top: 20upx; color: #fff; font-size: 25upx; }
	.user-qrcode-popup .content{ background: #fff; padding: 40upx 40upx 60upx; font-size: 25upx; border-radius: 0 0 40upx 40upx;}
	.user-qrcode-popup .content .ms{ text-align: center; }
	.user-qrcode-popup .content .total{ display: flex; flex-direction: row; margin-top: 40upx;}
	.user-qrcode-popup .content .total .total-item{ width: 33.3%; text-align: center; position: relative;}
	.user-qrcode-popup .content .total .total-item .number{ margin-top: 20upx; }
	.user-qrcode-popup .content .total .total-item:after{ position: absolute; content:""; width: 1px; background:#ecebeb; right: 0; top: 5upx; bottom: 5upx; }
	.user-qrcode-popup .content .total .total-item:last-child:after{ height: 0; }
	.user-qrcode-popup .content .qrcode{ display: flex; justify-content: center; margin: 50upx 0; }
	
	.user-qrcode-popup .not-mobile{ height: 200px; text-align: center; line-height: 50px;}
	.user-qrcode-popup .not-mobile .p{ font-size: 28upx; padding-top: 50px;}
	.user-qrcode-popup .not-mobile .button{ background:#eb5354; color: #fff; height: 60upx; line-height: 60upx; text-align: center; border-radius: 40upx; padding: 0 50upx; font-size: 28upx; display: inline-block;}
	
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
