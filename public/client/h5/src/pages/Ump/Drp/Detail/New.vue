<template>
	<div class="con">
		<template v-if="drpNewsData!=''">
			<van-collapse v-model="activeNames" accordion class="drp-new">
				<van-collapse-item :title="item.title" :name="index" v-for="(item,index) in drpNewsData" :key='index'>
					<div v-html="item.content" class="drp-new-html"></div>
				</van-collapse-item>
			</van-collapse>
		</template>
		<template v-else>
			<NotCont/>
		</template>
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
	import NotCont from '@/components/NotCont'
	import CommonNav from '@/components/CommonNav'
	import { mapState } from 'vuex'
	import {
		Collapse,
		CollapseItem,
		Toast
	} from 'vant'
	export default {
		name: "drp-new",
		components: {
			CommonNav,
			NotCont,
			[Toast.name]: Toast,
			[Collapse.name]: Collapse,
			[CollapseItem.name]: CollapseItem
		},
		data() {
			return {
				activeNames: ["1"],
				routerName:'drp',
			};
		},
		computed: {
			...mapState({
				drpNewsData: state => state.drp.drpNewsData,
			})
		},
		mounted() {
			Toast.loading({
				duration: 500,
				mask: true,
				message: this.$t('lang.loading')
			}, this.$store.dispatch('setDrpNews'));

		}
	};
</script>
