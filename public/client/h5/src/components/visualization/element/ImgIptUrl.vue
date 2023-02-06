<template>
    <div class='img-ipt-url'>
        <div class="url-edit-img">
            <slot name="edit-img-close"></slot>
            <edit-img :image="info.img" :modules-index="modulesIndex" :list-index="listIndex" v-show="bEditImg"></edit-img>
            <div class="edit-input">
                <div class="form-group">
                    <label class="group-l">{{$t('lang.label_describe')}}</label>
                    <div class="group-r">
                        <ec-input v-model="desc" size="small" :placeholder="$t('lang.add_describe')"></ec-input>
                    </div>
                </div>
                <div class="form-group">
                    <label class="group-l">{{$t('lang.label_url')}}</label>
                    <div class="group-r">
                        <menu-link :catetory="info.urlCatetory" :url-name="info.urlName" :modules-index="modulesIndex" :list-index="listIndex">
                            <span class="link-name-close" slot="link-name-close" @click.stop="removeMenuUrl">
                                <i class="iconfont icon-close"></i>
                            </span>
                        </menu-link>
                    </div>
                </div>
                <div class="form-group" v-if="sortType">
                    <label class="group-l">{{$t('lang.label_sort')}}</label>
                    <div class="group-r">
                        <ec-input type="sort" size="small" min="1" :value="sort" @change="sortChange" :placeholder="$t('lang.enter_sort')"></ec-input>
                    </div>
                </div>
                <div class="form-group" v-if="bgColor">
                    <label class="group-l">背景：</label>
                    <div class="group-r">
                        <color-input v-model="bgColorValue" :placeholder="$t('lang.bgColor2_placeholder')"></color-input>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
//custom components
import MenuLink from './MenuLink'
import EditImg from './EditImg'
import PopoverIptBtn from './PopoverIptBtn'
import ColorInput from './ColorInput'

import { Input, Popover } from 'element-ui'

//mixins
import fromProcessing from '@/mixins/form-processing'

import localConfig from '@/config/local/config'

export default {
    name: 'img-ipt-url',
    props: {
        info: {
            type: Object,
            required: true
        },
        bEditImg: {
            type: Boolean,
            required: true
        },
        listIndex: {
            type: Number,
            required: true
        },
        modulesIndex: {
            type: Number,
            required: true
        },
        sortType:{
            type:Boolean,
            default:true
        },
        bgColor:{
            type:Boolean,
            default:false
        }
    },
    mixins: [fromProcessing],
    components: {
        "EcInput": Input,
        "EcPopover": Popover,
        PopoverIptBtn,
        MenuLink,
        EditImg,
        ColorInput
    },
    data() {
        return {

        }
    },
    beforeMount() {

    },
    methods: {
        removeMenuUrl() {
            this.$store.dispatch('removeMenuUrl', {
                modulesIndex: this.modulesIndex,
                listIndex: this.listIndex
            })
        },
        sortChange(e){
            this.$emit("setInfoValue", {
                listIndex: this.listIndex,
                infoName: "sort",
                attrListName: "list",
                newValue: e
            })
        }
    },
    computed: {
        bStore() {
            return window.shopInfo.ruid != 0
        },
        desc: {
            get() {
                return this.info.desc
            },
            set(value) {
                this.$emit("setInfoValue", {
                    listIndex: this.listIndex,
                    infoName: "desc",
                    attrListName: "list",
                    newValue: value
                })
            }
        },
        sort: {
            get() {
                return this.info.sort == '' ? 1 : this.info.sort
            },
            set(value) {
                // if (value < 1) {
                //     value = 1
                // }
                // const timer = setTimeout(() => {
                //     this.$emit("setInfoValue", {
                //         listIndex: this.listIndex,
                //         infoName: "sort",
                //         attrListName: "list",
                //         newValue: value
                //     })
                //     clearTimeout(timer);
                // }, 1000)                
            }
        },
        bgColorValue:{
            get() {
                return this.info.bgColor == '' ? localConfig.pageInfo.defalutBg : this.info.bgColor
            },
            set(value){
                this.$emit("setInfoValue", {
                    listIndex: this.listIndex,
                    infoName: "bgColor",
                    attrListName: "list",
                    newValue: value
                })
            }
        }
    }
}

</script>

<style lang="scss" scoped>
@import '../../../assets/style/config.scss';
@import '../../../assets/style/mixins/common.scss';
.url-edit-img {
    box-sizing: border-box;
    border-radius: .4rem;
    padding: 1.2rem 1rem;
    max-width: 60rem;
    margin-top: 1.4rem;
    position: relative;
    border: 1px solid $border-color-split;
    @include direction(center, flex-start)
}

.link-close,
.link-name-close {
    position: absolute;
    right: -.9rem;
    top: -.9rem;
    color: #fff;
    text-align: center;
    width: 1.8rem;
    height: 1.8rem;
    line-height: 1.8rem;
    background: rgba(0, 0, 0, .4);
    border-radius: 9999px;
    transform: scale(.9);
    display: none;
}

.url-edit-img:hover .link-close {
    display: block;
    cursor: pointer;
}

.link-close i {
    font-size: .8rem;
    font-weight: bold;
}

.edit-input {
    flex: 1;
    width: 60%;
}

.edit-input .group-r {
    flex: 1;
    width: 70%;
}
</style>