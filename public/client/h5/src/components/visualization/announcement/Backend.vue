<template>
    <div class='b-announcement'>
        <component-con :modules-index="modulesIndex" :component-name="componentName" :setting="setting">
            <div class="form-group" v-if="bStyleSel">
                <label class="group-l">{{$t('lang.label_display_usage')}}</label>
                <radio-group v-model="styleSel" size="small">
                    <radio v-for="item in data.showStyle" :key="item.key" :label="item.key">{{ item.title }}</radio>
                </radio-group>
            </div>
            <div class="form-group">
                <label class="group-l">ICON：</label>
                <div class="group-r">
                    <edit-img :image="onlineData.allValue.img" :modules-index="modulesIndex" all-values-type="img"></edit-img>
                    <p class="ec-remark">{{$t('lang.announcement_prompt_1')}}</p>
                </div>
            </div>
            <div class="form-group" v-show="bNews">
                <label class="group-l">{{$t('lang.label_article_categary')}}</label>
                <div class="group-r">
                    <cascader 
                        class="cascader"
                        :options="optionCascader || []"
                        v-model="optionCascaderVal"
                        clearable
                        filterable
                        change-on-select
                        size="small"
                        :props="optionCProps"
                        :placeholder="$t('lang.article_categary_placeholder')"
                        :style="{
                            width: '100%'
                        }">
                    </cascader>
                </div>
            </div>
            <div class="form-group" v-show="bNews">
                <label class="group-l">{{$t('lang.label_display_number')}}</label>
                <div class="group-r">
                    <ec-input 
                        type="number" 
                        size="small"
                        min="1"
                        v-model="number"
                        :placeholder="$t('lang.display_number_placeholder')">
                    </ec-input>
                </div>
            </div>
            <div class="form-group" v-show="bNews">
                <label class="group-l">{{$t('lang.label_display_date')}}</label>
                <radio-group v-model="dateSel" size="small">
                    <radio v-for="item in data.showDate" :key="item.key" :label="item.key">{{ item.title }}</radio>
                </radio-group>
            </div>
            <div class="form-group" v-show="!bNews">
                <label class="group-l">{{$t('lang.label_announcement_content')}}</label>
                 <div class="group-r">
                    <ec-input type="textarea" :autosize="{ minRows: 2, maxRows: 6 }" v-model="announContent"></ec-input>
                    <p class="ec-remark">{{$t('lang.announcement_prompt_2')}}</p>
                </div>
            </div>
            <div class="form-group">
                <label class="group-l">显示样式：</label>
                <radio-group v-model="newStyleSel" size="small">
                    <radio v-for="item in data.showNewStyle" :key="item.key" :label="item.key">{{ item.title }}</radio>
                </radio-group>
            </div>
        </component-con>
    </div>
</template>

<script>
// custom components
import ComponentCon from '../element/ComponentCon'
import EditImg from '../element/EditImg'

// third party components
import { Radio, RadioGroup, Input, Cascader } from 'element-ui'

// minxin
import formProcessing from '@/mixins/form-processing'

// localData
import localData from './data/local'

export default {
    name: 'b-announcement',
    props: ['setting', 'onlineData', 'modulesIndex'],
    mixins: [formProcessing],
    data() {
        return {
            componentName: localData.componentName,
            optionCascader: [],
            optionCProps: {
                value: 'cat_id',
                label: 'cat_name',
                children: 'tree'
            }
        }
    },
    components: {
        Radio,
        RadioGroup,
        ComponentCon,
        EditImg,
        EcInput: Input,
        Cascader
    },
    created() {
        this.init()
    },
    beforeMount() {},
    methods: {
        init() {
            if (window.sessionStorage) {
                if (
                    localStorage.getItem('announcementCOption') &&
                    localStorage.getItem('announcementCOption') !==
                        'undefined'
                ) {
                    this.optionCascader = JSON.parse(
                        localStorage.getItem('announcementCOption')
                    )
                } else {
                    this.resAnnouncementCOption().then(() => {
                        localStorage.setItem(
                            'announcementCOption',
                            JSON.stringify(this.optionCascader)
                        )
                    })
                }
            }
        },
        resAnnouncementCOption() {
            return this.$http
                .get(`/${window.apiAuthority}/touch_visual/article_list`)
                .then(({ data }) => {
                    this.optionCascader = data.list
                }).catch(err=>{
                    console.log(err)
                })
        }
    },
    computed: {
        bStyleSel() {
            return window.shopInfo.ruid == 0
        },
        bNews() {
            return this.styleSel == '0' ? true : false
        },
        announContent: {
            get() {
                return this.data.allValue.announContent
            },
            set(value) {
                this.updateTitleText('announContent', value)
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
        optionCascaderVal: {
            get() {
                let s = this.data.allValue.optionCascaderVal.split(',')
                let arr = []

                //把分割数组字符串类型转换为int整型
                s.forEach((data)=>{
                    arr.push(+data)
                })

                return arr
            },
            set(value) {
                this.updateTitleText('optionCascaderVal', value.join(','))
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
        dateSel: {
            get() {
                return this.data.isDateSel
            },
            set(value) {
                this.updateRadioSel('isDateSel', value)
            }
        },
        newStyleSel: {
            get() {
                return this.data.isNewStyleSel
            },
            set(value) {
                this.updateRadioSel('isNewStyleSel', value)
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