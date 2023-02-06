<template>
	<view class="container">
		<view class="tab-bar">
			<view v-for="(tab,index) in drpTeamHeader" :key="index" :class="['tab-item',active == index ? 'active' : '']" @click="commonTabs(index)">
				<text>{{ tab }}</text>
			</view>
		</view>
		<view class="section-list" v-if="active == 0">
			<block v-if="drpTeamData && drpTeamData.length > 0">
				<scroll-view class="scrollList" scroll-y :lower-threshold="100" @scrolltolower="loadMore" :style="{height:winHeight + 'px'}">
				<view class="tit" v-if="!isLoading">
					<view class="t1">{{pageDrpTeam.user_id ? pageDrpTeam.user_id : $t('lang.user')}}</view>
					<view class="t2">{{pageDrpTeam.contribution_amount ? pageDrpTeam.contribution_amount : $t('lang.contribution_amount')}}</view>
				</view>
				<view class="list" v-for="(item,index) in drpTeamData" :key="index" @click="$outerHref('/pagesA/drp/teamDetail/teamDetail?user_id='+item.user_id,'app')">
					<view class="left">
						<view class="picture">
							<image :src="item.user_picture" mode="widthFix" v-if="item.user_picture"></image>
							<image :src="imagePath.userDefaultImg" mode="widthFix" v-else></image>
						</view>
						<view class="con">
							<view class="name">{{item.user_name}}</view>
							<view class="time">{{$t('lang.label_addtime')}}{{item.reg_time}}</view>
						</view>
					</view>
					<view class="right">
						<view class="price">{{item.money}}</view>
						<uni-icons type="forward" size="20" color="#999999"></uni-icons>
					</view>
				</view>
				</scroll-view>
			</block>
			<block v-else>
				<dsc-not-content></dsc-not-content>
			</block>
		</view>
		<view class="section-list" v-else>
			<block v-if="drpOffkineUserData && drpOffkineUserData.length > 0">
				<scroll-view class="scrollList" scroll-y :lower-threshold="100" @scrolltolower="loadMore2" :style="{height:winHeight2 + 'px'}">
				<view class="list" v-for="(item,index) in drpOffkineUserData" :key="index">
					<view class="left">
						<view class="picture">
							<image :src="item.user_picture" mode="widthFix" v-if="item.user_picture"></image>
							<image :src="imagePath.userDefaultImg" mode="widthFix" v-else></image>
						</view>
						<view class="con">
							<view class="name">{{item.user_name}}</view>
							<view class="time">{{$t('lang.label_addtime')}}{{item.reg_time}}</view>
						</view>
					</view>
				</view>
				</scroll-view>
			</block>
			<block v-else>
				<dsc-not-content></dsc-not-content>
			</block>
		</view>
		
		<dsc-common-nav>
			<navigator url="../drp" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.drp_center')}}</text>
			</navigator>
		</dsc-common-nav>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	
	export default {
		data() {
			return {
				tabs: [this.$t('lang.offline_distributors'), this.$t('lang.direct_referrals')],
				drpTeamHeader: [],
				pageDrpTeam: {},
				active:0,
				size:10,
				page:1,
				user_id:0,
				isLoading: false
			}
		},
		components:{
			uniIcons,
			dscCommonNav,
			dscNotContent
		},
		onLoad(e) {
			this.user_id = e.parent_id ? e.parent_id : 0;
		},
		async onShow(){
			await this.getCustomTextByCode();
			this.myDrpTeam()
		},
		computed: {
			...mapState({
				drpTeamData: state => state.drp.drpTeamData,
				drpOffkineUserData: state => state.drp.drpOffkineUserData,
			}),
			winHeight(){
				return uni.getSystemInfoSync().windowHeight - 50
			},
			winHeight2(){
				return uni.getSystemInfoSync().windowHeight - 100
			}
		},
		methods: {
			myDrpTeam(page) {
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}
				
				this.$store.dispatch('setDrpTeam',{
					user_id:this.user_id,
					page: this.page,
					size: this.size,
				})
			},
			//下级会员
			drpOffline(page) {
				if(page){
					this.page = page
					this.size = Number(page) * 10
				}
				
				this.$store.dispatch('setDrpOfflineUser',{
					user_id:this.user_id,
					page: this.page,
					size: this.size,
				})
			},
			commonTabs(index) {
				this.active = index
				
				if(index == 0){
					this.myDrpTeam(1)
				}else{
					this.drpOffline(1)
				}
			},
			// 分销管理-自定义设置数据
			async getCustomTextByCode() {
				this.isLoading = true;
				const {data: { page_drp_team }, status} = await this.$store.dispatch('getCustomText',{code: 'page_drp_team'});
			    if (status == 'success') {
			        this.pageDrpTeam = page_drp_team || {};
					let isSetHeader = ['child_drp', 'child_user'];
					this.drpTeamHeader = isSetHeader.map((item, index) => this.pageDrpTeam[item] || this.tabs[index]);
					this.isLoading = false;
			    }
			},
			loadMore(){
				if(this.page * this.size == this.drpTeamData.length){
					this.page ++
					this.myDrpTeam()
				}
			},
			loadMore2(){
				if(this.page * this.size == this.drpOffkineUserData.length){
					this.page ++
					this.drpOffline()
				}
			}
		}
	}
</script>

<style scoped>
.section-list{ margin-top: 50px; margin-bottom: 0;}
.section-list .tit{ display: flex; flex-direction: row; padding: 20upx 40upx; background: #FFFFFF;}
.section-list .tit .t1{ width: 80%; }
.section-list .tit .t2{ width: 20%; }
.section-list .list{ padding: 20upx 40upx; display: flex; flex-direction: row; background-color: #FFFFFF;}
.section-list .list .left{ display: flex; flex-direction: row; width: 80%;}
.section-list .list .left .picture{ width: 70upx; height: 70upx; margin-right: 20upx;}
.section-list .list .left .picture image{ width: 100%; height: auto; }
.section-list .list .left .con{ display: flex; flex-direction: column;}
.section-list .list .left .con .name{ font-size: 28upx; }
.section-list .list .left .con .time{ font-size: 25upx; color: #999999;}

.section-list .list .right{ flex: 1; display: flex; flex-direction: row; justify-content: center; align-items: center;}
.section-list .list .right .price{ color: #f92028; font-size: 30upx; height: 40upx; line-height:35upx; }
</style>
