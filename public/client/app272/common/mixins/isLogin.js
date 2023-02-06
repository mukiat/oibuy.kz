export default function isLogin(){
	return uni.getStorageSync('token') == '' ? false : true
}