<template>
	<view class="dsc_nav_bar" :style="{backgroundColor: bgc}">
		<view class="nav_item" :class="navIndex == index ? 'active' : ''" v-for="(item,index) in list" :key="index" @click="tapNav(index)">
			<text>{{ item.nav_name }}</text>
		</view>
		<view class="navigation_bars" :style="{left: nav_bars_left}"></view>
	</view>
</template>

<script>
export default {
	props: {
		list: {
			type: Array,
			required: true
		},
		navIndex: {
			type: Number,
			default: 0
		},
		bgc: {
			type: String,
			default: '#FFFFFF'
		}
	},
	computed: {
		nav_bars_left: function () {
			return (this.navIndex + 0.5) / this.list.length * 100 + '%';
		}
	},
	methods: {
		tapNav(i) {
			if (this.navIndex == i) return;
			this.$emit('change-index', i);
		}
	}
}
</script>

<style scoped>
.dsc_nav_bar {
	position: relative;
	display: flex;
	justify-content: space-between;
	height: 100upx;
	box-sizing: border-box;
}
.nav_item {
	flex: 1;
	text-align: center;
	line-height: 100upx;
}
.navigation_bars {
	position: absolute;
	bottom: 0;
	transition: all .3s;
	transform: translateX(-50%);
	width:34upx;
	height:8upx;
	background:rgba(249,41,41,1);
	border-radius:4upx;
}
.nav_item text {
	font-size: 30upx;
	font-weight: 700;
	color: #000000;
}
</style>
