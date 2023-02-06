<template>
	<scroll-view class="list" scroll-y @scrolltolower="loadMore">
		<view class="activity">
			<view class="item" v-for="(item,index) in activityIndexData" :key="index" @click="$outerHref('/pages/activity/detail/detail?act_id='+item.act_id,'app')">
				<view class="pic">
					<view class="activity-tag">
						<text class="text tag-gradients-color">{{item.act_type}}</text>
						<view class="sanjiao"></view>
					</view>
					<image :src="item.activity_thumb" mode="widthFix" class="img"></image>
				</view>
				<view class="cont">
					<view class="tit">{{item.act_name}}</view>
					<view class="time">{{item.start_time}}{{$t('lang.zhi')}}{{item.end_time}}</view>
					<view class="name">{{item.activity_name}}</view>
				</view>
			</view>
		</view>
	</scroll-view>
</template>

<script>
	import { mapState } from 'vuex'
	export default {
		data() {
			return {
				page:1,
				size:10
			}
		},
		created(){
			this.load();
		},
		computed: {
			...mapState({
				activityIndexData: state => state.ump.activityIndexData
			})
		},
		methods: {
			load(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}
				
				this.$store.dispatch('setActivityIndex',{
					page:this.page,
					size:this.size
				});
			},
			loadMore(){
				if(this.page * this.size == this.activityIndexData.length){
					this.page ++
					this.load()
				}
			}
		}
	}
</script>

<style>
.activity .item{ margin: 20upx; border-radius: 10upx; overflow: hidden; background: #FFFFFF;}
.activity .item .pic{ position: relative; }
.activity .item .pic .activity-tag{ position: absolute; top: 0; left: 0; }
.activity .item .pic .activity-tag .text{ padding: 5upx 25upx; border-radius: 0 8upx 8upx 0; color: #FFFFFF; display: block; font-size: 25upx;}
.activity .item .pic .activity-tag .sanjiao{ height: 0; width: 0;border-right: 6upx solid transparent; border-bottom: 6upx solid transparent;border-left: 8upx solid #F92929;}

.activity .item .cont{ display: flex; justify-content: center; align-items: center; flex-direction: column; padding: 20upx;}
.activity .item .cont .tit{ font-size: 30upx; color: #333333;}
.activity .item .cont .time{ color: #999999; }
.activity .item .cont .name{ color: #f92028;}
</style>
