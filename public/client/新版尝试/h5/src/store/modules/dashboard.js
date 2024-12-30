//模块配置信息
import { localConfig } from '@/config'

import Vue from 'vue'

import {
    SET_DOC_CONFIG,
    NAV_ENTER_TAB,
    NAV_LEAVE_TAB,
    ADD_MODULES,
    UPDATE_MODULES,
    REMOVE_MODULES,
    COPY_MODULES,
    REMOVE_ALL_MODULES,
    IS_SHOW_EDIT_COMPONENT,
    SORT_MODULES,
    UPDATE_RADIO_SEL,
    UPDATE_TEXT,
    UPDATE_LIST_TEXT,
    REMOVE_LIST,
    ADD_LIST,
    IS_UPDATE_MODULES,
    UPDATE_SETTING_ACTIVE,
    UPDATE_GLOBAL_BGCOLOR,
    UPDATE_IS_SHOW,
    NAV_HIDDEN_TAB
} from '../mutation-type'

const state = {
    configInfo: {},
    bUpdateModules: false,
}

const mutations = {
    [SET_DOC_CONFIG](state, configInfo) {
        state.configInfo = configInfo
    },
    [NAV_ENTER_TAB](state, index) {
        let menuCommponent = state.configInfo.headerMenu.menuComponent
        menuCommponent.map((o, i) => {
            i == index ? o.isActive = true : o.isActive = false;
        })
    },
    [NAV_LEAVE_TAB](state, index) {
        let menuCommponent = state.configInfo.headerMenu.menuComponent
        menuCommponent[index].isActive = false
    },
    [NAV_HIDDEN_TAB](state) {
        state.configInfo.headerMenu.menuComponent.forEach(item => {
            item.isActive = false
        })
    },
    [ADD_MODULES](state, { rootState, o }) {
        rootState.modules.splice(o.newIndex, 0, o.module)
    },
    [UPDATE_MODULES](state, { rootState, o }) {
        rootState.modules = o.modules
    },
    [SORT_MODULES](state, { rootState, o }) {
        let currModule = rootState.modules[o.modulesIndex],
            sort = o.sort
        rootState.modules.splice(o.modulesIndex, 1)
        if (sort == "up") {
            rootState.modules.splice(o.modulesIndex - 1, 0, currModule)
        } else {
            rootState.modules.splice(o.modulesIndex + 1, 0, currModule)
        }
    },
    [COPY_MODULES](state, { rootState, o }) {
        let currModule = rootState.modules[o.modulesIndex];

        rootState.modules.splice(o.modulesIndex, 0, currModule)
    },
    [REMOVE_MODULES](state, { rootState, o }) {
        rootState.modules.splice(o.modulesIndex, 1)
    },
    [REMOVE_ALL_MODULES](state, rootState) {
        rootState.modules = []
    },
    [IS_SHOW_EDIT_COMPONENT](state, { rootState, o }) {
        let sSetting = rootState.modules[o.modulesIndex].setting;
        '1' == sSetting ? rootState.modules[o.modulesIndex].setting = '0' : rootState.modules[o.modulesIndex].setting = '1'
    },
    [UPDATE_RADIO_SEL](state, { rootState, o }) {
        rootState.modules[o.modulesIndex].data[o.sName] = o.newValue
    },
    [ADD_LIST](state, { rootState, o }) {
        let thisModules = rootState.modules[o.modulesIndex];
        delete o["modulesIndex"]
        thisModules.data.list.push(o)
    },
    [REMOVE_LIST](state, { rootState, o }) {
        rootState.modules[o.modulesIndex].data.list.splice(o.listIndex, 1)
    },
    [UPDATE_TEXT](state, { rootState, o }) {
        let data = rootState.modules[o.modulesIndex].data
        if (!isNaN(o.listIndex)) {
            if(o.infoName == "desc"){
                data.list[o.listIndex].desc = o.newValue
            }else if(o.infoName == 'bgColor'){
                data.list[o.listIndex].bgColor = o.newValue
            }else if(o.infoName == 'url'){
                data.list[o.listIndex].url = o.newValue
            }else if(o.infoName == 'appPage'){
                data.list[o.listIndex].appPage = o.newValue
            }else if(o.infoName == 'appletPage'){
                data.list[o.listIndex].appletPage = o.newValue
            }else{
                data.list[o.listIndex].sort = o.newValue
                data.list.sort((a,b)=>{
                    return a.sort - b.sort
                })
            }
        } else {
            if (!data[o.dataNext]) {
                data = Object.assign({}, data, {
                    allValue: {}
                })
            }

            if (!data[o.dataNext][o.attrName]) {
                data[o.dataNext] = Object.assign({}, data.allValue, {
                    [o.attrName]: o.newValue
                })
                return false
            }
            
            Vue.set(data.allValue, o.attrName, o.newValue)
        }
    },
    [UPDATE_SETTING_ACTIVE](state, { rootState, attrName }) {
        let bActive = state.configInfo.headerMenu[attrName].isActive;
        state.configInfo.headerMenu[attrName].isActive = !bActive;
    },
    [IS_UPDATE_MODULES](state, bUpdateModules) {
        state.bUpdateModules = bUpdateModules
    },
    [UPDATE_GLOBAL_BGCOLOR](state,{ rootState, o }){
        rootState.modules.find((v)=>{
            if(v.globalbg && o.bgColor){
                v.data.allValue.bgColor = o.bgColor
            }
        })
    },
    [UPDATE_IS_SHOW](state,{rootState, o}){
        rootState.modules.find((v)=>{
            if(typeof v.fixed === "undefined"){
                v.isShow = o.type
            }
        })
        
        rootState.topCategoryCatid = o.cat_id
    }
}

const actions = {
    setDocConfig({ commit }) {
        commit(SET_DOC_CONFIG, localConfig.pageInfo)
    },
    navEnterTab({ commit }, index) {
        commit(NAV_ENTER_TAB, index)
    },
    navLeaveTab({ commit }, index) {
        commit(NAV_LEAVE_TAB, index)
    },
    navHiddenTab({ commit }) {
        commit(NAV_HIDDEN_TAB)
    },
    updateModules({ commit, rootState }, o) {
        commit(UPDATE_MODULES, { rootState, o })
    },
    sortModules({ commit, rootState }, o) {
        commit(SORT_MODULES, { rootState, o })
    },
    copyModules({ commit, rootState }, o) {
        commit(COPY_MODULES, { rootState, o })
    },
    removeModules({ commit, rootState }, o) {
        commit(REMOVE_MODULES, { rootState, o })
    },
    removeAllModules({ commit, rootState }) {
        commit(REMOVE_ALL_MODULES, rootState)
    },
    isShowEditComponent({ commit, rootState }, o) {
        commit(IS_SHOW_EDIT_COMPONENT, { rootState, o })
    },
    addModules({ commit, rootState }, o) {
        commit(ADD_MODULES, { rootState, o })
    },
    updateRadioSel({ commit, rootState }, o) {
        commit(UPDATE_RADIO_SEL, { rootState, o })
    },
    updateText({ commit, rootState }, o) {
        commit(UPDATE_TEXT, { rootState, o });
    },
    removeList({ commit, rootState }, o) {
        commit(REMOVE_LIST, { rootState, o })
    },
    addList({ commit, rootState }, o) {
        commit(ADD_LIST, { rootState, o })
    },
    updateSettingActive({ commit, rootState }, attrName) {
        commit(UPDATE_SETTING_ACTIVE, { rootState, attrName })
    },
    isUpdateModules({ commit }, bUpdateModules) {
        commit(IS_UPDATE_MODULES, bUpdateModules)
    },
    //新开发轮播颜色切换
    updateGlobalBgColor({ commit,rootState }, o){
        commit(UPDATE_GLOBAL_BGCOLOR, { rootState,o })
    },
    //新开发首页顶级分类切换
    updateIsShow({ commit,rootState}, o){
        commit(UPDATE_IS_SHOW, { rootState,o })  
    }
}

const getters = {

}

export default {
    state,
    mutations,
    actions,
    getters
}