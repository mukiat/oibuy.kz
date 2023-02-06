<template>
	<div class="drp-set">
		<div class="padding-all f-06 color-7">{{$t('lang.basic_set')}}</div>
		<van-uploader :after-read="onRead" class="show">
			<div class="header padding-all bg-color-write p-r dis-box">
				<div class="user-img img-common">
					<img v-if="drpSetData.shop_portrait" class="img" :src="drpSetData.shop_portrait" />
					<img src="../../../../assets/img/user_default.png" v-else />
				</div>
				<div class="box-flex title">
					<h3>{{$t('lang.shop_image')}}</h3>
				</div>
				<i class="iconfont icon-more p-a f-03 color-9"></i>
			</div>
		</van-uploader>
		<div class="input-list">
			<van-field class="f-04" v-model="drpSetData.shop_name" clearable :label="$t('lang.shop_name')" :placeholder="$t('lang.shop_name_placeholder')" />
			<van-field class="f-04" v-model="drpSetData.real_name" clearable :label="$t('lang.truename')" :placeholder="$t('lang.truename_placeholder')" />
			<van-field class="f-04" v-model="drpSetData.mobile" clearable :label="$t('lang.phone_number')" maxlength="11" :placeholder="$t('lang.enter_mobile')" />
			<van-field class="f-04" v-model="drpSetData.qq" clearable label="QQ" :placeholder="$t('lang.qq_placeholder')" />
		</div>
		<div class="padding-all f-02 color-7">{{$t('lang.upload_bg_image')}}</div>
        <van-uploader :after-read="onShop('drpBg')" class="show">
            <div class="bg-color-write padding-all">
                <div class="img-common">
                	<template v-if="materialPic">
                		<img :src="materialPic" />
                	</template>
                	<template v-else>
	                    <img :src="drpSetData.shop_img" v-if="drpSetData.shop_img" />
	                    <img :src="pic" v-else />
                    </template>
                </div>
            </div>
        </van-uploader>
		<div class="padding-all f-02 color-7">{{$t('lang.advanced_setup')}}</div>
        <div class="dis-box drp-model bg-color-write f-04">
            <div class="left-title">{{$t('lang.drp_select')}}</div>
            <van-radio-group v-model="radio" class="dis-box">
                <van-radio name="0" class="box-flex">{{$t('lang.all')}}</van-radio>
                <van-radio name="1" class="box-flex">{{$t('lang.category')}}</van-radio>
                <van-radio name="2" class="box-flex">{{$t('lang.goods')}}</van-radio>
            </van-radio-group>
        </div>
		<div class="padding-all">
			<van-button class="br-5 f-06" @click="drpSetClick" type="primary" bottom-action>{{$t('lang.confirm_update')}}</van-button>
		</div>
	</div>
</template>
<script>
	import { mapState } from 'vuex'
	import {
		Toast,
		Field,
		Button,
		RadioGroup,
		Radio,
		Uploader
	} from 'vant'

	import commonGet from '@/mixins/common-get'

	export default {
		name: "drp-set",
		components: {
			[Field.name]: Field,
			[Button.name]: Button,
			[Radio.name]: Radio,
			[RadioGroup.name]: RadioGroup,
			[Uploader.name]: Uploader,
			[Toast.name]: Toast
		},
		data() {
			return {
				pic:require('../../../../assets/img/user-shop.png'),
				materialPic:'',
				radio:'0',
				avatarUploadImage:''
			}
		},
		computed: {
			...mapState({
				drpSetData: state => state.drp.drpSetData
			})
		},
		mounted() {
			this.onLoad()
		},
		methods: {
			onLoad(){
				this.$store.dispatch('setDrpSet')
			},
			//头像上传
			onRead(file){
				this.imgPreview(file.file);
		    },
		    updataAvatar(){
		    	Toast.loading({ message:'上传中...',duration:0 });
		    	this.$store.dispatch('setMaterial',{
					file:{
						content:this.avatarUploadImage
					},
					type:'drpAvatar'
				}).then(res=>{
					this.$store.dispatch('setDrpUpdateAvatar',{
						pic:res.data[0],
						id:this.$store.state.drp.drpSetData.id
					})
				})
		    },
		    imgPreview(file) {
				let that = this;
				let orientation;

				//去获取拍照时的信息，解决拍出来的照片旋转问题
				that.Exif.getData(file, function () {
					orientation = that.Exif.getTag(this, "orientation");
				});

				// 看支持不支持FileReader
				if (!file || !window.FileReader) return;
				if (/^image/.test(file.type)) {
					// 创建一个reader
					let reader = new FileReader();
					// 将图片2将转成 base64 格式
					reader.readAsDataURL(file);
					// 读取成功后的回调
					reader.onloadend = function () {
						let result = this.result;
						let img = new Image();
						img.src = result;
						//判断图片是否大于500K,是就直接上传，反之压缩图片
						if (this.result.length <= 500 * 1024) {
							that.avatarUploadImage = this.result;
							that.updataAvatar();
						} else {
							img.onload = function () {
								let data = commonGet.compress(img, orientation);
								console.log(data)
								that.avatarUploadImage = data;
								that.updataAvatar();
							};
						}
					};
				}
		    },
			//店招上传
			onShop(item) {
				return file => {
			      	if(file.length > 1){
			      		Toast(this.$t('lang.only_one_image_can_be_selected'))
			      	}else{
				      	this.$store.dispatch('setMaterial',{
				            file:file,
				            type:item
				        }).then(res=>{
				        	this.materialPic = res.data[0]
				        })
			        }
				}
			},
			drpSetClick() {
				this.$store.dispatch('setDrpUpdate',{
					id: this.$store.state.drp.drpSetData.id,
					shop_name: this.$store.state.drp.drpSetData.shop_name,
					real_name: this.$store.state.drp.drpSetData.real_name,
					mobile: this.$store.state.drp.drpSetData.mobile,
					qq: this.$store.state.drp.drpSetData.qq,
					pic:this.materialPic,
					type:this.radio
				}).then(({data:data})=>{
					Toast(data.msg)
					this.$router.push({
						name:'drp'
					})
				})
			}
		},
		watch:{
			drpSetData(){
				this.radio = this.drpSetData.type ? this.drpSetData.type.toString() : '0'
			}
		}
	};
</script>
