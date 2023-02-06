import * as localConfig from '../local/config'
import request from '@/common/request.js'

const webUrl = localConfig.websiteUrl
//网友讨论圈首页
function getDiscoverIndex(){
	return new Promise((reslove, reject) => {
        request.post(webUrl + '/api/discover').then(res => {
            reslove(res)
        }).catch(err => {
            console.error(err)
        })
    })
}

//网友讨论圈列表
function getDiscoverList(o){
	return new Promise((reslove, reject) => {
        request.post(webUrl + '/api/discover/list',o).then(res => {
            reslove({
                data:res.data,
                size:o.size,
                page:o.page
            })
        }).catch(err => {
            console.error(err)
        })
    })
}

function getDiscoverShow(o){
	return new Promise((reslove, reject) => {
        request.post(webUrl + '/api/discover/show',o).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//发帖
function getDiscoverCreate(o){
	return new Promise((reslove, reject) => {
        request.post(webUrl + '/api/discover/create',o).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//详情
function getDiscoverDetail(o){
	return new Promise((reslove, reject) => {
        request.post(webUrl + '/api/discover/detail',o).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//列表
function getDiscoverCommentList(o){
	return new Promise((reslove, reject) => {
        request.post(webUrl + '/api/discover/commentlist',o).then(res => {
            reslove(res)
        }).catch(err => {
            console.error(err)
        })
    })
}

//提交评论
function getDiscoverComment(o){
	return new Promise((reslove, reject) => {
        request.post(webUrl + '/api/discover/comment',o).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//我的帖子
function getDiscoverMy(o){
	return new Promise((reslove, reject) => {
        request.post(webUrl + '/api/discover/my',o).then(res => {
            reslove(res)
        }).catch(err => {
            console.error(err)
        })
    })
}

function getDiscoverMyList(o){
    return new Promise((reslove, reject) => {
        request.post(webUrl + '/api/discover/mylist',o).then(res => {
            reslove({
                data:res.data,
                size:o.size,
                page:o.page
            })
        }).catch(err => {
            console.error(err)
        })
    })
}

//回复我的帖子
function getDiscoverReply(o){
	return new Promise((reslove, reject) => {
        request.post(webUrl + '/api/discover/reply',o).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//点赞
function getDiscoverLike(o){
    return new Promise((reslove, reject) => {
        request.post(webUrl + '/api/discover/like',o).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//删除
function getDiscoverDelete(o){
    return new Promise((reslove, reject) => {
        request.delete(webUrl + '/api/discover/delete',o).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}


export default {
	getDiscoverIndex,
	getDiscoverList,
	getDiscoverShow,
	getDiscoverCreate,
	getDiscoverDetail,
	getDiscoverCommentList,
	getDiscoverComment,
	getDiscoverMy,
    getDiscoverMyList,
	getDiscoverReply,
    getDiscoverLike,
    getDiscoverDelete,
}
