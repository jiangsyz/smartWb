<?php
//分类
namespace store\models\category;
use Yii;
use yii\base\SmartException;
use yii\db\SmartActiveRecord;
use yii\helpers\ArrayHelper;
//========================================
class Category extends SmartActiveRecord
{
	//虚拟字段  用于表述该分类下的二级分类
	public $childrenid;
	//========================================
	//规则
    public function rules(){ 
        return [
            [['name'], 'required'],
            [['pid', 'sort'], 'integer'],
            [['name', 'icon'], 'string', 'max' => 200],
            [['name', 'pid'], 'unique', 'targetAttribute' => ['name', 'pid']],
        ]; 
    } 
	//========================================
	//属性
    public function attributeLabels(){ 
        return [ 
            'id' => 'ID',
            'name' => '一级分类',
            'icon' => 'Icon',
            'pid' => 'Pid',
            'sort' => 'Sort',
        ]; 
    } 
	//获取子分类
	public function getChildren(){return $this->hasMany(self::className(),array('pid'=>'id'));}
	//========================================
	//获取所有的顶级分类
	static public function getTopCategories(){
		return self::find()->where("`pid` is NULL")->orderBy("`sort` ASC")->all();
	}

    /** 
     * 获取所有的分类 
     */  
    public function getCategories()  
    {  
        $data = self::find()->all();  
        $data = ArrayHelper::toArray($data);  
        return $data;  
    } 

    /** 
     *遍历出各个子类 获得树状结构的数组 
     */  
    public static function getTree($data,$pid = 0,$lev = 1)  
    {  
        $tree = [];  
        foreach($data as $value){  
            if($value['pid'] == $pid){  
                $value['name'] = str_repeat('|___',($lev-1)).$value['name'];  
                $tree[] = $value;  
                $tree = array_merge($tree,self::getTree($data,$value['id'],$lev+1));  
            }  
        }  
        
        return $tree;  
    }  
  
    /** 
     * 得到相应  id  对应的  分类名  数组 
     */  
    public function getOptions()  
    {  
        $data = $this->getCategories();  
        $tree = $this->getTree($data);  
        foreach($tree as $value){  
            $list[$value['id']] = $value['name'];  
        }  
        return $list;  
    }  
}