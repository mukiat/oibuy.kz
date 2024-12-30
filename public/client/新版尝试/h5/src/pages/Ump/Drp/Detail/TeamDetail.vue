<template>
	<div class="drp-rankdetail">
		<header class="header">
			<div class="header-img">
				<img v-if="drpTeamDetailData.user_picture" class="img" :src="drpTeamDetailData.user_picture" />
				<img v-else class="img" src="../../../../assets/img/user_default.png" />
			</div>
			<div class="admin color-white">
				<h4 class="f-05">{{drpTeamDetailData.shop_name}}</h4>
				<p class="f-03 m-top04" v-if="drpTeamDetailData.get_drp_shop && drpTeamDetailData.get_drp_shop.create_time">{{$t('lang.label_open_shop_time')}}{{drpTeamDetailData.get_drp_shop.create_time}}</p>
			</div>
			<router-link :to="{name:'drp-shop',query:{shop_id:drpTeamDetailData.get_drp_shop.id}}" class="show dis-set" v-if="drpTeamDetailData.get_drp_shop">
				<i class="iconfont icon-dianpu text-center color-white f-06"></i>
			</router-link>
		</header>
		<van-row class="cont padding-all bg-color-write">
			<van-col span="12">
				<p class="color-7 f-03">{{$t('lang.cumulative')}}</p>
				<h4 class="color-red">{{drpTeamDetailData.sum_money}}</h4>
			</van-col>
			<van-col span="12">
				<p class="color-7 f-03">{{$t('lang.day_cumulative')}}</p>
				<h4 class="color-3">{{drpTeamDetailData.now_money}}</h4>
			</van-col>
		</van-row>
		<div class="padding-all" v-if="drpTeamDetailData.parent_id == drpTeamDetailData.top_user_id">
			<van-button size="large" type="danger" @click="drpTeam" class="f-06 br-5 btn-submit">{{$t('lang.view_lower_user')}}</van-button>
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
	import { mapState } from 'vuex'
	import CommonNav from '@/components/CommonNav'
	import NotCont from '@/components/NotCont'
	import {
		Row,
		Col,
		Button,
		Toast
	} from 'vant'
	export default {
		name: "drp-teamdetail",
		components: {
			CommonNav,
			[Row.name]: Row,
			[Col.name]: Col,
			[Button.name]: Button,
			[Toast.name]: Toast,
		},
		data() {
			return {
				routerName:'drp',
				next_id:this.$route.query.next_id ? this.$route.query.next_id : ''
			};
		},
		created() {
			this.loading(
				this.$store.dispatch('setDrpTeamDetail',{
					user_id: this.$route.params.user_id,
					next_id: this.next_id
				})
			)
		},
		computed: {
			...mapState({
				drpTeamDetailData: state => state.drp.drpTeamDetailData,
			})
		},
		mounted() {},
		methods: {
			//加载动画
			loading(url) {
				let that = this
				Toast.loading({
					duration: 500,
					mask: true,
					message: this.$t('lang.loading')
				}, url);
			},
			//团队列表
			drpTeam() {
				this.$router.push({
					name: "drp-team",
					params: {
						user_id: this.$route.params.user_id
					}
				})
			}
		}
	};
</script>
<style>
</style>
