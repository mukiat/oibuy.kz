<template>
	<div class="con" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<div class="header-list-goods">
			<Search :mode="mode" :isFilter='isFilter' v-on:getViewSwitch="handleViewSwitch" :app="app"></Search>
			<FilterTab :filter="filter" :isPopupVisible="isPopupVisible" v-on:getFilter='handleFilter' @setPopupVisible="setPopupVisible"></FilterTab>
		</div>
		<section class="product-list" :class="{'product-list-medium':mode === 'medium'}">
			<ProductList v-if="cateGoodsList" :data="cateGoodsList" :routerName="routerName"></ProductList>
			<div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
			<template v-if="loading">
				<van-loading type="spinner" color="black" />
			</template>
		</section>

		<CommonNav></CommonNav>

		<!--筛选-->
		<van-popup class="show-popup-right show-popup-filter" v-model="isPopupVisible" position="right">
			<div class="top">
				<div class="section">
					<div class="title"><span>Баға аралығы</span></div>
					<div class="section-warp price-filter">
						<div class="input">
							<input type="tel" v-model="filter.min" :placeholder="$t('lang.minimum_price')" />
						</div>
						<span class="hang"></span>
						<div class="input">
							<input type="tel" v-model="filter.max" :placeholder="$t('lang.top_price')" />
						</div>
					</div>
					<div class="section-warp select-tabs">
						<div class="select-list" :class="{'active':item.sn == price_filter_sn}" v-for="(item,index) in grade" :key="index" @click="onPriceFilter(item)">
							<span v-html="item.price_range"></span>
						</div>
					</div>
				</div>
				<div class="section" v-if="filter.brandResult.length > 0">
					<div class="title">
						<span>Бренд</span>
						<div class="right-icon" @click="isPopupBrand = !isPopupBrand"><i class="iconfont" :class="[isPopupBrand ? 'icon-less' : 'icon-moreunfold']" v-if="filter.brandResult.length > 9"></i></div>
					</div>
					<div class="section-warp select-tabs">
						<div class="select-list" :class="{'active':filter.brandResultArr.includes(item)}" @click="onBrandResult(item)" v-show="index < 9 || isPopupBrand" v-for="(item,index) in filter.brandResult" :key="index">
							<span>{{ item.brand_name }}</span>
						</div>
					</div>
				</div>
				<div class="section" v-for="(item,index) in attribute" :key="index">
					<div class="title">
						<span>{{item.filter_attr_name}}</span>
						<div class="right-icon" @click="isAttribute(item.filter_attr_id)"><i class="iconfont" :class="[!attribute_id.includes(item.filter_attr_id) ? 'icon-less' : 'icon-moreunfold']"></i></div>
					</div>
					<div class="section-warp select-tabs" v-show="!attribute_id.includes(item.filter_attr_id)">
						<div class="select-list" :class="{'active':filter.filter_attr[index] == attritem.goods_attr_id}" v-for="(attritem,attrindex) in item.attr_list" :key="attrindex" @click="onAttributeValue(attritem,index)">
							<span>{{attritem.attr_value}}</span>
						</div>
					</div>
				</div>
			</div>
			<div class="footer">
				<div class="btn-bar">
					<div class="btn btn-lg-white" @click="closeFilter">{{$t('lang.merchants_reset')}}</div>
					<div class="btn btn-lg-red" @click="submitFilter">{{$t('lang.confirm')}}</div>
				</div>
			</div>
		</van-popup>

		<!--初始化loading-->
	    <DscLoading :dscLoading="dscLoading"></DscLoading>

	    <ec-filter-top :scrollState="scrollState" outerClass="true"></ec-filter-top>
	</div>
</template>

<script>
import qs from 'qs'
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

import {
  swiper,
  swiperSlide
} from 'vue-awesome-swiper'

import Search from '@/components/Search'
import FilterTab from '@/components/filter/FilterTab'
import ProductList from '@/components/ProductList'
import CommonNav from '@/components/CommonNav'
import arrRemove from '@/mixins/arr-remove'
import DscLoading from '@/components/DscLoading'
import EcFilterTop from '@/components/visualization/element/FilterTop'

export default{
	data(){
		return{
			disabled:false,
			loading:true,
			mode:'',
			filter:{
				sort:'goods_id',
				order:'desc',
				goods_num:'0',
				promote:'0',
				min:'',
				max:'',
				brand_id:[],
				brandResult:[],
				brandResultArr:[],
				self:'0',
				intro:'',
				filter_attr:[]
			},
			isFilter:true,
			isPopupVisible:false,
			isPopupBrand:false,
			swiperOption:{
				direction: 'vertical',
				slidesPerView: 'auto',
				freeMode: true
			},
			routerName:'goods',
			cat_id:this.$route.params.id,
			page:1,
			size:10,
			footerCont:false,
			dscLoading:true,
			app:false,
			scrollState:false,
			grade:[],
			attribute:[],
			attribute_id:[],
			price_filter_sn:null
		}
	},
	directives: {
    	WaterfallLower: Waterfall('lower')
	},
	components:{
		Search,
		FilterTab,
		ProductList,
		swiper,
	  	swiperSlide,
	  	CommonNav,
	  	DscLoading,
		[Field.name] : Field,
		[Popup.name] : Popup,
		[Switch.name] : Switch,
		[Cell.name] : Cell,
		[CellGroup.name] : CellGroup,
		[Checkbox.name] : Checkbox,
		[CheckboxGroup.name] : CheckboxGroup,
		[Loading.name] : Loading,
		EcFilterTop
	},
	mounted(){
		this.$nextTick(() => {
			window.addEventListener('scroll', this.onScroll)
		})
	},
	created(){
		let that = this
		that.getGoodsList()

		setTimeout(() => {
	      uni.getEnv(function(res){
	        if(res.plus || res.miniprogram){
	          that.app = true
	        }
	      })
		},100)

		if(sessionStorage.getItem('configData')){
			that.mode = JSON.parse(sessionStorage.getItem('configData')).show_order_type == 1 ? 'small' : 'medium'
		}else{
			that.shopConfig();
		}

		//品牌
		that.selectBrand();
	},
	computed:{
		cateGoodsList:{
			get(){
				return this.$store.state.category.cateGoodsList
			},
			set(val){
				this.$store.state.category.cateGoodsList = val
			}
		},
		checkedSelf:{
			get(){
				return this.filter.self == '0' ? false : true
			},
			set(val){
				this.filter.self = val == true ? 1 : 0
			}
		}
	},
	methods: {
		async shopConfig(){
			let {data:{data}} = await this.$http.get(`${window.ROOT_URL}api/shop/config`);

			this.mode = data.show_order_type == 1 ? 'small' : 'medium';
		},
		getGoodsList(page){
			if(page){
				this.page = page
				this.size = Number(page) * 10
			}

			if(this.filter.promote == 1){
				this.filter.intro = 'promote'
			}else{
				this.filter.intro = ''
			}

			this.$store.dispatch('setGoodsList',{
				cat_id:this.cat_id,
				brand:this.filter.brand_id,
				warehouse_id:'0',
				area_id:'0',
				min:this.filter.min,
				max:this.filter.max,
				filter_attr:this.filter.filter_attr,
				ext:'',
				goods_num:this.filter.goods_num,
				size:this.size,
				page:this.page,
				sort:this.filter.sort,
				order:this.filter.order,
				self:this.filter.self,
				intro:this.filter.intro
			})
		},
	    handleViewSwitch(val){
	    	this.mode = val;
	    },
	    handleFilter(o){
	    	this.filter.sort = o.sort;
	    	this.filter.order = o.order;

	    	this.getGoodsList(1);
	    },
	    setPopupVisible(val){
	    	this.isPopupVisible = val;

	    	if(this.isPopupVisible){
	    		this.$http.get(`${window.ROOT_URL}/api/catalog/grade`,{params:{cat_id:this.cat_id}}).then(res=>{
		    		if(res.data.status == 'success'){
		    			this.grade = res.data.data;
		    		}
		    	});
		    	this.$http.get(`${window.ROOT_URL}/api/catalog/attribute`,{params:{cat_id:this.cat_id}}).then(res=>{
		    		if(res.data.status == 'success'){
		    			this.attribute = res.data.data;

		    			for(let index of this.attribute){
		    				this.filter.filter_attr.push(0)
		    			}
		    		}
		    	});
	    	}
	    },
	    selectBrand(){
	    	this.$http.post(`${window.ROOT_URL}api/catalog/brandlist`,qs.stringify({
	    		cat_id:this.cat_id
	    	})).then(res=>{
	    		if(res.data.data.length > 0){
	    			this.filter.brandResult = res.data.data
	    		}
	    	})
	    },
	    //重置筛选
	    closeFilter(){
	    	this.filter.self = '0'
			this.filter.goods_num = '0'
			this.filter.promote = '0'
			this.filter.min = ''
			this.filter.max = ''
			this.filter.brand_id = []
			this.filter.brandResultArr = []
			this.filter.filter_attr = []
			this.price_filter_sn = null
			this.getGoodsList(1);
	    },
	    submitFilter(){
	    	this.getGoodsList(1);
	    	this.isPopupVisible = false
	    },
	    handleTags(val){
	    	if(val == 'hasgoods'){
	    		this.filter.goods_num = this.filter.goods_num == 0 ? 1 : 0;
	    	}else{
	    		this.filter.promote = this.filter.promote == 0 ? 1 : 0;
	    	}
	    },
	    onBrandResult(item){
	    	let arr = [];
	    	if(this.filter.brandResultArr.includes(item)){
	    		this.filter.brandResultArr.splice(this.filter.brandResultArr.indexOf(item),1);
	    	}else{
	    		this.filter.brandResultArr.push(item);
	    	}

	    	this.filter.brand_id = this.filter.brandResultArr.map(v=>{ return v.brand_id });
	    },
	    loadMore(){
			setTimeout(() => {
				this.disabled = true
		    	if(this.page * this.size == this.cateGoodsList.length){
		  			this.page ++
		  			this.getGoodsList()
		  		}
			},200)
	    },
	    onScroll(){
	    	let scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;

			if (scrollTop > 800) {
				this.scrollState = true
			} else {
				this.scrollState = false
			}
	    },
	    isAttribute(id){
	    	if(this.attribute_id.includes(id)){
	    		this.attribute_id.splice(this.attribute_id.indexOf(id),1);
	    	}else{
	    		this.attribute_id.push(id);
	    	}
	    },
	    onPriceFilter(item){
	    	this.price_filter_sn = item.sn;
	    	this.filter.min = item.start;
			this.filter.max = item.end;
	    },
	    onAttributeValue(item,index){
	    	this.filter.filter_attr.splice(index,1,item.goods_attr_id);
	    }
	},
	destroyed() {
		window.removeEventListener("scroll",  this.onScroll);
	},
	watch:{
		cateGoodsList(){
			this.dscLoading = false
			if(this.page * this.size == this.cateGoodsList.length){
				this.disabled = false
				this.loading = true
			}else{
				this.loading = false
				this.footerCont = this.page > 1 ? true : false
			}

			this.cateGoodsList = arrRemove.trimSpace(this.cateGoodsList)
		}
	}
}
</script>
<style>
.con-filter-warp{ width: 80%; }
</style>
