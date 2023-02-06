<template>
    <div class="con" v-if="presaleData.length!=0">
        <div class="con_main preslae-box">
            <div class="preslae-list" v-for="(item,index) in presaleData" :key="index" v-if="item.goods!=''">
                <router-link :to="{name:'presale-list',params:{cat_id:item.cat_id}}" class="show">
                    <van-cell-group class="common-cell">
                        <van-cell :title="item.cat_name" is-link :value="$t('lang.more')" />
                    </van-cell-group>
                </router-link>
                <div class="goods-li">
                    <router-link :to="{name:'presale-detail',params:{act_id:item.act_id}}" class="li active" v-for='(item,index) in item.goods' :key="index" @click="detailClick(item)">
                        <div class="left p-r">
                            <img v-if="item.goods_thumb!=''" class="img" :src="item.goods_thumb" />
                           	<img v-else class="img" src="../../../assets/img/not_goods.png" />
                        </div>
                        <div class="right bg-color-write">
                            <h4 class="f-05 color-3 twolist-hidden">{{item.goods_name}}</h4>
                            <div class="cont">
                                <div class="m-top08 dis-box">
                                    <div class="box-flex">
                                        <span class="f-06 color-red" v-html="item.format_shop_price"></span>
                                        <del class="color-9 f-02" v-html="item.format_market_price"></del>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <em class="preslae-end" v-if="item.already_over == 1"></em>
                    </router-link>
                </div>
            </div>
            <div class="presale-footer">
                <!--热门分类-->
                <van-popup v-model="cateShow" position="bottom" :overlay="false">
                    <ul>
                        <li v-for="(item,index) in  presaleData" :key="index">
                            <router-link :to="{name:'presale-list',params:{cat_id:item.cat_id}}" href="presale/detail" class="onelist-hidden">{{item.cat_name}}</router-link>
                        </li>
                    </ul>
                </van-popup>
                <!--底部导航-->
                <van-row class="bg-color-write nav-list text-center f-05 color-3">
                    <van-col span="8">
                        <div class="li" @click="listClick('list')">{{$t('lang.first_order')}}</div>
                    </van-col>
                    <van-col span="8">
                        <span class="li" @click="cateClick">
                            <i class="iconfont icon-menu"></i>
                            <label class="p-l05">{{$t('lang.popular_category')}}</label>
                        </span>
                    </van-col>
                    <van-col span="8">
                        <div class="li" @click="listClick('new')">{{$t('lang.new_product_release')}}</div>
                    </van-col>
                </van-row>
            </div>
        </div>
        <CommonNav/>
    </div>
    <div v-else>
        <div class="Loading-box">
            <van-loading color="black" />
            <p class="f-03 color-9 text-center m-top10">{{$t('lang.loading')}}</p>
        </div>
    </div>
</template>
<script>
	import { mapState } from 'vuex'

    import {
        Row,
        Col,
        Cell,
        CellGroup,
        Popup,
        Toast,
        Loading 
    } from 'vant'

    import NotCont from '@/components/NotCont'
    import CommonNav from '@/components/CommonNav'

    export default {
        name: "presale",
        components: {
            CommonNav,
            NotCont,
            [Row.name]: Row,
            [Col.name]: Col,
            [Cell.name]: Cell,
            [CellGroup.name]: CellGroup,
            [Popup.name]: Popup,
            [Toast.name]: Toast,
            [Loading.name]: Loading,
        },
        data() {
            return {
                cateShow: false
            }
        },
        //初始化加载数据
		created() {
            this.$store.dispatch('setPresale')
		},
		computed: {
			...mapState({
				presaleData: state => state.other.presaleData,
			})
		},
        methods: {
            //加载动画
			loading(url) {
				let that = this
				Toast.loading({
					duration: 600,
					mask: true,
					message: this.$t('lang.loading')
				}, url);
			},
            cateClick() {
                this.cateShow = !this.cateShow
            },
            listClick(val){
                if(val == 'list'){
                    this.$router.push({
                        name:'presale-list',
                        params:{
                            cat_id:0
                        }
                    })
                }else if(val == 'new'){
                    this.$router.push({
                        name:'presale-list',
                        params:{
                            cat_id:0
                        },
                        query:{
                            type:true
                        }
                    })
                }
            }
        }
    }
</script>
