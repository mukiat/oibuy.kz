import qs from 'qs'
import axios from 'axios'
import i18n from '@/locales'
import {
	Toast,
	Dialog
} from 'vant'

//众筹首页
async function getCrowdfunding(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/crowd_funding`,{
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}

//众筹列表
async function getCrowdfundingGoods(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/crowd_funding/goods`,{
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
//众筹详情
async function getCrowdfundingShow(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/crowd_funding/show`,{
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}

//众筹属性
async function getCrowdfundingProperty(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/crowd_funding/property`,{
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
//众筹提交
async function getCrowdfundingCheckout(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/crowd_funding/checkout`,{
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
//众筹完成
async function getCrowdfundingDone(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/crowd_funding/done`,{
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
//众筹订单
async function getCrowdfundingOrder(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/crowd_funding/order`,{
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
//众筹订单详情
async function getCrowdfundingDetail(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/crowd_funding/detail`,{
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
//众筹中心
async function getCrowdfundingUser(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/crowd_funding/user`,{
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
//微筹中心-我的支持
async function getCrowdfundingBuy(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/crowd_funding/crowd_buy`,{
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
//微筹中心-添加关注
async function getCrowdfundingFocus(o) {
    return new Promise((reslove, reject) => {
        if(o.status == 1){
            Dialog.confirm({
				message:i18n.t('lang.are_you_sure_cancel_collection'),
				className:'text-center'
			}).then(()=>{
                axios.get(`${window.ROOT_URL}api/crowd_funding/focus`,{params: o}).then(res => {
                    reslove({
						data:res.data.data,
						id:o.goods_id,
						status:0
					})
                })
			})
        }else{
            axios.get(`${window.ROOT_URL}api/crowd_funding/focus`,{params: o}).then(res => {
                Toast.success({
                    duration: 1000,
                    forbidClick: true,
                    loadingType: 'spinner',
                    message: i18n.t('lang.collection_success')
                  });
                reslove({
                    data:res.data.data,
                    id:o.goods_id,
                    status:1
                })
            })
        }
    })
}
//微筹中心-我的关注
async function getCrowdfundingMyFocus(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/crowd_funding/my_focus`,{
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
//话题表单
function getCrowdfundingTopic(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/crowd_funding/topic`, qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}
//话题列表
async function getCrowdfundingMyFocusList(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/crowd_funding/topic_list`,{
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
//详情-风险-主页
async function getCrowdfundingProperties(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/crowd_funding/properties`,{
            params: o
        }).then(res => {
            reslove(res.data)
        })
    })
}
async function getCrowdfundingBest(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/crowd_funding/crowd_best`,{
            params: o
        }).then(res => {
            reslove({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        })
    })
}
export default{
    getCrowdfunding,
    getCrowdfundingGoods,
    getCrowdfundingProperty,
    getCrowdfundingShow,
    getCrowdfundingCheckout,
    getCrowdfundingDone,
    getCrowdfundingOrder,
    getCrowdfundingDetail,
    getCrowdfundingUser,
    getCrowdfundingBuy,
    getCrowdfundingFocus,
    getCrowdfundingMyFocus,
    getCrowdfundingTopic,
    getCrowdfundingMyFocusList,
    getCrowdfundingProperties,
    getCrowdfundingBest
}
