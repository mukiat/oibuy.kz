<template>
	<view class="team-wait fixed-bottom-padding">
		<block v-if="teamInfo">
			<view class="header">
				<view class="left">
					<image :src="teamInfo.user_picture" mode="widthFix" v-if="teamInfo.user_picture"></image>
					<image :src="teamInfo.user_picture" mode="widthFix" v-else></image>
				</view>
				<view class="right">
					<block v-if="teamInfo.status == 0">
						<view class="tit">{{$t('lang.team_propmt_2')}}</view>
						<view class="subtit">{{$t('lang.only')}}<text class="uni-red">{{teamInfo.surplus}}</text>{{$t('lang.team_propmt_3')}}。</view>
					</block>
					<block v-else-if="teamInfo.status == 1">
						<view class="tit">{{$t('lang.team_propmt_4')}}</view>
						<view class="subtit">{{$t('lang.team_propmt_5')}}</view>
					</block>
					<block v-else>
						<view class="tit">{{$t('lang.team_propmt_6')}}</view>
						<view class="subtit">{{$t('lang.only')}}<text class="uni-red">{{teamInfo.surplus}}</text>{{$t('lang.team_propmt_7')}}</view>
					</block>
				</view>
			</view>
			<view class="goods-list">
				<view class="goods-item" @click="detailClick(teamInfo.goods_id)">
					<view class="goods-left"><image :src="teamInfo.goods_thumb" class="img" /></view>
					<view class="goods-right">
						<view class="goods-name twolist-hidden">{{teamInfo.goods_name}}</view>
						<view class="plan-box"><view class="shop-price">{{teamInfo.team_num}}{{$t('lang.one_group')}}</view></view>
						<view class="plan-box">
							<view class="price">{{teamInfo.team_price}}</view>
						</view>
					</view>
				</view>
			</view>
			<view class="time-content">
				<view class="time-header">
					<block v-if="teamInfo.status == 0">
						<text>{{$t('lang.residue')}}</text>
						<view class="time"><uni-countdown fontColor="#FFFFFF" bgrColor="#000000" :timer="dateTime" v-if="dateTime"></uni-countdown></view>
						<text>{{$t('lang.end')}}</text>
					</block>
					<block v-else-if="teamInfo.status == 1"><div class="title-hrbg-team success">{{$t('lang.team_success')}}</div></block>
					<block v-else><div class="title-hrbg-team error">{{$t('lang.team_fail')}}</div></block>
					<view class="hr"></view>
				</view>
				<view class="time-warp">
					<view class="picture">
						<scroll-view class="scroll-view" scroll-x="true" scroll-left="0">
						<view class="col-box" v-for="(item,index) in teamUser" :key="index">
							<view class="tag-box" v-if="index == 0">{{$t('lang.regimental_commander')}}</view>
							<view class="img-box"><image :src="item.user_picture" mode="widthFix"></image></view>
						</view>
						</scroll-view>
					</view>
					<view class="progress">
						<view class="progress-with-pivot" :style="{width:pivotText}">
							<text class="progress-pivot" :style="{'left': teamInfo.bar < 10 ? '0px' : ''}">{{pivotText}}</text>
						</view>
					</view>
				</view>
			</view>
			<view class="uni-card uni-card-not">
				<view class="uni-list">
					<view class="uni-list-cell uni-list-cell-title" hover-class="uni-list-cell-hover" @click="handleRule">
						<view class="uni-list-cell-navigate">
							<text class="title">{{$t('lang.team_rule')}}</text>
							<view class="steps">
								<view class="item" v-for="(item,index) in steps" :key="index">
									<view class="num">{{index+1}}</view>
									<view class="tit">{{item.title}}</view>
									<view class="n-list-xian"></view>
								</view>
							</view>
							<uni-icons type="arrowdown" size="24" color="#777777"></uni-icons>
						</view>
					</view>
				</view>
			</view>
			<view class="btn-bar">
				<button class="btn btn-org" @click="teamMore">{{$t('lang.team_more')}}</button>
				<button class="btn btn-red" @click="goTeam" v-if="teamInfo.status > 0">{{$t('lang.up_group')}}</button>
				<block v-else>
					<button class="btn btn-red" @tap="mpShare">{{$t('lang.invite_friends_join_team')}}</button>
				</block>
			</view>
		</block>
		<dsc-common-nav></dsc-common-nav>
		
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
		
		<uni-popup :show="ruleShow" type="bottom" v-on:hidePopup="handleClose('rule')">
			<view class="title">
				<view class="txt">{{$t('lang.team_rule')}}</view>
				<uni-icons type="closeempty" size="36" color="#999999" @click="handleClose('rule')"></uni-icons>
			</view>
			<view class="rule">
				<view>{{$t('lang.team_rule_con_1')}}</view>
				<view>{{$t('lang.team_rule_con_2')}}</view>
				<view>{{$t('lang.team_rule_con_3')}}</view>
				<view>{{$t('lang.team_rule_con_4')}}</view>
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
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import uniTag from "@/components/uni-tag.vue";
	import uniPopup from '@/components/uni-popup.vue';
	import uniPopups from '@/components/uni-popup/uni-popup.vue';
	
	import uniCountdown from "@/components/uni-countdown.vue";
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	
	import universal from '@/common/mixins/universal.js';
	
	export default {
		mixins:[universal],
		data() {
			return {
				user_id:'',
				team_id:'',
				steps:[
					{title: this.$t('lang.team_rule_tit_1')},
					{title: this.$t('lang.team_rule_tit_2')},
					{title: this.$t('lang.team_rule_tit_3')},
					{title: this.$t('lang.team_rule_tit_4')}
				],
				ruleShow:false,
				shareState: true,
				dscLoading:true,
				status:0,
				shareImgShow:false,
				mpShareImg: '',
			}
		},
		components: {
			uniIcons,
			uniTag,
			uniPopup,
			uniPopups,
			uniCountdown,
			dscNotContent,
			dscCommonNav,
		},
		computed: {
			...mapState({
				teamWaitData: state => state.team.teamWaitData,
			}),
			//拼团人员
            teamUser(){
                return this.teamWaitData.teamUser
            },
            //拼团信息
            teamInfo(){
                return this.teamWaitData.team_info
            },
            pivotText(){
                return this.teamInfo.bar + '%'
            },
			dateTime(){
				let dataTime = this.teamInfo.end_time
				if(dataTime != ''){
					return this.$formatDateTime(dataTime)
				}
			},
        },
		methods: {
			loadWait(){
				this.$store.dispatch('setTeamWait',{
				    team_id: this.team_id,
				    user_id: this.user_id,
				});
			},
			handleRule(){
				this.ruleShow = true
			},
			//关闭Popup
			handleClose(val){
				if(val == 'rule'){
			        this.ruleShow = false
			    }else if(val == 'base'){
					this.showBase = false
				}
			},
			detailClick(goods_id){
				uni.navigateTo({
					url:'/pagesA/team/detail/detail?goods_id='+goods_id+'&team_id=0'
				})
			},
			teamMore(){
				uni.navigateTo({
					url:'/pagesA/team/rank/rank'
				})
			},
			goTeam(){
				uni.navigateTo({
					url:'/pagesA/team/team'
				})
			},
			appShare(){
				let shareInfo = {
					href:this.$websiteUrl + 'team/wait?team_id=' + this.team_id + '&user_id=' + this.teamInfo.user_id,
					title: this.$t('lang.team_propmt_8'),
					summary:this.teamInfo.team_desc,
					imageUrl:this.teamInfo.goods_thumb
				};
				
				this.shareInfo(shareInfo, 'poster')
			},
			mpShare() {
				this.onGoodsShare();
			},
			onGoodsShare() {
				if (this.$isLogin()) {
					uni.showLoading({ title: this.$t('lang.loading') });
					let price = this.teamInfo.team_price;
					let o = {}

					// #ifdef MP-WEIXIN
					o = {
						goods_id: this.teamInfo.goods_id,
						ru_id: 0,
						price: price,
						share_type: 0,
						type: 0,
						platform: 'MP-WEIXIN',
						extension_code:'team',
						code_url:'pagesA/team/detail/detail',
						scene:`${this.teamInfo.goods_id}.${this.team_id}`,
						thumb:this.teamInfo.goods_img,
					}
					// #endif
					
					// #ifdef APP-PLUS
					o = {
						goods_id: this.teamInfo.goods_id,
						price: price,
						share_type: 0,
						platform: 'APP',
						extension_code:'team',
						code_url:`${this.$websiteUrl}team/detail?goods_id=${this.teamInfo.goods_id}&team_id=${this.team_id}`,
						thumb:this.teamInfo.goods_img,
					}
					// #endif
					console.log(`${this.$websiteUrl}team/detail?goods_id=${this.teamInfo.goods_id}&team_id=${this.team_id}`)
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
		},
		onShareAppMessage(res){
			return {
				title: this.$t('lang.team_propmt_8'),
				path: '/pagesA/team/detail/detail?goods_id='+this.teamInfo.goods_id+'&team_id='+this.team_id
			}
		},
		onLoad(e){
			this.user_id = e.user_id;
			this.team_id = e.team_id;
			
			this.loadWait();
		},
		watch:{
			teamWaitData(){
				this.dscLoading = false
			},
			sharePoster() {
				if (this.sharePoster) {
					this.$refs.popupPoster.open();
				}
			}
		}
	}
</script>

<style scoped>
.cont-box .title .goods-price{ padding: 0;}

.goods_outer{ padding: 0; margin-top: 10upx;}

.uni-list-cell-navigate{ justify-content: flex-start; }
.uni-list-cell-navigate .title{ min-width: 100upx; color: #999999; margin-right: 15upx;}
.uni-list-cell .iconfont{ color: #f92028; margin-right: 10upx;}
.uni-list-cell-title .uni-list-cell-navigate{ flex-direction: column; }
.uni-list-cell-title .uni-list-cell-navigate.uni-navigate-right:after{ top: 35%;}
.uni-list-cell-title .uni-list-cell-navigate .title{ width: 100%; color: #333333; font-size: 30upx; margin-bottom: 20upx;}
.uni-list-cell-title .uni-list-cell-navigate .value{ display: flex; align-items: center; color: #999999;}

.steps{ display: flex; flex-direction: row; width: 100%; justify-content: center; align-items: center;}
.steps .item{ position: relative; width: 25%; color: #777; text-align: center; margin: 20upx 0;}
.steps .item .num{ position: relative; width: 40upx; height: 40upx; line-height: 40upx; font-size: 24upx; border-radius: 50%; border:1px solid #e7e8ef; margin: 0 auto; z-index: 2; background: #FFFFFF;}
.steps .item .tit{ font-size: 24upx; margin-top: 20upx;}
.steps .item .n-list-xian{ position: absolute; border-top: 1px solid #e7e8ef; height: 1px; z-index: 1; width: 100%; top: 22%;}
.steps .item:first-child .n-list-xian{ left: 50%; width: 60%; }
.steps .item:last-child .n-list-xian{ right: 50%; width: 60%; }

.rule{ padding: 20upx; border-top: 1px solid #e7e8ef; }
.rule view{ text-align: left; font-size: 25upx; color: #999;}

.team-wait .header{ display: flex; flex-direction: row; justify-content:flex-start; align-items: center; padding: 20upx; background: #FFFFFF;}
.team-wait .header .left{ width: 100upx; height: 100upx;}
.team-wait .header .left image{ width: 100%;}
.team-wait .header .right{ flex: 1 1 0%; margin-left: 20upx;}
.team-wait .header .right .tit{ font-size: 32upx;}
.team-wait .header .right .subtit{ font-size: 26upx; color: #888;}

.time-content{ background: #FFFFFF; }
.time-content .time-header{ display: flex; flex-direction: row; justify-content: center; color: #888888; padding: 40upx 0; position: relative;}
.time-content .time-header text{ padding: 0 10upx; position: relative; z-index: 2; background: #FFFFFF;}
.time-content .time-header .time{ position: relative; z-index: 2; background: #FFFFFF; }
.time-content .time-header .hr{ position: absolute; height: 1px; border-bottom:1px solid #F4F4F4; top: 50%; left: 0; right: 0; z-index: 1;}
.time-content .title-hrbg-team{ background: #FFFFFF; padding: 0 10upx; z-index: 2; }
.time-content .title-hrbg-team.success{ color: #09BB07;}
.time-content .title-hrbg-team.error{ color: #ec5151;}

.time-warp{ padding: 20upx; }
.time-warp .picture{ display: flex; flex-direction: row;}
.time-warp .picture .col-box{ width: 16.6666667%; position: relative; display: inline-block; padding-right: 20upx; box-sizing: border-box;}
.time-warp .picture .col-box .img-box{ width: 50px; height: 50px; border:1px solid #ccc; border-radius: 50%; overflow: hidden; font-size: 0;}
.time-warp .picture .col-box .img-box image{ width: 100%; height: 100%; }
.time-warp .picture .col-box .tag-box{ position: absolute; background: #ec5151; border-radius: 10upx; color: #FFFFFF; padding: 0 10upx; font-size: 24upx; right: -20upx; z-index: 10; }
.time-warp .progress{ position: relative; height: 10upx; border-radius: 5upx; background: #e5e5e5; margin-top: 30upx;}
.time-warp .progress .progress-with-pivot{ position: absolute; left: 0; height: 100%; width: 0; background: #EC5051; border-radius: 5upx;}
.time-warp .progress .progress-pivot{ position: absolute; right: -20px; width: 60upx; height: 40upx; background: #ec5151; border-radius: 20upx; color: #FFFFFF; padding: 0 10upx; font-size: 24upx; top: -18upx; text-align: center;}

.scroll-view { white-space: nowrap;width: 100%; overflow: hidden;}

.btn-bar{position:fixed; height: 100upx; bottom: 0; left: 0; right: 0; z-index: 99; background: #FFFFFF;}

/* 小程序分享  start*/
.show-popup-shareImg /deep/ .uni-popup-bottom{ height: 80%; }
/* 小程序分享 end*/
</style>
