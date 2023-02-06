<template>
	<view>
		<block v-if="type == 'drphelp'">
			<view class="uni-card uni-card-not">
				<view class="uni-title">{{$t('lang.help_center')}}</view>
				<view class="uni-list" v-for="(listItem,listIndex) in articleHelpList" :key="listIndex">
					<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="$outerHref('/pagesC/article/detail/detail?id='+listItem.id,'app')">
						<view class="uni-list-cell-navigate uni-navigate-right">
							<text class="title">{{ listItem.title }} </text>
						</view>
					</view>
				</view>
			</view>

		</block>
		<block v-else>
			<view class="uni-card uni-card-not" v-for="(item,index) in articleHelpList" :key="index">
				<view class="uni-title">{{ item.cat_name }}</view>
				<view class="uni-list" v-for="(listItem,listIndex) in item.list" :key="listIndex">
					<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="$outerHref('/pagesC/article/detail/detail?id='+listItem.article_id,'app')">
						<view class="uni-list-cell-navigate uni-navigate-right">
							<text class="title">{{ listItem.title }} </text>
						</view>
					</view>
				</view>
			</view>
		</block>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import universal from '@/common/mixins/universal.js';
	export default {
		mixins:[universal],
		data() {
			return {
				type:''
			};
		},
		computed:{
			...mapState({
				articleHelpList: state => state.user.articleHelpList
			})
		},
		onLoad(e) {
			this.type = e.type ? e.type : '';
			
			this.$store.dispatch('setArticleHelp',{
				type:this.type
			})
		},
		onShareAppMessage(res){
			return {
			  title: this.$store.state.common.shopConfig.shop_title,
			  path: '/pagesB/help/help'
			}
		},
	}
</script>

<style>
.uni-card{ margin: 0;}
.uni-title{ background: #F5F5F5; padding: 20upx 30upx;}
</style>
