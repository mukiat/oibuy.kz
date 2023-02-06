<template>
	<view class="main">
		<code-elf-guide v-if="guidePages"></code-elf-guide>
		
		<uni-popups ref="privacy" type="center">
			<view class="privacy-content">
				<view class="privacy-content-title">欢迎进入{{ name }}</view>
				<scroll-view scroll-y="true">
					<rich-text :nodes="privacyArticle.content"></rich-text>
				</scroll-view>
				<view class="privacy-footer">
					<view class="link" @click="clickHref">《隐私政策》</view>
					<view class="confrim-btn">
						<view class="b-btn" @click="quit">不同意</view>
						<view class="b-btn b-primary" @click="submit">同意并继续</view>
					</view>
				</view>
			</view>
		</uni-popups>
	</view>
</template>

<script>
	import codeElfGuide from '@/components/code-elf-guide/code-elf-guide.vue'
	import uniPopups from '@/components/uni-popup/uni-popup.vue';
	export default {
		components: {
		    codeElfGuide,
			uniPopups
		},
		data() {
			return {
				guidePages: false,
				privacyShow: false,
				privacyArticle:'',
				name:getApp().globalData.mpName,
				configPrivacy:''
			}
		},
		onLoad(){
			// #ifdef APP-PLUS
			this.loadExecution()
			// #endif
			
			// #ifdef MP-WEIXIN
			uni.switchTab({
			    url: '/pages/index/index'
			});
			// #endif
		},
		methods: {
			async loadExecution(){
				/**
				 * 获取本地存储中launchFlag的值
				 * 若存在，说明不是首次启动，直接进入首页；
				 * 若不存在，说明是首次启动，进入引导页；
				 */
				try {
					//隐私声明和引导广告
					let roles,configData,privacy;
					
					//接口返回config
					configData = await this.$store.dispatch('setShopConfig',{type:true});
					
					//后台设置隐私协议文章id和版本号
					this.configPrivacy = configData.data.privacy;
					
					//获取本地存储隐私协议
					privacy = uni.getStorageSync('privacy') ? JSON.parse(uni.getStorageSync('privacy')) : '';
					
					const {data, status } = await this.$store.dispatch('setArticleDetail2',{id:this.configPrivacy.article_id});

					this.privacyArticle = data;

					// #ifdef APP-PLUS
					if(privacy.article_id == this.configPrivacy.article_id && privacy.version_code == this.configPrivacy.version_code){
						this.guidePages = true
					}else{
						if(!plus.runtime.isAgreePrivacy()){
							this.$refs['privacy'].open();
							this.guidePages = false
							return
						}
					}
					// #endif
					
					// 获取本地存储中launchFlag标识
					const value = uni.getStorageSync('launchFlag');
					
					if (value) {
						// launchFlag=true直接跳转到首页
						uni.switchTab({
							url: '/pages/index/index'
						});
					} else {
						// launchFlag!=true显示引导页
						this.guidePages = true
					}
				} catch(e) { 
					// error 
					uni.setStorage({ 
						key: 'launchFlag', 
						data: true, 
						success: function () {
							console.log('error时存储launchFlag');
						} 
					});
					
					this.guidePages = true
				}
				return;
				uni.switchTab({
				    url: '/pages/index/index'
				});
			},
			// 同意隐私
			submit(){
				plus.runtime.agreePrivacy();
				// this.privacyShow = false;
				this.$refs['privacy'].close();
				this.guidePages = true
				
				uni.setStorageSync('privacy',JSON.stringify(this.configPrivacy));
			},
			// 退出应用
			quit(){
				let that = this;
				
				//that.privacyShow = false;
				this.$refs['privacy'].close();
				
				uni.showModal({
					content: '您需要同意「网站隐私政策」,才能继续使用我们服务',
					cancelText:'狠心离去',
					confirmText:'同意授权',
					success: function(res) {
						if (res.confirm) {
							that.submit();
						}else{
							plus.runtime.disagreePrivacy();
							
							if(uni.getSystemInfoSync().platform == 'ios'){
								plus.ios.import("UIApplication").sharedApplication().performSelector("exit");
							}
						}
					}
				})
			},
			clickHref(){
				uni.navigateTo({
					url:`/pagesC/article/detail/detail?id=${this.privacyArticle.article_id}&show=false`
				})
			}
		}
	}
</script>

<style lang="scss">
	page,.main{
		width: 100%;
		height: 100%;
	}
	
	.privacy-content{
		padding: 20px;
		width: 70%;
		background: #FFFFFF;
		margin: 0 auto;
		
		.privacy-content-title{
			height: 70px;
			line-height: 70px;
			font-size: 24px;
			text-align: center;
			font-weight: 600;
			width: 100%;
		}
		
		scroll-view{
			height: 300px;
			word-wrap: break-word;
			white-space: normal;
			word-break: break-all;
			font-size: 25rpx;
			line-height: 1.8;
		}
		
		.privacy-footer{
			text-align: center;
			height: 90px;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			align-items: space-between;
			
			.link{
				font-size: 25rpx;
				color: #4e9deb;
				height: 30px;
				line-height: 30px;
				margin-top: 10px;
			}
			
			.confrim-btn{
				display: flex;
				justify-content: space-between;
				
				.b-btn{
					width: 50%;
					font-size: 30rpx;
					height: 80rpx;
					line-height: 78rpx;
					color: #606266;
					border:1px solid #c0c4cc;
					background-color: #ffffff;
					
					&.b-primary{
						color: #ffffff;
						border-color: #f92028;
						background-color: #f92028;
					}
					
					&:first-child{
						margin-right: 10px;
						border-radius: 0;
					}
					&:last-child{
						margin-left: 10px;
						border-radius: 0;
					}
				}
			}
		}
	}
</style>
