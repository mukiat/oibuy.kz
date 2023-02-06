<template>
	<view class="drp-warp drp-set">
		<view class="title">{{$t('lang.basic_set')}}</view>
		<form @submit="formSubmit">
			<view class="avatar" @click="chooseImage('touxian')">
				<view class="user-img">
					<image :src="drpSetData.shop_portrait" v-if="drpSetData.shop_portrait"></image>
					<image :src="imagePath.userDefaultImg" v-else></image>
				</view>
				<view class="tit">{{$t('lang.shop_image')}}</view>
				<uni-icons type="forward" size="16" color="#999999"></uni-icons>
			</view>
			<view class="input-list">
				<view class="uni-form-item uni-inline-item">
					<view class="title">{{$t('lang.shop_name')}}</view>
					<input type="text" name="shopName" v-model="drpSetData.shop_name" :placeholder="$t('lang.shop_name_placeholder')" />
				</view>
				<view class="uni-form-item uni-inline-item">
					<view class="title">{{$t('lang.truename')}}</view>
					<input type="text" name="realName" v-model="drpSetData.real_name" :placeholder="$t('lang.truename_placeholder')" />
				</view>
				<view class="uni-form-item uni-inline-item">
					<view class="title">{{$t('lang.label_mobile2')}}</view>
					<input type="number" name="mobile" v-model="drpSetData.mobile" :placeholder="$t('lang.phone_placeholder')" />
				</view>
				<view class="uni-form-item uni-inline-item">
					<view class="title">{{$t('lang.qq_number')}}</view>
					<input type="number" name="qq" v-model="drpSetData.qq" :placeholder="$t('lang.qq_placeholder')" />
				</view>
				<view class="uni-list">
					<view class="title">{{$t('lang.drp_select')}}</view>
					<view class="value">
						<radio-group @change="valueRadioHandle">
							<label class="uni-list-cell uni-list-cell-not" v-for="(item,index) in list" :key="index">
								<view>
									<radio :value="item.value" :checked="item.value == radio" color="#f92028" />
								</view>
								<view>{{item.name}}</view>
							</label>
						</radio-group>
					</view>
				</view>
			</view>
			<view class="title">{{$t('lang.upload_bg_image')}}</view>
			<view class="imgbg" @click="chooseImage('bg')">
				<block v-if="materialPic">
					<image :src="materialPic" mode="widthFix"></image>
				</block>
				<block v-else>
					<image :src="drpSetData.shop_img" mode="widthFix" v-if="drpSetData.shop_img"></image>
					<image :src="pic" mode="widthFix" v-else></image>
				</block>
			</view>
			<view class="btn-bar btn-bar-radius">
				<button class="btn btn-red" formType="submit">{{$t('lang.subimt')}}</button>
			</view>
		</form>
		
		<dsc-common-nav>
			<navigator url="../drp" class="nav-item" slot="right">
				<view class="iconfont icon-fenxiao"></view>
				<text>{{$t('lang.my_drp')}}</text>
			</navigator>
		</dsc-common-nav>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	import uniIcons from '@/components/uni-icons/uni-icons.vue';
	import dscNotContent from '@/components/dsc-not-content.vue';
	import dscCommonNav from '@/components/dsc-common-nav.vue';
	
	import { pathToBase64, base64ToPath } from '@/common/image-tools/index.js'
	
	var graceChecker = require("@/common/graceChecker.js");
	export default {
		data() {
			return {
				list:[{
					value:0,
					name: this.$t('lang.all')
				},{
					value:1,
					name: this.$t('lang.category')
				},{
					value:2,
					name: this.$t('lang.goods')
				}],
				materialPic:''
			}
		},
		components:{
			uniIcons,
			dscNotContent,
			dscCommonNav,
		},
		onLoad() {
			this.load()
		},
		computed: {
			...mapState({
				drpSetData: state => state.drp.drpSetData,
			}),
			radio:{
				get(){
					return this.drpSetData.type
				},
				set(val){
					this.drpSetData.type = val
				}
			},
			pic(){
				return this.imagePath.userShop
			}
		},
		methods: {
			load(){
				this.$store.dispatch('setDrpSet')
			},
			formSubmit(e){
				var rule = [
					{name:"shopName", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.shop_name_not_null')},					
					{name:"mobile", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.mobile_not_null')},
					{name:"realName", checkType : "notnull", checkRule:"",  errorMsg:this.$t('lang.truename_not_null')}
				];
				
				//进行表单检查
				var formData = e.detail.value;
				var checkRes = graceChecker.check(formData, rule);
				
				if (checkRes) {
					this.$store.dispatch('setDrpUpdate',{
						id: this.drpSetData.id,
						shop_name: e.detail.value.shopName,
						real_name: e.detail.value.realName,
						mobile: e.detail.value.mobile,
						qq: e.detail.value.qq,
						pic:this.materialPic,
						type:this.radio
					}).then(res=>{
						uni.showToast({
							title: res.data.msg,
							icon: "none",
							complete: (data) => {
								uni.navigateTo({
									url:'../drp'
								})
							}
						});
					})
				} else {
					uni.showToast({
						title: graceChecker.error,
						icon: "none"
					});
				}
			},
			chooseImage(type){
				uni.chooseImage({
					count:1,
					sizeType: ['compressed'],
					success:(res)=>{
						pathToBase64(res.tempFilePaths[0]).then(base64 => {
							this.$store.dispatch('setMaterial',{
								file:{
									content:base64
								},
								type:'danzhang'
							}).then(data=>{
								if(type == 'touxian'){
									this.$store.dispatch('setDrpUpdateAvatar',{
										pic:data.data[0],
										id:this.$store.state.drp.drpSetData.id
									})
								}else{
									this.materialPic = data.data[0]
								}
							})
						}).catch(error => {
							console.error(error,5);
						});
					}
				})
			},
			valueRadioHandle(res){
				this.radio = res.detail.value.toString()
			}
		}
	}
</script>

<style>
.drp-warp .input-list{ background: #FFFFFF; border-radius: 10upx; margin: 0; }
.drp-warp .input-list .uni-form-item .title{ min-width: 120upx;}
.drp-warp .btn-bar{ margin-top: 40upx; padding: 0 20upx;}
.drp-set { padding-bottom: 100upx;}
.drp-set .title{ padding: 20upx; color: #444444; font-size: 32upx;}
.drp-set .avatar{ display: flex; flex-direction: row; justify-content: flex-start; align-items: center; background: #FFFFFF; padding:20upx; border-bottom: 1px solid #f4f4f4;}
.drp-set .avatar .user-img{ width: 120upx; height: 120upx; border-radius: 100%; overflow: hidden;}
.drp-set .avatar .user-img image{ width: 100%; height: 100%; }
.drp-set .avatar .tit{ flex: 1 1 0%; padding-left: 30upx; font-size: 32upx; color: #666666; }
.drp-set .imgbg{ padding: 20upx; background: #FFFFFF; box-sizing: border-box;}
.drp-set .imgbg,.drp-set .imgbg image{ width: 100%;}

.uni-list{ display: flex; flex-direction: row;}
.uni-list .title{  }
.uni-list .value{ flex: 1; display: flex; justify-content: flex-start; align-items: center; }
.uni-list radio-group{ display: flex; flex-direction: row; justify-content: flex-start; align-items: center;}
.uni-list:after,
.uni-list:before{ height: 0; }

radio {
  transform:scale(0.8);
}
</style>
