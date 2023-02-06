import {
    SET_MODULE_INFO,
    SET_PAGE_LIST,
    UPDATE_PAGE,
    REMOVE_PAGE,
    ADD_PAGE,
    REMOVE_MENU_URL,
    SEARCH_PAGE_LIST,
    UPDATE_PAGE_PIC,
    REGION,
    IMG_VERIFY,
    SET_POSITION,
    UPDATE_SCROLLPICK_OPEN
} from './mutation-type.js'

import {
    apiModules,
    apiPageList
} from '@/config'


export default {
    async setPageList({ commit }, o) {
        return apiPageList.getPageList(o)
    },
    setModuleInfo({ commit }, o) {
        apiModules.getModules(o, {
            getModule(o) {
                commit(SET_MODULE_INFO, o)
            }
        })
    },
    async initPageModule({ dispatch, commit }, o) {
        let defaultModuleId = -1
        let modulesTitle = ""
        await dispatch('setPageList', o).then(({ data: { data } }) => {
            data.forEach(item => {
                if (!item.pic) item.pic = ""
                if (!item.thumb_pic) item.thumb_pic = ""
            })
            
            commit(SET_PAGE_LIST, {
                pageList: data
            })
            let pageListLen = data.length
            if (pageListLen > 0 && !o.bTopId) {
                for (let i = 0; i < pageListLen; i++) {
                    if ((data[i].type == o.type && data[i].default == 1)) {
                        defaultModuleId = data[i].id
                        break
                    }
                }
            }
        })
       
        if (defaultModuleId > -1) {
            dispatch('setModuleInfo', {
                id: defaultModuleId,
                type:o.type
            })
        }
    },
    updatePage({ commit }, o) {
        commit(UPDATE_PAGE, o)
    },
    removePage({ commit }, o) {
        commit(REMOVE_PAGE, o)
    },
    addPage({ commit }, o) {
        commit(ADD_PAGE, o)
    },
    isEqualTitle({ commit, rootState }, o) {
        let aPageList = rootState.pageList
        let pageListLen = aPageList.length
        let trimValue = o.value.trims()
        for (let i = 0; i < pageListLen; i++) {
            if (aPageList[i].id == o.id && aPageList[i].title == trimValue) {
                return true
            }
        }
        return false
    },
    searchPageList({ commit }, o) {
        commit(SEARCH_PAGE_LIST, o)
    },
    removeMenuUrl({ commit }, o) {
        commit(REMOVE_MENU_URL, o)
    },
    updatePagePic({ commit }, o) {
        commit(UPDATE_PAGE_PIC, o)
    },
    setConversionModules({commit},o){
        commit(SET_MODULE_INFO,o)
    },
    setRegion({ commit }, o){
        apiModules.getRegion(o).then(res=>{
            commit(REGION, res)
        })
    },
    setImgVerify({ commit }, o){
        apiModules.getImgVerify(o).then(res=>{
            commit(IMG_VERIFY, res)
        })
    },
    async setSendVerify({ commit }, o){
        return apiModules.getSendVerify(o)
    },
    async setSmsVerify({ commit }, o){
        return apiModules.getSmsVerify(o)
    },
    setPosition({ commit }, o){
        return apiModules.getPosition(o)
    },
    updateScrollPickOpen({ commit,rootState}, o){
        commit(UPDATE_SCROLLPICK_OPEN, { rootState,o })  
    }
}