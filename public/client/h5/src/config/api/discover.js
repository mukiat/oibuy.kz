import qs from 'qs'
import axios from 'axios'

//网友讨论圈首页
function getDiscoverIndex(){
	return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/discover`).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//网友讨论圈列表
function getDiscoverList(o){
	return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/discover/list`,qs.stringify(o)).then(res => {
            reslove({
                data:res.data.data,
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
        axios.post(`${window.ROOT_URL}api/discover/show`,qs.stringify(o)).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//发帖
function getDiscoverCreate(o){
	return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/discover/create`,qs.stringify(o)).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//详情
function getDiscoverDetail(o){
	return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/discover/detail`,qs.stringify(o)).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//列表
function getDiscoverCommentList(o){
	return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/discover/commentlist`,qs.stringify(o)).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//提交评论
function getDiscoverComment(o){
	return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/discover/comment`,qs.stringify(o)).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//我的帖子
function getDiscoverMy(o){
	return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/discover/my`,qs.stringify(o)).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

function getDiscoverMyList(o){
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/discover/mylist`,qs.stringify(o)).then(res => {
            reslove({
                data:res.data.data,
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
        axios.post(`${window.ROOT_URL}api/discover/reply`,qs.stringify(o)).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//点赞
function getDiscoverLike(o){
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/discover/like`,qs.stringify(o)).then(res => {
            reslove(res.data)
        }).catch(err => {
            console.error(err)
        })
    })
}

//删除
function getDiscoverDelete(o){
    return new Promise((reslove, reject) => {
        axios.delete(`${window.ROOT_URL}api/discover/delete`,{params:o}).then(res => {
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
