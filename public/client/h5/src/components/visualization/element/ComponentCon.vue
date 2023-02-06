<template>
    <div id='component-con'>
        <div class="collapse" :class="{close:!bSetting}">
            <header class="title">
                <div class="handle"></div>
                <div class="title-left" @click.stop="isShowEditComponent()">
                    <span>
                      {{ componentName }}
                    </span>
                    <span class="rotate-arrow">
                        <i class="iconfont icon-arrow"></i>
                     </span>
                </div>
                <div class="title-right">
                    <ul v-if="bSortOrDel">
                        <li @click="sortModules('up')" :class="{first:bFirst}">
                            <i class="iconfont icon-sort-arrow"></i>
                        </li>
                        <li @click="sortModules('down')" :class="{last:bLast}">
                            <i class="iconfont icon-sort-arrow"></i>
                        </li>
                        <li @click.stop="copyModules()">
                            <i class="iconfont icon-page"></i>
                        </li>
                        <li @click="removeModules()">
                            <i class="iconfont icon-clear"></i>
                        </li>
                    </ul>
                </div>
            </header>
            <section class="con">
                <slot></slot>
            </section>
        </div>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import {
        MessageBox,
        Message
    } from 'element-ui'

    export default {
        name: 'component-con',
        props:{
            setting:{},
            componentName:{},
            modulesIndex:{},
            bSortOrDel:{
                default:true
            }
        },
        methods: {
            isShowEditComponent() {
                this.$store.dispatch('isShowEditComponent', {
                    modulesIndex: this.modulesIndex,
                })
            },
            sortModules(value) {
                if(value == "down" && this.bLast) return false
                if(value == "up" && this.bFirst) return false
                this.$store.dispatch('sortModules',{
                    modulesIndex:this.modulesIndex,
                    sort:value
                })
            },
            //复制
            copyModules(){
                MessageBox.confirm(this.$t('lang.copy_components_confirm'), this.$t('lang.hint'), {
                    confirmButtonText: this.$t('lang.confirm'),
                    cancelButtonText: this.$t('lang.cancel'),
                    type: 'warning'
                }).then(() => {
                    this.$store.dispatch('copyModules', {
                        modulesIndex: this.modulesIndex
                    })
                    Message.success({
                        message: this.$t('lang.copy_success')
                    });
                }).catch(() => {
                    Message.info({
                        message: this.$t('lang.cancelled_operation_1')
                    });
                });
            },
            removeModules() {
                 MessageBox.confirm(this.$t('lang.delete_components_confirm'), this.$t('lang.hint'), {
                    confirmButtonText: this.$t('lang.confirm'),
                    cancelButtonText: this.$t('lang.cancel'),
                    type: 'warning'
                }).then(() => {
                    this.$store.dispatch('removeModules', {
                        modulesIndex: this.modulesIndex
                    })
                    Message.success({
                        message: this.$t('lang.delete_success')
                    });
                }).catch(() => {
                    Message.info({
                        message: this.$t('lang.cancelled_operation_1')
                    });
                });
            }
        },
        computed: {
            ...mapState({
                bFirst(state) {
                    return this.modulesIndex === 0 ? true : false
                },
                bLast(state) {
                    return this.modulesIndex === state.modules.length - 1 ? true : false
                },
            }),
            bSetting() {
                return '0' == this.setting ? false : true
            }
        }

    }

</script>

<style lang="scss" scoped>
    @import '../../../assets/style/config.scss';
    @import '../../../assets/style/mixins/common.scss';
    .title {
        position: relative;
    }
    
    .title-left,
    .title-right {
        position: absolute;
        height: $component-height;
        line-height: $component-height;
    }
    
    .title-left {
        left: .7rem;
        cursor: pointer;
    }
    
    .title-left span {
        min-width: 1rem;
    }
    
    .title-right {
        right: .7rem;
    }
    
    .title-right .iconfont.icon-sort-arrow {
        font-size: 1.7rem;
        margin-top: -1px;
        color: #888;
        display: block;
    }
    
    .title-right .iconfont {
        cursor: pointer;
    }
    
    .title-right ul li {
        float: left;
        color: #777;
        margin-left: .4rem;
        cursor: pointer;
        padding: 0 1px;
        overflow: hidden;
    }
    
    .title-right ul li.first .iconfont,
    .title-right ul li.last .iconfont {
        color: #bbb;
    }
    
    .title-right ul li:first-of-type .icon-sort-arrow {
        @include ransformRotate(90deg);
    }
    
    .title-right ul li:nth-child(2) .icon-sort-arrow {
        @include ransformRotate(270deg);
    }
    
    .con {
        background: #fff;
    }
    .handle{
        position: absolute;
        left: 0;
        top:0;
        bottom:0;
        right: 100px;
    }
</style>