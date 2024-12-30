<template>
	<div
		class="video-page video_list_container"
		v-waterfall-lower="loadMores"
		waterfall-disabled="disabled"
		waterfall-offset="300"
	>
		<!-- 列表 -->
		<section>
			<div :class="[mode == 'grid' ? 'video_list_grid' : 'video_list']">
				<template v-if="list.length > 0">
					<div
						class="video_item bgc_fff"
						v-for="(item, index) in list"
						:key="index"
						@click="showPopup(index)"
					>
						<img
							class="video_poster"
							:src="mode === 'grid' ? item.goods_thumb : item.goods_img"
							v-if="item.goods_thumb"
						>
						<img class="video_poster" src="@/assets/img/no_image.jpg" v-else>
						<div class="video_duration size_13 color_fff flex_box jc_center ai_center">
              <i class="iconfont icon-play1"></i>
							<!-- <i class="iconfont icon-find-broadcast"></i>{{ durationArr[index] ? durationArr[index] : '00:00' }} -->
						</div>
						<div class="video_info flex_box fd_column jc_sb">
							<p class="text_2 size_15 color_333 weight_700">{{item.goods_name}}</p>
							<div class="video_user_info flex_box jc_sb ai_center">
								<div class="video_info_left flex_box ai_center">
									<img class="video_upic" :src="item.logo_thumb">
									<span class="video_uname size_12 color_666">{{item.shop_name}}</span>
								</div>
								<div class="video_info_right">
									<i class="iconfont icon-find-liulan-alt color_ccc"></i>
									<span class="size_12 color_ccc">{{item.look_num ? item.look_num : '0'}}</span>
								</div>
							</div>
						</div>
					</div>
				</template>
				<template v-else>
					<NotCont></NotCont>
				</template>
			</div>
			<div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
			<template v-if="loading">
				<van-loading type="spinner" color="black"/>
			</template>
		</section>
		<van-popup class="video-popup" v-model="popupShow" position="right" v-if="popupInfo.goods_video">
			<div class="video">
				<video :src="popupInfo.goods_video" id="movie" loop></video>
			</div>
			<div class="close" @click="hidePopup"></div>
			<div class="bottom">
				<router-link class="goodsinfo" :to="{ name: 'goods', params: { id: popupInfo.goods_id }}">
					<div class="img">
						<img :src="popupInfo.goods_thumb" v-if="popupInfo.goods_thumb">
						<img src="@/assets/img/no_image.jpg" v-else>
					</div>
					<div class="text">
						<h3>{{popupInfo.goods_name}}</h3>
						<p v-html="popupInfo.shop_price_formated"></p>
					</div>
				</router-link>
			</div>
			<div class="fab">
				<div class="fab-item" @click="collection(popupInfo.goods_id)">
					<div class="fab-image">
						<van-icon :name="popupInfo.is_collect == 1 ? 'like' : 'like-o'" size="2rem"/>
					</div>
					<span>{{popupInfo.user_collect}}</span>
				</div>
				<router-link :to="{ name: 'goodsComment', params: { id: popupInfo.goods_id }}" class="fab-item">
					<div class="fab-image">
						<i class="iconfont icon-message1"></i>
					</div>
					<span>{{popupInfo.comment_num}}</span>
				</router-link>
				<div class="fab-item">
					<div class="fab-image" @click="onGoodsShare">
						<i class="iconfont icon-share"></i>
					</div>
				</div>
			</div>
		</van-popup>
		<!--分享-->
		<van-popup v-model="shareImgShow" class="shareImg" overlay-class="shareImg-overlay">
			<img :src="shareImg" v-if="shareImg" class="img">
			<span v-else>{{$t('lang.error_generating_image')}}</span>
		</van-popup>
		<!--初始化loading-->
		<DscLoading :dscLoading="dscLoading"></DscLoading>
    <video style="display:none" controls="controls"  name="media" id="divVideo" >
      <source type="video/mp4">
    </video>
	</div>
</template>
<script>
import qs from "qs";
import DscLoading from "@/components/DscLoading";
import arrRemove from "@/mixins/arr-remove";
import NotCont from "@/components/NotCont";
import isApp from "@/mixins/is-app";

import { Popup, Waterfall, Loading, Dialog, Toast, Icon } from "vant";
let interval;
let length = 0;
let videoData = [];
let index = 0;
export default {
  name: "videoList",
  mixins: [isApp],
  data() {
    return {
      placeholder: this.$t("lang.search_goods"),
      disabled: false,
      keyword: "",
      mode: "large",
      list: [],
      durationArr: [],
      page: 1,
      size: 10,
      footerCont: false,
      loading: false,
      app: false,
      dscLoading: true,
      popupShow: false,
      popupInfo: {},
      isWx: false,
      shareImg: "",
      shareImgShow: false,
    };
  },
  directives: {
    WaterfallLower: Waterfall("lower"),
  },
  components: {
    DscLoading,
    NotCont,
    [Popup.name]: Popup,
    [Loading.name]: Loading,
    [Dialog.name]: Dialog,
    [Toast.name]: Toast,
    [Icon.name]: Icon,
  },
  computed: {
    isLogin() {
      return localStorage.getItem("token") == null ? false : true;
    },
  },
  created() {
    this.loadList();

    if (isApp.isWeixinBrowser()) {
      this.isWx = true;
    } else {
      this.isWx = false;
    }
  },
  methods: {
    initVideoElement() {
      document.getElementById("divVideo").src=videoData[index].goods_video;
		  interval = setInterval(this.countVideo,100)
    },
    countVideo() {
      if(document.getElementById("divVideo").readyState == 4){
        length=parseInt(document.getElementById("divVideo").duration);
        let m = Math.floor((length / 60 % 60)) < 10 ? '0' + Math.floor((length / 60 % 60)) : Math.floor((length / 60 % 60));
　　    let s = Math.floor((length % 60)) < 10 ? '0' + Math.floor((length % 60)) : Math.floor((length % 60));
        length = `${m}:${s}`;
        this.durationArr = [...this.durationArr, length];
        clearInterval(interval);
        index++;
        if(index < videoData.length){
          this.initVideoElement();
        }else{
          // console.log("end");
        }
      }else{
        // console.log("加载中...");
      }
    },
    resetData() {
      interval;
      length = 0;
      videoData = [];
      index = 0;
    },
    loadList(page) {
      if (page) {
        this.page = page;
        this.size = Number(page) * 10;
      }
      this.$http
        .post(
          `${window.ROOT_URL}api/goods/goodsvideo`,
          qs.stringify({
            size: this.size,
            page: this.page,
            sort: "goods_id",
            order: "desc",
          })
        )
        .then(({ data }) => {
          if (!page) {
            this.list = this.list.concat(data.data);
          } else {
            this.list = data.data;
          }
          if (this.list.length >= 4) {
            this.mode = "grid";
          } else {
            this.mode = "large";
          }

          if (data.data.length > 0) {
            this.$nextTick(function () {
              this.resetData();
              videoData = data.data;
              this.initVideoElement();
            })
          }

        });
    },
    loadMores() {
      setTimeout(() => {
        this.disabled = true;
        if (this.page * this.size == this.list.length) {
          this.page++;
          this.loadList();
        }
      }, 200);
    },
    showPopup(index) {
      let item = this.list[index];
      this.popupInfo = item;
      this.popupShow = true;
      this.videoPlay();
      this.collectionNumber();
      if (item.goods_id) this.lookVideo(item.goods_id, index);
    },
    async lookVideo(id, index) {
      const { data: {status, data} } = await this.$http.get(`${window.ROOT_URL}api/goods/videolooknum`, {params: {goods_id: id}});
      if (status != 'success') return;
      this.list[index].look_num = data;
    },
    hidePopup() {
      this.popupShow = false;
      this.videoPause();
    },
    collectionNumber() {
      this.$http
        .get(`${window.ROOT_URL}api/collect/collectnum`, {
          params: {
            goods_id: this.popupInfo.goods_id,
          },
        })
        .then(({ data }) => {
          this.popupInfo.user_collect = data.data;
        });
    },
    collection(goods_id) {
      let that = this;
      if (this.isLogin) {
        this.$http
          .post(
            `${window.ROOT_URL}api/collect/collectgoods`,
            qs.stringify({
              goods_id: this.popupInfo.goods_id,
              status: this.popupInfo.is_collect,
            })
          )
          .then(({ data }) => {
            if (data.data.error == 0) {
              Toast(data.data.msg);
              that.popupInfo.is_collect = !that.popupInfo.is_collect;

              that.collectionNumber();
            }
          });
      } else {
        let msg = this.$t("lang.fill_in_user_collect_goods");
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
                name: "videoList",
                query: {
                  type: 2,
                },
                url: url,
              },
            },
          });
        })
        .catch(() => {});
    },
    videoPlay() {
      setTimeout(function() {
        var video = document.getElementById("movie");
        video.play();
      }, 300);
    },
    videoPause() {
      var video = document.getElementById("movie");
      video.pause();
    },
    commentHandle(id) {
      this.$router.push({
        name: "goodsComment",
        id: id,
      });
    },
    //商品分享生成分享图片
    onGoodsShare() {
      if (this.isLogin) {
        Toast.loading({
          duration: 0,
          mask: true,
          forbidClick: true,
          message: this.$t("lang.loading"),
        });
        let price = this.popupInfo.shop_price_formated;
        this.$store
          .dispatch("setGoodsShare", {
            goods_id: this.popupInfo.goods_id,
            price: price,
            share_type: this.popupInfo.is_distribution || 0,
          })
          .then((res) => {
            if (res.status == "success") {
              this.shareImg = res.data;
              this.shareImgShow = true;
              Toast.clear();
            }
          });
      } else {
        let msg = this.$t("lang.login_user_not");
        this.notLogin(msg);
      }
    },
  },
  watch: {
    list() {
      this.dscLoading = false;
      if (this.page * this.size == this.list.length) {
        this.disabled = false;
        this.loading = true;
      } else {
        this.loading = false;
        this.footerCont = this.page > 1 ? true : false;
      }

      this.list = arrRemove.trimSpace(this.list);
    },
  },
};
</script>
<style lang="scss" scoped>
.fab {
  position: fixed;
  right: 20px;
  bottom: 50px;
  z-index: 10;
  color: #fff;
}
.fab-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin-bottom: 10px;
}
.fab-item .fab-image {
  width: 30px;
  height: 30px;
  line-height: 32px;
  border-radius: 100%;
  background: rgba(255, 255, 255, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
}
.fab-item .fab-image .iconfont,
.fab-item .fab-image .van-icon {
  color: #000;
}
.fab-item .fab-image .van-icon-like {
  color: #ff495e;
}
.fab-item .fab-image .icon-message1 {
  font-size: 20px;
}
.fab-item .fab-image .icon-share {
  font-size: 14px;
}
.fab-item span {
  margin-top: 5px;
  font-size: 16px;
  color: #fff;
}

.secrch-warp {
  .input-text {
    .search-check {
      width: 4.6rem;
      height: 4.6rem;
      top: 0;
      right: 0;
      .iconfont {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
      }
    }
  }
}

/** 视频改版 star */
.video_list_container {
  .video_list_grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
	  padding: 1rem 2% 0;
  }
  .video_list {
    padding: 1rem 2% 0;
  }
  .video_item {
    position: relative;
    width: 49%;
    border-radius: 1rem;
    margin-bottom: 0.7rem;
    overflow: hidden;
  }
  .video_list .video_item {
	width: 100%;
    .video_poster {
      height: 96vw;
    }
  }
  .video_duration {
    position: absolute;
    top: 1.3rem;
    left: 0.8rem;
    width: 2rem;
    height: 2rem;
    line-height: 1;
    text-align: center;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
  }
  .video_duration i {
	  // vertical-align: middle;
    font-size: .6rem;
    color: #fff;
  }
  .video_poster {
    width: 100%;
    height: 47.34vw;
  }
  .video_info {
    box-sizing: border-box;
    height: 10.3rem;
    padding: 1.3rem 1rem 1.6rem;
  }
  .video_upic {
    width: 2.2rem;
    height: 2.2rem;
    border-radius: 50%;
    margin-right: 0.5rem;
  }
  .video_uname {
    height: 2.2rem;
    line-height: 2.2rem;
  }
  .video_info_right .size_12 {
    vertical-align: middle;
  }
  .video_info_right i {
    font-size: .8rem;
    margin-right: 0.3rem;
  }
}
/** 视频改版 end */

/*弹窗*/
.video-popup {
  background: #000;
  .video {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    bottom: 0;
    video {
      position: absolute;
      width: 100%;
      max-height: 100%;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
    }
  }
  .close {
    position: absolute;
    right: 2rem;
    top: 2rem;
    width: 4rem;
    height: 4rem;
    background: rgba(0, 0, 0, 0.3) url(../../assets/img/video/close.png)
      no-repeat center;
    background-size: 1.3rem;
    border-radius: 100%;
  }
  .bottom {
    position: absolute;
    bottom: 2rem;
    left: 1.8rem;
    right: 2rem;
    .goodsinfo {
      background: #fff;
      height: 7rem;
      padding: 0.2rem;
      display: inline-block;
      width: 80%;
      border-radius: 0.5rem;
      .img {
        float: left;
        width: 6.8rem;
        height: 6.8rem;
        border-radius: 0.5rem;
        overflow: hidden;
        img {
          width: 6.8rem;
          height: 6.8rem;
          display: block;
          vertical-align: top;
        }
      }
      .text {
        margin-left: 8rem;
        padding-top: 0.7rem;
        text-align: left;
        h3,
        p {
          line-height: 1.8rem;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
          font-size: 1.4rem;
        }
        p {
          color: #fd2f2f;
        }
      }
    }
    .like {
      display: inline-block;
      vertical-align: top;
      color: #fff;
      height: 5.2rem;
      line-height: 5.2rem;
      width: 22%;
      margin-left: 1.7rem;
      i {
        display: inline-block;
        width: 1.4rem;
        height: 1.2rem;
        vertical-align: -0.2rem;
        background: url(../../assets/img/video/heart.png) no-repeat left bottom;
        background-size: 1.4rem;
        margin-right: 0.5rem;
      }
      &.active i {
        background-position: left top;
      }
    }
  }
}
</style>
