<template>
    <div id='edit-area'>
        <header>
            <h4>{{ editArea.text }}</h4>
            <div class="compontent-tool">
                <label @click="removeModules" for="">
                    <i class="iconfont" :class="'icon-'+editArea.function.clear.icon"></i>{{$t('lang.empty')}}
                </label>
                <label for="" v-if="!bStore && sDefault != 0" @click.stop="restoreModules">
                    <i class="iconfont" :class="'icon-'+editArea.function.restore.icon"></i>{{$t('lang.restore')}}
                </label>
                <label @click="saveModules" for="">
                    <i class="iconfont" :class="'icon-'+editArea.function.save.icon"></i>{{$t('lang.storage')}}
                </label>
            </div>
        </header>
        <div class="edit-area-con" :class="{no:!isMoudles}">
            <div style="height:auto">
                <draggable v-model="modules" style="min-height:10rem; height:auto;" :options="{group:'people',handle:'.handle'}" @start="isDragging=true" @add="onDraggAdd($event)" :move="onDraggMove" @end="isDragging=false">
                    <component class="component" v-for="(item,index) in modules" :key="index" :is="'b-'+item.module" :modules-index="index" :onlineData="item.data" :setting="item.setting" :data-index="index" :show="item.isShow">
                    </component>
                </draggable>
            </div>
        </div>
    </div>
</template>
<script>
//node library
import qs from 'qs'
// mapActions mapState
import {
    mapActions,
    mapState
} from 'vuex'

// third party libraries
import html2canvas from 'html2canvas'

// custom components
import BSlide from '@/components/visualization/slide/Backend'
import BTitle from '@/components/visualization/title/Backend'
import BAnnouncement from '@/components/visualization/announcement/Backend'
import BNav from '@/components/visualization/nav/Backend'
import BLine from '@/components/visualization/line/Backend'
import BBlank from '@/components/visualization/blank/Backend'
import BJigsaw from '@/components/visualization/jigsaw/Backend'
import BProduct from '@/components/visualization/product/Backend'
import BCoupon from '@/components/visualization/coupon/Backend'
import BCountDown from '@/components/visualization/count-down/Backend'
import BButton from '@/components/visualization/button/Backend'
import BSearch from '@/components/visualization/search/Backend'
import BStore from '@/components/visualization/store/Backend'
import BShopSigns from '@/components/visualization/shop-signs/Backend'
import BTabDown from '@/components/visualization/tab-down/Backend'
import BLive from '@/components/visualization/live/Backend'
import BCategoryNav from '@/components/visualization/category-nav/Backend'
import BVisualTeam from '@/components/visualization/visual-team/Backend'
import BVisualAdv from '@/components/visualization/visual-adv/Backend'
import BProductPick from '@/components/visualization/product-pick/Backend'

// third party components
import {
    MessageBox,
    Message,
    Loading
} from 'element-ui'

import draggable from 'vuedraggable'

export default {
    name: 'edit-area',
    components: {
        draggable,
        MessageBox,
        Message,
        BSlide,
        BTitle,
        BAnnouncement,
        BNav,
        BLine,
        BBlank,
        BJigsaw,
        BProduct,
        BCoupon,
        BCountDown,
        BButton,
        BSearch,
        BStore,
        BShopSigns,
        BTabDown,
		BLive,
        BCategoryNav,
        BVisualTeam,
        BVisualAdv,
        BProductPick,
        Loading,
    },
    data() {
        return {
        }
    },
    created() {
    },
    methods: {
        generateImage() {
            return new Promise((resolove, reject) => {
                let divImg = document.getElementById("j-phone-edit")
                html2canvas(divImg, {
                    proxy: window.ROOT_URL + `html2canvasproxy.php`, //跨域支持
                    width: 318,
                    height: 366,
                    background: "#fff",
                    useCORS: true
                }).then(canvas => {
                    resolove(canvas.toDataURL())
                }).catch(err => {
                    reject(false)
                    console.log(err)
                })
            })
        },
        loadImage() {
        },
        onDraggMove({ relatedContext, draggedContext }) {
            const relatedElement = relatedContext.element
            const draggedElement = draggedContext.element
        },
        onDraggAdd(e) {
            let moduleName = e.item.getAttribute("data-module")
            let length = 0
            if(moduleName == 'product-pick'){
                this.modules.find((v)=>{
                    if(v.module == moduleName){
                        length++;
                    }
                })
            }
            e.item.outerHTML = "" //拖拽添加后删除该内容'
            this.$store.dispatch('navHiddenTab')//拖拽放置结束影藏下拉框

            if(length > 0){
                Message.error('此组件只能添加一个');
                return 
            }

            this.$store.dispatch('addModules', {
                newIndex: e.newIndex,
                module: this.cloneModules(moduleName)
            })
        },
        cloneModules(moduleName) {
            delete require.cache[require.resolve(`@/components/visualization/${moduleName}/data/online`)];
            return require(`@/components/visualization/${moduleName}/data/online`);
        },
        removeModules() {
            if (this.isMoudles) {
                MessageBox.confirm(this.$t('lang.remove_modules_confirm'), this.$t('lang.hint'), {
                    confirmButtonText: this.$t('lang.confirm'),
                    cancelButtonText: this.$t('lang.cancel'),
                    type: 'warning'
                }).then(() => {
                    this.$store.dispatch('removeAllModules')
                    Message.success(this.$t('lang.empty_success'))
                }).catch(err => {
                    Message.info(this.$t('lang.cancelled_operation_1'))
                });
            } else {
                Message.warning(this.$t('lang.no_data'))
            }
        },
        saveModules() {
            let results = this.modules.find(v=>(v.module == 'slide' && v.data.isStyleSel == '2'));
            let modules1 = JSON.parse(JSON.stringify(this.modules));
            let device = window.shopInfo.device // device 设备  h5 app wxapp
            if(results){
                modules1.forEach((item)=>{
                    if(item.module == 'search' || item.module == 'category-nav'){
                        if(results.data.list[0].bgColor){
                            item.data.allValue.bgColor = results.data.list[0].bgColor;
                        }
                    }else if(item.module == 'slide'){
                        if(item.data.isStyleSel == 2){
                            item.data.allValue.bgColor = results.data.list[0].bgColor;
                        }
                    }
                })
            }

            let sModules = JSON.stringify(modules1)
            let localModules = ""
            
            if (localStorage.getItem("modules")) {
                localModules = localStorage.getItem("modules")
            }

            if (sModules != localModules) {
                MessageBox.confirm(this.$t('lang.is_save_current_mod'), this.$t('lang.hint'), {
                    confirmButtonText: this.$t('lang.confirm'),
                    cancelButtonText: this.$t('lang.cancel'),
                    type: 'warning'
                }).then(() => {
                    let loadingInstance = Loading.service({ fullscreen: true, text: this.$t('lang.data_passing') });
                    this.generateImage().then(resImage => {
                        this.$http.post(`/${window.apiAuthority}/touch_visual/save`, qs.stringify({
                            id: this.modulesId,
                            data: sModules,
                            pic: resImage,
                            device:device
                        })).then(res => {
                            if (res.data.error == 0) {
                                Message.success(this.$t('lang.save_success_1'))
                                this.$store.dispatch('updatePagePic', {
                                    pic: resImage,
                                    id: this.modulesId,
                                })
                                loadingInstance.close()
                                localStorage.setItem("modules", JSON.stringify(this.modules))
                            } else {
                                Message.error(this.$t('lang.save_fail_1'))
                            }
                        }).catch(err => {
                            Message.error(this.$t('lang.save_fail_1'))
                        })
                    }, rej => {
                        Message.error(this.$t('lang.pic_generat_fail'))
                    })
                }).catch(() => {
                    Message.info(this.$t('lang.cancelled_operation_1'))
                })
            } else {
                Message.warning({
                    message:this.$t('lang.current_page_not_edit')
                })
            }
        },
        restoreModules() {
            MessageBox.confirm(this.$t('lang.restore_modules_confirm'), this.$t('lang.hint'), {
                confirmButtonText: this.$t('lang.confirm'),
                cancelButtonText: this.$t('lang.cancel'),
                type: 'warning'
            }).then(() => {
                let device = window.shopInfo.device // device 设备  h5 app wxapp
                let loadingInstance = Loading.service({ fullscreen: true, text: this.$t('lang.data_passing') });
                this.$http.post(`/${window.apiAuthority}/touch_visual/restore`, qs.stringify({
                    id: this.modulesId,
                    device: device
                })).then(({ data: { keep: { data } } }) => {
                    loadingInstance.close()
                    this.$store.dispatch('updateModules', {
                        modules: JSON.parse(data)
                    })
                })
            }).catch(err => {
                console.error()
            })
        }
    },
    computed: {
        isMoudles() {
            return 0 < this.modules.length ? true : false
        },
        ...mapState({
            editArea: state => state.dashboard.configInfo.editArea,
            bUpdateModules: state => state.dashboard.bUpdateModules,
            modulesId: state => state.pageSetting.modulesId,
            menuComponent: state => state.dashboard.configInfo.headerMenu.menuComponent,
            sDefault: state => state.pageSetting.default
        }),
        bStore() {
            return window.shopInfo.ruid != 0
        },
        modules: {
            get() {
                return this.$store.state.modules
            },
            set(value) {
                this.$store.dispatch('updateModules', {
                    modules: value
                })
            }
        }
    }
}

</script>

<style lang="scss" scoped>
@import '../../../assets/style/config';
@import '../../../assets/style/mixins/common';
header {
    height: ($tab-height+.4rem);
    background: $tool-bg;
    padding: 0 1rem;
    @include direction(center, space-between)
}

header h4 {
    float: left;
    font-weight: normal;
    font-size: $tool-title-size
}

header .compontent-tool {
    float: right;
}

header .compontent-tool label {
    margin-left: $icon-margin * 1.5;
    color: #555;
    cursor: pointer;
    font-size: 1.4rem;
}

header .compontent-tool label i.iconfont {
    margin-right: ($icon-margin)
}

.edit-area-con {
    background: $write-color;
    position: absolute;
    width: 100%;
    padding: 1.4rem;
    top: ($tab-height+.4rem);
    bottom: 0rem;
    overflow-y: scroll;
}

.edit-area-con.no {
    background: url('../../../assets/img/admin_edit.jpg') no-repeat center center;
}

.component {
    margin-bottom: .8rem
}

.component:last-child {
    margin-bottom: 0;
}

.component.hidden {
    visibility: hidden;
}

.flip-list-move {
    transition: transform 0.5s;
}
</style>