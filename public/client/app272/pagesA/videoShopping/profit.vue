<template>
	<view class="container-tab-bar">
		<view class="tab-bar">
			<view v-for="(tab,index) in tabBars" :key="index" :class="['tab-item',type == index ? 'active' : '']" @click="orderStatusHandle(index)">
				<text>{{ tab }}</text>
			</view>
		</view>
		<view class="section-list">
			<block v-if="profiltList.length > 0">
				<view class="video-order-list" v-for="(item,index) in profiltList" :key="index">
					<view class="left">
						<view class="image-box"><image :src="item.goods_thumb" class="img" /></view>
						<view class="box-flex">
							<view class="tit onelist-hidden">{{ item.order_sn }}</view>
							<view class="time">{{ item.add_time }}</view>
						</view>
					</view>
					<view class="right">
						<view class="price">{{ item.goods_amount_format }}</view>
						<view class="ticheng">提成 {{ item.order_commission }}</view>
					</view>
				</view>
				<view class="uni-loadmore" v-if="showLoadMore && page > 1">{{loadMoreText}}</view>
			</block>
			<block v-else>
				<dsc-not-content></dsc-not-content>
			</block>
		</view>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';

	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import uniPopups from '@/components/uni-popup/uni-popup.vue';

	export default {
		data() {
			return {
				loadMoreText: this.$t('lang.loading'),
				showLoadMore: false,
				tabBars:[this.$t('lang.all'), this.$t('lang.has_been_divide'), this.$t('lang.not_into'), this.$t('lang.refunded_order')],
				active:0,
				status:2,
				size:10,
				page:1,
				type:0,
				profiltList:[]
			}
		},
		components:{
			uniIcons,
			dscCommonNav,
			dscNotContent,
			uniPopups
		},
		onUnload(){
			this.loadMoreText = this.$t('lang.load_more');
			this.showLoadMore = false;
		},
		onReachBottom(){
			this.showLoadMore = true
			if(this.page * this.size == this.profiltList.length){
				this.page ++
				this.getProfiltList()
			}else{
				this.loadMoreText = this.$t('lang.no_more');
				return;
			}
		},
		onLoad(e) {
			this.type = e.type ? e.type : 0;
		},
		onShow() {
			this.getProfiltList();
		},
		methods: {
			async getProfiltList(){
				const { data } = await this.$http.post(`${this.websiteUrl}/api/media/promoter/income/details`,{
					type: this.type,
					page: this.page,
					size: this.size
				});

				if(data.error > 0) return
				
				if(this.page > 1){
					this.profiltList = [...this.profiltList, data.list]
				}else{
					this.profiltList = data.list
				}
			},
			orderStatusHandle(index){
				this.type = index;
				this.getProfiltList();
			}
		}
	}
</script>

<style lang="scss" scoped>
.drp-order-list{ background: #FFFFFF; margin-top: 20upx;}
.drp-order-list .order-box{ padding: 0 30upx;}
.drp-order-list .order-box .order-header{ padding: 20upx 0; display: flex; flex-direction: row; justify-content: space-between; align-items: center; border-bottom: 1px solid #f4f4f4;}
.drp-order-list .order-box .order-sn{ padding: 20upx 0;}
.drp-order-list .order-box .order-sn view{ color: #777777; line-height: 1.6; font-size: 26upx;}
.drp-order-list .order-box .order-sn text{ color: #333333;}

.goods-list{ padding: 20upx; border-top: 1px solid #f4f4f4; border-bottom: 1px solid #f4f4f4; display: flex; flex-direction: row; margin-bottom: 0;}
.goods-list .left{ width: 150upx; height: 150upx; border: 1px solid #f4f4f4;}
.goods-list .left image{ width: 100%; }
.goods-list .right{ display: flex; flex: 1 1 0%; flex-direction: column; margin-left: 20upx; }
.goods-list .right .name{ font-size: 28upx; color: #333333; line-height: 1.5;}
.goods-list .right .out{ display: flex; justify-content: space-between; align-items: center; margin-top: 20upx;}
.goods-list .right .out .number{ color: #999999; font-size: 25upx;}

.text-right{ padding: 20upx 30upx; color: #777777;}
.ml_10 {
	margin-left: 20upx;
}
.border_top {
    border-top: 1upx solid #f4f4f4!important;
}
.border_bottom {
    border-bottom: 1upx solid #f4f4f4!important;
}
.border_top_none {
      border-top: none!important;
}
.container-tab-bar {
	height: 100vh;
	background-color: #FFFFFF;
	
	.section-list {
		.goods-list {
			padding: 20rpx 30rpx;
			border: none!important;
		}
	}
	.commission_wrap {
		display: flex;
		margin: 0 24rpx;
		padding: 24rpx 0;
		border-radius: 20rpx;
		background-color: #F9F9F9;
		.commission_item {
			flex: auto;
			display: flex;
			flex-direction: column;
			align-items: center;
			color: #333;
			.value_box {
				margin-top: 20rpx;
			}
		}
	}
	.commission_all {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 30rpx;
		view {
			font-weight: bold;
			color: #333;
			&:nth-child(1) {
				position: relative;
				.ico {
					position: absolute;
					top: 50%;
					right: -50rpx;
					transform: translateY(-50%);
					width: 36rpx;
					height: 36rpx;
					border-radius: 50%;
					line-height: 36rpx;
					text-align: center;
					font-size: 24rpx;
					color: #fff;
					background-color: #FEA402;
				}
			}
		}
	}
	.pop_content {
		overflow: hidden;
		width: 675rpx;
		border-radius: 12rpx;
		background-color: #fff;
		.pop_header {
			position: relative;
			padding: 0 30rpx;
			text-align: center;
			height: 90rpx;
			line-height: 90rpx;
			.icon-find-fanhui {
				position: absolute;
				top: 50%;
				left: 30rpx;
				transform: translateY(-50%);
				font-size: 28rpx;
			}
			.pop_title {
				font-size: 32rpx;
				font-weight: 700;
				line-height: 90rpx;
				color: #282828;
			}
			.close_img {
				position: absolute;
				top: 50%;
				right: 30rpx;
				transform: translateY(-50%);
				width: 44rpx;
				height: 44rpx;
			}
		}
		.main_content {
			display: flex;
			flex-direction: column;
			padding: 0 30rpx 30rpx;
			background-color: #fff;
		}
	}
}

.video-order-list{
	padding: 20rpx;
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 20rpx;
	
	.left{
		display: flex;
		flex: 1;
		
		.image-box{
			width: 50px;
			height: 50px;
		}
		
		.box-flex{
			flex: 1;
			display: flex;
			flex-direction: column;
			flex-flow: column;
			justify-content: space-between;
			margin: 0 10px;
			
			.tit{
				font-size: 30rpx;
			}
			
			.time{
				color: #999999;
			}
		}
	}
	
	.right{
		display: flex;
		flex-direction: column;
		flex-flow: column;
		text-align: right;
		
		.price{
			font-size: 32rpx;
			color: #000;
		}
		.ticheng{
			color: #999999;
		}
	}
}
</style>
