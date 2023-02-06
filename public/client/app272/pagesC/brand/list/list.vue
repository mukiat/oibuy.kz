<template>
	<view>
		<uni-indexed-list :options="brandList" :show-brand="true" :show-select="false" @click="bindClick" />
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIndexedList from '@/components/uni-indexed-list/uni-indexed-list.vue'
	
	export default {
		data() {
			return {
				
			}
		},
		components: {
			uniIndexedList
		},
		computed:{
			...mapState({
				brandList: state => state.brand.brandList
			})
		},
		methods: {
			bindClick(e) {
				console.log('点击item，返回数据' + JSON.stringify(e))
			}
		},
		onLoad(){
			this.$store.dispatch('setBrandList')
		}
	}
</script>

<style>

</style>
