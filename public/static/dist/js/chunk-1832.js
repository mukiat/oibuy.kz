(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-1832"],{2662:function(t,o,i){},"36ea":function(t,o,i){"use strict";i.r(o);var e,s,a=function(){var t=this,o=t.$createElement,e=t._self._c||o;return e("div",{directives:[{name:"waterfall-lower",rawName:"v-waterfall-lower",value:t.loadMores,expression:"loadMores"}],staticClass:"video-page video_list_container",attrs:{"waterfall-disabled":"disabled","waterfall-offset":"300"}},[e("section",[e("div",{class:["grid"==t.mode?"video_list_grid":"video_list"]},[t.list.length>0?t._l(t.list,function(o,s){return e("div",{key:s,staticClass:"video_item bgc_fff",on:{click:function(o){t.showPopup(s)}}},[o.goods_thumb?e("img",{staticClass:"video_poster",attrs:{src:"grid"===t.mode?o.goods_thumb:o.goods_img}}):e("img",{staticClass:"video_poster",attrs:{src:i("d9e6")}}),t._m(0,!0),e("div",{staticClass:"video_info flex_box fd_column jc_sb"},[e("p",{staticClass:"text_2 size_15 color_333 weight_700"},[t._v(t._s(o.goods_name))]),e("div",{staticClass:"video_user_info flex_box jc_sb ai_center"},[e("div",{staticClass:"video_info_left flex_box ai_center"},[e("img",{staticClass:"video_upic",attrs:{src:o.logo_thumb}}),e("span",{staticClass:"video_uname size_12 color_666"},[t._v(t._s(o.shop_name))])]),e("div",{staticClass:"video_info_right"},[e("i",{staticClass:"iconfont icon-find-liulan-alt color_ccc"}),e("span",{staticClass:"size_12 color_ccc"},[t._v(t._s(o.look_num?o.look_num:"0"))])])])])])}):[e("NotCont")]],2),t.footerCont?e("div",{staticClass:"footer-cont"},[t._v(t._s(t.$t("lang.no_more")))]):t._e(),t.loading?[e("van-loading",{attrs:{type:"spinner",color:"black"}})]:t._e()],2),t.popupInfo.goods_video?e("van-popup",{staticClass:"video-popup",attrs:{position:"right"},model:{value:t.popupShow,callback:function(o){t.popupShow=o},expression:"popupShow"}},[e("div",{staticClass:"video"},[e("video",{attrs:{src:t.popupInfo.goods_video,id:"movie",loop:""}})]),e("div",{staticClass:"close",on:{click:t.hidePopup}}),e("div",{staticClass:"bottom"},[e("router-link",{staticClass:"goodsinfo",attrs:{to:{name:"goods",params:{id:t.popupInfo.goods_id}}}},[e("div",{staticClass:"img"},[t.popupInfo.goods_thumb?e("img",{attrs:{src:t.popupInfo.goods_thumb}}):e("img",{attrs:{src:i("d9e6")}})]),e("div",{staticClass:"text"},[e("h3",[t._v(t._s(t.popupInfo.goods_name))]),e("p",{domProps:{innerHTML:t._s(t.popupInfo.shop_price_formated)}})])])],1),e("div",{staticClass:"fab"},[e("div",{staticClass:"fab-item",on:{click:function(o){t.collection(t.popupInfo.goods_id)}}},[e("div",{staticClass:"fab-image"},[e("van-icon",{attrs:{name:1==t.popupInfo.is_collect?"like":"like-o",size:"2rem"}})],1),e("span",[t._v(t._s(t.popupInfo.user_collect))])]),e("router-link",{staticClass:"fab-item",attrs:{to:{name:"goodsComment",params:{id:t.popupInfo.goods_id}}}},[e("div",{staticClass:"fab-image"},[e("i",{staticClass:"iconfont icon-message1"})]),e("span",[t._v(t._s(t.popupInfo.comment_num))])]),e("div",{staticClass:"fab-item"},[e("div",{staticClass:"fab-image",on:{click:t.onGoodsShare}},[e("i",{staticClass:"iconfont icon-share"})])])],1)]):t._e(),e("van-popup",{staticClass:"shareImg",attrs:{"overlay-class":"shareImg-overlay"},model:{value:t.shareImgShow,callback:function(o){t.shareImgShow=o},expression:"shareImgShow"}},[t.shareImg?e("img",{staticClass:"img",attrs:{src:t.shareImg}}):e("span",[t._v(t._s(t.$t("lang.error_generating_image")))])]),e("DscLoading",{attrs:{dscLoading:t.dscLoading}}),t._m(1)],1)},n=[function(){var t=this,o=t.$createElement,i=t._self._c||o;return i("div",{staticClass:"video_duration size_13 color_fff flex_box jc_center ai_center"},[i("i",{staticClass:"iconfont icon-play1"})])},function(){var t=this,o=t.$createElement,i=t._self._c||o;return i("video",{staticStyle:{display:"none"},attrs:{controls:"controls",name:"media",id:"divVideo"}},[i("source",{attrs:{type:"video/mp4"}})])}],u=(i("96cf"),i("cb0c")),c=(i("c5f6"),i("f210")),r=i("88d8"),d=(i("c3a6"),i("ad06")),l=(i("e7e5"),i("d399")),p=(i("e17f"),i("2241")),f=(i("ac1e"),i("543e")),m=(i("7f7f"),i("8a58"),i("e41f")),h=(i("d49c"),i("5487")),g=i("4328"),v=i.n(g),_=i("42d1"),C=i("a454"),b=i("6f38"),w=i("09d6"),I=0,y=[],L=0,x={name:"videoList",mixins:[w["a"]],data:function(){return{placeholder:this.$t("lang.search_goods"),disabled:!1,keyword:"",mode:"large",list:[],durationArr:[],page:1,size:10,footerCont:!1,loading:!1,app:!1,dscLoading:!0,popupShow:!1,popupInfo:{},isWx:!1,shareImg:"",shareImgShow:!1}},directives:{WaterfallLower:Object(h["a"])("lower")},components:(e={DscLoading:_["a"],NotCont:b["a"]},Object(r["a"])(e,m["a"].name,m["a"]),Object(r["a"])(e,f["a"].name,f["a"]),Object(r["a"])(e,p["a"].name,p["a"]),Object(r["a"])(e,l["a"].name,l["a"]),Object(r["a"])(e,d["a"].name,d["a"]),e),computed:{isLogin:function(){return null!=localStorage.getItem("token")}},created:function(){this.loadList(),w["a"].isWeixinBrowser()?this.isWx=!0:this.isWx=!1},methods:{initVideoElement:function(){document.getElementById("divVideo").src=y[L].goods_video,s=setInterval(this.countVideo,100)},countVideo:function(){if(4==document.getElementById("divVideo").readyState){I=parseInt(document.getElementById("divVideo").duration);var t=Math.floor(I/60%60)<10?"0"+Math.floor(I/60%60):Math.floor(I/60%60),o=Math.floor(I%60)<10?"0"+Math.floor(I%60):Math.floor(I%60);I="".concat(t,":").concat(o),this.durationArr=Object(c["a"])(this.durationArr).concat([I]),clearInterval(s),L++,L<y.length&&this.initVideoElement()}},resetData:function(){I=0,y=[],L=0},loadList:function(t){var o=this;t&&(this.page=t,this.size=10*Number(t)),this.$http.post("".concat(window.ROOT_URL,"api/goods/goodsvideo"),v.a.stringify({size:this.size,page:this.page,sort:"goods_id",order:"desc"})).then(function(i){var e=i.data;o.list=t?e.data:o.list.concat(e.data),o.list.length>=4?o.mode="grid":o.mode="large",e.data.length>0&&o.$nextTick(function(){this.resetData(),y=e.data,this.initVideoElement()})})},loadMores:function(){var t=this;setTimeout(function(){t.disabled=!0,t.page*t.size==t.list.length&&(t.page++,t.loadList())},200)},showPopup:function(t){var o=this.list[t];this.popupInfo=o,this.popupShow=!0,this.videoPlay(),this.collectionNumber(),o.goods_id&&this.lookVideo(o.goods_id,t)},lookVideo:function(){var t=Object(u["a"])(regeneratorRuntime.mark(function t(o,i){var e,s,a,n;return regeneratorRuntime.wrap(function(t){while(1)switch(t.prev=t.next){case 0:return t.next=2,this.$http.get("".concat(window.ROOT_URL,"api/goods/videolooknum"),{params:{goods_id:o}});case 2:if(e=t.sent,s=e.data,a=s.status,n=s.data,"success"==a){t.next=8;break}return t.abrupt("return");case 8:this.list[i].look_num=n;case 9:case"end":return t.stop()}},t,this)}));return function(o,i){return t.apply(this,arguments)}}(),hidePopup:function(){this.popupShow=!1,this.videoPause()},collectionNumber:function(){var t=this;this.$http.get("".concat(window.ROOT_URL,"api/collect/collectnum"),{params:{goods_id:this.popupInfo.goods_id}}).then(function(o){var i=o.data;t.popupInfo.user_collect=i.data})},collection:function(t){var o=this;if(this.isLogin)this.$http.post("".concat(window.ROOT_URL,"api/collect/collectgoods"),v.a.stringify({goods_id:this.popupInfo.goods_id,status:this.popupInfo.is_collect})).then(function(t){var i=t.data;0==i.data.error&&(Object(l["a"])(i.data.msg),o.popupInfo.is_collect=!o.popupInfo.is_collect,o.collectionNumber())});else{var i=this.$t("lang.fill_in_user_collect_goods");this.notLogin(i)}},notLogin:function(t){var o=this,i=window.location.href;p["a"].confirm({message:t,className:"text-center"}).then(function(){o.$router.push({name:"login",query:{redirect:{name:"videoList",query:{type:2},url:i}}})}).catch(function(){})},videoPlay:function(){setTimeout(function(){var t=document.getElementById("movie");t.play()},300)},videoPause:function(){var t=document.getElementById("movie");t.pause()},commentHandle:function(t){this.$router.push({name:"goodsComment",id:t})},onGoodsShare:function(){var t=this;if(this.isLogin){l["a"].loading({duration:0,mask:!0,forbidClick:!0,message:this.$t("lang.loading")});var o=this.popupInfo.shop_price_formated;this.$store.dispatch("setGoodsShare",{goods_id:this.popupInfo.goods_id,price:o,share_type:this.popupInfo.is_distribution||0}).then(function(o){"success"==o.status&&(t.shareImg=o.data,t.shareImgShow=!0,l["a"].clear())})}else{var i=this.$t("lang.login_user_not");this.notLogin(i)}}},watch:{list:function(){this.dscLoading=!1,this.page*this.size==this.list.length?(this.disabled=!1,this.loading=!0):(this.loading=!1,this.footerCont=this.page>1),this.list=C["a"].trimSpace(this.list)}}},S=x,A=(i("5293"),i("2877")),k=Object(A["a"])(S,a,n,!1,null,"48abb83e",null);k.options.__file="VideoList.vue";o["default"]=k.exports},"42d1":function(t,o,i){"use strict";var e=function(){var t=this,o=t.$createElement,i=t._self._c||o;return t.dscLoading?i("div",{staticClass:"cloading",style:{height:t.clientHeight+"px"},on:{touchmove:function(t){t.preventDefault()},mousewheel:function(t){t.preventDefault()}}},[i("div",{staticClass:"cloading-mask"}),t._t("text",[t._m(0)])],2):t._e()},s=[function(){var t=this,o=t.$createElement,e=t._self._c||o;return e("div",{staticClass:"cloading-main"},[e("img",{attrs:{src:i("f8b2")}})])}],a=i("88d8"),n=(i("7f7f"),i("ac1e"),i("543e")),u={props:["dscLoading"],data:function(){return{clientHeight:""}},components:Object(a["a"])({},n["a"].name,n["a"]),created:function(){},mounted:function(){this.clientHeight=document.documentElement.clientHeight},methods:{}},c=u,r=(i("a637"),i("2877")),d=Object(r["a"])(c,e,s,!1,null,"9a0469b6",null);d.options.__file="DscLoading.vue";o["a"]=d.exports},5293:function(t,o,i){"use strict";var e=i("5769"),s=i.n(e);s.a},5769:function(t,o,i){},"6f38":function(t,o,i){"use strict";var e=function(){var t=this,o=t.$createElement,i=t._self._c||o;return i("div",{staticClass:"ectouch-notcont"},[t._m(0),t.isSpan?[i("span",{staticClass:"cont"},[t._v(t._s(t.$t("lang.not_cont_prompt")))])]:[t._t("spanCon")]],2)},s=[function(){var t=this,o=t.$createElement,e=t._self._c||o;return e("div",{staticClass:"img"},[e("img",{staticClass:"img",attrs:{src:i("b8c9")}})])}],a=(i("cadf"),i("551c"),i("097d"),{props:{isSpan:{type:Boolean,default:!0}},name:"NotCont",data:function(){return{}}}),n=a,u=i("2877"),c=Object(u["a"])(n,e,s,!1,null,null,null);c.options.__file="NotCont.vue";o["a"]=c.exports},a637:function(t,o,i){"use strict";var e=i("2662"),s=i.n(e);s.a},b8c9:function(t,o){t.exports="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAL4AAACkCAMAAAAe52RSAAABfVBMVEUAAADi4eHu7u7u7u7q6uru7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7r6+vu7u7u7u7u7u7u7u7p6eju7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u7u6xr63u7u7u7u7u7u7u7u7u7u7u7u6wrqyxr62xr62wrqyxr63u7u6wrqyxr63u7u6wrqyxr62wrqyzsa+wrqyzsa+0srGwrqzu7u7y8vLm5ub29vbx8fHn5+fs7Ozq6urp6enl5eXLy8v09PTh4eHFxcjd3NzU1NfQ0NDw8O/Y2NjDw8PU1NTd3d7FxcXj4+Pa2trOzs7JycnHyMrHx8ewrqzf39/X19bY2t/Iys7k5efb3eLN0NTPz9DT1tvh4+bR09fAwMHS0tLLzNHe4OVSBNVGAAAAUnRSTlMAAu74CAT1/LbqXy8fFA3msVAyEaDCjm/78d7a07+Y1qyUcmpkQhqdVzn68/DGJSLKuop3RRamhUkp03zy4+HONvScivi6PjepgSN3Mm5sYFdKhfmmdgAACwVJREFUeNrt3Odb21YUBvAjeS+82HuPsDeEMNp07x4PDQ+opzyB0EIaSP/2ypA0FCz5ajq0+X3JB+XheX05uq+vMMAnn3yigNtpd7rhqXJbENHyZPM7scEJT5QdG+zwRD3x1X/is//Ed55P/ss+/wmeMOtnX0K72LxT7r3h0a7BfdqC2DvvH1hxdq71BENhCgh9/cVzG7TB5kbP8NCBi563W3od+I7DYbHY+2jX4Oi2O0QS67svTz/7sQPMFd5YHx1w9VkcKOWZnfYvjk16Wiz98y9ORZ89B/NMz3Yf+vt6sTU7vR9Y/0pu7T//spH+y5dgknCwe8RlR2KOPr9zPASSqJenz78Gk3jGh/x2VIrunwlKjvcPp9+AKWxT2yM0qmLxOye9EvPzhZb4HSMrhOF3xgbmUT3XUM8y6G4OcRNaozaGDyyoDd3VMw06m0CcJXiRa/0W1M7ldOu8xTsRx6AF78SgHfWxv/UVBfqxWhAHW8xNMOBC/QzueXULv92HosNxmZtqaa8fdUUHdmy6NJAdG+RP4juBBdTb4Pom6OAF/sMCTfkmRtAA9FYI9DBLo2jg27BEyXbTaIyuWasuA/QCcRQkTAXsaJSRcR/oYAgxLLXjrKCB/N1e0K4TUWJXmhxAQ1m2dkGzdYn4HT1+NJpzFwzSse5C4zk9YAQxPY1mWG1soE82PeKQAetv7XGhWZxLuqefdKF5Rr2gK1vwAM00o+sZhppaQVM9WwuDfjwBNJlrwgp6CY9Z0GyDGyCrcwIIWSdoNN/Qsuw2Ph8gHfydfmyHGR9Im+tdsQGRpQC2xcKk9PjvBvDFZJhodPb6sD1WPBQ0dTRiR5FrkWB0gvvYLp2bzfMvDw8hYp+zG1rymjg65ONjW8OROZK6naCxfbq8FDQXwmEgcDSC7bTWAc0tW2ZIFn9tHttp4IgCDTb6sb3GfKCebdiC7XUwpWH5dw6w3WY21S/+mAXbbX8K1JoawPZTP/3bdmy/gRC8M9e50DkHxDwjKIvloxy2whWT+SaqZR7JOLY7Pjz8w04g1kO3SJZLlrAFNidUM4VHkkK+wCGZRS/cWUDEBSDlG3LIJ2MyQpFlUQ57IuSyscdSTCZfZpGIxW0jWf3wN1/DfUF/i6nIVIVKLoFy+GQhEos8lophpc4hmc5pktn/4fRnuK/bjnKiFUHIC9UiyiklS7FIPPJIPB4r5qNIpt8DBF6efg73TLe6caPFKyEXZVHEcs2x6WQ0FmkmHksIJSTjGLdCK98//0L8CM29FxCksZXzaundC8k1V84ICcn4+SJL/NCNIP7p6akYn3R2RGypyDf+KVaSGQmVDJ+SiB8V6iecjtPz8vQldW/fOUQy7Ek9x2UlxSNNxVPZsyvSzccxYYOWfj39BT6YciGZUjWXikmLSIhHYtmKwCDpM5PWKLhnfQGJsOUkK24ukiLSYqXrHGFz7YJCo71IhDvPZGMRVVKsUEAi81OgzOYAEspl4jGVUPjtfolHWZQyblN4SiQcfb50U01wvCpcOp+J8h/eQd3wKGXYB4r09CEB9q/Li7qQVKsq1K8u/+Dexc9UGZSyqnD4iQ657B+1ZO4sXTxRqZhOn51XX7N38W+S0vH750AJ25ADW/vzIsOlUjFNIifHf2ADW6hwKMUSBCW8B9ga+6qaiKUi2sSymUuWLxYb34l0sXhWYrCZHmWnXBpb495UGlu+NpHIeYVL1/P5DMve5IV6oYzNdIMS7gWS+K+TfCyiVVYcGqacyxXTxTPxTd5ZCZvZAiX2XpAOj9j+2rBXbzkUcYUKj5JWlW08dqL4tXQspTF+iq1csncbZ5JBSYegxCjhvnmiLH6z/8slX2Pr+P2gRFcvEvirVk49ih+LySx1k2vR5Ku7+OVzDiXRoMSgAwn8fnEeiT2IL94MKcn04rVHF0u1It7iOJTWC0rsI1H8t4WH8VPMVfIsIiF6lUxHHkrX/kACoARNGP/hm+UYn6zX6+lU07Xnk0K9Wnp47aT2p+7xLUiCuRR7618nwFhZYLLVTCTVLH5ZSLDVTPbf1+Lli991j49EuDdJ7sHqF4ViSbhpPvm31woPbo3s+QXfpvhsrsr8u7dSWMhfV/jmw4OF6+sr7sE1vDEgfi/hObf28CFaKpsusg8Sfrh29vgae3XJ6h5/niz+q+P0g/jxSCqVkiqtlOhhdXGV16h7fD8iWW+dPOwtpe+BmOQr/eMPIJE/L8opcePXolQzIP6KgzD+uVizmpSOiVrLDko4nyHZu4abuMb4Z8d/IQE/KNFpIa1d1BY/Xq4RtdYIKLFmRxL87XFRi+x5jUECXYof85hyXMwWajwSCIASQRpJsK/rUW3xMfOWRQLDoETIRdhb9ZK24yJbeYMkvgUlwoOIZMfFM23x+SRZ6bpBCWr0mZLalSN/lakRxac3DPk4w5+1XCO+eoljotI9DIEibpqwdgvyvZWKyT9GTIutZcAH+kMuwt66ke2tWLyUiMjlPzsmOip2d4AitkUkwV9mZHrr9vSSL8vkj5fJ4s9SoMyYnfy4KCmVE8oFoXE611a6/Ueg0KSLLH6VEeNLHk8q9SyfL0vHx8JbDltzLoNCHj9h7ZbkVr9Yv0rWozLxM5cGjL7IFuglOy7K9VYqUqzXz+RKN0lSuvM7FCi1/oKodo/lekscHxZTcqVL1FqLu6DYnIssvnxvifu+bOkStdawDxSzrjqI4p+LI9KalqOiPUiBcuMLJL1VK7T8TIDW1upaAhVC/dga97aC6uOLrcVjS9sdoIJ1xoItsCx3VWM1xD8X47Moz6/yb2gEXfLZOZ7hf784FmtX9Q9FCwLDMRzLooxRH6gSdjrkwjNMNMr8fpkvqf3RdCoWrx5zfCLK8ByLUubdNlBnkpZOz0RvMbnrJBtTKXt+/Ya7+zIMS/rbc+S8Q/LpRUzi8rqaS6tyUrm+KImLf0sqv0XD7172SDUvE32PKb05vhaO1cgLl2k+KpLLv7gEqnm7WsYX17/4W+E3VV4lmOitREIqvqXHCur1LLQYntu5jSbUYd7fQJx4B3DElUVuWmrz4RqZP7wCdaLv1z6dlth6XrhtoMWsS3rXjyaiOkgwLJPJMNhUYBM08W31yu38jDgC2sInGpOTTLLYjCtoA23mBuXeMzS+B1o0GpcpXNWFTJnDRxzdVtDIumdHkexL4Jh32weZu+/YbdeyLOJ5XiRUo/jISogCrbwBB7bCijiO4xmSURdjcxwr+mcXKFarZ03uXdpNgXY7g0iKvcXd4nnmHzzP3WJv4UPRSoVpMjpjHaAD63ofGosrFll8ZNFDgR6mt56h+VyzoBNPlwPNNr8HupkdQZM9m5mmQC/WcT+ayrHqAR35ui1oovt/oeQJ3r7+WdDZkrMXzUJPgu52TctPT4ABPKsONAM9DoYIDZmRnx63gTE8Tc9eT2Fy7iwFjM7vnwQDeWcM3T8dg7NgJGp6zYWG6V3doMBY4YlBNIh9xkOB0aw7Q2gIem+aAuPZlof7UH+Ls1YKzED5JldQZ/Yxjw3MYvV09qGeVtwdFJiH2pzsQt30dYesYC6rd21Ap4NVIBimwHTho7ED1G7IvWmDtvBNzeyjNl09S1ZoF2pzamxAw9isNsK3lS+03YWquLbcy1Zou45ld+eA4oXv2v5q2gYfBdt0aHx0AIlZFseCS2H4iFi9RxMziyRd1u/s3vH44OPj250aH17td6AU+nB0bfZouQM+VpRvdy443r21ethP9+J78/6RrsDwt+6NkPfjjf7J/8/fj3J07I6O478AAAAASUVORK5CYII="},f210:function(t,o,i){"use strict";function e(t){if(Array.isArray(t)){for(var o=0,i=new Array(t.length);o<t.length;o++)i[o]=t[o];return i}}function s(t){if(Symbol.iterator in Object(t)||"[object Arguments]"===Object.prototype.toString.call(t))return Array.from(t)}function a(){throw new TypeError("Invalid attempt to spread non-iterable instance")}function n(t){return e(t)||s(t)||a()}i.d(o,"a",function(){return n})},f8b2:function(t,o,i){t.exports=i.p+"img/loading.gif"}}]);