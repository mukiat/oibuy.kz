//微信
let ua = navigator.userAgent;
//android终端
let isAndroid = ua.indexOf('Android') > -1 || ua.indexOf('Adr') > -1;  
//ios终端
let isiOS = !!ua.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);

function isWeixinBrowser() {
	return (/micromessenger/.test(ua.toLowerCase())) ? true : false;
}

export default {
    isWeixinBrowser,
    isAndroid,
    isiOS
}