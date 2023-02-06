<template>
    <view class="blank" :style="{height:sHeight}"></view>
</template>

<script>
export default{
	props: ['module', 'preview'],
	data(){
		return {}
	},
	computed: {
		sHeight(){
			return Number(this.module.allValue.height)+'px'
		}
	}
}
</script>

<style>
	
</style>
