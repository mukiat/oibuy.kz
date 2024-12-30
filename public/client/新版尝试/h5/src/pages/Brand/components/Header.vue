<template>
	<div class="brand-cont-header">
		<div class="my-brand-header">
			<div class="goods-shop-info shopping-info-title">
				<section class="dis-box s-i-title-con">
					<div class="g-s-i-img"><img :src="data.brand_logo"></div>
					<div class="g-s-i-title box-flex">
						<h3 class="ellipsis-one box-flex">{{ data.brand_name }}</h3>
						<p class="t-remark m-top04">{{$t('lang.owned_all')}} {{ data.goods_count }} {{$t('lang.goods_letter')}}</p>
					</div>
					<template v-if="outer">
						<div class="g-s-info-add">
						<router-link :to="{ name: 'brandDetail', params: { id: data.brand_id }}">{{$t('lang.enter_zone')}}</router-link>
						</div>
					</template>
				</section>
				<!-- <img class="bg" :src="data.brand_msg.brand_bg" v-if="data.brand_msg && data.brand_msg.brand_bg">
				<img class="bg" src="../../../assets/img/shopping_info_b.png" v-else> -->
			</div>
		</div>
	</div>
</template>

<script>
export default{
	props:['outer','data'],
	data(){
		return {}
	}
}
</script>