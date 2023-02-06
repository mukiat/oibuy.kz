<template>
	<view class="discover fixed-bottom-padding">
		<view class="community">
			<view class="item" @click="onTabs(1)">
				<view class="theme-left tm-ns"><icon class="iconfont icon-xiao36"></icon></view>
				<view class="theme-right">
					<text class="tit">{{$t('lang.discuss_post')}}</text>
					<text class="span" v-if="communityData.tao">{{ communityData.tao.num }}{{$t('lang.tiao')}}</text>
				</view>
			</view>
			<view class="item" @click="onTabs(2)">
				<view class="theme-left tm-zs"><icon class="iconfont icon-wenda"></icon></view>
				<view class="theme-right">
					<text class="tit">{{$t('lang.interlocution_post')}}</text>
					<text class="span" v-if="communityData.wen">{{ communityData.wen.num }}{{$t('lang.tiao')}}</text>
				</view>
			</view>
			<view class="item" @click="onTabs(3)">
				<view class="theme-left tm-ls"><icon class="iconfont icon-quanzi"></icon></view>
				<view class="theme-right">
					<text class="tit">{{$t('lang.circle_post')}}</text>
					<text class="span" v-if="communityData.quan">{{ communityData.quan.num }}{{$t('lang.tiao')}}</text>
				</view>
			</view>
			<view class="item" @click="onTabs(4)" v-if="communityData.shop_can_comment > 0">
				<view class="theme-left tm-hs"><icon class="iconfont icon-paizhao"></icon></view>
				<view class="theme-right">
					<text class="tit">{{$t('lang.sunburn_post')}}</text>
					<text class="span" v-if="communityData.sun">{{ communityData.sun.num }}{{$t('lang.tiao')}}</text>
				</view>
			</view>
		</view>
		<discover-list :discoverList="discoverList" v-on:getLikeNum="handleLikeNum" v-on:getDelete="handleDelete" v-if="discoverList"></discover-list>
		<discover-nav :mode="mode" :type="type"></discover-nav>
	</view>
</template>

<script>
	import { mapState } from 'vuex'	
	import mpvuePicker from '@/components/mpvue-picker/mpvuePicker.vue';
	import uniPopup from '@/components/uni-popup.vue';
	import discoverList from '@/components/dsc-discover/list';
	import discoverNav from '@/components/dsc-discover/nav'; 

	export default{ 
		components:{
			mpvuePicker,
			uniPopup,
			discoverList,
			discoverNav
		},
		data(){
			return{
				communityData:{
					tao:{},
					wen:{},
					quan:{},
					sun:{}
				},
				mode:true,
				dis_type:'all',
				page:1,
				size:10,
				type:'index',
				communityType:true,
			}
		},
		
		created(){	
			this.$store.dispatch('setDiscoverIndex').then(res=>{
				this.communityData = res.data
			})
	
			this.onlist()
		},
		computed:{
			discoverList:{
				get(){
					return this.$store.state.discover.discoverList
				},
				set(val){
					this.$store.state.discover.discoverList = val
				}
			}
		},
		methods:{
			onlist(page){
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}

				this.$store.dispatch('setDiscoverList',{
					dis_type:this.dis_type,
					page:this.page,
					size:this.size
				})
			},
			onTabs(val){				
				uni.navigateTo({
					url:'/pagesC/discover/listType/listType?type='+val
				})
			},
			handleLikeNum(obj){
				this.discoverList.forEach(v=>{
					if(v.dis_id == obj.dis_id){
						v.like_num = obj.likeNum
					}
				})
			},
			handleDelete(obj){
				this.discoverList.forEach((v,i)=>{
					if(v.dis_id == obj.dis_id){
						 this.discoverList.splice(i, 1)
					}
				})
			}
		},
		onReachBottom(){
			if(this.page * this.size == this.discoverList.length){
				this.page ++
				this.onlist()
			}
		}
	}
</script>

<style>
.community { background: #FFFFFF; display: flex; flex-direction: row; flex-wrap: wrap; margin-bottom: 15upx;}
.community .item{ width: 50%; padding: 20upx; display: flex; flex-direction: row; box-sizing: border-box; border-right: 2upx solid #f6f6f9; border-bottom: 2upx solid #f6f6f9;}
.community .item .theme-left{ width: 120upx; height: 120upx; display: flex; justify-content: center; align-items: center; border-radius: 10upx; margin-right: 20upx;}
.community .item .theme-left .iconfont{ font-size: 80upx; color: #FFFFFF; }
.community .item .theme-right{ display: flex; flex-direction: column; justify-content: center; }
.community .item .theme-right .tit{ font-size: 32upx; }
.community .item .theme-right .span{ font-size: 25upx; color: #999999;}

.tm-ns{ background: #fc295b; }
.tm-zs{ background: #f85bd1; }
.tm-ls{ background: #25d081; }
.tm-hs{ background: #fc6e29; }
</style>