<template>
	<div class="log_info">
		<template v-if="accountlog_list.length > 0">
			<van-cell class="detail-list" v-for="(item,index) in accountlog_list" :key="index">
				<div class="card-div dis-box">
					<div>
						<p>{{ item.type }}</p>
						<span>{{ item.add_time }}</span>
					</div>
					<div class="box-flex text-right">
						<p v-html="item.amount"></p>
						<span class="color-red">{{ item.pay_status }}</span>
					</div>
				</div>
			</van-cell>
		</template>
		<template v-else-if="!isLoading">
        	<NotCont></NotCont>
      	</template>
      	<CommonNav></CommonNav>
	</div>
</template>

<script>
import {
	List,
	Cell
} from 'vant'

import CommonNav from '@/components/CommonNav'
import NotCont from '@/components/NotCont'

export default{
	data(){
		return {
			loading:false,
			accountlog_list:[],
			isLoading: true
		}
	},
	components:{
		[List.name]:List,
		[Cell.name]:Cell,
		NotCont,
		CommonNav
	},
	created(){
		this.isLoading = true;
	    this.$http.get(`${window.ROOT_URL}api/account/replylog`).then(res =>{
			this.isLoading = false;
	     	if(res.data.status == 'success'){
	     		this.accountlog_list = res.data.data
	     	}
	    })
  	},
	methods:{
		onLoad(){

		}
	}
}
</script>
