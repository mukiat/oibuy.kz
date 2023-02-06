<template>
	<view class="container flow-checkout" v-if="crowdCheckoutData">		
		<view class="flow-checkout-adr" @click="checkoutAddress">
			<view class="adr-content">
				<view class="title" v-if="crowdCheckoutData.default_address">
					<text class="name">{{ crowdCheckoutData.default_address.consignee }}</text>
					<text class="mobile">{{ crowdCheckoutData.default_address.mobile }}</text>
				</view>
				<view class="address">{{ consignee_address }}</view>
			</view>
			<uni-icons type="forward" size="18" color="#999999"></uni-icons>
		</view>
		
		<view class="goods-list" v-if="cart_goods">			
			<view class="goods-item" @click="$outerHref('/pagesA/crowdfunding/detail/detail?id='+cart_goods.id,'app')">
				<view class="goods-left uni-flex-common">
					<image :src="cart_goods.title_img" class="img" mode="widthFix" v-if="cart_goods.title_img" />
					<image src="../../../static/not_goods.png" class="img" mode="widthFix" v-else />
				</view>
				<view class="goods-right">
					<text class="goods-name twolist-hidden">{{cart_goods.title}}</text>
					<view class="goods-cont uni-flex-common uni-space-between">
						<view class="text">{{$t('lang.label_crowdfunding_fund')}}<text class="uni-red">{{cart_goods.formated_price}}</text>{{$t('lang.yuan')}}</view>
						<view class="text">{{$t('lang.support_number')}}{{cart_goods.join_num}}{{$t('lang.ren')}}</view>
					</view>
					<view class="ect-progress">
						<progress :percent="cart_goods.baifen_bi" show-info="true" border-radius="3" stroke-width="6" font-size="12" active="true" activeColor="#f92028"></progress>
					</view>
					<view class="goods-cont">{{cart_goods.content}}</view>
				</view>
			</view>
		</view>	

		<view class="uni-card uni-card-not">
			<view class="uni-list">
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.delivery_cost')}}</text>
						<view class="value">
							<text class="uni-red" >{{ shipping_fee }}</text>
						</view>
					</view>
				</view>
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate justify-content-fs">
						<text class="title">{{$t('lang.buyer_message')}}</text>
						<view class="value">
							<input :placeholder="$t('lang.buyer_message_placeholder')" v-model="postscriptValue[index]" />
						</view>
					</view>
				</view>
				<view class="uni-list-cell">
					<view class="uni-list-cell-navigate justify-content-fe">
						<view class="value">{{$t('lang.gong')}}{{crowdCheckoutData.number}} {{$t('lang.goods_letter')}}，{{$t('lang.total_flow')}}：<text class="uni-red">{{ crowdCheckoutData.total.amount_formated }}</text></view>
					</view>
				</view>
			</view>
		</view>	
		<view class="uni-card uni-card-not">
			<view class="uni-list">
				<view class="uni-list-cell" hover-class="uni-list-cell-hover" @click="paymentSelect">
					<view class="uni-list-cell-navigate uni-navigate-right uni-navigate-badge">
						<text class="title">{{$t('lang.payment_mode')}}</text>
						<view class="value">{{ pay_name }}</view>
					</view>
				</view>				
			</view>
		</view>		
		<view class="uni-card uni-card-not" v-if="crowdCheckoutData.use_surplus > 0">
			<view class="uni-list">
				<view class="uni-list-cell uni-list-cell-title">
					<view class="uni-list-cell-navigate">
						<text class="title">{{$t('lang.is_use_balance')}}</text>
						<view class="value"><switch :checked="surplusSelf" @change="surplusSelfHandle" /></view>
					</view>
				</view>
			</view>
		</view>		
		<view class="btn-goods-action">
			<view class="submit-bar-text">
				<text>{{$t('lang.label_total_amount_payable')}}</text>
				<view class="submit-bar-price">{{ amountTotal }}</view>
			</view>
			<view class="btn-bar">
				<button class="btn btn-red" :disabled="disabled" @click="onSubmit">{{$t('lang.immediate_payment')}}</button>
			</view>
		</view>		
		
		<!--支付方式-->
		<uni-popup :show="paymentShow" type="bottom" mode="fixed" v-on:hidePopup="handelClose('payment')">
			<view class="activity-popup">
				<view class="title">
					<view class="txt">{{$t('lang.payment_mode')}}</view>
					<uni-icons type="closeempty" size="24" color="#999999" @click="handelClose('payment')"></uni-icons>
				</view>
				<view class="not-content">
					<scroll-view :scroll-y="true" class="select-list">
						<view class="select-item" v-for="(item,index) in payment_method" :key="index" :class="{'active':pay_id == item.pay_id}" @click="payment_method_select(item.pay_id,item.pay_name)">
							<view class="txt">{{ item.pay_name }}</view>
							<view class="iconfont icon-ok"></view>
						</view>
					</scroll-view>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script>
import { mapState} from 'vuex'
import uniIcons from '@/components/uni-icons/uni-icons.vue';
import uniPopup from '@/components/uni-popup.vue';

export default{
    data() {
        return {
			loading:true,
            routerName:'crowd_funding',
			paymentShow: false,
            pay_name:'',
            cur_id:1,   
            value:'',
            radio:1,
            apart:'apart',
            showBase: false,
            use_surplus_val:0,
            pay_id: null,
			pid: this.pid,
			id:this.id,
			number: this.number	
        }
    },
	components:{ 
		uniIcons,
		uniPopup
    },
    //初始化加载数据
     onLoad(e){
		this.pid = e.pid,
		this.id  = e.id,
		this.number  = e.number		
        this.checkoutDefault()
    },
    computed: {
        ...mapState({
            crowdCheckoutData: state => state.crowdfunding.crowdCheckoutData,
        }),
        consignee_title(){
            if(this.crowdCheckoutData.default_address){
                return this.crowdCheckoutData.default_address.consignee +' '+ this.crowdCheckoutData.default_address.mobile
            }else{
                return ''
            }
        },
        consignee_address(){
            if(this.crowdCheckoutData.default_address){
                return this.crowdCheckoutData.default_address.province+this.crowdCheckoutData.default_address.city+this.crowdCheckoutData.default_address.district+this.crowdCheckoutData.default_address.address
            }else{
                return ''
            }
        },
        surplusSelf: {
            get() {
                return this.use_surplus_val == 0 ? false : true
            },
            set(val) {
                this.use_surplus_val = val == true ? 1 : 0
            }
        },
        cart_goods(){
            return this.crowdCheckoutData.cart_goods
        },
        order(){
            return this.crowdCheckoutData.order
        },
        total(){
            return this.crowdCheckoutData.total
        },
        amountTotal(){
            return this.total ? this.total.amount_formated : 0
        },
        payment_method() {
            return this.crowdCheckoutData.payment_list ? this.crowdCheckoutData.payment_list : ''
        },
        shipping_fee(){
            return this.total && this.total.shipping_fee != 0 ? this.total.shipping_fee : this.$t('lang.free_shipping')
        }
    },
    methods:{
        checkoutDefault(){
            this.$store.dispatch('setCrowdfundingCheckout',{
                pid: this.pid,
                id:this.id,
                number: this.number	,
            })
        },
		//展开支付方式
		paymentSelect() {
			this.paymentShow = true
		},
        payment_method_select(id, name) {
            this.pay_id = id
            this.pay_name = name
        },
		//是否使用余额
		surplusSelfHandle(e){
			this.use_surplus_val = e.detail.value == true ? 1 : 0
		},
		//关闭弹出层
		handelClose(val){
			if(val == 'payment'){
				this.paymentShow = false
			}
		},
		//选择收货地址
        checkoutAddress() {                 
			uni.navigateTo({
				url:'/pagesB/address/address?type=checkout'
			})
        },
		// 立即付款
        onSubmit(){
            this.$store.dispatch('setCrowdfundingDone',{
                pid: this.pid,
                id:this.id,
                number: this.number,
                pay_id:this.pay_id,
                is_surplus: this.use_surplus_val,
            }).then(res=>{
				if(res.error == 1){
					uni.showToast({
						icon:'none',
						title:res.msg
					})
					return false
				}else{
					uni.reLaunch({
						url:'/pages/done/done?order_sn=' + res
					})   
				}
            })
        },
    },
    watch:{
        crowdCheckoutData(){
            //默认选中在线支付
            if (this.pay_name == '' && this.crowdCheckoutData.error != 'address') {
                this.payment_method.forEach(v => {
                    if (v.pay_code == 'onlinepay') {
                        this.pay_name = v.pay_name
                        this.pay_id = v.pay_id
                    }
                })
            }

            if (this.crowdCheckoutData.error == 'address') {
				uni.navigateTo({
					url:'/pagesB/address/addressEdit'
				})
            }
        },
        payment_method() {
            if (this.payment_method == '') {
				uni.showToast({
					title: this.$('lang.payment_method_not_installed'),
					icon:'none'
				})
                return false
            }
        },
    }
}
</script>

<style>	
	.goods-list .goods-cont{ font-size: 25upx; color: #999; line-height: 1.5;}
	.goods-list .goods-cont .uni-red{ margin: 0 5upx;}
</style>
