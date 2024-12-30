<template>
    <div class="con">
        <template v-if="card_id">
            <div class="vip-buy">
                <div class="purchase-card">
                    <div class="swiper-card">
                        <div class="purchase-card-item" :style="[ drpRightsCard.background_img ? {'background-image': 'url(' + drpRightsCard.background_img + ')'} : {'background-color': drpRightsCard.background_color} ]">
                            <div class="left">
                                <div class="rank">{{drpRightsCard.name}}</div>
                                <span class="period" v-if="drpRightsCard.expiry_type == 'forever'">{{$t('lang.term_of_validity')}}：{{$t('lang.permanence')}}</span>
                                <span class="period" v-else-if="drpRightsCard.expiry_type == 'days'">{{$t('lang.term_of_validity')}}：{{drpRightsCard.expiry_type_format}}</span>
                                <span class="period" v-else-if="drpRightsCard.expiry_type == 'timespan'">{{$t('lang.term_of_validity')}}：{{drpRightsCard.expiry_date_end}}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="protection" v-if="drpRightsCard.user_membership_card_rights_list && drpRightsCard.user_membership_card_rights_list.length > 0">
                    <div class="title">{{$t('lang.vip_protection')}}</div>
                    <div class="items">
                        <div class="item" v-for="(card,cardIndex) in drpRightsCard.user_membership_card_rights_list" :key="cardIndex" @click="protectionHref(card.membership_card_id,cardIndex)">
                            <div class="icon"><div class="img-box"><img :src="card.icon" class="img" /></div></div>
                            <div class="text">{{card.name}}</div>
                        </div>
                    </div>
                </div>
                <div class="head" v-if="drpRightsCard.description">
                    <div class="title">{{$t('lang.vip_card')}}</div>
                    <div class="notice">
                        <p>{{drpRightsCard.description}}</p>
                    </div>
                </div>
            </div>
        </template>
        <template v-else>
            <swiper :options="swiperOption" ref="slideSwiper" class="apply-swiper">
                <swiper-slide v-for="(item,index) in drpChangeCard.list" :key="index">
                    <div class="list" :class="{'list-active':type != 'goods'}">
                        <div class="vip-buy">
                            <div class="purchase-card">
                                <div class="swiper-card">
                                    <div class="purchase-card-item" :style="[ item.background_img ? {'background-image': 'url(' + item.background_img + ')' } : {'background-color': item.background_color } ]">
                                        <div class="left">
                                            <div class="rank">{{item.name}}</div>
                                            <span class="period" v-if="item.expiry_type == 'forever'">{{$t('lang.term_of_validity')}}：{{$t('lang.permanence')}}</span>
                                            <span class="period" v-else-if="item.expiry_type == 'days'">{{$t('lang.term_of_validity')}}：{{item.expiry_type_format}}</span>
                                            <span class="period" v-else-if="item.expiry_type == 'timespan'">{{$t('lang.term_of_validity')}}：{{item.expiry_date_end}}</span>
                                        </div>
                                        <div class="right" v-if="type != 'goods'">{{item.receive_value_arr.value_format}}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="protection" v-if="item.user_membership_card_rights_list && item.user_membership_card_rights_list.length > 0">
                                <div class="title">{{$t('lang.vip_protection')}}</div>
                                <div class="items">
                                    <div class="item" v-for="(card,cardIndex) in item.user_membership_card_rights_list" :key="cardIndex" @click="protectionHref(card.membership_card_id,cardIndex)">
                                        <div class="icon"><div class="img-box"><img :src="card.icon" class="img" /></div></div>
                                        <div class="text">{{card.name}}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="head" v-if="item.description">
                                <div class="title">{{$t('lang.vip_card')}}</div>
                                <div class="notice">
                                    <p>{{item.description}}</p>
                                </div>
                            </div>
                            <div class="bg-color-write" v-if="type == 'integral'">
                                <div class="cell-box">
                                    <div class="cell-title">{{$t('lang.receive_value_integral')}}</div>
                                    <div class="cell-content">{{item.receive_value_arr.value}}</div>
                                </div>
                            </div>
                            <div class="bg-color-write" v-if="type == 'order'">
                                <div class="cell-box">
                                    <div class="cell-title">{{$t('lang.receive_value_order')}}</div>
                                    <div class="cell-content">{{item.receive_value_arr.value_format}}</div>
                                </div>
                            </div>
                            <div v-if="type == 'goods'">
                                <div class="head" v-if="item.description">
                                    <div class="title">{{$t('lang.receive_value_goods')}}</div>
                                </div>
                                <ul class="apply-goods-list clearfix">
                                    <li class="item" v-for="(goodsitem,index2) in item.goods_list" :key="index2">
                                        <div class="item-wapper">
                                            <div class="img"><router-link :to="{name:'goods',params:{id:goodsitem.goods_id}}"><img :src="goodsitem.goods_thumb" alt=""></router-link><div class="tag">{{$t('lang.drp_apply_goods_label')}}</div></div>
                                            <div class="tit"><router-link :to="{name:'goods',params:{id:goodsitem.goods_id}}">{{goodsitem.goods_name}}</router-link></div>
                                            <div class="info">
                                                <div class="price" v-html="goodsitem.shop_price_formated"></div>
                                                <div class="i-btn" v-if="goodsitem.is_buy==0" @click="onAddCartClicked(goodsitem.goods_id,10)">{{$t('lang.drp_apply_btn_2')}}</div>
                                                <div class="i-btn" v-else>{{$t('lang.drp_apply_goods_bought')}}</div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="vip-fixed-bottom" v-if="type != 'goods'">
                            <div class="item article-confirm">
                                <div class="radio-wrap" @click="toggleConfirm"><i class="radio-icon" :class="{'active': confirm}"></i>{{$t('lang.checkout_help_article')}}</div>
                                <span @click="articleHref(drpChangeCard.agreement_id)">{{ drpChangeCard.agreement_article_title }}</span>
                            </div>
                            <div class="item vip-btn" @click="onSubmit">
                                <template v-if="type == 'goods'">
                                    <span>{{$t('lang.drp_apply_btn_1')}}</span>
                                </template>
                                <template v-else-if="type == 'buy'">
                                    <span>{{$t('lang.immediate_pay')}}</span>
                                    <span class="number">{{ item.receive_value_arr.value_format }}</span>
                                </template>
                                <template v-else-if="type == 'free'">
                                    <span>{{ $t('lang.immediately_receive') }}</span>
                                </template>
                                <template v-else>
                                    <span>{{$t('lang.immediately_change')}}</span>
                                </template>
                            </div>
                        </div>
                    </div>
                </swiper-slide>
                <div class="swiper-button swiper-button-next" slot="button-prev"><i class="iconfont icon-more"></i></div>
                <div class="swiper-button swiper-button-prev" slot="button-next"><i class="iconfont icon-back"></i></div>
            </swiper>
        </template>

        <van-popup v-model="applyPopupShow" class="vip-popup">
            <!-- <div class="p-close" @click='closePopup'><van-icon name="clear" /></div> -->
            <div class="p-content">
                <div class="p-icon" v-if="popupStep == 2 || popupStep == 3">
                    <div class="loader04" v-if="popupStep == 2"></div>
                    <div class="p-icon-success" v-if="popupStep == 3"></div>
                </div>
                <p v-html="validMsg"></p>
                <p v-html="validNumber" v-if="validNumber.length > 0" class="number"></p>
                <p v-html="validTip" v-if="validTip.length > 0" :class="{'green': formValid, 'red': !formValid}"></p>
            </div>
            <div class="p-handler">
                <template v-if="popupStep == 1">
                <div class="v-btn" @click='closePopup'>{{$t('lang.close_window')}}</div>
                </template>
                <template v-if="popupStep == 2">
                <div class="v-btn disabled">{{$t('lang.drp_apply_padding')}}</div>
                </template>
                <template v-if="popupStep == 3">
                <div class="v-btn" @click='drpInfoHref'>{{$t('lang.href_drp_center')}}</div>
                </template>
            </div>
        </van-popup>

        <!--初始化loading-->
        <DscLoading :dscLoading="dscLoading"></DscLoading>

        <CommonNav :routerName="routerName">
             <li slot="aloneNav">
                <router-link :to="{name: 'drp'}">
                    <i class="iconfont icon-fenxiao"></i>
                    <p>{{$t('lang.drp_center')}}</p>
                </router-link>
            </li>
        </CommonNav>
    </div>
</template>

<script>
import qs from 'qs'
import { mapState } from 'vuex'
import {
    Button,
    GoodsAction,
    GoodsActionBigBtn,
    GoodsActionMiniBtn,
    Toast,
    Dialog,
    Popup
} from 'vant'

import {
    swiper,
    swiperSlide
} from 'vue-awesome-swiper'

import CommonNav from '@/components/CommonNav'
import DscLoading from '@/components/DscLoading'

let vm = null

export default{
    data(){
        return{
            routerName:'drp',
            confirm: false,
            formValid: true,
            validMsg: '',
            validNumber: '',
            validTip: '',
            popupStep: 1,
            applySuccess: false,
            applyPopupShow: false,
            dscLoading:true,
            index:0,
            swiperOption:{
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                on:{
                    init(){
                        vm.index = this.activeIndex;
                    },
                    slideChange(){
                        vm.index = this.activeIndex;
                    }
                },
                autoHeight:true
            },
            point:this.$route.query.point ? this.$route.query.point : '',
            type:this.$route.query.receive_type,
            card_id:this.$route.query.card_id ? this.$route.query.card_id : '',
        }
    },
    components:{
        [Button.name]:Button,
        [GoodsAction.name] : GoodsAction,
        [GoodsActionBigBtn.name] : GoodsActionBigBtn,
        [GoodsActionMiniBtn.name] : GoodsActionMiniBtn,
        [Toast.name]:Toast,
        [Popup.name]:Popup,
        [Dialog.name]:Dialog,
        swiper,
        swiperSlide,
        CommonNav,
        DscLoading
    },
    computed:{
        ...mapState({
            drpChangeCard: state => state.drp.drpChangeCard,
            drpRightsCard: state => state.drp.drpRightsCard
        }),
        isLogin() {
            return localStorage.getItem('token') == null ? false : true
        },
    },
    created(){
        vm = this;

		// 续费、重新领取、更换
		let apply_status = this.$route.query.apply_status ? this.$route.query.apply_status : null
		let membership_card_id = this.$route.query.membership_card_id ? this.$route.query.membership_card_id : null

        if(this.card_id){
            this.onRightsCard();
        }else{
			if (apply_status && membership_card_id) {
				this.$store.dispatch('setDrpChangeCard',{
				    receive_type:this.type,
					apply_status:apply_status,
				    membership_card_id: membership_card_id
				})
			} else {
                this.$store.dispatch('setDrpChangeCard',{
                    receive_type:this.type
                })
            }

        }
    },
    methods:{
        onSubmit(){
            let that = this
            let o = {
                receive_type: this.type,
                membership_card_id: this.drpChangeCard.list[this.index].id
            }

            if(!this.confirm){
                Toast(this.$t('lang.drp_agreement_please'));
                return false
            }

            if (!this.isLogin) {
                let msg = this.$t('lang.login_user_not')
                this.notLogin(msg)

                return false
            }

			let apply_status = this.$route.query.apply_status ? this.$route.query.apply_status : null

			if (apply_status) {
				o = {
					receive_type: this.type,
					apply_status:apply_status,
					membership_card_id: this.drpChangeCard.list[this.index].id
				}
			}

            if(this.type == 'integral'){
                if (apply_status) {
                	o = {
                		receive_type: this.type,
						apply_status:apply_status,
                		membership_card_id: this.drpChangeCard.list[this.index].id,
						pay_point:this.drpChangeCard.list[this.index].receive_value_arr.value
                	}
                } else {
					o = {
					    receive_type: this.type,
					    membership_card_id: this.drpChangeCard.list[this.index].id,
					    pay_point:this.drpChangeCard.list[this.index].receive_value_arr.value
					}
				}

            }

            if(this.type == 'buy'){
                this.$router.push({
                    name:'drp-done',
                    query:{
						apply_status:apply_status,
                        membership_card_id:this.drpChangeCard.list[this.index].id,
                    }
                })

                return false
            }

			let msg_tips = this.$t('lang.apply_confirm')

			if (apply_status == 'renew') {
				msg_tips = this.$t('lang.apply_confirm_renew')
			}
			if (apply_status == 'change') {
				msg_tips = this.$t('lang.apply_confirm_change')
			}

			Dialog.confirm({
				message:msg_tips,
				className:'text-center'
			}).then(()=>{
				this.$http.post(`${window.ROOT_URL}api/drp/apply`,qs.stringify(o)).then(({data:data})=>{
					if(data.status == 'success'){
						this.validTip = data.data.msg;
						if(data.data.error == 0){
							this.formValid = true;
							this.popupStep = 3;
							setTimeout(function(){
								that.$router.push({
									name:'drp-info'
								})
							}, 2000)
						}else{
							this.formValid = false;
							this.popupStep = 1;
						}

						this.applyPopupShow = true;
					}else{
						if (data.errors) {
							Toast(data.errors.message)
						} else {
							Toast(this.$t('lang.interface_error_reporting'))
						}
					}
				})
			})

        },
        onRightsCard(){
            this.$store.dispatch('setDrpRightsCard',{
                membership_card_id:this.card_id
            })
        },
        toggleConfirm(){
            this.confirm = !this.confirm
        },
        closePopup(){
            this.applyPopupShow = false
        },
        articleHref(id){
            this.$router.push({
                name:'articleDetail',
                params:{
                    id:id
                }
            })
        },
        protectionHref(id,index){
            this.$router.push({
                name:'drp-protection',
                query:{
                    card_id:id,
                    index:index
                }
            })
        },
        drpInfoHref(){
            this.$router.push({
                name:'drp-info'
            })
        },
        onAddCartClicked(goods_id,type){
            this.$store.dispatch('setAddCart', {
                goods_id: goods_id,
                num: 1,
                spec: [],
                rec_type: type
            }).then(res => {
                if (res == true) {
                    this.$router.push({
                        name: 'checkout',
                        query: {
                            rec_type: type
                        }
                    })
                } else {
                    Toast(res.msg)
                }
            })
        },
        notLogin(msg) {
            let url = window.location.href;
            Dialog.confirm({
                message: msg,
                className: 'text-center'
            }).then(() => {
                this.$router.push({
                    name: 'login',
                    query: {
                        redirect: {
                            name: 'drp-apply',
                            url: url,
                            query:{
                                receive_type:this.type
                            }
                        }
                    }
                })
            }).catch(() => {

            })
        },
    },
    watch:{
        drpChangeCard(){
            setTimeout(()=>{
                this.dscLoading = false
            },1000)
        },
        drpRightsCard(){
            setTimeout(()=>{
                this.dscLoading = false
            },1000)
        }
    }
}
</script>
