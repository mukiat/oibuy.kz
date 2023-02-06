<template>
    <view class="line" :style="{height:nHeight,backgroundColor:sBgColor}"></view>
</template>

<script>
import universal from '@/common/mixins/universal.js';
export default{
	mixins:[universal],
	props: ['module', 'preview'],
	data(){
		return {}
	},
	computed: {
		hrStyle() {
			return {
				height: this.nHeight + 'px',
				backgroundColor: this.sBgColor
			}
		},
		nHeight() {
			return this.module ? this.module.allValue.height : 1
		},
		sBgColor() {
			return this.module ? this.module.allValue.bgColor : ''
			// return this.getText({
			// 	dataNext: "allValue",
			// 	attrName: "bgColor",
			// 	defaultValue: ""
			// })
		}
	}
}
</script>

<style scoped>
.line { height: 10px;border: 0;background: none; }
</style>
