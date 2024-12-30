<template>
	<div class="prolist" :class="{'prolist-swiper':styleType == 'slide'}">
		<template v-if="data.length > 0">
			<template v-if="styleType == 'slide'">
				<div class="slide">
					<swiper class="swiper" :options="swiperOption" ref="slideSwiper">
						<swiper-slide v-for="(goods,index) in goodslist" :key="index" class="goods-swiper-slide">
							<div class="goods-item" v-for="item in goods">
								<div class="img" @click="detailLink(routerName,item.goods_id)">
									<img :src="item.goods_thumb" class="img" v-if="item.goods_thumb">
									<img src="../assets/img/no_image.jpg" class="img" v-else>
								</div>
								<div class="pro-info">
									<h4 class="twolist-hidden" @click="detailLink(routerName,item.goods_id)">
										<img v-if="item.country_icon != ''" class="country_icon" :src="item.country_icon" /><em v-if="item.is_promote > 0" class="em-promotion em-special-sale">{{$t('lang.special_sale')}}</em>{{ item.goods_name }}
									</h4>
									<currency-price :price="item.shop_price" :delPrice="item.is_promote > 0 ? item.market_price : ''" :size="16" style="padding: 6px 0 8px;"></currency-price>
									<div class="outer" v-if="!productOuter">
										<em class="tag" v-if="(item.user_id > 0 && item.self_run == 1) || item.user_id == 0">{{$t('lang.self_support')}}</em>
										<em class="tag" @click="shopUrl(item.user_id)" v-else>{{$t('lang.into_shop')}}</em>
										<span>{{ item.sales_volume }}{{$t('lang.a_have_purchased')}}</span>
									</div>
									<template v-if="item.prod == 1">
									<a href="javascript:void(0)" class="add_cart" @click="addCart(item.goods_id,index)" v-if="!productOuter">
										<span class="add_num" :class="{'show':addCartClass == true && index == curIndex}" id="popone">+1</span>
										<i class="iconfont icon-cart"></i>
									</a>
									</template>
									<template v-else>
										<div class="add_cart" v-if="!productOuter" @click="detailLink(routerName,item.goods_id)">
											<i class="iconfont icon-cart"></i>
										</div>
									</template>
								</div> 
							</div>
						</swiper-slide>
						<div class="swiper-pagination" slot="pagination"></div>
					</swiper>
				</div>
			</template>
			<template v-else>
				<div class="prolist-item" v-for="(item,index) in data" :key="index">
					<template v-if="routerName == 'team-detail'">
						<div class="pro-img" @click="detailLink(routerName,item.goods_id)">
							<div class="img-box">
								<img :src="item.goods_thumb" class="img" v-if="item.goods_thumb">
								<img src="../assets/img/no_image.jpg" class="img" v-else>

								<div class="goods-label-suspension" v-if="item.goods_label_suspension && item.goods_label_suspension.formated_label_image">
									<img :src="item.goods_label_suspension.formated_label_image" class="img">
								</div>
							</div>
						</div>
						<div class="pro-info" @click="detailLink(routerName,item.goods_id)">
							<h4 class="twolist-hidden">
								<img class="country_icon" v-if="item.country_icon != ''" :src="item.country_icon" />{{ item.goods_name }}
							</h4>
							<div class="dis-box cont">
								<div class="f-02 color-9 box-flex">{{$t('lang.single_purchase_price')}}<span v-html="item.shop_price_formated"></span></div>
							</div>
							<div class="dis-box m-top10">
								<div class="f-05 color-red">{{ item.team_num }}{{$t('lang.one_group')}}</div>
								<div class="box-flex f-06 color-red f-weight p-l1" v-html="item.team_price"></div>
							</div>
						</div>
					</template>
					<template v-else>
						<div class="pro-img" @click="detailLink(routerName,item.goods_id)">
							<div class="img-box">
								<img :src="item.goods_thumb" class="img" v-if="item.goods_thumb">
								<img src="../assets/img/no_image.jpg" class="img" v-else>

								<div class="goods-label-suspension" v-if="item.goods_label_suspension && item.goods_label_suspension.formated_label_image">
									<img :src="item.goods_label_suspension.formated_label_image" class="img">
								</div>
							</div>
						</div>
						<div class="pro-info">
							<h4 class="twolist-hidden" @click="detailLink(routerName,item.goods_id)">
								<img class="country_icon" v-if="item.country_icon != ''" :src="item.country_icon" /><em v-if="item.is_promote > 0" class="em-promotion em-special-sale">{{$t('lang.special_sale')}}</em>{{ item.goods_name }}
							</h4>
							<currency-price :price="item.shop_price" :delPrice="item.is_promote > 0 ? item.market_price : ''" :size="16" style="padding: 6px 0 8px;"></currency-price>
							<div class="outer outer_teshu" v-if="!productOuter">
								<em class="tag" v-if="(item.user_id > 0 && item.self_run == 1) || item.user_id == 0">{{$t('lang.self_support')}}</em>
								<em class="tag" @click="shopUrl(item.user_id)" v-else>{{$t('lang.into_shop')}}</em>
								<div class="label-list" v-if="item.goods_label">
									<div class="label-img" v-for="(label,labelIndex) in item.goods_label" :key="labelIndex">
					                    <a :href="label.label_url ? label.label_url : 'javascript:;'"><img :src="label.formated_label_image" /></a>
					                </div>
	              				</div>
							</div>
							<div class="outer" v-if="!productOuter">
								<span>{{ item.sales_volume }}{{$t('lang.a_have_purchased')}}</span>
								<template v-if="item.prod == 1">
								<a href="javascript:void(0)" class="add_cart" @click="addCart(item.goods_id,index)" v-if="!productOuter">
									<span class="add_num" :class="{'show':addCartClass == true && index == curIndex}" id="popone">+1</span>
									<i class="iconfont icon-cart"></i>
								</a>
								</template>
								<template v-else>
									<div class="add_cart" v-if="!productOuter" @click="detailLink(routerName,item.goods_id)">
										<i class="iconfont icon-cart"></i>
									</div>
								</template>
							</div>
						</div>
					</template>
				</div>
			</template>
		</template>
		<template v-else>
			<NotCont></NotCont>
		</template>
	</div>
</template>

<script>
import { Toast } from 'vant'
import NotCont from '@/components/NotCont'

import {
    swiper,
    swiperSlide
} from 'vue-awesome-swiper'

export default{
	props:{
		data:Array,
		routerName:{
			type:String,
			default:'goods'
		},
		productOuter:{
			type:Boolean,
			default:false
		},
		styleType:{
			type:String,
			default:''
		}
	},
	data(){
		return {
			addCartClass:false,
			curIndex:null,
			swiperOption: {
                notNextTick: true,
                pagination: {
		        	el: '.swiper-pagination'
		        },
                lazyLoading: true,
                autoplay: 5600
            },
		}
	},
	components:{
		swiper,
    	swiperSlide,
		NotCont,
		[Toast.name] : Toast
	},
	computed:{
		goodslist(){
			let result = []
			for(var i=0;i<this.data.length;i+=6){
			    result.push(this.data.slice(i,i+6));
			}

			return result
		}
	},
	methods:{
		addCart(id,index){
			this.addCartClass = false
			this.curIndex = null
			this.$store.dispatch('setAddCart',{
				goods_id:id,
				num:1,
				spec:[],
				warehouse_id:'0',
				area_id:'0',
				parent_id:'0'
			}).then(res => {
				if(res == true){
					this.addCartClass = true
					this.curIndex = index
					Toast.success({
						duration:1000,
						message:this.$t('lang.goods_been_add_cart')
					})
				}
			})
		},
		shopUrl(id){
			this.$router.push({
				name:'shopHome',
				params:{
		          id:id
		        }
			})
		},
		detailLink(routerName,id){
			if(routerName == 'goods'){
				this.data.forEach(v=>{
					if(v.goods_id == id){
						//判断是否是预售
						if(v.get_presale_activity){
							this.$router.push({
					            name:'presale-detail',
					            params:{
					                act_id:v.get_presale_activity.act_id
					            }
					        })
						}else{
							this.$router.push({
					            name:'goods',
					            params:{
					                id:id
					            }
					        })
						}
					}
				})
			}else if(routerName == 'team-detail'){
				this.$router.push({
		            name:'team-detail',
		            query:{
		                goods_id:id,
		                team_id:0
		            }
		        })
			}
	    }
	}
}
</script>
<style scoped>
.prolist-swiper{
	padding: 0
}
.swiper-container{
	padding-bottom: 30px;
	background: #fff;
}
.goods-swiper-slide{
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
	/*border-top: 1px solid #ddd;*/
}
.goods-item{
	width: 33.3%;
	/*border-right: 1px solid #ddd;
	border-bottom: 1px solid #ddd;*/
	background: #fff;
}
.goods-item:nth-child(3n){
	border-right: 0;
}
.goods-item .img img{
	transform: scale(.8);
}
.goods-item .pro-info{
	padding: 10px 5px;
}
.goods-item .pro-info h4{
	margin-bottom: 5px;
	font-size: 12px;
	line-height: 1.5;
}

.country_icon{
	width: 2.4rem;
	padding-right: 0.3rem;
	position: relative;
	top: 0.2rem;
	display: inline-block;
}

.swiper-pagination{
	bottom: 8px;
}
</style>