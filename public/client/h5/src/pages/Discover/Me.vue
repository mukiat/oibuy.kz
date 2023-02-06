<template>
	<div class="con con_main">
		<div class="my-admin-header-box">
			<div class="com-bg">
				<div class="com-header">
					<div class="com-header-img">
						<div class="user-com-img-box">
							<div class="img-commom p-r">
								<img :src= "discoverMyInfo.avatar" class="img-height">
							</div>
						</div>
						<div class="com-admin"><h4>{{ discoverMyInfo.user_name }}</h4></div>
					</div>
				</div>
			</div>
		</div>
		<div class="com-list dis-box">
			<div class="item box-flex" :class="{'active':dis_type == 1}" @click="onTabs(1)">
				<h4>{{$t('lang.discuss_post')}}</h4>
				<p>{{ discoverMyInfo.type1_num }}</p>
			</div>
			<div class="item box-flex" :class="{'active':dis_type == 2}" @click="onTabs(2)">
				<h4>{{$t('lang.interlocution_post')}}</h4>
				<p>{{ discoverMyInfo.type2_num }}</p>
			</div>
			<div class="item box-flex" :class="{'active':dis_type == 3}" @click="onTabs(3)">
				<h4>{{$t('lang.circle_post')}}</h4>
				<p>{{ discoverMyInfo.type3_num }}</p>
			</div>
			<div class="item box-flex" :class="{'active':dis_type == 4}" @click="onTabs(4)">
				<h4>{{$t('lang.sunburn_post')}}</h4>
				<p>{{ discoverMyInfo.type4_num }}</p>
			</div>
		</div>
		<List :discoverList="discoverMyList" v-on:getLikeNum="handleLikeNum" v-on:getDelete="handleDelete"></List>
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
		return {
			dis_type:1,
			page:1,
			size:10,
			mode:true,
			type:'my',
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
		this.$store.dispatch('setDiscoverMy',{
			dis_type:this.dis_type,
			page:this.page,
			size:this.size,
		})
		this.onlist()
	},
	computed:{
		 ...mapState({
            discoverMyInfo: state => state.discover.discoverMyInfo,
        }),
		discoverMyList:{
			get(){
        		return this.$store.state.discover.discoverMyList
        	},
        	set(val){
        		this.$store.state.discover.discoverMyList = val
        	}
		}
	},
	methods:{
		onlist(page){
			if(page){
				this.page = page
				this.size = Number(page) * 10
			}

			this.$store.dispatch('setDiscoverMyList',{
				dis_type:this.dis_type,
				page:this.page,
				size:this.size
			})
		},
		onTabs(val){
			this.dis_type = val 
			this.onlist(1)
		},
		handleLikeNum(obj){
			this.discoverMyList.forEach(v=>{
				if(v.dis_id == obj.dis_id){
					v.like_num = obj.likeNum
				}
			})
		},
		handleDelete(obj){
			this.discoverMyList.forEach((v,i)=>{
				if(v.dis_id == obj.dis_id){
					 this.discoverMyList.splice(i, 1)
				}
			})
		},
	    loadMore(){
			setTimeout(() => {
				this.disabled = true
		    	if(this.page * this.size == this.discoverMyList.length){
		  			this.page ++
		  			this.onlist()	
		  		}
			},200)
	    }
	},
	watch:{
		discoverMyList(){
			if(this.page * this.size == this.discoverMyList.length){
				this.disabled = false
				this.loading = true
			}else{
				this.loading = false
				this.footerCont = this.page > 1 ? true : false
			}

			this.discoverMyList = arrRemove.trimSpace(this.discoverMyList)
		}
	}
}
</script>