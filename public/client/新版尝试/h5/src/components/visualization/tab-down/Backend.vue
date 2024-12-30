<template>
    <div class='b-tab-down'>
        <component-con :modules-index="modulesIndex" :component-name="componentName" :setting="setting">
            <div class="form-group">
                <label class="group-l">{{$t('lang.label_hint')}}</label>
                <div class="group-r">
                    <p class="ec-remark">{{$t('lang.tab_down_prompt_1')}}</p>
                    <!-- <p class="ec-remark">{{$t('lang.tab_down_prompt_2')}}</p> -->
                </div>
            </div>
            <!-- <div class="form-group">
                <label class="group-l">{{$t('lang.label_img_style')}}</label>
                <div class="group-r consult">
                    <img src="@/assets/img/tab-down.png" alt="">
                </div>
            </div>
            <img-ipt-url v-for="(item,index) in data.list" :key="item.key" :info="item" :modules-index="modulesIndex" :list-index="index" :b-edit-img="true" @setInfoValue="updateText">
                <span slot="edit-img-close" class="link-close" @click="removeList(index)">
                    <i class="iconfont icon-close"></i>
                </span>
                <span slot="link-name-close" class="link-name-close" @click="close()">
                    <i class="iconfont icon-close"></i>
                </span>
            </img-ipt-url>
            <add-btn :add="add" @click.native="addList('imgList')" v-show="oAddBtn"></add-btn> -->
        </component-con>
    </div>
</template>

<script>
// custom components
import ComponentCon from '../element/ComponentCon'
import AddBtn from '../element/AddBtn'
import ImgIptUrl from '../element/ImgIptUrl'

// third party components


// minxin
import formProcessing from '@/mixins/form-processing'

// localData
import localData from './data/local'

export default {
    name: 'b-tab-down',
    props: ['setting', 'onlineData', 'modulesIndex'],
    mixins: [formProcessing],
    data() {
        return {
            componentName: localData.componentName,
            add: {
                title: this.$t('lang.add_one_nav')
            },
            maxLength: 5
        }
    },
    components: {
        ComponentCon,
        AddBtn,
        ImgIptUrl
    },
    created() {

    },
    computed: {
        oAddBtn() {
            return this.maxLength <= this.data.list.length ? false : true
        },
        data() {
            return Object.assign({}, localData.data, this.onlineData)
        }
    }
}

</script>

<style lang="scss" scoped>
.consult{
    img{
        width:2.6rem;
        height:auto
    }
}
</style>