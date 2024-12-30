<template>
    <div class="team-wait" v-if="teamInfo">
        <div class="header dis-box">
            <div class="left">
                <img class="img" :src="teamInfo.user_picture" v-if="teamInfo.user_picture" />
                <img class="img" src="../../../../assets/img/user_default.png" v-else />
            </div>
            <template v-if="teamInfo.status == 0">
            <div class="right box-flex">
                <h4 class="f-06 color-3">{{$t('lang.team_propmt_2')}}</h4>
                <p class="f-03 color-7 m-top06">{{$t('lang.only')}}<span class="color-red">{{teamInfo.surplus}}</span>{{$t('lang.team_propmt_3')}}</p>
            </div>
            </template>
            <template v-else-if="teamInfo.status == 1">
            <div class="right box-flex">
                <h4 class="f-06 color-3">{{$t('lang.team_propmt_4')}}</h4>
                <p class="f-03 color-7 m-top06">{{$t('lang.team_propmt_5')}}</p>
            </div>
            </template>
            <template v-else>
            <div class="right box-flex">
                <h4 class="f-06 color-3">{{$t('lang.team_propmt_6')}}</h4>
                <p class="f-03 color-7 m-top06">{{$t('lang.only')}}<span class="color-red">{{teamInfo.surplus}}</span>{{$t('lang.team_propmt_7')}}</p>
            </div>
            </template>
        </div>
        <a href="javascript:;" @click="detailLink(teamInfo.goods_id)" class="goods-li show">
            <div class="li">
                <div class="left">
                    <img class="img" :src="teamInfo.goods_thumb" />
                </div>
                <div class="right bg-color-write">
                    <h4 class="f-05 color-3 twolist-hidden">{{teamInfo.goods_name}}</h4>
                    <div class="dis-box cont">
                        <div class="f-02 color-9 box-flex">{{teamInfo.team_num}}{{$t('lang.one_group')}}</div>
                    </div>
                    <div class="dis-box m-top06 ">
                        <div class="box-flex color-red f-06" v-html="teamInfo.team_price"></div>
                    </div>
                </div>
            </div>
        </a>    
        <div class="m-top10 bg-color-write">
            <template v-if="teamInfo.status == 0">
                <div class="title-hrbg-team" v-if="teamInfo.current_time != undefined && teamInfo.end_time != undefined">
                    <label>{{$t('lang.residue')}}<count-down :endTime="teamInfo.end_time" :callback="callback" :endText="$t('lang.activity_end')"></count-down>{{$t('lang.end')}}</label>
                    <hr/>
                </div>
            </template>
            <template v-else-if="teamInfo.status == 1"><div class="title-hrbg-team">{{$t('lang.team_success')}}<hr></div></template>
            <template v-else><div class="title-hrbg-team">{{$t('lang.team_fail')}}<hr></div></template>
        </div>
        <div class="team-header bg-color-write">
            <van-row>
                <van-col span="4" v-for="(item,index) in teamUser" :key="index">
                    <div class="tag-box" v-if="index == 0">{{$t('lang.regimental_commander')}}</div>
                    <div class="img-box">
                        <img class="img" :src="item.user_picture" v-if="item.user_picture" />
                        <img class="img" src="../../../../assets/img/user_default.png" v-else />
                    </div>
                </van-col>
                <van-col span="4" v-if="teamUser.length > 5">
                    <router-link :to="{name:'team-user',params:{team_id:teamInfo.team_id}}" class="icobox show">
                        <i class="iconfont icon-gengduo1 text-center color-7" />
                    </router-link>
                </van-col>
            </van-row>
            <van-progress class="m-top10" :pivot-text="pivotText" color="#f3594b" :percentage="teamInfo.bar" />
        </div>
        <!--拼团规则-->
          <div class="team-rule bg-color-write m-top10">
            <div class="title  f-04 color-3">{{$t('lang.team_rule')}}</div>
            <div class="dis-box fontsize-04 text-c color-7 p-r m-top10">
                <div class="box-flex p-r">
                    <label class="num-left p-a f-04 text-center bg-color-write">1</label>
                    <p class="text-center">{{$t('lang.team_rule_tit_1')}}</p>
                    <div class="n-list-xian-first p-a"></div>
                </div>
                <div class="box-flex p-r">
                    <label class="num-left p-a f-04 text-center bg-color-write">2</label>
                    <p class="text-center">{{$t('lang.team_rule_tit_2')}}</p>
                    <div class="n-list-xian p-a"></div>
                </div>
                <div class="box-flex p-r">
                    <label class="num-left p-a f-04 text-center bg-color-write">3</label>
                    <p class="text-center">{{$t('lang.team_rule_tit_3')}}</p>
                    <div class="n-list-xian p-a"></div>
                </div>
                <div class="box-flex p-r">
                    <label class="num-left p-a f-04 text-center bg-color-write">4</label>
                    <p class="text-center">{{$t('lang.team_rule_tit_4')}}</p>
                    <div class="n-list-xian-last p-a"></div>
                </div>
            </div>
            <div class="team-jiantou p-r" @click="goodsRule">
                <i class="iconfont icon-moreunfold p-a color-9"></i>
            </div>
            <!--拼团规则弹框-->
            <van-actionsheet class="auction-box" v-model="ruleShow" :title="$t('lang.team_rule')">
              <div class="padding-all">
                <div class="f-03 color-7">{{$t('lang.team_rule_con_1')}}</div>
                <div class="f-03 color-7 m-top06">{{$t('lang.team_rule_con_2')}}</div>
                <div class="f-03 color-7 m-top06">{{$t('lang.team_rule_con_3')}}</div>
                <div class="f-03 color-7 m-top06">{{$t('lang.team_rule_con_4')}}</div>
              </div>
            </van-actionsheet>
          </div>
        <van-goods-action>
            <van-goods-action-big-btn :text="$t('lang.team_more')" @click="teamMore"/>
            <template v-if="teamInfo.status > 0">
                <van-goods-action-big-btn :text="$t('lang.up_group')" primary @click="goTeam" />
            </template>
            <template v-else>
                <template v-if="teamInfo.team_join == 1">
                    <van-goods-action-big-btn :text="$t('lang.invite_friends_join_team')" primary @click="shareClick" />
                </template>
                <template v-else>
                    <van-goods-action-big-btn :text="$t('lang.tuxedo_immediately')" primary @click="goTeamGoods" />
                </template>
            </template>
        </van-goods-action>
        <!--分享-->
        <div class="van-modal" :class="{'hide':mask === true}" @click="close" style="z-index:1000"></div>
        <div class="bargain-share van-modal" :class="{'hide':shareState === true}" style="z-index:1001">
            <div class="bargain-friends">
                <div class="header f-30 col-3">{{$t('lang.share_hint')}}</div>
                <div class="cont f-24 text-center">{{$t('lang.share_toast_hint')}}</div>
                <div class="footer f-24 col-3" @click="close">{{$t('lang.i_see')}}</div>
            </div>
        </div>
        <CommonNav :routerName="routerName"></CommonNav>
    </div>
</template>
<script>
    import { mapState } from 'vuex'
    import {
        Row,
        Col,
        Progress,
        Actionsheet,
        GoodsAction,
        GoodsActionBigBtn,
    } from 'vant'

    import CommonNav from '@/components/CommonNav'
    import CountDown from '@/components/CountDown'

    export default {
        name: "team-wait",
        components: {
            CountDown,
            CommonNav,
            [Row.name]: Row,
            [Col.name]: Col,
            [Progress.name]: Progress,
            [Actionsheet.name]: Actionsheet,
            [GoodsAction.name]: GoodsAction,
            [GoodsActionBigBtn.name]: GoodsActionBigBtn,
        },
        data() {
            return {
                routerName:'team',
                mask:true,
                shareState: true,
                ruleShow: false, //拼团规则
                content: '',
                status:this.$route.query.status ? this.$route.query.status : 0
            };
        },
        created() {
            this.$store.dispatch('setTeamWait',{
                team_id: this.$route.query.team_id,
                user_id: this.$route.query.user_id,
            })

            if(this.status == 1){
                this.shareState = false
            }
        },
        computed: {
            ...mapState({
                teamWaitData: state => state.team.teamWaitData,
            }),
            //拼团人员
            teamUser(){
                return this.teamWaitData.teamUser
            },
            //拼团信息
            teamInfo(){
                return this.teamWaitData.team_info
            },
            pivotText(){
                return this.teamInfo.bar + '%'
            }
        },
        methods: {
            //分享
            shareClick() {
                this.shareState = false
            },
            //关闭蒙板
            close() {
                this.mask = true
                this.shareState = true
            },
            teamMore(){
                this.$router.push({
                    path: "team/rank",
                    name: "team-rank",
                })
            },
            //拼团规则
            goodsRule() {
                this.ruleShow = !this.ruleShow
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
            goTeam(){
                this.$router.push({
                    name:'team'
                })
            },
            goTeamGoods(){
                this.$router.push({
                    name:'team-detail',
                    query:{
                        team_id:this.$route.query.team_id,
                        goods_id:this.teamInfo.goods_id
                    }
                })
            },
            callback(){}
        },
        watch:{
            teamWaitData(){
                let url = window.location.href.split('#')[0] + '#'
                let link = ''
                let parent_id = this.$route.query.parent_id ? this.$route.query.parent_id : null
                
                if(parent_id){
                    link = url + this.$route.path +'?parent_id='+ parent_id + '&team_id=' + this.teamInfo.team_id
                }else{
                    link = url + this.$route.path + '?team_id='+ this.teamInfo.team_id
                }
                
                //单独设置微信分享信息
                this.$wxShare.share({
                    title:this.teamInfo.goods_name,
                    desc:this.teamInfo.team_desc,
                    link:link,
                    imgUrl:this.teamInfo.goods_thumb
                })
            }
        }
    }
</script>
