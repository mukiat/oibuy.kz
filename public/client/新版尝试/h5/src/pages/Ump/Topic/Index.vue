<template>
    <div class="activity">
        <div class="list" v-for="(item,index) in topicList" :key='index' @click="detailClick(item.topic_id)">
            <div class="p-r">
                <span class="tag p-a color-white tag-gradients-color">{{$t('lang.topic')}}</span>
                <img class="img" :src="item.topic_img" v-if="item.topic_img" />
            </div>
            <div class="cont padding-all text-center bg-color-write">
                <h4 class="f-06 f-weight color-3">{{item.title}}</h4>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        name: "topic",
        components: {},
        data() {
            return {
                topicList: []
            }
        },
        created(){
            this.$http.get(`${window.ROOT_URL}api/topic`,{params:{
                page:1,
                size:10,
                device: window.shopInfo.device  // device 设备  h5 app wxapp
            }}).then(({data})=>{
                this.topicList = data.data
            })
        },
        computed: {},
        methods: {
            detailClick(id) {
                let title = ''
                this.topicList.forEach(res=>{
                    if(res.topic_id == id){
                        title = res.title
                    }
                })

                this.$router.push({
                    name: "topicHome",
                    params: {
                        id:id
                    }
                })
            }
        }
    };
</script>
