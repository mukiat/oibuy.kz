(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-8f45"],{"228c":function(t,i,s){t.exports=s.p+"img/info-icon4.png"},2414:function(t,i,s){"use strict";s.r(i);var e,a=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("div",{staticClass:"con drp-info"},[0==t.drpdata.error||1==t.drpdata.audit?[e("div",{staticClass:"warp"},[t.drpdata.expiry&&t.drpdata.expiry.expiry_status>0&&1==t.drpdata.expiry.card_expiry_status?e("div",{staticClass:"tip"},[t._v(t._s(t.drpdata.expiry.expiry_time_notice))]):t._e(),1!=t.drpdata.expiry.card_expiry_status?e("div",{staticClass:"tip"},[t._v(t._s(t.drpdata.expiry.card_status_notice))]):t._e(),e("div",{staticClass:"header"},[e("div",{staticClass:"header-top"},[e("div",{staticClass:"header-img"},[e("router-link",{attrs:{to:{name:"drp-set"}}},[t.drpdata.shop_info.user_picture?e("img",{staticClass:"img",attrs:{src:t.drpdata.shop_info.user_picture,alt:""}}):e("img",{staticClass:"img",attrs:{src:s("e31e"),alt:""}})])],1),e("div",{staticClass:"header-right"},[e("h4",[t._v(t._s(t.drpdata.shop_info.shop_name))]),1==t.drpdata.expiry.expiry_status?[e("span",{staticClass:"time"},[t._v(t._s(t.$t("lang.membership_of_validity"))+"："+t._s(t.$t("lang.have_expired")))])]:["forever"==t.drpdata.expiry.expiry_type?e("span",{staticClass:"time"},[t._v(t._s(t.$t("lang.membership_of_validity"))+"："+t._s(t.$t("lang.permanence")))]):"days"==t.drpdata.expiry.expiry_type?e("span",{staticClass:"time"},[t._v(t._s(t.$t("lang.membership_of_validity"))+"："+t._s(t.drpdata.expiry.expiry_time_format))]):"timespan"==t.drpdata.expiry.expiry_type?e("span",{staticClass:"time"},[t._v(t._s(t.$t("lang.membership_of_validity"))+"："+t._s(t.drpdata.expiry.expiry_time_format))]):t._e()],e("div",{staticClass:"hang"},[e("div",{staticClass:"vip"},[t._m(0),e("span",[t._v(t._s(t.drpdata.user_rank))])]),e("span",{staticClass:"user-more",on:{click:t.drpApplyHref}},[t._v(t._s(t.$t("lang.detail"))),e("i",{staticClass:"iconfont icon-more"})])])],2)]),e("div",{staticClass:"header-bottom bor"},[e("div",{staticClass:"drp-button"},[0!=t.drpdata.expiry.expiry_status&&1==t.drpdata.expiry.card_expiry_status?e("div",{staticClass:"item",on:{click:t.drpRenew}},[e("p",[t._v(t._s(t.$t("lang.renew")))])]):t._e(),0==t.drpdata.expiry.expiry_status||2==t.drpdata.expiry.expiry_status?e("div",{staticClass:"item",on:{click:t.drpChange}},[e("p",[t._v(t._s(t.$t("lang.change")))])]):t._e(),1==t.drpdata.expiry.expiry_status?e("div",{staticClass:"item",on:{click:t.applyAgain}},[e("p",[t._v(t._s(t.$t("lang.re_purchase")))])]):t._e()])])]),t.card&&t.protectionList.length>0?e("div",{staticClass:"section protection"},[e("div",{staticClass:"tit"},[e("div",[t._v(t._s(t.$t("lang.enjoy_equity")))]),e("span",{staticClass:"user-more",on:{click:function(i){t.protectionHref(0)}}},[t._v(t._s(t.$t("lang.more"))),e("i",{staticClass:"iconfont icon-more"})])]),e("div",{staticClass:"value"},t._l(t.protectionList,function(i,s){return e("div",{key:s,staticClass:"item-list",on:{click:function(i){t.protectionHref(s)}}},[e("div",{staticClass:"icon"},[e("div",{staticClass:"img-box"},[e("img",{staticClass:"img",attrs:{src:i.icon}})])]),e("div",{staticClass:"text"},[t._v(t._s(i.name))])])}))]):t._e(),e("div",{staticClass:"section section-money"},[e("div",{staticClass:"tit"},[e("div",[t._v(t._s(t.pageDrpInfo.my_asset?t.pageDrpInfo.my_asset:t.$t("lang.my_assets")))]),e("span",{staticClass:"user-more",on:{click:t.depositLog}},[t._v(t._s(t.$t("lang.deposit_log"))),e("i",{staticClass:"iconfont icon-more"})])]),e("div",{staticClass:"value"},[e("div",{staticClass:"item",on:{click:t.Withdraw}},[e("p",[t._v(t._s(t.drpdata.surplus_amount))]),e("span",[t._v(t._s(t.pageDrpInfo.shop_money?t.pageDrpInfo.shop_money:t.$t("lang.deposit_brokerage")))])]),e("div",{staticClass:"item"},[e("p",[t._v(t._s(t.drpdata.totals))]),e("span",[t._v(t._s(t.pageDrpInfo.total_drp_log_money?t.pageDrpInfo.total_drp_log_money:t.$t("lang.drp_totals")))])]),e("div",{staticClass:"item"},[e("p",[t._v(t._s(t.drpdata.today_total))]),e("span",[t._v(t._s(t.pageDrpInfo.today_drp_log_money?t.pageDrpInfo.today_drp_log_money:t.$t("lang.today_income")))])]),e("div",{staticClass:"item"},[e("p",[t._v(t._s(t.drpdata.total_amount))]),e("span",[t._v(t._s(t.pageDrpInfo.total_drp_order_amount?t.pageDrpInfo.total_drp_order_amount:t.$t("lang.drp_total_amount")))])])])]),e("div",{staticClass:"section section-money"},[e("div",{staticClass:"tit"},[e("div",[t._v(t._s(t.pageDrpInfo.order_card?t.pageDrpInfo.order_card:t.$t("lang.rec_card")))]),e("router-link",{staticClass:"user-more",attrs:{to:{name:"drp-order",query:{type:"card"}}}},[t._v(t._s(t.$t("lang.detailed"))),e("i",{staticClass:"iconfont icon-more"})])],1),e("div",{staticClass:"value"},[e("div",{staticClass:"item",on:{click:t.teamClick}},[e("p",[t._v(t._s(t.drpdata.team_count))]),e("span",[t._v(t._s(t.pageDrpInfo.order_card_total?t.pageDrpInfo.order_card_total:t.$t("lang.card_total_number")))])]),e("div",{staticClass:"item"},[e("p",[t._v(t._s(t.drpdata.card_total_amount))]),e("span",[t._v(t._s(t.pageDrpInfo.card_total_amount?t.pageDrpInfo.card_total_amount:t.$t("lang.drp_total_amount")))])]),e("div",{staticClass:"item"},[e("p",[t._v(t._s(t.drpdata.card_today_money))]),e("span",[t._v(t._s(t.pageDrpInfo.card_today_money?t.pageDrpInfo.card_today_money:t.$t("lang.today_rewards")))])]),e("div",{staticClass:"item"},[e("p",[t._v(t._s(t.drpdata.card_total_money))]),e("span",[t._v(t._s(t.pageDrpInfo.card_total_money?t.pageDrpInfo.card_total_money:t.$t("lang.cumulative_rewards")))])])]),e("div",{staticClass:"invite_friends_button",on:{click:function(i){t.inviteFriends()}}},[t._v(t._s(t.pageDrpInfo.drp_card?t.pageDrpInfo.drp_card:t.$t("lang.team_rule_tit_3"))),e("i",{staticClass:"iconfont icon-more"})])]),e("div",{staticClass:"section section-money"},[e("div",{staticClass:"tit"},[e("div",[t._v(t._s(t.$t("lang.help_center")))]),e("router-link",{staticClass:"user-more",attrs:{to:{name:"help",query:{type:"drphelp"}}}},[t._v(t._s(t.$t("lang.more"))),e("i",{staticClass:"iconfont icon-more"})])],1),e("ul",{staticClass:"list-ul"},t._l(t.drpdata.article_list,function(i,s){return e("li",{key:s},[e("router-link",{attrs:{to:{name:"articleDetail",params:{id:i.id}}}},[t._v(t._s(i.title))])],1)}))])]),e("div",{staticClass:"drp-info-team"},[e("div",{staticClass:"tit"},[e("i",{staticClass:"row"}),e("span",[t._v(t._s(t.pageDrpInfo.drp_team?t.pageDrpInfo.drp_team:t.$t("lang.my_team_alt")))])]),e("div",{staticClass:"items"},[e("div",{staticClass:"item item1",on:{click:t.teamClick}},[e("div",{staticClass:"num"},[t._v(t._s(t.drpdata.sum_count))]),e("div",{staticClass:"link"}),e("div",{staticClass:"text"},[t._v(t._s(t.pageDrpInfo.sum_count?t.pageDrpInfo.sum_count:t.$t("lang.user_total")))])]),e("div",{staticClass:"item item2"},[e("div",{staticClass:"num"},[t._v(t._s(t.drpdata.team_count))]),e("div",{staticClass:"link"}),e("div",{staticClass:"text"},[t._v(t._s(t.pageDrpInfo.team_count?t.pageDrpInfo.team_count:t.$t("lang.directly_user")))])]),e("div",{staticClass:"item item3"},[e("div",{staticClass:"num"},[t._v(t._s(t.drpdata.user_count))]),e("div",{staticClass:"link"}),e("div",{staticClass:"text"},[t._v(t._s(t.pageDrpInfo.user_count?t.pageDrpInfo.user_count:t.$t("lang.direct_referrals")))])])])]),e("div",{staticClass:"nav-items"},[e("router-link",{staticClass:"nav-item",attrs:{to:{name:"drp-order",query:{type:"card"}}}},[e("i",[e("img",{staticClass:"img",attrs:{src:s("d003")}})]),e("span",[t._v(t._s(t.pageDrpInfo.order_card_list?t.pageDrpInfo.order_card_list:t.$t("lang.card_reward")))])]),e("router-link",{staticClass:"nav-item",attrs:{to:{name:"drp-order",query:{type:"order"}}}},[e("i",[e("img",{staticClass:"img",attrs:{src:s("3f7c")}})]),e("span",[t._v(t._s(t.pageDrpInfo.order_list?t.pageDrpInfo.order_list:t.$t("lang.sale_reward")))])]),e("router-link",{staticClass:"nav-item",attrs:{to:{name:"drp-rank"}}},[e("i",[e("img",{staticClass:"img",attrs:{src:s("b57f")}})]),e("span",[t._v(t._s(t.pageDrpInfo.drp_rank?t.pageDrpInfo.drp_rank:t.$t("lang.rich_list")))])]),e("div",{staticClass:"nav-item",on:{click:t.drpshopLink}},[t._m(1),e("span",[t._v(t._s(t.pageDrpInfo.drp_store?t.pageDrpInfo.drp_store:t.$t("lang.my_drp")))])])],1),t.drpdata.banner&&t.drpdata.banner.length>0?e("div",{staticClass:"adv"},[t.drpdata.banner?e("Swiper",{attrs:{data:t.drpdata.banner,autoplay:3e3}}):t._e()],1):t._e()]:[e("div",{staticClass:"ectouch-notcont"},[t._m(2),1==t.viewStatus?[0==t.viewAudit?[e("span",{staticClass:"cont"},[t._v(t._s(t.$t("lang.drp_status_propmt_1")))])]:t._e(),2==t.viewAudit?[e("span",{staticClass:"cont"},[t._v(t._s(t.drpdata.msg?t.drpdata.msg:t.$t("lang.drp_status_propmt_7")))]),e("span",{staticClass:"cont"},[t._v(t._s(t.drpdata.log_content?t.drpdata.log_content:""))]),e("div",{staticClass:"v-btn",on:{click:t.reApply}},[t._v(t._s(t.$t("lang.new_registration")))])]:t._e()]:t._e(),2==t.viewStatus?[e("span",{staticClass:"cont"},[t._v(t._s(t.$t("lang.drp_status_propmt_3"))),e("router-link",{staticClass:"color-red",attrs:{to:{name:"drp-register"}}},[t._v(t._s(t.$t("lang.to_apply")))])],1)]:t._e()],2)],e("ec-tab-down"),e("van-popup",{staticClass:"show-popup-bottom",attrs:{position:"bottom"},model:{value:t.renewShow,callback:function(i){t.renewShow=i},expression:"renewShow"}},[e("div",{staticClass:"goods-show-title padding-all"},[e("h3",{staticClass:"fl"},[t._v(t._s(t.$t("lang.fill_in_renew")))]),e("i",{staticClass:"iconfont icon-close fr",on:{click:t.renewClose}})]),e("div",{staticClass:"s-g-list-con"},[e("div",{staticClass:"select-two"},[e("ul",t._l(t.card.receive_value,function(i,s){return e("li",{key:s,staticClass:"ect-select",class:{active:t.renew_type==i.type},on:{click:function(s){t.renew_method_select(i.type)}}},[e("label",{staticClass:"dis-box"},["integral"==i.type?e("span",{staticClass:"box-flex"},[t._v(t._s(t.$t("lang.drp_apply_title_1")))]):t._e(),"order"==i.type?e("span",{staticClass:"box-flex"},[t._v(t._s(t.$t("lang.drp_apply_title_2")))]):t._e(),"buy"==i.type?e("span",{staticClass:"box-flex"},[t._v(t._s(t.$t("lang.drp_apply_title_3")))]):t._e(),"goods"==i.type?e("span",{staticClass:"box-flex"},[t._v(t._s(t.$t("lang.drp_apply_title_4")))]):t._e(),"free"==i.type?e("span",{staticClass:"box-flex"},[t._v(t._s(t.$t("lang.drp_apply_title_5")))]):t._e(),e("i",{staticClass:"iconfont icon-gou"})])])}))])])]),e("DscLoading",{attrs:{dscLoading:t.dscLoading}})],2)},n=[function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("i",[e("img",{staticClass:"img",attrs:{src:s("60c6")}})])},function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("i",[e("img",{staticClass:"img",attrs:{src:s("228c")}})])},function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("div",{staticClass:"img"},[e("img",{staticClass:"img",attrs:{src:s("b8c9")}})])}],r=(s("a481"),s("9395")),o=(s("96cf"),s("cb0c")),c=s("88d8"),u=(s("8a58"),s("e41f")),p=(s("e7e5"),s("d399")),d=(s("7f7f"),s("66b9"),s("b650")),l=s("2f62"),h=function(){var t=this,i=t.$createElement,s=t._self._c||i;return s("van-swipe",{staticClass:"swipe",attrs:{autoplay:t.autoplay}},t._l(t.data,function(t,i){return s("van-swipe-item",{key:i},[s("a",{attrs:{href:t.link}},[s("img",{staticClass:"img",attrs:{src:t.pic}})])])}))},_=[],m=(s("4b0a"),s("2bb1")),f=(s("7844"),s("5596")),v={props:["data","swipeUrl","autoplay"],components:(e={},Object(c["a"])(e,f["a"].name,f["a"]),Object(c["a"])(e,m["a"].name,m["a"]),e),data:function(){return{}},computed:{}},g=v,y=s("2877"),C=Object(y["a"])(g,h,_,!1,null,null,null);C.options.__file="Swiper.vue";var w,x=C.exports,b=s("d567"),k=s("ef53"),D=s("42d1"),S={components:(w={Swiper:x,CommonNav:b["a"],EcTabDown:k["a"],DscLoading:D["a"]},Object(c["a"])(w,d["a"].name,d["a"]),Object(c["a"])(w,p["a"].name,p["a"]),Object(c["a"])(w,u["a"].name,u["a"]),w),data:function(){return{viewStatus:0,routerName:"drp",routerPath:"",dscLoading:!0,renewShow:!1,renew_type:"",back:this.$route.query.back,pageDrpInfo:{}}},created:function(){var t=Object(o["a"])(regeneratorRuntime.mark(function t(){return regeneratorRuntime.wrap(function(t){while(1)switch(t.prev=t.next){case 0:return t.next=2,this.getCustomText();case 2:this.$store.dispatch("setDrp");case 3:case"end":return t.stop()}},t,this)}));return function(){return t.apply(this,arguments)}}(),computed:Object(r["a"])({},Object(l["c"])({drpdata:function(t){return t.drp.drpData}}),{card:function(){return this.drpdata.membership_card_info?this.drpdata.membership_card_info:""},protectionList:function(){return this.card?this.card.user_membership_card_rights_list:""}}),mounted:function(){window.history&&window.history.pushState&&this.back&&(history.pushState(null,null,document.URL),window.addEventListener("popstate",this.goBack,!1))},methods:{goBack:function(){this.$router.replace({path:this.back})},teamClick:function(){var t=this;t.$router.push({name:"drp-team",params:{user_id:t.drpdata.shop_info.user_id}})},drpshopLink:function(){3!=this.viewStatus?this.$router.push({name:"drp"}):Object(p["a"])(this.$t("lang.drp_status_propmt_8"))},inviteFriends:function(){this.$router.push({name:"drp-card"})},Withdraw:function(){var t=this;t.$router.push({name:"drp-withdraw"})},protectionHref:function(t){this.$router.push({name:"drp-protection",query:{card_id:this.card.id,index:t}})},drpApplyHref:function(){this.$router.push({name:"drp-apply",query:{card_id:this.card.id}})},drpRenew:function(){this.renewShow=!0},drpChange:function(){this.$router.push({name:"drp-register",query:{apply_status:"change",membership_card_id:this.card.id}})},applyAgain:function(){this.$router.push({name:"drp-register",query:{apply_status:"repeat",membership_card_id:this.card.id}})},renewClose:function(){this.renewShow=!1},renew_method_select:function(t){var i={};this.renew_type=t,i=this.card.id?{receive_type:t,apply_status:"renew",membership_card_id:this.card.id}:{receive_type:t,apply_status:"renew"},this.$router.push({name:"drp-apply",query:i})},depositLog:function(){this.$router.push({name:"drp-withdraw-log"})},getCustomText:function(){var t=Object(o["a"])(regeneratorRuntime.mark(function t(){var i,s,e,a;return regeneratorRuntime.wrap(function(t){while(1)switch(t.prev=t.next){case 0:return t.next=2,this.$http.post("".concat(window.ROOT_URL,"api/drp/custom_text"),{code:"page_drp_info"});case 2:i=t.sent,s=i.data,e=s.status,a=s.data.page_drp_info,"success"==e&&(this.pageDrpInfo=a||{});case 7:case"end":return t.stop()}},t,this)}));return function(){return t.apply(this,arguments)}}(),reApply:function(){this.$router.push({name:"drp-register",query:{apply_status:"repeat",membership_card_id:this.drpdata.shop_info.membership_card_id}})}},watch:{drpdata:function(){var t=this;setTimeout(function(){t.dscLoading=!1},1e3),this.viewStatus=this.drpdata.error,this.viewAudit=this.drpdata.audit,2==this.viewStatus&&this.$router.replace({name:"drp-register",query:{back:this.routerPath}})}},beforeRouteEnter:function(t,i,s){s(function(t){t.routerPath=i.fullPath})}},I=S,L=(s("c92b"),Object(y["a"])(I,a,n,!1,null,"753e2f95",null));L.options.__file="Drpinfo.vue";i["default"]=L.exports},2662:function(t,i,s){},"2bb1":function(t,i,s){"use strict";var e=s("c31d"),a=s("a142"),n=Object(a["j"])("swipe-item"),r=n[0],o=n[1];i["a"]=r({data:function(){return{offset:0}},beforeCreate:function(){this.$parent.swipes.push(this)},destroyed:function(){this.$parent.swipes.splice(this.$parent.swipes.indexOf(this),1)},render:function(t){var i=this.$parent,s=i.vertical,a=i.computedWidth,n=i.computedHeight,r={width:a+"px",height:s?n+"px":"100%",transform:"translate"+(s?"Y":"X")+"("+this.offset+"px)"};return t("div",{class:o(),style:r,on:Object(e["a"])({},this.$listeners)},[this.slots()])}})},"3f7c":function(t,i,s){t.exports=s.p+"img/info-icon2.png"},"42d1":function(t,i,s){"use strict";var e=function(){var t=this,i=t.$createElement,s=t._self._c||i;return t.dscLoading?s("div",{staticClass:"cloading",style:{height:t.clientHeight+"px"},on:{touchmove:function(t){t.preventDefault()},mousewheel:function(t){t.preventDefault()}}},[s("div",{staticClass:"cloading-mask"}),t._t("text",[t._m(0)])],2):t._e()},a=[function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("div",{staticClass:"cloading-main"},[e("img",{attrs:{src:s("f8b2")}})])}],n=s("88d8"),r=(s("7f7f"),s("ac1e"),s("543e")),o={props:["dscLoading"],data:function(){return{clientHeight:""}},components:Object(n["a"])({},r["a"].name,r["a"]),created:function(){},mounted:function(){this.clientHeight=document.documentElement.clientHeight},methods:{}},c=o,u=(s("a637"),s("2877")),p=Object(u["a"])(c,e,a,!1,null,"9a0469b6",null);p.options.__file="DscLoading.vue";i["a"]=p.exports},"4b0a":function(t,i,s){"use strict";s("68ef"),s("786d")},5596:function(t,i,s){"use strict";var e=s("a142"),a=s("db78"),n=s("3875"),r=Object(e["j"])("swipe"),o=r[0],c=r[1];i["a"]=o({mixins:[n["a"]],props:{width:Number,height:Number,autoplay:Number,vertical:Boolean,initialSwipe:Number,indicatorColor:String,loop:{type:Boolean,default:!0},touchable:{type:Boolean,default:!0},showIndicators:{type:Boolean,default:!0},duration:{type:Number,default:500}},data:function(){return{computedWidth:0,computedHeight:0,offset:0,active:0,deltaX:0,deltaY:0,swipes:[],swiping:!1}},mounted:function(){this.initialize(),this.$isServer||Object(a["b"])(window,"resize",this.onResize,!0)},activated:function(){this.rendered&&this.initialize(),this.rendered=!0},destroyed:function(){this.clear(),this.$isServer||Object(a["a"])(window,"resize",this.onResize,!0)},watch:{swipes:function(){this.initialize()},initialSwipe:function(){this.initialize()},autoplay:function(t){t?this.autoPlay():this.clear()}},computed:{count:function(){return this.swipes.length},delta:function(){return this.vertical?this.deltaY:this.deltaX},size:function(){return this[this.vertical?"computedHeight":"computedWidth"]},trackSize:function(){return this.count*this.size},activeIndicator:function(){return(this.active+this.count)%this.count},isCorrectDirection:function(){var t=this.vertical?"vertical":"horizontal";return this.direction===t},trackStyle:function(){var t,i=this.vertical?"height":"width",s=this.vertical?"width":"height";return t={},t[i]=this.trackSize+"px",t[s]=this[s]?this[s]+"px":"",t.transitionDuration=(this.swiping?0:this.duration)+"ms",t.transform="translate"+(this.vertical?"Y":"X")+"("+this.offset+"px)",t},indicatorStyle:function(){return{backgroundColor:this.indicatorColor}}},methods:{initialize:function(t){if(void 0===t&&(t=this.initialSwipe),clearTimeout(this.timer),this.$el){var i=this.$el.getBoundingClientRect();this.computedWidth=this.width||i.width,this.computedHeight=this.height||i.height}this.swiping=!0,this.active=t,this.offset=this.count>1?-this.size*this.active:0,this.swipes.forEach(function(t){t.offset=0}),this.autoPlay()},onResize:function(){this.initialize(this.activeIndicator)},onTouchStart:function(t){this.touchable&&(this.clear(),this.swiping=!0,this.touchStart(t),this.correctPosition())},onTouchMove:function(t){this.touchable&&this.swiping&&(this.touchMove(t),this.isCorrectDirection&&(Object(a["c"])(t,!0),this.move({offset:Math.min(Math.max(this.delta,-this.size),this.size)})))},onTouchEnd:function(){if(this.touchable&&this.swiping){if(this.delta&&this.isCorrectDirection){var t=this.vertical?this.offsetY:this.offsetX;this.move({pace:t>0?this.delta>0?-1:1:0,emitChange:!0})}this.swiping=!1,this.autoPlay()}},move:function(t){var i=t.pace,s=void 0===i?0:i,e=t.offset,a=void 0===e?0:e,n=t.emitChange,r=this.delta,o=this.active,c=this.count,u=this.swipes,p=this.trackSize,d=0===o,l=o===c-1,h=!this.loop&&(d&&(a>0||s<0)||l&&(a<0||s>0));h||c<=1||(u[0]&&(u[0].offset=l&&(r<0||s>0)?p:0),u[c-1]&&(u[c-1].offset=d&&(r>0||s<0)?-p:0),s&&o+s>=-1&&o+s<=c&&(this.active+=s,n&&this.$emit("change",this.activeIndicator)),this.offset=Math.round(a-this.active*this.size))},swipeTo:function(t){var i=this;this.swiping=!0,this.resetTouchStatus(),this.correctPosition(),setTimeout(function(){i.swiping=!1,i.move({pace:t%i.count-i.active,emitChange:!0})},30)},correctPosition:function(){this.active<=-1&&this.move({pace:this.count}),this.active>=this.count&&this.move({pace:-this.count})},clear:function(){clearTimeout(this.timer)},autoPlay:function(){var t=this,i=this.autoplay;i&&this.count>1&&(this.clear(),this.timer=setTimeout(function(){t.swiping=!0,t.resetTouchStatus(),t.correctPosition(),setTimeout(function(){t.swiping=!1,t.move({pace:1,emitChange:!0}),t.autoPlay()},30)},i))}},render:function(t){var i=this,s=this.count,e=this.activeIndicator,a=this.slots("indicator")||this.showIndicators&&s>1&&t("div",{class:c("indicators",{vertical:this.vertical})},[Array.apply(void 0,Array(s)).map(function(s,a){return t("i",{class:c("indicator",{active:a===e}),style:a===e?i.indicatorStyle:null})})]);return t("div",{class:c()},[t("div",{ref:"track",style:this.trackStyle,class:c("track"),on:{touchstart:this.onTouchStart,touchmove:this.onTouchMove,touchend:this.onTouchEnd,touchcancel:this.onTouchEnd}},[this.slots()]),a])}})},"60c6":function(t,i,s){t.exports=s.p+"img/icon-vip.png"},"66b9":function(t,i,s){"use strict";s("68ef")},"677e":function(t,i,s){},"6fd6":function(t,i,s){},7844:function(t,i,s){"use strict";s("68ef"),s("8270")},"786d":function(t,i,s){},8270:function(t,i,s){},"8a58":function(t,i,s){"use strict";s("68ef"),s("4d75")},a637:function(t,i,s){"use strict";var e=s("2662"),a=s.n(e);a.a},ac1e:function(t,i,s){"use strict";s("68ef")},b57f:function(t,i,s){t.exports=s.p+"img/info-icon3.png"},b8c9:function(t,i){t.exports="data:image/png;base64,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"},c1ee:function(t,i,s){"use strict";var e=s("6fd6"),a=s.n(e);a.a},c92b:function(t,i,s){"use strict";var e=s("677e"),a=s.n(e);a.a},d003:function(t,i,s){t.exports=s.p+"img/info-icon1.png"},d567:function(t,i,s){"use strict";var e=function(){var t=this,i=t.$createElement,s=t._self._c||i;return s("div",{staticClass:"sus-nav"},[s("div",{staticClass:"common-nav",class:{active:!0===t.navType},attrs:{id:"moveDiv"},on:{touchstart:t.down,touchmove:t.move,touchend:t.end}},[s("div",{staticClass:"right-cont",attrs:{id:"rightDiv"}},[s("ul",[s("li",{on:{click:function(i){t.routerLink("home")}}},[s("i",{staticClass:"iconfont icon-zhuye"}),s("p",[t._v(t._s(t.$t("lang.home")))])]),"drp"!=t.routerName&&"crowd_funding"!=t.routerName&&"team"!=t.routerName&&"supplier"!=t.routerName&&"presale"!=t.routerName?s("li",{on:{click:function(i){t.routerLink("search")}}},[s("i",{staticClass:"iconfont icon-search"}),s("p",[t._v(t._s(t.$t("lang.search")))])]):t._e(),s("li",{on:{click:function(i){t.routerLink("catalog")}}},[s("i",{staticClass:"iconfont icon-menu"}),s("p",[t._v(t._s(t.$t("lang.category")))])]),s("li",{on:{click:function(i){t.routerLink("cart")}}},[s("i",{staticClass:"iconfont icon-cart"}),s("p",[t._v(t._s(t.$t("lang.cart")))])]),s("li",{on:{click:function(i){t.routerLink("user")}}},[s("i",{staticClass:"iconfont icon-gerenzhongxin"}),s("p",[t._v(t._s(t.$t("lang.personal_center")))])]),"team"==t.routerName?s("li",{on:{click:function(i){t.routerLink("team")}}},[s("i",{staticClass:"iconfont icon-wodetuandui"}),s("p",[t._v(t._s(t.$t("lang.my_team")))])]):t._e(),"supplier"==t.routerName?s("li",{on:{click:function(i){t.routerLink("supplier")}}},[s("i",{staticClass:"iconfont icon-wodetuandui"}),s("p",[t._v(t._s(t.$t("lang.suppliers")))])]):t._e(),t._t("aloneNav")],2)]),s("div",{staticClass:"nav-icon",on:{click:t.handelNav}},[t._v(t._s(t.$t("lang.quick_navigation")))])]),s("div",{staticClass:"common-show",class:{active:!0===t.navType},on:{click:function(i){return i.stopPropagation(),t.handelShow(i)}}})])},a=[],n=(s("3846"),{props:["routerName"],data:function(){return{navType:!1,flags:!1,position:{x:0,y:0},nx:"",ny:"",dx:"",dy:"",xPum:"",yPum:""}},mounted:function(){this.flags=!1},methods:{handelNav:function(){this.navType=1!=this.navType},handelShow:function(){this.navType=!1},down:function(t){var i;this.flags=!0,i=t.touches?t.touches[0]:t,this.position.x=i.clientX,this.position.y=i.clientY,this.dx=moveDiv.offsetLeft,this.dy=moveDiv.offsetTop},move:function(t){var i,s,e,a;(t.preventDefault(),this.flags)&&(i=t.touches?t.touches[0]:t,s=document.documentElement.clientHeight,e=moveDiv.clientHeight,this.nx=i.clientX-this.position.x,this.ny=i.clientY-this.position.y,this.xPum=this.dx+this.nx,this.yPum=this.dy+this.ny,this.navType?this.yPum>0&&(a=s-e-this.yPum>0?s-e-this.yPum:0):(e+=rightDiv.clientHeight,this.yPum-e>0&&(a=s-this.yPum>0?s-this.yPum:0)),moveDiv.style.bottom=a+"px")},end:function(){this.flags=!1},routerLink:function(t){var i=this;"home"==t||"catalog"==t||"search"==t||"user"==t?setTimeout(function(){uni.getEnv(function(s){s.plus||s.miniprogram?"home"==t?uni.reLaunch({url:"../../pages/index/index"}):"catalog"==t?uni.reLaunch({url:"../../pages/category/category"}):"search"==t?uni.reLaunch({url:"../../pages/search/search"}):"user"==t&&uni.reLaunch({url:"../../pages/user/user"}):"search"==t?i.$router.push({name:"search"}):i.$router.push({name:t})})},100):i.$router.push({name:t})}}}),r=n,o=(s("c1ee"),s("2877")),c=Object(o["a"])(r,e,a,!1,null,null,null);c.options.__file="CommonNav.vue";i["a"]=c.exports},e31e:function(t,i,s){t.exports=s.p+"img/user_default.png"},e41f:function(t,i,s){"use strict";var e=s("a142"),a=s("6605"),n=Object(e["j"])("popup"),r=n[0],o=n[1];i["a"]=r({mixins:[a["a"]],props:{position:String,transition:String,overlay:{type:Boolean,default:!0},closeOnClickOverlay:{type:Boolean,default:!0}},render:function(t){var i,s=this;if(this.shouldRender){var e=this.position,a=function(t){return function(){return s.$emit(t)}},n=this.transition||(e?"van-popup-slide-"+e:"van-fade");return t("transition",{attrs:{name:n},on:{afterEnter:a("opened"),afterLeave:a("closed")}},[t("div",{directives:[{name:"show",value:this.value}],class:o((i={},i[e]=e,i))},[this.slots()])])}}})},f8b2:function(t,i,s){t.exports=s.p+"img/loading.gif"}}]);