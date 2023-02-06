<template>
    <div class="con crowd-topic">
       <van-cell-group >
            <van-field
                v-model="message"
                type="textarea"
                :placeholder="$t('lang.crowdfunding_topic_message')"
                rows="1"
                maxlength="140"
                autosize
                @input="messageText"
            />
        </van-cell-group>
         <div class="text-right f-04  bg-color-write num"><span>{{remnant}}/140</span></div>
         <div class="padding-all dis-box footer-btn">
            <div class="box-flex li">
                <van-button class="f-05" size="large" @click="onClose">{{$t('lang.cancel')}}</van-button>
            </div>
            <div class="box-flex li">
                <van-button class=" br-5 f-05" type="primary" bottom-action @click="onTopic">{{$t('lang.subimt')}}</van-button>
            </div>
        </div>
    </div>
</template>
<script>
    import {
        Toast ,
        Field,
        Button,
        Cell,
        CellGroup,
    } from 'vant'
    export default {
        name: "drp-category",
        components: {
             [Cell.name] : Cell,
             [CellGroup.name] : CellGroup,
             [Button.name]: Button,
             [Field.name]: Field,
             [Toast.name]: Toast,
        },
        data() {
            return {
                routerName:'crowd_funding',
                message:'',
                remnant:140,
            };
        },
        created() {
            this.$store.dispatch('setCrowdfundingShow',{
                id: this.$route.query.id,
            })
        },
        methods: {
             messageText(){
                this.remnant = 140 - this.message.length;
            },
            onTopic(){
                if(this.message == ''){
                    Toast(this.$t('lang.topic_message_not_null'))
                }else{
                    this.$store.dispatch('setCrowdfundingMyTopic',{
                        id:this.$route.query.id,
                        topic_id:this.$route.query.topic_id,
                        content:this.message
                    }).then(res=>{
                        Toast(this.$t('lang.successful_topic_release'))

                        this.$router.push({
                            name:'crowdfunding-detail',
                            params:{
                                id:this.$route.query.id
                            }
                        })
                    })
                }
            },
            onClose(){
                this.message = ''
            }
        }
    };
</script>
