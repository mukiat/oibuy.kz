(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-3d75"],{"20ba":function(t,e,i){"use strict";i.r(e);var s,n=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("section",{staticClass:"con bg-color-write"},[i("div",{staticClass:"user-login-box"},[i("ec-form",{ref:"loginForm",staticClass:"user-login-form"},[i("div",{staticClass:"user-login-head"},[i("i",{staticClass:"iconfont icon-back",on:{click:t.onClickHome}}),i("h1",[t._v(t._s(t.$t("lang.sms_code_login")))])]),i("div",{staticClass:"user-login-ul"},[i("ec-form-item",{attrs:{prop:"imgverify"}},[i("div",{staticClass:"item-input dis-box"},[i("div",{staticClass:"label"},[i("i",{staticClass:"iconfont icon-pic"})]),i("div",{staticClass:"value box-flex"},[i("ec-input",{attrs:{type:"text",placeholder:t.$t("lang.captcha_img")},model:{value:t.imgverifyValue,callback:function(e){t.imgverifyValue=e},expression:"imgverifyValue"}})],1),i("div",{staticClass:"key"},[i("img",{staticClass:"j-verify-img",attrs:{src:t.captcha},on:{click:t.clickCaptcha}})])])]),i("ec-form-item",{attrs:{prop:"mobile"}},[i("div",{staticClass:"item-input dis-box"},[i("div",{staticClass:"label"},[i("i",{staticClass:"iconfont icon-mobiles"})]),i("div",{staticClass:"value box-flex"},[i("ec-input",{attrs:{type:"tel",placeholder:t.$t("lang.enter_mobile")},model:{value:t.mobile,callback:function(e){t.mobile=e},expression:"mobile"}})],1),i("div",{staticClass:"key"},[t.button_type?i("label",{on:{click:t.sendVerifyCode}},[t._v(t._s(t.$t("lang.get_code")))]):i("label",[t._v(t._s(t.button_text))])])])]),i("ec-form-item",{attrs:{prop:"sms"}},[i("div",{staticClass:"item-input dis-box"},[i("div",{staticClass:"label"},[i("i",{staticClass:"iconfont icon-key"})]),i("div",{staticClass:"value box-flex"},[i("ec-input",{attrs:{type:"tel",maxlength:"6",placeholder:t.$t("lang.get_sms_code")},model:{value:t.sms,callback:function(e){t.sms=e},expression:"sms"}})],1)])])],1),i("button",{staticClass:"btn btn-submit border-radius-top05",attrs:{type:"button"},on:{click:t.submitBtn}},[t._v(t._s(t.$t("lang.login_immediately")))])]),i("p",{staticClass:"tips"},[t._v(t._s(t.$t("lang.register_prompt_notic"))),i("a",{attrs:{href:"javascript:;"},on:{click:t.articleHref}},[t._v(t._s(t.$t("lang.register_prompt_1")))])]),i("div",{staticClass:"user-login-list"},[i("router-link",{staticClass:"list-new",attrs:{to:{name:"login"}}},[t._v(t._s(t.$t("lang.account_pwd_login"))),i("i",{staticClass:"iconfont icon-more"})])],1)],1),i("van-popup",{staticClass:"shareImg",attrs:{"overlay-class":"shareImg-overlay"},model:{value:t.articleShow,callback:function(e){t.articleShow=e},expression:"articleShow"}},[t.articleDetail?i("div",{staticClass:"content"},[i("div",{domProps:{innerHTML:t._s(t.articleDetail)}}),i("div",{staticClass:"btn btn-submit",on:{click:t.submitArticle}},[t._v(t._s(t.$t("lang.confirm")))])]):i("div",{staticClass:"content not-content"},[t._v(t._s(t.$t("lang.article_not_content")))])])],1)},a=[],o=i("88d8"),l=(i("8a58"),i("e41f")),r=(i("a7cc"),i("450d"),i("df33")),c=i.n(r),u=(i("7f7f"),i("e7e5"),i("d399")),d=(i("10cb"),i("f3ad")),p=i.n(d),f=(i("eca7"),i("3787")),h=i.n(f),_=(i("425f"),i("4105")),m=i.n(_),v=(i("2f62"),i("bc3a"),{name:"login",data:function(){return{mobile:"",imgverifyValue:"",sms:"",button_text:this.$t("lang.send_again_60"),send_again:this.$t("lang.send_again"),button_type:!0,register_article_id:6,articleShow:!1,articleDetail:""}},components:(s={EcForm:m.a,EcFormItem:h.a,EcInput:p.a},Object(o["a"])(s,u["a"].name,u["a"]),Object(o["a"])(s,c.a.name,c.a),Object(o["a"])(s,l["a"].name,l["a"]),s),created:function(){this.$store.dispatch("setImgVerify"),this.shopConfig()},computed:{token:{get:function(){return this.$store.state.user.token},set:function(t){this.$store.state.user.token=t}},captcha:function(){return this.$store.state.imgVerify.captcha},client:function(){return this.$store.state.imgVerify.client}},methods:{clickCaptcha:function(){this.$store.dispatch("setImgVerify")},sendVerifyCode:function(){var t=this,e={captcha:this.imgverifyValue,client:this.client,mobile:this.mobile};t.$store.dispatch("setSendVerify",e).then(function(e){if("success"==e){t.button_type=!1;var i=60,s=setInterval(function(){i--,i?t.button_text=t.send_again+"("+i+"s)":(t.button_type=!0,clearInterval(s))},1e3)}})},submitBtn:function(){var t=this,e=localStorage.getItem("parent_id")?localStorage.getItem("parent_id"):this.$route.query.parent_id?this.$route.query.parent_id:null,i={client:this.client,mobile:this.mobile,code:this.sms,parent_id:e,allow_login:1};return this.checkMobile()?""==this.imgverifyValue?(Object(u["a"])(this.$t("lang.captcha_img")),!1):""==this.sms?(Object(u["a"])(this.$t("lang.get_sms_code_notic")),!1):void this.$store.dispatch("userRegister",i).then(function(e){"success"==e.status?(u["a"].success({duration:1e3,forbidClick:!0,loadingType:"spinner",message:t.$t("lang.login_success")}),localStorage.setItem("token",e.data),t.token=e.data):Object(u["a"])(e.errors.message)}):(Object(u["a"])(this.$t("lang.phone_number_format")),!1)},onClickHome:function(){this.$router.push({name:"user"})},checkMobile:function(){var t=/^((13|14|15|16|17|18|19)[0-9]{1}\d{8})$/;return!!t.test(this.mobile)},shopConfig:function(){var t=this;this.$http.get("".concat(window.ROOT_URL,"api/shop/config")).then(function(e){var i=e.data.data;i.register_article_id&&(t.register_article_id=i.register_article_id)})},articleHref:function(){var t=this;this.$store.dispatch("setArticleDetail2",{id:this.register_article_id}).then(function(e){"success"==e.status?(t.articleDetail=e.data.content,t.articleShow=!0):Object(u["a"])(t.$t("lang.article_set_not"))})},submitArticle:function(){this.articleShow=!1}},watch:{token:function(){this.$router.push({name:"user"})}}}),g=v,b=i("2877"),C=Object(b["a"])(g,n,a,!1,null,null,null);C.options.__file="LoginMobile.vue";e["default"]=C.exports},"8a58":function(t,e,i){"use strict";i("68ef"),i("4d75")},a7cc:function(t,e,i){},df33:function(t,e,i){t.exports=function(t){var e={};function i(s){if(e[s])return e[s].exports;var n=e[s]={i:s,l:!1,exports:{}};return t[s].call(n.exports,n,n.exports,i),n.l=!0,n.exports}return i.m=t,i.c=e,i.d=function(t,e,s){i.o(t,e)||Object.defineProperty(t,e,{configurable:!1,enumerable:!0,get:s})},i.n=function(t){var e=t&&t.__esModule?function(){return t["default"]}:function(){return t};return i.d(e,"a",e),e},i.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},i.p="/dist/",i(i.s=61)}({0:function(t,e){t.exports=function(t,e,i,s,n,a){var o,l=t=t||{},r=typeof t.default;"object"!==r&&"function"!==r||(o=t,l=t.default);var c,u="function"===typeof l?l.options:l;if(e&&(u.render=e.render,u.staticRenderFns=e.staticRenderFns,u._compiled=!0),i&&(u.functional=!0),n&&(u._scopeId=n),a?(c=function(t){t=t||this.$vnode&&this.$vnode.ssrContext||this.parent&&this.parent.$vnode&&this.parent.$vnode.ssrContext,t||"undefined"===typeof __VUE_SSR_CONTEXT__||(t=__VUE_SSR_CONTEXT__),s&&s.call(this,t),t&&t._registeredComponents&&t._registeredComponents.add(a)},u._ssrRegister=c):s&&(c=s),c){var d=u.functional,p=d?u.render:u.beforeCreate;d?(u._injectStyles=c,u.render=function(t,e){return c.call(e),p(t,e)}):u.beforeCreate=p?[].concat(p,c):[c]}return{esModule:o,exports:l,options:u}}},1:function(t,e){t.exports=i("d010")},13:function(t,e){t.exports=i("5128")},61:function(t,e,i){"use strict";e.__esModule=!0;var s=i(62),n=a(s);function a(t){return t&&t.__esModule?t:{default:t}}n.default.install=function(t){t.component(n.default.name,n.default)},e.default=n.default},62:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var s=i(63),n=i.n(s),a=i(64),o=i(0),l=!1,r=null,c=null,u=null,d=o(n.a,a["a"],l,r,c,u);e["default"]=d.exports},63:function(t,e,i){"use strict";e.__esModule=!0;var s=i(13),n=c(s),a=i(8),o=c(a),l=i(1),r=c(l);function c(t){return t&&t.__esModule?t:{default:t}}e.default={name:"ElDialog",mixins:[n.default,r.default,o.default],props:{title:{type:String,default:""},modal:{type:Boolean,default:!0},modalAppendToBody:{type:Boolean,default:!0},appendToBody:{type:Boolean,default:!1},lockScroll:{type:Boolean,default:!0},closeOnClickModal:{type:Boolean,default:!0},closeOnPressEscape:{type:Boolean,default:!0},showClose:{type:Boolean,default:!0},width:String,fullscreen:Boolean,customClass:{type:String,default:""},top:{type:String,default:"15vh"},beforeClose:Function,center:{type:Boolean,default:!1}},data:function(){return{closed:!1}},watch:{visible:function(t){var e=this;t?(this.closed=!1,this.$emit("open"),this.$el.addEventListener("scroll",this.updatePopper),this.$nextTick(function(){e.$refs.dialog.scrollTop=0}),this.appendToBody&&document.body.appendChild(this.$el)):(this.$el.removeEventListener("scroll",this.updatePopper),this.closed||this.$emit("close"))}},computed:{style:function(){var t={};return this.fullscreen||(t.marginTop=this.top,this.width&&(t.width=this.width)),t}},methods:{getMigratingConfig:function(){return{props:{size:"size is removed."}}},handleWrapperClick:function(){this.closeOnClickModal&&this.handleClose()},handleClose:function(){"function"===typeof this.beforeClose?this.beforeClose(this.hide):this.hide()},hide:function(t){!1!==t&&(this.$emit("update:visible",!1),this.$emit("close"),this.closed=!0)},updatePopper:function(){this.broadcast("ElSelectDropdown","updatePopper"),this.broadcast("ElDropdownMenu","updatePopper")},afterLeave:function(){this.$emit("closed")}},mounted:function(){this.visible&&(this.rendered=!0,this.open(),this.appendToBody&&document.body.appendChild(this.$el))},destroyed:function(){this.appendToBody&&this.$el&&this.$el.parentNode&&this.$el.parentNode.removeChild(this.$el)}}},64:function(t,e,i){"use strict";var s=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("transition",{attrs:{name:"dialog-fade"},on:{"after-leave":t.afterLeave}},[i("div",{directives:[{name:"show",rawName:"v-show",value:t.visible,expression:"visible"}],staticClass:"el-dialog__wrapper",on:{click:function(e){if(e.target!==e.currentTarget)return null;t.handleWrapperClick(e)}}},[i("div",{ref:"dialog",staticClass:"el-dialog",class:[{"is-fullscreen":t.fullscreen,"el-dialog--center":t.center},t.customClass],style:t.style},[i("div",{staticClass:"el-dialog__header"},[t._t("title",[i("span",{staticClass:"el-dialog__title"},[t._v(t._s(t.title))])]),t.showClose?i("button",{staticClass:"el-dialog__headerbtn",attrs:{type:"button","aria-label":"Close"},on:{click:t.handleClose}},[i("i",{staticClass:"el-dialog__close el-icon el-icon-close"})]):t._e()],2),t.rendered?i("div",{staticClass:"el-dialog__body"},[t._t("default")],2):t._e(),t.$slots.footer?i("div",{staticClass:"el-dialog__footer"},[t._t("footer")],2):t._e()])])])},n=[],a={render:s,staticRenderFns:n};e["a"]=a},8:function(t,e){t.exports=i("2bb5")}})},e41f:function(t,e,i){"use strict";var s=i("a142"),n=i("6605"),a=Object(s["j"])("popup"),o=a[0],l=a[1];e["a"]=o({mixins:[n["a"]],props:{position:String,transition:String,overlay:{type:Boolean,default:!0},closeOnClickOverlay:{type:Boolean,default:!0}},render:function(t){var e,i=this;if(this.shouldRender){var s=this.position,n=function(t){return function(){return i.$emit(t)}},a=this.transition||(s?"van-popup-slide-"+s:"van-fade");return t("transition",{attrs:{name:a},on:{afterEnter:n("opened"),afterLeave:n("closed")}},[t("div",{directives:[{name:"show",value:this.value}],class:l((e={},e[s]=s,e))},[this.slots()])])}}})}}]);