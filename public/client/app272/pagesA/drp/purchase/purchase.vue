<template>
	<view class="container">
		<view class="vip-buy">
			<view class="head">
				<view class="title">{{$t('lang.vip_card')}}</view>
				<view class="notice">
					<h3>
						<!-- <a href="javascript:;" class="more">{{$t('lang.view_euity')}}<i class="iconfont icon-more"></i></a> -->
						<b>{{$t('lang.must_be_read')}}</b>
					</h3>
					<p v-html="purchaseData.novice"></p>
				</view>
			</view>
			<view class="bg-color-write">
				<view class="cell-box">
					<view class="cell-title">{{$t('lang.total_amount_payable')}}</view>
					<view class="cell-content">{{ purchaseData.price }}</view>
				</view>
				<view class="cell-box">
					<view class="cell-title">{{$t('lang.payment_mode')}}</view>
					<view class="cell-content">{{$t('lang.online_pay')}}</view>
				</view>
			</view>
		</view>
		<view class="vip-fixed-bottom">
			<view class="item article-confirm">
				<view class="radio-wrap" @click="toggleConfirm"><i class="radio-icon" :class="{'active': confirm}"></i>{{$t('lang.checkout_help_article')}}</view>
				<view class="a" @click="$outerHref('/pagesC/article/detail/detail?id='+purchaseData.agreement_id,'app')">《{{wgtinfo.name}}{{$t('lang.drp_purchase_agreement2')}}》</view>
			</view>
			<view class="item vip-btn" @click="onSubmit">
				<span>{{$t('lang.immediate_pay')}}</span>
				<span class="number">{{ purchaseData.price }}</span>
			</view>
		</view>
		<dsc-common-nav>
			<navigator url="../drp" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.my_drp')}}</text>
			</navigator>
		</dsc-common-nav>s
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	
	export default {
		data() {
			return {
				amount: '',
				confirm: true,
				wgtinfo:{}
			}
		},
		components:{
			uniIcons,
			dscCommonNav,
			dscNotContent
		},
		onLoad() {
			this.wgtinfo = JSON.parse(uni.getStorageSync('wgtinfo'))
			this.load();
		},
		computed: {
			...mapState({
				purchaseData: state => state.drp.purchaseData
			})
		},
		methods: {
			load(){
				this.$store.dispatch('setDrpPurchase');
			},
			onSubmit(){
				if(this.confirm){
					// #ifdef APP-PLUS
					this.$outerHref(this.$websiteUrl+'drp/drpDone')
					// #endif
					
					// #ifdef MP-WEIXIN
					uni.navigateTo({
						url:"../done/done"
					})
					// #endif
				}else{
					uni.showToast({
						title: this.$t('lang.drp_agreement_please')
					})
				}
			},
			toggleConfirm(){
				this.confirm = !this.confirm
			}
		}
	}
</script>

<style scoped lang="scss">
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
    .head{
        background:linear-gradient(0deg, #696969 0%, #151515 30%); overflow: hidden;
        .title{ 
            color: #EBD6BA; text-align: center; font-size: 30upx; line-height: 82upx;
            &:before,
            &:after{ content:''; width: 52.6upx; height: 8.4upx; margin: 0 12upx; vertical-align: middle; display: inline-block; background-size: contain; background-position: center; background-repeat: no-repeat;}
            &:before{ background-image: url(../../../static/vip/title-star-l.png); }
            &:after{ background-image: url(../../../static/vip/title-star-r.png); }
        }
        .notice{ 
            background: #FCF3E7; margin: 0 25upx 25upx; border-radius: 20upx; padding: 25upx;
            h3{
                margin-bottom: 20upx;
                b{ font-size: 36upx; }
                a{ 
                    float: right; 
                    .iconfont{ font-size: 20upx; margin-left: 2upx; }
                }
            }
            p{ color: #805223; line-height: 1.6; }
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
</style>
