<template>
	<view class="currency-price" v-if="priceConfig">
		<slot></slot>
		<view class="currency-price-warp" :style="[priceSize, colorStyle, delStyle, customStyle]">
			<text class="em" v-if="priceConfig.is_show_currency_format > 0"
				:style="[currencySize,currencyStyle]">{{ priceConfig.currency_format }}</text>
			<block v-if="price">
				<text v-if="priceStyleConfig == 3">
					<text>{{priceFormat.price_format_zhu}}.</text>
					<text :style="[currencySize]">{{ priceFormat.price_format_ci }}</text>
				</text>
				<text v-else>{{ priceFormat }}</text>
			</block>
			<text v-else>0.00</text>
		</view>
		<view class="del" v-if="delPrice" :style="[delPriceStyle]">
			<text class="em" v-if="priceConfig.is_show_currency_format > 0">{{ priceConfig.currency_format }}</text>
			<text>{{ delPrice }}</text>
		</view>
	</view>
</template>

<script>
	/**
	 * #price:价格参数不带格式化价格(字符串/数字)
	 * #color:价格颜色默认红色(字符串)
	 * #size:价格字体大小(数字),currency(￥)为字体size的70%
	 * #del:价格是否划横线(text-decoration:line-through)
	 * #style:价格自定义样式
	 * #currencyStyle:currency(￥)自定义样式
	 * #
	 */
	export default {
		name: 'currency-price',
		props: {
			price: {
				type: [Number, String],
				default: 0
			},
			color: {
				type: String,
				default: '#F20E28'
			},
			size: {
				type: Number,
				default: 15
			},
			del: {
				type: Boolean,
				default: false
			},
			delPrice: {
				type: [Number, String],
				default: 0
			},
			delColor: {
				type: String,
				default: '#999'
			},
			delSize: {
				type: Number,
				default: 12
			},
			customStyle: {
				type: String,
				default: ''
			},
			currencyStyle: {
				type: String,
				default: ''
			}
		},
		data() {
			return {}
		},
		computed: {
			priceConfig() {
				let data = uni.getStorageSync('configData');
				let object = {
					is_show_currency_format: data.is_show_currency_format ? data.is_show_currency_format : 1,
					currency_format: data.currency_format ? data.currency_format : '¥',
					price_format: data.price_format ? data.price_format : 0,
					price_style: data.price_style ? data.price_style : 0
				}

				return object
			},
			priceClass() {
				return 'currency-price-' + this.priceConfig.price_style
			},
			priceStyleConfig() {
				return this.priceConfig.price_style
			},
			priceFormat() {
				let price, arr = [];
				switch (parseInt(this.priceConfig.price_style)) {
					case 1:
						price = this.price
						break
					case 2:
						price = this.price
						break
					case 3:
						if (this.price !== null) {
							this.price = this.price.toString().indexOf('.') > -1 ? this.price : this.price.toFixed(2);
							arr = this.price.split('.');
							price = {
								price_format_zhu: arr[0],
								price_format_ci: arr[1]
							}
						} else {
							price = this.price
						}
						break
					case 4:
						price = this.price
						break
				}
				return price
			},
			priceSize() {
				return {
					"font-size": this.size + "px",
					"font-weight": this.priceConfig.price_style == 1 ? "500" : "700"
				}
			},
			currencySize() {
				if (this.priceConfig.price_style == 1 || this.priceConfig.price_style == 2) return false
				return {
					"font-size": this.size * .7 + "px"
				}
			},
			delPriceStyle() {
				return {
					"font-size": this.delSize + "px",
					"color": this.delColor
				}
			},
			colorStyle() {
				return {
					color: this.color
				}
			},
			delStyle() {
				if (!this.del) return
				return {
					"text-decoration": "line-through",
					"color": "#888",
					"font-size": this.size * .7 + 'px',
					"font-weight": "normal"
				}
			}
		},
		mounted() {

		},
		methods: {

		}
	}
</script>

<style>
	.currency-price {
		line-height: normal;
	}

	.currency-price .em {
		margin-right: 1px;
		font-weight: normal;
	}

	.currency-price-warp {
		display: inline-block;
		line-height: normal;
	}

	.currency-price .del {
		vertical-align: text-bottom;
		margin: 0 0 0 8px;
		line-height: normal;
		text-decoration: line-through;
		display: inline-block;
	}
</style>
