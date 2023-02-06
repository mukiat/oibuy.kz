<template>
    <div class='b-category-nav'>
        <component-con :modules-index="modulesIndex" :component-name="componentName" :setting="setting">
            <div class="form-group">
                <label class="group-l">{{$t('lang.label_display_usage')}}</label>
                <radio-group v-model="styleSel" size="small">
                    <radio v-for="item in data.showStyle" :key="item.key" :label="item.key">{{ item.title }}</radio>
                </radio-group>
            </div>
            <div class="form-group">
                <label class="group-l">背景图片：</label>
                <div class="group-r">
                    <edit-img :image="data.allValue.titleImg" :modules-index="modulesIndex" all-values-type="titleImg"></edit-img>
                </div>
            </div>
            <div class="form-group">
                <label class="group-l">图片链接：</label>
                <div class="group-r">
                    <ec-input size="small" v-model="sCustomUrl" placeholder="填写背景图片跳转链接"></ec-input>
                </div>
            </div>
            <div class="form-group" v-if="device == 'app'">
                <label class="group-l">{{$t('lang.label_app_url')}}</label>
                <div class="group-r">
                    <ec-input size="small" v-model="sCustomUrlApp" :placeholder="$t('lang.add_url')"></ec-input>
                </div>
            </div>
            <div class="form-group" v-if="device == 'wxapp'">
                <label class="group-l">{{$t('lang.label_applet_url')}}</label>
                <div class="group-r">
                    <ec-input size="small" v-model="sCustomUrlAppLet" :placeholder="$t('lang.add_url')"></ec-input>
                </div>
            </div>
            <div class="form-group" v-if="styleSel == 1">
                <label class="group-l">
                    {{$t('lang.label_bgColor')}}
                </label>
                <div class="group-r">
                    <color-input v-model="bgColor" :placeholder="$t('lang.bgColor2_placeholder')"></color-input>
                </div>
            </div>
            <div class="form-group">
                <label class="group-l" style="">{{$t('lang.label_select_goods')}}</label>
                <div class="group-r">
                    {{$t('lang.select')}} {{ selGoodNum }} {{$t('lang.a_commodity')}} 
                    <a href="javascript:;" @click="openDialogGoods()"> {{$t('lang.click_select')}}</a> 
                    <a href="javascript:;" class="clear" @click="clearSelGoods({
                        modulesIndex
                    })"> [ {{$t('lang.empty')}} ]</a>
                </div>
            </div>
            <div class="form-group">
                <label class="group-l">{{$t('lang.label_goods_number')}}</label>
                <div class="group-r">
                    <ec-input type="number" size="small" v-model="number" :placeholder="$t('lang.goods_number_placeholder')"></ec-input>
                </div>
            </div>

        </component-con>
    </div>
</template>

<script>
// mapActions mapState
import { mapActions, mapState } from 'vuex'

// custom components
import ComponentCon from '../element/ComponentCon'
import ColorInput from '../element/ColorInput'
import EditImg from '../element/EditImg'

// third party components
import { Radio, RadioGroup, Input } from 'element-ui'

// minxin
import formProcessing from '@/mixins/form-processing'

// localData
import localData from './data/local'

export default {
    name: 'b-visual-adv',
    props: ['setting', 'onlineData', 'modulesIndex'],
    mixins: [formProcessing],
    data() {
        return {
            componentName: localData.componentName,
            device: window.shopInfo.device // device 设备  h5 app wxapp
        }
    },
    components: {
        Radio,
        RadioGroup,
        ComponentCon,
        ColorInput,
        EditImg,
        EcInput: Input
    },
    created() {
        
    },
    beforeMount() {},
    methods: {
        ...mapActions('dialogGoods', [
            'onOffDialogGoods',
            'setDialogGoods',
            'clearSelGoods'
        ]),
        openDialogGoods() {
            this.setDialogGoods({
                bShowDialog: true,
                currentPage: 1,
                modulesIndex: this.modulesIndex,
                pageSize: 15,
                ru_id:window.shopInfo.ruid
            })
        }
    },
    computed: {
        styleSel: {
            get() {
                return this.data.isStyleSel;
            },
            set(value) {
                this.updateRadioSel('isStyleSel', value)
            }
        },
        number: {
            get() {
                return this.data.allValue.number < 1
                    ? 1
                    : this.data.allValue.number
            },
            set(value) {
                if (value < 1) {
                    value = 1
                }
                this.updateTitleText('number', Number(value))
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
        sCustomUrl:{
            get() {
                return this.data.allValue.url
            },
            set(value) {
                this.updateTitleText('url', value)
            }
        },
        sCustomUrlApp:{
            get() {
                return this.data.allValue.appPage
            },
            set(value) {
                this.updateTitleText('appPage', value)
            }
        },
        sCustomUrlAppLet:{
            get() {
                return this.data.allValue.appletPage
            },
            set(value) {
                this.updateTitleText('appletPage', value)
            }
        },
        selGoodNum() {
            return this.selectGoodsId ? this.selectGoodsId.length : 0
        },
        selectGoodsId() {
            return this.data.allValue.selectGoodsId
        },
        data() {
            return Object.assign({}, localData.data, this.onlineData)
        }
    }
}
</script>

<style scoped>

</style>