(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-6927"],{"0a26":function(t,i,e){"use strict";e.d(i,"a",function(){return a});var n=e("ad06"),s=e("f331"),a=function(t,i){return{mixins:[s["a"]],props:{name:null,value:null,disabled:Boolean,checkedColor:String,labelPosition:String,labelDisabled:Boolean,shape:{type:String,default:"round"},bindGroup:{type:Boolean,default:!0}},created:function(){this.bindGroup&&this.findParent(t)},computed:{isDisabled:function(){return this.parent&&this.parent.disabled||this.disabled},iconStyle:function(){var t=this.checkedColor;if(t&&this.checked&&!this.isDisabled)return{borderColor:t,backgroundColor:t}}},render:function(){var t=this,e=arguments[0],s=this.slots,a=this.checked,o=s("icon",{checked:a})||e(n["a"],{attrs:{name:"success"},style:this.iconStyle}),r=s()&&e("span",{class:i("label",[this.labelPosition,{disabled:this.isDisabled}]),on:{click:this.onClickLabel}},[s()]);return e("div",{class:i(),on:{click:function(i){t.$emit("click",i)}}},[e("div",{class:i("icon",[this.shape,{disabled:this.isDisabled,checked:a}]),on:{click:this.onClickIcon}},[o]),r])}}}},1437:function(t,i,e){"use strict";var n=e("c31d"),s=e("a142"),a=e("8624"),o=e("7744"),r=e("dfaf"),c=e("f331"),l=Object(s["j"])("collapse-item"),d=l[0],u=l[1],f=["title","icon","right-icon"];i["a"]=d({mixins:[c["a"]],props:Object(n["a"])({},r["a"],{name:[String,Number],disabled:Boolean,isLink:{type:Boolean,default:!0}}),data:function(){return{show:null,inited:null}},computed:{items:function(){return this.parent.items},index:function(){return this.items.indexOf(this)},currentName:function(){return Object(s["c"])(this.name)?this.name:this.index},expanded:function(){var t=this;if(!this.parent)return null;var i=this.parent.value;return this.parent.accordion?i===this.currentName:i.some(function(i){return i===t.currentName})}},created:function(){this.findParent("van-collapse"),this.items.push(this),this.show=this.expanded,this.inited=this.expanded},destroyed:function(){this.items.splice(this.index,1)},watch:{expanded:function(t,i){var e=this;null!==i&&(t&&(this.show=!0,this.inited=!0),Object(a["a"])(function(){var i=e.$refs,n=i.content,s=i.wrapper;if(n&&s){var o=n.clientHeight;if(o){var r=o+"px";s.style.height=t?0:r,Object(a["a"])(function(){s.style.height=t?r:0})}else e.onTransitionEnd()}}))}},methods:{onClick:function(){if(!this.disabled){var t=this.parent,i=t.accordion&&this.currentName===t.value?"":this.currentName,e=!this.expanded;this.parent["switch"](i,e)}},onTransitionEnd:function(){this.expanded?this.$refs.wrapper.style.height=null:this.show=!1}},render:function(t){var i=this,e=f.reduce(function(t,e){return i.slots(e)&&(t[e]=function(){return i.slots(e)}),t},{});this.slots("value")&&(e["default"]=function(){return i.slots("value")});var s=t(o["a"],{class:u("title",{disabled:this.disabled,expanded:this.expanded}),on:{click:this.onClick},scopedSlots:e,props:Object(n["a"])({},this.$props)}),a=this.inited&&t("div",{directives:[{name:"show",value:this.show}],ref:"wrapper",class:u("wrapper"),on:{transitionend:this.onTransitionEnd}},[t("div",{ref:"content",class:u("content")},[this.slots()])]);return t("div",{class:[u(),{"van-hairline--top":this.index}]},[s,a])}})},"14d8":function(t,i,e){"use strict";var n=e("668e"),s=e.n(n);s.a},2381:function(t,i,e){},"342a":function(t,i,e){"use strict";e("68ef"),e("bff0")},"3acc":function(t,i,e){"use strict";var n=e("a142"),s=Object(n["j"])("checkbox-group"),a=s[0],o=s[1];i["a"]=a({props:{max:Number,value:Array,disabled:Boolean},watch:{value:function(t){this.$emit("change",t)}},render:function(t){return t("div",{class:o()},[this.slots()])}})},"3c32":function(t,i,e){"use strict";e("68ef"),e("2381")},"417e":function(t,i,e){"use strict";var n=e("a142"),s=e("0a26"),a=Object(n["j"])("checkbox"),o=a[0],r=a[1];i["a"]=o({mixins:[Object(s["a"])("van-checkbox-group",r)],computed:{checked:{get:function(){return this.parent?-1!==this.parent.value.indexOf(this.name):this.value},set:function(t){this.parent?this.setParentValue(t):this.$emit("input",t)}}},watch:{value:function(t){this.$emit("change",t)}},methods:{toggle:function(){var t=this,i=!this.checked;clearTimeout(this.toggleTask),this.toggleTask=setTimeout(function(){t.checked=i})},onClickIcon:function(){this.isDisabled||this.toggle()},onClickLabel:function(){this.isDisabled||this.labelDisabled||this.toggle()},setParentValue:function(t){var i=this.parent,e=i.value.slice();if(t){if(i.max&&e.length>=i.max)return;-1===e.indexOf(this.name)&&(e.push(this.name),i.$emit("input",e))}else{var n=e.indexOf(this.name);-1!==n&&(e.splice(n,1),i.$emit("input",e))}}}})},5246:function(t,i,e){"use strict";e("68ef"),e("8a0b")},"5d17":function(t,i,e){"use strict";e("68ef")},"668e":function(t,i,e){},"66b9":function(t,i,e){"use strict";e("68ef")},"6b41":function(t,i,e){"use strict";var n=e("2638"),s=e.n(n),a=e("a142"),o=e("ba31"),r=e("ad06"),c=Object(a["j"])("nav-bar"),l=c[0],d=c[1];function u(t,i,e,n){return t("div",s()([{class:[d({fixed:i.fixed}),{"van-hairline--bottom":i.border}],style:{zIndex:i.zIndex}},Object(o["b"])(n)]),[t("div",{class:d("left"),on:{click:n.listeners["click-left"]||a["h"]}},[e.left?e.left():[i.leftArrow&&t(r["a"],{class:d("arrow"),attrs:{name:"arrow-left"}}),i.leftText&&t("span",{class:d("text")},[i.leftText])]]),t("div",{class:[d("title"),"van-ellipsis"]},[e.title?e.title():i.title]),t("div",{class:d("right"),on:{click:n.listeners["click-right"]||a["h"]}},[e.right?e.right():i.rightText&&t("span",{class:d("text")},[i.rightText])])])}u.props={title:String,fixed:Boolean,leftText:String,rightText:String,leftArrow:Boolean,border:{type:Boolean,default:!0},zIndex:{type:Number,default:1}},i["a"]=l(u)},7744:function(t,i,e){"use strict";var n=e("c31d"),s=e("2638"),a=e.n(s),o=e("a142"),r=e("dfaf"),c=e("ba31"),l=e("48f4"),d=e("ad06"),u=Object(o["j"])("cell"),f=u[0],h=u[1];function g(t,i,e,n){var s=i.icon,r=i.size,u=i.title,f=i.label,g=i.value,p=i.isLink,b=i.arrowDirection,m=e.title||Object(o["c"])(u),v=e["default"]||Object(o["c"])(g),_=e.label||Object(o["c"])(f),k=_&&t("div",{class:[h("label"),i.labelClass]},[e.label?e.label():f]),x=m&&t("div",{class:[h("title"),i.titleClass],style:i.titleStyle},[e.title?e.title():t("span",[u]),k]),C=v&&t("div",{class:[h("value",{alone:!e.title&&!u}),i.valueClass]},[e["default"]?e["default"]():t("span",[g])]),w=e.icon?e.icon():s&&t(d["a"],{class:h("left-icon"),attrs:{name:s}}),T=e["right-icon"],j=T?T():p&&t(d["a"],{class:h("right-icon"),attrs:{name:b?"arrow-"+b:"arrow"}}),y=function(t){Object(c["a"])(n,"click",t),Object(l["a"])(n)},I={center:i.center,required:i.required,borderless:!i.border,clickable:p||i.clickable};return r&&(I[r]=r),t("div",a()([{class:h(I),on:{click:y}},Object(c["b"])(n)]),[w,x,C,j,e.extra&&e.extra()])}g.props=Object(n["a"])({},r["a"],l["c"],{clickable:Boolean,arrowDirection:String}),i["a"]=f(g)},8367:function(t,i,e){"use strict";e.r(i);var n=function(){var t=this,i=t.$createElement,n=t._self._c||i;return n("div",{staticClass:"set_meal_content"},[n("header",{staticClass:"header-nav-content"},[n("van-nav-bar",{attrs:{title:t.$t("lang.discount_package"),"left-arrow":""},on:{"click-left":t.onClickLeft}})],1),n("section",{staticClass:"comment-content"},t._l(t.fittingInfo.comboTab,function(i,s){return n("section",{key:s,staticClass:"goods_module_wrap m-top10"},[n("van-collapse",{attrs:{accordion:""},on:{change:t.toggleTab},model:{value:t.currTab,callback:function(i){t.currTab=i},expression:"currTab"}},[t.tabList[s]>0?n("van-collapse-item",{attrs:{name:i.group_id}},[n("div",{staticClass:"title_box",attrs:{slot:"title"},slot:"title"},[n("div",{staticClass:"title_text"},[n("span",[t._v(t._s(i.text))])])]),n("ul",{staticClass:"goods_list"},[n("li",{staticClass:"goods_item van-hairline--top"},[n("van-checkbox",{attrs:{disabled:t.checkDisabled},model:{value:t.checked,callback:function(i){t.checked=i},expression:"checked"}}),t.fittingInfo.goods.goods_thumb?n("img",{attrs:{src:t.fittingInfo.goods.goods_thumb}}):n("img",{attrs:{src:e("d9e6")}}),n("div",{staticClass:"name_price"},[n("p",[t._v(t._s(t.fittingInfo.goods.goods_name))]),n("currency-price",{attrs:{price:t.fittingInfo.goods.shop_price}})],1)],1),t._l(t.fittingInfo.fittings,function(s,a){return[i.group_id==s.group_id?n("li",{staticClass:"goods_item van-hairline--top",attrs:{index:a}},[n("van-checkbox-group",{model:{value:t.fittingsCheckModel,callback:function(i){t.fittingsCheckModel=i},expression:"fittingsCheckModel"}},[n("van-checkbox",{ref:"checkboxes",refInFor:!0,attrs:{name:s.goods_id}})],1),s.goods_thumb?n("img",{attrs:{src:s.goods_thumb},on:{click:function(i){t.checkboxHandle(s.goods_id,a)}}}):n("img",{attrs:{src:e("d9e6")}}),n("div",{staticClass:"name_price",on:{click:function(i){t.checkboxHandle(s.goods_id,a)}}},[n("p",[t._v(t._s(s.goods_name))]),n("currency-price",{attrs:{price:s.goods_price}})],1)],1):t._e()]})],2)]):t._e()],1)],1)})),n("footer",{staticClass:"submit_bar van-hairline--top"},[n("div",{staticClass:"left_price"},[n("div",{staticClass:"setmeal_price"},[t._v(t._s(t.$t("lang.package_price"))+"："),n("span",{domProps:{innerHTML:t._s(t.fittings_minMax)}})]),n("div",{staticClass:"save_price"},[t._v(t._s(t.$t("lang.save_money"))+"："),n("span",{domProps:{innerHTML:t._s(t.save_minMaxPrice)}})])]),n("van-button",{staticClass:"buynow",attrs:{round:""},on:{click:t.fittingsAddCart}},[t._v(t._s(t.$t("lang.add_cart")))])],1)])},s=[],a=(e("6762"),e("2fdb"),e("7514"),e("ac6a"),e("9395")),o=(e("5246"),e("6b41")),r=(e("3c32"),e("417e")),c=(e("a909"),e("3acc")),l=(e("66b9"),e("b650")),d=(e("e7e5"),e("d399")),u=(e("5d17"),e("f9bd")),f=(e("342a"),e("1437")),h=e("2b0e"),g=e("2f62");h["default"].use(o["a"]).use(r["a"]).use(c["a"]).use(l["a"]).use(d["a"]).use(u["a"]).use(f["a"]);var p={data:function(){return{checked:!0,checkDisabled:!0,fittingNames:"",fittingsCheckModel:[],fittings_minMax:0,save_minMaxPrice:0,currTab:"",id:this.$route.params.id?this.$route.params.id:0}},computed:Object(a["a"])({},Object(g["c"])({fittingInfo:function(t){return t.goods.fittingInfo},fittingPriceData:function(t){return t.goods.fittingPriceData},shipping_fee:function(t){return t.shopping.shipping_fee},goodsAttrInit:function(t){return t.goods.goodsAttrInit}}),{tabList:function(){var t=0,i=0,e=[];return this.fittingInfo.fittings.forEach(function(e){1==e.group_id?t++:i++}),e=[t,i],e}}),watch:{fittingInfo:"toggleTab",fittingsCheckModel:"fittingsCheckChange"},created:function(){this.getSetMealById()},methods:{onClickLeft:function(){this.$router.go(-1)},getSetMealById:function(){this.$store.dispatch("setFitting",{goods_id:this.id})},toggleTab:function(t){if(t.comboTab)this.fittingNames=this.fittingInfo.comboTab[0].group_id,this.currTab=this.fittingNames;else{if(this.currTab=this.currTab==t?"":t,t==this.fittingNames)return;this.fittingNames=t,this.fittingsCheckModel=[]}},checkboxHandle:function(t,i){this.$refs.checkboxes[i].toggle()},fittingsAddCart:function(){var t=this;d["a"].loading({duration:0,forbidClick:!0,loadingType:"spinner",message:this.$t("lang.loading")+"..."});var i="m_goods_"+this.fittingNames;this.id;this.$store.dispatch("setAddToCartGroup",{group_name:i,goods_id:this.id,warehouse_id:0,area_id:0,area_city:0,number:this.fittingInfo.goods.is_minimum>0?this.fittingInfo.goods.minimum:1}).then(function(i){var e=i.data;d["a"].clear(),0==e.error?t.$router.push({name:"cart"}):Object(d["a"])(e.msg)})},fittingsCheckChange:function(t,i){var e=this;d["a"].loading({duration:0,forbidClick:!0,loadingType:"spinner",message:this.$t("lang.loading")+"..."});var n="m_goods_"+this.fittingNames,s=n+"_"+this.id,a="";if(t.length>i.length){var o=t.find(function(t){return!i.includes(t)});this.fittingInfo.fittings.some(function(t){if(t.id==o)return a=t.goods_attr_id,!0}),this.$store.dispatch("setAddToCartCombo",{goods_id:o,number:1,spec:a,parent_attr:this.goodsAttrInit,warehouse_id:0,area_id:0,area_city:0,parent:this.id,group_id:s,add_group:""}).then(function(t){var i=t.data;d["a"].clear(),0==i.error?(e.save_minMaxPrice=i.save_minMaxPrice,e.fittings_minMax=i.fittings_minMax):Object(d["a"])(i.msg)})}else{var r="";if(t.length>0)r=i.find(function(i){return!t.includes(i)}),this.fittingInfo.fittings.some(function(t){if(t.id==r)return a=t.goods_attr_id,!0});else{var c=this.fittingInfo.fittings.filter(function(t){return t.group_id==e.fittingNames});r=c.length>0?c[0].goods_id:this.fittingInfo.fittings[0].goods_id}this.delcartCombo(r,s,a)}},delcartCombo:function(t,i,e){var n=this;this.$store.dispatch("setDelInCartCombo",{goods_id:t,parent:this.id,group_id:i,spec:e,goods_attr:this.goodsAttrInit,warehouse_id:0,area_id:0,area_city:0}).then(function(t){var i=t.data;d["a"].clear(),0==i.error?(n.save_minMaxPrice=i.save_minMaxPrice,n.fittings_minMax=i.fittings_minMax):Object(d["a"])(i.msg)})}}},b=p,m=(e("14d8"),e("2877")),v=Object(m["a"])(b,n,s,!1,null,"452a0e9c",null);v.options.__file="setMeal.vue";i["default"]=v.exports},8624:function(t,i,e){"use strict";(function(t){e.d(i,"a",function(){return c});var n=e("a142"),s=Date.now();function a(t){var i=Date.now(),e=Math.max(0,16-(i-s)),n=setTimeout(t,e);return s=i+e,n}var o=n["g"]?t:window,r=o.requestAnimationFrame||a;o.cancelAnimationFrame||o.clearTimeout;function c(t){return r.call(o,t)}}).call(this,e("c8ba"))},"8a0b":function(t,i,e){},a909:function(t,i,e){"use strict";e("68ef")},bff0:function(t,i,e){},d9e6:function(t,i,e){t.exports=e.p+"img/no_image.jpg"},dfaf:function(t,i,e){"use strict";e.d(i,"a",function(){return n});var n={icon:String,size:String,center:Boolean,isLink:Boolean,required:Boolean,titleStyle:null,titleClass:null,valueClass:null,labelClass:null,title:[String,Number],value:[String,Number],label:[String,Number],border:{type:Boolean,default:!0}}},f331:function(t,i,e){"use strict";e.d(i,"a",function(){return n});var n={data:function(){return{parent:null}},methods:{findParent:function(t){var i=this.$parent;while(i){if(i.$options.name===t){this.parent=i;break}i=i.$parent}}}}},f9bd:function(t,i,e){"use strict";var n=e("a142"),s=Object(n["j"])("collapse"),a=s[0],o=s[1];i["a"]=a({props:{accordion:Boolean,value:[String,Number,Array],border:{type:Boolean,default:!0}},data:function(){return{items:[]}},methods:{switch:function(t,i){this.accordion||(t=i?this.value.concat(t):this.value.filter(function(i){return i!==t})),this.$emit("change",t),this.$emit("input",t)}},render:function(t){return t("div",{class:[o(),{"van-hairline--top-bottom":this.border}]},[this.slots()])}})}}]);