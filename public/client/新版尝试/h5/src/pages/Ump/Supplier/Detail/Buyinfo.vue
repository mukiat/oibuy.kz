<template>
    <div class="con">
        <div class="wholesale-buyinfo  con_main">
			<van-cell-group>
				<van-cell class="f-04" :title="$t('lang.asktobuy_product')" />
			</van-cell-group>
            <div class="goods-li of-hidden" v-if="detail">
                <div class="li" v-for="(item,index) in detail.goods_list" :key="index">
                    <div class="left left-img p-r">
                        <img class="img goods-img" :src="item.goods_img" />
                    </div>
                    <div class="right bg-color-write">
                        <h4 class="f-05 color-3 twolist-hidden"> {{item.goods_name}}</h4>
						<div class="f-02 color-9 m-top04">{{$t('lang.category')}}:<span class="color-3">{{item.cat_name}}</span></div>
						<div class="f-02 color-9">{{$t('lang.label_purchase_quantity')}}<span class="color-3">{{item.goods_number}}</span></div>
						<div class="f-02 color-9">{{$t('lang.target_price')}}<span class="color-red" v-html="item.goods_price"></span></div>
                    </div>
                </div>
            </div>
			<van-cell-group class="m-top08">
				<van-cell class="f-04" :title="$t('lang.purchase_need')" />
			</van-cell-group>
			<div class="info-cont bg-color-write">
				<div class="list dis-box f-04 color-9">
					<div class="left">{{$t('lang.invoice_info')}}:</div>
					<div class="box-flex color-3">
						<span v-if="detail.need_invoice == 0">{{$t('lang.no_invoice')}}</span>
						<span v-if="detail.need_invoice == 1">{{$t('lang.plain_invoice')}}</span>
						<span v-if="detail.need_invoice == 2">{{$t('lang.vat_tax_invoice')}}</span>
					</div>
				</div>
				<div class="list dis-box f-04 color-9">
					<div class="left">{{$t('lang.label_address')}}</div>
					<div class="box-flex color-3">{{detail.consignee_address}}</div>
				</div>
				<div class="list dis-box f-04 color-9">
					<div class="left">{{$t('lang.label_supplementary_instruction')}}</div>
					<div class="box-flex color-3">{{detail.description}}</div>
				</div>
			</div>
			<van-cell-group class="m-top08">
				<van-cell class="f-04" :title="$t('lang.business_info')" />
			</van-cell-group>
			<div class="info-cont bg-color-write">
				<div class="shop-cont">
					<div class="f-05 color-3">{{detail.shop_name}}</div>
					<div class="f-02 color-9">{{detail.area_info}}</div>
				</div>
				<div class="list dis-box f-04 color-9">
					<div class="left">{{$t('lang.label_name')}}</div>
					<div class="box-flex color-3">{{detail.contact_name}}</div>
				</div>
				<div class="list dis-box f-04 color-9">
					<div class="left">{{$t('lang.label_tel')}}</div>
					<div class="box-flex color-3">{{detail.contact_phone}}</div>
				</div>
				<div class="list dis-box f-04 color-9">
					<div class="left">{{$t('lang.label_email')}}</div>
					<div class="box-flex color-3">{{detail.contact_email}}</div>
				</div>
			</div>
        </div>
        <WhoTabbar />
    </div>
</template>
<script>
	import { mapState } from 'vuex'
    import WhoTabbar from './WhoTabbar'
    import {
		Cell,
		CellGroup,
        Button,
        Tab,
        Tabs,
        Row,
        Col,
    } from 'vant'
    export default {
        name: "wholesale-buyinfo",
        components: {
            [Tab.name]: Tab,
            [Tabs.name]: Tabs,
            [Row.name]: Row,
            [Col.name]: Col,
            [Button.name]: Button,
			[Cell.name]: Cell,
			[CellGroup.name]: CellGroup,
            WhoTabbar
        },
        data() {
            return {
                purchase_id:this.$route.query.purchase_id ? this.$route.query.purchase_id : 0
            };
        },
        created(){
            this.purchaseInfo()
        },
        computed: {
            ...mapState({
                supplierPurchaseInfo: state => state.other.supplierPurchaseInfo,
            }),
            detail(){
            	return this.supplierPurchaseInfo.purchase_info
            }
        },
        methods: {
        	purchaseInfo(){
        		this.$store.dispatch('setSupplierPurchaseInfo',{
        			id:this.purchase_id
        		})
        	},
            detailClick(item) {
                this.$router.push({
                    name: "team-detail",
                    params: {
                        goods_id: item.goods_id
                    }
                });
            }
        }
    };
</script>
