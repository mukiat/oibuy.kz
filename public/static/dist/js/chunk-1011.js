(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-1011"],{6938:function(t,i,s){"use strict";s.r(i);var e,a=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("div",{staticClass:"con con_main"},[e("Goods",{attrs:{data:t.discoverDetail.link_good}}),e("div",{staticClass:"com-nav com-nav-detail"},[e("div",{staticClass:"com-hd com-header-img-cont"},[e("div",{staticClass:"dis-box"},[e("div",{staticClass:"com-img"},[e("div",{staticClass:"img-commom"},[e("img",{staticClass:"img-height",attrs:{src:t.discoverDetail.user_picture}})])]),e("div",{staticClass:"com-info box-flex"},[e("h4",[t._v(t._s(t.discoverDetail.user_name))])]),e("div",{staticClass:"com-time"},[e("i",{staticClass:"iconfont icon-shijian"}),e("span",[t._v(t._s(t.discoverDetail.add_time))])])])]),e("div",{staticClass:"com-bd"},[e("div",{staticClass:"com-bd-title dis-box"},[e("em",{staticClass:"em-promotion-1 tm-ns"},[1==t.discoverDetail.dis_type?[t._v(t._s(t.$t("lang.tao")))]:2==t.discoverDetail.dis_type?[t._v(t._s(t.$t("lang.wen")))]:3==t.discoverDetail.dis_type?[t._v(t._s(t.$t("lang.quan")))]:[t._v(t._s(t.$t("lang.shai")))]],2),e("div",{staticClass:"com-title box-flex"},[e("strong",{staticClass:"ellipsis-one"},[t._v(t._s(t.discoverDetail.dis_title))])])]),e("div",{staticClass:"article-con",domProps:{innerHTML:t._s(t.discoverDetail.dis_text)}})]),e("div",{staticClass:"com-fd"},[e("ul",{staticClass:"dis-box fx-deta-box"},[e("li",{staticClass:"box-flex"},[e("div",{staticClass:"yuan",on:{click:function(i){t.onZan(t.discoverDetail.dis_type,t.discoverDetail.dis_id)}}},[e("a",{attrs:{href:"javascript:void(0);"}},[e("i",{staticClass:"iconfont icon-zan"}),e("p",[t._v(t._s(t.discoverDetail.like_num))])])])]),e("li",{staticClass:"box-flex",on:{click:function(i){t.shareHandle(!1)}}},[e("div",{staticClass:"yuan"},[e("a",{attrs:{href:"javascript:void(0);"}},[e("i",{staticClass:"iconfont icon-fenxiang"}),e("p",[t._v(t._s(t.$t("lang.share")))])])])])])])]),t.discoverDetail.user_comment&&t.discoverDetail.user_comment.length>0?e("div",{staticClass:"comment-list"},[e("div",{staticClass:"dis-box padding-all my-nav-box my-bottom"},[e("h3",{staticClass:"box-flex"},[t._v(t._s(t.$t("lang.comment_list"))+"("+t._s(t.discoverDetail.user_comment.length)+")")])]),e("ul",{staticClass:"my-com-max-box"},t._l(t.discoverDetail.user_comment,function(i,s){return e("li",{key:s,staticClass:"padding-all"},[e("div",{staticClass:"com-img-left"},[e("div",{staticClass:"com-left-img"},[e("img",{staticClass:"img",attrs:{src:i.user_picture}})])]),e("div",{staticClass:"com-con-right"},[e("div",{staticClass:"dis-box"},[e("div",{staticClass:"box-flex"},[e("div",{staticClass:"com-adm-box",staticStyle:{"padding-top":".2rem"}},[e("h4",[t._v(t._s(i.user_name))]),e("p",[t._v(t._s(i.add_time))])])]),e("div",{staticClass:"not",on:{click:function(s){t.onQuote(i.dis_id,i.user_name)}}},[t._m(0,!0)])]),e("p",{staticClass:"com-con-m"},[t._v(t._s(i.dis_text))]),t._l(i.next_com,function(i){return e("div",{staticClass:"pl-hf-box padding-all"},[e("p",[e("span",[t._v(t._s(i.user_name)+" : ")]),t._v(t._s(i.dis_text))]),e("div",{staticClass:"x-jiant"})])})],2)])}))]):t._e(),e("div",{staticClass:"filter-btn consult-filter-btn"},[e("div",{staticClass:"dis-box"},[e("div",{staticClass:"com-left-img"},[e("div",{staticClass:"img-commom"},[t.discoverDetail.avatar?e("img",{staticClass:"img-height",attrs:{src:t.discoverDetail.avatar}}):e("img",{staticClass:"img-height",attrs:{src:s("e31e")}})])]),e("div",{staticClass:"text-all box-flex"},[e("input",{directives:[{name:"model",rawName:"v-model",value:t.comment,expression:"comment"}],staticClass:"j-input-text",attrs:{type:"text",name:"comment",placeholder:t.placeholder,autocomplete:"off",value:""},domProps:{value:t.comment},on:{input:function(i){i.target.composing||(t.comment=i.target.value)}}})]),e("button",{staticClass:"btn-submit",attrs:{type:"button"},on:{click:t.btnSubmit}},[t._v(t._s(t.$t("lang.send")))])])]),e("div",{staticClass:"van-modal",class:{hide:!0===t.mask},staticStyle:{"z-index":"1000"},on:{click:t.close}}),e("div",{staticClass:"bargain-share van-modal",class:{hide:!0===t.shareState},staticStyle:{"z-index":"1001"}},[e("div",{staticClass:"bargain-friends"},[e("div",{staticClass:"header f-30 col-3"},[t._v(t._s(t.$t("lang.share_hint")))]),e("div",{staticClass:"cont f-24 text-center"},[t._v(t._s(t.$t("lang.share_toast_hint")))]),e("div",{staticClass:"footer f-24 col-3",on:{click:t.close}},[t._v(t._s(t.$t("lang.i_see")))])])]),e("CommonNav")],1)},n=[function(){var t=this,i=t.$createElement,s=t._self._c||i;return s("div",{staticClass:"com-data-right com-list-1"},[s("span",{staticClass:"oncle-color"},[s("span",{staticClass:"my-right1"},[t._v("0"),s("i",{staticClass:"iconfont icon-daipingjia"})])])])}],o=s("9395"),c=s("88d8"),l=(s("e17f"),s("2241")),r=(s("7f7f"),s("e7e5"),s("d399")),d=s("2f62"),u=s("bd9b"),m=s("d567"),v={data:function(){return{comment:"",parent_id:this.$route.query.dis_id,dis_type:this.$route.query.dis_type,shareActive:!1,placeholder:this.$t("lang.label_reply_post"),quote_id:0,shareState:!0,mask:!0}},components:(e={Goods:u["a"],CommonNav:m["a"]},Object(c["a"])(e,r["a"].name,r["a"]),Object(c["a"])(e,l["a"].name,l["a"]),e),created:function(){this.load()},computed:Object(o["a"])({},Object(d["c"])({discoverDetail:function(t){return t.discover.discoverDetail}}),{like_num:{get:function(){return this.$store.state.discover.discoverDetail.like_num},set:function(t){this.$store.state.discover.discoverDetail.like_num=t}},isLogin:function(){return null!=localStorage.getItem("token")}}),methods:{load:function(){this.$store.dispatch("setDiscoverDetail",{dis_type:this.$route.query.dis_type,dis_id:this.$route.query.dis_id})},onZan:function(t,i){var s=this;this.$store.dispatch("setDiscoverLike",{dis_type:t,dis_id:i}).then(function(t){var i=t.data;Object(r["a"])(i.msg),s.like_num=i.like_num})},btnSubmit:function(){var t=this;if(this.isLogin)this.$store.dispatch("setDiscoverComment",{parent_id:this.parent_id,quote_id:this.quote_id,dis_text:this.comment,reply_type:0,dis_type:this.dis_type,goods_id:this.discoverDetail.goods_id}).then(function(i){var s=i.data;Object(r["a"])(s.msg),0==s.error&&(t.load(),t.comment="")});else{var i=this.$t("lang.login_user_not");this.notLogin(i)}},onQuote:function(t,i){this.quote_id=t,this.placeholder=this.$t("lang.reply")+i+":"},shareHandle:function(t){this.shareState=t},notLogin:function(t){var i=this,s=window.location.href;l["a"].confirm({message:t,className:"text-center"}).then(function(){i.$router.push({name:"login",query:{redirect:{name:"discoverDetail",query:{dis_type:i.$route.query.dis_type,dis_id:i.$route.query.dis_id},url:s}}})}).catch(function(){})},close:function(){this.mask=!0,this.shareState=!0}}},h=v,_=(s("87a1"),s("2877")),p=Object(_["a"])(h,a,n,!1,null,"0b913fd4",null);p.options.__file="Detail.vue";i["default"]=p.exports},"6fd6":function(t,i,s){},"87a1":function(t,i,s){"use strict";var e=s("bed5"),a=s.n(e);a.a},bd9b:function(t,i,s){"use strict";var e=function(){var t=this,i=t.$createElement,s=t._self._c||i;return t.data?s("div",{staticClass:"product-info-warp m-b06"},[s("router-link",{staticClass:"dis-box",attrs:{to:{name:"goods",params:{id:t.data.goods_id}}}},[s("div",{staticClass:"product-img"},[s("img",{staticClass:"img",attrs:{src:t.data.goods_thumb}})]),s("div",{staticClass:"product-name box-flex"},[s("p",[t._v(t._s(t.data.goods_name))])]),s("div",{staticClass:"user-more"},[s("i",{staticClass:"iconfont icon-more"})])])],1):t._e()},a=[],n={props:["data"],data:function(){return{}}},o=n,c=s("2877"),l=Object(c["a"])(o,e,a,!1,null,null,null);l.options.__file="Goods.vue";i["a"]=l.exports},bed5:function(t,i,s){},c1ee:function(t,i,s){"use strict";var e=s("6fd6"),a=s.n(e);a.a},d567:function(t,i,s){"use strict";var e=function(){var t=this,i=t.$createElement,s=t._self._c||i;return s("div",{staticClass:"sus-nav"},[s("div",{staticClass:"common-nav",class:{active:!0===t.navType},attrs:{id:"moveDiv"},on:{touchstart:t.down,touchmove:t.move,touchend:t.end}},[s("div",{staticClass:"right-cont",attrs:{id:"rightDiv"}},[s("ul",[s("li",{on:{click:function(i){t.routerLink("home")}}},[s("i",{staticClass:"iconfont icon-zhuye"}),s("p",[t._v(t._s(t.$t("lang.home")))])]),"drp"!=t.routerName&&"crowd_funding"!=t.routerName&&"team"!=t.routerName&&"supplier"!=t.routerName&&"presale"!=t.routerName?s("li",{on:{click:function(i){t.routerLink("search")}}},[s("i",{staticClass:"iconfont icon-search"}),s("p",[t._v(t._s(t.$t("lang.search")))])]):t._e(),s("li",{on:{click:function(i){t.routerLink("catalog")}}},[s("i",{staticClass:"iconfont icon-menu"}),s("p",[t._v(t._s(t.$t("lang.category")))])]),s("li",{on:{click:function(i){t.routerLink("cart")}}},[s("i",{staticClass:"iconfont icon-cart"}),s("p",[t._v(t._s(t.$t("lang.cart")))])]),s("li",{on:{click:function(i){t.routerLink("user")}}},[s("i",{staticClass:"iconfont icon-gerenzhongxin"}),s("p",[t._v(t._s(t.$t("lang.personal_center")))])]),"team"==t.routerName?s("li",{on:{click:function(i){t.routerLink("team")}}},[s("i",{staticClass:"iconfont icon-wodetuandui"}),s("p",[t._v(t._s(t.$t("lang.my_team")))])]):t._e(),"supplier"==t.routerName?s("li",{on:{click:function(i){t.routerLink("supplier")}}},[s("i",{staticClass:"iconfont icon-wodetuandui"}),s("p",[t._v(t._s(t.$t("lang.suppliers")))])]):t._e(),t._t("aloneNav")],2)]),s("div",{staticClass:"nav-icon",on:{click:t.handelNav}},[t._v(t._s(t.$t("lang.quick_navigation")))])]),s("div",{staticClass:"common-show",class:{active:!0===t.navType},on:{click:function(i){return i.stopPropagation(),t.handelShow(i)}}})])},a=[],n=(s("3846"),{props:["routerName"],data:function(){return{navType:!1,flags:!1,position:{x:0,y:0},nx:"",ny:"",dx:"",dy:"",xPum:"",yPum:""}},mounted:function(){this.flags=!1},methods:{handelNav:function(){this.navType=1!=this.navType},handelShow:function(){this.navType=!1},down:function(t){var i;this.flags=!0,i=t.touches?t.touches[0]:t,this.position.x=i.clientX,this.position.y=i.clientY,this.dx=moveDiv.offsetLeft,this.dy=moveDiv.offsetTop},move:function(t){var i,s,e,a;(t.preventDefault(),this.flags)&&(i=t.touches?t.touches[0]:t,s=document.documentElement.clientHeight,e=moveDiv.clientHeight,this.nx=i.clientX-this.position.x,this.ny=i.clientY-this.position.y,this.xPum=this.dx+this.nx,this.yPum=this.dy+this.ny,this.navType?this.yPum>0&&(a=s-e-this.yPum>0?s-e-this.yPum:0):(e+=rightDiv.clientHeight,this.yPum-e>0&&(a=s-this.yPum>0?s-this.yPum:0)),moveDiv.style.bottom=a+"px")},end:function(){this.flags=!1},routerLink:function(t){var i=this;"home"==t||"catalog"==t||"search"==t||"user"==t?setTimeout(function(){uni.getEnv(function(s){s.plus||s.miniprogram?"home"==t?uni.reLaunch({url:"../../pages/index/index"}):"catalog"==t?uni.reLaunch({url:"../../pages/category/category"}):"search"==t?uni.reLaunch({url:"../../pages/search/search"}):"user"==t&&uni.reLaunch({url:"../../pages/user/user"}):"search"==t?i.$router.push({name:"search"}):i.$router.push({name:t})})},100):i.$router.push({name:t})}}}),o=n,c=(s("c1ee"),s("2877")),l=Object(c["a"])(o,e,a,!1,null,null,null);l.options.__file="CommonNav.vue";i["a"]=l.exports},e31e:function(t,i,s){t.exports=s.p+"img/user_default.png"}}]);