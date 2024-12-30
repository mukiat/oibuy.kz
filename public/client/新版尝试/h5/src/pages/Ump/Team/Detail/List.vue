<template>
    <div class="con" v-waterfall-lower="loadMore" waterfall-disabled="disabled" waterfall-offset="300">
        <!-- <Search :isFilter='isFilter' v-on:getViewSwitch="handleViewSwitch"></Search> -->
        <FilterTab :filter="filter" :isPopupVisible="isPopupVisible" v-on:getFilter='handleFilter' @setPopupVisible="setPopupVisible"></FilterTab>
        <section class="product-list" :class="{'product-list-medium':filter.mode !== 'medium'}">
            <ProductList v-if="teamListData" :data="teamListData" :routerName="routerName"></ProductList>
        </section>
        <van-popup class="con-filter-warp" v-model="isPopupVisible" position="right">
            <div class="con-filter-div">
                <swiper :options="swiperOption">
                    <swiper-slide>
                        <ul class="mod_list">
                            <li class="radio-switching">
                                <div class="super_li">
                                    <div class="li_line">
                                        <div class="big">{{$t('lang.self_support_product')}}</div>
                                        <div class="right">
                                            <van-switch v-model="checked" size="23px" />
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <ul class="tags_selection">
                            <li :class="{'active':filter.tags.hasgoods == 1}" @click="handleTags('hasgoods')">
                                <label>{{$t('lang.just_look_stock')}}</label>
                            </li>
                            <li :class="{'active':filter.tags.promotion == 1}" @click="handleTags('promotion')">
                                <label>{{$t('lang.promotion')}}</label>
                            </li>
                        </ul>
                        <ul class="mod_list">
                            <li class="super_li">
                                <div class="li_line">
                                    <div class="big">{{$t('lang.price_range')}}</div>
                                </div>
                            </li>
                            <li class="filterlayer_price">
                                <div class="filterlayer_price_area">
                                    <input type="tel" class="filterlayer_price_area_input" placeholder="min" autocomplete="off" v-model="filter.min" />
                                    <span class="filterlayer_price_hang"></span>
                                    <input type="tel" class="filterlayer_price_area_input" placeholder="max" autocomplete="off" v-model="filter.max" />
                                </div>
                            </li>
                        </ul>
                        <van-cell-group>
                            <van-cell :title="$t('lang.brand')" @click="selectBrand" is-link>
                                <div class="">111</div>
                            </van-cell>
                        </van-cell-group>
                    </swiper-slide>
                </swiper>
            </div>
            <div class="filterlayer_bottom_buttons">
                <span class="filterlayer_bottom_button bg_1">{{$t('lang.empty_options')}}</span>
                <span class="filterlayer_bottom_button bg_2">{{$t('lang.confirm')}}</span>
            </div>
        </van-popup>
    </div>
</template>

<script>
    import { mapState } from 'vuex'

    import {
        List,
        Popup,
        Switch,
        Cell,
        CellGroup,
        Checkbox,
        CheckboxGroup,
        Waterfall
    } from 'vant'

    import {
        swiper,
        swiperSlide
    } from 'vue-awesome-swiper'

    import Search from '@/components/Search'
    import FilterTab from '../components/filter/FilterTab'
    import ProductList from '@/components/ProductList'
    import arrRemove from '@/mixins/arr-remove'

    export default {
        data() {
            return {
                routerName: 'team-detail',
                loading: false,
                finished: false,
                checked: true,
                filter: {
                    mode: 'medium',
                    sort: '0',
                    order: 'DESC',
                    tags: {
                        hasgoods: 0,
                        promotion: 0,
                    },
                },
                isFilter: true,
                isPopupVisible: false,
                isPopupBrand: false,
                swiperOption: {
                    direction: 'vertical',
                    slidesPerView: 'auto',
                    freeMode: true
                },
                result: [],
                page:1,
                size:10,
                disabled:false,
            }
        },
        directives: {
            WaterfallLower: Waterfall('lower')
        },
        components: {
            Search,
            FilterTab,
            ProductList,
            swiper,
            swiperSlide,
            [List.name]: List,
            [Popup.name]: Popup,
            [Switch.name]: Switch,
            [Cell.name]: Cell,
            [CellGroup.name]: CellGroup,
            [Checkbox.name]: Checkbox,
            [CheckboxGroup.name]: CheckboxGroup
        },
        created() {
            this.getGoodsList(1)
        },
        computed: {
            teamListData:{
                get(){
                    return this.$store.state.team.teamListData
                },
                set(val){
                    this.$store.state.team.teamListData = val
                }
            },
            tagsList() {
                return this.filter.tags
            }
        },
        methods: {
            getGoodsList(page) {
                if(page){
                    this.page = page
                    this.size = Number(page) * 10
                }
                this.$store.dispatch('setTeamList',{
                    tc_id: this.$route.query.id || '',
					brand_id: this.$route.query.brand_id || '',
                    keyword: '',
                    sort_key: this.filter.sort,
                    sort_value: this.filter.order,
                    size: this.size,
                    page: this.page,
                });
            },
            onLoad() {
            },
            handleViewSwitch(val) {
                this.filter.mode = val;
            },
            handleFilter(o) {
                this.filter.sort = o.sort;
                this.filter.order = o.order;

                this.getGoodsList();
            },
            setPopupVisible(val) {
                this.isPopupVisible = val;
            },
            selectBrand() {
                this.isPopupBrand = this.isPopupBrand == false ? true : false;
            },
            handleTags(val) {
                if (val == 'hasgoods') {
                    this.filter.tags.hasgoods = this.filter.tags.hasgoods == 0 ? 1 : 0;
                } else {
                    this.filter.tags.promotion = this.filter.tags.promotion == 0 ? 1 : 0;
                }
            },
            cancelSelectBrand() {
                this.isPopupBrand = false;
            },
            confirmSelectBrand() {
                this.isPopupBrand = false;
            },
            loadMore(){
                setTimeout(() => {
                    this.disabled = true
                    if(this.page * this.size == this.teamListData.length){
                        this.page ++
                        this.getGoodsList() 
                    }
                },200)
            }
        },
        watch:{
            teamListData(){
                if(this.page * this.size == this.teamListData.length){
                    this.disabled = false
                    this.loading = true
                }else{
                    this.loading = false
                }

                this.teamListData = arrRemove.trimSpace(this.teamListData)
            }
        }
    }
</script>
