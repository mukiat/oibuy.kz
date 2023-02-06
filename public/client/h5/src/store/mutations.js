import {
    SET_MODULE_INFO,
    SET_PAGE_LIST,
    ADD_PAGE,
    UPDATE_PAGE,
    REMOVE_PAGE,
    SEARCH_PAGE_LIST,
    REMOVE_MENU_URL,
    UPDATE_PAGE_PIC,
    REGION,
    IMG_VERIFY,
    SET_POSITION,
    UPDATE_SCROLLPICK_OPEN
} from './mutation-type.js'

export default {
    [SET_MODULE_INFO](state, o) {
        if (o.moduleData == "" || !o.moduleData) {
            o.moduleData = "[]"
        }
        
        localStorage.setItem("modules", o.moduleData)
        
        if(window.shopInfo.type == 'web'){
            if(o.type == 'index'){
                state.modules = JSON.parse(o.moduleData)
            }else if(o.type == 'store'){
                state.modulesShop = JSON.parse(o.moduleData)
            }else if(o.type == 'topic'){
                state.modulesTopic = JSON.parse(o.moduleData)
            }
        }else{
            state.modules = JSON.parse(o.moduleData)
        }

        // console.log(state.modules,1)
        // console.log(state.modulesShop,2)
        // console.log(state.modulesTopic,3)

        state.pageSetting.title = o.moduleTitle
        state.pageSetting.modulesId = o.id
        state.pageSetting.type = o.type
        state.pageSetting.default = o.default
    },
    [SET_PAGE_LIST](state, o) {
        state.pageList = o.pageList
        state.customPageList = o.pageList.filter(item => {
            return item.default == 0
        })
    },
    [UPDATE_PAGE](state, o) {
        let pageListLen = state.pageList.length
        for (let i = 0; i < pageListLen; i++) {
            if (state.pageList[i].id == o.id) {
                state.pageList[i].title = o.title
                state.pageList[i].thumb_pic = o.thumb_pic
                break
            }
        }
    },
    [ADD_PAGE](state, o) {
        state.customPageList.unshift(o)
        state.pageList.unshift(o)
    },
    [REMOVE_PAGE](state, o) {
        let pageListLen = state.customPageList.length
        for (let i = 0; i < pageListLen; i++) {
            if (state.customPageList[i].id == o.id) {
                state.customPageList.splice(i, 1)
                break
            }
        }
    },
    [SEARCH_PAGE_LIST](state, o) {
        let aFilterPageList = state.pageList.filter(item => {
            return item.title == "" ? item.default == o.default : item.default == o.default && item.title.toLowerCase().includes(o.title.toLowerCase())
        })
        if (o.default == 0) {
            state.customPageList = aFilterPageList
        }
    },
    [REMOVE_MENU_URL](state, o) {
        let list = state.modules[o.modulesIndex].data.list[o.listIndex]
        list.url = ""
        list.urlCatetory = ""
        list.urlName = ""
    },
    [UPDATE_PAGE_PIC](state,o){
        let pageLength = state.pageList.length
        for(let i=0; i<pageLength; i++){
            if(state.pageList[i].id == o.id){
                state.pageList[i].pic = o.pic
                break
            }
        }
    },
    [REGION](state,o){
        if(o.status != true){
            switch(o.level){
                case 1:
                    state.region.data.provinceData = o.data
                    break
                case 2:
                    state.region.data.cityDate = o.data
                    break
                case 3:
                    state.region.data.districtDate = o.data
                    break
                case 4:
                    state.region.data.streetDate = o.data
                    break
                default:
                    break
            }

            state.region.id = o.id
            state.region.level = o.data[0].level
        }

        state.region.status = false
    },
    [IMG_VERIFY](state,o){
        state.imgVerify.captcha = o.data.captcha
        state.imgVerify.client = o.data.client
    },
    [SET_POSITION](state,{o,res}){
        let data = res.data

        let itemsBak = {
            province:{ id:data.province_id,name:data.province },
            city:{ id:data.city_id,name:data.city },
            district:{ id:data.district_id, name:data.district},
            street: {id:'',name:''},
            postion:{ lat:o.lat,lng:o.lng }
        }

        itemsBak.regionSplic = data.province + ' ' + data.city + ' ' + data.district;

        //设置本地地区存储
        localStorage.setItem('userRegion',JSON.stringify(itemsBak))

        state.userRegion = itemsBak
    },
    [UPDATE_SCROLLPICK_OPEN](state,{rootState, o}){
        rootState.scrollPickOpen = o.type
    }
}