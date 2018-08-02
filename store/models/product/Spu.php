<?php
//标准售卖单元
namespace store\models\product;
use Yii;
use yii\base\SmartException;
use yii\db\SmartActiveRecord;
use store\models\model\Source;
use store\models\model\Product;
use store\models\member\Member;
use store\models\distribute\distribute;
use yii\web\UploadedFile;
use store\models\category\CategoryRecord;
use store\models\category\Category;
use store\models\logistics\Logistics;
use store\models\Common;

/** 
 * This is the model class for table "spu". 
 * 
 * @property int $id 主键
 * @property string $uniqueId spu唯一编号
 * @property string $title 标题
 * @property string $desc 描述
 * @property string $cover 封面图
 * @property double $freight 运费
 * @property int $distributeType 配送方式(1=冷链/2=非冷链)
 * @property int $logisticsId 物流渠道id
 * @property string $detail 商品详情
 * @property string $uri 详情uri
 * @property int $memberId 推荐人id
 * @property int $closed 是否下架(0=上架/1=下架)
 * @property int $locked 锁定(0=正常/1=锁定)
 * @property int $createTime 创建时间
 */ 
//========================================
class Spu extends Product
{
	public function rules() 
    { 
        return [
            [['uniqueId', 'title', 'distributeType', 'cover', 'desc', 'logisticsId', 'memberId', 'createTime'], 'required'],
            [['distributeType', 'logisticsId', 'memberId', 'closed', 'locked', 'createTime'], 'integer'],
            [['detail'], 'string'],
            [['uniqueId', 'title', 'desc', 'cover', 'uri'], 'string', 'max' => 200],
            [['title'], 'unique'],
            [['uniqueId'], 'unique'],
        ]; 
    } 
	//========================================
    public function attributeLabels(){ 
        return [ 
            'id' => 'ID',
            'uniqueId' => '商品编码',
            'title' => '商品标题',
            'desc' => '商品描述',
            'cover' => '封面图',
            'freight' => '运费',
            'distributeType' => '配送方式',
            'detail' => '商品详情',
            'uri' => '详情uri',
            'memberId' => '推荐人id',
            'closed' => '是否下架',
            'locked' => '是否锁定',
            'logisticsId' => '物流渠道ID',
        ]; 
    } 
    //========================================
	//返回资源类型
	public function getSourceType(){return Source::TYPE_SPU;}
	//========================================
	//初始化
	public function init(){
		parent::init();
		$this->on(self::EVENT_BEFORE_INSERT,array($this,"initCreateTime"));
	}
	//========================================
	//获取物流配送方式
	public function getDistributeType(){
		if($this->distributeType==1) return distribute::TYPE_REFRIGERATION;
		if($this->distributeType==2) return distribute::TYPE_NORMAL;
		throw new SmartException("error distributeType");
	}
	//========================================
	//获取sku
	public function getSkus()
	{
		return $this->hasMany(Sku::className(),array('spuId'=>'id'))->orderBy('id asc');
	}

	//获取物流渠道
	public function getLogistics()
	{
		return $this->hasOne(Logistics::className(), ['id'=>'logisticsId']);
	}
	//========================================
	//获取大类
	public function getTopCategory(){
		return $this->hasMany(Category::className(), ['id'=>'categoryId'])
			->viaTable(CategoryRecord::tableName(), ['sourceId'=>'id']);
	}
	//========================================
	//获取销售价最便宜的sku
	public function getCheapestSku(){
		$cheapestSku=NULL;
		//统计销售价最便宜的
		foreach($this->skus as $sku){
			if(!$cheapestSku) $cheapestSku=$sku;
			if($sku->getPrice()<$cheapestSku->getPrice()) $cheapestSku=$sku;
		}
		//没有找到最便宜的报错
		if(!$cheapestSku) throw new SmartException("miss cheapestSku");
		//返回最便宜的
		return $cheapestSku;
	}


	public function initCreateTime()
	{
		$this->createTime = time();
	}

	//========================================
	//插入spu
	public function saveSpu($data)
	{
		if ($this->load($data)) {
			$this->cover = $this->cover[0];
			//$this->detail = self::subImg($this->detail);
			$this->memberId = Yii::$app->session['staff']['staffId'];
			$this->createTime = time();
			//echo "<pre>";print_r($this->detail);exit;
			self::checkDetail($this->detail);
			if (!$this->validate()) {
				throw new SmartException("spu参数校验失败");
			}
			$spuId = $this->save(false);
			if (!$spuId){
				throw new SmartException("spu保存失败");
			}
			return $spuId;
			//echo "<pre>"; print_r($this->getValidators());exit;
		}
	}

	//校验商品详情是否符合正则条件
	public static function checkDetail($detail)
	{
		if (strpos($detail, 'img') === false) {
			throw new SmartException("商品详情必须带有图片");
		}
		//先找出所有带<a>标签的图片
        $pa = '%<a.*?>(.*?)</a>%si';
        preg_match_all($pa, $detail, $match);
        //echo "<pre>";print_r($match[0]);exit;
        foreach (@$match[0] as $content) {
            $pat = '/<a(.*?)href="(.*?)"(.*?)>(.*?)<\/a>/i';
            preg_match_all($pat, $content, $m);
            //echo "<pre>";print_r($m);exit;
            if (!isset($m[2][0])) {
            	throw new SmartException("a标签的href链接不能为空！");
            }
            $pregRule = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
            preg_match_all($pregRule, $content, $out,PREG_PATTERN_ORDER);
            if (!isset($out[1][0])) {
            	throw new SmartException("a标签里的img不能为空！");
            }
        }
        //echo "<pre>";print_r($images);exit;
        //再找出所有不带<a>标签的图片
        $content = preg_replace("/<(a.*?)>(.*?)<(\/a.*?)>/si","",$detail); 
        $pregRule = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
        preg_match_all($pregRule, $content, $out, PREG_PATTERN_ORDER);
        
        foreach ($out[1] as $val) {
        	if (!$val) {
        		throw new SmartException("img标签里的src不能为空！");
        	}
        }
	}

	//正则提取图片
    public static function subImg($html)
    {
        $images = [];
        $i = 0;
        $str = '<a href="http://www.baidu.com" target="_blank" title="百度"><img src="http://p33mnuvro.bkt.clouddn.com/uploads/0410/152333922416664591.jpg"/></a><a href="http://www.baidu.com" target="_blank" title="百度"><img src="http://p33mnuvro.bkt.clouddn.com/uploads/0410/152333922466284678.jpg"/></a><img alt="" src="http://p33mnuvro.bkt.clouddn.com/2018/6/5xxczn3js0.jpg"/><img alt="" src="http://p33mnuvro.bkt.clouddn.com/2018/4/65c16brc19.jpg"/><video class="edui-upload-video  vjs-default-skin video-js" controls="" preload="none" width="420" height="280" src="http://p33mnuvro.bkt.clouddn.com/uploads/0625/1529916372129979276.mp4" data-setup="{}"></video>';
        //先找出所有带<a>标签的图片
        $pa = '%<a.*?>(.*?)</a>%si';
        preg_match_all($pa, $html, $match);
        //echo "<pre>";print_r($match[0]);exit;
        foreach ($match[0] as $content) {
            $pat = '/<a(.*?)href="(.*?)"(.*?)>(.*?)<\/a>/i';   
            preg_match_all($pat, $content, $m);
            //echo "<pre>";print_r($m);exit;
            $href = $m[2][0];
            $images[$i]['href'] = $href;
            $pregRule = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
            preg_match_all($pregRule, $content, $out,PREG_PATTERN_ORDER);
            $images[$i]['src'] = @$out[1][0];
            $i++;
        }
        //echo "<pre>";print_r($images);exit;
        //再找出所有不带<a>标签的图片
        $content = preg_replace("/<(a.*?)>(.*?)<(\/a.*?)>/si","",$html); 
        $pregRule = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
        preg_match_all($pregRule, $content, $out,PREG_PATTERN_ORDER);
        
        foreach ($out[1] as $val) {
            $images[$i]['src'] = $val;
            $images[$i]['href'] = '';
            $i++;
        }

        //找出所有<video>标签的视频
        $pregRule = "/<[video|VIDEO].*?src=[\'|\"](.*?(?:[\.mp4|\.avi]))[\'|\"].*?[\/]?>/";
        preg_match_all($pregRule, $html, $out,PREG_PATTERN_ORDER);

        echo "<pre>";print_r($out);exit;


        return json_encode($images);
    }

    public static function getQrcode($spuId)
    {
		//获取access_token
		$access_token = Yii::$app->smartWechat->getAccessToken(Yii::$app->params['miniApp']['appId'], Yii::$app->params['miniApp']['appSecret']);
		/*$access_token = "11_e3s7-ZOeKLVwsW4-LqCI_xIJ60pxQFuWfqXxDDHJX7AQEeMLikg-8YDP1MDrZazrtq95n9-Gpp9iMhwLpeUNi-q6a4Q38tzU3xFuomVMgd3AgqaRGWQjw8-abK1ZDKC1BWRjyjVs3j74j_vyZMUaAEAPCK";*/
		//echo "<pre>";print_r($access_token);exit;
		//header('content-type:image/gif');  
		$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;
		$params = [
			'scene' => 'id='.$spuId,
			'page' => 'pages/detail',
			'width' => '430'
		];
		$params = json_encode($params);
		$response = Common::api_notice_increment($url,$params);
		$file = "./images/qrcode/spu_".$spuId.".jpg";
        file_put_contents($file,$response);
		//$response = $this->get_http_array($url,$params);
		return "./images/qrcode/spu_".$spuId.".jpg";
    }
}