<template>
	<div class="user-view" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
		<TabNav>
	        <template slot="tabItem">
	            <ul>
	                <li :class="{'active':status == ''}" @click="tabClick('')">{{$t('lang.all')}}({{count.all_auction}})</li>
	                <li :class="{'active':status == 'is_going'}" @click="tabClick('is_going')">{{$t('lang.underway')}}({{count.is_going}})</li>
	                <li :class="{'active':status == 'is_finished'}" @click="tabClick('is_finished')">{{$t('lang.has_ended')}}({{count.is_finished}})</li>
	            </ul>
	        </template>
	    </TabNav>
	    <template v-if="auction_list && auction_list.length > 0">
		    <div class="user-item user-order-item" v-for="(item,index) in auction_list" :key="index">
		    	<div class="item-hd">
        			<div class="head">
        				<h3></h3>
        				<em>{{ item.status }}</em>
        			</div>
        		</div>
        		<div class="item-bd">
        			<div class="subHead">
        				<h4><label>{{$t('lang.act_id')}}：</label><span>{{ item.act_id }}</span></h4>
        				<p><span>{{$t('lang.bid_time')}}：{{ item.bid_time }} </span></p>
        			</div>
        			<div class="list-bd-box list-order-box">
	                    <ul class="dis-box">
	                        <li class="reture-left-img"><div class="img-box"><img :src="item.goods_thumb" class="img"></div></li>
	                        <li class="reture-right-cont"><h4 class="twolist-hidden">{{ item.goods_name }}</h4></li>
	        			</ul>
        			</div>
        			<div class="list-item-box">{{$t('lang.bid_price')}}：<span v-html="item.bid_price" class="color-red"></span></div>
        		</div>
        		<div class="item-fd">
        			<h4></h4>
        			<div class="ect-button-more">
        				<router-link :to="{name:'auction-detail',params:{ act_id: item.act_id}}" class="btn-default-new">{{$t('lang.auction_desc')}}</router-link>
        			</div>
        		</div>
		    </div>
		</template>
		<template v-else>
			<NotCont></NotCont>
		</template>

	    <CommonNav></CommonNav>

	    <!--初始化loading-->
	    <DscLoading :dscLoading="dscLoading"></DscLoading>
	</div>
</template>

<script>
import {
    Waterfall,
    Loading,
    Dialog,
    Toast,
} from 'vant'

import CommonNav from '@/components/CommonNav'
import NotCont from '@/components/NotCont'
import TabNav from '@/components/user/TabNav'
import DscLoading from '@/components/DscLoading'
import arrRemove from '@/mixins/arr-remove'

export default{
	data(){
		return{
			disabled:false,
            loading:true,
            page:1,
            size:10,
            status:'',
            type:'type',
            tabStatus:true,
            dscLoading:true,
            footerCont:false,
            auction_list:[],
            count:{},
            keyword:''
		}
	},
	directives: {
        WaterfallLower: Waterfall('lower')
    },
	components:{
        TabNav,
        CommonNav,
        NotCont,
        DscLoading,
        [Dialog.name]: Dialog,
        [Loading.name]: Loading,
	},
    created(){
    	this.auctionList()
    },
    methods:{
    	auctionList(page){
    		if(page){
                this.page = page
                this.size = Number(page) * 10
            }

    		let o = {
    			page:this.page,
    			size:this.size,
    			auction:{
    				idTxt:this.status,
    				keyword:this.keyword,
    				action:'auction_list',
    				type:this.status
    			}
    		}

    		this.$http.get(`${window.ROOT_URL}api/auction/auction_list`,{ params:o }).then(res=>{
    			if(res.data.status == 'success'){
    				this.auction_list = res.data.data.list
    				this.count = res.data.data.count
    			}else{
    				Toast(this.$t('lang.error_in_return_data'))
    			}
    		})
    	},
    	tabClick(val){
    		this.status = val
    		if(val == 'is_going'){
    			this.keyword = 1
    		}else if(val == 'is_finished'){
    			this.keyword = 3
    		}else{
    			this.keyword = ''
    		}
    		this.auctionList()
    	},
    	loadMore(){
    		 setTimeout(() => {
                this.disabled = true
                if(this.page * this.size == this.auction_list.length){
                    this.page ++
                    this.auctionList()
                }
            },200);
    	},
    },
    watch:{
    	auction_list(){
            this.dscLoading = false
            if(this.page * this.size == this.auction_list.length){
                this.disabled = false
                this.loading = true
            }else{
                this.loading = false
                this.footerCont = this.page > 1 ? true : false
            }
            this.auction_list = arrRemove.trimSpace(this.auction_list)
        }
    }
}
</script>
