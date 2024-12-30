<template>
	<header class="goods-shop-info padding-all">
		<section class="dis-box">
			<div class="g-s-i-img" @click="shopDetail(shopInfo[index].ru_id)"><img :src="shopInfo[index].logo" class="img"></div>
			<div class="g-s-i-title box-flex" @click="shopDetail(shopInfo[index].ru_id)">
				<h3>{{ shopInfo[index].shopName }}<van-icon name="shop" /></h3>
				<p>{{$t('lang.collection_one')}} <span>{{ shopInfo[index].count_gaze }}</span> {{$t('lang.collection_two')}}</p>
			</div>
			<div class="g-s-info-add">
				<a href="javascript:void(0);" :class="{'active':shopInfo[index].is_collect_shop == 0}" @click="collectHandle(shopInfo[index].ru_id)">
					<span v-if="shopInfo[index].is_collect_shop == 1">{{$t('lang.followed')}}</span>
					<span v-else>{{$t('lang.did_not_concern')}}</span>
				</a>
			</div>
		</section>
		<template v-if="shopScore">
		<div class="goods-shop-score dis-box">
			<p class="box-flex">
      	<label class="fl">{{$t('lang.goods')}}</label>
      	<span class="color-red margin-lr fl">{{shopInfo[index].commentrank}}</span>
      	<em class="em-promotion fl">{{shopInfo[index].commentrank_font}}</em>
      </p>
      <p class="box-flex">
      	<label class="fl">{{$t('lang.service')}}</label>
      	<span class="color-low margin-lr fl">{{shopInfo[index].commentserver}}</span>
      	<em class="em-promotion em-p-low fl">{{shopInfo[index].commentserver_font}}</em>
      </p>
      <p class="box-flex">
      	<label class="fl">{{$t('lang.commentdelivery')}}</label>
      	<span class="color-center margin-lr fl">{{shopInfo[index].commentdelivery}}</span>
      	<em class="em-promotion em-p-center fl">{{shopInfo[index].commentdelivery_font}}</em>
      </p>
		</div>
		</template>
	</header>
</template>

<script>
import { Dialog, Icon } from 'vant'

export default{
	props:['shopInfo','index','shopScore'],
	data(){
		return{
			distance:false,
		}
	},
	components:{
		[Dialog.name]:Dialog,
		[Icon.name]:Icon
	},
	created(){
	},
	computed:{
		isLogin(){
 			return localStorage.getItem('token') == null ? false : true
 		},
 		shopCollectStatue(){
			return this.$store.state.user.shopCollectStatue
		},
 		is_collect_shop(){
 			return this.shopInfo[this.index].is_collect_shop
 		},
 		ru_id(){
 			return this.shopInfo[this.index].ru_id
 		}
	},
	methods:{
		shopDetail(val){
			this.$router.push({
				name:'shopHome',
				params:{
					id:val
				}
			})
		},
		collectHandle(val){
			if(this.isLogin){
				this.$store.dispatch('setCollectShop',{
					ru_id:val,
					status:this.is_collect_shop
				})
			}else{
				let msg = this.$t('lang.fill_in_user_collect_goods')
				this.notLogin(msg)
			}
		},
		notLogin(msg){
			Dialog.confirm({
				message:msg,
				className:'text-center'
			}).then(()=>{
				this.$router.push({
					path: '/login',
					query:{ redirect: {name:'shopDetail',params:{ id:this.ru_id }}}
				})
			}).catch(()=>{

			})
		},
	},
	watch:{
		shopCollectStatue(){
			this.shopCollectStatue.forEach((v)=>{
				if(v.id == this.ru_id){
					this.$emit('update',{
	 					is_collect_shop:v.status
	 				})
				}
			})
		}
	}
}
</script>