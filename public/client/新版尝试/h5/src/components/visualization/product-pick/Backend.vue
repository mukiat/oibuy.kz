<template>
    <div class='b-product'>
        <component-con
            :modules-index="modulesIndex"
            :component-name="componentName"
            :setting="setting">
            <div class="form-group">
                <label class="group-l">{{$t('lang.label_hint')}}</label>
                <div class="group-r">
                    <p class="ec-remark">{{$t('lang.tab_down_prompt_1')}}</p>
                    <!-- <p class="ec-remark">{{$t('lang.tab_down_prompt_2')}}</p> -->
                </div>
            </div>
            <!-- <div class="form-group">
                <label class="group-l">{{$t('lang.label_manner')}}</label>
                <radio-group
                    v-model="styleSel"
                    size="small">
                    <radio
                        v-for="item in data.showStyle"
                        :key="item.key"
                        :label="item.key">
                            {{ item.title }}
                    </radio>
                </radio-group>
            </div>
             <div class="form-group">
                <label class="group-l">Таңба：</label>
                <checkbox-group v-model="tabSelList" size="small">
                    <checkbox 
                        v-for="item in aShowtabs" 
                        :key="item.key" 
                        :label="item.key">
                            {{ item.title }}
                    </checkbox>
                </checkbox-group>
            </div>
           <div class="form-group" v-for="(item,index) in tabInputList" :key="index">
                <label class="group-l">tab{{ index }}：</label>
                <div class="group-r">
                    <div style="margin-bottom: 10px;">
                        <ec-input 
                            type="text" 
                            size="small"
                            v-model="item.text"
                            placeholder="Тақырып"
                            @input="tabsInput($event,index,'text')">
                        </ec-input>
                    </div>
                    <div class="">
                        <ec-input 
                            type="text" 
                            size="small"
                            v-model="item.desc"
                            placeholder="Сипат"
                             @input="tabsInput($event,index,'desc')">
                        </ec-input>
                    </div>
                </div>
            </div> 
            <div class="form-group">
                <label class="group-l">{{$t('lang.label_categary')}}</label>
                <div class="group-r">
                    <cascader 
                        class="cascader"
                        :options="categoryCOption || []"
                        v-model="categorySOption"
                        clearable
                        filterable
                        change-on-select
                        :props="categoryCProps"
                        size="small"
                        :placeholder="$t('lang.article_categary_placeholder')">
                    </cascader>
                </div>
            </div>
            <div class="form-group">
                <label class="group-l">{{$t('lang.label_brand')}}</label>
                <div class="group-r">
                    <ec-select 
                        class="brand-select"
                        size="small" 
                        v-model="brandSelect" 
                        clearable
                        filterable
                        :placeholder="$t('lang.article_categary_placeholder')">
                        <ec-option 
                            v-for="item in brandList" 
                            :key="item.brand_id"
                            :label="item.brand_name"
                            :value="item.brand_id">
                        </ec-option>
                    </ec-select>
                </div>
            </div>
             <div class="form-group">
                <label class="group-l">{{$t('lang.label_module')}}</label>
                <radio-group v-model="moduleSel" size="small">
                    <radio
                        v-for="item in data.showModule" 
                        :key="item.key" 
                        :label="item.key">
                            {{ item.title }}
                    </radio>
                </radio-group>
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
            <div class="form-group form-group--remak">
                <label class="group-l" style="">
                </label>
                <div class="group-r">
                    <p class="ec-remark">{{$t('lang.product_prompt_2')}}</p>
                </div>
            </div>
            <div class="form-group">
                <label class="group-l">{{$t('lang.label_goods_number')}}</label>
                <div class="group-r">
                    <ec-input 
                        type="number" 
                        size="small"
                        min="1"
                        v-model="number"
                        :placeholder="$t('lang.goods_number_placeholder')">
                    </ec-input>
                </div>
            </div>
            <div class="form-group form-group--remak">
                <label class="group-l" style="">
                </label>
                <div class="group-r">
                    <p class="ec-remark">{{$t('lang.product_prompt_3')}}</p>
                </div>
            </div>-->
        </component-con>
    </div>
</template>

<script>
// mapActions mapState
import { mapActions, mapState } from 'vuex'

//qs
import qs from 'qs'

// custom components
import ComponentCon from '../element/ComponentCon'

// third party components
import {
    CheckboxGroup,
    Checkbox,
    Radio,
    RadioGroup,
    Input,
    DatePicker,
    Cascader,
    Select,
    Option
} from 'element-ui'

// minxin
import formProcessing from '@/mixins/form-processing'

// localData
import localData from './data/local'

export default {
    name: 'b-product-pick',
    props: ['setting', 'onlineData', 'modulesIndex'],
    mixins: [formProcessing],
    data() {
        return {
            componentName: localData.componentName,
            showDialogGoods: false,
            /**
             * 商品分类列表
             * 
             * @type {Array}
             */
            categoryCOption: [],
            categoryCProps: {
                value: 'cat_id',
                label: 'cat_name',
                children: 'child_tree'
            },
            /**
             * 品牌分类列表
             * 
             * @type {Array}
             */
            brandList: [],
            text:'',
            desc:''
        }
    },
    components: {
        Radio,
        Checkbox,
        CheckboxGroup,
        RadioGroup,
        ComponentCon,
        EcInput: Input,
        EcSelect: Select,
        EcOption: Option,
        DatePicker,
        Cascader
    },
    created() {
        this.init()
    },
    mounted() {},
    methods: {
        ...mapActions('dialogGoods', [
            'onOffDialogGoods',
            'setDialogGoods',
            'clearSelGoods'
        ]),
        init() {
            if (window.sessionStorage) {
                //分类需要实时更新数据
                // if (
                //     localStorage.getItem('categoryCOption') &&
                //     localStorage.getItem('categoryCOption') !== 'undefined'
                // ) {
                //     this.categoryCOption = JSON.parse(
                //         localStorage.getItem('categoryCOption')
                //     )
                // } else {
                    this.resCategoryCOption().then(() => {
                        localStorage.setItem(
                            'categoryCOption',
                            JSON.stringify(this.categoryCOption)
                        )
                    })
                //}

                if (
                    localStorage.getItem('brandList') &&
                    localStorage.getItem('categoryCOption') !== 'undefined'
                ) {
                    this.brandList = JSON.parse(
                        localStorage.getItem('brandList')
                    )
                } else {
                    this.resBrandList().then(() => {
                        localStorage.setItem(
                            'brandList',
                            JSON.stringify(this.brandList)
                        )
                    })
                }
            } else {
                this.resCategoryCOption()
                this.resBrandList()
            }
        },
        resCategoryCOption() {
            return this.$http
                .post(`/${window.apiAuthority}/touch_visual/category`,qs.stringify({
                    ru_id:window.shopInfo.ruid
                }))
                .then(({ data }) => {
                    let { category } = data
                    this.categoryCOption = category
                }).catch(err => {
                    console.error(err)
                })
        },
        resBrandList() {
            return this.$http
                .post(`/${window.apiAuthority}/touch_visual/brand`,qs.stringify({
                    ru_id:window.shopInfo.ruid
                }))
                .then(({ data }) => {
                    let { brand } = data
                    this.brandList = brand
                }).catch(err => {
                    console.error(err)
                })
        },
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
        aShowtags() {
            let styleStyleIndex = 0
            this.data.showStyle.map((v, i) => {
                if (v.key == this.styleSel) styleStyleIndex = i
            })
            let aTag = this.data.showStyle[styleStyleIndex].tags
            let aShowTags = []

            // this.data.showTag.map((v, i) => {
            //     aTag.map((tV, tI) => {
            //         if (v.key == tV) {
            //             aShowTags.push(v)
            //         }
            //     })
            // })
            return aShowTags
        },
        aShowtabs(){
            let aShowtabs = []

            this.data.showTab.map((v, i) => {
                aShowtabs.push(v)
            })
            
            return aShowtabs
        },
        styleSel: {
            get() {
                return this.data.isStyleSel
            },
            set(value) {
                this.updateRadioSel('isStyleSel', value)
            }
        },
        tabSelList: {
            get() {
                return this.data.tabSelList
            },
            set(value) {
                this.updateRadioSel('tabSelList', value)
            }
        },
        tabInputList: {
            get() {
                return this.data.tabInputList
            },
            set(value) {
                //this.updateRadioSel('tabSelList', value)
            }
        },
        moduleSel: {
            get() {
                return this.data.isModuleSel
            },
            set(value) {
                this.updateRadioSel('isModuleSel', value)
            }
        },
        scrollNumber: {
            get() {
                return this.data.allValue.scrollNumber
            },
            set(value) {
                this.updateTitleText('scrollNumber', value)
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
        brandSelect: {
            get() {
                return this.data.allValue.brandSelect || ''
            },
            set(value) {
                this.updateTitleText('brandSelect', value)
            }
        },
        categorySOption: {
            get() {
                let s = this.data.allValue.categorySOption.split(',')
                let arr = []

                //把分割数组字符串类型转换为int整型
                s.forEach((data)=>{
                    arr.push(+data)
                })

                return arr
            },
            set(value) {
                this.updateTitleText('categorySOption', value.join(','))
            }
        },
        catId() {
            let len = this.categorySOption.length
            return len > 0 ? this.categorySOption[len - 1] : ''
        },
        type() {
            let s = ''
            switch (this.moduleSel) {
                case '0':
                    s = 'best'
                    break
                case '1':
                    s = 'new'
                    break
                case '2':
                    s = 'host'
                    break
                case '3':
                    s = ''
                    break
                default:
                    s = ''
                    break
            }
            return s
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
.cascader {
    width: 100%;
}
.brand-select {
    width: 100%;
}
a.clear {
    color: #999;
}
</style>