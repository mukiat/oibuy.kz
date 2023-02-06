<template>
	<section class="drp-finish">
		<header class="header padding-all">
			<h4 class="color-3  f-weight">
				<span class="color-7">{{$t('lang.gongxi')}}</span>{{$t('lang.drp_status_propmt_4')}}</h4>
			<p class="f-03 color-7 m-top06">{{$t('lang.drp_status_propmt_5')}}</p>
		</header>
		<router-link :to="{name:'drp'}" class="p-r dis-box detail color-white">
			<div class="left">
				<div class="icon">
					<i class="iconfont icon-dianpu  text-center"></i>
				</div>
			</div>
			<div class="box-flex">
				<h4 class="f-06 f-weight  m-top04">{{drpRegendData.shop_name}}</h4>
				<p class="f-03 m-top06">{{drpRegendData.mobile}}</p>
			</div>
			<div class="right f-02">
				<span>{{$t('lang.go_drp_center')}}</span>
				<i class="iconfont icon-more f-02"></i>
			</div>
			<div class="drp-tag f-02">{{$t('lang.shop_detail')}}</div>
		</router-link>
		<article class="padding-all color-7">
			<h4 class="f-06 m-top08">{{$t('lang.must_be_read')}}</h4>
			<ul class="m-top08 f-02">
				<li class="m-top02" v-html="drpRegendData.novice"></li>
			</ul>
		</article>
	</section>
</template>
<script>
	import { mapState } from 'vuex'
	import {
		Toast
	} from 'vant'
	export default {
		name: "drp-finish",
		components: {
			[Toast.name]: Toast
		},
		created() {
			Toast.loading({
				duration: 500,
				mask: true,
				message: this.$t('lang.loading')
			}, this.$store.dispatch('setDrpRegend'));
		},
		computed: {
			...mapState({
				drpRegendData: state => state.drp.drpRegendData,
			})
		}
	};
</script>
