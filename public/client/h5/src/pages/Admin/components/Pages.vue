<template>
    <div class='pages' :class="{'close-page-list':!bPageListTitle}">
        <div class="search" v-show="activeName == 'custom'">
            <div class="search-text">
                <i class="iconfont icon-search"></i>
                <input type="text" :placeholder="$t('lang.page_search')" v-model="searchValue">
            </div>
        </div>
        <tabs class="ec-tabs" v-model="activeName" type="border-card" @tab-click="tabClick()" ref="pageTabs">
            <tab-pane :label="pageListTool.system.text" name="system" :class="'system'">
                <page-list :page-list="systemPageList" :system="true"></page-list>
            </tab-pane>
            <tab-pane :label="pageListTool.custom.text" name="custom" v-if="oCustom">
                <page-list :page-list="customPageList" @custom="updateDialog">
                    <figure slot="addPage" class="add-page" @click.stop="addPage">
                        <i class="iconfont icon-plus"></i>
                    </figure>
                </page-list>
            </tab-pane>
        </tabs>
        <ec-dialog :title="nUpdateId==-1 ? $t('lang.add_page') : $t('lang.edit_page')" :visible.sync="bAddPageDialog" :modal-append-to-body="false" @close="closePageDialog">
            <div class="form-group">
                <label class="group-l">
                    {{$t('lang.label_topic_name')}}
                </label>
                <div class="group-r">
                    <ec-input size="small" :maxlength="15" v-model="sTitle" :placeholder="$t('lang.enter_page_name')"></ec-input>
                </div>
            </div>
            <div class="form-group">
                <label class="group-l">
                    {{$t('lang.label_topic_img')}}
                </label>
                <div class="group-r">
                    <ec-upload class="custom-uploader" ref="upload" :action="sUpload" :data="uploadData" :show-file-list="false" :auto-upload="false" :before-upload="beforeAvatarUpload" :on-change="changeAvatarUpload" :on-success="successAvatarUpload" accept="image/jpeg,image/png">
                        <i class="iconfont icon-plus" v-if="!sImageUrl"></i>
                        <img :src="sImageUrl" class="avatar" v-else>
                    </ec-upload>
                    <p class="ec-remark">{{$t('lang.topic_img_propmt')}}</p>
                </div>
            </div>
            <div slot="footer" class="dialog-footer">
                <ec-button size="small" @click="bAddPageDialog = false">{{$t('lang.cancel')}}</ec-button>
                <ec-button size="small" type="primary" @click="confirmAddPage('btnAddPage')">{{$t('lang.confirm')}}</ec-button>
            </div>
        </ec-dialog>
    </div>
</template>

<script>
//node library
import qs from 'qs'
import url from 'url'

//loadsh library
import debounce from 'lodash/debounce'

// mapActions mapState
import { mapActions, mapState } from 'vuex'

// custom components
import PageList from '@/components/visualization/element/PageList'

// api
import { apiModules } from '@/config'

// third party components
import {
    Tabs,
    TabPane,
    MessageBox,
    Message,
    Dialog,
    Input,
    Button,
    Upload,
    Loading
} from 'element-ui'

export default {
    name: 'pages',
    data() {
        return {
            activeName: 'system',
            sTitle: '',
            bAddPageDialog: false,
            searchValue: '',
            sImageUrl: '',
            bUpdate: false,
            nUpdateId: -1,
            loadingInstance: null
        }
    },
    components: {
        Tabs,
        TabPane,
        PageList,
        MessageBox,
        Message,
        Loading,
        EcDialog: Dialog,
        EcInput: Input,
        EcButton: Button,
        EcUpload: Upload
    },
    created() {
        this.setPageList()
        // 进入页面就执行该方法，如果有 top_id 直接请求数据库新建一个页面
        this.confirmAddPage()
    },
    mounted() {},
    methods: {
        autoAddTopic() {},
        tabClick() {
            let sCurrentName = this.$refs.pageTabs.value
            let sActiveName = this.activeName

            this.$emit('activeName', this.activeName)

            if (sCurrentName != sActiveName) {
                if (sActiveName == 'custom') {
                    let nCustomLen = this.customPageList.length
                    if (nCustomLen > 0) {
                        let nId = this.customPageList[0].id
                        if (this.modulesId != nId)
                            this.$store.dispatch('setModuleInfo', {
                                id: nId,
                                type:'topic'
                            })
                    } else {
                        MessageBox.confirm(this.$t('lang.no_custom_page'), this.$t('lang.hint'),
                            {
                                confirmButtonText: this.$t('lang.confirm'),
                                cancelButtonText: this.$t('lang.cancel'),
                                type: 'warning'
                            }
                        ).then(() => {
                            setTimeout(() => {
                                this.addPage()
                            }, 400)
                        }).catch(() => {
                            Message.info(this.$t('lang.you_cancel_operation'))
                        })
                    }
                } else {
                    let nSystemLen = this.systemPageList.length
                    if (nSystemLen > 0) {
                        let nId = this.systemPageList[0].id
                        if (this.modulesId != nId)
                            this.$store.dispatch('setModuleInfo', {
                                id: nId,
                                type:'index'
                            })
                    }
                }
            }
        },
        updateDialog(o) {
            this.sImageUrl = ''
            this.sTitle = o.title
            let picUrl = ''
            if (o.thumb_pic != '') {
                picUrl = o.thumb_pic
            }
            this.sImageUrl = picUrl
            this.bAddPageDialog = true
            this.nUpdateId = o.id
        },
        changeAvatarUpload(file, fileList) {
            this.sImageUrl = file.url
            this.$refs.upload.uploadFiles = this.$refs.upload.uploadFiles.filter(
                item => {
                    return Object.is(item.uid, file.uid)
                }
            )
        },
        successAvatarUpload({ error, page }, file) {
            if (error == 0) {
                this.addOrUpdatePage(page)
            } else {
                Message.error(this.$t('lang.add_fail_1'))
            }
        },
        beforeAvatarUpload(file) {
            //添加新页面上传图片前钩子
            const isLt200 = file.size / 1024 < 200
            if (!isLt200) {
                Message.error(this.$t('lang.upload_img_size'))
                setTimeout(() => {
                    this.loadingInstance.close()
                    this.sImageUrl = ''
                }, 300)
            }
            return isLt200
        },
        confirmAddPage(sBtnAddPage) {
            if (sBtnAddPage !== 'btnAddPage') {
                if (!this.uploadData.topicId) {
                    return false
                } else {
                    this.sTitle = this.uploadData.title
                }
            }
            if (this.sTitle != '') {
                this.loadingInstance = Loading.service({
                    fullscreen: true,
                    text: this.$t('lang.data_passing')
                })
                
                if (
                    this.$refs.upload &&
                    this.$refs.upload.uploadFiles.length > 0
                ) {
                    this.$refs.upload.submit()
                } else {
                    this.$http
                        .post(`/${window.apiAuthority}/touch_visual/title`,
                            qs.stringify(this.uploadData)
                        )
                        .then(
                            ({ data: { error, msg, page } }) => {
                                let { title, id } = page
                                if (error == 0) {
                                    this.addOrUpdatePage(page)
                                }
                                if (error == 1) {
                                    history.pushState(null, null, '/')
                                    this.$store.dispatch('setModuleInfo', {
                                        id,
                                        title
                                    })
                                    this.loadingInstance.close()
                                }
                            }
                        )
                        .catch(err => {
                            this.loadingInstance.close()
                        })
                }
            } else {
                Message.warning(this.$t('lang.fill_in_page_name'))
            }
        },
        addOrUpdatePage(page) {
            let o = {
                id: page.id,
                type: page.type,
                default: page.default,
                thumb_pic: page.thumb_pic,
                title: this.sTitle.trims()
            }
            if (this.nUpdateId == -1) {
                this.$store.dispatch('addPage', o)
                this.loadingInstance.close()
                this.sTitle = ''
                //history.pushState(null, null, '/')
                this.$store.dispatch('setModuleInfo', {
                    id: o.id,
                    title: o.title
                })
                if (this.uploadData.topicId) {
                    Message.success(this.$t('lang.has_auto_create_pc_topic'))
                } else {
                    Message.success(this.$t('lang.page_add_success'))
                }
            } else {
                this.$store.dispatch('updatePage', o)
                this.loadingInstance.close()
                Message.success(this.$t('lang.page_edit_success'))
            }
            this.nUpdateId = -1
            this.sImageUrl = ''
            this.bAddPageDialog = false
        },
        addPage() {
            this.sImageUrl = ''
            this.nUpdateId = -1
            let sLocalModules,
                sModules = JSON.stringify(this.modules)
            if (localStorage.getItem('modules')) {
                sLocalModules = localStorage.getItem('modules')
            }
            if (!(sLocalModules === sModules)) {
                apiModules
                    .tipsSaveModules({
                        modulesId: this.modulesId,
                        modules: sModules
                    })
                    .then(
                        resolve => {
                            if (resolve) {
                                Message.success(this.$t('lang.page_save_success'))
                                setTimeout(() => {
                                    this.bAddPageDialog = true
                                }, 700)
                            }
                        },
                        reject => {
                            if (!reject) Message.error(this.$t('lang.save_fail_1'))
                            if (reject === 'messageBox catch')
                                this.bAddPageDialog = true
                        }
                    )
            } else {
                this.bAddPageDialog = true
            }
        },
        searchPageList: debounce(function(o) {
            this.$store.dispatch('searchPageList', o)
        }, 600),
        setPageList() {
            return new Promise((resolve, reject) => {
                let ruid = window.shopInfo.ruid
                let sDefault = 3 //1为默认，0为自定义页面，3返回全部页面
                let number = 0
                let type = ruid > 0 ? 'store': 'index'
                let device = window.shopInfo.device // device 设备  h5 app wxapp

                this.$store.dispatch('initPageModule', {
                    //将pagelist赋值给store  pageList对象
                    ruid: ruid,
                    sDefault,
                    number,
                    bTopId: !!this.uploadData.topicId,
                    type:type,
                    device:device
                })
            })
        },
        closePageDialog() {
            this.sTitle = ''
            let file = document.querySelector(
                '.custom-uploader .el-upload__input'
            )
            if (file.value) {
                file.value = ''
            } else {
                file.outerHTML = file.outerHTML
            }
            this.$refs.upload.uploadFiles = []
        },
        isEqualTitle(o) {
            this.$store.dispatch(o)
        }
    },
    computed: {
        ...mapState({
            pageListTool: state => state.dashboard.configInfo.pageListTool,
            bPageListTitle: state =>
                state.dashboard.configInfo.headerMenu.pageListTitle.isActive,
            pageList: state => state.pageList,
            systemPageList: state => {
                return state.pageList.filter(item => {
                    return item.default == 1
                })
            },
            customPageList: state => state.customPageList,
            modules: state => state.modules,
            modulesId: state => state.pageSetting.modulesId,
            oCustom: () => window.shopInfo.ruid == 0
        }),
        sUpload() {
            return `/${window.apiAuthority}/touch_visual/title`
        },
        uploadData() {
            if (this.nUpdateId != -1) {
                return {
                    title: this.sTitle.trims(),
                    ruid: 0,
                    id: this.nUpdateId
                }
            }
            let oUrl = url.parse(window.location.href, true)
            let topicId = 0
            let title = ''
            let device = window.shopInfo.device // device 设备  h5 app wxapp
            if (oUrl.query.topic_id) {
                this.activeName = 'custom'
                topicId = oUrl.query.topic_id
                this.sTitle = oUrl.query.title || this.$t('lang.custom_establish_page')
            }

            return {
                title: this.sTitle.trims(),
                ruid: 0,
                topicId: topicId ? topicId : void 0,
                type: 'topic',
                device:device
            }
        }
    },
    watch: {
        searchValue(val, oldVal) {
            let nDefault = this.activeName == 'system' ? 1 : 0
            this.searchPageList({
                title: val,
                default: nDefault
            })
        }
    }
}
</script>

<style scoped lang="scss">
@import '../../../assets/style/config';
@import '../../../assets/style/mixins/common';
.pages {
    left: 0;
    @include transition(left, 0.3s);
}

.pages.close-page-list {
    left: -18rem;
    @include transition(left, 0.3s);
}

.search {
    position: absolute;
    z-index: 11;
    width: 100%;
    top: $tab-height;
    padding: 1.2rem;
    border-bottom: 1px solid $border-color-split;
    background: #fff;
}

.search-text {
    border-radius: 9999px;
    border: 1px solid $border-color-base;
    overflow: hidden;
    position: relative;
    height: 2.4rem;
}

.search-text i {
    font-size: 1.4rem;
    position: absolute;
    left: 0.8rem;
    color: #999;
    top: 50%;
    @include translateY(-50%);
}

.search input {
    font-size: 1.24rem;
    border: 0;
    height: 2.2rem;
    line-height: 2.2rem;
    position: absolute;
    left: 2.3rem;
    right: -1rem;
    display: inline-block;
    /*width:100%;*/
    color: $text-color;
}

.ec-tabs {
    @include shadow(10px, 0, 20px, raba(0, 0, 0, 0.1));
    border: none !important;
    position: absolute;
    top: 0;
    bottom: 0;
    width: 100%;
}

.custom-uploader {
    border: 1px dashed $border-color-split;
    max-width: 22rem;
    height: 12rem;
    border-radius: 6px;
    position: relative;
    padding: 0.8rem;
    @include disFlex();
    @include direction(center, center);
    a {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 2.8rem;
        height: 2.8rem;
        line-height: 2.8rem;
        display: block;
        border-radius: 1.4rem;
        border: 1px solid rgba(255, 255, 255, 0.4); // opacity: 0;
        // visibility: hidden;
        @include transition(all, 0.2s);
        @include translate3d(-50%, -50%, 0);
        i {
            font-size: 1.6rem;
            color: #fff;
        }
    }
    a:hover {
        background: $color;
    }
}

.custom-uploader i.icon-plus {
    font-size: 4rem;
    display: block;
    width: 100%;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    line-height: 12rem;
    color: $border-color-split;
    position: absolute;
}

.custom-uploader img {
    max-width: 20rem;
    max-height: 10rem;
}

.custom-uploader:hover {
    border-color: $link-color;
    cursor: pointer;
}

.custom-uploader:hover i.icon-plus {
    color: $link-color;
}
</style>