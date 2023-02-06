import {
    ARTICLE_CATE_LIST,
	ARTICLE_LIST,
	ARTICLE_DETAIL,
	ARTICLE_COMMENT_LIST
} from '../mutation-type.js'

import {
    apiArticle
} from '@/config'

const state = {
    articleCateList:[],
	articleList:[],
	articleDetail:[],
	articleCommentList:[],
}
const mutations = {
    [ARTICLE_CATE_LIST](state,o){
		state.articleCateList = o.data
	},
	[ARTICLE_LIST](state,o){
		if(o.page == 1){
			state.articleList = o.data
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.articleList.push(o.data[i])
				}
			}else{
				state.articleList.push('')
			}
		}
	},
	[ARTICLE_DETAIL](state,o){
		state.articleDetail = o.data
	},
	[ARTICLE_COMMENT_LIST](state,o){
		if(o.page == 1){
			state.articleCommentList = o.data
		}else{
			if(o.data.length != 0){
				for(let i= 0;i<o.data.length;i++){
					state.articleCommentList.push(o.data[i])
				}
			}else{
				state.articleCommentList.push('')
			}
		}
	},
}
const actions = {
    setArticleCate({commit}, o) {
		apiArticle.getArticleCate(o).then(res => {
			commit(ARTICLE_CATE_LIST, res)
		})
	},
	setArticleList({commit}, o) {
		apiArticle.getArticleList(o).then(res => {
			commit(ARTICLE_LIST, res)
		})
	},
	setArticleDetail({commit}, o) {
		apiArticle.getArticleDetail(o).then(res => {
			commit(ARTICLE_DETAIL, res)
		})
	},
	setArticleDetail2({commit}, o) {
		return apiArticle.getArticleDetail(o)
	},
	setActicleComment({commit}, o){
		return apiArticle.getActicleComment(o)
	},
	setActicleCommentList({ commit }, o){
		apiArticle.getActicleCommentList(o).then(res=>{
			commit(ARTICLE_COMMENT_LIST,res)
		})
	},
	setActicleCommentLike({ commit }, o){
		return apiArticle.getActicleCommentLike(o)
	},
}
export default {
    state,
    mutations,
    actions,
}
