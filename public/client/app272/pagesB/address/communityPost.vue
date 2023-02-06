<template>
	<view class="container-box" :style="{height: ch + 'px'}">
		<block v-if="postList.length > 0">
			<view class="map_container">
				<map class="map" id="map" :longitude="longitude" :latitude="latitude" :markers="covers" scale="15" enable-3D show-scale></map>
			</view>
			<view class="list-box">
				<scroll-view class="scroll_view" scroll-y="true" @scrolltolower="loadmore">
					<view class="list_item" v-for="(item, index) in postList" :key="item.phone" @click="changePost(index)">
						<icon type="success" color="#f92028" size="20" v-if="index == currentIndex" />
						<span class="item_checked_icon" v-else></span>
						<view class="item-main">
							<view>
								<text>{{item.pick_up_point}}</text>
								<text>{{item.distance}}km</text>
							</view>
							<view>{{$t('lang.post_address')}}: {{item.address}}</view>
							<view>{{$t('lang.label_tel')}} {{item.phone}}</view>
						</view>
					</view>
					<view class="loading_text" v-if="postList.length > 3 && loading">
						{{isOver ? $t('lang.no_more') : $t('lang.loading')}}{{isOver ? '' : '...'}}
					</view>
				</scroll-view>
				<view class="item-btn-submit" @click="usePost">{{$t('lang.post_use')}}</view>
			</view>
		</block>
		<block v-else>
			<dsc-not-content></dsc-not-content>
		</block>
	</view>
</template>

<script>
	import dscNotContent from '@/components/dsc-not-content.vue';
	
	export default {
		data() {
			return {
				latitude: '',
				longitude: '',
				leader_id:0,
				postList: [],
				currentIndex: 0,
				loading: false,
				isOver: false,
				page: 1,
				size: 10,
				ch: 0,
				covers: []
			}
		},
		components:{
			dscNotContent
		},
		onLoad() {
			const {
				windowHeight
			} = uni.getSystemInfoSync();
			this.ch = windowHeight;
			const {
				lng,
				lat
			} = JSON.parse(uni.getStorageSync('addressLngLat'));

			this.latitude = lat;
			this.longitude = lng;
			const timestamp = new Date().getTime();
			const label = {
				content: this.$t('lang.post_shipping_address'),
				bgColor: '#ffffff',
				borderWidth: 1,
				borderColor: '#f00000'
			}
			const obj = {
				id: timestamp,
				latitude: lat,
				longitude: lng,
				width: 20,
				height: 20,
				iconPath: '../../static/location.png',
				label
			};
			this.covers.push(obj);
			
			this.getPostListData(1);
		},
		methods: {
			async getPostListData(page) {
				this.loading = true;
				
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}
				
				const {
					data: {
						post_list
					},
					status
				} = await this.$store.dispatch('getPostList', {
					page: this.page,
					size: this.size
				});
				
				if (status != 'success') {
					return uni.showToast({
						title: this.$t('lang.post_msg_fail'),
						icon: 'none'
					})
				};
				
				this.isOver = post_list.length > 0 ? false : true;
				
				//判断是否length > 0
				if(post_list.length > 0){
					this.postList = [...this.postList, ...post_list];
				
					this.$nextTick(function() {
						if(this.page == 1) this.openInfoCart(this.postList[0]);
					})
				}
			},
			openInfoCart(currentInfo) {
				const timestamp = new Date().getTime();
				const {pick_up_point, address, phone, distance, lng, lat} = currentInfo;
				this.latitude = lat;
				this.longitude = lng;
				const contentStr = `${pick_up_point}\n${this.$t('lang.post_address')}: ${address}\n${this.$t('lang.label_tel')} ${phone}\n${this.$t('lang.post_distance_address')}: ${distance}km`;
				const callout = {
					content: contentStr,
					fontSize: 16,
					color: '#ffffff',
					bgColor: '#3366ff',
					padding: 10,
					borderRadius: 6,
					display: 'ALWAYS'
				};
				const obj = {
					id: timestamp,
					latitude: lat,
					longitude: lng,
					width: 20,
					height: 20,
					iconPath: '../../static/location.png',
					callout
				};
				this.covers[1] = obj;
			},
			changePost(i) {
				if (i == this.currentIndex) return;
				if (!this.isShowCart) this.isShowCart = true;
				//当前选中index
				this.currentIndex = i;
				//当前选中的社区信息
				this.openInfoCart(this.postList[i])
			},
			async usePost() {
				uni.showLoading({ title: this.$t('lang.loading'),mask: true });
				
				//当前选中的社区leader_id
				this.leader_id = this.postList[this.currentIndex].leader_id;
				
				//存储选择驿站leader_id
				uni.setStorageSync('leader_id',this.leader_id);
				
				//请求接口
				await this.$store.dispatch('setChangeConsignee',{
					leader_id:this.leader_id
				});
				
				//返回checkout
				uni.hideLoading();
				uni.navigateBack({
					delta:2
				})
			},
			// 下拉加载更多
			loadmore() {
				if (this.isOver) return;
				this.page ++;
				this.getPostListData();
			}
		}
	}
</script>

<style>
	.container-box {
		width: 100%;
	}

	.map_container {
		width: 100%;
		height: 50%;
	}

	.map {
		width: 100%;
		height: 100%;
	}

	.list-box {
		box-sizing: border-box;
		height: 50%;
		padding-top: 20upx;
		background-color: #fff;
	}
	
	.scroll_view {
		height: calc(100% - 100upx);
	}
	
	.item-btn-submit {
		font-size: 30upx;
		line-height: 100upx;
		text-align: center;
		color: #fff;
		background: linear-gradient(178deg,#f91f28,#ff4f2e);
	}

	.list_item {
		display: flex;
		padding: 0 20upx 20upx;
	}

	.item-main {
		flex: 1;
		margin-left: 20upx;
		color: #A0A0A0;
	}

	.item-main view:nth-child(1) {
		display: flex;
		justify-content: space-between;
		font-size: 30upx;
		line-height: 20px;
		font-weight: 700;
		margin-bottom: 12upx;
		color: #333;
	}

	.item_checked_icon {
		box-sizing: border-box;
		display: inline-block;
		width: 20px;
		height: 20px;
		border: 1px solid #ccc;
		border-radius: 50%;
	}
	
	.loading_text {
		text-align: center;
		padding: 0 0 20upx;
	}
	
	/* 社区驿站页 地图窗体覆盖物样式 */
	.info_cart {
	  position: absolute;
	  top: 50%;
	  left: 50%;
	  transform: translate(-50%, -100%);
	  font-size: 28upx;
	}
	.info_top {
	  display: flex;
	  flex-direction: column;
	  padding: 20upx 20upx 10upx;
	  color: #F8F8F8;
	  line-height: 1;
	  border-top-left-radius: 10upx;
	  border-top-right-radius: 10upx;
	  box-sizing: border-box;
	  background-color: #3366ff;
	}
	.info_top .top_title {
	  font-size: 32upx;
	  font-weight: 700;
	  line-height: 1.5;
	  color: #fff;
	}
	.top_address,
	.top_phone{
	  padding: 8upx 0;
	}
	.info_bottom {
	  display: flex;
	  justify-content: space-between;
	  background-color: #fff;
	  padding: 20upx;
	  color: #A0A0A0;
	  font-size: 30upx;
	  border-bottom-left-radius: 10upx;
	  border-bottom-right-radius: 10upx;
	}
	.daosanjiao_box {
		height: 50upx;
	}
	.daosanjiao {
	  width: 30upx;
	  height: 30upx;
	  transform: rotate(45deg);
	  margin: 0 auto;
	  margin-top: -20upx;
	  background-color: #fff;
	}
	/* 社区驿站页 地图窗体覆盖物样式结束 */
</style>
