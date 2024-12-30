<template>
	<div class="con" v-waterfall-lower="loadMore" waterfall-disabled="waterDisabled" waterfall-offset="300">
		<template v-if="list.length > 0">
			<div class="product-list">
				<ul>
					<li v-for="(item, index) in list" :key="index">
						<div class="product-div">
							<router-link to="">
								<div class="product-list-img"><img class="img" :src="item.goods_thumb"></div>
								<div class="product-info product-comment">
									<h4>{{item.goods_name}}</h4>
									<div class="extra m-top08">
										<template v-if="item.status == 1">
											<span class="fr color-red">{{$t('lang.order_status_09')}}</span>
										</template>
										<template v-else-if="item.status == 2">
											<div class="p-btn" :data-id="item.gift_gard_id" @click="pickGoods">{{$t('lang.received')}}</div>
										</template>
										<template v-else-if="item.status == 3">
											<span class="fr color-red">{{$t('lang.ss_received')}}</span>
										</template>
										<div class="gray">{{$t('lang.label_card')}}{{item.gift_sn}}</div>
									</div>
								</div>
							</router-link>
						</div>
					</li>
				</ul>
			</div>
			<div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
			<template v-if="loading">
				<van-loading type="spinner" color="black" />
			</template>
		</template>
        <NotCont v-else></NotCont>
		<CommonNav></CommonNav>
		<!--初始化loading-->
		<DscLoading :dscLoading="dscLoading"></DscLoading>
	</div>
</template>

<script>
import { mapState} from 'vuex'
import CommonNav from '@/components/CommonNav'
import {
    Waterfall,
    Loading,
	Toast
} from 'vant'
import NotCont from '@/components/NotCont'
import DscLoading from '@/components/DscLoading'

export default{
	data(){
		return {
			id: '',
			page: 1,
			size: 10,
			list: [],
			listLength: 0,
			waterDisabled: false,
            dscLoading:true,
			loading:true,
			footerCont:false
        }
	},
    directives: {
        WaterfallLower: Waterfall('lower')
    },
	components:{
        [Toast.name]:Toast,
		[Loading.name]: Loading,
		DscLoading,
        NotCont,
		CommonNav
	},
	created(){
	},
	computed:{
	},
	methods:{
		loadOrder(page){
            if(page){
                this.page = page
                this.size = Number(page) * 10
            }
			let that = this;
			let o = {
				page:this.page,
				size:this.size
			}
			this.$http.get(`${window.ROOT_URL}api/gift_gard/take_list`,{ params:o }).then(({data})=>{
				this.dscLoading = false
				if(data.data && data.data.length > 0){
					if(page == 1){
						that.list = data.data;
					}else{
						// 合并列表
						that.list = that.list.concat(data.data);
					}
					// 尚未加载完所有
					this.waterDisabled = false
					this.loading = true
				}else{
					// 所有数据加载完成
					this.waterDisabled = true
					this.loading = false
					this.footerCont = this.page > 1 ? true : false
				}
			})
		},
        loadMore(){
			this.waterDisabled = true
			this.loadOrder()
        },
        pickGoods(e){
			let that = this
			let take_id = e.target.dataset.id;
			let o = {
				take_id:take_id
			}
			this.$http.get(`${window.ROOT_URL}api/gift_gard/confim_goods`,{ params:o }).then(({data})=>{
				if(data.data.error==1){
					Toast(data.data.msg)
				}else{
					that.loadOrder(1)
				}
			})
        }
	},
	watch:{
		list(){
			this.page = this.page + 1
		}
	}
}
</script>

<style lang="scss">
.product-div{
    .extra{ height: 2.8rem; line-height: 2.8rem; font-size: 1.2rem;}
    .p-btn{
        float: right;
        width: 6rem;
        height: 2.8rem;
        line-height: 2.8rem;
        background: #f92028;
        color: #fff;
        text-align: center;
        font-size: 1.2rem;
        cursor: pointer;
    }
}
</style>
