<template>
    <van-popup v-model="display" position="bottom" class="attr-goods-box">
        <div class="attr-goods-header">
            <template v-if="attr != ''">
                <div class="attr-img">
                    <img :src="goodsAttrOper.attr_img" class="img" v-if="goodsAttrOper.attr_img" />
                    <img :src="goodsInfo.goods_thumb" class="img" v-else>
                </div>
                <div class="attr-info">
                    <div class="attr-price">{{ goodsAttrOper.goods_price_formated }}</div>
                    <div class="attr-stock">{{$t('lang.stock')}}：{{ goodsAttrOper.stock }}</div>
                    <div class="attr-other">{{$t('lang.label_selected')}}：{{ goodsAttrOper.attr_name }} {{ num }}件</div>
                </div>
            </template>
            <template v-else>
                <div class="attr-img">
                    <img :src="goodsInfo.goods_img" class="img"/>
                </div>
                <div class="attr-info">
                    <div class="attr-name twolist-hidden">{{ goodsInfo.goods_name }}</div>
                    <div class="attr-price">{{ goodsInfo.goods_price }}</div>
                    <div class="attr-stock">{{$t('lang.stock')}}：{{ goodsInfo.goods_number }}</div>
                </div>
            </template>
            <i class="iconfont icon-close" @click="closeSku"></i>
        </div>
        <div class="attr-goods-content" v-if="attr != ''">
            <van-radio-group class="sku-item" v-model="goodsAttrInit[index]" v-for="(item,index) in attr" :key="index" @change="changeAttr">
                <div class="sku-tit">{{ item.attr_name }}</div>
                <div class="sku-list">
                    <template v-for="(option,listIndex) in item.attr_key">
                        <van-radio class="option" :class="{'active':goodsAttrInit[index] == option.goods_attr_id}" :name="option.goods_attr_id">{{ option.attr_value }}
                        </van-radio>
                    </template>
                </div>
            </van-radio-group>
        </div>
        <div class="attr-goods-number dis-box">
            <span class="tit box-flex">{{$t('lang.number')}}</span>
            <div class="stepper">
                <van-stepper
                    v-model="num"
                    integer
                    :min="1"
                    :max="stock"
                />
            </div>
        </div>
        <div class="van-sku-actions">
            <template v-if="!storeBtn">
                <template v-if="is_on_sale">
                    <van-button type="disabled" class="van-button--bottom-action">{{$t('lang.button_buy')}}</van-button>
                    <van-button type="disabled" class="van-button--bottom-action">{{$t('lang.goods_sold_out')}}</van-button>
                </template>
                <template v-else>
                    <van-button type="default" class="van-button--bottom-action" @click="onBuyClicked">{{$t('lang.button_buy')}}
                    </van-button>
                    <van-button type="primary" class="van-button--bottom-action" @click="onAddCartClicked">{{$t('lang.add_cart')}}
                    </van-button>
                </template>
            </template>
            <template v-else>
                <van-button type="primary" class="van-button--bottom-action" @click="onStoreClicked">{{$t('lang.private_store')}}
                </van-button>
            </template>
        </div>
    </van-popup>
</template>

<script>
export default{
    props:['display','goodsAttrOper','goodsInfo'],
    data(){
        return{

        }
    }
}
</script>