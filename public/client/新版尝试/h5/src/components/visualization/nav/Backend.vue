<template>
    <div class="b-nav">
        <component-con :modules-index="modulesIndex" :component-name="componentName" :setting="setting">
            <div class="form-group">
                <label class="group-l">{{$t('lang.label_display_usage')}}</label>
                <radio-group v-model="styleSel" size="small">
                    <radio v-for="item in data.showStyle" :key="item.key" :label="item.key">{{ item.title }}</radio>
                </radio-group>
            </div>
            <div class="form-group" v-if="styleSel != '2'">
                <label class="group-l">{{$t('lang.label_icon')}}</label>
                <radio-group v-model="iconSel" size="small">
                    <radio v-for="item in data.showIcon" :key="item.key" :label="item.key">{{ item.title }}</radio>
                </radio-group>
            </div>
            <template v-if="styleSel == '1' || styleSel == '2'">
                <div class="form-group" v-if="styleSel == '1'">
                    <label class="group-l">{{$t('lang.label_style')}}</label>
                    <checkbox-group v-model="styleSelList" size="small">
                        <checkbox v-for="item in data.showStyleList" :key="item.key" :label="item.key">{{ item.title }}</checkbox>
                    </checkbox-group>
                </div>
                <div class="form-group">
                    <label class="group-l">{{$t('lang.label_columns')}}</label>
                    <radio-group v-model="numberSel" size="small">
                        <radio v-for="item in data.showNumber" :key="item.key" :label="item.key">{{ item.title }}</radio>
                    </radio-group>
                </div>
                <div class="form-group" v-if="styleSel == '1'">
                    <label class="group-l">{{$t('lang.label_describe')}}</label>
                    <radio-group v-model="descSel" size="small">
                        <radio v-for="item in data.showDesc" :key="item.key" :label="item.key">{{ item.title }}</radio>
                    </radio-group>
                </div>
            </template>
            <div class="form-group">
                <label class="group-l"></label>
                <div>
                    <label class="control-label pic-tips" style="color:#ec5151;font-size:12px;text-align:left;">
                       {{$t('lang.nav_prompt_1')}}<span v-show="styleSel == 1 || styleSel == 2">{{ 360 / numberSel }}</span><span v-show="styleSel == 0">72</span>{{$t('lang.nav_prompt_2')}}
                    </label>
                </div>
            </div>
            <template v-if="bNav">
                <img-ipt-url v-for="(item,index) in data.list" :key="index" :info="item" :modules-index="modulesIndex" :list-index="index" :b-edit-img="bEditImg" @setInfoValue="updateText">
                    <span slot="edit-img-close" class="link-close" @click="removeList(index)">
                        <i class="iconfont icon-close"></i>
                    </span>
                </img-ipt-url>
            </template>
            <add-btn :add="add" @click.native="addList('imgList')" v-show="oAddBtn"></add-btn>
        </component-con>
    </div>
</template>

<script>
// mapActions mapState
import { mapActions, mapState } from 'vuex'
// custom components
import ComponentCon from '../element/ComponentCon'
import ImgIptUrl from '../element/ImgIptUrl'
import AddBtn from '../element/AddBtn'

// third party components
import {
    Radio,
    RadioGroup,
    CheckboxGroup,
    Checkbox
} from 'element-ui'

// minxin
import formProcessing from '@/mixins/form-processing'

// localData
import localData from './data/local'
export default {
    name: 'b-nav',
    props: ['setting', 'onlineData', 'modulesIndex','show'],
    mixins: [formProcessing],
    data() {
        return {
            componentName: localData.componentName,
            add: {
                title: this.$t('lang.add_one_nav')
            },
            maxLength: 30
        }
    },
    components: {
        Radio,
        RadioGroup,
        Checkbox,
        CheckboxGroup,
        ComponentCon,
        ImgIptUrl,
        AddBtn
    },
    beforeMount() {
    },
    methods: {

    },
    computed: {
        bNav() {
            return this.data.list.length > 0 ? true : false
        },
        bEditImg() {
            return this.iconSel == "0" ? true : false
        },
        oAddBtn() {
            return this.maxLength == this.data.list.length ? false : true
        },
        numberSel: {
            get() {
                return this.data.isNumberSel;
            },
            set(value) {
                this.updateRadioSel('isNumberSel', value)
            }
        },
        descSel: {
            get() {
                return this.data.isDescSel;
            },
            set(value) {
                this.updateRadioSel('isDescSel', value)
            }
        },
        styleSel: {
            get() {
                return this.data.isStyleSel;
            },
            set(value) {
                this.updateRadioSel('isStyleSel', value)
            }
        },
        styleSelList: {
            get() {
                return this.data.styleSelList;
            },
            set(value) {
                this.updateRadioSel('styleSelList', value)
            }
        },
        iconSel: {
            get() {
                return this.data.isIconSel;
            },
            set(value) {
                this.updateRadioSel('isIconSel', value)
            }
        },
        data() {
            return Object.assign({}, localData.data, this.onlineData)
        }
    }
}

</script>