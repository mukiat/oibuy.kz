<template>
    <view class="container">
		<view class="tab-bar">
			<view v-for="(item,index) in tabs" :key="index" :class="['tab-item',status==index ? 'active' : '']" @click="teamNav(index)">
				<text>{{ item }}</text>
			</view>
		</view>	
		<!--商品列表-->
		<view class="goods-list" v-if="crowdMyFocusData && crowdMyFocusData.length > 0">
			<view class="goods-item"  v-for='(item,index) in crowdMyFocusData' :key="index" @click="$outerHref('/pagesA/crowdfunding/detail/detail?id='+item.id,'app')">
				<view class="goods-left uni-flex-common">
					<image :src="item.title_img" class="img" mode="widthFix" v-if="item.title_img" />
					<image src="../../../static/not_goods.png" class="img" mode="widthFix" v-else />
				</view>
				<view class="goods-right">
					<view class="goods-name twolist-hidden">{{item.title}}</view>
					<view class="goods-cont uni-flex-common uni-space-between">
						<view class="text">{{$t('lang.support_number')}}<text class="uni-red">{{item.join_num}}</text>{{$t('lang.ren')}}</view>
						<view class="text">{{$t('lang.time_remaining')}}{{item.shenyu_time}}{{$t('lang.tian')}}</view>
					</view>
					<view class="ect-progress">
						<progress :percent="item.baifen_bi" show-info="true" border-radius="3" stroke-width="6" font-size="12" active="true" activeColor="#f92028"></progress>
					</view>
					<view class="goods-cont uni-flex-common uni-space-between">
						<view class="text">{{$t('lang.label_has_crowdfunding')}}<text class="uni-red">{{item.join_money}}</text></view>
						<view class="text">{{$t('lang.label_target')}}<text class="uni-red">{{item.amount}}</text></view>
					</view>
				</view>
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
        name: "bargain-order",
        components: {
			dscNotContent
        },
        data() {
            return {
                routerName:'crowd_funding',
                disabled:false,
			    loading:true,
				size:10,
                page:1,
                status:0,
                active: 0,
                tabs: [this.$t('lang.all_project'), this.$t('lang.underway'), this.$t('lang.have_succeeded')],
            };
        },
        //初始化加载数据
        created() {
             this.focusList(1)
        },
        computed: {
            crowdMyFocusData:{
                get(){
                    return this.$store.state.crowdfunding.crowdMyFocusData
                },
                set(val){
                    this.$store.state.crowdfunding.crowdMyFocusData = val
                }
            }
        },
        methods: {
            teamNav(i) {
				this.status = i;
                this.focusList(1)
            },
            focusList(page) {
                if(page){
                    this.page = page
                    this.size = Number(page) * 10
                }
                this.$store.dispatch('setCrowdfundingMyFocus',{
                    status: this.status,
                    size:this.size,
                    page:this.page,
                });
            },
        },
		onReachBottom(){
			if(this.page * this.size == this.crowdMyFocusData.length){
				this.page ++
				this.focusList()
			}
		}
    };
</script>

<style scoped>
	.goods-list{ padding-top: 50px;}
	.goods-list .goods-cont{ font-size: 25upx; color: #999; line-height: 1.5;}
	.goods-list .goods-cont .uni-red{ margin: 0 5upx;}
</style>
