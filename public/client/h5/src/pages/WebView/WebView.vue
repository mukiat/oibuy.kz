<template>
	<div>
		<div class="shopping-prompt ts-2 active" v-if="isWx"><img src="../../assets/img/liulanqi.png" /></div>
	</div>
</template>

<script>
import isApp from '@/mixins/is-app'
export default{
	mixins: [isApp],
	data(){
		return{
			url:this.$route.query.url,
			isWx:false
		}
	},
	created(){
		if(isApp.isWeixinBrowser()){
			this.isWx = true
		}else{
			this.isWx = false
			window.location.href = this.url
		}
	},
	methods:{

	}
}
</script>