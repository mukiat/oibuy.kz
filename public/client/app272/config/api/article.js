import * as localConfig from '../local/config'
import request from '@/common/request.js'

const webUrl = localConfig.websiteUrl

/* 文章分类 */
function getArticleCate(o) {
    return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/article',o).then(res=>{
			reslove(res)
		})
    })
}
/* 文章列表 */
function getArticleList(o) {
    return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/article/list', o).then(res=>{
			reslove({
				data:res.data,
				page:o.page,
				size:o.size
			})
		})
    })
}
/* 文章详情 */
function getArticleDetail(o) {
    return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/article/show', o).then(res=>{
			reslove(res)
		})
    })
}
/* 添加文章评论 */
function getActicleComment(o) {
    return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/article/comment',o).then(res=>{
			reslove(res)
		})
    })
}
/* 文章评论列表 */
function getActicleCommentList(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/article/commentlist', o).then(res=>{
			reslove({
				data:res.data,
				page:o.page,
				size:o.size
			})
		})
    })
}
/* 文章评论点赞 */
function getActicleCommentLike(o) {
    return new Promise((reslove, reject) => {
		request.get(webUrl + '/api/article/like', o).then(res=>{
			reslove(res)
		})
    })
}

export default {
	getArticleCate,
	getArticleList,
	getArticleDetail,
	getActicleComment,
	getActicleCommentList,
	getActicleCommentLike,
}
