<template>
  <div class="user-evaluation comment_content" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
    <div class="product-list">
    	<div class="tabs">
    		<div :class="['tab_item', activeTab == 0 ? 'active_tab' : '']" @click="switchTab(0)">待评价<span class="await_count">{{ signNum && activeTab == 0 ? ' · ' + signNum : '' }}</span></div>
    		<div :class="['tab_item', activeTab == 1 ? 'active_tab' : '']" @click="switchTab(1)">已评价{{showEvaluate ? '/追评' : ''}}</div>
    	</div>
    	<template v-if="activeTab == 0">
    		<div :class="['product_wrap', index > 0 ? 'u-border-top' : '']" v-for="(item,index) in commentList" :key="index" @click="$router.push({ name: 'goods', params: { id: item.goods_id }})">
    			<div class="shop_name">{{item.shop_name}}</div>
    			<div class="product_item">
    				<img class="goods_img" :src="item.goods_thumb" v-if="item.goods_thumb" />
    				<img class="goods_img" src="../../../assets/img/no_image.jpg" v-else>
    				<div class="goods_name">
    					<p class="text_2">{{ item.goods_name }}</p>
    					<div class="btns" v-if="item.can_evaluate">
    						<div class="evaluate_btn" @click.stop="$router.push({name:'commentDetail',params:{id:item.rec_id, type: 0}})">评价</div>
    					</div>
    				</div>
    			</div>
    		</div>
    		<template v-if="commentList.length == 0 && !loading">
    		    <NotCont />
    		</template>
    	</template>
    	<template v-if="activeTab == 1">
    		<div :class="['product_wrap', 'have_evaluation', index == 0 ? 'border_top_0' : '']" v-for="(item,index) in commentList" :key="index" @click="$router.push({ name: 'goods', params: { id: item.goods_id }})">
    			<div class="shop_name">{{item.shop_name}}</div>
    			<div class="product_item">
    				<img class="goods_img" :src="item.goods_thumb" v-if="item.goods_thumb" />
    				<img class="goods_img" src="../../../assets/img/no_image.jpg" v-else>
    				<div class="goods_name">
    					<p class="text_1">{{ item.goods_name }}</p>
    					<div class="rate_wrap">
    						<div class="rate">
    							<span>评分</span>
    							<i :class="['iconfont', 'icon-wujiaoxing', 'size_12', rIndex < item.comment_rank ? 'color_red' : '']" v-for="(rate, rIndex) in 5" :key="rIndex"></i>
    						</div>
    					</div>
    				</div>
    			</div>
    			
    			<div v-for="(comItem,comIndex) in item.comment" :key="comIndex">
    				<div class="pro_content" v-if="comItem.add_comment_id == 0">
    					<p class="no_comment">{{comItem.content}}</p>
    					<div class="img_list" v-if="comItem.comment_img_list.length">
    						<img class="goods_img" v-for="(imgItem,imgIndex) in comItem.comment_img_list" :key="imgIndex" :src="imgItem.comment_img" />
    					</div>
    					<div class="btn_wrap" v-if="item.can_add_evaluate">
    						<div class="additional_review" @click.stop="$router.push({name:'commentDetail',params:{id:item.rec_id, type: 1}})">追评</div>
    					</div>
    				</div>
    				<div class="pro_content u-border-top additional_review_content" v-if="comItem.add_comment_id > 0">
    					<div class="title">追评</div>
    					<p class="no_comment">{{comItem.content}}</p>
    					<div class="img_list" v-if="comItem.comment_img_list.length">
    						<img class="goods_img" v-for="(imgItem,imgIndex) in comItem.comment_img_list" :key="imgIndex" :src="imgItem.comment_img" />
    					</div>
    				</div>
    			</div>
    		</div>
    		<template v-if="commentList.length == 0 && !loading">
    		    <NotCont />
    		</template>
    	</template>
    </div>
    <template v-if="loading">
    	<van-loading type="spinner" color="black" />
    </template>
    
    <CommonNav></CommonNav>
  </div>
</template>

<script>
import qs from 'qs'
import { mapState } from 'vuex'
import {
    Waterfall,
    Loading
} from 'vant'
import NotCont from '@/components/NotCont'
import CommonNav from '@/components/CommonNav'
import arrRemove from '@/mixins/arr-remove'

export default{
	data(){
		return {
            apart:'comment',
            sign:0,
			signNum: '',
            page:1,
            size:10,
            loading:true,
			activeTab: this.$route.query.have || 0,
			showEvaluate: 0
        }
	},
    directives: {
        WaterfallLower: Waterfall('lower')
    },
    components:{
        [Loading.name] : Loading,
        NotCont,
        CommonNav
    },
    created(){
        this.commentListHandle()
    },
    computed:{
        commentList:{
            get(){
                return this.$store.state.user.commentList
            },
            set(val){
                this.$store.state.user.commentList = val  
            }
        }
    },
    methods:{
		// 切换tab
		switchTab(i) {
			if (this.activeTab == i) return;
			this.page = 1;
			this.activeTab = i;
		},
        async commentListHandle(page){
			const { data: { data: { signNum0, add_evaluate } }, status } = await this.$http.post(`${window.ROOT_URL}api/comment/order_goods_title`, qs.stringify({
	    		id:this.$route.query.id
	    	}));
			this.signNum = signNum0 || ''
			this.showEvaluate = add_evaluate || 0
			
            if(page){
                this.page = page
                this.size = Number(page) * 10
            }

            this.$store.dispatch('setCommentList',{
                sign:this.activeTab,
                page:this.page,
                size:this.size,
                id:this.$route.query.id
            })
			
        },
        //瀑布流加载分页
        loadMore(){
            setTimeout(() => {
                this.disabled = true
                if(this.page * this.size == this.commentList.length){
                    this.page ++
                    this.commentListHandle() 
                }
            },200);
        }
    },
    watch:{
        commentList(){
            if(this.page * this.size == this.commentList.length){
                this.disabled = false
                this.loading = true
            }else{
                this.loading = false
            }

            this.commentList = arrRemove.trimSpace(this.commentList)
        },
		activeTab: 'commentListHandle'
    }
}
</script>

<style lang="scss" scoped>
.comment_content {
	padding-top: 1.2rem;
	.tabs {
		display: flex;
		align-items: center;
		justify-content: space-around;
		height: 5rem;
		border-top-left-radius: 1rem;
		border-top-right-radius: 1rem;
		background-color: #fff;
		.tab_item {
			position: relative;
			font-size: 1.5rem;
			.await_count {
				position: absolute;
				right: -2rem;
				top: 50%;
				transform: translateY(-50%);
			}
		}
		.active_tab {
			font-weight: 700;
			&:after {
				content: '';
				position: absolute;
				left: 50%;
				bottom: -0.6rem;
				transform: translateX(-50%);
				width: 100%;
				height: 0.3rem;
				background: linear-gradient(90deg, #F91F28 0%, rgba(255, 79, 46, 0.35) 100%);
			}
		}
	}
	.product_wrap {
		padding: 1rem 0 2rem; 
		background-color: #fff;
		.shop_name {
			margin-bottom: 1rem;
			padding: 0 1rem;
			font-size: 1.5rem;
			font-weight: bold;
		}
	}
	.product_item {
		display: flex;
		padding: 0 1rem;
		.goods_img {
			width: 9rem;
			height: 9rem;
		}
		.goods_name {
			overflow: hidden;
			flex: auto;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			margin-left: 1rem;
			.btns {
				display: flex;
				justify-content: flex-end;
			}
			.evaluate_btn {
				padding: 0 2rem;
				height: 3rem;
				line-height: 3rem;
				border-radius: 1.5rem;
				text-align: center;
				font-size: 1.5rem;
				color: #f92028;
				border: 1px solid #f92028;
			}
			.rate_wrap {
				flex: auto;
				.rate {
					display: flex;
					align-items: center;
					height: 2rem;
					line-height: 2rem;
					font-size: 1.2rem;
					.icon-wujiaoxing {
						transform: translateY(-0.2rem);
						margin-left: 0.5rem;
						color: #DDD;
					}
					.color_red {
						color: #E93B3D;
					}
				}
			}
		}
	}
	.have_evaluation {
		margin-bottom: 1rem;
		border-radius: 1rem;
		.goods_img {
			width: 6rem;
			height: 6rem;
		}
		.btn_wrap {
			display: flex;
			justify-content: flex-end;
			margin-top: 1rem;
			.additional_review {
				padding: 0 2rem;
				height: 3rem;
				line-height: 3rem;
				border-radius: 1.5rem;
				text-align: center;
				font-size: 1.5rem;
				border: 1px solid #ccc;
			}
		}
		.additional_review_content {
			margin-top: 1.2rem;
		}
		.title {
			font-weight: bold;
			line-height: 1;
			padding-top: 1.2rem;
		}
		.pro_content {
			padding: 0 1rem;
		}
		.no_comment {
			margin-top: 0.8rem;
		}
		.img_list {
			display: flex;
			flex-wrap: wrap;
			.goods_img {
				width: 6.2rem;
				height: 6.2rem;
				margin: 0.8rem 0.8rem 0 0;
				border-radius: 0.5rem;
			}
		}
	}
	.border_top_0 {
		border-top-left-radius: 0;
		border-top-right-radius: 0;
	}
}
</style>