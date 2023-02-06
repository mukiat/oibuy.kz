<template>
    <div class="search">
        <template v-if="bSuspend">
            <div class="search-warp" :style="{position:'fixed',width:preview ? '318px' : '',left:authority == 'view' ? '0' : ''}">
                <header :style="{'background-color':oPosition.backgroundColor}">
                    <template v-if="bLogonShow">
                        <img class="icon" :src="data.allValue.img" v-if="data.allValue.img" />
                        <img class="icon" src="@/assets/img/default-img.jpg" alt="" v-else>
                    </template>
                    <section class="input" @click="searchHref" :style="{'color':sFontColor}">
                        <i class="iconfont icon-search"></i>{{searchValue}}
                    </section>
                    <a href="javascript:;" class="message" @click="messageHref" v-if="bMessage" :style="{'color':sFontColor}">
                        <i class="iconfont icon-message"></i>
                    </a>
                </header>
                <div class="mask"></div>
            </div>
        </template>
        <template v-else>
            <div class="search-warp" :class="{'position-fixed': scrollFixed}" :style="{'top':topHeight}">
                <header :style="{'background-color':oPosition.backgroundColor}">
                    <template v-if="bLogonShow">
                        <img class="icon" :src="data.allValue.img" v-if="data.allValue.img" />
                        <img class="icon" src="@/assets/img/default-img.jpg" alt="" v-else>
                    </template>
                    <section class="input" @click="searchHref">
                        {{searchValue}}<i class="iconfont icon-search"></i>
                    </section>
                    <a href="javascript:;" class="message" @click="messageHref" v-if="bMessage" :style="{'color':sFontColor}">
						<i class="mess" v-if="isUnread"></i>
                        <i class="iconfont icon-home-xiaoxi"></i>
                    </a>
                </header>
            </div>
            <div class="seize-seat" v-if="scrollFixed"></div>
        </template>
    </div>
</template>

<script>
//node library
import url from 'url'
import qs from 'qs'

//mixins
import frontendGet from '@/mixins/frontend-get'
import formProcessing from '@/mixins/form-processing'

export default {
    name: "search",
    props: ["data", "preview","scrollFixed","localShowVal","shopId","fristBackgroundColor"],
    mixins: [frontendGet,formProcessing],
    components: {},
    data() {
        return {
            nSearchOpacity: 0,
            lbsCityName: "Анықтау...",
            sFontColor: "#ffffff",
			liststatus:[],
            topHeight:localStorage.getItem('localShow') === 'true' ? (this.scrollFixed ? '5rem' : '0') : 0,
            shop_id:0
        }
    },
    created() {
        //判断是否安装im客服
        if(this.mobile_kefu){
		  this.default()
        }
    },
    activated(){
        this.shop_id = this.shopId;
    },
    mounted() {
        // let sLbsCityName = qs.parse(document.cookie.replace(/; /ig, '&')).lbs_city_name
        // if (sLbsCityName) {
        //     this.lbsCityName = unescape(sLbsCityName)
        // }
        // if (!this.bStore && !sLbsCityName) {
        //     /**
        //     * Gps 
        //     **/
        //     let geolocation = new qq.maps.Geolocation(this.sTenKey, "h5")
        //     let options = { timeout: 8000 }
        //     geolocation.getLocation(this.showPosition, this.showErr, options)
        // }
        //this.searchScrollTop()
    },
    methods: {
        showPosition(position) {
            if (position.city) {
                this.lbsCityName = this.formatCity(position.city)
            }
        },
        showErr(err) {
            return false
        },
        /*
        * 格式化城市 将城市 “市” 去掉
        */
        formatCity(city) {
            let lastIndex = city.length - 1
            return city.charAt(lastIndex) == "Облыс" ? city.slice(0, lastIndex) : city
        },
        /**
         * 计算头部搜索与滚动条位置动态显示背景颜色
        */
        searchScrollTop() {
            this.$nextTick(() => {
                let oDomScroll = null,
                    nSearchOpacity = 0,
                    vm = this
                this.preview ? oDomScroll = document.body.querySelector(".phone-edit-con") : oDomScroll = document.body
                oDomScroll.onscroll = function () {
                    let nScrollT = oDomScroll.scrollTop
                    nSearchOpacity = nScrollT / 10 / 16
                    if (nSearchOpacity >= 1) {
                        nSearchOpacity = 1
                    }
                    vm.nSearchOpacity = nSearchOpacity
                    if (nScrollT > 120) {
                        vm.sFontColor = vm.getText({
                            dataNext: "allValue",
                            attrName: "fontColor",
                            defaultValue: "#333333"
                        })
                    } else {
                        vm.sFontColor = "#ffffff"
                    }
                }
            })
        },
		//消息通知
		default () {
			let o = {
				page: 1,
				size: 10
			}
			this.$http.get(`${window.ROOT_URL}api/chat/sessions`, {
				params: o
			}).then(res => {
				if (res.data.status == 'success') {
				    this.liststatus = res.data.data
				}
			})
		},
        messageHref(){
            if(this.authority == 'view'){
                if(this.mobile_kefu){
                    this.$router.push({
                        name:'messagelist'
                    })
                }else{
                    this.$router.push({
                        name:'message'
                    })
                }
            }
        },
        searchHref(){
            if(this.authority == 'view'){
                this.$router.push({
                    name:'search',
                    query:{
                        shop_id:this.shop_id
                    }
                })
            }
        }
    },
    computed: {
        bStore() {
            return window.shopInfo.ruid != 0
        },
        searchValue() {
            return this.getText({
                dataNext: "allValue",
                attrName: "searchValue",
                defaultValue: this.$t('lang.search_goods_placeholder')
            })
        },
        sTenKey() {
            return this.getText({
                dataNext: "allValue",
                attrName: "tenKey",
                defaultValue: "F75BZ-54UKV-6AGPT-UYF6Z-BLUBV-22BAE"
            })
        },
        bMessageUnread() {

        },
        bLogonShow(){
            return this.data.isLogoSel == "0" ? true : false
        },
        bPosition() {
            return this.data.isPositionSel == "0" && window.shopInfo.ruid == 0 ? true : false
        },
        bMessage() {
            return this.data.isMessageSel == "0" && window.shopInfo.ruid == 0 ? true : false
        },
        bSuspend() {
            return this.data.isSuspendSel == "0" ? true : false
        },
        sBgColor() {
            return this.getText({
                dataNext: "allValue",
                attrName: "bgColor",
                defaultValue: "#ff495e"
            })
        },
        oPosition() {
            let o = {}
            if (this.bSuspend) {
                o.position = "fixed"
                let aRgbVal = this.sBgColor.colorRgb(0, true)
                //o.backgroundColor = `rgba(${aRgbVal[0]},${aRgbVal[1]},${aRgbVal[2]},${this.nSearchOpacity})`
            } else {
                o.position = "relative"
                o.backgroundColor = !this.scrollFixed ? this.sBgColor : this.fristBackgroundColor
            }
            return o
        },
        authority(){
            return window.apiAuthority
        },
        isUnread(){
            let i = 0
            this.liststatus.forEach((res)=>{
                if(res.unread){
                    i++
                }
            })
            
            return i > 0 ? true : false
        }
    },
    watch:{
        scrollFixed(){
            this.topHeight = localStorage.getItem('localShow') === 'true' ? (this.scrollFixed ? '5rem' : '0') : 0
        },
        localShowVal(){
            if(!this.localShowVal){
                this.topHeight = 0
            }
        }
    }
}

</script>

<style lang="scss" scoped>
@import "../../../assets/style/config";
@import "../../../assets/style/mixins/common";
.search{

}
.search-warp {
    width: 100%;
    height: auto;
    overflow: hidden;
    z-index: 11;
}

.search header {
    left: 0;
    right: 0;
    padding: 1rem;
    height: 5rem;
    @include disFlex();
}

.seize-seat{
    height: 5rem;
}

.position-fixed{
    position: fixed;
    z-index: 1999;
    top: 0;
}

.search a {
    color: #fff;
    text-align: left;
    font-size: 1.2rem;
    @include disFlex();
    @include direction(center, center);
}

.search a span {
    margin-left: .1rem;
}

.search a.message {
    position: relative;
    text-align: right;
}
.message .mess{
	position: absolute;
	width: 0.5rem;
	height: 0.5rem;
	background-color: #FFF;
	border-radius: 50%;
	right: -0.6rem;
	top: 0.3rem;
	z-index: 999999;
}
.message span {
    background: #ff0000;
    display: block;
    width: .8rem;
    height: .8rem;
    position: absolute;
    top: .3rem;
    right: -.2rem;
    border-radius: 9999px;
    border: 1px solid rgba(255, 255, 255, .8);
}

.search .iconfont {
    font-size: 2rem;
}

.search .icon-moreunfold {
    font-size: .9rem;
    margin-left: .18rem;
    font-weight: bold;
}

.search .icon {
    width: 2.8rem;
    height: 2.8rem;
}

.search .input {
    font-size: 1.3rem;
    /*height: 2.86rem;
    line-height: 2.86rem;*/
    padding: 0 1rem 0 2rem;
    margin: 0 .6rem 0 0;
    border-radius: 1.43rem;
    color: #999;
    position: relative;
    background: rgba(255, 255, 255, 1);
    @include flex1-1();
    @include disFlex();
    @include direction(center, space-between);
}

.search .input .icon-search {
    font-size: 1.4rem;
    margin-right: .4rem;
    margin-top: .3rem;
}

.search .input a {
    @include urlAbsolute()
}

.search .mask {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    z-index: -1;
    background: linear-gradient(to bottom, rgba(0, 0, 0, .8) 0%, rgba(0, 0, 0, 0) 100%);
}
</style>