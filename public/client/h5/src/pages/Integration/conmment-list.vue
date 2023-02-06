<template>
	<div
		class="comment_box"
		v-waterfall-lower="loadMore"
		waterfall-disabled="disabled"
		waterfall-offset="300"
	>
		<div class="comment_header flex_box jc_sb ai_center bgc_fff">
			<div class="header_left flex_box ai_center">
				<span class="iconfont icon-find-fanhui size_17 color_333" @click="$router.go(-1)"></span>
				<div class="source_head_portrait">
					<img :src="detailData.user_picture">
				</div>
				<div class="source_info flex_box fd_column">
					<span class="size_14 color_333">{{ detailData.user_name }}</span>
					<span class="size_10 color_888">{{ detailData.add_time_formated }}</span>
				</div>
			</div>
			<div class="header_right" @click="shareHandle(false)">
				<span class="iconfont icon-find-share size_17 color_333"></span>
			</div>
		</div>
		<div class="slideshow_box">
			<span class="indicator_dots_box size_12 color_fe bgc_888">{{ indicator }}</span>
			<van-swipe :autoplay="3000" :show-indicators="false" @change="dscSwipeChange">
				<van-swipe-item v-for="(swipeItem, swipeIndex) in detailData.goods_gallery" :key="swipeIndex">
					<img :src="swipeItem" class="swipe_item_img" @click="previewImgs(swipeIndex,swipeItem)">
				</van-swipe-item>
			</van-swipe>
		</div>
		<div class="comment_explain flex_box fd_column ai_center bgc_fff">
			<span
				class="comment_explain_title size_20 color_333 text_1"
				v-if="false"
			>{{ detailData.content }}</span>
			<span
				:class="['comment_explain_content', 'size_14', 'color_888', isZhanKai ? '' : 'text_9']"
			>{{ detailData.content }}</span>
			<div class="zhankai flex_box ai_center" @click="zhankaiHandle" v-if="isShowZhanKai">
				<span class="size_14 color_333">{{ isZhanKai ? $t('lang.shouqi') : $t('lang.zhankai') }}</span>
				<span
					:class="['iconfont', isZhanKai ? 'icon-expand-down' : 'icon-zhankai', 'size_12', 'color_333']"
				></span>
			</div>
		</div>
		<div class="comment_goods_list br_1 bgc_fff">
			<router-link :to="{name:'goods',params:{id:detailData.goods_id}}">
				<div :class="['goods_item', 'flex_box']">
					<div class="goods_left">
						<img :src="detailData.goods_thumb">
					</div>
					<div class="goods_right flex_1 flex_box fd_column jc_sb">
						<span class="size_16 color_333 text_2 weight_700">{{ detailData.goods_name }}</span>
						<div class="price_box">
							<span class="size_16 color_f0151b weight_700">{{ detailData.shop_price }}</span>
							<span class="size_10 color_888 td_lt">{{ detailData.market_price }}</span>
						</div>
					</div>
				</div>
			</router-link>
		</div>
		<div class="comments_box">
			<span class="comments_title size_18 color_333 weight_700">{{$t('lang.comments')}}</span>
			<div :class="['comments_list', commentList.length > 0 ? 'br_1' : 'br_2', 'bgc_fff']">
				<span class="comment_count lh_1 size_14 color_888">{{$t('lang.gong')}}{{ detailData.reply_count }}{{$t('lang.tiaopinglun')}}</span>
				<template v-if="commentList.length > 0">
					<div class="comments_list_item flex_box" v-for="(item,index) in commentList" :key="index">
						<div class="item_left_box">
							<img :src="item.user_picture">
						</div>
						<div class="item_right_box flex_1 flex_box fd_column bb_e6">
							<span class="lh_1 size_14 color_888 text_1 flex-common ju-start">{{ item.user_name }}<span
									v-if="item.user_type > 0"
									:class="['size_10', 'color_fff', item.user_type == 1 ? 'user_type_1' : 'user_type_2']"
								>{{ item.user_type == 1 ? $t('lang.admins') : $t('lang.biz') }}</span>
							</span>
							<div class="right_box_bottom">
								<span class="size_16 color_333">{{ item.content }}</span>
								<span class="size_12 color_888">{{ item.add_time_formated }}</span>
							</div>
						</div>
					</div>
				</template>
				<div class="no_comment flex_box ai_center" v-else>
					<div class="no_commont_pic">
						<img :src="detailData.my_picture" v-if="detailData.my_picture">
            <img class="video_upic" src="@/assets/img/no_image.jpg" v-else>
					</div>
					<span class="no_commont_placeholder flex_1 size_14 color_888 bgc_f5" @click="showKeyBar">{{$t('lang.say_something')}}</span>
				</div>
			</div>
		</div>
		<div class="dsc_loading_box size_14" v-show="commentList.length > 0">{{isOver ? $t('lang.no_more') : $t('lang.loading')}}</div>
		<div class="van-modal" :class="{'hide':mask === true}" @click="close" style="z-index:1000"></div>
		<div class="bargain-share van-modal" :class="{'hide':shareState === true}" style="z-index:1001">
			<div class="bargain-friends">
				<div class="header f-30 col-3">{{$t('lang.share_hint')}}</div>
				<div class="cont f-24 text-center">{{$t('lang.share_toast_hint')}}</div>
				<div class="footer f-24 col-3" @click="close">{{$t('lang.i_see')}}</div>
			</div>
		</div>
		<div :class="['footer_box', 'flex_box', 'bgc_fff', isFocus && !isIos ? 'footer_box_focus' : '']">
			<div class="footer_mian flex_box ai_fe">
				<div
					style="position: relative; overflow: hidden;"
					:class="[isShowspanArea ? 'textarea_focus_box' : 'textarea_box', 'flex_box', 'ai_center', 'bgc_f5']"
				>
					<div class="blur_box" v-if="!isShowspanArea">
						<span class="iconfont icon-find-pinglun size_14 color_888"></span>
						<span class="size_14 color_888">{{$t('lang.say_something2')}}</span>
					</div>
					<textarea
						class="comment_area size_14"
						v-model="commentCont"
						@focus="focusHandle"
						@blur="blurHandle"
					></textarea>
				</div>
				<div class="functional_zone flex_box" v-if="!isShowspanArea">
					<div class="dianzan_box" @click="onZan">
						<span :class="['iconfont', isDianZan ? 'icon-dianzan-after' : 'icon-find-zan', 'size_20']"></span>
						<span class="num_box size_12">{{ detailData.like_num }}</span>
					</div>
					<div class="liulan_box" v-if="false">
						<span class="iconfont icon-find-liulan size_20"></span>
						<span class="num_box size_12">{{ detailData.dis_browse_num }}</span>
					</div>
					<div class="pinlun_box">
						<span class="iconfont icon-find-talk size_20"></span>
						<span class="num_box size_12">{{ detailData.reply_count }}</span>
					</div>
				</div>
				<div class="comment_send_btn size_14 color_fff" @click="sendComment" v-else>{{$t('lang.send')}}</div>
			</div>
		</div>
		<DscLoading :dscLoading="dscLoading"></DscLoading>
	</div>
</template>

<script>
import Vue from "vue";
import DscLoading from "@/components/DscLoading";
import { Waterfall, Swipe, SwipeItem, Dialog, Toast , ImagePreview} from "vant";
import { setTimeout, clearTimeout } from 'timers';

Vue.use(Swipe)
  .use(SwipeItem)
  .use(Dialog)
  .use(Toast)
  .use(ImagePreview);
export default {
  props: {
    id: {
      required: true,
    },
  },
  directives: {
    WaterfallLower: Waterfall("lower"),
  },
  components: {
    DscLoading,
  },
  data() {
    return {
      detailData: {},
      commentList: [],
      mapUser: ["", "管理员", "商家"],
      swipeIndex: 1,
	  isZhanKai: true,
	  disabled: true,
      isFocus: false,
      isIos: true,
      commentCont: "",
      isLoading: false,
      isOver: false,
      size: 10,
      page: 1,
      isShowZhanKai: false,
      shareState: true,
      mask: true,
	  dscLoading: true,
	  isDianZan: false
    };
  },
  created() {
    var u = navigator.userAgent;
    this.isIos = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
    // const { platform } = uni.getSystemInfoSync();
    // console.log(this.id)
    this.getcommentDetailById();
    this.getcommentById();
    // console.log(Number(Math.random().toString().substr(3, 3) + Date.now()).toString(36))
  },
  mounted() {

  },
  computed: {
    indicator: function() {
      if (this.detailData.goods_gallery)
        return `${this.swipeIndex}/${this.detailData.goods_gallery.length}`;
      return "0/0";
    },
    isShowspanArea: function() {
      if (this.commentCont) {
        return true;
      } else if (this.isFocus) {
        return true;
      } else {
        return false;
      }
    },
    isLogin() {
      return localStorage.getItem("token") == null ? false : true;
    },
  },
  filters: {
    shopPrice: function(val) {
      if (!val) return "";
      return val.substr(1);
    },
  },
  methods: {
    async getcommentDetailById(isSend = false) {
      const {
        data: { data, status },
      } = await this.$http.get(
        `${window.ROOT_URL}/api/discover/find_detail?dis_id=${this.id}`
      );
      this.dscLoading = false;
      if (status == "success") {
        this.detailData = data || {};
        this.$nextTick(function() {
          console.log(document.querySelector(".comment_explain_content").offsetHeight)
          this.isShowZhanKai = document.querySelector(".comment_explain_content").offsetHeight > 235;
          this.isZhanKai = !this.isShowZhanKai;
        });
        if (isSend) {
          this.page = 1;
          this.getcommentById(true);
        }
      } else {
        let that = this;
        Toast(that.$t('lang.post_msg_fail'));
      }
    },
    async getcommentById(toTop = false) {
	  this.isLoading = true;
	  this.disabled = true;
      const {
        data: { data, status },
      } = await this.$http.get(
        `${window.ROOT_URL}/api/discover/find_reply_comment?dis_id=${
          this.id
        }`,
        {
          params: {
            page: this.page,
            size: this.size,
          },
        }
      );
      if (status == "success") {
        this.isOver = data.length < this.size;
        if (this.page == 1) this.commentList = [];
        this.commentList = [...this.commentList, ...data];
		if (toTop) document.querySelector('.comments_box').scrollIntoView();
      } else {
        let that = this;
        Toast(that.$t('lang.post_msg_fail'));
      }

	  this.isLoading = false;
	  this.disabled = false;
    },
    dscSwipeChange(i) {
      this.swipeIndex = i + 1;
    },
    focusHandle(e) {
    this.isFocus = true;
    if (this.isIos) {
      let timerId = setTimeout(() => {
		  document.querySelector('.dsc_loading_box').scrollIntoView();
        clearTimeout(timerId);
      },300);
    }

    },
    blurHandle(e) {
      this.isFocus = false;
	},
	showKeyBar() {
		document.querySelector('.comment_area').focus();
	},
  shareHandle(val) {
    this.shareState = val;
  },
  zhankaiHandle() {
    let scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;

    this.isZhanKai = !this.isZhanKai;

    this.$nextTick(function () {
      window.scrollTo({
        top: scrollTop,
        behavior: 'instant'
      });
    })
  },
    onZan() {
      this.$store
        .dispatch("setDiscoverLike", {
          dis_type: 4,
          dis_id: this.id,
        })
        .then(({ data, status }) => {
          Toast(data.msg);
		  if (status == "success") {
			  this.isDianZan = true;
			  this.detailData.like_num = data.like_num;
		  }

        });
    },
    sendComment() {
      if (!this.commentCont.trim()) return;
      if (this.isLogin) {
        this.$store
          .dispatch("setDiscoverComment", {
            parent_id: this.id,
            quote_id: 0,
            dis_text: this.commentCont,
            reply_type: 0,
            dis_type: 4,
            goods_id: this.detailData.goods_id,
          })
          .then(({ data }) => {
            Toast(data.msg);
            if (data.error == 0) {
              this.commentCont = "";
              // this.detailData.reply_count = this.detailData.reply_count + 1;
              this.getcommentDetailById(true);

            }
          });
      } else {
        let msg = this.$t("lang.login_user_not");
        this.notLogin(msg);
      }
    },
    notLogin(msg) {
      let url = window.location.href;
      Dialog.confirm({
        message: msg,
        className: "text-center",
      })
        .then(() => {
          this.$router.push({
            name: "login",
            query: {
              redirect: {
                name: "conmmentlist",
                params: {
                  id: this.id
                },
                url: url
              },
            },
          });
        })
        .catch(() => {});
    },
    loadMore() {
      if (this.isOver || this.isLoading) return;
      this.page = this.page + 1;
      this.getcommentById();
    },
    //关闭蒙板
    close() {
      this.mask = true;
      this.shareState = true;
    },
    // 图片预览 
    previewImgs(i = 0, imgs) {
      let arr = []
      if (imgs){
        if(typeof imgs == 'string') arr.push(imgs);
        function handler(e){ e.preventDefault(); }
        document.body.addEventListener('touchmove',handler,{passive:false});
        ImagePreview({
          images: arr.length > 0 ? arr : imgs,
          startPosition: i,
          onClose(){
            document.body.removeEventListener('touchmove',handler,{passive:false});
          }
        });
      }
    },
  },
  watch:{
    detailData(){
      //单独设置微信分享信息
      this.$wxShare.share({
          title:this.detailData.goods_name,
          desc:this.detailData.content,
          link:`${window.ROOT_URL}mobile#/conmmentlist/` + this.id,
          imgUrl:this.detailData.goods_thumb
      })
    }
  }
};
</script>

<style scoped>
.comment_box {
  padding: 4.9rem 0 6.3rem;
}

.comment_header {
  position: fixed;
  top: 0;
  width: 100%;
  padding: 0.8rem 1.9rem 0.8rem 1.3rem;
  z-index: 3;
}

.icon-find-fanhui {
  margin-right: 1.6rem;
}

.source_head_portrait {
  width: 3.3rem;
  height: 3.3rem;
  border-radius: 50%;
  margin-right: 0.8rem;
}
.source_head_portrait img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}

.source_info span {
  line-height: 1.2;
}

.slideshow_box {
  position: relative;
  height: 117.6vw;
}
.slideshow_box .swipe_item_img {
  width: 100vw;
  height: 117.6vw;
  object-fit: cover;
}

.indicator_dots_box {
  position: absolute;
  top: 1rem;
  right: 1.5rem;
  height: 1.8rem;
  line-height: 1.8rem;
  text-align: center;
  padding: 0 1rem;
  border-radius: 0.9rem;
  z-index: 2;
}

.comment_explain {
  position: relative;
  top: -1rem;
  left: 0;
  width: 100%;
  /* max-height: 37.25rem; */
  padding: 2rem 1.5rem;
  border-radius: 1rem;
  box-sizing: border-box;
  z-index: 1;
}
.comment_explain_title {
  width: 100%;
  line-height: 1;
}
.comment_explain_content {
  width: 100%;
  line-height: 25px;
  text-align: justify;
  white-space: pre-line;
}
.zhankai {
  margin-top: 2.6rem;
}
.zhankai span:nth-child(1) {
  margin-right: 0.5rem;
}
.comment_goods_list {
  margin-bottom: 1rem;
}
.goods_item {
  padding: 1.5rem;
}
.goods_left {
  width: 9.5rem;
  height: 9.5rem;
}
.goods_left img {
  width: 100%;
  height: 100%;
  border-radius: 0.1rem;
}
.goods_right {
  padding: 0.5rem 0 0.5rem 1rem;
}
.goods_right .size_16 {
  line-height: 2.1rem;
}
.price_box .size_16 {
  margin-right: 1.1rem;
}
.comments_title {
  display: inline-block;
  line-height: 1;
  padding: 1.5rem 0 1.5rem 1.2rem;
}
.comments_list {
  padding: 2.1rem 1.2rem 0;
}
.comment_count {
  margin-bottom: 2rem;
}
.comments_list_item {
  padding-bottom: 1.5rem;
}
.comments_list_item:last-child{
  padding-bottom: 0;
}
.comments_list_item:last-child .bb_e6{ border-bottom: 0 }
.item_left_box {
  width: 3rem;
  height: 3rem;
  margin: 0.5rem 1.4rem 0 0;
  border-radius: 50%;
}
.item_left_box img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}
.item_right_box {
  padding-bottom: 1.5rem;
}
.item_right_box > span {
  margin-bottom: 0.5rem;
}
.user_type_1 {
  display: inline-block;
  font-size: 1rem !important;
  height: 1.4rem;
  line-height: 1.4rem;
  background: linear-gradient(
    -90deg,
    rgba(75, 185, 243, 1),
    rgba(55, 158, 255, 1)
  );
  border-radius: 1px;
  margin-left: 0.5rem;
  padding: 0 0.2rem;
}
.user_type_2 {
  display: inline-block;
  font-size: 1rem !important;
  height: 1.4rem;
  line-height: 1.4rem;
  background: linear-gradient(
    -90deg,
    rgba(255, 106, 82, 1),
    rgba(255, 75, 75, 1)
  );
  border-radius: 1px;
  margin-left: 0.5rem;
  padding: 0 0.2rem;
}
.right_box_bottom span:last-child {
  margin-left: 0.5rem;
}
.no_comment {
  padding-bottom: 10rem;
}
.no_commont_pic {
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  margin-right: 0.8rem;
}
.no_commont_pic img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
}
.no_commont_placeholder {
  border-radius: 1.5rem;
  padding-left: 1.6rem;
  line-height: 3rem;
}
.footer_box {
  position: fixed;
  bottom: 0;
  width: 100%;
  min-height: 6.3rem;
  /* min-height: 7.9rem; */
  border-top: 0.1rem solid #e6e6e6;
  z-index: 3;
  padding-bottom: 0;
  padding-bottom: constant(safe-area-inset-bottom);
  padding-bottom: env(safe-area-inset-bottom);
}
.footer_box_focus {
  min-height: 7.9rem;
}
.footer_mian {
  width: 100%;
  max-height: 3.5rem;
  margin: 1.4rem 0 0 1.2rem;
}
.textarea_box {
  width: 60%;
  min-height: 3.5rem;
  border-radius: 1.75rem;
}
.textarea_focus_box {
  flex: 1;
  min-height: 3.5rem;
  border-radius: 1.75rem;
  transition: all 0.3s;
  /* border-radius: 0.3rem; */
}

.blur_box {
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  line-height: 1.4rem;
}

.functional_zone {
  flex: 1;
  height: 3.5rem;
  max-height: 3.5rem;
}

.dianzan_box,
.liulan_box,
.pinlun_box {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  flex-direction: row;
  margin-left: 2rem;
}
.dianzan_box .icon-dianzan-after {
	color: #F0151B;
}
.num_box {
  vertical-align: top;
}
.comment_send_btn {
  width: 6rem;
  height: 3.5rem;
  line-height: 3.5rem;
  text-align: center;
  margin: 0 1.2rem 0 0.7rem;
  background: linear-gradient(
    -88deg,
    rgba(255, 79, 45, 1),
    rgba(249, 31, 39, 1)
  );
  border-radius: 1.75rem;
}
.footer_mian .iconfont {
  margin-right: 0.5rem;
}
.textarea_box .icon-find-pinglun {
  margin: 0 1.2rem;
}
.comment_area {
  width: 100%;
  max-height: 3.5rem;
  padding: 0.9rem 1.75rem;
  z-index: 999;
  background-color: transparent;
  line-height: 1.2;
  box-sizing: border-box;
}
.dsc_loading_box {
  height: 5rem;
  line-height: 5rem;
  text-align: center;
}
.bargain-friends {
  top: 50%;
  transform: translateY(-50%);
}

.br_2 {
  border-top-left-radius: 1rem;
  border-top-right-radius: 1rem;
}
</style>
