<template>
	<div class="con" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<div class="wholesale-box">
			<div class="who-search dis-box p-a">
		        <div class="left-icon" @click="back">
		            <i class="iconfont icon-back j-wholesale-cate j-show-div"></i>
		        </div>
		        <div class="box-flex">
		            <van-search v-model="value" :placeholder="$t('lang.enter_keyword')" show-action style="background:none">
		                <div slot="action" @click="onSearch">{{$t('lang.search')}}</div>
		            </van-search>
		        </div>
		    </div>
		    <div class="goods-li of-hidden" v-if="supplierGoodsList && supplierGoodsList.length > 0">
                <router-link :to="{name:'supplier-detail',params:{id:item.goods_id}}" class="li active" v-for='(item,index) in supplierGoodsList' :key="index" @click="detailClick(item)">
                    <div class="left">
                        <img class="img" :src="item.goods_img" />
                    </div>
                    <div class="right bg-color-write">
                        <h4 class="f-05 color-3 twolist-hidden"> {{item.goods_name}}</h4>
                        <div class="box-flex f-06 color-red m-top08">
                            <label v-html="item.volume_price"></label>
                            <span class="f-01 color-9">/个</span>
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
            <div v-else>
                <NotCont/>
            </div>
            <template v-if="loading">
                <van-loading type="spinner" color="black" />
            </template>
		</div>
		<CommonNav/>
	</div>
</template>

<script>
	import { mapState } from 'vuex'

	import {
	    Search,
	    Cell,
	    CellGroup,
	    Waterfall,
        Loading
	} from 'vant'

	import CommonNav from '@/components/CommonNav'
    import NotCont from '@/components/NotCont'
    import arrRemove from '@/mixins/arr-remove'

	export default{
		 components: {
            [Search.name]: Search,
            [Cell.name]: Cell,
            [CellGroup.name]: CellGroup,
            [Loading.name]: Loading,
            CommonNav,
            NotCont,
        },
        directives: {
            WaterfallLower: Waterfall('lower')
        },
		data(){
			return{
				value:this.$route.query.keywords ? this.$route.query.keywords : '',
				page:1,
				size:10,
				loading:true,
                disabled:false,
			}
		},
		created() {
			this.goodsList(1);
        },
        computed: {
			supplierGoodsList:{
                get(){
                    return this.$store.state.other.supplierGoodsList
                },
                set(val){
                    this.$store.state.other.supplierGoodsList = val
                }
            }
		},
        methods: {
        	goodsList(page){
        		if(page){
                    this.page = page
                    this.size = Number(page) * 10
                }

        		this.$store.dispatch('setSupplierSearch',{
        			keyword:this.value,
        			page:this.page,
        			size:this.size
        		});
        	},
        	back(){
        		this.$router.push({
        			name:'supplier'
        		})
        	},
        	onSearch(){
        		this.goodsList(1);
        	},
            loadMore(){
                setTimeout(() => {
                    this.disabled = true
                    if(this.page * this.size == this.supplierGoodsList.length){
                        this.page ++
                        this.goodsList() 
                    }
                },200)
            }
        },
        watch:{
            supplierGoodsList(){
                if(this.page * this.size == this.supplierGoodsList.length){
                    this.disabled = false
                    this.loading = true
                }else{
                    this.loading = false
                }

                this.supplierGoodsList = arrRemove.trimSpace(this.supplierGoodsList)
            },
        }
	}
</script>

<style scoped>
.goods-li{
	padding-top: 45px;
}
</style>