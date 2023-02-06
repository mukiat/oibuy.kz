<template>    
	<view class="container-tab-bar">
		<view class="tab-bar">
			<view v-for="(item,index) in tabs" :key="index" :class="['tab-item',status==index ? 'active' : '']" @click="teamNav(index)">
				<text>{{ item }}</text>
			</view>
		</view>	
		<view class="section-list">
			<view v-if="crowdOrderData && crowdOrderData.length > 0">
				<view class="user-item user-order-item" v-for="(item,index) in crowdOrderData" :key="index">
					<view class="item-hd">
						<view class="shop-name">{{$t('lang.order_status')}}</view>
						<view class="order-status uni-red">{{item.orderstatus}}</view>
					</view>	
					<view class="item-bd">						
						<view class="subHead">
							<view class="item">
								<view class="tit">{{$t('lang.label_order')}}</view>
								<view class="value" @click="$outerHref('/pagesA/crowdfunding/user/orderdetail?id='+item.order_id,'app')">{{ item.order_sn }}</view>
							</view>
							<view class="item">
								<view class="tit">{{$t('lang.order_time')}}：</view>
								<view class="value time">
									<view>{{ item.add_time }}</view>
								</view>
							</view>	
						</view>
						<view class="product-list product-list-max" >
							<view class="product-items">
								<view class="item" @click="$outerHref('/pagesA/crowdfunding/user/orderdetail?id='+item.order_id,'app')">
									<view class="product-img">
										<image :src="item.title_img" mode="widthFix" v-if="item.title_img"></image>
									</view>									
								</view>
							</view>
							<view class="product-more" @click="$outerHref('/pagesA/crowdfunding/user/orderdetail?id='+item.order_id,'app')">
								<view class="reture-right-cont">
									<text class="twolist-hidden f-06">{{ item.title }}</text>
									<text class="f-03 color-7 m-top04">{{$t('lang.label_support_money')}}<text class="color-red">{{ item.total_fee }}</text>{{$t('lang.yuan')}}</text>
									<view class="f-03 color-7 m-top02">{{item.content}}</view>
								</view>
							</view>
						</view>	
						<view class="list-item-box">{{$t('lang.total_flow')}}：<text class="uni-red">{{ item.total_fee }}</text></view>
					</view>
					<view class="item-fd">
						<view class="btn-bar-min">
							<view class="btn" v-if="item.total_amount > 0 && item.handler != 7 && item.handler != 8" @click="onlinepay(item.order_sn)">{{$t('lang.immediate_payment')}}</view>
							<view class="btn" v-if="item.handler == 2"  @click="receivedOrder(item.order_id)">{{$t('lang.received')}}</view>
							<view @click="$outerHref('/pagesA/crowdfunding/user/orderdetail?id='+item.order_id,'app')" hover-class="none"><view class="btn">{{$t('lang.view_order')}}</view></view>
						</view>
					</view>
				</view>
			</view>	
			<view v-else>
				<dsc-not-content></dsc-not-content>
			</view>
		</view>	
		<dsc-tabbar :tabbar="tabbar"></dsc-tabbar>
	</view>
</template>
<script>
    import { mapState } from 'vuex'
    import dscTabbar from '@/components/dsc-tabbar.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';

    export default {
        name: "bargain-order",
        components: {
            dscTabbar,
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
                tabs: [this.$t('lang.all_order'), this.$t('lang.order_status_08'), this.$t('lang.order_status_09'), this.$t('lang.order_status_03'), this.$t('lang.order_status_10')],
				tabbar:{
					type:'crowdfunding',
					index:1,
					curpage:''
				}
            }
        },
        //初始化加载数据
        created() {
            this.orderList(1)
        },
        computed: {
            crowdOrderData:{
                get(){
                    return this.$store.state.crowdfunding.crowdOrderData
                },
                set(val){
                    this.$store.state.crowdfunding.crowdOrderData = val
                }
            }
        },
        methods: {
            teamNav(i) {
				this.status = i;
                this.orderList(1);
            },
            orderList(page) {
                if(page){
                    this.page = page
                    this.size = Number(page) * 10
                }
                this.$store.dispatch('setCrowdfundingOrder',{
                    status: this.status,
                    size:this.size,
                    page:this.page,
                })
            },
			//确认收货
			receivedOrder(id){
				this.$store.dispatch('setReceivedOrder',{
					order_id:id
				}).then(res=>{
					if(res.data == true){
						uni.showToast({
							title: this.$t('lang.order_confirm_receipt'),
							icon:'none'
						});
						this.orderList(1)
					}
				})
			},
			//立即支付
			onlinepay(id){
				uni.navigateTo({
					url:'/pages/done/done?order_sn=' + id
				})
			}
			
        },
		onReachBottom(){
			if(this.page * this.size == this.crowdOrderData.length){
				this.page ++
				this.orderList()
			}
		}
    };
</script>

<style scoped>
	.product-items .item .product-img{ border: 0;}
</style>
