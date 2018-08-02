<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "platform_order".
 *
 * @property string $order_id
 * @property string $spu_title
 * @property string $sku_title
 * @property string $sku_code
 * @property string $store_code
 * @property string $consignee
 * @property string $address
 * @property string $phone
 * @property string $hash
 * @property integer $quantity
 * @property string $buyer_memo
 * @property string $memo
 * @property integer $logistics_id
 * @property string $single_price
 * @property string $order_status
 * @property integer $origin
 * @property string $goods_price
 * @property string $post_fee
 * @property string $real_pay
 * @property string $order_created
 * @property string $order_payed
 * @property integer $pay_time
 */
class PlatformOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'platform_order';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('wbdb');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'spu_title', 'consignee', 'address', 'phone', 'hash', 'quantity', 'logistics_id', 'pay_time'], 'required'],
            [['quantity', 'logistics_id', 'origin', 'pay_time'], 'integer'],
            [['single_price', 'goods_price', 'post_fee', 'real_pay'], 'number'],
            [['order_id'], 'string', 'max' => 255],
            [['spu_title', 'sku_title', 'address', 'order_status'], 'string', 'max' => 200],
            [['sku_code', 'store_code', 'phone', 'order_created', 'order_payed'], 'string', 'max' => 100],
            [['consignee'], 'string', 'max' => 32],
            [['hash', 'buyer_memo', 'memo'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'order_id' => 'Order ID',
            'spu_title' => 'Spu Title',
            'sku_title' => 'Sku Title',
            'sku_code' => 'Sku Code',
            'store_code' => 'Store Code',
            'consignee' => 'Consignee',
            'address' => 'Address',
            'phone' => 'Phone',
            'hash' => 'Hash',
            'quantity' => 'Quantity',
            'buyer_memo' => 'Buyer Memo',
            'memo' => 'Memo',
            'logistics_id' => 'Logistics ID',
            'single_price' => 'Single Price',
            'order_status' => 'Order Status',
            'origin' => 'Origin',
            'goods_price' => 'Goods Price',
            'post_fee' => 'Post Fee',
            'real_pay' => 'Real Pay',
            'order_created' => 'Order Created',
            'order_payed' => 'Order Payed',
            'pay_time' => 'Pay Time',
        ];
    }
}
