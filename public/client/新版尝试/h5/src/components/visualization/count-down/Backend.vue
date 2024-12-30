<template>
    <div class='b-count-down'>
        <component-con :modules-index="modulesIndex" :component-name="componentName" :setting="setting">
            <div class="form-group">
                <label class="group-l">{{$t('lang.label_style')}}</label>
                <radio-group v-model="styleSel" size="small">
                    <radio v-for="item in data.showStyle" :key="item.key" :label="item.key">{{ item.title }}</radio>
                </radio-group>
            </div>
            <div class="form-group">
                <label class="group-l">{{$t('lang.label_headline_img')}}</label>
                <div class="group-r">
                    <edit-img :image="data.allValue.titleImg" :modules-index="modulesIndex" all-values-type="titleImg"></edit-img>
                </div>
            </div>
            <div class="form-group" v-show="styleSel == '1'">
                <label class="group-l">{{$t('lang.label_maximage_img')}}</label>
                <div class="group-r">
                    <edit-img :image="data.allValue.productImg" :modules-index="modulesIndex" all-values-type="productImg"></edit-img>
                    <p class="ec-remark">{{$t('lang.announcement_prompt_2')}}</p>
                </div>
            </div>
            <div class="form-group">
                <label class="group-l">{{$t('lang.label_seckill_desc')}}</label>
                <div class="group-r">
                    <ec-input size="small" v-model="spikeDesc" :placeholder="$t('lang.seckill_desc_placeholder')"></ec-input>
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
    // custom components
    import ComponentCon from '../element/ComponentCon'
    import EditImg from '../element/EditImg'

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
        name: 'b-count-down',
        props: ['setting', 'onlineData', 'modulesIndex'],
        mixins: [formProcessing],
        data() {
            return {
                componentName: localData.componentName
            }
        },
        components: {
            Radio,
            Checkbox,
            CheckboxGroup,
            RadioGroup,
            ComponentCon,
            "EcInput": Input,
            EditImg,
            DatePicker
        },
        created() {
            
        },
        computed: {
            number:{
                get() {
                    return this.data.allValue.number
                },
                set(value) {
                    this.updateTitleText('number', Number(value))
                }
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
            endTime: {
                get() {
                    return this.data.allValue.endTime
                },
                set(value) {
                    this.updateTitleText('endTime', value)
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