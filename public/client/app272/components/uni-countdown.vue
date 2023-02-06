<template name="uni-countdown">
	<view class="uni-countdown" :class="{'uni-countdown-not':notStyle}">
		<view :class="['uni-countdown-numbers', mini ? 'uni-countdown-numbers-p5' : '']" :style="{borderColor:borderColor, color:fontColor, background:bgrColor}" v-if="typeZero && dType">{{d}}</view>
		<view class="uni-countdown-splitor" :style="{color:splitorColor}" v-if="typeZero && dType && showColon">:</view>
		<view class="uni-countdown-splitor" :style="{color:splitorColor}" v-if="mini && dType">天</view>
		<view :class="['uni-countdown-numbers', mini ? 'uni-countdown-numbers-p5' : '']" :style="{borderColor:borderColor, color:fontColor, background:bgrColor}">{{h}}</view>
		<view class="uni-countdown-splitor" :style="{color:splitorColor}" v-if="showColon">:</view>
		<view :class="['uni-countdown-numbers', mini ? 'uni-countdown-numbers-p5' : '']" :style="{borderColor:borderColor, color:fontColor, background:bgrColor}">{{i}}</view>
		<view class="uni-countdown-splitor" :style="{color:splitorColor}" v-if="showColon">:</view>
		<view :class="['uni-countdown-numbers', mini ? 'uni-countdown-numbers-p5' : '']" :style="{borderColor:borderColor, color:fontColor, background:bgrColor}">{{s}}</view>
	</view>
</template>
<script>
	export default {
		name: "uni-countdown",
		props: {
			bgrColor: {
				type: String,
				default: "#FFFFFF"
			},
			borderColor: {
				type: String,
				default: "#000000"
			},
			fontColor: {
				type: String,
				value: "#000000"
			},
			splitorColor: {
				type: String,
				default: "#000000"
			},
			timer: {
				type: String,
				default: ""
			},
			typeZero: {
				type: Boolean,
				default: true
			},
			notStyle:{
				type: Boolean,
				default: false
			},
			showColon: {
				type: Boolean,
				default: true
			},
			mini: {
				type: Boolean,
				default: false
			}
		},
		data() {
			return {
				setTime: null,
				d: '00',
				h: '00',
				i: '00',
				s: '00',
				dType: false,
				hType: false,
				iType: false,
				sType: false,
				leftTime: 0
			}
		},
		created: function(e) {
			this.load();
		},
		beforeDestroy() {
			clearInterval(this.setTime)
		},
		methods: {
			load(){
				var reg = /^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/;
				var res = this.timer.match(reg);
				
				if (res == null) {
					return false;
				} else {
					var year = parseInt(res[1]);
					if (year < 1000) {
						return false;
					}
					var month = parseInt(res[2]);
					var day = parseInt(res[3]);
					var h = parseInt(res[4]);
					if (h < 0 || h > 24) {
						return false;
					}
					var i = parseInt(res[5]);
					if (i < 0 || i > 60) {
						return false;
					}
					var s = parseInt(res[6]);
					if (s < 0 || s > 60) {
						return false;
					}
					var leftTime = new Date(year, month - 1, day, h, i, s);
					this.leftTime = leftTime;
					this.countDown(this);
					this.setInterValFunc(this);
				}
			},
			setInterValFunc: function(obj) {
				this.setTime = setInterval(() => {
					obj.countDown(obj);
				}, 1000);
			},
			countDown: function(self) {
				var leftTime = self.leftTime - new Date();
				if (leftTime > 0) {
					var day = parseInt(leftTime / 1000 / 60 / 60 / 24, 10);
					var hours = parseInt((leftTime / 1000) % 86400 / 60 / 60, 10);
					var minutes = parseInt(leftTime / 1000 / 60 % 60, 10);
					var seconds = parseInt(leftTime / 1000 % 60, 10);
				} else {
					var hours = 0,
						minutes = 0,
						seconds = 0;
				}
				self.dType = day > 0 ? true : false;
				self.hType = hours > 0 ? true : false;
				self.iType = minutes > 0 ? true : false;
				self.sType = seconds > 0 ? true : false;
				
				if (day < 10) {
					day = '0' + day;
				}
				if (hours < 10) {
					hours = '0' + hours;
				}
				if (minutes < 10) {
					minutes = '0' + minutes;
				}
				if (seconds < 10) {
					seconds = '0' + seconds;
				}
				
				self.d = day;
				self.h = hours;
				self.i = minutes;
				self.s = seconds;
			}
		},
		watch:{
			timer(){
				this.load();
			}
		}
	}
</script>
<style>
	.uni-countdown {
		padding: 2upx 0;
		flex-wrap: nowrap;
		justify-content: center;
	}

	.uni-countdown-splitor {
		width: auto !important;
		justify-content: center;
		line-height: 44upx;
		padding: 0 5upx;
	}

	.uni-countdown-numbers {
		line-height: 38upx;
		width: auto !important;
		min-width: 20upx;
		text-align: center;
		padding: 0 10upx;
		justify-content: center;
		height: 38upx;
		border-radius: 8upx;
		margin: 0 5upx;
		border: 1px solid #000000;
		font-size: 22upx;
	}
	
	.uni-countdown-numbers-p5 {
		padding: 0 4upx;
	}
	
	.uni-countdown-not .uni-countdown-splitor{
		line-height: 1.8;
		height: auto;
		padding: 0;
		font-size: 22upx;
	}
	.uni-countdown-not .uni-countdown-numbers{
		line-height: 1.8;
		height: auto;
		border: 0;
		padding: 0;
		border-radius: 0;
	}
</style>
