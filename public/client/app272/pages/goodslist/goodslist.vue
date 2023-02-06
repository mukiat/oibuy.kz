<template>
	<view class="container goodslist">
		<view class="header">
			<uni-nav-bar color="#333333" background-color="#FFFFFF" shadow="false" :statusBar="false" fixed="false" leftState="false">
				<view class="input-view">
					<input confirm-type="search" disabled="true" @click="searchFocus" class="input" type="text" :placeholder="placeholder" />
					<uni-icons type="search" size="22" color="#666666" class="right-icon" @click="searchFocus"></uni-icons>
				</view>
				<view slot="right"><text class="iconfont" @click="handleViewSwitch" :class="[mode === 'grid' ? 'icon-grid' : 'icon-list']"></text></view>
			</uni-nav-bar>
			<dsc-filter :filter="filter" :isPopupVisible="isPopupVisible" v-on:getFilter='handleFilter' @setPopupVisible="setPopupVisible"></dsc-filter>
		</view>
		<view class="product-list-lie" v-if="!dscLoading">
			<dsc-product-list :list="cateGoodsList" :mode="mode" v-if="cateGoodsList"></dsc-product-list>
		</view>
		<uni-drawer :visible="isPopupVisible" mode="right" @close="isPopupVisible = false">
			<view class="show-popup-filter">
				<scroll-view class="scroll-view top" scroll-y>
					<view class="section">
						<view class="title"><text>价格区间</text></view>
						<view class="section-warp price-filter">
							<view class="input">
								<input type="tel" v-model="filter.min" :placeholder="$t('lang.minimum_price')" />
							</view>
							<text class="hang"></text>
							<view class="input">
								<input type="tel" v-model="filter.max" :placeholder="$t('lang.top_price')" />
							</view>
						</view>
						<view class="section-warp select-tabs">
							<view class="select-list" :class="{'active':item.sn == price_filter_sn}" v-for="(item,index) in grade" :key="index" @click="onPriceFilter(item)">
								<rich-text :nodes="item.price_range"></rich-text>
							</view>
						</view>
					</view>
					<view class="section" v-if="filter.brandResult.length > 0">
						<view class="title">
							<text>品牌</text>
							<view class="right-icon" @click="isPopupBrand = !isPopupBrand"><i class="iconfont" :class="[isPopupBrand ? 'icon-less' : 'icon-moreunfold']" v-if="filter.brandResult.length > 9"></i></view>
						</view>
						<view class="section-warp select-tabs">
							<view class="select-list" :class="{'active':filter.brandResultArr.includes(item)}" @click="onBrandResult(item)" v-show="index < 9 || isPopupBrand" v-for="(item,index) in filter.brandResult" :key="index">
								<text>{{ item.brand_name }}</text>
							</view>
						</view>
					</view>
					<view class="section" v-for="(item,index) in attribute" :key="index">
						<view class="title">
							<text>{{item.filter_attr_name}}</text>
							<view class="right-icon" @click="isAttribute(item.filter_attr_id)"><i class="iconfont" :class="[!attribute_id.includes(item.filter_attr_id) ? 'icon-less' : 'icon-moreunfold']"></i></view>
						</view>
						<view class="section-warp select-tabs" :class="[attribute_id.includes(item.filter_attr_id) ? 'hide' : 'show']">
							<view class="select-list" :class="{'active':filter.filter_attr[index] == attritem.goods_attr_id}" v-for="(attritem,attrindex) in item.attr_list" :key="attrindex" @click="onAttributeValue(attritem,index)">
								<text>{{attritem.attr_value}}</text>
							</view>
						</view>
					</view>
				</scroll-view>
				<view class="footer">
					<view class="btn-bar btn-bar-min">
						<view class="btn btn-white" @click="closeFilter">{{$t('lang.merchants_reset')}}</view>
						<view class="btn btn-red btn-bor-red" @click="submitFilter">{{$t('lang.confirm')}}</view>
					</view>
				</view>
			</view>
		</uni-drawer>
		
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>

		<dsc-common-nav></dsc-common-nav>
		
		<!--返回顶部-->
		<dsc-filter-top :scrollState="scrollState" outerClass="true"></dsc-filter-top>
	</view>
</template>

<script>
	import { mapState } from 'vuex'

	import uniNavBar from '@/components/uni-nav-bar.vue'
	import uniIcons from '@/components/uni-icons/uni-icons.vue'
	import uniDrawer from '@/components/uni-drawer.vue'
	import dscFilter from '@/components/dsc-filter.vue'
	import dscProductList from '@/components/dsc-product-list.vue'
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import universal from '@/common/mixins/universal.js';
	
	//返回顶部
	import dscFilterTop from '@/components/dsc-filter-top'

	export default {
		mixins:[universal],
		components: {
			uniNavBar,
			uniIcons,
			uniDrawer,
			dscFilter,
			dscProductList,
			dscCommonNav,
			dscFilterTop
		},
		data() {
			return {
				queryObj:'',
				disabled:false,
				loading:true,
				mode:'grid',
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
				cat_id:0,
				page:1,
				size:10,
				winHeight:600,
				cou_id:0,
				placeholder:this.$t('lang.enter_search_keywords'),
				dscLoading:true,
				grade:[],
				attribute:[],
				attribute_id:[],
				price_filter_sn:null,
				scrollState:false
			};
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pages/goodslist/goodslist?id=' + this.cat_id
			}
		},
		computed:{
			...mapState({
				cateGoodsList: state => state.category.cateGoodsList
			}),
			checkedSelf(){
				return this.filter.self == '0' ? false : true
			}
		},
		methods:{
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

				if(this.queryObj.keywords){
					this.$store.dispatch('setGoodsList',{
						keywords:this.queryObj.keywords,
						brand:this.filter.brand_id,
						min:this.filter.min,
						max:this.filter.max,
						filter_attr:this.filter.filter_attr,
						goods_num:this.filter.goods_num,
						size:this.size,
						page:this.page,
						sort:this.filter.sort,
						order:this.filter.order,
						self:this.filter.self,
						intro:this.filter.intro,
						cou_id:this.cou_id
					})
				}else{
					this.$store.dispatch('setGoodsList',{
						cat_id:this.cat_id,
						brand:this.filter.brand_id,
						min:this.filter.min,
						max:this.filter.max,
						filter_attr:this.filter.filter_attr,
						goods_num:this.filter.goods_num,
						size:this.size,
						page:this.page,
						sort:this.filter.sort,
						order:this.filter.order,
						self:this.filter.self,
						cou_id:this.cou_id
					})
				}
			},
			handleViewSwitch(){
				this.mode = this.mode === 'grid' ? 'list' : 'grid'
			},
			setPopupVisible(val){
				this.isPopupVisible = val;
				
				if(this.isPopupVisible){
					//价格区间
					uni.request({
						url:this.websiteUrl + '/api/catalog/grade',
						method:'GET',
						data:{
							cat_id:this.cat_id
						},
						header: {
							'Content-Type': 'application/json',
							'token': uni.getStorageSync('token'),
							'X-Client-Hash':uni.getStorageSync('client_hash')
						},
						success: (res) => {
							if(res.data.status == 'success'){
								this.grade = res.data.data;
							}
						}
					})
					//属性列表
					uni.request({
						url:this.websiteUrl + '/api/catalog/attribute',
						method:'GET',
						data:{
							cat_id:this.cat_id
						},
						header: {
							'Content-Type': 'application/json',
							'token': uni.getStorageSync('token'),
							'X-Client-Hash':uni.getStorageSync('client_hash')
						},
						success: (res) => {
							if(res.data.status == 'success'){
								this.attribute = res.data.data;
		
								for(let index of this.attribute){
									this.filter.filter_attr.push(0)
								}
							}
						}
					})
				}
			},
			handleFilter(o){
				this.filter.sort = o.sort;
				this.filter.order = o.order;

				this.getGoodsList(1);
			},
			handleTags(val){
				if(val == 'hasgoods'){
					this.filter.goods_num = this.filter.goods_num == 0 ? 1 : 0;
				}else{
					this.filter.promote = this.filter.promote == 0 ? 1 : 0;
				}
			},
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
				this.isPopupVisible = false;
				this.getGoodsList(1);
			},
			selectBrand(){
				uni.request({
					url:this.websiteUrl + '/api/catalog/brandlist',
					method:'POST',
					data:{
						cat_id:this.cat_id,
						keywords:this.queryObj.keywords
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						if(res.data.data.length > 0){
							this.filter.brandResult = res.data.data
						}
					}
				})
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
			switchChange(e){
				this.filter.self = e.detail.value == true ? 1 : 0
			},
			searchFocus(){
				let pages = getCurrentPages()
				if(pages.length > 1){
					if(pages[pages.length - 2].route == 'pages/search/search'){
						uni.navigateBack()
					}else{
						
						uni.navigateTo({
							url:'/pages/search/search?cou_id=' + this.cou_id
						})
					}
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
		onLoad(e) {
			this.queryObj = e;
			this.cat_id = e.id;
			this.cou_id = e.cou_id;

			if(this.queryObj.keywords){
				this.placeholder = this.queryObj.keywords
			}
			
			this.getGoodsList(1);
			this.winHeight = uni.getSystemInfoSync().screenHeight - 95;
			
			this.selectBrand();
		},
		onShow(){
			this.getGoodsList(1);
		},
		onReachBottom(){
			if(this.page * this.size == this.cateGoodsList.length){
				this.page ++
				this.getGoodsList()
			}
		},
		onPageScroll(e) {
			this.scrollState = e.scrollTop > 800 ? true : false
		},
		watch:{
			cateGoodsList(){
				this.dscLoading = false
			},
			isPopupVisible(){
				if(this.isPopupVisible == false){
					this.filter.self = '0'
					this.filter.goods_num = '0'
					this.filter.promote = '0'
					this.filter.min = ''
					this.filter.max = ''
					this.filter.brand_id = []
				}
			}
		}
	}
</script>

<style lang="scss" scoped>
	/*header*/
	.header .uni-navbar { border-bottom: solid 1px #e6e6e6;}
	.header .uni-navbar view{ line-height: 50px;}
	.header .uni-navbar-header{ height: 50px;}
	.header .uni-navbar-header .uni-navbar-header-btns{ padding: 0;}
	.header .uni-navbar-container{ margin: 0 20upx;}
	.header .uni-navbar .input-view{background-color: #FFFFFF; border:1px solid #e6e6e6; margin: 9px 0; display: flex; flex-direction: row; align-items: center;}

	/*popop*/
	.con-filter-view{ padding-bottom: 50px; }
	.mod_list{ background: #ffffff; margin-bottom: 20upx;}
	.mod_list .item .li_line{ display: flex; flex-direction: row; justify-content: space-between; align-items: center; padding: 20upx;}
	.mod_list .item.radio-switching{ padding: 20upx; }
	.mod_list .item.radio-switching .li_line{ padding: 0;}
	.tags_selection{ background: #FFFFFF; margin-bottom: 20upx; padding: 20upx 0 0 20upx; display: flex; flex-direction:row;}
	.tags_selection text{  padding: 5upx 30upx; background: #f7f7f7; border-radius: 10upx; margin: 0 20upx 20upx 0; border:1px solid #f7f7f7;}
	.tags_selection .active{ background: #FFFFFF; border:1px solid #e93b3d; color: #e93b3d;}

	.filterlayer_price{ padding: 20upx; position: relative;}
	.filterlayer_price:before{ content: "";position: absolute;z-index: 1;pointer-events: none;background-color: #e5e5e5;height: 1px;left: 0;right: 0;top: 0;}
	.filterlayer_price .filterlayer_price_area{ display: flex;}
	.filterlayer_price_area_input{ flex: 1; background: #f7f7f7; color: #333333; padding: 10upx; text-align: center; }
	.filterlayer_price_hang{ width: 50upx; position: relative;}
	.filterlayer_price_hang:before{ content: "";position: absolute;top: 50%;left: 50%;margin-left: -5px;width: 10px;height: 1px;background-color: #f1f1f1;}

	.filterlayer_bottom_buttons{ display: flex; flex-direction: row; position:absolute; bottom: 0; width: 100%; background: #FFFFFF; z-index: 9;}
	.filterlayer_bottom_button{ height: 49px; line-height: 49px; flex: 1; text-align: center; box-shadow: 0 -1px 2px 0 rgba(0, 0, 0, 0.07);}
	.filterlayer_bottom_button.active{ background-color: #e93b3d; color: #ffffff;}

	.sf_layer{ background: #FFFFFF; height: 100%;}
	.sf_layer .sf_layer_sub_title{ display: flex; flex-direction: row; align-items: center; padding: 20upx; background-color: #FFFFFF;}
	.sf_layer .sf_layer_sub_title .tit{ width: 150upx;}
	.sf_layer .sf_layer_sub_title text{ flex: 1 1 0%;}

	.center-box{ width: 100%; background: #FFFFFF;}
	
	/*筛选*/
	.show-popup-filter{
		background: #f4f4f4;
		height: 100%;
	
		.top{
			height: calc(100% - 120rpx);
		}
	
		.section{
			padding: 10px;
			background: #fff;
	
			.title{
				display: flex;
				justify-content: space-between;
				align-items: center;
				text{
					font-weight: 700;
					color: #000;
					font-size: 16px;	
				}
	
				.right-icon{
					.iconfont{
						font-size: 14px;
						margin-right: 5px;
					}
				}
			}
	
			.section-warp{
				margin-top: 10px;
				&.price-filter{
					display: flex;
					flex-direction: row;
					align-items: center;
	
					.input{
						width: 40%;
						background: #f2f2f2;
						border-radius: 20px;
						height: 40px;
						text-align: center;
	
						input{
							width: 100%;
							background: transparent;
							height: 100%;
							text-align: center;
							font-size: 14px;
						}
					}
	
					.hang{
						margin: 0 10px;
						height: 1px;
						background: #000;
						width: 10px;
					}
				}
				
				&.select-tabs{
					display: flex;
					flex-direction: row;
					align-items: center;
					flex-wrap: wrap;
	
					.select-list{
						width: calc(33.3% - 6px);
						box-sizing: border-box;
						margin: 0 9px 10px 0;
						padding: 0;
	
						text,rich-text{
							padding: 5px;
							background: #f2f2f2;
							text-align: center;
							display: block;
							border-radius: 40px;
							white-space: nowrap;
							overflow: hidden;
							text-overflow: ellipsis;
							border:1px solid transparent;
						}
	
						&:nth-child(3n){
							margin-right: 0;
						}
	
						&.active{
							text,rich-text{
								border-color: #e93422;
								color: #e93422;
								background-color: #faeeec;
							}
						}
					}
				}
				
				&.hide{
					display: none;
				}
			}
		}
	
		.footer{
			position: absolute;
			bottom: 0;
			left: 0;
			right: 0;
			background: #fff;
			.btn-bar{
				.btn{
					border-radius: 50px;
					height: 60rpx;
					line-height: 60rpx;
					font-size: 16px;
					
					&.btn-red{
						color: #FFFFFF;
					}
				}
			}
		}
	}
</style>
