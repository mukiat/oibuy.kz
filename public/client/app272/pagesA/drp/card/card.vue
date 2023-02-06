<template>
	<view class="card">
		<view class="img-common" @longpress="downloadImg">
			<image :src="cardData" mode="widthFix"></image>
			<view class="btn-bar btn-bar-radius" v-if="isShow" style="margin-top: -2px;">
				<button class="btn btn-red mlr50" @click="downloadImg">{{$t('lang.save_picture')}}</button>
				<button class="btn btn-red mlr50"  open-type="share">{{$t('lang.forward_link')}}</button>
			</view>
		</view>

		<dsc-common-nav>
			<navigator url="../drp" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.my_drp')}}</text>
			</navigator>
		</dsc-common-nav>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';

	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';

	export default {
		data() {
			return {
				cardData:''
			}
		},
		components:{
			uniIcons,
			dscNotContent,
			dscCommonNav,
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesA/drp/register/register?shop_id='+this.shop_id+"&parent_id="+this.user_id
			}
		},
		onLoad() {
			this.load();
		},
		computed: {
		},
		methods: {
			load(){
				let platform = 'H5';
				
				// #ifdef MP-WEIXIN
				platform = 'MP-WEIXIN'
				// #endif
				
				uni.request({
					url: this.websiteUrl + '/api/drp/user_card',
					data:{
						platform:platform
					},
					method: 'GET',
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						let data = res.data.data;
						this.cardData = data.outImg;
					}
				})
			},
			preview(){
				uni.previewImage({
					current:1,
					urls:this.cardData,
					longPressActions:{
						itemList:[this.$t('lang.save_picture'),this.$t('lang.recognition_qr_code'),this.$t('lang.collect')],
						success:(res)=>{
							if(res.tapIndex == 1){
								uni.scanCode({
									scanType:['qrCode'],
									success:(res)=>{
										console.log(res)
									}
								})
							}
						}
					}
				})
			},
			downloadImg(){
				const that = this
				uni.downloadFile({
					url:that.cardData,
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
	}
</script>

<style scoped>
.card{ height: 100vh; background:linear-gradient(90deg,rgba(236,36,91,1),rgba(212,0,52,1)); }
.img-common,.img-common image { width: 100%;}
</style>
