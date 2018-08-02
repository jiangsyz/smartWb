//重置查询条件
$(".reset-search").click(function(){
	$("input[name='title']").val('');
	$("input[name='uniqueId']").val('');
	$("input[name='minPrice']").val('');
	$("input[name='maxPrice']").val('');
	$("input[name='minCount']").val('');
	$("input[name='maxCount']").val('');
	$("input[name='minDate']").val('');
	$("input[name='maxDate']").val('');
});

//验证筛选表单
$("#search-product-form").on("beforeSubmit", function (event) {  
	var minPrice = $("input[name='minPrice']").val();
 	var maxPrice = $("input[name='maxPrice']").val();
 	var minCount = $("input[name='minCount']").val();
 	var maxCount = $("input[name='maxCount']").val();
 	if (isNaN(minPrice) || isNaN(maxPrice)) {
 		krajeeDialog.alert('请输入正确的价格区间');
 		return false;
 	}
});  

