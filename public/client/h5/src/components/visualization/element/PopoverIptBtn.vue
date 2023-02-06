<template>
    <div class="popover-ipt-btn">
        <ec-input class="popover-input" size="small" v-model="thisValue" style="float:left;"></ec-input>
        <ec-button size="small" type="primary" @click.native="updateValue">{{$t('lang.confirm')}}</ec-button>
    </div>
</template>

<script>
// third party components
import {
    Button,
    Input
} from 'element-ui'
export default {
    name: "popover-ipt-btn",
    props: ['value', 'listIndex', 'popoverRef', 'id'],
    components: {
        "EcButton": Button,
        "EcInput": Input
    },
    data() {
        return {
            sUpdateValue: ""
        }
    },
    methods: {
        updateValue() {
            this.$emit('updateValue', {
                value: this.sUpdateValue,
                listIndex: this.listIndex,
                popoverRef: this.popoverRef,
                id: this.id
            })
        }
    },
    computed: {
        thisValue: {
            get() {
                return this.value
            },
            set(val) {
                this.sUpdateValue = val
            }
        }
    }
}
</script>

<style lang="scss">
@import '../../../assets/style/config.scss';
@import '../../../assets/style/mixins/common.scss';
.popover-ipt-btn {
    @include disFlex();
}

.popover-ipt-btn .popover-input {
    margin-right: .6rem;
}
</style>

