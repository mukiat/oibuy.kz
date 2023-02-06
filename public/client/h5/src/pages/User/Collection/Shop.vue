<template>
  <div class="store_info" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
  	<template v-if="shopCollectList != ''">
	  	<section class="goods-shop" :class="{'m-top04' : index > 0}" v-for="(item,index) in shopCollectList" :key="index">
	  		<div class="padding-all">
	  			<section class="dis-box">
					<div class="g-s-i-img">
						<router-link :to="{name:'shopHome',params: { id: item.ru_id }}">
							<img :src="item.shop_logo" class="img" v-if="item.shop_logo">
							<img src="../../../assets/img/no_image.jpg" alt="" class="img" v-else>
						</router-link>
					</div>
					<div class="g-s-i-title">
						<h3><router-link :to="{name:'shopHome',params: { id: item.ru_id }}">{{ item.shoprz_brand_name }}{{ item.shop_name_suffix }}</router-link></h3>
						<p>{{$t('lang.collection_one')}} {{ item.count_store }} {{$t('lang.collection_two')}}</p>
					</div>
					<div class="g-s-info-add"><a href="javascript:void(0)" class="active" @click="collectHandle(item.ru_id)">{{$t('lang.cancel_collection')}}</a></div>
				</section>
	  		</div>
	  	</section>
  	</template>
  	<template v-else>
  		<NotCont />
  	</template>
  	<CommonNav></CommonNav>
  </div>
</template>

<script>
import { mapState } from 'vuex'

import {
    Waterfall,
} from 'vant'

import NotCont from '@/components/NotCont'
import CommonNav from '@/components/CommonNav'
import arrRemove from '@/mixins/arr-remove'

export default{
	data(){
		return {
			disabled:false,
			loading:true,
			page:1,
			size:10,
		}
	},
	directives: {
		WaterfallLower: Waterfall('lower')
	},
	components:{
		NotCont,
		CommonNav
	},
	created(){
		this.setShopList(1)
	},
	computed:{
		shopCollectList:{
			get(){
				return this.$store.state.user.shopCollectList
			},
			set(val){
				this.$store.state.user.shopCollectList = val
			}
	    }
	},
	methods:{
		setShopList(page){
	        if(page){
	            this.page = page
	            this.size = Number(page) * 10
	        }
			this.$store.dispatch('setCollectShopList',{
				page:this.page,
          		size:this.size,
			})
		},
		collectHandle(val){
			this.$store.dispatch('setCollectShop',{
				ru_id:val,
				status:1
			})
		},
		loadMore(){
			setTimeout(() => {
				this.disabled = true
				if(this.page * this.size == this.shopCollectList.length){
					this.page ++
					this.setShopList() 
				}
			},200);
		},
	},
	watch:{
		shopCollectList(){
			if(this.page * this.size == this.shopCollectList.length){
				this.disabled = false
				this.loading = true
			}else{
				this.loading = false
			}
			this.shopCollectList = arrRemove.trimSpace(this.shopCollectList)
		}
	}
}
</script>
