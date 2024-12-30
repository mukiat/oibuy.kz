<template>
    <div class="b-slide">
        <component-con :modules-index="modulesIndex" :component-name="componentName" :setting="setting">
            <div class="form-group">
                <label class="group-l">
                        {{$t('lang.label_display_usage')}}
                    </label>
                <radio-group v-model="styleSel" size="small">
                    <radio v-for="item in data.showStyle" :key="item.key" :label="item.key">{{ item.title }}</radio>
                </radio-group>
            </div>
            <template v-if="styleSel != 2">
            <div class="form-group">
                <label class="group-l">{{$t('lang.label_display_size')}}</label>
                <radio-group v-model="sizeSel" size="small">
                    <radio v-for="item in picSize" :key="item.key" :label="item.key">{{ item.title }}</radio>
                </radio-group>
            </div>
            <div class="form-group">
                <label class="group-l"></label>
                <div>
                    <label class="control-label pic-tips" style="color:#ec5151;font-size:12px;text-align:left;">
                        {{$t('lang.nav_prompt_1')}}<template v-if="styleSel == 1">{{ 640 / (Number(sizeSel) + 1) }}</template><template v-else>640</template>{{$t('lang.nav_prompt_2')}}
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="group-l"></label>
                <div>
                    <label class="control-label pic-tips" style="color:#ec5151;font-size:12px;text-align:left;">
                        {{$t('lang.live_prompt')}}
                    </label>
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
            <add-btn :add="add" @click.native="addList('imgList')" v-show="oAddBtn"></add-btn>
            </template>
            <template v-else>
                <div class="form-group">
                    <label class="group-l">Сипаттау：</label>
                    <div class="group-r">
                        <ec-input size="small" v-model="spikeDesc" placeholder="Блогер ұсынысы"></ec-input>
                    </div>
                </div>
            </template>
        </component-con>
    </div>
</template>

<script>
    // mapActions mapState
    import { mapActions, mapState } from 'vuex'
    // custom components
    import ComponentCon from '../element/ComponentCon'
    import AddBtn from '../element/AddBtn'
    import ImgIptUrl from '../element/ImgIptUrl'

    // third party components
    import {
        Radio,
        RadioGroup,
        Checkbox,
        Input
    } from 'element-ui'

    // minxin
    import formProcessing from '@/mixins/form-processing'

    // localData
    import localData from './data/local'
    export default {
        name: 'b-live',
        props: ['setting', 'onlineData', 'modulesIndex'],
        mixins: [formProcessing],
        data() {
            return {
                add: {
                    title: this.$t('lang.add_adv_position')
                },
                componentName: localData.componentName,
                maxLength:10
            }
        },
        components: {
            Radio,
            RadioGroup,
            Checkbox,
            ImgIptUrl,
            ComponentCon,
            AddBtn,
            EcInput: Input
        },
        beforeMount() {
            
        },
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
            picSize() {//动态显示 显示大小 属性
                let picSize = [];
                if(this.styleSel != 2){                
                    let sizeKey = this.data.showStyle[this.styleSel].picSizeKey;
                    sizeKey.map((v) => {
                        let dPicSize = this.data.picSize //data picsize
                        dPicSize.map((dV) => {
                            if (dV.key === v) {
                                picSize.push(dV)
                            }
                        })
                    })
                }
                return picSize
            },
            oAddBtn(){
                return this.maxLength <= this.data.list.length ? false : true
            },
            styleSel: {
                get() {
                    return this.data.isStyleSel;
                },
                set(value) {
                    console.log(value)
                    this.updateRadioSel('isStyleSel', value)
                }
            },
            sizeSel: {
                get() {
                    return this.data.isSizeSel;
                },
                set(value) {
                    this.updateRadioSel('isSizeSel', value)
                }
            },
            data() {
                return Object.assign({}, localData.data, this.onlineData)
            }
        }
    }

</script>