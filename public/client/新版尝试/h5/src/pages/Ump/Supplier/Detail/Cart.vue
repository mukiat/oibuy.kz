<template>
    <div class="con" :class="supplierCartList.length > 0 ? 'con_main' : 'con_main_b5'">
        <template v-if="supplierCartList.length > 0">
        <header class="page-layout-header">
          <h4 class="title">{{$t('lang.purchase_order')}}</h4>
          <van-button :type="editType ? 'danger' : 'default'" size="mini" plain @click="editClick">
            <span v-if="editType">{{$t('lang.accomplish')}}</span>
            <span v-else>{{$t('lang.edit')}}</span>
          </van-button>
        </header>
      	<section class="card-goods supplier-card-goods">
    	    <div class="van-checkbox-group" v-for="(supplierList,listIndex) in supplierCartList" :key="listIndex">
    	    	<div class="card-shop-head b-min b-min-b" @click="checkShop(listIndex)">
    	    		<van-checkbox class="card-goods__item" v-model="supplierChecked[listIndex]">
    	    			<div class="store_name">
    	    				<i class="iconfont icon-dianpu2 flow-shop-icon"></i>
    	    				<strong>{{ supplierList.shop_name }}</strong>
    	    			</div>
    	    		</van-checkbox>
    	    	</div>
            <van-checkbox-group v-model="supplierCheckedGoods[listIndex]">
              <div class="card-goods__item supplier-card-item" v-for="item in supplierList.goods_list" :key="item.goods_id">
                  <router-link :to="{name:'supplier-detail',params:{id:item.goods_id}}">
                  <van-card :title="item.goods_name">
                      <template slot="thumb">
                          <img :src="item.goods_thumb" v-if="item.goods_thumb" />
                          <img class="img" src="../../../../assets/img/no_image.jpg" v-else>
                      </template>
                  </van-card>
                  </router-link>
                  <div class="attr-list" v-for="(attr,index) in item.list" :key="index">
                      <div class="opt">
                          <van-checkbox :name="attr.rec_id" />
                      </div>
                      <div class="meta">
                          <div class="meta-sku">
                              <div class="sku">
                                  <span v-for="oper in attr.goods_attr" v-if="attr.goods_attr">{{ oper.attr_name }}：{{ oper.attr_value }}</span>
                                  <span v-else>{{$t('lang.not_specification')}}</span>
                              </div>
                              <div class="unit-price" v-html="attr.goods_price_formatted"></div>
                          </div>
                          <div class="meta-amount">
                              <div class="amount" v-html="attr.total_price_formatted"></div>
                              <div class="stepper">
                                  <van-stepper 
                                    v-model="attr.goods_number"
                                    integer
                                    :min="1"
                                    :disabled="stepDisabled"
                                    @change="goodsNumberhandle(attr.rec_id,attr.goods_number)"
                                    />
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
            </van-checkbox-group>
          </div>
        </section>
        <template v-if="editType">
          <van-submit-bar button-type="warning" :button-text="$t('lang.delete')" @submit="onBatchDelete">
            <van-checkbox v-model="checkAll" class="checkall" @change="onCheckAll" />
          </van-submit-bar>
        </template>
        <template v-else>
          <van-submit-bar :price="totalPrice" :disabled="!count > 0" :loading="loading" :button-text="submitBarText" @submit="onSubmit" />
        </template>
        </template>
        <template v-else>
          <div class="flow-no-cart">
            <span class="gwc-icon"><img src="../../../../assets/img/cart-empty.png" class="img" /></span>
            <h3>{{$t('lang.shopping_cart_notice')}}</h3>
            <div class="card-btn">
              <van-button type="default" plain @click="goHome">{{$t('lang.go_around')}}</van-button>
              <van-button type="default" plain @click="goUser">{{$t('lang.check_purchase_order')}}</van-button>
            </div>
          </div>
        </template>
        <WhoTabbar />

        <!--初始化loading-->
        <DscLoading :dscLoading="dscLoading"></DscLoading>
    </div>
</template>

<script>
import { mapState } from 'vuex'

import WhoTabbar from './WhoTabbar'
import {
	Checkbox, 
	CheckboxGroup, 
	Card, 
	SubmitBar, 
	Toast,
  Stepper,
  Button,
  Dialog
} from 'vant';

import DscLoading from '@/components/DscLoading'

export default {
  components: {
    [Card.name]: Card,
    [Checkbox.name]: Checkbox,
    [SubmitBar.name]: SubmitBar,
    [CheckboxGroup.name]: CheckboxGroup,
    [Stepper.name]: Stepper,
    [Button.name]: Button,
    [Dialog.name]: Dialog,
    WhoTabbar,
    DscLoading
  },
  data() {
    return {
      supplierChecked:[],
      stepDisabled:false,
      totalPrice:0.00,
      total:0,
      count:0,
      rec_id:[],
      loading:true,
      goods_id:this.$route.query.goods_id ? this.$route.query.goods_id : 0,
      editType:false,
      checkAll:false,
      dscLoading:true
    };
  },
  created(){
    //购物车商品列表
    this.goodsList()
  },
  mounted(){

  },
  computed: {
    ...mapState({
      supplierCartList: state => state.other.supplierCartList,
      supplierShopLength:state=>state.other.supplierShopLength
    }),
    supplierCheckedGoods:{
        get(){
            return this.$store.state.other.supplierCheckedGoods
        },
        set(val){
            this.$store.state.other.supplierCheckedGoods = val
        }
    },
    submitBarText() {
      return this.$t('lang.go_checkout') + '(' + this.count + ')';
    },
    isLogin(){
      return localStorage.getItem('token') == null ? false : true
    },
  },
  methods: {
    //购物车列表
    goodsList(){
      this.$store.dispatch('setSupplierCart',{
        goods_id:this.goods_id
      });

      this.supplierCheckedGoods.forEach((v,i)=>{
        v.forEach(rec=>{
            this.rec_id.push(rec)
        })
      })
    },
    formatPrice(price) {
      return (price / 100).toFixed(2);
    },
    onSubmit() {
      this.$store.dispatch('setSupplierFlows',{
        rec_ids:this.rec_id
      }).then(res=>{
        if(this.supplierCheckedGoods.length > 0){
            if(this.isLogin){
              if(res.data.error == 1){
                Toast(res.data.message)
              }else{
                this.$router.push({ 
                  name:'supplier-checkout',
                  query:{
                      rec_type:'supplier',
                      rec_id:JSON.stringify(this.rec_id)
                  }
                })
              }
            }else{
              let msg = this.$t('lang.login_user_not')
              this.notLogin(msg)
            }
        }
      })
    },
    goodsNumberhandle(rec_id,number){
        this.$store.dispatch('setSupplierUpdatecart',{
            rec_id:rec_id,
            rec_ids:this.rec_id,
            rec_num:number
        }).then(res=>{
            if(res.status == 'success'){
                this.total = res.data.cart_info.total_price;
                this.count = res.data.cart_info.total_number;
            }else{
                Toast(this.$t('lang.error_in_return_data'))
            }
        })
    },
    checkShop(index){
        this.$store.dispatch('setUpdetaCheckSupplier',{
            index:index,
            checked:this.supplierChecked[index]
        })
    },
    changeChecked(rec_id){
        this.loading = true;
        if(rec_id.length > 0){
            this.$store.dispatch('setCheckedSupplier',{
                rec_id:rec_id
            }).then(res=>{
                if(res.status == 'success'){
                    this.total = res.data.cart_info.total_price;
                    this.count = res.data.cart_info.total_number;
                    this.loading = false;
                }else{
                    Toast(this.$t('lang.error_in_return_data'))
                }
            })
        }else{
            this.loading = false;
            this.total = 0.00;
            this.count = 0;
        }
    },
    editClick(){
      this.editType = this.editType ? false : true;
    },
    onCheckAll(){
      this.supplierCartList.forEach((v,i)=>{
        this.supplierChecked[i] = this.checkAll;
        this.checkShop(i);
      })
    },
    onBatchDelete(){
      this.$store.dispatch('setSupplierCartDelete',{
        rec_ids:this.rec_id
      })
    },
    //返回供应链首页
    goHome(){
      this.$router.push({ name:'supplier'})
    },
    //查看采购单列表
    goUser(){
      this.$router.push({ name:'supplier-orderlist'})
    },
    //判断是否登录
    notLogin(msg){
      Dialog.confirm({
        message:msg,
        className:'text-center'
      }).then(()=>{
        this.$router.push({
          path: '/login',
          query:{ redirect: {name:'supplier-cart'}}
        })
      }).catch(()=>{

      })
    },
  },
  watch:{
    supplierCartList(){
      this.dscLoading = false
    },
    supplierCheckedGoods(){
        let arr = [];
        this.rec_id = [];
        this.supplierCheckedGoods.forEach((v,i)=>{
            arr.push(v.length === this.supplierShopLength[i] ? true : false)

            v.forEach(rec=>{
                this.rec_id.push(rec)
            })
        })

        this.supplierChecked = arr;
    },
    rec_id(){
        this.changeChecked(this.rec_id)
    },
    total(){
        this.totalPrice = this.total * 100
    },
    editType(){
      this.checkAll = false;
      if(this.editType){
        this.supplierCartList.forEach((v,i)=>{
          this.supplierChecked[i] = false;
          this.checkShop(i);
        })
      }
    }
  }
};
</script>

<style scoped> 
.page-layout-header{ display: flex; padding: 1.5rem 1.1rem; flex-direction: row; justify-content: space-between; align-items: center; background-color: #FFFFFF; border-bottom: 1px solid #ddd;}
.page-layout-header h4{ font-size: 16px; color: #4d4d4d;}
.supplier-card-goods{ margin-bottom: 0; }
.van-submit-bar{ bottom: 60px; }
.van-submit-bar .checkall{ padding-left: 10px; }
</style>
