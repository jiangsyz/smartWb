
$(function(){
	//tab页切换
	var tab = $("#tab").val();
	var spuId = $("#spuId").val();
	var host = window.location.pathname;
	if (tab == 'spu' || tab == '') {
		$(".tab-spu").tab("show");
		$(".div-sku").hide();
		$(".div-spu").show();
	} else {
		$(".tab-sku").tab("show");
		$(".div-spu").hide();
		$(".div-sku").show();
	}
	//$(".tab-spu").tab("show");
	$(".tab-sku").click(function(){
		var url = host+'?r=product/update&id='+spuId+'&tab=sku';
		window.location.href = url;
	});
	$(".tab-spu").click(function(){
		var url = host+'?r=product/update&id='+spuId+'&tab=spu';
		window.location.href = url;
	});


	//添加规格
	$(".add-sku").click(function(){
		$.ajax({
			type: "get",
			url: "index.php?r=product/get-property-conf",
			data: {},
			dataType: "json",
			success: function(data){
				if (data.error!=0) {
					alert("获取属性名称失败");
					return false;
				} 
				var max = 0;
				$(".sku").each(function(){
					if (Number($(this).attr("cnt")) > max) {
						max = Number($(this).attr("cnt"));
					}
				});
				var cnt = max + 1;
				var html = '<tr class="sku" cnt="'+cnt+'">';
				html += '<td><input type="text" name="Sku['+cnt+'][title]" value="" class="form-bom sku-td"></td>';
				html += '<td><input type="text" name="Sku['+cnt+'][uniqueId]" value="" class="form-bom sku-td"></td>';
				html += '<td><select name="Sku['+cnt+'][propertyKey]" style="height:26px;width:137px;">'+data.option+'</select></td>';
				html += '<td><input type="text" name="Sku['+cnt+'][propertyVal]" value="" class="form-bom sku-td"></td>';
				//html += '<td><input type="text" name="Sku['+cnt+'][count]" value="" class="form-bom sku-td"></td>';
				html += '<td><input type="text" name="Sku['+cnt+'][price]" value="" class="form-bom sku-td"></td>';
				html += '<td><input type="text" name="Sku['+cnt+'][vprice]" value="" class="form-bom sku-td"></td>';
				html += '<td><a href="javascript:void(0)" onclick="deleteSkuTd('+cnt+')"><span class="glyphicon glyphicon-minus-sign" style="color: rgb(255, 0, 0);"></a></span></td>';
				html += '</tr>'
			  	$(".sku-list").append(html);
	        }
		});
	});


	//验证SKU表单
	$("#create-product-form").on("beforeSubmit", function (event) {  
	 	var success = true;
		$(".sku-td").each(function(){
			if ($(this).val() == '') {
				success = false;
			}
		});
		if (success == false) {
			$('.sku-list').parent('.form-group').removeClass('has-success').addClass('has-error');
			$(".sku-list").after('<p class="help-block help-block-error">商品规格 cannot be blank.</p>');
		} else {
			$('.sku-list').parent('.form-group').removeClass('has-error').addClass('has-success');
			$(".sku-list").find('.help-block-error').remove();
		}
		return success;
	});  


	var editor = UE.getEditor('detail');
	if ($("#old-detail").length) {
		editor.ready(function() {
	        editor.setContent($("#old-detail").val());
	    });
	}

	editor.addListener('blur',function(editor){
		validate();
	});
	
	//发布前删除某一行SKU规格  不知为何冒泡后没绑定事件  先用onclick代替
	/*$(".delete-sku").on("click", function(e){
		e.stopPropagation();
		$(this).parents('tr').remove();
	});*/
});

function validate()
{
	var flag = true;
	$.ajax({
		type: "post",
		async: false,
		url: "index.php?r=product/validate-detail",
		data: {detail: UE.getEditor('detail').getContent()},
		dataType: "json",
		success: function(data){
			$("#detail-error").remove();
			if (data.error != 0) {
				$("#detail").after(data.msg);
				flag = false;
			}
        }
	});
	return flag;
}

function deleteSkuTd(cnt)
{
	$(".sku[cnt="+cnt+"]").remove();
}


