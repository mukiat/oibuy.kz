import qs from 'qs'
import axios from 'axios'

async function getCategoryLists(o,oF){
    let url = '';

    if(o.id){
        url = `${window.ROOT_URL}api/catalog/list/` + o.id
    }else{
        url = `${window.ROOT_URL}api/catalog/list`
    }
    axios.get(url).then(res => {
        if(res.data.status ==='success'){
            oF.getCategoryList({
                data : res.data.data,
                index : o.index,
                id:o.id
            })
        }
    }).catch(err => {
        console.log(err);
    })
}

async function getCategoryInfo(o){
	return new Promise((reslove, reject) => {
        axios.get(`${window.ROOT_URL}api/catalog/` + o.id + `/detail`).then(res => {
        	reslove(res.data)
        }).catch(err => {
        	console.error(err)
        })
	})
}

function getGoodsLists(o,oF){
    axios.post(`${window.ROOT_URL}api/catalog/goodslist`, qs.stringify(o)).then(res => {
        if(res.data.status ==='success'){
            oF.getGoodsList({
                data:res.data.data,
                size:o.size,
                page:o.page
            })
        }
    }).catch(err => {
        console.log(err);
    })
}

export default{
	getCategoryLists,
	getCategoryInfo,
    getGoodsLists
}
