<template>
    <div class='menu-link'>
        <div class="link-group">
            <div class="link-name" :class="{ no:bUrlName }">
                <template v-if="!bUrlName">
                    <slot name="link-name-close"></slot>
                    <p :title="catetory + '-' + urlName">
                        <b>{{ catetory }}</b>
                        <template v-if="urlName">- {{ urlName }}</template>
                    </p>
                </template>
                <template v-else>
                    <p>{{$t('lang.select')}}</p>
                </template>
            </div>
            <dropdown trigger="click" @command="handleCommand">
                <span class="dropdown-link">{{$t('lang.mod')}}</span>
                <dropdown-menu slot="dropdown">
                    <dropdown-item v-for="item in dropdownItem" :key="item.command" :command="item.command"> {{ item.text }}</dropdown-item>
                </dropdown-menu>
            </dropdown>
        </div>
    </div>
</template>

<script>
// mapActions mapState
import {
    mapActions,
    mapState
} from 'vuex'

// third party components
import {
    Dropdown,
    DropdownMenu,
    DropdownItem
} from 'element-ui'



export default {
    name: 'menu-link',
    props: {
        urlName: {},
        catetory: {},
        listIndex: {
            type: Number,
            required: true
        },
        modulesIndex: {
            type: Number,
            required: true
        }
    },
    components: {
        Dropdown,
        DropdownMenu,
        DropdownItem,
    },
    data() {
        return {

        }
    },
    created() {

    },
    methods: {
        handleCommand(val) {
            let sText
            this.dropdownItem.map((v, i) => {
                if (v.command == val) {
                    sText = v.text
                    return false
                }
            })
            let o = {
                type: val,
                bShowDialog: true,
                currentPage: 1,
                pageSize: 10,
                modulesIndex: this.modulesIndex,
                listIndex: this.listIndex,
                dialogTitle: sText
            }
            this.$store.dispatch('setDialogUrl', o)

        }
    },
    computed: {
        dropdownItem() {
            let aDropdownItem = [{
                command: 'function',
                text: this.$t('lang.function_page'),
                bShowStore:false
            }, {
                command: 'activity',
                text: this.$t('lang.mark_campaign'),
                bShowStore:true
            }, {
                command: 'category',
                text: this.$t('lang.goods_categary'),
                bShowStore:true
            }, {
                command: 'article',
                text: this.$t('lang.article_categary'),
                bShowStore:false
            }, {
                command: 'custom',
                text: this.$t('lang.custom_page'),
                bShowStore:true
            }]
            if (!this.bStore) {
                return aDropdownItem
            } else {
                return aDropdownItem.filter(item => {
                    return item.bShowStore
                })
            }
        },
        bStore() {
            return window.shopInfo.ruid != 0
        },
        bUrlName() {
            return '' == this.urlName || '' == this.urlName ? true : false
        },
    }
}

</script>

<style lang="scss" scoped>
@import '../../../assets/style/config.scss';
@import '../../../assets/style/mixins/common.scss';
.dropdown-link {
    font-size: 1.3rem;
    cursor: pointer;
    color: $link-color;
}

.link-group {
    width: 100%;
    @include direction(center, flex-start);
}

.link-name {
    position: relative;
    max-width: 74%;
    margin-right: .6rem;
}

.link-name.no p {
    background: #bbb;
}

.link-name p {
    color: #fff;
    font-size: 1.3rem;
    background: #3eb1fa;
    padding: .3rem .5rem;
    border-radius: 6px;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
}

.link-name .link-name-close {
    position: absolute;
    right: -.9rem;
    top: -.9rem;
    color: #fff;
    text-align: center;
    width: 1.8rem;
    height: 1.8rem;
    line-height: 1.8rem;
    background: rgba(0, 0, 0, .4);
    border-radius: 9999px;
    transform: scale(.9);
    display: none;
}

.link-name:hover .link-name-close {
    display: block;
    cursor: pointer;
}

.link-name .link-name-close i {
    font-size: 14px;
}
</style>