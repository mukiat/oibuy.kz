<template>
	<div class="search-page">
		<Search :placeholder="placeholder" :placeholderState="placeholderState" :shopId="shopId" :couId="couId"></Search>
		<div class="ms-search-wraper">
			<div class="recent-search">
				<div class="ms-search-head clearfix">
					<p>{{$t('lang.recent_search')}}</p>
					<i class="iconfont icon-delete" @click="emptyArr"></i>
				</div>
				<div class="ms-search-tags">
					<span v-for="(item,index) in latelyKeyword" :key="index" @click="searchTag(item)"><a href="javascript:void(0)">{{item}}</a></span>
					<span v-if="latelyKeyword.length == 0"><a href="javascript:void(0);">{{$t('lang.not_available')}}</a></span>
				</div>
			</div>
			<div class="hot-search" v-if="search_keywords.length>0">
				<div class="ms-search-head clearfix">
					<p>{{$t('lang.hot_search')}}</p>
				</div>
				<div class="ms-search-tags">
					<span v-for="(item,index) in search_keywords" :key="index"><a href="javascript:void(0)" @click="searchTag(item)">{{ item }}</a></span>
				</div>
			</div>
		</div>
		<ec-tab-down></ec-tab-down>
	</div>
</template>

<script>
import EcTabDown from '@/components/visualization/tab-down/Frontend'
import Search from '@/components/Search'

export default {
  name: 'search',
  data(){
  	return {
  		latelyKeyword:[],
  		search_keywords:[],
      placeholderState:0,
      placeholder:this.$t('lang.search_goods'),
      shopId:this.$route.query.shop_id ? this.$route.query.shop_id : 0,
      couId:this.$route.query.cou_id ? this.$route.query.cou_id : 0,
  	}
  },
  components: {
  	EcTabDown,
  	Search
  },
  created(){
  	console.log(this.couId,'search1')
  },
  activated(){
    this.shopId = this.$route.query.shop_id ? this.$route.query.shop_id : 0
    this.couId = this.$route.query.cou_id ? this.$route.query.cou_id : 0
    console.log(this.couId,'search2')
    this.load();
  },
  methods:{
  	load(){
      let configData = JSON.parse(sessionStorage.getItem('configData'));
      if(configData){
        this.search_keywords = configData.search_keywords.split(',');
      }

  		if(localStorage.getItem('LatelyKeyword')){
	  		this.latelyKeyword = JSON.parse(localStorage.getItem('LatelyKeyword'))
        this.placeholder = this.latelyKeyword[0]
        this.placeholderState = 1
	  	}else{
	  		this.latelyKeyword = []
        this.placeholder = this.$t('lang.search_goods')
        this.placeholderState = 0
	  	}
  	},
  	searchTag(val){
      this.placeholder = val
      if(this.shopId > 0){
        this.$router.push({
          name:'shopGoodsList',
          query:{
            keywords:val,
            ru_id:this.shopId
          }
        })
      }else{ 
    		this.$router.push({
          name:'searchList',
    			query:{
    				keywords:val,
            cou_id:this.couId
    			}
  		  })
      }
  	},
  	emptyArr(){
      this.placeholder = this.$t('lang.search_goods');
      this.placeholderState = 0;

  		localStorage.removeItem('LatelyKeyword')
  		this.load()
  	}
  }
}
</script>