<template>	
	<view>
		<view class="tank">
			<view class="get-more" @click="moreMessage">{{$t('lang.click_view_more')}}</view>
			<scroll-view class="tank-con" scroll-y="true" :style="{height:style.contentViewHeight+'px'}" :scroll-top="scrollTop">
				<view class="user-consult">		
					<view class="user-item" :class="{'user-item-admin':item.user_type == 1 || item.user_type == 'service'}" v-for="(item,index) in list" :key="index" v-if="list.length > 0">
						<block v-if="item.goods_id">
							<!-- 商品详情 -->
							<view class="goods_link">
								<view class="goods_link_left">
									<image :src="kefuLoginData.goods.goods_thumb" mode=""></image>
								</view>
								<view class="goods_link_right">
									<view class="goods_link_right_name twolist-hidden">{{kefuLoginData.goods.goods_name}}</view>
									<view class="goods_link_right_footer">
										<currency-price :price="kefuLoginData.goods.shop_price" :size="18"></currency-price>
										<view class="send_link" @click="sub_send">发送链接</view>
									</view>
								</view>
							</view>
						</block>
						<block v-else>
							<block v-if="item.user_type == 1 ||  item.user_type == 'service'">
								<view class="right"><image :src="item.avatar" class="img"></image></view>
								<view class="left">
									<view class="name">
										<text class="txt">{{item.name}}</text>
										<text class="time">{{item.time}}</text>
									</view>
									<view class="cons-cont">
										<jyf-parser :html="item.message" :tag-style="{video: 'width: 100%;'}" class="text" @ready="allImgReady" @tap="richtextChange(item.message)"></jyf-parser>
									</view>
								</view>
							</block>
							<block v-else>
								<view class="left">
									<view class="name">
										<text class="txt">{{item.name}}</text>
										<text class="time">{{item.time}}</text>
									</view>
									<view class="cons-cont">
										<block v-if="!item.message">
											<view class="goods_link" @click="goodsLink(item.goods.goods_id)">
												<view class="goods_link_left">
													<image :src="item.goods.goods_thumb" mode=""></image>
												</view>
												<view class="goods_link_right">
													<view class="goods_link_right_name twolist-hidden">{{item.goods.goods_name}}</view>
													<view class="goods_link_right_footer">
														<currency-price :price="item.goods.shop_price" :size="18"></currency-price>
													</view>
												</view>
											</view>
										</block>
										<block v-else>
											<jyf-parser :html="item.message" :tag-style="{video: 'width: 100%;'}" class="text" @ready="allImgReady" @tap="richtextChange(item.message)"></jyf-parser>
										</block>
									</view>
								</view>
								<view class="right"><image :src="item.avatar" class="img"></image></view>
							</block>
						</block>
					</view>
				</view>
			</scroll-view>
		</view>
		<view class="kefu-bottom">
			<view class="footer">
				<view class="speak-contcom">
					<view class="iconfont icon-xiaolian" @tap="show"></view>
					<view class="iconfont icon-picture" @click="chooseImage"></view>
				</view>
				<view class="text-input" style="margin-right: 20rpx;">
					<input class="write-input" type="text" name="comment" v-model="comment" confirm-hold="true" confirm-type="发送" autocomplete="off" @confirm="btnSubmit">
				</view>
				<!-- <view class="send-button">
					<view class="send-btn" @click="btnSubmit">{{$t('lang.send')}}</view>
				</view> -->
			</view>
			<emotion @emotion="handleEmotion" :height="200" v-if="showPannel"></emotion>
		</view>
		
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
	</view>	
</template>
  
<script>
	import { mapState } from 'vuex'
	import { pathToBase64, base64ToPath } from '@/common/image-tools/index.js'
	
	import Emotion from '@/components/emotion/index'
	import jyfParser from "@/components/jyf-parser/jyf-parser";
	
	export default {
		data() {
		    return {
				user_type:2,
				goods_id:0,
				store_id:0,
				token:'',
				type:'default',
				default:1,
		        size:10,
		        page:1,
				img:'',
				content:'',
				socketTask: null,
				is_open_socket: false,
				style: {
					pageHeight: 0,
					contentViewHeight: 0,
					footViewHeight: 46,
					mitemHeight: 50,
					consultHeight:0,
				},
				scrollTop: 0,
				comment:'',
				showPannel: false,
				timer:null,
				kefuLoginData:null,
				reg: /\#[\S]{1,3}\;/gi,
				from_id:null,
				dscLoading: true,
				list:[]
		    }			
		},
		components:{
			Emotion,
			jyfParser
		},
		//初始化加载数据
		async onLoad(e){
			const res = uni.getSystemInfoSync();
			
			this.store_id = e.shop_id
			this.goods_id = e.goods_id	
			this.token = e.token
			
			// 高度
			this.style.pageHeight = res.windowHeight - this.style.mitemHeight;
			this.style.contentViewHeight = this.style.pageHeight - this.style.footViewHeight - uni.getSystemInfoSync().statusBarHeight;
			
			// 用户信息
			this.kefuLoginData = await this.$store.dispatch('setKefuLogin',{
			    ru_id: this.store_id,
			    goods_id: this.goods_id,
			    t:this.token,
				type:'app'
			});
			
			//初始化socket
			this.connectSocketInit();
			
			//聊天列表
		    this.chatList();
		},
		// 关闭websocket
		beforeDestroy(){
			this.closeSocket();
		},
		computed: {
		    kefuChatListData:{
		        get(){
		            return this.$store.state.kefu.kefuChatListData
		        },
		        set(val){
		            this.$store.state.kefu.kefuChatListData = val
		        }
		    }
		},
		watch:{
			kefuChatListData(){
				//容器高度
				this.consultH();
				
				this.dscLoading = false;
			}
		},
		methods: {
			richtextHtml(html){
				const regex = new RegExp('<img', 'gi');
				html = html.replace(regex, '<img class="title-img"');
				return html
			},
			connectSocketInit(){
				let that = this
				
				// 创建一个this.socketTask对象【发送、接收、关闭socket都由这个对象操作】
				let url = 'wss://' + that.kefuLoginData.listen_route + '/socket'
				that.socketTask = uni.connectSocket({
					url: url,
					success(data) {
						console.log("websocket连接成功",JSON.stringify(data));
					},
				});

				// 消息的发送和接收必须在正常连接打开中
				that.socketTask.onOpen((res) => {
					that.is_open_socket = true;
					
					let obj = {
						uid:that.kefuLoginData.user.user_id,
						name:that.kefuLoginData.user.user_name,
						avatar:that.kefuLoginData.user.avatar,
						store_id:that.store_id,
						user_type:'customer',
						type:'login',
						origin:uni.getStorageSync('platform')
					}
					
					that.socketTask.send({
						data:JSON.stringify(obj),
						success: (send) => {
							console.log(JSON.stringify(send))
						}
					})
					
					// 客户端定时发送心跳 15 秒
					clearInterval(that.timer);
					that.timer = setInterval(function () {
						that.socketTask.send({
							data:'{"type":"ping"}',
							fail() {
								that.connectSocketInit()
							}
						});
					}, 3000);
				});
				
				// websocket接收消息
				this.socketTask.onMessage(function(res){
					console.log(JSON.stringify(res))
					let info = JSON.parse(res.data);
					console.log(info)
					switch (info.message_type) {
						// 服务端ping客户端
						case 'ping':
							that.socketTask.send({
								data:'{"type":"pong"}'
							});
							return;
						// 有客服登录
						case 'come':
							if (info.uid == that.kefuLoginData.user.user_id) return;
							return;
						//有客服登出
						case 'leave':
							if (info.uid == that.kefuLoginData.user.user_id || info.uid != '') return;
							return;
						//取得客服列表
						case 'init':
							console.log('取得客服列表')
							return;
						//获取到消息
						case 'come_msg':
							that.from_id = info.from_id ? info.from_id : null
							
							// 更新聊天列表
							let message = {
								message:info.message.replace(that.reg, that.emotion),
								avatar:info.avatar,
								name:info.name,
								time:info.time,
								user_type:info.user_type,
								to_user_id:that.from_id,
								status:info.status
							}
							that.list.push(message);
							
							return;
						//待接入消息
						case 'come_wait':
							that.from_id = info.from_id ? info.from_id : null
							
							// 更新聊天列表
							let message2 = {
								message:info.message.replace(that.reg, that.emotion),
								avatar:info.avatar,
								name:info.name,
								time:info.time,
								user_type:info.user_type,
								to_user_id:that.from_id,
								status:info.status
							}
							
							that.list.push(message2);
							
							return;
						//获取被抢客户
						case 'robbed':
							console.log('获取被抢客户')
							return;
						//通知用户已被接入
						case 'user_robbed':
							//接入更新列表
							console.log(info)
							that.list.push(
								{
									message:info.msg,
									avatar:info.avatar,
									name:info.name,
									time:info.time,
									user_type:"service",
									to_user_id:info.from_id ? info.from_id : null,
									status:info.status
								}
							);
							console.log('通知用户已被接入');
							return;
						//用户已下线
						case 'uoffline':
							console.log('用户已下线')
							return;
						//客服已断开
						case 'close_link':
							console.log('客服已断开')
							return;
						//异地登录
						case 'others_login':
							console.log('异地登录')
							return;
						//切换客服
						case 'change_service':  
							console.log('切换客服')
							return;
					}
				})
			},
			// websocket接收消息
			onMessage(){
				this.socketTask.onMessage(function(res){
					console.log("接收消息",res)
				})
			},
			// 关闭websocket【离开这个页面的时候执行关闭】
			closeSocket() {
				let that = this
				that.socketTask.close({
					success(res) {
						that.is_open_socket = false;
						
						clearInterval(that.timer);
						
						console.log("关闭成功", res)
					},
					fail(err) {
						console.log("关闭失败", err)
					}
				})
			},
			//发送消息websocket
			clickRequest(obj) {
				let that = this
				
				if(that.is_open_socket){
					that.socketTask.send({
						data: JSON.stringify(obj),
						async success(res) {
							console.log("消息发送成功");
						},
					});
				}
			},
			// 聊天列表
		    async chatList() {
		        const list = await this.$store.dispatch('setKefuSingleChatList',{
		            user_type:this.user_type,
					type:this.type,
		            store_id:this.store_id,
					page:this.page,
					type:true
		        });
				
				// 组装商品详情信息到聊天列表
				this.list = list.reverse();

				if(this.goods_id > 0){
					this.list.push(this.kefuLoginData.goods);
				}
				
				this.$nextTick(function(){
					this.listMove();
				});
				this.page = this.page + 1
		    },
			// 聊天列表跟随滚动操作
			listMove() {
				let view = uni.createSelectorQuery().in(this).select(".user-consult");
				
				view.fields({
				  size: true
				}, data => {
				  this.scrollTop = data.height;
				  this.dscLoading = false;
				}).exec();
			},
			// 查看更多
			async moreMessage() {
				const list = await this.$store.dispatch('setKefuSingleChatList',{
					page:this.page,
				    user_type:this.user_type,
				    store_id:this.store_id,
					default:this.default,
					type:true
				})
				
				list.forEach(v=>{
					this.list.unshift(v);
				})
				
				this.page = this.page + 1;
				
				this.$nextTick(function(){
					this.scrollTop = 0;
				})
			},
			// 发送消息
			btnSubmit() {
				let that = this
				if(that.comment == ''|| that.comment == null){
					uni.showToast({
						title:'发送消息不能为空',
						icon:'none'
					})
					return false
				}
				
				uni.showLoading({title:'发送中...'});
				
				//发送消息
				let obj = {
					msg:that.comment.replace(that.reg, that.emotion),
					type:'sendmsg',
					to_id:that.kefuLoginData.services_id,
					avatar:that.kefuLoginData.user.avatar,
					goods_id:that.goods_id,
					store_id:that.store_id,
					origin:uni.getStorageSync('platform')
				}
				
				that.clickRequest(obj); 
				
				// 更新聊天列表
				let message = {
					message:that.comment.replace(that.reg, that.emotion),
					avatar:that.kefuLoginData.user.avatar,
					name:that.kefuLoginData.user.nick_name,
					time:that.$formatDateTime(that.$getCurDate()-(3600 * 8)),
					user_type:2,
					to_user_id:that.from_id,
					status:0
				}
				that.list.push(message);
				
				//清空输入框内
				uni.hideLoading();
				that.comment = ''
				
				//关闭表情窗
				this.showPannel = false;
				this.setScrollH();
				this.$nextTick(function(){
					this.listMove();
				})
			},
			// 发送商品链接
			sub_send(){
				let that = this;
				
				uni.showLoading({title:'发送中...'});
				
				let obj = {
					msg:that.kefuLoginData.goods.url,
					goods:that.kefuLoginData.goods,
					type:'sendmsg',
					to_id:that.kefuLoginData.services_id,
					avatar:that.kefuLoginData.user.avatar,
					goods_id:that.goods_id,
					store_id:that.store_id,
					origin:uni.getStorageSync('platform')
				}
				
				that.clickRequest(obj);
				
				// 更新聊天列表
				let message = {
					message:'',
					goods:that.kefuLoginData.goods,
					avatar:that.kefuLoginData.user.avatar,
					name:that.kefuLoginData.user.nick_name,
					time:that.$formatDateTime(that.$getCurDate()-(3600 * 8)),
					user_type:2,
					to_user_id:that.from_id,
					status:0
				}
				
				that.list.push(message);
				
				uni.hideLoading();
				
				this.setScrollH();
				
				this.$nextTick(function(){
					this.listMove();
				})
			},
			sendMsg(msg){
				let that = this;
				uni.showLoading({title:'发送中...'});
				
				//发送消息
				let obj = {
					msg:msg,
					type:'sendmsg',
					to_id:that.kefuLoginData.services_id,
					avatar:that.kefuLoginData.user.avatar,
					goods_id:that.goods_id,
					store_id:that.store_id,
					origin:uni.getStorageSync('platform')
				}
				
				that.clickRequest(obj);
				
				// 更新聊天列表
				let message = {
					message:msg,
					goods:that.kefuLoginData.goods,
					avatar:that.kefuLoginData.user.avatar,
					name:that.kefuLoginData.user.nick_name,
					time:that.$formatDateTime(that.$getCurDate()-(3600 * 8)),
					user_type:2,
					to_user_id:that.from_id,
					status:0
				}
				
				that.list.push(message);
				
				uni.hideLoading();
				
				that.comment = '';
				
				this.$nextTick(function(){
					this.listMove();
				})
			},
			// 发送图片加载完成
			allImgReady() {
				this.$nextTick(function(){
					this.listMove();
				})
			},
			//发送图片
			chooseImage(){
				uni.showToast({
					title:'请选择2MB以下的图片',
					icon:'none'
				});
				
				uni.chooseImage({
					count: 1,
					sizeType: ['original', 'compressed'],
					sourceType: ['album', 'camera'],
					success:(res)=>{
						pathToBase64(res.tempFilePaths[0]).then(base64 => {
							this.$store.dispatch('setSendImage',{
								file:{
									content:base64
								},
							}).then(data=>{
								this.img = '<img class="title-img" src="'+ data.data[0] + '" />'
								this.sendMsg(this.img);
							})
						}).catch(error => {
							console.error(error,5);
						});
					}
				})
			},
			//展示表情
			show() {
				this.showPannel = !this.showPannel;
				this.setScrollH();
			},
			emotion(res) {
				let word = res.replace(/\#|\;/gi, '')
				const list = ['微笑', '撇嘴', '色', '发呆', '得意', '流泪', '害羞', '闭嘴', '睡', '大哭', '尴尬', '发怒', '调皮', '呲牙', '惊讶', '难过', '酷',
					'冷汗', '抓狂', '吐', '偷笑', '可爱', '白眼', '傲慢', '饥饿', '困', '惊恐', '流汗', '憨笑', '大兵', '奋斗', '咒骂', '疑问', '嘘', '晕', '折磨', '衰',
					'骷髅', '敲打', '再见', '擦汗', '抠鼻', '鼓掌', '糗大了', '坏笑', '左哼哼', '右哼哼', '哈欠', '鄙视', '委屈', '快哭了', '阴险', '亲亲', '吓', '可怜',
					'菜刀', '西瓜', '啤酒', '篮球', '乒乓', '咖啡', '饭', '猪头', '玫瑰', '凋谢', '示爱', '爱心', '心碎', '蛋糕', '闪电', '炸弹', '刀', '足球', '瓢虫',
					'便便', '月亮', '太阳', '礼物', '拥抱', '强', '弱', '握手', '胜利', '抱拳', '勾引', '拳头', '差劲', '爱你', 'NO', 'OK', '爱情', '飞吻', '跳跳',
					'发抖', '怄火', '转圈', '磕头', '回头', '跳绳', '挥手', '激动', '街舞', '献吻', '左太极', '右太极'
				]
				let index = list.indexOf(word)
				return `<img src="https://res.wx.qq.com/mpres/htmledition/images/icon/emotion/${index}.gif" align="middle">`
			},
			handleEmotion(i) {
				this.comment += i
			},
			// 设置高度 用emit辅助
			setScrollH(){
				let that = this
				var query = uni.createSelectorQuery();
				let footh = query.select('.kefu-bottom');
				const res = uni.getSystemInfoSync();
				that.$nextTick(function(){
					footh.fields({
						size: true
					}, data => {
						footh = data.height;
						that.style.contentViewHeight = that.style.pageHeight - footh; //像素
					}).exec();
				})	
			},
			consultH(){
				let that = this
				var query = uni.createSelectorQuery();
				let footh = query.select('.user-consult');
				that.$nextTick(function(){
					footh.fields({
						size: true
					}, data => {
						that.style.consultHeight = data.height;
						
						if(data.height > that.style.contentViewHeight){
							that.scrollTop = data.height - that.style.contentViewHeight
							console.log(that.scrollTop)
						}
					}).exec();
				})	
			},
			richtextChange(obj){
				let arr = [];
				var srcReg = /src=[\'\"]?([^\'\"]*)[\'\"]?/i;
				
				if(obj.match(srcReg)){
					arr.push(obj.match(srcReg)[1])
					
					uni.previewImage({
						current:1,
						urls:arr
					})
				}
			},
			goodsLink(id){
				uni.reLaunch({
					url:'/pagesC/goodsDetail/goodsDetail?id='+id
				})
			}
		}
	}
</script>

<style lang="scss">
.tank{}
.tank .get-more{ width: 100%; padding: 30upx 0; color: #00a2d4; text-align: center; font-size: 25upx;}

.tank-con{}
.user-consult{ padding: 0 20upx 30upx;}
.user-consult .user-item{ display: flex; flex-direction: row; justify-content: flex-end; align-items: center; margin-bottom: 30upx;}
.user-consult .user-item:last-child{ margin-bottom: 0;}
.user-consult .user-item .left{ flex: 1; display: flex; flex-direction: column; justify-content: flex-end; margin-right: 20upx;}
.user-consult .user-item .left .name{ display: flex; flex-direction: row; align-items: center;justify-content: flex-end;}
.user-consult .user-item .left .name .txt{ font-size: 28upx; color: #333; font-weight: bold; margin-right: 10upx;}
.user-consult .user-item .left .name .time{ font-size: 25upx; color: #999;}
.user-consult .user-item .left .cons-cont{ display: flex; justify-content: flex-end;}
.user-consult .user-item .left .cons-cont .text{ padding: 20upx 20upx; color: #333333; border-radius: 10upx; background: #e7e8ef; display: inline-block; font-size: 26upx;}
.user-consult .user-item .right{ width: 80upx; height: 80upx; border-radius: 50%; overflow: hidden;}

.user-consult .user-item-admin{ justify-content: flex-start; }
.user-consult .user-item-admin .left{ margin: 0 0 0 20upx; flex: 1;}
.user-consult .user-item-admin .left .cons-cont{ justify-content: flex-start;}
.user-consult .user-item-admin .left .cons-cont .text{ background: #f1cdcea3;}

.kefu-bottom{ position: fixed; left: 0; right: 0; bottom: 0; box-sizing: border-box; background: #e8e8e8; border-top: 1px solid #efefef;padding-bottom: env(safe-area-inset-bottom);}
.kefu-bottom .footer{ display: flex; flex-direction: row; align-items: center; }
.kefu-bottom .speak-contcom{ display: flex; flex-direction: row; margin-right: 25upx;}
.kefu-bottom .speak-contcom .iconfont{ font-size: 50upx; margin-left: 25upx;}
.kefu-bottom .text-input{ background: #FFFFFF; height: 70upx; flex: 1; display: flex; align-items: center; margin-right: 20rpx;}
.kefu-bottom .text-input .write-input{ width: 100%; padding: 0 20upx; box-sizing: border-box; }
.kefu-bottom .send-button{ margin-left: 25upx; }
.kefu-bottom .send-button .send-btn{ background: #00a2d4; color: #FFFFFF; height: 70upx; line-height: 70upx; padding: 0 20upx; border-radius: 10upx; margin-right: 25upx; }

rich-text{
	.new_message_list{
		display: flex;
		.title-img{
			max-width: 180upx;
			max-height: 180upx;
		}
		.left_goods_info{
			margin-left: 20upx;
			word-wrap: break-word;
			white-space: normal;
			word-break: break-all;
			span{
				margin-top: 20upx;
			}
		}
	}
}

.goods_link{
	margin: 10upx;
	padding: 20upx;
	background-color: #E7E8EF;
	display: flex;
	border-radius: 10upx;
	width: 100%;
	
	.goods_link_left{
		line-height: 0;
		font-size: 0;
		
		image{
			max-width: 180upx;
			max-height: 180upx;
		}
	}
	
	.goods_link_right{
		flex: 1;
		margin-left: 20upx;
		display:flex;
		flex-flow: column;
		justify-content: space-between;
		
		.goods_link_right_footer{
			display: flex;
			justify-content: space-between;
		}
		
		.send_link{
			padding: 2rpx 30rpx;
			background-color: #ff0000;
			color: #fff;
			border-radius: 30upx;
		}
	}
}
</style>
