<template>
	<view class="discover-list">
		<discover-goods :discoverGoods="discoverShow"></discover-goods>
		<discover-list :discoverList="commentList" :listMode="comlist" v-if="!mode"></discover-list>
		<view class="community-box" v-else>
			<view class="selects">
				<view class="select" v-for="(item,index) in tabs" :key="index" @click="onTab(item.text,item.id)" :class="{'active':active == item.id}">{{ item.text }}</view>
			</view>
			<view class="community-box-con">
                <view class="com-input-title">
	                <view class="input-text"><input type="text" v-model="title" :placeholder="$t('lang.fill_in_title')" autocomplete="off" name="title"></view>
	            </view>
                <view class="com-textarea">
					<textarea name="content" v-model="content" class="text-area1" maxlength="300" :placeholder="$t('lang.leave_some_text')"></textarea>
					<text>{{ length }}</text>
                </view>
            </view>
            <view class="btn-bar">
                <view class="btn btn-white" @click="cancel">{{$t('lang.cancel')}}</view>
                <view class="btn btn-red" @click="btnSubmit">{{$t('lang.confirm')}}</view>
            </view>
        </view>
		<discover-nav :mode="mode" v-on:getState="handleState"></discover-nav>
	</view>
</template>

<script>
import { mapState } from 'vuex'
import discoverGoods from '@/components/dsc-discover/goods'; 
import discoverList from '@/components/dsc-discover/list';
import discoverNav from '@/components/dsc-discover/nav'; 

export default{
	data(){
		return {
			goods_id:797,
			prent_id:0,
			dis_type:'all',
			page:1,
			size:10,
			mode:false,
			tabs:[
				{
					id:1,
					text:this.$t('lang.discuss_post'),
				},
				{
					id:2,
					text:this.$t('lang.interlocution_post'),
				},
				{
					id:3,
					text:this.$t('lang.circle_post'),
				},
			],
			title:'',
			content:'',
			active:1,
			commentList:[],
			comlist:'comlist'
		}
	},
	components:{
		discoverGoods,
		discoverNav,
		discoverList
	},
	onLoad(e){
		this.goods_id = e.id
		this.$store.dispatch('setDiscoverShow',{
			goods_id:this.goods_id,
		})

		this.$store.dispatch('setDiscoverCommentList',{
			goods_id:this.goods_id,
			dis_type:this.dis_type,
			page:this.page,
			size:this.size,
			id:this.prent_id
		}).then(res=>{
			this.commentList = res.data
		})
	},
	computed:{
		 ...mapState({
            discoverShow: state => state.discover.discoverShow,
        }),
		length(){
			let length = 300

			length = length - this.content.length

			return length
		}
	},
	methods:{
		onTab(text,val){
			this.active = val
		},
		handleState(val){
			this.mode = val
		},
		cancel(){
			this.mode = false
		},
		btnSubmit(){
			this.$store.dispatch('setDiscoverCreate',{
				goods_id:this.goods_id,
				dis_type:this.active,
				title:this.title,
				content:this.content
			}).then(res=>{
				uni.showToast({
					title:res.msg,
					icon:'none'
				});
				
				uni.navigateTo({
					url:'/pagesC/discover/index'
				})
			})
		}
	}
}
</script>
<style>
.community-box{}
.community-box .selects{ background: #FFFFFF; padding: 20upx; display: flex; flex-direction: row; }
.community-box .community-box-con{ background:#FFFFFF; }
.community-box .community-box-con .com-input-title{ padding: 20upx; border-bottom: 2upx solid #F6F6F9;}
.community-box .community-box-con .com-textarea{ padding: 20upx; }
.community-box .community-box-con .com-textarea .text-area1{ width: 100%;}
.community-box .community-box-con .com-textarea text{ text-align: right; display: block; color: #999; }
.btn-bar .btn{ margin: 30upx 20upx;}
</style>