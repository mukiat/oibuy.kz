<template>
	<div style="height: 100%;">
		<template v-if="regionType == 'currency'">
			<van-popup v-model="display" position="bottom" @click-overlay="overlay" :close-on-click-overlay="false">
				<div class="mod-address-main">
					<div class="mod-address-head">
						<div class="mod-address-head-tit box-flex">{{$t('lang.region_alt')}}</div>
						<i class="iconfont icon-close" @click="onRegionClose"></i>
					</div>
					<div class="mod-address-body">
						<ul class="ulAddrTab">
							<li :class="{'cur':regionLevel-1 == 1}" @click="tabClickRegion(1,1)">
								<span>{{ regionOption.province.name ? regionOption.province.name : $t('lang.select') }}</span>
							</li>
							<li :class="{'cur':regionLevel-1 == 2}" v-if="regionOption.province.name" @click="tabClickRegion(regionOption.province.id,2)"><span>{{ regionOption.city.name ? regionOption.city.name : $t('lang.select')}}</span></li>
							<li :class="{'cur':regionLevel-1 == 3}" v-if="regionOption.city.name" @click="tabClickRegion(regionOption.city.id,3)"><span>{{ regionOption.district.name ? regionOption.district.name : $t('lang.select')}}</span></li>
							<li :class="{'cur':regionLevel-1 == 4}" v-if="regionOption.district.name && isLevel == 5" @click="tabClickRegion(regionOption.district.id,4)"><span>{{ regionOption.street.name ? regionOption.street.name : $t('lang.select') }}</span></li>
						</ul>
						<ul class="ulAddrList" v-if="regionLevel == 2">
							<li :class="{'active':regionOption.province.id == item.id}" v-for="(item,index) in regionDate.provinceData" :key="index" @click="childRegion(item.id,item.name,item.level)">{{ item.name }}</li>
						</ul>
						<ul class="ulAddrList" v-if="regionLevel == 3">
							<li :class="{'active':regionOption.city.id == item.id}" v-for="(item,index) in regionDate.cityDate" :key="index" @click="childRegion(item.id,item.name,item.level)">{{ item.name }}</li>
						</ul>
						<ul class="ulAddrList" v-if="regionLevel == 4">
							<li :class="{'active':regionOption.district.id == item.id}" v-for="(item,index) in regionDate.districtDate" :key="index" @click="childRegion(item.id,item.name,item.level)">{{ item.name }}</li>
						</ul>
						<ul class="ulAddrList" v-if="regionLevel == 5">
							<li :class="{'active':regionOption.street.id == item.id}" v-for="(item,index) in regionDate.streetDate" :key="index" @click="childRegion(item.id,item.name,item.level)">{{ item.name }}</li>
						</ul>
					</div>
				</div>
			</van-popup>
		</template>
		<template v-if="regionType == 'goods'">
			<div class="mod-address-main mod-address-main-goods">
				<div class="mod-address-body">
					<ul class="ulAddrTab">
						<li :class="{'cur':regionLevel-1 == 1}" @click="tabClickRegion(1,1)">
							<span>{{ regionOption.province.name ? regionOption.province.name : $t('lang.select') }}</span>
						</li>
						<li :class="{'cur':regionLevel-1 == 2}" v-if="regionOption.province.name" @click="tabClickRegion(regionOption.province.id,2)"><span>{{ regionOption.city.name ? regionOption.city.name : $t('lang.select')}}</span></li>
						<li :class="{'cur':regionLevel-1 == 3}" v-if="regionOption.city.name" @click="tabClickRegion(regionOption.city.id,3)"><span>{{ regionOption.district.name ? regionOption.district.name : $t('lang.select')}}</span></li>
						<li :class="{'cur':regionLevel-1 == 4}" v-if="regionOption.district.name && isLevel == 5" @click="tabClickRegion(regionOption.district.id,4)"><span>{{ regionOption.street.name ? regionOption.street.name : $t('lang.select') }}</span></li>
					</ul>
			
					<ul class="ulAddrList" v-if="regionLevel == 2">
						<li :class="{'active':regionOption.province.id == item.id}" v-for="(item,index) in regionDate.provinceData" :key="index" @click="childRegion(item.id,item.name,item.level)">{{ item.name }}</li>
					</ul>
					<ul class="ulAddrList" v-if="regionLevel == 3">
						<li :class="{'active':regionOption.city.id == item.id}" v-for="(item,index) in regionDate.cityDate" :key="index" @click="childRegion(item.id,item.name,item.level)">{{ item.name }}</li>
					</ul>
					<ul class="ulAddrList" v-if="regionLevel == 4">
						<li :class="{'active':regionOption.district.id == item.id}" v-for="(item,index) in regionDate.districtDate" :key="index" @click="childRegion(item.id,item.name,item.level)">{{ item.name }}</li>
					</ul>
					<ul class="ulAddrList" v-if="regionLevel == 5">
						<li :class="{'active':regionOption.street.id == item.id}" v-for="(item,index) in regionDate.streetDate" :key="index" @click="childRegion(item.id,item.name,item.level)">{{ item.name }}</li>
					</ul>
				</div>
			</div>
		</template>
	</div>
</template>

<script>
import { mapState } from 'vuex'

import {
  Popup
} from 'vant'

export default{
	props:{
		display:{
			type:Boolean,
			default:false
		},
		regionOptionDate:{
			type:Object,
			default:''
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
	data(){
		return{
			regionOption: this.regionOptionDate,
			arr:['province','city','district','street'],
			lat:'',
			lng:''
		}
	},
	components:{
		[Popup.name] : Popup
	},
	created(){
		let o = { region:1, level:1 };

		if(this.regionOption.district.id == this.regionId) return 

		if(this.isLevel == 5 && this.regionOption.district.id){
			o.region = this.regionOption.district.id;
			o.level = this.isLevel-1;
		}
		console.log(this.regionOption.district.id)
		this.$store.dispatch('setRegion', o);
	},
	computed:{
		regionId(){
			return this.$store.state.region.id
		},
		regionLevel(){
			return this.isLevel > this.$store.state.region.level ? this.$store.state.region.level : this.isLevel
		},
		regionDate(){
			return this.$store.state.region.data
		},
		status:{
			get(){
				return this.$store.state.region.status
			},
			set(val){
				this.$store.state.region.status = val
			}
		},
 		userRegion(){
 			return this.$store.state.userRegion
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
		overlay(){
			this.$emit('updateDisplay',false)
		},
		//查看地图定位
	    async locationMap(address){
	    	this.$http.get(`${window.ROOT_URL}/api/misc/address2location`,{ params:{
	    		address:address.replace(/\s*/g,"")
	    	}}).then(({data})=>{
	    		if(data.status == 'success'){
	    			let location = data.data
	    			let o = {
	    				lat:location.lat,
	    				lng:location.lng
	    			}
	    			
					this.regionOption.postion = o;

					if(this.isStorage){
						localStorage.setItem('regionOption',JSON.stringify(this.regionOption))
					}

					this.$emit('updateRegionDate',this.regionOption);
					this.$emit('updateDisplay',false);
					this.$emit('update:isPrice',1);
	    		}else{
	    			Toast(data.message);
	    		}
	    	})
	    }
	},
	watch:{
		status(){
			if(this.status == true){
				this.regionOption.regionSplic = this.regionOption.province.name +' '+ this.regionOption.city.name +' '+ this.regionOption.district.name +' '+ this.regionOption.street.name;

				this.locationMap(this.regionOption.regionSplic);
			}
		}
	}
}
</script>

<style lang="scss" scoped>
.mod-address-main {
	height: inherit;
	.mod-address-body {
		display: flex;
		flex-direction: column;
		height: inherit;
		.ulAddrTab {
			&:after {
				background-color: transparent;
			}
			li.cur {
				span {
					font-weight: 700;
					color: #000;
					&:after {
						content: '';
						position: absolute;
						left: 50%;
						bottom: 0;
						transform: translateX(-50%);
						width: 80%;
						height: 0.3rem;
						background: linear-gradient(90deg, #F91F28 0%, rgba(255, 79, 46, 0.35) 100%);
					}
				}
			}
		}
		.ulAddrList {
			flex: auto;
		}
	}
}
.mod-address-main-goods{
	.ulAddrList {
		flex: auto;
		height: inherit;
	}
}
</style>