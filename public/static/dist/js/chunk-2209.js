(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-2209"],{"0653":function(t,e,i){"use strict";i("68ef")},1146:function(t,e,i){},"34e9":function(t,e,i){"use strict";var s=i("2638"),n=i.n(s),a=i("a142"),r=i("ba31"),l=Object(a["j"])("cell-group"),o=l[0],c=l[1];function u(t,e,i,s){var a=t("div",n()([{class:[c(),{"van-hairline--top-bottom":e.border}]},Object(r["b"])(s,!0)]),[i["default"]&&i["default"]()]);return e.title?t("div",[t("div",{class:c("title")},[e.title]),a]):a}u.props={title:String,border:{type:Boolean,default:!0}},e["a"]=o(u)},"4c9b":function(t,e,i){"use strict";i.r(e);var s,n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"con_main"},[i("section",{staticClass:"section-list"},[i("div",{staticClass:"detail-title"},[t._v(t._s(t.$t("lang.return_apply_title")))]),i("div",{staticClass:"bg-color-write"},[t.refoundDetail?i("div",{staticClass:"product-list product-list-small"},[i("ul",[i("li",[i("div",{staticClass:"product-div"},[i("div",{staticClass:"product-list-img"},[i("img",{staticClass:"img",attrs:{src:t.refoundDetail.goods_thumb}})]),i("div",{staticClass:"product-info"},[i("h4",[t._v(t._s(t.refoundDetail.goods_name))]),i("div",{staticClass:"p-attr"},[t._v(t._s(t.refoundDetail.goods_attr))]),i("div",{staticClass:"price"},[i("label",{domProps:{innerHTML:t._s(t.refoundDetail.goods_price)}}),i("span",[t._v("x"+t._s(t.refoundDetail.return_number))])]),t.refoundDetail.get_order_return?i("div",{staticClass:"p-t-remark"},[t._v(t._s(t.refoundDetail.get_order_return.attr_val))]):t._e()])])])])]):t._e()])]),i("section",{staticClass:"section-list"},[i("div",{staticClass:"detail-title"},[t._v(t._s(t.$t("lang.detail_info")))]),i("ul",{staticClass:"user-refound-box"},[i("li",[i("div",[t._v(t._s(t.$t("lang.return_sn_user"))+":")]),i("div",{staticClass:"value color-red"},[t._v(t._s(t.refoundDetail.return_sn))])]),i("li",[i("label",[t._v(t._s(t.$t("lang.apply_time"))+":")]),i("div",{staticClass:"value color-red"},[t._v(t._s(t.refoundDetail.apply_time))])]),i("li",[i("label",[t._v(t._s(t.$t("lang.service_type"))+":")]),i("div",{staticClass:"value color-red"},[0==t.refoundDetail.return_type?[t._v("\n                    "+t._s(t.$t("lang.order_return_type_0"))+"\n                    ")]:1==t.refoundDetail.return_type?[t._v("\n                    "+t._s(t.$t("lang.order_return_type_1"))+"\n                    ")]:2==t.refoundDetail.return_type?[t._v("\n                    "+t._s(t.$t("lang.order_return_type_2"))+"\n                    ")]:[t._v("\n                    "+t._s(t.$t("lang.order_return_type_3"))+"\n                    ")]],2)])]),i("div",{staticClass:"detail-title m-top10"},[t._v(t._s(t.$t("lang.order_status")))]),i("ul",{staticClass:"user-refound-box"},[i("li",[i("label",[t._v(t._s(t.$t("lang.order_status"))+":")]),i("div",{staticClass:"value color-red"},[t._v(t._s(t.refoundDetail.return_status)+" - "+t._s(t.refoundDetail.refound_status))])]),i("li",[i("label",[t._v(t._s(t.$t("lang.problem_desc"))+":")]),i("div",{staticClass:"value color-red"},[t._v(t._s(t.refoundDetail.return_brief))])]),6==t.refoundDetail.return_status?[i("li",[i("label",[t._v(t._s(t.$t("lang.refusal_cause"))+":")]),i("div",{staticClass:"value color-red"},[t._v(t._s(t.refoundDetail.action_note))])])]:[i("li",[i("label",[t._v(t._s(t.$t("lang.return_reason"))+":")]),i("div",{staticClass:"value color-red"},[t._v(t._s(t.refoundDetail.return_cause))])])],t.refoundDetail.return_shipping_fee>0?[i("li",[i("label",[t._v(t._s(t.$t("lang.return_shipping"))+":")]),i("div",{staticClass:"value"},[i("div",{staticClass:"price"},[i("span",{domProps:{innerHTML:t._s(t.refoundDetail.formated_return_shipping_fee)}})])])])]:t._e(),1==t.refoundDetail.return_type||3==t.refoundDetail.return_type?[i("li",[i("label",[t._v(t._s(t.$t("lang.amount_return"))+":")]),i("div",{staticClass:"value"},[i("div",{staticClass:"price"},[i("span",{domProps:{innerHTML:t._s(t.refoundDetail.formated_should_return)}})])])])]:t._e()],2),t.refoundDetail.img_list&&t.refoundDetail.img_list.length>0?[i("ul",{staticClass:"user-refound-box b-color-f m-top10"},[i("li",{staticClass:"dis-box"},[i("div",[t._v(t._s(t.$t("lang.voucher_pic"))+":")]),t._m(0)]),i("div",{staticClass:"goods-evaluation-page b-color-f tab-con refound-list-box"},[i("div",{staticClass:"my-gallery",attrs:{"data-pswp-uid":"5"}},t._l(t.refoundDetail.img_list,function(t,e){return i("figure",{key:e},[i("div",[i("a",{attrs:{href:t.img_file,"data-size":"640x640"}},[i("img",{staticClass:"img",attrs:{src:t.img_file}})])])])})),t._m(1)])])]:t._e(),i("ul",{staticClass:"user-refound-box m-top10"},[i("li",[i("label",[t._v(t._s(t.$t("lang.consignee"))+":")]),i("div",{staticClass:"value"},[t._v(t._s(t.refoundDetail.addressee))])]),i("li",[i("label",[t._v(t._s(t.$t("lang.phone_number"))+":")]),i("div",{staticClass:"value"},[t._v(t._s(t.refoundDetail.phone))])]),i("li",[i("label",[t._v(t._s(t.$t("lang.address_alt"))+":")]),i("div",{staticClass:"value"},[t._v(t._s(t.refoundDetail.address_detail))])])]),1==t.refoundDetail.agree_apply&&3!=t.refoundDetail.return_type?[t.refoundDetail.back_shipp_shipping?[i("div",{staticClass:"detail-title m-top10"},[t._v(t._s(t.$t("lang.express_info"))+" "),i("span",{staticClass:"help color-red"},[t._v("("+t._s(t.$t("lang.user_sent"))+")")])]),i("ul",{staticClass:"user-refound-box b-color-f m-top04"},[i("li",{staticClass:"dis-box"},[i("div",[t._v(t._s(t.$t("lang.express_company"))+":")]),i("div",{staticClass:"box-flex"},[i("p",{staticClass:"col-3 text-right"},[t._v(t._s(t.refoundDetail.back_shipp_shipping))])])]),i("li",{staticClass:"dis-box"},[i("div",[t._v(t._s(t.$t("lang.courier_sz"))+":")]),i("div",{staticClass:"box-flex"},[i("p",{staticClass:"col-3 text-right"},[t._v(t._s(t.refoundDetail.back_invoice_no))])])]),t.refoundDetail.back_invoice_no_btn?i("li",{staticClass:"dis-box"},[i("div",{staticClass:"box-flex"},[i("p",{staticClass:"col-3 text-right n-refound-btn"},[i("a",{staticClass:"btn-default-new current",attrs:{href:t.refoundDetail.back_invoice_no_btn}},[t._v(t._s(t.$t("lang.order_tracking")))])])])]):t._e()])]:[i("div",{staticClass:"detail-title m-top10"},[t._v(t._s(t.$t("lang.express_info"))+" "),i("span",{staticClass:"help color-red"},[t._v("("+t._s(t.$t("lang.fill_in_express_info"))+")")])]),i("van-cell-group",{staticClass:"van-cell-noright m-top08"},[i("van-cell",{attrs:{title:t.$t("lang.label_express_company")}},[i("div",{staticClass:"select-one-1"},[i("select",{directives:[{name:"model",rawName:"v-model",value:t.shippingSelect,expression:"shippingSelect"}],staticClass:"select form-control parent_cause_select",on:{change:function(e){var i=Array.prototype.filter.call(e.target.options,function(t){return t.selected}).map(function(t){var e="_value"in t?t._value:t.value;return e});t.shippingSelect=e.target.multiple?i:i[0]}}},[t._l(t.shipping_list,function(e){return i("option",{domProps:{value:e.shipping_id}},[t._v(t._s(e.shipping_name))])}),i("option",{attrs:{value:"999"}},[t._v(t._s(t.$t("lang.outer_express")))])],2)])]),999==t.shippingSelect?i("van-cell",{attrs:{title:t.$t("lang.label_outer_express")}},[i("van-field",{staticClass:"van-cell-ptb0",attrs:{placeholder:t.$t("lang.fill_in_express_company")},model:{value:t.other_express,callback:function(e){t.other_express=e},expression:"other_express"}})],1):t._e(),i("van-cell",{attrs:{title:t.$t("lang.label_courier_sz")}},[i("van-field",{staticClass:"van-cell-ptb0",attrs:{placeholder:t.$t("lang.fill_in_courier_sz")},model:{value:t.express_sn,callback:function(e){t.express_sn=e},expression:"express_sn"}})],1),i("div",{staticClass:"filter-btn"},[i("div",{staticClass:"btn btn-submit",on:{click:t.submitBtn}},[t._v(t._s(t.$t("lang.subimt")))])])],1)],t.refoundDetail.out_shipp_shipping?[i("div",{staticClass:"detail-title m-top10"},[t._v(t._s(t.$t("lang.express_info"))+" "),i("span",{staticClass:"help color-red"},[t._v("("+t._s(t.$t("lang.seller_shipping"))+")")])]),i("ul",{staticClass:"user-refound-box b-color-f m-top04"},[i("li",{staticClass:"dis-box"},[i("div",[t._v(t._s(t.$t("lang.express_company"))+":")]),i("div",{staticClass:"box-flex"},[i("p",{staticClass:"col-3 text-right"},[t._v(t._s(t.refoundDetail.out_shipp_shipping))])])]),i("li",{staticClass:"dis-box"},[i("div",[t._v(t._s(t.$t("lang.courier_sz"))+":")]),i("div",{staticClass:"box-flex"},[i("p",{staticClass:"col-3 text-right"},[t._v(t._s(t.refoundDetail.out_invoice_no))])])]),t.refoundDetail.out_invoice_no_btn?i("li",{staticClass:"dis-box"},[i("div",{staticClass:"box-flex"},[i("p",{staticClass:"col-3 text-right n-refound-btn"},[i("a",{staticClass:"btn-default-new current",attrs:{href:t.refoundDetail.out_invoice_no_btn}},[t._v(t._s(t.$t("lang.order_tracking")))])])])]):t._e()])]:t._e()]:t._e()],2),3==t.refoundDetail.status?[i("div",{staticClass:"filter-btn"},[i("div",{staticClass:"btn btn-submit",on:{click:t.receivedOrder}},[t._v(t._s(t.$t("lang.received")))])])]:4==t.refoundDetail.status||1==t.refoundDetail.status?[i("div",{staticClass:"filter-btn"},[i("div",{staticClass:"btn btn-submit"},[t._v(t._s(t.$t("lang.ss_received")))])])]:6==t.refoundDetail.status?[i("div",{staticClass:"filter-btn"},[i("div",{staticClass:"btn btn-submit"},[t._v(t._s(t.$t("lang.denied")))])])]:0==t.refoundDetail.agree_apply?[i("div",{staticClass:"filter-btn"},[i("div",{staticClass:"btn btn-submit"},[t._v(t._s(t.$t("lang.to_be_agreed")))])])]:t._e(),i("CommonNav")],2)},a=[function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"box-flex"},[i("p",{staticClass:"col-3 text-right"})])},function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"refound-list-box-bg"},[i("div",{staticClass:"goods-list-close position-abo"},[i("i",{staticClass:"iconfont icon-guanbi2 text-r"})])])}],r=(i("ac6a"),i("9395")),l=i("88d8"),o=(i("e7e5"),i("d399")),c=(i("e17f"),i("2241")),u=(i("66b9"),i("b650")),d=(i("be7f"),i("565f")),_=(i("0653"),i("34e9")),f=(i("7f7f"),i("c194"),i("7744")),p=i("4328"),h=i.n(p),v=i("2f62"),g=i("6567"),b=i("d567"),m={data:function(){return{shippingSelect:999,other_express:"",express_sn:""}},components:(s={},Object(l["a"])(s,f["a"].name,f["a"]),Object(l["a"])(s,_["a"].name,_["a"]),Object(l["a"])(s,d["a"].name,d["a"]),Object(l["a"])(s,u["a"].name,u["a"]),Object(l["a"])(s,c["a"].name,c["a"]),Object(l["a"])(s,o["a"].name,o["a"]),Object(l["a"])(s,"ProductList",g["a"]),Object(l["a"])(s,"CommonNav",b["a"]),s),created:function(){this.refoundLoad()},computed:Object(r["a"])({},Object(v["c"])({refoundDetail:function(t){return t.other.supplierRefoundDetail}}),{shipping_list:function(){return this.refoundDetail.shipping_list?this.refoundDetail.shipping_list:[]}}),methods:{refoundLoad:function(){this.$store.dispatch("setSupplierReturnDatail",{ret_id:this.$route.query.ret_id})},receivedOrder:function(){var t=this;c["a"].confirm({message:this.$t("lang.confirm_received"),className:"text-center"}).then(function(){t.$http.post("".concat(window.ROOT_URL,"api/refound/affirm_receive"),h.a.stringify({ret_id:t.$route.query.ret_id})).then(function(e){var i=e.data.data;Object(o["a"])(i.msg),0==i.code&&t.refoundLoad()})}).catch(function(){})},submitBtn:function(){var t=this;if(""==this.other_express)return Object(o["a"])(this.$t("lang.fill_in_express_company")),!1;if(""==this.express_sn)return Object(o["a"])(this.$t("lang.fill_in_courier_sz")),!1;var e={shipping_id:this.shippingSelect,express_name:this.other_express,express_sn:this.express_sn,order_id:this.refoundDetail.order_id,ret_id:this.refoundDetail.ret_id};this.$http.post("".concat(window.ROOT_URL,"api/refound/edit_express"),h.a.stringify(e)).then(function(e){var i=e.data.data;Object(o["a"])(i.msg),0==i.code&&t.refoundLoad()})}},watch:{shippingSelect:function(){var t=this;this.shipping_list.length>0&&this.shipping_list.forEach(function(e){e.shipping_id==t.shippingSelect&&(t.other_express=e.shipping_name)})}}},C=m,x=i("2877"),y=Object(x["a"])(C,n,a,!1,null,null,null);y.options.__file="ReturnDetail.vue";e["default"]=y.exports},"565f":function(t,e,i){"use strict";var s=i("2638"),n=i.n(s),a=i("c31d"),r=i("ad06"),l=i("7744"),o=i("dfaf"),c=i("a142"),u=i("db78"),d=i("023d"),_=i("90c6"),f=Object(c["j"])("field"),p=f[0],h=f[1];e["a"]=p({inheritAttrs:!1,props:Object(a["a"])({},o["a"],{error:Boolean,leftIcon:String,rightIcon:String,readonly:Boolean,clearable:Boolean,labelWidth:[String,Number],labelAlign:String,inputAlign:String,onIconClick:Function,autosize:[Boolean,Object],errorMessage:String,errorMessageAlign:String,type:{type:String,default:"text"}}),data:function(){return{focused:!1}},watch:{value:function(){this.$nextTick(this.adjustSize)}},mounted:function(){this.format(),this.$nextTick(this.adjustSize)},computed:{showClear:function(){return this.clearable&&this.focused&&""!==this.value&&Object(c["c"])(this.value)&&!this.readonly},listeners:function(){return Object(a["a"])({},this.$listeners,{input:this.onInput,keypress:this.onKeypress,focus:this.onFocus,blur:this.onBlur})},labelStyle:function(){var t=this.labelWidth;if(t){var e=Object(_["a"])(String(t))?t+"px":t;return{maxWidth:e,minWidth:e}}}},methods:{focus:function(){this.$refs.input&&this.$refs.input.focus()},blur:function(){this.$refs.input&&this.$refs.input.blur()},format:function(t){void 0===t&&(t=this.$refs.input);var e=t,i=e.value,s=this.$attrs.maxlength;return"number"===this.type&&Object(c["c"])(s)&&i.length>s&&(i=i.slice(0,s),t.value=i),i},onInput:function(t){this.$emit("input",this.format(t.target))},onFocus:function(t){this.focused=!0,this.$emit("focus",t),this.readonly&&this.blur()},onBlur:function(t){this.focused=!1,this.$emit("blur",t),Object(c["d"])()&&window.scrollTo(0,Object(d["b"])())},onClickLeftIcon:function(){this.$emit("click-left-icon")},onClickRightIcon:function(){this.$emit("click-icon"),this.$emit("click-right-icon"),this.onIconClick&&this.onIconClick()},onClear:function(t){Object(u["c"])(t),this.$emit("input",""),this.$emit("clear")},onKeypress:function(t){if("number"===this.type){var e=t.keyCode,i=-1===String(this.value).indexOf("."),s=e>=48&&e<=57||46===e&&i||45===e;s||Object(u["c"])(t)}"search"===this.type&&13===t.keyCode&&this.blur(),this.$emit("keypress",t)},adjustSize:function(){var t=this.$refs.input;if("textarea"===this.type&&this.autosize&&t){t.style.height="auto";var e=t.scrollHeight;if(Object(c["f"])(this.autosize)){var i=this.autosize,s=i.maxHeight,n=i.minHeight;s&&(e=Math.min(e,s)),n&&(e=Math.max(e,n))}e&&(t.style.height=e+"px")}},renderInput:function(){var t=this.$createElement,e={ref:"input",class:h("control",this.inputAlign),domProps:{value:this.value},attrs:Object(a["a"])({},this.$attrs,{readonly:this.readonly}),on:this.listeners};return"textarea"===this.type?t("textarea",n()([{},e])):t("input",n()([{attrs:{type:this.type}},e]))},renderLeftIcon:function(){var t=this.$createElement,e=this.slots("left-icon")||this.leftIcon;if(e)return t("div",{class:h("left-icon"),on:{click:this.onClickLeftIcon}},[this.slots("left-icon")||t(r["a"],{attrs:{name:this.leftIcon}})])},renderRightIcon:function(){var t=this.$createElement,e=this.slots,i=e("right-icon")||e("icon")||this.rightIcon||this.icon;if(i)return t("div",{class:h("right-icon"),on:{click:this.onClickRightIcon}},[e("right-icon")||e("icon")||t(r["a"],{attrs:{name:this.rightIcon||this.icon}})])}},render:function(t){var e,i=this.slots,s=this.labelAlign,n={icon:this.renderLeftIcon};return i("label")&&(n.title=function(){return i("label")}),t(l["a"],{attrs:{icon:this.leftIcon,size:this.size,title:this.label,center:this.center,border:this.border,isLink:this.isLink,required:this.required,titleStyle:this.labelStyle,titleClass:h("label",s)},class:h((e={error:this.error,disabled:this.$attrs.disabled},e["label-"+s]=s,e["min-height"]="textarea"===this.type&&!this.autosize,e)),scopedSlots:n},[t("div",{class:h("body")},[this.renderInput(),this.showClear&&t(r["a"],{attrs:{name:"clear"},class:h("clear"),on:{touchstart:this.onClear}}),this.renderRightIcon(),i("button")&&t("div",{class:h("button")},[i("button")])]),this.errorMessage&&t("div",{class:h("error-message",this.errorMessageAlign)},[this.errorMessage])])}})},6567:function(t,e,i){"use strict";var s=function(){var t=this,e=t.$createElement,i=t._self._c||e;return t.goodsInfo?i("div",{staticClass:"product-list product-list-small"},[i("ul",[i("li",[i("div",{staticClass:"product-div"},[i("div",{staticClass:"product-list-img"},[i("img",{staticClass:"img",attrs:{src:t.goodsInfo.goods_img}})]),i("div",{staticClass:"product-info"},[i("h4",[t._v(t._s(t.goodsInfo.goods_name))]),i("div",{staticClass:"price"},[i("em",[t._v(t._s(t.goodsInfo.shop_price_formated))]),i("span",[t._v("x1")])]),i("div",{staticClass:"p-t-remark"},[t._v(t._s(t.goodsInfo.attr_name))])])])])])]):t._e()},n=[],a={props:["goodsInfo"],data:function(){return{}}},r=a,l=i("2877"),o=Object(l["a"])(r,s,n,!1,null,null,null);o.options.__file="ProductList.vue";e["a"]=o.exports},"66b9":function(t,e,i){"use strict";i("68ef")},"6fd6":function(t,e,i){},7744:function(t,e,i){"use strict";var s=i("c31d"),n=i("2638"),a=i.n(n),r=i("a142"),l=i("dfaf"),o=i("ba31"),c=i("48f4"),u=i("ad06"),d=Object(r["j"])("cell"),_=d[0],f=d[1];function p(t,e,i,s){var n=e.icon,l=e.size,d=e.title,_=e.label,p=e.value,h=e.isLink,v=e.arrowDirection,g=i.title||Object(r["c"])(d),b=i["default"]||Object(r["c"])(p),m=i.label||Object(r["c"])(_),C=m&&t("div",{class:[f("label"),e.labelClass]},[i.label?i.label():_]),x=g&&t("div",{class:[f("title"),e.titleClass],style:e.titleStyle},[i.title?i.title():t("span",[d]),C]),y=b&&t("div",{class:[f("value",{alone:!i.title&&!d}),e.valueClass]},[i["default"]?i["default"]():t("span",[p])]),$=i.icon?i.icon():n&&t(u["a"],{class:f("left-icon"),attrs:{name:n}}),D=i["right-icon"],k=D?D():h&&t(u["a"],{class:f("right-icon"),attrs:{name:v?"arrow-"+v:"arrow"}}),O=function(t){Object(o["a"])(s,"click",t),Object(c["a"])(s)},j={center:e.center,required:e.required,borderless:!e.border,clickable:h||e.clickable};return l&&(j[l]=l),t("div",a()([{class:f(j),on:{click:O}},Object(o["b"])(s)]),[$,x,y,k,i.extra&&i.extra()])}p.props=Object(s["a"])({},l["a"],c["c"],{clickable:Boolean,arrowDirection:String}),e["a"]=_(p)},"90c6":function(t,e,i){"use strict";function s(t){return/^\d+$/.test(t)}i.d(e,"a",function(){return s})},be7f:function(t,e,i){"use strict";i("68ef"),i("1146")},c194:function(t,e,i){"use strict";i("68ef")},c1ee:function(t,e,i){"use strict";var s=i("6fd6"),n=i.n(s);n.a},d567:function(t,e,i){"use strict";var s=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"sus-nav"},[i("div",{staticClass:"common-nav",class:{active:!0===t.navType},attrs:{id:"moveDiv"},on:{touchstart:t.down,touchmove:t.move,touchend:t.end}},[i("div",{staticClass:"right-cont",attrs:{id:"rightDiv"}},[i("ul",[i("li",{on:{click:function(e){t.routerLink("home")}}},[i("i",{staticClass:"iconfont icon-zhuye"}),i("p",[t._v(t._s(t.$t("lang.home")))])]),"drp"!=t.routerName&&"crowd_funding"!=t.routerName&&"team"!=t.routerName&&"supplier"!=t.routerName&&"presale"!=t.routerName?i("li",{on:{click:function(e){t.routerLink("search")}}},[i("i",{staticClass:"iconfont icon-search"}),i("p",[t._v(t._s(t.$t("lang.search")))])]):t._e(),i("li",{on:{click:function(e){t.routerLink("catalog")}}},[i("i",{staticClass:"iconfont icon-menu"}),i("p",[t._v(t._s(t.$t("lang.category")))])]),i("li",{on:{click:function(e){t.routerLink("cart")}}},[i("i",{staticClass:"iconfont icon-cart"}),i("p",[t._v(t._s(t.$t("lang.cart")))])]),i("li",{on:{click:function(e){t.routerLink("user")}}},[i("i",{staticClass:"iconfont icon-gerenzhongxin"}),i("p",[t._v(t._s(t.$t("lang.personal_center")))])]),"team"==t.routerName?i("li",{on:{click:function(e){t.routerLink("team")}}},[i("i",{staticClass:"iconfont icon-wodetuandui"}),i("p",[t._v(t._s(t.$t("lang.my_team")))])]):t._e(),"supplier"==t.routerName?i("li",{on:{click:function(e){t.routerLink("supplier")}}},[i("i",{staticClass:"iconfont icon-wodetuandui"}),i("p",[t._v(t._s(t.$t("lang.suppliers")))])]):t._e(),t._t("aloneNav")],2)]),i("div",{staticClass:"nav-icon",on:{click:t.handelNav}},[t._v(t._s(t.$t("lang.quick_navigation")))])]),i("div",{staticClass:"common-show",class:{active:!0===t.navType},on:{click:function(e){return e.stopPropagation(),t.handelShow(e)}}})])},n=[],a=(i("3846"),{props:["routerName"],data:function(){return{navType:!1,flags:!1,position:{x:0,y:0},nx:"",ny:"",dx:"",dy:"",xPum:"",yPum:""}},mounted:function(){this.flags=!1},methods:{handelNav:function(){this.navType=1!=this.navType},handelShow:function(){this.navType=!1},down:function(t){var e;this.flags=!0,e=t.touches?t.touches[0]:t,this.position.x=e.clientX,this.position.y=e.clientY,this.dx=moveDiv.offsetLeft,this.dy=moveDiv.offsetTop},move:function(t){var e,i,s,n;(t.preventDefault(),this.flags)&&(e=t.touches?t.touches[0]:t,i=document.documentElement.clientHeight,s=moveDiv.clientHeight,this.nx=e.clientX-this.position.x,this.ny=e.clientY-this.position.y,this.xPum=this.dx+this.nx,this.yPum=this.dy+this.ny,this.navType?this.yPum>0&&(n=i-s-this.yPum>0?i-s-this.yPum:0):(s+=rightDiv.clientHeight,this.yPum-s>0&&(n=i-this.yPum>0?i-this.yPum:0)),moveDiv.style.bottom=n+"px")},end:function(){this.flags=!1},routerLink:function(t){var e=this;"home"==t||"catalog"==t||"search"==t||"user"==t?setTimeout(function(){uni.getEnv(function(i){i.plus||i.miniprogram?"home"==t?uni.reLaunch({url:"../../pages/index/index"}):"catalog"==t?uni.reLaunch({url:"../../pages/category/category"}):"search"==t?uni.reLaunch({url:"../../pages/search/search"}):"user"==t&&uni.reLaunch({url:"../../pages/user/user"}):"search"==t?e.$router.push({name:"search"}):e.$router.push({name:t})})},100):e.$router.push({name:t})}}}),r=a,l=(i("c1ee"),i("2877")),o=Object(l["a"])(r,s,n,!1,null,null,null);o.options.__file="CommonNav.vue";e["a"]=o.exports},dfaf:function(t,e,i){"use strict";i.d(e,"a",function(){return s});var s={icon:String,size:String,center:Boolean,isLink:Boolean,required:Boolean,titleStyle:null,titleClass:null,valueClass:null,labelClass:null,title:[String,Number],value:[String,Number],label:[String,Number],border:{type:Boolean,default:!0}}}}]);