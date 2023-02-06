<template>
  <div id="app">
    <keep-alive>
      <router-view v-if="$route.meta.keepAlive"></router-view>
    </keep-alive>
    <router-view v-if="!$route.meta.keepAlive"></router-view>
  </div>
</template>

<script>
import qs from 'qs'

import 'swiper/dist/css/swiper.css'

export default {
  name: 'app',
  data(){
  	return{
    }
  },
  mounted(){
  },
  computed:{
  },
  methods: {
  }
}
</script>

<style lang="scss">
@import './assets/css/common/common.scss';
@import './assets/css/common/style.scss';
@import './assets/css/common/new.scss';
@import './assets/css/common.scss';
	.attr-goods-content .sku-item .sku-list .van-checkbox .van-checkbox__icon{
		display: none !important;
	}
	.attr-goods-content .sku-item .sku-list .van-checkbox .van-checkbox__label{
		margin-left:0 !important;
	}
.el-loading-spinner .path {
  stroke: $color !important;
}

.el-loading-spinner .el-loading-text {
  color: $color !important;
}

// 社区驿站页 地图窗体覆盖物样式
.info_cart {
  width: 26rem;
}
.info_top {
  display: flex;
  flex-direction: column;
  padding: 1.2rem 1.2rem .5rem;
  color: #F8F8F8;
  line-height: 1;
  border-top-left-radius: .5rem;
  border-top-right-radius: .5rem;
  box-sizing: border-box;
  background-color: #3366ff;
}
.info_top div:nth-child(1) {
  font-size: 1.6rem;
  font-weight: 700;
  color: #fff;
}
.info_top div:nth-child(2) {
  padding: .7rem 0;
}
.info_bottom {
  display: flex;
  justify-content: space-between;
  background-color: #fff;
  padding: 1rem 1.2rem;
  color: #A0A0A0;
  font-size: 1.4rem;
  border-bottom-left-radius: .5rem;
  border-bottom-right-radius: .5rem;
}
.info_bottom .info_bottom_icon {
  display: inline-block;
  width: 2rem;
  height: 2rem;
  text-align: center;
  line-height: 2rem;
  font-size: 1.2rem;
  color: white;
  background-color: #f92028;
  border-radius: 50%;
}
.daosanjiao {
  width: 1.5rem;
  height: 1.5rem;
  transform: rotate(45deg);
  margin: 0 auto;
  margin-top: -0.8rem;
  background-color: #fff;
}
// 地图窗体覆盖物文本标记样式
.amap-marker-label{
  border: 0;
}
.marker_info_label {
  position: relative;
}
.marker_info_label span {
  padding: 0.5rem;
}
.marker_info_label div {
  position: absolute;
  top: 50%;
  left: -1rem;
  width: 1rem;
  height: 1rem;
  transform: rotate(45deg) translateY(-50%);
  background-color: #fff;
}
// 社区驿站页 地图窗体覆盖物样式结束
</style>
