import * as localConfig from '../local/config'
import request from '@/common/request.js'

const webUrl = localConfig.websiteUrl

function getLogins(o){
	return new Promise((resolve, reject) => {
		uni.request({
			url: webUrl + '/api/user/login',
			method: 'POST',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				if(res.data.status ==='success'){
					uni.showToast({title:'登录成功',icon:'success'});
					if(o.delta){
						resolve({
							data:res.data.data,
							delta:o.delta
						})
					}else{
						resolve(res.data)
					}
				}else{
					uni.showToast({title:res.data.errors.message,icon:'none'});
				}
			}
		})
	})
}

/* 注册 */
function getRegister(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/user/fast-login',
			method: 'POST',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}

/* 获取微信授权登录 */
function getCallback(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/oauth/callback',o).then(res=>{
			reslove(res)
		})
	})
}
/* 绑定会员 */
function getBindRegister(o){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/oauth/bind_register',o).then(res=>{
			reslove(res)
		})
	})
}

/* 获取会员id(userId) */
function getUserid(){
	return new Promise((reslove, reject) => {
		request.post(webUrl + '/api/user/get-userid').then(res=>{
			reslove(res)
		})
	})
}

/* 用户中心首页 数据 */
function getProfiles(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/user/profile',
			method: 'GET',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}

/* 修改用户资料 */
function getUpdateProfile(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/user/profile',
			method: 'PUT',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}

/* 修改头像 */
function getUpdateAvatar(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/user/avatar',
			method: 'POST',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}

/* 收货地址列表 */
function getAddress(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/address',
			method: 'GET',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}

/* 设置默认收货地址 */
function getAddressDefault(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/address/default',
			method: 'POST',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data.data)
			}
		})
	})
}

/* 添加收货地址 */
function getAddressAdd(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/address/store',
			method: 'POST',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}

/* 删除收货地址 */
function getAddressDelete(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/address/destroy',
			method: 'GET',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove({
					data:res.data,
					address_id:o.address_id
				})
			}
		})
	})
}

/* 查看收货地址 */
function getAddressInfo(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/address/show',
			method: 'POST',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}
/* 导入微信收货地址 */
function getwxImportAddress(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/address/wximport',
			method: 'POST',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}
/* 结算页面切换收货地址 */
function getChangeConsignee(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/trade/change_consignee',
			method: 'POST',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}
/* 关注店铺列表 */
function getCollectShopList(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/collect/shop',
			method: 'GET',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove({
					data:res.data.data,
					size:o.size,
					page:o.page
				})
			}
		})
	})
}

/* 关注店铺 */
function getCollectShop(o){
	return new Promise((reslove, reject) => {
		let status = o.status == 1 ? 0 : 1
		uni.request({
			url: webUrl + '/api/collect/collectshop',
			method: 'POST',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: ({data:{data}}) => {
				uni.showToast({
					title:data.msg,
					icon:'none'
				})

				reslove({
					data:data,
					ru_id:o.ru_id,
					status:status
				})
			}
		})
	})
}

/* 收藏商品列表 */
function getCollectGoodsList(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/collect/goods',
			method: 'GET',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove({
					data:res.data.data,
					size:o.size,
					page:o.page
				})
			}
		})
	})
}

/* 收藏商品 */
function getCollectGoods(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/collect/collectgoods',
			method: 'POST',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: ({data:{data}}) => {
				uni.showToast({
					title:data.msg,
					icon:'none'
				})

				reslove({
					data:data,
					goods_id:o.goods_id,
					status:data.msg == '已关注' ? 1 : 0
				})
			}
		})
	})
}

/* 订单列表 */
function getOrderList(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/order/list',
			method: 'POST',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				if(res.data.status == 'success'){
					reslove({
						data:res.data.data,
						size:o.size,
						page:o.page
					})
				}
			}
		})
	})
}

/* 订单详情 */
function getOrderDetail(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/order/detail',
			method: 'POST',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				if(res.data.status == 'success'){
					reslove(res.data)
				}
			}
		})
	})
}

/* 取消订单 */
function getOrderHandler(o){
	return new Promise((reslove, reject) => {
		uni.showModal({
			title:'',
			content:'您确定要取消此订单吗？',
			success: (res) => {
				if(res.confirm){
					uni.request({
						url: webUrl + '/api/order/cancel',
						method: 'POST',
						data: o,
						header: {
							'Content-Type': 'application/json',
							'token': uni.getStorageSync('token'),
							'X-Client-Hash':uni.getStorageSync('client_hash')
						},
						success: (res) => {
							if(res.data.status == 'success'){
								reslove(res.data)
							}
						}
					})
				}
			}
		})
	})
}

/* 确认收货 */
function getReceivedOrder(o){
	return new Promise((reslove, reject) => {
		uni.showModal({
			title:'',
			content:'你确认已经收到货物了吗？',
			success: (res) => {
				if(res.confirm){
					uni.request({
						url: webUrl + '/api/order/confirm',
						method: 'POST',
						data: o,
						header: {
							'Content-Type': 'application/json',
							'token': uni.getStorageSync('token'),
							'X-Client-Hash':uni.getStorageSync('client_hash')
						},
						success: (res) => {
							if(res.data.status == 'success'){
								reslove(res.data)
							}
						}
					})
				}
			}
		})
	})
}

/* 再次购买 */
function getbuyAgain(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/trade/buyagain',
			method: 'GET',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				if(res.data.status == 'success'){
					reslove(res.data)
				}
			}
		})
	})
}

/* 延迟收货 */
function getDelayOrder(o){
	return new Promise((reslove, reject) => {
		uni.showModal({
			title:'',
			content:'是否延迟收货此订单？',
			success: (res) => {
				if(res.confirm){
					uni.request({
						url: webUrl + '/api/order/delay',
						method: 'POST',
						data: o,
						header: {
							'Content-Type': 'application/json',
							'token': uni.getStorageSync('token'),
							'X-Client-Hash':uni.getStorageSync('client_hash')
						},
						success: (res) => {
							if(res.data.status == 'success'){
								reslove(res.data)
							}
						}
					})
				}
			}
		})
	})
}

/* 删除订单 */
function getDeleteOrder(o){
	return new Promise((reslove, reject) => {
		uni.showModal({
			title:'',
			content:'是否删除此订单？',
			success: (res) => {
				if(res.confirm){
					uni.request({
						url: webUrl + '/api/order/delete',
						method: 'POST',
						data: o,
						header: {
							'Content-Type': 'application/json',
							'token': uni.getStorageSync('token'),
							'X-Client-Hash':uni.getStorageSync('client_hash')
						},
						success: (res) => {
							if(res.data.status == 'success'){
								reslove(res.data)
							}
						}
					})
				}
			}
		})
	})
}

/* 还原订单 */
function getRestoreOrder(o){
	return new Promise((reslove, reject) => {
		uni.showModal({
			title:'',
			content:'是否还原订单？',
			success: (res) => {
				if(res.confirm){
					uni.request({
						url: webUrl + '/api/order/restore',
						method: 'POST',
						data: o,
						header: {
							'Content-Type': 'application/json',
							'token': uni.getStorageSync('token'),
							'X-Client-Hash':uni.getStorageSync('client_hash')
						},
						success: (res) => {
							if(res.data.status == 'success'){
								reslove(res.data)
							}
						}
					})
				}
			}
		})
	})
}

/* 浏览历史 */
function getHistory() {
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/history',
			method: 'GET',
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}

/* 添加浏览历史 */
function getHistoryAdd(o) {
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/history/store',
			method: 'POST',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}

/* 清空浏览历史 */
function getHistoryDelete(o) {
	return new Promise((reslove, reject) => {
		if(o){
			uni.request({
				url: webUrl + '/api/history/destroy',
				method: 'DELETE',
				data: o,
				header: {
					'Content-Type': 'application/json',
					'token': uni.getStorageSync('token'),
					'X-Client-Hash':uni.getStorageSync('client_hash')
				},
				success: (res) => {
					uni.showToast({
						title:res.data.msg,
						icon:'none'
					})
					reslove({
						data:res.data.code
					})
				}
			})
		}else{
			uni.showModal({
				title:'',
				content:'您确定要清空浏览历史吗？',
				success: (res) => {
					if(res.confirm){
						uni.request({
							url: webUrl + '/api/history/destroy',
							method: 'DELETE',
							header: {
								'Content-Type': 'application/json',
								'token': uni.getStorageSync('token'),
								'X-Client-Hash':uni.getStorageSync('client_hash')
							},
							success: (res) => {
								uni.showToast({
									title:res.data.msg,
									icon:'none'
								})
								reslove({
									data:res.data.code
								})
							}
						})
					}
				}
			})
		}
	})
}

/* 退换货申请列表 */
function getRefoundList(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/refound',
			method: 'GET',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove({
					data:res.data.data,
					size:o.size,
					page:o.page
				})
			}
		})
	})
}

/* 单个订单商品退换货列表 */
function getOrderRefound(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/refound/returngoods',
			method: 'GET',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}

/* 申请退换货 */
function getApplyRefound(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/refound/applyreturn',
			method: 'GET',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}

/* 退换货详情 */
function getReturnDatail(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/refound/returndetail',
			method: 'GET',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}

/* 优惠券 */
async function getUserCoupon(o) {
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/coupon/coupon',
			method: 'GET',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove({
					data:res.data.data,
					size:o.size,
					page:o.page
				})
			}
		})
	})
}

/* 评论晒单列表 */
function getCommentList(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/comment/commentlist',
			method: 'POST',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove({
					data:res.data.data,
					size:o.size,
					page:o.page
				})
			}
		})
	})
}

/* 评论详情商品信息 */
function getAddcomment(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/comment/addcomment',
			method: 'POST',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}

/* 添加商品评论 */
function getAddgoodscomment(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/comment/addgoodscomment',
			method: 'POST',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}

//上传图片
function getMaterial(o){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/user/material',
			method: 'POST',
			data:o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
  	})
}

/* 帮助 */
function getArticleHelp(o){
	return new Promise((reslove, reject) => {
		let url = '';
		if(o.type == 'drphelp'){
			url = webUrl + '/api/drp/news'
		}else{
			url = webUrl + '/api/user/help'
		}

		uni.request({
			url: url,
			method: o.type == 'drphelp' ? 'GET' : 'POST',
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}

/* 资金管理 */
function getAccount(){
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/account',
			method: 'GET',
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				reslove(res.data)
			}
		})
	})
}

//会员中心红包
async function bonusList(o) {
    return new Promise((reslove, reject) => {
    	uni.request({
    		url: webUrl + '/api/bonus/bonus',
    		method: 'GET',
    		data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
    		success: (res) => {
    			reslove({
					data:res.data.data,
					size:o.size,
					page:o.page
				})
    		}
    	})
    })
}

//会员添加红包
function addBonus(o) {
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/bonus/store',
			method: 'POST',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				if(res.data.status == 'success'){
					reslove(res.data)
				}
			}
		})
	})
}

//储值卡列表
function valueCardList(o) {
    return new Promise((reslove, reject) => {
    	uni.request({
    		url: webUrl + '/api/valuecard',
    		method: 'GET',
    		data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
    		success: (res) => {
    			reslove({
					data:res.data.data,
					size:o.size,
					page:o.page
				})
    		}
    	})
    })
}

//领取储值卡
function addValueCard(o) {
    return new Promise((reslove, reject) => {
    	uni.request({
    		url: webUrl + '/api/valuecard/addvaluecard',
    		method: 'POST',
    		data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
    		success: (res) => {
    			if(res.data.status == 'success'){
    				reslove(res.data)
    			}
    		}
    	})
    })
}

//储值卡详情
function valueCardInfo(o) {
    return new Promise((reslove, reject) => {
    	uni.request({
    		url: webUrl + '/api/valuecard/detail',
    		method: 'GET',
    		data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
    		success: (res) => {
    			if(res.data.status == 'success'){
    				reslove(res.data)
    			}
    		}
    	})
    })
}

//充值储值卡
function getDepositValueCard(o) {
    return new Promise((reslove, reject) => {
    	uni.request({
    		url: webUrl + '/api/valuecard/deposit',
    		method: 'POST',
    		data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
    		success: (res) => {
    			if(res.data.status == 'success'){
    				reslove(res.data)
    			}
    		}
    	})
    })
}

// 获取驿站列表数据
function getPostList(o) {
    return new Promise((reslove, reject) => {
    	uni.request({
    		url: webUrl + '/api/cgroup/flow/postlist',
    		method: 'POST',
    		data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
    		success: (res) => {
    			if(res.data.status == 'success'){
    				reslove(res.data)
    			}
    		}
    	})
    })
}

// 获取驿站订单二维码
function getQrCode(o) {
	return new Promise((reslove, reject) => {
		uni.request({
			url: webUrl + '/api/cgroup/order/userPostcode',
			method: 'GET',
			data: o,
			header: {
				'Content-Type': 'application/json',
				'token': uni.getStorageSync('token'),
				'X-Client-Hash':uni.getStorageSync('client_hash')
			},
			success: (res) => {
				if(res.data.status == 'success'){
					reslove(res.data)
				}
			}
		})
	})
}

/* 手机端商家入驻 信息 */
function getMerchants(o){
    return new Promise((reslove, reject) => {
        request.post(`${webUrl}/api/merchants`, o).then(res => {
            reslove(res)
        })
    })
}

/* 手机端商家入驻 入驻须知 */
function getMerchantsGuide(){
    return new Promise((reslove, reject) => {
        request.post(`${webUrl}/api/merchants/guide`).then(res => {
            reslove(res)
        })
    })
}

/* 手机端商家入驻 信息 */
function getMerchantsInfo(o){
    return new Promise((reslove, reject) => {
        request.post(`${webUrl}/api/merchants/applyInfo`, o).then(res => {
            reslove(res)
        })
    })
}

/* 手机端商家入驻 同意协议 */
function getMerchantsAgree(o){
    return new Promise((reslove, reject) => {
        request.post(`${webUrl}/api/merchants/agree`, o).then(res => {
            reslove(res)
        })
    })
}

/* 手机端商家入驻 同意协议 */
function getMerchantsAgreePersonal(o){
    return new Promise((reslove, reject) => {
        request.post(`${webUrl}/api/v4/merchants/agree_personal`, o).then(res => {
            reslove(res)
        })
    })
}

/* 手机端商家入驻 一级分类列表 */
function getMerchantsShop(){
    return new Promise((reslove, reject) => {
        request.get(`${webUrl}/api/merchants/shop`).then(res => {
            reslove(res)
        })
    })
}
/* 手机端商家入驻 获取下级分类 */
function getMerchantsChildCate(o){
    return new Promise((reslove, reject) => {
        request.post(`${webUrl}/api/merchants/get_child_cate`, o).then(res => {
            reslove(res)
        })
    })
}
/* 手机端商家入驻 添加分类 */
function getMerchantsAddCate(o){
    return new Promise((reslove, reject) => {
        request.post(`${webUrl}/api/merchants/add_child_cate`, o).then(res => {
            reslove(res)
        })
    })
}
/* 手机端商家入驻 删除分类 */
function getMerchantsDeleteCate(o){
    return new Promise((reslove, reject) => {
        request.post(`${webUrl}/api/merchants/delete_child_cate`, o).then(res => {
            reslove(res)
        })
    })
}
/* 手机端商家入驻 提交店铺审核 */
function getMerchantsAddShop(o){
    return new Promise((reslove, reject) => {
        request.post(`${webUrl}/api/merchants/add_shop`, o).then(res => {
            reslove(res)
        })
    })
}
/* 手机端商家入驻 等待审核 */
function getMerchantsAudit(){
    return new Promise((reslove, reject) => {
        request.get(`${webUrl}/api/merchants/audit`).then(res => {
            reslove(res)
        })
    })
}

export default{
	getLogins,
	getProfiles,
	getUpdateProfile,
	getCallback,
	getBindRegister,
	getUserid,
	getRegister,
	getUpdateAvatar,
	getAddress,
	getAddressDefault,
	getAddressDelete,
	getAddressInfo,
	getwxImportAddress,
	getAddressAdd,
	getCollectShopList,
	getChangeConsignee,
	getCollectShop,
	getCollectGoodsList,
	getCollectGoods,
	getOrderList,
	getOrderDetail,
	getOrderHandler,
	getReceivedOrder,
	getDelayOrder,
	getbuyAgain,
	getDeleteOrder,
	getRestoreOrder,
	getHistory,
	getHistoryAdd,
	getHistoryDelete,
	getRefoundList,
	getOrderRefound,
	getApplyRefound,
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
	getPostList,
	getQrCode,
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
}
