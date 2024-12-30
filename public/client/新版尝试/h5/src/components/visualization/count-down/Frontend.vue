<template>
    <div class='count-down' :class="{'new-style':styleSel == 2}">
        <template v-if="styleSel != 2">
            <template v-if="!bEnd">
            <header v-if="styleSel == 0">
                <h4>
                    <img v-lazy="data.allValue.titleImg" alt="" v-if="data.allValue.titleImg">
                    <img src="@/assets/img/default-img.jpg" alt="" v-else>
                </h4>
                <section class="date">
                    <p v-if="!bEnd">
                        <!-- <span>{{ oDateTime.d }}</span>: -->
                        <span>{{ oDateTime.h }}</span>:
                        <span>{{ oDateTime.m }}</span>:
                        <span>{{ oDateTime.s }}</span>
                        <strong style="font-size:1.24rem;"><template v-if="type == 0">{{$t('lang.after_the_start')}}</template></strong>
                    </p>
                    <p style="font-size:1.3rem;" v-else>{{$t('lang.coming_soon')}}</p>
                </section>
                <template v-if="authority == 'view'">
                    <router-link :to="{name:'seckill'}" class="more-link">{{$t('lang.more')}}
                        <i class="iconfont icon-more"></i>
                    </router-link>
                </template>
                <template v-else>
                    <a href="javascript:void(0)" class="more-link">{{$t('lang.more')}}
                        <i class="iconfont icon-more"></i>
                    </a>
                </template>
            </header>
            <section class="count-down-big" v-else>
                <div class="big">
                    <router-link :to="{name:'seckill'}" v-if="authority == 'view'"></router-link>
                    <header>
                        <h4>
                            <img v-lazy="data.allValue.titleImg" alt="" v-if="data.allValue.titleImg">
                            <img src="@/assets/img/default-img.jpg" alt="" v-else>
                        </h4>
                        <section class="date">
                            <p v-if="!bEnd">
                            <!-- <span>{{ oDateTime.d }}</span>{{$t('lang.tian')}} -->
                            <span>{{ oDateTime.h }}</span>:
                            <span>{{ oDateTime.m }}</span>:
                            <span>{{ oDateTime.s }}</span>
                            <strong style="font-size:1.24rem;"><template v-if="type == 0">{{$t('lang.after_the_start')}}</template></strong>
                            </p>
                            <p style="font-size:1.3rem;" v-else>{{$t('lang.coming_soon')}}</p>
                        </section>
                        <p class="big-desc">{{ spikeDesc }}</p>
                    </header>
                    <img v-lazy="data.allValue.productImg" alt="" v-if="data.allValue.titleImg">
                    <img src="@/assets/img/default-img.jpg" alt="" v-else>
                </div>
                <div class="four-product">
                    <ul>
                        <li v-for="item in spikeBigProlist" :key="item.goods_id">
                            <router-link :to="{name:'seckill-detail',query:{seckill_id:item.id}}" v-if="authority == 'view'"></router-link>
                            <img v-lazy="item.goods_thumb" alt="">
                            <span class="price" v-html="item.price_formated"></span>
                            <del v-html="item.market_price_formated"></del>
                        </li>
                    </ul>
                </div>
            </section>
            <section class="count-down-all" v-if="bSpikeSwiperProlist">
                <scroll-prolist :prolist="spikeSwiperProlist" bTitle="true" scrollNumber="3.4" :preview="preview"></scroll-prolist>
            </section>
            </template>
        </template>
        <!--新版样式-->
        <template v-else>
            <div class="seckill-new-style">
                <div class="seckill-header">
                    <div class="header-top">
                        <div class="tit-img">
                            <img v-lazy="data.allValue.titleImg" alt="" v-if="data.allValue.titleImg">
                            <img src="@/assets/img/default-img.jpg" alt="" v-else>
                        </div>
                        <div class="data" v-if="bEndTime">
                            <div class="data-txt" v-if="authority == 'view'">{{$t('lang.were_still_end')}}</div>
                            <count-down-view class="data-time" :endTime="bEndTime" :endText="$t('lang.activity_end')"></count-down-view>
                        </div>
                    </div>
                    <div class="header-time-slot">
                        <div class="item"
                        :class="{'active':firstId == item.id}"
                        v-for="(item,index) in seckillTime"
                        :key="index"
                        @click="seckillClick(item)"
                        >
                            <strong>{{item.title}}</strong>
                            <span v-if="item.status && !item.soon && !item.is_end">{{$t('lang.in_a_rush')}}</span>
                            <span v-if="!item.status && item.soon && !item.is_end">{{$t('lang.begin_minute')}}</span>
                            <span v-if="!item.status && !item.soon && item.is_end">{{$t('lang.has_ended')}}</span>
                        </div>
                    </div>
                </div>
                <div class="seckill-goods-list">
                    <section class="count-down-all">
                        <scroll-prolist :prolist="seckillGoodsList" bTitle="true" scrollNumber="3.1" :preview="preview" listType="seckill"></scroll-prolist>
                    </section>
                </div>
                <div class="more relative">
                    <router-link :to="{name:'seckill'}" class="absolute-link" v-if="authority == 'view'"></router-link>
                    <strong>{{$t('lang.view_more_count_down_goods')}}</strong>
                    <i class="iconfont icon-more"></i>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import qs from 'qs'

// custom components
import ScrollProlist from '../element/ScrollProlist'
import CountDown from '@/components/CountDown'

// third party components
import {
    swiper,
    swiperSlide
} from 'vue-awesome-swiper'

//mixins
import frontendGet from '@/mixins/frontend-get'
import countDown from '@/mixins/count-down'

export default {
    name: 'count-down',
    props: ['data', 'preview'],
    mixins: [frontendGet],
    components: {
        swiper,
        swiperSlide,
        ScrollProlist,
        "CountDownView": CountDown,
    },
    data() {
        return {
            spikeProlist: [],
            oDateTime: {
                d: "0",
                h: "00",
                m: "00",
                s: "00"
            },
            countDownAuto: null,
            type:0,
            seckillTime:[],
            seckillGoodsList:[],
            seckillTimeActive:[],
            firstId:0,
            status:0
        }
    },
    created() {
        if(this.styleSel != 2){
            this.$http.post(`${window.ROOT_URL}api/visual/seckill`, qs.stringify({
                num: this.nNumber
            })).then(({ data:{ data }}) => {
                if(data.type){
                    data.type == 0 ? this.endTime = data.begin_time : this.endTime = data.end_time
                    if (data.goods) this.spikeProlist = data.goods
                    this.type = data.type
                }else{
                    this.type = 0
                }
            }).catch(err => {
                console.error(err)
            })
        }else{
            this.getSeckillData('load');
        }
    },
    mounted() {
        this.getCountTimeObj()
        clearInterval(this.countDownAuto)
        this.countDownAuto = setInterval(this.getCountTimeObj, 1000)
    },
    methods: {
        getCountTimeObj() {
            let sEndTime = "",
                o
            o = countDown.getTime(this.endTime, this.type, true, true)
            if (o && this.endTime != "") {
                this.oDateTime.d = o.d
                this.oDateTime.h = o.h
                this.oDateTime.m = o.m
                this.oDateTime.s = o.s
            } else {
                this.oDateTime = {
                    d: "0",
                    h: "00",
                    m: "00",
                    s: "00"
                }
            }
        },
        seckillClick(item) {
            let o = {
                id:item.id,
                tomorrow:item.tomorrow || 0
            }
            this.firstId = item.id;
            this.getSeckillData('list',o)
        },
        getSeckillData(type,o){
            this.$http.get(`${window.ROOT_URL}api/visual/visual_seckill`,{ params:o }).then(({ data:{ data }}) => {
                if(type == 'load'){
                    this.seckillTime = data.time_list
                    this.seckillTimeActive = this.seckillTime[0]
                    this.firstId = this.seckillTime[0].id
                    this.status = this.seckillTime[0].status
                }

                this.seckillGoodsList = data.seckill_list
            }).catch(err => {
                console.error(err)
            })
        }
    },
    computed: {
        bEnd(){
            return this.oDateTime.d == '0' && this.oDateTime.h == '00' && this.oDateTime.m == '00' && this.oDateTime.s == '00'
        },
        bSpikeSwiperProlist() {
            if (this.styleSel == "0") {
                return true
            } else {
                return this.styleSel == "1" && this.spikeProlist.length > 4 ? true : false
            }
        },
        spikeSwiperProlist() {
            let arr = [];
            this.spikeProlist.map((v, i) => {
                arr.push(v)
            })
            if (this.styleSel == "1") arr.splice(0, 4)
            return arr
        },
        spikeBigProlist() {
            let arr = []
            if (this.spikeProlist.length > 0) {
                for (let i = 0; i < 4; i++) {
                    if(this.spikeProlist[i]){
                        arr.push(this.spikeProlist[i])
                    }else{
                        break;
                    }
                }
                return arr
            }

        },
        spikeDesc() {
            return this.getText({
                dataNext: "allValue",
                attrName: "spikeDesc",
                defaultValue: this.$t('lang.seckill_desc_placeholder')
            })
        },
        nNumber() {
            return this.data.allValue.number
        },
        styleSel: {
            get() {
                return this.data.isStyleSel
            },
            set(value) {
                this.updateRadioSel('isStyleSel', value)
            }
        },
        authority(){
            return window.apiAuthority
        },
        bEndTime(){
            let time = this.status ? this.seckillTimeActive.end_time : this.seckillTimeActive.begin_time
            return time
        }
    },
    watch: {
        "data.allValue.endTime": {
            handler(val, oldVal) {
                clearInterval(this.countDownAuto)
                this.countDownAuto = setInterval(this.getCountTimeObj, 1000)
                this.getCountTimeObj()
            }
        }
    }
}

</script>

<style lang="scss" scoped>
@import '../../../assets/style/config.scss';
@import '../../../assets/style/mixins/common.scss';
.count-down {
    background: #fff;
    overflow: hidden;
    position: relative;
}

.count-down-big {
    min-height: 12rem;
}

.count-down>header {
    font-size: 1.5rem;
    padding: 1rem $padding-all;
    color: $color;
    @include disFlex();
    @include direction(center, space-between);
}

.count-down header h4 {
    @include disFlex();
    @include direction(center, space-between);
    width: 7rem;
    height: auto;
}

.count-down header h4 img {
    width: 100%;
}

.count-down header section {
    color: #4f4f4f;
    @include direction(center, flex-start);
    margin-left: .6rem;
    @include flex1-1();
}

.count-down header .date span {
    font-size: 1.2rem;
    display: inline-block;
    height: 1.6rem;
    line-height: 1.6rem;
    text-align: center;
    min-width: 1.8rem;
    padding: 0 .4rem;
    background: #4f4f4f;
    color: #fff;
    margin: 0 .2rem;
    border-radius: 9999px;
}

.count-down header .date span:first-of-type {
    margin-left: 0;
}

.count-down header .icon-bolt {
    font-size: 1.2rem;
}

.count-down .big {
    width: 50%;
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 50%;
    padding: $padding-all*1.4;
    float: left;
    height: 100%;
    border-bottom: 1px solid $body-background;
    a {
        color: #333;
    }
}

.count-down-big {
    position: relative;
}

.count-down .big img {
    width: 100%;
    height: auto;
}

.count-down .big header {
    padding: 0;
}

.count-down .big header h4 {
    width: 8rem;
}

.count-down .big header .date {
    margin-left: 0;
    margin-top: .6rem;
}

.count-down .big-desc {
    font-size: 1.3rem;
    margin-top: .6rem;
}

.count-down .four-product {
    overflow: hidden;
    border-left: 1px solid $body-background;
    margin-left: 50%;
}

.count-down .four-product li {
    width: 50%;
    float: left;
    position: relative;
    border-bottom: 1px solid $body-background;
    border-right: 1px solid $body-background;
    padding: $padding-all*1.4/2;
    a {
        @include urlAbsolute();
    }
}

.count-down .four-product li img {
    width: 100%;
}

.count-down .four-product li .price,
.count-down .four-product li del,
.spike-swiper-slide .price,
.spike-swiper-slide del {
    text-align: center;
    display: block;
}

.count-down .four-product li .price {
    font-size: 1.3rem;
    color: $color;
}

.count-down .four-product li del {
    color: $subsidiary-color;
    font-size: 1.1rem;
}

.big .day-date {
    font-size: 1.3rem;
    margin-top: .4rem;
    margin-left: -.3rem;
}

.day-date {
    font-weight: bold;
}

.day-date span {
    margin: 0 .3rem;
}

/*新版秒杀样式*/
.new-style{
    background: transparent;
}
.seckill-new-style{
    margin: 1rem 1rem 0;
    background-color: #fff;
    border-radius: 1rem;
}
.seckill-new-style .seckill-header{
    padding: 0;
}
.seckill-new-style .header-top{
    display: flex;
    justify-content: space-between;
    flex-direction: row;
    align-items: center;
    padding: 2.2rem 1rem 1.2rem 1.5rem;
}
.seckill-new-style .header-top .tit-img,.seckill-new-style .header-top .tit-img img{
    width: 10rem;
}
.seckill-new-style .header-top .data{
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
}
.seckill-new-style .header-top .data .data-txt{
    font-size: 1.3rem;
    color: #333;
    margin-right: .8rem;
}
.seckill-new-style .header-top /deep/ .data-time{
    font-size: 1.4rem;
}
.seckill-new-style .header-top /deep/ .data-time span{
    display: inline-block;
    padding: .3rem .5rem;
    background:linear-gradient(-88deg,rgba(255,79,46,1),rgba(249,31,40,1));
    color: #fff;
    font-size: 1.2rem;
    border-radius: .5rem;
    min-width: 1.5rem;
}
.seckill-new-style .header-top /deep/ .data-time i{
    font-size: 1.2rem;
    color: #F20D23;
    font-size: 1.6rem;
    font-weight: 700;
    margin: 0 .4rem;
}
.seckill-new-style .header-top /deep/ .data-time strong{
    font-size: 1.2rem;
    color: #333;
    font-weight: 400;
    margin-right: 1.2rem;
}
.seckill-new-style .header-time-slot{
    display: flex;
    padding: .5rem 0;
}
.seckill-new-style .header-time-slot .item{
    display: flex;
    flex: 1;
    flex-direction: column;
    color: #999999;
    justify-content: center;
    align-items: center;
    position: relative;
    padding-bottom: .8rem;
}
.seckill-new-style .header-time-slot .item:after{
    content: ' ';
    position: absolute;
    height: 1px;
    background-color: #EEEEEE;
    left: 0;
    right: 0;
    bottom: 0;
}
.seckill-new-style .header-time-slot .item:first-child:after{
    left: 1rem;
}
.seckill-new-style .header-time-slot .item:last-child:after{
    right: 1rem;
}

.seckill-new-style .header-time-slot .item strong{
    font-size: 1.4rem;
    font-weight: 700;
}
.seckill-new-style .header-time-slot .item span{
    font-size: 1.2rem;
    font-weight: 400;
    margin-top: 3px;
}
.seckill-new-style .header-time-slot .item.active{
    color: #F20D28;
}
.seckill-new-style .header-time-slot .item.active strong{
    font-size: 1.8rem;
    font-weight: 600;
}
.seckill-new-style .header-time-slot .item.active span{
    font-size: 1.4rem;
    margin-top: 0;
}
.seckill-new-style .header-time-slot .item.active:after{
    background-color: #F20D28;
    height: 2px;
    left: 1rem;
    right: 1rem;
}
.seckill-new-style .more{
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    padding-bottom: 2rem;
}
.seckill-new-style .more strong{
    font-size: 1.4rem;
    color: #000;
    margin-right: .5rem;
}
.seckill-new-style .more .iconfont{
    font-size: 1rem;
    font-weight: bold;
    color: #000;
}
</style>
