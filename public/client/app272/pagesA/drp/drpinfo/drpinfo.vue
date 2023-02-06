<template>
	<view class="drp-info tabbar-padding-bottom">
		<block v-if="drpdata.error == 0 || drpdata.audit == 1">
		<view class="drp-warp">
			<view class="tip" v-if="drpdata.expiry && drpdata.expiry.expiry_status > 0 && drpdata.expiry.card_expiry_status == 1">{{drpdata.expiry.expiry_time_notice}}</view>
			<view class="tip" v-if="drpdata.expiry.card_expiry_status != 1">{{drpdata.expiry.card_status_notice}}</view>
			<view class="header" :style="{'background-image':'url(' + drpInfoBg + ')'}">
				<view class="header-top">
					<view class="header-img" @click="drpHref"><image :src="drpdata.shop_info.user_picture" class="img" /></view>
					<view class="header-right">
						<view class="tit">{{drpdata.shop_info.shop_name}}</view>

                        <block v-if="drpdata.expiry.expiry_status == 1">
                            <view class="time" >{{$t('lang.membership_of_validity')}}：{{$t('lang.have_expired')}}</view>
                        </block>
                        <block v-else>
                            <view class="time" v-if="drpdata.expiry.expiry_type == 'forever'">{{$t('lang.membership_of_validity')}}：{{$t('lang.permanence')}}</view>
                            <view class="time" v-else-if="drpdata.expiry.expiry_type == 'days'">{{$t('lang.membership_of_validity')}}：{{drpdata.expiry.expiry_time_format}}</view>
                            <view class="time" v-else-if="drpdata.expiry.expiry_type == 'timespan'">{{$t('lang.membership_of_validity')}}：{{drpdata.expiry.expiry_time_format}}</view>
                        </block>

                        <view class="rank">
							<view class="vip">
								<view class="icon"><image src="../../../static/vip/icon-vip.png" class="img" mode="widthFix"></image></view>
								<text>{{drpdata.user_rank}}</text>
							</view>
							<view class="more" @click="drpApplyHref">{{$t('lang.detail')}}<uni-icons type="forward" size="18" color="#666666"></uni-icons></view>
						</view>
					</view>
				</view>
				<view class="header-bottom bor">
					<view class="item" @click="drpRenew" v-if="drpdata.expiry.expiry_status != 0 && drpdata.expiry.card_expiry_status == 1">{{$t('lang.renew')}}</view>
					<view class="item" @click="drpChange" v-if="drpdata.expiry.expiry_status == 0 || drpdata.expiry.expiry_status == 2">{{$t('lang.change')}}</view>
					<view class="item" v-if="drpdata.expiry.expiry_status == 1" @click="applyAgain">{{$t('lang.re_purchase')}}</view>
				</view>
			</view>
			<view class="section protection" v-if="card && protectionList.length > 0">
				<view class="tit">
					<view>{{$t('lang.enjoy_equity')}}</view>
					<view class="more" @click="protectionHref(0)">{{$t('lang.more')}}<uni-icons type="forward" size="18" color="#805223"></uni-icons></view>
				</view>
				<view class="value">
					<view class="item-list" v-for="(item,index) in protectionList" :key="index" @click="protectionHref(index)">
						<view class="icon"><image :src="item.icon" class="img" /></view>
						<view class="text">{{item.name}}</view>
					</view>
				</view>
			</view>
			<view class="section section-money">
				<view class="tit">
					<view>{{pageDrpInfo.my_asset ? pageDrpInfo.my_asset : $t('lang.my_assets')}}</view>
					<view class="more" @click="depositLog">{{$t('lang.deposit_log')}}<uni-icons type="forward" size="18" color="#805223"></uni-icons></view>
				</view>
				<view class="items">
					<view class="item" @click="withdraw">
						<text class="txt">{{drpdata.surplus_amount}}</text>
						<text class="span">{{pageDrpInfo.shop_money ? pageDrpInfo.shop_money : $t('lang.deposit_brokerage')}}</text>
					</view>
					<view class="item">
						<text class="txt">{{drpdata.totals}}</text>
						<text class="span">{{pageDrpInfo.total_drp_log_money ? pageDrpInfo.total_drp_log_money : $t('lang.drp_totals')}}</text>
					</view>
					<view class="item">
						<text class="txt">{{drpdata.today_total}}</text>
						<text class="span">{{pageDrpInfo.today_drp_log_money ? pageDrpInfo.today_drp_log_money : $t('lang.today_income')}}</text>
					</view>
					<view class="item">
						<text class="txt">{{drpdata.total_amount}}</text>
						<text class="span">{{pageDrpInfo.total_drp_order_amount ? pageDrpInfo.total_drp_order_amount : $t('lang.drp_total_amount')}}</text>
					</view>
				</view>
			</view>
			<view class="section section-money">
				<view class="tit">
					<view>{{pageDrpInfo.order_card ? pageDrpInfo.order_card : $t('lang.rec_card')}}</view>
					<navigator url="../order/order?type=card" class="more" hover-class="none">{{$t('lang.detailed')}}<uni-icons type="forward" size="18" color="#805223"></uni-icons></navigator>
				</view>
				<view class="items">
					<navigator :url="'../team/team?parent_id=' + drpdata.shop_info.user_id" class="item" hover-class="none">
						<text class="txt">{{drpdata.team_count}}</text>
						<text class="span">{{pageDrpInfo.order_card_total ? pageDrpInfo.order_card_total : $t('lang.card_total_number')}}</text>
					</navigator>
					<view class="item">
						<text class="txt">{{drpdata.card_total_amount}}</text>
						<text class="span">{{pageDrpInfo.card_total_amount ? pageDrpInfo.card_total_amount : $t('lang.drp_total_amount')}}</text>
					</view>
					<view class="item">
						<text class="txt">{{drpdata.card_today_money}}</text>
						<text class="span">{{pageDrpInfo.card_today_money ? pageDrpInfo.card_today_money : $t('lang.today_rewards')}}</text>
					</view>
					<view class="item">
						<text class="txt">{{drpdata.card_total_money}}</text>
						<text class="span">{{pageDrpInfo.card_total_money ? pageDrpInfo.card_total_money : $t('lang.cumulative_rewards')}}</text>
					</view>
				</view>
				<view class="invite_friends_button" @click="inviteFriends()">
					<text>{{pageDrpInfo.drp_card ? pageDrpInfo.drp_card : $t('lang.team_rule_tit_3')}}</text>
					<uni-icons type="arrowright" color="#805223" size="24"></uni-icons>
				</view>
			</view>
			<view class="section section-money">
				<view class="tit">
					<view>{{$t('lang.help_center')}}</view>
					<navigator url="/pagesB/help/help?type=drphelp" class="more" hover-class="none">{{$t('lang.more')}}<uni-icons type="forward" size="18" color="#805223"></uni-icons></navigator>
				</view>
				<view class="list-ul">
					<view class="li" v-for="(item,index) in drpdata.article_list" :key="index">
						<navigator :url="'/pagesC/article/detail/detail?id='+item.id" hover-class="none">{{item.title}}</navigator>
					</view>
				</view>
			</view>
		</view>
		<view class="team-box">
			<view class="tit">
				<icon class="row"></icon>
				<text>{{pageDrpInfo.drp_team ? pageDrpInfo.drp_team : $t('lang.my_team_alt')}}</text>
			</view>
			<view class="items">
				<navigator :url="'../team/team?parent_id=' + drpdata.shop_info.user_id" class="item item1" hover-class="none">
					<text class="num">{{drpdata.sum_count}}</text>
					<icon class="link"></icon>
					<text class="text">{{pageDrpInfo.sum_count ? pageDrpInfo.sum_count : $t('lang.user_total')}}</text>
				</navigator>
				<view class="item item2">
					<text class="num">{{drpdata.team_count}}</text>
					<icon class="link"></icon>
					<text class="text">{{pageDrpInfo.team_count ? pageDrpInfo.team_count : $t('lang.directly_user')}}</text>
				</view>
				<view class="item item3">
					<text class="num">{{drpdata.user_count}}</text>
					<icon class="link"></icon>
					<text class="text">{{pageDrpInfo.user_count ? pageDrpInfo.user_count : $t('lang.direct_referrals')}}</text>
				</view>
			</view>
		</view>
		<view class="nav-items">
			<navigator url="../order/order?type=card" class="item">
				<view class="icon"><image :src="imagePath.infoIcon1" class="img" mode="widthFix"></image></view>
				<text>{{pageDrpInfo.order_card_list ? pageDrpInfo.order_card_list : $t('lang.card_reward')}}</text>
			</navigator>
			<navigator url="../order/order?type=order" class="item">
				<view class="icon"><image :src="imagePath.infoIcon2" class="img" mode="widthFix"></image></view>
				<text>{{pageDrpInfo.order_list ? pageDrpInfo.order_list : $t('lang.sale_reward')}}</text>
			</navigator>
			<navigator url="../rank/rank" class="item">
				<view class="icon"><image :src="imagePath.infoIcon3" class="img" mode="widthFix"></image></view>
				<text>{{pageDrpInfo.drp_rank ? pageDrpInfo.drp_rank : $t('lang.rich_list')}}</text>
			</navigator>
			<navigator url="../drp" class="item">
				<view class="icon"><image :src="imagePath.infoIcon4" class="img" mode="widthFix"></image></view>
				<text>{{pageDrpInfo.drp_store ? pageDrpInfo.drp_store : $t('lang.my_drp')}}</text>
			</navigator>
		</view>
		<view class="adv" v-if="drpdata.banner && drpdata.banner.length > 0">
			<swiper class="swiper" :autoplay="autoplay" :interval="interval" :duration="duration">
				<swiper-item v-for="(item,index) in drpdata.banner" :key="index">
					<view class="swiper-item"><image :src="item.pic"></image></view>
				</swiper-item>
			</swiper>
		</view>
		</block>
		<block v-else>
			<view class="ectouch-notcont">
				<view class="img"><image src="../../../static/no_content.png" mode="widthFix"></image></view>
				<block v-if="viewStatus == 1">
					<block v-if="viewAudit == 0">
						<view class="cont">{{$t('lang.drp_status_propmt_1')}}<navigator url="/pages/user/user" hover-class="none" open-type="switchTab" class="uni-red">{{$t('lang.drp_return_member_center')}}</navigator></view>
					</block>
					<block v-if="viewAudit == 2">
						<view class="cont">{{$t('lang.drp_status_propmt_7')}}<navigator url="/pages/user/user" hover-class="none" open-type="switchTab" class="uni-red">{{$t('lang.drp_return_member_center')}}</navigator></view>
					</block>
				</block>
				<block v-if="viewStatus == 2">
					<view class="cont">{{$t('lang.drp_status_propmt_2')}}<navigator url="/pagesA/drp/register/register" hover-class="none" class="uni-red">{{$t('lang.to_buy')}}</navigator></view>
				</block>
				<block v-if="viewStatus == 3">
					<view class="cont">{{$t('lang.drp_status_propmt_8')}}<navigator url="/pages/user/user" hover-class="none" open-type="switchTab" class="uni-red">{{$t('lang.drp_return_member_center')}}</navigator></view>
				</block>
			</view>
		</block>

		<!--续费方式-->
		<uni-popup :show="renewShow" type="bottom" mode="fixed" v-on:hidePopup="renewClose">
			<view class="activity-popup">
				<view class="title">
					<view class="txt">{{$t('lang.fill_in_renew')}}</view>
					<uni-icons type="closeempty" size="36" color="#999999" @click="renewClose"></uni-icons>
				</view>
				<view class="not-content">
					<scroll-view :scroll-y="true" class="select-list">
						<view class="select-item" v-for="(item,index) in card.receive_value" :key="index" :class="{'active':renew_type == item.type}" @click="renew_method_select(item.type)">
							<view class="txt" v-if="item.type == 'integral'">{{$t('lang.drp_apply_title_1')}}</view>
							<view class="txt" v-if="item.type == 'order'">{{$t('lang.drp_apply_title_2')}}</view>
							<view class="txt" v-if="item.type == 'buy'">{{$t('lang.drp_apply_title_3')}}</view>
							<view class="txt" v-if="item.type == 'goods'">{{$t('lang.drp_apply_title_4')}}</view>
							<view class="txt" v-if="item.type == 'free'">{{$t('lang.drp_apply_title_5')}}</view>
							<view class="iconfont icon-ok"></view>
						</view>
					</scroll-view>
				</view>
			</view>
		</uni-popup>

		<dsc-loading :dscLoading="dscLoading"></dsc-loading>

		<dsc-common-nav>
			<navigator url="../drp" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.drp_center')}}</text>
			</navigator>
		</dsc-common-nav>
		<!-- <tabbar :curpage="curpage"></tabbar> -->
	</view>
</template>

<script>
	import { mapState } from 'vuex'

	import uniPopup from "@/components/uni-popup.vue";
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import tabbar from "@/components/tabbar/tabbar.vue";

	export default {
		data() {
			return {
				autoplay: true,
				interval: 5000,
				duration: 500,
				dscLoading:true,
				curpage:'',
				renewShow:false,
				renew_type:'',
				pageDrpInfo: {}
			}
		},
		components:{
			uniIcons,
			uniPopup,
			dscNotContent,
			dscCommonNav,
			tabbar
		},
		computed:{
			...mapState({
				drpdata: state => state.drp.drpData
			}),
			viewStatus(){
				return this.drpdata ? this.drpdata.error : 0
			},
			viewAudit(){
				return this.drpdata ? this.drpdata.audit : 0
			},
			card(){
				return this.drpdata.membership_card_info ? this.drpdata.membership_card_info : ''
			},
			protectionList(){
				return this.card ? this.card.user_membership_card_rights_list : ''
			},
			receive_type_renew(){
				return this.drpdata.shop_info ? this.drpdata.shop_info.receive_type_format : 0
			},
			drpInfoBg(){
				return this.imagePath.drpInfoBg
			},
		},
		methods: {
			inviteFriends(){
				uni.navigateTo({
					url:'../card/card'
				})
			},
			drpHref(){
				uni.navigateTo({
					url:'../drp'
				})
			},
			protectionHref(index){
				uni.navigateTo({
					url:'../protection/protection?card_id='+this.card.id+'&index='+index
				})
			},
			drpApplyHref(){
				uni.navigateTo({
					url:'../apply/apply?card_id='+this.card.id
				})
			},
			withdraw(){
				uni.navigateTo({
					url:'../withdraw/withdraw'
				})
			},
			drpRenew(){
				this.renewShow = true
			},
			drpChange(){
				uni.navigateTo({
					url:"../register/register?apply_status=change&membership_card_id=" + this.card.id
				})
			},
			applyAgain(){
				uni.navigateTo({
					url:"../register/register?apply_status=repeat&membership_card_id=" + this.card.id
				})
			},
			renewClose(){
				this.renewShow = false
			},
			renew_method_select(type){
				this.renew_type = type;

				if(this.card.id){
					uni.navigateTo({
						url:"../apply/apply?receive_type=" + type + "&apply_status=renew&membership_card_id=" + this.card.id
					})
				}else{
					uni.navigateTo({
						url:"../apply/apply?receive_type=" + type + "&apply_status=renew"
					})
				}
			},
			depositLog(){
				uni.navigateTo({
					url:"../withdrawLog/withdrawLog"
				})
			},
			// 分销管理-自定义设置数据
			async getCustomTextByCode() {
				const {data: { page_drp_info }, status} = await this.$store.dispatch('getCustomText',{code: 'page_drp_info'});
			    if (status == 'success') {
			        this.pageDrpInfo = page_drp_info || {};
			    }
			}
		},
		onLoad(){

		},
		async onShow() {
			// let pages = getCurrentPages()
			// this.curpage = pages[pages.length - 1].route
			await this.getCustomTextByCode();
			this.$store.dispatch('setDrp')
		},
		watch:{
			drpdata(){
				this.dscLoading = false
			},
			viewStatus(){
				if(this.viewStatus == 2){
					uni.redirectTo({
						url:'register/register'
					})
				}
			}
		}
	}
</script>

<style>
.drp-info{ padding-bottom: 50upx; }
.drp-warp{ padding: 20upx 20upx 120upx 20upx; background: linear-gradient(0deg, #565555, #1c1c1c); }
.drp-warp .tip{ padding: 20upx; font-size: 25upx; background: #3b3b3b; color: #ecd8be; border-radius: 20upx; margin-bottom: 20upx; }
.drp-warp .header{padding: 20upx; background-size: 100% 100%; border-radius: 20upx; margin-bottom: 20upx;}
.drp-warp .header-top{ display: flex; flex-direction: row; justify-content: flex-start; align-items: center; }
.drp-warp .header-top .header-img{ width: 120upx; height: 120upx; overflow: hidden; border-radius: 100%; border:5upx solid rgba(255,255,255,.3); }
.drp-warp .header-top .header-right{ flex: 1; margin-left: 20upx; }
.drp-warp .header-top .header-right .rank{ display: flex; flex-direction: row; justify-content: space-between; align-items: center; }
.drp-warp .header-top .header-right .vip{ display: flex; flex-direction: row; justify-content: flex-start; align-items: center; background: linear-gradient(0deg, #151515, #2a2724); padding: 0 25upx 0 20upx; border-radius: 50upx; margin-top: 5upx;}
.drp-warp .header-top .header-right .time{ font-size: 25upx; color: #666666;}
.drp-warp .header-top .header-right .rank .more{ color: #666666;}
.drp-warp .header-top .header-right .vip .icon{ width: 40upx; height: 40upx; margin-right: 10upx;}
.drp-warp .header-top .header-right .vip text{ color: #E3C49E;}

.drp-warp .header-bottom{ margin-top: 20upx; padding-top: 20upx; display: flex; justify-content: flex-start; }
.drp-warp .header-bottom.bor{ border-top: 2upx solid #dea35a; }
.drp-warp .header-bottom .item{ background: #dea35a; display: flex; justify-content: center; align-items: center; height: 52upx; padding: 0 30upx; border-radius: 26upx; color: #FFFFFF; font-size: 25upx; margin-right: 20upx;}

.section{ background: #FFFFFF; box-shadow: 0 10upx 30upx 0 rgba(68, 79, 90, 0.11); border-radius: 20upx; margin-bottom: 20upx; overflow: hidden;}
.section .tit{ padding: 10upx 20upx; color: #805223; border-bottom: 2upx solid #E3D6C4; display: flex; flex-direction: row; justify-content: space-between; align-items: center; }
.section .items{ display: flex; flex-direction: row; justify-content: flex-start; align-items: center;}
.section .item{ flex: 1; width: 25%; padding: 25upx 0; display: flex; flex-direction: column; justify-content: center; align-items: center;}
.section .item .txt{ color: #AC8054; font-weight: 500;}
.section .item:first-child .txt{ color: #F2041A;}
.section .item .span{ color: #232323; font-size: 25upx;}

.team-box{ margin: -120upx 20upx; padding: 20upx; background: #FFFFFF; box-shadow: 0 10upx 20upx 0 rgba(95, 95, 95, 0.1); border-radius: 20upx;}
.team-box .tit{ display: flex; justify-content: center; align-items: center; position: relative; height: 60upx; margin: 10upx 0 30upx;}
.team-box .tit .row{ width: 200upx; height: 4upx; background: #000000;}
.team-box .tit text{ font-size: 33upx; font-weight: 600; padding: 0 10upx; background: #FFFFFF; position: absolute;}
.team-box .items{ display: flex; flex-direction: row;}
.team-box .item{ flex: 1 1 0%; display: flex; flex-direction: column; justify-content: center; align-items: center; background: #FCF3E7; border-radius: 10upx; margin: 0 10upx; height: 160upx;}
.team-box .item1{ margin-right: 10upx;}
.team-box .item3{ margin-left: 10upx;}
.team-box .item .num{ font-size: 32upx; font-weight: 700; color: #3A3A3A; }
.team-box .item .link{ background: linear-gradient(90deg, #ecd8be, #dbb280); width: 30upx; height: 4upx; margin: 10upx 0 15upx; }
.team-box .item .text{ color: #805223; font-size: 25upx;}

.invite_friends_button{ width: 90%; height: 100upx; line-height: 100upx; background: linear-gradient(118deg, #ecd8be, #dbb280); border-radius: 50upx; color: #805223; font-size: 40upx; text-align: center; margin:20upx auto 40upx; font-weight: 600; display: flex; flex-direction: row; justify-content: center; align-items: center;}
.invite_friends_button text{ margin-right: 10upx;}
.invite_friends_button .uni-icon{ height: 100upx; line-height: 110upx; display: block;}

.nav-items{ display: flex; flex-direction: row; justify-content: space-between; align-items: center; flex-wrap: wrap; margin: 145upx 20upx 0upx;}
.nav-items .item{ width: 48.5%; padding: 20upx; margin-bottom: 20upx; display: flex; flex-direction: row; justify-content: flex-start; align-items: center; background: #FFFFFF; box-shadow: 0 10upx 20upx 0 rgba(95, 95, 95, 0.1); border-radius: 10upx; box-sizing: border-box;}
.nav-items .item .icon{ width: 80upx; height: 80upx; margin-right: 20upx;}
.nav-items .item text{ height: 80upx; line-height: 80upx; color: #805223;}

.list-ul{ padding: 20upx; }
.list-ul .li{ padding-bottom: 10upx; }
.list-ul .li:last-child{ padding-bottom: 0; }

.ectouch-notcont{ padding: 100upx 0 150upx; text-align: center;}
.ectouch-notcont .img{ width: 280upx; height: 280upx; margin: 0 auto;}
.ectouch-notcont .img image{ width: 100%;}
.ectouch-notcont .cont{ color: #999999; font-size: 30upx; display: block; flex-direction: row;}

.adv{ width: 720upx; height: 200upx; margin: 0 auto;}
.adv .swiper,
.adv .swiper .swiper-item{ width: 720upx; height: 200upx;}
.adv .swiper .swiper-item image{ width: 720upx; height: 200upx; }

.protection .value{
	border-radius: 20upx;
	display: flex;
	flex-direction: row;
	padding: 20upx 20upx 0 20upx;
	font-size: 24upx;
	flex-wrap: wrap;
}
.protection .value .item-list{
	width: 25%;
	text-align: center;
	margin-bottom: 20upx;
	box-sizing: border-box;
}
.protection .value .item-list .icon{
	width: 100%;
	border-radius: 50%;
	width: 130upx;
	height: 130upx;
	margin: 0 auto;
}
.protection .value .item-list .icon .img{ border-radius: 50%; }
.protection .value .item-list .text{
	margin-top: 10upx;
	width: 100%;
	overflow: hidden;
	white-space: nowrap;
	text-overflow: ellipsis;
}
</style>
