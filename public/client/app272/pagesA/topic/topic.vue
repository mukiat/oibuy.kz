<template>
	<view class="activity">
	    <view class="list" v-for="(item,index) in topicList" :key='index' @click="detailClick(item.topic_id)" v-if="topicList">
	        <view class="p-r">
	            <span class="tag tag-gradients-color">{{$t('lang.topic')}}</span>
	            <image class="img" :src="item.topic_img" mode="widthFix" v-if="item.topic_img" ></image>
	        </view>
	        <view class="cont padding-all text-center bg-color-write">
	            <h4 class="f-06 f-weight color-3">{{item.title}}</h4>
	        </view>
	    </view>
	    <view v-else>
	    	<dsc-not-content></dsc-not-content>
	    </view>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import dscNotContent from '@/components/dsc-not-content.vue';

	export default {
		data() {
			return {
				topicList: []
			}
		},
		components: {
			dscNotContent,
		},
		computed:{
		},
		methods: {
			detailClick(id){
				uni.navigateTo({
					url:'/pagesA/topic/detail/detail?type=topic&id='+id,
				})
			}
		},
		onLoad() {
			let referer = uni.getStorageSync('platform').toLowerCase()
			if(uni.getStorageSync('platform') == 'MP-WEIXIN'){
				referer = 'wxapp'
			}
			uni.request({
				url:this.websiteUrl + '/api/topic',
				method:'GET',
				data:{
					page:1,
				    size:10,
					device: referer
				},
				header: {
					'Content-Type': 'application/json',
					'token': uni.getStorageSync('token'),
					'X-Client-Hash':uni.getStorageSync('client_hash')
				},
				success: (res) => {
					this.topicList = res.data.data
				}
			})
		},
		watch:{
		}
	}
</script>
