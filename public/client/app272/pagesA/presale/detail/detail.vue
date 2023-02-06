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
								<image :src="gallerylList[0].img_url" class="imgbox" @click="onPlay" v-if="gallerylList"></image>
								<view class="playicon" @click="onPlay">
									<view class="iconfont icon-play"></view>
								</view>
							</view>
						</swiper-item>
						<block v-if="gallerylList.length > 0">
							<swiper-item v-for="(item,index) in gallerylList" :key="index">
								<image :src="item.img_url" class="imgbox"></image>
							</swiper-item>
						</block>
						<block v-else>
							<swiper-item><image :src="goodsInfo.goods_img" class="imgbox"></image></swiper-item>
						</block>
					</swiper>
				</view>
				<view class="cont-box">
					<view class="price-box">
						<view class="left">
							<currency-price 
							:price="goodsInfo.shop_price_original" 
							:size="24"
							color="#fff"
							></currency-price>
							<view class="price-row">
								<view class="tag">
									<view class="tag-icon"><text class="iconfont icon-tixingnaozhong"></text></view>
									<view class="tag-right-cont">{{$t('lang.presale')}}</view>
								</view>
								<view class="sub"><text>{{$t('lang.market_price')}}</text><view class="price-original">{{goodsInfo.market_price_formated}}</view></view>
							</view>
						</view>
						<view class="right">
							<block v-if="presaleInfo.status == 0">
								<text>{{$t('lang.activities_not_started')}}</text>
							</block>
							<block v-if="presaleInfo.status == 1">
								<text>{{$t('lang.upgrade_beford')}}:</text>
								<view class="data"><uni-countdown fontColor="#FFFFFF" borderColor="#f23157" bgrColor="#f23157" :timer="dateTime" v-if="dateTime"></uni-countdown></view>
							</block>
							<block v-else>
								<text class="end">{{$t('lang.activities_end')}}</text>
							</block>
						</view>
					</view>
					<view class="title">
						<view class="uni-flex-common uni-space-between">
							<view class="goods-name twolist-hidden flex_1"><uni-tag :text="goodsInfo.rz_shop_name" size="small" type="error" v-if="goodsInfo.rz_shop_name"></uni-tag><text>{{goodsInfo.goods_name}}</text></view>
							<view class="goods_share" @click="mpShare" v-if="presaleInfo.status != 2">
								<text class="iconfont icon-share" style="line-height: 1;"></text>
								<text class="share_txt">{{ $t('lang.share') }}</text>
							</view>
						</view>
						<view class="goods_shipai" v-if="goodsInfo.goods_shipai">{{goodsInfo.goods_shipai}}</view>
						<view class="goods_outer">
							<view class="text-left">{{$t('lang.sales_volume')}} {{ goodsInfo.sales_volume }}</view>
							<view class="text-center">{{$t('lang.current_stock')}} {{ goodsInfo.goods_number }}</view>
							<view class="text-right" v-if="goodsInfo.basic_info">
								<text v-if="goodsInfo.basic_info.province_name">{{ goodsInfo.basic_info.province_name }}</text>
								<text v-if="goodsInfo.basic_info.city_name">{{ goodsInfo.basic_info.city_name }}</text>
							</view>
						</view>
					</view>
				</view>
				<view class="uni-card uni-card-not">
					<view class="uni-list">
						<view class="uni-list-cell not-cell-bot" hover-class="uni-list-cell-hover" @click="handleRegionShow">
							<view class="uni-list-cell-navigate uni-navigate-right">
								<text class="title">{{$t('lang.label_send_to_2')}}</text>
								<view class="value">{{ regionSplic }}</view>
							</view>
						</view>
						<view class="uni-list-cell" hover-class="uni-list-cell-hover" v-if="goodsInfo.goods_extend">
							<view class="uni-list-cell-navigate">
								<text class="title">{{$t('lang.label_freight2')}}</text>
								<view class="value uni-red">{{ freight }}</view>
							</view>
						</view>
					</view>
				</view>
				<view class="uni-card uni-card-not">
					<view class="uni-list">
						<view class="uni-list-cell uni-collapse">
							<view class="uni-list-cell-navigate uni-navigate-bottom" hover-class="uni-list-cell-hover" @click="trigerCollapse()" :class="listshow ? 'uni-active' : ''">
								<text class="title">{{$t('lang.label_selected2')}}</text>
								<view class="value">{{ attr_name }}</view>
							</view>
							<view class="uni-list-cell-navigate items" :class="listshow ? 'uni-active' : ''">
								<view class="item" v-for="(item,index) in attr" :key="index">
									<text class="title">{{ item.attr_name }}</text>
									<view class="value">
										<view>
											<view class="sku-list" v-for="(option,listIndex) in item.attr_key" :key="listIndex" :class="{'active':goodsAttrInit[index] == option.goods_attr_id}" @click="attrChange(index,option.goods_attr_id)">{{ option.attr_value }}</view>
										</view>
									</view>
								</view>
								<view class="item">
									<text class="title">{{$t('lang.number')}}</text>
									<view class="value"><uni-number-box :value="number" :min="goodsInfo.is_minimum > 0 ? goodsInfo.minimum : 1" :max="stock" @change="bindChange"></uni-number-box></view>
								</view>
							</view>
						</view>
					</view>
				</view>
				<view class="goods-desc" id="tab2">
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
				<view class="btn btn-red" v-if="presaleInfo.status == 0">{{$t('lang.activities_not_started')}}</view>
				<view class="btn btn-org" v-if="presaleInfo.status == 1" @click="onCheckoutClicked()">{{$t('lang.make_appointment_now')}}</view>
				<view class="btn btn-org" v-if="presaleInfo.status == 2">{{$t('lang.activities_end')}}</view>
			</view>
		</view>
		
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
		
		<!--地区选择-->
		<dsc-region :display="regionShow" :regionOptionData="regionData" @updateDisplay="getRegionShow" @updateRegionDate="getRegionOptionDate" v-if="regionLoading"></dsc-region>
		
		<dsc-common-nav>
			<navigator url="../presale" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.presale_center')}}</text>
			</navigator>
		</dsc-common-nav>
		
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	
	import uniSegmentedControl from '@/components/uni-segmented-control.vue';
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniNumberBox from '@/components/uni-number-box.vue';
	import uniCountdown from "@/components/uni-countdown.vue";
	import uniTag from "@/components/uni-tag.vue";
	import uniPopup from '@/components/uni-popup.vue';
	import uniPopups from '@/components/uni-popup/uni-popup.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import universal from '@/common/mixins/universal.js';
	import dscRegion from '@/components/dsc-region.vue';
	import goodsNavBar from '@/components/goods-nav-bar/goods-nav-bar.vue';
	import dscCopyright from '@/components/dsc-copyright/dsc-copyright.vue';
	export default {
		mixins:[universal],
		data() {
			return {
				act_id:0,
				poster:false,
				seckillTime:'',
				current: 0,
				tabs: [this.$t('lang.goods_detail_info'), this.$t('lang.specification_parameter')],
				num:1,
				dscLoading:true,
				listshow:true,
				shipping_region:'',
				shareImgShow:false,
				mpShareImg: '',
				rgba: 'rgba(0,0,0,0)',
				navIconRgba: 'rgba(0,0,0,0.4)',
				navOpacity: 0,
				//微信小程序客服
				wxappChat:uni.getStorageSync("configData").wxapp_chat || 0
			}
		},
		components:{
			uniSegmentedControl,
			uniIcons,
			uniNumberBox,
			uniCountdown,
			uniTag,
			uniPopup,
			uniPopups,
			dscNotContent,
			dscCommonNav,
			dscRegion,
			goodsNavBar,
			dscCopyright
		},
		computed:{
			...mapState({
                presaleDetailData: state => state.ump.presaleDetailData,
				shipping_fee: state => state.shopping.shipping_fee,//配送运费信息
			}),
			goodsInfo(){
				return this.presaleDetailData.goods
			},
			goods_id(){
				return this.goodsInfo.goods_id
			},
			presaleInfo(){
				return this.presaleDetailData.presale
			},
			gallerylList() {
				return this.goodsInfo.gallery_list ? this.goodsInfo.gallery_list : []
			},
			attr(){
				return this.goodsInfo.attr
			},
			goodsAttrInit() {
				return this.$store.state.ump.goodsAttrInit
			},
			goodsAttrOper() {
				return this.$store.state.goods.goodsAttrOper
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
			//已选
			attr_name: {
				get() {
					return this.attr != '' ? this.goodsInfo.attr_name + ' ' + this.num + this.goodsInfo.goods_unit : this.num + this.goodsInfo.goods_unit
				},
				set(val) {
					this.goodsInfo.attr_name = val
				}
			},
			stock() {
				return this.attr != '' ? this.goodsAttrOper.stock : this.goodsInfo.goods_number
			},
			isTimeEnd(){
				return this.presaleInfo.gmt_end_date > this.goodsInfo.current_time ? true : false
			},
			dateTime(){
				let dataTime = this.presaleInfo.gmt_end_date
				if(dataTime != ''){
					return this.$formatDateTime(dataTime)
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
			goodsDesc(){
				let result = this.goodsInfo.goods_desc;
				const reg = /style\s*=(['\"\s]?)[^'\"]*?\1/gi;
				const regex = new RegExp('<img', 'gi');
				const regex2 = new RegExp('<p', 'gi');
			
				if(result){
					result = result.replace(reg, '');
					result = result.replace(regex, '<img style="width: 100% !important; height:auto;vertical-align:top;"');
					result = result.replace(regex2, '<p style="margin:0;padding:0;"');
				}
				return result
			},
			//运费
			freight() {
			    return this.shipping_fee != null && this.shipping_fee.is_shipping > 0 ? this.shipping_fee.shipping_fee_formated : this.$t('lang.is_shipping_area')
			},
		},
		onShareAppMessage(res){
			return {
			  title: this.goodsInfo.goods_name,
			  path: '/pagesA/presale/detail/detail?act_id=' + this.act_id
			}
		},
		onNavigationBarButtonTap(e){
			if(e.type == 'share'){
				let shareInfo = {
					href:this.$websiteUrl + 'presale/detail?act_id=' + this.act_id + '&platform=APP',
					title:this.goodsInfo.goods_name,
					summary:this.goodsInfo.goods_brief,
					imageUrl:this.goodsInfo.goods_thumb
				};
				this.shareInfo(shareInfo)
			}
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
		onLoad(e){
			let that = this
			that.act_id = e.act_id ? e.act_id : 0;
				
			//小程序扫码
			if (e.scene) {
				let scene = decodeURIComponent(e.scene);
				let fristParameter = scene.split('_')[0];
				let lastParameter = scene.split('_')[1];
				
				// 判断分割第一个参数是否有 "."
				that.act_id = fristParameter.indexOf('.') > 0 ? fristParameter.split('.')[0] : fristParameter;
				
				if(lastParameter){
					uni.setStorageSync('parent_id',lastParameter);
				}
			}
			
			
			that.loadPresaleInfo();
			
			that.regionData = that.getRegionData;
		},
		watch:{
			goodsInfo(){
				if(this.goodsInfo.is_minimum > 0){
					this.num = this.goodsInfo.minimum
				}
				
				this.dscLoading = false
			},
			regionSplic(){
				this.shipping_region = {
			        province_id: this.regionData.province.id,
			        city_id: this.regionData.city.id,
			        district_id: this.regionData.district.id,
			        street_id: this.regionData.street.id
			    }
			
			    //运费
				if(this.goodsInfo){
					this.shippingFee(this.shipping_region)
				}
			},
			goodsAttrInit(){
				this.changeAttr()
				this.storeBtn = true
				
				//运费
				this.shippingFee(this.shipping_region)
			},
			goodsAttrOper() {
			    if (this.goodsAttrOper) {
			        this.attr_name = this.goodsAttrOper.attr_name
			    }
			},
			regionShow() {
				if (this.regionShow) {
					this.regionLoading = true
				}
			},
			sharePoster() {
				if (this.sharePoster) {
					this.$refs.popupPoster.open();
				}
			}
		},
		methods: {
			loadPresaleInfo(){
				this.$store.dispatch('setPresaleDetail',{
					act_id: this.act_id
				})
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
			onClickItem(index) {
				if (this.current !== index) {
					this.current = index;
				}
			},
			async onCheckoutClicked(){
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

				this.$store.dispatch('setPresaleBuy',{
					act_id:this.act_id,
					number:this.num,
					goods_spec:newAttr,
				}).then(({ data })=>{
					if(data.error == 0){
						uni.navigateTo({
							url:'/pages/checkout/checkout?rec_type=5' + '&type_id=' + data.presale_id
						})
					}else{
						uni.showToast({
							title:data.message,
							icon:'none'
						})
					}
				})
			},
			changeAttr() {
			    this.$store.dispatch('setGoodsAttrOper', {
			        goods_id: this.goods_id,
			        num: this.num,
			        attr_id: this.goodsAttrInit
			    })
			},
			trigerCollapse(){
				this.listshow = this.listshow ? false : true
			},
			//属性切换
			attrChange(index,id){
				this.goodsAttrInit.splice(index,1,id)
			},
			bindChange(e){
				this.num = e
			},
			//运费
			shippingFee(val) {
			    this.$store.dispatch('setShippingFee', {
			        goods_id: this.goods_id,
			        position: JSON.stringify(val)
			    })
			},
			// 分享
			appShare() {
				let shareInfo = {
					href: `${this.$websiteUrl}auction/detail/${this.act_id}?parent_id=${uni.getStorageSync("user_id")}&platform=APP`,
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
					let price = this.goodsInfo.shop_price_original;
					let o = {}
					
					// #ifdef MP-WEIXIN
					o = {
						goods_id: this.goodsInfo.goods_id,
						ru_id: 0,
						price: price,
						share_type: 0,
						type: 0,
						platform: 'MP-WEIXIN',
						extension_code:'presale',
						code_url:'pagesA/presale/detail/detail',
						scene:`${this.act_id}`,
						thumb:this.gallerylList[0].img_url,
					}
					// #endif
					
					// #ifdef APP-PLUS
					o = {
						goods_id: this.goodsInfo.goods_id,
						price: price,
						share_type: 0,
						platform: 'APP',
						extension_code:'presale',
						code_url:`${this.$websiteUrl}presale/detail/${this.act_id}`,
						thumb:this.gallerylList[0].img_url,
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
.cont-box .title .goods_shipai{ color: #f92028;}

.price-box{ display: flex; flex-direction: row; justify-content: center;}
.price-box .left{ flex: 1; padding: 20upx; background: linear-gradient(90deg,#f22c8f,#f23256); color: #FFFFFF;}
.price-box .left view{ line-height: 1.2;}
.price-box .left .price-weight{ font-size: 36upx; font-weight: 700;}
.price-box .left .price-row{ display: flex; flex-direction: row; justify-content: flex-start;}
.price-box .left .price-row .tag{ display: flex; flex-direction: row; overflow: hidden;}
.price-box .left .price-row .tag-icon{ background: #FFFFFF; width: 50upx; border-radius: 20upx 0 0 20upx; display: flex; flex-direction: row; justify-content: center; align-items: center;}
.price-box .left .price-row .tag-icon .iconfont{ color: #f23157; font-size: 24upx;}
.price-box .left .price-row .tag-right-cont{ border: 1px solid #FFFFFF; padding: 0 20upx;}
.price-box .left .price-row .sub{ display: flex; flex-direction: row; margin-left: 10upx; font-size: 25upx; align-items: center;}
.price-box .left .price-row .sub .price-original{ font-size: 25upx; margin-left: 10upx;text-decoration:line-through;}
.price-box .right{ display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 0 20upx; background: #ffeae9;}
.price-box .right text{ color: #f23157;}

.goods_outer{ padding: 0; margin-top: 10upx;}

.uni-list-cell-navigate{ justify-content: flex-start; } 
.uni-list-cell-navigate .title{ min-width: 100upx; color: #999999; margin-right: 15upx;}
.uni-list-cell .iconfont{ color: #f92028; margin-right: 10upx;}
.uni-list-cell-title .uni-list-cell-navigate{ flex-direction: column; }
.uni-list-cell-title .uni-list-cell-navigate.uni-navigate-right:after{ top: 35%;}
.uni-list-cell-title .uni-list-cell-navigate .title{ width: 100%; color: #333333; font-weight: 600; font-size: 32upx; margin-bottom: 20upx;}
.uni-list-cell-title .uni-list-cell-navigate .value{ display: flex; align-items: center; color: #999999;}

/* 小程序分享  start*/
.show-popup-shareImg /deep/ .uni-popup-bottom{ height: 80%; }
/* 小程序分享 end*/
</style>
