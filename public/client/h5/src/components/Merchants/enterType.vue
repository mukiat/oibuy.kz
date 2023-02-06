<template>
    <div class="enter_type_content">
        <img class="bg_img" src="../../assets/img/merchant_ico.png" alt="">
        <div class="items">
            <div class="item" @click="selectHandle(1)">
                <img src="../../assets/img/merchant_ico1.png" alt="">
                <div class="title_box">
                    <h3>{{$t('lang.merchants_maintitle6')}}</h3>
                    <span>{{$t('lang.merchants_maintitle7')}}</span>
                </div>
                <i class="iconfont icon-more"></i>
            </div>
            <div class="item" @click="selectHandle(2)">
                <img src="../../assets/img/merchant_ico2.png" alt="">
                <div class="title_box">
                    <h3>{{$t('lang.merchants_storetype1')}}</h3>
                    <span>{{$t('lang.merchants_maintitle8')}}</span>
                </div>
                <i class="iconfont icon-more"></i>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        userInfo: {
            type: Object,
            default: function () {
                return {
                    enterType: ''
                }
            }
        }
    },
    data() {
        return {
            uInfo: this.userInfo
        }
    },
    created() {
        
    },
    methods: {
        selectHandle(enterType){
            this.$set(this.uInfo, 'step', 3);

            this.$set(this.uInfo, 'enterType', enterType);

            this.$emit('go-next', this.uInfo)
        }
    }
}
</script>

<style lang="scss" scoped>
.enter_type_content {
    .bg_img {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
    }
    .items {
        position: relative;
        margin-top: 12rem;
        z-index: 1;
        padding: 0 1rem;
    }
    .item {
        display: flex;
        align-items: center;
        padding: 2rem;
        border-radius: 1rem;
        background-color: #fff;
        img {
            width: 5rem;
        }
        .title_box {
            flex: 1;
            font-size: 14px;
            margin-left: 2rem;
            h3 {
                font-size: 15px;
                font-weight: bold;
                margin-bottom: 1rem;
            }
            span {
                color: #999;
            }
        }
        .iconfont {
            font-size: 1.5rem;
            color: #999;
        }
        &:last-child {
            margin-top: 1rem;
        }
    }
}
</style>