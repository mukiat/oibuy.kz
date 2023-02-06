<template>
	<div class="user-detail" v-if="goodsList" >
    <section class="section-list">
    	<div class="bg-color-write">
        <div class="product-list product-list-small" >
          <template v-if="goodsList">
          <ul>
            <li v-for="(goodsInfo,index) in goodsList" :key="index">
              <div class="product-div">
                <div class="product-list-img"><img class="img" :src="goodsInfo.goods_img"></div>
                <div class="product-info">
                  <h4>{{ goodsInfo.goods_name }}</h4>
                  <div class="price">
                    <label v-html="goodsInfo.shop_price_formated"></label>
                    <span>x{{goodsInfo.goods_number}}</span>
                  </div>
				  <div class="price" v-if="goodsInfo.goods_coupons > 0">- <label v-html="goodsInfo.formated_goods_coupons"></label>[{{ $t('lang.coupons') }}]</div>
				  <div class="price" v-if="goodsInfo.goods_bonus > 0">- <label v-html="goodsInfo.formated_goods_bonus"></label>[{{ $t('lang.bonus') }}]</div>
				  <div class="price" v-if="goodsInfo.goods_favourable > 0">- <label v-html="goodsInfo.formated_goods_favourable"></label>[{{ $t('lang.discount') }}]</div>
				  <div class="price" v-if="goodsInfo.value_card_discount > 0">- <label v-html="goodsInfo.formated_value_card_discount"></label>[{{ $t('lang.value_card_discount') }}]</div>
				  <div class="price" v-if="goodsInfo.goods_coupons > 0 || goodsInfo.goods_bonus > 0 || goodsInfo.goods_favourable > 0 || goodsInfo.value_card_discount > 0">{{ $t('lang.return_total') }}：<label v-html="goodsInfo.formated_should_return"></label></div>
                  <div class="p-t-remark m-top04">{{ goodsInfo.attr_name }}</div>
				</div>
              </div>
            </li>
          </ul>
          </template>
        </div>
      </div>
    </section>
    <van-cell-group class="van-cell-noleft m-top08">
    	<van-cell>
    		<div slot="title"><em class="color-red">{{ $t('lang.reminder') }}：</em></div>
		  	<div class="f-03 col-6">{{ $t('lang.reminder_one') }}<em class="color-red" v-if="goodsList">{{ goodsList[0].shop_name }}</em>{{ $t('lang.reminder_two') }}</div>
		  </van-cell>
    </van-cell-group>
    <section class="user-return-list-box padding-all bg-color-write m-top08">
      <h4 class="f-04 col-7">{{ $t('lang.service_type') }}<em>*</em></h4>
      <div class="select-one-1">
        <ul class="ect-selects">
          <li class="ect-select" :class="{'active':item.cause == retrun_cause_id}" v-for="(item,index) in goods_cause" @click="causeSelect(item.cause)"><span>{{ item.lang }}</span></li>
        </ul>
      </div>
    </section>
    <section class="user-return-list-box padding-all bg-color-write m-top08">
      <h4 class="f-04 col-7">{{ $t('lang.return_reason') }}<em>*</em></h4>
      <div class="select-one-1">
        <select class="select form-control parent_cause_select" v-model="causeSelected">
          <option v-for="item in parent_cause" :value="item.cause_id">{{ item.cause_name }}</option>
        </select>
      </div>
    </section>
    <section class="user-return-list-box padding-all bg-color-write m-top08" v-if="shippingStatus && showReturnNumber">
      <h4 class="f-04 col-7">{{ $t('lang.return_number') }}<em>*</em></h4>
      <div class="select-one-1">
        <van-stepper v-model="value" integer :min="1" :max="applyRefoundDetail.return_goods_num" :step="1" />
      </div>
    </section>
    <section class="user-return-list-box padding-all bg-color-write m-top08">
      <h4 class="f-04 col-7">{{ $t('lang.problem_desc') }}<em>*</em></h4>
      <van-field
        v-model="return_brief"
        :placeholder="$t('lang.problem_desc')"
        type="textarea"
        class="not_padding"
      />
    </section>
    <section class="user-return-list-box padding-all bg-color-write m-top08">
      <h4 class="f-04 col-7">{{ $t('lang.application_credentials') }}</h4>
      <div class="select-one-1">
        <van-cell :title="$t('lang.has_test_report')" clickable class="not_padding" >
          <van-checkbox v-model="checked" />
        </van-cell>
      </div>
    </section>
    <section class="user-return-list-box padding-all bg-color-write m-top08">
      <h4 class="f-04 col-7">{{ $t('lang.pic_info') }}</h4>
      <div class="goods-info-img-box" v-if="materialList.length > 0">
        <div class="goods-info-img" v-for="(item,index) in materialList" :key="index">
          <img :src="item" />
          <i class="iconfont icon-delete" @click="deleteImg(index)"></i>
        </div>
      </div>
      <van-uploader :after-read="onRead()" accept="image/jpg, image/jpeg, image/png, image/gif" multiple>
        <div class="user-return-img">
          <h5><i class="iconfont icon-jiahao"></i></h5>
          <p>{{ $t('lang.pic_voucher') }}</p>
        </div>
      </van-uploader>
      <p class="f-03 col-7 m-top06"> {{ $t('lang.pic_prompt_notic_one') }}<br>{{ $t('lang.pic_prompt_notic_two') }}{{ returnPictures }}{{ $t('lang.pic_prompt_notic_two2') }}</p>
    </section>
    <template v-if="consignee">
    <section class="user-return-list-box padding-all bg-color-write m-top08" v-if="retrun_cause_id == 0 || retrun_cause_id == 2">
      <h4 class="f-04 col-7">{{ $t('lang.profile') }}<em>*</em></h4>
      <van-field v-model="addressee" :label="$t('lang.consignee')" :placeholder="$t('lang.enter_consignee')" class="my-bottom" />
      <van-field type="tel" v-model="mobile" :label="$t('lang.phone_number')" :placeholder="$t('lang.enter_mobile')" class="my-bottom" />
      <van-cell v-model="regionSplic" :title="$t('lang.region_alt')" class="my-bottom not_cell" is-link @click="handelRegionShow"/>
      <van-field v-model="address" :label="$t('lang.address_alt')" type="textarea" :placeholder="$t('lang.enter_address')" class="my-bottom" />
    </section>
    </template>
    <section class="user-return-list-box m-top08">
      <van-field v-model="return_remark" :label="$t('lang.message')" :placeholder="$t('lang.enter_message')" type="textarea" class="my-bottom" />
    </section>
    <div class="padding-all user-bg m-top12">
      <h4 class="f-04 col-6 m-b10"> {{ $t('lang.service_note')}}</h4>
      <p class="f-03 col-9">{{ $t('lang.return_explain_1')}}</p>
      <p class="f-03 col-9 m-top04">{{ $t('lang.return_explain_2')}}</p>
      <p class="f-03 col-9 m-top04">{{ $t('lang.return_explain_3')}}</p>
      <p class="f-03 col-9 m-top04">{{ $t('lang.return_explain_4')}}</p>
    </div>
    <div class="filter-btn dis-box">
      <a href="javascript:void(0)" class="btn btn-submit" @click="submitBtn()">{{ $t('lang.submit_apply') }}</a>
    </div>
    <Region :display="regionShow" :regionOptionDate="regionOptionDate" @updateDisplay="getRegionShow" @updateRegionDate="getRegionOptionDate"></Region>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import qs from 'qs'

import formProcessing from '@/mixins/form-processing'

import {
	Cell,
	CellGroup,
  Stepper,
  Field,
  Checkbox,
  CheckboxGroup,
  Uploader,
  Button,
  Dialog,
  Toast
} from 'vant'

export default{
  mixins: [formProcessing],
	data(){
		return {
      value:1,            //商品数量
      checked:false,      //是否有检测报告
      return_brief:'',    //退货问题描述
      retrun_cause_id:'', //退换货服务类型
      return_remark:'',   //退换货留言
      causeSelected:'',   //退换货原因
		}
	},
  components:{
  	[Cell.name]:Cell,
  	[CellGroup.name]:CellGroup,
    [Stepper.name]:Stepper,
    [Field.name]:Field,
    [CheckboxGroup.name]:CheckboxGroup,
    [Checkbox.name]:Checkbox,
    [Uploader.name]:Uploader,
    [Button.name]:Button,
    [Dialog.name]:Dialog,
    [Toast.name]:Toast,
  },
  created(){
    this.$store.dispatch('setApplyRefound',{
      rec_id:this.$route.query.rec_id,
      order_id:this.$route.query.order_id
    })

    if(this.getRegionData){
      this.regionOptionDate = this.getRegionData;
    }
  },
  computed:{
      ...mapState({
          materialList: state => state.user.materialList,
          applyRefoundDetail: state => state.user.applyRefoundDetail
      }),
      goodsList(){
          return this.applyRefoundDetail.goods_list ? this.applyRefoundDetail.goods_list : false
      },
      consignee(){
          return this.applyRefoundDetail.consignee
      },
      goods_cause(){
        return this.applyRefoundDetail.goods_cause
      },
      parent_cause(){
        return this.applyRefoundDetail.parent_cause
      },
      shippingStatus(){
          return this.applyRefoundDetail.order ? this.applyRefoundDetail.order.shipping_status : 0
      },
  	  showReturnNumber(){
  	      return this.applyRefoundDetail.show_return_number ? this.applyRefoundDetail.show_return_number : 0
  	  },
      addressee:{
        get(){
          return this.applyRefoundDetail.consignee.consignee
        },
        set(val){
          this.applyRefoundDetail.consignee.consignee = val
        }
      },
      mobile:{
        get(){
          return this.applyRefoundDetail.consignee.mobile
        },
        set(val){
          this.applyRefoundDetail.consignee.mobile = val
        }
      },
      address:{
        get(){
          return this.applyRefoundDetail.consignee.address
        },
        set(val){
          this.applyRefoundDetail.consignee.address = val
        }
      },
      returnGoodsNum(){
         return this.applyRefoundDetail.order.shipping_status == 0 ? this.applyRefoundDetail.return_goods_num : this.value
      },
      returnPictures(){
        return this.applyRefoundDetail.return_pictures ? this.applyRefoundDetail.return_pictures : 5
      }
  },
  methods:{
    onRead(){
      return file => {
        let length = 0
        if(file.length == undefined){
          length = this.materialList.length + 1
        }else{
          length = file.length + this.materialList.length
        }

        if(length > this.returnPictures){
          Toast(this.$t('lang.supplier_return') + this.$t('lang.return_max_pic_prompt') + this.returnPictures + this.$t('lang.return_max_pic_prompt2'));
        }else{
          this.$store.dispatch('setMaterial',{
            file:file
          })
        }
      }
    },
    causeSelect(id){
      this.retrun_cause_id = id
    },
    handelRegionShow(){
      this.regionShow = this.regionShow ? false : true
    },
    submitBtn(){
      let o = {
        rec_id:this.$route.query.rec_id,
        last_option:this.causeSelected,
        return_remark:this.return_remark,
        return_brief:this.return_brief,
        chargeoff_status:this.applyRefoundDetail.order.chargeoff_status,
        return_type:this.retrun_cause_id,
        return_images:this.materialList,
        return_number:this.value,
        addressee:this.addressee,
        mobile:this.mobile,
        code:this.email,
        return_address:this.address,
        province_region_id:this.regionOptionDate.province.id,
        city_region_id:this.regionOptionDate.city.id,
        district_region_id:this.regionOptionDate.district.id,
        street:this.regionOptionDate.street.id != '' ? this.regionOptionDate.street.id : 0
      }

      if(!this.return_brief){
        Toast(this.$t('lang.fill_in_problem_desc'))
        return false
      }else if(!this.retrun_cause_id){
        Toast(this.$t('lang.fill_in_service_type'))
        return false
      }else if(this.causeSelected == 0){
        Toast(this.$t('lang.fill_in_return_reason'))
        return false
      }

      this.$http.post(`${window.ROOT_URL}api/refound/submit_return`,qs.stringify(o)).then(({data:{data}})=>{
        Toast({
          message: data.msg,
          duration:1000
        })

        if(data.code == 0){
          this.returnApply()
        }
      })
    },
    deleteImg(val){
      Dialog.confirm({
          message: this.$t('lang.confirm_remove_pic'),
          className: 'text-center'
      }).then(() => {
        this.$store.dispatch('setDeleteImg',{
          index:val
        })
      })
    },
    returnApply(){
      setTimeout(() => {
        this.$router.push({
          name:'refound'
        })
      },1000)
    }
  },
  watch:{
    parent_cause(){
      this.causeSelected = this.parent_cause[0].cause_id
    },
    goods_cause(){
      this.retrun_cause_id =  this.goods_cause[0].cause
    },
    applyRefoundDetail(){
      this.value = this.applyRefoundDetail.return_goods_num;
      /* 已申请跳转到申请列表页 */
      if(this.applyRefoundDetail.msg){
        Toast({
          message: this.applyRefoundDetail.msg,
          duration:1000
        })

        this.returnApply()
      }
    }
  }
}
</script>
