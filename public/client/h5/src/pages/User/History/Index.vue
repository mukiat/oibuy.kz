<template>
	<div class="con">
		<header class="history-header dis-box">
		    <div class="box-flex">{{$t('lang.history')}} <em class="col-7">{{ length }}</em>{{$t('lang.tiao')}}</div>
		    <div class="his-btn clear_history" @click="clearHistory">{{$t('lang.empty')}}</div>
		</header>
		<div class="product-list">
			<ul>
				<li v-for="(item,index) in historyList" :key="index">
					<div class="product-div">
						<div class="product-list-img"><router-link :to="{ name: 'goods', params: { id: item.id }}"><img class="img" :src="item.img"></router-link></div>
						<div class="product-info product-comment">
		      	    		<h4><router-link :to="{ name: 'goods', params: { id: item.id }}">{{item.name}}</router-link></h4>
		      	    		<div class="product-lst">
		      	    			<div class="price" v-html="item.price"></div>
		      	    			<a href="javascript:;" @click="deleteHistory(item.id)">{{$t('lang.delete')}}</a>
		      	    		</div>
		      	    	</div>
					</div>
				</li>
			</ul>
		</div>
		<CommonNav></CommonNav>
	</div>
</template>

<script>
import { mapState } from 'vuex'
import CommonNav from '@/components/CommonNav'

export default{
	data(){
		return {}
	},
	components:{
		CommonNav
	},
	created(){
		this.$store.dispatch('setHistory')
	},
	computed:{
		...mapState({
			historyList : state => state.user.historyList,
		}),
		length(){
			return this.$store.state.user.historyList.length
		}
	},
	methods:{
		clearHistory(){
			this.$store.dispatch('setHistoryDelete')
		},
		deleteHistory(id){
			this.$store.dispatch('setHistoryDelete',{
				id:id
			})
		}
	}
}
</script>