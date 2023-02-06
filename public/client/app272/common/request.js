import Fly from 'flyio/dist/npm/wx'

const request = new Fly()
const errorPrompt = (err) => {
    // #ifdef MP-WEIXIN
    wx.showToast({
        title: err.message || 'fetch data error.',
        icon: 'none'
    })
    // #endif

    // #ifdef APP-PLUS
    uni.showToast({
        title: err.message || 'fetch data error.',
        icon: 'none'
    })
    // #endif
}

request.interceptors.request.use((request) => {
    request.timeout = 30000;
    request.headers = {
        'Content-Type': 'application/json',
        'token': uni.getStorageSync('token'),
        'X-Client-Hash': uni.getStorageSync('client_hash')
    }
    return request;
})

request.interceptors.response.use((response, promise) => {
    let client_hash = '';
    for (var k in response.headers) {
        if (k.toLowerCase() === "x-client-hash") {
            client_hash = response.headers[k][0]
        }
    }
    if (client_hash) {
        uni.setStorageSync('client_hash', client_hash);
    }
    if (response.data.status === 'failed') {
        switch (response.data.errors.code) {
            case 12:
                uni.showToast({
                    title: response.data.errors.message ? response.data.errors.message : '用户未登录',
                    icon: 'none'
                })
				
				uni.removeStorageSync("token");
				
                setTimeout(() => {
                    uni.navigateTo({
                        url: '/pagesB/login/login?delta=1'
                    })
                }, 200)
                break
            case 404:
                console.log("暂无记录")
                break
            case 502:
                console.log(response.data.errors.message)
                break
            case 506:
                console.log("存在违禁词")
                break
            case 102:
                uni.showToast({
                    title: response.data.errors.message ? response.data.errors.message : '用户数据错误，请重新登录',
                    icon: 'none'
                })
				
				uni.removeStorageSync("token");
				
				setTimeout(() => {
				    uni.navigateTo({
				        url: '/pagesB/login/login?delta=1'
				    })
				}, 200)
                break
            default:
        }
    }

    return promise.resolve(response.data)
}, (err, promise) => {
    errorPrompt(err)
    return promise.reject(err)
})

export default request