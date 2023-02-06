<template>
    <view>
        <web-view :src="url" @message="handleMessage"></web-view>
    </view>
</template>

<script>
	import universal from '@/common/mixins/universal.js';
    export default {
		mixins:[universal],
		data(){
			return{
				url:'',
				type:''
			}
		},
		onLoad(e){
			if(e.type){
				this.type = e.type;
			}

			if(e.isDirect){
				this.url = decodeURIComponent(e.url);
			}else{
				if(this.$isLogin()){
					uni.request({
						url:this.websiteUrl + '/api/user/ecjia-hash',
						method: 'POST',
						data: {},
						header: {
							'Content-Type': 'application/json',
							'token': uni.getStorageSync('token'),
							'X-Client-Hash':uni.getStorageSync('client_hash')
						},
						success: (res) => {
							let data = res.data;
							let url = decodeURIComponent(e.url);
							if(data.status == 'success'){
								if(url.indexOf('?') > 0){
									this.url = url + '&ecjiahash=' + data.data;
								}else{
									this.url = url + '?ecjiahash=' + data.data;
								}
							}else{
								uni.showModal({
									content: this.$t('lang.login_lapse_web_view'),
									confirmText:this.$t('lang.login_lapse_confirm_text'),
									cancelText:this.$t('lang.login_lapse_cancel_text'),
									success:(res)=>{
										if(res.confirm){
											uni.reLaunch({
												url:'/pagesB/login/login?delta=index'
											})
										}else if(res.cancel){
											this.url = url;
										}
									},
									complete:()=>{
										this.$store.commit('toggleHrefType',false)
									}
								})
							}
						}
					})
				}else{
					this.url = decodeURIComponent(e.url);
				}
			}
		},
		onNavigationBarButtonTap(e){
			let user_id = uni.getStorageSync('user_id');
			if(e.type == 'close'){
				let pages = getCurrentPages()
				let page = pages[pages.length - 1];
				let curPages = page.$getAppWebview()
				let children = curPages.children()
				if(children.length===0){
					uni.navigateBack()
				}else{
					children[0].close()
					setTimeout(()=>{
					   uni.navigateBack()
					},0)
				}
			}else if(e.type == 'share'){
				let configData = uni.getStorageSync('configData');
				let url = '';
				if(this.url.indexOf('?') > 0){
					url = this.url + '&parent_id=' + user_id + '&platform=APP';
				}else{
					url = this.url + '?parent_id=' + user_id + '&platform=APP';
				}
				let shareInfo = {
					href:url,
					title:configData.shop_title,
					summary:configData.shop_desc,
					imageUrl:configData.wap_logo
				};
				this.shareInfo(shareInfo)
			}
		},
		onBackPress(e){
			// if(this.type == 'onback'){
			// 	uni.redirectTo({
			// 		url:'/pages/index/index'
			// 	})
			// }
		},
		methods: {
            handleMessage(evt) {
                console.log('接收到的消息：' + JSON.stringify(evt.detail.data));
            }
        },
		watch:{
			url(){
				this.$store.commit('toggleHrefType',false)
			}
		}
	}
</script>

<style>

</style>
