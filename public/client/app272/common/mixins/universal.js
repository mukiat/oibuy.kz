import share from "@/common/share.js";
import imageRemotePath from '@/common/imageRemotePath.js';

export default {
	data(){
		return {
			regionShow:false,
			regionLoading:false,
			regionData:{
				province:{ id:'', name:'' },
				city:{ id:'', name:'' },
				district:{ id:'', name:'' },
				street:{ id:'', name:'' },
				regionSplic:''
			},
			sharePoster:false
		}
	},
	computed:{
		weappInReview(){
			return uni.getStorageSync('configData') && uni.getStorageSync('configData').weapp_in_review ? uni.getStorageSync('configData').weapp_in_review : ''
		},
		appInReview(){
			return uni.getStorageSync('configData') && uni.getStorageSync('configData').app_in_review ? uni.getStorageSync('configData').app_in_review : ''
		},
		imagePath(){
			return imageRemotePath
		},
		regionSplic:{
			get(){
				return this.regionData.regionSplic ? this.regionData.regionSplic : this.$t('lang.select')
			},
			set(val){
				this.regionData = val;
			}
		},
		getRegionData(){
			return uni.getStorageSync("regionData") ? uni.getStorageSync("regionData") : (uni.getStorageSync("userRegion") ? uni.getStorageSync("userRegion") : this.regionData);
		},
		//是否是审核模式
		controlVersion(){
			let fald = false
			
			// #ifdef APP-PLUS
			let wgtinfo = JSON.parse(uni.getStorageSync('wgtinfo'));
			let version = Number(wgtinfo.version.replace(/\./g, ''));
			
			fald = version == this.appInReview;
			// #endif
			
			// #ifdef MP-WEIXIN
			fald = getApp().globalData.mpVersionCode == this.weappInReview
			// #endif
			
			return !fald
		}
	},
    methods: {
		htmlEmFormat(html){
			if(html != undefined && html){
				return html.replace(/<em>/ig, '<em class="em">');
			}
		},
		/**
         * 修改list或allValuevalue值
         * @param {*} o
         */
        updateText(o) {
            this.$store.dispatch('updateText', o)
        },
		 /**
         * 修改list或allValuevalue值
         * @param {*} o
         */
        updateConText(sName, sValue) {
            let o = {
                attrName: sName,
                newValue: sValue
            }
			this.updateText(o)
        },
		/**
         * 前台组件获取属性值，如果为空返回返回defaultValue
         * @param {*} o 
         */
        getText(o) {
            let inputValue = "";
            if(o.listIndex == undefined){
                inputValue = this.module[o.dataNext][o.attrName]
            }else{
                inputValue = this.module.list[o.listIndex][o.attrName]
            }
            return inputValue == "" || inputValue == "undefined" ? o.defaultValue : inputValue
        },
        //客服
        onChat(goods_id,shop_id){
			if(this.$isLogin()){
				let type = ''
				// #ifdef APP-PLUS
				type = 'app'
				// #endif
				
				// #ifdef MP-WEIXIN
				//type = 'weixin'
				// #endif
				
				this.$store.dispatch('setChat',{
					goods_id:goods_id,
					shop_id:shop_id ? shop_id : 0,
					type:type
				}).then((res)=>{
					if(res.status == 'success'){
						let url = res.data.url;
						if(url){
							let reg = RegExp(/wpa.qq.com/);
							let isUrl = reg.test(url); //判断是否是qq客服
							let platform = uni.getSystemInfoSync().platform;
							
							if(isUrl && platform == 'ios'){
								let index1 = url.indexOf("&uin="); //截取字符串位置1
								let index2 = url.indexOf("&site="); //截取字符串位置2
								let qq = url.substring((index1+5),index2); //客服qq号
								
								plus.runtime.openURL('mqq://im/chat?chat_type=wpa&uin='+ qq +'&version=1&src_type=web');
							}else{
								this.$outerHref(url);
							}
						}else{
							uni.navigateTo({
								url:"/pagesC/kefu/kefu?shop_id=" + res.data.ru_id + '&goods_id=' + res.data.goods_id + '&token='+ res.data.t
							})
						}
					}else{
						uni.showToast({
							title:res.errors.message,
							icon:'none'
						})
					}
				})
			}else{
				uni.showModal({
					content:'请登录会员！',
					success:(res)=>{
						if(res.confirm){
							uni.navigateTo({
								url:'/pagesB/login/login?delta=1'
							})
						}
					}
				})
			}
        },
		shareInfo(info,poster){
			let shareInfo = info
			let that = this
			
			let shareList=[
				{
					icon:"/static/sharemenu/wx.png",
					text:"微信好友",
					provider:'weixin'
				},
				{
					icon:"/static/sharemenu/pyq.png",
					text:"朋友圈",
					provider:'pengyouquan'
				},
				{
					icon:"/static/sharemenu/copy.png",
					text:"复制",
					provider:'copy'
				},
				{
					icon:"/static/sharemenu/more.png",
					text:"更多",
					provider:'more'
				},
			];
			
			if(poster){
				shareList.splice(2,0,{
					icon:"/static/sharemenu/poster.png",
					text:"生成海报",
					provider:'poster'
				})
			}
			
			uni.getProvider({
				service:'share',
				success: (res) => {
					this.shareObj = share(shareInfo,shareList,function(index){
						let shareObj={
							href:shareInfo.href||"",
							title:shareInfo.title||"",
							summary:shareInfo.summary||"",
							imageUrl:shareInfo.imageUrl||"",
							success:(res)=>{
								//console.log("success:" + JSON.stringify(res));
							},
							fail:(err)=>{
								//console.log("fail:" + JSON.stringify(err));
							}
						};
						
						let str = shareList[index].provider;
						switch (str) {
							case 'weixin':
								shareObj.provider="weixin";
								shareObj.scene="WXSceneSession";
								shareObj.type=0;
								shareObj.imageUrl=shareInfo.imageUrl||"";
								uni.share(shareObj);
								break;
							case 'pengyouquan':
								shareObj.provider="weixin";
								shareObj.scene="WXSenceTimeline";
								shareObj.type=0;
								shareObj.imageUrl=shareInfo.imageUrl||"";
								uni.share(shareObj);
								break;
							case 'poster':
								that.sharePoster = true
								break;
							case 'copy':
								uni.setClipboardData({
									data:shareInfo.href,
									complete() {
										uni.showToast({
											title: "已复制到剪贴板"
										})
									}
								})
								break;
							case 'more':
								plus.share.sendWithSystem({
									type:"web",
									title:shareInfo.title||"",
									thumbs:[shareInfo.imageUrl||""],
									href:shareInfo.href||"",
									content: shareInfo.summary||"",
								})
								break;
						};
					})
					
					this.$nextTick(()=>{
						this.shareObj.alphaBg.show();
						this.shareObj.shareMenu.show();
					})
				}
			})
		},
		handleRegionShow() {
			this.regionShow = this.regionShow ? false : true
		},
		//地区弹窗是否显示
		getRegionShow(e){
			this.regionShow = e
		},
		getRegionOptionDate(e){
			this.regionData = e
		}
    },
}