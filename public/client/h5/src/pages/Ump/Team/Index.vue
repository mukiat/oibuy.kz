<template>
	<div class="team con_main">
		<van-tabs :active="active" class="team-tabs">
			<van-tab>
				<div slot="title" class="nav_active" @click="teamNavAll">{{$t('lang.all')}}</div>
			</van-tab>
			<van-tab v-for="(item,index) in teamIndexData.team_categories" :key="index">
				<div slot="title" class="nav_active" @click="teamNav(item)">{{item.name}}</div>
			</van-tab>
		</van-tabs>
		<!--拼团首页-->
		<div class="main" v-if="tc_id==0">
			<!-- 二级频道 -->
			<van-swipe class="swipe_wrap" indicator-color="white"
				v-if="teamCateData.team_categories_child && teamCateData.team_categories_child.length">
				<van-swipe-item v-for="(categoriesChilds, categoriesChildsIndex) in categoriesChild"
					:key="categoriesChildsIndex">
					<ul class="sub_nav">
						<li class="sub_nav_item" v-for="(item,index) in categoriesChilds" :key="index" @click="onChildsTab(index)">
							<img class="nav_img" :src="item.tc_img" />
							<p class="text-center m-top06 f-03 color-9">{{item.name}}</p>
						</li>
					</ul>
				</van-swipe-item>
			</van-swipe>
			<!-- 品牌精选 -->
			<div class="brand_choice"
				v-if="teamCateData.team_categories_brand && teamCateData.team_categories_brand.length">
				<h4 class="title">品牌精选</h4>
				<van-swipe indicator-color="white">
					<van-swipe-item v-for="(categoriesBrandItem, categoriesBrandIndex) in categoriesBrand"
						:key="categoriesBrandIndex">
						<ul class="sub_nav">
							<li class="sub_nav_item" v-for="(item,index) in categoriesBrandItem" :key="index">
								<router-link :to="{ path: 'team/list', query: { id: tc_id, brand_id: item.brand_id }}">
									<img class="nav_img" :src="item.brand_logo" />
									<p class="text-center m-top06 f-03 color-9">{{item.brand_name}}</p>
								</router-link>
							</li>
						</ul>
					</van-swipe-item>
				</van-swipe>
			</div>
			<!-- 商品列表 -->
			<div class="goods-li" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="200">
				<a hred="javascript:;" @click="detailLink(item.goods_id)" class="show bg-color-write li"
					v-for='(item,index) in teamGoodsData' :key="index">
					<div class="left">
						<img v-if="item.goods_thumb" class="img" :src="item.goods_thumb" />
						<img v-else class="img" src="../../../assets/img/not_goods.png" />
					</div>
					<div class="right">
						<h4 class="f-05 color-3 twolist-hidden">{{item.goods_name}}</h4>
						<div class="dis-box cont">
							<div class="f-02 color-9 box-flex">{{$t('lang.single_purchase_price')}}<span
									v-html="item.shop_price"></span></div>

						</div>
						<div class="dis-box m-top10">
							<div class=" f-05 color-red">{{item.team_num}}{{$t('lang.one_group')}}</div>
							<div class="box-flex f-06 color-red f-weight p-l1" v-html="item.team_price"></div>
							<div>
								<span
									class="min-btn tag-gradients-color br-100 color-white f-03">{{$t('lang.up_group')}}</span>
							</div>
						</div>
					</div>
				</a>
			</div>
		</div>
		<div class="main" v-else>
			<div v-if="teamCateData">
				<!-- 二级频道 -->
				<van-swipe class="swipe_wrap" indicator-color="white"
					v-if="teamCateData.team_categories_child && teamCateData.team_categories_child.length">
					<van-swipe-item v-for="(categoriesChilds, categoriesChildsIndex) in categoriesChild"
						:key="categoriesChildsIndex">
						<ul class="sub_nav">
							<li class="sub_nav_item" v-for="(item,index) in categoriesChilds" :key="index">
								<router-link :to="{ path: 'team/list', query: { id: item.tc_id }}">
									<img class="nav_img" :src="item.tc_img" />
									<p class="text-center m-top06 f-03 color-9">{{item.name}}</p>
								</router-link>
							</li>
						</ul>
					</van-swipe-item>
				</van-swipe>
				<!-- 品牌精选 -->
				<div class="brand_choice"
					v-if="teamCateData.team_categories_brand && teamCateData.team_categories_brand.length">
					<h4 class="title">品牌精选</h4>
					<van-swipe indicator-color="white">
						<van-swipe-item v-for="(categoriesBrandItem, categoriesBrandIndex) in categoriesBrand"
							:key="categoriesBrandIndex">
							<ul class="sub_nav">
								<li class="sub_nav_item" v-for="(item,index) in categoriesBrandItem" :key="index">
									<router-link :to="{ path: 'team/list', query: { id: tc_id, brand_id: item.brand_id }}">
										<img class="nav_img" :src="item.brand_logo" />
										<p class="text-center m-top06 f-03 color-9">{{item.brand_name}}</p>
									</router-link>
								</li>
							</ul>
						</van-swipe-item>
					</van-swipe>
				</div>
				<!-- 商品列表 -->
				<div class="goods-li" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
					<a hred="javascript:;" @click="detailLink(item.goods_id)" class="show bg-color-write li"
						v-for='(item,index) in teamGoodsData' :key="index">
						<div class="left">
							<img v-if="item.goods_thumb" class="img" :src="item.goods_thumb" />
							<img v-else class="img" src="../../../assets/img/not_goods.png" />
						</div>
						<div class="right">
							<h4 class="f-05 color-3 twolist-hidden">{{item.goods_name}}</h4>
							<div class="dis-box cont">
								<div class="f-02 color-9 box-flex">{{$t('lang.single_purchase_price')}}<span
										v-html="item.shop_price"></span></div>
							</div>
							<div class="dis-box m-top10">
								<div class=" f-05 color-red">{{item.team_num}}{{$t('lang.one_group')}}</div>
								<div class="box-flex f-06 color-red f-weight p-l1" v-html="item.team_price"></div>
								<div>
									<span
										class="min-btn btn-submit br-100 color-white f-03">{{$t('lang.up_group')}}</span>
								</div>
							</div>
						</div>
					</a>
				</div>
			</div>
			<div v-else>
			</div>
			<!--内页内容 e-->
		</div>
		<!--拼团首页 e-->
		<TeamTabbar />
		<CommonNav :routerName="routerName"></CommonNav>
		<template v-if="loading">
			<van-loading type="spinner" color="black" />
		</template>
	</div>
</template>
<script>
	import {
		mapState
	} from 'vuex'
	import {
		Loading,
		Tab,
		Tabs,
		Swipe,
		SwipeItem,
		Row,
		Col,
		Cell,
		CellGroup,
		Toast,
		Waterfall
	} from 'vant'

	import CommonNav from '@/components/CommonNav'
	import TeamTabbar from './Detail/TeamTabbar'
	import arrRemove from '@/mixins/arr-remove'

	export default {
		name: "team",
		components: {
			CommonNav,
			TeamTabbar,
			[Loading.name]: Loading,
			[Tab.name]: Tab,
			[Tabs.name]: Tabs,
			[Swipe.name]: Swipe,
			[SwipeItem.name]: SwipeItem,
			[Row.name]: Row,
			[Col.name]: Col,
			[Cell.name]: Cell,
			[CellGroup.name]: CellGroup,
			[Toast.name]: Toast,
		},
		directives: {
			WaterfallLower: Waterfall('lower')
		},
		data() {
			return {
				routerName: 'team',
				disabled: false,
				loading: true,
				size: 10,
				page: 1,
				active: 0,
				team_id: 0,
				virtual_order: [],
				swiperOption2: {
					notNextTick: true,
					direction: 'vertical',
					observer: true, //修改swiper自己或子元素时，自动初始化swiper
					observeParents: true, //修改swiper的父元素时，自动初始化swiper
					loop: true,
					autoplay: {
						delay: 5000,
						disableOnInteraction: false
					}
				},
			};
		},
		created() {
			let that = this

			setTimeout(() => {
				uni.getEnv(function(res) {
					if (res.plus || res.miniprogram) {
						uni.redirectTo({
							url: '../../pagesA/team/team'
						})
					}
				})
			}, 100)

			that.loadingData(that.teamNavAll());

			this.virtualOrder();
		},
		computed: {
			...mapState({
				teamIndexData: state => state.team.teamIndexData,
				teamCateData: state => state.team.teamCateData,
			}),
			teamGoodsData: {
				get() {
					return this.$store.state.team.teamGoodsData
				},
				set(val) {
					this.$store.state.team.teamGoodsData = val
				}
			},
			swiper() {
				return this.$refs.announSwiper.swiper
			},
			categoriesChild() {
				const list = this.teamCateData.team_categories_child || []

				if (list.length > 10) {
					const childs = []
					for (var i = 0; i < list.length; i += 10) {
						childs.push(list.slice(i, i + 10))
					}
					return childs
				} else {
					return [list]
				}
			},
			categoriesBrand() {
				const list = this.teamCateData.team_categories_brand || []

				if (list.length > 10) {
					const childs = []
					for (var i = 0; i < list.length; i += 10) {
						childs.push(list.slice(i, i + 10))
					}
					return childs
				} else {
					return [list]
				}
			}
		},
		methods: {
			//全部
			teamNavAll() {
				this.$store.dispatch('setTeamIndex')
				let tc_id = this.tc_id = 0
				this.$store.dispatch({
					type: 'setTeamCate',
					tc_id: tc_id
				})
				this.getGoodsList(tc_id);
			},
			teamNav(item) {
				let that = this
				that.tc_id = item.tc_id
				that.$store.dispatch({
					type: 'setTeamCate',
					tc_id: that.tc_id
				})
				that.getGoodsList(that.tc_id)
			},
			//商品列表
			getGoodsList(tc_id) {
				this.$store.dispatch({
					type: 'setTeamGoods',
					size: this.size,
					page: this.page,
					tc_id: tc_id
				});
			},
			loadMore() {
				setTimeout(() => {
					this.disabled = true
					if (this.page * this.size == this.teamGoodsData.length) {
						this.page++
						this.getGoodsList(this.tc_id)
					}
				}, 200);
			},
			//加载动画
			loadingData(url) {
				Toast.loading({
					duration: 600,
					mask: true,
					message: this.$t('lang.loading')
				}, url);
			},
			detailLink(goods_id) {
				this.$router.push({
					name: 'team-detail',
					query: {
						goods_id: goods_id,
						team_id: this.team_id
					}
				})
			},
			virtualOrder() {
				this.$http.get(`${window.ROOT_URL}api/team/virtual_order`).then(res => {
					if (res.data.status == "success") {
						this.virtual_order = res.data.data
					} else {
						Toast('数据错误')
					}
				})
			},
			onChildsTab(index){
				this.active = index+1;

				this.teamNav(this.teamIndexData.team_categories[index])
			}
		},
		watch: {
			teamGoodsData() {
				if (this.page * this.size == this.teamGoodsData.length) {
					this.disabled = false
					this.loading = true
				} else {
					this.loading = false
				}

				this.teamGoodsData = arrRemove.trimSpace(this.teamGoodsData)
			}
		}
	};
</script>
<style>
	.v-order {
		height: 5rem;
		position: absolute;
		top: 2rem;
		left: 1rem;
	}

	.swiper-virtual {
		font-size: 1.3rem;
		height: 100%;
		@include flex1-1();
	}

	.swiper-virtual .swiper-slide {
		overflow: hidden;
		position: relative;
		@include box-pack(center, start);
		@include direction(center, flex-start);
	}

	.swiper-virtual .swiper-slide section {
		width: 100%;
		display: flex;
		flex-direction: row;
		justify-content: flex-start;
		align-items: center;
		background: rgba(0, 0, 0, .2);
		border-radius: 5rem;
		padding-right: 1rem;
	}

	.swiper-virtual .swiper-slide section .pic {
		width: 3rem;
		height: 3rem;
		border-radius: 50%;
		overflow: hidden;
		margin-right: 1rem;
	}

	.swiper-virtual .swiper-slide section .pic img {
		border-radius: 50%;
	}

	.swiper-virtual .swiper-slide section .name {
		margin-right: 1rem;
		color: #fff;
	}

	/* 二级导航 start */
	.main {
		padding: 0.8rem;
	}

	.brand_choice {
		margin-bottom: 0.8rem;
		border-radius: 0.5rem;
		background-color: #fff;
	}

	.brand_choice .title {
		padding: 1rem 1rem 0.2rem;
		font-weight: 700;
	}

	.swipe_wrap {
		margin-bottom: 0.8rem;
	}

	.sub_nav {
		overflow: hidden;
		display: flex;
		flex-wrap: wrap;
		padding-top: 1rem;
		border-radius: 0.5rem;
		background-color: #fff;
	}

	.sub_nav_item {
		width: 20%;
		padding: 0 0.8rem 1rem;
	}

	.sub_nav_item .nav_img {
		width: 100%;
		height: calc((100vw - 1.6rem) / 5 - 1.6rem);
		object-fit: contain;
		border-radius: 50%;
	}

	.brand_choice .sub_nav_item .nav_img {
		height: 2.5rem;
		border-radius: 0;
	}

	/* 二级导航 end */

	/* 商品列表 */
	.goods-li {
		border-radius: 0.5rem;
	}

	.goods-li .li {
		border-radius: 0;
		border-top: 1px solid #f4f4f4;
	}
</style>
