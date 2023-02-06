<template>
	<view class="uni-navbar" :class="{'uni-navbar-fixed':isFixed,'uni-navbar-shadow':hasShadow,'app-nav-top':istitleNView}" :style="{'background-color':backgroundColor}">
		<uni-status-bar v-if="statusBar"></uni-status-bar>
		<view class="uni-navbar-header" :style="{color:color}">
			<view class="uni-navbar-header-btns" @tap="onClickLeft" v-if="isLeftState">
				<view v-if="leftIcon.length">
					<uni-icons :type="leftIcon" :color="color" size="24"></uni-icons>
				</view>
				<view v-if="leftText.length" class="uni-navbar-btn-text" :class="{'uni-navbar-btn-icon-left':!leftIcon.length}">{{leftText}}</view>
				<slot name="left"></slot>
			</view>
			<view class="uni-mp-navbar" v-if="isMpNavbar">
				<view class="mp-navbar-warp">
					<icon class="iconfont icon-back" v-if="isCurpage" @tap="onBack"></icon>
					<icon class="iconfont icon-menu" @tap="onClickMenu"></icon>
				</view>
				<view class="menu-warp" :style="{'display':(menu ? 'block' : 'none')}">
					<navigator url="/pages/index/index" open-type="reLaunch" hover-class="none" class="list">
						<icon class="iconfont icon-home"></icon>
						<text>{{$t('lang.home')}}</text>
					</navigator>
					<navigator url="/pages/search/search" open-type="navigate" hover-class="none" class="list">
						<icon class="iconfont icon-search-alt"></icon>
						<text>{{$t('lang.search')}}</text>
					</navigator>
					<navigator url="/pages/category/category" open-type="reLaunch" hover-class="none" class="list">
						<icon class="iconfont icon-category-alt"></icon>
						<text>{{$t('lang.category')}}</text>
					</navigator>
					<navigator url="/pages/cart/cart" open-type="reLaunch" hover-class="none" class="list">
						<icon class="iconfont icon-cart-alt"></icon>
						<text>{{$t('lang.cart')}}</text>
					</navigator>
					<navigator url="/pages/user/user" open-type="reLaunch" hover-class="none" class="list">
						<icon class="iconfont icon-user-portrait"></icon>
						<text>{{$t('lang.my')}}</text>
					</navigator>
				</view>
				<view class="menu-mask" :style="{'display':(menu ? 'block' : 'none')}" @tap="onMenuMask"></view>
			</view>
			<view class="uni-navbar-container">
				<view v-if="title.length" class="uni-navbar-container-title">{{title}}</view>
				<!-- 标题插槽 -->
				<slot></slot>
			</view>
			<view class="uni-navbar-header-btns" @tap="onClickRight" v-if="isRightState">
                <view v-if="rightIcon.length" class="goods-img">
					<text class="txt" v-if="isUnread"></text>
                    <uni-icons :type="rightIcon" :color="color" size="24"></uni-icons>
                </view>
                <!-- 优先显示图标 -->
                <view v-if="rightText.length&&!rightIcon.length" class="uni-navbar-btn-text">{{rightText}}</view>
                <slot name="right"></slot>
            </view>
		</view>
	</view>
</template>

<script>
    import uniStatusBar from './uni-status-bar.vue'
    import uniIcons from '@/components/uni-icons/uni-icons.vue';

    export default {
		data() {
			return {
				menu:false,
				liststatus:[]
			};
		},
        components: {
            uniStatusBar,
            uniIcons
        },
        props: {
            /**
             * 标题文字
             */
            title: {
                type: String,
                default: ''
            },
            /**
             * 左侧按钮文本
             */
            leftText: {
                type: String,
                default: ''
            },
            /**
             * 右侧按钮文本
             */
            rightText: {
                type: String,
                default: ''
            },
            /**
             * 左侧按钮图标
             */
            leftIcon: {
                type: String,
                default: ''
            },
            /**
             * 右侧按钮图标
             */
            rightIcon: {
                type: String,
                default: ''
            },
            /**
             * 是否固定在顶部
             */
            fixed: {
                type: [Boolean, String],
                default: false
            },
            /**
             * 按钮图标和文字颜色
             */
            color: {
                type: String,
                default: '#000000'
            },
            /**
             * 背景颜色
             */
            backgroundColor: {
                type: String,
                default: '#FFFFFF'
            },
            /**
             * 是否包含状态栏，默认固定在顶部时包含
             */
            statusBar: {
            	type: [Boolean, String],
            	default: false
            },
            /**
             * 是否使用阴影，默认根据背景色判断
             */
            shadow: {
                type: String,
                default: ''
            },
			/**
			* 新增
			* 是否显示左侧按钮
			*/
			leftState: {
				type:[Boolean, String],
				default:true
			},
			/**
			* 新增
			* 是否显示右侧按钮
			*/
			rightState: {
				type:[Boolean, String],
				default:true
			},
			/**
			* 新增
			* 判断app顶部距离样式
			*/
			titleNView:{
				type:[Boolean, String],
				default:false
			},
			/**
			* 新增
			* 判断小程序
			*/
		   mpNavbar:{
			   type:[Boolean, String],
			   default:false
		   },
		   /**
			* 新增
			* 判断url
			*/
		   curpage:{
			   type:String,
			   default:''
		   },
		   /**
			* 新增
			* 判断消息提示是否存在
			*/
		   isUnread:{
			   type:Boolean,
			   default:false
		   },
        },
        computed: {
			istitleNView(){
				return String(this.titleNView) === 'true'
			},
            isFixed() {
                return String(this.fixed) === 'true'
            },
			isLeftState(){
				return String(this.leftState) === 'true'
			},
			isRightState(){
				return String(this.rightState) === 'true'
			},
			isMpNavbar(){
				return String(this.mpNavbar) === 'true'
			},
			isCurpage(){
				return this.curpage ? true : false
			},
            insertStatusBar() {
                switch (String(this.statusBar)) {
                    case 'true':
                        return true
                    case 'false':
                        return false
                    default:
                        return this.isFixed
                }
            },
            hasShadow() {
                var backgroundColor = this.backgroundColor
                switch (String(this.shadow)) {
                    case 'true':
                        return true
                    case 'false':
                        return false
                    default:
                        return backgroundColor !== 'transparent' && backgroundColor.indexOf('rgba') < 0
                }
            }
        },
        methods: {
            /**
             * 左侧按钮点击事件
             */
            onClickLeft() {
              //  this.$emit('clickLeft')
                this.$emit('click-left')
            },
            /**
             * 右侧按钮点击事件
             */
            onClickRight() {
               // this.$emit('clickRight')
                this.$emit('click-right')
            },
			/**
			 * 
			 */
			onClickMenu(){
				console.log(111)
				this.menu = !this.menu
			},
			onMenuMask(){
				this.menu = false
			},
			onBack(){
				uni.navigateBack({
					delta:1
				})
			}
        }
    }
</script>

<style>
    .uni-navbar {
        display: block;
        position: relative;
        width: 100%;
        background-color: #FFFFFF;
        /* overflow: hidden; */
    }
	.app-nav-top{
		/* padding-top: 60upx; */
	}
	/* .uni-navbar view{
		line-height:44px;
	} */
    .uni-navbar-shadow {
        box-shadow: 0 1px 6px #ccc;
    }

    .uni-navbar.uni-navbar-fixed {
        position: fixed;
        z-index: 998;
    }

    .uni-navbar-header {
        display: flex;
        flex-direction: row;
        width: 100%;
        height:44px;
        line-height:44px;
        font-size: 16px;
    }
	
	.uni-navbar-header .uni-navbar-header-btns{
		display:inline-flex;
		flex-wrap:nowrap;
		flex-shrink:0;
		width: 120upx;
		padding:0 20upx;
	}
	
	.uni-navbar-header .uni-navbar-header-btns:first-child{
		padding-left:0;
	}
	.uni-navbar-header .uni-navbar-header-btns:last-child{
		width: 48upx;
		padding-left: 0upx;
	}
	.uni-navbar-container{
		width:100%;
		margin:0 20upx;
	}
	.uni-navbar-container-title{
		font-size:36upx;
		text-align:center;
		/* padding-right: 60upx; */
	}
	
	/*新增小程序自定义导航*/
	.uni-mp-navbar{ position: relative; display: flex; justify-content: flex-start; align-items: center;}
	.uni-mp-navbar .mp-navbar-warp{ display: flex; flex-direction: row; align-items: center; justify-content: center;}
	.uni-mp-navbar .mp-navbar-warp .iconfont{ color: #000000; font-weight: bold; padding-left: 20upx;}
	.uni-mp-navbar .mp-navbar-warp .icon-menu{ padding-left:40upx; font-size: 40upx; margin-top: -9upx; }
	.uni-mp-navbar .menu-warp{ position: absolute; background: #FFFFFF; top: 100upx; left: 20upx; display: flex; flex-direction: column; z-index: 100; box-shadow: 0px 0px 2px rgba(61,52,75,0.5); width: 220upx; border-radius: 10upx; z-index: 999;}
	.uni-mp-navbar .menu-warp .list{ padding: 10upx 30upx; border-bottom: 2upx solid #F1F0F6; color: #666666; display: flex; flex-direction: row; justify-content: flex-start; align-items: center;}
	.uni-mp-navbar .menu-warp .list:last-child{ border-bottom: 0;}
	.uni-mp-navbar .menu-warp .list .iconfont{ color: #666666; margin-right: 20upx;}
	.uni-mp-navbar .menu-warp::after{
		content: '';
		position: absolute;
		display: inline-block;
		top: -12upx;
		left: 80upx;
		width: 0;
		height: 0;
		border-style: solid;
		border-width: 12upx;
		border-color:transparent transparent #fff #fff;
		transform: rotate(-45deg);
		/* margin-left: -7upx; */
		box-shadow: 1upx -1upx 4upx rgba(61,52,75,0.5);
		background: #fff;
	}
	.uni-mp-navbar .menu-warp::before{
		content:'';
		position: absolute;
		z-index: 3;
		width: 35upx;
		height: 18upx;
		background: #fff;
		top: 0;
		left: 75upx;
		/* transform: translate(-50%,0); */
	}
	.goods-img {
		/* width: 80upx;
		height: 80upx;
		border-radius: 10upx; */
		position: relative;
		/* border: 1px solid #666666; */
	}
	.goods-img .txt{
		background: #fff;
		height: 10upx;
		width: 10upx;
		position: absolute;
		right:-8upx;
		top: 10upx;
		z-index: 99;
		border-radius: 50%;
	}
	.menu-mask{ position: fixed;z-index: 998;top: 0;right: 0;bottom: 0;left: 0;background:transparent;}
</style>
