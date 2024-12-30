<template>
	<div class="con bonus" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<van-tabs :active="active">
			<van-tab v-for="(item,index) in tabs" :key="index">
				<div class="nav_active" slot="title" @click="CommonTabs(index)">{{item}}</div>
			</van-tab>
		</van-tabs>
		<template v-if="bonusData != ''">
			<div class="bonus-select">
				<div class="li br-5 bg-color-write p-r" :class="bonus_index != 0 ? 'active':''" v-for="(item,index) in bonusData" :key="index">
					<div class="dis-box cont">
						<div class="left-cont cont-center">
							<h2 class="price color-red f-weight" v-html="item.type_money"></h2>
						</div>
						<div class="box-flex center-cont p-l1">
							<h4 class="f-weight f-05 color-3">{{item.type_name}}</h4>
							<p class="f-02 color-9 m-top02">{{ $t('lang.man') }}{{item.min_goods_amount}}{{ $t('lang.price_unit') }}{{ $t('lang.have_access_to') }}</p>
							<p class="f-02 color-9 ">{{item.shop_name}}</p>
							<!-- <p class="f-02 color-9 ">{{ $t('lang.limit') }}{{item.limit}}{{ $t('lang.have_access_to') }}</p> -->
						</div>
						<router-link :to="{name:'home'}" class="show right-cont p-r" v-if="bonus_index===0">
							<span class="p-a f-02">{{$t('lang.to_use_the')}}</span>
						</router-link>
						<img v-if="bonus_index===1" class="bonus-img" src="../../../../assets/img/coupons-print-end.png" />
						<img v-if="bonus_index===2" class="bonus-img" src="../../../../assets/img/coupons-print-enddata.png" />
					</div>
					<div class="footer f-02 color-9">{{$t('lang.service_life')}}:{{item.use_start_date}}{{$t('lang.zhi')}}{{item.use_end_date}}
					</div>
				</div>
			</div>
		</template>
		<template v-else>
			<NotCont />
		</template>
		<van-goods-action>
			<van-goods-action-big-btn :text="$t('lang.convert_bonus')" @click="onClickBigBtn" primary />
		</van-goods-action>
		<template v-if="loading">
			<van-loading type="spinner" color="black" />
		</template>
		<CommonNav></CommonNav>
	</div>
</template>
<script>
import { mapState } from 'vuex'

import {
	Tab,
	Tabs,
	Loading,
	Waterfall,
	GoodsAction,
	GoodsActionBigBtn,
	GoodsActionMiniBtn,
} from 'vant'

import NotCont from '@/components/NotCont'
import CommonNav from '@/components/CommonNav'
import arrRemove from '@/mixins/arr-remove'

export default {
	name: "bonus-name",
	components: {
		NotCont,
		CommonNav,
		[Tab.name]: Tab,
		[Tabs.name]: Tabs,
		[Loading.name] : Loading,
		[GoodsAction.name] : GoodsAction,
		[GoodsActionBigBtn.name] : GoodsActionBigBtn,
		[GoodsActionMiniBtn.name] : GoodsActionMiniBtn,
	},
	directives: {
		WaterfallLower: Waterfall('lower')
	},
	data() {
		return {
			disabled:false,
			loading:true,
			page:1,
			size:10,
			active: 0,
			bonus_index:0,
			tabs: [this.$t('lang.unused'),this.$t('lang.have_been_used'),this.$t('lang.have_expired')],
		};
	},
	//初始化加载数据
	created() {
		this.bonusClick()
	},
	computed: {
		bonusData:{
			get(){
				return this.$store.state.user.bonusData
			},
			set(val){
				this.$store.state.user.bonusData = val
			}
		}
	},
	methods: {
		CommonTabs(val) {
			this.bonus_index = val
			this.bonusClick(1)
		},
		bonusClick(page) {
			if(page){
				this.page = page
				this.size = Number(page) * 10
			}

			this.$store.dispatch('setBonusList',{
				page: this.page,
				size: this.size,
				type: this.bonus_index
			})
		},
		loadMore(){
			//瀑布流分页
			setTimeout(() => {
				this.disabled = true
				if(this.page * this.size == this.bonusData.length){
					this.page ++	
					this.bonusClick()
				}
			},200);
		},
		onClickBigBtn() {
			this.$router.push({
				name: 'addBonus'
			})
		}
	},
	watch:{
		bonusData(){
			if(this.page * this.size == this.bonusData.length){
				this.disabled = false
				this.loading = true
			}else{
				this.loading = false
			}

			this.bonusData = arrRemove.trimSpace(this.bonusData)
		}
	}
};
</script>
