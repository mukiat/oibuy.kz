(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-12d7"],{"10cb":function(t,e,i){},"1b10":function(t,e,i){"use strict";i.d(e,"a",function(){return n});var n={title:String,loading:Boolean,showToolbar:Boolean,cancelButtonText:String,confirmButtonText:String,visibleItemCount:{type:Number,default:5},itemHeight:{type:Number,default:44}}},"5f5f":function(t,e,i){"use strict";i("68ef"),i("a526")},6680:function(t,e,i){"use strict";var n=i("ff5e"),s=i.n(n);s.a},"8a58":function(t,e,i){"use strict";i("68ef"),i("4d75")},a526:function(t,e,i){},cd11:function(t,e,i){"use strict";i.r(e);var n,s=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"con bg-color-write"},[i("div",{staticClass:"flow-consignee"},[i("div",{staticClass:"text-all dis-box"},[i("label",[t._v(t._s(t.$t("lang.consignee"))),i("em",[t._v("*")])]),i("div",{staticClass:"input-text"},[i("ec-input",{attrs:{type:"text",size:"consignee",placeholder:t.$t("lang.enter_consignee")},model:{value:t.consignee,callback:function(e){t.consignee=e},expression:"consignee"}}),i("i",{staticClass:"iconfont icon-guanbi1 close-common"})],1)]),i("div",{staticClass:"text-all dis-box"},[i("label",[t._v(t._s(t.$t("lang.phone_number"))),i("em",[t._v("*")])]),i("div",{staticClass:"input-text"},[i("ec-input",{attrs:{type:"tel",size:"mobile",placeholder:t.$t("lang.enter_contact_number")},model:{value:t.mobile,callback:function(e){t.mobile=e},expression:"mobile"}}),i("i",{staticClass:"iconfont icon-guanbi1 close-common"})],1)]),i("div",{staticClass:"text-all dis-box"},[i("label",[t._v(t._s(t.$t("lang.region_alt"))),i("em",[t._v("*")])]),i("div",{staticClass:"input-text"},[i("div",{staticClass:"address-box",on:{click:t.handelRegionShow}},[i("span",{staticClass:"text-all-span"},[t._v(t._s(t.regionSplic))]),t._m(0)])])]),i("div",{staticClass:"text-all"},[i("label",[t._v(t._s(t.$t("lang.detail_info"))),i("em",[t._v("*")])]),i("div",{staticClass:"input-text"},[i("ec-input",{attrs:{type:"text",size:"address",placeholder:t.$t("lang.enter_address")},model:{value:t.address,callback:function(e){t.address=e},expression:"address"}}),i("i",{staticClass:"iconfont icon-guanbi1 close-common"})],1)]),i("div",{staticClass:"ect-button-more"},[i("a",{staticClass:"btn btn-submit",attrs:{href:"javascript:;"},on:{click:t.submitBtn}},[t._v(t._s(t.$t("lang.save")))])])]),i("Region",{attrs:{display:t.regionShow,regionOptionDate:t.regionOptionDate},on:{updateDisplay:t.getRegionShow,updateRegionDate:t.getRegionOptionDate}})],1)},o=[function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("span",{staticClass:"user-more"},[i("i",{staticClass:"iconfont icon-more"})])}],a=i("88d8"),r=(i("e7e5"),i("d399")),c=(i("5f5f"),i("f253")),l=(i("7f7f"),i("8a58"),i("e41f")),u=(i("10cb"),i("450d"),i("f3ad")),d=i.n(u),h=(i("2f62"),i("f27a")),g={mixins:[h["a"]],data:function(){return{consignee:"",mobile:"",email:"",address:""}},components:(n={EcInput:d.a},Object(a["a"])(n,l["a"].name,l["a"]),Object(a["a"])(n,c["a"].name,c["a"]),Object(a["a"])(n,r["a"].name,r["a"]),n),created:function(){this.$route.params.id;this.getRegionData&&(this.regionOptionDate=this.getRegionData)},methods:{handelRegionShow:function(){this.regionShow=!this.regionShow,this.$store.dispatch("setRegion",{region:1,level:1})},submitBtn:function(){var t=this,e=this;if(""==this.consignee)return Object(r["a"])(this.$t("lang.consignee_not_null")),!1;if(!this.checkMobile())return Object(r["a"])(this.$t("lang.phone_number_format")),!1;if(""==this.regionOptionDate.regionSplic)return Object(r["a"])(this.$t("lang.fill_in_region")),!1;if(""==this.address)return Object(r["a"])(this.$t("lang.address_not_null")),!1;var i={goods_id:this.$route.params.id,consignee:this.consignee,mobile:this.mobile,country:1,province:this.regionOptionDate.province.id,city:this.regionOptionDate.city.id,district:this.regionOptionDate.district.id,street:this.regionOptionDate.street.id,address:this.address};this.$http.get("".concat(window.ROOT_URL,"api/gift_gard/check_take"),{params:i}).then(function(i){i.data;Object(r["a"])(t.$t("lang.pick_success")),e.$http.get("".concat(window.ROOT_URL,"api/gift_gard/exit_gift")).then(function(t){var i=t.data;0==i.data.error&&e.$router.push({name:"giftCardOrder"})})})},checkMobile:function(){var t=/^((13|14|15|16|17|18|19)[0-9]{1}\d{8})$/;return!!t.test(this.mobile)},shippingFee:function(t){this.$store.dispatch("setShippingFee",{goods_id:0,position:t})}},watch:{regionSplic:function(){var t={province_id:this.regionOptionDate.province.id,city_id:this.regionOptionDate.city.id,district_id:this.regionOptionDate.district.id,street_id:this.regionOptionDate.street.id};this.shippingFee(t)}}},p=g,f=i("2877"),m=Object(f["a"])(p,s,o,!1,null,null,null);m.options.__file="Address.vue";e["default"]=m.exports},e41f:function(t,e,i){"use strict";var n=i("a142"),s=i("6605"),o=Object(n["j"])("popup"),a=o[0],r=o[1];e["a"]=a({mixins:[s["a"]],props:{position:String,transition:String,overlay:{type:Boolean,default:!0},closeOnClickOverlay:{type:Boolean,default:!0}},render:function(t){var e,i=this;if(this.shouldRender){var n=this.position,s=function(t){return function(){return i.$emit(t)}},o=this.transition||(n?"van-popup-slide-"+n:"van-fade");return t("transition",{attrs:{name:o},on:{afterEnter:s("opened"),afterLeave:s("closed")}},[t("div",{directives:[{name:"show",value:this.value}],class:r((e={},e[n]=n,e))},[this.slots()])])}}})},f253:function(t,e,i){"use strict";var n=i("c31d"),s=i("a142"),o=i("db78"),a=i("1128");function r(t){return Array.isArray(t)?t.map(function(t){return r(t)}):"object"===typeof t?Object(a["a"])({},t):t}var c=i("1b10"),l=i("543e"),u=200,d=Object(s["j"])("picker-column"),h=d[0],g=d[1],p=h({props:{valueKey:String,className:String,itemHeight:Number,defaultIndex:Number,initialOptions:Array,visibleItemCount:Number},data:function(){return{startY:0,offset:0,duration:0,startOffset:0,options:r(this.initialOptions),currentIndex:this.defaultIndex}},created:function(){this.$parent.children&&this.$parent.children.push(this),this.setIndex(this.currentIndex)},destroyed:function(){var t=this.$parent.children;t&&t.splice(t.indexOf(this),1)},watch:{defaultIndex:function(){this.setIndex(this.defaultIndex)}},computed:{count:function(){return this.options.length}},methods:{onTouchStart:function(t){this.startY=t.touches[0].clientY,this.startOffset=this.offset,this.duration=0},onTouchMove:function(t){Object(o["c"])(t);var e=t.touches[0].clientY-this.startY;this.offset=Object(s["i"])(this.startOffset+e,-this.count*this.itemHeight,this.itemHeight)},onTouchEnd:function(){if(this.offset!==this.startOffset){this.duration=u;var t=Object(s["i"])(Math.round(-this.offset/this.itemHeight),0,this.count-1);this.setIndex(t,!0)}},adjustIndex:function(t){t=Object(s["i"])(t,0,this.count);for(var e=t;e<this.count;e++)if(!this.isDisabled(this.options[e]))return e;for(var i=t-1;i>=0;i--)if(!this.isDisabled(this.options[i]))return i},isDisabled:function(t){return Object(s["f"])(t)&&t.disabled},getOptionText:function(t){return Object(s["f"])(t)&&this.valueKey in t?t[this.valueKey]:t},setIndex:function(t,e){t=this.adjustIndex(t)||0,this.offset=-t*this.itemHeight,t!==this.currentIndex&&(this.currentIndex=t,e&&this.$emit("change",t))},setValue:function(t){for(var e=this.options,i=0;i<e.length;i++)if(this.getOptionText(e[i])===t)return this.setIndex(i)},getValue:function(){return this.options[this.currentIndex]}},render:function(t){var e=this,i=this.itemHeight,n=this.visibleItemCount,s={height:i*n+"px"},o=i*(n-1)/2,a={transition:this.duration+"ms",transform:"translate3d(0, "+(this.offset+o)+"px, 0)",lineHeight:i+"px"},r={height:i+"px"};return t("div",{style:s,class:[g(),this.className],on:{touchstart:this.onTouchStart,touchmove:this.onTouchMove,touchend:this.onTouchEnd,touchcancel:this.onTouchEnd}},[t("ul",{style:a},[this.options.map(function(i,n){return t("li",{style:r,class:["van-ellipsis",g("item",{disabled:e.isDisabled(i),selected:n===e.currentIndex})],domProps:{innerHTML:e.getOptionText(i)},on:{click:function(){e.setIndex(n,!0)}}})})])])}}),f=Object(s["j"])("picker"),m=f[0],v=f[1],O=f[2];e["a"]=m({props:Object(n["a"])({},c["a"],{columns:Array,defaultIndex:{type:Number,default:0},valueKey:{type:String,default:"text"}}),data:function(){return{children:[]}},computed:{simple:function(){return this.columns.length&&!this.columns[0].values}},watch:{columns:function(){this.setColumns()}},methods:{setColumns:function(){var t=this,e=this.simple?[{values:this.columns}]:this.columns;e.forEach(function(e,i){t.setColumnValues(i,r(e.values))})},emit:function(t){this.simple?this.$emit(t,this.getColumnValue(0),this.getColumnIndex(0)):this.$emit(t,this.getValues(),this.getIndexes())},onChange:function(t){this.simple?this.$emit("change",this,this.getColumnValue(0),this.getColumnIndex(0)):this.$emit("change",this,this.getValues(),t)},getColumn:function(t){return this.children[t]},getColumnValue:function(t){var e=this.getColumn(t);return e&&e.getValue()},setColumnValue:function(t,e){var i=this.getColumn(t);i&&i.setValue(e)},getColumnIndex:function(t){return(this.getColumn(t)||{}).currentIndex},setColumnIndex:function(t,e){var i=this.getColumn(t);i&&i.setIndex(e)},getColumnValues:function(t){return(this.children[t]||{}).options},setColumnValues:function(t,e){var i=this.children[t];i&&JSON.stringify(i.options)!==JSON.stringify(e)&&(i.options=e,i.setIndex(0))},getValues:function(){return this.children.map(function(t){return t.getValue()})},setValues:function(t){var e=this;t.forEach(function(t,i){e.setColumnValue(i,t)})},getIndexes:function(){return this.children.map(function(t){return t.currentIndex})},setIndexes:function(t){var e=this;t.forEach(function(t,i){e.setColumnIndex(i,t)})},onConfirm:function(){this.emit("confirm")},onCancel:function(){this.emit("cancel")}},render:function(t){var e=this,i=this.itemHeight,n=this.simple?[this.columns]:this.columns,s={height:i+"px"},a={height:i*this.visibleItemCount+"px"},r=this.showToolbar&&t("div",{class:["van-hairline--top-bottom",v("toolbar")]},[this.slots()||[t("div",{class:v("cancel"),on:{click:this.onCancel}},[this.cancelButtonText||O("cancel")]),this.slots("title")||this.title&&t("div",{class:["van-ellipsis",v("title")]},[this.title]),t("div",{class:v("confirm"),on:{click:this.onConfirm}},[this.confirmButtonText||O("confirm")])]]);return t("div",{class:v()},[r,this.loading?t("div",{class:v("loading")},[t(l["a"])]):t(),t("div",{class:v("columns"),style:a,on:{touchmove:o["c"]}},[n.map(function(i,n){return t(p,{attrs:{valueKey:e.valueKey,className:i.className,itemHeight:e.itemHeight,defaultIndex:i.defaultIndex||e.defaultIndex,visibleItemCount:e.visibleItemCount,initialOptions:e.simple?i:i.values},on:{change:function(){e.onChange(n)}}})}),t("div",{class:["van-hairline--top-bottom",v("frame")],style:s})])])}})},f27a:function(t,e,i){"use strict";i("e7e5");var n=i("d399"),s=(i("4917"),i("3b2b"),i("a481"),i("09d6")),o=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticStyle:{height:"100%"}},["currency"==t.regionType?[i("van-popup",{attrs:{position:"bottom","close-on-click-overlay":!1},on:{"click-overlay":t.overlay},model:{value:t.display,callback:function(e){t.display=e},expression:"display"}},[i("div",{staticClass:"mod-address-main"},[i("div",{staticClass:"mod-address-head"},[i("div",{staticClass:"mod-address-head-tit box-flex"},[t._v(t._s(t.$t("lang.region_alt")))]),i("i",{staticClass:"iconfont icon-close",on:{click:t.onRegionClose}})]),i("div",{staticClass:"mod-address-body"},[i("ul",{staticClass:"ulAddrTab"},[i("li",{class:{cur:t.regionLevel-1==1},on:{click:function(e){t.tabClickRegion(1,1)}}},[i("span",[t._v(t._s(t.regionOption.province.name?t.regionOption.province.name:t.$t("lang.select")))])]),t.regionOption.province.name?i("li",{class:{cur:t.regionLevel-1==2},on:{click:function(e){t.tabClickRegion(t.regionOption.province.id,2)}}},[i("span",[t._v(t._s(t.regionOption.city.name?t.regionOption.city.name:t.$t("lang.select")))])]):t._e(),t.regionOption.city.name?i("li",{class:{cur:t.regionLevel-1==3},on:{click:function(e){t.tabClickRegion(t.regionOption.city.id,3)}}},[i("span",[t._v(t._s(t.regionOption.district.name?t.regionOption.district.name:t.$t("lang.select")))])]):t._e(),t.regionOption.district.name&&5==t.isLevel?i("li",{class:{cur:t.regionLevel-1==4},on:{click:function(e){t.tabClickRegion(t.regionOption.district.id,4)}}},[i("span",[t._v(t._s(t.regionOption.street.name?t.regionOption.street.name:t.$t("lang.select")))])]):t._e()]),2==t.regionLevel?i("ul",{staticClass:"ulAddrList"},t._l(t.regionDate.provinceData,function(e,n){return i("li",{key:n,class:{active:t.regionOption.province.id==e.id},on:{click:function(i){t.childRegion(e.id,e.name,e.level)}}},[t._v(t._s(e.name))])})):t._e(),3==t.regionLevel?i("ul",{staticClass:"ulAddrList"},t._l(t.regionDate.cityDate,function(e,n){return i("li",{key:n,class:{active:t.regionOption.city.id==e.id},on:{click:function(i){t.childRegion(e.id,e.name,e.level)}}},[t._v(t._s(e.name))])})):t._e(),4==t.regionLevel?i("ul",{staticClass:"ulAddrList"},t._l(t.regionDate.districtDate,function(e,n){return i("li",{key:n,class:{active:t.regionOption.district.id==e.id},on:{click:function(i){t.childRegion(e.id,e.name,e.level)}}},[t._v(t._s(e.name))])})):t._e(),5==t.regionLevel?i("ul",{staticClass:"ulAddrList"},t._l(t.regionDate.streetDate,function(e,n){return i("li",{key:n,class:{active:t.regionOption.street.id==e.id},on:{click:function(i){t.childRegion(e.id,e.name,e.level)}}},[t._v(t._s(e.name))])})):t._e()])])])]:t._e(),"goods"==t.regionType?[i("div",{staticClass:"mod-address-main mod-address-main-goods"},[i("div",{staticClass:"mod-address-body"},[i("ul",{staticClass:"ulAddrTab"},[i("li",{class:{cur:t.regionLevel-1==1},on:{click:function(e){t.tabClickRegion(1,1)}}},[i("span",[t._v(t._s(t.regionOption.province.name?t.regionOption.province.name:t.$t("lang.select")))])]),t.regionOption.province.name?i("li",{class:{cur:t.regionLevel-1==2},on:{click:function(e){t.tabClickRegion(t.regionOption.province.id,2)}}},[i("span",[t._v(t._s(t.regionOption.city.name?t.regionOption.city.name:t.$t("lang.select")))])]):t._e(),t.regionOption.city.name?i("li",{class:{cur:t.regionLevel-1==3},on:{click:function(e){t.tabClickRegion(t.regionOption.city.id,3)}}},[i("span",[t._v(t._s(t.regionOption.district.name?t.regionOption.district.name:t.$t("lang.select")))])]):t._e(),t.regionOption.district.name&&5==t.isLevel?i("li",{class:{cur:t.regionLevel-1==4},on:{click:function(e){t.tabClickRegion(t.regionOption.district.id,4)}}},[i("span",[t._v(t._s(t.regionOption.street.name?t.regionOption.street.name:t.$t("lang.select")))])]):t._e()]),2==t.regionLevel?i("ul",{staticClass:"ulAddrList"},t._l(t.regionDate.provinceData,function(e,n){return i("li",{key:n,class:{active:t.regionOption.province.id==e.id},on:{click:function(i){t.childRegion(e.id,e.name,e.level)}}},[t._v(t._s(e.name))])})):t._e(),3==t.regionLevel?i("ul",{staticClass:"ulAddrList"},t._l(t.regionDate.cityDate,function(e,n){return i("li",{key:n,class:{active:t.regionOption.city.id==e.id},on:{click:function(i){t.childRegion(e.id,e.name,e.level)}}},[t._v(t._s(e.name))])})):t._e(),4==t.regionLevel?i("ul",{staticClass:"ulAddrList"},t._l(t.regionDate.districtDate,function(e,n){return i("li",{key:n,class:{active:t.regionOption.district.id==e.id},on:{click:function(i){t.childRegion(e.id,e.name,e.level)}}},[t._v(t._s(e.name))])})):t._e(),5==t.regionLevel?i("ul",{staticClass:"ulAddrList"},t._l(t.regionDate.streetDate,function(e,n){return i("li",{key:n,class:{active:t.regionOption.street.id==e.id},on:{click:function(i){t.childRegion(e.id,e.name,e.level)}}},[t._v(t._s(e.name))])})):t._e()])])]:t._e()],2)},a=[],r=(i("96cf"),i("cb0c")),c=(i("ac6a"),i("88d8")),l=(i("7f7f"),i("8a58"),i("e41f")),u=(i("c5f6"),i("2f62"),{props:{display:{type:Boolean,default:!1},regionOptionDate:{type:Object,default:""},isPrice:{type:Number,default:0},isLevel:{type:Number,default:5},isStorage:{type:Boolean,default:!0},regionType:{type:String,default:"currency"}},data:function(){return{regionOption:this.regionOptionDate,arr:["province","city","district","street"],lat:"",lng:""}},components:Object(c["a"])({},l["a"].name,l["a"]),created:function(){var t={region:1,level:1};this.regionOption.district.id!=this.regionId&&(5==this.isLevel&&this.regionOption.district.id&&(t.region=this.regionOption.district.id,t.level=this.isLevel-1),this.$store.dispatch("setRegion",t))},computed:{regionId:function(){return this.$store.state.region.id},regionLevel:function(){return this.isLevel>this.$store.state.region.level?this.$store.state.region.level:this.isLevel},regionDate:function(){return this.$store.state.region.data},status:{get:function(){return this.$store.state.region.status},set:function(t){this.$store.state.region.status=t}},userRegion:function(){return this.$store.state.userRegion}},methods:{onRegionClose:function(){this.$emit("updateDisplay",!1)},childRegion:function(t,e,i){var n=this;switch(this.isLevel==i?this.status=!0:this.status=!1,i){case 2:this.regionOption.province.id=t,this.regionOption.province.name=e;break;case 3:this.regionOption.city.id=t,this.regionOption.city.name=e;break;case 4:this.regionOption.district.id=t,this.regionOption.district.name=e;break;case 5:this.regionOption.street.id=t,this.regionOption.street.name=e;break;default:break}this.arr.forEach(function(t,e){e+1>i&&(n.regionOption[t].id="",n.regionOption[t].name="")}),this.$store.dispatch("setRegion",{region:t,level:i})},tabClickRegion:function(t,e){var i=this;this.arr.forEach(function(t,n){n+1>e&&(i.regionOption[t].id="",i.regionOption[t].name="")}),this.$store.dispatch("setRegion",{region:t,level:e})},overlay:function(){this.$emit("updateDisplay",!1)},locationMap:function(){var t=Object(r["a"])(regeneratorRuntime.mark(function t(e){var i=this;return regeneratorRuntime.wrap(function(t){while(1)switch(t.prev=t.next){case 0:this.$http.get("".concat(window.ROOT_URL,"/api/misc/address2location"),{params:{address:e.replace(/\s*/g,"")}}).then(function(t){var e=t.data;if("success"==e.status){var n=e.data,s={lat:n.lat,lng:n.lng};i.regionOption.postion=s,i.isStorage&&localStorage.setItem("regionOption",JSON.stringify(i.regionOption)),i.$emit("updateRegionDate",i.regionOption),i.$emit("updateDisplay",!1),i.$emit("update:isPrice",1)}else Toast(e.message)});case 1:case"end":return t.stop()}},t,this)}));return function(e){return t.apply(this,arguments)}}()},watch:{status:function(){1==this.status&&(this.regionOption.regionSplic=this.regionOption.province.name+" "+this.regionOption.city.name+" "+this.regionOption.district.name+" "+this.regionOption.street.name,this.locationMap(this.regionOption.regionSplic))}}}),d=u,h=(i("6680"),i("2877")),g=Object(h["a"])(d,o,a,!1,null,"6b53e9da",null);g.options.__file="Region.vue";var p=g.exports;e["a"]={mixins:[s["a"]],components:{Region:p},data:function(){return{regionShow:!1,regionLoading:!1,regionOptionDate:{province:{id:"",name:""},city:{id:"",name:""},district:{id:"",name:""},street:{id:"",name:""},postion:{lat:"",lng:""},regionSplic:""},docmHeight:0,showHeight:0,isResize:!1,oauthHidden:!0,isGuide:!1,configData:JSON.parse(sessionStorage.getItem("configData")),swipe_height:document.documentElement.clientWidth?document.documentElement.clientWidth:375}},computed:{decimalLength:function(){var t=2;if(this.configData)switch(this.configData.price_format){case"0":t=2;break;case"1":t=2;break;case"2":t=1;break;case"3":t=0;break;case"4":t=1;break;case"5":t=0;break}return t},currencyFormat:function(){return this.configData.currency_format?this.configData.currency_format.replace("%s",""):"¥"},mobile_kefu:function(){return!!this.configData&&this.configData.mobile_kefu},getRegionData:function(){var t=JSON.parse(localStorage.getItem("regionOption")),e=JSON.parse(localStorage.getItem("userRegion"));return t||e},isWeiXin:function(){return s["a"].isWeixinBrowser()},userRegion:function(){return this.$store.state.userRegion},regionSplic:{get:function(){return this.regionOptionDate.regionSplic?this.regionOptionDate.regionSplic:this.$t("lang.select")},set:function(t){this.regionOptionDate=t}}},methods:{updateRadioSel:function(t,e){this.$store.dispatch("updateRadioSel",{modulesIndex:this.modulesIndex,sName:t,newValue:e})},updateText:function(t){isNaN(t.listIndex)||(t.modulesIndex=this.modulesIndex),this.$store.dispatch("updateText",t)},removeList:function(t){this.$store.dispatch("removeList",{modulesIndex:this.modulesIndex,listIndex:t})},addList:function(t){if("imgList"==t){localStorage.getItem("aPicture")&&localStorage.removeItem("aPicture");var e={bShowDialog:!0,currentPage:1,pageSize:12,oneOrMore:"more",bAlbum:!0,modulesIndex:this.modulesIndex,maxLength:this.maxLength,residueLength:this.maxLength-this.onlineData.list.length};this.$store.dispatch("setDialogPicture",e)}else{var i={modulesIndex:this.modulesIndex,url:"",urlCatetory:"",urlName:"",desc:""};this.$store.dispatch("addList",i)}},updateTitleText:function(t,e){var i={modulesIndex:this.modulesIndex,dataNext:"allValue",attrName:t,newValue:e};this.updateText(i)},onChat:function(t,e,i){var o=this;this.$store.dispatch("setChat",{goods_id:t,shop_id:e||0,type:i}).then(function(t){if("success"==t.status){var e=t.data.url;if(e){var i=RegExp(/wpa.qq.com/),a=i.test(e),r=navigator.userAgent,c=!!r.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);if(a){var l=e.indexOf("&uin="),u=e.indexOf("&site="),d=e.substring(l+5,u);c?s["a"].isWeixinBrowser()?window.location.href=e:window.location.href="mqq://im/chat?chat_type=wpa&uin="+d+"&version=1&src_type=web":window.location.href=e}else window.location.href=e}else Object(n["a"])(o.$t("lang.kefu_set_notic"))}else Object(n["a"])(t.errors.message)})},onresize:function(){var t=this;window.onresize=function(){return function(){t.docmHeight=document.documentElement.clientHeight,t.showHeight=document.body.clientHeight}()}},clickGuide:function(){this.isGuide=!1},handelRegionShow:function(){this.regionShow=!this.regionShow},getRegionShow:function(t){this.regionShow=t},getRegionOptionDate:function(t){this.regionOptionDate=t}}}},ff5e:function(t,e,i){}}]);