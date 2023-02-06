<script type="text/javascript">


    /**
     * 正则验证 非负浮点数 且小数点后两位 就返回true
     * @param val
     * @returns {boolean}
     */
    function isFloatNum(val) {
        if (val) {
            var reg = /^\+{0,1}\d+(\.\d{1,2})?$/; // 非负浮点数 小数点后2位
            if (reg.test(val)){
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * 正则验证 非负整数 就返回true
     * @param val
     * @returns {boolean}
     */
    function isIntNum(val) {
        if (val) {
            var reg = /^[0-9]*[1-9][0-9]*$/; // 非负整数
            if (reg.test(val)){
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * 正则验证 百分比
     * @param val
     * @returns {boolean}
     */
    function isPercentNum(val) {
        if (val) {
            var reg = /^(100|[1-9]?\d(\.\d\d?\d?)?)\%$/; // 只能输入0%-100% 之间的数
            if (reg.test(val)){
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

</script>