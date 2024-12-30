<template>
	<div class="sus-nav">
		<div class="common-nav" :class="{'active' : navType === true}" @touchstart="down" @touchmove="move" @touchend="end" id="moveDiv">
			<div class="right-cont" id="rightDiv">
				<ul>
					<li @click="routerLink('home')">
						<i class="iconfont icon-zhuye"></i>
						<p>{{$t('lang.home')}}</p>
					</li>
					<li @click="routerLink('search')" v-if="routerName!='drp' && routerName !='crowd_funding' && routerName !='team' && routerName != 'supplier' && routerName != 'presale'">
						<i class="iconfont icon-search"></i>
						<p>{{$t('lang.search')}}</p>
					</li>
					<li @click="routerLink('catalog')">
						<i class="iconfont icon-menu"></i>
						<p>{{$t('lang.category')}}</p>
					</li>
					<li @click="routerLink('cart')">
						<i class="iconfont icon-cart"></i>
						<p>{{$t('lang.cart')}}</p>
					</li>
					<li @click="routerLink('user')">
						<i class="iconfont icon-gerenzhongxin"></i>
						<p>{{$t('lang.personal_center')}}</p>
					</li>
					<li @click="routerLink('team')" v-if="routerName=='team'">
						<i class="iconfont icon-wodetuandui"></i>
						<p>{{$t('lang.my_team')}}</p>
					</li>
					<li @click="routerLink('supplier')" v-if="routerName=='supplier'">
						<i class="iconfont icon-wodetuandui"></i>
						<p>{{$t('lang.suppliers')}}</p>
					</li>
					<slot name="aloneNav"></slot>
				</ul>
			</div>
			<div class="nav-icon" @click="handelNav">{{$t('lang.quick_navigation')}}</div>
		</div>
		<div class="common-show" :class="{'active' : navType === true}" @click.stop="handelShow"></div>
	</div>
</template>
<script>
export default{
	props:['routerName'],
	data(){
		return {
			navType:false,
			flags: false,
		    position: { x: 0, y: 0 },
		    nx: '', ny: '', dx: '', dy: '', xPum: '', yPum: '',
		}
	},
	mounted(){
		this.flags = false
	},
	methods:{
		handelNav(){
			this.navType = this.navType == true ? false : true
		},
		handelShow(){
			this.navType = false
		},
		// 实现移动端拖拽
		down(event){
			this.flags = true
			var touch

			if(event.touches){
				touch = event.touches[0]
			}else {
				touch = event
			}

			this.position.x = touch.clientX
			this.position.y = touch.clientY

			this.dx = moveDiv.offsetLeft
			this.dy = moveDiv.offsetTop
		},
		move(event){
			event.preventDefault();
			if(this.flags){
				var touch,wheight,objHeight,totalHeight

				if(event.touches){
					touch = event.touches[0]
				}else {
					touch = event
				}

				wheight = document.documentElement.clientHeight	//页面容器高度
				objHeight = moveDiv.clientHeight				//容器高度

				this.nx = touch.clientX - this.position.x 		//拖动x长度
				this.ny = touch.clientY - this.position.y       //拖动y长度
				this.xPum = this.dx + this.nx 					//距离左侧距离
				this.yPum = this.dy + this.ny                   //距离顶部距离

				if(this.navType){
					if(this.yPum > 0){
						if((wheight - objHeight - this.yPum)  > 0){
							totalHeight = wheight - objHeight - this.yPum
						}else{
							totalHeight = 0
						}
					}
				}else{
					objHeight += rightDiv.clientHeight
					if((this.yPum - objHeight) > 0){
						if((wheight - this.yPum)  > 0){
							totalHeight = wheight - this.yPum
						}else{
							totalHeight = 0
						}
					}
				}

				//moveDiv.style.left = this.xPum+"px"
				moveDiv.style.bottom = totalHeight +"px"
			}
		},
		end(){
			this.flags = false
		},
		routerLink(val){
			let that = this
			if(val == 'home' || val == 'catalog' || val == 'search' || val == 'user'){
                setTimeout(() => {
                    uni.getEnv(function(res){
                        if(res.plus || res.miniprogram){
                        	if(val == 'home'){
                                uni.reLaunch({  
                                    url: '../../pages/index/index'
                                })
                            }else if(val == 'catalog'){
                                uni.reLaunch({  
                                    url: '../../pages/category/category'
                                })
                            }else if(val == 'search'){
                                uni.reLaunch({  
                                    url: '../../pages/search/search'
                                })
                            }else if(val == 'user'){
                                uni.reLaunch({  
                                    url: '../../pages/user/user'
                                })
                            }
                        }else{
                        	if(val == 'search'){
                        		that.$router.push({
	                                name:'search'
	                            })
                        	}else{
	                        	that.$router.push({
	                                name:val
	                            })
                            }
                        }
                    })
                },100)
            }else{
                that.$router.push({
                    name:val
                })
            }
		}
	}
}
</script>
<style lang="scss">
@import '@/assets/style/config.scss';
@import '@/assets/style/mixins/common.scss';

* { 
	touch-action: pan-y 
}

.common-nav{
	position: fixed;
	width: 4.5rem;
	z-index: 1999;	
	border-radius: 2.5rem;
	right: 1rem;
	bottom: 20%;
	height: 4.5rem;
	overflow: hidden;
	background: rgba(255,255,255,.9);
	@include transition(all,.5s);

	&.active{
		height: 28.25rem;
	}

	.nav-icon{
		width: 4.5rem;
		height: 4.5rem;
		border-radius: 50%;
		background-color: #f92028;
		color: #fff;
	    padding: 1.2rem 1rem;
	    font-size: 1.2rem;
	    display: flex;
	    @include direction(center, center);
	    position: absolute;
	    bottom: 0;
	    text-align: center;
	}

	.right-cont{
		border-radius: .5rem 0 0 .5rem;
		width: 4.5rem;
		display: flex;
		@include direction(center, center);

		ul{
			overflow: hidden;
		}

		li{
			float: left;
			text-align: center;
			width: 100%;
			height: 4.5rem;
			display: flex;
			justify-content: center;
			align-items: center;
			flex-direction: column;

			i{
				font-size: 1.5rem;
				color: #666;
				font-weight: 600;
			}

			p{
				font-size: 1rem;
				color: #666;
				margin-top: .1rem;
				text-overflow: ellipsis;
				overflow: hidden;
				white-space: nowrap;
			}

			&:first-child{
				padding-top: 1rem;
				box-sizing: initial;
			}

			&:last-child{
				padding-bottom: .25rem;
			}
		}
	}
}
.common-show{
	position: fixed;
	width: 100%;
	height: 100%;
	left: 100%;
	right: 0;
	top: 0;
	bottom: 0;
	z-index: 1998;
	background: rgba(0,0,0,.4);
	display: none;

	&.active{
		display: block;
		left: 0;
	}
}
</style>
