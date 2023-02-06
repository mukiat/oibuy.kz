<template>
	<view class="merchants">
		<block v-if="queryInfo.step == 1">
			<image :src="imagePath.merchantBg" class="merchant_bg" mode="widthFix"></image>
			<view class="footer_box uni-flex uni-column safe-area-inset-bottom">
				<view class="checkbox" :class="{'checked':checked}">
					<view class="checkbox-icon" @click="checked = !checked">
						<uni-icons type="checkmarkempty" size="16" color="#ffffff"></uni-icons>
					</view>
					<view class="checkbox-con">
						<text class="txt" @click="checked = !checked">{{$t('lang.merchants_read')}}</text>
						<text class="uni-blue" @click="show = !show">《{{$t('lang.merchants_agreement')}}》</text>
					</view>
				</view>
				<view class="btn-bar btn-bar-radius">
					<button class="btn btn-red" :class="[checked ? 'btn-red' : 'btn-disabled2']" @click="nowOpen">{{$t('lang.merchants_nowStore')}}</button>
				</view>
			</view>
			
			<!--入驻文章-->
			<view class="popup-article uni-popup-bottom" v-show="show">
				<view class="content">
					<rich-text :nodes="articleContent"></rich-text>
				</view>
				<view class="btn-bar btn-bar-radius">
					<button class="btn btn-red" @click="haveRed">{{$t('lang.merchants_isread')}}</button>
				</view>
			</view>
		</block>
		<block v-if="queryInfo.step == 2">
			<enterType :user-info="queryInfo" @go-next="setStep"></enterType>
		</block>
		<block v-if="queryInfo.step == 3">
			<basicInfo :user-info="queryInfo" @go-next="setStep"></basicInfo>
		</block>
		<block v-if="queryInfo.step == 4">
			<mainInfo :user-info="queryInfo" @go-next="setStep" @set-main="setMainInfo"></mainInfo>
		</block>
		<block v-if="queryInfo.step == 5">
			<storeInfo :user-info="queryInfo" @go-next="setStep" @submit-apply="addCateById"></storeInfo>
		</block>
		
		<dsc-loading :dscLoading="dscLoading"></dsc-loading>
	</view>
</template>

<script>
	import { mapState } from 'vuex'
	
	import uniPopup from '@/components/uni-popup.vue';
	
	import basicInfo from './components/basicInfo.vue';
	import enterType from './components/enterType.vue';
	import mainInfo from './components/mainInfo.vue';
	import storeInfo from './components/storeInfo.vue';
	
	export default {
		components:{
			uniPopup,
			basicInfo,
			enterType,
			mainInfo,
			storeInfo
		},
		data() {
			return {
				checked: false,
				show: false,
				dscLoading: false,
				protocol: {},
				steps: {},
				queryInfo: {
					uname: '',
					mobile: '',
					step: 1,
					fid: 0,
					enterType: 1,
					company: '',
					companyTel: '',
					business_license:'',
					id_card:'',
					id_card_one:'',
					id_card_two:'',
					id_card_three:'',
					columns: ['请选择',this.$t('lang.flagship_store'), this.$t('lang.exclusive_shop'), this.$t('lang.franchised_store')],
					shopName: '',
					stopLoginName: '',
					cateId: '',
					storeTypeKey: '',
					storeTypeVal: '',
					allCheckedCate: {},
					allCheckedSubCate: [],
					allParentCate: []
				}
			}
		},
		onLoad(e){
			this.merchants(0)
		},
		computed: {
			...mapState({
				merchantsCategory: state => state.user.merchantsCategory
			}),
			articleContent() {
				let result = this.protocol.article_content;
				const reg = /style\s*=(['\"\s]?)[^'\"]*?\1/gi;
				const regex = new RegExp('<img', 'gi');
				const regex2 = new RegExp('<p', 'gi');
				const regex3 = new RegExp('<div', 'gi');
				const regex4 = new RegExp('<h2', 'gi');
			
				if (result) {
					result = result.replace(reg, '');
					result = result.replace(regex, '<img style="width: 100% !important; height:auto;vertical-align:top;"');
					result = result.replace(regex2, '<p style="margin:0;padding:0;"');
					result = result.replace(regex3, '<div style="margin-bottom: 20upx; width:100%;"');
					result = result.replace(regex4, '<h2 style="width:100%; font-size:14px; font-weight:700;"');
				}
				result = `<div style="display:flex; flex-direction: column;">${result}</div>`;
				
				return result;
			},
		},
		methods: {
			async merchants(i){
				this.dscLoading = true;
				const { data, status } = await this.$store.dispatch('getMerchantsHandle')
				
				if (status == 'success') {
					this.getMerchantsGuide();
					
					//步骤
					const stepsObj = data.steps || {};
					//店铺信息
					const shopObj = data.shop || {};
					//合并对象
					const newObj = Object.assign(stepsObj, shopObj);
					
					const newObj2 = {};
					
					const { merchants_audit, steps_audit } = newObj;
					const mapObj = {
						uname: 'contactName',
						mobile: 'contactPhone',
						step: 'step_id',
						fid: 'fid',
						enterType: 'is_personal',
						company: 'company',
						companyTel: 'company_contactTel',
						business_license:'license_fileImg',
						id_card:'legal_person_fileImg',
						id_card_one:'id_card_img_one_fileImg',
						id_card_two:'id_card_img_two_fileImg',
						id_card_three:'id_card_img_three_fileImg',
						shopName: 'rz_shop_name',
						stopLoginName: 'hope_login_name',
						storeTypeKey: 'shoprz_type',
						storeTypeVal: 'shop_name_suffix'
					}
	
					for (const key in mapObj) {
						const item = newObj[mapObj[key]];
						if (mapObj.hasOwnProperty(key) && item) {
							if (mapObj[key] == 'is_personal') newObj2[key] = parseInt(item) + 1;
							else newObj2[key] = item;
						}
					}
					
					if (merchants_audit == 2 && steps_audit == 0 && i == 2) newObj2.step = 2;
					
					if (i == 0) {
						const merchantInfo = uni.getStorageSync("merchantInfo") || {};
						switch (merchantInfo.step) {
							case 2:
								delete newObj2.step;
								break;
							case 3:
								delete newObj2.step;
								delete newObj2.enterType;
								break;
							case 4:
								delete newObj2.step;
								delete newObj2.enterType;
								delete newObj2.uname;
								delete newObj2.mobile;
								break;
							case 5:
								delete newObj2.step;
								delete newObj2.enterType;
								delete newObj2.uname;
								delete newObj2.mobile;
								delete newObj2.company;
								delete newObj2.companyTel;
								delete newObj2.business_license;
								delete newObj2.id_card;
								delete newObj2.id_card_one;
								delete newObj2.id_card_two;
								delete newObj2.id_card_three;
								break;
							default:
				
								break;
						}
						
						this.setStep(newObj2);
					} else {
						this.setStep(newObj2);
					}
				}
			},
			async getMerchantsGuide(){
			   const { data, status } = await this.$store.dispatch('getMerchantsGuideHandle')
			   if (status == 'success') {
					if (data.article_content) {
						this.protocol = data;
						this.dscLoading = false;
					}
				}
			},
			//分类
			getMerchantsShop(){
				this.$store.dispatch('setMerchantsShop')
			},
			//已阅读
			haveRed() {
				this.checked = true;
				this.show = false;
			},
			nowOpen() {
				if (this.checked) {
					this.queryInfo.step ++
				}
			},
			setStep(obj = {}) {
				const merchantInfo = uni.getStorageSync("merchantInfo") || {};
				for (const key in obj) {
					if (obj.hasOwnProperty(key)) {
						merchantInfo[key] = obj[key]
					}
				}
				
				uni.setStorageSync('merchantInfo', merchantInfo);
	
				this.getStep();
			},
			getStep() {
				const merchantInfo = uni.getStorageSync("merchantInfo") || {};
				const queryData = JSON.parse(JSON.stringify(this.queryInfo));
				for (const key in merchantInfo) {
					if (merchantInfo.hasOwnProperty(key)) {
						queryData[key] = merchantInfo[key]
					}
				}
				this.queryInfo = queryData;
				
				switch (this.queryInfo.step) {
					case 1:
						break;
					case 2:
						uni.setNavigationBarTitle({ title: this.$t('lang.merchants_type')});
						break;
					case 3:
						uni.setNavigationBarTitle({ title: this.$t('lang.basic_info')});
						break;
					case 4:
						uni.setNavigationBarTitle({ title: this.$t('lang.main_info')});
						break;
					case 5:
						uni.setNavigationBarTitle({ title: this.$t('lang.shop_detail')});
						if (this.merchantsCategory.length == 0) this.getMerchantsShop();
						break;
					case 6:
						uni.redirectTo({ url:'./reviewProgress' });
						break;
					default:
						break;
				}
			},
			setMainInfo(res) {
				if (res.type == 1 && res.flag) {
					this.getMerchantsAgree();
				}
				
				if (res.type == 2 && res.flag) {
					this.getMerchantsAgreePersonal();
				}
			},
			getMerchantsAgree(){
				uni.showLoading({ title: this.$t('lang.loading') + '...' });
	
				this.$store.dispatch('setMerchantsAgree',{
					fid: this.queryInfo.fid,
					agree: 1,
					contactName: this.queryInfo.uname,
					contactPhone: this.queryInfo.mobile,
					contactXinbie: '',
					contactEmail: '',
					companyName: this.queryInfo.company,
					legal_person_fileImg: this.queryInfo.id_card,
					license_fileImg: this.queryInfo.business_license,
					company_contactTel: this.queryInfo.companyTel,
					huoyuan: ''
				}).then(res=>{
					uni.hideLoading();
					if(res.status == 'success'){
						if(res.data.code == 0){
							const obj = {
								uname: this.queryInfo.uname,
								mobile: this.queryInfo.mobile,
								id_card_one: this.queryInfo.id_card_one,
								id_card_two: this.queryInfo.id_card_two,
								id_card_three: this.queryInfo.id_card_three,
								company: this.queryInfo.company,
								id_card: this.queryInfo.id_card,
								business_license: this.queryInfo.business_license,
								companyTel: this.queryInfo.companyTel
							};
							this.saveMainData(obj);
	
							this.setStep({step: 5})
						}else{
							uni.showToast({ title: res.data.msg, icon: 'none' });
							return false
						}
					}
				})
			},
			getMerchantsAgreePersonal(){
				uni.showLoading({ title: this.$t('lang.loading') + '...' });
	
				this.$store.dispatch('setMerchantsAgreePersonal',{
					fid: this.queryInfo.fid,
					agree: 1,
					name: this.queryInfo.uname,
					mobile: this.queryInfo.mobile,
					id_card: '',
					business_address: '',
					business_category: '',
					id_card_img_one_fileImg: this.queryInfo.id_card_one,
					id_card_img_two_fileImg: this.queryInfo.id_card_two,
					id_card_img_three_fileImg: this.queryInfo.id_card_three,
					commitment_fileImg: ''
				}).then(res=>{
					uni.hideLoading();
					if(res.status == 'success'){
						if(res.data.code == 0){
							const obj = {
								uname: this.queryInfo.uname,
								mobile: this.queryInfo.mobile,
								id_card_one: this.queryInfo.id_card_one,
								id_card_two: this.queryInfo.id_card_two,
								id_card_three: this.queryInfo.id_card_three,
								company: this.queryInfo.company,
								id_card: this.queryInfo.id_card,
								business_license: this.queryInfo.business_license,
								companyTel: this.queryInfo.companyTel
							};
							this.saveMainData(obj);
	
							this.setStep({step: 5})
						}else{
							uni.showToast({ title: res.data.msg, icon: 'none' });
							return false
						}
					}
				})
			},
			saveMainData(obj = {}) {
				const merchantsData = uni.getStorageSync('merchantsData') || {};
				for (const key in obj) {
					if (obj.hasOwnProperty(key)) {
						merchantsData[key] = obj[key]
					}
				}
	
				uni.setStorageSync('merchantsData', merchantsData);
			},
			async addCateById() {
				const allParentCateId = Object.keys(this.queryInfo.allCheckedCate);
				const arr = [];
	
				allParentCateId.forEach(item => {
					if (this.queryInfo.allCheckedCate[item].length > 0) {
						const subArr = [item, this.queryInfo.allCheckedCate[item].join()];
						arr.push(subArr);
					} else {
						const subArr = [item, ''];
						arr.push(subArr);
					}
				})
	
				if (arr.length > 0) {
					uni.showLoading({ title: this.$t('lang.loading') + '...' });
	
					const { data, status } = await  this.$store.dispatch('setMerchantsAddCate',{
						data: arr
					});
	
					if (status != 'success') {
						uni.hideLoading()
						return uni.showToast({ title: this.$t('lang.save_fail'), icon: 'none' });
					}
	
					this.addShop()
				}
			},
			addShop(){
				const allParentCateId = Object.keys(this.queryInfo.allCheckedCate);
	
				this.$store.dispatch('setMerchantsAddShop',{
					data:{
						rz_shop_name: this.queryInfo.shopName,
						hope_login_name: this.queryInfo.stopLoginName,
						shoprz_type: this.queryInfo.storeTypeKey,
						sub_shoprz_type: '',
						shop_category_main: allParentCateId.join()
					}
				}).then(res=>{
					uni.hideLoading()
					
					uni.setStorageSync('merchantInfo', {});
					
					if(res.status == "success"){
						if(res.data.code == 0){
							this.setStep({step: 6});
						} else {
							uni.showToast({ title: res.data.msg, icon: 'none' });
						}
					}else{
						uni.showToast({ title: this.$t('lang.jk_error'), icon: 'none' });
					}
				})
			}
		}
	}
</script>

<style scoped>
.merchants{ padding-bottom: 200upx; overflow: hidden;}
.merchant_bg { width: 100%; }

.footer_box{ padding: 20upx; background-color: #FFFFFF; position: fixed; left: 0; right: 0; bottom: 0; text-align: center;}
.footer_box .checkbox{ display: flex; flex-direction: row; justify-content: center; margin: 0;}
.footer_box .btn-bar{ margin-top: 30upx;}
.footer_box .btn-bar .btn{ height: 80upx; line-height: 80upx; width: 100%;}

.popup-article{position: fixed; left: 0; right: 0; bottom: 0; background: #FFFFFF; height: 80%; padding: 20upx 20upx 0; border-radius: 20upx 20upx 0 0;}
.popup-article .content{ height: calc(100% - 140upx); overflow-y: auto;}
.popup-article .btn-bar{ height: 100upx; padding: 20upx 0; position: fixed; bottom: 0; left: 20upx; right: 20upx;}

.merchants /deep/ .business_scope_pop .uni-popup-right{
	width: 75% !important;
	border-top-left-radius: 30upx;
	border-bottom-left-radius: 30upx;
	overflow: hidden;
}
</style>
