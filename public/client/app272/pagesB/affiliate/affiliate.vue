<template>
	<view class="container">
		<view class="affiliate-cont-box" v-if="share && share.on > 0">
			<view class="header" @longpress="downloadImg">
				<image :src="shareImg" v-if="shareImg" class="img"></image>
			</view>
			<view class="affiliate-warp">
				<view class="title">{{$t('lang.activity_content')}}：</view>
				<rich-text :nodes="share.config.separate_desc"></rich-text>
			</view>
		</view>
		<view v-else>
			<dsc-not-content :isSpan="false">
				<block slot="spanCon">{{$t('lang.activity_cont')}}<br>{{$t('lang.activity_admin')}}</block>
			</dsc-not-content>
		</view>

		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
	</view>
</template>

<script>
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';

	import universal from '@/common/mixins/universal.js';
	export default {
		mixins:[universal],
		data() {
			return {
				affdb:[],
				all_res:[],
				config_info:[],
				share:{},
				shareImg:'',
				dscLoading:true,
			};
		},
		components:{
			dscCommonNav,
			dscNotContent
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/affiliate/affiliate'
			}
		},
		onLoad(){
			let platform = 'MP-WEIXIN'
			// #ifdef APP-PLUS
			platform = 'H5'
			// #endif
			
			uni.request({
				url:this.websiteUrl + '/api/invite',
				method:'GET',
				data:{
					page:1,
					size:10,
					platform:platform
				},
				header: {
					'Content-Type': 'application/json',
					'token': uni.getStorageSync('token'),
					'X-Client-Hash':uni.getStorageSync('client_hash')
				},
				success:(res) => {
					this.affdb = res.data.data.affdb
					this.all_res = res.data.data.all_res
					this.config_info = res.data.data.config_info
					this.share = res.data.data.share
					this.shareImg = res.data.data.img_src
				}
			})
		},
		methods:{
			downloadImg(){
				const that = this
				uni.downloadFile({
					url:that.shareImg,
					success: (res) => {
						uni.showActionSheet({
							itemList: ['保存图片'],
							success: (e) => {
								if(e.tapIndex === 0){
									uni.saveImageToPhotosAlbum({
										filePath:res.tempFilePath,
										success:function(){
											uni.showToast({
												title: that.$t('lang.picture_saved_success'),
												icon: 'none',
												duration: 2000
											})
										}
									})
								}
							}
						})
					}
				})
			}
		},
		watch:{
			share(){
				this.dscLoading = false
			}
		}
	}
</script>

<style>
.affiliate-cont-box{ background-color: #FFFFFF; padding-bottom: 30px;}
.affiliate-cont-box .header{ width: 750upx; height: 1177upx; position: relative;}
.affiliate-cont-box .header .share-ewm-box{ position: absolute; top: 80%; left: 31%; width: 48%;}
.affiliate-cont-box .header .share-ewm-box text{ border:1px solid #ec5051; border-radius: 20upx; color: #f92028; text-align: center; padding: 5upx 20upx; font-size: 25upx;}

.affiliate-warp{ padding: 20upx;}
.affiliate-warp .title{ padding: 10upx 0; font-size: 30upx;}
</style>
