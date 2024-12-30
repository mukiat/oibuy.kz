<template>
    <div class="con"  v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
        <div class="team-rank  con_main">
            <TabSwitch @child-event="CommonTabs" :data='tabs'></TabSwitch>
            <div class="goods-li" v-if="teamRankData!=''">
                <a href="javascript:;" @click="detailLink(item.goods_id)" class="show li" v-for='(item, index) in teamRankData' :key="index">
                    <div class="left left-img p-r">
                        <img class="img goods-img" :src="item.goods_thumb" />
                        <img v-if="item.key===1" class="rank p-a" src="../../../../assets/img/rank-1.png" />
                        <img v-if="item.key===2" class="rank p-a" src="../../../../assets/img/rank-2.png" />
                        <img v-if="item.key===3" class="rank p-a" src="../../../../assets/img/rank-3.png" />
                    </div>
                    <div class="right bg-color-write">
                        <h4 class="f-05 color-3 twolist-hidden"> {{item.goods_name}}</h4>
                        <div class="f-02 color-9 m-top02">{{item.limit_num}}{{$t('lang.men_tuxedo')}}</div>
                        <div class="dis-box m-top06">
                            <div class="box-flex f-06 color-red f-weight " v-html="item.team_price"></div>
                            <van-row class="box-flex team-btn text-c f-03 text-center">
                                <van-col span="12" class="left-btn color-red"> {{item.team_num}}{{$t('lang.one_group')}}</van-col>
                                <van-col span="12" class="right-btn color-white btn-submit">{{$t('lang.up_group')}}</van-col>
                            </van-row>
                        </div>
                    </div>
                </a>
            </div>
            <div v-else>
                <NotCont/>
            </div>
            <TeamTabbar />
        </div>
        <CommonNav :routerName="routerName"></CommonNav>
        <template v-if="loading">
            <van-loading type="spinner" color="black" />
        </template>
    </div>
</template>
<script>
    import { mapState } from 'vuex'
    import {
        Tab,
        Tabs,
        Row,
        Col,
        Toast,
        Waterfall,
        Loading
    } from 'vant'

    import CommonNav from '@/components/CommonNav'
    import NotCont from '@/components/NotCont'
    import TabSwitch from '@/components/filter/TabSwitch'
    import TeamTabbar from './TeamTabbar'
    import arrRemove from '@/mixins/arr-remove'
    
    export default {
        name: "team-rank",
        components: {
            CommonNav,
            NotCont,
            TabSwitch,
            TeamTabbar,
            [Tab.name]: Tab,
            [Tabs.name]: Tabs,
            [Row.name]: Row,
            [Col.name]: Col,
            [Toast.name]: Toast,
            [Loading.name]: Loading,
        },
        directives: {
			WaterfallLower: Waterfall('lower')
		},
        data() {
            return {
                routerName:'team',
                disabled:false,
			    loading:true,
				size:10,
                page:1,
                status:0,
                active: 0,
                tabs: [this.$t('lang.hot_alt'), this.$t('lang.new'), this.$t('lang.optimization')],
            };
        },
        created() {
            setTimeout(() => {
                uni.getEnv(function(res){
                    if(res.plus || res.miniprogram){
                        uni.redirectTo({  
                            url:'../../pagesA/team/rank/rank'
                        })
                    }
                })
            },100)
            this.loadingData(this.teamRank(this.status))
        },
        computed: {
            teamRankData:{
                get(){
                    return this.$store.state.team.teamRankData      
                },
                set(val){
                    this.$store.state.team.teamRankData = val
                }
            }
        },
        methods: {
            loadMore(){
				setTimeout(() => {
					this.disabled = true
					if(this.page * this.size == this.teamRankData.length){
						this.page ++
                        this.teamRank(this.status)
					}
				},200)
			},
            teamRank(status) {
                this.$store.dispatch({
                    type: 'setTeamRank',
                    status: status,
                    size:this.size,
                    page: this.page,
                });
            },
            CommonTabs(index) {
                this.status = index 
                this.page =1    
                this.loadingData(this.teamRank(index))
            },
            //加载动画
            loadingData(url) {
                Toast.loading({
                    duration: 600,
                    mask: true,
                    message: this.$t('lang.loading')
                }, url);
            },
            detailLink(goods_id){
                this.$router.push({
                    name:'team-detail',
                    query:{ 
                        team_id:0,
                        goods_id:goods_id 
                    }
                })
            },
        },
        watch:{
			teamRankData(){
				if(this.page * this.size == this.teamRankData.length){
					this.disabled = false
					this.loading = true
				}else{
					this.loading = false
				}
                this.teamRankData = arrRemove.trimSpace(this.teamRankData)
			}
		}
    };
</script>
