<template>
	<view class="container comment_detail_content">
		<view class="imgthumb">
			<image class="goods_thumb" :src="commentInfo.goods_thumb"></image>
			<view class="imgthumb_wrap_left">
				<text>商品评价</text>
				<view class="rate_wrap" v-if="isAddEvaluate == 1">
					<!-- #ifdef APP-PLUS -->
					<text :class="['iconfont', 'icon-collection-alt', 'size_32', commentInfo.comment_rank >= rate ? 'color_red' : '']" v-for="(rate, rIndex) in 5" :key="rIndex"></text>
					<!-- #endif -->
					<!-- #ifdef MP-WEIXIN -->
					<text :class="['iconfont', 'icon-collection-alt', 'size_32', commentInfo.comment_rank > rate ? 'color_red' : '']" v-for="(rate, rIndex) in 5" :key="rIndex"></text>
					<!-- #endif -->
				</view>
				<view class="rate_wrap" v-else>
					<!-- #ifdef APP-PLUS -->
					<text :class="['iconfont', 'icon-collection-alt', 'size_32', rank >= rate ? 'color_red' : '']" v-for="(rate, rIndex) in 5" :key="rIndex" @click="evaluation(rate)"></text>
					<!-- #endif -->
					<!-- #ifdef MP-WEIXIN -->
					<text :class="['iconfont', 'icon-collection-alt', 'size_32', rank > rate ? 'color_red' : '']" v-for="(rate, rIndex) in 5" :key="rIndex" @click="evaluation(rate)"></text>
					<!-- #endif -->
				</view>
			</view>
		</view>
		
		<view class="comment_module">
			<view class="header">{{tips.title}}</view>
			<view class="tag_list u-border-bottom" v-if="isAddEvaluate == 0 && commentInfo.goods_product_tag && commentInfo.goods_product_tag.length">
				<view :class="['tag_item', tagList.includes(tag) ? 'active_tag' : '']" v-for="(tag, tagIndex) in commentInfo.goods_product_tag" :key="tagIndex" @click="selectTag(tag)">{{tag}}</view>
			</view>
			<view class="input_wrap">
				<textarea class="text_area" maxlength="500" v-model="textarea" :placeholder="tips.placeholder" />
				<view class="comment_length">已写<text>{{textareaLength}}</text>个字</view>
			</view>
			<view class="add_img">
				<view class="img_box" v-for="(image,index) in imageList" :key="index">
					<image mode="aspectFill" :src="image" :data-src="image" @click="previewImage"></image>
					<text class="iconfont icon-delete" @click.stop="deteleImg(index)"></text>
				</view>
				<view :class="['add_btn', hasImg ? 'has_img' : '']" @click="chooseImage('apply')">
					<text class="iconfont icon-jia"></text>
					<text class="add_pic">添加图片</text>
				</view>
			</view>
		</view>
		
		<view class="satisfaction_module" v-if="commentInfo.degree_count == 0 && commentInfo.ru_id > 0 && isAddEvaluate == 0">
			<view class="header">
				<text class="header_left">满意度评价</text>
				<text class="header_right">满意请给5颗星哦</text>
			</view>
			<view class="rate_list">
				<view class="rate_item" v-for="(item, index) in satisfactionList" :key="index">
					<view class="rate_label">{{item.title}}</view>
					<view class="rate_value">
						<!-- #ifdef APP-PLUS -->
						<text :class="['iconfont', 'icon-collection-alt', 'size_32', item.rank >= rate ? 'color_red' : '']" v-for="(rate, rIndex) in 5" :key="rIndex" @click="satisfaction(rate, index)"></text>
						<!-- #endif -->
						<!-- #ifdef MP-WEIXIN -->
						<text :class="['iconfont', 'icon-collection-alt', 'size_32', item.rank > rate ? 'color_red' : '']" v-for="(rate, rIndex) in 5" :key="rIndex" @click="satisfaction(rate, index)"></text>
						<!-- #endif -->
					</view>
				</view>
			</view>
		</view>
		
		<view class="floor_bar">
			<view class="btn" @click="btnSubmit">{{$t('lang.comment_submit')}}</view>
		</view>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	
	import uniRate from '@/components/uni-rate.vue'
	import uniIcons from '@/components/uni-icons/uni-icons.vue'
	
	import { pathToBase64, base64ToPath } from '@/common/image-tools/index.js'
	import { compressImage } from '@/common/compressImage.js'
	
	export default {
		data() {
			return {
				textarea:'',
				type:0,
				rank:0,
				server:0,
				delivery:0,
				imageList:[],
				imageSrc:'',
				isAddEvaluate: 0,
				tagList: [],
				satisfactionList: [
					{
						title: '商品描述相符度',
						rank: 0
					},
					{
						title: '卖家服务态度',
						rank: 0
					},
					{
						title: '物流发货速度',
						rank: 0
					},
					{
						title: '配送人员态度',
						rank: 0
					}
				]
			};
		},
		components:{
			uniRate,
			uniIcons
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/commentDetail/commentDetail'
			}
		},
		computed:{
			...mapState({
				materialList: state => state.user.materialList,
				commentInfo: state => state.user.commentInfo
			}),
			textareaLength(){
			  return this.textarea.length || 0
			},
			hasImg() {
				return this.imageList.length > 0 ? true : false
			},
			tips() {
				return {
					title: this.isAddEvaluate == 1 ? '追加一下你的使用体验吧' : '分享你的使用体验吧',
					placeholder: this.isAddEvaluate == 1 ? '对评价进行补充，更客观，更全面~（500字）' : '商品质量如何？快写下你的评价，分享给大家吧！（500字）'
				}
			}
		},
		methods:{
			// 点击商品标签
			selectTag(i) {
				if (this.tagList.includes(i)) this.tagList = this.tagList.filter(item => item != i)
				else this.tagList.push(i);
			},
			// 商品评价
			evaluation(val){
				// #ifdef APP-PLUS
				this.rank = val;
				// #endif
				// #ifdef MP-WEIXIN
				this.rank = val + 1;
				// #endif
			},
			// 满意度打分
			satisfaction(rate, i) {
				// #ifdef APP-PLUS
				this.satisfactionList.forEach((item, index) => {
					if (i == index) this.$set(this.satisfactionList[i], 'rank', rate)
					else this.$set(this.satisfactionList[index], 'rank', item.rank > rate ? item.rank : item.rank >	0 ? item.rank : 1)
				})
				// #endif
				// #ifdef MP-WEIXIN
				this.satisfactionList.forEach((item, index) => {
					if (i == index) this.$set(this.satisfactionList[i], 'rank', rate + 1)
					else this.$set(this.satisfactionList[index], 'rank', item.rank > rate ? item.rank : item.rank >	0 ? item.rank : 1)
				})
				// #endif
			},
			chooseImage: function(val) {
				let that = this
				if(this.imageList.length > 9){
					uni.showToast({
						title:this.$t('lang.return_nine_pic'),
						icon:'none'
					})
					return;
				}
				
				uni.chooseImage({
					count:9,
					sizeType: ['compressed'],
					success: (res) => {
						if(that.imageList.length + res.tempFilePaths.length > 9){
							uni.showToast({
								title:this.$t('lang.return_nine_pic'),
								icon:'none'
							})
							return;
						}
						
						// #ifdef APP-PLUS
						res.tempFilePaths.forEach((image,index) => {
							that.imageSrc = res.tempFilePaths[index];
							that.uploadImage(val);
						});
						// #endif
						
						// #ifdef MP-WEIXIN
						res.tempFilePaths.forEach((image,index) => {
							let size = res.tempFiles[index].size; //上传图片大小
							let maxSize = 1024 * 1024 * 2; //最大可上传2mb
							if(size > maxSize){
								uni.compressImage({
									src:image,
									quality:10,
									success:(result) => {
										that.imageSrc = result.tempFilePath;
										that.uploadImage(val);
									},
									fail:(result) => {
										console.log(result)
									}
								})
							}else{
								that.imageSrc = res.tempFilePaths[index];
								that.uploadImage(val);
							}
						})
						// #endif
					}
				})
			},
			async uploadImage(val){
				let that = this;
				uni.showLoading({ mask:true, title: this.$t('lang.shang_chu')});
				
				//app压缩图片
				// #ifdef APP-PLUS
				that.imageSrc = await compressImage(that.imageSrc);
				// #endif
				
				pathToBase64(that.imageSrc).then(base64 => {
					that.$store.dispatch('setMaterial', {
						file: {
							content: base64
						},
						type: val
					}).then(data => {
						if (data.status == 'success') {
							uni.hideLoading();
							that.imageList.push(data.data[0]);
						}else{
							uni.showToast({
								title:this.$t('lang.shang_cuo'),
								icon:'none'
							});
						}
					})
				}).catch(error => {
					console.error(error, 5);
				});
				// that.imageSrc.forEach(file=>{
				// 	pathToBase64(file).then(base64 =>{
				// 		that.getMaterial(base64)
				// 	})
				// });
			},
			getMaterial(base64){
				let that = this
				this.$store.dispatch('setMaterial',{
					file:{
						content:base64
					},
					type:'dange'
				}).then(data=>{
					if(data.status == 'success'){
						uni.hideLoading();
						that.imageList.push(data.data[0]);
					}else{
						uni.showToast({
							title:this.$t('lang.shang_cuo'),
							icon:'none'
						});
					}
				})
			},
			previewImage: function(e) {
				var current = e.target.dataset.src
				uni.previewImage({
					current: current,
					urls: this.imageList
				})
			},
			deteleImg(index){
				let that = this
				
				uni.showModal({
					title:'',
					content:this.$t('lang.delete_nine_pic'),
					success: (res) => {
						if(res.confirm){
							that.imageList.splice(index, 1)
						}
					}
				})
			},
			changeRank(e){
				this.rank = e.value
			},
			btnSubmit(){
				if (this.isAddEvaluate == 1) this.rank = this.commentInfo.comment_rank;
				if(this.rank == 0){
					uni.showToast({
						title:this.$t('lang.fill_in_comment_rank'),
						icon:'none'
					});
					
					return;
				} else if(this.textarea == ''){
					uni.showToast({
						title:this.$t('lang.comment_not_null'),
						icon:'none'
					});
					
					return;
				} else{
					this.$store.dispatch('setAddgoodscomment',{
						id:this.commentInfo.goods_id,
						order_id:this.commentInfo.order_id,
						rec_id:this.commentInfo.rec_id,
						type:this.type,
						rank:this.rank,
						server:this.server,
						tag: this.tagList,
						is_add_evaluate: this.isAddEvaluate,
						desc_rank: this.satisfactionList[0].rank,
						service_rank: this.satisfactionList[1].rank,
						delivery_rank: this.satisfactionList[2].rank,
						sender_rank: this.satisfactionList[3].rank,
						comment_id: this.commentInfo.comment_id,
						content:this.textarea,
						delivery:this.delivery,
						pic:this.imageList
					}).then(res=>{
						if(res.data.error == 0){
							uni.showToast({
								title:this.$t('lang.comment_success'),
								icon:'success'
							});
							
							setTimeout(() => {
								uni.reLaunch({
									url:'../comment/comment?have= 1'
								});
							}, 2000)
						}else{
							uni.showToast({
								title:this.$t('lang.comment_fail'),
								icon:'none'
							});
						}
					})
				}
			}
		},
		onLoad(e){
			this.isAddEvaluate = e.type || 0;
			this.$store.dispatch('setAddcomment',{
				rec_id:e.id,
				is_add_evaluate: this.isAddEvaluate
			})
		}
	}
</script>

<style lang="scss" scoped>
.comment-form{ position: relative;}
.comment-form .commont-hd{ background: #FFFFFF; padding: 20upx; display: flex; flex-direction: row; align-items: center; border-bottom: 1px solid #f6f6f9;}
.comment-form .commont-hd text{ color: #999999; font-size: 30upx; margin-right: 15upx;}
.comment-form .commont-bd{ background: #FFFFFF; padding: 30upx 20upx;}
.comment-form .commont-bd textarea{ width: 100%;}
.comment-form .commont-ft{ background: #FFFFFF; margin-top: 20upx; padding: 20upx;}
.comment-form .commont-ft .uni-uploader-head{ padding: 0 10upx;}
.comment-form .commont-ft .uni-uploader-head .uni-uploader-title{ font-size: 30upx; color: #999999;}

.btn-bar{ margin: 40upx 30upx 0;}

.uni-uploader__file{ position: relative;}
.uni-uploader__file .uni-icon{ position: absolute; top: 0; right: 0;}



.comment_detail_content {
	padding-bottom: 120rpx;
	.imgthumb {
		display: flex;
		padding: 20rpx;
		margin: 20rpx 0;
		border-radius: 20rpx;
		background-color: #fff;
		.goods_thumb {
			width: 140rpx;
			height: 140rpx;
		}
		.imgthumb_wrap_left {
			flex: auto;
			margin-left: 20rpx;
		}
		.rate_wrap {
			margin-top: 10rpx;
			line-height: 1;
			.icon-collection-alt {
				margin-right: 26rpx;
				line-height: 1;
				color: #DDD;
			}
			.color_red {
				color: #E93B3D;
			}
		}
	}
	.comment_module {
		overflow: hidden;
		border-radius: 20rpx;
		margin-bottom: 20rpx;
		background-color: #fff;
		.header {
			font-size: 30rpx;
			padding: 20rpx;
			background-color: #FBFBFB;
		}
		.tag_list {
			display: flex;
			flex-wrap: wrap;
			padding: 40rpx 20rpx;
			font-size: 30rpx;
			.tag_item {
				padding: 0 40rpx;
				height: 64rpx;
				line-height: 64rpx;
				border-radius: 32rpx;
				margin-right: 40rpx;
				background-color: #f4f4f4;
			}
			.active_tag {
				color: #E93B3D;
				background-color: #FDF0EF;
			}
		}
		.input_wrap {
			display: flex;
			flex-direction: column;
			padding: 20rpx;
			.text_area {
				flex: auto;
				width: auto;
				height: 160rpx;
			}
			.comment_length {
				color: #999;
				text-align: right;
				text {
					color: #E93B3D;
				}
			}
		}
		.add_img {
			display: flex;
			flex-wrap: wrap;
			padding: 10rpx;
			.img_box {
				overflow: hidden;
				position: relative;
				width: 160rpx;
				height: 160rpx;
				border-radius: 12rpx;
				margin: 10rpx;
				image {
					width: 100%;
					height: 100%;
				}
				.iconfont {
					position: absolute;
					top: 10rpx;
					right: 10rpx;
					line-height: 1;
					color: #E93B3D;
				}
			}
			.add_btn {
				flex: auto;
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
				height: 160rpx;
				border-radius: 12rpx;
				border: none;
				margin: 10rpx;
				box-shadow: 0 4rpx 16rpx #ddd;
				.iconfont {
					font-size: 40rpx;
					font-weight: bold;
					color: #999;
				}
				.add_pic {
					color: #999;
				}
			}
			.has_img {
				flex: none;
				width: 160rpx;
			}
		}
	}
	.satisfaction_module {
		overflow: hidden;
		border-radius: 20rpx;
		margin-bottom: 20rpx;
		background-color: #fff;
		.header {
			display: flex;
			justify-content: space-between;
			padding: 20rpx;
			.header_left {
				font-size: 30rpx;
			}
			.header_right {
				color: #999;
			}
		}
		.rate_list {
			.rate_item {
				display: flex;
				align-items: center;
				padding: 20rpx;
			}
			.rate_label {
				min-width: 240rpx;
			}
			.rate_value {
				display: flex;
				align-items: center;
				.iconfont {
					margin-right: 26rpx;
					color: #DDD;
				}
				.color_red {
					color: #E93B3D;
				}
			}
		}
	}
	.floor_bar {
		position: fixed;
		bottom: 0;
		left: 0;
		display: flex;
		justify-content: flex-end;
		align-items: center;
		width: 100%;
		height: 100rpx;
		// padding: 0 30rpx;
		padding-bottom: 0;
		padding-bottom: constant(safe-area-inset-bottom);  
		padding-bottom: env(safe-area-inset-bottom);  
		background-color: #fff;
		&::after {
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
			border: 0 solid #e4e7ed;
			border-top-width: 1px;
			z-index: 2;
		}
		.btn {
			padding: 0 30rpx;
			height: 64rpx;
			line-height: 64rpx;
			border-radius: 32rpx;
			margin-right: 30rpx;
			font-size: 30rpx;
			color: #fff;
			background-color: #E93B3D;
		}
	}
}
</style>
