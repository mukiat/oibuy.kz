<template>
    <div id='other-area'>
        <div class="code">
            <div id="qrcode"></div>
            <qrcanvas :options="options"></qrcanvas>
            <p>{{ otherArea.QRCodeText }}</p>
        </div>
        <div class="button" v-if="false">
            <ec-button class="export" size="large" type="primary">{{ otherArea.function.export }}</ec-button>
            <ec-button class="import" size="large" type="success">{{ otherArea.function.import }}</ec-button>
        </div>
        <div class="warm-tip">
            <h5>{{ otherArea.prompt.headline }}</h5>
            <p>{{ otherArea.prompt.text }}</p>
            <a :href="otherArea.prompt.http.link" target="_blank" v-if="false">{{ otherArea.prompt.http.text }}</a>
        </div>
    </div>
</template>

<script>
//library node
import url from 'url'

// mapActions mapState
import { mapActions, mapState } from 'vuex'

// third party components
import Qrcanvas from 'qrcanvas-vue';
import {
    Button
} from 'element-ui'

export default {
    name: 'other-area',
    components: {
        "EcButton": Button,
        Qrcanvas
    },
    data() {
        return {
            sCodeUrl:''
        }
    },
    mounted() {
    },
    computed: {
        ...mapState({
            otherArea: state => state.dashboard.configInfo.otherArea,
            type: state => state.pageSetting.type,
            modulesId: state => state.pageSetting.modulesId
        }),
        options() {
            return {
                data: this.sCodeUrl,
                size: 166,
            }
        }
    },
    watch:{
        modulesId(){
            if (window.shopInfo.ruid != 0) {
                this.sCodeUrl = `${window.ROOT_URL}mobile#/shopHome/${window.shopInfo.ruid}?codeurl=true`
            }else{
                if(this.type == "index"){
                    this.sCodeUrl = `${window.ROOT_URL}mobile#/home?codeurl=true`
                }else{
                    this.sCodeUrl = `${window.ROOT_URL}mobile#/topicHome/` + this.modulesId + '?codeurl=true'
                }
            }
        }
    }
}

</script>

<style lang="scss" scoped>
@import '../../../assets/style/config';
@import '../../../assets/style/mixins/common';
$export-color :#3eb1fa;
$import-color :#0cd0c4;
.code {
    margin-top: 1.1rem;
    width: 164px;
    margin-left: 50%;
    @include translateX(-50%);
}

.code img {
    width: 100%;
    height: auto;
}

.code p {
    font-size: 1.3rem;
    text-align: center;
    margin-top: .6rem;
    color: $title-color;
    overflow: hidden;
}

.button {
    overflow: hidden;
    padding: 1rem 1.2rem;
    margin-top: .6rem;
}

.button button {
    width: 100%;
    margin: 0;
    margin-top: .8rem;
}

.button button.export {
    background: $export-color;
    border-color: $export-color
}

.button button.import {
    background: $import-color;
    border-color: $import-color
}

.warm-tip {
    width: 100%;
    text-align: center;
    margin-top: 1rem;
}

.warm-tip h5 {
    font-size: 1.4rem;
}

.warm-tip p {
    font-size: 1.3rem;
    color: #666;
    margin-top: .4rem;
    padding: 0 1rem;
}

.warm-tip a {
    font-size: 1.2rem;
}
</style>