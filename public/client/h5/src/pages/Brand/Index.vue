<template>
	<div class="con">
		<div class="brand-cont-box">
			<div class="bg-img"><img src="../../assets/img/brand_header_bg.jpg" class="img"></div>
			<div class="dis-box cont-box">
				<div class="box-flex"><router-link :to="{ name: 'brandDetail', params: { id: best.brand_id }}" class="img-box"><img :src="best.index_img" class="img"></router-link></div>
				<div class="box-flex">
					<div class="cont">
						<h4>{{$t('lang.big_day_brand')}}</h4>
						<p>{{$t('lang.daily_brand_promotion')}}</p>
					</div>
					<div class="cont">
						<h5>{{$t('lang.date')}}：<label class="color-red">{{ best.time }}</label></h5>
						<span>{{$t('lang.brand')}}：<label><router-link :to="{ name: 'brandDetail', params: { id: best.brand_id }}">{{ best.brand_name }}</router-link></label></span>
					</div>
				</div>
			</div>
		</div>
		<div class="brand_section m-top08">
			<div class="brand_section_title"><h3><span>{{$t('lang.hot_big')}}</span></h3></div>
			<van-swipe class="hot-brand_street">
				<van-swipe-item v-for="(item,index) in hot" :key="index">
					<section class="brand-name-list-box">
						<Header :outer="outer" :data="item.brand_msg"></Header>
						<div class="brand-list-box dis-box">
							<template v-for="(goodsItme,goodsIndex) in item.goods">
								<div class="brand-left-list-box" v-if="goodsIndex == 0">
									<router-link :to="{ name: 'goods', params: { id: goodsItme.goods_id }}">
										<img :src="goodsItme.goods_thumb">
									</router-link>
								</div>
							</template>
							<div class="brand-right-list-box">
								<template v-for="(goodsItme,goodsIndex) in item.goods">
									<div class="brand-right-box-1" v-if="goodsIndex > 0 && goodsIndex < 3">
										<router-link :to="{ name: 'goods', params: { id: goodsItme.goods_id }}">
											<img :src="goodsItme.goods_thumb">
										</router-link>
									</div>
								</template>
							</div>
						</div>
					</section>
				</van-swipe-item>
			</van-swipe>
		</div>
		<div class="brand_section m-top08">
			<div class="brand_section_title wallet-bt"><h3><span>{{$t('lang.recommend_big')}}</span></h3></div>
			<ul class="my-brand-login">
				<li v-for="(item,index) in recommend" :key="index">
					<router-link :to="{ name: 'brandDetail', params: { id: item.brand_id }}">
						<img :src="item.brand_logo" />
					</router-link>
				</li>
				<li class="my-brand-text"><router-link :to="{name:'brandList'}">{{$t('lang.view_more')}}</router-link></li>
			</ul>
		</div>
	</div>
</template>

<script>
import { Swipe, SwipeItem } from 'vant'
import Header from './components/Header'

export default{
	data(){
		return{
			outer:true,
			best:{},
			hot:[],
			recommend:[]
		}
	},
	components:{
		[Swipe.name] : Swipe,
		[SwipeItem.name] : SwipeItem,
		Header
	},
	created(){
		this.$store.dispatch('setBrand').then(res=>{
			this.best = res.best
			this.hot = res.hot
			this.recommend = res.recommend
		})
	},
	computed:{
	}
}
</script>