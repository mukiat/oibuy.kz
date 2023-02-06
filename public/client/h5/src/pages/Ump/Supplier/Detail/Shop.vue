<template>
	<div class="con con_main bg-color-write" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<div class="supplier-shop">
			<div class="header">
				<div class="shop-icon">
					<div class="img-icon">
						<img :src="supplierShopDetail.suppliers_logo" v-if="supplierShopDetail.suppliers_logo" />
						<img src="../../../../assets/img/default-img.jpg" v-else />
					</div>
				</div>
				<div class="shop-content">
					<div class="shop-name">{{supplierShopDetail.suppliers_name}}</div>
					<div class="shop-desc">{{supplierShopDetail.suppliers_desc}}</div>
				</div>
			</div>
			<div class="content">
				<div class="tit">{{$t('lang.all_goods')}}</div>
				<div class="goods-li of-hidden">
					<router-link :to="{name:'supplier-detail',params:{id:item.goods_id}}" class="li active" v-for='(item,index) in supplierShopGoodsList' :key="index" @click="detailClick(item)">
	                    <div class="left">
	                        <img class="img" :src="item.goods_thumb" />
	                    </div>
	                    <div class="right bg-color-write">
	                        <h4 class="f-05 color-3 twolist-hidden"> {{item.goods_name}}</h4>
	                        <div class="box-flex f-06 color-red m-top08"> <em v-html="(item.price_model===0?item.goods_price:item.shop_price)"></em>
	                            <span class="f-01 color-9">/{{item.goods_unit}}</span>
	                        </div>
	                        <div class="dis-box m-top06">
	                            <div class="box-flex f-02 color-9">{{$t('lang.label_volume_number')}}
	                                <span class="color-red" v-if="item.price_model===1">{{item.volume_number}}</span>
	                                <span class="color-red" v-if="item.price_model===0">{{item.moq}}</span>
	                            </div>
	                            <div class="box-flex f-02 color-9 text-right">{{$t('lang.label_trading_volume')}}
	                                <span>{{item.goods_sale}}</span>
	                            </div>
	                        </div>
	                    </div>
	                </router-link>
				</div>
			</div>
		</div>
		<template v-if="loading">
            <van-loading type="spinner" color="black" />
        </template>

        <div class="shop-menu">
        	<ul>
        		<li @click="onHome">
    				<i class="iconfont icon-dianpu1"></i>
    				<p>{{$t('lang.supplier_home')}}</p>
        		</li>
        		<li @click="onChat('',suppliers_id,'supplier')">
    				<i class="iconfont icon-kefu"></i>
    				<p>{{$t('lang.customer_service')}}</p>
        		</li>
        	</ul>
        </div>
	</div>
</template>

<script>
import { mapState } from 'vuex'

import {
    Waterfall,
    Loading
} from 'vant'

import arrRemove from '@/mixins/arr-remove'
import formProcessing from '@/mixins/form-processing'

export default{
	mixins: [formProcessing],
	data(){
		return {
			suppliers_id:this.$route.params.id,
			page:1,
			size:10,
			loading:true,
            disabled:false,
		}
	},
	components:{
		[Loading.name]: Loading,
	},
	directives: {
        WaterfallLower: Waterfall('lower')
    },
	created(){
		this.$store.dispatch('setSupplierShopHome',{
			suppliers_id:this.suppliers_id
		})

		this.goodsList();
	},
	computed:{
		...mapState({
            supplierShopDetail: state => state.other.supplierShopDetail
		}),
		supplierShopGoodsList:{
            get(){
                return this.$store.state.other.supplierShopGoodsList
            },
            set(val){
                this.$store.state.other.supplierShopGoodsList = val
            }
        }
	},
	methods:{
		goodsList(page){
			if(page){
                this.page = page
                this.size = Number(page) * 10
            }

			this.$store.dispatch('setSupplierHomeList',{
				suppliers_id:this.suppliers_id,
                page: this.page,
                size: this.size,
            })
		},
		onHome(){
			this.$router.push({
				name:'supplier'
			})
		},
		loadMore(){
            setTimeout(() => {
                this.disabled = true
                if(this.page * this.size == this.supplierShopGoodsList.length){
                    this.page ++
                    this.goodsList() 
                }
            },200)
        }
	},
    watch:{
        supplierShopGoodsList(){
            if(this.page * this.size == this.supplierShopGoodsList.length){
                this.disabled = false
                this.loading = true
            }else{
                this.loading = false
            }

            this.supplierShopGoodsList = arrRemove.trimSpace(this.supplierShopGoodsList)
        },
    }
}
</script>
<style scoped>
.supplier-shop{ padding: 1rem; }
.supplier-shop .header{ background: #f4f4f4; border-radius: 5px; position: relative; margin-top: 4rem; }
.supplier-shop .header .shop-icon{ position: absolute; width: 8rem; height: 8rem; border-radius: 50%; background: #ffffff; top: -4rem; left: 50%; margin-left: -4rem; }
.supplier-shop .header .shop-content{ padding: 5rem 1rem 1rem; text-align: center; }
.supplier-shop .header .shop-content .shop-name{ font-size: 18px; font-weight: bold; }
.supplier-shop .header .shop-content .shop-desc{ font-size: 14px; line-height: 1.5; color: #999; margin-top: 1rem; padding: 0 2rem; }
.supplier-shop .header .shop-icon .img-icon{ width: 6rem; height: 6rem; margin: 1rem; border-radius: 50%; box-shadow: 0px 0px 20px 0px rgba(0,0,0,.1); overflow: hidden;}
.supplier-shop .header .shop-icon .img-icon img{ width: 100%; height: 100%; }
.supplier-shop .content{ }
.supplier-shop .content .tit{ font-size: 16px; margin: 1rem 0; }
.supplier-shop .content .goods-li .li.active{ width: 49%; background: none; border: 1px solid #f4f4f4; margin-left: 1%; padding: .5rem; }
.supplier-shop .content .goods-li .li.active:nth-child(2n-1){ margin:0 1% 0 0; }

.shop-menu{ height: 4.4rem; position: fixed; bottom: 0; right: 0; left: 0;}
.shop-menu ul{width: 100%;background: #fff; display: flex; flex-direction: row; justify-content: center; text-align: center; align-items: center; border-top: 1px solid #e3e8ee;}
.shop-menu ul li{ width: 50%; border-right: 1px solid #e3e8ee; height: 4.4rem; display: flex; flex-direction: column; justify-content: center; align-items: center;}
.shop-menu ul li .iconfont{ font-size: 18px; }
</style>