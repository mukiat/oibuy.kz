<template>
	<div class="product-list product-list-small" v-if="goodsInfo">
		<ul>
			<li>
				<div class="product-div">
					<div class="product-list-img"><img class="img" :src="goodsInfo.goods_img"></div>
					<div class="product-info">
						<h4>{{ goodsInfo.goods_name }}</h4>
						<div class="price">
							<em>{{ goodsInfo.shop_price_formated }}</em>
							<span>x1</span>
						</div>
						<div class="p-t-remark">{{ goodsInfo.attr_name }}</div>
		          	</div>
				</div>
			</li>
		</ul>
	</div>
</template>
<script>
export default{
	props:['goodsInfo'],
	data(){
		return {}
	}
}
</script>