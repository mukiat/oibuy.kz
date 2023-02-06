<template>
	<div class="user-recharge">
		<van-field v-model="gift_card" :label="$t('lang.gift_card')" :placeholder="$t('lang.enter_gift_card')" />
		<van-field v-model="gift_pwd" :label="$t('lang.gift_pwd')" :placeholder="$t('lang.enter_gift_pwd')" class="m-top10"/>
		<div class="padding-all">
			<template v-if="submit_btn">
				<div v-html="submit_btn"></div>
			</template>
			<template v-else>
				<button class="btn btn-submit border-radius-top05" @click="submitBtn">{{$t('lang.subimt')}}</button>
			</template>
		</div>
		<CommonNav></CommonNav>
	</div>
</template>

<script>
import qs from 'qs'

import {
	Field,
	Cell,
	CellGroup,
	Popup,
	Toast
} from 'vant'

import CommonNav from '@/components/CommonNav'

export default{
	data(){
		return {
			show:false,
			gift_card:'',
			gift_pwd:'',
			submit_btn:''
		}
	},
	components:{
		[Field.name]:Field,
		[Cell.name]:Cell,
		[CellGroup.name]:CellGroup,
		[Popup.name]:Popup,
		[Toast.name]:Toast,
		CommonNav
	},
	created(){
		this.checkLoginGift();
	},
	methods:{
		checkLoginGift(){
			let that = this;
			this.$http.get(`${window.ROOT_URL}api/gift_gard`).then(({data})=>{
				if(data.data.error && data.data.error != 0){
					that.$router.push({name: 'giftCard'});
				}else{
					that.goList();
				}
			})
		},
		submitBtn(){
			if(this.gift_card.length == 0){
				Toast(this.$t('lang.enter_gift_card'));
				return false;
			}
			if(this.gift_pwd.length == 0){
				Toast(this.$t('lang.enter_gift_pwd'));
				return false;
			}
			let that = this;
			let o = {
				gift_card:this.gift_card,
				gift_pwd:this.gift_pwd
			}
			this.$http.get(`${window.ROOT_URL}api/gift_gard/check_gift`,{ params:o }).then(({data})=>{
				if(data.data.error == 1){
					Toast(data.data.msg)
				}else{
					that.goList();
				}
			})
		},
		goList(){
			this.$router.push({name: 'giftCardResult'})
		}
	}
}
</script>
