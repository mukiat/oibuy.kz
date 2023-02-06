<template>
    <div class='dialog-ec dialog-goods'>
        <ec-dialog 
            :visible.sync="bThisShowDialog" 
            @close="closeDialog()">
            <div slot="title" class="title">
                <ul>
                    <li 
                        v-for="(item,index) in tabSelect" 
                        :key="index" 
                        :class="{
                            active:index == selActive
                        }"
                        @click="tabSelectFn(index)">
                        {{ 
                            item.title == '商品库' 
                                ? item.title 
                                : `${item.title} (${selGoodNum})`
                        }}
                    </li>
                </ul>
               
                <div class="search" v-show="selActive == 0">
                    <cascader 
                        class="cascader"
                        :options="storeList || []"
                        v-model="shopSOption"
                        :props="shopCProps"
                        clearable
                        filterable
                        change-on-select
                        size="small"
                        :placeholder="$t('lang.article_categary_placeholder')"
                        @change="changeShop"
                        v-if="!isSeller">
                    </cascader>
                    <ec-input
                        :placeholder="$t('lang.enter_goods_keyword')"
                        v-model="kwords"
                        size="small">
                        <i class="el-input__icon el-icon-search" slot="suffix" @click="handleIconSearch"></i>
                    </ec-input>
                </div>
            </div>
            <template v-if="selActive == 0">
                <ul class="picture-list">
                    <li 
                        class="dialog-picture-active"
                        v-for="(item,index) in goodsList" 
                        :key="item.goods_id"
                        :title="item.goods_name"
                        @click="select(index)">
                        <div class="dialog-picture-active" v-show="item.bActive">
                            <span>
                                <i class="iconfont icon-correct"></i>
                            </span>
                        </div>
                        <figure>
                            <img :src="item.goods_img" alt="">
                        </figure>
                        <figcaption>{{ item.goods_name }}</figcaption>
                        <div class="price">{{item.shop_price_formated}}</div>
                    </li>
                </ul>
                <div slot="footer" class="dialog-footer">
                    <pagination 
                        :page-size="pageSize" 
                        :current-page="currentPage" 
                        :total="total" 
                        @current-change="currentChange"
                        layout="total, prev, pager, next">
                    </pagination>
                    <ec-button type="primary" @click="closeDialog()">{{$t('lang.confirm')}}</ec-button>
                </div>
            </template>
            <template v-else>
                <ul class="picture-list sel-product-list">
                    <li 
                        class="dialog-picture-active"
                        v-for="(item,index) in selGoodsList" 
                        :key="item.goods_id"
                        :title="item.title"
                        @click="removeSelectGoods({
                            index,
                            goodsId: item.goods_id
                        })">
                        <div class="dialog-picture-active">
                            <span>
                                <i class="iconfont icon-clear1"></i>
                            </span>
                        </div>
                        <figure>
                            <img :src="item.goods_img" alt="">
                        </figure>
                        <figcaption>{{ item.title }}</figcaption>
                    </li>
                </ul>
                <div slot="footer" class="dialog-footer">
                    <pagination 
                        :page-size="selPageSize" 
                        :current-page="selCurrentPage" 
                        :total="selGoodNum" 
                        @current-change="selCurrentChange"
                        layout="total, prev, pager, next">
                    </pagination>
                    <ec-button type="primary" @click="closeDialog()">{{$t('lang.confirm')}}</ec-button>
                </div>
            </template>
        </ec-dialog>
    </div>
</template>

<script>
// mapActions mapState
import {
    mapActions,
    mapState
} from 'vuex'

// third party components
import {
    Button,
    Loading,
    Dialog,
    Pagination,
    Upload,
    Input,
    Cascader
} from 'element-ui'

export default {
    name: 'dialog-goods',
    components: {
        'EcInput': Input,
        'EcDialog': Dialog,
        'EcButton': Button,
        Pagination,
        Cascader
    },
    data() {
        return {
            bThisShowDialog: false,
            selActive: 0,
            kwords:'',
            tabSelect: [{
                title: this.$t('lang.library_of_goods')
            }, {
                title: this.$t('lang.selected')
            }],
            shopSOption:[],
            shopCProps: {
                value: 'ru_id',
                label: 'store_name'
            },
            ruid:window.shopInfo.ruid
        }
    },
    created() {

    },
    methods: {
        ...mapActions('dialogGoods', [
            'onOffDialogGoods',
            'setDialogGoods',
            'selectGoods',
            'setDialogSelGoods',
            'removeSelect'
        ]),
        /**
         * 关闭goodsDialog 并修改 state 中的状态
         * 
         */
        closeDialog() {
            this.selActive = 0
            this.bThisShowDialog = false
            this.onOffDialogGoods({
                bShowDialog: false
            })
        },
        /**
         * 切换 tab
         * 
         * @param {* number} 当前索引
         */
        tabSelectFn(index) {
            this.selActive = index
            if (index == 1) {
                //判断已选择商品数量是否超出15条
                var str = [];
                if(this.selectGoodsId.length > 15){
                    str = this.selectGoodsId.slice(0,15)
                }else{
                    str = this.selectGoodsId
                }

                this.setDialogSelGoods({
                    selectGoodsId: str,
                    currentPage: 1,
                    pageSize: 15
                })
            } else {
                this.currentChange(this.currentPage)
            }
        },
        /**
         * 选中取消当前商品
         * 
         * @param {* index} 当前索引
         */
        select(index) {
            this.selectGoods({
                index
            })
        },
        /**
         * 商品tab 的分页
         * 
         * @param {* Number} val - currentPage
        */
        currentChange(val) {
            this.setDialogGoods({
                bShowDialog: true,
                currentPage: val,
                kwords: this.kwords,
                pageSize: this.pageSize,
                modulesIndex: this.modulesIndex,
                ru_id:this.ruid
            })
        },
        /**
         * 选中商品tab 的分页
         * 
         * @param {* Number} val - currentPage
        */
        selCurrentChange(val) {
            var str = [];
            var index = val-1;

            //处理已选商品分页
            if(this.selectGoodsId.length > val*15){
                str = this.selectGoodsId.slice(index*15,15)
            }else{
                str = this.selectGoodsId.slice(index*15,this.selectGoodsId.length)
            }

            this.setDialogSelGoods({
                selectGoodsId: str,
                currentPage: val,
                pageSize: this.selPageSize
            })
        },
        /**
         * 删除已选商品
         * 
         * @param {* Object}
         */
        removeSelectGoods(o) {
            this.removeSelect({
                ...o,
                modulesIndex: this.modulesIndex,
                pageSize: this.selPageSize,
                currentPage: this.selCurrentPage,
                total: this.selGoodNum
            })
            this.setDialogSelGoods({
                selectGoodsId: this.selectGoodsId,
                currentPage: this.selCurrentPage,
                pageSize: this.selPageSize
            })
        },
        /**
         * 根据关键字搜索商品
         * 
         */
        handleIconSearch(){
            this.setDialogGoods({
                bShowDialog: true,
                currentPage: 1,
                pageSize: 15,
                modulesIndex: this.modulesIndex,
                kwords: this.kwords,
                ru_id:this.ruid
            })
        },
        changeShop(e){
            this.ruid = e[0]
        }
    },
    computed: {
        ...mapState('dialogGoods', {
            bShowDialog: state => state.settingType.bShowDialog,
            pageSize: state => state.settingType.pageSize,
            total: state => state.settingType.total,
            currentPage: state => state.settingType.currentPage,
            goodsList: state => state.goodsList,
            type: state => state.settingType.type,
            brandId: state => state.settingType.brandId,
            modulesIndex: state => state.settingType.modulesIndex,
            catId: state => state.settingType.catId,
            selGoodsList: state => state.selGoodsList,
            selPageSize: state => state.selectGoodsType.pageSize,
            selCurrentPage: state => state.selectGoodsType.currentPage,
            storeList: state => state.storeList
        }),
        selectGoodsId() {
            let selectGoodsId = []
            if (this.modulesIndex != -1) {
                let allValue = this.$store.state.modules[this.modulesIndex].data.allValue
                if (allValue && allValue.hasOwnProperty('selectGoodsId')) {
                    selectGoodsId = allValue.selectGoodsId
                }
            }
            return selectGoodsId
        },
        selGoodNum() {
            return this.selectGoodsId.length
        },
        isSeller(){
            return window.shopInfo.ruid == 0 ? false : true
        }
    },
    watch: {
        'bShowDialog'(val, oldVal) {
            if (val) this.bThisShowDialog = true
        }
    }
}
</script>

<style lang="scss" scoped>
@import '../../../assets/style/config.scss';
@import '../../../assets/style/mixins/common.scss';
.dialog-goods{
    .title{
        display:inline-block;
        .search{
            position: absolute;
            top:50%;
            right:50px;
            margin-top:-15px;
        }
    }
    ul{
        li{
            line-height: 1;
            font-size:16px;
            color:#646b73;
            margin-right:.8rem;
            cursor:pointer;
            display:inline-block;
        }
    }
    .active{
        font-size: 16px;
        font-weight: 700;
        color: #1f2d3d;
    }
}

ul.picture-list {
    overflow: hidden;
    float:inherit;
}

ul.picture-list li {
    width: 19.4%;
    margin: .3%;
    font-size:12px;
    text-align: center;
    position: relative;
    float: left;
    background: #fff;
    box-sizing: border-box;
    border: 2px solid #fff;
}

ul.picture-list li:hover {
    border: 2px solid #20a0ff;
    cursor: pointer;
}

ul.picture-list li .dialog-picture-active {
    position: absolute;
    background: rgba(32, 160, 255, .08);
    border: 2px solid #20a0ff;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 1
}

ul.picture-list li .dialog-picture-active span {
    background: #20a0ff;
    position: absolute;
    left: 50%;
    top: 50%;
    border-radius: 100%;
    color: #fff;
    display: block;
    height: 5rem;
    width: 5rem;
    line-height: 5rem;
    transform: translate3d(-50%, -60%, 0);
}

ul.picture-list li .dialog-picture-active span i {
    font-size: 2.6rem;
}

ul.picture-list li figure {
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 13rem;
}

ul.picture-list li figcaption {
    color: #20a0ff;
    height: 6rem;
    background: #FFF;
    padding: 0rem 1rem;
    margin: 1rem 0;
    z-index: 2;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    /* autoprefixer: off */
    -webkit-box-orient: vertical;
    font-size: 1.4rem;
    line-height: 2rem;
    text-align: left;
}
ul.picture-list li .price{
    color: #f20e28;
    text-align: left;
    font-size: 1.6rem;
    padding: 0 1rem 1rem;
}

ul.picture-list li img {
    max-width: 100%;
    max-height: 100%;
}

ul.sel-product-list{
    li{
        .dialog-picture-active{
            border:none;
        }
    }
    li.dialog-picture-active span{
        display:none;
    }
    li.dialog-picture-active:hover span{
        display:inherit
    }
}

@media only screen and (max-width: 1540px) {
    .dialog-goods ul.picture-list li figure {
        height: 11rem;
    }

    .dialog-goods ul.picture-list li .dialog-picture-active span{
        height: 3rem;
        width: 3rem;
        line-height: 3rem;
    }
    .dialog-goods ul.picture-list li .dialog-picture-active span i{
        font-size: 1.6rem;
    }

    .dialog-goods ul.picture-list li figcaption{
        font-size: 1.2rem;
        padding: 0 .5rem;
        margin: .5rem 0;
        line-height: 1.5rem;
        height: 4.6rem;
    }

    .dialog-goods ul.picture-list li .price{
        font-size: 1.4rem;
    }
}

@media only screen and (max-width: 1440px) {
    .dialog-goods ul.picture-list li figure {
        height: 9rem;
    }
}
.dialog-footer {
    display: flex;
    justify-content: space-between;
}

.search{ display: flex; }
.search .el-cascader{ margin-right: 10px; }
.search .el-input{ flex: 1; }
</style>