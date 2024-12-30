<template>
	<div class="community-list">
		<template v-if="tabStatus">
			<template v-if="discoverList && discoverList.length > 0">
				<section class="com-nav" v-for="(item,index) in discoverList" :key="index">
					<template v-if="listMode == 'comlist'">
						<a href="javascript:;" @click="onDiscoverDetail(item.dis_type,item.dis_id)">
						<div class="com-min-tit dis-box">
							<em class="em-promotion-1 tm-ns">
								<template v-if="item.dis_type == 1">{{$t('lang.tao')}}</template>
								<template v-else-if="item.dis_type == 2">{{$t('lang.wen')}}</template>
								<template v-else-if="item.dis_type == 3">{{$t('lang.quan')}}</template>
								<template v-else>{{$t('lang.shai')}}</template>
							</em>
							<span class="box-flex"><strong class="ellipsis-one">{{ item.dis_title }}</strong></span>
						</div>
						<div class="dis-box com-header-img-cont">
							<div class="box-flex">
								<div class="com-header-img-box fl">
									<div class="img-commom"><img :src="item.user_picture" class="img-height"></div>
								</div>
								<div class="com-header-span-box fl"><span>{{ item.user_name }}</span></div>
							</div>
							<div class="t-time">
								<i class="iconfont icon-shijian"></i>
								<span>{{ item.add_time }}</span>
							</div>
						</div>
						</a>
					</template>
					<template v-else>
						<div class="com-hd">
							<a href="javascript:;" @click="onDiscoverDetail(item.dis_type,item.dis_id)">
								<div class="com-img">
									<div class="img-commom"><img :src="item.user_picture" class="img-height"></div>
								</div>
								<div class="com-info fl"><h4>{{ item.user_name }}</h4><p>{{ item.add_time }}</p></div>
							</a>
							
						</div>
						<div class="com-bd">
							<a href="javascript:;" @click="onDiscoverDetail(item.dis_type,item.dis_id)" class="dis-box">
								<em class="em-promotion-1 tm-ns">
									<template v-if="item.dis_type == 1">{{$t('lang.tao')}}</template>
									<template v-else-if="item.dis_type == 2">{{$t('lang.wen')}}</template>
									<template v-else-if="item.dis_type == 3">{{$t('lang.quan')}}</template>
									<template v-else>{{$t('lang.shai')}}</template>
								</em>
								<div class="com-title box-flex"><strong class="ellipsis-one">{{ item.dis_title }}</strong></div>
							</a>
						</div>
						<div class="com-fd dis-box">
							<div class="com-icon box-flex" @click="onZan(item.dis_type,item.dis_id)">
								<i class="iconfont icon-zan"></i>
								<span>{{item.like_num}}</span>
							</div>
							<div class="com-icon box-flex">
								<a href="javascript:;" @click="onDiscoverDetail(item.dis_type,item.dis_id)">
									<i class="iconfont icon-daipingjia"></i>
									<span>{{item.community_num}}</span>
								</a>
							</div>
							<div class="com-icon box-flex">
								<i class="iconfont icon-liulan"></i>
								<span>{{item.dis_browse_num}}</span>
							</div>
						</div>
					</template>
				</section>
			</template>
			<template v-else>
				<NotCont></NotCont>
			</template>
		</template>
		<template v-else>
			<van-loading type="spinner" color="black" size="3rem" class="loading-mar-5" />
		</template>
	</div>
</template>

<script>
import { Toast,Loading } from 'vant'

import NotCont from '@/components/NotCont'
export default{
	props:{
		discoverList:{
			type:Array,
			Default:[]
		},
		listMode:{
			type:String,
			Default:''
		}
	},
	data(){
		return{
			tabStatus:false
		}
	},
	components:{
		NotCont,
		[Toast.name]:Toast,
		[Loading.name]:Loading
	},
	methods:{
		onDiscoverDetail(type,id){
			this.$router.push({
				name:'discoverDetail',
				query:{
					dis_type:type,
					dis_id:id
				}
			})
		},
		onZan(type,id){
			this.$store.dispatch('setDiscoverLike',{
				dis_type:type,
				dis_id:id
			}).then(({data})=>{
				Toast(data.msg)
				this.$emit('getLikeNum',{
					likeNum:data.like_num,
					dis_id:id
				})
			})
		},
		onDelete(type,id){
			this.$store.dispatch('setDiscoverDelete',{
				dis_type:type,
				dis_id:id
			}).then(({data})=>{
				Toast(data.msg)

				if(data.error == 0){
					this.$emit('getDelete',{
						dis_id:id
					})
				}
			})
		}
	},
	watch:{
		discoverList(){
			this.tabStatus = true
		}
	}
}
</script>