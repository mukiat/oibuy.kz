<template>
	<section class="filter_tab">
		<div class="pro_filter_items dis-box">
			<div class="item" :class="[{'active':filter.sort === 'goods_id','a-change':filter.order === 'asc' && filter.sort === 'goods_id'}]" @click="handleFilter('goods_id',filter.order)"><span>{{$t('lang.comprehensive')}}</span><i class="iconfont icon-xiajiantou"></i></div>
			<div class="item" :class="{'active':filter.sort === 'last_update'}" @click="handleFilter('last_update')" v-if="filterStyle !='coudan'"><span>{{$t('lang.new')}}</span></div>
			<div class="item" :class="{'active':filter.sort === 'sales_volume'}" @click="handleFilter('sales_volume')"><span>{{$t('lang.sales_volume')}}</span></div>
			<div class="item" :class="[{'active':filter.sort === 'shop_price','a-change':filter.order === 'asc' && filter.sort === 'shop_price'}]" @click="handleFilter('shop_price',filter.order)"><span>{{$t('lang.price')}}</span><i class="iconfont icon-xiajiantou"></i></div>
			<div class="item item-icon" v-if="filterStyle != 'coudan'">
				<a href="javascript:void(0);" @click="handelFilterUp"><i class="iconfont icon-filter"></i>{{$t('lang.filter')}}</a>
			</div>
		</div>
	</section>
</template>
<script>
export default{
	props:{
		filter:{
			type:Object,
			default:''
		},
		isPopupVisible:{
			type:Boolean,
			default:true
		},
		filterStyle:{
			type:String,
			default:'goods'
		}
	},
	data(){
		return{
			myMode: this.filter.mode
		}
	},
	computed:{

	},
	methods:{
		handleFilter(val,order){
			if(order && this.filter.sort == val){
				this.filter.order = order == 'desc' ? 'asc' : 'desc'
			}
			this.filter.sort = val;

			this.$emit('getFilter',{
				sort : this.filter.sort,
				order: this.filter.order
			})
		},
		handelFilterUp(){
			let val = this.isPopupVisible == false ? true : false;
			this.$emit('setPopupVisible',val);
		}
	}
}
</script>