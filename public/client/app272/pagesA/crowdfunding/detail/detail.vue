<template>
    <view class="container">
		<view class="header-nav-content">
			<view class="items">
				<view v-for="(item,index) in navTabs" :key="index" class="item" @click="handleFilter(index)" :class="{'active':filter == index}">
					<text>{{item}}</text>
				</view>
			</view>
		</view>
		<scroll-view class="scrollList" scroll-y :lower-threshold="100" @scrolltolower="loadMore" :style="{height:winHeight + 'px'}">
			<!-- 活动介绍 -->
			<view class="goods-wrap" :style="{minHeight:winHeight + 'px'}" v-show="filter == 0">
				<!-- 轮播图 -->
				<view class="banner banner-auto">
					<image :src="crowdShowData.info.title_img" mode="widthFix" class="imgbox"></image>
				</view>
				<view class="detail-user uni-flex-common uni-flex-wrap">
				    <view class="left"><image :src="crowdShowData.user_info.img" mode="widthFix" class="img" /></view>
				    <view class="center onelist-hidden uni-red">{{ crowdShowData.user_info.name }}</view>
					<!-- #ifdef MP-WEIXIN -->
					<button open-type="contact" type="default" size="5" session-from="weapp" hover-class="none" class="right kefu-cantact" v-if="wxappChat > 0">
						<text>{{$t('lang.consult')}}</text>
					</button>
					<view class="right" @click="onChat(0,0)" v-else><text>{{$t('lang.consult')}}</text></view>
					<!-- #endif -->
					<!-- #ifndef MP-WEIXIN -->
					<view class="right" @click="onChat(0,0)"><text>{{$t('lang.consult')}}</text></view>
					<!-- #endif -->
				</view>
				<view class="cont-box">
					<view class="box-wrap">
						<view class="title">
							<view class="tit">{{crowdShowData.info.title}}</view>
							<view class="cont">{{$t('lang.label_residue')}}<text class="uni-red">{{crowdShowData.info.shenyu_time}}</text>{{$t('lang.tian')}}</view>
							<view class="ect-progress">
								<progress :percent="crowdShowData.info.baifen_bi" show-info="true" border-radius="3" stroke-width="6" font-size="12" active="true" activeColor="#f92028"></progress>
							</view>
							<view class="int-tabs uni-flex-common uni-flex-wrap">
								<view class="col uni-flex-common">
									<text class="iconfont icon-iconfontqizhi"></text>
									<text class="txt">{{$t('lang.target_funds')}}</text>
									<text class="text"><text class="uni-red">{{crowdShowData.info.amount}}</text>{{$t('lang.yuan')}}</text>
								</view>
								<view class="col uni-flex-common">
									<text class="iconfont icon-jinqian"></text>
									<text class="txt">{{$t('lang.crowdfunding_fund')}}</text>
									<text class="text"><text class="uni-red">{{crowdShowData.info.join_money}}</text>{{$t('lang.yuan')}}</text>
								</view>
								<view class="col uni-flex-common">
									<text class="iconfont icon-geren"></text>
									<text class="txt">{{$t('lang.support_number')}}</text>
									<text class="text"><text class="uni-red">{{crowdShowData.info.join_num}}</text>{{$t('lang.ren')}}</text>
								</view>
							</view>
							<view class="project-more uni-flex-common uni-red">
								<text calss="label">{{$t('lang.view_project_detail')}}</text>
								<text class="iconfont icon-jiantouyou"></text>
							</view>
						</view>
					</view>
				</view>
				<view class="crowd-card">
					<view class="crowd-list">
						<view class="title">{{$t('lang.package_return')}}</view>
						<view class="crowd-list-cell uni-flex-common" v-for="(item,index) in crowdShowData.goods.goods_arr" :key="index">
							<view class="left"><image :src="item.img" mode="widthFix" class="img" /></view>
							<view class="right uni-flex-item">
							    <view class="top uni-flex-common uni-space-between">
							        <view class="top-left">{{$t('lang.support')}}<text class="uni-red">{{item.price}}</text>{{$t('lang.yuan')}}</view>
							        <view class="top-right" v-if="item.wuxian == 0">{{$t('lang.residue')}}<text class="uni-red">{{item.shenyu_ren}}</text>{{$t('lang.fen')}}</view>
							        <view class="top-right" v-else><text class="uni-red">{{$t('lang.unlimited_total')}}</text></view>
							    </view>
							    <view class="col-weight">{{item.content}}</view>
							    <view class="col mt10">{{$t('lang.returns_time')}}{{ item.return_time }}</view>
							    <view class="col">{{$t('lang.delivery_cost')}}:{{ item.shipping_fee != 0 ? item.shipping_fee : $t('lang.free_shipping') }}</view>
							</view>
						</view>
					</view>
				</view>
				<view class="crowd-desc bgc_fff">
					<view class="uni-common-mt">
						<uni-segmented-control :current="current" :values="tabs" v-on:clickItem="datailFilter" styleType="text" activeColor="#f92028"></uni-segmented-control>
					</view>
					<view class="content">
						<view v-show="current === 0">
							<view class="user-cell-card" v-if="crowdShowData.topic_list != ''">
								<view class="user-cell-list" v-for="(item, index) in crowdShowData.topic_list" :key="index">
									<view class="top uni-flex-common">
										<view class="left">
											<image :src="item.user_picture" class="img" v-if="item.user_picture" />
											<image src="../../../static/get_avatar.png" class="img" v-else />
										</view>
										<view class="right uni-flex-item">
											<view class="name">{{item.user_name}}</view>
											<view class="time">{{item.add_time}}</view>
										</view>
									</view>
									<view class="bottom">{{ item.content }}</view>
								</view>
								<view class="list-more" @click="topicListMore" v-if="crowdShowData.topic_list && crowdShowData.topic_list.length > 5">{{$t('lang.view_more')}}<uni-icons type="arrowright" size="14" color="#666"></uni-icons></view>
							</view>
							<view v-else>
								<dsc-not-content></dsc-not-content>
							</view>
						</view>
						<view v-show="current === 1">
							<view class="user-cell-card" v-if="crowdShowData.backer_list != ''">
								<view class="user-cell-list uni-flex-common" v-for="(item, index) in crowdShowData.backer_list" :key="index">
									<view class="left">
										<image :src="item.user_picture" class="img" v-if="item.user_picture" />
										<image src="../../../static/get_avatar.png" class="img" v-else />
									</view>
									<view class="right uni-flex-item">
										<view class="top uni-flex-common uni-space-between">
											<text class="name">{{item.user_name}}</text>
											<view class="support">{{$t('lang.support')}}<text class="uni-red">{{item.price}}</text>{{$t('lang.yuan')}}</view>
										</view>
										<text class="time">{{item.add_time}}</text>
									</view>
								</view>
							</view>
							<view v-else>
								<dsc-not-content></dsc-not-content>
							</view>
						</view>
						<view v-show="current === 2">
							<view class="user-cell-card">
								<view class="user-cell-list" v-for="(item,index) in crowdShowData.progress" :key="index">
									<view class="top">
										<view class="name">{{item.progress}}</view>
										<view class="time">{{item.add_time}}</view>
									</view>
									<view class="progress-list-imgs uni-flex-common uni-flex-wrap">
										<view class="list-img" v-for="(itemImg,indexImg) in item.img" :key="indexImg">
											<image :src="itemImg" mode="widthFix" class="img" />
										</view>
									</view>
								</view>
							</view>
						</view>
					</view>
				</view>
			</view>
			<!-- 活动详情 -->
			<view class="goods-desc" :style="{minHeight:winHeight + 'px'}" v-show="filter == 1">
				<view class="uni-common-mt">
					<uni-segmented-control :current="detailCurrent" :values="descTabs" v-on:clickItem="handleFilterDetail" styleType="text" activeColor="#f92028"></uni-segmented-control>
				</view>
				<view class="content">
					<view v-show="detailCurrent === 0" >
						<jyf-parser :html="crowdProoertiesData.details" :tag-style="{img: 'float:left;'}"></jyf-parser>
					</view>
					<view v-show="detailCurrent === 1">
						<view class="detail-warp" v-if="crowdProoertiesData.describe!=''" v-html="crowdProoertiesData.describe"></view>
					</view>
					<view v-show="detailCurrent === 2">
						<view class="detail-warp" v-if="crowdProoertiesData.risk_instruction!=''" v-html="crowdProoertiesData.risk_instruction"></view>
					</view>
				</view>
			</view>
			<!-- 活动话题 -->
			<view class="goods-progress" :style="{minHeight:winHeight + 'px'}" v-show="filter == 2">
				<view class="user-cell-card" v-if="crowdTopicListData != ''">
					<view class="user-cell-list" v-for="(item, index) in crowdTopicListData" :key="index">
						<view class="top uni-flex-common">
							<view class="left">
								<image :src="item.user_picture" class="img" v-if="item.user_picture" />
								<image src="../../../static/get_avatar.png" class="img" v-else />
							</view>
							<view class="right uni-flex-item">
								<view class="name">{{item.user_name}}</view>
								<view class="time">{{item.add_time}}</view>
							</view>
						</view>
						<view class="bottom">{{ item.content }}</view>
					</view>
				</view>
				<view v-else>
					<dsc-not-content></dsc-not-content>
				</view>
			</view>
			<!-- 底部版权 -->
			<dsc-copyright></dsc-copyright>
		</scroll-view>
		
		<view class="btn-goods-action">
			<view class="item" @click="collection">
				<block v-if="is_collect == 1"><view class="iconfont icon-collection-alt"></view></block>
				<block v-else><view class="iconfont icon-collection"></view></block>
				<text class="txt">{{$t('lang.attention')}}</text>
			</view>
			<view class="item" @click="topicClick">
				<view class="iconfont icon-find-pinglun"></view>
				<text class="txt">{{$t('lang.theme')}}</text>
			</view>
			<view class="item" @click="mpShare">
				<view class="iconfont icon-share"></view>
				<text class="txt">{{$t('lang.share')}}</text>
			</view>
			<view class="btn-bar">
				<view class="btn btn-disabled" v-if="crowdShowData.info.info_status">{{$t('lang.have_expired')}}</view>
				<view class="btn btn-red" @click="crowsdCheck" v-else>{{$t('lang.support')}}</view>
			</view>
		</view>
		
		<!--属性弹框-->
		<uni-popup :show="showBase" type="bottom" mode="fixed" v-on:hidePopup="closeSku()">					
			<view class="attr-goods-box">						
				<view class="attr-goods-header wallet-bt">
					<view class="title">
						<view class="txt">{{$t('lang.package_return')}}</view>
						<uni-icons type="closeempty" size="30" color="#999999" @click="closeSku()"></uni-icons>
					</view>
		        </view>
				<view class="attr-goods-content">
					<view class="crowd-card">
						<view class="crowd-list">
							<view class="crowd-list-cell uni-flex-common" v-for="(item,index) in crowdShowData.goods.goods_arr" :key="index">
								<view class="checkbox" :class="{'checked':crowArr_id == item.id}" @click="crowArr(item)">
									<view class="checkbox-icon">
										<uni-icons type="checkmarkempty" size="22" color="#ffffff"></uni-icons>
									</view>
								</view>
								<view class="left"><image :src="item.img" mode="widthFix" class="img" /></view>
								<view class="right uni-flex-item">
									<view class="top uni-flex-common uni-space-between">
										<view class="top-left">{{$t('lang.support')}}<text class="uni-red">{{item.price}}</text>{{$t('lang.yuan')}}</view>
										<view class="top-right" v-if="item.wuxian == 0">{{$t('lang.residue')}}<text class="uni-red">{{item.shenyu_ren}}</text>{{$t('lang.fen')}}</view>
										<view class="top-right" v-else><text class="uni-red">{{$t('lang.unlimited_total')}}</text></view>
									</view>
									<view class="col-weight">{{item.content}}</view>
									<view class="col mt10">{{$t('lang.returns_time')}}{{ item.return_time }}</view>
									<view class="col">{{$t('lang.delivery_cost')}}:{{ item.shipping_fee != 0 ? item.shipping_fee : $t('lang.free_shipping') }}</view>
								</view>
							</view>
						</view>
					</view>
				</view>
				<view class="btn-goods-action">
					<view class="submit-bar-text">
						<text>{{$t('lang.total_flow')}}:</text>
						<view class="submit-bar-price">{{crowdPropertyData.result?crowdPropertyData.result: $t('lang.select')}}</view>
					</view>
					<view class="btn-bar">
						<view class="btn btn-disabled" v-if="crowdPropertyData.error == 1">{{$t('lang.confirm')}}</view>
						<view class="btn btn-red" @click="bargainLogCheckout" v-else>{{$t('lang.confirm')}}</view>
					</view>
				</view>
			</view>
		</uni-popup>
		
		<!--小程序分享-->
		<view class="show-popup-shareImg">
			<uni-popup :show="shareImgShow" type="bottom" animation="true" v-on:hidePopup="shareImgShow = false">
				<view class="mp-share-warp">
					<view class="title">
						<text>{{$t('lang.save_xaingce')}}</text>
						<uni-icon type="closeempty" size="30" color="#8f8f94" @click="shareImgShow = false"></uni-icon>
					</view>
					<view class="mp-share-img"><image :src="mpShareImg" mode="heightFix" class="img" @tap="previewImage"></image></view>
					<view class="btn-bar btn-bar-radius"><button class="btn btn-red" @click="downloadImg">{{$t('lang.save_picture')}}</button></view>
				</view>
			</uni-popup>
		</view>
		
		<!--自定义分享-->
		<uni-popups id="popupPoster" ref="popupPoster" :animation="true" type="bottom">
			<view class="popup-poster">
				<view class="poster-image"><image :src="mpShareImg" mode="widthFix" class="img"></image></view>
				<view class="poster-btn">
					<view class="tit">{{$t('lang.share_to')}}</view>
					<view class="lists">
						<!-- #ifdef MP-WEIXIN -->
						<button class="list" open-type="share">
							<image src="@/static/sharemenu/weix.png" mode="widthFix"></image>
							<text>{{ $t('lang.share_with_friends') }}</text>
						</button>
						<!-- #endif -->
						<!-- #ifdef APP-PLUS -->
						<view class="list" @click="posterAppShare('weixin')">
							<image src="@/static/sharemenu/weix.png" mode="widthFix"></image>
							<text>{{ $t('lang.share_with_friends') }}</text>
						</view>
						<view class="list" @click="posterAppShare('pyq')">
							<image src="@/static/sharemenu/pengy.png" mode="widthFix"></image>
							<text>{{ $t('lang.generate_sharing_poster') }}</text>
						</view>
						<!-- #endif -->
						<view class="list" @click="downloadImg">
							<image src="@/static/sharemenu/baocun.png" mode="widthFix"></image>
							<text>{{ $t('lang.save_picture') }}</text>
						</view>
					</view>
					<view class="cancel" @click="popupPosterCancel">{{$t('lang.cancel')}}</view>
				</view>
			</view>
		</uni-popups>
		
		<dsc-common-nav>
			<navigator @click="$outerHref('/pagesA/crowdfunding/crowd','app')" class="nav-item" slot="right">
				<view class="iconfont icon-team"></view>
				<text>{{$t('lang.square')}}</text>
			</navigator>			
		</dsc-common-nav>		
    </view>
</template>

<script>
import { mapState } from 'vuex'
import uniPopup from '@/components/uni-popup.vue'; 
import uniPopups from '@/components/uni-popup/uni-popup.vue';
import dscNotContent from '@/components/dsc-not-content.vue';
import dscCommonNav from '@/components/dsc-common-nav.vue';
import uniIcons from '@/components/uni-icons/uni-icons.vue';
import uniSegmentedControl from '@/components/uni-segmented-control.vue';
import dscCopyright from '@/components/dsc-copyright/dsc-copyright.vue';

export default {
    name: "auction-detail",
    components: {
		uniIcons,
		uniPopup,
		uniPopups,
		dscNotContent,
        dscCommonNav,
		uniSegmentedControl,
		dscCopyright
    },
    data() {
        return {
			navTabs: [this.$t('lang.goods'), this.$t('lang.detail'), this.$t('lang.theme')],
            routerName:'crowd_funding',
            radio: '1',
            crowArr_id:'',
            pid:'',
            id:'',
            number:1,
            showBase: false,
            filter: 0, //默认选中值
            tabs: [this.$t('lang.crowdfunding_tabs_1'), this.$t('lang.crowdfunding_tabs_2'), this.$t('lang.crowdfunding_tabs_3')],
			descTabs:[this.$t('lang.project_home_page'), this.$t('lang.crowdfunding_shuoming'), this.$t('lang.risk_that')],
			current: 0,
			detailCurrent: 0,
            active: 0,
            disabled:false,
            loading:true,
            size:10,
            page:1,
			shareImgShow:false,
			mpShareImg: '',
			//微信小程序客服
			wxappChat:uni.getStorageSync("configData").wxapp_chat || 0
        }
    },
	onShareAppMessage(res){
		return {
		  title: this.crowdShowData.info.title,
		  path: '/pagesA/crowdfunding/detail/detail?id=' + this.id
		}
	},
    //初始化加载数据
    onLoad(e){
		let that = this
    	that.id = e.id
		
		//小程序扫码
		if (e.scene) {
			let scene = decodeURIComponent(e.scene);
			let fristParameter = scene.split('_')[0];
			let lastParameter = scene.split('_')[1];
			
			// 判断分割第一个参数是否有 "."
			that.id = fristParameter.indexOf('.') > 0 ? fristParameter.split('.')[0] : fristParameter;
			
			if(lastParameter){
				uni.setStorageSync('parent_id',lastParameter);
			}
		}
		
        that.$store.dispatch('setCrowdfundingShow',{
            id:that.id
        })

        that.crfTopicList()

        that.onInstructions()
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
        	return this.$isLogin()
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
		winHeight(){
			return uni.getSystemInfoSync().windowHeight - 100;
		}
    },
	watch:{
	    crowdCollectStatue(){
	        this.is_collect = this.crowdCollectStatue
	    },
		sharePoster() {
			if (this.sharePoster) {
				this.$refs.popupPoster.open();
			}
		}
	},
    methods: {
        //话题列表请求
        crfTopicList(page){
            if(page){
                this.page = page
                this.size = Number(page) * 10
            }

            this.$store.dispatch('setCrowdfundingMyTopicList',{
                id:this.id,
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
            //this.bargainBidData.bargain_join =''
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
			if(this.$isLogin()){
				if(this.$store.state.crowdfunding.crowdPropertyData.result == undefined){
					uni.showToast({
						title: this.$t('lang.fill_in_package_return'),
						icon:'none'
					});	
				}else{
					uni.navigateTo({
						url:'/pagesA/crowdfunding/checkout/checkout?id=' + this.id + '&pid=' + this.pid + '&number=' + this.number
					})		
				}
			}else{
				uni.showModal({
					content:this.$t('lang.login_user_not'),
					success:(res)=>{
						if(res.confirm){
							uni.navigateTo({
								url:'/pagesB/login/login?delta=1'
							})
						}
					}
				})
			}
        },
        //菜单模式切换
        handleFilter(val) {
			this.filter = val
        },		
		//菜单模式切换
		datailFilter(index) {
		    if (this.current !== index) {
		    	this.current = index;
		    }
		},
        //话题、评论
        topicClick(item) {
            let topic_id = item.topic_id ? item.topic_id : 0
			let id = this.$store.state.crowdfunding.crowdShowData.info.id
			uni.navigateTo({
				url:'/pagesA/crowdfunding/topic/topic?id='+id+'&topic_id='+topic_id
			})
        },
        //设置详情
        detailClick(){
            this.filter = 1
        },
        topicListMore(){
            this.filter = 2
        },
        handleFilterDetail(index){
			if (this.detailCurrent !== index) {
				this.detailCurrent = index;
			}
        },
        //风险说明
        onInstructions(){
            this.$store.dispatch('setCrowdfundingProperties',{
				id:this.id
			})
        },
        //收藏
        collection(){
            if(this.$isLogin()){
                this.$store.dispatch('setCrowdfundingFocus',{
					
                    id:this.$store.state.crowdfunding.crowdShowData.info.id,
                    status:this.is_collect
                })
            }else{
				uni.showModal({
					content: this.$t('lang.fill_in_user_collect_goods'),
					success:(res)=>{
						if(res.confirm){
							uni.navigateTo({
								url:'/pagesB/login/login?delta=1'
							})
						}
					}
				})
            }
        },
		loadMore(){
			if(this.page * this.size == this.crowdTopicListData.length){
				this.page ++
				this.crfTopicList()
			}
		},
		// 分享
		appShare() {
			let shareInfo = {
				href: `${this.$websiteUrl}crowdfunding/detail/${this.id}?parent_id=${uni.getStorageSync("user_id")}&platform=APP`,
				title: this.crowdShowData.info.title,
				summary: '',
				imageUrl: this.crowdShowData.info.title_img
			};
			this.shareInfo(shareInfo, 'poster');
		},
		mpShare() {
			this.onGoodsShare();
		},
		onGoodsShare() {
			if (this.$isLogin()) {
				uni.showLoading({ title: this.$t('lang.loading') });
				let price = this.crowdShowData.info.amount
				let o = {}
				
				// #ifdef MP-WEIXIN
				o = {
					goods_id: this.id,
					ru_id: 0,
					price: price,
					share_type: 0,
					type: 0,
					platform: 'MP-WEIXIN',
					extension_code:'crowdfunding',
					thumb:this.crowdShowData.info.title_img,
					title:this.crowdShowData.info.title,
					code_url:'pagesA/crowdfunding/detail/detail',
					scene:`${this.id}`
				}
				// #endif
				
				// #ifdef APP-PLUS
				o = {
					goods_id: this.id,
					price: price,
					share_type: 0,
					platform: 'APP',
					extension_code:'crowdfunding',
					thumb:this.crowdShowData.info.title_img,
					title:this.crowdShowData.info.title,
					code_url:`${this.$websiteUrl}crowdfunding/detail/${this.id}`
				}
				// #endif
		
				this.$store
					.dispatch('setGoodsShare', o)
					.then(res => {
						if (res.status == 'success') {
							this.mpShareImg = res.data;
		
							// #ifdef MP-WEIXIN
							this.shareImgShow = true;
							// #endif
		
							// #ifdef APP-PLUS
							this.appShare();
							// #endif
		
							uni.hideLoading();
						}
					});
			} else {
				uni.showModal({
					content: this.$t('lang.login_user_not'),
					success: res => {
						if (res.confirm) {
							uni.navigateTo({
								url: '/pagesB/login/login?delta=1'
							});
						}
					}
				});
			}
		
			// #ifdef APP-PLUS
			this.sharePoster = false;
			// #endif
		},
		popupPosterCancel() {
			this.$refs.popupPoster.close();
			// #ifdef APP-PLUS
			this.sharePoster = false;
			// #endif
		},
		previewImage() {
			let that = this;
			let arr = [];
			arr.push(that.mpShareImg);
			uni.previewImage({
				current: 1,
				urls: arr,
				indicator: 'number',
				longPressActions: {
					itemList: [this.$t('lang.send_to_friend'), this.$t('lang.save_picture'), this.$t('lang.collect')],
					success: function(data) {
						console.log('选中了第' + (data.tapIndex + 1) + '个按钮,第' + (data.index + 1) + '张图片');
					},
					fail: function(err) {
						console.log(err.errMsg);
					}
				}
			});
		},
		downloadImg() {
			var that = this;
			uni.downloadFile({
				url: that.mpShareImg,
				success: res => {
					uni.saveImageToPhotosAlbum({
						filePath: res.tempFilePath,
						success: function() {
							uni.showToast({
								title: that.$t('lang.picture_saved_success'),
								icon: 'none',
								duration: 1000,
								success: () => {
									that.$refs.popupPoster.close();
									that.sharePoster = false;
								}
							});
						}
					});
				}
			});
		},
		posterAppShare(type) {
			let that = this;
			let scene = type == 'weixin' ? 'WXSceneSession' : 'WXSenceTimeline';
			uni.share({
				provider: 'weixin',
				scene: scene,
				type: 2,
				imageUrl: that.mpShareImg
			});
		
			that.$refs.popupPoster.close();
			that.sharePoster = false;
		},
    }
}
</script>

<style scoped>
	.header-nav-content{ position: fixed; display: flex; flex-direction: row; justify-content: center; align-items: center; width: 100%; height: 50px; background-color: #FFFFFF; top: 0; z-index: 99;}
	.header-nav-content .items{ flex: 1; display: flex; flex-direction: row; justify-content: center; align-items: center;}
	.header-nav-content .item{ width: 150upx; text-align: center;}
	.header-nav-content .item.active{ color: #f92028; }
	
	.scrollList{ padding-top: 100upx;}
	
	.detail-user{ background: #FFFFFF; padding: 20upx; margin-bottom: 20upx;}
	.detail-user .left{ width: 60upx; height: 60upx; }
	.detail-user .center{ width: calc(100% - 220upx); margin: 0 20upx; font-size: 30upx;}
	.detail-user .right{ flex: 1; display: flex; justify-content: flex-end; }
	.detail-user .right text{ background: linear-gradient(90deg,#ff010c,#ff7e0c); padding: 5upx 25upx; color: #FFFFFF; border-radius: 100upx; }
	
	.cont-box { margin-bottom: 20upx;}
	.cont-box .title .tit{ line-height: 1; font-size: 30upx; color: #333; font-weight: 700;}
	.cont-box .title .cont{ font-size: 25upx; color: #999; margin-top: 15upx; text-align: right;}
	
	.int-tabs { margin: 20upx 0 30upx;}
	.int-tabs .col{ width: 33.3%; flex: 1; flex-direction: column; line-height: 1.5; font-size: 25upx; color: #999;}
	
	.project-more{ justify-content: center; border-top: 2upx solid #f1f1f1; padding-top: 20upx;}
	.project-more .iconfont{ line-height: 1; margin-left: 10upx;}
	
	.ect-progress progress{ color: #999;}
	
	.crowd-card{ background: #FFFFFF; margin-bottom: 20upx; text-align: left;}
	.crowd-card .crowd-list{ padding: 0 20upx; }
	.crowd-card .title{ padding:20upx 0; font-size: 30upx; color: #333333;}
	.crowd-card .crowd-list-cell{ padding-bottom: 20upx;}
	.crowd-card .crowd-list-cell .left{ width: 100upx; height: auto; line-height: 1; margin-right: 20upx; }
	.crowd-card .crowd-list-cell .right { justify-content: flex-start;}
	.crowd-card .crowd-list-cell .right view{ line-height: 1.5;}
	.crowd-card .crowd-list-cell .right .top-right,
	.crowd-card .crowd-list-cell .right .col{ color: #999;}
	.crowd-card .crowd-list-cell .right .col{ line-height: 1.2;}
	
	.crowd-desc{ padding: 0 20upx;}
	.crowd-desc .content{ margin-top: 20upx; min-height: 500upx; }
	.crowd-desc .uni-common-mt{ position: relative;}
	.crowd-desc .uni-common-mt::before{background-color: #f1f1f1; content: " "; position: absolute; left: 0; right: 0; bottom: 0; height: 2upx;}
	.user-cell-list .bottom{ padding: 20upx;}
	.user-cell-list .support{ color: #999; font-size: 25upx;}
	
	.progress-list-imgs .list-img{ width: 30%; margin-right: 1%; }
	
	.goods-desc,.goods-progress{ margin-top: 20upx;}
	.goods-desc .content{ padding: 20upx; box-sizing: border-box;}
	.goods-desc .uni-common-mt{ margin-top: 0;}
	.attr-goods-box{padding-bottom: 35px;}
	.attr-goods-box .btn-goods-action{ border-top: 1px solid #eee;}
	
	/* 小程序分享  start*/
	.show-popup-shareImg /deep/ .uni-popup-bottom{ height: 80%; }
	/* 小程序分享 end*/
</style>
