<template>
  <div
    class="community_list_container"
    v-waterfall-lower="loadMore"
    waterfall-disabled="disabled"
    waterfall-offset="300"
    :class="{'community_container_list':routerName == 'tab'}"
  >
    <div :class="[mode == 'grid' ? 'video_list_grid' : 'video_list']">
      <template v-if="list.length > 0">
        <div
          class="video_item bgc_fff"
          v-for="(item, index) in list"
          :key="index"
          @click="goDetail(item.comment_id)"
        >
          <img class="video_poster" :src="item.img" v-if="item.img">
          <img class="video_poster" src="@/assets/img/no_image.jpg" v-else>
          <!-- <div class="video_duration size_13 color_fff">
							<i class="iconfont icon-find-broadcast"></i>00.16
          </div>-->
          <div class="video_info flex_box fd_column jc_sb">
            <p class="text_2 size_15 color_333 weight_700">{{item.goods_name}}</p>
            <div class="video_user_info flex_box jc_sb ai_center">
              <div class="video_info_left flex_box ai_center">
                <img class="video_upic" :src="item.user_picture" v-if="item.user_picture">
                <img class="video_upic" src="@/assets/img/no_image.jpg" v-else>
                <span class="video_uname size_12 color_666">{{item.user_name}}</span>
              </div>
              <div class="video_info_right">
                <i class="iconfont icon-find-liulan-alt color_ccc"></i>
                <span class="size_12 color_ccc">{{item.dis_browse_num ? item.dis_browse_num : '0'}}</span>
              </div>
            </div>
          </div>
        </div>
      </template>
      <template v-else-if="!loading">
        <div style="margin: 0 auto;">
          <no-data/>
        </div>
      </template>
    </div>
    <template v-if="loading">
      <van-loading type="spinner" color="black"/>
    </template>
    <div class="footer-cont dsc_loading_box">{{isOver && list.length > 4 ? $t('lang.no_more') : ''}}</div>
  </div>
</template>

<script>
import Vue from "vue";
import NotCont from "@/components/NotCont";
import DscLoading from "@/components/DscLoading";
import { Waterfall, Toast, Loading } from "vant";

Vue.use(Toast).use(Loading);
export default {
  name: "conmmentlist",
  directives: {
    WaterfallLower: Waterfall("lower"),
  },
  props: {
    routerName:{
      type:String,
      default:''
    }
  },
  components: {
    "no-data": NotCont,
    "dsc-loading": DscLoading,
  },
  data() {
    return {
      dscLoading: true,
      list: [],
      disabled: true,
      loading: true,
      finished: false,
      mode: "grid",
      page: 1,
      size: 10,
      isOver: false
    };
  },
  created() {
    this.getList();
  },
  methods: {
    async getList() {
      this.disabled = true;
      this.loading = true;
      const {
        data: { data, status },
      } = await this.$http.get(`${window.ROOT_URL}api/discover/find_list`, {
        params: {
          size: this.size,
          page: this.page,
        },
      });
      if (status == "success") {
        this.isOver = data.length < this.size;
        this.list = [...this.list, ...data];
      } else {
        Toast("获取数据失败!");
      }
      this.loading = false;
      this.disabled = false;
    },
    goDetail(userId) {
      this.$router.push({ path: `/conmmentlist/${userId}` });
    },
    loadMore() {
      if (this.isOver || this.loading) return;
      this.page = this.page + 1;
      this.getList();
    },
  }
};
</script>

<style lang="scss" scoped>
/** 从视频列表 COPY 的样式 star */
.community_list_container {
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
    width: 48.8%;
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
    height: 2rem;
    line-height: 2rem;
    text-align: center;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 1rem;
    padding: 0 1rem;
  }
  .video_duration i {
    font-size: 1rem;
    margin-right: 0.6rem;
  }
  .video_poster {
    width: 100%;
    height: 63.46vw;
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
    font-size: 0.8rem;
    margin-right: 0.3rem;
  }
}
/** 从视频列表 COPY 的样式  end */

.community_container_list .video_list_grid{
  padding: 0;
}

.video_poster {
  object-fit: cover;
}
</style>
