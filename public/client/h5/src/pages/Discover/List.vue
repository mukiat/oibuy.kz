<template>
	<div class="con con_main">
		<Goods :data="discoverShow"></Goods>
		<template v-if="!mode">
			<List :discoverList="commentList" :listMode="comlist" ></List>
		</template>
		<template v-else>
		<div class="community-box">
			<div class="padding-all select-one-1 m-top06">
				<ul class="dis-box j-get-one">
					<li class="ect-select box-flex" v-for="(item,index) in tabs" :key="index" @click="onTab(item.text,item.id)"><label :class="{'active':active == item.id}">{{ item.text }}</label></li>
				</ul>
			</div>
			<div class="community-box-con m-top02">
                <div id="title" class="m-top02 b-color-f com-input-title">
	                <div class="input-text">
	                	<input type="text" v-model="title" :placeholder="$t('lang.fill_in_title')" autocomplete="off" name="title">
	                </div>
	            </div>
                <div class="com-textarea">
                	<div class="text-area1">
                		<textarea name="content" v-model="content" class="text-area1" maxlength="300" :placeholder="$t('lang.leave_some_text')"></textarea>
                		<span>{{ length }}</span>
                	</div>
                </div>
            </div>
            <div class="dis-box padding-all community-box-btn">
                <div class="box-flex"><label class="j-close-com" @click="cancel">{{$t('lang.cancel')}}</label></div>
                <div class="box-flex"><button class="btn btn-submit" @click="btnSubmit">{{$t('lang.confirm')}}</button></div>
            </div>
        </div>
		</template>
		<Nav :mode="mode" v-on:getState="handleState"></Nav>
	</div>
</template>

<script>
import { mapState } from 'vuex'

import { Toast } from 'vant'

import Goods from './components/Goods'
import Nav from './components/Nav'
import List from './components/List'

export default{
	data(){
		return {
			goods_id:this.$route.query.id,
			prent_id:0,
			dis_type:'all',
			page:1,
			size:10,
			mode:false,
			tabs:[
				{
					id:1,
					text:this.$t('lang.discuss_post'),
				},
				{
					id:2,
					text:this.$t('lang.interlocution_post'),
				},
				{
					id:3,
					text:this.$t('lang.circle_post'),
				},
			],
			title:'',
			content:'',
			active:1,
			commentList:[],
			comlist:'comlist'
		}
	},
	components:{
		Goods,
		Nav,
		List,
		[Toast.name]:Toast
	},
	created(){
		this.$store.dispatch('setDiscoverShow',{
			goods_id:this.goods_id,
		})

		this.$store.dispatch('setDiscoverCommentList',{
			goods_id:this.goods_id,
			dis_type:this.dis_type,
			page:this.page,
			size:this.size,
			id:this.prent_id
		}).then(res=>{
			this.commentList = res.data
		})
	},
	computed:{
		 ...mapState({
            discoverShow: state => state.discover.discoverShow,
        }),
		length(){
			let length = 300

			length = length - this.content.length

			return length
		}
	},
	methods:{
		onTab(text,val){
			this.active = val
		},
		handleState(val){
			this.mode = val
		},
		cancel(){
			this.mode = false
		},
		btnSubmit(){
			this.$store.dispatch('setDiscoverCreate',{
				goods_id:this.goods_id,
				dis_type:this.active,
				title:this.title,
				content:this.content
			}).then(res=>{
				Toast(res.data)

				this.$router.push({
					name:'discover'
				})
			})
		}
	}
}
</script>