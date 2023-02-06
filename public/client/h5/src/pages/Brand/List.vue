<template>
	<div class="bs-list-warp">
		<div class="b-l-page-pos" id="bs-example">
			<ul>
				<li v-for="(item,index) in brandList" :key="index" :class="{'active':active == index}"><a href="javascript:;" @click="jump(index)">{{ item.info }}</a></li>
			</ul>
		</div>
		<div class="brand-list-page">
			<template v-for="(item,index) in brandList">
			<div class="item d_jump">
				<em class="b-l-a-id" :id="'link_'+item.info">{{ item.info }}</em>
				<ul>
					<li v-for="(brandlist,listIndex) in item.list">
						<router-link :to="{name:'brandDetail',params:{id:brandlist.brand_id}}">
							<img :src="brandlist.brand_logo"><span>{{ brandlist.brand_name }}</span>
						</router-link>
					</li>
				</ul>
			</div>
			</template>
		</div>

		<CommonNav></CommonNav>
	</div>
</template>

<script>
import { mapState } from 'vuex'

import CommonNav from '@/components/CommonNav'
export default{
	data(){
		return {
			active:0
		}
	},
	components:{
		CommonNav
	},
	created(){
		this.$store.dispatch('setBrandList')
	},
	computed:{
		...mapState({
			brandList: state => state.brand.brandList
		})
	},
	mounted(){
		this.$nextTick(() => {
      window.addEventListener('scroll', this.onScroll)
    })
	},
	methods:{
		onScroll(){
			let scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop
			let jump = document.querySelectorAll('.d_jump')
			let all = []
			
			for(let i=0; i<jump.length; i++){
				console.log(jump[i].offsetHeight + jump[i].offsetTop)
			}
		},
		jump(index){
			this.active = index

	    // 用 class="d_jump" 添加锚点
	    let jump = document.querySelectorAll('.d_jump')
	    let total = jump[index].offsetTop
	    let scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop
	    // 平滑滚动，时长500ms，每10ms一跳，共50跳
	    let step = total / 50
	    if (total > scrollTop) {
	      smoothDown()
	    } else {
	      let newTotal = scrollTop - total
	      step = newTotal / 50
	      smoothUp()
	    }
	    function smoothDown () {
	      if (scrollTop < total) {
	        scrollTop += step
	　　　　 document.body.scrollTop = scrollTop
	        document.documentElement.scrollTop = scrollTop
	        setTimeout(smoothDown, 10)
	      } else {
	        document.body.scrollTop = total
	        document.documentElement.scrollTop = total
	      }
	    }
	    function smoothUp () {
	      if (scrollTop > total) {
	        scrollTop -= step
	　　　　 document.body.scrollTop = scrollTop
	        document.documentElement.scrollTop = scrollTop
	        setTimeout(smoothUp, 10)
	      } else {
	        document.body.scrollTop = total
	        document.documentElement.scrollTop = total
	      }
			} 
		}
	}
}
</script>