<template>
    <view class="jigsaw" :class="aJigsawClass">
		<view class="content">
			<view class="big-img" :class="aClass" :style="{'width':aStyle1Width0+'%'}" v-if="styleSel == '0' && module.list[0]" @click="link(module.list[0])">
				<view class="desc" v-if="module.list[0].desc!=''">{{ module.list[0].desc }}</view>
				<view class="icon"><image :src="module.list[0].img" mode="widthFix" v-if="module.list[0].img" class="image"></image></view>
			</view>
			<view :class="aClass" :style="{'width':aStyle1Width1+'%'}">
				<view class="items" v-if="styleSel == '0'">
					<view class="item" v-for="(item, index) in imgList" :key="index" :class="s2Class" :style="{'width':style1RightW}" @click="link(item)">
						<view class="desc" v-if="item.desc">{{ item.desc }}</view>
						<view class="icon"><image :src="item.img" mode="widthFix" v-if="item.img" class="image"></image></view>
					</view>
				</view>
				<view class="items" v-else>
					<view class="item" v-for="(item,index) in imgList" :key="index" :class="s2Class" :style="{'width':aStyle2Width[index]+'%'}" @click="link(item)">
						<view class="desc" v-if="item.desc">{{ item.desc }}</view>
						<view class="icon"><image :src="item.img" mode="widthFix" v-if="item.img" class="image"></image></view>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
export default{
	props: ['module', 'preview'],
	data(){
		return {}
	},
	computed:{
		imgList() {
            let arr = []
            this.module.list.map((v, i) => {
                arr.push(v)
            })
            if (this.styleSel == "0") {
                arr.splice(0, 1)
                return arr
            }
            return arr
        },
        style1RightW() {
            let nStyle1Right = this.module.allValue.showStyle1Right,
                nEvenW = 0
            if (nStyle1Right == "") nStyle1Right == 2
            nEvenW = 100 / Number(nStyle1Right)
            return this.styleSel == "0" ? nEvenW + "%" : ""

        },
        aStyle1Width0() {
            return this.aStyle1Width.length > 0 ? this.aStyle1Width[0] : ""
        },
        aStyle1Width1() {
            return this.aStyle1Width.length > 0 ? this.aStyle1Width[1] : "100"
        },
        aStyle1Width() {
            let arrNum = this.module.allValue.showStyle1Size.split(":"),
                nEven = 0,
                nEvenW = 0,
                aStyleW = []
            arrNum.forEach((v) => {
                nEven += Number(v)
            })
            nEvenW = 100 / nEven
            arrNum.forEach((v) => {
                aStyleW.push(Number(v) * nEvenW)
            })
            return this.styleSel == "0" ? aStyleW : []
        },
        aStyle2Width() {
            let arrNum = this.module.allValue.showStyle2Size.split(":"),
                nEven = 0,
                nEvenW = 0,
                aStyleW = []
            arrNum.forEach((v) => {
                nEven += Number(v)
            });
            nEvenW = 100 / nEven
            arrNum.forEach((v) => {
                aStyleW.push((Number(v) * nEvenW))
            })
            if (this.styleSel == "1") return aStyleW
        },
        styleSel() {
            return this.module.isStyleSel
        },
        s2Class() {
            if (this.styleSel != "0") return 'f-left'
        },
        aClass() {
            let arr = []
            this.module.isPositionSel == "0" ? arr.push("f-left") : arr.push("f-right")
            this.styleSel == "0" ? arr.push("w50deg") : arr.push("w100deg")
            return arr
        },
        aJigsawClass() {
            let arr = []
            arr.push(this.listStyle)
            this.module.styleSelList.map((v, i) => {
                switch (v) {
                    case "1":
                        arr.push("all-padding")
                        break;
                    case "2":
                        arr.push("all-border")
                        break;
                    default:
                        break;
                }
            })
            return arr
        }
	},
	methods:{
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
		}
	}
}
</script>

<style>
.jigsaw {
    overflow: hidden;
    background: #fff;
}

.jigsaw .content .icon{ width: 100%; }
.jigsaw .content .icon .image{ width: 100%; height: auto; margin: 0 auto; display: block; }
.jigsaw .content .big-img{ float: left; position: relative;}
.jigsaw .content .big-img .desc{position: absolute;color: #fff; font-size: 25upx;bottom: 0;padding: 8upx 12upx; background: rgba(0,0,0,.6); box-sizing:border-box; left: 0; right: 0; margin: 8upx 12upx;}
.jigsaw .content .items .item{ float: left; border-right: 0; border-left: 0; position: relative;}
.jigsaw .content .items .item .desc{ position: absolute;color: #fff; font-size: 25upx;bottom: 0;padding: 8upx 12upx; background: rgba(0,0,0,.6); box-sizing:border-box;left: 0; right: 0; margin: 8upx 12upx;}

.jigsaw.all-padding .content .big-img,
.all-padding .items .item{
	box-sizing: border-box;
	padding: 10upx;
}
.jigsaw.all-border .big-img{
	border-top: 1px solid #e7ecec;
}
.jigsaw.all-border .items .item{
	border-left: 1px solid #e7ecec;
	border-top: 1px solid #e7ecec;
	box-sizing: border-box;
}
.jigsaw .content .big-img.f-right{
	float: right;
}
</style>
