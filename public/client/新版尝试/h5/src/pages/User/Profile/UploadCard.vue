<template>
	<div class="card-main-warp">
    <div class="containTop">
      <h6>{{$t('lang.fill_in_upload_id_number')}}</h6>
      <div class="photoBox">
      	<div class="photo">
	        <van-uploader :after-read="onRead('front_of_id_card')" accept="image/jpg, image/jpeg, image/png, image/gif" multiple>
	          <div class="cardA">
	          	<i class="preShow" :style="{backgroundImage:'url('+card_imgs.front_of_id_card+')',backgroundColor:card_imgs.front_of_id_card != '' ? '#fff' : ''}"></i>
	          </div>
	          <p>{{$t('lang.fill_in_upload_portrait')}}</p>
	        </van-uploader>
	      </div>
	      <div class="photo m-top10">
	        <van-uploader :after-read="onRead('reverse_of_id_card')" accept="image/jpg, image/jpeg, image/png, image/gif" multiple>
	          <div class="cardB">
	          	<i class="preShow" :style="{backgroundImage:'url('+card_imgs.reverse_of_id_card+')',backgroundColor:card_imgs.reverse_of_id_card != '' ? '#fff' : ''}"></i>
	          </div>
	          <p>{{$t('lang.fill_in_upload_national_emblem')}}</p>
	        </van-uploader>
	      </div>
      </div>
    </div>
    <div class="containBottom">
    	<h6 @click="prompt">{{$t('lang.fill_in_shoot_original')}}：<div class="fr"><i class="iconfont icon-sigh"></i>{{$t('lang.fill_in_upload_photoid')}}</div></h6>
    	<div class="m-top10"><img src="../../../assets/img/false-1.png" class="img"></div>
    	<div class="con-btn">
    		<template v-if="disabled == false">
    			<van-button size="large" :disabled="disabled" class="van-button--primary" @click="uploadCardImg">{{$t('lang.confirm_upload')}}</van-button>
    		</template>
	    	<template v-else>
	    		<van-button size="large" :disabled="disabled">{{$t('lang.confirm_upload')}}</van-button>
	    	</template>
	    </div>
    </div>
    <van-popup v-model="show" class="user-popup-right" :overlay="false">
    	<div class="rule-layer">
	    	<div class="rule-cont">
	    		<h6>{{$t('lang.fill_in_upload_photoid')}}</h6>
	    		<p class="rule-list">{{$t('lang.upload_card_propmt_1')}}<br>{{$t('lang.upload_card_propmt_2')}}<br>{{$t('lang.upload_card_propmt_3')}}<br>{{$t('lang.upload_card_propmt_4')}}<br>{{$t('lang.upload_card_propmt_5')}}<br>{{$t('lang.upload_card_propmt_6')}}<br></p>
	    	</div>
	    	<div class="close-btn" @click="close"><i class="iconfont icon-close"></i></div>
	    </div>
    </van-popup>
  </div>
</template>

<script>
import qs from 'qs'

import {
  Uploader,
  Button,
  Popup,
  Toast
} from 'vant'

export default{
	data(){
		return{
			show:false,
			card_imgs:{
				front_of_id_card:0,
				reverse_of_id_card:0
			}
		}
	},
	components:{
		[Uploader.name]:Uploader,
		[Button.name]:Button,
		[Popup.name]:Popup,
		[Toast.name]:Toast
	},
	created(){
		
	},
	computed:{
		disabled(){
			return this.card_imgs.front_of_id_card != 0 && this.card_imgs.reverse_of_id_card != 0 ? false : true
		}
	},
	methods:{
		onRead(item) {
	      return file => {
	      	if(file.length > 1){
	      		Toast(this.$t('lang.only_one_image_can_be_selected'))
	      	}else{
		      	this.$store.dispatch('setMaterial',{
		            file:file,
		            type:item
		        }).then(res=>{
			        if(item == 'front_of_id_card'){
		              this.card_imgs.front_of_id_card = res.data[0]
		            }else{
		              this.card_imgs.reverse_of_id_card = res.data[0]
		            }
		        })
	        }
	      }
	    },
		prompt(){
			this.show = this.show == false ? true : false
		},
		close(){
			this.show = false
		},
		uploadCardImg(){
			this.$router.push({
				name:'realname',
				query:this.card_imgs
			})
		}
	}
}
</script>