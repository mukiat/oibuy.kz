<template>
	<div class="drp-rank">
		<div class="header">
			<img src="../../../../assets/img/newuser/rank_bg.jpg" class="img" />
			<div class="cont p-a">
				<span>{{$t('lang.label_your_rank')}}</span>
				<span>{{drpRankData.rank}}</span>
				<span>{{$t('lang.ming')}}</span>
			</div>
		</div>
		<div class="rank-box">
			<div class="title">
				<div class="t1">{{$t('lang.rank_ming')}}</div>
				<div class="t2">{{$t('lang.user')}}ID</div>
				<div class="t3">{{$t('lang.share_zhuan')}}</div>
			</div>
			<div class="rank-list">
				<router-link :to="{name:'drp-shop',query:{shop_id:item.shop_id}}" v-for="(item,index) in drpRankData.list"
				    :key="index" class="item">
					<div class="left">
						<div class="icon-rank">
							<img v-if="item.rank===1" class="img" src="../../../../assets/img/newuser/rank1.png" />
							<img v-if="item.rank===2" class="img" src="../../../../assets/img/newuser/rank2.png" />
							<img v-if="item.rank===3" class="img" src="../../../../assets/img/newuser/rank3.png" />
							<span class="rank" v-if="item.rank>'3'">{{item.rank}}</span>
						</div>
					</div>
					<div class="center">
						<div class="via">
							<img v-if="item.user_picture" class="img" :src="item.user_picture" />
							<img v-else class="img" src="../../../../assets/img/user_default.png" />
						</div>
						<h4 class="onelist-hidden">{{item.user_name}}</h4>
					</div>
					<div class="right">
						<p class="price" v-html="item.money"></p>
						<i class="iconfont icon-more" />
					</div>
				</router-link>
			</div>
		</div>
		<CommonNav :routerName="routerName">
	         <li slot="aloneNav">
				<router-link :to="{name: 'drp'}">
					<i class="iconfont icon-fenxiao"></i>
					<p>{{$t('lang.drp_center')}}</p>
				</router-link>
			</li>
	    </CommonNav>
	</div>
</template>
<script>
	import CommonNav from '@/components/CommonNav'
	import { mapState } from 'vuex'
	export default {
		name: "drp-rank",
		components: {
			CommonNav,
		},
		data() {
			return {
				routerName:'drp'
			};
		},
		computed: {
			...mapState({
				drpRankData: state => state.drp.drpRankData,
			})
		},
		mounted() {
			this.$store.dispatch('setDrpRank')
		},
		methods: {}
	};
</script>
