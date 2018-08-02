function closeLockCard(field, updateVal, id)
{
    console.log(id);
    $.ajax({
        type: "get",
        url: "index.php?r=card/close-lock-card",
        data: {id:id, updateVal:updateVal, field:field},
        dataType: "json",
        success: function(data){
            if (data.error == 0) {
                krajeeDialog.alert('操作成功');
                setTimeout("location.reload()", 2000);
            } else {
                krajeeDialog.alert('操作失败');
            }
        }
    });
}
