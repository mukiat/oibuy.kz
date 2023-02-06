<template>
    <component-con :modules-index="modulesIndex" :component-name="componentName" :setting="setting">
        <div class="form-group">
            <label class="group-l">{{$t('lang.label_headline')}}</label>
            <div class="group-r">
                <ec-input type="text" size="small" v-model="searchValue"></ec-input>
            </div>
        </div>
        <!-- <div class="form-group" v-if="!bStore">
            <label class="group-l">{{$t('lang.label_location')}}</label>
            <radio-group v-model="positionSel" size="small">
                <radio v-for="item in data.positionSel" :key="item.key" :label="item.key">{{ item.title }}</radio>
            </radio-group>
        </div> -->
        <!--<div class="form-group" v-show="positionSel == '0'" v-if="!bStore">
            <label class="group-l">
                Key：
            </label>
            <div class="group-r">
                <ec-input type="text" size="small" v-model="tenKey" placeholder="Карта key-ін енгізіңіз,бос қатырылса бастапқы мәнді білдіреді"></ec-input>
            </div>
        </div>-->
        <div class="form-group" v-if="!bStore">
            <label class="group-l">
                LOGO：
            </label>
            <radio-group v-model="logoSel" size="small">
                <radio v-for="item in data.logoSel" :key="item.key" :label="item.key">{{ item.title }}</radio>
            </radio-group>
        </div>
        <div class="form-group" v-show="logoSel == '0'">
            <label class="group-l">
                    ICON：
                </label>
            <div class="group-r">
                <edit-img :image="onlineData.allValue.img" :modules-index="modulesIndex" all-values-type="img"></edit-img>
                <p class="ec-remark">{{$t('lang.announcement_prompt_1')}}</p>
            </div>
        </div>
        <div class="form-group" v-if="!bStore">
            <label class="group-l">
                {{$t('lang.label_message')}}
            </label>
            <radio-group v-model="messageSel" size="small">
                <radio v-for="item in data.messageSel" :key="item.key" :label="item.key">{{ item.title }}</radio>
            </radio-group>
        </div>
        <div class="form-group">
            <label class="group-l">
                {{$t('lang.label_suspension')}}
            </label>
            <radio-group v-model="suspendSel" size="small">
                <radio v-for="item in data.suspendSel" :key="item.key" :label="item.key">{{ item.title }}</radio>
            </radio-group>
        </div>
        <div class="form-group">
            <label class="group-l">
                {{$t('lang.label_fontColor')}}
            </label>
            <div class="group-r">
                <color-input v-model="fontColor" :placeholder="$t('lang.fontColor_placeholder')"></color-input>
                <p class="ec-remark">{{$t('lang.search_prompt_1')}}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="group-l">
                {{$t('lang.label_bgColor')}}
            </label>
            <div class="group-r">
                <color-input v-model="bgColor" :placeholder="$t('lang.bgColor2_placeholder')"></color-input>
            </div>
        </div>
         <div class="form-group">
            <label class="group-l"></label>
            <div class="group-r">
                 <p class="ec-remark">{{$t('lang.search_prompt_2')}}</p>
            </div>
        </div>
    </component-con>
</template>

<script>
// custom components
import ComponentCon from '../element/ComponentCon'
import ColorInput from '../element/ColorInput'
import EditImg from '../element/EditImg'

// third party components
import {
    Radio,
    RadioGroup,
    Input
} from 'element-ui'

// minxin
import formProcessing from '@/mixins/form-processing'

// localData
import localData from './data/local'

export default {
    name: 'b-search',
    props: ['setting', 'onlineData', 'modulesIndex'],
    mixins: [formProcessing],
    data() {
        return {
            componentName: localData.componentName
        }
    },
    components: {
        Radio,
        RadioGroup,
        ComponentCon,
        "EcInput": Input,
        ColorInput,
        EditImg
    },
    beforeMount() {
    },
    computed: {
        bStore(){
            return window.shopInfo.ruid != 0
        },
        tenKey: {
            get() {
                return this.data.allValue.tenKey
            },
            set(value) {
                this.updateTitleText('tenKey', value)
            }
        },
        searchValue: {
            get() {
                return this.data.allValue.searchValue
            },
            set(value) {
                this.updateTitleText('searchValue', value)
            }
        },
        fontColor:{
            get(){
                return this.data.allValue.fontColor
            },
            set(value){
                this.updateTitleText('fontColor', value)
            }
        },
        bgColor: {
            get() {
                return this.data.allValue.bgColor
            },
            set(value) {
                this.updateTitleText('bgColor', value)
            }
        },
        positionSel: {
            get() {
                return this.data.isPositionSel
            },
            set(value) {
                this.updateRadioSel('isPositionSel', value)
            }
        },
        logoSel:{
            get() {
                return this.data.isLogoSel
            },
            set(value) {
                this.updateRadioSel('isLogoSel', value)
            }
        },
        messageSel: {
            get() {
                return this.data.isMessageSel
            },
            set(value) {
                this.updateRadioSel('isMessageSel', value)
            }
        },
        suspendSel: {
            get() {
                return this.data.isSuspendSel
            },
            set(value) {
                this.updateRadioSel('isSuspendSel', value)
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