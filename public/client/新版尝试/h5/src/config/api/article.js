import qs from 'qs'
import axios from 'axios'

/* 文章分类 */
function getArticleCate(o) {
  return new Promise((reslove, reject) => {
    axios.post(`${window.ROOT_URL}api/article`,qs.stringify(o)).then(res => {
      reslove(res.data)
    })
  })
}

/* 文章列表 */
function getArticleList(o) {
  return new Promise((reslove, reject) => {
    axios.post(`${window.ROOT_URL}api/article/list`,qs.stringify(o)).then(res => {
      reslove({
        data:res.data.data,
        size:o.size,
        page:o.page
      })
    })
  })
}

/* 文章详情 */
function getArticleDetail(o) {
  return new Promise((reslove, reject) => {
    axios.post(`${window.ROOT_URL}api/article/show`,qs.stringify(o)).then(res => {
      reslove(res.data)
    })
  })
}

/* 添加文章评论 */
function getActicleComment(o){
  return new Promise((reslove, reject) => {
    axios.post(`${window.ROOT_URL}api/article/comment`,qs.stringify(o)).then(res => {
      reslove(res.data)
    })
  })
}

/* 文章评论列表 */
function getActicleCommentList(o){
  return new Promise((reslove, reject) => {
    axios.get(`${window.ROOT_URL}api/article/commentlist`,{ params:o }).then(res => {
      reslove({
        data:res.data.data,
        size:o.size,
        page:o.page
      })
    })
  })
}
/* 文章评论点赞 */
function getActicleCommentLike(o){
  return new Promise((reslove, reject) => {
    axios.get(`${window.ROOT_URL}api/article/like`,{ params:o }).then(res => {
      reslove(res.data)
    })
  })
}

/* 文章评论点赞 */
function getWechatMedia(o){
  return new Promise((reslove, reject) => {
    axios.get(`${window.ROOT_URL}api/article/wechat_media`,{ params:o }).then(res => {
      reslove(res.data)
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
  getWechatMedia
}
