<template>
	<div class="product-info-warp m-b06" v-if="data">
		<router-link :to="{name:'goods',params: {id: data.goods_id}}" class="dis-box">
			<div class="product-img"><img :src="data.goods_thumb" class="img"></div>
			<div class="product-name box-flex"><p>{{ data.goods_name }}</p></div>
			<div class="user-more"><i class="iconfont icon-more"></i></div>
		</router-link>
	</div>
</template>
<script>
export default{
	props:['data'],
	data(){
		return{}
	}
}
</script>