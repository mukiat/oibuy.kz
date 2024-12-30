<template>
    <div class=" bargain-detail team-user">
        <div class="qinyou-cont">
            <div class="li dis-box bg-color-write p-r" v-for="(item, index) in teamUserData" :key="index">
                <div class="left">
                    <div class="tag-box" v-if="item.team_user_id==0">{{$t('lang.regimental_commander')}}</div>
                    <div class="img-box">
                        <img class="img" src="../../../../assets/img/user_default.png" />
                    </div>
                </div>
                <div class="box-flex">
                    <div class="dis-box m-top02">
                        <h4 class="f-05 color-3 box-flex">Angelo</h4>
                    </div>
                    <p class="color-9 f-02 m-top04">{{$t('lang.open_team_time')}}{{item.add_time}}</p>
                </div>
            </div>
        </div>
        <CommonNav :routerName="routerName"></CommonNav>
    </div>
</template>
<script>
    import { mapState } from 'vuex'
    import CommonNav from '@/components/CommonNav'
    export default {
        name: "team-user",
        components: {
            CommonNav
        },
        data() {
            return {
                routerName:'team',
            };
        },
        created() {
            this.$store.dispatch({
                type: 'setTeamUser',
                team_id: this.$route.params.team_id
            });
        },
        computed: {
            ...mapState({
                teamUserData: state => state.team.teamUserData,
            })
        }
    };
</script>
