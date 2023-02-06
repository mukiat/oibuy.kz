<template>
    <ec-dialog class="dialog-ec" width="50%" top="10%" :visible.sync="bThisShowDialog" @close="thisClose">
        <div slot="title" class="el-dialog__title">
            <div class="title">{{ dialogTitle }}</div>
            <div class="subTitle" v-if="dialogSubTitle">（{{dialogSubTitle}}）</div>
        </div>
        <template v-if="type != 'custom'">
            <ec-table class="dialog-url-table" :data="tableData" :default-sort="{prop: 'id', order: 'ascending'}" :row-style="isShowTr" :element-loading-text="$t('lang.loading')" height="441" style="width: 100%">
                <ec-table-column v-for="(column,index) in defaultColumns" :key="index" :label="column.text" :width="column.text == 'ID' ? 100 : ''">
                    <template slot-scope="props">
                        {{props.row[column.field]}}
                    </template>
                </ec-table-column>
                <ec-table-column :label="$t('lang.operation')" width="100" align="right">
                    <template slot-scope="scope">
                        <ec-button size="mini" type="text" @click="handleSelect(scope.row)">{{$t('lang.choose')}}</ec-button>
                    </template>
                </ec-table-column>
            </ec-table>
            <div slot="footer" class="dialog-footer">
                <pagination :page-size="pageSize" :current-page="currentPage" :total="total" @current-change="handleCurrentChange" layout="total, prev, pager, next">
                </pagination>
            </div>
        </template>
        <template v-else>
            <div class="form-group">
                <label class="group-l">{{$t('lang.label_designation')}}</label>
                <div class="group-r">
                    <ec-input size="small" v-model="sCustomName" :placeholder="$t('lang.enter_designation')"></ec-input>
                </div>
            </div>
            <div class="form-group">
                <label class="group-l">{{$t('lang.label_h5_url')}}</label>
                <div class="group-r">
                    <ec-input size="small" v-model="sCustomUrl" :placeholder="$t('lang.add_url')"></ec-input>
                </div>
            </div>
            <div class="form-group" v-if="device == 'app'">
                <label class="group-l">{{$t('lang.label_app_url')}}</label>
                <div class="group-r">
                    <ec-input size="small" v-model="sCustomUrlApp" :placeholder="$t('lang.add_url')"></ec-input>
                </div>
            </div>
            <div class="form-group" v-if="device == 'wxapp'">
                <label class="group-l">{{$t('lang.label_applet_url')}}</label>
                <div class="group-r">
                    <ec-input size="small" v-model="sCustomUrlAppLet" :placeholder="$t('lang.add_url')"></ec-input>
                </div>
            </div>
            <div slot="footer" class="dialog-footer">
                <ec-button type="primary" @click.stop="trimCustom">{{$t('lang.confirm')}}</ec-button>
            </div>
        </template>
    </ec-dialog>
</template>

<script>
// mapActions mapState
import {
    mapActions,
    mapState
} from 'vuex'

// third party components
import {
    Table,
    TableColumn,
    Button,
    Loading,
    Dialog,
    Pagination,
    Input
} from 'element-ui'

export default {
    name: "dialog-url",
    props: {
        tableData: {},
    },
    components: {
        "EcTable": Table,
        "EcTableColumn": TableColumn,
        "EcButton": Button,
        "EcDialog": Dialog,
        "EcInput": Input,
        Loading,
        Pagination
    },
    data() {
        return {
            defaultColumns: [{
                text: 'ID',
                field: 'cat_id'
            }, {
                text: this.$t('lang.designation'),
                field: 'cat_name'
            }],
            bThisShowDialog: false,
            sCustomUrl: "",
            sCustomUrlApp: "",
            sCustomUrlAppLet:"",
            sCustomName: "",
            device: window.shopInfo.device // device 设备  h5 app wxapp
        }
    },
    beforeCreate() { },
    mounted() {
    },
    methods: {
        isShowTr(row, index) {
        },
        handleCurrentChange(val) { //分页
            this.$store.dispatch('setDialogUrl', {
                bShowDialog: true,
                type: this.type,
                currentPage: val,
                pageSize: this.pageSize,
                modulesIndex: this.modulesIndex,
                listIndex: this.listIndex,
                dialogTitle: this.dialogTitle
            })
        },
        handleSelect(o) {
            this.$store.dispatch('setModulesUrl', {
                dialogTitle: this.dialogTitle,
                urlName: o.cat_name,
                url: o.url,
                appPage: o.app_page,
                appletPage: o.applet_page
            })
            this.thisClose()
        },
        trimCustom() {
            this.$store.dispatch('setModulesUrl', {
                dialogTitle: this.dialogTitle,
                urlName: this.sCustomName,
                url: this.sCustomUrl,
                appPage: this.sCustomUrlApp,
                appletPage: this.sCustomUrlAppLet
            })
            this.thisClose()
        },
        thisClose() { //关闭
            this.$store.dispatch('onOffDialogUrl', {
                bShowDialog: false
            })
        }
    },
    computed: {
        ...mapState({
            bShowDialog: state => state.dialogUrl.settingType.bShowDialog,
            type: state => state.dialogUrl.settingType.type,
            total: state => Number(state.dialogUrl.settingType.total),
            pageSize: state => Number(state.dialogUrl.settingType.pageSize),
            currentPage: state => Number(state.dialogUrl.settingType.currentPage),
            modulesIndex: state => Number(state.dialogUrl.settingType.modulesIndex),
            listIndex: state => Number(state.dialogUrl.settingType.listIndex),
            dialogTitle: state => state.dialogUrl.settingType.dialogTitle
        }),
        dialogSubTitle(){
            let hint = ''

            if(this.type == 'function'){
                hint = '“Фукция беті”Микро App қазірше“Бренд、Достық” қолдамайды'
            }else if(this.type == 'activity'){
                hint = '“Маркетинг”Микро App қазірше“Пакетті” қолдамайды'
            }else if(this.type == 'article'){
                hint = '“Мақала”Микро App қазірше қолдамайды'
            }

            return hint
        }
    },
    watch: {
        bShowDialog(val, oldVal) {
            this.bThisShowDialog = val

            if (val && this.type == "custom") {
                let list = this.$store.state.modules[this.modulesIndex].data.list[this.listIndex]
                console.log(list)
                this.sCustomName = list.urlName
                this.sCustomUrl = list.url
                this.sCustomUrlApp = list.appPage
                this.sCustomUrlAppLet = list.appletPage
            }
        }
    }
}
</script>

<style lang="scss" scoped>
.el-dialog__title{ display: flex; }
.el-dialog__title .subTitle{ font-size: 14px; color: #f92028; }
</style>