//重置查询条件
$(".reset-search").click(function(){
	$("input[name='code']").val('');
    $("input[name='phone']").val('');
	$("input[name='minDate']").val('');
	$("input[name='maxDate']").val('');
    $("select option").eq(0).attr("selected",true);
});

$(".agree-refund").click(function(){
    var id = $(this).attr("data-id");
    krajeeDialog.confirm("确认退款吗？", function (result) {
        if (result) {
            $.ajax({
                type: "post",
                url: "index.php?r=refund/update-refund-status",
                data: {id:id, status:1},
                dataType: "json",
                success: function(data){
                    /*if (data.error == 0) {
                        krajeeDialog.alert('操作成功');
                        location.reload();
                    } else {
                        krajeeDialog.alert('操作失败');
                    }*/
                }
            });
        }
    });
});


$(".reopen").click(function(){
    var id = $(this).attr("data-id");
    krajeeDialog.confirm("确认重新激活退款状态吗？", function (result) {
        if (result) {
            $.ajax({
                type: "post",
                url: "index.php?r=refund/update-refund-status",
                data: {id:id, status:0},
                dataType: "json",
                success: function(data){
                    /*if (data.error == 0) {
                        krajeeDialog.alert('操作成功');
                        location.reload();
                    } else {
                        krajeeDialog.alert('操作失败');
                    }*/
                }
            });
        }
    });
});

$(".reset").click(function(){
    var id = $(this).attr("data-id");
    krajeeDialog.confirm("确认重置退款吗？", function (result) {
        if (result) {
            $.ajax({
                type: "post",
                url: "index.php?r=refund/update-refund-status",
                //重置用9表示
                data: {id:id, status:9},
                dataType: "json",
                success: function(data){
                    /*if (data.error == 0) {
                        krajeeDialog.alert('操作成功');
                        location.reload();
                    } else {
                        krajeeDialog.alert('操作失败');
                    }*/
                }
            });
        }
    });
});





