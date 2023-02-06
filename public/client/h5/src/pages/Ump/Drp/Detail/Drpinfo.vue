<template>
    <div class="con drp-info">
        <template v-if="drpdata.error == 0 || drpdata.audit == 1">
            <div class="warp">
                <div class="tip" v-if="drpdata.expiry && drpdata.expiry.expiry_status > 0 && drpdata.expiry.card_expiry_status == 1">{{drpdata.expiry.expiry_time_notice}}</div>
                <div class="tip" v-if="drpdata.expiry.card_expiry_status != 1">{{drpdata.expiry.card_status_notice}}</div>
                <div class="header">
                    <div class="header-top">
                        <div class="header-img">
                            <router-link :to="{ name: 'drp-set' }">
                                <img :src="drpdata.shop_info.user_picture" alt="" class="img" v-if="drpdata.shop_info.user_picture">
                                <img src="../../../../assets/img/user_default.png" alt="" class="img" v-else>
                            </router-link>
                        </div>
                        <div class="header-right">
                            <h4>{{drpdata.shop_info.shop_name}}</h4>

                            <template v-if="drpdata.expiry.expiry_status == 1">
                                <span class="time" >{{$t('lang.membership_of_validity')}}：{{$t('lang.have_expired')}}</span>
                            </template>
                            <template v-else>
                                <span class="time" v-if="drpdata.expiry.expiry_type == 'forever'">{{$t('lang.membership_of_validity')}}：{{$t('lang.permanence')}}</span>
                                <span class="time" v-else-if="drpdata.expiry.expiry_type == 'days'">{{$t('lang.membership_of_validity')}}：{{drpdata.expiry.expiry_time_format}}</span>
                                <span class="time" v-else-if="drpdata.expiry.expiry_type == 'timespan'">{{$t('lang.membership_of_validity')}}：{{drpdata.expiry.expiry_time_format}}</span>
                            </template>

                            <div class="hang">
                                <div class="vip">
                                    <i><img src="../../../../assets/img/newuser/icon-vip.png" class="img"></i>
                                    <span>{{drpdata.user_rank}}</span>
                                </div>
                                <span class="user-more" @click="drpApplyHref">{{$t('lang.detail')}}<i class="iconfont icon-more"></i></span>
                            </div>
                        </div>
                    </div>
					<!-- expiry_status 会员权益状态 0 未过期、1 已过期、2 快过期 -->
					<!-- card_expiry_status 权益卡状态 0 已停发、1 正常、2 已过期 -->
                    <div class="header-bottom bor">
                        <div class="drp-button">
							<div class="item" @click="drpRenew" v-if="drpdata.expiry.expiry_status != 0 && drpdata.expiry.card_expiry_status == 1">
								<p>{{$t('lang.renew')}}</p>
                            </div>
							<div class="item" @click="drpChange" v-if="drpdata.expiry.expiry_status == 0 || drpdata.expiry.expiry_status == 2">
								<p>{{$t('lang.change')}}</p>
							</div>
							<div class="item" v-if="drpdata.expiry.expiry_status == 1" @click="applyAgain">
								<p>{{$t('lang.re_purchase')}}</p>
							</div>
                        </div>
                    </div>
                </div>
                <div class="section protection" v-if="card && protectionList.length > 0">
                    <div class="tit">
                        <div>{{$t('lang.enjoy_equity')}}</div>
                        <span class="user-more" @click="protectionHref(0)">{{$t('lang.more')}}<i class="iconfont icon-more"></i></span>
                    </div>
                    <div class="value">
                        <div class="item-list" v-for="(item,index) in protectionList" :key="index" @click="protectionHref(index)">
                            <div class="icon"><div class="img-box"><img :src="item.icon" class="img" /></div></div>
                            <div class="text">{{item.name}}</div>
                        </div>
                    </div>
                </div>
                <div class="section section-money">
                    <div class="tit">
                        <div>{{pageDrpInfo.my_asset ? pageDrpInfo.my_asset : $t('lang.my_assets')}}</div>
                        <span class="user-more" @click="depositLog">{{$t('lang.deposit_log')}}<i class="iconfont icon-more"></i></span>
                    </div>
                    <div class="value">
                        <div class="item" @click="Withdraw">
                            <p>{{drpdata.surplus_amount}}</p>
                            <span>{{pageDrpInfo.shop_money ? pageDrpInfo.shop_money : $t('lang.deposit_brokerage')}}</span>
                        </div>
                        <div class="item">
                            <p>{{drpdata.totals}}</p>
                            <span>{{pageDrpInfo.total_drp_log_money ? pageDrpInfo.total_drp_log_money : $t('lang.drp_totals')}}</span>
                        </div>
                        <div class="item">
                            <p>{{drpdata.today_total}}</p>
                            <span>{{pageDrpInfo.today_drp_log_money ? pageDrpInfo.today_drp_log_money : $t('lang.today_income')}}</span>
                        </div>
                        <div class="item">
                            <p>{{drpdata.total_amount}}</p>
                            <span>{{pageDrpInfo.total_drp_order_amount ? pageDrpInfo.total_drp_order_amount : $t('lang.drp_total_amount')}}</span>
                        </div>
                    </div>
                </div>
                <div class="section section-money">
                    <div class="tit">
                        <div>{{pageDrpInfo.order_card ? pageDrpInfo.order_card : $t('lang.rec_card')}}</div>
                        <router-link :to="{name:'drp-order',query:{type:'card'}}" class="user-more">{{$t('lang.detailed')}}<i class="iconfont icon-more"></i></router-link>
                    </div>
                    <div class="value">
                        <div class="item" @click="teamClick">
                            <p>{{drpdata.team_count}}</p>
                            <span>{{pageDrpInfo.order_card_total ? pageDrpInfo.order_card_total : $t('lang.card_total_number')}}</span>
                        </div>
                        <div class="item">
                            <p>{{drpdata.card_total_amount}}</p>
                            <span>{{pageDrpInfo.card_total_amount ? pageDrpInfo.card_total_amount : $t('lang.drp_total_amount')}}</span>
                        </div>
                        <div class="item">
                            <p>{{drpdata.card_today_money}}</p>
                            <span>{{pageDrpInfo.card_today_money ? pageDrpInfo.card_today_money : $t('lang.today_rewards')}}</span>
                        </div>
                        <div class="item">
                            <p>{{drpdata.card_total_money}}</p>
                            <span>{{pageDrpInfo.card_total_money ? pageDrpInfo.card_total_money : $t('lang.cumulative_rewards')}}</span>
                        </div>
                    </div>
                    <div class="invite_friends_button" @click="inviteFriends()">{{pageDrpInfo.drp_card ? pageDrpInfo.drp_card : $t('lang.team_rule_tit_3')}}<i class="iconfont icon-more"></i></div>
                </div>
                <div class="section section-money">
                    <div class="tit">
                        <div>{{$t('lang.help_center')}}</div>
                        <router-link :to="{name:'help',query:{type:'drphelp'}}" class="user-more">{{$t('lang.more')}}<i class="iconfont icon-more"></i></router-link>
                    </div>
                    <ul class="list-ul">
                        <li v-for="(item, articleIndex) in drpdata.article_list" :key="articleIndex"><router-link :to="{name:'articleDetail',params:{id:item.id}}">{{item.title}}</router-link></li>
                    </ul>
                </div>
            </div>
            <div class="drp-info-team">
                <div class="tit">
                    <i class="row"></i>
                    <span>{{pageDrpInfo.drp_team ? pageDrpInfo.drp_team : $t('lang.my_team_alt')}}</span>
                </div>
                <div class="items">
                    <div class="item item1" @click="teamClick">
                        <div class="num">{{drpdata.sum_count}}</div>
                        <div class="link"></div>
                        <div class="text">{{pageDrpInfo.sum_count ? pageDrpInfo.sum_count : $t('lang.user_total')}}</div>
                    </div>
                    <div class="item item2">
                        <div class="num">{{drpdata.team_count}}</div>
                        <div class="link"></div>
                        <div class="text">{{pageDrpInfo.team_count ? pageDrpInfo.team_count : $t('lang.directly_user')}}</div>
                    </div>
                    <div class="item item3">
                        <div class="num">{{drpdata.user_count}}</div>
                        <div class="link"></div>
                        <div class="text">{{pageDrpInfo.user_count ? pageDrpInfo.user_count : $t('lang.direct_referrals')}}</div>
                    </div>
                </div>
            </div>
            <div class="nav-items">
                <router-link :to="{name:'drp-order',query:{type:'card'}}" class="nav-item">
                    <i><img src="../../../../assets/img/newuser/info-icon1.png" class="img"></i>
                    <span>{{pageDrpInfo.order_card_list ? pageDrpInfo.order_card_list : $t('lang.card_reward')}}</span>
                </router-link>
                <router-link :to="{name:'drp-order',query:{type:'order'}}" class="nav-item">
                    <i><img src="../../../../assets/img/newuser/info-icon2.png" class="img"></i>
                    <span>{{pageDrpInfo.order_list ? pageDrpInfo.order_list : $t('lang.sale_reward')}}</span>
                </router-link>
                <router-link :to="{name:'drp-rank'}"  class="nav-item">
                    <i><img src="../../../../assets/img/newuser/info-icon3.png" class="img"></i>
                    <span>{{pageDrpInfo.drp_rank ? pageDrpInfo.drp_rank : $t('lang.rich_list')}}</span>
                </router-link>
                <div class="nav-item" @click="drpshopLink">
                    <i><img src="../../../../assets/img/newuser/info-icon4.png" class="img"></i>
                    <span>{{pageDrpInfo.drp_store ? pageDrpInfo.drp_store : $t('lang.my_drp')}}</span>
                </div>
            </div>
            <div class="adv" v-if="drpdata.banner && drpdata.banner.length > 0">
                <Swiper v-if="drpdata.banner" :data="drpdata.banner" :autoplay='3000'></Swiper>
            </div>
        </template>
        <template v-else>
            <div class="ectouch-notcont">
                <div class="img">
                    <img class="img" src="../../../../assets/img/no_content.png" />
                </div>
                <template v-if="viewStatus == 1">
                    <template v-if="viewAudit == 0">
                        <span class="cont">{{$t('lang.drp_status_propmt_1')}}</span>
                    </template>
                    <template v-if="viewAudit == 2">
                        <span class="cont">{{drpdata.msg ? drpdata.msg : $t('lang.drp_status_propmt_7')}}</span>
						<span class="cont">{{drpdata.log_content ? drpdata.log_content : ''}}</span>
						<div class="v-btn" @click='reApply'>{{$t('lang.new_registration')}}</div>
                    </template>
                </template>
                <template v-if="viewStatus == 2">
                    <span class="cont">{{$t('lang.drp_status_propmt_3')}}<router-link :to="{name:'drp-register'}" class="color-red">{{$t('lang.to_apply')}}</router-link></span>
                </template>
            </div>
        </template>
        <ec-tab-down></ec-tab-down>

        <!--续费方式-->
        <van-popup class="show-popup-bottom" v-model="renewShow" position="bottom">
            <div class="goods-show-title padding-all">
                <h3 class="fl">{{$t('lang.fill_in_renew')}}</h3>
                <i class="iconfont icon-close fr" @click="renewClose"></i>
            </div>
            <div class="s-g-list-con">
                <div class="select-two">
                    <ul>
                        <li class="ect-select" v-for="(item,index) in card.receive_value" :key="index"
                            :class="{'active':renew_type == item.type}"
                            @click="renew_method_select(item.type)">
                            <label class="dis-box">
                                <span class="box-flex" v-if="item.type == 'integral'">{{$t('lang.drp_apply_title_1')}}</span>
                                <span class="box-flex" v-if="item.type == 'order'">{{$t('lang.drp_apply_title_2')}}</span>
                                <span class="box-flex" v-if="item.type == 'buy'">{{$t('lang.drp_apply_title_3')}}</span>
                                <span class="box-flex" v-if="item.type == 'goods'">{{$t('lang.drp_apply_title_4')}}</span>
                                <span class="box-flex" v-if="item.type == 'free'">{{$t('lang.drp_apply_title_5')}}</span>
                                <i class="iconfont icon-gou"></i>
                            </label>
                        </li>
                    </ul>
                </div>
            </div>
        </van-popup>

        <!--初始化loading-->
        <DscLoading :dscLoading="dscLoading"></DscLoading>
    </div>
</template>
<script>
    import { mapState } from 'vuex'
    import {
        Button,
        Toast,
        Popup
    } from 'vant'
    import Swiper from '@/components/Swiper'
    import CommonNav from '@/components/CommonNav'
    import EcTabDown from '@/components/visualization/tab-down/Frontend'
    import DscLoading from '@/components/DscLoading'
    export default {
        components: {
            Swiper,
            CommonNav,
            EcTabDown,
            DscLoading,
            [Button.name]: Button,
            [Toast.name]: Toast,
            [Popup.name]: Popup,
        },
        data() {
            return {
                viewStatus:0,
                routerName:'drp',
                routerPath:'',
                dscLoading:true,
                renewShow:false,
                renew_type:'',
                back:this.$route.query.back,
                pageDrpInfo: {}
            }
        },
        //初始化加载数据
        async created() {
            await this.getCustomText();
            this.$store.dispatch('setDrp');
        },
        //计算属性
        computed: {
            ...mapState({
                drpdata: state => state.drp.drpData
            }),
            card(){
                return this.drpdata.membership_card_info ? this.drpdata.membership_card_info : ''
            },
            protectionList(){
                return this.card ? this.card.user_membership_card_rights_list : ''
            }
        },
        mounted(){
            if (window.history && window.history.pushState && this.back) {
                // 向历史记录中插入了当前页
                history.pushState(null, null, document.URL);
                window.addEventListener('popstate', this.goBack, false);
            }
        },
        methods: {
            goBack(){
                this.$router.replace({
                    path: this.back
                })
            },
            teamClick(){
                let that = this
                that.$router.push({
                    name: "drp-team",
                    params: {
                        user_id: that.drpdata.shop_info.user_id
                    }
                });
            },
            drpshopLink(){
                if(this.viewStatus != 3){
                    this.$router.push({
                        name:'drp'
                    })
                }else{
                    Toast(this.$t('lang.drp_status_propmt_8'))
                }
            },
            inviteFriends(){
                this.$router.push({
                    name:'drp-card'
                })
            },
            //提现金额
            Withdraw() {
                let that = this
                that.$router.push({
                    name: 'drp-withdraw'
                });
            },
            protectionHref(index){
                this.$router.push({
                    name:'drp-protection',
                    query:{
                        card_id:this.card.id,
                        index:index
                    }
                })
            },
            drpApplyHref(){
                this.$router.push({
                    name:'drp-apply',
                    query:{
                        card_id:this.card.id
                    }
                })
            },
            drpRenew(){
                this.renewShow = true
            },
			drpChange(){

				this.$router.push({
				    name: "drp-register",
				    query: {
						apply_status: 'change',
				        membership_card_id: this.card.id
				    }
				});
			},
            applyAgain(){

                this.$router.push({
                    name: "drp-register",
                    query: {
						apply_status: 'repeat',
                        membership_card_id: this.card.id
                    }
                });
            },
            renewClose(){
                this.renewShow = false
            },
            renew_method_select(type){
                let o = {}

                this.renew_type = type;
				// 续费权益卡
                if(this.card.id){
                    o = {
                        receive_type: type,
						apply_status: 'renew',
                        membership_card_id:this.card.id
                    }
                }else{
                    o = {
                        receive_type: type,
						apply_status: 'renew',
                    }
                }

                this.$router.push({
                    name:'drp-apply',
                    query:o
                });
            },
            depositLog(){
                this.$router.push({
                    name:'drp-withdraw-log'
                });
            },
            // 分销管理-自定义设置数据
            async getCustomText() {
                const {data: {status, data: {page_drp_info}}} = await this.$http.post(`${window.ROOT_URL}api/drp/custom_text`, {code: 'page_drp_info'});
                if (status == 'success') {
                    this.pageDrpInfo = page_drp_info || {};
                }
            },
			// 重新申请
			reApply(){
				this.$router.push({
				    name: "drp-register",
				    query: {
						apply_status: 'repeat',
				        membership_card_id: this.drpdata.shop_info.membership_card_id
				    }
				});
			}
        },
        watch:{
            drpdata(){
                setTimeout(()=>{
                    this.dscLoading = false
                },1000)

                this.viewStatus = this.drpdata.error
                this.viewAudit = this.drpdata.audit

                if(this.viewStatus == 2){
                    this.$router.replace({
                        name:'drp-register',
                        query:{
                            back:this.routerPath
                        }
                    });
                }
            }
        },
        beforeRouteEnter(to,form,next){
            next(vm=>{
                vm.routerPath = form.fullPath
            })
        }
    }
</script>
<style scoped>

.v-btn {
	width: 100%;
	max-width: 18rem;
	margin: 0.5rem auto 0.5rem;
	text-align: center;
	line-height: 3rem;
	background: #000;
	color: #E3C49E;
	border-radius: 1.5rem;
	box-shadow: 0 12px 6px -8px rgba(0,0,0,0.3);
	font-size: 1.5rem;
	cursor: pointer;
}

</style>
