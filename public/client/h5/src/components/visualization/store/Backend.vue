<template>
    <div class='b-store'>
        <component-con :modules-index="modulesIndex" :component-name="componentName" :setting="setting">
            <div class="form-group">
                <label class="group-l">显示样式</label>
                <radio-group v-model="styleSel" size="small">
                    <radio v-for="item in data.showStyle" :key="item.key" :label="item.key">{{ item.title }}</radio>
                </radio-group>
            </div>
            <div class="form-group">
                <label class="group-l">
                    {{$t('lang.label_display_number')}}
                </label>
                <div class="group-r">
                    <ec-input type="number" size="small" v-model="number" :placeholder="$t('lang.store_number_placeholder')"></ec-input>
                    <p class="ec-remark">{{$t('lang.store_prompt_1')}}</p>
                </div>
            </div>
            <template v-if="styleSel == 1">
                <div class="form-group">
                    <label class="group-l">店铺描述：</label>
                    <div class="group-r">
                        <ec-input size="small" v-model="spikeDesc" placeholder="更多品质好店"></ec-input>
                    </div>
                </div>
                <img-ipt-url v-for="(item,index) in data.list" :key="index" :info="item" :modules-index="modulesIndex" :list-index="index" :b-edit-img="true" @setInfoValue="updateText">
                    <span slot="edit-img-close" class="link-close" @click="removeList(index)"><i class="iconfont icon-close"></i></span>
                    <span slot="link-name-close" class="link-name-close" @click="close()"><i class="iconfont icon-close"></i></span>
                </img-ipt-url>
                <add-btn :add="add" @click.native="addList('imgList')" v-show="oAddBtn"></add-btn>
            </template>
        </component-con>
    </div>
</template>

<script>
// custom components
import ComponentCon from '../element/ComponentCon'
import AddBtn from '../element/AddBtn'
import ImgIptUrl from '../element/ImgIptUrl'

// third party components
import {
    CheckboxGroup,
    Checkbox,
    Radio,
    RadioGroup,
    Input,
    DatePicker
} from 'element-ui'

// minxin
import formProcessing from '@/mixins/form-processing'

// localData
import localData from './data/local'

export default {
    name: 'b-store',
    props: ['setting', 'onlineData', 'modulesIndex'],
    mixins: [formProcessing],
    data() {
        return {
            componentName: localData.componentName,
            add: {
                title: this.$t('lang.add_adv_position'),
            },
            maxLength: 6
        }
    },
    components: {
        Radio,
        Checkbox,
        CheckboxGroup,
        RadioGroup,
        ComponentCon,
        AddBtn,
        ImgIptUrl,
        EcInput: Input,
        DatePicker,
    },
    mounted() {

    },
    computed: {
        oAddBtn() {
            return this.maxLength <= this.data.list.length ? false : true
        },
        spikeDesc: {
            get() {
                return this.data.allValue.spikeDesc
            },
            set(value) {
                this.updateTitleText('spikeDesc', value)
            }
        },
        styleSel: {
            get() {
                return this.data.isStyleSel
            },
            set(value) {
                this.updateRadioSel('isStyleSel', value)
            }
        },
        number: {
            get() {
                return this.data.allValue.number < 1 ? 1 : this.data.allValue.number
            },
            set(value) {
                if (value < 1) {
                    value = 1
                }
                this.updateTitleText('number', Number(value))
            }
        },
        data() {
            return Object.assign({}, localData.data, this.onlineData)
        }
    }
}

</script>

<style scoped>

</style>