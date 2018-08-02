<?php
//文件导入类 
namespace common\models;
use phpoffice\phpexcel;
class Excel
{
	//=================================
	//报告导入一份
	public static function read($file_name,$file_tmp,$file_error)
	{
		try
		{
			//判断文件是否上传失败
			$error=$file_error;
			if($error>0)throw new \Exception(self::get_upload_error($error),$error);
			//去除文件后缀名
			$extension=pathinfo($file_name,PATHINFO_EXTENSION); 
			//文件后缀格式
			if($extension=="xls"||$extension=="xlsx")
			{
				try{
					$inputFileType=\PHPExcel_IOFactory::identify($file_tmp);
					$objReader=\PHPExcel_IOFactory::createReader($inputFileType);
					$objPHPExcel=$objReader->load($file_tmp);
				} 
				catch(\Exception $e)
				{
					 return array('error'=>$e->getCode(),'message'=>'加载文件发生错误："'.$extension.'": '.$e->getMessage());
				}
				// 确定要读取的sheet，什么是sheet，看excel的右下角，真的不懂去百度吧
				$sheet = $objPHPExcel->getSheet(0);
				$highestRow = $sheet->getHighestRow();
				$highestColumn = $sheet->getHighestColumn();
				$rowData=[];
				// 获取一行的数据
				for ($row=1; $row<=$highestRow;$row++)
				{
					// Read a row of data into an array
					//这里得到的rowData都是一行的数据，得到数据后自行处理，我们这里只打出来看看效果
					$rowData[]=$sheet->rangeToArray('A'.$row.':'.$highestColumn.$row,NULL,TRUE,FALSE);
				}
				//删除数组第一项
		        array_shift($rowData);
				return array('error'=>true,'data'=>$rowData);
			}
			elseif($extension=='csv')
			{
				//获取临时文件
				$tmp_file=$file_tmp;
				$objReader=\PHPExcel_IOFactory::createReader('CSV')
					        ->setDelimiter(',')  
					        ->setInputEncoding('GBK')  
					        ->setEnclosure('"') 
					        ->setSheetIndex(0);  
                //读取文件            
		        $objPHPExcel=$objReader->load($tmp_file); 
		        //将csv转成数组格式
		        $data=$objPHPExcel->getSheet()->toArray();
		         //删除数组第一项
		        array_shift($data);
			}
			//返回数据
			return array('error'=>true,'data'=>$data);
		}catch(\Exception $e){return array('error'=>$e->getCode(),'message'=>$e->getMessage());}
	}

	//导入多SHEET工作表
	public static function multipleSheetRead($file_name,$file_tmp,$file_error, $type)
	{
		try
		{
			//判断文件是否上传失败
			$error=$file_error;
			if($error>0)throw new \Exception(self::get_upload_error($error),$error);
			//去除文件后缀名
			$extension=pathinfo($file_name,PATHINFO_EXTENSION); 
			//文件后缀格式
			if($extension=="xls"||$extension=="xlsx")
			{
				try{
					$inputFileType=\PHPExcel_IOFactory::identify($file_tmp);
					$objReader=\PHPExcel_IOFactory::createReader($inputFileType);
					$objPHPExcel=$objReader->load($file_tmp);
				} 
				catch(\Exception $e)
				{
					 return array('error'=>$e->getCode(),'message'=>'加载文件发生错误："'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
				}

				$rowData=[];
				for ($i=0; $i<$objPHPExcel->getSheetCount(); $i++) {
					$sheet = $objPHPExcel->getSheet($i);
					$highestRow = $sheet->getHighestRow();
					$highestColumn = $sheet->getHighestColumn();
					
					// 获取一行的数据
					for ($row=1; $row<=$highestRow;$row++)
					{
						// Read a row of data into an array
						//这里得到的rowData都是一行的数据，得到数据后自行处理，我们这里只打出来看看效果
						$tmp = $sheet->rangeToArray('A'.$row.':'.$highestColumn.$row,NULL,TRUE,FALSE);
						$tmp1 = current($tmp);
						//过滤空行和标题行  用发件人电话判断
						if (!is_numeric($tmp1[2]) && $type==4) {
							continue;
						}
						if (!is_numeric($tmp1[6]) && $type==5) {
							continue;
						}
						$rowData[] = $tmp;
					}
					//删除数组第一项
			        //array_shift($rowData);
				}
				
				return array('error'=>true,'data'=>$rowData);
			}
			elseif($extension=='csv')
			{
				//获取临时文件
				$tmp_file=$file_tmp;
				$objReader=\PHPExcel_IOFactory::createReader('CSV')
					        ->setDelimiter(',')  
					        ->setInputEncoding('GBK')  
					        ->setEnclosure('"') 
					        ->setSheetIndex(0);  
                //读取文件            
		        $objPHPExcel=$objReader->load($tmp_file); 
		        //将csv转成数组格式
		        $data=$objPHPExcel->getSheet()->toArray();
		         //删除数组第一项
		        array_shift($data);
			}
			//返回数据
			return array('error'=>true,'data'=>$data);
		}catch(\Exception $e){return array('error'=>$e->getCode(),'message'=>$e->getMessage());}
	}

	//======================================
	//获取报错码
	public static function get_upload_error($error)
	{
		$text='';
		switch($error) 
		{
			case 1:
				$text='上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值';
					break;
				case 2:
				$text='上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
					break;
				case 3:
				$text='文件只有部分被上传';
					break;
				case 4:
				$text=' 没有文件被上传';
					break;
			default:
				$text='服务器错误';
				break;
		}
		return $text;
	}
	//=================================
	//物流导出csv格式的订单
	public static function exportDataCsv($data,$header,$filename = "data")
	{
		if (!is_array ($data)||empty($header)) return false;
		$header=iconv('utf-8','gb2312',$header); 
		foreach($data as $v)
		{ 
			$order_no=iconv('utf-8','gb2312',$v->order_no); //中文转码 
			$delivery_company=iconv('utf-8','gb2312',$v->delivery_company); 
			$header.=$order_no.",".$delivery_company.",".$v->delivery_no."\n"; //用引文逗号分开
		} 
		header("Content-type:text/csv"); 
        header("Content-Disposition:attachment;filename=".$filename.'.csv'); 
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0'); 
        header('Expires:0'); 
        header('Pragma:public'); 
        echo $header;
        exit();
	}


	//=================================
	//订单导出
		/**
	 *  @DESC 数据导
	 *  @notice 解决了上面导出列数过多的问题
	 *  @example 
	 *  $data = [1, "小明", "25"];
	 *  $header = ["id", "姓名", "年龄"];
	 *  Myhelpers::exportData($data, $header);
	 *  @return void, Browser direct output
	 */
	public static function exportData ($data,$header,$sheet,$filename="data")
	{
	    if (!is_array ($data) || !is_array ($header)) return false;
	    $objPHPExcel = new \PHPExcel();
	    // Set properties
	    $objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
	    $objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
	    $objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
	    $objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
	    $objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");
	    foreach ($sheet as $sk => $sv)
	    {
	    	if($sk==0)
	    	{
		    	// Add some data
			    $objPHPExcel->setActiveSheetIndex($sk);
			    //Rename sheet
			    $objPHPExcel->getActiveSheet()->setTitle($sv);
	    	}else
	    	{
	    		$objPHPExcel->createSheet();
	    		$objPHPExcel->setActiveSheetIndex($sk);
	    		//Rename sheet
			    $objPHPExcel->getActiveSheet()->setTitle($sv);
			}
			// $objPHPExcel->getActiveSheet()->setAutoFilter('C1:C20');
			// $autoFilter=$objPHPExcel->getActiveSheet()->getAutoFilter();
			// $columnFilter=$autoFilter->getC('C');
			// $columnFilter->createRule()->setRule('equal',array(
			// 	'year' => 2012,
			// 	'month' => 1
			// ));
		    //添加头部
		    $hk = 0;
		    foreach ($header as $k => $v)
		    {
		        $colum = \PHPExcel_Cell::stringFromColumnIndex($hk);
		        $objPHPExcel->setActiveSheetIndex($sk)->setCellValue($colum."1", $v);
		        $hk += 1;
		    }
		    $column = 2;
		    $objActSheet = $objPHPExcel->getActiveSheet();
		    if(!empty($data[$sv]))
		    {
			  foreach($data[$sv] as $key => $rows)  //行写入
			    {
			        $span = 0;
			        foreach($rows as $keyName => $value) // 列写入
			        {

			            $j = \PHPExcel_Cell::stringFromColumnIndex($span);
			            $objActSheet->getDefaultColumnDimension($j.$column)->setWidth(18);
			            $objActSheet->setCellValue($j.$column, $value);
			            $span++;
			        }
			        $column++;
			    }
		    }else continue;
		}
		
	    // Save Excel 2007 file
	    $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
	    header("Pragma:public");
	    header("Content-Type:application/x-msexecl;name=\"{$filename}.xls\"");
	    header("Content-Disposition:inline;filename=\"{$filename}.xls\"");
	    $objWriter->save("php://output");
	}
}