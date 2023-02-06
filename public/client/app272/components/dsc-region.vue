<template>
	<view class="region-popup">
		<block v-if="regionType == 'currency'">
		<uni-popup :show="display" type="bottom" v-on:hidePopup="onRegionClose">
			<view class="mod-address-main">
				<view class="mod-address-head">
					<view class="mod-address-head-tit">{{$t('lang.region_alt')}}</view>
					<text class="iconfont icon-close" @click="onRegionClose"></text>
				</view>
			</view>
			<view class="mod-address-body">
				<view class="ulAddrTab">
					<text class="li" :class="{'cur':regionLevel-1 == 1}" @click="tabClickRegion(1,1)">
						{{ regionOption.province.name ? regionOption.province.name : $t('lang.select') }}
					</text>
					<text class="li" :class="{'cur':regionLevel-1 == 2}" v-if="regionOption.province.name" @click="tabClickRegion(regionOption.province.id,2)">{{ regionOption.city.name ? regionOption.city.name : $t('lang.select')}}</text>
					<text class="li" :class="{'cur':regionLevel-1 == 3}" v-if="regionOption.city.name" @click="tabClickRegion(regionOption.city.id,3)">{{ regionOption.district.name ? regionOption.district.name : $t('lang.select')}}</text>
					<text class="li" :class="{'cur':regionLevel-1 == 4}" v-if="regionOption.district.name && isLevel == 5" @click="tabClickRegion(regionOption.district.id,4)">{{ regionOption.street.name ? regionOption.street.name : $t('lang.select') }}</text>
				</view>
				
				<scroll-view class="ulAddrList" scroll-y v-if="regionLevel == 2">
					<view class="li" :class="{'active':regionOption.province.id == item.id}" v-for="(item,index) in regionDate.provinceData" :key="index" @click="childRegion(item.id,item.name,item.level)">{{ item.name }}</view>
				</scroll-view>
				<scroll-view class="ulAddrList" scroll-y v-if="regionLevel == 3">
					<view class="li" :class="{'active':regionOption.city.id == item.id}" v-for="(item,index) in regionDate.cityData" :key="index" @click="childRegion(item.id,item.name,item.level)">{{ item.name }}</view>
				</scroll-view>
				<scroll-view class="ulAddrList" scroll-y v-if="regionLevel == 4">
					<view class="li" :class="{'active':regionOption.district.id == item.id}" v-for="(item,index) in regionDate.districtData" :key="index" @click="childRegion(item.id,item.name,item.level)">{{ item.name }}</view>
				</scroll-view>
				<scroll-view class="ulAddrList" scroll-y v-if="regionLevel == 5">
					<view class="li" :class="{'active':regionOption.street.id == item.id}" v-for="(item,index) in regionDate.streetData" :key="index" @click="childRegion(item.id,item.name,item.level)">{{ item.name }}</view>
				</scroll-view>
			</view>
		</uni-popup>
		</block>
		<block v-if="regionType == 'goods'">
			<view class="mod-address-body">
				<view class="ulAddrTab">
					<text class="li" :class="{'cur':regionLevel-1 == 1}" @click="tabClickRegion(1,1)">
						{{ regionOption.province.name ? regionOption.province.name : $t('lang.select') }}
					</text>
					<text class="li" :class="{'cur':regionLevel-1 == 2}" v-if="regionOption.province.name" @click="tabClickRegion(regionOption.province.id,2)">{{ regionOption.city.name ? regionOption.city.name : $t('lang.select')}}</text>
					<text class="li" :class="{'cur':regionLevel-1 == 3}" v-if="regionOption.city.name" @click="tabClickRegion(regionOption.city.id,3)">{{ regionOption.district.name ? regionOption.district.name : $t('lang.select')}}</text>
					<text class="li" :class="{'cur':regionLevel-1 == 4}" v-if="regionOption.district.name && isLevel == 5" @click="tabClickRegion(regionOption.district.id,4)">{{ regionOption.street.name ? regionOption.street.name : $t('lang.select') }}</text>
				</view>
				
				<scroll-view class="ulAddrList" scroll-y v-if="regionLevel == 2">
					<view class="li" :class="{'active':regionOption.province.id == item.id}" v-for="(item,index) in regionDate.provinceData" :key="index" @click="childRegion(item.id,item.name,item.level)">{{ item.name }}</view>
				</scroll-view>
				<scroll-view class="ulAddrList" scroll-y v-if="regionLevel == 3">
					<view class="li" :class="{'active':regionOption.city.id == item.id}" v-for="(item,index) in regionDate.cityData" :key="index" @click="childRegion(item.id,item.name,item.level)">{{ item.name }}</view>
				</scroll-view>
				<scroll-view class="ulAddrList" scroll-y v-if="regionLevel == 4">
					<view class="li" :class="{'active':regionOption.district.id == item.id}" v-for="(item,index) in regionDate.districtData" :key="index" @click="childRegion(item.id,item.name,item.level)">{{ item.name }}</view>
				</scroll-view>
				<scroll-view class="ulAddrList" scroll-y v-if="regionLevel == 5">
					<view class="li" :class="{'active':regionOption.street.id == item.id}" v-for="(item,index) in regionDate.streetData" :key="index" @click="childRegion(item.id,item.name,item.level)">{{ item.name }}</view>
				</scroll-view>
			</view>
		</block>
	</view>
</template>

<script>
	//uniapp新版弹出层组件
	import uniPopup from '@/components/uni-popup.vue';
	export default {
		name:'region',
		components:{
			uniPopup
		},
		props: {
			display:{
				type:Boolean,
				default:false
			},
			regionOptionData:{
				type:Object,
				default:{}
			},
			isPrice:{
				type:Number,
				default:0
			},
			isLevel:{
				type:Number,
				default:5
			},
			//是否存储选中值到本地存储
			isStorage:{
				type:Boolean,
				default:true
			},
			//弹窗
			regionType:{
				type:String,
				default:'currency'
			}
		},
		watch:{
			status(){
				if(this.status == true){
					this.regionOption.regionSplic = this.regionOption.province.name +' '+ this.regionOption.city.name +' '+ this.regionOption.district.name +' '+ this.regionOption.street.name;

					if(this.isStorage){
						uni.setStorageSync('regionData',this.regionOption)
					}

					this.$emit('updateRegionDate',this.regionOption);
					this.$emit('updateDisplay',false);
					this.$emit('update:isPrice',1);
				}
			},
			regionOptionData(){
				this.regionOption = this.regionOptionData
				if(this.regionOptionData.street.id === ''){
					this.$store.dispatch('setRegion', {
						region: this.regionOptionData.district.id,
						level:4
					});
				}
			}
		},
		data() {
			return {
				regionOption: this.regionOptionData,
				arr:['province','city','district','street'],
			};
		},
		created(){
			let o = { region:1, level:1 };

			if(this.regionOption.district.id == this.regionId) return
			
			if(this.isLevel == 5 && this.regionOption.district.id){
				o.region = this.regionOption.district.id;
				o.level = this.isLevel-1;
			}

			this.$store.dispatch('setRegion', o);
		},
		computed:{
			regionId(){
				return this.$store.state.common.region.id
			},
			regionLevel(){
				return this.isLevel > this.$store.state.common.region.level ? this.$store.state.common.region.level : this.isLevel
			},
			regionDate(){
				return this.$store.state.common.region.data
			},
			status:{
				get(){
					return this.$store.state.common.region.status
				},
				set(val){
					this.$store.state.common.region.status = val
				}
			}
		},
		methods:{
			onRegionClose(){
				this.$emit('updateDisplay',false)
			},
			childRegion(val,name,level){
				if(this.isLevel == level){
					this.status = true
				}else{
					this.status = false
				}

				switch(level){
					case 2:
						this.regionOption.province.id = val
						this.regionOption.province.name = name
						break
					case 3:
						this.regionOption.city.id = val
						this.regionOption.city.name = name
						break
					case 4:
						this.regionOption.district.id = val
						this.regionOption.district.name = name
						break
					case 5:
						this.regionOption.street.id = val
						this.regionOption.street.name = name
						break
					default:
						break
				}

				this.arr.forEach((v,i)=>{
					if((i+1) > level){
						this.regionOption[v].id = ''
						this.regionOption[v].name = ''
					}
				})

				this.$store.dispatch('setRegion',{
					region:val,
					level:level
				})
			},
			tabClickRegion(val,level){
				this.arr.forEach((v,i)=>{
					if((i+1) > level){
						this.regionOption[v].id = ''
						this.regionOption[v].name = ''
					}
				})
				this.$store.dispatch('setRegion',{
					region:val,
					level:level
				})
			},
		}
	}
</script>

<style scoped>
.uni-card{ margin: 0;}
.uni-card .uni-list-cell-navigate{ padding: 0;}
.uni-card .uni-list-cell-navigate .title{ padding: 20upx 30upx; min-width: 100upx;}
.uni-card .uni-list-cell-navigate .value{ height: 80upx;}
.btn-bar{ margin: 30upx 40upx;}

.mod-address-main{ }
.mod-address-main .mod-address-head{ position: relative;}
.mod-address-main .mod-address-head .mod-address-head-tit{ height: 80upx; line-height: 80upx; }
.mod-address-main .mod-address-head .icon-close{ position: absolute; right: 20upx; top: 20upx;}

.mod-address-body{ height: 800upx;}
.mod-address-body .ulAddrTab{ overflow: hidden; position: relative; padding: 0 20upx; height: 80upx; line-height: 80upx;}
.mod-address-body .ulAddrTab::after{ content: ""; position: absolute; z-index: 1; background-color: #E5E5E5; height: 1px; left: 0; right: 0; bottom: 0; }
.mod-address-body .ulAddrTab .li{ margin-right: 20upx; float: left; text-align: center; height: 80upx; line-height: 80upx; position: relative;}
.mod-address-body .ulAddrTab .li.cur{ color: #F2270C;}
.mod-address-body .ulAddrTab .li.cur::after{ content: ""; position: absolute; height: 1px; background-color: #F2270C; left: 0; bottom: 1px; right: 0;}
.mod-address-body .ulAddrList{ height: calc(100% - 80upx); padding: 20upx 0 0 20upx; box-sizing: border-box;}
.mod-address-body .ulAddrList .li{ text-align: left; color: #333333; padding: 10upx 0; position: relative;}
.mod-address-body .ulAddrList .li.active{ color: #F2270C;}
.mod-address-body .ulAddrList .li.active::before{ font-family: "iconfont";content: "\e61a";position: absolute;right: 20upx; font-size: 18px;}
</style>
