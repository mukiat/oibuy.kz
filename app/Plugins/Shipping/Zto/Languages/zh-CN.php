<?php

$_LANG['zto'] = '中通速递';
//$_LANG['zto_desc']     = '中通快递的相关说明。保价费按照申报价值的2％交纳，但是，保价费不低于100元，保价金额不得高于10000元，保价金额超过10000元的，超过的部分无效';
$_LANG['zto_desc'] = '<a href="http://www.zto.cn" target="_blank">http://www.zto.cn</a>';
$_LANG['item_fee'] = '单件商品费用';
$_LANG['base_fee'] = '首重1KG以内费用';
$_LANG['step_fee'] = '续重每1KG或其零数的费用';
$_LANG['shipping_print'] = '<table style="width:18.2cm" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td style="height:2.2cm;">&nbsp;</td>
  </tr>
</table>
<table style="width:18.2cm" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td style="height:4.4cm; width:9.1cm;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
    <td style="width:2cm; height:0.8cm;">&nbsp;</td>
    <td style="width:2.7cm;">{$shop_name}</td>
    <td style="width:1.2cm;">&nbsp;</td>
    <td>{$province}</td>
    </tr>
    <tr>
    <td>&nbsp;</td>
    <td colspan="3" style="height:1.6cm;">{$shop_address}</td>
    </tr>
    <tr>
    <td>&nbsp;</td>
    <td colspan="3" style="height:0.8cm;">{$shop_name}</td>
    </tr>
    <tr>
    <td>&nbsp;</td>
    <td style="height:1.2cm;">{$service_phone}</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    </tr>
    </table>
    </td>
    <td style="height:4.4cm; width:9.1cm;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
    <td style="width:2cm; height:0.8cm;">&nbsp;</td>
    <td style="width:2.7cm;">{$order.consignee}</td>
    <td style="width:1.2cm;">&nbsp;</td>
    <td>{$order.region}</td>
    </tr>
    <tr>
    <td>&nbsp;</td>
    <td colspan="3" style="height:1.6cm;">{$order.address}</td>
    </tr>
    <tr>
    <td>&nbsp;</td>
    <td colspan="3" style="height:0.8cm;"></td>
    </tr>
    <tr>
    <td>&nbsp;</td>
    <td style="height:1.2cm;">{$order.mobile}</td>
    <td>&nbsp;</td>
    <td>{$order.zipcode}</td>
    </tr>
    </table>
    </td>
  </tr>
</table>
<table style="width:18.2cm" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td style="height:4.2cm;">&nbsp;</td>
  </tr>
</table>';

/* 快递单部分 */
$_LANG['lable_select_notice'] = '--选择插入标签--';
$_LANG['lable_box']['shop_country'] = '网店-国家';
$_LANG['lable_box']['shop_province'] = '网店-省份';
$_LANG['lable_box']['shop_city'] = '网店-城市';
$_LANG['lable_box']['shop_name'] = '网店-名称';
$_LANG['lable_box']['shop_district'] = '网店-区/县';
$_LANG['lable_box']['shop_tel'] = '网店-联系电话';
$_LANG['lable_box']['shop_address'] = '网店-地址';
$_LANG['lable_box']['customer_country'] = '收件人-国家';
$_LANG['lable_box']['customer_province'] = '收件人-省份';
$_LANG['lable_box']['customer_city'] = '收件人-城市';
$_LANG['lable_box']['customer_district'] = '收件人-区/县';
$_LANG['lable_box']['customer_tel'] = '收件人-电话';
$_LANG['lable_box']['customer_mobel'] = '收件人-手机';
$_LANG['lable_box']['customer_post'] = '收件人-邮编';
$_LANG['lable_box']['customer_address'] = '收件人-详细地址';
$_LANG['lable_box']['customer_name'] = '收件人-姓名';
$_LANG['lable_box']['year'] = '年-当日日期';
$_LANG['lable_box']['months'] = '月-当日日期';
$_LANG['lable_box']['day'] = '日-当日日期';
$_LANG['lable_box']['order_no'] = '订单号-订单';
$_LANG['lable_box']['order_postscript'] = '备注-订单';
$_LANG['lable_box']['order_best_time'] = '送货时间-订单';
$_LANG['lable_box']['pigeon'] = '√-对号';

return $_LANG;
