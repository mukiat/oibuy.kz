<template>
	<view class="kd-content">
		<block v-if="trackerItem">
			<view class="header">
				<view class="content">
					<view class="data-img" v-for="(itemImg, listImg) in trackerItem.img" :key="listImg">
						<image :src="itemImg.goods_img" class="img" v-if="itemImg.goods_img">
					</view>
					<view class="text">{{listData.state ? listData.state : '运单不匹配'}}</view>
				</view>
			</view>
			<view class="footer">
				<scroll-view class="warpper" :scroll-y="true">
					<view class="info">
						<view class="item" v-if="trackerItem.shipping_name">
							<view class="label">国内承运人：</view>
							<view class="value">{{ trackerItem.shipping_name }}</view>
						</view>
						<view class="item">
							<view class="label">运单号：</view>
							<view class="value">{{ trackerItem.invoice_no }}<text :data-text="trackerItem.invoice_no" class="copy" @click="copyText">复制</text></view>
						</view>
					</view>
					<view class="list">
						<view id="result" class="result-list sortup" v-if="traces">
							<view class="item" v-for="(item, index) in traces" :key="index">
								<view class="col1">{{ item.time }}</view>
								<view class="col2"><text></text></view>
								<view class="col3">{{ item.context }}</view>
							</view>
						</view>
					</view>
				</scroll-view>
			</view>
		</block>
		<block v-else>
			<dsc-not-content></dsc-not-content>
		</block>
		
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
	</view>
</template>

<script>
	import dscNotContent from '@/components/dsc-not-content.vue';
	export default {
		data() {
			return {
				delivery_sn:0,
				trackerItem:'',
				listData:'',
				dscLoading:true,
				traces:[]
			}
		},
		components:{
			dscNotContent
		},
		onLoad(e){
			this.delivery_sn = e.delivery_sn
			this.loader();
		},
		methods: {
			loader(){
				let that = this;
				
				uni.request({
					url: this.websiteUrl + '/api/order/tracker_order',
					method: 'GET',
					data: {
						delivery_sn:this.delivery_sn
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: (res) => {
						if (res.data.status === 'success') {
							if(res.data.data.length > 0){
								that.trackerItem = res.data.data[0];
								that.seeMore();
							}
							that.dscLoading = false
						}
					}
				})
			},
			seeMore(){
				let that = this;
				
				uni.request({
					url: this.websiteUrl + '/api/order/tracker',
					method: 'GET',
					data: {
						type:that.trackerItem.shipping_code,
						postid:that.trackerItem.invoice_no,
						order_id:that.trackerItem.order_id
					},
					header: {
						'Content-Type': 'application/json',
						'token': uni.getStorageSync('token'),
						'X-Client-Hash': uni.getStorageSync('client_hash')
					},
					success: (res) => {
						if (res.data.status === 'success') {
							that.listData = res.data.data;
							that.traces = res.data.data.traces.reverse()
						}
					}
				})
			},
			// 复制文本
			copyText(e){
				let data = (e.currentTarget.dataset.text).toString();
				uni.setClipboardData({
					data: data,
					success: function(){
						uni.getClipboardData({
							success:function(){
								uni.showToast({
									title:'复制成功'
								})
							}
						})
					}
				});
			},
		},
	}
</script>

<style scoped>
.kd-content{ height: 100%;}
.header{ background:#fff; height:120upx; border-radius: 20upx; box-shadow: 4upx 0px 16upx 4upx rgba(0,0,0,.1); display: flex; flex-direction: row; align-items: center; justify-content: flex-start;margin: 20upx 10upx; position: fixed; top: 0; left:0; right: 0; z-index: 11;}
.header .left{ margin: 0 20upx; }
.header .content{ flex: 1; display: flex; flex-direction: row; align-items: center; padding: 0 20upx;}
.header .content .data-img{ width: 80upx; height: 80upx; }
.header .content .data-img image{ height: 100%; width: 100%; }
.header .content .text{ margin-left: 20upx; }
.header .right{ display: flex; flex-direction: row; align-items: center; }
.header .right .kefu{ display: flex; flex-direction: column; justify-content: center; align-items: center; }
.header .right .kefu text{ font-size: 20upx; color: #999; }
.header .right .icon-gengduo1{ margin: 0 30upx; }

.footer{ height: calc(100% - 160upx); padding-top: 160upx;}
.footer .warpper{ position: relative; height: 100%; }
.footer .info{ background: #fff; border-radius: 20upx; padding: 20upx; margin: 0 10upx 20upx;}
.footer .info .item{ display: flex; flex-direction: row; justify-content: flex-start; align-items: center; font-size: 28upx; line-height: 2; color: #666; }
.footer .info .item .value{ flex: 1; display: flex; flex-direction: row; justify-content: space-between;}
.footer .info .item .value .copy{ margin-left: 40upx; cursor: pointer; color: #007AFF;}

.footer .list{ background: #fff; border-radius: 20upx 20upx 0 0; margin: 0 10upx;}
.footer .list .result-list{ position: relative;}
.footer .list .result-list .item{ border: 0; font-size: 28upx; display: flex; flex-direction: row; color: #828282;}
.footer .list .result-list .item view{ line-height: 1.5;}
.footer .list .result-list .item .col1{ padding: 20upx; width: 200upx; text-align: center; box-sizing: border-box; font-size: 26upx; font-weight: 700;}
.footer .list .result-list .item .col2{ position: relative; width: 40upx;}
.footer .list .result-list .item .col2 text{ border: 1px solid #e6e6e6; border-radius: 50%; position: absolute; left: 0; top: 50%; margin-top: -.5rem; width: 1rem; height: 1rem; background: #FFF; z-index: 2; color: #e6e6e6; }
.footer .list .result-list .item .col2 text::before{ position: absolute; top: 50%; left: 50%; content: ''; width: 6px; height: 6px; margin-top: -2px; border-left: 1px solid; border-bottom: 1px solid; transform: translate(-50%, 0) rotate(135deg);}
.footer .list .result-list .item .col3{ padding: 20upx; flex: 1; display: flex; align-items: center; position: relative;}
.footer .list .result-list .item .col3::before{ content: ''; position: absolute; top: -2rem; bottom: -2rem; left: -12px; border-left: 1px solid #e6e6e6;}

.footer .list .result-list .item:first-child{ color: #ff7800;}
.footer .list .result-list .item:first-child .col2 text{ border-color: #ff7800; color: #ff7800;}
.footer .list .result-list .item:first-child .col3::before{ top: 50%;}
.footer .list .result-list .item:last-child .col3::before{ bottom: 50%;}
</style>
