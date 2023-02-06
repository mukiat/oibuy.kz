<template>
	<div class="con bonus">
		<template v-if="valueCardInfo && valueCardInfo.length > 0">
			<ul class="user_valuecard_detail">
    			<li class="padding-all bg-color-write m-b06" v-for="(item,index) in valueCardInfo" :key="index">
    				<header class="f-03 col-3">
    					<div><em class="col-9">{{ $t('lang.order_sn') }}：{{ item.order_sn }}</em></div>
    					<div><em class="col-9">{{ $t('lang.use_time') }}：{{ item.record_time }}</em></div>
    				</header>
    				<div class="m-top04 card_conttent">
    					<div class="dis-box f-05 col-9">
    						<div class="box-flex">{{ $t('lang.recharge') }}：</div><p class="color-red">{{ item.add_val }}</p>
    					</div>
    					<div class="dis-box f-05 col-9">
    						<div class="box-flex">{{ $t('lang.use') }}：</div><p class="color-red">{{ item.use_val }}</p>
    					</div>
    				</div>
    			</li>
    		</ul>
		</template>
		<template v-else>
			<NotCont />
		</template>
		<!-- <van-goods-action>
			<van-goods-action-big-btn :text="$t('lang.recharge')" @click="onClickBigBtn" primary />
		</van-goods-action> -->
		<CommonNav></CommonNav>
	</div>
</template>

<script>
import { mapState } from 'vuex'

import {
	GoodsAction,
	GoodsActionBigBtn,
	GoodsActionMiniBtn,
} from 'vant'

import NotCont from '@/components/NotCont'
import CommonNav from '@/components/CommonNav'

export default{
	name:'value-card',
	components: {
		NotCont,
		CommonNav,
		[GoodsAction.name] : GoodsAction,
		[GoodsActionBigBtn.name] : GoodsActionBigBtn,
		[GoodsActionMiniBtn.name] : GoodsActionMiniBtn,
	},
	data(){
		return {
			vc_id:this.$route.params.id
		}
	},
	created() {
		this.valueCardLoad()
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
		valueCardLoad(){
			this.$store.dispatch('setValueCardInfo',{
				vc_id:this.vc_id
			})
		},
		onClickBigBtn(){
			this.$router.push({
				name: 'addValueCard',
				query:{
					type: 'deposit',
					vc_id: this.vc_id
				}
			})
		},
	}
}
</script>