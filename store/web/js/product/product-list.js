
//SPU上下架 锁定解锁
function closeLockSpu(field, updateVal, id)
{
	console.log(id);
	$.ajax({
		type: "get",
		url: "index.php?r=product/close-lock-spu",
		data: {id:id, updateVal:updateVal, field:field},
		dataType: "json",
		success: function(data){
			if (data.error == 0) {
				krajeeDialog.alert('操作成功');
				setTimeout("location.reload()", 1000);
			} else {
				krajeeDialog.alert('操作失败');
			}
        }
	});
}

//SKU上下架 锁定解锁
function closeLockSku(field, id)
{
	$.ajax({
		type: "get",
		url: "index.php?r=product/close-lock-sku",
		data: {id:id, field:field},
		dataType: "json",
		success: function(data){
			if (data.error == 0) {
				krajeeDialog.alert('操作成功');
				setTimeout("location.reload()", 1000);
			} else {
				krajeeDialog.alert('操作失败');
			}
        }
	});
}

$(function(){
	$("#batch-close").click(function(){
		//var ids = $(".grid-view").yiiGridView("getSelectedRows");
		var ids = new Array();
		$("input[name='id[]']").each(function(){
			if ($(this).is(':checked')) {
				ids.push($(this).val());
			}
		});
		closeLockSpu('closed', 1, ids);
	});
	$("#batch-open").click(function(){
		var ids = new Array();
		$("input[name='id[]']").each(function(){
			if ($(this).is(':checked')) {
				ids.push($(this).val());
			}
		});
		closeLockSpu('closed', 0, ids);
	});
});




