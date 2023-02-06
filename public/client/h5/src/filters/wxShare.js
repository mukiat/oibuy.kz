import axios from 'axios'
import store from '../store/index'
import qs from 'qs'

export default {
	initConfig(url){
		if (!/micromessenger/i.test(navigator.userAgent)) {
			return
	    }

	    //如果是 iOS 设备，则使用第一次进入App时的 URL 去请求 wxConfig，不然的话会导致 iOS 中分享的链接不对
	    if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) {

	    }

		axios.post(`${window.ROOT_URL}api/wechat/jssdk`,qs.stringify({
	  		url:url
	  	})).then(({ data })=>{
	  		if(data.status == 'success'){
	  			wx.config(data.data)
	  		}
	  	})
	},
	share({title, desc, link, imgUrl} = {}){
		if (!/micromessenger/i.test(navigator.userAgent)) {
			return
	    }

	    wx.ready(()=>{
		    wx.updateAppMessageShareData({
		        title: title, // 分享标题
		        desc: desc, // 分享描述
		        link: link, // 分享链接
		        imgUrl: imgUrl, // 分享图标
		        success: function() {

		        },
		        cancel: function() {
		        }
		    })
		    wx.updateTimelineShareData({
		        title: title, // 分享标题
		        desc: desc, // 分享描述
		        link: link, // 分享链接
		        imgUrl: imgUrl, // 分享图标
		        success: function(res) {
		        },
		        cancel: function() {
		        }
		    })
		})

		wx.error(function(err) {
		  console.log(JSON.stringify(err))
		})
	}
}
