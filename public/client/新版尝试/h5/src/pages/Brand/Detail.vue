<template>
	<div class="con" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<div class="brand-list-info">
			<Header :data="brandMsg" :outer="outer"></Header>
			<div class="shopping-list">
				<div class="shopping-list-nav box-flex">
					<swiper :options="swiperOption">
						<swiper-slide v-for="(item, index) in brandCategory" :key="index" :class="{'active':cat_id == item.cat_id}"><span @click="tabCategory(item.cat_id)">{{ item.cat_name }}</span></swiper-slide>
					</swiper>
				</div>
			</div>
			<div class="shopping-list-nums">
				<ul class="dis-box text-center">
					<li @click="tabNavNums()"><h5>{{ brandNavNums.all_goods }}</h5><p>{{$t('lang.all_goods')}}</p></li>
					<li @click="tabNavNums('new')"><h5>{{ brandNavNums.new_goods }}</h5><p>{{$t('lang.new')}}</p></li>
					<li @click="tabNavNums('hot')"><h5>{{ brandNavNums.hot_goods }}</h5><p>{{$t('lang.hot')}}</p></li>
				</ul>
			</div>
			<div class="shopping-info-hot product-list product-list-medium">
				<template v-if="tabStatus">
					<ProductList :data="brandProduct" :routerName="routerName" :productOuter="productOuter"></ProductList>
					<div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
					<template v-if="loading">
						<van-loading type="spinner" color="black" />
					</template>
				</template>
				<template v-else>
		            <van-loading type="spinner" color="black" size="3rem" class="loading-mar-5" />
		        </template>
			</div>
		</div>

		<!--初始化loading-->
    	<DscLoading :dscLoading="dscLoading"></DscLoading>

    	<!-- 快捷菜单 -->
        <CommonNav></CommonNav>
	</div>
</template>

<script>
import { mapState } from 'vuex'

import {
	Waterfall,
	Loading
} from 'vant'

import {
    swiper,
    swiperSlide
} from 'vue-awesome-swiper'

import ProductList from '@/components/ProductList'
import Header from './components/Header'
import DscLoading from '@/components/DscLoading'
import arrRemove from '@/mixins/arr-remove'
import CommonNav from '@/components/CommonNav'

export default{
	data(){
		return{
			outer:false,
			productOuter:true,
			cat_id:0,
			type:0,
			swiperOption:{
				notNextTick: true,
				watchSlidesProgress: true,
				watchSlidesVisibility: true,
				slidesPerView: 'auto',
				lazyLoading: true,
			},
			routerName:'goods',
			disabled:false,
			loading:true,
			page:1,
			size:10,
			tabStatus:true,
			dscLoading:true,
			footerCont:false
		}
	},
	directives: {
    	WaterfallLower: Waterfall('lower')
  	},
	components:{
		swiper,
	    swiperSlide,
	    Header,
	    ProductList,
	    DscLoading,
	    CommonNav,
	    [Loading.name]:Loading,
	},
	created(){
		this.brandDetail()
		this.brandProductList(1)
	},
	computed:{
		...mapState({
			brandCategory: state => state.brand.brandCategory,
			brandMsg: state => state.brand.brandMsg,
			brandNavNums: state => state.brand.brandNavNums
		}),
		brandProduct:{
			get(){
				return this.$store.state.brand.brandProduct
			},
			set(val){
				this.$store.state.brand.brandProduct = val
			}
		}
	},
	methods:{
		brandDetail(){
			this.$store.dispatch('setBrandDetail',{
				brand_id:this.$route.params.id,
				cat:this.cat_id
			})
		},
		brandProductList(page){
			if(page){
                this.page = page
                this.size = Number(page) * 10
            }
            
			this.$store.dispatch('setBrandProduct',{
				brand_id:this.$route.params.id,
				cat:this.cat_id,
				sort:'',
        		type:this.type,
				size:this.size,
				page:this.page,
				warehouse_id:0,
				area_id:0,
				area_city:0
			})
		},
		tabCategory(val){
			this.cat_id = val
			this.tabStatus = false
			this.brandProductList(1)
			this.brandDetail()
		},
		tabNavNums(val){
			this.type = val
			this.tabStatus = false
			this.brandProductList(1)
		},
		loadMore(){
			setTimeout(() => {
				this.disabled = true
		    	if(this.page * this.size == this.brandProduct.length){
		  			this.page ++
		  			this.brandProductList()	
		  		}
	    	},200);
    	}
	},
	watch:{
  		brandProduct(){
  			this.tabStatus = true
  			this.dscLoading = false
	  		if(this.page * this.size == this.brandProduct.length){
	  			this.disabled = false
	  			this.loading = true
	  		}else{
	  			this.loading = false
	  			this.footerCont = this.page > 1 ? true : false
	  		}

	  		this.brandProduct = arrRemove.trimSpace(this.brandProduct)
  		}
	}
}
</script>