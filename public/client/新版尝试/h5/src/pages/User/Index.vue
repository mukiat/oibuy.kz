<template>
  <div class="con" style="padding-bottom: 7rem;">
      <Default v-if="userInfo" :data="userInfo"></Default>
      <ec-tab-down></ec-tab-down>
  </div>
</template>

<script>
import { mapState } from 'vuex'

import axios from 'axios'

import EcTabDown from '@/components/visualization/tab-down/Frontend'
import Login from './Auth/Login'
import Default from './Auth/Default'

export default {
  name: 'user',
  components: {
    EcTabDown,
    Default
  },
  data(){
    return{}
  },
  created(){
    this.$store.dispatch('userProfile')
  },
  computed: {
    ...mapState({
      userInfo: state => state.user.userInfo
    })
  }
}
</script>