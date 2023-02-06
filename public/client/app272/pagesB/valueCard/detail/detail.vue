<template>
	<view>
		<block v-if="valueCardInfo.length > 0">
		<view class="ny-list">
			<view class="ny-item" v-for="(item,index) in valueCardInfo" :key="index">
				<view class="header">
					<view>{{$t('lang.order_sn')}}：{{ item.order_sn }}</view>
					<view>{{$t('lang.use_time')}}：{{ item.record_time }}</view>
				</view>
				<view class="content">
					<view class="item"><label>{{$t('lang.recharge')}}：</label><text class="uni-red">{{ item.add_val }}</text></view>
					<view class="item"><label>{{$t('lang.use')}}：</label><text class="uni-red">{{ item.use_val }}</text></view>
				</view>
			</view>
		</view>
		</block>
		<block v-else>
			<dsc-not-content></dsc-not-content>
		</block>
	</view>
</template>

<script>
	import dscNotContent from '@/components/dsc-not-content.vue';
	export default {
		data() {
			return {
				vc_id:''
			};
		},
		components:{
			dscNotContent
		},
		computed: {
			valueCardInfo:{
				get(){
					return this.$store.state.user.valueCardInfo
				},
				set(val){
					this.$store.state.user.valueCardInfo = val
				}
			}
		},
		methods: {
			valueCardLoad(id){
				this.$store.dispatch('setValueCardInfo',{
					vc_id:id
				})
			}
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/valueCard/detail/detail'
			}
		},
		onLoad(e){
			this.vc_id = e.id
			this.valueCardLoad(e.id)
		}
	}
</script>

<style scoped>
	
.ny-list{
	padding-bottom: 100upx;
}
</style>
