<template>
	<ul :class="['cart_list', type == 1 ? 'disabled_list' : '']">
		<li class="cart_list_item" v-for="(item,index) in list" :key="index" @click="clickHandle({type: 'active', value: item})">
			<div class="cart_head">
				<div class="card_sn">
					<span>Карта：{{item.value_card_sn}}</span>
					<span v-if="item.vc_dis != 1">Жеңілдік：{{item.vc_dis_format}}</span>
				</div>
				<div class="how_much">Құны：{{item.vc_value_money}}₸</div>
			</div>
			<div :class="['cart_body', page == 'checkout' ? 'h_11' : '']">
				<div class="money_and_time">
					<span class="balance">Теңгерім{{currency_format}}<span>{{item.use_card_money}}</span></span>
					<span class="past_due">Аяқтау：{{item.local_end_time}}</span>
				</div>
				<div class="btn_wrap" v-if="page == 'valuecart'">
					<div class="red_btn" @click.stop="clickHandle({type: 'add', value: item})" v-if="item.is_rec == 1 && type == 0">充值</div>
					<div class="status_txt size_14" v-if="item.use_status == 0">Жарамсыз</div>
					<div class="status_txt size_14" v-if="item.use_status == 2">Мерзімі біткен</div>
					<div class="status_txt size_14" v-if="item.use_status == 3">Таусылған</div>
					<div class="cart_btn" @click.stop="clickHandle({type: 'detail', value: item})">Істету дерегі</div>
				</div>
				<div class="disabled_tips" v-if="page == 'checkout' && type == 1">Тауарлар арасында істету шартына келмейтін тауар бар</div>
				<div class="new-store-radio-box" v-if="page == 'checkout' && type == 0 && active == item.vid"><i class="iconfont icon-gou"></i></div>
			</div>
		</li>
	</ul>
</template>

<script>
	export default {
		props: {
			list: {
				type: Array,
				default: () => {
					return []
				}
			},
			type: {
				type: Number,
				default: 0
			},
			active: {
				type: [Number,String],
				default: -1
			},
			page: {
				type: String,
				default: 'valuecart'
			}
		},
		data() {
			return {
				currency_format: window.sessionStorage.getItem('configData').currency_format || '￥'
			}
		},
		methods: {
			clickHandle(res) {
				this.$emit('link', res)
			}
		}
	}
</script>

<style lang="scss" scoped>
	.cart_list {
		.cart_list_item {
			overflow: hidden;
			border: 1px solid #E83C2D;
			border-top: none;
			border-radius: 1rem;
			margin-bottom: 1.2rem;
			.cart_head {
				padding: 1rem;
				color: #fff;
				background-color: #E83C2D;
			}
			.card_sn {
				display: flex;
				justify-content: space-between;
				span:first-child {
					font-size: 1.6rem;
					font-weight: 700;
				}
			}
			.how_much {
				margin-top: 0.5rem;
				font-weight: 700;
			}
			.cart_body {
				display: flex;
				flex-direction: column;
				justify-content: space-between;
				align-items: center;
				height: 15rem;
				text-align: center;
				color: #E83C2D;
			}
			.h_11 {
				position: relative;
				height: 11rem;
				.new-store-radio-box {
					position: absolute;
					right: 0;
					bottom: 0;
					width: 4rem;
					border-bottom: 4rem solid #E83C2D;
					border-left: 4rem solid transparent;
					.iconfont {
						position: absolute;
						right: 0.5rem;
						bottom: -3.7rem;
						font-weight: 700;
						color: #fff;
					}
				}
			}
			.money_and_time {
				display: flex;
				flex-direction: column;
				justify-content: center;
				align-items: center;
				margin-top: 2rem;
				.balance {
					font-size: 1.5rem;
					span {
						font-size: 2rem;
						font-weight: 700;
					}
				}
				.past_due {
					margin-top: 0.5rem;
				}
			}
			.btn_wrap {
				display: flex;
				flex-direction: column;
				align-items: center;
				font-size: 1rem;
				.red_btn {
					padding: 0 3rem;
					height: 2.6rem;
					line-height: 2.6rem;
					border-radius: 0.3rem;
					color: #fff;
					background-color: #E83C2D;
				}
				.cart_btn {
					padding: 0 1rem;
					height: 3.6rem;
					line-height: 3.6rem;
				}
			}
			.disabled_tips  {
				width: 100%;
				height: 2.6rem;
				padding: 0 1rem;
				font-size: 1.2rem;
				text-align: left;
			}
		}
	}
	.disabled_list {
		.cart_list_item {
			border-color: #BABABA;
			.cart_head {
				background-color: #BABABA;
			}
			.cart_body {
				color: #BABABA;
				.disabled_tips {
					color: #BABABA;
				}
			}
		}
	}
</style>
