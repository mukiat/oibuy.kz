<template>
    <viwe class="con crowd-topic">
		<view class="community-box">
			<view class="community-box-con">
		        <view class="com-textarea">
					<textarea name="message" v-model="message" class="text-area1" maxlength="140" :placeholder="$t('lang.crowdfunding_topic_message')"></textarea>
					<text>{{ length }}/140</text>
		        </view>
		    </view>
		    <view class="btn-bar">
		        <view class="btn btn-white" @click="onClose">{{$t('lang.cancel')}}</view>
		        <view class="btn btn-red" @click="onTopic">{{$t('lang.confirm')}}</view>
		    </view>
		</view>
    </viwe>
</template>
<script>
    export default {
        name: "drp-category",
        data() {
            return {
                routerName:'crowd_funding',
                message:'',
                remnant:140,
				id:0,
				topic_id:0,
            };
        },
        //初始化加载数据
        onLoad(e){
			this.id = e.id			
			this.topic_id = e.topic_id
        },
		computed:{
			length(){
				let length = 140
			
				length = length - this.message.length
			
				return length
			}
		},
        methods: {
            onTopic(){
                if(this.message == ''){
					uni.showToast({
						title: this.$t('lang.topic_message_not_null'),
						icon:'none'
					})
                }else{
                    this.$store.dispatch('setCrowdfundingMyTopic',{
                        id:this.id,
                        topic_id:this.topic_id,
                        content:this.message
                    }).then(res=>{
						uni.showToast({
							title: this.$t('lang.successful_topic_release'),
							icon:'none'
						})
                        uni.navigateTo({
							url:'/pagesA/crowdfunding/detail/detail?id='+this.id
						})
                    })
                }
            },
            onClose(){
                this.message = ''
            }
        }
    };
</script>

<style scoped>
	.community-box{}
	.community-box .selects{ background: #FFFFFF; padding: 20upx; display: flex; flex-direction: row; }
	.community-box .community-box-con{ background:#FFFFFF; }
	.community-box .community-box-con .com-input-title{ padding: 20upx; border-bottom: 2upx solid #F6F6F9;}
	.community-box .community-box-con .com-textarea{ padding: 20upx; }
	.community-box .community-box-con .com-textarea .text-area1{ width: 100%;}
	.community-box .community-box-con .com-textarea text{ text-align: right; display: block; color: #999; }
	.btn-bar .btn{ margin: 30upx 20upx;}
</style>
