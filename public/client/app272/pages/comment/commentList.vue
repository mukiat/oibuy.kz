<template>
	<view class="comment_box">

		<!-- 头部帖子时间和分享 star -->
		<view class="comment_header flex_box jc_sb ai_center bgc_fff">
			<view class="header_left flex_box ai_center">
				<view class="source_head_portrait">
					<image :src="detailData.user_picture"></image>
				</view>
				<view class="source_info flex_box fd_column">
					<text class="size_28 color_333">{{ detailData.user_name }}</text>
					<text class="size_20 color_888">{{ detailData.add_time_formated }}</text>
				</view>
			</view>
			<!-- #ifdef MP-WEIXIN -->
			<button class="header_right" open-type="share" hover-class="none">
				<text class="iconfont icon-find-share size_34 color_333"></text>
			</button>
			<!-- #endif -->
			<!-- #ifdef APP-PLUS -->
			<view class="header_right" @tap="appShare">
				<text class="iconfont icon-find-share size_34 color_333"></text>
			</view>
			<!-- #endif -->
		</view>
		<!-- 头部帖子时间和分享 end -->

		<!-- 轮播图 star -->
		<view class="slideshow_box">
			<text class="indicator_dots_box size_24 color_fe bgc_888">{{ indicator }}</text>
			<swiper class="slideshow" :autoplay="true" :interval="3000" :circular="true" @change="swiperChange">
				<swiper-item v-for="(swipeItem, swipeIndex) in detailData.goods_gallery" :key="swipeIndex">
					<image class="swiper_item_img" :src="swipeItem" mode="aspectFill" @click="previewImg(swipeIndex,swipeItem)"></image>
				</swiper-item>
			</swiper>
		</view>
		<!-- 轮播图 end -->

		<!-- 贴子内容 star -->
		<view class="comment_explain flex_box fd_column ai_center bgc_fff">
			<text class="comment_explain_title size_40 color_333 text_1" v-if="false">{{ detailData.content }}</text>
			<text :class="['comment_explain_content', 'size_28', 'color_888', isZhanKai ? '' : 'text_9']">
				{{ detailData.content }}
			</text>
			<view class="zhankai" @click="zhankaiHandle" v-if="isShowZhanKai">
				<text class="size_28 color_333">{{ isZhanKai ? $t('lang.pack_up') : $t('lang.zhankai') }}</text>
				<text :class="['iconfont', isZhanKai ? 'icon-expand-down' : 'icon-zhankai', 'size_24', 'color_333']"></text>
			</view>
		</view>
		<!-- 贴子内容 end -->

		<!-- 商品 star -->
		<view class="comment_goods_list br_20 bgc_fff">
			<view class="goods_item flex_box" @click="$outerHref('/pagesC/goodsDetail/goodsDetail?id='+detailData.goods_id,'app')">
				<view class="goods_left bgc_F6">
					<image :src="detailData.goods_thumb"></image>
				</view>
				<view class="goods_right flex_1 flex_box fd_column jc_sb">
					<text class="size_32 color_333 text_2 weight_700">{{ detailData.goods_name }}</text>
					<view class="price_box">
						<text class="size_32 weight_700 color_f0151b">{{ detailData.shop_price }}</text><text class="size_20 color_888 td_lt"><text class="size_18">{{ currency_format }}</text>{{ detailData.market_price }}</text>
					</view>
				</view>
			</view>
		</view>
		<!-- 商品 end -->

		<!-- 相关评论 star -->
		<view class="comments_box">
			<text class="comments_title size_38 color_333 weight_700">{{$t('lang.comments')}}</text>
			<view :class="['comments_list', commentList.length > 0 ? 'br_20' : 'br_2', 'bgc_fff']">
				<!-- <text class="comment_count lh_1 size_28 color_888">共{{ detailData.reply_count }}条评论</text> -->
				<text class="comment_count lh_1 size_28 color_888">{{$t('lang.gong')}}{{ detailData.reply_count }}{{$t('lang.tiaopinglun')}}</text>
				<template v-if="commentList.length > 0">
					<view class="comments_list_item flex_box" v-for="(item,index) in commentList" :key="index">
						<view class="item_left_box">
							<image :src="item.user_picture"></image>
						</view>
						<view class="item_right_box flex_1 flex_box fd_column bb_e6">
							<text class="lh_1 size_28 color_888 text_1">{{ item.user_name }}<text v-if="item.user_type > 0" :class="['size_20', 'color_fff', item.user_type == 1 ? 'user_type_1' : 'user_type_2']">{{ item.user_type == 1 ? $t('lang.admins') : $t('lang.biz') }}</text></text>
							<view class="right_box_bottom">
								<text class="size_32 color_333">{{ item.content }}</text>
								<text class="size_24 color_888 margin_left10">{{ item.add_time_formated }}</text>
							</view>
						</view>
					</view>
				</template>
				<template v-else>
					<view class="no_comment flex_box ai_center">
						<view class="no_commont_pic">
							<image :src="detailData.my_picture" v-if="detailData.my_picture"></image>
							<image src="../../static/get_avatar.png" v-else></image>
						</view>
						<text class="no_commont_placeholder flex_1 size_28 color_888 bgc_f5" v-if="isFocus">{{$t('lang.say_something')}}</text>
						<text class="no_commont_placeholder flex_1 size_28 color_888 bgc_f5" @click.stop="showTextAreaHandle" v-else>{{$t('lang.say_something')}}</text>
					</view>
				</template>
			</view>
		</view>
		<!-- 相关评论 end -->

		<!-- 下拉加载提示 -->
		<view class="dsc_loading_box size_28 color_333" v-if="commentList.length > 0">{{isOver ? $t('lang.no_more') : $t('lang.loading')}}</view>
		<!-- 滚动区域 end -->

		<dsc-loading :dscLoading="dscLoading"></dsc-loading>

		<!-- 底部评论框 star -->
		<view class="footer_fiexed_box">

			<view :style="{display: isShowTextArea ? 'none' : 'block'}" class="placeholder_box">
				<view class="footer_container">

					<view class="functional_zone">
						<view class="functional_zone_left" @click.stop="showTextAreaHandle">
							<text class="iconfont icon-find-pinglun size_28 color_888"></text>
							<text class="size_28 color_888">{{$t('lang.say_something2')}}</text>
						</view>
						<view class="functional_zone_right">
							<view class="dianzan_box" @click="onZan">
								<text :class="['iconfont', isDianZan ? 'icon-dianzan-after' : 'icon-find-zan', 'size_38', 'color_333']"></text>
								<text class="size_26 color_333">{{ detailData.like_num }}</text>
							</view>
							<view class="liulan_box" v-if="false">
								<text class="iconfont icon-find-liulan size_38 color_333"></text>
								<text class="size_26 color_333">{{ detailData.dis_browse_num }}</text>
							</view>
							<view class="pinlun_box">
								<text class="iconfont icon-find-talk size_38 color_333"></text>
								<text class="size_26 color_333">{{ detailData.reply_count }}</text>
							</view>
						</view>
					</view>

				</view>
			</view>

			<view :style="{display: isShowTextArea ? 'block' : 'none'}">
				<view :class="[isIos ? 'footer_container2' : 'footer_container3']">
					<view class="textarea_container">
						<view class="textarea_box">
							<textarea class="comment_area size_28" v-model="commentCont" maxlength="-1" @focus="focusHandle" :fixed="true"
							 @blur="blurHandle" :auto-height="autoHeight" :adjust-position="true" :show-confirm-bar="false" :focus="isFocus" :cursor-spacing="cursorSpacing"
							 @linechange="linechange" />
						</view>
						<view class="comment_send_btn size_28 color_fff" @click="sendComment">{{$t('lang.send')}}</view>
					</view>
				</view>
			</view>

		</view>
		<!-- 底部评论框 end -->

	</view>
</template>

<script>

	export default {
		data() {
			return {
				commentId: '',
				detailData: {},
				commentList: [],
				isZhanKai: true,
				isShowZhanKai: false,
				isFocus: false,
				isLoading: false,
				isOver: false,
				commentCont: '',
				keyBoardHeight: 0,
				textAreaLineHeight: 1,
				isIos: true,
				scrollViewHeight: 0,
				currentSwiper: 1,
				page: 1,
				size: 10,
				currency_format: uni.getStorageSync('configData').currency_format || '¥',
				zhankaiTop: 0,
				isDianZan: false,
				dscLoading: true,
				isTouchmove: false,
				showKeybar: false
			}
		},
		onLoad({id}) {
			this.commentId = id || '';
			this.getCommentDetailById();
			this.getcommentById();
			const { platform, windowHeight } = uni.getSystemInfoSync();
			this.isIos = platform == 'ios';
			this.scrollViewHeight = windowHeight - uni.upx2px(210);
			uni.$emit('lookComment', {id: this.commentId});
		},
		onShareAppMessage(res){
			let shareTitle = this.detailData.content.slice(0, 25);
			shareTitle = shareTitle.length == 25 ? shareTitle + '...' : shareTitle;
			return {
			  title: shareTitle,
			  path: `/pages/comment/commentList?id=${this.commentId}`
			}
		},
		computed: {
			indicator: function () {
				if (this.detailData.goods_gallery) return `${this.currentSwiper}/${this.detailData.goods_gallery.length}`;
				return '0/0';
			},
			autoHeight: function () {
				return this.textAreaLineHeight < 2
			},
			cursorSpacing: function () {
				return uni.upx2px(47)
			},
			footerBoxBottom: function () {
				if (this.isIos) return this.keyBoardHeight > 0 ? `${this.keyBoardHeight}px` : 0;
				else return 0
			},
			isShowTextArea: function () {
				if (this.commentCont) {
					return true
				} else if (this.isFocus) {
					return true
				} else if (this.showKeybar) {
					return true
				} else {
					return false
				}
			}
		},
		filters: {
			shopPrice: function (val) {
				if (!val) return '0';
				return val.substr(1);
			}
		},
		onPageScroll(e) {
			this.zhankaiTop = e.scrollTop;
		},
		onReachBottom() {
			if (this.isOver || this.isLoading) return;
				this.page = this.page + 1;
				this.getcommentById();
		},
		methods: {
			getCommentDetailById(isSend = false) {
				uni.request({
					url: this.websiteUrl + '/api/discover/find_detail',
					method: 'GET',
					data: {
						dis_id: this.commentId,
					},
					header: {
						'Content-Type': 'application/json',
						'X-Client-Hash': uni.getStorageSync('client_hash'),
						'token': uni.getStorageSync('token')
					},
					success: ({data: {status, data}}) => {
						this.dscLoading = false;
						if (status == 'success') {
							this.detailData = data || {};
							this.$nextTick(function(){
								let timerId = setTimeout(() => {
									this.getHeight();
									clearTimeout(timerId);
								},50)
							});
							if (isSend) {
								this.page = 1;
								this.getcommentById(true);
							}
						} else {
							uni.showToast({
								title: this.$t('lang.post_msg_fail'),
								icon: 'none'
							})
						}
					},
					fail: (err) => {

					}
				})
			},
			getcommentById(toTop = false) {
				this.isLoading = true;
				uni.request({
					url: this.websiteUrl + '/api/discover/find_reply_comment',
					method: 'GET',
					data: {
						dis_id: this.commentId,
						page: this.page,
						size: this.size
					},
					header: {
						'Content-Type': 'application/json',
						'X-Client-Hash': uni.getStorageSync('client_hash'),
						'token': uni.getStorageSync('token')
					},
					success: ({data: {status, data}}) => {
						if (status == 'success') {
							this.isOver = data.length < this.size;
							if (this.page == 1) this.commentList = [];
							this.commentList = [...this.commentList, ...data];
							if (toTop) {
								uni.pageScrollTo({
								    selector: '.comments_box',
								    duration: 300
								});
							}
						} else {
							uni.showToast({
								title: this.$t('lang.post_msg_fail'),
								icon: 'none'
							})
						}

						this.isLoading = false;
					},
					fail: (err) => {
						this.isLoading = false;
					}
				})
			},
			getHeight() {
				const px = uni.upx2px(50) * 9 + 9; // 1px 抹平机型误差
				const query = uni.createSelectorQuery();
				query.select('.comment_explain_content').fields({size: true}, data => {
					this.isShowZhanKai = data.height > px;
					this.isZhanKai = !this.isShowZhanKai;
				}).exec();
			},
			swiperChange(e) {
				this.currentSwiper = e.detail.current + 1;
			},
			focusHandle(e) {
				this.isFocus = true;
				this.keyBoardHeight = e.detail.height || 0;
			},
			blurHandle() {
				this.isFocus = false;
				this.showKeybar = false;
				this.keyBoardHeight = 0;
			},
			linechange(e) {
				this.textAreaLineHeight = e.detail.lineCount || 1;
			},
			showTextAreaHandle() {
				this.showKeybar = true;
				this.$nextTick(function(){
					this.isFocus = true;
				})
			},
			sendComment() {
				if (!this.commentCont.trim()) return;
				if(this.$isLogin()){
					this.$store.dispatch('setDiscoverComment',{
						parent_id:this.commentId,
						quote_id:0,
						dis_text:this.commentCont,
						reply_type:0,
						dis_type:4,
						goods_id:this.detailData.goods_id,
					}).then(res=>{
						uni.showToast({
							title:res.msg,
							icon:'none'
						})
						if(res.error == 0){
							this.commentCont = '';
							this.getCommentDetailById(true);

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
			zhankaiHandle() {
				this.isZhanKai = !this.isZhanKai;
				this.$nextTick(function(){
					uni.pageScrollTo({
					    scrollTop: this.zhankaiTop,
					    duration: 100
					});
				})
			 },
			//app分享
			appShare(){
				let shareTitle = this.detailData.content.slice(0, 25);
				shareTitle = shareTitle.length == 25 ? shareTitle + '...' : shareTitle;
				let shareInfo = {
					href: this.$websiteUrl + 'conmmentlist/' + this.commentId,
					title: shareTitle,
					summary: this.detailData.goods_name,
					imageUrl: this.detailData.goods_thumb
				};
				this.shareInfo(shareInfo)
			},
			onZan(){
				this.$store.dispatch('setDiscoverLike',{
					dis_type: 4,
					dis_id: this.commentId
				}).then(res=>{
					uni.showToast({
						title:res.msg,
						icon:'none'
					})
					this.isDianZan = true;
					this.detailData.like_num = res.like_num;
				})
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
		}
	}
</script>

<style scoped>
	.comment_box {
		padding: 80upx 0 calc(env(safe-area-inset-bottom) + 130upx);
	}

	.comment_header {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 80upx;
		padding: 0 38upx 16upx;
		box-sizing: border-box;
		z-index: 3;
	}

	.comment_header .header_right {
		font-size: inherit;
		margin: 0;
		padding: 0;
		border-radius: 0;
		background-color: transparent;
		line-height: inherit;
	}

	.comment_header .header_right::after {
		border: none;
		border-radius: 0;
	}

	.source_head_portrait {
		width: 65upx;
		height: 65upx;
		margin-right: 16upx;
	}
	.source_head_portrait image {
		width: 100%;
		height: 100%;
		border-radius: 50%;
	}

	.source_info text {
		line-height: 1.2;
	}

	.slideshow_box {
		position: relative;
		height: 882upx;
	}
	.slideshow {
		width: 100%;
		height: 100%;
	}
	.swiper_item_img {
		width: 100%;
		height: 100%;
	}
	.indicator_dots_box {
		position: absolute;
		top: 20upx;
		right: 30upx;
		height:36upx;
		line-height: 36upx;
		text-align: center;
		padding: 0 20upx;
		border-radius:18upx;
		z-index: 2;
	}

	.comment_explain {
		position: relative;
		top: -20upx;
		left: 0;
		width: 100%;
		/* max-height: 745upx; */
		padding: 40upx 25upx;
		border-radius: 20upx;
		box-sizing: border-box;
		z-index: 1;
	}
	.comment_explain_title {
		width: 100%;
		line-height: 1;
	}
	.comment_explain_content {
		width: 100%;
		line-height: 50upx;
		text-align: justify;
	}
	.zhankai {
		margin-top: 52upx;
	}
	.zhankai text:nth-child(1) {
		margin-right: 10upx;
	}
	.comment_goods_list {
		margin-bottom: 20upx;
	}
	.goods_item {
		padding: 30upx;
	}
	.goods_left {
		width:190upx;
		height:190upx;
	}
	.goods_left image {
		width: 100%;
		height: 100%;
		border-radius:2upx;
	}
	.goods_right {
		padding: 10upx 0 10upx 20upx;
	}
	.goods_right .size_32 {
		line-height: 42upx;
	}
	.price_box .size_32 {
		margin-right: 22upx;
	}
	.comments_title {
		display: inline-block;
		line-height: 1;
		padding: 30upx 0 30upx 24upx;
	}
	.comments_list {
		padding: 42upx 24upx 0;
	}
	.comment_count {
		margin-bottom: 40upx;
	}
	.comments_list_item {
		padding-bottom: 30upx;
	}
	.comments_list_item:last-child{
		padding-bottom: 0;
	}
	.comments_list_item:last-child .bb_e6{
		border-bottom: 0;
	}
	.item_left_box {
		width:60upx;
		height:60upx;
		margin: 10upx 28upx 0 0;
		border-radius:50%;
	}
	.item_left_box image {
		width: 100%;
		height: 100%;
		border-radius:50%;
	}
	.user_type_1 {
		display: inline-block;
		height:28upx;
		line-height: 28upx;
		background:linear-gradient(-90deg,rgba(75,185,243,1),rgba(55,158,255,1));
		border-radius:3upx;
		padding: 0 6upx;
		margin-left: 8upx;
	}
	.user_type_2 {
		display: inline-block;
		height:28upx;
		line-height: 28upx;
		background:linear-gradient(-90deg,rgba(255,106,82,1),rgba(255,75,75,1));
		border-radius:3upx;
		padding: 0 6upx;
		margin-left: 8upx;
	}
	.item_right_box {
		padding-bottom: 30upx;
	}
	.margin_left10 {
		margin-left: 10upx;
	}
	.no_comment {
		padding-bottom: 200upx;
	}
	.no_commont_pic {
		width:60upx;
		height:60upx;
		border-radius:50%;
		margin-right: 16upx;
	}
	.no_commont_pic image {
		width: 100%;
		height: 100%;
		border-radius:50%;
	}
	.no_commont_placeholder {
		border-radius:30upx;
		padding-left: 33upx;
		line-height: 60upx;
	}

	.dsc_loading_box {
		text-align: center;
		padding: 30upx;
	}



	.footer_fiexed_box {
		position: fixed;
		bottom: 0;
		left: 0;
		width: 100%;
		z-index: 999;
	}

	.placeholder_box {
		background-color: #fff;
		padding-bottom: env(safe-area-inset-bottom);
	}

	.footer_container {
		display: flex;
		align-items: flex-end;
		width: 100%;
		height: 130upx;
		max-height: 130upx;
		border-top: 2upx solid #E6E6E6;
		background-color: #fff;
		padding-bottom: 30upx;
		box-sizing: border-box;
	}

	.functional_zone {
		display: flex;
		align-items: center;
		width: 100%;
		padding-left: 25upx;
	}

	.functional_zone_left {
		width: 60%;
		height: 70upx;
		line-height: 70upx;
		background-color: #F5F5F5;
		border-radius: 35upx;
	}

	.functional_zone_left .icon-find-pinglun {
		margin: 0 24upx;
	}

	.functional_zone_right {
		flex: 1;
		display: flex;
		align-items: center;
	}

	.functional_zone_right .iconfont {
		margin-right: 10upx;
	}

	.dianzan_box,
	.liulan_box,
	.pinlun_box {
		flex: 1;
		display: flex;
		justify-content: center;
		align-items: center;
		/* margin-left: 40upx; */
	}
	.dianzan_box .icon-dianzan-after {
		color: #F0151B;
	}

	.footer_container2 {
		display: flex;
		align-items: flex-end;
		width: 100%;
		height: 98upx;
		border-top: 2upx solid #E6E6E6;
		background-color: #fff;
		padding-bottom: calc(env(safe-area-inset-bottom) + 30upx);
	}

	.footer_container3 {
		display: flex;
		align-items: flex-end;
		width: 100%;
		height: 150upx;
		max-height: 150upx;
		border-top: 2upx solid #E6E6E6;
		background-color: #fff;
		padding-bottom: 50upx;
		box-sizing: border-box;
	}

	.textarea_container {
		display: flex;
		justify-content: space-between;
		align-items: flex-end;
		width: 100%;
		padding: 0 25upx;
		box-sizing: border-box;
	}

	.textarea_box {
		display: flex;
		align-items: center;
		width: 564upx;
		height: 70upx;
		background-color: #F5F5F5;
		border-radius: 35upx;
		padding: 0 35upx;
		box-sizing: border-box;
	}

	.comment_area {
		width: 100%;
		height: 70upx;
		line-height: 35upx;
	}

	.comment_send_btn {
		width:122upx;
		height:70upx;
		line-height: 70upx;
		text-align: center;
		background:linear-gradient(-88deg,rgba(255,79,46,1),rgba(249,31,40,1));
		border-radius:35upx;
	}


/* 	.footer_box {
		position: fixed;
		bottom: 0;
		width: 100%;
		min-height: 126upx;
		border-top: 2upx solid #E6E6E6;
		z-index: 3;
		padding-bottom: 0;
		padding-bottom: constant(safe-area-inset-bottom);
		padding-bottom: env(safe-area-inset-bottom);
	}
	.footer_box_focus {
		min-height: 158upx;
	}
	.footer_mian {
		width: 100%;
		max-height: 70upx;
		margin: 28upx 0 0 24upx;
	}
	.textarea_box {
		width:60%;
		min-height: 70upx;
		border-radius:35px;
	}
	.textarea_focus_box {
		flex: 1;
		padding: 0 35upx;
		transition: all .1s;
		min-height: 70upx;
		border-radius:35px;
	}

	.blur_box {
		position: absolute;
		left: 0;
		top: 50%;
		transform: translateY(-50%);
	}
	.functional_zone {
		flex: 1;
		height: 70upx;
		max-height: 70upx;
	}
	.dianzan_box,
	.liulan_box,
	.pinlun_box {
		display: flex;
		justify-content: center;
		align-items: center;
		margin-left: 40upx;
	}
	.dianzan_box .icon-dianzan-after {
		color: #F0151B;
	}
	.footer_mian .iconfont {
		margin-right: 12upx;
	}
	.textarea_box .icon-find-pinglun {
		margin: 0 24upx;
	}

	.comment_area {
		width: 100%;
		height: 70upx;
		line-height: 70upx;
	}
	.comment_area_line_2 {
		line-height: 35upx;
	}
	.comment_send_btn {
		width:122upx;
		height:70upx;
		line-height: 70upx;
		text-align: center;
		margin: 0 25upx 0 14upx;
		background:linear-gradient(-88deg,rgba(255,79,46,1),rgba(249,31,40,1));
		border-radius:35upx;
	}
	.dsc_loading_box {
		text-align: center;
		padding: 30upx;
	}

	.br_2 {
	  border-top-left-radius: 20upx;
	  border-top-right-radius: 20upx;
	}

	.margin_left10 {
		margin-left: 10upx;
	} */
</style>
