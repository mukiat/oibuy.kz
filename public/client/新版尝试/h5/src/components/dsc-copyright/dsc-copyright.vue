<template>
	<div class="copyright_container" v-if="configData">
		<img class="copyright_img" :src="configData.copyright_img" v-if="configData.copyright_img" />
		<span class="copyright_text" v-if="configData.copyright_text_mobile">{{configData.copyright_text_mobile}}</span>
	</div>
</template>

<script>
	export default {
		name: "dsc-copyright",
		data() {
			return {
				copyrightInfo: {},
				configData: JSON.parse(window.sessionStorage.getItem('configData'))
			};
		},
		created() {
			if (!this.configData) this.shopConfig();
		},
		methods: {
			shopConfig() {
				this.$http.get(`${window.ROOT_URL}api/shop/config`).then(({
					data: {
						data
					}
				}) => {
					this.configData = data;
				})
			},
		}
	}
</script>

<style lang="scss">
	.copyright_container {
		display: flex;
		flex-direction: column;
		align-items: center;
		padding: 1rem 0;

		.copyright_img {
			height: 2rem;
		}

		.copyright_text {
			width: 80%;
			font-size: 1.2rem;
			color: #999;
			margin-top: 0.8rem;
			text-align: center;
			line-height: 1.5;
		}
	}
	
	#app .van-loading {
		margin: 0 auto;
	}
</style>
