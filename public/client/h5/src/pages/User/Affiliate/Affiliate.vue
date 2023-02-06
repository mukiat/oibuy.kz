<template>
  <div>
    <template v-if="!dscLoading">
    	<template v-if="share && share.on > 0">
    		<div class="affiliate-cont-box">
  		  	<header class="p-r">
  				<img :src="shareImg" v-if="shareImg" class="img" />
  				<img src="../../../assets/img/user_share_1_bg.png" class="img" v-else/>
  		    </header>
          <div class="share-cont-box">
            <h3>{{ $t('lang.activity_content') }}:</h3>
            <p v-html="share.config.separate_desc"></p>
          </div>
  	  	</div>
    	</template>
    	<template v-else>
    		<NotCont :isSpan="false">
    			<span class="cont" slot="spanCon">{{ $t('lang.activity_cont') }}<br>{{ $t('lang.activity_admin') }}</span>
    		</NotCont>
    	</template>
    </template>
  	<CommonNav></CommonNav>

    <!--初始化loading-->
    <DscLoading :dscLoading="dscLoading"></DscLoading>
  </div>
</template>
<script>
import NotCont from '@/components/NotCont'
import CommonNav from '@/components/CommonNav'
import DscLoading from '@/components/DscLoading'
export default{
	data(){
		return{
			affdb:[],
			all_res:[],
			config_info:[],
			share:[],
      shareImg:'',
      dscLoading:true,
		}
	},
	components:{
		NotCont,
		CommonNav,
    DscLoading
	},
	created(){
		let o = {
			page:1,
			size:10
		}
		this.$http.get(`${window.ROOT_URL}api/invite`,{ params:o }).then(res=>{
			this.affdb = res.data.data.affdb
			this.all_res = res.data.data.all_res
			this.config_info = res.data.data.config_info
			this.share = res.data.data.share
      this.shareImg = res.data.data.img_src
      this.dscLoading = false
		})
	}
}
</script>
