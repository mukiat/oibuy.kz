<template>
	<view class="container-tab-bar">
		<view class="tab-bar">
			<view v-for="(tab,index) in tabBars" :key="index" :class="['tab-item',active == index ? 'active' : '']" @click="commonTabs(index)">
				<text>{{ tab }}</text>
			</view>
		</view>
		<view class="section-list">
			<block v-if="drpLogData !='' ">
				<view class="list" v-for="(item,index) in drpLogData" :key="index">
					<view class="hd">
						<view class="left">
							<view class="price"><text>{{item.money_format}}</text></view>
							<view class="log-tag" :class="{'active':item.drp_is_separate == 1}">{{item.is_separate_format}}</view>
						</view>
						<view class="right">{{$t('lang.commission_into')}}</view>
					</view>
					<view class="fd">
						<view>{{$t('lang.order_time_alt')}}：{{item.time_format}}</view>

                        <view v-if="item.log_type == 0 || item.log_type == 2">
                            <p>{{$t('lang.order_sn_into')}}：{{item.order_sn}}</p>
                        </view>
                        <view v-else-if="item.log_type == 1">
                            <p>{{$t('lang.label_buyer')}}{{item.buy_user_name}}</p>
                        </view>

					</view>
				</view>
			</block>
			<block v-else>
				<dsc-not-content></dsc-not-content>
			</block>
		</view>
		<dsc-common-nav>
			<navigator url="../drp" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.my_drp')}}</text>
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
				tabBars:[this.$t('lang.all'),this.$t('lang.has_been_divide'),this.$t('lang.not_into')],
				status:2,
				active:0,
				size:10,
				page:1,
			}
		},
		components:{
			uniIcons,
			dscCommonNav,
			dscNotContent
		},
		onLoad() {
			this.logList()
		},
		computed: {
			drpLogData:{
				get(){
					return this.$store.state.drp.drpLogData
				},
				set(val){
					this.$store.state.drp.drpLogData = val
				}
			}
		},
		methods: {
			logList() {
				this.$store.dispatch('setDrpLog',{
					page: this.page,
					size: this.size,
					status: this.status
				})
			},
			commonTabs(index) {
				this.active = index

				if(index == 0){
					this.status = 2
				}else if(index == 2){
					this.status = 0
				}else{
					this.status = 1
				}
				this.logList();
			}
		}
	}
</script>

<style scoped>
.section-list .list{ padding: 30upx 36upx; background-color: #FFFFFF; border-bottom: 2upx solid #e3e8ee; display: flex; flex-direction: column;}
.section-list .list .hd{ display: flex; flex-direction: row; justify-content: space-between; align-items: center;}
.section-list .list .hd .left{ display: flex; flex-direction: row; justify-content: flex-start; align-items: center; }
.section-list .list .hd .left .price{ color: #F2031F; margin-right: 20upx; font-size: 40upx;}
.section-list .list .hd .left .price icon{ font-size: 25upx; }
.section-list .list .hd .left .log-tag{ display: flex; justify-content: center; align-items: center; border-radius: 50upx; padding: 0 20upx; border: 2upx solid #B78D5A; color: #B78D5A; font-size: 25upx;}
.section-list .list .hd .left .log-tag.active{ background: linear-gradient(118deg, #ecd8be, #dbb280); }
.section-list .list .hd .right{ font-size: 28upx; color: #666;}
.section-list .list .fd view{ font-size: 25upx; color: #999999;}
</style>
