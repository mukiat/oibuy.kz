<template>
	<div class="user_help">
    <!--分销帮助-->
    <template v-if="type == 'drphelp'">
      <section>
        <h2 class="van-title">{{$t('lang.help_center')}}</h2>
        <van-cell-group>
          <van-cell :title="listItem.title" :to="{name:'articleDetail',params:{id:listItem.id}}" is-link v-for="(listItem,listIndex) in articleHelpList" :key="listIndex" />
        </van-cell-group>
      </section>
    </template>
    <!--会员帮助-->
    <template v-else>
  		<section v-for="(item,index) in articleHelpList" :key="index">
  			<h2 class="van-title">{{ item.cat_name }}</h2>
  			<van-cell-group>
  		    <van-cell :title="listItem.title" :to="{name:'articleDetail',params:{id:listItem.article_id}}" is-link v-for="(listItem,listIndex) in item.list" :key="listIndex" />
  		  </van-cell-group>
  	  </section>
    </template>
	</div>
</template>

<script>
import { mapState } from 'vuex'
import {
  Cell,
  CellGroup,
  Button,
  Popup,
  Field,
  RadioGroup,
  Radio,
  Panel
} from 'vant'

export default{
	data(){
		return{
      type:this.$route.query.type ? this.$route.query.type : ''
    }
	},
  components: {
    [Cell.name]: Cell, 
    [CellGroup.name]: CellGroup,
    [Button.name]: Button,
    [Popup.name]: Popup,
    [Field.name]: Field,
    [RadioGroup.name]: RadioGroup,
    [Radio.name]: Radio,
    [Panel.name]: Panel,
  },
  created(){
    this.$store.dispatch('setArticleHelp',{
      type:this.type
    })
  },
  computed:{
  	...mapState({
  		articleHelpList: state => state.user.articleHelpList
  	})
  },
  watch:{
  	articleHelpList(){
  		console.log(this.articleHelpList)
  	}
  }
}
</script>