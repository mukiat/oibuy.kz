<template>
    <div class="auction  groupbuy-cont presale-list" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="200">
		<div class="search-nav">
			<div class="search dis-box">
				<div class="f-04 box-flex">
					<input class="f-03" type="text" v-model="value" :placeholder="$t('lang.activitie_name')" autocomplete="off">
				</div>
				<div class="icon-search-box" @click="onSearch"><i class="iconfont icon-sousuo1 f-05 color-7"/></div>
			</div>
        </div>
        <section class="filter_tab">
			<div class="pro_filter_items dis-box">
				<div class="item" :class="{'active':filter.sort === 'act_id'}" @click="handleFilter('act_id')">
					<span>{{$t('lang.comprehensive')}}</span>
				</div>
				<div class="item" :class="[{'active':filter.sort === 'shop_price','a-change':filter.order === 'ASC' && filter.sort === 'shop_price'}]"
				    @click="handleFilter('shop_price',filter.order)">
					<span>{{$t('lang.price')}}</span>
					<i class="iconfont icon-xiajiantou"></i>
				</div>
				<div class="item" :class="[{'active':filter.sort === 'start_time','a-change':filter.order === 'ASC' && filter.sort === 'start_time'}]"
				    @click="handleFilter('start_time',filter.order)">
					<span>{{$t('lang.new')}}</span>
					<i class="iconfont icon-xiajiantou"></i>
                </div>
			</div>
		</section>
        <div class="tabCon" >
            <div class="goods-li" v-if="presaleListData!=''">
                <router-link :to="{name:'presale-detail',params:{act_id:item.act_id}}" class="show li active" v-for='(item,index) in presaleListData' :key="index" @click="detailClick(item)">
                    <div class="left p-r">
                        <img v-if="item.goods_thumb!=''" class="img" :src="item.goods_thumb" />
                        <img v-else class="img" src="../../../assets/img/not_goods.png" />
                    </div>
                    <div class="right bg-color-write">
                        <h4 class="f-05 color-3 twolist-hidden">{{item.goods_name}}</h4>
                        <div class="cont">
                            <div class="m-top08 dis-box">
                                <div class="box-flex">
                                    <p class="f-06 color-red" v-html="item.shop_price"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <em class="preslae-end" v-if="item.already_over == 1"></em>
                </router-link>
            </div>
            <div v-else>
                <NotCont/>
            </div>
        </div>
        <!--筛选-->
        <van-popup v-model="filtrateShow" position="right">
            <section class="close-filter-div j-close-filter-div">
                <div class="close-f-btn">
                    <i class="iconfont icon-fanhui"></i>
                    <span>{{$t('lang.close')}}</span>
                </div>
            </section>
            {{$t('lang.content')}}
        </van-popup>
        <CommonNav :routerName="routerName">
             <li slot="aloneNav">
                <router-link :to="{name: 'presale'}">
                    <i class="iconfont icon-fenxiao"></i>
                    <p>{{$t('lang.presale_activity')}}</p>
                </router-link>
            </li>
        </CommonNav>
        <template v-if="loading">
            <van-loading type="spinner" color="black" />
        </template>
    </div>
</template>
<script>
	import {mapState } from 'vuex'

    import {
        Search,
        Row,
        Col,
        Popup,
        Waterfall,
        Loading
    } from 'vant'

    import CommonNav from '@/components/CommonNav'
    import NotCont from '@/components/NotCont'
    import arrRemove from '@/mixins/arr-remove'

    export default {
        name: "presale-list",
        components: {
            CommonNav,
            NotCont,
            [Search.name]: Search,
            [Row.name]: Row,
            [Col.name]: Col,
            [Popup.name]: Popup,
            [Loading.name]: Loading,
        },
        directives: {
			WaterfallLower: Waterfall('lower')
		},
        data() {
            return {
                disabled:false,
			    loading:true,
				size:10,
                page:1,
                status:0,
                active: 0,
                value: "", //搜索默认值
				filter: {
					sort: 'act_id',
					order: 'DESC',
				},
                filtrateShow: false,
                cat_id:this.$route.params.cat_id ? this.$route.params.cat_id : 0,
                type:this.$route.query.type ? this.$route.query.type : false,
                routerName:'presale'
            };
        },
        //初始化加载数据
		created() {
           this.presaleList(1)
		},
		computed: {
            presaleListData:{
                get(){
                    return this.$store.state.other.presaleListData
                },
                set(val){
                    this.$store.state.other.presaleListData = val
                }
            }
		},
        methods: {
            onSearch() {
                let value = this.value;
            },
    		handleFilter(val, order) {
				if (order && this.filter.sort == val) {
					this.filter.order = order == 'DESC' ? 'ASC' : 'DESC'
				}
                this.filter.sort = val;

				this.presaleList(1);
			},
            presaleList(page){
                if(page){
                    this.page = page
                    this.size = Number(page) * 10
                }
                let presale = {
                    status:this.status,
					page: this.page,
                    size: this.size,
                    cat_id: this.cat_id,
                    sort:this.filter.sort,
                    order:this.filter.order,
                    keywords:this.value,
				}
                if(this.type){
                    this.$store.dispatch('setPresaleNew',{
                        cat_id: 0,
                        page: this.page,
                        size: this.size,
                        keywords:this.value
                    })
                }else{
				    this.$store.dispatch('setPresaleList', presale)
                }
            },
            onSearch() {
				this.presaleList(1);
			},
            filtrateClick() {
                this.filtrateShow = !this.filtrateShow
            },
            loadMore(){
				setTimeout(() => {
					this.disabled = true
					if(this.page * this.size == this.presaleListData.length){
						this.page ++
                        this.presaleList()
					}
				},200);
			},
        },
        watch:{
			presaleListData(){
				if(this.page * this.size == this.presaleListData.length){
					this.disabled = false
					this.loading = true
				}else{
					this.loading = false
				}

                this.presaleListData = arrRemove.trimSpace(this.presaleListData)
			}
		}
    };
</script>
