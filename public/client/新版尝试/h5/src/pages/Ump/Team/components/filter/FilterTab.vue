<template>
    <section class="filter_tab">
        <div class="pro_filter_items dis-box">
            <div class="item" :class="[{'active':filter.sort === '0','a-change':filter.order === 'ASC' && filter.sort === '0'}]" @click="handleFilter('0',filter.order)">
                <span>{{$t('lang.comprehensive')}}</span>
                <i class="iconfont icon-xiajiantou"></i>
            </div>
            <div class="item" :class="{'active':filter.sort === '1'}" @click="handleFilter('1')">
                <span>{{$t('lang.new')}}</span>
            </div>
            <div class="item" :class="{'active':filter.sort === '2'}" @click="handleFilter('2')">
                <span>{{$t('lang.sales_volume')}}</span>
            </div>
            <div class="item" :class="[{'active':filter.sort === '3','a-change':filter.order === 'ASC' && filter.sort === '3'}]" @click="handleFilter('3',filter.order)">
                <span>{{$t('lang.price')}}</span>
                <i class="iconfont icon-xiajiantou"></i>
            </div>
            <!-- <div class="item item-icon" @click="viewSwitch">
                <template v-if="myMode === 'small'">
                    <i class="iconfont icon-viewlist"></i>
                </template>
                <template v-else>
                    <i class="iconfont icon-pailie"></i>
                </template>
            </div> -->
        </div>
    </section>
</template>
<script>
    export default {
        props: ['filter'],
        data() {
            return {
                myMode: this.filter.mode
            }
        },
        computed: {

        },
        methods: {
            viewSwitch() {
                this.myMode = this.myMode === 'small' ? 'medium' : 'small'
                this.$emit('getViewSwitch', this.myMode)
            },
            handleFilter(val, order) {
                if (order && this.filter.sort == val) {
                    this.filter.order = order == 'DESC' ? 'ASC' : 'DESC'
                }
                this.filter.sort = val;
                this.$emit('getFilter', {
                    sort: this.filter.sort,
                    order: this.filter.order
                })
            }
        }
    }
</script>
