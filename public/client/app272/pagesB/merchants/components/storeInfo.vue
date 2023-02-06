<template>
    <view class="basic_info_content">
        <view class="steps">
            <view class="step_item active_item">
                <view class="index_box">1</view>
                <text>{{$t('lang.basic_info')}}</text>
            </view>
            <view class="line_box"></view>
            <view class="step_item active_item">
                <view class="index_box">2</view>
                <text>{{$t('lang.main_info')}}</text>
            </view>
            <view class="line_box"></view>
            <view class="step_item active_item">
                <view class="index_box">3</view>
                <text>{{$t('lang.shop_detail')}}</text>
            </view>
        </view>

        <view class="main_box">
            <view class="title item">{{$t('lang.basic_store_info')}}</view>
            <view class="cell_box">
                <view class="cate_text">{{$t('lang.merchants_storetype10')}}</view>
                <view class="cate_value">
					<picker @change="bindPickerChange" :value="uInfo.storeTypeKey" :range="uInfo.columns">
						<text class="cate_text" :class="{'store_type':uInfo.storeTypeKey === ''}">{{uInfo.storeTypeKey === '' ? $t('lang.fill_in_shop_type') : uInfo.columns[uInfo.storeTypeKey]}}</text>
					</picker>
                </view>
                <text class="iconfont icon-more"></text>
            </view>
            <view class="input_box item border_top">
                <text class="text">{{$t('lang.shop_name')}}</text>
                <input type="text" class="flex_1 input" v-model="uInfo.shopName" :placeholder="$t('lang.enter_shop_name')">
            </view>
            <view class="input_box item border_top">
                <text class="text">{{$t('lang.merchants_loginname')}}</text>
                <input type="text" class="flex_1 input" v-model="uInfo.stopLoginName" :placeholder="$t('lang.enter_shop_login_name')">
            </view>
        </view>

        <view class="main_box">
            <view class="title item">{{$t('lang.merchants_jingyininfo')}}</view>
            <view class="cell_box" @click="showBusinessScope = true">
                <template v-if="uInfo.allParentCate.length > 0">
                    <view class="cate_text">{{$t('lang.merchants_catename')}}</view>
                    <view class="cate_value">
                        <text class="cate_text" v-for="(item, index) in uInfo.allParentCate" :key="index">{{item.cat_name}}  </text>
                    </view>
                </template>
                <template v-else>
                    <view class="cate_text">{{$t('lang.merchants_catename')}}</view>
                    <view class="cate_value"><text class="store_type">{{$t('lang.merchants_seletecate1')}}</text></view>
                </template>
                <text class="iconfont icon-more"></text>
            </view>
        </view>

        <view class="btns">
            <view class="btn_item gray_btn" @click="goBack">{{$t('lang.merchants_goback')}}</view>
            <view class="btn_item red_btn" @click="goNext">{{$t('lang.next_step')}}</view>
        </view>
		
		<view class="business_scope_pop">
			<uni-popup :show="showBusinessScope" type="right" mode="fixed" v-on:hidePopup="showBusinessScope = !showBusinessScope">
				<view class="business_scope_content">
					<view class="cell_box">
						<text class="title font_bold">{{$t('lang.merchants_seletecate2')}}<text>{{$t('lang.merchants_selectmore')}}</text></text>
					</view>
					<view class="scorll_box">
						<view class="warp" v-for="(cate, index) in merchantsCategory" :key="index">
							<view class="cell_box" @click="changeCate(cate.cat_id)">
								<text class="title">{{cate.cat_name}}</text>
								<i :class="['iconfont', cate.cat_id == uInfo.cateId ? 'icon-moreunfold' : 'icon-more']"></i>
							</view>
							<view class="cat_items" v-if="cate.cat_id == uInfo.cateId">
								<view :class="['cat_item', uInfo.allCheckedSubCate.includes(subItem.cat_id) ? 'active' : '']" v-for="(subItem, subIndex) in cate.childCate" :key="subIndex" @click="chackedCate(subItem.cat_id, cate.cat_id)">{{subItem.cat_name}}</view>
							</view>
						</view>
					</view>
					<view class="gap_box"></view>
					<view class="btns">
						<view class="btn_item gray_btn" @click="reset">{{$t('lang.merchants_reset')}}</view>
						<view class="btn_item red_btn" @click="selectOk">{{$t('lang.merchants_selectok')}}</view>
					</view>
				</view>
			</uni-popup>
		</view>
    </view>
</template>

<script>
import { mapState } from 'vuex';

import uniPopup from '@/components/uni-popup.vue';
export default {
    props: {
        userInfo: {
            type: Object,
            default: function () {
                return {
                    columns: ['请选择','旗舰店','专卖店','专营店'],
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
	components:{
		uniPopup
	},
    data() {
        return {
            uInfo: this.userInfo,
            showStoreType: false,
            showBusinessScope: false,
			indicatorStyle: `height: ${Math.round(uni.getSystemInfoSync().screenWidth/(750/100))}px;`,
			value:''
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
            this.uInfo.allCheckedCate = {};
            this.uInfo.allCheckedSubCate = [];
            this.uInfo.allParentCate = [];
        },
        selectOk() {
            const allParentCateId = Object.keys(this.uInfo.allCheckedCate);
            this.uInfo.allParentCate = this.merchantsCategory.filter(item => allParentCateId.includes(`${item.cat_id}`))
            this.showBusinessScope = false
        },
        goBack() {
            this.$set(this.uInfo, 'step', 4)
            this.$emit('go-next', this.uInfo)
        },
        goNext() {
            if (!this.uInfo.storeTypeVal.trim()) return uni.showToast({ title: this.$t('lang.merchants_toast1'), icon: 'none' });
            if (!this.uInfo.shopName.trim()) return uni.showToast({ title: this.$t('lang.merchants_toast2'), icon: 'none' });
            if (!this.uInfo.stopLoginName.trim()) return uni.showToast({ title: this.$t('lang.merchants_toast3'), icon: 'none' });
            if (this.uInfo.allParentCate.length == 0) return uni.showToast({ title: this.$t('lang.merchants_toast4'), icon: 'none' });
            this.$set(this.uInfo, 'step', 5)
            this.$emit('go-next', this.uInfo)
            this.$emit('submit-apply')
        },
		bindPickerChange(e){
			this.uInfo.storeTypeVal = this.uInfo.columns[e.detail.value];
			this.uInfo.storeTypeKey = e.detail.value; 
		}
    }
}
</script>

<style lang="scss" scoped>
.basic_info_content {
    padding: 20upx;
    .steps {
        display: flex;
        justify-content: space-between;
        padding: 40upx 40upx 30upx;;
        border-radius: 20upx;
        color: #fff;
        background: linear-gradient(to bottom,#FF2C2D,#FD5E29);
        .step_item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #FEA694;
            font-size: 25upx;
            line-height: 1.5;
        }
        .index_box {
            width: 40upx;
            height: 40upx;
            line-height: 40upx;
            text-align: center;
            border-radius: 50%;
            margin-bottom: 10upx;
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
            margin-top: 20upx;
            background-color: #fff;
        }
    }
    .main_box {
        border-radius: 20upx;
        margin-top: 20upx;
        background-color: #fff;
        .cell_box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 100upx;
            margin: 0 30upx;
            .cate_text {
                margin-right: 40upx;
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
            height: 100upx;
        }
        .title {
            font-size: 30upx;
            font-weight: bold;
            padding: 0 30upx;
            border-bottom: 1px solid #f6f6f9;
        }
        .input_box {
            margin-left: 30upx;
            text {
                margin-right: 40upx;
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
        box-sizing: border-box;
		
        .btn_item {
            width: 40%;
            height: 34px;
            text-align: center;
            border-radius: 10upx;
            color: #333;
        }
        .gray_btn {
            border: 1px solid #cdcdcf;
            line-height: 32px;
        }
        .red_btn {
            color: #fff;
            line-height: 34px;
            background-color: #f44;
        }
    }
    .business_scope_pop {
        .business_scope_content {
            display: flex;
            flex-direction: column;
            height: 100%;
            padding-left: 30upx;
            .scorll_box {
                flex: 1;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch; /*这句是为了滑动更顺畅*/
            }
            .gap_box {
                height: 110upx;
            }
        }
        .cell_box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20upx 30upx 20upx 0;
            border-bottom: 1px solid #f6f6f9;
            &:first-child {
                font-size: 25upx;
            }
            
        }
        .font_bold {
            font-weight: bold;
        }
        .title {
            text {
                font-weight: 400;
            }
        }
    }
    .iconfont {
        font-size: 16px;
        color: #999;
    }

    .cat_items {
        display: flex;
        flex-wrap: wrap;
        padding-bottom: 30upx;
        .cat_item {
            width: 27%;
            height: 30px;
            line-height: 30px;
            text-align: center;
            border-radius: 30upx;
            margin: 30upx 30upx 0 0;
            padding: 0 10upx;
            border: 1px solid #eee;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            background-color: #eee;
			box-sizing: border-box;
        }
        .active {
            color: #FF443B;
            border: 1px solid #FF0000;
            background-color: #FFEAE7;
        }
    }
}

.show-popup-store-type .title .text{ color: #1989fa; }
</style>