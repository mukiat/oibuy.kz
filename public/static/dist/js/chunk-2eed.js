(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-2eed"],{2994:function(t,i,e){"use strict";e("68ef"),e("c0c2")},"2bdd":function(t,i,e){"use strict";var s=e("a142"),a=e("543e"),n=e("db78"),o=e("023d"),r=Object(s["j"])("list"),c=r[0],l=r[1],u=r[2];i["a"]=c({model:{prop:"loading"},props:{error:Boolean,loading:Boolean,finished:Boolean,errorText:String,loadingText:String,finishedText:String,immediateCheck:{type:Boolean,default:!0},offset:{type:Number,default:300},direction:{type:String,default:"down"}},mounted:function(){this.scroller=Object(o["c"])(this.$el),this.handler(!0),this.immediateCheck&&this.$nextTick(this.check)},destroyed:function(){this.handler(!1)},activated:function(){this.handler(!0)},deactivated:function(){this.handler(!1)},watch:{loading:function(){this.$nextTick(this.check)},finished:function(){this.$nextTick(this.check)}},methods:{check:function(){if(!(this.loading||this.finished||this.error)){var t=this.$el,i=this.scroller,e=Object(o["e"])(i);if(e&&"none"!==window.getComputedStyle(t).display&&null!==t.offsetParent){var s=this.offset,a=this.direction;n()&&(this.$emit("input",!0),this.$emit("load"))}}function n(){if(t===i){var n=Object(o["d"])(t);if("up"===a)return n<=s;var r=n+e;return i.scrollHeight-r<=s}if("up"===a)return Object(o["d"])(i)-Object(o["a"])(t)<=s;var c=Object(o["a"])(t)+Object(o["e"])(t)-Object(o["a"])(i);return c-e<=s}},clickErrorText:function(){this.$emit("update:error",!1),this.$nextTick(this.check)},handler:function(t){this.binded!==t&&(this.binded=t,(t?n["b"]:n["a"])(this.scroller,"scroll",this.check))}},render:function(t){return t("div",{class:l()},["down"===this.direction&&this.slots(),this.loading&&t("div",{class:l("loading"),key:"loading"},[this.slots("loading")||[t(a["a"],{class:l("loading-icon")}),t("span",{class:l("loading-text")},[this.loadingText||u("loading")])]]),this.finished&&this.finishedText&&t("div",{class:l("finished-text")},[this.finishedText]),this.error&&this.errorText&&t("div",{on:{click:this.clickErrorText},class:l("error-text")},[this.errorText]),"up"===this.direction&&this.slots()])}})},"4ddd":function(t,i,e){"use strict";e("68ef"),e("dde9")},6682:function(t,i,e){"use strict";e.r(i);var s,a=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("div",{directives:[{name:"waterfall-lower",rawName:"v-waterfall-lower",value:t.loadMore,expression:"loadMore"}],staticClass:"con",attrs:{"waterfall-disabled":"disabled","waterfall-offset":"300"}},[e("div",{staticClass:"header-list-goods"},[e("Search",{attrs:{mode:t.mode,isFilter:t.isFilter,shopId:t.shopId},on:{getViewSwitch:t.handleViewSwitch}}),e("FilterTab",{attrs:{filter:t.filter,isPopupVisible:t.isPopupVisible},on:{getFilter:t.handleFilter,setPopupVisible:t.setPopupVisible}})],1),e("section",{staticClass:"product-list",class:{"product-list-medium":"medium"===t.mode}},[t.shopGoodsList?e("ProductList",{attrs:{data:t.shopGoodsList,routerName:t.routerName}}):t._e(),t.footerCont?e("div",{staticClass:"footer-cont"},[t._v(t._s(t.$t("lang.no_more")))]):t._e(),t.loading?[e("van-loading",{attrs:{type:"spinner",color:"black"}})]:t._e()],2),e("CommonNav"),e("van-popup",{staticClass:"con-filter-warp",attrs:{position:"right"},model:{value:t.isPopupVisible,callback:function(i){t.isPopupVisible=i},expression:"isPopupVisible"}},[e("div",{staticClass:"con-filter-div"},[e("swiper",{attrs:{options:t.swiperOption}},[e("swiper-slide",[e("van-cell-group",[e("van-cell",{attrs:{title:t.$t("lang.brand"),"is-link":""},on:{click:t.selectBrand}},[t._v("\n              "+t._s(t.filter.brandResultName)+"\n            ")])],1),e("van-cell-group",[e("van-cell",{attrs:{title:t.$t("lang.shop_category"),"is-link":""},on:{click:t.selectShopCat}},[t._v("\n              "+t._s(t.filter.catResultName)+"\n            ")])],1)],1)],1)],1),e("div",{staticClass:"filterlayer_bottom_buttons"},[e("span",{staticClass:"filterlayer_bottom_button bg_1",on:{click:t.closeFilter}},[t._v(t._s(t.$t("lang.close")))]),e("span",{staticClass:"filterlayer_bottom_button bg_2",on:{click:t.submitFilter}},[t._v(t._s(t.$t("lang.confirm")))])])]),e("van-popup",{staticClass:"sf_layer",attrs:{position:"right"},model:{value:t.isPopupBrand,callback:function(i){t.isPopupBrand=i},expression:"isPopupBrand"}},[e("div",{staticClass:"sf_layer_sub_title"},[e("strong",[t._v(t._s(t.$t("lang.label_selected_brand")))]),t.filter.brandResultName.length>0?e("span",[t._v(t._s(t.filter.brandResultName))]):t._e()]),e("div",{staticClass:"sf_layer_con"},[e("van-checkbox-group",{on:{change:t.onBrandResult},model:{value:t.filter.brand_id,callback:function(i){t.$set(t.filter,"brand_id",i)},expression:"filter.brand_id"}},t._l(t.filter.brandResult,function(i,s){return e("van-checkbox",{attrs:{name:i.brand_id}},[t._v(t._s(i.brand_name))])}))],1),e("div",{staticClass:"filterlayer_bottom_buttons"},[e("span",{staticClass:"filterlayer_bottom_button bg_1",on:{click:t.cancelSelectBrand}},[t._v(t._s(t.$t("lang.cancel")))]),e("span",{staticClass:"filterlayer_bottom_button bg_2",on:{click:t.confirmSelectBrand}},[t._v(t._s(t.$t("lang.confirm")))])])]),e("van-popup",{staticClass:"sf_layer",attrs:{position:"right"},model:{value:t.isPopupCat,callback:function(i){t.isPopupCat=i},expression:"isPopupCat"}},[e("div",{staticClass:"sf_layer_con sf_layer_con_no"},[e("van-radio-group",{on:{change:t.onCatResult},model:{value:t.cat_id,callback:function(i){t.cat_id=i},expression:"cat_id"}},t._l(t.filter.catResult,function(i,s){return e("van-radio",{attrs:{name:i.cat_id}},[t._v(t._s(i.cat_name))])}))],1),e("div",{staticClass:"filterlayer_bottom_buttons"},[e("span",{staticClass:"filterlayer_bottom_button bg_1",on:{click:t.cancelSelectCat}},[t._v(t._s(t.$t("lang.cancel")))]),e("span",{staticClass:"filterlayer_bottom_button bg_2",on:{click:t.confirmSelectCat}},[t._v(t._s(t.$t("lang.confirm")))])])])],1)},n=[],o=(e("ac6a"),e("55dd"),e("c5f6"),e("88d8")),r=(e("ac1e"),e("543e")),c=(e("a909"),e("3acc")),l=(e("3c32"),e("417e")),u=(e("a44c"),e("e27c")),d=(e("4ddd"),e("9f14")),h=(e("0653"),e("34e9")),p=(e("c194"),e("7744")),f=(e("b000"),e("1a23")),m=(e("8a58"),e("e41f")),_=(e("be7f"),e("565f")),v=(e("7f7f"),e("2994"),e("2bdd")),b=(e("d49c"),e("5487")),g=e("4328"),y=e.n(g),k=(e("2f62"),e("7212")),C=e("c106"),w=e("4ee6"),$=e("1c14"),P=e("d567"),x=e("a454"),L={data:function(){return{disabled:!1,loading:!0,mode:"medium",filter:{sort:"goods_id",order:"desc",promote:"0",brand_id:[],brandResult:[],brandResultName:"",catResult:[],catResultName:""},isFilter:!0,isPopupVisible:!1,isPopupBrand:!1,isPopupCat:!1,swiperOption:{direction:"vertical",slidesPerView:"auto",freeMode:!0},routerName:"goods",cat_id:0,page:1,size:10,footerCont:!1,shopId:this.$route.query.ru_id?this.$route.query.ru_id:0}},directives:{WaterfallLower:Object(b["a"])("lower")},components:(s={Search:C["a"],FilterTab:w["a"],ProductList:$["a"],swiper:k["swiper"],swiperSlide:k["swiperSlide"],CommonNav:P["a"]},Object(o["a"])(s,v["a"].name,v["a"]),Object(o["a"])(s,_["a"].name,_["a"]),Object(o["a"])(s,m["a"].name,m["a"]),Object(o["a"])(s,f["a"].name,f["a"]),Object(o["a"])(s,p["a"].name,p["a"]),Object(o["a"])(s,h["a"].name,h["a"]),Object(o["a"])(s,d["a"].name,d["a"]),Object(o["a"])(s,u["a"].name,u["a"]),Object(o["a"])(s,l["a"].name,l["a"]),Object(o["a"])(s,c["a"].name,c["a"]),Object(o["a"])(s,r["a"].name,r["a"]),s),created:function(){this.$route.query.cat_id&&(this.cat_id=this.$route.query.cat_id),this.getGoodsList()},computed:{shopGoodsList:{get:function(){return this.$store.state.shop.shopGoodsList},set:function(t){this.$store.state.shop.shopGoodsList=t}}},methods:{getGoodsList:function(t){t&&(this.page=t,this.size=10*Number(t)),1==this.filter.promote&&(this.filter.sort="promote"),this.$store.dispatch("setShopGoodsList",{keywords:this.$route.query.keywords,brand_id:this.filter.brand_id,store_id:this.shopId,cat_id:this.cat_id,warehouse_id:"0",area_id:"0",size:this.size,page:this.page,sort:this.filter.sort,order:this.filter.order,type:this.$route.query.type})},handleViewSwitch:function(t){this.mode=t},handleFilter:function(t){this.filter.sort=t.sort,this.filter.order=t.order,this.getGoodsList(1)},setPopupVisible:function(t){this.isPopupVisible=t},selectBrand:function(){var t=this;this.isPopupBrand=0==this.isPopupBrand,this.$http.post("".concat(window.ROOT_URL,"api/catalog/brandlist"),y.a.stringify({keywords:this.$route.query.keywords,cat_id:this.cat_id,ru_id:this.$route.query.ru_id})).then(function(i){var e=i.data.data;e.length>0&&(t.filter.brandResult=e)})},selectShopCat:function(){var t=this;this.isPopupCat=0==this.isPopupCat,this.$http.post("".concat(window.ROOT_URL,"api/catalog/shopcat"),y.a.stringify({ru_id:this.$route.query.ru_id})).then(function(i){var e=i.data.data;e.length>0&&(t.filter.catResult=e)})},closeFilter:function(){this.isPopupVisible=!1},submitFilter:function(){this.getGoodsList(),this.isPopupVisible=!1},onBrandResult:function(){var t=this,i="";this.filter.brand_id.forEach(function(e){t.filter.brandResult.forEach(function(t){e==t.brand_id&&(i+=t.brand_name+",")})}),this.filter.brandResultName=i},cancelSelectBrand:function(){this.filter.brand_id=[],this.filter.brandResultName="",this.isPopupBrand=!1},confirmSelectBrand:function(){this.isPopupBrand=!1},onCatResult:function(){var t=this,i="";this.filter.catResult.forEach(function(e){t.cat_id==e.cat_id&&(i=e.cat_name)}),this.filter.catResultName=i},cancelSelectCat:function(){this.isPopupCat=!1},confirmSelectCat:function(){this.isPopupCat=!1},loadMore:function(){var t=this;setTimeout(function(){t.disabled=!0,t.page*t.size==t.shopGoodsList.length&&(t.page++,t.getGoodsList())},200)}},watch:{shopGoodsList:function(){this.page*this.size==this.shopGoodsList.length?(this.disabled=!1,this.loading=!0):(this.loading=!1,this.footerCont=this.page>1),this.shopGoodsList=x["a"].trimSpace(this.shopGoodsList)},isPopupVisible:function(){0==this.isPopupVisible&&(this.filter.goods_num="0",this.filter.promote="0",this.filter.brand_id=[])}}},O=L,j=e("2877"),N=Object(j["a"])(O,a,n,!1,null,null,null);N.options.__file="Goods.vue";i["default"]=N.exports},"6fd6":function(t,i,e){},"9f14":function(t,i,e){"use strict";var s=e("a142"),a=e("0a26"),n=Object(s["j"])("radio"),o=n[0],r=n[1];i["a"]=o({mixins:[Object(a["a"])("van-radio-group",r)],computed:{currentValue:{get:function(){return this.parent?this.parent.value:this.value},set:function(t){(this.parent||this).$emit("input",t)}},checked:function(){return this.currentValue===this.name}},methods:{onClickIcon:function(){this.isDisabled||(this.currentValue=this.name)},onClickLabel:function(){this.isDisabled||this.labelDisabled||(this.currentValue=this.name)}}})},a44c:function(t,i,e){"use strict";e("68ef")},c0c2:function(t,i,e){},c1ee:function(t,i,e){"use strict";var s=e("6fd6"),a=e.n(s);a.a},d567:function(t,i,e){"use strict";var s=function(){var t=this,i=t.$createElement,e=t._self._c||i;return e("div",{staticClass:"sus-nav"},[e("div",{staticClass:"common-nav",class:{active:!0===t.navType},attrs:{id:"moveDiv"},on:{touchstart:t.down,touchmove:t.move,touchend:t.end}},[e("div",{staticClass:"right-cont",attrs:{id:"rightDiv"}},[e("ul",[e("li",{on:{click:function(i){t.routerLink("home")}}},[e("i",{staticClass:"iconfont icon-zhuye"}),e("p",[t._v(t._s(t.$t("lang.home")))])]),"drp"!=t.routerName&&"crowd_funding"!=t.routerName&&"team"!=t.routerName&&"supplier"!=t.routerName&&"presale"!=t.routerName?e("li",{on:{click:function(i){t.routerLink("search")}}},[e("i",{staticClass:"iconfont icon-search"}),e("p",[t._v(t._s(t.$t("lang.search")))])]):t._e(),e("li",{on:{click:function(i){t.routerLink("catalog")}}},[e("i",{staticClass:"iconfont icon-menu"}),e("p",[t._v(t._s(t.$t("lang.category")))])]),e("li",{on:{click:function(i){t.routerLink("cart")}}},[e("i",{staticClass:"iconfont icon-cart"}),e("p",[t._v(t._s(t.$t("lang.cart")))])]),e("li",{on:{click:function(i){t.routerLink("user")}}},[e("i",{staticClass:"iconfont icon-gerenzhongxin"}),e("p",[t._v(t._s(t.$t("lang.personal_center")))])]),"team"==t.routerName?e("li",{on:{click:function(i){t.routerLink("team")}}},[e("i",{staticClass:"iconfont icon-wodetuandui"}),e("p",[t._v(t._s(t.$t("lang.my_team")))])]):t._e(),"supplier"==t.routerName?e("li",{on:{click:function(i){t.routerLink("supplier")}}},[e("i",{staticClass:"iconfont icon-wodetuandui"}),e("p",[t._v(t._s(t.$t("lang.suppliers")))])]):t._e(),t._t("aloneNav")],2)]),e("div",{staticClass:"nav-icon",on:{click:t.handelNav}},[t._v(t._s(t.$t("lang.quick_navigation")))])]),e("div",{staticClass:"common-show",class:{active:!0===t.navType},on:{click:function(i){return i.stopPropagation(),t.handelShow(i)}}})])},a=[],n=(e("3846"),{props:["routerName"],data:function(){return{navType:!1,flags:!1,position:{x:0,y:0},nx:"",ny:"",dx:"",dy:"",xPum:"",yPum:""}},mounted:function(){this.flags=!1},methods:{handelNav:function(){this.navType=1!=this.navType},handelShow:function(){this.navType=!1},down:function(t){var i;this.flags=!0,i=t.touches?t.touches[0]:t,this.position.x=i.clientX,this.position.y=i.clientY,this.dx=moveDiv.offsetLeft,this.dy=moveDiv.offsetTop},move:function(t){var i,e,s,a;(t.preventDefault(),this.flags)&&(i=t.touches?t.touches[0]:t,e=document.documentElement.clientHeight,s=moveDiv.clientHeight,this.nx=i.clientX-this.position.x,this.ny=i.clientY-this.position.y,this.xPum=this.dx+this.nx,this.yPum=this.dy+this.ny,this.navType?this.yPum>0&&(a=e-s-this.yPum>0?e-s-this.yPum:0):(s+=rightDiv.clientHeight,this.yPum-s>0&&(a=e-this.yPum>0?e-this.yPum:0)),moveDiv.style.bottom=a+"px")},end:function(){this.flags=!1},routerLink:function(t){var i=this;"home"==t||"catalog"==t||"search"==t||"user"==t?setTimeout(function(){uni.getEnv(function(e){e.plus||e.miniprogram?"home"==t?uni.reLaunch({url:"../../pages/index/index"}):"catalog"==t?uni.reLaunch({url:"../../pages/category/category"}):"search"==t?uni.reLaunch({url:"../../pages/search/search"}):"user"==t&&uni.reLaunch({url:"../../pages/user/user"}):"search"==t?i.$router.push({name:"search"}):i.$router.push({name:t})})},100):i.$router.push({name:t})}}}),o=n,r=(e("c1ee"),e("2877")),c=Object(r["a"])(o,s,a,!1,null,null,null);c.options.__file="CommonNav.vue";i["a"]=c.exports},dde9:function(t,i,e){},e27c:function(t,i,e){"use strict";var s=e("a142"),a=Object(s["j"])("radio-group"),n=a[0],o=a[1];i["a"]=n({props:{value:null,disabled:Boolean},watch:{value:function(t){this.$emit("change",t)}},render:function(t){return t("div",{class:o()},[this.slots()])}})}}]);