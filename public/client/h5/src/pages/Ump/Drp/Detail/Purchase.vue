<template>
	<div class="con">
		<div class="vip-buy">
			<div class="head">
				<div class="title">{{$t('lang.vip_card')}}</div>
				<div class="notice">
					<h3>
						<!-- <a href="javascript:;" class="more">{{$t('lang.view_euity')}}<i class="iconfont icon-more"></i></a> -->
						<b>{{$t('lang.must_be_read')}}</b>
					</h3>
					<p v-html="purchaseData.novice"></p>
				</div>
			</div>
			<div class="bg-color-write">
				<div class="cell-box">
					<div class="cell-title">{{$t('lang.total_amount_payable')}}</div>
					<div class="cell-content" v-html="purchaseData.price"></div>
				</div>
				<div class="cell-box">
					<div class="cell-title">{{$t('lang.payment_mode')}}</div>
					<div class="cell-content">{{$t('lang.online_pay')}}</div>
				</div>
			</div>
		</div>
		<div class="vip-fixed-bottom">
			<div class="item article-confirm">
				<div class="radio-wrap" @click="toggleConfirm"><i class="radio-icon" :class="{'active': confirm}"></i>{{$t('lang.checkout_help_article')}}</div>
				<span @click="articleHref(purchaseData.agreement_id)">{{$t('lang.drp_purchase_agreement')}}</span>
			</div>
			<div class="item vip-btn" @click="onSubmit">
				<span>{{$t('lang.immediate_pay')}}</span>
				<span class="number" v-html="purchaseData.price"></span>
			</div>
		</div>
  		<CommonNav :routerName="routerName">
	         <li slot="aloneNav">
				<router-link :to="{name: 'drp'}">
					<i class="iconfont icon-fenxiao"></i>
					<p>{{$t('lang.drp_center')}}</p>
				</router-link>
			</li>
	    </CommonNav>
	</div>
</template>

<script>
import { mapState } from 'vuex'
import { 
	Button,
	GoodsAction,
	GoodsActionBigBtn,
	GoodsActionMiniBtn,
	Toast,
} from 'vant'

import CommonNav from '@/components/CommonNav'

export default{
	data(){
		return{
			routerName:'drp',
			confirm: true
		}
	},
	components:{
		[Button.name]:Button,
		[GoodsAction.name] : GoodsAction,
		[GoodsActionBigBtn.name] : GoodsActionBigBtn,
		[GoodsActionMiniBtn.name] : GoodsActionMiniBtn,
		[Toast.name]:Toast,
		CommonNav
	},
	computed:{
		...mapState({
			purchaseData: state => state.drp.purchaseData
		}),
	},
	created(){
		this.$store.dispatch('setDrpPurchase')
	},
	methods:{
		onSubmit(){
			if(this.confirm){
				this.$router.push({
					name:'drp-done'
				})	
			}else{
				Toast(this.$t('lang.drp_agreement_please'))
			}
		},
		toggleConfirm(){
			this.confirm = !this.confirm
		},
		articleHref(id){
            this.$router.push({
                name:'articleDetail',
                params:{
                    id:id
                }
            })
        }
	}
}
</script>