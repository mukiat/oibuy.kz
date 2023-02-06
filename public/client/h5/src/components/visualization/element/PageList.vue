<template>
    <div class='page-list'>
        <slot name="addPage"></slot>
        <figure v-for="(item,index) in pageList" :key="index" :class="{
            selected: item.id == modulesId
        }">
            <template>
                <section class="page-list-handle">
                    <a href="javascript:;" class="remove" :title="$t('lang.delete')" @click.stop="removePage(item.id,item.title)" v-if="bShowPopover">
                        <i class="iconfont icon-clear"></i>
                    </a>
                    <a href="javascript:;" class="editor" :class="{system:!bShowPopover}" :title="$t('lang.use')" @click.stop="editorPage(item.id,item.title)">
                        <i class="iconfont icon-wancheng1"></i>
                    </a>
                </section>
                <div class="mask"></div>
            </template>
            <div class="selected-current">
                <span>{{$t('lang.cur')}}</span>
            </div>
            <img :src="item.pic" alt="1" v-if="item.pic" />
            <img :src="defaultImg" alt="2" :data-url="defaultImg" v-else>
            <figcaption>
                <!-- <ec-popover v-if="bShowPopover" placement="bottom" width="300" trigger="click" :ref="'popover'+item.id">
                    <popover-ipt-btn :value="item.title" :list-index="index" :popover-ref="'popover'+item.id" :id="item.id" @updateValue="updatePageTitle"></popover-ipt-btn>    
                </ec-popover> -->
                <span style="margin-right: 5px;"><em style="color: #409EFF;">{{item.id}}.</em> {{ item.title }}</span>
                <i slot="reference" class="iconfont icon-editor" v-if="bShowPopover" @click.stop="showPageDialog(item.id,item.title,item.thumb_pic)" :title="$t('lang.edit')"></i>
            </figcaption>
        </figure>
    </div>
</template>
<script>
// node library
import qs from 'qs'

// mapActions mapState
import { mapActions, mapState } from 'vuex'
// third party components
import { Popover, MessageBox, Message } from 'element-ui'

// custom components
import PopoverIptBtn from './PopoverIptBtn'

//api
import { apiModules } from '@/config'

export default {
    name: 'page-list',
    props: ['pageList', 'system'],
    components: {
        EcPopover: Popover,
        PopoverIptBtn
    },
    data() {
        return {
            bShowPopover: true
        }
    },
    created() {
        if (this.system) this.bShowPopover = false
    },
    beforeMount() {
        this.$store.dispatch('setDocConfig') //将页面列表信息赋值给store  pagelist对象
    },
    methods: {
        showPageDialog(id, title, thumb_pic) {
            this.$emit('custom', {
                id,
                title,
                thumb_pic
            })
        },
        editorPage(id, title) {
            if (id != this.modulesId) {
                let localModules = ''
                let sModules = JSON.stringify(this.modules)
                console.log(sModules,'1')
                if (localStorage.getItem('modules')) {
                    localModules = localStorage.getItem('modules')
                }
                console.log(localModules,'2')
                if (sModules != localModules) {
                    apiModules
                        .tipsSaveModules({
                            modulesId: this.modulesId,
                            modules: sModules
                        })
                        .then(
                            resolve => {
                                if (resolve) {
                                    this.$store.dispatch('setModuleInfo', {
                                        id,
                                        title
                                    })
                                }
                            },
                            reject => {
                                if (reject === 'messageBox catch') {
                                    this.$store.dispatch('setModuleInfo', {
                                        id,
                                        title
                                    })
                                }
                            }
                        )
                } else {
                    this.modules = []
                    this.$store.dispatch('setModuleInfo', {
                        id,
                        title
                    })
                }
            }
        },
        removePage(id, title) {
            apiModules
                .removeModules({
                    id,
                    title
                })
                .then(
                    resolve => {
                        if (resolve) {
                            this.$store.dispatch('removePage', {
                                id
                            })
                            Message.success(this.$t('lang.delete_success'))
                        }
                    },
                    reject => {
                        if (!reject) Message.error(this.$t('lang.delete_fail'))
                        if (reject === 'messageBox catch')
                            Message.warning(this.$t('lang.cancelled_operation_alt'))
                    }
                )
        }
    },
    computed: {
        ...mapState({
            modulesId: state => state.pageSetting.modulesId,
            //modules: state => state.modules
        }),
        modules:{
          get(){
            return this.$store.state.modules
          },
          set(val){
            this.$store.state.modules = val
          }
        },
        defaultImg(){
            return this.release ? `${window.ROOT_URL}/img/default-img.fe203b8.jpg` : require('@/assets/img/default-img.jpg')
        }
    }
}
</script>

<style lang="scss" scoped>
@import '../../../assets/style/config.scss';
@import '../../../assets/style/mixins/common.scss';
.page-list {
    text-align: center;
    .mask {
        top: 2px;
        left: 2px;
        right: 2px;
        bottom: 2.2rem;
        opacity: 0;
        visibility: hidden;
        @include transition(all, 0.2s);
    }
}

.page-list figure {
    position: relative;
    width: 90%;
    margin: 0 auto;
    margin-bottom: 1.2rem;
    @include transition(all, 0.2s);
}

.page-list figure.add-page {
    padding: 0.8rem 0;
    border: 1px solid $border-color-base;
}

.page-list figure.add-page i {
    color: $border-color-base;
    font-size: 3rem;
}

.page-list figure img:hover,
.page-list .add-page:hover {
    cursor: pointer;
    border-color: $link-color;
}

.page-list .add-page:hover i {
    color: $link-color;
}

.page-list figure img {
    width: 100%;
    height: auto;
    padding: 4px;
    border: 1px solid $border-color-base;
}

.page-list figcaption {
    position: relative;
    margin-top: 0.4rem;
    @include translateX(-0.4rem);
}

.page-list span {
    font-size: 1.3rem;
    color: #666;
}

.page-list i.icon-editor {
    color: #aaa;
    font-size: 1.3rem;
    margin-right: 0.4rem;
    cursor: pointer;
}

.page-list input:focus {
    background: #f1f1f1;
}

.page-list-handle {
    display: block;
    width: 7rem;
    height: 2.8rem;
    line-height: 2.8rem;
    position: absolute;
    top: 50%;
    left: 50%;
    z-index: 123;
    @include translate3d(-50%, -50%, 0);
    a {
        position: absolute;
        width: 2.8rem;
        height: 2.8rem;
        display: block;
        float: left;
        border-radius: 1.4rem;
        border: 1px solid rgba(255, 255, 255, 0.4);
        opacity: 0;
        visibility: hidden;
        @include transition(all, 0.2s);
    }
    a:first-of-type {
        left: -1.6rem;
    }
    a:last-of-type {
        right: -1.6rem;
    }
    a:hover {
        background: $link-color;
        border: 1px solid $link-color;
    }
    a.remove:hover {
        background: $color;
        border: 1px solid $color;
    }
    a.editor.system {
        left: 50%;
        @include translateX(-50%);
    }
    i {
        color: #fff;
    }
}

.page-list figure:hover {
    @include transition(all, 0.2s);
    .mask {
        opacity: 1;
        visibility: visible;
        @include transition(all, 0.2s);
    }
    a {
        opacity: 1;
        visibility: visible;
        @include transition(all, 0.2s);
    }
    a.remove {
        left: 0;
    }
    a.editor {
        right: 0;
    }
}
.selected .selected-current {
    display: inherit;
}
.selected-current {
    position: absolute;
    color: #fff !important;
    display: none;
    font-size: 12px;
    right:-24px;
    top:-24px;
    width: 0;
    height: 0;
    border-right: 26px solid transparent;
    border-top: 26px solid transparent;
    border-left: 26px solid transparent;
    border-bottom: 26px solid #f8475d;
    transform: rotateZ(45deg);
    z-index: 124;
    span{
        color: #fff;
        font-weight: bold;
        position: absolute;
        // left: -14px;
        // top: 16px;
        left: -12px;
        top: 7px;
        transform: scale(.8);
        white-space:nowrap;
    }
}
</style>