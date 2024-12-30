<template>
    <div class="top-catalog">
        <template v-if="categoryData">
            <div class="category-secondary">
                <div class="item" v-for="(item,index) in categoryData.category" :key="index" @click="linkCate(item.cat_id)">
                    <div class="box"><img :src="item.touch_icon" class="img"></div>
                    <div class="name">{{item.cat_name}}</div>
                </div>
            </div>
            <div class="category-brand">
                <div class="title">{{$t('lang.brand_best_title')}}</div>
                <div class="list">
                    <div class="item" v-for="(item,index) in categoryData.brand" :key="index" @click="linkBrand(item.brand_id)">
                        <div class="box"><img :src="item.brand_logo" class="img"></div>
                        <div class="name">{{item.brand_name}}</div>
                    </div>
                </div>
            </div>
            <section class="product-list product-list-medium">
                <ProductList v-if="cateGoodsList" :data="cateGoodsList" :routerName="routerName"></ProductList>
                <div class="footer-cont" v-if="footerCont">{{$t('lang.no_more')}}</div>
                <template v-if="loading">
                    <van-loading type="spinner" color="black" />
                </template>
            </section>
        </template>
        <van-loading color="black" size="3rem" v-else />
    </div>
</template>

<script>
// mapActions mapState
import {
    mapState
} from 'vuex'

import {
    Loading
} from 'vant'

import ProductList from '@/components/ProductList'
import arrRemove from '@/mixins/arr-remove'

export default{
    name:'top-catalog',
    components: {
        ProductList,
        [Loading.name]:Loading
    },
    data(){
        return {
            categoryData:'',
            disabled:false,
            loading:true,
            page:1,
            size:10,
            footerCont:false,
            routerName:'goods',
        }
    },
    created(){
        this.load();
    },
    mounted(){
        if(this.topCategoryCatid > 0){
            this.$nextTick(() => {
                window.addEventListener('scroll', this.onScroll)
            })
        }
    },
    computed:{
        ...mapState({
            topCategoryCatid: state => state.topCategoryCatid,
        }),
        cateGoodsList:{
            get(){
                return this.$store.state.category.cateGoodsList
            },
            set(val){
                this.$store.state.category.cateGoodsList = val
            }
        },
    },
    methods:{
        linkCate(id){
            this.$router.push({
                name:'list',
                params:{
                    id:id
                }
            })
        },
        linkBrand(id){
            this.$router.push({
                name:'brandDetail',
                params:{
                    id:id
                }
            })
        },
        load(){
            this.loading = true
            this.categoryData = ''
            this.$http.get(`${window.ROOT_URL}api/visual/visual_second_category`,{ params:{
                cat_id:this.topCategoryCatid
            }})
            .then(({ data: { data } }) => {
                this.categoryData = data
                this.loading = false

                this.getGoodsList(1);
            }).catch(err => {
                console.error(err)
            })
        },
        getGoodsList(page){
            if(page){
                this.page = page
                this.size = Number(page) * 10
            }

            this.$store.dispatch('setGoodsList',{
                cat_id:this.topCategoryCatid,
                brand:'',
                warehouse_id:'0',
                area_id:'0',
                min:'',
                max:'',
                filter_attr:'',
                ext:'',
                goods_num:'',
                size:this.size,
                page:this.page,
                sort:'goods_id',
                order:'desc',
                self:'0',
                intro:''
            })
        },
        onScroll(){
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
            let clientHeight = document.documentElement.clientHeight;
            let scrollHeight = document.body.scrollHeight;

            let bili = (scrollHeight-clientHeight-scrollTop)/clientHeight;
            if(bili < 0.03 && this.topCategoryCatid > 0){
                if(this.page * this.size == this.cateGoodsList.length){
                    this.page ++
                    this.getGoodsList()
                }
            }
        }
    },
    destroyed() {
        window.removeEventListener("scroll",  this.onScroll);
    },
    watch:{
        topCategoryCatid(){
            this.load();
        },
        cateGoodsList(){
            if(this.page * this.size == this.cateGoodsList.length){
                this.disabled = false
                this.loading = true
            }else{
                this.loading = false
                //this.footerCont = this.page > 1 ? true : false
            }

            this.cateGoodsList = arrRemove.trimSpace(this.cateGoodsList)
        },
    }
}
</script>

<style scoped>
.top-catalog{ margin: 1rem 0; }
.category-secondary{ background-color: #fff; display: flex; flex-direction: row; flex-wrap: wrap; border-radius: 1rem; padding: 1rem; margin: 0 1rem 1rem;}
.category-secondary .item{ width: 25%; }
.category-brand{ background-color: #fff; border-radius: 1rem; margin:0 1rem;}
.category-brand .title{ font-size: 1.5rem; color: #000; padding: 2rem 2rem 1rem; }
.category-brand .list{ display: flex; flex-direction: row; flex-wrap: wrap; }
.category-brand .list .item{ width: 25%; }
.category-brand .list .item .box{ height: 5.5rem; }

.item{ display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; margin-bottom: 1rem;}
.name{ font-size: 1.2rem; }
.box { display: flex; flex-direction: row; justify-content: center; align-items: center; height: 7.5rem;}
.box .img{ width: 80%; height: auto; }

.product-list{ padding: .4rem .4rem 0; }
</style>
