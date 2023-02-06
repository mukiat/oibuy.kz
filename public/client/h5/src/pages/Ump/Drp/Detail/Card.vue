<template>
    <div class="con">
        <div class="card" v-if="isWx">
            <div class="banner">
                <img src="../../../../assets/img/newuser/fx-img-1.jpg" class="img" />
            </div>
            <div class="content">
                <div class="tit">{{$t('lang.invitation_rules')}}</div>
                <div class="text">
                    <p>{{$t('lang.invitation_rules_app')}}</p>
                    <p>{{$t('lang.invitation_rules_haibao')}}<p>
                    <p>{{$t('lang.invitation_rules_count')}}</p>
                </div>
                <div class="button"><img src="../../../../assets/img/newuser/fx-img-2.png" class="img" /></div>
            </div>
        </div>
        <div class="card" v-else>
            <img :src="cardData" class="img" />
        </div>

        <CommonNav :routerName="routerName">
	         <li slot="aloneNav">
				<router-link :to="{name: 'drp'}">
					<i class="iconfont icon-fenxiao"></i>
					<p>{{$t('lang.drp_center')}}</p>
				</router-link>
			</li>
	    </CommonNav>
    </div>
</template>
<script>
    import isApp from '@/mixins/is-app'
    import CommonNav from '@/components/CommonNav'
    export default {
        name: "drp-card",
        components: {
            CommonNav
        },
        mixins: [isApp],
        data() {
            return {
                routerName:'drp',
                cardData:'',
                isWx:false
            }
        },
        created(){
            // if(isApp.isWeixinBrowser()){
            //     this.isWx = true
            // }else{
            //     this.isWx = false
            // }

            this.$http.get(`${window.ROOT_URL}api/drp/user_card`).then(({ data:{ data } })=>{
                this.cardData = data.outImg
            })
        },
        computed: {},
        methods: {

        }
    }
</script>
