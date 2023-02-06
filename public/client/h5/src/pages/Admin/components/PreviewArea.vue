<template>
    <div class='preview-area'>
        <header class="component-header">
            <h4>{{ previewArea.text }}</h4>
            <ec-button class="release-btn" size="mini" type="primary" @click="release" v-if="false">{{ previewArea.function.release.text }}</ec-button>
        </header>
        <div class="edit-area-con">
            <div class="phone-view">
                <div id="j-phone-edit" class="phone-edit">
                    <header class="title">
                        {{ modulesTitle }}
                    </header>
                    <div class="phone-edit-con" :class="{no:bMoudles}">
                        <template v-if="bStore">
                            <ec-search :preview="true" :data="searchStoreData"></ec-search>
                            <ec-shop-signs :preview="true"></ec-shop-signs>
                            <ec-line :preview="true" :data="lineData"></ec-line>
                        </template>
                        <!--preview 如果为 true 则取消所有链接 -->
                        <component v-for="(item,index) in modules" :key="index" :is="'ec-'+item.module" :data="item.data" :preview="true" :modules-index="index" v-if="item.module != 'live'" :shop-id="shopId">
                        </component>
                        <!-- <template v-if="bStore">
                            <ec-title :preview="true" :data="titleData"></ec-title>
                            <ec-product :preview="true" :data="productData"></ec-product>
                        </template> -->
                    </div>
                    <ec-shop-menu v-if="bStore" :preview="true"></ec-shop-menu>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
// mapActions mapState
import {
    mapActions,
    mapState
} from 'vuex'

// custom components
import EcSlide from '@/components/visualization/slide/Frontend'
import EcTitle from '@/components/visualization/title/Frontend'
import EcAnnouncement from '@/components/visualization/announcement/Frontend'
import EcNav from '@/components/visualization/nav/Frontend'
import EcLine from '@/components/visualization/line/Frontend'
import EcBlank from '@/components/visualization/blank/Frontend'
import EcJigsaw from '@/components/visualization/jigsaw/Frontend'
import EcProduct from '@/components/visualization/product/Frontend'
import EcCoupon from '@/components/visualization/coupon/Frontend'
import EcCountDown from '@/components/visualization/count-down/Frontend'
import EcButton from '@/components/visualization/button/Frontend'
import EcSearch from '@/components/visualization/search/Frontend'
import EcStore from '@/components/visualization/store/Frontend'
import EcShopSigns from '@/components/visualization/shop-signs/Frontend'
import EcShopMenu from '@/components/visualization/shop-menu/Frontend'
import EcTabDown from '@/components/visualization/tab-down/Frontend'
import EcLive from '@/components/visualization/live/Frontend'

//新增可视化分类组件
import EcCategoryNav from '@/components/visualization/category-nav/Frontend'
import EcVisualTeam from '@/components/visualization/visual-team/Frontend'
import EcVisualAdv from '@/components/visualization/visual-adv/Frontend'
import EcProductPick from '@/components/visualization/product-pick/Frontend'

// third party components
import {
    Button
} from 'element-ui'

// third party  libraries
import 'html2canvas'

export default {
    name: 'admin-prview-area',
    components: {
        "EcButton": Button,
        EcSlide,
        EcTitle,
        EcAnnouncement,
        EcNav,
        EcLine,
        EcBlank,
        EcJigsaw,
        EcProduct,
        EcCoupon,
        EcCountDown,
        EcSearch,
        EcStore,
        EcShopSigns,
        EcShopMenu,
        EcTabDown,
		EcLive,
        EcCategoryNav,
        EcVisualTeam,
        EcVisualAdv,
        EcProductPick
    },
    data() {
        return {}
    },
    mounted() {
        
    },
    methods: {
        release() {
            this.generateImage()
        },
    },
    computed: {
        bMoudles() {
            return this.modules.length > 0 || this.bStore ? false : true
        },
        ...mapState({
            previewArea: state => state.dashboard.configInfo.previewArea,
            modulesId: state => state.pageSetting.modulesId,
            pageList: state => state.pageList,
            searchStoreData: state => state.shopInfo.searchStoreData,
            lineData: state => state.shopInfo.lineData,
            titleData: state => state.shopInfo.titleData,
            productData: state => state.shopInfo.productData
        }),
        modulesTitle() {
            let aModules = this.pageList.filter(item => {
                return item.id == this.modulesId
            })

            if (aModules.length > 0) {
                return aModules[0].title
            }
        },
        modulesType(){
            let aModules = this.pageList.filter(item => {
                return item.id == this.modulesId
            })

            if (aModules.length > 0) {
                return aModules[0].type
            }
        },
        modules() {
            return this.$store.state.modules
        },
        bStore() {
            return window.shopInfo.ruid != 0
        },
        shopId(){
            return window.shopInfo.ruid
        }
    },
    watch:{
        modulesType(){
            let modulesType = {
              type:window.shopInfo.type,
              name:this.modulesType
            }

            localStorage.setItem('modulesType',JSON.stringify(modulesType))
        }
    }
}

</script>

<style lang="scss" scoped>
@import '../../../assets/style/config';
@import '../../../assets/style/mixins/common';
$title-height :48px;
.component-header {
    height: ($tab-height+.4rem);
    background: $tool-bg;
    padding: 0 1rem;
    @include direction(center, space-between)
}

.component-header h4 {
    float: left;
    font-weight: normal;
    font-size: $tool-title-size;
}

.release-btn i {
    font-size: .8rem;
    margin-right: .1rem;
}

.phone-view {
    width: 364px;
    height: 729px;
    background: url('../../../assets/img/iphone-bg.jpg');
    background-size: cover;
    margin-top: 2.4rem;
    overflow: hidden;
    margin-left: 50%;
    @include translateX(-50%);
}

.phone-view header.title {
    color: $write-color;
    background: $color;
    height: $title-height;
    width: 100%;
    font-size: 1.6rem;
    font-weight: bold;
    text-align: center;
    background: url('../../../assets/img/iphone-title-info.png') $color no-repeat center center;
    background-size: cover auto;
    padding-top: 1.4rem;
}

.phone-edit {
    width: 318px;
    height: 560px;
    background: #f1f2f4;
    position: relative;
    margin-top: 86px;
    margin-left: 22px;
}

.phone-edit .phone-edit-con {
    position: absolute;
    width: 100%;
    top: $title-height;
    bottom: 0;
    overflow-y: auto;
    overflow-x: hidden;
    height: 512px;

    &::-webkit-scrollbar {
        width: 6px;
    }

    &::-webkit-scrollbar-thumb {
        background: rgba(20, 20, 20, 0.2);
        border-radius: 3px;
    }

    &::-webkit-scrollbar-track {
        background: transparent;
    }
}


.phone-edit-con.no {
    background: url('../../../assets/img/admin_pre.jpg') #fff no-repeat center 30%;
}
</style>