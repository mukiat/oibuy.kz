(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-b11a"],{"0653":function(t,e,i){"use strict";i("68ef")},"0a26":function(t,e,i){"use strict";i.d(e,"a",function(){return a});var s=i("ad06"),n=i("f331"),a=function(t,e){return{mixins:[n["a"]],props:{name:null,value:null,disabled:Boolean,checkedColor:String,labelPosition:String,labelDisabled:Boolean,shape:{type:String,default:"round"},bindGroup:{type:Boolean,default:!0}},created:function(){this.bindGroup&&this.findParent(t)},computed:{isDisabled:function(){return this.parent&&this.parent.disabled||this.disabled},iconStyle:function(){var t=this.checkedColor;if(t&&this.checked&&!this.isDisabled)return{borderColor:t,backgroundColor:t}}},render:function(){var t=this,i=arguments[0],n=this.slots,a=this.checked,r=n("icon",{checked:a})||i(s["a"],{attrs:{name:"success"},style:this.iconStyle}),o=n()&&i("span",{class:e("label",[this.labelPosition,{disabled:this.isDisabled}]),on:{click:this.onClickLabel}},[n()]);return i("div",{class:e(),on:{click:function(e){t.$emit("click",e)}}},[i("div",{class:e("icon",[this.shape,{disabled:this.isDisabled,checked:a}]),on:{click:this.onClickIcon}},[r]),o])}}}},"195b":function(t,e,i){"use strict";var s=i("298f"),n=i.n(s);n.a},"298f":function(t,e,i){},"34e9":function(t,e,i){"use strict";var s=i("2638"),n=i.n(s),a=i("a142"),r=i("ba31"),o=Object(a["j"])("cell-group"),c=o[0],u=o[1];function l(t,e,i,s){var a=t("div",n()([{class:[u(),{"van-hairline--top-bottom":e.border}]},Object(r["b"])(s,!0)]),[i["default"]&&i["default"]()]);return e.title?t("div",[t("div",{class:u("title")},[e.title]),a]):a}l.props={title:String,border:{type:Boolean,default:!0}},e["a"]=c(l)},"4ddd":function(t,e,i){"use strict";i("68ef"),i("dde9")},"5a5e":function(t,e,i){"use strict";i.r(e);var s,n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"address"},[t.$route.query.nearbyleader>0?i("van-cell",{staticClass:"margin_bottom",attrs:{to:{name:"communityPost",query:t.$route.query},title:t.$t("lang.selected_post"),"is-link":""}}):t._e(),t.isRouterlink?[i("div",{staticClass:"flow-consignee-list"},[i("van-radio-group",{model:{value:t.iSaddress,callback:function(e){t.iSaddress=e},expression:"iSaddress"}},t._l(t.addressList,function(e,s){return i("van-cell-group",{key:s},[i("section",{staticClass:"flow-checkout-adr"},[i("div",{staticClass:"flow-have-adr",on:{click:function(i){t.checkoutRouter(e)}}},[i("div",{staticClass:"f-h-adr-title"},[i("div",{staticClass:"box-flex"},[i("label",[t._v(t._s(e.name))])]),i("div",{staticClass:"box-flex"},[i("label",{staticClass:"fr"},[t._v(t._s(e.mobile))])])]),i("p",[t._v(t._s(e.province_name)+" "+t._s(e.city_name)+" "+t._s(e.district_name)+" "+t._s(e.street_name)+" "+t._s(e.address))])]),i("van-cell",{staticClass:"flow-set-adr"},[i("template",{slot:"title"},[i("van-radio",{attrs:{name:e.id}},[t._v(t._s(t.$t("lang.default_address")))])],1),i("a",{attrs:{href:"javascript:void(0);"},on:{click:function(i){t.checkoutRouterEdit(e.id)}}},[i("i",{staticClass:"iconfont icon-bianji"}),t._v(t._s(t.$t("lang.edit")))]),i("a",{attrs:{href:"javascript:void(0);"},on:{click:function(i){t.userAddressDelete(e.id,e.is_checked)}}},[i("i",{staticClass:"iconfont icon-delete"}),t._v(t._s(t.$t("lang.delete")))])],2)],1)])}))],1),i("div",{staticClass:"filter-btn"},[i("a",{staticClass:"btn btn-submit box-flex",attrs:{href:"javascript:void(0)"},on:{click:t.checkoutRouterAdd}},[t._v(t._s(t.$t("lang.add_consignee_info")))])])]:[i("div",{staticClass:"flow-consignee-list"},[i("van-radio-group",{model:{value:t.iSaddress,callback:function(e){t.iSaddress=e},expression:"iSaddress"}},t._l(t.addressList,function(e,s){return i("van-cell-group",{key:s},[i("section",{staticClass:"flow-checkout-adr"},[i("div",{staticClass:"flow-have-adr"},[i("div",{staticClass:"f-h-adr-title"},[i("div",{staticClass:"box-flex"},[i("label",[t._v(t._s(e.name))])]),i("div",{staticClass:"box-flex"},[i("label",{staticClass:"fr"},[t._v(t._s(e.mobile))])])]),i("p",[t._v(t._s(e.province_name)+" "+t._s(e.city_name)+" "+t._s(e.district_name)+" "+t._s(e.street_name)+" "+t._s(e.address))])]),i("van-cell",{staticClass:"flow-set-adr"},[i("template",{slot:"title"},[i("van-radio",{attrs:{name:e.id}},[t._v(t._s(t.$t("lang.default_address")))])],1),i("router-link",{attrs:{to:{name:"editAddressForm",params:{id:e.id}}}},[i("i",{staticClass:"iconfont icon-bianji"}),t._v(t._s(t.$t("lang.edit")))]),i("a",{attrs:{href:"javascript:void(0);"},on:{click:function(i){t.userAddressDelete(e.id,e.is_checked)}}},[i("i",{staticClass:"iconfont icon-delete"}),t._v(t._s(t.$t("lang.delete")))])],2)],1)])}))],1),i("div",{staticClass:"filter-btn"},[t.isWeiXin?i("div",{staticClass:"btn btn-wximport",on:{click:t.wxAddress}},[t._v(t._s(t.$t("lang.import_wx_address")))]):t._e(),i("router-link",{staticClass:"btn btn-submit box-flex",attrs:{to:{name:"addAddressForm"}}},[t._v(t._s(t.$t("lang.add_consignee_info")))])],1)],i("CommonNav")],2)},a=[],r=i("9395"),o=i("88d8"),c=(i("a44c"),i("e27c")),u=(i("4ddd"),i("9f14")),l=(i("0653"),i("34e9")),d=(i("c194"),i("7744")),h=(i("7f7f"),i("e7e5"),i("d399")),f=i("2f62"),v=i("09d6"),p=i("d567"),m={mixins:[v["a"]],data:function(){return{defaultSel:0}},components:(s={},Object(o["a"])(s,h["a"].name,h["a"]),Object(o["a"])(s,d["a"].name,d["a"]),Object(o["a"])(s,l["a"].name,l["a"]),Object(o["a"])(s,u["a"].name,u["a"]),Object(o["a"])(s,c["a"].name,c["a"]),Object(o["a"])(s,"CommonNav",p["a"]),s),created:function(){this.$store.dispatch("userAddress")},computed:Object(r["a"])({},Object(f["c"])({addressList:function(t){return t.user.addressList},addressId:function(t){return t.user.addressId}}),{iSaddress:{get:function(){return this.$store.state.user.addressId},set:function(t){this.$store.dispatch("userAddressDefault",{address_id:t})}},isRouterlink:function(){return this.$route.query.routerLink?this.$route.query.routerLink:""},isWeiXin:function(){return v["a"].isWeixinBrowser()}}),methods:{userAddressDelete:function(t,e){1!=e?this.$store.dispatch("userAddressDelete",{address_id:t}):Object(h["a"])("Әдепкі мекен-жайды жоя алмайсыз")},checkoutRouterEdit:function(t){this.$router.push({name:"editAddressForm",params:{id:t},query:this.$route.query})},checkoutRouterAdd:function(){this.$router.push({name:"addAddressForm",query:this.$route.query})},wxAddress:function(){this.$router.push({name:"addAddressForm",query:{wximport:!0}})},checkoutRouter:function(t){var e=this;this.$store.dispatch("setChangeConsignee",{address_id:t.id}).then(function(t){t.data&&e.$route.query&&(e.$route.query.rec_type?"supplier"==e.$route.query.rec_type?e.$router.push({name:e.$route.query.routerLink,query:{rec_type:e.$route.query.rec_type,rec_id:e.$route.query.rec_id}}):e.$router.push({name:e.$route.query.routerLink,query:{rec_type:e.$route.query.rec_type,type_id:e.$route.query.type_id,team_id:e.$route.query.team_id}}):"crowdfunding-checkout"==e.$route.query.routerLink?e.$router.push({name:e.$route.query.routerLink,query:{pid:e.$route.query.pid,id:e.$route.query.id,number:e.$route.query.number}}):e.$router.push({name:e.$route.query.routerLink,rec_type:e.$route.query.rec_type,type_id:e.$route.query.type_id}))})}},watch:{addressId:function(){this.$store.dispatch("userAddress")}}},_=m,b=(i("195b"),i("2877")),y=Object(b["a"])(_,n,a,!1,null,"6067aaac",null);y.options.__file="Index.vue";e["default"]=y.exports},"6fd6":function(t,e,i){},7744:function(t,e,i){"use strict";var s=i("c31d"),n=i("2638"),a=i.n(n),r=i("a142"),o=i("dfaf"),c=i("ba31"),u=i("48f4"),l=i("ad06"),d=Object(r["j"])("cell"),h=d[0],f=d[1];function v(t,e,i,s){var n=e.icon,o=e.size,d=e.title,h=e.label,v=e.value,p=e.isLink,m=e.arrowDirection,_=i.title||Object(r["c"])(d),b=i["default"]||Object(r["c"])(v),y=i.label||Object(r["c"])(h),k=y&&t("div",{class:[f("label"),e.labelClass]},[i.label?i.label():h]),g=_&&t("div",{class:[f("title"),e.titleClass],style:e.titleStyle},[i.title?i.title():t("span",[d]),k]),$=b&&t("div",{class:[f("value",{alone:!i.title&&!d}),e.valueClass]},[i["default"]?i["default"]():t("span",[v])]),C=i.icon?i.icon():n&&t(l["a"],{class:f("left-icon"),attrs:{name:n}}),x=i["right-icon"],w=x?x():p&&t(l["a"],{class:f("right-icon"),attrs:{name:m?"arrow-"+m:"arrow"}}),q=function(t){Object(c["a"])(s,"click",t),Object(u["a"])(s)},j={center:e.center,required:e.required,borderless:!e.border,clickable:p||e.clickable};return o&&(j[o]=o),t("div",a()([{class:f(j),on:{click:q}},Object(c["b"])(s)]),[C,g,$,w,i.extra&&i.extra()])}v.props=Object(s["a"])({},o["a"],u["c"],{clickable:Boolean,arrowDirection:String}),e["a"]=h(v)},"9f14":function(t,e,i){"use strict";var s=i("a142"),n=i("0a26"),a=Object(s["j"])("radio"),r=a[0],o=a[1];e["a"]=r({mixins:[Object(n["a"])("van-radio-group",o)],computed:{currentValue:{get:function(){return this.parent?this.parent.value:this.value},set:function(t){(this.parent||this).$emit("input",t)}},checked:function(){return this.currentValue===this.name}},methods:{onClickIcon:function(){this.isDisabled||(this.currentValue=this.name)},onClickLabel:function(){this.isDisabled||this.labelDisabled||(this.currentValue=this.name)}}})},a44c:function(t,e,i){"use strict";i("68ef")},c194:function(t,e,i){"use strict";i("68ef")},c1ee:function(t,e,i){"use strict";var s=i("6fd6"),n=i.n(s);n.a},d567:function(t,e,i){"use strict";var s=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"sus-nav"},[i("div",{staticClass:"common-nav",class:{active:!0===t.navType},attrs:{id:"moveDiv"},on:{touchstart:t.down,touchmove:t.move,touchend:t.end}},[i("div",{staticClass:"right-cont",attrs:{id:"rightDiv"}},[i("ul",[i("li",{on:{click:function(e){t.routerLink("home")}}},[i("i",{staticClass:"iconfont icon-zhuye"}),i("p",[t._v(t._s(t.$t("lang.home")))])]),"drp"!=t.routerName&&"crowd_funding"!=t.routerName&&"team"!=t.routerName&&"supplier"!=t.routerName&&"presale"!=t.routerName?i("li",{on:{click:function(e){t.routerLink("search")}}},[i("i",{staticClass:"iconfont icon-search"}),i("p",[t._v(t._s(t.$t("lang.search")))])]):t._e(),i("li",{on:{click:function(e){t.routerLink("catalog")}}},[i("i",{staticClass:"iconfont icon-menu"}),i("p",[t._v(t._s(t.$t("lang.category")))])]),i("li",{on:{click:function(e){t.routerLink("cart")}}},[i("i",{staticClass:"iconfont icon-cart"}),i("p",[t._v(t._s(t.$t("lang.cart")))])]),i("li",{on:{click:function(e){t.routerLink("user")}}},[i("i",{staticClass:"iconfont icon-gerenzhongxin"}),i("p",[t._v(t._s(t.$t("lang.personal_center")))])]),"team"==t.routerName?i("li",{on:{click:function(e){t.routerLink("team")}}},[i("i",{staticClass:"iconfont icon-wodetuandui"}),i("p",[t._v(t._s(t.$t("lang.my_team")))])]):t._e(),"supplier"==t.routerName?i("li",{on:{click:function(e){t.routerLink("supplier")}}},[i("i",{staticClass:"iconfont icon-wodetuandui"}),i("p",[t._v(t._s(t.$t("lang.suppliers")))])]):t._e(),t._t("aloneNav")],2)]),i("div",{staticClass:"nav-icon",on:{click:t.handelNav}},[t._v(t._s(t.$t("lang.quick_navigation")))])]),i("div",{staticClass:"common-show",class:{active:!0===t.navType},on:{click:function(e){return e.stopPropagation(),t.handelShow(e)}}})])},n=[],a=(i("3846"),i("cadf"),i("551c"),i("097d"),{props:["routerName"],data:function(){return{navType:!1,flags:!1,position:{x:0,y:0},nx:"",ny:"",dx:"",dy:"",xPum:"",yPum:""}},mounted:function(){this.flags=!1},methods:{handelNav:function(){this.navType=1!=this.navType},handelShow:function(){this.navType=!1},down:function(t){var e;this.flags=!0,e=t.touches?t.touches[0]:t,this.position.x=e.clientX,this.position.y=e.clientY,this.dx=moveDiv.offsetLeft,this.dy=moveDiv.offsetTop},move:function(t){var e,i,s,n;(t.preventDefault(),this.flags)&&(e=t.touches?t.touches[0]:t,i=document.documentElement.clientHeight,s=moveDiv.clientHeight,this.nx=e.clientX-this.position.x,this.ny=e.clientY-this.position.y,this.xPum=this.dx+this.nx,this.yPum=this.dy+this.ny,this.navType?this.yPum>0&&(n=i-s-this.yPum>0?i-s-this.yPum:0):(s+=rightDiv.clientHeight,this.yPum-s>0&&(n=i-this.yPum>0?i-this.yPum:0)),moveDiv.style.bottom=n+"px")},end:function(){this.flags=!1},routerLink:function(t){var e=this;"home"==t||"catalog"==t||"search"==t||"user"==t?setTimeout(function(){uni.getEnv(function(i){i.plus||i.miniprogram?"home"==t?uni.reLaunch({url:"../../pages/index/index"}):"catalog"==t?uni.reLaunch({url:"../../pages/category/category"}):"search"==t?uni.reLaunch({url:"../../pages/search/search"}):"user"==t&&uni.reLaunch({url:"../../pages/user/user"}):"search"==t?e.$router.push({name:"search"}):e.$router.push({name:t})})},100):e.$router.push({name:t})}}}),r=a,o=(i("c1ee"),i("2877")),c=Object(o["a"])(r,s,n,!1,null,null,null);c.options.__file="CommonNav.vue";e["a"]=c.exports},dde9:function(t,e,i){},dfaf:function(t,e,i){"use strict";i.d(e,"a",function(){return s});var s={icon:String,size:String,center:Boolean,isLink:Boolean,required:Boolean,titleStyle:null,titleClass:null,valueClass:null,labelClass:null,title:[String,Number],value:[String,Number],label:[String,Number],border:{type:Boolean,default:!0}}},e27c:function(t,e,i){"use strict";var s=i("a142"),n=Object(s["j"])("radio-group"),a=n[0],r=n[1];e["a"]=a({props:{value:null,disabled:Boolean},watch:{value:function(t){this.$emit("change",t)}},render:function(t){return t("div",{class:r()},[this.slots()])}})},f331:function(t,e,i){"use strict";i.d(e,"a",function(){return s});var s={data:function(){return{parent:null}},methods:{findParent:function(t){var e=this.$parent;while(e){if(e.$options.name===t){this.parent=e;break}e=e.$parent}}}}}}]);