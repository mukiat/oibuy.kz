<template>
	<div class="footer-nav">
		<van-tabbar class="ect-tabbar" v-model="active" fixed>
			<van-tabbar-item icon="home-o" to="/bargain">{{$t('lang.home_bargain')}}</van-tabbar-item>
			<van-tabbar-item icon="contact" to="/bargain/mylist">{{$t('lang.my_bargain')}}</van-tabbar-item>
		</van-tabbar>
	</div>
</template>

<script>
	import {
		Tabbar,
		TabbarItem
	} from 'vant';
	export default {
		name: "bargain-tabbar",
		components: {
			[Tabbar.name]: Tabbar,
			[TabbarItem.name]: TabbarItem
		},
		data() {
			return {
				active: 0
			}
		},
		mounted: function () {
			let current = this.$route.path.substr(1)
			let nav = ['bargain',  'bargain/mylist']
			this.active = nav.indexOf(current)
		}
	}
</script>
