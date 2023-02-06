(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-07a0"],{"075e":function(t,e,a){},"10cb":function(t,e,a){},"86a2":function(t,e,a){"use strict";var i=a("075e"),s=a.n(i);s.a},"8f80":function(t,e,a){"use strict";var i=a("c31d"),s=a("a142"),n=Object(s["j"])("uploader"),r=n[0],o=n[1];e["a"]=r({inheritAttrs:!1,props:{disabled:Boolean,beforeRead:Function,afterRead:Function,accept:{type:String,default:"image/*"},resultType:{type:String,default:"dataUrl"},maxSize:{type:Number,default:Number.MAX_VALUE}},computed:{detail:function(){return{name:this.$attrs.name||""}}},methods:{onChange:function(t){var e=this,a=t.target.files;!this.disabled&&a.length&&(a=1===a.length?a[0]:[].slice.call(a,0),!a||this.beforeRead&&!this.beforeRead(a,this.detail)?this.resetInput():Array.isArray(a)?Promise.all(a.map(this.readFile)).then(function(t){var i=!1,s=a.map(function(s,n){return s.size>e.maxSize&&(i=!0),{file:a[n],content:t[n]}});e.onAfterRead(s,i)}):this.readFile(a).then(function(t){e.onAfterRead({file:a,content:t},a.size>e.maxSize)}))},readFile:function(t){var e=this;return new Promise(function(a){var i=new FileReader;i.onload=function(t){a(t.target.result)},"dataUrl"===e.resultType?i.readAsDataURL(t):"text"===e.resultType&&i.readAsText(t)})},onAfterRead:function(t,e){e?this.$emit("oversize",t):this.afterRead&&this.afterRead(t,this.detail),this.resetInput()},resetInput:function(){this.$refs.input&&(this.$refs.input.value="")}},render:function(t){var e=this.accept,a=this.disabled;return t("div",{class:o()},[this.slots(),t("input",{attrs:Object(i["a"])({},this.$attrs,{type:"file",accept:e,disabled:a}),ref:"input",class:o("input"),on:{change:this.onChange}})])}})},"9e4b":function(t,e,a){"use strict";a.r(e);var i,s=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"user-detail comment_detail_content"},[i("div",{staticClass:"goods_info"},[t.commentInfo.goods_thumb?i("img",{staticClass:"img",attrs:{src:t.commentInfo.goods_thumb}}):i("img",{staticClass:"img",attrs:{src:a("d9e6")}}),i("div",{staticClass:"rate_box"},[i("p",[t._v("商品评价")]),t.$route.params.type?i("div",{staticClass:"rate"},t._l(5,function(e,a){return i("i",{key:a,class:["iconfont","icon-wujiaoxing","size_16",t.commentInfo.comment_rank>=e?"color_red":""]})})):i("div",{staticClass:"rate"},t._l(5,function(e,a){return i("i",{key:a,class:["iconfont","icon-wujiaoxing","size_16",t.rank>=e?"color_red":""],on:{click:function(a){t.evaluation(e)}}})}))])]),i("div",{staticClass:"comment_module"},[i("div",{staticClass:"header"},[t._v(t._s(t.tips.title))]),0==t.$route.params.type&&t.commentInfo.goods_product_tag&&t.commentInfo.goods_product_tag.length?i("div",{staticClass:"tag_list u-border-bottom"},t._l(t.commentInfo.goods_product_tag,function(e,a){return i("div",{key:a,class:["tag_item",t.tagList.includes(e)?"active_tag":""],on:{click:function(a){t.selectTag(e)}}},[t._v("\n\t\t\t\t"+t._s(e)+"\n\t\t\t")])})):t._e(),i("div",{staticClass:"input_wrap"},[i("textarea",{directives:[{name:"model",rawName:"v-model",value:t.textarea,expression:"textarea"}],staticClass:"text_area",attrs:{maxlength:"500",placeholder:t.tips.placeholder},domProps:{value:t.textarea},on:{input:function(e){e.target.composing||(t.textarea=e.target.value)}}}),i("div",{staticClass:"comment_length"},[t._v("已写"),i("span",[t._v(t._s(t.textareaLength))]),t._v("个字")])]),i("div",{staticClass:"add_img"},[t._l(t.materialList,function(e,a){return i("div",{key:a,staticClass:"img_box"},[i("img",{attrs:{src:e}}),i("i",{staticClass:"iconfont icon-delete",on:{click:function(e){t.deleteImg(a)}}})])}),i("van-uploader",{class:["add_btn",t.materialList.length>0?"has_img":""],attrs:{"after-read":t.onRead(),accept:"image/jpg, image/jpeg, image/png, image/gif",multiple:""}},[i("div",{staticClass:"upload_content"},[i("i",{staticClass:"iconfont icon-jiahao"}),i("p",{staticClass:"btn_text"},[t._v("添加图片")])])])],2)]),0==t.commentInfo.degree_count&&t.commentInfo.ru_id>0&&0==t.$route.params.type?i("div",{staticClass:"satisfaction_module"},[t._m(0),i("div",{staticClass:"rate_list"},t._l(t.satisfactionList,function(e,a){return i("div",{key:a,staticClass:"rate_item"},[i("div",{staticClass:"rate_label"},[t._v(t._s(e.title))]),i("div",{staticClass:"rate_value"},t._l(5,function(s,n){return i("i",{key:n,class:["iconfont","icon-wujiaoxing","size_16",e.rank>=s?"color_red":""],on:{click:function(e){t.satisfaction(s,a)}}})}))])}))]):t._e(),i("div",{staticClass:"floor_bar"},[i("div",{staticClass:"submit_button",on:{click:t.btnSubmit}},[t._v(t._s(t.$t("lang.comment_submit")))])])])},n=[function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"header"},[a("span",[t._v("满意度评价")]),a("span",{staticClass:"header_right"},[t._v("满意请给5颗星哦")])])}],r=(a("a481"),a("ac6a"),a("6762"),a("2fdb"),a("9395")),o=a("88d8"),c=(a("e17f"),a("2241")),l=(a("e7e5"),a("d399")),u=(a("7f7f"),a("e930"),a("8f80")),d=(a("10cb"),a("450d"),a("f3ad")),m=a.n(d),f=a("2f62"),h=(a("4328"),{data:function(){return{textarea:"",type:0,rank:0,server:0,delivery:0,tagList:[],satisfactionList:[{title:"商品描述相符度",rank:0},{title:"卖家服务态度",rank:0},{title:"物流发货速度",rank:0},{title:"配送人员态度",rank:0}]}},components:(i={EcInput:m.a},Object(o["a"])(i,u["a"].name,u["a"]),Object(o["a"])(i,l["a"].name,l["a"]),Object(o["a"])(i,c["a"].name,c["a"]),i),created:function(){this.$store.dispatch("setAddcomment",{rec_id:this.$route.params.id,is_add_evaluate:this.$route.params.type})},destroyed:function(){this.$store.commit("clearMaterialImg")},computed:Object(r["a"])({},Object(f["c"])({materialList:function(t){return t.user.materialList},commentInfo:function(t){return t.user.commentInfo}}),{textareaLength:function(){return this.textarea.length||0},returnPictures:function(){return 9},tips:function(){return{title:this.$route.params.type?"追加一下你的使用体验吧":"分享你的使用体验吧",placeholder:this.$route.params.type?"对评价进行补充，更客观，更全面~（500字）":"商品质量如何？快写下你的评价，分享给大家吧！（500字）"}}}),methods:{evaluation:function(t){this.rank=t},selectTag:function(t){this.tagList.includes(t)?this.tagList=this.tagList.filter(function(e){return e!=t}):this.tagList.push(t)},satisfaction:function(t,e){var a=this;this.satisfactionList.forEach(function(i,s){e==s?a.$set(a.satisfactionList[e],"rank",t):a.$set(a.satisfactionList[s],"rank",i.rank>t?i.rank:i.rank>0?i.rank:1)})},onRead:function(){var t=this;return function(e){var a=0;a=void 0==e.length?t.materialList.length+1:e.length+t.materialList.length,a>t.returnPictures?Object(l["a"])(t.$t("lang.return_nine_pic")):t.$store.dispatch("setMaterial",{file:e})}},btnSubmit:function(){var t=this;return this.$route.params.type&&(this.rank=this.commentInfo.comment_rank),0==this.rank?(Object(l["a"])(this.$t("lang.fill_in_comment_rank")),!1):""==this.textarea?(Object(l["a"])(this.$t("lang.comment_not_null")),!1):void this.$store.dispatch("setAddgoodscomment",{type:this.type,id:this.commentInfo.goods_id,content:this.textarea,rank:this.rank,server:this.server,tag:this.tagList,is_add_evaluate:this.$route.params.type,desc_rank:this.satisfactionList[0].rank,service_rank:this.satisfactionList[1].rank,delivery_rank:this.satisfactionList[2].rank,sender_rank:this.satisfactionList[3].rank,comment_id:this.commentInfo.comment_id,delivery:this.delivery,order_id:this.commentInfo.order_id,rec_id:this.commentInfo.rec_id,pic:this.materialList}).then(function(e){if("success"==e.status)l["a"].success({duration:1e3,forbidClick:!0,loadingType:"spinner",message:e.data.msg||t.$t("lang.comment_success")}),setTimeout(function(){t.$router.replace({path:"/user/comment",query:{have:"1"}})},2e3);else{var a=e.errors.message||t.$t("lang.comment_fail");Object(l["a"])(a)}})},deleteImg:function(t){var e=this;c["a"].confirm({message:this.$t("lang.confirm_delete_pic"),className:"text-center"}).then(function(){e.$store.dispatch("setDeleteImg",{index:t})})}}}),_=h,p=(a("86a2"),a("2877")),g=Object(p["a"])(_,s,n,!1,null,"5e4113c0",null);g.options.__file="Detail.vue";e["default"]=g.exports},bcd3:function(t,e,a){},d9e6:function(t,e,a){t.exports=a.p+"img/no_image.jpg"},e930:function(t,e,a){"use strict";a("68ef"),a("bcd3")}}]);