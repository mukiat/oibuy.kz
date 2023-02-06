<template>
    <view class="title">
		<view class="header" :style="{'text-align':sPosition,'background-color':bgColor}" v-if="bStyleSelTradition || bSimplify">
			<view class="tit" :class="{simplify:bSimplify}">
				<view class="name">
					<view class="tit">{{ title }}</view>
					<view class="link" v-if="bList && bStyleSelTradition">
						<text class="heng">-</text>
						<text @click="$outerHref(sUrl,module.list[0].appPage ? 'app' : 'undefined')" class="url">{{desc}}</text>
					</view>
				</view>
				<view class="more-link" v-if="bSimplify && bList">
					<view class="txt" @click="$outerHref(sUrl,module.list[0].appPage ? 'app' : 'undefined')">{{ desc }}</view>
					<uni-icons type="arrowright" size="20"></uni-icons>
				</view>
			</view>
			<view class="suptit" v-if="!bSimplify">
				<text v-if="!bDate" class="text">{{ module.allValue.fitTitle }}</text>
				<text class="text" v-else>{{ dateTime }}</text>
			</view>
		</view>
		<view class="header wx-header" v-else>
			<view class="tit">{{ title }}</view>
			<view class="suptit">
				<text v-if="dateTime">{{ dateTime }}</text>
				<text class="link" v-if="module.allValue.author">作者:{{ module.allValue.author }}</text>
                <text class="link2" v-if="bList && bWechat" @click="$outerHref(sUrl,module.list[0].appPage ? 'app' : 'undefined')">{{ desc }}</text>
			</view>
		</view>
	</view>
</template>

<script>
import uniIcons from '@/components/uni-icons/uni-icons.vue';
import universal from '@/common/mixins/universal.js';
export default{
	mixins:[universal],
	props: ['module', 'preview'],
	data(){
		return {}
	},
	components:{
		uniIcons,
	},
	computed: {
        dateTime() {
            let dateTime = this.module.allValue.dateTime
            if (dateTime != "") {
                return (new Date(dateTime)).format("yyyy-MM-dd HH:mm:ss")
            }
        },
        bDate() {
            return this.module.isDate == "0" ? false : true
        },
        bSimplify() {
            return this.module.isStyleSel == "2" ? true : false
        },
        bWechat(){
            return this.module.isStyleSel == "1" ? true : false
        },
        bStyleSelTradition() {
            return this.module.isStyleSel == "0" ? true : false
        },
        sPosition() {
            let sPositionSel = this.module.isPositionSel
            switch (sPositionSel) {
                case "0":
                    return "left"
                    break;
                case "1":
                    return "center"
                    break;
                case "2":
                    return "right"
                    break;
                default:
                    return "left"
                    break;
            }
        },
        title() {
            return this.getText({
                dataNext: "allValue",
                attrName: "title",
                defaultValue: "[编辑标题名]"
            })
        },
        desc() {
            if (this.bList) {
                return this.getText({
                    listIndex: 0,
                    attrName: "desc",
                    defaultValue: "[链接名]"
                })
            }

        },
        sUrl() {
            if (this.bList) {
                return this.getText({
                    listIndex: 0,
                    attrName: this.module.list[0].appPage ? 'appPage' : 'url',
                    defaultValue: "#"
                })
            }
        },
        bgColor() {
            return this.getText({
                dataNext: "allValue",
                attrName: "bgColor",
                defaultValue: "#FFFFFF"
            })
        },
        bList() {
            return 0 >= this.module.list.length ? false : true
        }
    }
}
</script>

<style scoped>
.header{ background: #FFFFFF; padding: 15upx 10upx;}
.tit{ overflow: hidden;line-height: 1.5;}
.tit .name{ font-size: 28upx; color: #000000; font-weight: 500;}
.tit .name .tit{ display: inline-block;}
.tit .name .link{ font-size: 25upx; color: #464c5b;display: inline-block;}
.tit .name .link .heng{ margin: 0 10upx;}
.tit .name .link .url{ color: #007AFF;}

.simplify{ display: flex; flex-direction: row; justify-content: space-between; align-items: center;}

.suptit{line-height: 1.2;}
.suptit .text{ font-size: 25upx; color: #999999;}

.wx-header .link{ margin-left: 15upx;}
.wx-header .link2{ font-size: 25upx; color: #3eb1fa;}
</style>
