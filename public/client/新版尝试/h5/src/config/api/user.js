import qs from 'qs'
import axios from 'axios'
import i18n from '@/locales'

import {
	Toast,
	Dialog
} from 'vant'

import {
    Loading
} from 'element-ui';

async function getLogins(o,oF){
	axios.post(`${window.ROOT_URL}api/user/login`, qs.stringify(o)).then(res => {
		if(res.data.status ==='success'){
			Toast.success({
			  duration: 0,
			  forbidClick: true,
			  loadingType: 'spinner',
			  message: i18n.t('lang.login_success')
			})

			let timer = setInterval(() => {
			    clearInterval(timer)
			    Toast.clear()
			    oF.getLogin({
					token: res.data.data,
					status: res.data.status
				})
			}, 1000);
		}else{
			Toast(res.data.errors.message)
		}
	}).catch(err => {
		console.log(err);
	})
}

/* 注册 */
function getRegister(o){
	return new Promise((reslove,reject) => {
		axios.post(`${window.ROOT_URL}api/user/fast-login`,qs.stringify(o)).then(res=>{
			reslove(res.data)
		})
	}).catch(err => {
  		console.log(err)
	})
}

/* 获取微信授权登录 */
function getCallback(o){
  return new Promise((reslove,reject) => {
    axios.get(`${window.ROOT_URL}api/oauth/callback`,{params:o}).then(res=>{
      reslove(res.data)
    })
  }).catch(err => {
    console.log(err)
  })
}

/* 绑定会员 */
function getBindRegister(o){
  return new Promise((reslove,reject) => {
    axios.post(`${window.ROOT_URL}api/oauth/bind_register`,qs.stringify(o)).then(res=>{
      reslove(res.data)
    })
  }).catch(err => {
    console.log(err)
  })
}

/* 获取会员id(userId) */
function getUserid(){
  return new Promise((reslove,reject) => {
    axios.post(`${window.ROOT_URL}api/user/get-userid`).then(res=>{
      reslove(res.data)
    })
  }).catch(err => {
      console.log(err)
  })
}

/* 用户中心首页 数据 */
function getProfiles(o){
	return new Promise((reslove, reject) => {
		axios.get(`${window.ROOT_URL}api/user/profile`).then(res =>{
			if(res.data.status == 'success'){
				reslove(res.data)
			}
		}).catch(err => {
			console.log(err)
		})
	})
}

/* 修改用户资料 */
function getUpdateProfile(o){
    return new Promise((reslove, reject) => {
        axios.put(`${window.ROOT_URL}api/user/profile`,qs.stringify(o)).then(res =>{
            reslove(res.data)
        }).catch(err => {
            console.log(err)
        })
    })
}

/* 修改头像 */
function getUpdateAvatar(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/user/avatar`,qs.stringify(o)).then(res =>{
			reslove(res.data)
		}).catch(err => {
			console.log(err)
		})
	})
}

/* 收货地址列表 */
function getAddress(o){
	return new Promise((reslove, reject) => {
		axios.get(`${window.ROOT_URL}api/address`).then(res =>{
      if(res.data.status === 'success'){
  			reslove(res.data)
      }
		}).catch(()=>{
			console.error()
		})
	})
}

/* 设置默认收货地址 */
function getAddressDefault(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/address/default`,qs.stringify(o)).then(res => {
			reslove(res.data.data)
		}).catch(()=>{
			console.error()
		})
	})
}

/* 添加收货地址 */
function getAddressAdd(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/address/store`,qs.stringify(o)).then(res => {
			reslove(res.data)
		}).catch(()=>{
			console.error()
		})
	})
}

/* 删除收货地址 */
function getAddressDelete(o){
	return new Promise((reslove, reject) => {
		Dialog.confirm({
			message:i18n.t('lang.confirm_delete_address')
		}).then(()=>{
			axios.get(`${window.ROOT_URL}api/address/destroy`,{ params:o }).then(res => {
				reslove({
					data:res.data,
					address_id:o.address_id
				})
			})
		}).catch(()=>{
			console.error()
		})
	})
}

/* 查看收货地址 */
function getAddressInfo(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/address/show`,qs.stringify(o)).then(res => {
			reslove(res.data)
		}).catch(()=>{
			console.error()
		})
	})
}

/* 导入微信收货地址 */
function getwxImportAddress(o){
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/address/wximport`,qs.stringify(o)).then(res => {
            reslove(res.data)
        }).catch(()=>{
            console.error()
        })
    })
}

/* 结算页面切换收货地址 */
function getChangeConsignee(o){
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/trade/change_consignee`,qs.stringify(o)).then(res => {
            reslove(res.data)
        }).catch(()=>{
            console.error()
        })
    })
}

/* 关注店铺列表 */
function getCollectShopList(o){
	return new Promise((reslove, reject) =>{
		axios.get(`${window.ROOT_URL}api/collect/shop`,{ params:o }).then(res =>{
			reslove({
				data:res.data.data,
				size:o.size,
      			page:o.page
			})
		}).catch(err=>{
			console.error()
		})
	})
}

/* 关注店铺 */
function getCollectShop(o){
	return new Promise((reslove, reject) =>{
		axios.post(`${window.ROOT_URL}api/collect/collectshop`,qs.stringify(o)).then(({ data:{ data } }) =>{
			let status = o.status == 1 ? 0 : 1

			Toast({
				message: data.msg,
				duration: 600
			})

			reslove({
				data:data,
				ru_id:o.ru_id,
				status:status
			})
	    }).catch(err=>{
	      reject(err)
	    })
	})
}

/* 收藏商品列表 */
function getCollectGoodsList(o){
	return new Promise((reslove, reject) =>{
		axios.get(`${window.ROOT_URL}api/collect/goods`,{ params:o }).then(res =>{
			reslove({
				data:res.data.data,
				size:o.size,
      			page:o.page
			})
		}).catch(err=>{
			reject(err)
		})
	})
}

/* 收藏商品 */
function getCollectGoods(o){
	return new Promise((reslove, reject) =>{
		let status = o.status == 1 ? 0 : 1

		axios.post(`${window.ROOT_URL}api/collect/collectgoods`,qs.stringify(o)).then(({ data:{ data } }) => {
			Toast({
				message: data.msg,
				duration: 600
			})

			reslove({
				data:data,
				goods_id:o.goods_id,
				status:status
			})
	    }).catch(err=>{
			reject(err)
	    })
	})
}

/* 订单列表 */
function getOrderList(o){
	return new Promise((reslove, reject) =>{
		axios.post(`${window.ROOT_URL}api/order/list`,qs.stringify(o)).then(res =>{
			if(res.data.status == 'success'){
				reslove({
					data:res.data.data,
					size:o.size,
          			page:o.page
				})
			}
		}).catch(err=>{
			reject(err)
		})
	})
}

/* 订单详情 */
function getOrderDetail(o){
	return new Promise((reslove, reject) =>{
		Toast.loading({ duration: 0, mask: true, forbidClick: true, message: i18n.t('lang.loading') })
		axios.post(`${window.ROOT_URL}api/order/detail`,qs.stringify(o)).then(res =>{
			if(res.data.status == 'success'){
				reslove(res.data)
				Toast.clear()
			}
		}).catch(err=>{
			reject(err)
		})
	})
}

/* 取消订单 */
function getOrderHandler(o){
	return new Promise((reslove, reject) => {
		Dialog.confirm({
			message:i18n.t('lang.confirm_cancel_order'),
			className:'text-center'
		}).then(()=>{
	    axios.post(`${window.ROOT_URL}api/order/cancel`,qs.stringify(o)).then(res => {
	    	if(res.data.status == 'success'){
	    		reslove(res.data)
	    	}
	    })
    })
  })
}

/* 确认收货 */
function getReceivedOrder(o){
	return new Promise((reslove, reject) => {
		Dialog.confirm({
			message:i18n.t('lang.confirm_received_order'),
			className:'text-center'
		}).then(()=>{
	    axios.post(`${window.ROOT_URL}api/order/confirm`,qs.stringify(o)).then(res => {
	    	if(res.data.status == 'success'){
	    		reslove(res.data)
	    	}
	    })
    })
  })
}

/* 延迟收货 */
function getDelayOrder(o){
	return new Promise((reslove, reject) => {
		Dialog.confirm({
			message:i18n.t('lang.is_delay_order'),
			className:'text-center'
		}).then(()=>{
	    axios.post(`${window.ROOT_URL}api/order/delay`,qs.stringify(o)).then(res => {
	    	if(res.data.status == 'success'){
	    		reslove(res.data)
	    	}
	    })
    })
  })
}

/* 删除订单 */
function getDeleteOrder(o){
	return new Promise((reslove, reject) => {
		Dialog.confirm({
			message:i18n.t('lang.is_delete_order'),
			className:'text-center'
		}).then(()=>{
	    axios.post(`${window.ROOT_URL}api/order/delete`,qs.stringify(o)).then(res => {
	    	if(res.data.status == 'success'){
	    		reslove(res.data)
	    	}
	    })
    })
  })
}

/* 还原订单 */
function getRestoreOrder(o){
    return new Promise((reslove, reject) => {
        Dialog.confirm({
            message:i18n.t('lang.is_restore_order'),
            className:'text-center'
        }).then(()=>{
            axios.post(`${window.ROOT_URL}api/order/restore`,qs.stringify(o)).then(res => {
                if(res.data.status == 'success'){
                    reslove(res.data)
                }
            })
        })
    })
}

/* 浏览历史 */
function getHistory() {
	return new Promise((reslove, reject) => {
		axios.get(`${window.ROOT_URL}api/history`).then(res => {
			reslove(res.data)
		})
	})
}

/* 添加浏览历史 */
function getHistoryAdd(o) {
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/history/store`,qs.stringify(o)).then(res => {
			reslove(res.data)
		})
	})
}

/* 清空浏览历史 */
function getHistoryDelete(o) {
  return new Promise((reslove, reject) => {
        if(o){
            axios.delete(`${window.ROOT_URL}api/history/destroy`,{ params:o }).then(res => {
                Toast.loading({
                  message: res.data.msg,
                  duration:1000
                });
                reslove({
                    data:res.data.code
                })
            })
        }else{
      		Dialog.confirm({
    			message:i18n.t('lang.confirm_history_delete'),
    		}).then(()=>{
    			axios.delete(`${window.ROOT_URL}api/history/destroy`).then(res => {
    				Toast.loading({
    				  message: res.data.msg,
    				  duration:1000
    				});
    				reslove({
    					data:res.data.code
    				})
    			})
    		}).catch(()=>{
    			console.error()
    		})
        }
	})
}

/* 退换货申请列表 */
function getRefoundList(o){
	return new Promise((reslove, reject) => {
    axios.get(`${window.ROOT_URL}api/refound`,{params: o}).then(res => {
    	reslove({
    		data:res.data.data,
			size:o.size,
  			page:o.page
    	})
    })
  })
}

/* 单个订单商品退换货列表 */
function getOrderRefound(o){
	return new Promise((reslove, reject) => {
    axios.get(`${window.ROOT_URL}api/refound/returngoods`,{params: o}).then(res => {
    	reslove(res.data)
    })
  })
}

/* 申请退换货 */
function getApplyRefound(o){
	return new Promise((reslove, reject) => {
    axios.get(`${window.ROOT_URL}api/refound/applyreturn`,{params: o}).then(res => {
    	reslove(res.data)
    })
  })
}

/* 退换货详情 */
function getReturnDatail(o){
	return new Promise((reslove, reject) => {
    axios.get(`${window.ROOT_URL}api/refound/returndetail`,{params: o}).then(res => {
    	reslove(res.data)
    })
  })
}

/* 优惠券 */
async function getUserCoupon(o) {
  return new Promise((reslove, reject) => {
      axios.get(`${window.ROOT_URL}api/coupon/coupon`, {
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

/* 评论晒单列表 */
function getCommentList(o){
	return new Promise((reslove, reject) => {
    axios.post(`${window.ROOT_URL}api/comment/commentlist`,qs.stringify(o)).then(res => {
    	reslove({
          data:res.data.data,
          size:o.size,
          page:o.page
      })
    })
  })
}

/* 评论详情商品信息 */
function getAddcomment(o){
	return new Promise((reslove, reject) => {
    axios.post(`${window.ROOT_URL}api/comment/addcomment`,qs.stringify(o)).then(res => {
    	reslove(res.data)
    })
  })
}

/* 添加商品评论 */
function getAddgoodscomment(o){
	return new Promise((reslove, reject) => {
    axios.post(`${window.ROOT_URL}api/comment/addgoodscomment`,qs.stringify(o)).then(res => {
    	reslove(res.data)
    })
  })
}

//上传图片
function getMaterial(o){
	return new Promise((reslove, reject) => {
		axios.post(`${window.ROOT_URL}api/user/material`,qs.stringify(o)).then(res =>{
			reslove(res.data)
		})
  	})
}

/* 帮助 */
function getArticleHelp(o){
	return new Promise((reslove, reject) => {
        if(o.type == 'drphelp'){
            axios.get(`${window.ROOT_URL}api/drp/news`).then(res => {
                reslove(res.data)
            })
        }else{
            axios.post(`${window.ROOT_URL}api/user/help`).then(res => {
                reslove(res.data)
            })
        }
	})
}

/* 资金管理 */
function getAccount(){
	Toast.loading({ duration: 0, mask: true, forbidClick: true, message: i18n.t('lang.loading') })
	return new Promise((reslove, reject) => {
		axios.get(`${window.ROOT_URL}api/account`).then(res => {
			reslove(res.data)
			Toast.clear()
		})
	})
}

//会员中心红包
async function bonusList(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/bonus/bonus`, {
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

//会员添加红包
function addBonus(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/bonus/store`, qs.stringify(o)).then(res => {
            if(res.data.status == 'success'){
                reslove(res.data)
            }
        })
    })
}

//储值卡列表
function valueCardList(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/valuecard`, {
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

//领取储值卡
function addValueCard(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/valuecard/addvaluecard`, qs.stringify(o)).then(res => {
            if(res.data.status == 'success'){
                reslove(res.data)
            }
        })
    })
}

//储值卡详情
function valueCardInfo(o) {
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/valuecard/detail`, {
            params: o
        }).then(res => {
            if(res.data.status == 'success'){
                reslove(res.data)
            }
        })
    })
}

//充值储值卡
function getDepositValueCard(o) {
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/valuecard/deposit`, qs.stringify(o)).then(res => {
            if(res.data.status == 'success'){
                reslove(res.data)
            }
        })
    })
}

//供应链 采购单列表
function getSupplierOrderList(o){
	return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/orderlist`, {
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

//供应链 采购单列表
function getSupplierAffirmorder(o){
	return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/affirmorder`, {
            params: o
        }).then(res => {
             reslove(res.data)
        })
    })
}

/* 再次购买 */
function getbuyAgain(o){
	return new Promise((reslove, reject) => {
		axios.get(`${window.ROOT_URL}api/trade/buyagain`, {
			params: o
		}).then(res => {
			reslove(res.data)
		})
	})
}

/* 手机端商家入驻 信息 */
function getMerchants(o){
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/merchants`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}

/* 手机端商家入驻 入驻须知 */
function getMerchantsGuide(){
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/merchants/guide`).then(res => {
            reslove(res.data)
        })
    })
}

/* 手机端商家入驻 信息 */
function getMerchantsInfo(o){
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/merchants/applyInfo`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}

/* 手机端商家入驻 同意协议 */
function getMerchantsAgree(o){
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/merchants/agree`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}

/* 手机端商家入驻 同意协议 */
function getMerchantsAgreePersonal(o){
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/v4/merchants/agree_personal`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}

/* 手机端商家入驻 一级分类列表 */
function getMerchantsShop(){
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/merchants/shop`).then(res => {
            reslove(res.data)
        })
    })
}
/* 手机端商家入驻 获取下级分类 */
function getMerchantsChildCate(o){
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/merchants/get_child_cate`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}
/* 手机端商家入驻 添加分类 */
function getMerchantsAddCate(o){
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/merchants/add_child_cate`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}
/* 手机端商家入驻 删除分类 */
function getMerchantsDeleteCate(o){
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/merchants/delete_child_cate`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}
/* 手机端商家入驻 提交店铺审核 */
function getMerchantsAddShop(o){
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/merchants/add_shop`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}
/* 手机端商家入驻 等待审核 */
function getMerchantsAudit(){
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/merchants/audit`).then(res => {
            reslove(res.data)
        })
    })
}
/* 手机端供应商入驻 供应商信息 */
function getSupplierApplyInfo(o){
    return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/suppliers/apply`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}
/* 手机端供应商入驻 提交店铺审核 */
function getSupplierApply(o){
    return new Promise((reslove, reject) => {
        axios.post(`${window.ROOT_URL}api/suppliers/do_apply`,qs.stringify(o)).then(res => {
            reslove(res.data)
        })
    })
}

export default {
	getLogins,
	getRegister,
  getUserid,
  getCallback,
  getBindRegister,
	getProfiles,
  getUpdateProfile,
	getUpdateAvatar,
	getAddress,
	getAddressDefault,
	getAddressDelete,
	getAddressInfo,
  getwxImportAddress,
	getAddressAdd,
  getChangeConsignee,
	getCollectShopList,
	getCollectShop,
	getCollectGoodsList,
	getCollectGoods,
	getOrderList,
	getOrderDetail,
	getOrderHandler,
	getReceivedOrder,
	getDelayOrder,
	getDeleteOrder,
  getRestoreOrder,
	getHistory,
	getHistoryAdd,
	getHistoryDelete,
	getRefoundList,
	getOrderRefound,
	getApplyRefound,
	getReturnDatail,
	getUserCoupon,
	getCommentList,
	getMaterial,
	getAddcomment,
	getAddgoodscomment,
	getArticleHelp,
	getAccount,
	bonusList,
	addBonus,
	valueCardList,
	addValueCard,
	valueCardInfo,
	getDepositValueCard,
	getSupplierOrderList,
	getSupplierAffirmorder,
	getbuyAgain,
  getMerchants,
  getMerchantsGuide,
  getMerchantsInfo,
  getMerchantsAgree,
  getMerchantsAgreePersonal,
  getMerchantsShop,
  getMerchantsChildCate,
  getMerchantsAddCate,
  getMerchantsDeleteCate,
  getMerchantsAddShop,
  getMerchantsAudit,
  getSupplierApplyInfo,
  getSupplierApply
}
