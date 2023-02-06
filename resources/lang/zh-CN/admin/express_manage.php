<?php

return [
    'express_manage' => '物流管理',
    'express_company_manage' => '快递公司管理',
    'express_delivery_record' => '快递配送记录',
    'tip_content' => [
        '您需要先完成 <a href="' . route('admin.express.index') . '">快递查询接口配置</a>',
        '选择快递公司启用；',
        '发货时选择快递仅能在已启用快递公司中选择；',
        '请根据商城实际需求启用快递公司',
    ],
    'initiate_mode' => '启用状态',
    'on' => '启用',
    'off' => '禁用',
    'express_company' => '快递公司',
    'express_number' => '快递单号',
    'tracking_number' => '发货单号',
    'express_type' => '快递类型',
    'order_number' => '订单编号',
    'merchant' => '商家',
    'delivery_time' => '发货时间',
    'operation' => '操作',
    'initial' => '编码',
    'detail' => '详情',
    'please_enter_the_name' => '请输入快递公司名称',
    'delivery_record_placeholder' => '快递公司/订单号/发货单号',
];
