<template>
  <div id="app">
      <dashboard></dashboard>
  </div>
</template>

<script>
import Dashboard from '@/pages/Admin/Dashboard'

import 'swiper/dist/css/swiper.css'

export default {
  name: 'app',
  components: {
    Dashboard
  }
}
</script>

<style lang="scss">
@import '../assets/style/config';
@import '../assets/style/common/index';
</style>

