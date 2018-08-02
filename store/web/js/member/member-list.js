//重置查询条件
$(".reset-search").click(function(){
	$("input[name='id']").val('');
	$("input[name='phone']").val('');
	$("input[name='minDate']").val('');
	$("input[name='maxDate']").val('');
});