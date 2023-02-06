<template>
    <div class="con">
        <div class="con_main drp-select cro_funing cro-user">
            <!--店铺信息-->
            <header class="header fun-header">
                <div class="header-img">
                    <img :src="crowdUserData.user_picture" class="img" />
                </div>
                <div class="admin color-white">
                    <h4 class="f-05">{{crowdUserData.user_name}}</h4>
                    <p class="f-03 m-top04">{{$t('lang.user_rank')}}{{crowdUserData.rank_name}}</p>
                </div>
            </header>
            <van-cell-group class="m-top08">
               <van-cell  is-link>
                    <router-link class="show" :to="{name:'crowdfunding-order'}">
                        <i class="iconfont icon-dingdan1 color-red"></i>
                        <span class="p-l05">{{$t('lang.my_order')}}</span>
                    </router-link>
               </van-cell>
               <van-cell  is-link>
                    <router-link class="show" :to="{name:'crowdfunding-buy'}"> 
                        <i class="iconfont icon-zan color-red"></i>
                        <span class="p-l05">{{$t('lang.my_support')}}</span>
                     </router-link>
               </van-cell>
               <van-cell  is-link>
                    <router-link class="show" :to="{name:'crowdfunding-focus'}"> 
                        <i class="iconfont icon-02 color-red"></i>
                        <span class="p-l05">{{$t('lang.my_interest')}}</span>
                     </router-link>
               </van-cell>
            </van-cell-group>
			<van-cell-group class="m-top08">
				<van-cell :title="$t('lang.item_recommendation')" icon="clock" />
			</van-cell-group>
			<!--商品列表-->
			<div class="goods-li" v-if="crowdBestData!=''">
				<div class="bg-color-write li" v-for='(item,index) in crowdBestData' :key="index"
					@click="detailClick(item)">
					<div class="left p-r">
						<img class="img" :src="item.title_img" />
						<div class="raise-cate-tag"><i class="iconfont icon-geren"></i>1</div>
					</div>
					<div class="right">
						<h4 class="f-05 color-3">{{item.title}}</h4>
						<div class="goods-num">
							<div class="list f-03 color-9">{{$t('lang.support_number')}}
								<span class="color-red">{{item.join_num}}</span>人
							</div>
							<div class="list f-03 color-9">{{$t('lang.time_remaining')}}{{item.shenyu_time}}{{$t('lang.tian')}}</div>
						</div>
						<div class="ect-progress dis-box">
							<p class="wrap box-flex">
								<span class="bar"  :style="{width:item.baifen_bi + '%'}">
									<i class="color"></i>
								</span>
							</p>
							<p class="txt">{{item.baifen_bi}}%</p>
						</div>
						<div class="goods-cont">
							<div class="list f-03 color-9">{{$t('lang.has_crowdfunding')}}
								<span class="color-red" v-html="item.join_money_formated"></span>
							</div>
							<div class="list f-03 color-9">{{$t('lang.target')}}
								<span class="color-red" v-html="item.amount_formated"></span>
							</div>
						</div>
					</div>
				</div>
            </div>
            <CroTabbar/>
        </div>
    </div>
</template>
<script>
	import { mapState } from 'vuex'

    import {
        Swipe,
        SwipeItem,
        GoodsAction,
        GoodsActionBigBtn,
        GoodsActionMiniBtn,
        Actionsheet,
        Cell,
        CellGroup,
        Tab,
        Tabs,
        Tag,
        Button,
        Row,
        Col
    } from 'vant'

    import NotCont from '@/components/NotCont'
    import CroTabbar from './CroTabbar'
    import CountDown from '@/components/CountDown'
    import arrRemove from '@/mixins/arr-remove'

    export default {
        name: "auction-detail",
        components: {
            CroTabbar,
            CountDown,
            NotCont,
            [Swipe.name]: Swipe,
            [SwipeItem.name]: SwipeItem,
            [GoodsAction.name]: GoodsAction,
            [GoodsActionBigBtn.name]: GoodsActionBigBtn,
            [GoodsActionMiniBtn.name]: GoodsActionMiniBtn,
            [Actionsheet.name]: Actionsheet,
            [Cell.name]: Cell,
            [CellGroup.name]: CellGroup,
            [Tab.name]: Tab,
            [Tabs.name]: Tabs,
            [Tag.name]: Tag,
            [Button.name]: Button,
            [Row.name]: Row,
            [Col.name]: Col
        },
        data() {
            return {
                routerName:'crowd_funding',
                disabled:false,
                loading:true,
                size:10,
                page:1,
            }
        },
        //初始化加载数据
        created() {
            this.$store.dispatch('setCrowdfundingUser')
            this.crowdGoods()
        },
        computed: {
            ...mapState({
                crowdUserData: state => state.crowdfunding.crowdUserData,
            }),
            crowdBestData:{
                get(){
                    return this.$store.state.crowdfunding.crowdBestData
                },
                set(val){
                    this.$store.state.crowdfunding.crowdBestData = val
                }
            }
        },
        methods: {
            loadMore(){
                setTimeout(() => {
                    this.disabled = true
                    if(this.page * this.size == this.crowdGoodsData.length){
                        this.page ++
                        this.crowdGoods()		
                    }
                },200);
            },
            crowdGoods() {
                this.$store.dispatch('setCrowdfundingBest',{
                    page:this.page,
                    size:this.size
                })
            },
            detailClick(item){
                this.$router.push({
                    name:'crowdfunding-detail',
                    params:{
                        id:item.id
                    }
                })
            }
        },
        watch:{
            crowdBestData(){
                if(this.page * this.size == this.crowdBestData.length){
                    this.disabled = false
                    this.loading = true
                }else{
                    this.loading = false
                }

                this.crowdBestData = arrRemove.trimSpace(this.crowdBestData)
			}
		}
    };
</script>
<style scoped>
#app [class*=van-hairline]::after {
    border-bottom:solid #ddd 1px;
}
.drp-select .fun-header{
    flex-direction: row;
    justify-content: flex-start;
    padding-bottom: 2rem;
}
.drp-select .fun-header .header-img{
    margin-right: 10px;
}
.drp-select .fun-header .admin{
    display: flex;
    flex-direction: column;
    flex: 1;
}
</style>