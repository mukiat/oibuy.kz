<template>
    <div :endTime="endTime" :callback="callback" :endText="endText">
        <slot>
			<ul class="time_wrap" v-if="mini">
				<li class="time_item" v-if="dateInfo.day && showDay">{{dateInfo.day}}</li>
				<li class="time_splitor" v-if="dateInfo.day && showDay">күн</li>
				<li class="time_item" v-if="dateInfo.hour">{{dateInfo.hour}}</li>
				<li class="time_item" v-if="dateInfo.min">{{dateInfo.min}}</li>
				<li class="time_item" v-if="dateInfo.sec">{{dateInfo.sec}}</li>
			</ul>
            <p v-html="content" v-else></p>
        </slot>
    </div>
</template>
<script>
import i18n from '@/locales'
export default {
    data(){
      return {
        content: '',
		dateInfo: {},
		showDay: false
      }
    },
    props:{
        endTime:{
          type:Number,
          default:''
        },
        endText:{
          type:String,
          default: i18n.t('lang.has_ended')
        },
        callback:{
          type:Function,
          default:function(){}
        },
		mini: {
			type: Boolean,
			default: false
		}
    },
    mounted(){
      this.countdowm(this.endTime)
    },
    methods: {
       countdowm(timestamp){
        let self = this;
        let timer = setInterval(function(){
            let nowTime = new Date();
            let endTime = new Date((timestamp+3600*8) * 1000); //加8小时
            let t = endTime.getTime() - nowTime.getTime();
            if(t>0){
                let day = Math.floor(t/86400000);
                let hour= Math.floor((t/3600000)%24);
                let min = Math.floor((t/60000)%60);
                let sec = Math.floor((t/1000)%60);
				self.showDay = day > 0;
                day = day < 10 ? "0" + day : day;
                hour = hour < 10 ? "0" + hour : hour;
                min = min < 10 ? "0" + min : min;
                sec = sec < 10 ? "0" + sec : sec;
				self.dateInfo = {
					day,
					hour,
					min,
					sec
				};
                let format = '';
                if(day >= 0){
                   format =  `<span>${day}</span><i>:</i><span>${hour}</span><i>:</i><span>${min}</span><i>:</i><span>${sec}</span>`;
                }
                if(day <= 0 && hour > 0 ){
                   format = `<span>${hour}</span><i>:</i><span>${min}</span><i>:</i><span>${sec}</span>`;
                }
                if(day <= 0 && hour <= 0){
                   format =`<span>${hour}</span><i>:</i><span>${min}</span><i>:</i><span>${sec}</span>`;
                }
                self.content = format;
                }else{
                  clearInterval(timer);
                  self.content = self.endText;
                  //self._callback();
                }
             },1000);
           },
           _callback(){
           if(this.callback && this.callback instanceof Function){
            this.callback(...this)
            }
        }
    }
}
</script>

<style lang="scss" scoped>
.time_wrap {
	display: flex;
	.time_item {
		height: 2rem;
		line-height: 2rem;
		padding: 0 0.2rem;
		border-radius: 0.4rem;
		margin: 0 0.2rem;
		font-size: 1rem;
		color: #fff;
		background-color: #FF3616;
	}
	.time_splitor {
		height: 2rem;
		line-height: 2rem;
		font-size: 1rem;
		color: #FF3616;
	}
}
</style>
