<template>
	<div class="activity con_main_b5">
		<template v-if="activityIndexData && activityIndexData.length > 0">
		<router-link :to="{name:'activity-detail',params:{act_id:item.act_id}}" class="show list" v-for="(item,index) in activityIndexData"
		    :key='index'>
			<div class="p-r">
				<div class="activity-tag">
					<span class="tag tag-gradients-color  color-white f-02">{{item.act_type}}</span>
					<div class="sanjiao"></div>
				</div>
				<img class="img" :src="item.activity_thumb" />
			</div>
			<div class="cont text-center bg-color-write">
				<h4 class="f-03 color-3">{{item.act_name}}</h4>
				<div class="f-03 color-9 m-top04">{{item.start_time}}{{$t('lang.zhi')}}{{item.end_time}}</div>
				<p class="color-red f-03 m-top02">{{item.activity_name}}</p>
			</div>
		</router-link>
		</template>
		<template v-else>
			<NotCont/>
		</template>
		<ec-tab-down></ec-tab-down>
		
		<CommonNav/>
	</div>
</template>

<script>
import { mapState } from 'vuex'
import {
	Loading,
	Toast
} from 'vant'

import commonGet from '@/mixins/common-get'
import CommonNav from '@/components/CommonNav'
import NotCont from '@/components/NotCont'
import EcTabDown from '@/components/visualization/tab-down/Frontend'

export default {
	name: 'activity',
	components: {
		CommonNav,
		NotCont,
		EcTabDown,
		[Loading.name]: Loading,
		[Toast.name]: Toast,
	},
	//初始化加载数据
	created() {
		//loading
		commonGet.loadingData()

		//初始化加载数据
		setTimeout(() => {
			this.$store.dispatch('setActivityIndex')
		},600)
	},
	computed: {
		...mapState({
			activityIndexData: state => state.activity.activityIndexData
		})
	},
	methods: {},
	watch:{
	}
}
</script>
