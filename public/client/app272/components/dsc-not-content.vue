<template>
	<view class="not-content">
		<view class="notimg"><image :src="image" class="img"></image></view>
		<block v-if="isSpan">
			<view class="cont">{{$t('lang.not_cont_prompt')}}</view>
		</block>
		<block v-else>
			<view class="cont">
				<slot name="spanCon"></slot>
			</view>
		</block>
	</view>
</template>

<script>
	export default {
		props:{
            isSpan:{
                type:Boolean,
                default:true
            }
        },
		data() {
			return {
				
			};
		},
		computed:{
			image(){
				return this.websiteUrl + '/img/no_content.png'
			}
		}
	}
</script>

<style>
.not-content{ width: 100%; padding: 50px 0; display: flex; justify-content: center; flex-direction: column;}
.not-content .notimg{ width: 144px; height: 124px; margin: 0 auto 20px;}
.not-content .cont { text-align: center; color: #999999; }
</style>
