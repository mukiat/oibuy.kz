(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-2547"],{"0653":function(t,e,i){"use strict";i("68ef")},"0a26":function(t,e,i){"use strict";i.d(e,"a",function(){return s});var a=i("ad06"),n=i("f331"),s=function(t,e){return{mixins:[n["a"]],props:{name:null,value:null,disabled:Boolean,checkedColor:String,labelPosition:String,labelDisabled:Boolean,shape:{type:String,default:"round"},bindGroup:{type:Boolean,default:!0}},created:function(){this.bindGroup&&this.findParent(t)},computed:{isDisabled:function(){return this.parent&&this.parent.disabled||this.disabled},iconStyle:function(){var t=this.checkedColor;if(t&&this.checked&&!this.isDisabled)return{borderColor:t,backgroundColor:t}}},render:function(){var t=this,i=arguments[0],n=this.slots,s=this.checked,o=n("icon",{checked:s})||i(a["a"],{attrs:{name:"success"},style:this.iconStyle}),c=n()&&i("span",{class:e("label",[this.labelPosition,{disabled:this.isDisabled}]),on:{click:this.onClickLabel}},[n()]);return i("div",{class:e(),on:{click:function(e){t.$emit("click",e)}}},[i("div",{class:e("icon",[this.shape,{disabled:this.isDisabled,checked:s}]),on:{click:this.onClickIcon}},[o]),c])}}}},1146:function(t,e,i){},"19de":function(t,e,i){"use strict";i("68ef"),i("5fbe")},"1a23":function(t,e,i){"use strict";var a=i("2638"),n=i.n(a),s=i("a142"),o=i("543e"),c={value:null,loading:Boolean,disabled:Boolean,activeColor:String,inactiveColor:String,activeValue:{type:null,default:!0},inactiveValue:{type:null,default:!1},size:{type:String,default:"30px"}},r=i("ba31"),l=Object(s["j"])("switch"),u=l[0],d=l[1];function h(t,e,i,a){var s=e.value,c=e.loading,l=e.disabled,u=e.activeValue,h=e.inactiveValue,f=s===u,A={fontSize:e.size,backgroundColor:f?e.activeColor:e.inactiveColor},g=function(){if(!l&&!c){var t=f?h:u;Object(r["a"])(a,"input",t),Object(r["a"])(a,"change",t)}};return t("div",n()([{class:d({on:f,disabled:l}),style:A,on:{click:g}},Object(r["b"])(a)]),[t("div",{class:d("node")},[c&&t(o["a"],{class:d("loading")})])])}h.props=c;e["a"]=u(h)},"1f5b":function(t,e,i){},"234f":function(t,e,i){"use strict";var a=i("c31d"),n=i("2638"),s=i.n(n),o=i("a142"),c=i("b650"),r=i("ba31"),l=i("48f4"),u=Object(o["j"])("goods-action-big-btn"),d=u[0],h=u[1];function f(t,e,i,a){var n=function(t){Object(r["a"])(a,"click",t),Object(l["a"])(a)};return t(c["a"],s()([{attrs:{square:!0,size:"large",loading:e.loading,disabled:e.disabled,type:e.primary?"danger":"warning"},class:h(),on:{click:n}},Object(r["b"])(a)]),[i["default"]?i["default"]():e.text])}f.props=Object(a["a"])({},l["c"],{text:String,primary:Boolean,loading:Boolean,disabled:Boolean}),e["a"]=d(f)},"24db":function(t,e){t.exports="data:image/png;base64,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"},"34e9":function(t,e,i){"use strict";var a=i("2638"),n=i.n(a),s=i("a142"),o=i("ba31"),c=Object(s["j"])("cell-group"),r=c[0],l=c[1];function u(t,e,i,a){var s=t("div",n()([{class:[l(),{"van-hairline--top-bottom":e.border}]},Object(o["b"])(a,!0)]),[i["default"]&&i["default"]()]);return e.title?t("div",[t("div",{class:l("title")},[e.title]),s]):s}u.props={title:String,border:{type:Boolean,default:!0}},e["a"]=r(u)},"3b42":function(t,e,i){},"4cf9":function(t,e,i){},"4ddd":function(t,e,i){"use strict";i("68ef"),i("dde9")},"565f":function(t,e,i){"use strict";var a=i("2638"),n=i.n(a),s=i("c31d"),o=i("ad06"),c=i("7744"),r=i("dfaf"),l=i("a142"),u=i("db78"),d=i("023d"),h=i("90c6"),f=Object(l["j"])("field"),A=f[0],g=f[1];e["a"]=A({inheritAttrs:!1,props:Object(s["a"])({},r["a"],{error:Boolean,leftIcon:String,rightIcon:String,readonly:Boolean,clearable:Boolean,labelWidth:[String,Number],labelAlign:String,inputAlign:String,onIconClick:Function,autosize:[Boolean,Object],errorMessage:String,errorMessageAlign:String,type:{type:String,default:"text"}}),data:function(){return{focused:!1}},watch:{value:function(){this.$nextTick(this.adjustSize)}},mounted:function(){this.format(),this.$nextTick(this.adjustSize)},computed:{showClear:function(){return this.clearable&&this.focused&&""!==this.value&&Object(l["c"])(this.value)&&!this.readonly},listeners:function(){return Object(s["a"])({},this.$listeners,{input:this.onInput,keypress:this.onKeypress,focus:this.onFocus,blur:this.onBlur})},labelStyle:function(){var t=this.labelWidth;if(t){var e=Object(h["a"])(String(t))?t+"px":t;return{maxWidth:e,minWidth:e}}}},methods:{focus:function(){this.$refs.input&&this.$refs.input.focus()},blur:function(){this.$refs.input&&this.$refs.input.blur()},format:function(t){void 0===t&&(t=this.$refs.input);var e=t,i=e.value,a=this.$attrs.maxlength;return"number"===this.type&&Object(l["c"])(a)&&i.length>a&&(i=i.slice(0,a),t.value=i),i},onInput:function(t){this.$emit("input",this.format(t.target))},onFocus:function(t){this.focused=!0,this.$emit("focus",t),this.readonly&&this.blur()},onBlur:function(t){this.focused=!1,this.$emit("blur",t),Object(l["d"])()&&window.scrollTo(0,Object(d["b"])())},onClickLeftIcon:function(){this.$emit("click-left-icon")},onClickRightIcon:function(){this.$emit("click-icon"),this.$emit("click-right-icon"),this.onIconClick&&this.onIconClick()},onClear:function(t){Object(u["c"])(t),this.$emit("input",""),this.$emit("clear")},onKeypress:function(t){if("number"===this.type){var e=t.keyCode,i=-1===String(this.value).indexOf("."),a=e>=48&&e<=57||46===e&&i||45===e;a||Object(u["c"])(t)}"search"===this.type&&13===t.keyCode&&this.blur(),this.$emit("keypress",t)},adjustSize:function(){var t=this.$refs.input;if("textarea"===this.type&&this.autosize&&t){t.style.height="auto";var e=t.scrollHeight;if(Object(l["f"])(this.autosize)){var i=this.autosize,a=i.maxHeight,n=i.minHeight;a&&(e=Math.min(e,a)),n&&(e=Math.max(e,n))}e&&(t.style.height=e+"px")}},renderInput:function(){var t=this.$createElement,e={ref:"input",class:g("control",this.inputAlign),domProps:{value:this.value},attrs:Object(s["a"])({},this.$attrs,{readonly:this.readonly}),on:this.listeners};return"textarea"===this.type?t("textarea",n()([{},e])):t("input",n()([{attrs:{type:this.type}},e]))},renderLeftIcon:function(){var t=this.$createElement,e=this.slots("left-icon")||this.leftIcon;if(e)return t("div",{class:g("left-icon"),on:{click:this.onClickLeftIcon}},[this.slots("left-icon")||t(o["a"],{attrs:{name:this.leftIcon}})])},renderRightIcon:function(){var t=this.$createElement,e=this.slots,i=e("right-icon")||e("icon")||this.rightIcon||this.icon;if(i)return t("div",{class:g("right-icon"),on:{click:this.onClickRightIcon}},[e("right-icon")||e("icon")||t(o["a"],{attrs:{name:this.rightIcon||this.icon}})])}},render:function(t){var e,i=this.slots,a=this.labelAlign,n={icon:this.renderLeftIcon};return i("label")&&(n.title=function(){return i("label")}),t(c["a"],{attrs:{icon:this.leftIcon,size:this.size,title:this.label,center:this.center,border:this.border,isLink:this.isLink,required:this.required,titleStyle:this.labelStyle,titleClass:g("label",a)},class:g((e={error:this.error,disabled:this.$attrs.disabled},e["label-"+a]=a,e["min-height"]="textarea"===this.type&&!this.autosize,e)),scopedSlots:n},[t("div",{class:g("body")},[this.renderInput(),this.showClear&&t(o["a"],{attrs:{name:"clear"},class:g("clear"),on:{touchstart:this.onClear}}),this.renderRightIcon(),i("button")&&t("div",{class:g("button")},[i("button")])]),this.errorMessage&&t("div",{class:g("error-message",this.errorMessageAlign)},[this.errorMessage])])}})},"5fbe":function(t,e,i){},"6fd6":function(t,e,i){},7744:function(t,e,i){"use strict";var a=i("c31d"),n=i("2638"),s=i.n(n),o=i("a142"),c=i("dfaf"),r=i("ba31"),l=i("48f4"),u=i("ad06"),d=Object(o["j"])("cell"),h=d[0],f=d[1];function A(t,e,i,a){var n=e.icon,c=e.size,d=e.title,h=e.label,A=e.value,g=e.isLink,p=e.arrowDirection,b=i.title||Object(o["c"])(d),m=i["default"]||Object(o["c"])(A),v=i.label||Object(o["c"])(h),y=v&&t("div",{class:[f("label"),e.labelClass]},[i.label?i.label():h]),C=b&&t("div",{class:[f("title"),e.titleClass],style:e.titleStyle},[i.title?i.title():t("span",[d]),y]),k=m&&t("div",{class:[f("value",{alone:!i.title&&!d}),e.valueClass]},[i["default"]?i["default"]():t("span",[A])]),B=i.icon?i.icon():n&&t(u["a"],{class:f("left-icon"),attrs:{name:n}}),_=i["right-icon"],w=_?_():g&&t(u["a"],{class:f("right-icon"),attrs:{name:p?"arrow-"+p:"arrow"}}),I=function(t){Object(r["a"])(a,"click",t),Object(l["a"])(a)},D={center:e.center,required:e.required,borderless:!e.border,clickable:g||e.clickable};return c&&(D[c]=c),t("div",s()([{class:f(D),on:{click:I}},Object(r["b"])(a)]),[B,C,k,w,i.extra&&i.extra()])}A.props=Object(a["a"])({},c["a"],l["c"],{clickable:Boolean,arrowDirection:String}),e["a"]=h(A)},"8a58":function(t,e,i){"use strict";i("68ef"),i("4d75")},"90c6":function(t,e,i){"use strict";function a(t){return/^\d+$/.test(t)}i.d(e,"a",function(){return a})},"93ac":function(t,e,i){"use strict";i("68ef"),i("4cf9")},"9f14":function(t,e,i){"use strict";var a=i("a142"),n=i("0a26"),s=Object(a["j"])("radio"),o=s[0],c=s[1];e["a"]=o({mixins:[Object(n["a"])("van-radio-group",c)],computed:{currentValue:{get:function(){return this.parent?this.parent.value:this.value},set:function(t){(this.parent||this).$emit("input",t)}},checked:function(){return this.currentValue===this.name}},methods:{onClickIcon:function(){this.isDisabled||(this.currentValue=this.name)},onClickLabel:function(){this.isDisabled||this.labelDisabled||(this.currentValue=this.name)}}})},a44c:function(t,e,i){"use strict";i("68ef")},b000:function(t,e,i){"use strict";i("68ef"),i("d9d2")},b528:function(t,e,i){"use strict";var a=i("c31d"),n=i("2638"),s=i.n(n),o=i("a142"),c=i("ad06"),r=i("ba31"),l=i("48f4"),u=Object(o["j"])("goods-action-mini-btn"),d=u[0],h=u[1];function f(t,e,i,a){var n=function(t){Object(r["a"])(a,"click",t),Object(l["a"])(a)};return t("div",s()([{class:[h(),"van-hairline"],on:{click:n}},Object(r["b"])(a)]),[t(c["a"],{class:[h("icon"),e.iconClass],attrs:{tag:"div",info:e.info,name:e.icon}}),i["default"]?i["default"]():e.text])}f.props=Object(a["a"])({},l["c"],{text:String,icon:String,info:[String,Number],iconClass:null}),e["a"]=d(f)},bb33:function(t,e,i){"use strict";var a=i("2638"),n=i.n(a),s=i("a142"),o=i("ba31"),c=Object(s["j"])("goods-action"),r=c[0],l=c[1];function u(t,e,i,a){return t("div",n()([{class:l({"safe-area-inset-bottom":e.safeAreaInsetBottom})},Object(o["b"])(a,!0)]),[i["default"]&&i["default"]()])}u.props={safeAreaInsetBottom:Boolean},e["a"]=r(u)},be39:function(t,e,i){"use strict";i("68ef"),i("3b42")},be7f:function(t,e,i){"use strict";i("68ef"),i("1146")},c194:function(t,e,i){"use strict";i("68ef")},c1ee:function(t,e,i){"use strict";var a=i("6fd6"),n=i.n(a);n.a},d567:function(t,e,i){"use strict";var a=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"sus-nav"},[i("div",{staticClass:"common-nav",class:{active:!0===t.navType},attrs:{id:"moveDiv"},on:{touchstart:t.down,touchmove:t.move,touchend:t.end}},[i("div",{staticClass:"right-cont",attrs:{id:"rightDiv"}},[i("ul",[i("li",{on:{click:function(e){t.routerLink("home")}}},[i("i",{staticClass:"iconfont icon-zhuye"}),i("p",[t._v(t._s(t.$t("lang.home")))])]),"drp"!=t.routerName&&"crowd_funding"!=t.routerName&&"team"!=t.routerName&&"supplier"!=t.routerName&&"presale"!=t.routerName?i("li",{on:{click:function(e){t.routerLink("search")}}},[i("i",{staticClass:"iconfont icon-search"}),i("p",[t._v(t._s(t.$t("lang.search")))])]):t._e(),i("li",{on:{click:function(e){t.routerLink("catalog")}}},[i("i",{staticClass:"iconfont icon-menu"}),i("p",[t._v(t._s(t.$t("lang.category")))])]),i("li",{on:{click:function(e){t.routerLink("cart")}}},[i("i",{staticClass:"iconfont icon-cart"}),i("p",[t._v(t._s(t.$t("lang.cart")))])]),i("li",{on:{click:function(e){t.routerLink("user")}}},[i("i",{staticClass:"iconfont icon-gerenzhongxin"}),i("p",[t._v(t._s(t.$t("lang.personal_center")))])]),"team"==t.routerName?i("li",{on:{click:function(e){t.routerLink("team")}}},[i("i",{staticClass:"iconfont icon-wodetuandui"}),i("p",[t._v(t._s(t.$t("lang.my_team")))])]):t._e(),"supplier"==t.routerName?i("li",{on:{click:function(e){t.routerLink("supplier")}}},[i("i",{staticClass:"iconfont icon-wodetuandui"}),i("p",[t._v(t._s(t.$t("lang.suppliers")))])]):t._e(),t._t("aloneNav")],2)]),i("div",{staticClass:"nav-icon",on:{click:t.handelNav}},[t._v(t._s(t.$t("lang.quick_navigation")))])]),i("div",{staticClass:"common-show",class:{active:!0===t.navType},on:{click:function(e){return e.stopPropagation(),t.handelShow(e)}}})])},n=[],s=(i("3846"),{props:["routerName"],data:function(){return{navType:!1,flags:!1,position:{x:0,y:0},nx:"",ny:"",dx:"",dy:"",xPum:"",yPum:""}},mounted:function(){this.flags=!1},methods:{handelNav:function(){this.navType=1!=this.navType},handelShow:function(){this.navType=!1},down:function(t){var e;this.flags=!0,e=t.touches?t.touches[0]:t,this.position.x=e.clientX,this.position.y=e.clientY,this.dx=moveDiv.offsetLeft,this.dy=moveDiv.offsetTop},move:function(t){var e,i,a,n;(t.preventDefault(),this.flags)&&(e=t.touches?t.touches[0]:t,i=document.documentElement.clientHeight,a=moveDiv.clientHeight,this.nx=e.clientX-this.position.x,this.ny=e.clientY-this.position.y,this.xPum=this.dx+this.nx,this.yPum=this.dy+this.ny,this.navType?this.yPum>0&&(n=i-a-this.yPum>0?i-a-this.yPum:0):(a+=rightDiv.clientHeight,this.yPum-a>0&&(n=i-this.yPum>0?i-this.yPum:0)),moveDiv.style.bottom=n+"px")},end:function(){this.flags=!1},routerLink:function(t){var e=this;"home"==t||"catalog"==t||"search"==t||"user"==t?setTimeout(function(){uni.getEnv(function(i){i.plus||i.miniprogram?"home"==t?uni.reLaunch({url:"../../pages/index/index"}):"catalog"==t?uni.reLaunch({url:"../../pages/category/category"}):"search"==t?uni.reLaunch({url:"../../pages/search/search"}):"user"==t&&uni.reLaunch({url:"../../pages/user/user"}):"search"==t?e.$router.push({name:"search"}):e.$router.push({name:t})})},100):e.$router.push({name:t})}}}),o=s,c=(i("c1ee"),i("2877")),r=Object(c["a"])(o,a,n,!1,null,null,null);r.options.__file="CommonNav.vue";e["a"]=r.exports},d9d2:function(t,e,i){},dde9:function(t,e,i){},dfaf:function(t,e,i){"use strict";i.d(e,"a",function(){return a});var a={icon:String,size:String,center:Boolean,isLink:Boolean,required:Boolean,titleStyle:null,titleClass:null,valueClass:null,labelClass:null,title:[String,Number],value:[String,Number],label:[String,Number],border:{type:Boolean,default:!0}}},e27c:function(t,e,i){"use strict";var a=i("a142"),n=Object(a["j"])("radio-group"),s=n[0],o=n[1];e["a"]=s({props:{value:null,disabled:Boolean},watch:{value:function(t){this.$emit("change",t)}},render:function(t){return t("div",{class:o()},[this.slots()])}})},e41f:function(t,e,i){"use strict";var a=i("a142"),n=i("6605"),s=Object(a["j"])("popup"),o=s[0],c=s[1];e["a"]=o({mixins:[n["a"]],props:{position:String,transition:String,overlay:{type:Boolean,default:!0},closeOnClickOverlay:{type:Boolean,default:!0}},render:function(t){var e,i=this;if(this.shouldRender){var a=this.position,n=function(t){return function(){return i.$emit(t)}},s=this.transition||(a?"van-popup-slide-"+a:"van-fade");return t("transition",{attrs:{name:s},on:{afterEnter:n("opened"),afterLeave:n("closed")}},[t("div",{directives:[{name:"show",value:this.value}],class:c((e={},e[a]=a,e))},[this.slots()])])}}})},efa0:function(t,e,i){"use strict";var a=i("2638"),n=i.n(a),s=i("a142"),o=i("ba31"),c=i("b650"),r=Object(s["j"])("submit-bar"),l=r[0],u=r[1],d=r[2];function h(t,e,i,a){var s=e.tip,r=e.price,l="number"===typeof r;return t("div",n()([{class:u({"safe-area-inset-bottom":e.safeAreaInsetBottom})},Object(o["b"])(a)]),[i.top&&i.top(),(i.tip||s)&&t("div",{class:u("tip")},[s,i.tip&&i.tip()]),t("div",{class:u("bar")},[i["default"]&&i["default"](),t("div",{class:u("text")},[l&&[t("span",[e.label||d("label")]),t("span",{class:u("price")},[e.currency+" "+(r/100).toFixed(e.decimalLength)])]]),t(c["a"],{attrs:{square:!0,size:"large",type:e.buttonType,loading:e.loading,disabled:e.disabled,text:e.loading?"":e.buttonText},on:{click:function(){Object(o["a"])(a,"submit")}}})])])}h.props={tip:String,label:String,loading:Boolean,disabled:Boolean,buttonText:String,safeAreaInsetBottom:Boolean,price:{type:Number,default:null},decimalLength:{type:Number,default:2},currency:{type:String,default:"¥"},buttonType:{type:String,default:"danger"}},e["a"]=l(h)},f331:function(t,e,i){"use strict";i.d(e,"a",function(){return a});var a={data:function(){return{parent:null}},methods:{findParent:function(t){var e=this.$parent;while(e){if(e.$options.name===t){this.parent=e;break}e=e.$parent}}}}},f908:function(t,e,i){"use strict";i("68ef"),i("1f5b")},f983:function(t,e,i){"use strict";i.r(e);var a,n=function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"con"},[t.crowdCheckoutData?a("div",{staticClass:"flow-checkout"},[a("section",{staticClass:"show flow-checkout-item flow-checkout-adr",on:{click:t.checkoutAddress}},[""!=t.crowdCheckoutData.default_address?a("div",[a("van-cell-group",[a("van-cell",{attrs:{title:t.consignee_title,label:t.consignee_address,icon:"location","is-link":""}})],1)],1):a("div",[a("div",{staticClass:"not-address text-center color-9"},[t._v(t._s(t.$t("lang.not_address")))])])]),t.cart_goods?a("div",{staticClass:"goods-li bg-color-write"},[a("router-link",{staticClass:"show bg-color-write li",attrs:{to:{name:"crowdfunding-detail",params:{id:t.cart_goods.id}}}},[a("div",{staticClass:"left p-r"},[t.cart_goods.title_img?a("img",{staticClass:"img",attrs:{src:t.cart_goods.title_img}}):a("img",{staticClass:"img",attrs:{src:i("24db")}})]),a("div",{staticClass:"right"},[a("h4",{staticClass:"f-05 color-3"},[t._v(t._s(t.cart_goods.title))]),a("div",{staticClass:"goods-num dis-box"},[a("div",{staticClass:"box-flex f-03 color-7"},[t._v(t._s(t.$t("lang.label_crowdfunding_fund"))),a("em",{staticClass:"color-red",domProps:{innerHTML:t._s(t.crowdCheckoutData.cart_goods.formated_price)}}),t._v(t._s(t.$t("lang.yuan")))]),a("div",{staticClass:"list f-02 color-9"},[t._v(t._s(t.$t("lang.support_number"))+"\r\n                            "),a("span",{staticClass:"color-red"},[t._v(t._s(t.cart_goods.join_num))]),t._v(t._s(t.$t("lang.ren"))+"\r\n                        ")])]),a("div",{staticClass:"ect-progress dis-box"},[a("p",{staticClass:"wrap box-flex"},[a("span",{staticClass:"bar",style:{width:t.cart_goods.baifen_bi+"%"}},[a("i",{staticClass:"color"})])]),a("p",{staticClass:"txt f-02"},[t._v(t._s(t.cart_goods.baifen_bi)+"%")])]),a("div",{staticClass:"goods-cont f-03 color-7"},[t._v("\r\n                        "+t._s(t.cart_goods.content)+"\r\n                    ")])])])],1):t._e(),a("van-cell-group",{staticClass:"van-cell-noright m-top08"},[a("van-cell",{attrs:{title:t.$t("lang.delivery_cost")}},[a("div",{attrs:{solt:"value"}},[a("em",{staticClass:"color-red"},[t._v(t._s(t.shipping_fee))])])]),a("van-cell",{staticClass:"b-min b-min-t",attrs:{title:t.$t("lang.buyer_message")}},[a("div",{attrs:{solt:"value"}},[a("van-field",{staticClass:"van-cell-ptb0",attrs:{placeholder:t.$t("lang.buyer_message_placeholder")},model:{value:t.value,callback:function(e){t.value=e},expression:"value"}})],1)]),a("van-cell",{staticClass:"b-min b-min-t"},[a("div",{attrs:{solt:"value"}},[a("span",[t._v(t._s(t.$t("lang.gong"))+t._s(t.crowdCheckoutData.number)+t._s(t.$t("lang.total_amount_propmt_alt"))+"：")]),a("em",{staticClass:"color-red",domProps:{innerHTML:t._s(t.crowdCheckoutData.total.amount_formated)}})])])],1),a("section",{staticClass:"checkout-goods-other",on:{click:t.crowsdPay}},[a("van-cell-group",{staticClass:"van-cell-noright m-top08"},[a("van-cell",{attrs:{title:t.$t("lang.payment_mode"),"is-link":""},model:{value:t.pay_name,callback:function(e){t.pay_name=e},expression:"pay_name"}})],1)],1),a("section",[t.crowdCheckoutData.use_surplus>0?a("van-cell-group",{staticClass:"van-cell-noright m-top08"},[a("van-cell",{staticClass:"van-cell-title b-min b-min-b"},[a("div",{attrs:{slot:"title"},slot:"title"},[t._v(t._s(t.$t("lang.is_use_balance")))]),a("van-switch",{staticClass:"fr",attrs:{size:"20px"},model:{value:t.surplusSelf,callback:function(e){t.surplusSelf=e},expression:"surplusSelf"}})],1)],1):t._e()],1),a("section",{staticClass:"order-detail-submit order-checkout-submit"},[a("van-submit-bar",{attrs:{price:t.amountTotal,label:t.$t("lang.label_total_amount_payable"),"button-text":t.$t("lang.immediate_payment")},on:{submit:t.onSubmit}})],1)],1):t._e(),a("van-popup",{staticClass:"attr-goods-box crowd-pay",attrs:{position:"bottom"},model:{value:t.showBase,callback:function(e){t.showBase=e},expression:"showBase"}},[a("div",{staticClass:"attr-goods-header wallet-bt"},[a("div",{staticClass:"dis-box"},[a("div",{staticClass:"box-flex f-05 color-3"},[t._v(t._s(t.$t("lang.payment_mode")))]),a("div",{on:{click:t.closeSku}},[a("i",{staticClass:"iconfont icon-guanbi f-05 color-9"})])])]),a("van-radio-group",{staticClass:"bg-color-write",model:{value:t.radio,callback:function(e){t.radio=e},expression:"radio"}},t._l(t.payment_method,function(e,i){return a("van-radio",{key:i,class:{active:t.pay_id==e.pay_id},attrs:{name:e.pay_id},on:{click:function(i){t.payment_method_select(e.pay_id,e.pay_name)}}},[a("div",{staticClass:"dis-box detail-scheme bg-color-write li"},[a("div",{staticClass:"box-flex"},[t._v(t._s(e.pay_name))]),a("div",{staticClass:"left-icon"},[a("label",[a("i",{staticClass:"iconfont icon-gou"})])])])])}))],1),a("CommonNav",{attrs:{routerName:t.routerName}},[a("li",{attrs:{slot:"aloneNav"},slot:"aloneNav"},[a("router-link",{attrs:{to:{name:"crowdfunding"}}},[a("i",{staticClass:"iconfont icon-shequ2"}),a("p",[t._v(t._s(t.$t("lang.square")))])])],1),a("li",{attrs:{slot:"aloneNav"},slot:"aloneNav"},[a("router-link",{attrs:{to:{name:"crowdfunding-user"}}},[a("i",{staticClass:"iconfont icon-gerenzhongxin"}),a("p",[t._v(t._s(t.$t("lang.centre")))])])],1)])],1)},s=[],o=(i("ac6a"),i("9395")),c=i("88d8"),r=(i("e7e5"),i("d399")),l=(i("b000"),i("1a23")),u=(i("4ddd"),i("9f14")),d=(i("a44c"),i("e27c")),h=(i("8a58"),i("e41f")),f=(i("be7f"),i("565f")),A=(i("f908"),i("b528")),g=(i("19de"),i("234f")),p=(i("93ac"),i("bb33")),b=(i("be39"),i("efa0")),m=(i("0653"),i("34e9")),v=(i("7f7f"),i("c194"),i("7744")),y=(i("cadf"),i("551c"),i("097d"),i("2f62")),C=i("d567"),k={data:function(){return{routerName:"crowd_funding",pay_name:"",cur_id:1,value:"",radio:1,apart:"apart",showBase:!1,use_surplus_val:0,pay_id:null}},components:(a={CommonNav:C["a"]},Object(c["a"])(a,v["a"].name,v["a"]),Object(c["a"])(a,m["a"].name,m["a"]),Object(c["a"])(a,b["a"].name,b["a"]),Object(c["a"])(a,p["a"].name,p["a"]),Object(c["a"])(a,g["a"].name,g["a"]),Object(c["a"])(a,A["a"].name,A["a"]),Object(c["a"])(a,f["a"].name,f["a"]),Object(c["a"])(a,h["a"].name,h["a"]),Object(c["a"])(a,d["a"].name,d["a"]),Object(c["a"])(a,u["a"].name,u["a"]),Object(c["a"])(a,l["a"].name,l["a"]),Object(c["a"])(a,r["a"].name,r["a"]),a),created:function(){this.checkoutDefault()},computed:Object(o["a"])({},Object(y["c"])({crowdCheckoutData:function(t){return t.crowdfunding.crowdCheckoutData}}),{consignee_title:function(){return this.crowdCheckoutData.default_address?this.crowdCheckoutData.default_address.consignee+" "+this.crowdCheckoutData.default_address.mobile:""},consignee_address:function(){return this.crowdCheckoutData.default_address?this.crowdCheckoutData.default_address.province+this.crowdCheckoutData.default_address.city+this.crowdCheckoutData.default_address.district+this.crowdCheckoutData.default_address.address:""},surplusSelf:{get:function(){return 0!=this.use_surplus_val},set:function(t){this.use_surplus_val=1==t?1:0}},cart_goods:function(){return this.crowdCheckoutData.cart_goods},order:function(){return this.crowdCheckoutData.order},total:function(){return this.crowdCheckoutData.total},amountTotal:function(){return 100*this.crowdCheckoutData.total.amount},payment_method:function(){return this.crowdCheckoutData.payment_list?this.crowdCheckoutData.payment_list:[]},shipping_fee:function(){return 0!=this.total.shipping_fee?this.total.shipping_fee:this.$t("lang.free_shipping")}}),methods:{checkoutDefault:function(){this.$store.dispatch("setCrowdfundingCheckout",{pid:this.$route.query.pid,id:this.$route.query.id,number:this.$route.query.number})},crowsdPay:function(){this.showBase=!0},closeSku:function(){this.showBase=!1},payment_method_select:function(t,e){this.pay_id=t,this.pay_name=e},checkoutAddress:function(){var t={routerLink:"crowdfunding-checkout"};this.$route.query&&(t={routerLink:"crowdfunding-checkout",pid:this.$route.query.pid,id:this.$route.query.id,number:this.$route.query.number}),this.$router.push({name:"address",query:t})},onSubmit:function(){var t=this;this.$store.dispatch("setCrowdfundingDone",{pid:this.$route.query.pid,id:this.$route.query.id,number:this.$route.query.number,pay_id:this.pay_id,is_surplus:this.use_surplus_val}).then(function(e){1==e.data.error?Object(r["a"])(e.data.msg):t.$router.push({name:"done",query:{order_sn:e.data}})})}},watch:{crowdCheckoutData:function(){var t=this;""==this.pay_name&&"address"!=this.crowdCheckoutData.error&&this.payment_method.forEach(function(e){"onlinepay"==e.pay_code&&(t.pay_name=e.pay_name,t.pay_id=e.pay_id)}),"address"==this.crowdCheckoutData.error&&this.$router.push({name:"addAddressForm",query:{routerLink:"crowdfunding-checkout",entrance:"first",pid:this.$route.query.pid,id:this.$route.query.id,number:this.$route.query.number}})},payment_method:function(){if(""==this.payment_method)return Object(r["a"])(this.$t("lang.payment_method_not_installed")),!1}}},B=k,_=i("2877"),w=Object(_["a"])(B,n,s,!1,null,null,null);w.options.__file="Checkout.vue";e["default"]=w.exports}}]);