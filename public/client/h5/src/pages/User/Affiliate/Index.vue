<template>
    <div class="con drp-info">
        <div class="warp">
            <div class="header">
                <div class="header-top">
                    <div class="header-img">
                        <img :src="affiliate_info.avatar" alt="" class="img" v-if="affiliate_info.avatar">
                        <img src="../../../assets/img/user_default.png" alt="" class="img" v-else>
                    </div>
                    <div class="header-right">
                        <h4>{{ affiliate_info.name }}</h4>
                        <div class="hang">
                            <div class="vip" v-if="affiliate_info.user_rank_name">
                                <span>{{ affiliate_info.user_rank_name }}</span>
                            </div>
                            <router-link :to="{ name: 'drp-register' }" v-if="affiliate_info.is_drp > 0" class="user-more">{{ $t('lang.open_vip') }}<i class="iconfont icon-more"></i></router-link>
                        </div>
                    </div>
                </div>
            </div>
            <div class="section protection">
                <div class="tit">
                    <div>{{$t('lang.enjoy_equity')}}</div>
                    <span class="user-more" @click="protectionHref(0)">{{$t('lang.more')}}<i class="iconfont icon-more"></i></span>
                </div>
                <div class="value">
                    <div class="item-list" v-for="(item,index) in affiliate_info.user_rank_rights_list" :key="index" @click="protectionHref(index)">
                        <div class="icon"><div class="img-box"><img :src="item.icon" class="img" /></div></div>
                        <div class="text">{{item.name}}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="affiliate-items">
            <div class="section section-money">
                <div class="tit">
                    <div>{{$t('lang.my_money')}}</div>
                </div>
                <div class="value">
                    <div class="item">
                        <p>{{ affiliate_info.user_money }}</p>
                        <span>{{$t('lang.is_deposit_money')}}</span>
                    </div>
                    <div class="item">
                        <p>{{ affiliate_info.user_total_order_amount }}</p>
                        <span>{{$t('lang.cumulative_commission')}}</span>
                    </div>
                    <div class="item">
                        <p>{{ affiliate_info.user_today_affiliate_money }}</p>
                        <span>{{$t('lang.today_income')}}</span>
                    </div>
                    <div class="item">
                        <p>{{ affiliate_info.user_total_affiliate_money }}</p>
                        <span>{{$t('lang.drp_total_amount')}}</span>
                    </div>
                </div>
                <div class="invite_friends_button" @click="inviteFriends">{{$t('lang.team_rule_tit_3')}}<i class="iconfont icon-more"></i></div>
            </div>
        </div>
        <div class="nav-items">
            <router-link :to="{name:'affiliateTeam',query:{type:'award'}}" class="nav-item">
                <i><img src="../../../assets/img/newuser/info-icon1.png" class="img"></i>
                <span>{{$t('lang.registration_award')}}</span>
            </router-link>
            <router-link :to="{name:'affiliateTeam',query:{type:'team'}}" class="nav-item">
                <i><img src="../../../assets/img/newuser/info-icon1.png" class="img"></i>
                <span>{{$t('lang.my_team_alt')}}</span>
            </router-link>
        </div>
    </div>
</template>
<script>
import qs from 'qs'
export default{
    data(){
        return{
            affiliate_info:''
        }
    },
    created(){
        this.$http.post(`${window.ROOT_URL}api/user/affiliate_info`).then(res=>{
            this.affiliate_info = res.data.data;
        })
    },
    methods:{
        inviteFriends(){
            this.$router.push({
                name:'affiliate'
            })
        },
        protectionHref(index){
            this.$router.push({
                name:'affiliateProtection',
                query:{
                    rank_id:this.affiliate_info.user_rank,
                    index:index
                }
            })
        },
    }
}
</script>
<style>
.affiliate-items{
    margin: -6rem 1.1rem 0;
    background-color: #ffffff;
    box-shadow: 0 0.5rem 1rem 0 rgba(95, 95, 95, 0.1);
    border-radius: 0.5rem;
}
.warp .header{
    background-image: url(../../../assets/img/newuser/info-bg2.png);
}
</style>
