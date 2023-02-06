<template>
  <div class="drp-order" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="200">
    <!--订单-tabs-->
    <van-tabs :active="active">
      <template v-if="!isLoading">
        <van-tab>
          <div class="nav_active" slot="title" @click="tabNav(2)">{{pageDrpOrder.all ? pageDrpOrder.all : $t('lang.all')}}</div>
        </van-tab>
        <van-tab>
          <div class="nav_active" slot="title" @click="tabNav(1)">{{pageDrpOrder.already_separate ? pageDrpOrder.already_separate : $t('lang.has_been_divide')}}</div>
        </van-tab>
        <van-tab>
          <div class="nav_active" slot="title" @click="tabNav(0)">{{pageDrpOrder.wait_separate ? pageDrpOrder.wait_separate : $t('lang.not_into')}}</div>
        </van-tab>
      </template>
    </van-tabs>
    <!--状态-list-->
    <template v-if="drpOrderData!=''">
      <div class="m-top10 drp-order-list bg-color-write" v-for="(item,index) in drpOrderData" :key="index">
        <div class="order-box">
          <div class="order-header dis-box">
            <div class="box-flex f-06 color-3">{{$t('lang.label_buyer')}}{{item.buy_user_name}}</div>
            <div class="f-03 color-red">{{item.status==0?$t('lang.not_into'):$t('lang.has_been_divide')}}</div>
          </div>
        </div>
        <div class="f-03 color-7">
          <div class="order-box ">
            <div class="order-cont border_bottom">
              <template v-if="item.log_type == 0 || item.log_type == 2">
                  <div>{{$t('lang.label_order')}}
                    <span class="color-3">{{item.order_sn}}</span>
                  </div>
              </template>
              <div>{{item.add_time_format}}</div>
            </div>
          </div>
        <template v-if="item.log_type == 0 || item.log_type == 2">
          <router-link :to="{name:'drp-orderdetail',params: { order_id: item.log_id }}" >
            <block v-for="(goods,goodsIndex) in item.goods_list" :key='goodsIndex'>
              <div :class="['dis-box', 'goodslist', 'flex_box', goodsIndex > 0 ? 'border_top_none' : '']">
                <div class="left">
                  <div class="img img-common">
                    <img v-if="goods.goods_thumb" class="img" :src="goods.goods_thumb" style="display: block;" />
                    <img v-else class="img" src="../../../../assets/img/not_goods.png" style="display: block;" />
                  </div>
                </div>
                <div class="right color-3 flex_1 flex_box fd_column jc_sb">
                  <h4 class="twolist-hidden m-top02 f-05">{{goods.goods_name}}</h4>
                  <div class="dis-box m-top10 ">
                    <span v-if="item.log_type == 0" style="margin-right: 1rem;">{{$t('lang.dis_commission')}}：&nbsp;<span class="size_14">{{goods.dis_commission}}</span></span>
                    <span>分成层级比例 ({{goods.drp_level_format}}) ：&nbsp;<span class="size_14">{{goods.level_per}}</span></span>
                  </div>
                </div>
              </div>
			  <ul class="commission_wrap border_bottom">
				<li class="commission_item">
					<span class="label_box">购买数量</span>
					<span class="value_box color-red">{{goods.goods_number}}</span>
				</li>
				<template v-if="item.log_type == 0">
					<li class="commission_item">
						<span class="label_box">计佣金额</span>
						<span class="value_box color-red">{{goods.drp_goods_price_format}}</span>
					</li>
					<li class="commission_item">
						<span class="label_box">计佣佣金</span>
						<span class="value_box color-red">{{goods.drp_money_format}}</span>
					</li>
				</template>
				<li class="commission_item">
					<span class="label_box">获得佣金</span>
					<span class="value_box color-red">{{goods.level_money_format}}</span>
				</li>
			  </ul>
            </block>
          </router-link>
          </template>
          <template v-else-if="item.log_type == 1 || item.log_type == 3">
          <router-link :to="{name:'drp-orderdetail',params: { order_id: item.log_id }}" >
              <block v-for="(item,index) in item.goods_list" :key='index'>
                <div class="dis-box goodslist flex_box">
                  <div class="left">
                    <div class="img img-common">
                      <img v-if="item.goods_thumb" class="img" :src="item.goods_thumb" style="display: block;" />
                      <img v-else class="img" src="../../../../assets/img/not_goods.png" style="display: block;" />
                    </div>
                  </div>
                  <div class="right color-3 flex_1 flex_box fd_column jc_sb">
                    <h4 class="twolist-hidden m-top02 f-05">{{item.goods_name}}</h4>
                    <div class="dis-box m-top10">
                      
                      <span class="yongjinbili">{{$t('lang.dis_commission')}} ({{item.drp_level_format}}) ：&nbsp;<span class="color-red">{{item.level_per}}</span></span>
                    </div>
                  </div>
                </div>
                
              </block>
              
          </router-link>
          </template>
          <div class="padding-all commission_all"> 
            <span>佣金总和 <span class="ico" @click="show = true">?</span></span>
            <span v-html="item.money_format"></span>
          </div>
        </div>
      </div>
      <div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
      <template v-if="loading">
        <van-loading type="spinner" color="black" />
      </template>
    </template>
    <template v-else>
      <NotCont/>
    </template>
    <CommonNav :routerName="routerName">
      <li slot="aloneNav">
        <router-link :to="{name: 'drp'}">
          <i class="iconfont icon-fenxiao"></i>
          <p>{{$t('lang.drp_center')}}</p>
        </router-link>
      </li>
    </CommonNav>
	<van-popup v-model="show">
		<div class="pop_content">
			<header class="pop_header">
				<span>说明</span>
				<div class="pop_close" @click="show = false"><i class="iconfont icon-close size_12"></i></div>
			</header>
			<p class="content">
				1、佣金比例：指商品参与分成的佣金比例；<br />
				2、分成层级比例（X级）：指当前会员所属会员层级的分成比例；<br />
				3、计佣金额：指商品实际计算佣金的金额。去除红包、优惠券、储值卡折扣等折扣均摊后的金额；<br />
				4、计佣佣金：计算：计佣金额 X 商品参与分成的佣金比例；<br />
				5、获得佣金：指当前会员在该笔订单内，每件商品可获得的佣金数。（计算：计佣佣金 X 分成层级比例）；<br />
				6、佣金总和：指当前会员在该笔订单内，所有商品获得的佣金数总和；
			</p>
		</div>
	</van-popup>
	<!--初始化loading-->
	<DscLoading :dscLoading="dscLoading"></DscLoading>
  </div>
</template>
<script>
  import { mapState } from 'vuex'
  import DscLoading from '@/components/DscLoading'

  import {
    Toast,
    Tab,
    Tabs,
    Waterfall,
    Loading,
	Popup
  } from 'vant'

  import CommonNav from '@/components/CommonNav'
  import NotCont from '@/components/NotCont'
  import arrRemove from '@/mixins/arr-remove'

  export default {
    name: "drp-order",
    components: {
      CommonNav,
	  DscLoading,
      NotCont,
      [Toast.name]: Toast,
      [Tab.name]: Tab,
      [Tabs.name]: Tabs,
      [Loading.name]: Loading,
      [Popup.name]: Popup
    },
    directives: {
      WaterfallLower: Waterfall('lower')
    },
    data() {
      return {
        routerName:'drp',
        active: 0,
        status:2,
        disabled:false,
        loading:true,
        size:10,
        page:1,
        footerCont:false,
        type:this.$route.query.type ? this.$route.query.type : 'order',
        pageDrpOrder: {},
        isLoading: false,
    		show: false,
    		dscLoading: true
      };
    },
    beforeCreate(){
      document.title = this.$route.query.type == 'order' ? this.$t('lang.sale_reward') : this.$t('lang.card_reward');
    },
    async created() {
      await this.getCustomText();
      this.orderList(1)
    },
    //运算，方法使用不用加()
    computed: {
      drpOrderData:{
        get(){
          return this.$store.state.drp.drpOrderData
        },
        set(val){
          this.$store.state.drp.drpOrderData = val
        }
      }
    },
    methods: {
      //定义列表数据方法
      orderList(page) {
        if(page){
          this.page = page
          this.size = Number(page) * 10
        }

        this.$store.dispatch('setDrpOrder',{
          type: this.type,
          page: this.page,
          size: this.size,
          status: this.status
        })
      },
      loadMore(){
        setTimeout(() => {
          this.disabled = true
          if(this.page * this.size == this.drpOrderData.length){
            this.page ++
            this.orderList()
          }
        },200);
      },
      //状态切换
      tabNav(val) {
        this.status = val
        this.orderList(1)
      },
      // 分销管理-自定义设置数据
      async getCustomText() {
        this.isLoading = true;

        const {data: {status, data: {page_drp_order}}} = await this.$http.post(`${window.ROOT_URL}api/drp/custom_text`, {code: 'page_drp_order'});

        if (status == 'success') {
          this.pageDrpOrder = page_drp_order || {};
          this.isLoading = false;
        }
      }
    },
    watch:{
      drpOrderData(){
        if(this.page * this.size == this.drpOrderData.length){
          this.disabled = false
          this.loading = true
        }else{
          this.loading = false
          this.footerCont = this.page > 1 ? true : false
        }

        this.drpOrderData = arrRemove.trimSpace(this.drpOrderData)
		    this.dscLoading = false;
      }
    }
  };
</script>

<style lang="scss" scoped>
.yongjinbili {
  color: #777;
  text-align: right;
}
.mw_100 {
  margin-right: 1rem;
}
.border_top {
    border-top: 1px solid #f4f4f4!important;
}
.border_bottom {
    border-bottom: 1px solid #f4f4f4!important;
}
.border_top_none {
  border-top: none!important;
}
.drp-order {
	.drp-order-list .goodslist {
		padding: 1.2rem;
		border: none;
	}
	.commission_wrap {
		display: flex;
		margin: 0 1.2rem;
		padding: 1.2rem 0;
		border-radius: 1rem;
		background-color: #F9F9F9;
		.commission_item {
			flex: auto;
			display: flex;
			flex-direction: column;
			align-items: center;
			color: #333;
			.value_box {
				margin-top: 1rem;
			}
		}
	}
	.commission_all {
		display: flex;
		justify-content: space-between;
		align-items: center;
		span {
			font-size: 1.4rem;
			font-weight: bold;
			color: #333;
			&:nth-child(1) {
				position: relative;
				.ico {
					position: absolute;
					top: 50%;
					right: -2.5rem;
					transform: translateY(-50%);
					width: 1.8rem;
					height: 1.8rem;
					border-radius: 50%;
					line-height: 1.8rem;
					text-align: center;
					font-size: 1.2rem;
					color: #fff;
					background-color: #FEA402;
				}
			}
		}
	}
	.van-popup {
		width: 90%;
		border-radius: 0.6rem;
	}
	.pop_content {
		padding: 1.35rem 0;
		.pop_header {
			position: relative;
			margin: 0 1.35rem 1.35rem;
			font-size: 1.6rem;
			text-align: center;
			color: #282828;
			font-weight: 700;
			.pop_close {
				position: absolute;
				bottom: 0;
				right: 0.5rem;
			}
		}
		.content {
			padding: 0 1.35rem;
		}
	}
}
</style>
