<template>
	<div class="set_meal_content">
		<header class="header-nav-content">
			<van-nav-bar :title="$t('lang.discount_package')" left-arrow @click-left="onClickLeft"></van-nav-bar>
		</header>
		
		<section class="comment-content">
			<section class="goods_module_wrap m-top10" v-for="(item, index) in fittingInfo.comboTab" :key="index">
				<van-collapse v-model="currTab" @change="toggleTab" accordion>
					<van-collapse-item :name="item.group_id" v-if="tabList[index] > 0">
						<div slot="title" class="title_box">
							<div class="title_text">
								<span>{{item.text}}</span>
							</div>
						</div>
						<ul class="goods_list">
							<li class="goods_item van-hairline--top">
								<van-checkbox v-model="checked" :disabled="checkDisabled"></van-checkbox>
								<img :src="fittingInfo.goods.goods_thumb" v-if="fittingInfo.goods.goods_thumb" />
								<img src="../../assets/img/no_image.jpg" v-else>
								<div class="name_price">
									<p>{{fittingInfo.goods.goods_name}}</p>
									<currency-price :price="fittingInfo.goods.shop_price"></currency-price>
								</div>
							</li>
							<template v-for="(goodsItem,goodsindex) in fittingInfo.fittings">
								<li class="goods_item van-hairline--top" :index="goodsindex" v-if="item.group_id == goodsItem.group_id">
									<van-checkbox-group v-model="fittingsCheckModel">
										<van-checkbox :name="goodsItem.goods_id" ref="checkboxes"></van-checkbox>
									</van-checkbox-group>
									<img :src="goodsItem.goods_thumb" @click="checkboxHandle(goodsItem.goods_id, goodsindex)" v-if="goodsItem.goods_thumb" />
									<img src="../../assets/img/no_image.jpg" v-else>
									<div class="name_price" @click="checkboxHandle(goodsItem.goods_id, goodsindex)">
										<p>{{ goodsItem.goods_name }}</p>
										<currency-price :price="goodsItem.goods_price"></currency-price>
									</div>
								</li>
							</template>
						</ul>
					</van-collapse-item>
				</van-collapse>
			</section>
		</section>
		<footer class="submit_bar van-hairline--top">
			<div class="left_price">
				<div class="setmeal_price">{{$t('lang.package_price')}}：<span v-html="fittings_minMax"></span></div>
				<div class="save_price">{{$t('lang.save_money')}}：<span v-html="save_minMaxPrice"></span></div>
			</div>
			<van-button round @click="fittingsAddCart" class="buynow">{{$t('lang.add_cart')}}</van-button>
		</footer>
	</div>
</template>

<script>
	import Vue from 'vue';
	import { mapState } from 'vuex';
	import { NavBar, Checkbox, CheckboxGroup, Button, Toast, Collapse, CollapseItem } from 'vant';
	
	Vue.use(NavBar).use(Checkbox).use(CheckboxGroup).use(Button).use(Toast).use(Collapse).use(CollapseItem);
	export default {
		data() {
			return {
				checked:true,
				checkDisabled:true,
				fittingNames: '',
				fittingsCheckModel:[],
				fittings_minMax: 0.0,
				save_minMaxPrice: 0.0,
				currTab: '',
				id:this.$route.params.id ? this.$route.params.id : 0
			}
		},
		computed: {
			...mapState({
				fittingInfo: state => state.goods.fittingInfo,//组合配件详情
				fittingPriceData: state => state.goods.fittingPriceData,//组合配件价格信息
				shipping_fee: state => state.shopping.shipping_fee, //配送运费信息
				goodsAttrInit: state => state.goods.goodsAttrInit
			}),
			tabList(){
				let i = 0, a = 0,arr=[];
				this.fittingInfo.fittings.forEach(v=>{
					if(v.group_id == 1){
						i++
					}else{
						a++
					}
				});
				
				arr = [i,a]

				return arr
			}
		},
		watch: {
			fittingInfo: 'toggleTab',
			fittingsCheckModel: 'fittingsCheckChange'
		},
		created() {
			this.getSetMealById();
			//this.delcartCombo();
		},
		methods: {
			onClickLeft() {
				this.$router.go(-1);
			},
			getSetMealById() {
				this.$store.dispatch('setFitting',{ goods_id: this.id });
			},
			toggleTab(id) {
				console.log(id);
				if (id.comboTab) {
					this.fittingNames = this.fittingInfo.comboTab[0].group_id;
					this.currTab = this.fittingNames;
				} else {
					this.currTab = this.currTab == id ? '' : id;
					if (id == this.fittingNames) return;
					this.fittingNames = id;

					this.fittingsCheckModel = []
				}
			},
			checkboxHandle(id, i) {
				this.$refs.checkboxes[i].toggle()
			},
			fittingsAddCart(){
				Toast.loading({
				  duration: 0,       // 持续展示 toast
				  forbidClick: true, // 禁用背景点击
				  loadingType: 'spinner',
				  message: this.$t('lang.loading') + '...'
				});
			    let group_name = 'm_goods_' + this.fittingNames;
			    let group_id = group_name + '_' + this.id;
			    this.$store.dispatch('setAddToCartGroup',{
			        group_name:group_name,
			        goods_id:this.id,
			        warehouse_id:0,
			        area_id:0,
			        area_city:0,
			        number: this.fittingInfo.goods.is_minimum > 0 ? this.fittingInfo.goods.minimum : 1
			    }).then(({data})=>{
					Toast.clear();
			        if(data.error == 0){
		                this.$router.push({
		                    name:'cart'
		                })
			        }else{
			        	Toast(data.msg);
			        }
			    })
			},
			fittingsCheckChange(val, oldVal) {
				Toast.loading({
				  duration: 0,       // 持续展示 toast
				  forbidClick: true, // 禁用背景点击
				  loadingType: 'spinner',
				  message: this.$t('lang.loading') + '...'
				});

				let group_name = 'm_goods_' + this.fittingNames;
				let group_id = group_name + '_' + this.id;    
				let spec = '';
				if (val.length > oldVal.length) {
					const currGoodsId = val.find(item => !oldVal.includes(item));
					this.fittingInfo.fittings.some(item => {
						if (item.id == currGoodsId) {
							spec = item.goods_attr_id;
							return true;
						}
					});

					this.$store.dispatch('setAddToCartCombo',{
					    goods_id: currGoodsId,
					    number: 1,
					    spec: spec,
					    parent_attr: this.goodsAttrInit,
					    warehouse_id: 0,
					    area_id: 0,
					    area_city: 0,
					    parent: this.id,
					    group_id: group_id,
					    add_group:''
					}).then(({data})=>{
						Toast.clear();
					    if(data.error == 0){
					        this.save_minMaxPrice = data.save_minMaxPrice
					        this.fittings_minMax = data.fittings_minMax
					    }else{
					        Toast(data.msg)
					    }
					})
				} else {
					let currGoodsId = ''
					if(val.length > 0){
						currGoodsId = oldVal.find(item => !val.includes(item));
						this.fittingInfo.fittings.some(item => {
							if (item.id == currGoodsId) {
								spec = item.goods_attr_id;
								return true;
							}
						});
					}else{
						let arr = this.fittingInfo.fittings.filter(item=>{
							return item.group_id == this.fittingNames
						})

						currGoodsId = arr.length > 0 ? arr[0].goods_id : this.fittingInfo.fittings[0].goods_id
					}

					this.delcartCombo(currGoodsId,group_id,spec);
				}
			},
			delcartCombo(currGoodsId,group_id,spec){
				this.$store.dispatch('setDelInCartCombo',{
				    goods_id: currGoodsId,
				    parent: this.id,
				    group_id: group_id,
				    spec: spec,
				    goods_attr: this.goodsAttrInit,
				    warehouse_id: 0,
				    area_id: 0,
				    area_city: 0
				}).then(({data})=>{
					Toast.clear();
				    if(data.error == 0){
				        this.save_minMaxPrice = data.save_minMaxPrice
				        this.fittings_minMax = data.fittings_minMax
				    }else{
				        Toast(data.msg)
				    }
				});
			}
		}
	}
</script>

<style lang="scss" scoped>
	.set_meal_content {
		padding-bottom: 5.4rem;
		.comment-content {
			padding: 5rem 1rem 1rem;
			.goods_module_wrap {
				overflow: hidden;
				border-radius: 1rem;
				font-size: 1.4rem;
				background-color: #fff;
				.title_box {
					display: flex;
					justify-content: space-between;
					align-items: center;
					.title_text {
						position: relative;
						font-size: 1.4rem;
						font-weight: 700;
						padding-left: 1rem;
						&:before {
							position: absolute;
							top: 50%;
							left: 0;
							transform: translateY(-50%);
							content: '';
							width: 0.3rem;
							height: 1.5rem;
							background: linear-gradient(180deg, #F91F28 0%, rgba(255, 79, 46, 0.35) 100%);
						}
						.comment_count {
							margin-left: 1.5rem;
							font-weight: normal;
						}
					}
					.drgree_of_praise {
						color: #999;
					}
				}
				.goods_list {
					.goods_item {
						display: flex;
						align-items: center;
						padding: 1.1rem;
						.van-checkbox {
							flex: none;
						}
						img {
							width: 7rem;
							height: 7rem;
							border-radius: 0.5rem;
							margin: 0 0.8rem 0 1.1rem;
						}
					}
					.name_price {
						flex: auto;
						display: flex;
						flex-direction: column;
						justify-content: space-between;
						height: 7rem;
						p {
							word-break: break-word;
							display: -webkit-box;
							-webkit-line-clamp: 2;
							-webkit-box-orient: vertical;
							overflow: hidden;
						}
					}
				}
			}
		}
		.submit_bar {
			position: fixed;
			bottom: 0;
			left: 0;
			width: 100%;
			height: 5.4rem;
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 0 1.5rem;
			font-size: 1.4rem;
			background-color: #fff;
			z-index: 9;
			.left_price {
				.setmeal_price {
					display: flex;
					align-items: baseline;
					color: #000;
					span {
						color: #F22E20;
					}
				}
				.save_price {
					display: flex;
					align-items: baseline;
					font-size: 1.2rem;
					color: #999;
				}
			}
			.van-button {
				height: 40px;
				line-height: inherit;
				border: none;
				font-size: 14px;
				font-weight: 700;
				color: #fff;
				background-color: #F91F28;
				display: flex;
				justify-content: center;
				align-items: center;

				&.buynow{
					width: 8rem;
					box-sizing: content-box;
				}
			}
		}
	}
</style>
