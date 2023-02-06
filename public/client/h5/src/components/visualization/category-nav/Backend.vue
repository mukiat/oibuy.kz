<template>
    <div class='b-category-nav'>
        <component-con :modules-index="modulesIndex" :component-name="componentName" :setting="setting">
           <div class="form-group">
                <label class="group-l">是否滚动：</label>
                <radio-group v-model="styleSel" size="small">
                    <radio v-for="item in data.showStyle" :key="item.key" :label="item.key">{{ item.title }}</radio>
                </radio-group>
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
                <label class="group-l">{{$t('lang.label_display_number')}}</label>
                <div class="group-r">
                    <ec-input 
                        type="number" 
                        size="small"
                        min="1"
                        v-model="number"
                        placeholder="默认显示10条分类">
                    </ec-input>
                </div>
            </div>
        </component-con>
    </div>
</template>

<script>
// custom components
import ComponentCon from '../element/ComponentCon'
import ColorInput from '../element/ColorInput'

// third party components
import { Radio, RadioGroup, Input } from 'element-ui'

// minxin
import formProcessing from '@/mixins/form-processing'

// localData
import localData from './data/local'

export default {
    name: 'b-category-nav',
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
        ColorInput,
        EcInput: Input
    },
    created() {
        this.init()
    },
    beforeMount() {},
    methods: {
        init() {
            
        }
    },
    computed: {
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
        styleSel: {
            get() {
                return window.shopInfo.ruid == 0 ? this.data.isStyleSel : 1
            },
            set(value) {
                this.updateRadioSel('isStyleSel', value)
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