<template>
	<div class="con bonus value_cart_content" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<header class="header_wrap">
			<div class="use_price">Теңгерім {{currency}}<span>{{valueCartInfo.card_total || 0.00}}</span></div>
		</header>
		<div class="cart_tabs">
			<div :class="['tab_item', index == currTab ? 'active_tab' : '']" v-for="(item, index) in tabs" :key="index" @click="onClickTab(index)">
				{{item}}{{index == currTab ? `(${cardCount})` : ''}}
			</div>
		</div>
		<template v-for="(item, index) in cartList">
			<div class="list_wrap" v-show="currTab == index" :key="index">
				<dsc-value-cart :list="item" :type="index" @link="goLink"></dsc-value-cart>
				<NotCont v-if="shopEmpty" />
				<div class="loadmore" v-show="item.length >= size">{{loadmoreStatus}}</div>
			</div>
		</template>
		<van-goods-action>
			<van-goods-action-big-btn :text="$t('lang.bind_value_card')" @click="onClickBigBtn" primary />
		</van-goods-action>
		<CommonNav></CommonNav>
	</div>
</template>

<script>

import {
	Waterfall,
	GoodsAction,
	GoodsActionBigBtn,
	GoodsActionMiniBtn
} from 'vant'

import NotCont from '@/components/NotCont'
import CommonNav from '@/components/CommonNav'
import arrRemove from '@/mixins/arr-remove'
import dscValueCart from '@/components/dsc-value-cart/dsc-value-cart.vue'
export default{
	name:'value-card',
	components: {
		NotCont,
		CommonNav,
		dscValueCart,
		[GoodsAction.name] : GoodsAction,
		[GoodsActionBigBtn.name] : GoodsActionBigBtn,
		[GoodsActionMiniBtn.name] : GoodsActionMiniBtn
	},
	directives: {
		WaterfallLower: Waterfall('lower')
	},
	data(){
		return {
			disabled: false,
			loading:true,
			size:10,
			currTab: 0,
			currency:'₸',
			tabs: ['Жарамды', 'Жарамсыз'],
			loadmoreStatus: 'Жүктелуде...',
			shopEmpty: false,
			cartList: [],
			cartPaginated: [],
			valueCartInfo: {}
		}
	},
	created() {
		let configData = JSON.parse(sessionStorage.getItem('configData'));
		if(configData){
		  this.currency = configData.currency_format.replace('%s', '');
		}
		
		this.valueCardLoad()
	},
	computed: {
		cardCount: function () {
			if (this.valueCartInfo.card_list) {
				if (this.currTab == 0) {
					return this.valueCartInfo.use_card_count || 0
				} else {
					return this.valueCartInfo.not_use_card_count || 0
				}
			} else {
				return 0
			}
		}
	},
	methods: {
		async valueCardLoad() {
			this.loading = true;
			let i = this.currTab;
			
			if (this.cartList.length == 0) this.cartList = this.tabs.map(() => []);
			if (this.cartPaginated.length == 0) this.cartPaginated = this.tabs.map(() => 1);
			
			let page = this.cartList[i].length / this.size;
			
			page = Math.ceil(page) + 1
			
			const { data } = await this.$store.dispatch('getValueCard', {
				page: page,
				size: this.size,
				use_type: i == 0 ? 1 : 0
			});
			
			const { card_list } = data;
			
			this.valueCartInfo = data;
			
			if (card_list) {
				this.$set(this.cartPaginated, i, card_list.length < this.size ? 0 : 1);
				
				this.$set(this.cartList, i, [...this.cartList[i], ...card_list]);
				
				this.shopEmpty = this.cartList[i].length == 0;
				this.loadmoreStatus = card_list.length < this.size ? 'Басқа жоқ' : 'Жүктелуде...';
			};
			
			this.$nextTick(() => {
				this.disabled = false;
				this.loading = false;
			})
		},
		onClickBigBtn(){
			this.$router.push({
				name: 'addValueCard'
			})
		},
		onClickTab(i) {
			if (this.currTab == i) return;
			this.currTab = i;
			this.shopEmpty = false;
			if (this.cartList[i].length == 0) this.valueCardLoad();
		},
		loadMore(){
			if (this.loading) return;
			this.disabled = true;
			//瀑布流分页
			setTimeout(() => {
				let i = this.currTab;
				let isMore = this.cartPaginated[i];
				if (isMore > 0) {
					this.loadmoreStatus = 'Жүктелуде...';
					this.valueCardLoad();
				} else {
					this.loadmoreStatus = 'Басқа жоқ';
				}
			},200)
		},
		goLink(res) {
			const { type, value: { vid } } = res;
			if (type == 'add') this.$router.push({name:'addValueCard', query:{type: 'deposit', vc_id:vid}});
			if (type == 'detail') this.$router.push({name:'valueCardDetail', params:{id:vid}});
		}
	}
}
</script>

<style lang="scss" scoped>
.bonus {
	padding-bottom: 5rem;
	padding-bottom: calc(env(safe-area-inset-bottom) + 5rem);
}
.margin0 {
	margin-bottom: 0;
}
.value_cart_content {
	.header_wrap {
		overflow: hidden;
		position: relative;
		display: flex;
		justify-content: center;
		align-items: center;
		height: 25vw;
		&:after {
			content: '';
			position: absolute;
			bottom: 0;
			left: 50%;
			transform: translateX(-50%);
			width: 125%;
			height: 150%;
			border-radius: 50%;
			background-color: #E83C2D;
		}
		.use_price {
			position: relative;
			color: #fff;
			z-index: 2;
			span {
				font-size: 2.2rem;
				font-weight: 700;
			}
		}
	}
	.cart_tabs {
		display: flex;
		justify-content: space-around;
		align-items: center;
		padding: 2rem 0;
		.active_tab {
			position: relative;
			color: #E83C2D;
			&:after {
				content: '';
				position: absolute;
				bottom: -0.5rem;
				left: 0;
				width: 100%;
				height: 0.2rem;
				background-color: #E83C2D;
			}
		}
	}
	.list_wrap {
		padding: 0 2rem;
	}
	.loadmore {
		height: 3rem;
		text-align: center;
	}
}

</style>
