<template>
  <div class="con">
  	<ShopHeader :shopInfo="shopInfo" :index="index" :shopScore="shopScore" @update="updateInfo"></ShopHeader>
  	<div class="shopping-info-nums bg-color-write">
  		<ul class="dis-box text-center">
  			<li class="box-flex">
  				<a href="javascript:;" @click="sAllProductUrl">
  					<h4>{{ shopDetail.count_goods }}</h4>
  					<p>{{$t('lang.all_goods')}}</p>
  				</a>
  			</li>
  			<li class="box-flex">
  				<a href="javascript:;" @click="sNewProductUrl">
  					<h4>{{ shopDetail.count_goods_new }}</h4>
  					<p>{{$t('lang.new')}}</p>
  				</a>
  			</li>
  			<li class="box-flex">
  				<a href="javascript:;" @click="sPromotePoductUrl">
  					<h4>{{ shopDetail.count_goods_promote }}</h4>
  					<p>{{$t('lang.promotion')}}</p>
  				</a>
  			</li>
  		</ul>
  	</div>
  	<van-cell-group class="m-top08">
  		<van-cell class="my-bottom" :title="$t('lang.online_service')" @click="onChat(0,shopDetail.ru_id)">
  			<div class="van-cell__right-icon"><i class="iconfont icon-kefu"></i></div>
  		</van-cell>
  		<van-cell class="my-bottom" :title="$t('lang.shop_qr_code')" @click="handleCode">
  			<div class="van-cell__right-icon"><i class="iconfont icon-904anquansaoma"></i></div>
  		</van-cell>
      <template v-if="shopDetail.kf_tel && shopDetail.is_ru_tel == 1">
      <a :href="'tel:'+ shopDetail.kf_tel">
        <van-cell class="my-bottom" :title="$t('lang.shop_tel')">
          <div class="van-cell__right-icon"><i class="iconfont icon-a"></i></div>
        </van-cell>
      </a>  
      </template>
      <template v-else-if="shopDetail.is_ru_tel == 1">
        <van-cell class="my-bottom" :title="$t('lang.shop_tel')" @click="noTel">
          <div class="van-cell__right-icon"><i class="iconfont icon-a"></i></div>
        </van-cell>
      </template>
	  <van-cell class="my-bottom" :title="$t('lang.shop_tel')" @click="telShow=true" v-if="shopDetail.is_ru_tel == 0">
	  	<div class="van-cell__right-icon"><i class="iconfont icon-a"></i></div>
	  </van-cell>
  	</van-cell-group>

  	<van-cell-group class="van-cell-noleft m-top08">
  		<van-cell class="my-bottom" :title="$t('lang.company_profile')"></van-cell>
  		<van-cell class="my-bottom" :title="$t('lang.certificate_info')" @click="seeLicense">
        <div class="van-cell__value dis-box" solt="value">
          <span class="box-flex"></span>
          <div class="van-cell__right-icon"><i class="iconfont icon-iconfontzhizuobiaozhun10" style="color: #21ba45;"></i></div>
        </div>
      </van-cell>
      <van-cell class="my-bottom" :title="$t('lang.label_corporate_name')" :value="shopDetail.shop_name"></van-cell>
			<van-cell class="my-bottom" :title="$t('lang.label_region')" >
				<div class="van-cell__value dis-box" solt="value">
					<span class="box-flex">{{ shopDetail.shop_address }}</span>
					<!-- <div class="van-cell__right-icon"><i class="iconfont icon-location"></i></div> -->
				</div>
			</van-cell>
			<van-cell class="my-bottom" :title="$t('lang.label_main_brand')" :value="shopDetail.shoprz_brand_name"></van-cell>	
			<van-cell class="my-bottom" :title="$t('lang.label_seller_Grade')">
				<div class="van-cell__value dis-box" solt="value">
					<img :src="shopDetail.grade_img" width="20" height="20" v-if="shopDetail.grade_img"> {{shopDetail.grade_name}}
				</div>
			</van-cell>	
  	</van-cell-group>

    <van-popup v-model="show" class="show-temark-div">
      <h4>{{ shopDetail.shop_name }}</h4>
      <div class="img-new-box"><img :src="shopDetail.shop_qrcode"></div>
      <p>{{$t('lang.rm_wd_info_zz')}}</p>
    </van-popup>
	
	<van-popup v-model="telShow" class="show-temark-div2">
	  <div class="tip">{{$t('lang.is_tel_tip_1')}}</div>
	  <div class="desc">{{$t('lang.is_tel_tip_2')}}</div>
	  <div class="b-btn" @click="link">{{$t('lang.is_tel_tip_3')}}</div>
	  
	  <img src="@/assets/img/video/close.png" class="close" @click="telShow=false" />
	</van-popup>

    <van-popup position="right" :overlay="true" v-model="LicenseShow">
      <div class="license-div">
        <div class="title">{{$t('lang.rm_prompt_info')}}</div>
        <div class="content" v-if="basic_info">
          <div>{{$t('lang.label_companyName')}}{{ basic_info.company_name }}</div>
          <div>{{$t('lang.label_business_license_id')}}{{ basic_info.business_license_id }}</div>
          <div>{{$t('lang.label_legal_person')}}{{ basic_info.legal_person }}</div>
          <div>{{$t('lang.label_license_comp_adress')}}{{ basic_info.license_comp_adress }}</div>
          <div>{{$t('lang.label_registered_capital')}}{{ basic_info.registered_capital }}</div>
          <div>{{$t('lang.label_business_term')}}{{ basic_info.business_term }}</div>
          <div>{{$t('lang.label_busines_scope')}}{{ basic_info.busines_scope }}</div>
          <div>{{$t('lang.label_company_located')}}<span v-html="basic_info.company_adress"></span></div>
          <div>{{$t('lang.label_shop_name')}}{{ shopDetail.shop_name }}</div>
          <div v-if="basic_info.license_fileImg">
			  {{$t('lang.label_license_img')}}
			  <img :src="basic_info.license_fileImg" @click="previewImage(basic_info.license_fileImg)" class="img" />
          </div>
          <strong>{{$t('lang.rm_prompt_help')}}</strong>
          <div class="close-btn" @click="closeBtn"><i class="iconfont icon-close"></i></div>
        </div>
      </div>
    </van-popup>
  </div>
</template>

<script>
import { mapState } from 'vuex'

import { Cell, CellGroup, Popup, Toast, ImagePreview} from 'vant'
import ShopHeader from '@/components/shop/ShopHeader'
import formProcessing from '@/mixins/form-processing.js'

export default{
  mixins: [formProcessing],
	data(){
		return {
		  show: false,
		  shopScore:true,
		  index: 0,
		  LicenseShow:false,
		  telShow:false
		}
	},
	components:{
		ShopHeader,
		[Cell.name] : Cell,
		[CellGroup.name] : CellGroup,
    [Popup.name] : Popup,
    [Toast.name] : Toast
	},
  created(){
    this.$store.dispatch('setShopDetail',{
      ru_id:this.$route.params.id
    })
  },
  computed:{
    ...mapState({
      shopDetail: state => state.shop.shopDetail
    }),
    basic_info(){
      return this.shopDetail.basic_info
    },
    is_collect_shop:{
      get(){
        return this.shopDetail.is_collect_shop
      },
      set(val){
        this.shopDetail.is_collect_shop = val
      }
    },
    count_gaze:{
      get(){
        return this.shopDetail.count_gaze
      },
      set(val){
        this.shopDetail.count_gaze = val
      }
    },
    shopInfo(){
      let arr = []

      arr[this.index] = {
        shopName:this.shopDetail.shop_name,
        logo:this.shopDetail.logo_thumb,
        ru_id:this.shopDetail.ru_id,
        commentdelivery:this.shopDetail.commentdelivery,
        commentdelivery_font:this.shopDetail.commentdelivery_font,
        commentrank:this.shopDetail.commentrank,
        commentrank_font:this.shopDetail.commentrank_font,
        commentserver:this.shopDetail.commentserver,
        commentserver_font:this.shopDetail.commentserver_font,
        count_gaze:this.count_gaze,
        is_collect_shop:this.is_collect_shop,
      }

      return arr
    },
  },
  methods:{
	  
	previewImage(item){
		ImagePreview({
		  images: [item],
		})
	},
	  
    handleCode(){
      this.show = true;
    },
    updateInfo(obj){
      
      this.is_collect_shop = obj.is_collect_shop
      this.count_gaze = this.is_collect_shop == 1 ? this.count_gaze + 1 : this.count_gaze - 1 
    },
    sAllProductUrl(){
      this.$router.push({
        name:'shopGoodsList',
        query:{
          ru_id:this.shopDetail.ru_id,
          type:'goods_id'
        }
      })
    },
    sNewProductUrl(){
      this.$router.push({
        name:'shopGoodsList',
        query:{
          ru_id:this.shopDetail.ru_id,
          type:'store_new'
        }
      })
    },
    sPromotePoductUrl(){
      this.$router.push({
        name:'shopGoodsList',
        query:{
          ru_id:this.shopDetail.ru_id,
          type:'is_promote'
        }
      })
    },
    seeLicense(){
      this.LicenseShow = true
    },
    closeBtn(){
      this.LicenseShow = false
    },
    noTel(){
      Toast('Бұл дүкен тел.нөмір қоймаған')
    },
	link(){
		let that = this
		that.$router.push({
			name: 'drp-register'
		});
	}
  }
}
</script>
<style>
	.close {
		display: block;
		width: 21px;
		height: 21px;
		border-radius: 50%;
		float: right;
		margin-bottom: 15px;
		background: rgba(0,0,0,.6);
		padding: 5px;
		box-sizing: content-box;
		position: absolute;
		top: -15px;
		right: -15px;
		z-index: 999;
	}
</style>