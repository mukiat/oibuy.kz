<template>
	<div class="con con_main" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<div class="community">
			<ul>
				<li>
					<a href="javascript:;" @click="onTabs(1)">
						<div class="theme-left">
							<div class="theme-box tm-ns"><i class="iconfont icon-xiao36"></i></div>
						</div>
						<div class="theme-right">
							<h4>{{$t('lang.discuss_post')}}</h4>
							<p>{{ communityData.tao.num }}{{$t('lang.tiao')}}</p>
						</div>
					</a>
				</li>
				<li>
					<a href="javascript:;" @click="onTabs(2)">
						<div class="theme-left">
							<div class="theme-box tm-ls"><i class="iconfont icon-wenda"></i></div>
						</div>
						<div class="theme-right">
							<h4>{{$t('lang.interlocution_post')}}</h4>
							<p>{{ communityData.wen.num }}{{$t('lang.tiao')}}</p>
						</div>
					</a>
				</li>
				<li>
					<a href="javascript:;" @click="onTabs(3)">
						<div class="theme-left">
							<div class="theme-box tm-zs"><i class="iconfont icon-quanzi"></i></div>
						</div>
						<div class="theme-right">
							<h4>{{$t('lang.circle_post')}}</h4>
							<p>{{ communityData.quan.num }}{{$t('lang.tiao')}}</p>
						</div>
					</a>
				</li>
				<li v-if="communityData.shop_can_comment > 0">
					<a href="javascript:;" @click="onTabs(4)">
						<div class="theme-left">
							<div class="theme-box tm-hs"><i class="iconfont icon-paizhao"></i></div>
						</div>
						<div class="theme-right">
							<h4>{{$t('lang.sunburn_post')}}</h4>
							<p>{{ communityData.sun.num }}{{$t('lang.tiao')}}</p>
						</div>
					</a>
				</li>
			</ul>
		</div>
		<List :discoverList="discoverList" v-on:getLikeNum="handleLikeNum" v-on:getDelete="handleDelete" v-if="discoverList"></List>
		<div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
		<template v-if="loading">
			<van-loading type="spinner" color="black" />
		</template>
		<Nav :mode="mode" :type="type"></Nav>
		<CommonNav></CommonNav>
	</div>
</template>

<script>
import { mapState } from 'vuex'
import List from './components/List'
import Nav from './components/Nav'
import CommonNav from '@/components/CommonNav'
import arrRemove from '@/mixins/arr-remove'

import { 
	Waterfall,
	Loading
} from 'vant'

export default{
	data(){
		return{
			communityData:{
				tao:{},
				wen:{},
				quan:{},
				sun:{}
			},
			mode:true,
			dis_type:'all',
			page:1,
			size:10,
			type:'index',
			communityType:true,
			loading:false,
			footerCont:false
		}
	},
	directives: {
    	WaterfallLower: Waterfall('lower')
	},
	components:{ 
		List,
		Nav,
		CommonNav,
		[Loading.name]:Loading
	},
	created(){
		this.$store.dispatch('setDiscoverIndex').then(res=>{
			this.communityData = res.data
		})

		this.onlist()
	},
	computed:{
        discoverList:{
        	get(){
        		return this.$store.state.discover.discoverList
        	},
        	set(val){
        		this.$store.state.discover.discoverList = val
        	}
        }
	},
	methods:{
		onlist(page){
			if(page){
				this.page = page
				this.size = Number(page) * 10
			}

			this.$store.dispatch('setDiscoverList',{
				dis_type:this.dis_type,
				page:this.page,
				size:this.size
			})
		},
		onTabs(val){
			this.$router.push({
				name:'discoverListType',
				query:{
					type:val
				}
			})
		},
		handleLikeNum(obj){
			this.discoverList.forEach(v=>{
				if(v.dis_id == obj.dis_id){
					v.like_num = obj.likeNum
				}
			})
		},
		handleDelete(obj){
			this.discoverList.forEach((v,i)=>{
				if(v.dis_id == obj.dis_id){
					 this.discoverList.splice(i, 1)
				}
			})
		},
		loadMore(){
			setTimeout(() => {
				this.disabled = true
		    	if(this.page * this.size == this.discoverList.length){
		  			this.page ++
		  			this.onlist()	
		  		}
			},200)
	    }
	},
	watch:{
		discoverList(){
			if(this.page * this.size == this.discoverList.length){
				this.disabled = false
				this.loading = true
			}else{
				this.loading = false
				this.footerCont = this.page > 1 ? true : false
			}

			this.discoverList = arrRemove.trimSpace(this.discoverList)
		},
	}
}
</script>