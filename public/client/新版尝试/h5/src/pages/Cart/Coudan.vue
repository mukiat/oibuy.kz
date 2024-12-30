<template>
	<div class="con" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<div class="header-list-goods">
			<Search :mode="mode" :isFilter='isFilter' v-on:getViewSwitch="handleViewSwitch"></Search>
			<FilterTab :filter="filter" :filterStyle="filterStyle" :isPopupVisible="isPopupVisible" v-on:getFilter='handleFilter' @setPopupVisible="setPopupVisible"></FilterTab>
		</div>
		<section class="product-list" :class="{'product-list-medium':mode === 'medium'}">
			<ProductList v-if="coudanGoodsList" :data="coudanGoodsList" :routerName="routerName"></ProductList>
			<div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
			<template v-if="loading">
				<van-loading type="spinner" color="black" />
			</template>
		</section>
	</div>
</template>
<script>
import { mapState } from 'vuex'

import {
	Popup,
	Switch,
	Field,
	Cell,
	CellGroup,
	Checkbox,
	CheckboxGroup,
	Waterfall,
	Loading
} from 'vant'

import Search from '@/components/Search'
import FilterTab from '@/components/filter/FilterTab'
import ProductList from '@/components/ProductList'
import arrRemove from '@/mixins/arr-remove'

export default{
	data(){
		return {
			mode:'medium',
			loading:true,
			isFilter:true,
			filter:{
		      	sort:'goods_id',
				order:'desc',
				goods_num:'0',
				promote:'0',
				min:'',
				max:'',
				brand_id:[],
				brandResult:[],
				brandResultName:'',
				self:'0',
	      	},
	      	filterStyle:'coudan',
	      	isPopupVisible:false,
	      	routerName:'goods',
	      	act_id:this.$route.query.act_id ? this.$route.query.act_id : 0,
			page:1,
			size:10,
			footerCont:false
		}
	},
	directives: {
    	WaterfallLower: Waterfall('lower')
	},
	components:{
		[Field.name] : Field,
		[Popup.name] : Popup,
		[Switch.name] : Switch,
		[Cell.name] : Cell,
		[CellGroup.name] : CellGroup,
		[Checkbox.name] : Checkbox,
		[CheckboxGroup.name] : CheckboxGroup,
		[Loading.name] : Loading,
		FilterTab,
		ProductList,
		Search
	},
	created(){
		this.getGoodsList()
		this.onCoudanInfo()
	},
	computed:{
		coudanGoodsList:{
			get(){
				return this.$store.state.shopping.coudanGoodsList
			},
			set(val){
				this.$store.state.shopping.coudanGoodsList = val
			}
		},
		coudanInfo:{
			get(){
				return this.$store.state.shopping.coudanInfo
			},
			set(val){
				this.$store.state.shopping.coudanInfo = val
			}
		}
	},
	methods:{
		getGoodsList(page){
			let sort = 0

			if(page){
				this.page = page
				this.size = Number(page) * 10
			}

			if(this.filter.sort == 'goods_id'){
				sort = 0
			}else if(this.filter.sort == 'shop_price'){
				sort = 1
			}else if(this.filter.sort == 'sales_volume'){
				sort = 2
			}

			this.$store.dispatch('setCoudanGoodsList',{
				act_id:this.act_id,
				page:this.page,
				size:this.size,
				sort:sort,
				order:this.filter.order
			})
		},
		onCoudanInfo(){
			this.$store.dispatch('setCoudan',{
				act_id:this.act_id
			})
		},
		handleFilter(o){
	    	this.filter.sort = o.sort
	    	this.filter.order = o.order

	    	this.getGoodsList(1)
	    },
	    setPopupVisible(val){
	    	this.isPopupVisible = val;
	    },
	    handleViewSwitch(val){
	    	this.mode = val;
	    },
	    loadMore(){
			setTimeout(() => {
				this.disabled = true
		    	if(this.page * this.size == this.coudanGoodsList.length){
		  			this.page ++
		  			this.getGoodsList()	
		  		}
			},200)
	    }
	},
	watch:{
		coudanGoodsList(){
			if(this.page * this.size == this.coudanGoodsList.length){
				this.disabled = false
				this.loading = true
			}else{
				this.loading = false
				this.footerCont = this.page > 1 ? true : false
			}

			this.coudanGoodsList = arrRemove.trimSpace(this.coudanGoodsList)
		},
	}
}
</script>