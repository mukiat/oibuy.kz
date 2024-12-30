<template>
	<div class="con bonus" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<template v-if="bonusData != ''">
			<div class="bonus-select">
				<div class="li br-5 bg-color-write p-r" v-for="(item,index) in bonusData" :key="index">
					<div class="dis-box cont">
						<div class="left-cont cont-center">
							<h2 class="price color-red f-weight" v-html="item.type_money"></h2>
						</div>
						<div class="box-flex center-cont p-l1">
							<h4 class="f-weight f-05 color-3">{{item.type_name}}</h4>
							<p class="f-02 color-9 m-top02">{{$t('lang.man')}}{{item.min_goods_amount}}{{$t('lang.is_money_use')}}</p>
							<p class="f-02 color-9">{{item.shop_name}}</p>
						</div>
						<template v-if="item.is_receive == 1">
							<a href="javascript:;" class="right-cont">
								<span class="f-02 end">{{$t('lang.receive_hove')}}</span>
							</a>
						</template>
						<template v-else-if="item.is_left == 0">
							<a href="javascript:;" class="right-cont">
								<span class="f-02 end">{{$t('lang.brought_up')}}</span>
							</a>
						</template>
						<template v-else>
						<a href="javascript:;" class="right-cont" @click="receive(item.type_id)">
							<span class="f-02">{{$t('lang.receive')}}</span>
						</a>
						</template>
					</div>
					<div class="footer f-02 color-9">
						<div class="footer f-02 color-9">
						<template v-if="item.valid_period > 0">
						{{item.lang_receive}} {{item.valid_period}} {{item.lang_valid_period_lost}}
						</template>
						<template v-else>
						{{$t('lang.label_service_life')}}{{item.begintime}}{{$t('lang.zhi')}} {{item.endtime}}
						</template>
					</div>
					</div>
				</div>
			</div>
		</template>
		<template v-else>
			<NotCont />
		</template>
		<template v-if="loading">
			<van-loading type="spinner" color="black" />
		</template>

		<!-- 快捷菜单 -->
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
	Toast
} from 'vant'

import NotCont from '@/components/NotCont'
import arrRemove from '@/mixins/arr-remove'
import CommonNav from '@/components/CommonNav'

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
		[Toast.name] : Toast
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
			id:this.$route.query.id ? this.$route.query.id : 0
		};
	},
	//初始化加载数据
	created() {
		this.bonusClick()
	},
	computed: {
		bonusData:{ 
			get(){
				return this.$store.state.other.bonusData
			},
			set(val){
				this.$store.state.other.bonusData = val
			}
		}
	},
	methods: {
		bonusClick(page) {
			if(page){
				this.page = page
				this.size = Number(page) * 10
			}

			this.$store.dispatch('setBonus',{
				page: this.page,
				size: this.size,
				type: 4,
				id:this.id
			})
		},
		receive(id){
			this.$store.dispatch('receiveBonus',{
				type_id:id
			}).then(({data})=>{
				Toast(data.msg)
				if(data.code == 0){
					this.bonusClick(1)
				}
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
			},200)
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
