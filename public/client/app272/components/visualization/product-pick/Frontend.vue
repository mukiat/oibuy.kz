<template>
	<view class="product-new" :style="{'min-height':minHeight}">
		<view class="tabs">
			<view class="item" v-for="(item,index) in tabs" :key="index" :class="{'active':filter == index}" @click="tabClick(index)">
				<text class="tit">{{item.tit}}</text>
				<text class="txt">{{item.txt}}</text>
			</view>
		</view>

		<block v-if="filter != 1">
			<block v-if="!dscLoading">
				<view class="goods-list" v-if="prolist">
					<view class="item" v-for="(item, index) in prolist" :key="index" @click="linkHref(item.goods_id)">
						<view class="img-box">
							<image class="img" :src="item.goods_thumb" mode="widthFix"></image>
							<view class="goods-label-suspension" v-if="item.goods_label_suspension && item.goods_label_suspension.formated_label_image">
								<image :src="item.goods_label_suspension.formated_label_image" class="img"></image>
							</view>
						</view>
						<view class="info-box">
							<view class="goods-name twolist-hidden"><image class="country_icon" :src="item.country_icon" v-if="item.country_icon"></image>{{item.title || item.goods_name}}</view>
							<currency-price :price="item.shop_price" :size="18" style="display: block; margin-top: 8px;"></currency-price>
							<view class="label-list" v-if="item.goods_label && item.goods_label.length > 0">
								<view class="label-img" v-for="(label,labelIndex) in item.goods_label" :key="labelIndex" @click="$outerHref(label.label_url)">
									<image :src="label.formated_label_image" mode="heightFix"></image>
								</view>
							</view>
						</view>
					</view>
				</view>
				<uni-load-more :status="loadMoreStatus" :content-text="contentText" v-if="page > 1 && showLoadMore" />
			</block>
			<block v-else>
				<uni-load-more status="loading" type="false" />
			</block>
		</block>
		<dsc-community routerName="tab" :scrollPickOpen="scrollPickOpen" @updateScrollPickOpen2="updateScrollPickOpen2" v-else></dsc-community>
	</view>
</template>

<script>
import community from '@/components/dsc-community/community.vue';
import dscNotContent from '@/components/dsc-not-content.vue';
import uniLoadMore from '@/components/uni-load-more/uni-load-more.vue';

export default{
	props: ['module', 'preview','shopId','scrollPickOpen','userId'],
	data(){
		return {
			previewProlist: [
                {
                    title: '第一张图片',
                    sale: '0',
                    stock: '0',
                    price: '¥238.00',
                    marketPrice: '¥413.00'
                },
                {
                    title: '第二张图片',
                    sale: '0',
                    stock: '0',
                    price: '¥38.00',
                    marketPrice: '¥43.00'
                }
            ],
			prolist: [],
			tabs:[
				{
					tit:'精选',
					txt:'为你推荐'
				},
				{
					tit:'社区',
					txt:'新奇好物'
				},
				{
					tit:'新品',
					txt:'潮流上新'
				},
				{
					tit:'热卖',
					txt:'火热爆款'
				},
			],
			filter:0,
			page:1,
			size:10,
			type:'is_best',
			dscLoading:false,
			footerCont:false,
			loadMoreStatus:'more',
			contentText: {
				contentdown: this.$t('lang.view_more'),
				contentrefresh: this.$t('lang.loading'),
				contentnomore: this.$t('lang.no_more')
			},
			showLoadMore: false,
			minHeight:''
		}
	},
	components:{
		dscNotContent,
		'dsc-community': community
	},
	computed:{
		newUserId(){
			return this.userId
		}
	},
	mounted(){
		this.getGoodsList(1);
	},
	methods: {
		tabClick(index){
			this.prolist = [];
			this.filter = index;
			this.dscLoading = true;
			this.minHeight = '750px'

			if(index == 1){
				setTimeout(()=>{
					this.minHeight = '0';
				},3000)
				return
			}else if(index == 0){
				this.type = 'is_best'
			}else if(index == 2){
				this.type = 'is_new'
			}else if(index == 3){
				this.type = 'is_hot'
			}

			this.getGoodsList(1);
		},
		linkHref(goods_id){
			uni.navigateTo({
				url:'/pagesC/goodsDetail/goodsDetail?id='+goods_id
			})
		},
		getGoodsList(page){
			if(page){
				this.page = page
				this.size = Number(page) * 10
			}

			this.loadMoreStatus = "loading"

			uni.request({
				url: this.websiteUrl + '/api/goods/type_list',
				method: 'GET',
				data: {
					page:this.page,
					size:this.size,
					type:this.type
				},
				header: {
					'Content-Type': 'application/json',
					'token': uni.getStorageSync('token'),
					'X-Client-Hash':uni.getStorageSync('client_hash')
				},
				success: (res) => {
					let data = res.data.data
					if(this.page > 1){
						this.prolist = this.prolist.concat(data);
					}else{
						this.prolist = data
					}

					this.$emit('updateScrollPickOpen',false)

					this.dscLoading = false
					this.minHeight = '0'
				},
				fail: (err) => {
					console.error(err)
				}
			})
		},
		updateScrollPickOpen2(e){
			this.$emit('updateScrollPickOpen',e)
		}
	},
	watch:{
		scrollPickOpen(){
			if(this.scrollPickOpen){
				this.showLoadMore = true
				this.loadMoreStatus = 'loading';
				if(this.page * this.size == this.prolist.length){
					this.page ++
					this.getGoodsList()
				}else{
					this.loadMoreStatus = "noMore"
					return;
				}
			}
		},
		newUserId(){
			this.getGoodsList(1);
		}
	}
}
</script>

<style scoped>
.product-list{ padding: 0 18upx 18upx 18upx !important;}
.product-list .outer{ display: flex; flex-direction: row; justify-content: space-between;}
.product-list .outer .left{ width: auto; }
.product-list .outer .sales-volume{ font-size: 25upx; color: #999;}

.product-list-big .uni-product-list{ flex-direction: column;}
.product-list-big .uni-product-list .image-view,
.product-list-big .uni-product-list .uni-product-info{ width:100%; height: auto; }

/*新版样式*/
.product-new{
    margin: 20upx 20upx 0;
	min-height: 500upx;
}
.product-new .tabs{
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    padding: 20upx 0;
}
.product-new .tabs .item{
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    position: relative;
	line-height: 1.5;
}
.product-new .tabs .item .tit{
    font-size: 30upx;
    color: #333;
}
.product-new .tabs .item .txt{
    display: inline-block;
    font-size: 25upx;
    color: #888;
    border-radius: 20upx;
    padding: 2upx 16upx;
    margin-top: 8upx;
}
.product-new .tabs .item.active .tit{
	font-weight: 700;
}
.product-new .tabs .item.active .txt{
    background:linear-gradient(-88deg,rgba(255,79,46,1),rgba(249,31,40,1));
    color: #fff;
}
.product-new .tabs .item:after{
    content: ' ';
    position: absolute;
    height: 80%;
    width: 1px;
    right: 0;
    top: 10%;
    background-color: #ccc;
}
.product-new .tabs .item:last-child:after{
    height: 0;
}
.product-new .goods-list{
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
	margin-bottom: 0;
}
.product-new .goods-list .item{
    width: 48.5%;
    background-color: #fff;
    border-radius: 20upx;
    overflow: hidden;
    margin-bottom: 2.5%;
}
.product-new .goods-list .item .img-box{
	line-height: 0;
	position: relative;
}
.product-new .goods-list .item .info-box{
    padding: 20upx;
}
.product-new .goods-list .item .info-box .goods-name{
    color: #000;
    font-weight: 500;
	height: 42px;
	line-height: 21px;
}

.country_icon{
	width: 43rpx;
	height: 30rpx;
	padding-right: 7rpx;
	position: relative;
	top: 5rpx;
}

.goods-list .label-list{ overflow: hidden; margin-top: 10upx;}
.goods-list .label-list .label-img{ height: 16px; margin: 0 10upx 0 0; }
.goods-list .label-list .label-img:last-child{ margin-right: 0;}
</style>
