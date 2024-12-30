<template>
	<div>
		<div class="service">
			<div class="service_item" @click="changeNav(1)" >
				<img :src="consult.custom_jump_logo" mode="widthFix" class="image">
				<span></span>
				<span></span>
			</div>
			<div class="service_item" @click="changeNav(2)">
				<img :src="consult.kefu_logo" mode="widthFix" class="image">
			</div>
			<div class="service_item" @click="changeNav(3)">
				<img src="../../assets/img/service/03-share.png" mode="widthFix" class="image">
			</div>
		</div> 
	</div>
</template>

<script>
	import { mapState } from 'vuex'
	import formProcessing from '@/mixins/form-processing'
	export default {
		mixins: [formProcessing],
		props:{
			consult:{
				type:Object,
				default:''
			}
		},
		data() {
			return {
				curIndex:0,
			}
		},
		methods:{
			changeNav(o){
				if(o == 3){
					this.$emit('flaghanlde', true)
				}else if(o == 1){
					window.location.href = this.consult.custom_jump_url
				}else{
					if(this.consult.consult_kefu_type == 1){
						window.location.href = this.consult.consult_kefu_url
					}else{
						this.onChat(0,0)
					}
				}
			}
		},
	}
</script>

<style>
.service{
	position: fixed;
	bottom: 18%;
	right: 10px;
	color: #6e6d6b;
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	/* box-shadow: 1px 1px 2px 0 rgba(0,0,0,.05); */
	z-index: 15;
	padding-bottom: env(safe-area-inset-bottom);
}
.service_item{
	flex: 1;
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	font-size: 13px;
	position: relative;
}

.service_item .image{
	width: 58px;
	height: 58px;
}
.service_item {
		position: relative;
		width: 58px;
		height: 58px;
	}

	.service_item img {
		width: 58px;
		height: 58px;
		z-index: 0;
	}

	@keyframes living {
		0% {
			transform: scale(1);
			opacity: 0.5;
		}

		50% {
			transform: scale(1.5);
			opacity: 0;
			/*圆形放大的同时，透明度逐渐减小为0*/
		}

		100% {
			transform: scale(1);
			opacity: 0.5;
		}
	}

	.service_item span {
		position: absolute;
		width: 50px;
		height: 50px;
		left: 4px;
		bottom: 7px;
		background: red;
		border-radius: 50%;
		-webkit-animation: living 3s linear infinite;
		z-index: -1;
	}

	.service_item span:nth-child(2) {
		-webkit-animation-delay: 1.5s;
		/*第二个span动画延迟1.5秒*/
	}
</style>
