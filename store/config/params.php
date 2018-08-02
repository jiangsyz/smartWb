<?php
return [
    //顺丰冷运excel表头
    'SfColdExcelHeader' => [
    	'客户编码',
		'月结卡号',
		'客户订单号',
		'订单类型代码',
		'订单类型名称',
		'仓库代码',
		'付款方式',
		'是否货到付款',
		'代收货款金额',
		'是否保价',
		'声明价值',
		'包装费',
		'其他个性化服务',
		'承运商代码',
		'承运商服务类型',
		'收货人名称',
		'收货联系人',
		'收货联系人手机',
		'收件联系人电话',
		'收货人地址',
		'发货人',
		'发货联系人',
		'发货联系人手机',
		'发货联系人电话',
		'发货人地址',
		'商品代码',
		'商品名称',
		'件数',
		'包装单位名称',
		'批号',
		'第三方付款网点编码',
		'是否加工单',
		'是否签回单',
		'寄件人标识',
		'备注',
	],
	//顺丰冷运Excel固定字段
	'SfColdExcelBasicData' => [
		'codeNo' => '0212553176',
		'orderType' => 'PO',
		'typeName' => '',
		'houseCode' => '021DCF',
		'payType' => '寄付',
		'receiverPay' => 'N',
		'insteadCash' => '',
		'insured' => 'N',
		'statement' => '',
		'packingFee' => '',
		'individuationService' => '',
		'carrierCode' => 'CP',
		'carrierType' => 'SE0022',
		'consignor' => '正善牛肉',
		'tel' => '4009679177',
		'address' => '上海市虹口区花园路128号德必运动5街区A做501室',
		'receipt' => 'N'
	],
	//德邦红酒Excel表头
	'DbWineExcelHeader' => [
		'订单处理时间',
	    '发件人',
	    '发件人电话',
	    '订单ID/采购单ID',
	    '收货人姓名',
	    '收货地址',
	    '联系手机',
	    '宝贝标题',
		'宝贝代码',
	    '数量',
	    '快递公司',
	    '物流单号',
	    '运费单价',
	    '保价',
		'总价',
	    '备用品名',
        '备注',
	],
	//德邦快递Excel表头
	'DbExpressExcelHeader' => [
		'订单ID',
	    '收货人姓名',
	    '联系手机',
	    '收货地址',
	    '物流单号',
	    '宝贝编码',
	    '宝贝标题',
	    '订单备注',
	    '宝贝总数量',
	],
	//整条出库Excel表头
	'ZsckExcelHeader' => [
		'操作人',
	    '时间',
	    '出库类型',
	    '商品类型',
	    '序号',
	    '重量',
        '宝贝编码',
	    '发货对应订单号',
	    '加工方法',
	    '收货人',
	    '联系方式',
	    '收货地址',
	    '宝贝数量',
	    '订单留言',
	    '备注',
	    '物流公司',
	    '物流单号', 
	],
	//礼品卡Excel表头
	'CardExcelHeader' => [
		'订单ID',
		'收货人姓名',
		'联系手机',
		'收货地址',
		'宝贝SKU',
		'宝贝标题',
		'订单备注',
		'宝贝总数量',
		'订单留言'
	],

];
