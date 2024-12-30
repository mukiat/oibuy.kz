<template>
	<div class="dsc_nav_bar">
		<div class="nav_title_box" :style="nav_bars_style">
			<div class="nav_item" :class="navIndex == index ? 'active' : ''" v-for="(item,index) in list" :key="index" @click="tapNav(index)">
				<span>{{ item }}</span>
			</div>
			<div class="navigation_bars" :style="{left: nav_bars_left}"></div>
		</div>
		<div class="tabs_content">
			<div class="tabs_item" :style="{display: navIndex != tabIndex ? 'none' : ''}" v-for="(tabItem, tabIndex) in list" :key="tabIndex">
				<slot :name="tabIndex"></slot>
			</div>
		</div>
	</div>
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
		fixed: {
			type: Boolean,
			default: false
		},
		sticky: {
			type: Boolean,
			default: false
		},
		bgc: {
			type: String,
			default: '#FFFFFF'
		}
	},
	data() {
		return {
			scrollTopObj: {},
			offsetTop: 0,
			navBarFixed: false
		}
	},
	computed: {
		nav_bars_left: function () {
			return (this.navIndex + 0.5) / this.list.length * 100 + '%';
		},
		nav_bars_style: function () {
			return {
				backgroundColor: this.bgc,
				position: this.navBarFixed || this.fixed ? 'fixed' : 'absolute',
				top: this.fixed ? this.offsetTop : '0'
			};
		}
	},
	mounted() {
		this.offsetTop = document.querySelector('.dsc_nav_bar').offsetTop;

		if (this.sticky) {
			var passiveSupported = false;

			try {
				var options = Object.defineProperty({}, "passive", {
					get: function() {
					passiveSupported = true;
					}
				});

				window.addEventListener('scroll', this.handleScroll, passiveSupported ? { passive: true } : false);
			} catch(err) {
				window.addEventListener('scroll', this.handleScroll);
			}
		}
	},
	destroyed() {
		if (this.sticky) window.removeEventListener('scroll', this.handleScroll);
	},
	methods: {
		tapNav(i) {
			if (this.navIndex == i) return;
			this.$emit('change-index', i);
		},
		handleScroll(e) {
			let scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
			scrollTop > this.offsetTop ? this.navBarFixed = true : this.navBarFixed = false;
		}
	}
}
</script>

<style scoped>
.dsc_nav_bar {
	position: relative;
	height: 100%;
	padding-top: 44px;
	box-sizing: border-box;
}
.nav_title_box {
	top: 0;
	left: 0;
	right: 0;
	display: flex;
	justify-content: space-between;
	height: 44px;
	box-sizing: border-box;
	z-index: 999;
}
.nav_item {
	flex: 1;
	text-align: center;
	line-height: 44px;
}
.navigation_bars {
	position: absolute;
	bottom: 0;
	transition: all .3s;
	transform: translateX(-50%);
	width:17px;
	height:4px;
	background:rgba(249,41,41,1);
	border-radius:2px;
}
.nav_item span {
	font-size:15px;
	font-weight: 700;
	color: #000000;
}
</style>
