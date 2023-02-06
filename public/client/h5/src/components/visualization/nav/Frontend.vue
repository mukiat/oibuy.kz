<template>
    <nav class="nav" :class="aClass">
        <swiper class="swiper" :options="swiperOption" ref="slideSwiper" v-if="showStyle == 2 && isSwiperShow">
            <swiper-slide v-for="(list,index) in swiperList" :key="index">
                <section class="list" v-for="(item, index) in list" :key="index" :style="liStyle">
                    <a v-href="{sUrl:item.url,preview:preview}"></a>
                    <img class="icon" src="@/assets/img/default-img.jpg" v-if="!item.img" />
                    <img class="icon" v-lazy="item.img" v-else />
                    <div class="txt" v-if="bDesc">
                        <template v-if="item.desc">
                            {{ item.desc }}
                        </template>
                        <template v-else>
                            [{{$t('lang.describe')}}]
                        </template>
                    </div>
                    <i class="iconfont icon-more"></i>
                </section>
            </swiper-slide>
            <!-- 分页器 -->
            <div class="swiper-pagination" slot="pagination"></div>
        </swiper>

        <section class="list" v-for="(item, index) in data.list" :key="index" :style="liStyle" v-else>
            <a v-href="{sUrl:item.url,preview:preview}"></a>
            <img class="icon" :src="item.img" v-lazy="item.img" v-if="bIcon && item.img" />
            <img class="icon" src="@/assets/img/default-img.jpg" v-else />
            <div class="txt" v-if="bDesc">
                <template v-if="item.desc">
                    {{ item.desc }}
                </template>
                <template v-else>
                    [{{$t('lang.describe')}}]
                </template>
            </div>
            <i class="iconfont icon-more"></i>
        </section>
    </nav>
</template>

<script>
// custom components

// third party components
import {
    swiper,
    swiperSlide
} from 'vue-awesome-swiper'

export default {
    name: 'nav-head',
    props: ['data', 'preview'],
    components: {
        swiper,
        swiperSlide,
    },
    data() {
        return {
            swiperOption:{
                pagination:{
                    el:'.swiper-pagination',
                },
                slidesPerView:1,
                autoplay:false,
                loop:false
            }
        }
    },
    created() {

    },
    computed: {
        liStyle() {
            if (this.showStyle == 0) return false
            let nWidth = 100 / this.showNumber
            return {
                width: nWidth + "%"
            }
        },
        bIcon() {
            return this.data.isIconSel == "0" ? true : false
        },
        bDesc() {
            if (this.showStyle == "1") {
                return this.data.isDescSel == "0" ? true : false
            } else {
                return true
            }
        },
        listStyle() {
            let style = ''
            if(this.showStyle == "0"){
                style = 'list-style1'
            }else if(this.showStyle == "1"){
                style = 'list-style2'
            }else{
                style = 'list-style2 list-style3'
            }
            return style
        },
        showStyle() {
            return this.data.isStyleSel
        },
        showNumber() {
            return this.data.isNumberSel
        },
        aClass() {
            let arr = []
            arr.push(this.listStyle)
            if (this.listStyle == "list-style2") {
                this.data.styleSelList.map((v, i) => {
                    switch (v) {
                        case "0":
                            arr.push("whole-margin")
                            break;
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
            }
            return arr
        },
        isSwiperShow(){
            return this.data.list.length > this.showNumber * 2
        },
        swiperList(){
            var list = this.data.list;
            var len = list.length;
            var chunk = this.showNumber*2;
            var result = [];
            var v = len / chunk;
            
            if(this.showStyle == 2 && this.isSwiperShow){
                for(var i = 0; i < len; i += chunk){
                    result.push(list.slice(i,i+chunk));
                }
            }
            
            return result
        }
    }
}

</script>

<style lang="scss" scoped>
@import '../../../assets/style/config.scss';
@import '../../../assets/style/mixins/common.scss';
.nav {
    background: #fff;
    overflow: hidden;
}

.nav .list {
    overflow: hidden;
    box-sizing: content-box;
    position: relative;
}

.nav .list img {
    display: block;
}

.nav .txt {
    font-size: 1.5rem;
    color: $text-title-color;
    @include flex1-1();
    min-width: auto;
    min-height: auto;
}

.list a {
    border-left: 0;
    @include urlAbsolute();
}

.list-style1 {
    padding-bottom: 0;
    padding: 0 $padding-all;
}

.list-style1 .list {
    height: 4.8rem;
    border-bottom: 1px solid $border-color-split;
    @include box-pack(center, start);
    @include direction(center, initial)
}

.list-style1 .list .icon-more {
    color: $title-color;
    font-size: 1.2rem;
}

.list-style1 .list:last-of-type {
    border-bottom: 0;
}

.list-style1 .list img {
    height: 64%;
    width: auto;
    margin-right: .5rem;
}

.list-style2 .list {
    width: 20%;
    float: left;
    text-align: center;
    /*border-bottom: 0 !important;*/
    /*padding-top: $padding-all*1.2;*/
    overflow: hidden;
}

.list-style2 .list img {
    width: 100%;
    height: auto;
    margin: 0 auto;
    display: block;
}

.list-style2 .list .txt {
    margin-top: .2rem;
    font-size: 1.3rem;
    height: 1.6rem;
    overflow: hidden;
    text-overflow: ellipsis;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
}

.list-style2 .list .icon-more {
    display: none;
}

.list-style2.whole-margin {
    padding: $padding-all 0;
}

.list-style2.all-padding section {
    box-sizing: border-box;
    padding: .4rem;
}

.list-style2.all-border {}

.list-style2.all-border section {
    box-sizing: border-box;
    margin-bottom: -1px;
    border: 1px solid $body-background;
    border-right: 0;
}

/* 新版样式3 */
.swiper{ padding-bottom: 30px; }
.swiper .swiper-pagination /deep/ .swiper-pagination-bullet{ margin: 0; border-radius: 0; width: 15px; height: 4px; background:linear-gradient(-88deg,rgba(255,79,45,1),rgba(249,31,39,1)); opacity: .2;}
.swiper .swiper-pagination /deep/ .swiper-pagination-bullet:first-child{ border-radius: 2px 0 0 2px; }
.swiper .swiper-pagination /deep/ .swiper-pagination-bullet:last-child{ border-radius: 0 2px 2px 0; }
.swiper .swiper-pagination /deep/ .swiper-pagination-bullet-active{ opacity: 1; border-radius: 2px !important; }

.list-style3{
    margin: 1rem 1rem 0;
    padding: 0;
    border-radius: 1rem 1rem 0 0; 
}
</style>