<template>
	<view class="container-bwg">
		<view class="app-header-top">
		<view class="header">
			<view class="input-view">
				<uni-icons type="search" size="20" color="#666666"></uni-icons>
				<input confirm-type="search" class="input" type="text" v-model="keyValue" focus="true" :placeholder="placeholder" @input="KeyInput" @confirm="confirm" />
			</view>
			<button type="warn" size="mini" @click="confirm" class="button">{{$t('lang.search')}}</button>
		</view>
		<view class="ms-search-wraper">
			<view class="recent-search" v-if="isIntergWord.length > 0">
				<view class="ms-search-head">
					<text>{{$t('lang.history_search')}}</text>
					<view class="colse" @click="colse">{{$t('lang.eliminate')}}</view>
				</view>
				<view class="ms-search-tags">
					<text class="text uni-ellipsis" v-for="(item,index) in isIntergWord" :key="index" @click="searchTag(item)">{{item}}</text>
				</view>
			</view>
			<view class="hot-search">
				<view class="ms-search-head clearfix">{{$t('lang.hot_search')}}</view>
				<view class="ms-search-tags">
					<text class="text uni-ellipsis" v-for="(item,index) in search_keywords" :key="index" @click="searchTag(item)">{{ item }}</text>
				</view>
			</view>
		</view>

		<!-- <tabbar :curpage="curpage"></tabbar> -->
		</view>
	</view>
</template>

<script>
	import uniNavBar from '@/components/uni-nav-bar.vue';
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import tabbar from "@/components/tabbar/tabbar.vue";

	export default {
		components:{
			uniNavBar,
			uniIcons,
			tabbar
		},
		data() {
			return {
				keyword:'',
				keyValue:'',
				arr:[],
				latelyKeyword:[],
				search_keywords:[],
				placeholderState:0,
				placeholder:this.$t('lang.enter_search_keywords'),
				titleNView:false,
				statusBar:false,
				intergWord:uni.getStorageSync('LatelyKeyword') ? uni.getStorageSync('LatelyKeyword') : [],
				shopId:0,
				cou_id:0
			};
		},
		// props:{
		// 	intergWord:{
		// 		type:Array,
		// 		default:[]
		// 	}
		// },
		computed:{
			isIntergWord(){
				return this.intergWord
			}
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pages/search/search'
			}
		},
		onLoad(e) {
			this.shopId = e.shopId ? e.shopId : 0;
			this.cou_id = e.cou_id;
		},
		created(){
			this.shopConfig();

			//读取本地缓存
			uni.getStorage({
				key:'LatelyKeyword',
				success: (res) => {
					this.latelyKeyword = res.data
					this.placeholder = res.data[0]
					this.placeholderState = 1
				}
			});
		},
		methods:{
			KeyInput(e){
				this.keyword = e.detail.value;
			},
			searchTag(val){
				let arr = []
				let arr2 = this.latelyKeyword;

				if(arr2){
					arr2.unshift(val);
					arr = this.unique(arr2);
				}

				if(arr.length > 0){
					//存本地缓存
					uni.setStorage({
						key:'LatelyKeyword',
						data:arr,
						success: (res) => {
							if(this.shopId > 0){
								uni.navigateTo({
									url:'/pages/shop/shopGoods/shopGoods?keywords=' + val + '&ru_id=' + this.shopId
								})
							}else{
								uni.navigateTo({
									url:`/pages/goodslist/goodslist?keywords=${val}&cou_id=${this.cou_id}`
								})
							}
						}
					})
				}
			},
			confirm(){
				// this.keyValue = '';

				this.keyword = this.keyword.replace(/\s*/g,"");

				if(!this.keyword && this.placeholderState == 1){
					if(this.shopId > 0){
						uni.navigateTo({
							url:`/pages/shop/shopGoods/shopGoods?keywords=${this.placeholder}&ru_id=${this.shopId}`
						})
					}else{
						uni.navigateTo({
							url:`/pages/goodslist/goodslist?keywords=${this.placeholder}&cou_id=${this.cou_id}`
						})
					}
					return
				}

				//当前搜索 内容
				let arr = []
				let arr2 = this.latelyKeyword;

				if(this.keyword){
					this.placeholder = this.keyword;
					this.placeholderState = 1;

					//和已存在搜索内容组合并去除重复项
					if(arr2){
						arr2.unshift(this.keyword);
						arr = this.unique(arr2);
					}
				}

				if(arr.length > 0){
					//存本地缓存
					uni.setStorage({
						key:'LatelyKeyword',
						data:arr,
						success: (res) => {
							if(this.shopId > 0){
								uni.navigateTo({
									url:'/pages/shop/shopGoods/shopGoods?keywords=' + this.keyword + '&ru_id=' + this.shopId
								})
							}else{
								uni.navigateTo({
									url:`/pages/goodslist/goodslist?keywords=${this.keyword}&cou_id=${this.cou_id}`
								})
							}
						}
					})
				}
			},
			colse(){
				this.placeholder = this.$t('lang.enter_search_keywords');
				this.placeholderState = 0;

				//删除本地缓存
				uni.removeStorageSync('LatelyKeyword');
				this.latelyKeyword = [];
				this.intergWord = [];

				this.$emit('onColse');
			},
			shopConfig(){
				uni.request({
					url:this.websiteUrl + '/api/shop/config',
					method:'GET',
					data:{},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: ({data:{data}}) => {
						if(data.search_keywords){
							this.search_keywords = data.search_keywords.split(',')
						}
					}
				});
			},
			unique(arr){
				//去除重复项
				var result = [], hash = {};
				for (var i = 0, elem; (elem = arr[i]) != null; i++) {
					if (!hash[elem]) {
						result.push(elem);
						hash[elem] = true;
					}
				}
				return result;
			}
		},
	}
</script>

<style>
	/*header*/
	.header { border-bottom: solid 1px #e6e6e6; height: 50px; display: flex; backdrop-color: #fff; padding: 0 10px;}
	.header .input-view{ background-color: #FFFFFF; border:1px solid #e6e6e6; margin: 9px 9px 9px 0; line-height: 30px;}
	.header .button{ width: 120rpx; padding: 0; height: 30px; margin: 9px 0;}

	/*search*/
	.ms-search-wraper{ position: relative; padding: 0 20upx;}
	.recent-search,.hot-search{ padding-top: 30upx;}
	.ms-search-head{ display: flex; flex-direction: row; justify-content: space-between; align-items: center; padding-bottom: 20upx; font-size: 30upx;}
	.ms-search-head .colse{ font-size: 25upx; color: #f92028;}
	.ms-search-tags .text{ padding: 5upx 25upx; display: inline-block; background: #f0f2f5; border-radius: 8upx; font-size: 25upx; max-width: 200upx; margin:0 15upx 15upx 0;}
</style>
