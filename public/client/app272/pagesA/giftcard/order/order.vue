<template>
	<view class="giftcard-order">
		<view class="goods-list" v-if="list.length > 0">
			<view class="goods-item" v-for="(item,index) in list" :key="index">
				<view class="goods-left"><image :src="item.goods_thumb" class="img" /></view>
				<view class="goods-right">
					<view class="goods-name twolist-hidden">{{item.goods_name}}</view>
					<view class="plan-box">
						<view>{{$t('lang.label_card')}}{{item.gift_sn}}</view>
						<view class="uni-red" v-if="item.status == 1">{{$t('lang.order_status_09')}}</view>
						<view class="btn" @click="pickGoods(item.gift_gard_id)" v-else-if="item.status == 2">{{$t('lang.received')}}</view>
						<view class="uni-red" v-else>{{$t('lang.ss_received')}}</view>
					</view>
				</view>
			</view>
		</view>
		<block v-else>
			<dsc-not-content></dsc-not-content>
		</block>
		<view class="uni-loadmore" v-if="showLoadMore">{{loadMoreText}}</view>

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
				id: '',
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
			loadOrder(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}

				uni.request({
					url:this.websiteUrl + '/api/gift_gard/take_list',
					data:{
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
							if(this.page > 1){
								data.forEach(v=>{
									this.list.push(v);
								})
							}else{
								this.list = data
							}
						}
					},
				});
			},
			pickGoods(gift_gard_id){
				
				uni.request({
					url:this.websiteUrl + '/api/gift_gard/confim_goods',
					data:{
						take_id:gift_gard_id
					},
					method:'GET',
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						let data = res.data;
						
						if(data.status == 'success'){
							
							if(data.error){
								uni.showToast({
									title:data.msg,
									icon:'none'
								});
							}
							
							this.loadOrder(1)
						}else{
							uni.showToast({ title: data.errors.message || 'fail', icon: "none" });
						}
				
					},
				});
				
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
				this.loadOrder()
			}else{
				this.loadMoreText = this.$t('lang.no_more');
				return;
			}
		},
		onLoad(){
			this.loadOrder(1);
		},
	}
</script>

<style>

</style>
