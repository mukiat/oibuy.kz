<template>
	<view class="discover">
		<view class="my-admin-header-box">
			<view class="my-info">
				<view class="user-com-img-box"><image :src= "discoverMyInfo.avatar" class="img"></image></view>
				<view class="com-admin">{{ discoverMyInfo.user_name }}</view>
			</view>
			<view class="bg"><image :src="imagePath.comUser02" mode="widthFix"></image></view>
		</view>
		<view class="com-list">
			<view class="item" :class="{'active':dis_type == 1}" @click="onTabs(1)">
				<text>{{$t('lang.discuss_post')}}</text>
				<text>{{ discoverMyInfo.type1_num }}</text>
			</view>
			<view class="item" :class="{'active':dis_type == 2}" @click="onTabs(2)">
				<text>{{$t('lang.interlocution_post')}}</text>
				<text>{{ discoverMyInfo.type2_num }}</text>
			</view>
			<view class="item" :class="{'active':dis_type == 3}" @click="onTabs(3)">
				<text>{{$t('lang.circle_post')}}</text>
				<text>{{ discoverMyInfo.type3_num }}</text>
			</view>
			<view class="item" :class="{'active':dis_type == 4}" @click="onTabs(4)">
				<text>{{$t('lang.sunburn_post')}}</text>
				<text>{{ discoverMyInfo.type4_num }}</text>
			</view>
		</view>
		<discover-list :discoverList="discoverMyList" v-on:getLikeNum="handleLikeNum" v-on:getDelete="handleDelete"></discover-list>
		<discover-nav :mode="mode" :type="type"></discover-nav>
		<!-- <CommonNav></CommonNav> -->
	</view>
</template>

<script>
import { mapState } from 'vuex'
import discoverList from '@/components/dsc-discover/list';
import discoverNav from '@/components/dsc-discover/nav'; 

export default{
	data(){
		return {
			dis_type:1,
			page:1,
			size:10,
			mode:true,
			type:'my',
			loading:false,
			footerCont:false
		}
	},
	components:{
		discoverList,
		discoverNav
	},
	created(){
		this.$store.dispatch('setDiscoverMy',{
			dis_type:this.dis_type,
			page:this.page,
			size:this.size,
		})
		this.onlist()
	},
	computed:{
		 ...mapState({			 
            discoverMyInfo: state => state.discover.discoverMyInfo
			
        }),
		discoverMyList:{
			get(){
        		return this.$store.state.discover.discoverMyList
        	},
        	set(val){
        		this.$store.state.discover.discoverMyList = val
        	}
		}		
	},
	methods:{
		onlist(page){
			if(page){
				this.page = page
				this.size = Number(page) * 10
			}

			this.$store.dispatch('setDiscoverMyList',{
				dis_type:this.dis_type,
				page:this.page,
				size:this.size
			})
		},
		onTabs(val){
			this.dis_type = val 
			this.onlist(1)
		},
		handleLikeNum(obj){
			this.discoverMyList.forEach(v=>{
				if(v.dis_id == obj.dis_id){
					v.like_num = obj.likeNum
				}
			})
		},
		handleDelete(obj){
			this.discoverMyList.forEach((v,i)=>{
				if(v.dis_id == obj.dis_id){
					 this.discoverMyList.splice(i, 1)
				}
			})
		}
	},
	onReachBottom(){
		if(this.page * this.size == this.discoverMyList.length){
			this.page ++
			this.onlist()
		}
	},	
	watch:{
		discoverMyList(){
			if(this.page * this.size == this.discoverMyList.length){
				this.disabled = false
				this.loading = true
			}else{
				this.loading = false
				this.footerCont = this.page > 1 ? true : false
			}

			// this.discoverMyList = arrRemove.trimSpace(this.discoverMyList)
		}
	}
}
</script>

<style>
.my-admin-header-box { position: relative; height: 350upx;}
.my-admin-header-box .bg{ position: absolute; top: 0; left: 0; line-height: 0; z-index: 1; }
.my-admin-header-box .bg,.my-admin-header-box .bg image{ width: 100%; height: 350upx; overflow: hidden;}
.my-admin-header-box .my-info{ position: relative; z-index: 2; height: 350upx; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.my-admin-header-box .my-info .user-com-img-box{ width: 120upx; height: 120upx; border-radius: 100%; overflow: hidden;}
.my-admin-header-box .my-info .com-admin{ font-size: 35upx; color: #FFFFFF;}
.com-list{ display: flex; flex-direction: row; background: #FFFFFF;}
.com-list .item{ width: 25%; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #888;}
.com-list .item.active{ color: #f92028;}
</style>