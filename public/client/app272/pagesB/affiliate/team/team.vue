<template>
	<view class="container-tab-bar">
		<view class="section-list" v-if="type == 'team'">
			<block v-if="user_child_list && user_child_list.legnth > 0">
				<view class="list" v-for="(item,index) in user_child_list" :key="index">
					<view class="left">
						<view class="picture">
							<image :src="item.user_picture" mode="widthFix" v-if="item.user_picture"></image>
							<image :src="imagePath.userDefaultImg" mode="widthFix" v-else></image>
						</view>
						<view class="con">
							<view class="name">{{item.user_name}}</view>
							<view class="time">加入时间：{{item.reg_time}}</view>
						</view>
					</view>
				</view>
			</block>
			<block v-else>
				<dsc-not-content></dsc-not-content>
			</block>
		</view>
		<view class="section-list" v-else>
			<block v-if="affiliate_list && affiliate_list.legnth > 0">
				<view class="list" v-for="(item,index) in affiliate_list" :key="index">
					<view class="left">
						<view class="picture">
							<image :src="item.user_picture" mode="widthFix" v-if="item.user_picture"></image>
							<image :src="imagePath.userDefaultImg" mode="widthFix" v-else></image>
						</view>
						<view class="con">
							<view class="name">{{item.user_name}}</view>
							<view class="time">加入时间：{{item.reg_time}}</view>
						</view>
					</view>
					<view class="right">
						<view class="uni-red">+ {{ item.money }}</view>
					</view>
				</view>
			</block>
			<block v-else>
				<dsc-not-content></dsc-not-content>
			</block>
		</view>
	</view>
</template>

<script>
	import uniIcons from '@/components/uni-icons/uni-icons.vue';

	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';

	export default {
		data() {
			return {
				type:'',
				user_child_list:[],
				affiliate_list:[]
			}
		},
		components:{
			uniIcons,
			dscCommonNav,
			dscNotContent
		},
		methods: {
			userTeam(){
				uni.request({
					url:this.websiteUrl + '/api/user/child_list',
					method:'POST',
					data:{},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						this.user_child_list = res.data.data;
					}
				})
			},
			registerAward(){
				uni.request({
					url:this.websiteUrl + '/api/user/affiliate_list',
					method:'POST',
					data:{},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash':uni.getStorageSync('client_hash')
					},
					success: (res) => {
						this.affiliate_list = res.data.data;
					}
				})
			}
		},
		onLoad(e) {
			this.type = e.type;

			if(this.type == 'team'){
				this.userTeam();
			}else{
				this.registerAward();
			}
		}
	}
</script>

<style>

</style>
