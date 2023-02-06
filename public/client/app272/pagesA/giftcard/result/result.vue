<template>
	<view class="giftcard-result">
		<view class="title">
			<view class="left">{{$t('lang.gift_card_goods_list')}}</view>
			<view class="right">
				<view class="card-number">{{$t('lang.label_card')}}{{id}}</view>
				<view class="btn" @click="logOut">{{$t('lang.drop_out')}}</view>
			</view>
		</view>
		<view class="result-content">
			<view class="goods-list" v-if="list.length > 0">
				<view class="goods-item" v-for="(item,index) in list" :key="index">
					<view class="goods-left"><image :src="item.goods_thumb" class="img" /></view>
					<view class="goods-right">
						<view class="goods-name twolist-hidden">{{item.goods_name}}</view>
						<view class="plan-box">
							<view class="price">{{item.shop_price}}</view>
							<view class="btn" @click="pickGoods(item.goods_id)">{{$t('lang.pick_up_goods')}}</view>
						</view>
					</view>
				</view>
			</view>
			<block v-else>
				<dsc-not-content></dsc-not-content>
			</block>
			<view class="uni-loadmore" v-if="showLoadMore">{{loadMoreText}}</view>
		</view>

		<dsc-common-nav>
			<navigator url="../giftcard" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.my_gift_card')}}</text>
			</navigator>
		</dsc-common-nav>
	</view>
</template>

<script>
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	export default {
		data() {
			return {
				id:'',
				page: 1,
				size: 10,
				list: [],
				loadMoreText: this.$t('lang.loading'),
				showLoadMore: false,
			}
		},
		components:{
			dscCommonNav,
			dscNotContent
		},
		methods: {
			checkLoginGift(){
				uni.request({
					url:this.websiteUrl + '/api/gift_gard',
					data:{},
					method:'GET',
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						let data = res.data;
						if(data.status == 'success'){
							if(data.data.error != 0){
								uni.showToast({
									title:data.data.msg,
									icon:'success'
								});

								setTimeout(()=>{
									uni.redirectTo({
										url:'/pagesA/giftcard/giftcard'
									})
								},1000)
							}
						}else{
							if(data.errors.code == 12){
								uni.showToast({
									title: this.$t('lang.user_un_login'),
									icon:'none'
								})

								setTimeout(()=>{
									uni.navigateTo({
										url:'/pagesB/login/login?delta=1'
									})
								},1000)
							}
						}
					}
				})
			},
			loadList(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}

				uni.request({
					url:this.websiteUrl + '/api/gift_gard/gift_list',
					data: {
						page:this.page,
						size:this.size
					},
					method:'GET',
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						let data = res.data.data;

						if(res.data.status == 'success'){
							this.id = data.gif;
							if(this.page > 1){
								data.goods.forEach(v=>{
									this.list.push(v);
								})
							}else{
								this.list = data.goods;
							}
						}
					},
				});
			},
			logOut(){
				uni.request({
					url:this.websiteUrl + '/api/gift_gard/exit_gift',
					data:{},
					method:'GET',
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						let data = res.data
						uni.showToast({
							title:data.data.msg,
							icon:'none'
						});
						if(data.data.error == 0){
							setTimeout(()=>{
								uni.redirectTo({
									url:'/pagesA/giftcard/giftcard'
								})
							},1000)
						}
					},
				});
			},
			pickGoods(id){
				uni.navigateTo({
					url:'/pagesA/giftcard/address/address?id='+id
				})
			}
		},
		onUnload(){
			this.loadMoreText = this.$t('lang.load_more');
			this.showLoadMore = false;
		},
		onReachBottom(){
			this.showLoadMore = true

			if(this.page * this.size == this.list.length){
				this.page ++
				this.loadList()
			}else{
				this.loadMoreText = this.$t('lang.no_more');
				return;
			}
		},
		onLoad(){
			this.loadList(1);
		},
		onShow(){
			this.checkLoginGift();
		}
	}
</script>

<style>
.giftcard-result .title{ background: #FFFFFF; display: flex; justify-content: space-between; align-items: center; padding: 20upx; margin-bottom: 20upx; position: fixed; top: 0; left: 0; right: 0;}
.giftcard-result .title .right{ display: flex; flex-direction: row; align-items: center; justify-content: flex-end;}
.giftcard-result .title .right .card-number{ margin-right: 10upx; }
.giftcard-result .title .right .btn{ padding: 0upx 25upx; color: #f92028; border:2upx solid #f92028; }

.giftcard-result .result-content{ margin-top: 120upx;}
</style>
