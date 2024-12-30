<template>
	<div class="con" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<div class="header-list-goods">

			<Search :mode="mode" :isFilter='isFilter' v-on:getViewSwitch="handleViewSwitch" :placeholder="placeholder" :couId="cou_id"></Search>
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
		<van-popup class="con-filter-warp" v-model="isPopupVisible" position="right">
			<div class="con-filter-div">
				<swiper :options="swiperOption">
	        <swiper-slide>

	        	<ul class="mod_list">
	        		<li class="radio-switching">
	        			<div class="super_li">
	        				<div class="li_line">
		        				<div class="big">{{$t('lang.self_support_product')}}</div>
		        				<div class="right">
		        					<van-switch v-model="checkedSelf" size="23px" />
		        				</div>
		        			</div>
	        			</div>
		        	</li>
	        	</ul>
	        	<ul class="tags_selection">
	        		<li :class="{'active':filter.goods_num == 1}" @click="handleTags('hasgoods')"><label>{{$t('lang.just_look_stock')}}</label></li>
	        		<li :class="{'active':filter.promote == 1}" @click="handleTags('promote')"><label>{{$t('lang.promotion')}}</label></li>
	        	</ul>
	        	<ul class="mod_list">
	        		<li class="super_li">
	        			<div class="li_line">
	        				<div class="big">{{$t('lang.price_range')}}</div>
	        			</div>
	        		</li>
	        		<li class="filterlayer_price">
	        			<div class="filterlayer_price_area">
	        				<van-field type="tel" class="filterlayer_price_area_input" :placeholder="$t('lang.minimum_price')" v-model="filter.min" />
	        				<span class="filterlayer_price_hang"></span>
	        				<van-field type="tel" class="filterlayer_price_area_input" :placeholder="$t('lang.top_price')" v-model="filter.max" />
	        			</div>
	        		</li>
	        	</ul>
	        	<van-cell-group>
						  <van-cell :title="$t('lang.brand')" @click="selectBrand" is-link>
						  	{{filter.brandResultName}}
						  </van-cell>
						</van-cell-group>
	        </swiper-slide>
	      </swiper>
			</div>
			<div class="filterlayer_bottom_buttons">
				<span class="filterlayer_bottom_button bg_1" @click="closeFilter">{{$t('lang.close')}}</span>
				<span class="filterlayer_bottom_button bg_2" @click="submitFilter">{{$t('lang.confirm')}}</span>
			</div>
		</van-popup>

		<van-popup class="sf_layer" v-model="isPopupBrand" position="right">
			<div class="sf_layer_sub_title">
				<strong>{{$t('lang.selected_brand')}}：</strong>
				<span v-if="filter.brandResultName.length > 0">{{ filter.brandResultName }}</span>
			</div>
			<div class="sf_layer_con">
				<van-checkbox-group v-model="filter.brand_id" @change="onBrandResult">
					<van-checkbox v-for="(item,index) in filter.brandResult" :name="item.brand_id">{{ item.brand_name }}</van-checkbox>
				</van-checkbox-group>
			</div>
			<div class="filterlayer_bottom_buttons">
				<span class="filterlayer_bottom_button bg_1" @click="cancelSelectBrand">{{$t('lang.cancel')}}</span>
				<span class="filterlayer_bottom_button bg_2" @click="confirmSelectBrand">{{$t('lang.confirm')}}</span>
			</div>
		</van-popup>

		<!--初始化loading-->
        <DscLoading :dscLoading="dscLoading"></DscLoading>
	</div>
</template>

<script>
import qs from 'qs'
import { mapState } from 'vuex'

import {
	Popup,
	Field,
	Switch,
	Cell,
	CellGroup,
	Checkbox,
	CheckboxGroup,
	Waterfall,
	Loading,
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

export default{
	data(){
		return{
			disabled:false,
			loading:true,
			mode:'medium',
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
        intro:''
  		},
			isFilter:true,
			isPopupVisible:false,
			isPopupBrand:false,
			isFilterType:true,
			swiperOption:{
				direction: 'vertical',
				slidesPerView: 'auto',
				freeMode: true
			},
			routerName:'goods',
			cat_id:this.$route.params.id,
			cou_id:this.$route.query.cou_id,
			page:1,
			size:10,
			footerCont:false,
			dscLoading:true,
            placeholder:this.$t('lang.search_goods')
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
		[Loading.name] : Loading
	},
	created(){
		if(this.$route.query.keywords){
      this.placeholder = this.$route.query.keywords
    }
    this.getGoodsList();
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
				keywords:this.$route.query.keywords,
				sc_ds:'',
				brand:this.filter.brand_id,
				warehouse_id:'0',
				area_id:'0',
				min:this.filter.min,
				max:this.filter.max,
				filter_attr:'',
				ext:'',
				goods_num:this.filter.goods_num,
				size:this.size,
				page:this.page,
				sort:this.filter.sort,
				order:this.filter.order,
				self:this.filter.self,
                intro:this.filter.intro,
				cou_id:this.$route.query.cou_id
			});
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
    },
    selectBrand(){
    	this.isPopupBrand = this.isPopupBrand == false ? true : false;

    	this.$http.post(`${window.ROOT_URL}api/catalog/brandlist`,qs.stringify({
    		keywords:this.$route.query.keywords
    	})).then(res=>{
    		if(res.data.data.length > 0){
    			this.filter.brandResult = res.data.data
    		}
    	})
    },
    closeFilter(){
    	this.isPopupVisible = false
    	this.isFilterType = true
    },
    submitFilter(){
    	this.getGoodsList(1);
    	this.isPopupVisible = false
    	this.isFilterType = false
    },
    handleTags(val){
    	if(val == 'hasgoods'){
    		this.filter.goods_num = this.filter.goods_num == 0 ? 1 : 0;
    	}else{
    		this.filter.promote = this.filter.promote == 0 ? 1 : 0;
    	}
    },
    onBrandResult(){
    	let arr = ''
    	this.filter.brand_id.forEach((v) =>{
    		this.filter.brandResult.forEach((i)=>{
    			if(v == i.brand_id){
    				arr+=i.brand_name + ','
    			}
    		})
    	})

    	this.filter.brandResultName = arr
    },
    cancelSelectBrand(){
    	this.filter.brand_id = []
    	this.filter.brandResultName = ''
    	this.isPopupBrand = false
    },
    confirmSelectBrand(){
    	this.isPopupBrand = false
    },
    loadMore(){
		setTimeout(() => {
			this.disabled = true
	    	if(this.page * this.size == this.cateGoodsList.length){
	  			this.page ++
	  			this.getGoodsList()
	  		}
    	},200)
    }
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
  	},
  	isFilterType(){
  		if(this.isFilterType){
			this.filter.self = '0'
			this.filter.goods_num = '0'
			this.filter.promote = '0'
			this.filter.min = ''
			this.filter.max = ''
			this.filter.brand_id = []
			this.getGoodsList(1);
  		}
  	}
  }
}
</script>
