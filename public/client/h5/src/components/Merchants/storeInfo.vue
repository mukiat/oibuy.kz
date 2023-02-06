<template>
    <div class="basic_info_content">
        <div class="steps">
            <div class="step_item active_item">
                <div class="index_box">1</div>
                <span>{{$t('lang.basic_info')}}</span>
            </div>
            <div class="line_box"></div>
            <div class="step_item active_item">
                <div class="index_box">2</div>
                <span>{{$t('lang.main_info')}}</span>
            </div>
            <div class="line_box"></div>
            <div class="step_item active_item">
                <div class="index_box">3</div>
                <span>{{$t('lang.shop_detail')}}</span>
            </div>
        </div>

        <div class="main_box">
            <div class="title item">{{$t('lang.basic_store_info')}}</div>
            <div class="cell_box" @click="showStoreType = true">
                <div class="cate_label">{{$t('lang.merchants_storetype10')}}</div>
                <div class="cate_value">
                    <span class="cate_text" v-if="uInfo.storeTypeVal">{{uInfo.storeTypeVal}}</span>
                    <span class="store_type" v-else>{{$t('lang.fill_in_shop_type')}}</span>
                </div>
                <i class="iconfont icon-more"></i>
            </div>
            <div class="input_box item border_top">
                <label>{{$t('lang.shop_name')}}</label>
                <input type="text" class="flex_1" v-model="uInfo.shopName" :placeholder="$t('lang.enter_shop_name')">
            </div>
            <div class="input_box item border_top">
                <label>{{$t('lang.merchants_loginname')}}</label>
                <input type="text" class="flex_1" v-model="uInfo.stopLoginName" :placeholder="$t('lang.enter_shop_login_name')">
            </div>
        </div>

        <div class="main_box">
            <div class="title item">{{$t('lang.merchants_jingyininfo')}}</div>
            <div class="cell_box" @click="showBusinessScope = true">
                <template v-if="uInfo.allParentCate.length > 0">
                    <div class="cate_label">{{$t('lang.merchants_catename')}}</div>
                    <div class="cate_value">
                        <span class="cate_text" v-for="(item, index) in uInfo.allParentCate" :key="index">{{item.cat_name}}  </span>
                    </div>
                </template>
                <template v-else>
                    <div class="cate_label">{{$t('lang.merchants_catename')}}</div>
                    <div class="cate_value"><span class="store_type">{{$t('lang.merchants_seletecate1')}}</span></div>
                </template>
                <i class="iconfont icon-more"></i>
            </div>
        </div>

        <div class="btns">
            <div class="btn_item gray_btn" @click="goBack">{{$t('lang.merchants_goback')}}</div>
            <div class="btn_item red_btn" @click="goNext">{{$t('lang.next_step')}}</div>
        </div>

        <van-popup v-model="showStoreType" position="bottom">
            <van-picker :columns="uInfo.columns" :show-toolbar="true" :visible-item-count="3" @cancel="onCancel" @confirm="onConfirm" />
        </van-popup>

        <van-popup class="business_scope_pop" v-model="showBusinessScope" position="right">
            <div class="business_scope_content">
                <div class="cell_box">
                    <span class="title font_bold">{{$t('lang.merchants_seletecate2')}}<span>{{$t('lang.merchants_selectmore')}}</span></span>
                </div>
                <div class="scorll_box">
                    <template v-if="merchantsCategory.length > 0">
                        <div class="warp" v-for="(cate, index) in merchantsCategory" :key="index">
                            <div class="cell_box" @click="changeCate(cate.cat_id)">
                                <span class="title">{{cate.cat_name}}</span>
                                <i :class="['iconfont', cate.cat_id == uInfo.cateId ? 'icon-moreunfold' : 'icon-more']"></i>
                            </div>
                            <div class="items" v-if="cate.cat_id == uInfo.cateId">
                                <div :class="['item', uInfo.allCheckedSubCate.includes(subItem.cat_id) ? 'active' : '']" v-for="(subItem, subIndex) in cate.childCate" :key="subIndex" @click="chackedCate(subItem.cat_id, cate.cat_id)">{{subItem.cat_name}}</div>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <van-loading type="spinner" />
                    </template>
                </div>
                <div class="gap_box"></div>
                <div class="btns">
                    <div class="btn_item gray_btn" @click="reset">{{$t('lang.merchants_reset')}}</div>
                    <div class="btn_item red_btn" @click="selectOk">{{$t('lang.merchants_selectok')}}</div>
                </div>
            </div>
        </van-popup>
        
    </div>
</template>

<script>
import { mapState } from 'vuex';
import Vue from 'vue';
import { Toast, Popup, Picker, Cell, CellGroup, Loading } from 'vant';

Vue.use(Toast).use(Popup).use(Picker).use(Cell).use(CellGroup).use(Loading);
export default {
    props: {
        userInfo: {
            type: Object,
            default: function () {
                return {
                    columns: [this.$t('lang.flagship_store'), this.$t('lang.exclusive_shop'), this.$t('lang.franchised_store')],
                    shopName: '',
                    stopLoginName: '',
                    cateId: '',
                    storeTypeKey: '',
                    storeTypeVal: '',
                    allCheckedCate: {},
                    allCheckedSubCate: [],
                    allParentCate: []
                }
            }
        }
    },
    data() {
        return {
            uInfo: this.userInfo,
            showStoreType: false,
            showBusinessScope: false
        }
    },
    computed: {
        ...mapState({
            merchantsCategory: state => state.user.merchantsCategory,
            vuex_isApplyStore: state => state.user.vuex_isApplyStore
        })
    },
    created() {
        
    },
    methods: {
        changeCate(id) {
            if (this.uInfo.cateId == id) this.uInfo.cateId = '';
            else this.uInfo.cateId = id;
        },
        chackedCate(id, pid) {
            let flag = false

            let flag2 = false

            const selectCate = JSON.parse(JSON.stringify(this.uInfo.allCheckedCate))

            if (selectCate[pid]) {
                let flag = false;
                selectCate[pid].some(item => {
                    if (item == id) {
                        flag = true;

                        return true
                    }
                })
                if (flag) {
                    selectCate[pid] = selectCate[pid].filter(item => item != id);

                    this.uInfo.allCheckedSubCate = this.uInfo.allCheckedSubCate.filter(item => item != id);
                } else {
                    selectCate[pid].push(id);

                    this.uInfo.allCheckedSubCate = [...this.uInfo.allCheckedSubCate, id]
                }

                if (selectCate[pid].length == 0) delete selectCate[pid]
                
            } else {
                selectCate[pid] = [id];

                this.uInfo.allCheckedSubCate = [...this.uInfo.allCheckedSubCate, id]
            }

            this.uInfo.allCheckedCate = selectCate;

        },
        reset() {
            // const allParentCateId = Object.keys(this.allCheckedCate);

            // const arr = [];

            // allParentCateId.forEach(item => {
            //     const subArr = [item, ''];
            //     arr.push(subArr);
            // })

            // console.log(arr,this.allCheckedCate);

            // if (arr.length > 0) {
            //     this.$store.dispatch('setMerchantsAddCate',{
            //         data: arr
            //     })
            // }

            this.uInfo.allCheckedCate = {};

            this.uInfo.allCheckedSubCate = [];

            this.uInfo.allParentCate = [];
        },
        selectOk() {
            const allParentCateId = Object.keys(this.uInfo.allCheckedCate);

            this.uInfo.allParentCate = this.merchantsCategory.filter(item => allParentCateId.includes(`${item.cat_id}`))

            this.showBusinessScope = false
        },
        onCancel() {
            this.showStoreType = false
        },
        onConfirm(value, index) {
            this.uInfo.storeTypeVal = value;

            this.uInfo.storeTypeKey = index + 1; 

            this.showStoreType = false
        },
        goBack() {
            this.$set(this.uInfo, 'step', 4)

            this.$emit('go-next', this.uInfo)
        },
        goNext() {
            // if (this.vuex_isApplyStore) {
            //     this.$router.replace({name: 'user'});
            //     return;
            // };
            if (!this.uInfo.storeTypeVal.trim()) return Toast(this.$t('lang.merchants_toast1'));
            if (!this.uInfo.shopName.trim()) return Toast(this.$t('lang.merchants_toast2'));
            if (!this.uInfo.stopLoginName.trim()) return Toast(this.$t('lang.merchants_toast3'));
            if (this.uInfo.allParentCate.length == 0) return Toast(this.$t('lang.merchants_toast4'));

            // const merchantInfo = JSON.parse(window.localStorage.getItem('merchantInfo')) || {};
            // merchantInfo.shopName = this.shopName;
            // merchantInfo.stopLoginName = this.stopLoginName;
            // merchantInfo.cateId = this.cateId;
            // merchantInfo.storeTypeKey = this.storeTypeKey;
            // merchantInfo.storeTypeVal = this.storeTypeVal;
            // merchantInfo.allCheckedCate = this.allCheckedCate;
            // merchantInfo.allCheckedSubCate = this.allCheckedSubCate;
            // merchantInfo.allParentCate = this.allParentCate;

            // const data = JSON.stringify(merchantInfo)

            // window.localStorage.setItem('merchantInfo', data)
            this.$set(this.uInfo, 'step', 5)

            this.$emit('go-next', this.uInfo)

            this.$emit('submit-apply')
            
        }
    }
}
</script>

<style lang="scss" scoped>
.basic_info_content {
    font-size: 14px;
    padding: 1rem;
    .steps {
        display: flex;
        justify-content: space-between;
        padding: 2rem;
        border-radius: 1rem;
        color: #fff;
        background: linear-gradient(to bottom,#FF2C2D,#FD5E29);
        .step_item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #FEA694;
        }
        .index_box {
            width: 2rem;
            height: 2rem;
            line-height: 2rem;
            text-align: center;
            border-radius: 50%;
            margin-bottom: 1rem;
            color: #FE3F30;
            background-color: #FE9E94;
        }
        .active_item {
            color: #fff;
            .index_box {
                color: #FF4335;
                background-color: #fff;
            }
        }
        .line_box {
            flex: 1;
            height: 1px;
            margin-top: 1rem;
            background-color: #fff;
        }
    }
    .main_box {
        border-radius: 1rem;
        margin-top: 1rem;
        background-color: #fff;
        .cell_box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 5rem;
            margin: 0 1.5rem;
            .cate_label {
                margin-right: 2rem;
            }
            .cate_value {
                flex: 1;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
                .cate_text {
                    margin-right: 5px;
                }
            }
            .store_type {
                color: #757575;
            }
        }
        .item {
            display: flex;
            align-items: center;
            height: 5rem;
        }
        .title {
            font-size: 15px;
            font-weight: bold;
            padding: 0 1.5rem;
            border-bottom: 1px solid #f6f6f9;
        }
        .input_box {
            margin-left: 1.5rem;
            label {
                margin-right: 2rem;
            }
        }
        .border_top {
            border-top: 1px solid #f6f6f9;
        }
    }

    .btns {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        display: flex;
        justify-content: space-around;
        border-top: 1px solid #f6f6f9;
        padding: 1rem;
        font-size: 15px;
        background-color: #fff;
        .btn_item {
            width: 40%;
            height: 34px;
            text-align: center;
            border-radius: 0.5rem;
            color: #333;
        }
        .gray_btn {
            border: 1px solid #f6f6f9;
            line-height: 32px;
        }
        .red_btn {
            color: #fff;
            line-height: 34px;
            background-color: #f44;
        }
    }
    .business_scope_pop {
        width: 75%!important;
        border-top-left-radius: 1.5rem;
        border-bottom-left-radius: 1.5rem;
        .business_scope_content {
            display: flex;
            flex-direction: column;
            height: 100%;
            padding-left: 1.5rem;
            .scorll_box {
                flex: 1;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch; /*这句是为了滑动更顺畅*/
            }
            .gap_box {
                height: 5.4rem;
            }
        }
        .cell_box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.3rem 1.5rem 1.3rem 0;
            border-bottom: 1px solid #f6f6f9;
            &:first-child {
                font-size: 14px;
            }
            
        }
        .font_bold {
            font-weight: bold;
        }
        .title {
            span {
                font-weight: 400;
            }
        }
    }
    .iconfont {
        font-size: 1.5rem;
        color: #999;
    }

    .items {
        display: flex;
        flex-wrap: wrap;
        padding-bottom: 1.5rem;
        // justify-content: space-between;
        .item {
            width: 27%;
            height: 30px;
            line-height: 28px;
            text-align: center;
            border-radius: 1.5rem;
            margin: 1.5rem 1.5rem 0 0;
            padding: 0 0.5rem;
            border: 1px solid #eee;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            background-color: #eee;
        }
        .active {
            color: #FF443B;
            line-height: 2.8rem;
            border: 1px solid #FF0000;
            background-color: #FFEAE7;
        }
    }
}
</style>