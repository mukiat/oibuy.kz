<template>
	<view>
		<view class="uni-card uni-card-not">
			<view class="uni-list">
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.bid_record')}}</text>
						<view class="value" v-if="auctionLogData.auction_count">{{auctionLogData.auction_count}}{{$t('lang.ren')}}</view>
					</view>
				</view>
			</view>
			<view class="auction-log">
				<view class="log-item" v-for="(item,index) in auctionLogData.auction_log" :key="index">
					<view class="lie">
						<view class="name">
							<uni-tag :text="$t('lang.au_bid_ok')" size="small" type="error" v-if="index == 0"></uni-tag>
							<uni-tag :text="$t('lang.offer_a_price')" size="small" type="error" v-else></uni-tag>
							<text v-if="item.user_name">{{item.user_name}}</text>
						</view>
						<view class="time" v-if="item.bid_time">{{item.bid_time}}</view>
					</view>
					<view class="uni-red" v-if="item.formated_bid_price">{{item.formated_bid_price}}</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	
	export default {
		data() {
			return {
				act_id:0
			}
		},
		computed: {
			...mapState({
				auctionLogData: state => state.ump.auctionLogData,
			})
		},
		methods: {
			
		},
		onLoad(e){
			this.act_id = e.act_id
			
			this.$store.dispatch('setAuctionLog',{
				id: this.act_id
			})
		},
		watch:{
			auctionLogData(){
				console.log(this.auctionLogData)
			}
		}
	}
</script>

<style>

</style>
