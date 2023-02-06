<template>
    <div class="con con_main goods_details_content" ref="box" v-waterfall-lower="loadMore" waterfall-disabled="wDisabled" waterfall-offset="300">
        <template v-if="!dscLoading">
	        <header class="header-nav-content" :class="{'header-nav-fixed':!scrollState}">
	            <van-nav-bar left-arrow @click-left="onClickLeft">
	                <ul class="nav-con-warp" slot="title">
	                    <li :class="{'active':navActive == index}" v-for="(item,index) in navTabs" :key="index">
	                    	<span @click="jump(index)">{{ item }}</span>
	                    </li>
	                </ul>
	                <section slot="right" class="header_right">
	                    <div class="ico_box" :class="{'curr':is_collect == 1}"><i class="iconfont icon-guanzhu2" @click="collection"></i></div>
	                    <div class="ico_box" @click="showShortcutHandle">
							<i class="iconfont icon-gengduo1"></i>
							<ul class="shortcut" v-if="showShortcut">
								<li class="shortcut_item" v-for="(item, index) in shortcutData" :key="index" @click="routerLink(item.path)">
									<i :class="[item.ico]"></i>
									<span>{{ item.name }}</span>
								</li>
							</ul>
						</div>
	                </section>
	            </van-nav-bar>
	        </header>
	        <section class="goods_info" ref="goodsinfo">
	        	<!--相册/视频-->
	        	<div class="goods_photo_layer d_jump">
		            <div class="goods_photo">
		                <div class="goods_photo_warp">
		                    <van-swipe :height="swipe_height" @change="onSwipeChange" ref="goods_photo">
		                        <template v-if="gallery_list && gallery_list.length>0">
			                        <van-swipe-item v-for="(image, index) in gallery_list" :key="index" style="position: relative;">
			                            <img :src="image" class="imgalt" v-if="image" @click="previewImgs(index,gallery_list)" />
			                            <img src="../../assets/img/no_image.jpg" class="imgalt" v-else/>

			                            <!--商品标签-->
										<div class="goods-label-suspension" v-if="index == 0 && goodsInfo.goods_label_suspension && goodsInfo.goods_label_suspension.formated_label_image">
											<img :src="goodsInfo.goods_label_suspension.formated_label_image" class="img">
										</div>
			                        </van-swipe-item>
									<div class="custom-indicator goods-custom-indicator" slot="indicator">
										<span class="size_18">{{ currentSwipeItem + 1 }}</span>/{{gallery_list.length}}
									</div>
		                        </template>
		                        <template v-else>
		                            <van-swipe-item style="position: relative;">
		                                <img :src="goodsInfo.goods_img" class="imgalt" v-if="goodsInfo.goods_img"/>
		                                <img src="../../assets/img/no_image.jpg" class="imgalt" v-else/>

		                                <!--商品标签-->
										<div class="goods-label-suspension" v-if="index == 0 && goodsInfo.goods_label_suspension && goodsInfo.goods_label_suspension.formated_label_image">
											<img :src="goodsInfo.goods_label_suspension.formated_label_image" class="img">
										</div>
		                            </van-swipe-item>
		                        </template>
		                    </van-swipe>
		   					
		   					<!--视频按钮-->
							<div class="watch_video" @click="videoShow = true" v-if="goodsInfo.goods_video && index === 0">
								<i class="iconfont icon-bofang"></i>
								<span>{{$t('lang.goods_watchvideo')}}</span>
							</div>
		                </div>
		            </div>

		            <!--属性图片相册切换-->
		            <div class="goods-attr-img-list" v-if="attrColorList.length > 0">
		            	<div class="describe">{{ attrColorList.length }}{{$t('lang.goods_color_option')}}</div>
		            	<swiper class="imgs_scroll" :options="imgScrollOption">
							<swiper-slide class="com_img" :class="{'active':attrColorIndex == index}" v-for="(item, index) in attrColorList" :key="index" v-if="item.img_flie">
								<img :src="item.img_flie" class="img" @click="onSelectImg(item,index)" />
							</swiper-slide>
						</swiper>
		            </div>
		        </div>
	            <!--促销价格-->
	            <template v-if="goodsInfo.promote_end_date > 0 &&  goodsInfo.promote_start_date > 0">
	                <div class="activity_img_warp">
						<img class="activity_img" src="../../assets/img/activity_goods.png" />
	                    <div class="activity_left">
	                        <currency-price 
	                        :price="regionGoodsPirce ? regionGoodsPirce : goodsPriceManage" 
	                        :delPrice="goodsInfo.show_marketprice == 1 ? goodsMarketPriceManage : 0"
	                        :size="26"
	                        :delSize="14"
	                        color="#fff"
	                        delColor="#fff"
	                        ></currency-price>
	                    </div>
	                    <div class="activity_right">
	                        <p class="count_down_txt">{{$t('lang.promote_end')}}</p>
	                        <template v-if="goodsInfo.promote_start_date != undefined && goodsInfo.promote_end_date != undefined">
	                            <count-down class="seckill-time" :mini="true" :endTime="goodsInfo.promote_end_date" :endText="$t('lang.activity_end')"></count-down>
	                        </template>
	                    </div>
	                </div>
	            </template>

	            <!--普通价格-->
	            <div class="goods-price ai_fs" v-if="goodsInfo.promote_end_date == 0 &&  goodsInfo.promote_start_date == 0">
	                <div class="goods_price_wrap">
						<currency-price
						:price="regionGoodsPirce ? regionGoodsPirce : goodsPriceManage" 
						:delPrice="goodsInfo.show_marketprice == 1 ? goodsMarketPriceManage : 0"
						:size="24"
						:delSize="14"
						></currency-price>
						<span class="sold_out">{{$t('lang.goods_soltout')}} {{ goodsInfo.sales_volume }} {{$t('lang.jian')}}</span>
					</div>

					<!--普通商品分享start-->
					<div class="drp-share flex_box jc_center fd_column ai_center" @click="onGoodsShare" v-if="goodsInfo.is_show_drp == 1 && goodsInfo.is_drp > 0 && goodsInfo.commission_money != 0 && goodsInfo.is_distribution == 1">
					    <i class="iconfont icon-share"></i>
					    <span class="m-top04">
					    	<span class="m-r05">{{$t('lang.share_zhuan_alt')}}</span>
					    	<span v-html="goodsInfo.commission_money"></span>
					    </span>
					</div>
					<div class="drp-share flex_box jc_center fd_column ai_center" @click="onGoodsShare" v-else>
					    <i class="iconfont icon-share"></i>
					    <span>{{$t('lang.share')}}</span>
					</div>
					<!--普通商品分享end-->
	            </div>
				
				<!--预估税金-->
				<div class="goods-price goods_rate" v-if="goodsInfo.ru_id > 0 && goodsInfo.is_kj == 1">
				    <div class="goods_price_wrap" v-if="goodsInfo.goods_rate > 0">
						<span class="sold_out rate">{{$t('lang.import_tax')}}</span>
						<currency-price
						:price="goodsRate"
						:size="12"
						></currency-price>
					</div>
					<div class="goods_price_wrap" v-else>
						<span class="sold_out rate">{{$t('lang.import_tax')}}：{{$t('lang.goods_tax_included')}}</span>
					</div>
				</div>

	            <!--商品名称-->
	            <section class="goods_module_wrap border_radius_0">
					<div class="goods_title" :class="{'goods_title_active':goodsInfo.promote_end_date > 0 &&  goodsInfo.promote_start_date > 0}">
					    <div class="label-list" v-if="goodsInfo.goods_label">
					        <div class="label-img" v-for="(label,labelIndex) in goodsInfo.goods_label" :key="labelIndex">
					            <a :href="label.label_url ? label.label_url : 'javascript:;'"><img :src="label.formated_label_image" /></a>
					        </div>
					    </div>
					    <div class="goods_name_wrap">
							<h3 class="flex_1">
								<span class="span_block" v-if="goodsInfo.is_kj == 1"><em class="em-promotion ziying" style="background:#7a45e5">{{$t('lang.cross_goods')}}</em><img v-if="goodsInfo.country_icon != ''" class="country_icon" :src="goodsInfo.country_icon" /><em class="em_font">{{goodsInfo.country_name}}</em></span>
								<span class="span_block"><em v-if="goodsInfo.user_id == 0" class="em-promotion ziying self_support">{{$t('lang.self_support')}}</em>{{ goodsInfo.goods_name }}</span>
								
							</h3>
							<!--促销活动分享-->
							<template v-if="goodsInfo.promote_end_date > 0 &&  goodsInfo.promote_start_date > 0">
								<div class="drp-share flex_box jc_center fd_column ai_center" @click="onGoodsShare" v-if="goodsInfo.is_show_drp == 1 && goodsInfo.is_drp > 0 && goodsInfo.commission_money != 0 && goodsInfo.is_distribution == 1">
								    <i class="iconfont icon-share"></i>
								    <span class="m-top04">
								    	<span class="m-r05">{{$t('lang.share_zhuan_alt')}}</span>
								    	<span v-html="goodsInfo.commission_money"></span>
								    </span>
								</div>
								<div class="drp-share flex_box jc_center fd_column ai_center" @click="onGoodsShare" v-else>
								    <i class="iconfont icon-share"></i>
								    <span>{{$t('lang.share')}}</span>
								</div>
							</template>
						</div>
					    <p class="f-05 color_999 m-top02" v-if="goodsInfo.goods_brief">{{ goodsInfo.goods_brief }}</p>
					</div>

					<!--高级vip start-->
					<div class="hig-vip" :class="{'pt10':goodsInfo.promote_end_date > 0 &&  goodsInfo.promote_start_date > 0}" v-if="goodsInfo.is_show_drp == 1 && goodsInfo.is_drp && goodsInfo.drp_shop == 0">
					    <div class="hig-vip-warp">
					        <div class="text">
					            <i><img src="../../assets/img/newuser/icon-vip.png" class="img"></i>
					            <template v-if="goodsInfo.membership_card_discount_price > 0">
					                <span>{{$t('lang.high_grade_vip_tips_1')}}</span>
					                <currency-price :price="goodsInfo.membership_card_discount_price" color="#333"></currency-price>
					            </template>
					            <template v-else>{{$t('lang.high_grade_vip_tips_1')}}</template>
					        </div>
					        <div class="vip-register">
					            <template v-if="goodsInfo.drp_shop_membership_card_id && goodsInfo.drp_shop_membership_card_id > 0">
					                <a href="javascript:;" v-if="isLogin" @click="onVipRepurchase(goodsInfo.drp_shop_membership_card_id)">
					                    <span>{{$t('lang.re_purchase')}}</span>
					                    <i class="iconfont icon-more"></i>
					                </a>
					            </template>
					            <template v-else>
					                <a href="javascript:;" v-if="isLogin" @click="onVipRegister">
					                    <span>{{$t('lang.immediately_opened')}}</span>
					                    <i class="iconfont icon-more"></i>
					                </a>
					                <router-link :to="{ name: 'login' }" v-else><span>{{$t('lang.immediately_opened')}}</span><i class="iconfont icon-more"></i></router-link>
					            </template>
					        </div>
					    </div>
					</div>
					<!--高级vip end-->
				</section>
				
				<!-- 优惠与活动 start -->
				<section class="goods_module_wrap m-top08" v-if="formatDiscountsData.length > 0 || activityData.length > 0">
					<div class="activity_content" @click="showDiscount = true" v-if="formatDiscountsData.length > 0">
						<div class="title">{{$t('lang.goods_discounts')}}</div>
						<div class="activity_main">
							<div class="activity_item">
								<img src="../../assets/img/lower_price.png" />
								<span>{{$t('lang.activities_option')}}</span>
							</div>
							<div class="activity_item" v-for="(activityItem, activityIndex) in formatDiscountsData" :key="activityIndex">
								<template v-if="activityIndex < 2">
									<span class="activity_bg">{{activityItem.label}}</span>
									<span class="flex_1 activity_tips" v-if="activityItem.acttype != 0">{{activityItem.value}}</span>
									<template v-if="activityItem.acttype == 0">
										<span class="coupon_bg_wrap" v-for="(item, index) in activityItem.value" :key="index"><span class="coupon_bg">{{$t('lang.man')}}{{ item.cou_man }}{{$t('lang.minus')}}{{item.cou_money}}</span></span>
									</template>
								</template>
								<template v-else>
									<span class="activity_bg" v-for="(item, index) in activityItem" :key="index">{{item.label}}</span>
								</template>
							</div>
						</div>
						<div class="activity_ico"><i class="iconfont icon-gengduo2"></i></div>
					</div>
					<div class="activity_content" @click="showActivity = true" v-if="activityData.length > 0">
						<div class="title">{{$t('lang.goods_activity')}}</div>
						<div class="activity_main">
							<div :class="['activity_item', index == 0 ? 'activity_item_mt' : '']" v-for="(item, index) in activityData" :key="index">
								<span class="activity_bg">{{item.label}}</span>
								<span class="activity_tips">{{item.value}}</span>
							</div>
						</div>
						<div class="activity_ico"><i class="iconfont icon-gengduo2"></i></div>
					</div>
				</section>
				<!-- 优惠与活动 end -->
				
				<!--其他信息-->
				<section class="goods_module_wrap m-top08">
					<!--已选属性-->
					<div class="activity_content" @click="skuLink">
						<div class="title">{{$t('lang.label_selected_2')}}</div>
						<div class="activity_main">{{attr_name}}</div>
						<div class="activity_ico"><i class="iconfont icon-gengduo2"></i></div>
					</div>
					
					<!--保税仓发货地-->
					<div class="activity_content" v-if="goodsInfo.is_kj == 1 && goodsInfo.cross_warehouse_name != ''">
						<div class="title">{{$t('lang.place_of_shipment')}}</div>
						<div class="activity_main">{{goodsInfo.cross_warehouse_name}}</div>
					</div>
					
					<!--配送地区-->
					<div class="activity_content" @click="showRegionShow">
						<div class="title">{{$t('lang.label_send_to_2')}}</div>
						<div class="activity_main">
							<div class="location_wrap">
								<img class="location_ico" src="../../assets/img/location_ico.png" />
								{{regionSplic}}
							</div>
						</div>
						<div class="activity_ico"><i class="iconfont icon-gengduo2"></i></div>
					</div>
					<!--门店-->
					<div class="activity_content" @click="handleStore" v-if="shipping_fee.store_count > 0 && shipping_fee.store_info">
						<div class="title">{{$t('lang.store')}}</div>
						<div class="activity_main">
							<div class="store_wrap">
								<div class="store_name">
									<img class="store_ico" src="../../assets/img/store_ico.png" />
									{{shipping_fee.store_info.stores_name}}
								</div>
								<div class="address_text">{{shipping_fee.store_info.stores_address}}</div>
							</div>
						</div>
						<div class="activity_ico store_arrow_ico"><i class="iconfont icon-more size_12"></i></div>
					</div>
					<!--运费-->
					<div class="activity_content yunfei">
						<div class="title">{{$t('lang.label_freight_2')}}</div>
						<div class="activity_main">
							<span v-if="goodsInfo.is_shipping == 1 || freeShipping == 1" style="color: #AF743A;">{{$t('lang.pinkage')}}</span>
							<span v-else v-html="freight"></span>
							<span class="color-red" v-if="shipping_fee.shipping_title && shipping_fee.shipping_title != 0">({{ shipping_fee.shipping_title }})</span>
						</div>
					</div>
					<!--服务-->
					<div class="activity_content server" v-if="goodsInfo.goods_services_label && goodsInfo.goods_services_label.length" @click="showServerPop = true">
						<div class="title">{{$t('lang.service')}}</div>
						<div class="activity_main flex_box fw_w">
							<div class="server_item" v-for="(item, index) in goodsInfo.goods_services_label" :key="index">
								<img class="server_ico" :src="item.formated_label_image" />
								<span class="text_1">{{item.label_name}}</span>
							</div>
						</div>
						<div class="activity_ico"><i class="iconfont icon-gengduo2"></i></div>
					</div>
				</section>
				
				<!-- 商品评论/网友讨论 start -->
				<section class="goods_module_wrap m-top08 comment_wrap" v-if="this.goodsInfo.shop_can_comment > 0">
					<!-- 商品评论 -->
					<div class="goods_comment d_jump">
						<div class="title_box">
							<div class="title_text">
								<span>{{commentTotal.total > 0 ? $t('lang.comment') : $t('lang.no_comment')}}</span>
								<span class="comment_count" v-if="commentTotal.total > 0">{{commentTotal.total}}{{$t('lang.tiao')}}</span>
							</div>
							<div  @click="commentHandle">
								<span class="drgree_of_praise" v-if="commentTotal.total > 0">{{$t('lang.high_praise')}}{{commentTotal.good}}</span>
								<i class="iconfont icon-more size_12"></i>
							</div>
						</div>
						<div class="comment-items" v-if="goodsComment.length > 0">
							<div class="comitem" v-for="(item, index) in goodsComment" :key="index">
								<div class="item_header">
									<img :src="item.user_picture" class="head_l" v-if="item.user_picture">
									<img src="../../assets/img/get_avatar.png" class="head_l" v-else>
									<div class="head_r">
										<div class="com_name">{{item.user_name}}</div>
										<div class="com_time">
											<div class="rate_wrap"><i :class="['iconfont', 'icon-wujiaoxing', 'size_12', rate <= item.rank ? 'color_red' : '']" v-for="(rate, rIndex) in 5" :key="rIndex"></i></div>
											<span class="comment_time">{{ item.add_time }}</span>
										</div>
									</div>
								</div>
								<div class="item_body">
									<div class="comment_con">{{ item.content }}</div>
									<swiper class="imgs_scroll" :options="imgScrollOption">
										<swiper-slide class="com_img" v-for="(val, ind) in item.comment_img" :key="ind">
											<img :src="val" @click="previewImgs(ind, item.comment_img)" v-if="val" />
											<img src="../../assets/img/no_image.jpg" v-else />
										</swiper-slide>
									</swiper>
								</div>
								<div class="item_footer" v-if="item.goods_attr">{{item.goods_attr}}</div>
							</div>
							<div class="flex_box jc_center" v-if="goodsComment.length > 1">
								<div class="goods_module_btn" @click="commentHandle">{{$t('lang.view_all_comments')}}</div>
							</div>
						</div>
					</div>
					<!-- 网友讨论 -->
					<div class="goods_discover">
						<div class="title_box">
							<div class="title_text">
								<span>{{$t('lang.discuss_circle')}}</span>
							</div>
							<div @click="onDiscover">
								<span class="drgree_of_praise">{{$t('lang.view_all')}}</span>
								<i class="iconfont icon-more size_12"></i>
							</div>
						</div>
						<div class="friend_discuss" @click="onDiscover" v-if="friendDis.length > 0">
							<div class="discuss_item" v-for="(item, index) in friendDis" :key="index">
								<span class="dis_label" v-if="item.dis_type == 1">{{$t('lang.discuss')}}</span>
								<span class="dis_label" v-else>{{$t('lang.interlocution')}}</span>
								<span class="dis_value">{{item.dis_title}}</span>
								<span class="dis_time">{{item.add_time}}</span>
							</div>
						</div>
						<div class="no_dis" v-else>
							<div class="no_dis_title">{{$t('lang.no_discuss_tops')}}</div>
							<div class="goods_module_btn" @click="onDiscover">{{$t('lang.initiate_a_topic')}}</div>
						</div>
					</div>
				</section>
				<!-- 商品评论/网友讨论 end -->
				
				<!-- 商品所属店铺 start -->
				<section class="goods_module_wrap m-top08" @click="goLink({name: 'shopHome', params: {id: shopDetail.ru_id}})" v-if="goodsInfo.user_id">
					<div class="store_hade">
						<img class="store_logo" :src="shopDetail.shop_logo" />
						<div class="store_name_rate">
							<div class="sto_name">
								{{shopDetail.shop_name}} <em v-if="goodsInfo.cross_source" style="color: #AF743A; font-weight: normal; font-size: 1.2rem;">{{ goodsInfo.cross_source }}</em>
							</div>
							<div class="sto_rate_wrap">
								<div class="sto_rate">
									<span>{{$t('lang.store_star')}}</span>
									<i :class="['iconfont', 'icon-wujiaoxing', 'size_12', rIndex < storeRate ? 'color_red' : '']" v-for="(rate, rIndex) in 5" :key="rIndex"></i>
								</div>
							</div>
						</div>
						<i class="iconfont icon-more size_12"></i>
					</div>
					<div class="store_body">
						<div class="count_item">
							<div class="count_text">{{shopDetail.count_gaze}}</div>
							<div>{{$t('lang.follow_number')}}</div>
						</div>
						<div class="count_item">
							<div class="count_text">{{shopDetail.count_goods}}</div>
							<div>{{$t('lang.all_goods')}}</div>
						</div>
						<div class="count_item">
							<div class="serve_rate">
								<span>{{$t('lang.comment')}}</span>
								<span :class="[shopDetail.commentrank && shopDetail.commentrank.slice(0, 1) < 3 ? 'color_green' : 'color_red']">{{shopDetail.commentrank}} {{shopDetail.commentrank_font}}</span>
							</div>
							<div class="serve_rate">
								<span>{{$t('lang.logistics')}}</span>
								<span :class="[shopDetail.commentrank && shopDetail.commentdelivery.slice(0, 1) < 3 ? 'color_green' : 'color_red']">{{shopDetail.commentdelivery}} {{shopDetail.commentdelivery_font}}</span>
							</div>
							<div class="serve_rate">
								<span>{{$t('lang.after_sales')}}</span>
								<span :class="[shopDetail.commentrank && shopDetail.commentserver.slice(0, 1) < 3 ? 'color_green' : 'color_red']">{{shopDetail.commentserver}} {{shopDetail.commentserver_font}}</span>
							</div>
						</div>
					</div>
					<div class="store_footer">
						<div class="store_btn" :class="{'cur': is_collect_shop == 1}" @click.stop="collectHandle(shopDetail.ru_id)">
							<img src="../../assets/img/store_follow.png" v-if="is_collect_shop == 0" />
							<img src="../../assets/img/store_follow2.png" v-else />
							{{is_collect_shop == 1 ? $t('lang.followed') : $t('lang.attention_store')}}
						</div>
						<div class="store_btn">
							<img src="../../assets/img/into_shop.png" />
							{{$t('lang.go_shopping')}}
						</div>
					</div>
				</section>
				<!-- 商品所属店铺 end -->
				
				<!-- 搜索/本店推荐/猜你喜欢 statr -->
				<section class="goods_module_wrap recomment_wrap m-top08">
					<!--搜索-->
					<div class="search">
						<div class="search_wrap" @click="routerLink('search')">
							<div class="search_input">
								<i class="iconfont icon-home-search size_20"></i>
								{{$t('lang.search_goods_placeholder')}}
							</div>
							<div class="search_btn">{{$t('lang.search')}}</div>
						</div>
						<div class="top_search" v-if="search_keywords.length > 0">
							<div class="top_search_lable">{{$t('lang.hot_search')}}：</div>
							<div class="top_search_keyword" v-for="(item,index) in search_keywords" :key="index" @click="goLink({name: 'searchList', query: {keywords: item}})">{{item}}</div>
						</div>
					</div>
					<!--本店推荐/猜你喜欢-->
					<div class="recomment" v-if="recommentTabs.length > 0">
						<div class="re_tabs">
							<div :class="['re_tabs_item', currenTab == index ? 'active_tab' : '']" v-for="(item, index) in recommentTabs" :key="index" @click="changeTab(index)">{{item.title}}</div>
						</div>
						<div class="goods_list_wrap" v-for="(item, index) in goodsGuessList" :key="index">
							<template v-if="currenTab == index">
								<van-swipe :show-indicators="false" :loop="false" @change="onChangeRecomment">
									<van-swipe-item v-for="(pageItem, pageIndex) in Math.ceil(item.length / 6)" :key="pageIndex">
										  <section class="glist" :style="swipeItemHeight">
											  <div class="gitem" v-for="(goodsItem, goodsIndex) in item.slice(pageIndex * 6, pageItem * 6)" :key="goodsIndex" @click="detailLink(goodsItem)">
												  <img :src="goodsItem.goods_thumb" v-if="goodsItem.goods_thumb" />
												  <img src="../../assets/img/no_image.jpg" class="img" v-else>
												  <p><img v-if="goodsItem.country_icon != ''" style="width: 2.4rem; display: inline-block;" class="country_icon_image" :src="goodsItem.country_icon" />{{goodsItem.goods_name}}</p>
												  <currency-price :price="goodsItem.shop_price" :size="16" style="padding: 6px 0 8px;"></currency-price>
											  </div>
										  </section>
									</van-swipe-item>
									<ul class="custom-indicator recomment-custom-indicator" slot="indicator">
										<li :class="['indicator', curIndex == reCurSwipeItem ? 'active_indicator' : '']" v-for="(curIndicator, curIndex) in Math.ceil(item.length / 6)" :key="curIndex"></li>
									</ul>
								</van-swipe>
							</template>
						</div>
					</div>
				</section>
				<!-- 搜索/本店推荐/猜你喜欢 end -->
				
				<!-- 推荐文章 start -->
				<section class="goods_module_wrap article_wrap m-top08"  v-if="goodsInfo.goods_article_list && goodsInfo.goods_article_list.length > 0">
					<div class="title_box">
						<div class="title_text">
							<span>{{$t('lang.Recommended_articles')}}</span>
						</div>
					</div>
				
					<div :class="['article_item', index > 0 ? 'acticle_border' : '']" v-for="(item, index) in goodsInfo.goods_article_list" :key="index" @click="goLink({'name':'articleDetail',params:{id:item.article_id}})">
						<img class="article_img" :src="item.file_url" v-if="item.file_url" />
						<div class="article_content">
							<p>{{item.title}}</p>
							<span>{{item.add_time}}</span>
						</div>
					</div>
				</section>
				<!-- 推荐文章 end -->
				
				<!-- 详情 start -->
				<section class="goods_module_wrap goods_detail_wrap m-top08 d_jump">
					<div class="title_box">
						<div class="title_text">
							<span>{{$t('lang.detail')}}</span>
						</div>
					</div>
					<template v-if="goodsInfo.goods_desc">
						<div class="image_text">
							<div class="image_text_title">
								<img src="../../assets/img/img_txt_ico.png" />
								<span>{{$t('lang.goods_img_txt')}}</span>
							</div>
							
							<div class="goods_desc">
								<u-parse :html="goodsInfo.goods_desc" :lazy-load="false" :tag-style="{video: 'width: 100%!important; height: auto!important; margin: 0!important;'}"></u-parse>
							</div>
						</div>
					</template>
					<template v-if="goodsInfo.attr_parameter">
						<div class="image_text_title">
							<img src="../../assets/img/goods_param.png" />
							<span>{{$t('lang.commodity_parameters')}}</span>
						</div>
						<div class="goods_attr_parameter">
							<table cellpadding="0" cellspacing="1" width="100%" border="0" :class="['Ptable', 'param_table', isViewMore ? '' : 'attr_wrap']">
							    <tbody v-if="goodsInfo.show_goodssn == 1">
							        <tr><td>{{$t('lang.goods_sn')}}</td><td>{{ goodsInfo.goods_sn }}</td></tr>
							    </tbody>
							    <tbody>
							        <tr><th class="tdTitle" colspan="2">{{$t('lang.basic_info')}}</th></tr>
							        <tr v-if="goodsInfo.show_brand == 1"><td>{{$t('lang.brand')}}</td><td>{{ goodsInfo.brand_name }}</td></tr>
							        <tr v-if="goodsInfo.show_goodsweight == 1"><td>{{$t('lang.goods_weight')}}</td><td>{{ goodsInfo.goods_weight }}kg</td></tr>
							        <tr v-if="goodsInfo.show_addtime == 1"><td>{{$t('lang.add_time')}}</td><td>{{ goodsInfo.add_time_format }}</td></tr>
							        <tr v-for="item in goodsInfo.attr_parameter"><td>{{ item.attr_name }}</td><td>{{ item.attr_value }}</td></tr>
							    </tbody>
							</table>
							<div class="flex_box jc_center" v-if="moreAttr">
								<div class="view_more_btn" @click="isViewMore = !isViewMore">{{isViewMore ? $t('lang.shouqi') : $t('lang.zhankai')}}<i :class="['iconfont', isViewMore ? 'icon-less' : 'icon-moreunfold', 'size_12']"></i></div>
							</div>
						</div>
					</template>
				</section>
				<!-- 详情 end -->
				
				<!-- 猜你喜欢 start -->
				<section class="goods-detail-guess text-center d_jump" v-if="guessList.length > 0">
					<h5 class="title-hrbg"><span>{{$t('lang.same_style')}}</span><hr></h5>
					<section class="product-list product-list-medium">
						<ProductList :data="guessList" routerName="goods" :productOuter="true"></ProductList>
						<div class="footer-cont" v-if="isOver">{{$t('lang.no_more')}}</div>
						<template v-if="!isOver && guessList.length >= 10">
							<van-loading type="spinner" color="black" />
						</template>
					</section>
				</section>
				<!-- 猜你喜欢 end -->
				
				<!-- 底部版权 -->
				<dsc-copyright></dsc-copyright>
				
				<!-- 优惠弹框 start -->
				<van-popup class="show-popup-common show-popup-coupon" v-model="showDiscount" position="bottom">
					<section class="pop_content" id="pop_content">
						<header class="pop_header">
							<span>{{$t('lang.goods_discounts')}}</span>
							<div class="pop_close" @click="showDiscount = false"><i class="iconfont icon-close size_12"></i></div>
						</header>
						<div class="pop_main">
							<div class="label_text">{{$t('lang.promotion')}}</div>
							<div class="pop_activity_item" v-for="(activityItem, activityIndex) in discountsData" :key="activityIndex">
								<template v-if="activityItem.acttype != 0">
									<span class="activity_bg">{{activityItem.label}}</span>
									<span class="flex_1 activity_tips">{{activityItem.value}}</span>
								</template>
							</div>
							<template v-if="goodsCouponList && goodsCouponList.length > 0">
								<div class="label_text">{{$t('lang.coupons_available')}}</div>
								<div class="coupons-list">
									<ul>
										<li v-for="(item,index) in goodsCouponList" :key="index">
											<div class="left">
												<div class="coupon-price">{{ currency }}{{item.cou_money}}</div>
												<div class="coupon-desc">{{$t('lang.man')}}{{ item.cou_man }}{{$t('lang.available_full')}}</div>
											</div>
											<div class="right">
												<div class="coupon-tit">
													<em class="tag">{{$t('lang.coupon_tab_2')}}</em>
													<span>{{$t('lang.limit')}}{{item.shop_name}}{{$t('lang.usable')}}[{{item.cou_goods_name}}]</span>
												</div>
												<div class="time get_coupon">
													<div class="time_text">{{ item.cou_start_time }} - {{ item.cou_end_time }}</div>
													<div class="coupon_btn u-reset-disabled" v-if="item.cou_is_receive == 1 && isLogin">{{ $t('lang.receive_hove') }}</div>
													<div class="coupon_btn u-reset-disabled" v-else-if="item.enable_ling > 0">{{ $t('lang.brought_up') }}</div>
													<div class="coupon_btn" v-else @click="handelReceive(item.cou_id)">{{ $t('lang.click_to_collect') }}</div>
												</div>
											</div>
										</li>
									</ul>
								</div>
							</template>
						</div>
					</section>
				</van-popup>
				<!-- 优惠弹框 end-->
				
				<!-- 活动弹框 start-->
				<van-popup class="show-popup-common show-popup-coupon" v-model="showActivity" position="bottom">
					<section class="pop_content activity_pop">
						<header class="pop_header">
							<span>{{$t('lang.goods_activity')}}</span>
							<div class="pop_close" @click="showActivity = false"><i class="iconfont icon-close size_12"></i></div>
						</header>
						<div class="pop_main">
							<div class="act_item" v-for="(item, index) in activityData" :key="index" @click="goLink(item.path)">
								<div class="act_main">
									<div class="act_label">{{item.label}}</div>
									<div class="act_value" v-if="item.acttype == 2">
										<div>{{item.value}}</div>
										<div class="recommend_title">{{$t('lang.recommended_accessories')}} {{$t('lang.hig_discount')}} <span class="activity_price">{{spare_price}}</span> {{$t('lang.yuan')}}</div>
										<div class="set_meal">
											<div class="img_wrap">
												<img :src="goodsInfo.goods_thumb" />
											</div>
											<template v-for="(item, index) in goodsInfo.fittings">
												<div class="img_wrap" :key="index" v-if="index < 3">
													<img :src="item.goods_thumb" />
												</div>
											</template>
											<i class="iconfont icon-gengduo1" v-if="goodsInfo.fittings.length > 3"></i>
										</div>
									</div>
									<div class="act_value" v-else>{{item.value}}</div>
								</div>
								<div class="act_ico"><i class="iconfont icon-more size_12"></i></div>
							</div>
						</div>
					</section>
				</van-popup>
				<!-- 活动弹框 end-->
				
				<!-- 配送弹框 start -->
				<van-popup class="show-popup-common show-popup-coupon" v-model="showAddredd" @click-overlay="regionShow = false" position="bottom">
					<section class="pop_content address_pop">
						<header class="pop_header">
							<div class="pop_back" @click="selectAddresss" v-if="isBack"><i class="iconfont icon-back size_14"></i></div>
							<span>{{$t('lang.delivery_to_the')}}</span>
							<div class="pop_close" @click="addressClose"><i class="iconfont icon-close size_12"></i></div>
						</header>
						<div class="pop_main address_pop_main">
							<van-swipe ref="swiperAddress" :touchable="false" :initial-swipe="initialSwipe" :loop="false" :show-indicators="false">
								<van-swipe-item v-if="isLogin">
									<div class="address_wrap">
										<div class="address_main">
											<van-radio-group v-model="selectAddress">
												<van-cell @click="changeAddressRadio(item)" v-for="(item, index) in addressList" :key="index">
													<div slot="title" class="address_item">
														<van-radio :name="item.id" />
														<span class="address_val">{{ item.province_name }} {{ item.city_name }} {{item.district_name}} {{item.street_name}} {{item.address}}</span>
													</div>
												</van-cell>
											</van-radio-group>
										</div>
										<div class="select_btn van-hairline--top">
											<van-button round @click="selectAddresss">{{$t('lang.choose_another_address')}}</van-button>
										</div>
									</div>
								</van-swipe-item>
								<van-swipe-item>
									<!--商品地区选择-->
									<Region :isPrice.sync="isPrice" :display="regionShow" regionType="goods" :regionOptionDate="regionOptionDate" @updateDisplay="getRegionShow" @updateRegionDate="getRegionOptionDate" v-if="regionLoading"></Region>
								</van-swipe-item>
							</van-swipe>
						</div>
					</section>
				</van-popup>
				<!-- 配送弹框 end -->
				
				<!-- 服务弹框 start -->
				<van-popup class="show-popup-common show-popup-coupon" v-model="showServerPop" @click-overlay="showServerPop = false" position="bottom">
					<section class="pop_content address_pop">
						<header class="pop_header">
							<span>{{$t('lang.service')}}</span>
							<div class="pop_close" @click="showServerPop = false"><i class="iconfont icon-close size_12"></i></div>
						</header>
						<div class="pop_main address_pop_main">
							<div class="address_wrap" style="height: 100%;">
								<div class="address_main">
									<div class="server_list" v-for="(item,index) in goodsInfo.goods_services_label" :key="index">
										<div class="server_item">
											<img class="server_ico" :src="item.formated_label_image" />
											<span>{{item.label_name}}</span>
										</div>
										<p class="color_999">{{item.label_explain}}</p>
									</div>
								</div>
								<div class="select_btn van-hairline--top">
									<van-button round @click="showServerPop = false">{{$t('lang.confirm')}}</van-button>
								</div>
							</div>
						</div>
					</section>
				</van-popup>
				<!-- 服务弹框 end -->
				
				<!-- 底部功能区 start -->
				<footer class="submit_bar ipx-padding-bottom van-hairline-shadow--top">
					<div class="stockout" v-if="is_alone_sale || (!(regionGoodsNumberFormated ? regionGoodsNumberFormated : goodsNumberManage > 0)) || (goodsInfo.is_show_drp == 1 && goodsInfo.is_drp > 0 && goodsInfo.commission_money != 0 && goodsInfo.is_distribution == 1)">
						<p v-if="is_alone_sale">{{ $t('lang.no_alone_sale') }}</p>
						<p v-else-if="!(regionGoodsNumberFormated ? regionGoodsNumberFormated : goodsNumberManage > 0)">{{$t('lang.no_goods_tips')}}</p>
						<p v-else-if="goodsInfo.is_show_drp == 1 && goodsInfo.is_drp > 0 && goodsInfo.commission_money != 0 && goodsInfo.is_distribution == 1">{{$t('lang.share_goods_tips')}}</p>
					</div>
					<div class="submit_bar_warp">
						<div class="function_item" @click="goLink({name: 'shopHome', params: {id: ru_id}})" v-if="ru_id > 0">
							<img src="../../assets/img/store_ico.png" />
							<span>{{$t('lang.shop')}}</span>
						</div>
						<div class="function_item" @click="onChat(goods_id,goodsInfo.user_id)">
							<img src="../../assets/img/service_ico.png" />
							<span>{{$t('lang.customer_service')}}</span>
						</div>
						<div class="function_item badge_parent" @click="goLink({name:'cart'})">
							<div class="info_num" v-if="cart_number > 0">{{cart_number}}</div>
							<img src="../../assets/img/cart_ico.png" />
							<span>{{$t('lang.cart')}}</span>
						</div>
						<template v-if="is_on_sale">
							<van-button round class="cur_btn">{{$t('lang.goods_sold_out')}}</van-button>
						</template>
						<template v-else-if="is_alone_sale">
                        	<van-button round @click="goodsAloneSale" class="cur_btn">{{$t('lang.goods_alone_sale')}}</van-button>
                        </template>
						<template v-else>
							<template v-if="regionGoodsNumberFormated ? regionGoodsNumberFormated : goodsNumberManage > 0">
								<van-button round @click="onSku(0)" class="cur_btn">{{$t('lang.add_cart')}}</van-button>
								<van-button round @click="onSku(1)" class="buynow" :loading="loading" :disabled="disabled">
									{{ goodsInfo.best_price && goodsInfo.best_price.cou_id > 0 ? $t('lang.coupon_purchase') : $t('lang.button_buy') }}
									<div class="jiage" v-if="goodsInfo.best_price.price >= 0">{{$t('lang.on_hand_price')}} {{ goodsInfo.best_price.formated_price }}</div>
								</van-button>
							</template>
							<template v-else>
								<van-button round class="cur_btn">{{$t('lang.understock')}}</van-button>
							</template>
						</template>
					</div>
				</footer>
				<!-- 底部功能区 end -->
	        </section>
        </template>

        <!-- 属性弹窗 -->
        <van-popup v-model="showBase" position="bottom" class="attr-goods-box">
            <div class="attr-goods-header">
                <template v-if="attr != ''">
                    <div class="attr-img" @click="previewImgs(0,goodsAttrOper.attr_img ? goodsAttrOper.attr_img : goodsInfo.goods_img)">
                        <img :src="goodsAttrOper.attr_img" class="img" v-if="goodsAttrOper.attr_img" />
                        <img :src="goodsInfo.goods_img" class="img" v-else>
                    </div>
                    <div class="attr-info">
                        <div class="attr-price">
                        	<currency-price :price="goodsAttrOper.goods_price" :size="26"></currency-price>
                        </div>
                        <div class="attr-other">{{$t('lang.label_selected')}}{{ goodsAttrOper.attr_name }} {{ num }}{{ goodsInfo.goods_unit }}</div>
						<div class="attr-stock flex_box">
							<span style="flex: none; margin-right: 10px;" v-if="goodsInfo.show_goodsnumber > 0">{{$t('lang.label_stock')}}{{ goodsAttrOper.stock }}</span>
							<span v-if="goodsInfo.goods_sn && goodsInfo.show_goodssn == 1">{{$t('lang.commodity_number')}}：{{ goodsInfo.goods_sn }}</span>
						</div>
                    </div>
                </template>
                <template v-else>
                    <div class="attr-img" @click="previewImgs(0,goodsInfo.goods_img)">
                        <img :src="goodsInfo.goods_img" class="img"/>
                    </div>
                    <div class="attr-info">
                        <div class="attr-name twolist-hidden">{{ goodsInfo.goods_name }}</div>
                        <div class="attr-price" v-html="goodsPriceManage">{{ goodsPriceManage }}</div>
                        <div class="attr-stock flex_box">
							<span style="flex: none; margin-right: 10px;" v-if="goodsInfo.show_goodsnumber > 0">{{$t('lang.label_stock')}}{{ goodsInfo.goods_number }}</span>
							<span v-if="goodsInfo.goods_sn && goodsInfo.show_goodssn == 1">{{$t('lang.commodity_number')}}：{{ goodsInfo.goods_sn }}</span>
						</div>
                    </div>
                </template>
                <div class="close">
	                <i class="iconfont icon-close" @click="closeSku"></i>
	            </div>
            </div>
            <div class="attr-goods-content" v-if="attr != ''">
                <van-radio-group class="sku-item" v-model="goodsAttrInit[index]" v-for="(item,index) in attr" :key="index" v-if="item.attr_type == 1">
                    <div class="sku-tit">{{ item.name }}</div>
                    <div class="sku-list">
                        <template v-for="(option,listIndex) in item.attr_key">
                            <van-radio class="option" :class="{'active':goodsAttrInit[index] == option.goods_attr_id}" :name="option.goods_attr_id">{{ option.attr_value }}
                            </van-radio>
                        </template>
                    </div>
                </van-radio-group>
                <van-checkbox-group class="sku-item" v-model="goodsAttrInit" v-for="(item,index) in attr" :key="index" v-if="item.attr_type == 2">
                    <div class="sku-tit">{{ item.name }}</div>
                    <div class="sku-list">
                        <template v-for="(option,listIndex) in item.attr_key">
                            <van-checkbox class="option" :class="{'active':goodsAttrInit.indexOf(option.goods_attr_id)!=-1}" :name="option.goods_attr_id">{{ option.attr_value }}
                            </van-checkbox>
                        </template>
                    </div>
                </van-checkbox-group>
            </div>
            <div class="attr-goods-number dis-box">
                <span class="tit">
                	<span>{{$t('lang.number')}}</span>
                	<span>
	                	<em v-if="goodsInfo.is_minimum > 0">({{goodsInfo.minimum}}{{goodsInfo.goods_unit}}{{$t('lang.label_minimum_2')}})</em>
	                	<em v-if="goodsInfo.xiangou_num > 0 && goodsInfo.xiangou_end_date > goodsInfo.current_time">({{$t('lang.purchase_only')}}{{goodsInfo.xiangou_num}}{{goodsInfo.goods_unit}})</em>
	                </span>
                </span>
                <div class="stepper">
                    <van-stepper
                        v-model="number"
                        integer
                        :min="goodsInfo.is_minimum > 0 ? goodsInfo.minimum : 1"
                        :max="goodsInfo.xiangou_num > 0 && goodsInfo.xiangou_end_date > goodsInfo.current_time ? goodsInfo.xiangou_num : stock"
                    />
                </div>
            </div>
            <div class="submit_bar ipx-padding-bottom">
                <template v-if="!storeBtn">
                    <template v-if="zFittingAttr">
                        <van-button round @click="onFittingAttr">{{$t('lang.confirm')}}</van-button>
                    </template>
                    <template v-else>
                        <template v-if="is_on_sale">
                        	<van-button round class="cur_btn">{{$t('lang.goods_sold_out')}}</van-button>
                        </template>
                        <template v-else-if="is_alone_sale">
                        	<van-button round class="cur_btn">{{$t('lang.goods_alone_sale')}}</van-button>
                        </template>
                        <template v-else>
                            <template v-if="stock">
                            	<van-button round @click="onAddCartClicked(0)" class="cur_btn" v-if="addType == 1 || addType == ''">{{$t('lang.add_cart')}}</van-button>
								<van-button round @click="onAddCartClicked(10)" v-if="addType == 2 || addType == ''">{{$t('lang.button_buy')}}</van-button>
                            </template>
                            <template v-else>
                            	<van-button round class="cur_btn">{{$t('lang.understock')}}</van-button>
                            </template>
                        </template>
                    </template>
                </template>
                <template v-else>
                	<van-button round @click="onStoreClicked">{{$t('lang.private_store')}}</van-button>
                </template>
            </div>
        </van-popup>

        <!-- 优惠券 -->
        <van-popup v-model="couponShow" position="bottom" class="show-popup-bottom show-goods-coupon">
            <section class="goods-show-title padding-all">
                <h3 class="fl">{{$t('lang.receive_coupon')}} ({{ goodsInfo.coupon_count }})</h3>
                <i class="iconfont icon-close fr" @click="handelClose('coupon')"></i>
            </section>
            <swiper class="goods-show-con" :options="swiperOption">
                <swiper-slide>
                    <div class="padding-all">
                        <van-loading type="spinner" color="white" v-if="conpouLoading"/>
                        <template v-else>
                            <ul v-if="goodsCouponList && goodsCouponList.length > 0">
                                <li class="new-coupons-box dis-box" v-for="(item,index) in goodsCouponList">
                                    <div class="remark-all box-flex">
                                        <div class="q-type">
                                            <div class="b-r-a-price">
                                                <em>{{ currency }}</em>
                                                <strong class="coupons-money">{{ item.cou_money }}</strong>
                                                <div class="couons-text"><span>{{$t('lang.man')}}{{ item.cou_man }}{{$t('lang.available_full')}}</span></div>
                                            </div>
                                            <div class="b-r-a-con">
                                                <div class="range-item">{{ item.cou_name }}</div>
                                                <div class="range-item">{{ item.cou_start_time }} {{$t('lang.zhi')}} {{ item.cou_end_time }}<em class="color-red">({{$t('lang.cou_user_receive_hove')}}{{item.cou_user_num}}{{$t('lang.zhang')}})</em>
                                                </div>
                                            </div>
                                        </div>
                                        <b class="semi-circle"></b>
                                        <div class="print-img" v-if="item.cou_is_receive == 1"></div>
                                    </div>
                                    <a href="javascript:void(0);" class="b-r-a-btn tb-lr-center" v-if="item.enable_ling > 0">{{$t('lang.brought_up')}}</a>
                                    <a href="javascript:void(0);" class="b-r-a-btn tb-lr-center"
                                       @click="handelReceive(item.cou_id)" v-else>
                                        <template v-if="item.cou_is_receive == 1 && isLogin == true">{{$t('lang.receive_hove')}}</template>
                                        <template v-else>{{$t('lang.receive')}}</template>
                                    </a>
                                </li>
                            </ul>
                            <div class="notic-coupons" v-else>{{$t('lang.no_coupon_yet')}}</div>
                        </template>
                    </div>
                </swiper-slide>
            </swiper>
        </van-popup>

        <!-- 促销 -->
        <van-popup v-model="promotionShow" position="bottom" class="show-popup-bottom show-goods-promotion">
            <section class="goods-show-title padding-all">
                <h3 class="fl">{{$t('lang.promotion')}}</h3>
                <i class="iconfont icon-close fr" @click="handelClose('promotion')"></i>
            </section>
            <swiper class="goods-show-con" :options="swiperOption">
                <swiper-slide>
                    <div class="padding-all">
                        <ul class="promotion-popup-list m-b10" v-if="goods_promotion && goods_promotion.length > 0">
                            <li v-for="(item,index) in goods_promotion" :key="index" class="promotion-popup-item">
                                <template v-if="item.act_type == 0">
                                    <router-link :to="{name:'activity-detail',params:{act_id:item.act_id}}"><em class="em-sales">{{$t('lang.with_a_gift')}}</em><span>{{ item.act_name }}</span></router-link>
                                </template>
                                <template v-if="item.act_type == 1">
                                    <router-link :to="{name:'activity-detail',params:{act_id:item.act_id}}"><em class="em-sales">{{$t('lang.lijian')}}</em><span>{{ item.act_name }}</span></router-link>
                                </template>
                                <template v-if="item.act_type == 2">
                                    <router-link :to="{name:'activity-detail',params:{act_id:item.act_id}}"><em class="em-sales">{{$t('lang.discount')}}</em><span>{{ item.act_name }}</span></router-link>
                                </template>
                                <template v-if="item.act_type == 3">
                                    <router-link :to="{name:'groupbuy-detail',params:{group_buy_id:item.act_id}}"><em class="em-sales">{{$t('lang.group_buy')}}</em><span>{{ item.act_name }}</span></router-link>
                                </template>
                                <template v-if="item.act_type == 4">
                                    <router-link :to="{name:'auction-detail',params:{act_id:item.act_id}}"><em class="em-sales">{{$t('lang.auction')}}</em><span>{{ item.act_name }}</span></router-link>
                                </template>
                                <template v-if="item.act_type == 5">
                                    <a href="javascript:;" class="color-red"><em class="em-sales">{{$t('lang.full_reduction')}}</em><span>{{ item.act_name }}</span></a>
                                </template>
                            </li>
                        </ul>
                        <van-cell class="not_padding_lr" :class="{'my-top':goodsInfo.goods_promotion && goodsInfo.goods_promotion.length > 0}" is-link @click="handleFitting" v-if="goodsInfo.fittings">
                            <div class="promotion-popup-list">
                                <div class="promotion-popup-item">
                                    <em class="em-sales">{{$t('lang.combined_package')}}</em>
                                    <div class="prom_flex_box">
                                        <span>{{$t('lang.hig_discount')}}<div class="color-red" v-html="spare_price"></div>{{$t('lang.yuan')}}</span>
                                        <div class="scroll_box">
                                            <div class="scroll_box_item">
                                                <span class="scroll_box_item_good"><img :src="goodsInfo.goods_thumb" /></span>
                                                <span class="scroll_box_item_plus"></span>
                                                <template v-for="(item,index) in goodsInfo.fittings">
                                                    <template v-if="index < 2">
                                                    <span class="scroll_box_item_good"><img :src="item.goods_thumb" /></span>
                                                    <span class="scroll_box_item_plus" v-if="index != goodsInfo.fittings.length-1"></span>
                                                    </template>
                                                </template>
                                                <span class="scroll_box_item_last">...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </van-cell>
                    </div>
                </swiper-slide>
            </swiper>
        </van-popup>

        <!-- 视频 -->
        <van-popup v-model="videoShow" style="width: 80%;">
            <video-player
                class="video-player-box"
                ref="videoPlayer"
                :options="playerOptions"
                :playsinline="true">
            </video-player>
        </van-popup>

        <!-- 组合购买 -->
        <van-popup v-model="fittingShow" style="height:100%; width: 100%;" class="fitting-show">
            <van-nav-bar
              :title="$t('lang.combined_package')"
              left-arrow
              @click-left="onFittingLeft"
              class="btyes"
            />
            <van-collapse v-model="fittingNames" class="fitting-coll" accordion>
                <van-collapse-item :title="item.text" :name="item.group_id" v-for="(item,index) in fittingInfo.comboTab">
                    <div class="card-act-goods">
                        <div class="card-goods__item">
                            <van-checkbox v-model="checked" :disabled="checkDisabled" />
                            <div class="van-checkbox__con">
                                <van-card class="cart-goods-info">
                                    <template slot="thumb">
                                        <img :src="fittingInfo.goods.goods_thumb" v-if="fittingInfo.goods.goods_thumb" />
                                        <img class="img" src="../../assets/img/no_image.jpg" v-else>
                                    </template>
                                    <div class="goods-title twolist-hidden" slot="title">{{ fittingInfo.goods.goods_name }}</div>
                                    <div class="goods-price" slot="tags" v-html="goodsPriceManage"></div>
                                    <div class="goods-attr" slot="tags" v-if="fittingInfo.goods.attr.length > 0">
                                        <div class="property-box" @click="fittingAttrHandle()">
                                            <span>{{$t('lang.label_attr')}}</span>
                                            <span>{{ attr_name }}</span>
                                            <i class="iconfont icon-moreunfold fr"></i>
                                        </div>
                                    </div>
                                </van-card>
                            </div>
                        </div>
                        <van-checkbox-group v-model="fittingsCheckModel">
                        <div class="card-goods__item" v-for="(goodsItem,goodsindex) in fittingInfo.fittings" :index="goodsindex">
                            <template v-if="item.group_id == goodsItem.group_id">
                            <div @click="fittingsCheckChange(goodsItem.goods_id)">
                                <van-checkbox :name="goodsItem.goods_id" ref="checkboxes" />
                            </div>
                            <div class="van-checkbox__con">
                                <van-card class="cart-goods-info">
                                    <template slot="thumb">
                                        <img :src="goodsItem.goods_thumb" v-if="goodsItem.goods_thumb" />
                                        <img class="img" src="../../assets/img/no_image.jpg" v-else>
                                        <em class="activity-tag"><img src="../../assets/img/parts-icon.png" /></em>
                                    </template>
                                    <div class="goods-title twolist-hidden" slot="title">{{ goodsItem.goods_name }}</div>
                                    <div class="goods-price" slot="tags" v-html="goodsItem.fittings_price"></div>
                                    <div class="goods-attr" slot="tags" v-if="goodsItem.attr && goodsItem.attr.length > 0">
                                        <div class="property-box" @click="fittingAttrHandle(goodsItem.goods_id,goodsindex)">
                                            <span>{{$t('lang.label_attr')}}</span>
                                            <span>{{ goodsItem.attr_name }}</span>
                                            <i class="iconfont icon-moreunfold fr"></i>
                                        </div>
                                    </div>
                                </van-card>
                            </div>
                            </template>
                        </div>
                        </van-checkbox-group>
                    </div>
                </van-collapse-item>
            </van-collapse>
            <div class="goods-suit-btn">
                <div class="gns_item">
                    <em class="color-red">({{$t('lang.gb_limited')}} {{ fittingInfo.goods.group_number }} {{$t('lang.cover')}})</em>
                    <span>{{$t('lang.label_buy')}}</span>
                    <div class="stepper">
                        <van-stepper
                            v-model="fittingNum"
                            integer
                            :min="fittingPriceData.limit_number"
                            :max="fittingInfo.goods.group_number"
                        />
                    </div>
                    <span>{{$t('lang.cover')}}</span>
                </div>
            </div>
            <div class="cart-submit van-submit-bar cart-submit-not-bot">
              <div class="van-submit-bar__bar">
                <div class="van-submit-bar__text">
                  <p><span>{{$t('lang.label_package_price')}}</span><span class="van-submit-bar__price" v-html="fittings_minMax"></span></p>
                  <p class="van-submit-bar__sub">{{$t('lang.sheng')}}：<span v-html="save_minMaxPrice"></span></p>
                </div>
                <van-button type="danger" @click="fittingsAddCart">{{$t('lang.add_cart')}}</van-button>
              </div>
            </div>
        </van-popup>

        <!-- 组合购买 配件 属性弹窗 -->
        <van-popup v-model="fittingShowBase" position="bottom" class="attr-goods-box">
            <div class="attr-goods-header">
                <div class="attr-img">
                    <img :src="fittingImg" class="img" v-if="fittingImg" />
                    <img src="../../assets/img/no_image.jpg" class="img" v-else/>
                </div>
                <div class="attr-info">
                    <div class="attr-price" v-html="fittingPrice"></div>
                    <div class="attr-stock">{{$t('lang.label_stock')}}{{ fittingAttrNumber }}</div>
                    <div class="attr-other">{{$t('lang.label_selected')}}{{ fittingAttrName }}</div>
                </div>
                <i class="iconfont icon-close" @click="closeFitting"></i>
            </div>
            <div class="attr-goods-content" v-if="fittingAttr != ''">
                <van-radio-group class="sku-item" v-model="fittingAttrInit[index]" v-for="(item,index) in fittingAttr" :key="index" @change="fittingLoad(0)">
                    <div class="sku-tit">{{ item.attr_name }}</div>
                    <div class="sku-list">
                        <template v-for="(option,listIndex) in item.attr_key">
                            <van-radio class="option" :class="{'active':fittingAttrInit[index] == option.goods_attr_id}" :name="option.goods_attr_id">{{ option.attr_value }}
                            </van-radio>
                        </template>
                    </div>
                </van-radio-group>
            </div>
            <div class="attr-goods-number dis-box"></div>
            <div class="van-sku-actions">
                <van-button type="primary" class="van-button--bottom-action" @click="closeFitting">{{$t('lang.confirm')}}</van-button>
            </div>
        </van-popup>

        <!--分享-->
        <div class="shopping-prompt ts-2" :class="{'active':drpShareActive}" @click="shoppingPromptHandle"><img src="../../assets/img/fengxiang.png" /></div>

        <!--分享海报-->
        <van-popup v-model="shareImgShow" class="shareImg" overlay-class="shareImg-overlay">
            <img :src="shareImg" v-if="shareImg" class="img" />
            <span v-else>{{$t('lang.error_generating_image')}}</span>
        </van-popup>

        <!--回到顶部-->
        <ec-filter-top :scrollState="scrollState" outerClass="true"></ec-filter-top>

        <!--初始化loading-->
        <DscLoading :dscLoading="dscLoading"></DscLoading>
    </div>
</template>

<script>
	import Vue from 'vue'
    import { mapState } from 'vuex'

    import {
        NavBar,
        Swipe,
        SwipeItem,
        Cell,
        CellGroup,
        Tab,
        Tabs,
        GoodsAction,
        GoodsActionBigBtn,
        GoodsActionMiniBtn,
        Sku,
        Popup,
        Stepper,
        Button,
        Loading,
        RadioGroup,
        Radio,
        Toast,
        Dialog,
        Icon,
        Collapse,
        CollapseItem,
        Checkbox,
        CheckboxGroup,
        Card,
		ImagePreview,
		Waterfall
    } from 'vant'

    import {
        swiper,
        swiperSlide
    } from 'vue-awesome-swiper'

    import ShopHeader from '@/components/shop/ShopHeader'
    import NotCont from '@/components/NotCont'
    import CommonNav from '@/components/CommonNav'
    import CountDown from '@/components/CountDown'
    import ProductList from '@/components/ProductList'
    import DscLoading from '@/components/DscLoading'
    import formProcessing from '@/mixins/form-processing'
    import isApp from '@/mixins/is-app'
    import arrRemove from '@/mixins/arr-remove'
	import EcFilterTop from '@/components/visualization/element/FilterTop'
	import uParse from '@/components/u-parse/u-parse.vue'

    import 'video.js/dist/video-js.css'
    import {videoPlayer} from 'vue-video-player'
	import qs from 'qs'
	
	Vue.use(ImagePreview);

    export default {
        mixins: [formProcessing],
		directives: {
		  WaterfallLower: Waterfall('lower')
		},
        data() {
            return {
                navActive: 0,
                active: 0,
                tabs: [this.$t('lang.goods_detail_info'), this.$t('lang.specification_parameter')],
                showBase: false,
                num: 1,
                swiperOption: {
                    direction: 'vertical',
                    slidesPerView: 'auto',
                    freeMode: true
                },
                couponShow: false,
				currency:'₸',
                promotionShow:false,
                scrollState: false,
                conpouLoading: true,
                goods_id: this.$route.params.id,
                shopScore: true,
                index: 0,
                addCartClass: false,
                storeBtn: false,
                videoShow: false,
                drpShareActive:false,
                playerOptions: {
                    autoplay: false, // 如果true,浏览器准备好时开始回放。
                    muted: true, // 默认情况下将会消除任何音频。
                    loop: true, // 导致视频一结束就重新开始。
                    language: 'zh-CN',
                    fluid: true, // 当true时，Video.js player将拥有流体大小。换句话说，它将按比例缩放以适应其容器。
                    playbackRates: [0.7, 1.0, 1.5, 2.0], // 播放速度
                    sources: [{
                        type: "video/mp4",
                        src: null
                    }],
                },
                shareImg:'',
                shareImgShow:false,
                routerName:'goods',
                productOuter:true,
                fittingShow:false,
                fittingNames:'',
                checked:true,
                checkDisabled:true,
                fittingsCheckModel:[],
                fittingNum:1,
                fittings_minMax:0,
                save_minMaxPrice:0,
                zFittingAttr:false,
                fittingShowBase:false,
                fitting_index:'',
                fittingAttr:[],
                fittingAttrInit:[],
                fittingAttrId:'',
                fittingAttrNumber:'',
                fittingGoodsAttrId:'',
                fittingAttrName:'',
                fittingPrice:0,
                fittingImg:'',
                volumePriceShow:false,
                rankPriceShow:false,
                dscLoading:true,
                isPrice:0,
                regionGoodsPirceFormated:'',
                regionGoodsNumberFormated:'',
                activityRouterPath:'',
                platform: this.$route.query.platform ? this.$route.query.platform : '',
                disabled: false,
                loading: false,
                page: 1,
                size: 30,
                shipping_region:'',
				currentSwipeItem: 0,
				reCurSwipeItem: 0,
				friendDis: [],
				goodsGuessList: [],
				commentTotal: {},
				showShortcut: false,
				showDiscount: false,
				showActivity: false,
				showAddredd: false,
				isViewMore: false,
				currenTab: 0,
				search_keywords: [],
				isBack: false,
				addressRadio: '',
				swipeItemHeight: {
					minHeight: 0
				},
				goodsComment: [],
				recommentTabs: [],
				shortcutData: [
					{
						name: this.$t('lang.home'),
						ico: 'iconfont icon-zhuye',
						path: 'home'
					},
					{
						name: this.$t('lang.search'),
						ico: 'iconfont icon-search',
						path: 'search'
					},
					{
						name: this.$t('lang.category'),
						ico: 'iconfont icon-menu',
						path: 'catalog'
					},
					{
						name: this.$t('lang.cart'),
						ico: 'iconfont icon-cart',
						path: 'cart'
					},
					{
						name: this.$t('lang.personal_center'),
						ico: 'iconfont icon-gerenzhongxin',
						path: 'user'
					}
				],
				addType:'',
				initialSwipe: 0,
				stockout:true,
				imgScrollOption:{
					notNextTick: true,
			        watchSlidesProgress: true,
			        watchSlidesVisibility: true,
			        slidesPerView: 'auto',
			        lazyLoading: true,
				},
				attrColorIndex:0,
				guessList: [],
				isOver: false,
				wDisabled: false,
				showServerPop: false
            }
        },
        components: {
            [NavBar.name]: NavBar,
            [Swipe.name]: Swipe,
            [SwipeItem.name]: SwipeItem,
            [Cell.name]: Cell,
            [CellGroup.name]: CellGroup,
            [Tab.name]: Tab,
            [Tabs.name]: Tabs,
            [GoodsAction.name]: GoodsAction,
            [GoodsActionBigBtn.name]: GoodsActionBigBtn,
            [GoodsActionMiniBtn.name]: GoodsActionMiniBtn,
            [Sku.name]: Sku,
            [Popup.name]: Popup,
            [Stepper.name]: Stepper,
            [Button.name]: Button,
            [Loading.name]: Loading,
            [RadioGroup.name]: RadioGroup,
            [Radio.name]: Radio,
            [Toast.name]: Toast,
            [Dialog.name]: Dialog,
            [Icon.name]: Icon,
            [Collapse.name]: Collapse,
            [CollapseItem.name]: CollapseItem,
            [Card.name]: Card,
            [Checkbox.name]: Checkbox,
            [CheckboxGroup.name]: CheckboxGroup,
            ShopHeader,
            swiper,
            swiperSlide,
            CountDown,
            CommonNav,
            videoPlayer,
            ProductList,
            NotCont,
            DscLoading,
			EcFilterTop,
			uParse
        },
        async created() {
            let that = this
            setTimeout(() => {
                uni.getEnv(function(res){
                    if(res.plus || res.miniprogram){
                        uni.redirectTo({
                            url:'../../pagesC/goodsDetail/goodsDetail?id=' + that.goods_id
                        })
                    }
                })
            },100)
			
			let configData = JSON.parse(sessionStorage.getItem('configData'));
			if(configData){
			  this.currency = configData.currency_format.replace('%s', '');
			  this.search_keywords = configData.search_keywords.split(',');
			}

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

            this.loadGoodsInfo(this.goods_id);
			
			// 商品评论
			this.getCommentList();

			// 网友讨论
			this.getFriendDiscuss();

			//用户没有登录地区选择不显示收货地址
			if(this.isLogin){
				this.$store.dispatch('userAddress');
			}else{
				this.regionShow = true;
			}
        },
        mounted() {
            this.$nextTick(() => {
                window.addEventListener('scroll', this.onScroll);
            });

            //3秒关闭提示
            setTimeout(()=>{
	            if((!(this.regionGoodsNumberFormated ? this.regionGoodsNumberFormated : this.goodsNumberManage > 0)) && (this.goodsInfo.is_drp > 0 && this.goodsInfo.is_distribution == 1)){
		        	this.stockout = false
	            }
            },3000)
        },
        computed: {
            ...mapState({
				addressList: state => state.user.addressList,
                goodsInfo: state => state.goods.goodsInfo,//商品基本信息
                goodsCouponList: state => state.goods.goodsCouponList,//优惠券信息
                shopDetail: state => state.shop.shopDetail,//店铺信息
                fittingInfo: state => state.goods.fittingInfo,//组合配件详情
                fittingPriceData: state => state.goods.fittingPriceData,//组合配件价格信息
                shipping_fee: state => state.shopping.shipping_fee //配送运费信息,
            }),
			selectAddress: {
				get() {
					return this.addressRadio
				},
				set(value) {
					this.addressRadio = value
				}
			},
            gallery_list() {
            	let arr = [];
            	let gallery_list = this.$store.state.goods.goodsInfo.gallery_list;

            	gallery_list.forEach(v=>{
            		arr.push(v.img_url);
            	});

                return arr
            },
            attr() {
                return this.$store.state.goods.goodsInfo.attr
            },
            attrColorList(){
            	let attr = this.attr.filter(item => item.is_checked === 1);
            	let imgFile = [];
            	
            	if(attr.length > 0){
            		imgFile = attr[0].attr_key.filter(item => item.img_flie !== '');
            	}
            	
            	return imgFile.length > 0 && attr[0].attr_key.length > 0 ? attr[0].attr_key : []
            },
            goodsAttrInit: {
                get() {
                    return this.$store.state.goods.goodsAttrInit ? this.$store.state.goods.goodsAttrInit : ''
                },
                set(val) {
                    this.$store.state.goods.goodsAttrInit = val
                }
            },
            // goods_attr_id:{
            //     get(){
            //         return this.goodsInfo.goods_attr_id ? this.goodsInfo.goods_attr_id : ''
            //     },
            //     set(val){
            //         this.goodsInfo.goods_attr_id = val
            //     }
            // },
            goodsAttrOper() {
                return this.$store.state.goods.goodsAttrOper
            },
            stock() {
                return this.attr != '' ? this.goodsAttrOper.stock : this.goodsInfo.goods_number
            },
            isLogin() {
                return localStorage.getItem('token') == null ? false : true
            },
            ru_id() {
                return this.$store.state.goods.goodsInfo.user_id
            },
			goodsRate() {
				return this.goodsInfo.goods_rate
			},
            number:{
                get(){
                    return this.goodsInfo.is_minimum > 0 ? this.goodsInfo.minimum : 1
                },
                set(val){
                    this.num = val
                }
            },
            is_collect_shop: {
                get() {
                    return this.shopDetail.is_collect_shop
                },
                set(val) {
                    this.shopDetail.is_collect_shop = val
                }
            },
            count_gaze: {
                get() {
                    return this.shopDetail.count_gaze
                },
                set(val) {
                    this.shopDetail.count_gaze = val
                }
            },
            shopInfo() {
                let arr = []

                arr[this.index] = {
                    shopName: this.shopDetail.shop_name,
                    logo: this.shopDetail.logo_thumb,
                    ru_id: this.shopDetail.ru_id,
                    commentdelivery: this.shopDetail.commentdelivery,
                    commentdelivery_font: this.shopDetail.commentdelivery_font,
                    commentrank: this.shopDetail.commentrank,
                    commentrank_font: this.shopDetail.commentrank_font,
                    commentserver: this.shopDetail.commentserver,
                    commentserver_font: this.shopDetail.commentserver_font,
                    count_gaze: this.count_gaze,
                    is_collect_shop: this.is_collect_shop,
                }

                return arr
            },
            shopCollectStatue(){
				return this.$store.state.user.shopCollectStatue
			},
            goodsCollectStatue() {
                return this.$store.state.user.goodsCollectStatue
            },
            freight() {
                return this.shipping_fee != null && this.shipping_fee.is_shipping > 0 ? this.shipping_fee
                .shipping_fee_formated : "<span class='color-red'>"+this.$t('lang.is_shipping_area')+"</span>"
            },
			freeShipping() {
				return this.shipping_fee != null && this.shipping_fee.free_shipping ? this.shipping_fee.free_shipping : 0;
			},
            is_on_sale() {
                return this.$store.state.goods.goodsInfo.is_on_sale == 0 ? true : false
            },
            is_alone_sale() {
                return this.$store.state.goods.goodsInfo.is_alone_sale == 0 ? true : false
            },
            collect_icon() {
                return this.is_collect == 1 ? 'like' : 'like-o'
            },
            is_collect: {
                get() {
                    return this.$store.state.goods.goodsInfo.is_collect
                },
                set(val) {
                    this.$store.state.goods.goodsInfo.is_collect = val
                }
            },
            cart_number: {
                get() {
                    return this.$store.state.goods.goodsInfo.cart_number
                },
                set(val) {
                    this.$store.state.goods.goodsInfo.cart_number = val
                }
            },
            attr_name: {
                get() {
                    return this.attr != '' ? '‘' + this.$store.state.goods.goodsInfo.attr_name + '’' + this.num + this.goodsInfo.goods_unit  : this.num + '' + this.goodsInfo.goods_unit
                },
                set(val) {
                    this.$store.state.goods.goodsInfo.attr_name = val
                }
            },
            player() {
                return this.$refs.videoPlayer.player
            },
            regionGoodsPirce:{
                get(){
                    return this.shipping_fee.goods ? this.shipping_fee.goods.goods_price : 0
                },
                set(val){
                    if(this.shipping_fee.goods){
                        this.shipping_fee.goods.goods_price = val
                    }
                }
            },
            regionGoodsNumber:{
                 get(){
                    return this.shipping_fee.goods ? this.shipping_fee.goods.stock : 0
                 },
                 set(val){
                    if(this.shipping_fee.goods){
                        this.regionGoodsNumberFormated = val
                    }
                 }
            },
            //价格随属性变化
            goodsPriceManage(){
                let price = ''
                if(this.goodsAttrOper.goods_price){
                    price = this.goodsAttrOper.goods_price
                }else{
                    if(this.goodsInfo.promote_start_date > 0 && this.goodsInfo.promote_end_date > 0){
                        price = this.goodsInfo.promote_price
                    }else{
                        price = this.goodsInfo.shop_price_original
                    }
                }
                return price
            },
            //价格随属性变化 市场价
            goodsMarketPriceManage(){
                let price = ''
                if(this.goodsAttrOper.market_price){
                    price = this.goodsAttrOper.market_price
                }else{
                    price = this.goodsInfo.market_price
                }
                
                return price
            },
            //库存随属性变化
            goodsNumberManage(){
                let number = 0

                if(this.goodsAttrOper.stock){
                    number = this.goodsAttrOper.stock
                }else{
                    number = this.goodsInfo.goods_number
                }

                return number
            },
            goods_promotion(){
                let goods_promotion = this.goodsInfo.goods_promotion
                let act_name = ''
                let act_type = ''
                let act_id = ''
                let arr = []
                let obj = {}

                if(goods_promotion && goods_promotion.length > 0){
                    goods_promotion.forEach(v=>{
                        if(v.type == 'favourable'){//优惠活动
                            act_type = v.act_type
                            act_name = v.act_name
                        }else if(v.type == 'group_buy'){
                            act_type = 3
                            act_name = this.$t('lang.group_buy')
                        }else if(v.type == 'auction'){
                            act_type = 4
                            act_name = this.$t('lang.auction')
                        }

                        obj = {
                            act_type:act_type,
                            act_name:act_name,
                            act_id:v.act_id
                        }
                        arr.push(obj)
                    })
                }

                if(this.goodsInfo.consumption && this.goodsInfo.consumption.length > 0){
                    let consumption = ''
                    let str = ''
                    this.goodsInfo.consumption.forEach(v=>{
                        str = this.$t('lang.man') + v.cfull + this.$t('lang.minus') + v.creduce
                        consumption += str + ','
                    })

                    obj = {
                        act_type:5,
                        act_name:consumption
                    }

                    arr.push(obj)
                }

                return arr
            },
            spare_price(){
                return this.goodsInfo.fittings[0].spare_price
            },
            navTabs(){
                let arr = []
 
                if(this.goodsInfo.shop_can_comment > 0){
                    arr = [this.$t('lang.goods'), this.$t('lang.comment_alt'), this.$t('lang.detail'), this.$t('lang.recommend')];
                }else{
                    arr = [this.$t('lang.goods'), this.$t('lang.detail'), this.$t('lang.recommend')];
                }

                return arr
            },
            provinceName(){
                let city_name = this.goodsInfo.basic_info.city_name;
                let province_name = this.goodsInfo.basic_info.province_name;

                return city_name == province_name ? city_name : city_name + province_name
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
				if (this.goodsInfo.consumption && this.goodsInfo.consumption.length > 0) {
				    let consumption = ''
				    let str = ''
				    this.goodsInfo.consumption.forEach(v=>{
				        str = this.$t('lang.man') + v.cfull + this.$t('lang.minus') + v.creduce
				        consumption += str + ','
				    });
					arr.push({
						acttype: 1,
						label: this.$t('lang.full_reduction'),
						value: consumption.substr(0, consumption.length - 1)
					})
				};
				if (this.goodsInfo.goods_promotion && this.goodsInfo.goods_promotion.length > 0) {
					this.goodsInfo.goods_promotion.forEach(item => {
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
				if (this.goodsInfo.give_integral > 0 && this.goodsInfo.show_give_integral == 1) {
					arr.push({
						acttype: 4,
						label: this.$t('lang.label_give_integral_2'),
						value: `${this.$t('lang.buy_the_product_as_a_gift')}${this.goodsInfo.give_integral}${this.$t('lang.integral')}`
					})
				};
				if (this.goodsInfo.integral > 0) {
					arr.push({
						acttype: 5,
						label: this.$t('lang.points_deduction'),
						value: `${this.$t('lang.you_can_use_up_to')}${this.goodsInfo.integral}${this.$t('lang.integral')}`
					})
				};
				if (this.goodsInfo.volume_price_list && this.goodsInfo.volume_price_list.length > 0) {
					let volumePrice = '';
					this.goodsInfo.volume_price_list.forEach(item => {
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
			activityData: function () {
				let arr = [];
				if (this.goodsInfo.goods_promotion && this.goodsInfo.goods_promotion.length > 0) {
					this.goodsInfo.goods_promotion.forEach(item => {
						if (item.type == 'group_buy') {
							item.label = this.$t('lang.group_buy');
							item.value = this.$t('lang.in_group_buying_activities');
							item.acttype = 0;
							item.path = {name:'groupbuy-detail',params:{group_buy_id:item.act_id}};
							arr.push(item)
						} else if (item.type == 'auction') {
							item.label = this.$t('lang.auction');
							item.value = this.$t('lang.the_goods_are_being_auctioned');
							item.acttype = 1;
							item.path = {name:'auction-detail',params:{act_id:item.act_id}};
							arr.push(item);
						} else if (item.type == 'team') {
							item.label = this.$t('lang.team');
							item.value = this.$t('lang.participating_in_group_activities');
							item.acttype = 3;
							item.path = {name:'team-detail', query: {
								goods_id: item.goods_id,
								team_id: item.team_id
							}};
							arr.push(item);
						} else if (item.type == 'bargain') {
							item.label = this.$t('lang.bargain');
							item.value = this.$t('lang.participating_in_bargaining_activities');
							item.acttype = 4;
							item.path = {name:'bargain-detail',params:{id:item.bargain_id}};
							arr.push(item);
						}
					})
				};

				if (this.goodsInfo.fittings) {
					arr.push({
						acttype: 2,
						label: this.$t('lang.discount_package'),
						value: `${this.$t('lang.the_goods_are_in_common')}${this.tabList}${this.$t('lang.special_package')}`,
						path: {name: 'goodsSetmeal', params: {id: this.goods_id}}
					})
				}

				return arr;
			},
			tabList(){
				let i = 0, a = 0,length = 0,arr=[];
				this.goodsInfo.fittings.forEach(v=>{
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
				if (this.goodsInfo.attr_parameter) {
					if (this.goodsInfo.show_brand == 1) num += 1;
					if (this.goodsInfo.show_goodsweight == 1) num += 1;
					if (this.goodsInfo.show_addtime == 1) num += 1;
					num += this.goodsInfo.attr_parameter.length;
				}
				return num > 5 ? true : false;
			},
			storeRate: function () {
				const { commentrank, commentdelivery, commentserver } = this.shopDetail;
				let num = 0;
				if (commentrank) num += parseInt(commentrank.slice(0, 1)) || 0;
				if (commentdelivery) num += parseInt(commentdelivery.slice(0, 1)) || 0;
				if (commentserver) num += parseInt(commentserver.slice(0, 1)) || 0;
				
				num = num / 3;
				return Math.round(num);
			}
        },
        methods: {
			onSwipeChange(i) {
				this.currentSwipeItem = i
			},
			onChangeRecomment(i) {
				this.reCurSwipeItem = i;
			},
			changeTab(i) {
				if (this.currenTab == i) return;
				this.currenTab = i;
				this.reCurSwipeItem = 0;
				this.$nextTick(function() {
					this.getELHeight();
				})
			},
			showShortcutHandle() {
				this.showShortcut = !this.showShortcut;
			},
			showRegionShow(){
				this.showAddredd = true;
				this.regionShow = true;
			},
			addressClose() {
				this.showAddredd = false;
				this.regionShow = false;
			},
			// 点击跳转
			goLink(url) {
				this.$router.push(url)
			},
			changeAddressRadio(res) {
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
				this.showAddredd = false;
			},
			//选中其他地址
			getRegionShow(){
				//关闭弹窗
				this.showAddredd = false;
				this.regionShow = false;

				//还原初始
				this.isBack = false;
				this.regionShow = false;
				this.$refs.swiperAddress.swipeTo(0);

				//收货地址清空
				this.selectAddress = ''
			},
			collectHandle(val){
				if(this.isLogin){
					this.$store.dispatch('setCollectShop',{
						ru_id:val,
						status:this.is_collect_shop
					})
				}else{
					let msg = this.$t('lang.fill_in_user_collect_goods')
					this.storeNotLogin(msg)
				}
			},
			storeNotLogin(msg){
				Dialog.confirm({
					message:msg,
					className:'text-center'
				}).then(()=>{
					this.$router.push({
						path: '/login',
						query:{ redirect: {name:'shopDetail',params:{ id:this.ru_id }}}
					})
				}).catch(()=>{
			
				})
			},
			selectAddresss() {
				if (this.isBack) {
					this.isBack = false;
					this.regionShow = false;
					this.$refs.swiperAddress.swipeTo(0)
				} else {
					this.isBack = true;
					this.regionShow = true;
					this.$refs.swiperAddress.swipeTo(1)
				}
			},
			detailLink(item) {
				if(item.get_presale_activity){
					this.$router.push({
				        name:'presale-detail',
				        params:{
				            act_id: item.get_presale_activity.act_id
				        }
				    })
				}else{
					this.$router.push({
				        name:'goods',
				        params:{
				            id: item.goods_id
				        }
				    })
				}
			},
			// 领取优惠券
			getCoupon(res) {
				this.handelReceive(res.cou_id)
			},
			// 获取商品评论
			async getCommentList() {
				const { data } = await this.$http.post(`${window.ROOT_URL}api/comment/title`,qs.stringify({
					goods_id: this.goods_id
				}));
				
				if (data.status == 'success') {
					this.commentTotal = {
						total: data.data.all || 0,
						good: parseInt(data.data.good / data.data.all * 100) + '%'
					};
				};
				const { data: res } = await this.$store.dispatch('getGoodsCommentById', {
					goods_id: this.goods_id,
					rank: 'all',
					page: 1,
					size: 2
				});
				const list = arrRemove.trimSpace(res);
				if (Array.isArray(list)) this.goodsComment = list;
				
			},
			// 获取网友讨论圈
			async getFriendDiscuss() {
				const { data, status } = await this.$store.dispatch('setDiscoverCommentList',{
					goods_id: this.goods_id,
					dis_type: 'all',
					page: 1,
					size: 2,
					id: 0
				});
				
				if (status == 'success') this.friendDis = data;
				
			},
			// 获取本店推荐商品
			async getShopGoodsList() {
				let list = [];
				const { ru_id, basic_info, get_seller_shop_info } = this.goodsInfo;
				if(ru_id > 0){
					const {data: { data, status }} = await this.$http.post(`${window.ROOT_URL}/api/shop/shopgoodslist`, qs.stringify({
						store_best: 1,
						store_id: ru_id,
						page: 1,
						size: 30
					}));

					list = data;
				}else{
					const { data : { data, status }} = await this.$http.post(`${window.ROOT_URL}/api/catalog/goodslist`, qs.stringify({
						type: true,
						intro:'best',
					    page:1,
					    size:30
					}));

					list = data
				}

				// 猜你喜欢模块接口换成关联商品
				//this.getGoodsGuessList(list);
				this.getRelatedGoodsList(list);
			},
			async getGoodsGuessList() {
				let page = this.guessList.length / 10;
				
				page = Math.ceil(page) + 1;
				const {data} = await this.$store.dispatch('getGoodsGuessList',{
				    page:page,
				    size:10
				});

				if (Array.isArray(data)) {
					this.guessList = [...this.guessList, ...data];
				}
				if (data.length < 10) this.isOver = true
			},
			async getRelatedGoodsList(list = []) {
				const { data : {data} } = await this.$http.post(`${window.ROOT_URL}api/goods/linkgoods`,qs.stringify({
					goods_id: this.goods_id
				}));
				
				if (Array.isArray(data)) {
					const list1 = list || [];
					const list2 = data || [];
					//初始化
					this.recommentTabs = [];

					if (list1.length > 0) {
						this.recommentTabs.push({
							title: this.$t('lang.recommended_by_our_shop'),
							id: 1
						});
						this.goodsGuessList.push(list1);
					};
					
					if (list2.length > 0) {
						this.recommentTabs.push({
							title: this.$t('lang.guess_love'),
							id: 2
						});
						this.goodsGuessList.push(list2);
					};
					
					this.$nextTick(function() {
						this.getELHeight();
					})
				}
			},
			getELHeight() {
				this.swipeItemHeight = { minHeight: 0 };
				new Promise((resolve, reject) => {
					const section = document.querySelector('.glist');
					if (section && section.offsetHeight > 0) {
						this.swipeItemHeight = { minHeight: `${section.offsetHeight}px` };
					} else {
						setTimeout(() => {
							this.getELHeight()
						}, 10)
					}
				})
			},
			// 图片预览 
			previewImgs(i = 0, imgs) {
				let arr = []
				if (imgs){
					if(typeof imgs == 'string') arr.push(imgs);
					function handler(e){ e.preventDefault(); }
					document.body.addEventListener('touchmove',handler,{passive:false});
					ImagePreview({
					  images: arr.length > 0 ? arr : imgs,
					  startPosition: i,
					  onClose(){
					  	document.body.removeEventListener('touchmove',handler,{passive:false});
					  }
					});
				}
			},
			// 快捷导航
			routerLink(val){
				let that = this
				if(val == 'home' || val == 'catalog' || val == 'search' || val == 'user'){
			        setTimeout(() => {
			            uni.getEnv(function(res){
			                if(res.plus || res.miniprogram){
			                	if(val == 'home'){
			                        uni.reLaunch({  
			                            url: '../../pages/index/index'
			                        })
			                    }else if(val == 'catalog'){
			                        uni.reLaunch({  
			                            url: '../../pages/category/category'
			                        })
			                    }else if(val == 'search'){
			                        uni.reLaunch({  
			                            url: '../../pages/search/search'
			                        })
			                    }else if(val == 'user'){
			                        uni.reLaunch({  
			                            url: '../../pages/user/user'
			                        })
			                    }
			                }else{
			                	if(val == 'search'){
			                		that.$router.push({
			                            name:'search'
			                        })
			                	}else{
			                    	that.$router.push({
			                            name:val
			                        })
			                    }
			                }
			            })
			        },100)
			    }else{
			        that.$router.push({
			            name:val
			        })
			    }
			},
            async loadGoodsInfo(goods_id){
                let parent_id = this.$route.query.parent_id ? this.$route.query.parent_id : null
                await this.$store.dispatch('setGoodsInfo', {
                    goods_id: goods_id,
                    warehouse_id: 0,
                    area_id: 0,
                    is_delete: 0,
                    is_on_sale: 1,
                    parent_id:parent_id
                })
            },
            onAddCartClicked(type) {
                let newAttr = []
                this.addCartClass = false

                if(type == 10){
                    this.loading = true
                    this.disabled = true
                }

                if (this.attr.length > 0) {
                    newAttr = this.goodsAttrInit
                }

                this.$store.dispatch('setAddCart', {
                    goods_id: this.goods_id,
                    num: this.num,
                    spec: newAttr,
                    warehouse_id:'0',
                    area_id:'0',
                    parent_id:'0',
                    rec_type: type,
                    cou_id: this.goodsInfo.best_price.cou_id > 0 ? this.goodsInfo.best_price.cou_id : 0
                }).then(res => {
                	let status = res.uc_id >= 0 ? res.status : res
                    if (status == true) {
                        if (type == 10) {
                            if (this.isLogin) {
                                this.$router.push({
                                    name: 'checkout',
                                    query: {
                                        rec_type: type,
                                        uc_id: res.uc_id > 0 ? res.uc_id : 0
                                    }
                                })
                            }else{
                                let msg = this.$t('lang.login_user_invalid')
                                this.notLogin(msg)
                            }
                            this.loading = false
                            this.disabled = false
                        } else {
                            this.addCartClass = true
                            Toast.success({
                                duration: 1000,
                                message: this.$t('lang.added_to_cart')
                            })
                            this.cart_number = parseInt(this.cart_number) + this.num
                            this.closeSku()
                        }
                    } else {
                        Toast(res.msg);
						
						this.loading = false
						this.disabled = false
                    }
                })
            },
            onBuyClicked() {
                this.onAddCartClicked(10)
            },
            skuLink() {
                this.showBase = true;
				this.storeBtn = false;

				this.addType = '';
                this.changeAttr();
            },
            onSku(e) {
            	//加入购物车 0 or 立即购买 1
            	this.addType = e == 0 ? 1 : 2;

                if (this.attr.length > 0) {
                    this.showBase = true
                    this.changeAttr()
                } else {
                    if(this.addType == 2){
                        this.onAddCartClicked(10)
                    }else{
                        this.onAddCartClicked(0)
                    }
                }
            },
            handelClose(val) {
                if(val == 'coupon'){
                    this.couponShow = false
                }else if(val == 'promotion'){
                    this.promotionShow = false
                }
            },
            onClickLeft() {
                this.$router.go(-1)
            },
            closeSku() {
                this.showBase = false
                this.storeBtn = false
            },
            changeAttr() {
                this.$store.dispatch('setGoodsAttrOper', {
                    goods_id: this.goods_id,
                    num: this.num,
                    attr_id: this.goodsAttrInit
                })
            },
            //领取优惠券
            handleCoupon() {
                this.couponShow = true

                this.$store.dispatch('setGoodsCouponList', {
                    goods_id: this.goods_id,
                    ru_id: this.goodsInfo.user_id
                }).then(() => {
                    this.conpouLoading = false
                })
            },
            //阶梯价格
            handleVolumePrice(){
                this.volumePriceShow = true
            },
            //等级价格
            handleRankPrice(){
                this.rankPriceShow = true
            },
            //查看促销活动
            handlePromotion(){
                this.promotionShow = true
            },
            handelReceive(val) {
                this.$store.dispatch('setGoodsCouponReceive', {
                    cou_id: val
                }).then(({data:data}) => {
                    Toast({
                        message:data.msg,
                        duration:1000,
                    })

                    this.$store.dispatch('setGoodsCouponList', {
                        goods_id: this.goods_id,
                        ru_id: this.goodsInfo.user_id
                    })
                })
            },
            shippingFee(val,id) {
                this.$store.dispatch('setShippingFee', {
                    goods_id: this.goods_id,
                    position: val,
                    goods_attr_id: id,
                    is_price: this.isPrice
                })
            },
            //历史记录
            historyAdd() {
                let time = (new Date()).getTime()
                let price = this.goodsInfo.shop_price_formated
                this.$store.dispatch('setHistoryAdd', {
                    id: this.goods_id,
                    name: this.goodsInfo.goods_name,
                    img: this.goodsInfo.goods_thumb,
                    price: price,
                    addtime: time,
                })
            },
            collection() {
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
            //关注店铺
            updateInfo(obj) {
                this.is_collect_shop = obj.is_collect_shop
                this.count_gaze = this.is_collect_shop == 1 ? this.count_gaze + 1 : this.count_gaze - 1
            },
            handleStore() {
                this.changeAttr()
                this.showBase = true
                this.storeBtn = true
            },
            onStoreClicked() {
                if (this.isLogin) {
                    this.showBase = false
                    this.storeBtn = false
                    this.$router.push({
                        name: 'storeGoods',
                        query: {
                            id: this.goods_id,
                            attr_id: this.goodsAttrInit,
                            num:this.num,
                            isSingle: 'goods'
                        }
                    })
                } else {
                    let msg = this.$t('lang.login_user_not')
                    this.notLogin(msg)
                }
            },
            commentHandle(){
                this.$router.push({
                    name:'goodsComment',
                    id:this.goods_id
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
            //分销商品分享
            shareHandle(){
                this.drpShareActive = this.drpShareActive == true ? false : true
            },
            //分销商品分享层关闭
            shoppingPromptHandle(){
                this.drpShareActive = false
            },
            //商品分享生成分享图片
            onGoodsShare(){
                if (this.isLogin) {
                    Toast.loading({ duration: 0, mask: true, forbidClick: true, message: this.$t('lang.loading') })
                    let price = this.goodsInfo.goods_price || this.goodsInfo.shop_price
                    this.$store.dispatch('setGoodsShare',{
                        goods_id:this.goods_id,
                        price: price,
                        share_type:this.goodsInfo.is_distribution,
                        code_url:window.location.href,
                        thumb:this.gallery_list[0] || this.goodsInfo.goods_img
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
            },
            // 开通会员权益
            onVipRegister(){
                let parent_id = this.$route.query.parent_id ? this.$route.query.parent_id : null

                this.$router.push({
                    name:'drp-register',
                    query:{
                        parent_id:parent_id
                    }
                })
            },
            // 重新购买会员权益
            onVipRepurchase(drp_shop_membership_card_id){
                let parent_id = this.$route.query.parent_id ? this.$route.query.parent_id : null

                this.$router.push({
                    name:'drp-register',
                    query:{
						apply_status: 'repeat',
                        membership_card_id:drp_shop_membership_card_id,
                        parent_id:parent_id
                    }
                })
            },
            //网友讨论圈
            onDiscover(){
                this.$router.push({
                    name:'discoverList',
                    query:{
                        id:this.goods_id
                    }
                })
            },
            //组合套餐购买
            handleFitting(){
                this.fittingShow = true
                this.$store.dispatch('setFitting',{
                    goods_id:this.goods_id
                })
            },
            fittingLoad(load){
                let group_name = 'm_goods_' + this.fittingNames
                let group_id = group_name + '_' + this.goods_id
                let goods_attr_id = ''
                let goods_id = ''

                this.fittingAttrInit.forEach(v=>{
                    goods_attr_id += v + ','
                })

                this.fittingInfo.fittings.forEach((v,i)=>{
                    if(i == this.fitting_index){
                        goods_id = v.goods_id
                    }
                })

                this.fittingGoodsAttrId = goods_attr_id.substring(0,goods_attr_id.length-1)
 
                this.$store.dispatch('setFittingPrice',{
                    id:this.goods_id,
                    goods_id:goods_id,
                    group:{
                        attr:this.fittingGoodsAttrId,
                        goods_attr:this.fittingAttrId,
                        number:1,
                        warehouse_id:0,
                        area_id:0,
                        area_city:0,
                        group_name:group_name,
                        group_id:group_id,
                        fittings_goods:this.goods_id,
                    },
                    type:1,
                    load_type:load
                })
            },
            //选择配件
            fittingsCheckChange(id){
                let that = this
                let group_name = 'm_goods_' + this.fittingNames
                let group_id = group_name + '_' + this.goods_id                
                let spec = ''
                let index = null;
                this.$refs.checkboxes.forEach((v,i)=>{
                    if(v.name === id){
                        index = i;
                    }
                })

                this.fittingInfo.fittings.forEach((v,i)=>{
                    if(v.id == id){
                        spec = v.goods_attr_id
                    }
                })
                
                if(this.$refs.checkboxes[index].checked == false){
                    this.$store.dispatch('setAddToCartCombo',{
                        goods_id:this.$refs.checkboxes[index].name,
                        number:1,
                        spec:spec,
                        parent_attr: this.goodsAttrInit,
                        warehouse_id:0,
                        area_id:0,
                        area_city:0,
                        parent:this.goods_id,
                        group_id:group_id,
                        add_group:''
                    }).then(({data})=>{
                        if(data.error == 0){
                            this.save_minMaxPrice = data.save_minMaxPrice
                            this.fittings_minMax = data.fittings_minMax
                        }else{
                            Toast(data.msg)
                        }
                    })
                }else{
                    this.$store.dispatch('setDelInCartCombo',{
                        goods_id:this.$refs.checkboxes[index].name,
                        parent:this.goods_id,
                        group_id:group_id,
                        goods_attr:this.goodsAttrInit,
                        warehouse_id:0,
                        area_id:0,
                        area_city:0,
                    }).then(({data})=>{
                        if(data.error == 0){
                            this.save_minMaxPrice = data.save_minMaxPrice
                            this.fittings_minMax = data.fittings_minMax
                        }else{
                            Toast(data.msg)
                        }
                    })
                }
            },
            //配件属性选择
            fittingAttrHandle(goods_id,fitting_index){
                let i = 0,
                    attr_id = '',
                    goods_attr_id = ''

                this.zFittingAttr = true
                if(goods_id){
                    this.fitting_index = fitting_index;
                    this.fittingsCheckModel.forEach(v=>{
                        if(v == goods_id){
                            i++
                        }
                    })
                    if(i == 0){
                        Toast(this.$t('lang.fill_in_parts'))
                        return false
                    }
                    
                    this.fittingShowBase = true
                    this.fittingAttrInit = []
                    this.fittingInfo.fittings.forEach(v=>{
                        if(v.goods_id == goods_id){
                            this.fittingAttr = v.attr
                            this.fittingPrice = v.fittings_price
                            this.fittingImg = v.goods_thumb
                            v.attr.forEach(a => {
                                a.attr_key.forEach(k=>{
                                    if(k.attr_checked == 1){
                                        this.fittingAttrInit.push(k.goods_attr_id)

                                        attr_id += k.attr_id + ','
                                        goods_attr_id += k.goods_attr_id + ','
                                        this.fittingAttrName += k.attr_value + ' '
                                    }
                                })
                            })
                        }
                    })

                    //截取去除字符串最后一个字符
                    this.fittingAttrId = attr_id.substring(0,attr_id.length-1)
                    this.fittingGoodsAttrId = goods_attr_id.substring(0,goods_attr_id.length-1)

                    this.fittingLoad(1)
                }else{
                    this.skuLink()
                    this.fittingsCheckModel = []
                }
            },
            closeFitting(){
                this.fittingShowBase = false
            },
            //配件属性确定选择
            onFittingAttr(){
                this.showBase = false
            },
            onFittingLeft(){
                this.promotionShow = false
                this.fittingShow = false
            },
            //配件加入购物车
            fittingsAddCart(){
                let group_name = 'm_goods_' + this.fittingNames
                let group_id = group_name + '_' + this.goods_id
                this.$store.dispatch('setAddToCartGroup',{
                    group_name:group_name,
                    goods_id:this.goods_id,
                    warehouse_id:0,
                    area_id:0,
                    area_city:0,
                    number:this.fittingNum
                }).then(({data})=>{
                    Toast(data.msg)
                    
                    if(data.error == 0){
                        setTimeout(()=>{
                            this.$router.push({
                                name:'cart'
                            })
                        },2000)
                    }
                })
            },
            onScroll() {
                let scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
                let total = this.onJumpIndexoffsetTop();

                if (scrollTop > 10) {
                    this.scrollState = true
                } else {
                    this.scrollState = false
                }

                //滚动位置导航栏变化
                total.forEach((item,index)=>{
                	if(scrollTop + 40 > item){
                		this.navActive = index;
                	}
                })
            },
            onJumpIndexoffsetTop(){
            	let jump = document.querySelectorAll('.d_jump')
                let total = []
            	this.navTabs.forEach((item,index)=>{
            		if(jump[index]){
            			total.push(index > 0 ? jump[index].offsetTop - 40 : jump[index].offsetTop)
            		}
            	});

            	return total
            },
            jump(index) {
                this.navActive = index

                // 用 class="d_jump" 添加锚点
                let jump = document.querySelectorAll('.d_jump')
                let total = index > 0 ? jump[index].offsetTop - 40 : jump[index].offsetTop
                let scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop
                // 平滑滚动，时长500ms，每10ms一跳，共50跳
                let step = total / 10
                if (total > scrollTop) {
                    smoothDown()
                } else {
                    let newTotal = scrollTop - total
                    step = newTotal / 10
                    smoothUp()
                }

                function smoothDown() {
                    if (scrollTop < total) {
                        scrollTop += step
                        document.body.scrollTop = scrollTop
                        document.documentElement.scrollTop = scrollTop
                        setTimeout(smoothDown, 10)
                    } else {
                        document.body.scrollTop = total
                        document.documentElement.scrollTop = total
                    }
                }

                function smoothUp() {
                    if (scrollTop > total) {
                        scrollTop -= step
                        document.body.scrollTop = scrollTop
                        document.documentElement.scrollTop = scrollTop
                        setTimeout(smoothUp, 10)
                    } else {
                        document.body.scrollTop = total
                        document.documentElement.scrollTop = total
                    }
                }
            },
            showDiscountHandel(newVal,odlVal){
            	if(newVal){
            		this.$nextTick(()=>{
            			document.addEventListener('dblclick',function(e){
            				e.preventDefault();
            			})
            		})
            	}
            },
            //属性图片切换相册图
            onSelectImg(item,index){
            	let gallery_index = this.gallery_list.findIndex(v => v == item.attr_gallery_flie);

            	this.attrColorIndex = index;

            	if(gallery_index != undefined){
					this.$refs.goods_photo.swipeTo(gallery_index);
				}

				if(item.attr_img_site){
					window.location.href = item.attr_img_site;
				}     	
            },
			// 上拉触底
			loadMore(){
				this.wDisabled = true;
			  setTimeout(() => {
				  
			    if(!this.isOver){
			      this.getGoodsGuessList()
			    }
			  },200)
			},
			goodsAloneSale(){
				this.$router.push({
					name:'list',
					params:{
						id:this.goodsInfo.cat_id
					}
				})
			}
        },
        destroyed() {
            window.removeEventListener("scroll",  this.onScroll);
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;

            this.videoShow = false;
        },
        watch: {
        	//防止双击页面放大
        	showDiscount:'showDiscountHandel',

        	//路由监听
            '$route'(to, from) {
                this.dscLoading = true
                this.goods_id = to.params.id
                this.loadGoodsInfo(to.params.id)
            },
            ru_id() {
                //店铺信息
                if (this.goodsInfo.user_id > 0) {
                    this.$store.dispatch('setShopDetail', {
                        ru_id: this.goodsInfo.user_id
                    })
                }
            },
			guessList(val, oldVal) {
				if (val.length % 10 == 0) this.wDisabled = false;
			},
            goodsInfo() {
            	//分销分享parent_id绑定关系
                let parent_id = localStorage.getItem('parent_id') ? localStorage.getItem('parent_id') : this.$route.query.parent_id ? this.$route.query.parent_id : null;
                let user_id = localStorage.getItem('user_id') ? localStorage.getItem('user_id') : null;

                if(user_id) parent_id = user_id;

                //秒杀id大于0 跳转到秒杀详情
                if(this.goodsInfo.seckill_id){
                    this.$router.replace({
                        name:'seckill-detail',
                        query:{
                            seckill_id:this.goodsInfo.seckill_id
                        }
                    })
                }else{
                    this.dscLoading = false
                }

                //浏览历史
                if (this.isLogin) this.historyAdd()

                //如果有主图视频新增
                if(this.goodsInfo.goods_video) this.playerOptions.sources[0].src = this.goodsInfo.goods_video

                //设置title
                document.title = this.goodsInfo.goods_name;

                //单独设置微信分享信息
                this.$wxShare.share({
                    title:this.goodsInfo.goods_name,
                    desc:this.goodsInfo.goods_brief,
                    link:`${window.ROOT_URL}mobile#/goods/` + this.goods_id + '?parent_id=' + parent_id,
                    imgUrl:this.goodsInfo.goods_thumb
                })

                //最小起订量
                if(this.goodsInfo.is_minimum > 0) this.num = this.goodsInfo.minimum

				// 获取优惠券
				this.$store.dispatch('setGoodsCouponList', {
				    goods_id: this.goods_id,
				    ru_id: this.goodsInfo.user_id
				}).then(() => {
				    this.conpouLoading = false
				})
				
				// 本店推荐
				this.getShopGoodsList();
				
				// 底部猜你喜欢
				// this.getGoodsGuessList();
            },
            goodsAttrOper() {
                if (this.goodsAttrOper) {
                    this.attr_name = this.goodsAttrOper.attr_name
                    this.regionGoodsPirce = 0
                }
            },
            goodsAttrInit(){
                // let str = ''

                // this.goodsAttrInit.forEach(v=>{
                //     str += v + ','
                // })

                // this.goods_attr_id  = str.substring(str.length-1,0)

                //属性
                this.changeAttr();
            },
            goodsCollectStatue() {
                this.goodsCollectStatue.forEach((v) => {
                    if (v.id == this.goods_id) {
                        this.is_collect = v.status
                    }
                })
            },
            regionSplic() {
                this.shipping_region = {
                    province_id: this.regionOptionDate.province.id,
                    city_id: this.regionOptionDate.city.id,
                    district_id: this.regionOptionDate.district.id,
                    street_id: this.regionOptionDate.street.id
                }
                
                //运费
                if(this.goodsInfo) this.shippingFee(this.shipping_region,this.goodsInfo.goods_attr_id);
            },
            shipping_fee(){
                if(this.shipping_fee.goods){
                    this.regionGoodsPirce = this.shipping_fee.goods.shop_price
                    this.regionGoodsPirceFormated = this.shipping_fee.goods.shop_price_formated
                    this.regionGoodsNumber = this.shipping_fee.goods.stock
                }
            },
            fittingInfo(){
                this.fittingNames = this.fittingInfo.comboTab[0].group_id
            },
            fittingNames(){
                this.fittingLoad(1)
            },
            fittingPriceData(){
                this.fittingAttrNumber = this.fittingPriceData.attr_number
                this.fittingAttrName = this.fittingPriceData.attr_name

                this.fittingInfo.fittings.forEach(v=>{
                    if(v.goods_id == this.fittingPriceData.goods_id){
                        v.attr_name = this.fittingPriceData.attr_name
                    }
                })

                if(this.fittingPriceData.fittings_interval){
                    this.fittingPriceData.fittings_interval.forEach(v=>{
                        if(v.groupId == this.fittingNames){
                            this.fittings_minMax = v.combo_amount
                            this.save_minMaxPrice = v.save_minMaxPrice
                        }
                    })
                }
            },
            showBase(){
                if(this.showBase == false){
                    this.storeBtn = false
                }
            },
            regionShow(){
                if (this.regionShow) {
                    this.regionLoading = true
                }
            },
            //店铺关注状态
            shopCollectStatue(){
            	this.shopCollectStatue.forEach((v)=>{
					if(v.id == this.ru_id){
						this.is_collect_shop = v.status;
						this.count_gaze = v.status ? this.count_gaze+1 : this.count_gaze-1;
					}
				})
            },
            //视频播放
            videoShow(){
            	this.$nextTick(()=>{
	        		if(this.videoShow){
	            		this.$refs.videoPlayer.player.play();
	            	}else{
	            		this.$refs.videoPlayer.player.pause();
	            	}
            	})
            }
        },
        beforeRouteEnter(to,form,next){
            next(vm=>{
                vm.activityRouterPath = form.fullPath
            })
        }
    }
</script>
<style lang="scss" scoped>
	.van-cell-noleft .new .new-title,
	.van-cell-noleft .new .new-time {
		margin: 5px 20px;
		line-height: 20px;
        font-size: 12px;
	}

	.van-cell-noleft .new .new-time {
		color: #999;
		padding-bottom: 5px;
		border-bottom: 1px solid #F0F0F0;
	}
    .goods_details_content {
    	color: #000;
        .header_right {
            display: flex;
			height: 100%;
            .ico_box {
				position: relative;
                width: 2.5rem;
				height: 100%;
                border-radius: 50%;
                transition: all .5s ease-in;
				&:nth-child(1) {
					margin-right: 1rem;
				}
				.shortcut {
					position: absolute;
					right: 0;
					top: 140%;
					display: flex;
					flex-direction: column;
					border-radius: 0.5rem;
					background-color: rgba(255,255,255, 0.95);
					&:before {
						content: '';
						position: absolute;
						right: 1.25rem;
						top: -1.2rem;
						transform: translateX(50%);
						width: 0;
						height: 0;
						line-height: 0;
						font-size: 0;
						border: 0.6rem solid transparent;
						border-bottom-color: #fff;
					} 
				}
				.shortcut_item {
					width: 14.5rem;
					height: 4.7rem;
					line-height: 4.7rem;
					padding: 0 2rem;
					font-size: 1.5rem;
					text-align: left;
					color: #000000;
					i {
						margin-right: 1rem;
						font-size: 1.5rem;
						color: #000!important;
						font-weight: 600;
					}
					&:nth-child(n + 2) {
						border-top: 0.1rem solid #F9F9F9;
					}
				}
            }
        }
		.goods-custom-indicator {
			position: absolute;
			bottom: 3rem;
			right: 0;
			min-width: 5rem;
			height: 2.5rem;
			line-height: 2.5rem;
			border-top-left-radius: 1.25rem;
			border-bottom-left-radius: 1.25rem;
			text-align: center;
			color: #fff;
			font-size: 1.4rem;
			background-color: rgba(41, 47, 54, 0.4);
			z-index: 10;
		}
		.watch_video {
			position: absolute;
			bottom: 3rem;
			left: 50%;
			height: 3rem;
			padding: 0 0.3rem;
			border-radius: 1.5rem;
			transform: translateX(-50%);
			background-color: rgba(255,255,255, 0.8);
			display: flex;
			flex-direction: row;
			justify-content: center;
			align-items: center;
			z-index: 10;
			.iconfont {
				height: 2rem;
				line-height: 2rem;
				font-size: 2rem;
				color: #FF5E4D;
			}
			span {
				margin: 0 1rem 0 0.5rem;
				font-size: 1.2rem;
			}
		}
		.activity_img_warp {
			position: relative;
			.activity_img {
				width: 100%;
			}
			.activity_left {
				position: absolute;
				top: 0;
				left: 1.5rem;
				display: flex;
				align-items: center;
				height: 100%;
			}
			.activity_right {
				position: absolute;
				top: 0;
				right: 5.4rem;
				transform: translateX(50%);
				display: flex;
				flex-direction: column;
				justify-content: center;
				align-items: center;
				height: 100%;
				.count_down_txt {
					margin-bottom: 0.5rem;
					font-size: 1rem;
					line-height: 1;
					color: #FF3616;
				}
			}
		}
		.goods_price_wrap {
			display: flex;
			align-items: baseline;
			.sold_out {
				font-size: 1.4rem;
				color: rgb(153, 153, 153);
				margin-left: 1rem;
			}
		}
		.goods_name_wrap {
			display: flex;
			align-items: flex-start;
			.drp-share {
				padding-top: 0.5rem;
				margin-left: 1rem;

				span{ font-size:1.2rem; }
			}
			.flex_1{
				.country_icon{
					width: 2rem;
					position: relative;
					top: 0.25rem;
					display: inline-block;
				}
				.span_block{
					display: block;
					
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
		.goods_info .goods_title h3 {
			max-height: none;
			font-weight: 700;
			color: #000;
			.ziying {
			    background: linear-gradient(to right, #F91F28, #FF4E2E);
			}
		}
		
		.goods-price .rate {
			padding-right: 0.5rem;
		}
		
		.goods_rate {
			margin-left: -1rem;
			margin-top: -2rem;
			overflow: hidden;
		}
		
		.goods_info .goods-price .drp-share {
			i {
				margin: 0;
				color: #333;
				font-weight: 700;
			}
			span {
				color: #333;
			}
		}
		.color_999 {
			font-size: 1.4rem;
			color: #999;
		}
		.goods_module_wrap {
			overflow: hidden;
			border-radius: 1rem;
			padding: 1.5rem 1.1rem;
			font-size: 1.4rem;
			background-color: #fff;
		}
		.goods_module_btn {
			padding: 0 2rem;
			min-width: 13rem;
			height: 3.2rem;
			line-height: 3rem;
			border-radius: 1.6rem;
			border: 0.1rem solid #F9F9F9;
			text-align: center;
		}
		.border_radius_0 {
			padding: 0;
			border-top-right-radius: 0;
			border-top-left-radius: 0;
		}
		.activity_content {
			display: flex;
			align-items: flex-start;
			&:nth-child(n + 2) {
				position: relative;
				padding-top: 1.5rem;
				margin-top: 1.5rem;
				&:before {
					content: '';
					position: absolute;
					top: 0;
					left: 4.2rem;
					width: 100%;
					height: 0.1rem;
					background-color: #F9F9F9;
				}
			}
			.title {
				flex: none;
				font-size: 1.4rem;
				font-weight: 700;
				color: #000;
			}
			.activity_main {
				flex: auto;
				overflow: hidden;
				margin: 0 0 0 1.6rem;

				.location_wrap{
					display: flex;
					flex-direction: row;
					align-items: center;
				}
			}
			.activity_ico {
				flex: none;
				.iconfont{ 
					height: 16px;
					line-height: 16px;
				}
			}
			.activity_item {
				display: flex;
				flex-wrap: wrap;
				align-items: baseline;
				&:nth-child(1) {
					align-items: center;
				}
				img {
					width: 1.65rem;
					height: 1.65rem;
					margin-right: 0.5rem;
				}
				.activity_bg {
					height: 2.5rem;
					padding: 0 0.8rem;
					text-align: center;
					border-radius: 0.2rem;
					margin: 1.2rem 0.8rem 0 0;
					color: #F44C36;
					background: #FEE9E6;
					display: flex;
					justify-content: center;
					align-items: center;					
				}
				.coupon_bg_wrap {
					overflow: hidden;
					display: flex;
					margin: 1rem 0.8rem 0 0;
				}
				.coupon_bg {
					flex: 1;
					position: relative;
					min-width: 7rem;
					height: 2.5rem;
					line-height: 2.3rem;
					padding: 0 0.5rem;
					text-align: center;
					border-radius: 0.2rem;
					border: 0.1rem solid #F44C36;
					color: #F44C36;
					&:before {
						content: '';
						position: absolute;
						top: 50%;
						left: -0.3rem;
						transform: translateY(-50%);
						width: 0.6rem;
						height: 0.6rem;
						border-radius: 50%;
						border: 0.1rem solid #F44C36;
						background-color: #fff;
						z-index: 3;
					}
					&:after {
						content: '';
						position: absolute;
						top: 50%;
						right: -0.3rem;
						transform: translateY(-50%);
						width: 0.6rem;
						height: 0.6rem;
						border-radius: 50%;
						border: 0.1rem solid #F44C36;
						background-color: #fff;
						z-index: 3;
					}
				}
			}
			.activity_item_mt .activity_bg {
				margin-top: 0;
			}
			.location_ico {
				display: inline-block;
				width: 1.1rem;
				vertical-align: text-top;
				margin-right: .5rem;
			}
			.store_arrow_ico {
				align-self: center;
			}
			.store_wrap {
				width: 100%;
				.store_ico {
					display: inline-block;
					width: 1.1rem;
					vertical-align: baseline;
				}
			}
			.address_text {
				width: 100%;
				white-space: nowrap;
				overflow: hidden;
				text-overflow: ellipsis;
				color: #999;
			}
		}
		.yunfei,
		.server {
			align-items: baseline;
		}
		.server_list {
			padding: 0 1.35rem;
			&:nth-child(n + 2) {
				margin-top: 2rem;
			}
			.server_item {
				// align-items: baseline;
				line-height: normal;
			}
			.color_999 {
				margin-top: 0.8rem;
				font-size: 1.2rem;
			}
		}
		.server_item {
			display: flex;
			align-items: center;
			max-width: 100%;
			margin-right: 2rem;
			line-height: 2;

			// &:last-child{
			// 	margin-right: 0;
			// }
			.server_ico {
				width: 1.65rem;
				height: 1.65rem;
				margin-right: 0.4rem;
			}
			
		}
		.pop_content {
			display: flex;
			flex-direction: column;
			height: 100%;
			padding: 1.35rem 0;
			font-size: 1.4rem;
			.pop_header {
				position: relative;
				margin: 0 1.35rem 1.35rem;
				font-size: 1.6rem;
				text-align: center;
				color: #282828;
				font-weight: 700;
			}
			.pop_back {
				position: absolute;
				bottom: 0;
				left: 0.5rem;
			}
			.pop_close {
				position: absolute;
				bottom: 0;
				right: 0.5rem;
			}
			.pop_main {
				flex: 1;
				padding: 0 1.35rem;
				overflow-y: auto;
				-webkit-overflow-scrolling: touch; /*这句是为了滑动更顺畅*/
			}
			.address_pop_main {
				padding: 0;
				.address_wrap {
					display: flex;
					flex-direction: column;
					justify-content: space-between;
					height: inherit;
				}
				.address_main {
					flex: auto;
					overflow-y: auto;
					-webkit-overflow-scrolling: touch; /*这句是为了滑动更顺畅*/
				}
				.address_item {
					display: flex;
					align-items: flex-start;
					.address_val {
						margin-left: 1.5rem;
					}
				}
				.select_btn {
					
					padding: 1.5rem 1.5rem 0.2rem;
					background-color: #fff;
					.van-button {
						width: 100%;
						height: 40px;
						line-height: 40px;
						border: none;
						font-size: 14px;
						font-weight: 700;
						color: #fff;
						background-color: #F91F28;
					}
				}
			}
			.label_text {
				margin-bottom: 0.2rem;
				font-size: 1.4rem;
				font-weight: 700;
				color: #000;
				&:nth-child(n + 2) {
					margin-top: 2.8rem;
				}
			}
			.pop_activity_item {
				display: flex;
				flex-wrap: wrap;
				align-items: baseline;
				.activity_bg {
					height: 1.8rem;
					line-height: 1.8rem;
					padding: 0 0.5rem;
					text-align: center;
					border-radius: 0.2rem;
					margin: 1rem 0.8rem 0 0;
					font-size: 1.2rem;
					color: #F44C36;
					background: #FEE9E6;
				}
			}
			.coupons-list {
				padding: 1rem 0 0;
				overflow-y: visible;
				background-color: transparent;
				li {
					&:last-child {
						margin: 0;
					}
					.left {
						&:before,
						&:after {
							background-color: #fff;
						}
					}
				}
			}
			.get_coupon {
				display: flex;
				align-items: baseline;
				justify-content: space-between;

				.coupon-tit{
					white-space: nowrap;
					overflow: hidden;
					text-overflow: ellipsis;
				}
				.time_text {
					flex: 1;
					white-space: nowrap;
					overflow: hidden;
					text-overflow: ellipsis;
					font-size: 1.3rem;
				}
				.coupon_btn {
					min-width: 5.8rem;
					height: 2.4rem;
					line-height: 2.4rem;
					border-radius: 1.2rem;
					text-align: center;
					padding: 0 1rem;
					color: #fff;
					background-color: #F52923;
					font-size: 1.2rem;
				}

				.u-reset-disabled{
					background: #999999;
					color: #FFFFFF;
					border-color: #999999;
				}

				.is_get_btn {
					border: 0.1rem solid #F52923;
					line-height: 2.6rem;
					color: #F52923;
					background-color: transparent;
				}
			}
		}
		.activity_tips {
			white-space: pre-line;
		    /*overflow: hidden;
		    text-overflow: ellipsis;
		    display: -webkit-box;
		    -webkit-line-clamp: 1;
		    -webkit-box-orient: vertical;*/
		}
		.activity_pop {
			background-color: #FAF9F8;
			.act_item {
				display: flex;
				justify-content: space-between;
				align-items: center;
				padding: 1.6rem;
				border-radius: 1rem;
				background-color: #fff;
				&:nth-child(n + 2) {
					margin-top: 1rem;
				}
			}
			.act_main {
				flex: auto;
				.act_label {
					display: inline-block;
					height: 1.8rem;
					line-height: 1.8rem;
					padding: 0 0.5rem;
					text-align: center;
					border-radius: 0.2rem;
					font-size: 1.2rem;
					color: #F44C36;
					background: #FEE9E6;
				}
				.act_value {
					margin-top: 0.5rem;
					.recommend_title {
						margin: 0.8rem 0 1.2rem;
					}
				}
				.activity_price {
					color: #F22E20;
				}
				.set_meal {
					display: flex;
					align-items: center;
					.icon-gengduo1 {
						margin-left: 1rem;
					}
				}
				.img_wrap {
					img {
						width: 5.5rem;
						height: 5.5rem;
						border-radius: 0.5rem;
					}
					&:nth-child(n + 2) {
						position: relative;
						padding-left: 2.8rem;
						&:before {
							position: absolute;
							top: 50%;
							left: 1.4rem;
							transform: translate(-50%, -50%);
							content: '+';
							font-size: 2rem;
						}
					}
				}
			}
		}
		.comment_wrap {
			padding: 0;
			font-size: 1.4rem;
		}
		.title_box {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 1.1rem;
			&:nth-child(n + 2) {
				border-top: 0.1rem solid #F9F9F9;
			}
			.title_text {
				position: relative;
				font-size: 1.4rem;
				font-weight: 700;
				padding-left: 1rem;
				&:before {
					position: absolute;
					top: 50%;
					left: 0;
					transform: translateY(-50%);
					content: '';
					width: 0.3rem;
					height: 1.5rem;
					background: linear-gradient(180deg, #F91F28 0%, rgba(255, 79, 46, 0.35) 100%);
				}
				.comment_count {
					margin-left: 1.5rem;
					font-weight: normal;
				}
			}
			.drgree_of_praise {
				margin-right: 0.5rem;
				color: #999;
			}
		}
		.comment-items {
			margin-bottom: 1.4rem;
			.comitem {
				padding: 1.4rem 0;
				&:nth-child(n + 2) {
					border-top: 0.1rem solid #F9F9F9;
				}
			}
			.item_header {
				display: flex;
				align-items: center;
				padding: 0 1.1rem;
				.head_l {
					flex: none;
					width: 3.4rem;
					height: 3.4rem;
					border-radius: 50%;
					background-color: #ccc;
				}
				.head_r {
					flex: 1;
					display: flex;
					flex-direction: column;
					justify-content: space-between;
					height: 3.4rem;
					margin-left: 1rem;
				}
				.com_time {
					display: flex;
					justify-content: space-between;
					.comment_time {
						color: #999;
					}
				}
				.rate_wrap {
					.icon-wujiaoxing {
						margin-right: 0.5rem;
						color: #DDD;
					}
					.color_red {
						color: #E93B3D;
					}
				}
			}
			.item_body {
				.comment_con {
					margin: 1.3rem 1.1rem 0;
					word-break:break-all;
					display:-webkit-box;
					-webkit-line-clamp:2;
					-webkit-box-orient:vertical;
					overflow:hidden;
				}
				.imgs_scroll {
					display: flex;
					margin: 1.3rem 0 0 1.1rem;
					.com_img {
						display: inline-block;
						overflow: hidden;
						width: 10.2rem;
						height: 10.2rem;
						border-radius: 0.5rem;
						&:nth-child(n + 2) {
							margin-left: 0.5rem;
						}
						img {
							width: 100%;
							height: 100%;
							object-fit: cover;
						}
					}
				}
			}
			.item_footer {
				margin: 1.1rem 1.1rem 0;
				color: #999;
			}
		}
		.friend_discuss {
			padding: 0 1.1rem 1.1rem;
			.discuss_item {
				display: flex;
				align-items: baseline;
				&:nth-child(n + 2) {
					margin-top: 1.1rem;
				}
				.dis_label {
					height: 2rem;
					line-height: 2rem;
					font-size: 1.2rem;
					padding: 0 0.5rem;
					text-align: center;
					border-radius: 0.2rem;
					margin-right: 0.8rem;
					color: #fff;
					background: #FF320D;
				}
				.dis_value {
					flex: 1;
					white-space: nowrap;
					overflow: hidden;
					text-overflow: ellipsis;
				}
				.dis_time {
					margin-left: 1rem;
					color: #999;
				}
			}
		}
		.no_dis {
			display: flex;
			flex-direction: column;
			align-items: center;
			padding-bottom: 2rem;
			.no_dis_title {
				margin: 1.5rem 0;
			}
		}
		.store_hade {
			display: flex;
			justify-content: space-between;
			align-items: center;
			font-size: 1.4rem;
			.store_logo {
				width: 4rem;
				height: 4rem;
				border-radius: 0.5rem;
				background-color: #E93B3D;
			}
			.store_name_rate {
				flex: 1;
				display: flex;
				flex-direction: column;
				justify-content: space-between;
				height: 4rem;
				margin-left: 1.1rem;
				.sto_name {
					font-weight: 700;
					color: #282828;
				}
				.sto_rate_wrap {
					display: flex;
				}
				.sto_rate {
					display: flex;
					justify-content: center;
					align-items: center;
					height: 2rem;
					line-height: 2rem;
					padding: 0 0.6rem;
					border-radius: 1rem;
					font-size: 1.2rem;
					background-color: #F6F6F6;
					.icon-wujiaoxing {
						transform: translateY(-0.2rem);
						margin-left: 0.5rem;
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
			margin: 1.5rem 0;
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
					font-size: 1.2rem;
					span:nth-child(n + 2) {
						margin-left: 0.5rem;
					}
				}
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
			.store_btn {
				display: flex;
				align-items: center;
				justify-content: center;
				padding: 0 2rem;
				// min-width: 13rem;
				height: 3.2rem;
				line-height: 3rem;
				border-radius: 1.6rem;
				border: 0.1rem solid #F9F9F9;
				text-align: center;
				&:nth-child(n + 2) {
					margin-left: 1.8rem;
				}
				&.cur{
					border-color:#F91F28;
					color: #F91F28;
					.iconfont{
						color: #fff;
					}
				}
				img {
					width: 1.4rem;
					margin-right: 0.5rem;
				}
			}
		}
		
		.recomment_wrap {
			padding: 1.1rem 0;

			.search{
				padding-bottom: 1.3rem;
			}
			.search_wrap {
				display: flex;
				align-items: center;
				justify-content: space-between;
				font-size: 1.4rem;
				padding: 0 1.1rem;
				.search_input {
					flex: auto;
					display: flex;
					align-items: center;
					justify-content: flex-start;
					height: 3.4rem;
					padding-left: 0.8rem;
					border-radius: 1.7rem;
					color: #A2A2A2;
					background-color: #F2F2F2;
					.icon-home-search {
						margin-right: 0.8rem;
					}
				}
				.search_btn {
					margin: 0 1.3rem;
				}
			}
			.top_search {
				display: flex;
				flex-wrap: wrap;
				align-items: baseline;
				padding: 1.3rem 1.1rem 0;
				.top_search_keyword {
					height: 2.6rem;
					line-height: 2.6rem;
					padding: 0 1.2rem;
					margin-right: .8rem;
					border-radius: 1.5rem;
					color: #272727;
					background-color: #F2F2F2;

					&:last-child{
						margin-right: 0;
					}
				}
			}
			.recomment {
				font-size: 1.4rem;
				padding: 0 0.5rem;
				border-top: 0.1rem solid #F9F9F9;
				.re_tabs {
					display: flex;
					justify-content: center;
					align-items: center;
					padding: 1.2rem 0;
					.re_tabs_item {
						flex: auto;
						text-align: center;
						height: 2.4rem;
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
							width: 2.6rem;
							height: 0.3rem;
							background: linear-gradient(90deg, #F91F28 0%, rgba(255, 79, 46, 0.35) 100%);
						}
					}
				}
				.goods_list_wrap {
					.van-swipe {
						padding-bottom: 0.8rem;
					}
					.recomment-custom-indicator {
						position: absolute;
						display: flex;
						left: 50%;
						bottom: 0;
						transform: translateX(-50%);
						height: 0.3rem;
						border-radius: 0.15rem;
						background-color: #F1F1F1;
						.indicator {
							width: 1.8rem;
							height: 0.3rem;
							border-radius: 0.15rem;
						}
						.active_indicator {
							background-color: #F22E20;
						}
					}
					.glist {
						display: flex;
						flex-wrap: wrap;
						align-content: flex-start;
					}
					.gitem {
						flex: none;
						width: 33.33%;
						padding: 0 0.5rem;
						// &:nth-child(3n + 1) {
						// 	margin-left: 0;
						// }
						img {
							display: block;
							width: 100%;
						}
						p {
							// flex: auto;
							height: 3.8rem;
							margin-top: 1rem;
							word-break: break-all;
							display: -webkit-box;
							-webkit-line-clamp: 2;
							-webkit-box-orient: vertical;
							overflow: hidden;
						}
					}
					.country_icon_image{
						padding-right: 0.3rem;
						position: relative;
						top: 0.2rem;
					}
				}
			}
		}
		.article_wrap {
			padding: 0;
			font-size: 1.4rem;
			.title_box {
				line-height: 1;
			}
			.acticle_border {
				border-top: 0.1rem solid #F9F9F9;
			}
			.article_item {
				display: flex;
				padding: 1.1rem;
				.article_img {
					flex: none;
					width: 7rem;
					height: 7rem;
					margin-right: 1.1rem;
					border-radius: 0.5rem;
				}
				.article_content {
					flex: auto;
					display: flex;
					flex-direction: column;
					justify-content: space-between;
					padding-bottom: 1rem;
					p {
						word-break: break-all;
						display: -webkit-box;
						-webkit-line-clamp: 2;
						-webkit-box-orient: vertical;
						overflow: hidden;
					}
					span {
						color: #999;
					}
				}
			}
		}
		.goods_detail_wrap {
			padding: 0;
			font-size: 1.4rem;
			.title_box {
				line-height: 1;
			}
			.image_text_title {
				display: flex;
				align-items: center;
				padding: 0 1.1rem 1.1rem;
				color: #272727;
				img {
					width: 1.4rem;
					height: 1.4rem;
					margin-right: 0.6rem;
				}
			}
			.goods_desc {
				padding: 0 0.5rem 1.1rem;
			}
			.goods_attr_parameter {
				padding: 0 0.5rem;
				.param_table {
					tr {
						td,
						th {
							padding: 8px 22px;
							border-color: #F9F9F9;
							color: #939393;
							&:nth-child(1) {
								width: 40%;
							}
						}
					}
				}
				.attr_wrap tr:nth-child(n + 7) {
					display: none;
				}
				.tdTitle {
					color: #585858;
				}
				.flex_box {
					padding-bottom: 1.1rem;
				}
				.view_more_btn {
					color: #939393;
					.iconfont {
						margin-left: 1rem;
					}
				}
			}
		}
		.submit_bar {
			position: fixed;
			bottom: 0;
			left: 0;
			right: 0;
			height: 5.4rem;
			display: flex;
			align-items: center;
			background-color: #fff;
			z-index: 101;
			box-sizing: content-box;
			padding: 0 .5rem;
			.submit_bar_warp{
				display: flex;
				align-items: center;
				width: 100%;
			}
			.stockout {
				position: absolute;
				top: 0;
				left: 0;
				transform: translateY(-100%);
				width: 100%;
				min-height: 3.6rem;
				line-height: 3.6rem;
				text-align: center;
				font-size: 1.4rem;
				color: #E17B32;
				background-color: #FCF9DA;
				z-index: 101;

				p{
					border-bottom: 1px solid #f6f6f6;

					&:last-child{
						border-bottom: 0;
					}
				}
			}
			.function_item {
				display: flex;
				flex-direction: column;
				justify-content: center;
				align-items: center;
				padding: 0 1rem;
				font-size: 1.2rem;
				line-height: 1;
				color: #666;

				&:first-child{
					margin-left: .5rem;
				}
				img {
					width: 1.8rem;
					height: 1.8rem;
					margin-bottom: 0.8rem;
				}
			}
			.badge_parent {
				position: relative;
				.info_num {
					position: absolute;
					top: -0.2rem;
					left: 50%;
					min-width: 1.3rem;
					height: 1.3rem;
					padding: 0 0.2rem;
					line-height: 1.1rem;
					border-radius: 0.7rem;
					border: 0.1rem solid #F91F28;
					text-align: center;
					font-size: 1rem;
					color: #F91F28;
					background-color: #fff;
				}
			}
			.van-button {
				flex: auto;
				height: 42px;
				line-height: inherit;
				margin:0 .5rem;
				border: none;
				font-size: 14px;
				font-weight: 700;
				color: #fff;
				background-color: #F91F28;
				display: flex;
				justify-content: center;
				align-items: center;
				padding: 0 .5rem;

				&.buynow{
					width: 6rem;
					box-sizing: content-box;

					.jiage{
						font-size: 12px;
					}
				}
			}
			.cur_btn {
				background-color: #FFC707;
			}
		}

		/deep/ .commom-nav{
			bottom: 15%;
		}
    }

    .van-popup /deep/ .video-player-box .vjs-big-play-button{ top: 50%; left: 50%; transform: translate(-50%,-50%); }

    .goods-attr-img-list{
    	display: flex;
    	flex-direction: row;
    	background: #fff;
    	justify-content: flex-start;
    	align-items: center;

    	.describe{
    		padding: 0 15px;
    		font-size: 12px;
    	}

    	.imgs_scroll{
    		flex: 1;
    		padding: 10px 0;
    		border-bottom: 1px solid #F9F9F9;

    		.com_img{
    			width: 50px;
    			height: 50px;
    			margin-right: 15px;
    			border: 1px solid #dfdfdf;
    			opacity: .6;

    			&.active{
    				border-color: #F91F28;
    				opacity: 1;
    			}
    		}
    	}
    }
</style>