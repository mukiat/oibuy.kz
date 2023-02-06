<template>
	<view class="community-list">
		<block v-if="discoverList && discoverList.length > 0">
			<section class="com-nav" v-for="(item,index) in discoverList" :key="index">
				<block v-if="listMode == 'comlist'">
					<view @click="onDiscoverDetail(item.dis_type,item.dis_id)">
						<view class="com-min-tit">
							<uni-tag :text="$t('lang.tao')" size="small" type="error" v-if="item.dis_type == 1"></uni-tag>
							<uni-tag :text="$t('lang.wen')" size="small" type="error" v-else-if="item.dis_type == 2"></uni-tag>
							<uni-tag :text="$t('lang.quan')" size="small" type="error" v-else-if="item.dis_type == 3"></uni-tag>
							<uni-tag :text="$t('lang.shai')" size="small" type="error" v-else></uni-tag>
							<text>{{ item.dis_title }}</text>
						</view>
						<view class="dis-box com-header-img-cont">
							<view class="box-flex">
								<view class="com-header-img-box fl">
									<view class="img-commom"><img :src="item.user_picture" class="img-height"></view>
								</view>
								<view class="com-header-span-box fl"><span>{{ item.user_name }}</span></view>
							</view>
							<view class="t-time">
								<i class="iconfont icon-shijian"></i>
								<span>{{ item.add_time }}</span>
							</view>
						</view>
					</view>
				</block>
				<block v-else>
					<view class="com-hd" @click="onDiscoverDetail(item.dis_type,item.dis_id)">
						<view class="com-img"><image :src="item.user_picture" class="img"></image></view>
						<view class="com-info">
							<text class="tit">{{ item.user_name }}</text>
							<text class="txt">{{ item.add_time }}</text>
						</view>
					</view>
					<view class="com-bd" @click="onDiscoverDetail(item.dis_type,item.dis_id)">
						<view class="com-min-tit">
							<uni-tag :text="$t('lang.tao')" size="small" type="error" v-if="item.dis_type == 1"></uni-tag>
							<uni-tag :text="$t('lang.wen')" size="small" type="error" v-else-if="item.dis_type == 2"></uni-tag>
							<uni-tag :text="$t('lang.quan')" size="small" type="error" v-else-if="item.dis_type == 3"></uni-tag>
							<uni-tag :text="$t('lang.shai')" size="small" type="error" v-else></uni-tag>
							<view class="com-title">{{ item.dis_title }}</view>
						</view>
					</view>
					<view class="com-fd">
						<view class="com-icon" @click="onZan(item.dis_type,item.dis_id)">
							<icon class="iconfont icon-zan"></icon>
							<text class="text">{{item.like_num}}</text>
						</view>
						<view class="com-icon" @click="onDiscoverDetail(item.dis_type,item.dis_id)">
							<icon class="iconfont icon-daipingjia"></icon>
							<text class="text">{{item.community_num}}</text>
						</view>
						<view class="com-icon">
							<icon class="iconfont icon-liulan"></icon>
							<text class="text">{{item.dis_browse_num}}</text>
						</view>
					</view>
				</block>
			</section>
		</block>
		<block v-else>
			<dsc-not-content></dsc-not-content>
		</block>
	</view>
</template>

<script>
import { mapState } from 'vuex'	
import dscNotContent from '@/components/dsc-not-content.vue';
import uniTag from "@/components/uni-tag.vue";

export default{
	props:{
		discoverList:{
			type:Array,
			Default:[]
		},
		listMode:{
			type:String,
			Default:''
		}
	},
	data(){
		return{
			tabStatus:true
		}
	},
	components:{
		dscNotContent,
		uniTag
	},
	methods:{
		onDiscoverDetail(type,id){
			uni.navigateTo({
				url:'/pagesC/discover/detail/detail?dis_type='+ type + '&dis_id=' + id
			})
		},
		onZan(type,id){
			this.$store.dispatch('setDiscoverLike',{
				dis_type:type,
				dis_id:id
			}).then(res=>{
				uni.showToast({
					title:res.msg,
					icon:'none'
				})
				this.$emit('getLikeNum',{
					likeNum:res.like_num,
					dis_id:id
				})
			})
		},
		onDelete(type,id){
			this.$store.dispatch('setDiscoverDelete',{
				dis_type:type,
				dis_id:id
			}).then(({data})=>{
				Toast(data.msg)
				if(data.error == 0){
					this.$emit('getDelete',{
						dis_id:id
					})
				}
			})
		}
	},
	onReachBottom(){
		if(this.page * this.size == this.cateGoodsList.length){
			this.page ++
			this.getGoodsList()
		}
	}
}
</script>

<style>
.community-list{}
.com-nav{ margin-bottom: 15upx; background: #FFFFFF; }
.com-nav .com-hd{ display: flex; flex-direction: row; justify-content: flex-start; align-items: center; padding: 20upx; }
.com-nav .com-hd .com-img{ width: 80upx; height: 80upx; border-radius: 50%; overflow: hidden; }
.com-nav .com-hd .com-info{ display: flex; flex-direction: column; margin-left: 20upx;}
.com-nav .com-hd .com-info .tit{ color:#007AFF; font-size: 28upx;}
.com-nav .com-hd .com-info .txt{ color:#AAAAAA; font-size: 25upx;}
.com-nav .com-bd .com-min-tit{ display: flex; padding: 0 20upx 20upx; flex-direction: row; justify-content: flex-start; align-items:center}
.com-nav .com-bd .com-title{ font-weight: 700; color: #333333; margin-left: 10upx;}
.com-nav .com-fd{ display: flex; flex-direction: row; justify-content: center; align-items: center; border-top: 2upx solid #F6F6F9;}
.com-nav .com-fd .com-icon{ width: 33.3%; display: flex; flex-direction: row; justify-content: center; align-items: center;}
.com-nav .com-fd .com-icon .text{ color: #888; font-size: 25upx; margin-left: 10upx;}
</style>