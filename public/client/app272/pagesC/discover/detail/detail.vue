<template>
	<view class="discover fixed-bottom-padding">
		<discover-goods :discoverGoods="discoverDetail.link_good"></discover-goods>
		<view class="com-nav com-nav-detail">
			<view class="com-hd">
				<view class="com-img"><image :src="discoverDetail.user_picture" class="img"></image></view>
				<view class="com-info"><text class="tit">{{ discoverDetail.user_name }}</text></view>
				<view class="com-time">
					<icon class="iconfont icon-shijian"></icon>
					<text class="txt">{{ discoverDetail.add_time }}    </text>
				</view>
			</view>
			<view class="com-bd">
				<view class="com-min-tit">
					<uni-tag text="讨" size="small" type="error" v-if="discoverDetail.dis_type == 1"></uni-tag>
					<uni-tag text="问" size="small" type="error" v-else-if="discoverDetail.dis_type == 2"></uni-tag>
					<uni-tag text="圈" size="small" type="error" v-else-if="discoverDetail.dis_type == 3"></uni-tag>
					<uni-tag text="晒" size="small" type="error" v-else></uni-tag>
					<text class="com-title">{{ discoverDetail.dis_title }}</text>
				</view>
				<view class="article-con">
					<rich-text :nodes="discoverDetail.dis_text"></rich-text>
				</view>
			</view>
			<view class="com-fd">
				<view class="yuan" @click="onZan(discoverDetail.dis_type,discoverDetail.dis_id)">
					<icon class="iconfont icon-zan"></icon>
					<text class="txt">{{ discoverDetail.like_num }}</text>
				</view>
				<!-- #ifdef MP-WEIXIN -->
				<button class="btn yuan" open-type="share">
					<icon class="iconfont icon-fenxiang"></icon>
					<text class="txt">{{$t('lang.share')}}</text>
				</button>
				<!-- #endif -->
				<!-- #ifdef APP-PLUS -->
				<view class="yuan" @tap="appShare">
					<icon class="iconfont icon-fenxiang"></icon>
					<text class="txt">{{$t('lang.share')}}</text>
				</view>
				<!-- #endif -->
			</view>
		</view>
        <view class="comment-list" v-if="discoverDetail.user_comment && discoverDetail.user_comment.length > 0">
        	<view class="title">{{$t('lang.comment_list')}}({{discoverDetail.user_comment.length}})</view>
			<view class="list">
				<view class="item" v-for="(item,index) in discoverDetail.user_comment" :key="index">
					<view class="com-img-left">
						<image :src="item.user_picture" class="img"></image>
					</view>
					<view class="com-con-right">
						<view class="com-adm-box">
							<view class="tit">{{ item.user_name }}</view>
							<view class="not" @click="onQuote(item.dis_id,item.user_name)">
								<text>0</text>
								<icon class="iconfont icon-daipingjia"></icon>
							</view>
						</view>
						<view class="time">{{ item.add_time }}</view>
						<view class="com-con-m">{{ item.dis_text }}</view>
						<view class="pl-hf-box" v-for="(hfItem,hfindex) in item.next_com" :key="hfindex">
                            <view class="text">{{ hfItem.user_name}}:</view>{{ hfItem.dis_text }}
                        </view>
					</view>
				</view>
			</view>
		</view>
		<view class="btn-goods-action">
			<view class="submit-bar-text submit-bar-text-left submit-bar-text-img">
				<view class="com-img">
					<img :src="discoverDetail.avatar" class="img" v-if="discoverDetail.avatar">
					<img :src="imagePath.userDefaultImg" class="img" v-else>
				</view>
				<input name="comment" v-model="comment" :placeholder="placeholder" autocomplete="off" />
			</view>
			<view class="btn-bar">
				<view class="btn btn-red" @click="btnSubmit">{{$t('lang.send')}}</view>
			</view>
		</view>
		
		<dsc-common-nav></dsc-common-nav>
	</view>
</template>

<script>
import { mapState } from 'vuex'

import uniTag from "@/components/uni-tag.vue";
import discoverGoods from '@/components/dsc-discover/goods'; 
import dscCommonNav from '@/components/dsc-common-nav.vue';
import universal from '@/common/mixins/universal.js';

export default{
	mixins:[universal],
	data(){
		return {
			comment:'',
			parent_id:0,
			dis_type:1,
			shareActive:false,
			placeholder:this.$t('lang.label_reply_post'),
			quote_id:0,
			shareState: true,
			mask:true
		}
	},
	components:{ 
		discoverGoods,
		dscCommonNav,
		uniTag
	},
	onShareAppMessage(res){
		if (res.from === 'button') {// 来自页面内分享按钮
			return {
			  title: this.discoverDetail.dis_title,
			  path: "/pagesC/discover/detail/detail?dis_type=" + this.dis_type + '&dis_id=' + this.dis_id
			}
		}else{
			return {
			  title: this.discoverDetail.dis_title,
			  path: "/pagesC/discover/detail/detail?dis_type=" + this.dis_type + '&dis_id=' + this.dis_id
			}
		}
	},
	onLoad(e){
		this.dis_type = e.dis_type
		this.dis_id = e.dis_id
		
		this.load()
	},
	computed:{
		...mapState({
			discoverDetail: state => state.discover.discoverDetail,
		}),
		like_num:{
			get(){
				return this.$store.state.discover.discoverDetail.like_num
			},
			set(val){
				this.$store.state.discover.discoverDetail.like_num = val
			}
		},
		isLogin(){
			return this.$isLogin()
		}
	},
	methods:{
		load(){
			this.$store.dispatch('setDiscoverDetail',{
				dis_type:this.dis_type,
				dis_id:this.dis_id
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
				this.like_num = res.like_num	
			})
		},
		btnSubmit(){
			if(this.$isLogin()){
				this.$store.dispatch('setDiscoverComment',{
					parent_id:this.parent_id,
					quote_id:this.quote_id,
					dis_text:this.comment,
					reply_type:0,
					dis_type:this.dis_type,
					goods_id:0,
				}).then(res=>{
					uni.showToast({
						title:res.msg,
						icon:'none'
					})
					if(res.error == 0){
						this.load()
						this.comment = ''
					}
				})	
			}else{
				uni.showModal({
					content: this.$t('lang.login_user_not'),
					success:(res)=>{
						if(res.confirm){
							uni.navigateTo({
								url:'/pagesB/login/login?delta=1'
							})
						}
					}
				})
			}
		},
		onQuote(id,name){
			this.quote_id = id
			this.placeholder = '回复' + name +':'
		},
		//app分享
		appShare(){
			let shareInfo = {
				href:this.$websiteUrl + 'discoverDetail?dis_type=' + this.dis_type + '&dis_id=' + this.dis_id + '&platform=APP',
				title:this.discoverDetail.dis_title,
				summary:this.discoverDetail.link_good.goods_name ? this.discoverDetail.link_good.goods_name : this.discoverDetail.link_good.goods_name,
				imageUrl:this.discoverDetail.link_good.goods_thumb
			};
			this.shareInfo(shareInfo)
		},
	}
}
</script>

<style scoped>
.com-nav{ margin-bottom: 15upx; background: #FFFFFF; }
.com-nav .com-hd{ display: flex; flex-direction: row; justify-content: flex-start; align-items: center; padding: 20upx; }
.com-nav .com-hd .com-img{ width: 80upx; height: 80upx; border-radius: 50%; overflow: hidden; }
.com-nav .com-hd .com-info{ flex: 1; display: flex; flex-direction: column; margin-left: 20upx;}
.com-nav .com-hd .com-info .tit{ color:#007AFF; font-size: 28upx;}
.com-nav .com-hd .com-time{ display: flex; flex-direction: row; font-size: 25upx; color: #999;}
.com-nav .com-hd .com-time .iconfont{ font-size: 25upx; margin-right: 10upx;}

.com-nav .com-bd .com-min-tit{ display: flex; padding: 0 20upx 20upx; flex-direction: row; justify-content: flex-start; align-items:center}
.com-nav .com-bd .com-title{ font-weight: 700; color: #333333; margin-left: 10upx;}
.com-nav .com-bd .article-con{ padding: 20upx;}

.com-nav .com-fd{ display: flex; flex-direction: row; justify-content: center; padding: 20upx 0;}
.com-nav .com-fd .yuan{ width: 120upx; height: 120upx; border-radius: 100%; border: 2upx solid #F6F6F9; display: flex; flex-direction: column; justify-content: center; align-items: center; margin: 0 10upx; font-size: 25upx; background: #FFFFFF;}
.com-nav .com-fd .yuan .iconfont{ height: 50upx; line-height: 50upx; color: #888;}
.com-nav .com-fd .yuan .txt{ color: #888;}

.comment-list{ background: #FFFFFF;}
.comment-list .title{ padding: 20upx; font-size: 32upx; color: #333; border-bottom: 2upx solid #F6F6F9;}
.comment-list .list{}
.comment-list .list .item{ display: flex; flex-direction: row; justify-content: flex-start; padding: 20upx;}
.comment-list .list .item .com-img-left{ width: 60upx; height: 60upx; border-radius: 50%; border:2upx solid #eee; overflow: hidden; margin-top: 20upx;}
.comment-list .list .item .com-con-right{ flex: 1; margin-left: 20upx;}
.com-con-right .com-adm-box { display: flex; flex-direction: row; justify-content: space-between; align-items: center;}
.com-con-right .com-adm-box .tit{color:#007AFF; font-size: 28upx;}
.com-con-right .com-adm-box .not{ display: flex; flex-direction: row; justify-content: flex-start; align-items: center; font-size: 25upx;}
.com-con-right .com-adm-box .not text{ padding-right:10upx;}
.com-con-right .time{ font-size: 25upx; color: #999;}
.com-con-right .com-con-m{ margin-top: 10upx; color: #333;}
.com-con-right .pl-hf-box{ background: #f7f8fc; border-radius: 20upx; padding: 20upx; margin: 10upx 0 0 0; display: flex; flex-direction: row; color: #333; justify-content: flex-start; align-items: center; position: relative;}
.com-con-right .pl-hf-box .text{ color: #b4b4b4;}
.com-con-right .pl-hf-box:after{ content: " "; position: absolute; background: #f7f8fc;transform: rotate(45deg); width: 30upx; height: 30upx; top: -8upx; left: 30upx;}
</style>