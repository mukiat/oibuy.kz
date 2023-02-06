<template>
  <div class="account" v-if="account">
  	<section class="purse-header-box">
  		<p>{{$t('lang.usable_balance')}}</p>
      <div class="dis-box">
    		<h2 class="box-flex">{{ account.user_money }}</h2>
    		<h5>{{$t('lang.frozen_balance')}}：{{ account.frozen_money }}</h5>
      </div>
      <div class="user-money-list dis-box">
        <router-link :to="{name:'bonus'}" class="list box-flex text-left"><span>{{$t('lang.bonus')}}<em>{{ account.bonus_count }}</em></span></router-link>
        <router-link :to="{name:'valueCard'}" class="list box-flex text-center"><span>{{$t('lang.value_card')}} <em>{{ value_card.num }}</em></span></router-link>
        <div class="list box-flex text-right"><span>{{$t('lang.integral')}} <em>{{ account.pay_points }}</em></span></div>
      </div>
  	</section>
    <section class="user-pur-box" v-if="account.user_balance_recharge > 0 || account.user_balance_withdrawal > 0">
      <div class="dis-box">
        <router-link :to="{name:'deposit'}" class="box-flex" v-if="account.user_balance_recharge==1">
          <h4 class="ellipsis-one">
            <img src="../../../assets/img/recharge_1.png" />{{$t('lang.recharge')}}
          </h4>
        </router-link>
        <router-link :to="{name:'accountRaply'}" class="box-flex" v-if="account.user_balance_withdrawal==1">
          <h4 class="ellipsis-one">
            <img src="../../../assets/img/recharge_2.png" />{{$t('lang.deposit')}}
          </h4>
        </router-link>
      </div>
    </section>
  	<section class="my-nav-box">
  		<router-link :to="{name:'accountDetail'}">
  			<div class="dis-box padding-all my-bottom">
  				<h3 class="my-u-title-size box-flex"><i class="iconfont icon-detail"></i>{{$t('lang.account_detail')}}</h3>
  				<span class="user-more"><i class="iconfont icon-more"></i></span>
  			</div>
  		</router-link>
		<router-link :to="{name:'integra',query:{type:account.pay_points}}">
			<div class="dis-box padding-all my-bottom">
				<h3 class="my-u-title-size box-flex"><i class="iconfont icon-jinqian"></i>Бонус</h3>
				<span class="user-more"><i class="iconfont icon-more"></i></span>
			</div>
		</router-link>
  		<router-link :to="{name:'accountLog'}">
  			<div class="dis-box padding-all my-bottom">
  				<h3 class="my-u-title-size box-flex"><i class="iconfont icon-partner"></i>{{$t('lang.application_record')}}</h3>
  				<span class="user-more"><i class="iconfont icon-more"></i></span>
  			</div>
  		</router-link>
  		<router-link :to="{name:'invForm'}">
  			<div class="dis-box padding-all my-bottom">
  				<h3 class="my-u-title-size box-flex"><i class="iconfont icon-invoices-query"></i>{{$t('lang.vat_invoice')}}</h3>
  				<span class="user-more"><i class="iconfont icon-more"></i></span>
  			</div>
  		</router-link>
  	</section>
    <CommonNav></CommonNav>
  </div>
</template>

<script>
import CommonNav from '@/components/CommonNav'

export default{
	name:'account',
	data(){
		return {
      account:Object,
      value_card:Object
    }
	},
  components:{
    CommonNav
  },
  created(){
    this.$store.dispatch('setAccount').then(res =>{
      this.account = res.data
      this.value_card = res.data.value_card
    })
  }
}
</script>
