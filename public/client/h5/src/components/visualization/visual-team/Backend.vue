<template>
    <div class='b-category-nav'>
        <component-con :modules-index="modulesIndex" :component-name="componentName" :setting="setting">
            <div class="form-group">
                <label class="group-l">拼团描述：</label>
                <div class="group-r">
                    <ec-input size="small" v-model="spikeDesc" placeholder="众多精美商品等你拼团"></ec-input>
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
import ColorInput from '../element/ColorInput'

// third party components
import { Radio, RadioGroup, Input } from 'element-ui'

// minxin
import formProcessing from '@/mixins/form-processing'

// localData
import localData from './data/local'

export default {
    name: 'b-visual-team',
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

    },
    beforeMount() {},
    methods: {

    },
    computed: {
        spikeDesc: {
            get() {
                return this.data.allValue.spikeDesc
            },
            set(value) {
                this.updateTitleText('spikeDesc', value)
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
        data() {
            return Object.assign({}, localData.data, this.onlineData)
        }
    }
}
</script>

<style scoped>

</style>