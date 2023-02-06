<template>
    <div class="drp-team">
        <div class="team-info" v-if="type == 'team'">
            <div class="team-list" v-if="user_child_list && user_child_list.legnth > 0">
                <div class="item" v-for="(item,index) in user_child_list" :key="index">
                    <div class="left">
                        <div class="picture">
                            <img v-if="item.user_picture" class="img" :src="item.user_picture" />
                            <img v-else class="img" src="../../../assets/img/user_default.png" />
                        </div>
                        <div class="team_info_con">
                            <h4 class="onelist-hidden">{{item.user_name}}</h4>
                            <p>{{$t('lang.label_addtime')}}{{item.reg_time}}</p>
                        </div>
                    </div>
                </div>
            </div>
            <NotCont v-else />
        </div>
        <div class="team-info" v-else>
            <div class="team-list" v-if="affiliate_list && affiliate_list.legnth > 0">
                <div class="item" v-for="(item,index) in affiliate_list" :key="index">
                    <div class="left">
                        <div class="picture">
                            <img v-if="item.user_picture" class="img" :src="item.user_picture" />
                            <img v-else class="img" src="../../../assets/img/user_default.png" />
                        </div>
                        <div class="team_info_con">
                            <h4 class="onelist-hidden">{{item.user_name}}</h4>
                            <p>{{$t('lang.label_addtime')}}{{item.reg_time}}</p>
                        </div>
                    </div>
                    <div class="right">
                        <div class="color-red">+ {{ item.money }}</div>
                    </div>
                </div>
            </div>
            <NotCont v-else />
        </div>
    </div>
</template>
<script>
    import CommonNav from '@/components/CommonNav'
    import NotCont from '@/components/NotCont'

    export default {
        data() {
            return {
                type:this.$route.query.type,
                user_child_list:[],
                affiliate_list:[]
            }
        },
        components:{
            CommonNav,
            NotCont
        },
        beforeCreate(){
            document.title = this.$route.query.type == 'team' ? this.$t('lang.my_team_alt') : this.$t('lang.registration_award');
        },
        created() {
            if(this.type == 'team'){
                this.userTeam();
            }else{
                this.registerAward();
            }
        },
        methods:{
            userTeam(){
                this.$http.post(`${window.ROOT_URL}api/user/child_list`).then(res=>{
                    this.user_child_list = res.data.data;
                })
            },
            registerAward(){
                this.$http.post(`${window.ROOT_URL}api/user/affiliate_list`).then(res=>{
                    this.affiliate_list = res.data.data;
                })
            }
        }
    }
</script>
