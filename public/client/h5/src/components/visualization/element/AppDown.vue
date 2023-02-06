<template>
    <div class='app-down' v-if="bShow">
        <div class="ect-header-banner dis-box" id="ect-banner">
            <a href="javascript:;" @click="closeAppDown">
                <i class="iconfont icon-guanbi"></i>
            </a>
            <div class="box-flex">
                <img :src="imgUrl" style="margin-right:.4rem">
                <div class="ect-header-text">
                    <h4>{{ configData && configData.h5_index_pro_title ? configData.h5_index_pro_title : ''}}</h4>
                    <p>{{ configData && configData.h5_index_pro_small_title ? configData.h5_index_pro_small_title : ''}}</p>
                </div>
            </div>
            <a class="btn-submit1" @click="openApp">{{$t('lang.download_now')}}</a>
        </div>
    </div>
</template>

<script>
import { Toast } from 'vant'
import isApp from '@/mixins/is-app'
export default {
    mixins: [isApp],
    name: 'app-down',
    props:['configData'],
    data() {
        return {
            isShow: false,
            link: null,
            localShow: true,
            androidUrl:'',
            iosUrl:''
        }
    },
    components:{
        [Toast.name] : Toast
    },
    created() {
        let localShowVal = localStorage.getItem('localShow');

        if(localShowVal === null){
            this.getIsShow()
        }else{
            if(localShowVal === 'false'){
                this.localShow = false
            }else{
                this.localShow = true
            }
        }

        //this.getLink()
    },
    methods: {
        closeAppDown() {
            this.localShow = false
            localStorage.setItem('localShow', false)

            this.$emit('localShow',this.localShow)
        },
        getIsShow() {
            this.$http
                .post(`${window.ROOT_URL}api/visual/appnav`)
                .then(({ data: { data } }) => {
                    data.wap_index_pro == 1 ? (this.localShow = true) : (this.localShow = false)
                    localStorage.setItem('localShow', this.localShow)
                    this.androidUrl = data.wap_app_android
                    this.iosUrl = data.wap_app_ios
                }).catch(err => {
                    console.error(err)
                })
        },
        getLink() {
            this.$http
                .post(`${window.ROOT_URL}api/visual/view`)
                .then(({ data: { init_data: initData } }) => {
                    if(initData){
                        this.link = initData.app
                    }else{
                        this.link = null
                    }
                })
        },
        openApp(){
            let downHref = '';

            window.location.href = 'dscmall://type=home';
            //微信内
            if(isApp.isWeixinBrowser()){
                Toast('请在浏览器上打开')
            }else{
                //android端
                if(isApp.isAndroid){
                    downHref = this.androidUrl;
                }

                //ios端
                if(isApp.isiOS){
                    downHref = this.iosUrl;
                }

                setTimeout(function(){
                    window.location.href = downHref;
                },500)
            }
        }
    },
    computed: {
        bShow() {
            return this.localShow
        },
        imgUrl(){
            return this.configData && this.configData.h5_index_pro_image ? this.configData.h5_index_pro_image : `${ROOT_URL}/img/more_icon.png`
        }
    }
}
</script>

<style scoped>
.app-down {
    height: 5rem;
    z-index: 1999;
}

.ect-header-banner {
    background: rgba(0, 0, 0, 0.9);
    height: 5rem;
    line-height: 5rem;
    width: 100%;
    color: #fff;
    display: -webkit-box;
    display: -moz-box;
    display: -ms-box;
    display: box;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    width: 100%;
    z-index: 1999;
}

.ect-header-banner.active {
    top: -5rem;
}

.box-flex {
    -moz-box-flex: 1;
    -ms-box-flex: 1;
    box-flex: 1;
    -webkit-box-flex: 1;
    display: block;
    width: 100%;
}

.fl {
    float: left;
}

.box-flex img {
    float: left;
}

.ect-header-text {
    padding-left: 1.1rem;
    margin-top: 1.2rem;
}

.ect-header-banner.active {
    display: none;
}

.ect-header-banner i {
    color: #fff;
    font-size: 2rem;
    margin-left: 1rem;
}

.ect-header-banner img {
    width: 3rem;
    height: auto;
    margin-left: 1rem;
    margin-top: 1rem;
}

.ect-header-banner .ect-header-text {
    padding-left: 1.1rem;
    margin-top: 1.2rem;
}

.ect-header-banner h4 {
    font-size: 1.3rem;
    line-height: 1.2;
}

.ect-header-banner p {
    font-size: 1.1rem;
    color: #ccc;
    line-height: 1.2;
}

.ect-header-banner .btn-submit1 {
    padding: 0.4rem;
    margin-top: -0.3rem;
    font-size: 1.2rem;
    margin-right: 1rem;
    color: #fff;
    border: 1px solid #fff;
    border-radius: 4px;
}

.ect-header-banner .btn-submit1:hover {
    border-color: #fff;
}
</style>
