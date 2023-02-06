<template>
	<view class="container">
		<block v-if="card_id">
			<view class="vip-buy">
				<view class="purchase-card">
					<view class="swiper-card">
						<view class="purchase-card-item" :style="[ drpRightsCard.background_img ? {'background-image': 'url(' + drpRightsCard.background_img + ')'} : {'background-color': drpRightsCard.background_color} ]">
							<view class="left">
								<view class="rank">{{drpRightsCard.name}}</view>
								<text class="period" v-if="drpRightsCard.expiry_type == 'forever'">{{$t('lang.indate')}}{{$t('lang.permanence')}}</text>
								<text class="period" v-else-if="drpRightsCard.expiry_type == 'days'">{{$t('lang.indate')}}{{drpRightsCard.expiry_type_format}}</text>
								<text class="period" v-else-if="drpRightsCard.expiry_type == 'timespan'">{{$t('lang.indate')}}{{drpRightsCard.expiry_date_end}}</text>
							</view>
							<view class="right">{{drpRightsCard.receive_value_arr && drpRightsCard.receive_value_arr.value_format}}</view>
						</view>
					</view>
				</view>
				<view class="protection" v-if="drpRightsCard.user_membership_card_rights_list && drpRightsCard.user_membership_card_rights_list.length > 0">
					<view class="title">{{$t('lang.vip_protection')}}</view>
					<view class="items">
						<view class="item" v-for="(card,index) in drpRightsCard.user_membership_card_rights_list" :key="index" @click="protectionHref(card.membership_card_id,index)">
							<view class="icon"><image :src="card.icon" class="img" /></view>
							<view class="text">{{card.name}}</view>
						</view>
					</view>
				</view>
				<view class="head" v-if="drpRightsCard.description">
					<view class="title">{{$t('lang.vip_card')}}</view>
					<view class="notice">
						<view class="tit">{{$t('lang.must_be_read')}}</view>
						<text>{{drpRightsCard.description}}</text>
					</view>
				</view>
			</view>
		</block>
		<block v-else>
			<swiper class="swiper-box" :duration="300" :current="index" @change="change">
				<swiper-item v-for="(item,index) in drpChangeCard.list" :key="index">
					<scroll-view class="list" scroll-y>
					<view class="vip-buy">
						<view class="purchase-card">
							<view class="swiper-card">
								<view class="purchase-card-item" :style="[ item.background_img ? {'background-image': 'url(' + item.background_img + ')' } : {'background-color': item.background_color } ]">
									<view class="left">
										<view class="rank">{{item.name}}</view>
										<text class="period" v-if="item.expiry_type == 'forever'">{{$t('lang.indate')}}{{$t('lang.permanence')}}</text>
										<text class="period" v-else-if="item.expiry_type == 'days'">{{$t('lang.indate')}}{{item.expiry_type_format}}</text>
										<text class="period" v-else-if="item.expiry_type == 'timespan'">{{$t('lang.indate')}}{{item.expiry_date_end}}</text>
									</view>
									<view class="right" v-if="type != 'goods'">{{item.receive_value_arr.value_format}}</view>
								</view>
							</view>
							<view class="swiper-button swiper-button-next" @click="navigation('next')"><uni-icons type="forward" size="30" color="#E3C49E"></uni-icons></view>
							<view class="swiper-button swiper-button-prev" @click="navigation('prev')"><uni-icons type="back" size="30" color="#E3C49E"></uni-icons></view>
						</view>
						<view class="protection" v-if="item.user_membership_card_rights_list && item.user_membership_card_rights_list.length>0">
							<view class="title">{{$t('lang.vip_protection')}}</view>
							<view class="items">
								<view class="item" v-for="(card,cardIndex) in item.user_membership_card_rights_list" :key="cardIndex" @click="protectionHref(card.membership_card_id,cardIndex)">
									<view class="icon"><image :src="card.icon" class="img" /></view>
									<view class="text">{{card.name}}</view>
								</view>
							</view>
						</view>
						<view class="head" v-if="item.description">
							<view class="title">{{$t('lang.vip_card')}}</view>
							<view class="notice">
								<text>{{item.description}}</text>
							</view>
						</view>
						<view class="bg-color-write" v-if="type == 'integral'">
							<view class="cell-box">
								<view class="cell-title">{{$t('lang.receive_value_integral')}}</view>
								<view class="cell-content">{{ item.receive_value_arr.value }}</view>
							</view>
						</view>
						<view class="bg-color-write" v-if="type == 'order'">
							<view class="cell-box">
								<view class="cell-title">{{$t('lang.receive_value_order')}}</view>
								<view class="cell-content">{{ item.receive_value_arr.value_format }}</view>
							</view>
						</view>
						<view v-if="type == 'goods' && item.goods_list && item.goods_list.length > 0">
							<view class="head">
								<view class="title">{{$t('lang.receive_value_goods')}}</view>
							</view>
							<ul class="apply-goods-list clearfix">
								<li class="item" v-for="(goodsitem,index2) in item.goods_list" :key="index2">
									<view class="item-wapper">
										<view class="img"><navigator :url="'/pagesC/goodsDetail/goodsDetail?id='+goodsitem.goods_id" hover-class="none"><image :src="goodsitem.goods_thumb" alt="" mode="widthFix"></image></navigator><view class="tag">{{$t('lang.drp_apply_goods_label')}}</view></view>
										<view class="tit"><navigator :url="'/pagesC/goodsDetail/goodsDetail?id='+goodsitem.goods_id" hover-class="none">{{goodsitem.goods_name}}</navigator></view>
										<view class="info">
											<view class="price">{{goodsitem.shop_price_formated}}</view>
											<view class="i-btn" v-if="goodsitem.is_buy==0" @click="onAddCartClicked(goodsitem.goods_id,10)">{{$t('lang.button_buy')}}</view>
											<view class="i-btn" v-else>{{$t('lang.drp_apply_goods_bought')}}</view>
										</view>
									</view>
								</li>
							</ul>
						</view>
					</view>
					<view class="vip-fixed-bottom" v-if="type != 'goods'">
						<view class="item article-confirm">
							<view class="radio-wrap" @click="toggleConfirm"><i class="radio-icon" :class="{'active': confirm}"></i>{{$t('lang.checkout_help_article')}}</view>
							<view class="a" @click="$outerHref('/pagesC/article/detail/detail?id='+drpChangeCard.agreement_id,'app')">{{ drpChangeCard.agreement_article_title }}</view></view>
						<view class="item vip-btn" @click="onSubmit">
							<block v-if="type == 'buy'">
								<text>{{$t('lang.immediate_pay')}}</text>
								<text class="number">{{ item.receive_value_arr.value_format }}</text>
							</block>
							<block v-else-if="type == 'free'">
								<text v-if="apply_status == 'renew'">{{$t('lang.Immediately_renewal')}}</text>
								<text v-else>{{$t('lang.immediately_change')}}</text>
							</block>
							<block v-else>
								<text>{{$t('lang.immediately_change')}}</text>
							</block>
						</view>
					</view>
					</scroll-view>
				</swiper-item>
			</swiper>
		</block>
		
		<view class="vip-popup mod-popup" :class="{'show':applyPopupShow}">
			<!-- <view class="p-close" @click='closePopup'><van-icon name="clear" /></view> -->
			<view class="p-content">
				<view class="p-icon" v-if="popupStep == 2 || popupStep == 3">
					<view class="loader04" v-if="popupStep == 2"></view>
					<view class="p-icon-success" v-if="popupStep == 3"></view>
				</view>
				<view>{{validMsg}}</view>
				<!-- <view v-html="validNumber" v-if="validNumber.length > 0" class="number"></view> -->
				<view v-if="validTip.length > 0" :class="{'uni-green': formValid, 'uni-red': !formValid}">{{validTip}}</view>
			</view>
			<view class="p-handler">
				<block v-if="popupStep == 1">
				<view class="v-btn" @click='onSubmit' v-if="formValid">{{$t('lang.submit_apply')}}</view>
				<view class="v-btn" @click='closePopup' v-else>{{$t('lang.close_window')}}</view>
				</block>
				<block v-if="popupStep == 2">
				<view class="v-btn disabled">{{$t('lang.drp_apply_padding')}}</view>
				</block>
				<block v-if="popupStep == 3">
				<view class="v-btn" @click='drpInfoHref'>{{$t('lang.href_drp_center')}}</view>
				</block>
			</view>
		</view>
		<view class="mod-mask" :class="{'show':applyPopupShow}" @click='closePopup'></view>
		
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
	
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import universal from '@/common/mixins/universal.js';
	
	export default {
		mixins:[universal],
		data() {
			return {
				formValid: true,
				validMsg: '',
				validNumber: '',
				validTip: '',
				popupStep: 1,
				applySuccess: false,
				applyPopupShow: false,
				index:0,
				amount: '',
				confirm: false,
				point:'',
				card_id:'',
				type:'',
				timer:'',
				apply_status:'',
				membership_card_id:0
			}
		},
		components:{
			uniIcons,
			dscCommonNav,
			dscNotContent
		},
		onLoad(e) {
			this.card_id = e.card_id;
			this.type = e.receive_type;
			this.point = e.point;
			// 续费、重新领取、更换
			this.apply_status = e.apply_status ? e.apply_status : '';
			this.membership_card_id = e.membership_card_id ? e.membership_card_id : 0;
			
			if(this.card_id){
				this.onRightsCard();
			}else{
				this.load();
			}
		},
		computed: {
			...mapState({
				drpChangeCard: state => state.drp.drpChangeCard,
				drpRightsCard: state => state.drp.drpRightsCard
			}),
			length(){
				return this.drpChangeCard.list ? this.drpChangeCard.list.length-1 : 0;
			}
		},
		methods: {
			load(){
				let o ={
					receive_type:this.type
				}
				
				if(this.apply_status && this.membership_card_id){
					o = {
						receive_type:this.type,
						apply_status:this.apply_status,
						membership_card_id:this.membership_card_id
					}
				}
			
				this.$store.dispatch('setDrpChangeCard', o)
			},
			onRightsCard(){
				this.$store.dispatch('setDrpRightsCard',{
					membership_card_id:this.card_id
				})
			},
			onSubmit(){
				let that = this
				let o = {
					receive_type: this.type,
					membership_card_id: this.drpChangeCard.list[this.index].id
				}
				
				if(this.apply_status) {
					o = {
						receive_type: this.type,
						apply_status:this.apply_status,
						membership_card_id: this.drpChangeCard.list[this.index].id,
					}
				}
				
				if(!this.confirm){
					uni.showToast({
						icon:'none',
						title: this.$t('lang.drp_agreement_please')
					})
					
					return false
				}
				
				if (!this.$isLogin()) {
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
					
					return false
				}
				
				if(this.type == 'integral'){
					if(this.apply_status) {
						o = {
							receive_type: this.type,
							apply_status:this.apply_status,
							membership_card_id: this.drpChangeCard.list[this.index].id,
							pay_point:this.drpChangeCard.list[this.index].receive_value_arr.value
						}
					} else {
						o = {
							receive_type: this.type,
							membership_card_id: this.drpChangeCard.list[this.index].id,
							pay_point:this.drpChangeCard.list[this.index].receive_value_arr.value
						}
					}
				}
				
				if(this.type == 'buy'){
					uni.navigateTo({
						url:"../done/done?membership_card_id="+this.drpChangeCard.list[this.index].id+'&apply_status='+this.apply_status
					})
					return false
				}
				
				let msg_tips = this.$t('lang.whether_apply_distributor')
				
				if(this.apply_status == 'renew'){
					msg_tips = this.$t('lang.whether_to_renew')
				}
				if (this.apply_status == 'change') {
					msg_tips = this.$t('lang.whether_to_change')
				}
				
				uni.showModal({
					content: msg_tips,
					success: (res) => {
						if(res.confirm){
							this.$store.dispatch('setDrpApply',o).then(data=>{
								let res = data.data
								if(data.status == 'success'){
									this.validTip = res.msg;
									if(res.error == 0){
										this.formValid = true;
										this.popupStep = 3;
										this.timer = setTimeout(()=>{
											uni.reLaunch({
												url:'../drpinfo/drpinfo'
											})
										},3000)
									}else{
										this.formValid = false;
										this.popupStep = 1;
									}
									
									this.applyPopupShow = true;
								}else{
									if (data.errors) {
										uni.showToast({ title: data.errors.message, icon: "none" });
									} else {
										uni.showToast({ title: this.$t('lang.interface_error_reporting'), icon: "none" })
									}
								}
							})
						}
					}
				})
			},
			toggleConfirm(){
				this.confirm = !this.confirm
			},
			closePopup(){
				this.applyPopupShow = false;	
			},
			navigation(val){
				if(val == 'next'){
					if(this.length > this.index){
						this.index += 1;
					}
				}else if(val == 'prev'){
					if(this.index > 0){
						this.index -= 1;
					}
				}
			},
			protectionHref(id,index){
				uni.navigateTo({
					url:'../protection/protection?card_id='+id+'&index='+index
				})
			},
			drpInfoHref(){
				clearTimeout(this.timer)
				uni.reLaunch({
					url:'../drpinfo/drpinfo'
				})
			},
			onAddCartClicked(goods_id,type){
				this.$store.dispatch('setAddCart', {
				    goods_id: goods_id,
				    num: 1,
				    spec: [],
				    rec_type: type
				}).then(res => {
					if (res.data == true) {
						uni.navigateTo({
							url:'/pages/checkout/checkout?rec_type='+type
						});
					} else {
						uni.showToast({
							title:res.data.msg,
							icon:'none',
							duration:1000
						})
                    }
				})
			},
			change(e){
				this.index = e.detail.current
				this.confirm = false
			}
		}
	}
</script>

<style scoped lang="scss">
.img image{ width: 100%; }
.container{
	height: 100%;
	view{
		line-height: 1.2;
	}
}
.swiper-box{
	height: 100%;
}
.mod-popup{
    position: fixed;
    top: 50%;
    left: 50%;
    max-height: 100%;
    overflow-y: auto;
    background-color: #fff;
    -webkit-transition: .3s ease-out;
    transition: .3s ease-out;
    -webkit-overflow-scrolling: touch;
    -webkit-transform: translate3d(-50%,-50%,0);
    transform: translate3d(-50%,-50%,0);
    z-index: 2018;
    display: none;
    &.show{
    	display: block;
    	animation: fadeIn .5s;
    }
}
.mod-mask{
	position: fixed;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    background-color: rgba(0,0,0,.7);
    z-index: 2017;
    display: none;
    &.show{
    	display: block;
    	animation: fadeIn .5s;
    }
}
@mixin box-flex(){
    flex: 1;
    display: block !important;
    width: 100%;
}

@mixin box(){
    display: flex;
}
.bg-color-write{ background: #fff; }
.vip-buy{
	background:linear-gradient(0deg, #696969 0%, #151515 30%); overflow: hidden;
	.title{
	    color: #EBD6BA; text-align: center; font-size: 30upx; line-height: 82upx;
	    &:before,
	    &:after{ content:''; width: 52.6upx; height: 8.4upx; margin: 0 12upx; vertical-align: middle; display: inline-block; background-size: contain; background-position: center; background-repeat: no-repeat;}
	    &:before{ background-image: url(../../../static/vip/title-star-l.png); }
	    &:after{ background-image: url(../../../static/vip/title-star-r.png); }
	}
	
	.purchase-card{
		margin: 0 25upx 25upx;
		position: relative;
		
		.swiper-card{
			margin: 60upx 80upx 20upx;
			border-radius: 4upx;

			.purchase-card-item{
				// background: linear-gradient(118deg, #ecd8be, #dbb280);
				padding: 60upx 40upx;
				display: flex;
				justify-content: space-between;
				align-items: center;
				color: #463015;
				border-radius: 20upx;
				height: 102px;
				background-size: 100% 100%;

				.left{
					.rank{
						font-size: 32upx;
					}
					.period{
						margin-top: 4upx;
						display: block;
						font-size: 25upx;
					}
				}

				.right{
					font-size: 32upx;
					font-weight: 700;
				}
			}
		}
	}   

	.protection{
		.items{
			background: #FCF3E7;
			margin: 0 25upx 25upx;
			border-radius: 20upx;
			display: flex;
			flex-direction: row;
			padding: 20upx 20upx 0 20upx;
			font-size: 24upx;
			flex-wrap: wrap;

			.item{
				width: 25%;
				text-align: center;
				margin-bottom: 20upx;
				box-sizing: border-box;

				.icon{
					width: 100%;
					border-radius: 50%;
					width: 130upx;
					height: 130upx;
					margin: 0 auto;

					.img{
						border-radius: 50%;                        
					}
				}

				.text{
					margin-top: 10upx;
					width: 100%;
					overflow: hidden;
					white-space: nowrap;
					text-overflow: ellipsis;
				}
			}
		}
	}
	
    .head{
        .notice{ 
            background: #FCF3E7; margin: 0 25upx 25upx; border-radius: 20upx; padding: 25upx;
            .tit{
                margin-bottom: 20upx;
				font-size: 36upx;
            }
            text{ color: #805223; line-height: 1.6; }
        }
    }
}
.cell-box{ 
    padding: 20upx; border-bottom: 1px solid #f6f6f9; @include box();
    &:last-child{ border-bottom: 0 none; }
    .cell-title{ font-size: 28upx; line-height: 40upx; @include box-flex();}
    .cell-content{ text-align: right; line-height: 40upx;}
}
.vip-fixed-bottom{
    background-color: #fff;
    height: 94upx;
    padding-bottom: env(safe-area-inset-bottom);
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    overflow: hidden;
    box-shadow: 1px 0 5px rgba(100, 100, 100, 0.1);
    @include box();
    .item{
        @include box-flex();
        &.vip-btn{
            text-align: center; line-height: 94upx; font-size: 28upx; color: #463015;
            background:linear-gradient(118deg,rgba(236,216,190,1),rgba(219,178,128,1));
            .number{ font-size: 32upx; font-weight: 700; margin-left: 4upx; }
        }
        &.article-confirm{
            text-align: center;
            .radio-wrap{ 
                line-height: 36upx; margin-top: 10upx; margin-bottom: 8upx; 
                .radio-icon{ 
                    position: relative; margin-right: 4upx;
                    width: 36upx; height: 36upx; border-radius: 36upx; display: inline-block; vertical-align: bottom; 
                    background: linear-gradient(118deg,rgba(236,216,190,1),rgba(219,178,128,1)); border-radius:50%;
                    &:before{ content:''; position: absolute; width: 18upx; height: 10upx; border-width: 1px; border-color: transparent transparent #fff #fff; border-style: solid; left: 10upx; top: 10upx; transform: rotate(-45deg); opacity: 0; transition: all .2s;}
                    &:after{ content: ''; position: absolute; left: 3px; top: 3px; bottom: 3px; right: 3px; background: #fff; transition: all .2s; border-radius: 100%;}
                    &.active:before{ transform: rotate(-45deg); opacity: 1; }
                    &.active:after{ transform: scale(0); opacity: 0; }
                }
            }
            .a{ color: #C79557; font-size: 20upx; }
        }
    }
}
.swiper-button{
	position: absolute;
    background: none;
    display: flex;
    justify-content: center;
    align-items: center;
    top: 72upx;
    margin: 0;

    &.swiper-button-next{
        right: 0;
    }

    &.swiper-button-prev{
        left: 0;
    }
}
.list{
	height: 100%;
}
.apply-goods-list{
	margin: 0 25upx;
	padding-bottom: 20upx;
	overflow: hidden;
	
	.item{
		float: left; width: 50%;  border-radius: 10upx; overflow: hidden; position: relative; margin-top: 25upx; box-sizing: border-box;
		.item-wapper{
			background: #fff;
			position: relative;
			overflow: hidden;
			border-radius: 10upx;
		}
		&:nth-child(2n){
			padding-left: 12.5upx;
		}
		
		&:nth-child(2n-1){
			padding-right: 12.5upx;
		}
		
		.link{ position: absolute; z-index: 10; top: 0; right: 0; bottom: 0; left: 0; }
		.img{ 
			position: relative;  
			.tag{ 
				position: absolute; z-index: 2; left: -54upx; top: 6upx; background: #000; color: #573B1A; width: 166upx; text-align: center; line-height: 40upx; background:linear-gradient(90deg,#EBD5B9,#DCB483); transform: rotate(-45deg);
				&:before,
				&:after{ content:''; height: 0; width: 100%; position: absolute; left: 0; border-top: 1px solid #c19a6d; transform: translate(0,-.5px);}
				&:before{ top: 4upx; }
				&:after{ bottom: 4upx; }
			}
		}
		.tit{ font-size: 28upx; height: 74upx; line-height: 36upx; overflow: hidden; word-break: break-all; padding: 10upx 4upx 0 14upx; margin-bottom: 10upx;}
		.info{ 
			padding: 0 16upx 20upx 10upx; overflow: hidden; line-height: 48upx; font-size: 28upx;display: flex;align-items: center;
			.price{ float: left; width: 50%; color: #f92028; font-size: 26upx; }
			.i-btn{ float: left; width: 50%; border-radius: 6upx; padding: 5upx 0; text-align: center; background:linear-gradient(118deg,rgba(236,216,190,1),rgba(219,178,128,1)); color: #64503E; }
		}
	}
}
.vip-popup{
    padding: 40upx; border-radius: 20upx; width: 460upx; overflow: hidden;
    .p-close{ position: absolute; right: 20upx; top: 20upx; .van-icon{ font-size: 46upx; color: #dcdcdc; }}
    .p-content{
        text-align: center; margin-bottom: 32upx;
        p{ 
            color: #888; font-size: 28upx; margin: 10upx 0;
            &.number{ font-size: 32upx; color: #333; }
            &.green{ color: #41d357; }
            &.red{ color: #fc6c6c; }
        }
        .p-icon{
            margin-bottom: 20upx;
            .p-icon-success{
                width: 93.4upx;
                height: 93.4upx;
                background: url(../../../static/vip/icon-success.png) no-repeat center;
                background-size: contain;
                margin: 0 auto;
            }
        }
    }
    .p-handler{
        .v-btn{
            text-align: center;
            width: 100%;
            margin: 0 auto 10upx;
            text-align: center;
            line-height: 60upx;
            background: #000;
            color: #E3C49E;
            border-radius: 30upx;
            box-shadow: 0 12px 6px -8px rgba(0,0,0,0.3);
            font-size: 30upx;
            cursor: pointer;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            &.disabled{ background: #b0b0b0; color: #fff; }
        }
    }
}
.loader04 {
    width: 93.4upx;
    height: 93.4upx;
    border: 3.4upx solid #e7d2b5;
    border-radius: 50%;
    position: relative;
    animation: loader-rotate 1s ease-in-out infinite;
    top: 50%;
    margin: 0 auto;
    &:after {
        content: '';
        width: 16.6upx;
        height: 16.6upx;
        border-radius: 50%;
        background: #d5aa75;
        position: absolute;
        top: -10upx;
        left: 50%;
        margin-left: -8.4upx;
    }
}
</style>
