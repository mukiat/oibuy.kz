
<div class="floatbar-sales">{{ $act_type_txt }}</div>
<div class="floatbar-checkout">
    <div class="sales-tip">
    {{ $lang['cou_cart_one'] }}&nbsp;&nbsp;{{ $lang['total'] }}<font id="buyTotalNums"></font>{{ $lang['jian'] }}
    </div>
    <div class="price" id="buyTotalMoney"></div>
    <a href="flow.php" class="submit-btn">{{ $lang['cart_pay_go'] }}</a>
    
@if($act_type_txt == "{{ __('common.With_a_gift') }}")

    <div class="prize" id="wenAn">，<a href="flow.php" class="ftx-05">{{ $lang['back_cart'] }}</a></div>
    
@endif

</div>