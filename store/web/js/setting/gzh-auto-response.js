
$(function(){
	//添加规格
	$(".add").click(function(){
		var max = 0;
		$(".response").each(function(){
			if (Number($(this).attr("cnt")) > max) {
				max = Number($(this).attr("cnt"));
			}
		});
		var cnt = max + 1;
		var html = '<tr class="response" cnt="'+cnt+'">';
		html += '<td><input type="text" name="WechatConf['+cnt+'][confKey]" value="" class="form-bom response-td"></td>';
		html += '<td><input type="text" name="WechatConf['+cnt+'][confVal]" value="" class="form-bom response-td"></td>';
		html += '<td><a href="javascript:void(0)" onclick="deleteSkuTd('+cnt+')"><span class="glyphicon glyphicon-minus-sign" style="color: rgb(255, 0, 0);"></a></span></td>';
		html += '</tr>'
	  	$(".response-list").append(html);
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

});

function deleteSkuTd(cnt)
{
	if ($(".response[cnt="+cnt+"]").children().length == 4) {
		var id = $(".response[cnt="+cnt+"]").find('input').val();
		$.ajax({
			type: "get",
			url: "index.php?r=setting/delete-gzh-auto-response",
			data: {id, id},
			dataType: "json",
			success: function(data){
				if (data.error!=0) {
					alert("删除失败");
					return false;
				}
	        }
		});
	}
	$(".response[cnt="+cnt+"]").remove();
}


