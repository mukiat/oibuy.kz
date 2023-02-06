<template>
    <view class="page-section" :class="{'slide-new':bStyleSel == 2}">
		<view class="bg-ellipse" :style="{'background-color':backgroundColor}" v-if="bStyleSel == 2"></view>
    	<view class="page-section-spacing" v-if="!bSeparateShow">
    		<swiper class="swiper" :indicator-dots="false" :autoplay="autoplay" :interval="interval" :duration="duration" :style="{height:imgheights + 'px'}" @change="slideChange">
    			<swiper-item v-for="(item,index) in module.list" :key="index">
    				<view class="swiper-item" @click="link(item)">
    					<view class="desc" v-if="item.desc != ''">{{ item.desc }}</view>
    					<image :src="item.img" mode="widthFix" @load="imageLoad" class="image"></image>
    				</view>
    			</swiper-item>
    		</swiper>
			<view class="dots-container" :class="bPaginationSel">
				<view v-for="(ad, index) in module.list" :key="index" :class="['dot', index === activeIndex ? 'active' : '']"></view>
			</view>
    	</view>
		<view v-else class="separat-show" :class="{'mini': bMiniList,'separat-show-new':bSeparateSel}">
			<view v-for="(item,index) in module.list" :key="index" class="item" @click="link(item)">
				<view class="desc" v-if="item.desc != ''">{{ item.desc }}</view>
				<image :src="item.img || item.goods_img" mode="widthFix" style="width: 100%;"></image>
			</view>
		</view>
    </view>
</template>

<script>
export default{
	props:['module', 'preview', 'modulesIndex'],
	data(){
		return {
			autoplay: true,
			interval: 5000,
			duration: 500,
			imgheights:'',
			activeIndex:0,
			marginWidth:0
		}
	},
	mounted() {
		if(this.bStyleSel == 2){
			this.$store.dispatch('updateGlobalBgColor', {
				bgColor:this.backgroundColor
			})
		}
	},
	computed:{
		bStyleSel(){
			return this.module.isStyleSel
		},
		bSeparateShow() {
            return '1' == this.module.isStyleSel ? true : false
        },
        bMiniList() {
            return '1' == this.module.isSizeSel ? true : false
        },
		indicatorDots(){
			return this.module.list.length > 1 ? true : false
		},
		bPaginationSel(){
			let sel = ''

			if(this.module.isPaginationSel == 0){
				sel = 'pagination-left'
			}else if(this.module.isPaginationSel == 1){
				sel = 'pagination-center'
			}else{
				sel = 'pagination-right'
			}

			return sel
		},
		bSeparateSel(){
			return this.module.isSeparateSel == '1' ? true : false
		},
		backgroundColor(){
			return this.module.list.length > 0 ? this.module.list[this.activeIndex].bgColor : ''
		},
	},
	methods:{
		imageLoad(e){
			this.marginWidth = this.bStyleSel == 2 ? 20 : 0;
			
			let imgwidth = e.detail.width,
				imgheight = e.detail.height,
				//设备宽度
				windowWidth = uni.getSystemInfoSync().windowWidth - this.marginWidth;
			
			this.imgheights = (windowWidth/imgwidth) * imgheight
		},
		link(item){
			// #ifdef APP-PLUS
			let page = item.appPage ? item.appPage : item.url
			let built = item.appPage ? 'app' : 'undefined'
			// #endif
			
			// #ifdef MP-WEIXIN
			let page = item.appletPage ? item.appletPage : item.url
			let built = item.appletPage ? 'app' : 'undefined'
			// #endif
			
			this.$outerHref(page,built)
		},
		slideChange(e){
			this.activeIndex = e.detail.current;
			if(this.bStyleSel == 2){
				this.$store.dispatch('updateGlobalBgColor', {
					bgColor:this.backgroundColor
				})
			}
		}
	},
	watch:{
		bSeparateShow(){
			console.log(this.bSeparateShow)
		}
	}
}
</script>

<style>
.page-section-spacing{ position: relative;}

.page-section{ overflow: hidden;line-height: 0; font-size: 0;}
.page-section .swiper-item{ position: relative; line-height: 0; font-size: 0;}
.page-section .swiper-item .image{ width: 100%; }
.page-section .swiper-item .desc{box-sizing: border-box; position: absolute; left: 10upx; right: 10upx; bottom: 0; background: rgba(0, 0, 0, .5); color: #FFFFFF;}
.separat-show{ display: flex; flex-direction: column; font-size: 0; line-height: 0;margin-top: -0.5px;}
.separat-show .item{ width: 100%; position: relative; line-height: 0; font-size: 0;}
.separat-show .desc{ box-sizing: border-box; position: absolute; left: 10upx; right: 10upx; bottom: 0; background: rgba(0, 0, 0, .5); color: #FFFFFF; }
.separat-show.mini{ display: block;}
.separat-show.mini .item{ width: 50%; float: left;}

/*新版轮播样式*/
.slide-new{
    padding: 0 20upx;
    position: relative;
    overflow: hidden;
}
.slide-new .bg-ellipse{
    border-radius: 30% 30%;
    height: 200upx;
    position: absolute;
    top: -80upx;
    left: 0;
    right: 0;
    z-index: 1;
}
.slide-new .page-section-spacing{
	position: relative;
	z-index: 2;
	border-radius: 20upx;
	overflow: hidden;
}
.dots-container {
  position: absolute;
  left: 20upx;
  right: 20upx;
  bottom: 20upx;
  display: flex;
  justify-content: center;
}

.dot {
  margin: 0 6upx;
  width: 12upx;
  height: 12upx;
  background: rgba(0, 0, 0, 0.2);
  opacity: 1;
  border-radius: 6upx;
}
.dot.active {
  background: rgba(0, 0, 0, .5);
}
.dots-container.pagination-left{ justify-content: flex-start;}
.dots-container.pagination-center{ justify-content: center;}
.dots-container.pagination-right{ justify-content: flex-end;}

.separat-show-new{ padding: 20upx 20upx 0 20upx;}
</style>
