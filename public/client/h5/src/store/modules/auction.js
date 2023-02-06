import {
    SET_AUCTION_INDEX,
    SET_AUCTION_GOODS,
    SET_AUCTION_LOG
} from '../mutation-type.js'

import {
    apiAuction
} from '@/config'

const state = {
    status: '',
    auctionIndexData:[],
    auctionGoodsData:{
        goods_img:[],
        auction:Object,
        auction_goods:Object,
    },
    auctionLogData:[],
    auctionBidData:[],
    auctionBuyData:[],
}
const mutations = {
    [SET_AUCTION_INDEX](state, o) {
        if(o.page == 1){
			state.auctionIndexData = o.data
		}else{
            if(o.data.length != 0){
    			for(let i= 0;i<o.data.length;i++){
    				state.auctionIndexData.push(o.data[i])
    			}
            }else{
                state.auctionIndexData.push('')
            }
		}
    },
    [SET_AUCTION_GOODS](state, o) {
        state.auctionGoodsData = o.data;
    },
    [SET_AUCTION_LOG](state, o) {
        state.auctionLogData = o.data;
    },
}
const actions = {
    setAuctionIndex({commit}, o) {
        apiAuction.auctionIndex(o).then(res => {   
            commit(SET_AUCTION_INDEX, res)
        })
    },
    setAuctionGoods({commit}, o) {
        apiAuction.auctionGoods(o).then(res => {   
            commit(SET_AUCTION_GOODS, res)
        })
    },
    setAuctionLog({commit}, o) {
        apiAuction.auctionLog(o).then(res => {   
            commit(SET_AUCTION_LOG, res)
        })
    },
    setAuctionBid({commit}, o) {
        return apiAuction.auctionBid(o)
    },
    setAuctionBuy({commit}, o) {
        return apiAuction.auctionBuy(o)
    },


}
export default {
    state,
    mutations,
    actions,
}
