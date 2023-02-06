<template>
    <div class="con">
        <div class="con_main goods bargain-detail cro-detail">
            <div class="dis-box tab-common nav f-05 bg-color-write text-center">
                <div class="box-flex">
                    <div class="item text-right" :class="{'active':filter === 'goods'}" @click="handleFilter('goods')">
                        <span>{{$t('lang.goods')}}</span>
                    </div>
                </div>
                <div class="box-flex">
                    <div class="item" :class="{'active':filter === 'detail'}" @click="handleFilter('detail')">
                        <span>{{$t('lang.detail')}}</span>
                    </div>
                </div>
                <div class="box-flex">
                    <div class="item text-left" :class="{'active':filter === 'topic'}" @click="handleFilter('topic')">
                        <span>{{$t('lang.theme')}}</span>
                    </div>
                </div>
            </div>
            <!--商品-->
            <div class="tabCon m-top40" v-if="filter =='goods'">
                <!--商品模块-->
                <div class="cont-max-height" v-show="filter == 'goods'">
                    <!--轮播图-->
                    <van-swipe :autoplay="3000" class="swipe" :height="swipe_height">
                        <van-swipe-item>
                            <img class="imgalt" :src="crowdShowData.info.title_img" />
                        </van-swipe-item>
                    </van-swipe>
                    <div class="dis-box detail-user bg-color-write padding-all">
                        <div class="left">
                            <img class="img" :src="crowdShowData.user_info.img" />
                        </div>
                        <div class="center name color-red p-l05">{{crowdShowData.user_info.name}}</div>
                        <div class="box-flex right" @click="onChat(0,0)">
                            <span class="f-04 tag-gradients-color br-100 color-white fr">{{$t('lang.consult')}}</span>
                        </div>
                    </div>
                    <!--title-->
                    <div class="cont-box m-top08">
                        <div class="title bg-color-write  padding-all">
                            <div class="f-05 color-3 f-weight">{{crowdShowData.info.title}}</div>
                            <p class="m-top06 of-hidden">
                                <span class="fr color-9 f-03">{{$t('lang.label_residue')}}
                                    <label class="color-red">{{crowdShowData.info.shenyu_time}}</label>{{$t('lang.tian')}}</span>
                            </p>
                            <div class="ect-progress dis-box m-top08">
                                <p class="wrap box-flex">
                                    <span class="bar" :style="{width:crowdShowData.info.baifen_bi + '%'}">
                                        <i class="color"></i>
                                    </span>
                                </p>
                                <p class="txt">{{crowdShowData.info.baifen_bi}}%</p>
                            </div>
                            <van-row class="text-center color-9 f-02 m-top08">
                                <van-col span="8">
                                    <i class="iconfont icon-iconfontqizhi my-img-size col-9"></i>
                                    <p>{{$t('lang.target_funds')}}</p>
                                    <span class="color-red f-04">{{crowdShowData.info.amount}}</span>{{$t('lang.yuan')}}
                                </van-col>
                                <van-col span="8">
                                    <i class="iconfont icon-jinqian my-img-size col-9"></i>
                                    <p>{{$t('lang.crowdfunding_fund')}}</p>
                                    <span class="color-red f-04">{{crowdShowData.info.join_money}}</span>{{$t('lang.yuan')}}
                                </van-col>
                                <van-col span="8">
                                    <i class="iconfont icon-geren my-img-size col-9"></i>
                                    <p>{{$t('lang.support_number')}}</p>
                                    <span class="color-red f-04">{{crowdShowData.info.join_num}}</span>{{$t('lang.ren')}}
                                </van-col>
                            </van-row>
                        </div>
                    </div>
                    <section class="padding-all   bg-color-write border-t-common" @click="detailClick">
                            <div class="text-center color-red f-04">
                                <label>{{$t('lang.view_project_detail')}}</label>
                                <i class="iconfont icon-jiantouyou"></i>
                            </div>
                    </section>
                    <van-cell-group class="m-top08 f-04">
                        <van-cell :title="$t('lang.package_return')" />
                    </van-cell-group>
                    <div class="dis-box detail-scheme bg-color-write" v-for="(item,index) in crowdShowData.goods.goods_arr" :key="index">
                        <div class="left">
                            <img class="img" :src="item.img" />
                        </div>
                        <div class="right box-flex">
                            <div class="dis-box">
                                <div class="box-flex f-04">{{$t('lang.support')}}
                                    <span class="color-red" v-html="item.price"></span>{{$t('lang.yuan')}}</div>
                                <div v-if="item.wuxian == 0" class="color-9 f-03">{{$t('lang.residue')}}
                                    <span class="color-red ">{{item.shenyu_ren}}</span>{{$t('lang.fen')}}
                                </div>
                                <div v-else class="color-9 f-03">
                                    <span class="color-red ">{{$t('lang.unlimited_total')}}</span>
                                </div>
                            </div>
                            <div class="color-3 f-03 m-top06">{{item.content}}</div>
                            <div class="color-9 f-03 m-top04">{{$t('lang.returns_time')}}{{item.return_time}}</div>
                            <div class="color-9 f-03">{{$t('lang.delivery_cost')}}:{{item.shipping_fee!=0?item.shipping_fee:$t('lang.free_shipping')}}</div>
                        </div>
                    </div>
                    <!--详情-->
                    <van-tabs v-model="active" class="m-top08 bg-color-write">
                        <van-tab v-for="(item,index) in tabs" :title="item" :key="index">
                            <template v-if="index == 0">
                             <div class="qinyou-cont crowd-comment-icon">
                                    <div v-if="crowdShowData.topic_list!=''">
                                        <van-cell-group v-if="crowdShowData.topic_list.length > 5">
                                            <van-cell :title="$t('lang.topics_list')" :value="$t('lang.more')" is-link @click="topicListMore"></van-cell>
                                        </van-cell-group>
                                        <div class="bg-color-write li" v-for="(item, index) in crowdShowData.topic_list " :key="index">
                                            <div class="dis-box">
                                                <div class="left">
                                                    <div class="img-box">
                                                        <img v-if="item.user_picture" class="img" :src="item.user_picture" />
                                                        <img v-else class="img" src="../../../../assets/img/get_avatar.png" />
                                                    </div>
                                                </div>
                                                <div class="box-flex">
                                                    <div class="dis-box m-top02">
                                                        <h4 class="f-05 color-3 box-flex">{{item.user_name}}</h4>
                                                        <!-- <div class="color-9 f-02 text-right" @click="topicClick(item)"><i class="iconfont icon-pinglun"></i>{{$t('lang.comment_alt')}}</div> -->
                                                    </div>
                                                    <p class="color-9 f-02 m-top04">{{item.add_time}}</p>
                                                </div>
                                            </div>
                                            <div class="f-03 color-3 padding-all">{{item.content}}</div>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <NotCont/>
                                    </div>
                                </div>
                            </template>
                            <template v-else-if="index == 1">
                                <div class="qinyou-cont">
                                    <div v-if="crowdShowData.backer_list!=''">
                                        <div class="li dis-box bg-color-write" v-for="(item, index) in crowdShowData.backer_list" :key="index">
                                            <div class="left">
                                                <div class="img-box">
                                                    <img v-if="item.user_picture" class="img" :src="item.user_picture" />
                                                    <img v-else class="img" src="../../../../assets/img/get_avatar.png" />
                                                </div>
                                            </div>
                                            <div class="box-flex">
                                                <div class="dis-box m-top02">
                                                    <h4 class="f-05 color-3 box-flex">{{item.user_name}}</h4>
                                                    <div class="color-9 f-02 text-right">{{$t('lang.support')}}
                                                        <span class="color-red" v-html="item.price"></span>{{$t('lang.yuan')}}</div>
                                                </div>
                                                <p class="color-9 f-02 m-top04">{{item.add_time}}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <NotCont/>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                             <!--话题模块-->
                                <div class="cont-max-height bg-color-write">
                                    <section class="swiper-slide goods-info-attr swiper-slide-next">
                                        <div class="order-id-list order-id-list-raise">
                                            <ul class="padding-all b-color-f">
                                                <li v-for="(item,index) in crowdShowData.progress" :key="index">
                                                    <p class="t-remark3">{{item.add_time}}</p>
                                                    <p class="t-remark m-top02">{{item.progress}}</p>
                                                    <div class="t-remark m-top02 n-comments-img">
                                                        <div v-for="itemImg in item.img">
                                                            <img class="img" :src="itemImg">
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </section>
                                </div>
                            </template>
                        </van-tab>
                    </van-tabs>
					<!-- 底部版权 -->
					<dsc-copyright></dsc-copyright>
                    <van-goods-action>
                       <van-goods-action-mini-btn icon="like-o" :text="$t('lang.attention')" :class="{'curr':is_collect == 1}" @click="Collection" />
                       <van-goods-action-mini-btn @click="topicClick" icon="edit" :text="$t('lang.theme')"  />
                       <van-goods-action-mini-btn @click="onGoodsShare">
                           <div slots="icon" class="van-goods-action-mini-btn__icon"><i class="iconfont icon-share"></i></div>
                           <div slots="default">{{$t('lang.share')}}</div>
                       </van-goods-action-mini-btn>
                       <template v-if="crowdShowData.info.info_status">
                           <van-goods-action-big-btn :text="$t('lang.have_expired')" disabled primary />
                       </template>
                       <template v-else>
                        <van-goods-action-big-btn :text="$t('lang.support')" primary @click="crowsdCheck" />
                       </template>
                    </van-goods-action>
                </div>
                <!--属性弹框-->
                <van-popup v-model="showBase" position="bottom" class="attr-goods-box">
                    <div class="attr-goods-header wallet-bt">
                            <div class="dis-box">
                                <div class="box-flex f-05 color-3">{{$t('lang.package_return')}}</div>
                                <div @click="closeSku"><i class="iconfont icon-guanbi f-05 color-9"></i></div>
                            </div>
                        </div>
                        <div class="attr-goods-content crowd-cont">
                            <van-radio-group v-model="radio" class="bg-color-write">
                                <van-radio :name ='item.id' :class="{'active':crowArr_id == item.id}"  v-for="(item,index) in crowdShowData.goods.goods_arr" :key="index">
                                    <div class="dis-box detail-scheme bg-color-write li" @click="crowArr(item)">
                                        <div class="left-icon"><label><i class="iconfont icon-gou"/></label></div>
                                        <div class="left">
                                            <img class="img" :src="item.img" />
                                        </div>
                                        <div class="right box-flex">
                                            <div class="dis-box">
                                                <div class="box-flex f-04">{{$t('lang.support')}}
                                                    <span class="color-red" v-html="item.price"></span>{{$t('lang.yuan')}}</div>
                                                <div v-if="item.wuxian == 0" class="color-9 f-03">{{$t('lang.residue')}}
                                                    <span class="color-red ">{{item.shenyu_ren}}</span>{{$t('lang.fen')}}
                                                </div>
                                                <div v-else class="color-9 f-03">
                                                    <span class="color-red ">{{$t('lang.unlimited_total')}}</span>
                                                </div>
                                            </div>
                                            <div class="color-3 f-03 m-top06">{{item.content}}</div>
                                            <div class="color-9 f-03 m-top04">{{$t('lang.returns_time')}}{{item.return_time}}</div>
                                            <div class="color-9 f-03">{{$t('lang.delivery_cost')}}:{{item.shipping_fee}}</div>
                                        </div>
                                    </div>
                                </van-radio>
                            </van-radio-group>
                        </div>
                        <div class="van-sku-actions dis-box corwd-footer">
                            <div class="left-btn box-flex color-red"><em class="color-9">{{$t('lang.total_flow')}}:</em><em v-html="crowdPropertyData.result ? crowdPropertyData.result : $t('lang.select')"></em></div>
                            <div class="" style="width:10rem">
                                <van-button type="primary" class="van-button--bottom-action" :disabled="crowdPropertyData.error == 1 ? true : false" @click="bargainLogCheckout">{{$t('lang.confirm')}}</van-button>
                            </div>
                        </div> 
                </van-popup>
            </div>
            <!--详情-->
            <div v-if="filter =='detail'" style="margin-top:4rem">
                <div class="dis-box tab-common  f-05 bg-color-write text-center">
                    <div class="box-flex ">
                        <div class="item " :class="{'active':filter_detail === 'detail-index'}" @click="handleFilterDetail('detail-index')">
                            <span>{{$t('lang.project_home_page')}}</span>
                        </div>
                    </div>
                    <div class="box-flex ">
                        <div class="item " :class="{'active':filter_detail === 'describe'}" @click="handleFilterDetail('describe')">
                            <span>{{$t('lang.crowdfunding_shuoming')}}</span>
                        </div>
                    </div>
                    <div class="box-flex" @click="onInstructions">
                        <div class="item" :class="{'active':filter_detail === 'instructions'}" @click="handleFilterDetail('instructions')">
                            <span>{{$t('lang.risk_that')}}</span>
                        </div>
                    </div>
                </div>
                <!--主页-->
                <div v-if="filter_detail =='detail-index'">
                  <div class="bg-color-write padding-all f-03 goods_desc" v-if="crowdProoertiesData.details!=''" v-html="crowdProoertiesData.details"></div>
                    <div v-else>
                        <NotCont/>
                    </div>
                </div>
                <div v-if="filter_detail =='describe'">
                  <div class="bg-color-write padding-all f-03 goods_desc" v-if="crowdProoertiesData.describe!=''" v-html="crowdProoertiesData.describe"></div>
                    <div v-else>
                        <NotCont/>
                    </div>
                </div>
                <div v-if="filter_detail =='instructions'">
                    <div class="bg-color-write padding-all f-03" v-if="crowdProoertiesData.risk_instruction!=''" v-html="crowdProoertiesData.risk_instruction"></div>
                    <div v-else>
                        <NotCont/>
                    </div>
                </div>
            </div>
            <!--话题-->
            <div v-if="filter =='topic'" style="margin-top:4rem">
                <div class="bargain-detail" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300" v-if="crowdTopicListData != ''">
                    <div class="qinyou-cont crowd-comment-icon">
                        <div class="bg-color-write li" v-for="(item, index) in crowdTopicListData" :key="index">
                            <div class=" dis-box " >
                                <div class="left">
                                    <div class="img-box">
                                        <img v-if="item.user_picture" class="img" :src="item.user_picture" />
                                        <img v-else class="img" src="../../../../assets/img/get_avatar.png" />
                                    </div>
                                </div>
                                <div class="box-flex">
                                    <div class="dis-box m-top02">
                                        <h4 class="f-05 color-3 box-flex">{{item.user_name}}</h4>
                                        <!-- 子评论暂时隐藏 -->
                                        <!-- <div class="color-9 f-02 text-right" @click="topicClick(item)"><i class="iconfont icon-pinglun "></i>{{$t('lang.comment_alt')}}</div> -->
                                    </div>
                                    <p class="color-9 f-02 m-top04">{{item.add_time}}</p>
                                </div>
                            </div>
                            <div class="f-03 color-3 padding-all">{{item.content}}</div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <NotCont />
                </div>
            </div>
        </div>

        <!--分享海报-->
        <van-popup v-model="shareImgShow" class="shareImg" overlay-class="shareImg-overlay">
            <img :src="shareImg" v-if="shareImg" class="img" />
            <span v-else>{{$t('lang.error_generating_image')}}</span>
        </van-popup>

        <CommonNav :routerName="routerName">
            <li slot="aloneNav">
    			<router-link :to="{name: 'crowdfunding'}">
					<i class="iconfont icon-shequ2"></i>
					<p>{{$t('lang.square')}}</p>
				</router-link>
    		</li>
			<li slot="aloneNav">
				<router-link :to="{name: 'crowdfunding-user'}">
					<i class="iconfont icon-gerenzhongxin"></i>
					<p>{{$t('lang.centre')}}</p>
				</router-link>
			</li>
	    </CommonNav>
    </div>
</template>

<script>
import { mapState } from 'vuex'

import {
    Swipe,
    SwipeItem,
    GoodsAction,
    GoodsActionBigBtn,
    GoodsActionMiniBtn,
    Actionsheet,
    Cell,
    CellGroup,
    Tab,
    Tabs,
    Tag,
    Button,
    Row,
    Col,
    Popup,
    RadioGroup,
    Radio,
    Stepper,
    Toast ,
    Dialog
} from "vant"

import CommonNav from '@/components/CommonNav'
import NotCont from '@/components/NotCont'
import arrRemove from '@/mixins/arr-remove'
import CountDown from '@/components/CountDown'
import formProcessing from '@/mixins/form-processing'

export default {
    name: "auction-detail",
    components: {
        CommonNav,
        CountDown,
        NotCont,
        [Swipe.name]: Swipe,
        [SwipeItem.name]: SwipeItem,
        [GoodsAction.name]: GoodsAction,
        [GoodsActionBigBtn.name]: GoodsActionBigBtn,
        [GoodsActionMiniBtn.name]: GoodsActionMiniBtn,
        [Actionsheet.name]: Actionsheet,
        [Cell.name]: Cell,
        [CellGroup.name]: CellGroup,
        [Tab.name]: Tab,
        [Tabs.name]: Tabs,
        [Tag.name]: Tag,
        [Button.name]: Button,
        [Row.name]: Row,
        [Col.name]: Col,
        [Popup.name]: Popup,
        [RadioGroup.name]: RadioGroup,
        [Radio.name]: Radio,
        [Stepper.name]: Stepper,
        [Toast.name]: Toast,
        [Dialog.name] : Dialog,
    },
    mixins: [formProcessing],
    data() {
        return {
            routerName:'crowd_funding',
            radio: '1',
            crowArr_id:'',
            pid:'',
            id:'',
            number:1,
            showBase: false,
            filter: "goods", //默认选中值
            filter_detail:'detail-index',
            tabs: [this.$t('lang.crowdfunding_tabs_1'), this.$t('lang.crowdfunding_tabs_2'), this.$t('lang.crowdfunding_tabs_3')],
            active: 0,
            disabled:false,
            loading:true,
            size:10,
            page:1,
            shareImgShow:false,
            shareImg:''
        }
    },
    //初始化加载数据
    created() {
        this.$store.dispatch('setCrowdfundingShow',{
            id:this.$route.params.id,
        })

        this.crfTopicList()

        this.onInstructions()
    },
    computed: {
        ...mapState({
            //众筹商品信息
            crowdShowData: state => state.crowdfunding.crowdShowData,
            //众筹详情信息
            crowdProoertiesData: state => state.crowdfunding.crowdProoertiesData,
            //众筹详情属性信息
            crowdPropertyData: state => state.crowdfunding.crowdPropertyData,
        }),
        //众筹话题
        crowdTopicListData:{
            get(){
                return this.$store.state.crowdfunding.crowdTopicListData
            },
            set(val){
                this.$store.state.crowdfunding.crowdTopicListData = val  
            }
        },
        isLogin(){
            return localStorage.getItem('token') == null ? false : true
        },
        crowdCollectStatue(){
            return this.$store.state.crowdfunding.crowdCollectStatue
        },
        is_collect:{
            get(){
                return this.$store.state.crowdfunding.crowdShowData.info.is_collect
            },
            set(val){
                this.$store.state.crowdfunding.crowdShowData.info.is_collect = val
            }
        },
    },
    methods: {
        //话题列表请求
        crfTopicList(page){
            if(page){
                this.page = page
                this.size = Number(page) * 10
            }

            this.$store.dispatch('setCrowdfundingMyTopicList',{
                id:this.$route.params.id,
                page:this.page,
                size:this.size
            })
        },
        //属性弹框  
        crowsdCheck() {
            this.showBase = true
        },
        //关闭属性弹框
        closeSku() {
            this.showBase = false;
            this.bargainBidData.bargain_join =''
        },
        //属性选择
        crowArr(item){
            this.crowArr_id = item.id
            this.pid= item.pid
            this.id= item.id

            this.$store.dispatch('setCrowdfundingProperty',{
                pid: this.pid,
                id:this.id,
                number: this.number,
            })
        },
        //提交
        bargainLogCheckout(){
            if(this.$store.state.crowdfunding.crowdPropertyData.error == 1){
                return
            }

            if(this.$store.state.crowdfunding.crowdPropertyData.result == undefined){
                Toast(this.$t('lang.fill_in_package_return'));
            }else{
                this.$router.push({
                    name: "crowdfunding-checkout",
                    query: {
                        pid: this.pid,
                        id:this.id,
                        number: this.number,
                    }
                })
            }
        },
        //菜单模式切换
        handleFilter(val) {
            this.filter = val;
        },
        //话题、评论
        topicClick(item) {
            let topic_id = item.topic_id ? item.topic_id : 0

            this.$router.push({
                name: "crowdfunding-topic",
                query:{
                    id:this.$store.state.crowdfunding.crowdShowData.info.id,
                    topic_id:topic_id
                }
            })
        },
        //设置详情
        detailClick(){
            this.filter = 'detail'
        },
        topicListMore(){
            this.filter = 'topic'
        },
        handleFilterDetail(val){
            this.filter_detail = val;
        },
        loadMore(){
            setTimeout(() => {
                this.disabled = true
                if(this.page * this.size == this.crowdTopicListData.length){
                    this.page ++
                    this.crfTopicList()
                }
            },200)
        },
        //风险说明
        onInstructions(){
            let Instructions= {
                id:this.$route.params.id,
            }
            this.$store.dispatch('setCrowdfundingProperties',Instructions)
        },
        //收藏
        Collection(){
            if(this.isLogin){
                this.$store.dispatch('setCrowdfundingFocus',{
                    id:this.$store.state.crowdfunding.crowdShowData.info.id,
                    status:this.is_collect
                })
            }else{
                Dialog.confirm({
                    message:this.$t('lang.fill_in_user_collect_goods'),
                    className:'text-center'
                }).then(()=>{
                    this.$router.push({
                        name: 'login',
                        query:{ redirect: {name:'crowdfunding-detail',params:{ id:this.$route.params.id }}}
                    })
                }).catch(()=>{

                })
            }
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
                            name: 'crowdfunding-detail',
                            params: {id: this.$route.params.id},
                            url:url
                        }
                    }
                })
            }).catch(() => {

            })
        },
        //分享海报
        onGoodsShare(){
            if (this.isLogin) {
                Toast.loading({ duration: 0, mask: true, forbidClick: true, message: this.$t('lang.loading') })
                let price = this.crowdShowData.info.amount

                this.$store.dispatch('setGoodsShare',{
                    goods_id:this.$route.params.id,
                    price: price,
                    share_type:0,
                    extension_code:'crowdfunding',
                    code_url:window.location.href,
                    thumb:this.crowdShowData.info.title_img,
                    title:this.crowdShowData.info.title,
                }).then(res => {
                    if(res.status == 'success'){
                        this.shareImg = res.data
                        this.shareImgShow = true
                        Toast.clear()
                    }
                })
            } else {
                let msg = this.$t('lang.login_user_not')
                this.notLogin(msg)
            }
        }
    },
    watch:{
        crowdShowData(){
            //单独设置微信分享信息
            this.$wxShare.share({
                title:this.crowdShowData.info.title,
                desc:'',
                link:window.location.href,
                imgUrl:this.crowdShowData.info.title_img
            })
        },
  	    crowdTopicListData(){
			if(this.page * this.size == this.crowdTopicListData.length){
				this.disabled = false
				this.loading = true
			}else{
				this.loading = false
			}

            this.crowdTopicListData = arrRemove.trimSpace(this.crowdTopicListData)
        },
        crowdCollectStatue(){
            this.is_collect = this.crowdCollectStatue
        }
  	}
}
</script>
