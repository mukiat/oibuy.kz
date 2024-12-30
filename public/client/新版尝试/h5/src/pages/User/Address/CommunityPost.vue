<template>
  <div class="container-box" :style="{height: ch + 'px'}">
    <!-- 地图容器 -->
    <div id="container"></div>

    <!-- 社区列表 -->
    <div class="list-box">
      <div class="list_scroll_box">
        <van-pull-refresh v-model="refreshing" @refresh="onRefresh">
          <van-list v-model="loading" :finished="finished" :finished-text="$t('lang.no_more')" @load="getPostList">
            <div class="list_item" v-for="(item, index) in postList" :key="item.phone" @click="changePost(index)">

              <van-icon name="checked" color="#f92028" size="2rem" v-if="index == currentIndex"/>
              <span class="item_checked_icon" v-else></span>

              <div class="item-main">
                <div>
                  <span>{{item.pick_up_point}}</span>
                  <span>{{item.distance}}km</span>
                </div>
                <div>{{$t('lang.post_address')}}: {{item.address}}</div>
                <div>{{$t('lang.label_tel')}} {{item.phone}}</div>
              </div>
              
            </div>
          </van-list>
        </van-pull-refresh>
      </div>

      <div class="btn btn-submit box-flex" @click="usePost">{{$t('lang.post_use')}}</div>

    </div>

    <!--初始化loading-->
    <DscLoading :dscLoading="dscLoading"></DscLoading>
  </div>
</template>

<script>
import Vue from "vue";
import qs from 'qs';
import DscLoading from '@/components/DscLoading'
import { PullRefresh, List, Icon, Toast } from "vant";
Vue.use(PullRefresh);
Vue.use(List);
Vue.use(Icon);
Vue.use(Toast);
var AMap = window.AMap;
export default {
  components: {DscLoading},
  data() {
    return {
      postList: [],
      currentIndex: 0,
      loading: false,
      finished: false,
      dscLoading: true,
      refreshing: false,
      page: 1,
      showMap: false,
      ch: 0
    };
  },
  created() {
    this.ch = document.documentElement.clientHeight || document.body.clientHeight;
  },
  methods: {
    initMap() {
      this.showMap = true;
      const addressLngLat = JSON.parse(sessionStorage.getItem('addressLngLat'));
      let {lng, lat} = this.postList[0];
      const position = new AMap.LngLat(lng, lat);//标准写法
      let map = new AMap.Map("container", {
        zoom: 15, //级别
        center: position, //中心点坐标
        viewMode: "3D", //使用3D视图
        resizeEnable: true, //是否监控地图容器尺寸变化
      });
      // 创建一个 Marker 实例：
      let marker = new AMap.Marker({position: new AMap.LngLat(addressLngLat.lng, addressLngLat.lat)});
      // 设置label标签
      // label默认蓝框白底左上角显示，样式className为：amap-marker-label
      marker.setLabel({
          offset: new AMap.Pixel(10, 0),  //设置文本标注偏移量
          content: `<div class='marker_info_label'><div></div><span>${this.$t('lang.post_shipping_address')}</span></div>`, //设置文本标注内容
          direction: 'right' //设置文本标注方位
      });
      // 将创建的点标记添加到已有的地图实例：
      map.add(marker);
      this.map = map
      // 同时引入工具条插件，比例尺插件和鹰眼插件
      AMap.plugin(
        [
          "AMap.ToolBar",
          "AMap.Scale"
        ],
        function() {
          // 在图面添加工具条控件，工具条控件集成了缩放、平移、定位等功能按钮在内的组合控件
          map.addControl(new AMap.ToolBar());

          // 在图面添加比例尺控件，展示地图在当前层级和纬度下的比例尺
          map.addControl(new AMap.Scale());

        }
      );
      this.dscLoading = false;
      this.openInfoCart(this.postList[0]);
    },
    openInfoCart(currentInfo) {
      this.map.clearInfoWindow();
      let position = new AMap.LngLat(currentInfo.lng, currentInfo.lat);
      // 创建一个 Marker 实例：
      let marker = new AMap.Marker({position});
      // 将创建的点标记添加到已有的地图实例：
      this.map.add(marker);
      // 创建 infoWindow 实例 
      // 自定义信息窗体 
      let html = `<div class="info_cart">
                    <div class="info_top">
                      <div>${currentInfo.pick_up_point}</div>
                      <div>${this.$t('lang.post_address')}: ${currentInfo.address}</div>
                      <div>${this.$t('lang.label_tel')} ${currentInfo.phone}</div>
                    </div>
                    <div class="info_bottom">
                      <span>${this.$t('lang.post_distance_address')}: ${currentInfo.distance}km</span>
                    </div>
                    <div class="daosanjiao"></div>
                  </div>`
                  
      let infoWindow = new AMap.InfoWindow({
        autoMove: true,
        isCustom: true,  //使用自定义窗体
        content: html,  //传入 dom 对象，或者 html 字符串
        offset: new AMap.Pixel(0, -33)
      });
      // 在指定位置打开已创建的信息窗体
      infoWindow.open(this.map, position);
    },
    async getPostList() {
      const { data: {data: {post_list}}, status } = await this.$http.post(`${window.ROOT_URL}api/cgroup/flow/postlist`, qs.stringify({
        page: this.page,
        size: 10
      }));
      // 加载状态结束
      this.loading = false;
      if (status != 200) return Toast(this.$t('lang.post_msg_fail'));
      if (this.refreshing) {
          this.postList = [];
          this.currentIndex = 0;
          this.refreshing = false;
        }
       // 数据全部加载完成
      if (post_list.length < 10) {
        this.finished = true;
      } else {
        this.page ++
      }
      this.postList = [...this.postList, ...post_list];
      if (!this.showMap) this.initMap();
      
    },
    onRefresh() {
      // 清空列表数据
      this.finished = false;
      this.showMap = false;

      // 重新加载数据
      // 将 loading 设置为 true，表示处于加载状态
      this.loading = true;
      this.page = 1;
      this.getPostList();
    },
    changePost(i) {
      if (i == this.currentIndex) return;
      this.currentIndex = i;
      this.openInfoCart(this.postList[i])
    },
    async usePost() {
      if (this.dscLoading) return;
      this.dscLoading = true;
      const {leader_id} = this.postList[this.currentIndex];
      const {status} = await this.$store.dispatch('setChangeConsignee',{leader_id});
      this.dscLoading = false;
      if (status != 'success') return Toast(this.$t('lang.post_server_busy'));
      // this.$router.push('checkout');
      if(this.$route.query){
          if(this.$route.query.rec_type){
            if(this.$route.query.rec_type == 'supplier'){
              this.$router.push({
                name:this.$route.query.routerLink,
                query:{
                  rec_type:this.$route.query.rec_type,
                  rec_id:this.$route.query.rec_id,
                  leader_id
                }
              })
            }else{
              this.$router.push({
                name:this.$route.query.routerLink,
                query:{
                  rec_type:this.$route.query.rec_type,
                  type_id:this.$route.query.type_id,
                  team_id:this.$route.query.team_id,
                  leader_id
                }
              })
            }
          }else{
            if(this.$route.query.routerLink == 'crowdfunding-checkout'){
              this.$router.push({
                name:this.$route.query.routerLink,
                query:{
                  pid:this.$route.query.pid,
                  id:this.$route.query.id,
                  number:this.$route.query.number,
                  leader_id
                }
              })
            }else{
              this.$router.push({
                name:this.$route.query.routerLink,
                query: {
                  leader_id
                }
              })
            }
          }
        }
    }
  },
};
</script>

<style scoped>
.container-box {
  display: flex;
  flex-direction: column;
  width: 100%;
}
#container {
  flex: 1;
}
.list-box {
  height: 50%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding-top: 1rem;
  background-color: #fff;
}
.list_scroll_box::-webkit-scrollbar {
  display: none; /* Chrome Safari */
}
.list_scroll_box {
  box-sizing: border-box;
  flex: 1;
  scrollbar-width: none; /* firefox */
  -ms-overflow-style: none; /* IE 10+ */
  overflow-x: hidden;
  overflow-y: auto;
  padding: 0 1.2rem;
  -webkit-overflow-scrolling: touch; /*这句是为了滑动更顺畅*/
}
.list_item {
  display: flex;
  padding-bottom: 1rem;
}
.item-main {
  flex: 1;
  margin-left: 1rem;
  color: #A0A0A0;
}
.item-main div:nth-child(1) {
  display: flex;
  justify-content: space-between;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.6rem;
  color: #333;
}
.item_checked_icon {
  display: inline-block;
  width: 2rem;
  height: 2rem;
  border: 1px solid #ccc;
  border-radius: 50%;
}


</style>

