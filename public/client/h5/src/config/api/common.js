import qs from 'qs'
import axios from 'axios'

/* 获取后台设置shopcofig */
function getShopConfig(){
  return new Promise((reslove, reject) => {
    axios.get(`${window.ROOT_URL}api/shop/config`).then(res => {
      reslove(res)
    }).catch(err => {
      console.error(err)
    })
  })
}

export default{
  getShopConfig
}

