<template>
	<div class="con" v-waterfall-lower="loadMore" waterfall-disabled="waterDisabled" waterfall-offset="300">
		<div class="section-head">
			<van-row type="flex" justify="space-between">
				<van-col class="section-title">{{$t('lang.gift_card_goods_list')}}</van-col>
				<van-col><span class="section-note">{{$t('lang.label_card')}}{{id}}</span> <span class="section-btn" @click="logOut">{{$t('lang.drop_out')}}</span></van-col>
			</van-row>
		</div>
		<template v-if="listLength > 0">
			<div class="product-list m-top10">
				<ul>
					<li v-for="(item, index) in list" :key="index">
						<div class="product-div">
							<div class="product-list-img"><img class="img" :src="item.goods_thumb"></div>
							<div class="product-info product-comment">
								<h4>{{item.goods_name}}</h4>
								<div class="extra">
									<span :data-id="item.goods_id" class="p-btn" @click="pickGoods">{{$t('lang.pick_up_goods')}}</span>
									<div class="price" v-html="item.shop_price"></div>
								</div>
							</div>
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
	Row,
	Col,
	Toast
} from 'vant'
import NotCont from '@/components/NotCont'
import DscLoading from '@/components/DscLoading'

export default{
	data(){
		return {
			id: "",
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
		[Row.name]:Row,
		[Col.name]:Col,
        [Toast.name]:Toast,
		[Loading.name]: Loading,
		DscLoading,
        NotCont,
		CommonNav
	},
	created(){
		this.checkLoginGift();
	},
	computed:{
	},
	methods:{
		checkLoginGift(){
			let that = this;
			this.$http.get(`${window.ROOT_URL}api/gift_gard`).then(({data})=>{
				if(data.data.error && data.data.error != 0){
					that.$router.push({name: 'giftCard'})
				}
			})
		},
		loadList(page){
            if(page){
                this.page = page
                this.size = Number(page) * 10
            }
			let that = this;
			let o = {
				page:this.page,
				size:this.size
			}
			this.$http.get(`${window.ROOT_URL}api/gift_gard/gift_list`,{ params:o }).then(({data})=>{
				this.dscLoading = false
				that.id = data.data.gif;
				// 数据由object格式改为array格式
				let g = Object.keys(data.data.goods).map(function(v,i,arr){
					return data.data.goods[v]
				})
				if(g && g.length > 0){
					that.list = that.list.concat(g);
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
			this.loadList()
        },
        pickGoods(e){
			let goodsId = e.target.dataset.id;
			this.$router.push({name: 'giftCardAddress', params:{id: goodsId}})
        },
		logOut(){
			let that = this;
			this.$http.get(`${window.ROOT_URL}api/gift_gard/exit_gift`).then(({data})=>{
				if(data.data.error == 0){
					that.$router.push({name: 'giftCard'})
				}
			})
		}
	},
	watch:{
        list(){
			this.listLength = Object.keys(this.list).length;
			this.page = this.page + 1
        }
	}
}
</script>

<style lang="scss" scoped>
.section-head{
	padding: .8rem; background: #fff; line-height: 2.4rem;
	.section-title{ font-size: 1.4rem;}
    .section-note{ font-size: 1.2rem; }
	.section-btn{float: right; width: 6rem; height: 2.4rem; line-height: 2.2rem; margin-left: 1rem; border: 1px solid #f92028; color: #f92028; text-align: center; font-size: 1.2rem; cursor: pointer;}
}
.product-div{
    .extra{ height: 2.8rem; line-height: 2.8rem;}
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
