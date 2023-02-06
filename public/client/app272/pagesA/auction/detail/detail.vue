<template>
	<view class="container fixed-bottom-padding">
		<goods-nav-bar :navIconRgba="navIconRgba" :navOpacity="navOpacity" :rgba="rgba"></goods-nav-bar>
		<scroll-view class="scrollList" scroll-y>
			<view class="goods-wrap">
				<view class="banner">
					<swiper indicator-dots="true" @change="swiperChange">
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
					<view class="price-box">
						<view class="left">
							<view class="price-weight">{{auction.formated_current_price}}</view>
							<view class="price-row">
								<view class="tag">
									<view class="tag-icon"><text class="iconfont icon-xidi-paimai"></text></view>
									<view class="tag-right-cont">{{$t('lang.auction')}}</view>
								</view>
								<view class="sub">
									<text>{{$t('lang.label_au_end_price')}}</text>
									<text v-if="auction.no_top == 0">{{auction.formated_end_price}}</text>
									<text v-else>{{$t('lang.uncapped')}}</text>
								</view>
							</view>
						</view>
						<view class="right">
							<block v-if="auction.status_no == 0">
								<text>{{$t('lang.activities_not_started')}}</text>
							</block>
							<block v-if="auction.status_no == 1">
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
							<view class="goods-name twolist-hidden flex_1"><text>{{auction.goods_name}}</text></view>
							<view class="goods_share" @click="mpShare">
								<text class="iconfont icon-share" style="line-height: 1;"></text>
								<text class="share_txt">{{ $t('lang.share') }}</text>
							</view>
						</view>
						<view class="goods_shipai" v-if="auction.formated_deposit">{{$t('lang.gb_deposit')}}:{{auction.formated_deposit}}</view>
						<view class="uni-flex-common uni-space-between">
							<view class="goods_shipai" v-if="auction.formated_amplitude">{{$t('lang.au_amplitude')}}:{{auction.formated_amplitude}}</view>
							<text style="color: #999999; font-size: 25upx;">{{$t('lang.au_mechanism')}} {{auctionGoodsData.auction_goods.rz_shop_name}}</text>
						</view>
					</view>
				</view>
				<view class="uni-card uni-card-not">
					<view class="uni-list">
						<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="auctionProcess">
							<view class="uni-list-cell-navigate uni-navigate-right">
								<text class="title">{{$t('lang.bidding_process')}}</text>
								<view class="value"></view>
							</view>
						</view>
					</view>
				</view>
				<view class="uni-card uni-card-not">
					<view class="uni-list">
						<view class="uni-list-cell uni-collapse">
							<view class="uni-list-cell-navigate items" :class="listshow ? 'uni-active' : ''">
								<view class="item">
									<view class="value">
										<uni-number-box :value="price" :step="auction.amplitude" :min="auction.current_price_int" :max="end_price" @change="bindChange"></uni-number-box>
										<text style="margin-left: 20upx; color: #f92028;">{{$t('lang.label_min_fare')}}{{auction.amplitude}} * N</text>
									</view>
								</view>
							</view>
						</view>
					</view>
				</view>
				<view class="uni-card uni-card-not">
					<view class="uni-list">
						<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="$outerHref('/pagesA/auction/log/log?act_id='+act_id,'app')">
							<view class="uni-list-cell-navigate uni-navigate-right">
								<text class="title">{{$t('lang.bid_record')}}</text>
								<view class="value"></view>
							</view>
						</view>
					</view>
					<view class="auction-log">
						<view class="log-item" v-for="(item,index) in auctionGoodsData.auction_log" :key="index">
							<view class="lie">
								<view class="name">
									<uni-tag :text="$t('lang.au_bid_ok')" size="small" type="error" v-if="index == 0"></uni-tag>
									<uni-tag :text="$t('lang.offer_a_price')" size="small" type="error" v-else></uni-tag>
									<text v-if="item.user_name">{{item.user_name}}</text>
								</view>
								<view class="time" v-if="item.bid_time">{{item.bid_time}}</view>
							</view>
							<view class="uni-red" v-if="item.formated_bid_price">{{item.formated_bid_price}}</view>
						</view>
						<view class="log-more" @click="$outerHref('/pagesA/auction/log/log?act_id='+act_id,'app')">{{$t('lang.view_more')}}</view>
					</view>
				</view>
				<view class="uni-card uni-card-not" v-if="auctionGoodsData.hot_goods && auctionGoodsData.hot_goods.length > 0">
					<view class="uni-list">
						<view class="uni-list-cell uni-list-cell-title">
							<view class="uni-list-cell-navigate">
								<text class="title">{{$t('lang.auction_is_recommended')}}</text>
								<scroll-view class="scroll-view scroll-view-product" scroll-x="true" scroll-left="0">
									<view class="scroll-view-item" v-for="(item,index) in auctionGoodsData.hot_goods" :key="index" @click="detailHref(item)">
										<image :src="item.goods_thumb" mode="widthFix"></image>
										<text class="name uni-ellipsis">{{ item.goods_name }}</text>
										<view class="price uni-flex">
											<view class="price-original">{{ item.formated_start_price }}</view>
										</view>
									</view>
								</scroll-view>
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
							<block v-if="actPromise">
								<rich-text :nodes="actPromise"></rich-text>
							</block>
							<block v-else>
								<dsc-not-content></dsc-not-content>
							</block>
						</view>
						<view v-show="current === 2">
							<block v-if="actEnsure">
								<rich-text :nodes="actEnsure"></rich-text>
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
			
			<view class="item" @click="collection">
				<block v-if="is_collect == 1"><view class="iconfont icon-collection-alt"></view></block>
				<block v-else><view class="iconfont icon-collection"></view></block>
				<text class="txt">{{$t('lang.collect')}}</text>
			</view>
			<view class="btn-bar">
				<view class="btn btn-btn" v-if="auction.status_no === 1" @click="auctionBid()">{{$t('lang.me_bid')}}</view>
				<block v-else>
					<view class="btn btn-btn" v-if="auction.is_winner === 1" @click="auctionBuy()">{{$t('lang.button_buy')}}</view>
					<view class="btn btn-org" v-else>{{$t('lang.activities_end')}}</view>
				</block>
			</view>
		</view>
		
		<uni-popup :show="processShow" type="bottom" v-on:hidePopup="handelClose('process')">
			<view class="title">
				<view class="txt">{{$t('lang.the_auction_process')}}</view>
				<uni-icons type="closeempty" size="36" color="#999999" @click="handelClose('process')"></uni-icons>
			</view>
			<view class="rule">
				<view>{{$t('lang.bidding_process_1')}} <text class="ml20">{{$t('lang.bidding_process_1_propmt')}}</text></view>
				<view>{{$t('lang.bidding_process_2')}} <text class="ml20">{{$t('lang.bidding_process_2_propmt')}}</text></view>
				<view>{{$t('lang.bidding_process_3')}} <text class="ml20">{{$t('lang.bidding_process_3_propmt')}}</text></view>
				<view>{{$t('lang.bidding_process_4')}} <text class="ml20">{{$t('lang.bidding_process_4_propmt')}}</text></view>
				<view>{{$t('lang.bidding_process_5')}} <text class="ml20">{{$t('lang.bidding_process_5_propmt')}}</text></view>
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
		
		<dsc-common-nav>
			<navigator url="../auction" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.auction_page')}}</text>
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
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import universal from '@/common/mixins/universal.js';
	import dscRegion from '@/components/dsc-region.vue';
	import uniPopups from '@/components/uni-popup/uni-popup.vue';
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
				tabs: [this.$t('lang.au_introduce'),this.$t('lang.service_guarantee'),this.$t('lang.au_raiders')],
				ladderFristNum:1,
				processShow:false,
				num:1,
				dscLoading:true,
				ruleShow: false,
				listshow:true,
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
                auctionGoodsData: state => state.ump.auctionGoodsData,
			}),
			goodsInfo(){
				return this.auctionGoodsData.auction_goods
			},
			auction(){
				return this.auctionGoodsData.auction
			},
			auction_log(){
				return this.auctionGoodsData.auction_log
			},
			gallerylList() {
				return this.auctionGoodsData.goods_img ? this.auctionGoodsData.goods_img : []
			},
			dateTime(){
				let dataTime = this.auction.endTime
				if(dataTime != ''){
					return this.$formatDateTime(dataTime)
				}
			},
			price:{
				get(){
					return this.auction.current_price_int
				},
				set(val){
					this.auction.current_price_int = val
				}
			},
			end_price(){
				let end_price = 100000000000000000;
				if(this.auction.end_price > 0){
					end_price = this.auction.end_price
				}
	
				return end_price
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
			actPromise(){
				let result = this.auction.act_promise
				const regex = new RegExp('<img', 'gi');
				const regex2 = new RegExp('<p', 'gi');
				
				if(result){
					result = result.replace(regex, '<img style="width: 100% !important; height:auto;vertical-align:top;"');
					result = result.replace(regex2, '<p style="margin:0;padding:0;"');
				}
				return result
			},
			actEnsure(){
				let result = this.auction.act_ensure
				const regex = new RegExp('<img', 'gi');
				const regex2 = new RegExp('<p', 'gi');
				
				if(result){
					result = result.replace(regex, '<img style="width: 100% !important; height:auto;vertical-align:top;"');
					result = result.replace(regex2, '<p style="margin:0;padding:0;"');
				}
				return result
			},
			goodsCollectStatue(){
			    return this.$store.state.user.goodsCollectStatue
			},
			is_collect:{
			    get(){
					return this.auction.is_collect
				},
				set(val){
					this.auction.is_collect = val
				}
			}
		},
		onShareAppMessage(res){
			return {
			  title: this.goodsInfo.goods_name,
			  path: '/pagesA/auction/detail/detail?act_id=' + this.act_id
			}
		},
		onNavigationBarButtonTap(e){
			if(e.type == 'share'){
				let shareInfo = {
					href:this.$websiteUrl + 'auction/detail/' + this.act_id + '&platform=APP',
					title:this.goodsInfo.goods_name,
					summary:this.goodsInfo.goods_brief,
					imageUrl:this.goodsInfo.goods_thumb
				};
				this.shareInfo(shareInfo)
			}
		},
		onLoad(e){
			let that = this;
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
			
			this.loadInfo();
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
		watch:{
			auctionGoodsData(){
				this.dscLoading = false
			},
			goodsCollectStatue() {
			    this.goodsCollectStatue.forEach((v) => {
			        if (v.id == this.goodsInfo.goods_id) {
			            this.is_collect = v.status
			        }
			    })
			},
			sharePoster() {
				if (this.sharePoster) {
					this.$refs.popupPoster.open();
				}
			}
		},
		methods: {
			loadInfo(){
				this.$store.dispatch('setAuctionGoods',{
					id: this.act_id
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
			//出价
			auctionBid(){
				const that = this;
				this.$store.dispatch('setAuctionBid',{
					id: this.act_id,
					price_times:this.price
				}).then(res => {
					let data = res.data
					if(data.error == 0){
						uni.showToast({
							title: that.$t('lang.button_bid_succeed')
						})
						this.loadInfo()
					}else{
						uni.showToast({
							icon:'none',
							title:data.msg
						})
					}
				})
			},
			async auctionBuy(){
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
				
				if(this.$isLogin()){
					this.$store.dispatch('setAuctionBuy',{
						id: this.act_id,
					}).then(res =>{
						let data = res.data
						if(data.error == 0){
							uni.navigateTo({
								url:'/pages/checkout/checkout?rec_type=' + data.flow_type + '&type_id=' + data.extension_id
							})
						}else{
							uni.showToast({
								icon:'none',
								title:data.msg
							})
						}
					})
				}else{
					uni.showModal({
						content: this.$t('lang.login_user_not'),
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
			//关闭Popup
			handelClose(val){
				if(val == 'process'){
			        this.processShow = false
			    }else if(val == 'rule'){
					this.ruleShow = false
				}
			},
			auctionProcess() {
				this.processShow = !this.processShow
			},
			detailHref(item){
				uni.navigateTo({
					url:'/pagesA/auction/detail/detail?id=' + item.act_id
				})
			},
			//收藏
			collection(){
				if(this.$isLogin()){
					this.$store.dispatch('setCollectGoods', {
						goods_id: this.goodsInfo.goods_id,
						status: this.is_collect
					})
				}else{
					uni.showModal({
						content: this.$t('lang.fill_in_user_collect_goods'),
						success:(res)=>{
							if(res.confirm){
								uni.navigateTo({
									url:'/pagesB/login/login'
								})
							}
						}
					})
				}
			},
			handleRule(){
				this.ruleShow = true
			},
			// 分享
			appShare() {
				let shareInfo = {
					href: `${this.$websiteUrl}auction/detail/${this.act_id}?parent_id=${uni.getStorageSync("user_id")}&platform=APP`,
					title: this.auction.goods_name,
					summary: this.auction.goods_brief,
					imageUrl: this.auction.goods_thumb
				};

				this.shareInfo(shareInfo, 'poster');
			},
			mpShare() {
				this.onGoodsShare();
			},
			onGoodsShare() {
				if (this.$isLogin()) {
					uni.showLoading({ title: this.$t('lang.loading') });
					let price = this.auction.formated_current_price;
					let o = {}
					
					// #ifdef MP-WEIXIN
					o = {
						goods_id: this.auction.goods_id,
						ru_id: 0,
						price: price,
						share_type: 0,
						type: 0,
						platform: 'MP-WEIXIN',
						extension_code:'auction',
						code_url:'pagesA/auction/detail/detail',
						scene:`${this.act_id}`,
						thumb:this.gallerylList[0] || this.goodsInfo.goods_img
					}
					// #endif
					
					// #ifdef APP-PLUS
					o = {
						goods_id: this.auction.goods_id,
						price: price,
						share_type: 0,
						platform: 'APP',
						extension_code:'auction',
						code_url:`${this.$websiteUrl}auction/detail/${this.act_id}`,
						thumb:this.gallerylList[0] || this.goodsInfo.goods_img
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

.groupbuy-price-list{ padding: 20upx 0; border-top: 2upx solid #F4F4F4;}
.groupbuy-price-list .li{ display: flex; flex-direction: row;}
.groupbuy-price-list .li text{ padding: 10upx 20upx; width: 50%; box-sizing: border-box; flex: 1; display: flex; justify-content: center; align-items: center;}
.groupbuy-price-list .li.li-tit text{ color: #f92028;}

/* 小程序分享  start*/
.show-popup-shareImg /deep/ .uni-popup-bottom{ height: 80%; }
/* 小程序分享 end*/
</style>
