<template>
	<div class="con">
		<div class="con_main">
			<!--出价记录-->
			<div class="auction-price">
				<van-cell-group class="common-cell">
					<van-cell class="f-04" :title="$t('lang.bid_record')" :value="auctionLogData.auction_count + $t('lang.ren')" />
				</van-cell-group>
				<!--list-->
				<div class="list bg-color-write" v-for="(item,index) in auctionLogData.auction_log" :key="index">
					<div class="dis-box ">
						<div class="box-flex f-03 color-3">
							<van-tag type="danger" class="m-r05  br-100 btn-submit" v-if="index==0">{{$t('lang.au_bid_ok')}}</van-tag>
							<van-tag type="danger" class="m-r05  br-100 btn-default" v-else>{{$t('lang.out')}}</van-tag>
							<span class="f-04 color-3">{{item.user_name}}</span>
						</div>
						<div class="f-02 color-9">{{item.bid_time}}</div>
					</div>
					<div class="f-04 color-red" v-html="item.bid_price"></div>
				</div>
			</div>
		</div>
		<CommonNav/>
	</div>
</template>
<script>
	import { mapState } from 'vuex'
	import {
		Cell,
		CellGroup,
		Tag,
	} from 'vant'
	import CommonNav from '@/components/CommonNav'
	import NotCont from '@/components/NotCont'
	export default {
		name: "auction-log",
		components: {
			CommonNav,
			NotCont,
			[Cell.name]: Cell,
			[CellGroup.name]: CellGroup,
			[Tag.name]: Tag,
		},
		//初始化加载数据
		created() {
			this.$store.dispatch({
				type: 'setAuctionLog',
				id: this.$route.params.act_id
			})
		},
		computed: {
			...mapState({
				auctionLogData: state => state.auction.auctionLogData,
			})
		}
	}
</script>
