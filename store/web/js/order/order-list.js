//重置查询条件
$(".reset-search").click(function(){
    $("input[name='id']").val('');
	$("input[name='code']").val('');
    $("input[name='phone']").val('');
	$("input[name='title']").val('');
	$("input[name='minDate']").val('');
	$("input[name='maxDate']").val('');
});


//导出
$("#export").click(function(){
    $(this).parents(".modal-content").find(".close").click();
    var code = $("input[name='code']").val();
    var title = $("input[name='title']").val();
    var minDate = $("input[name='minDate']").val();
    var maxDate = $("input[name='maxDate']").val();
    $("#export-dropzone").find(".hid").remove();
    $("#export-dropzone").append('<input type="hidden" class="hid" name="code" value="'+code+'">');
    $("#export-dropzone").append('<input type="hidden" class="hid" name="title" value="'+title+'">');
    $("#export-dropzone").append('<input type="hidden" class="hid" name="minDate" value="'+minDate+'">');
    $("#export-dropzone").append('<input type="hidden" class="hid" name="maxDate" value="'+maxDate+'">');
    //$("#export-dropzone").attr('action', 'index.php?r=order/export-order-for-delivery&id='+id+'&title='+title+'&minDate='+minDate+'&maxDate='+maxDate);
    //alert('index.php?r=order/export-order-for-delivery&id='+id+'&title='+title+'&minDate='+minDate+'&maxDate='+maxDate);
    //console.log($("#export-dropzone").attr('action'));
    $("#export-dropzone").submit();
});


//关闭订单
$(".refund-all").on("click", function() {
    var oid = $(this).attr("data-oid");
    krajeeDialog.confirm("确认要退整笔订单吗？", function (result) {
        if (result) {
            $.ajax({
                type: "get",
                url: "index.php?r=order/refund-all",
                data: {id:oid},
                dataType: "json",
                success: function(data){
                    if (data.error == 0) {
                        krajeeDialog.alert('操作成功');
                    } else {
                        location.reload();
                        //krajeeDialog.alert('操作失败');
                    }

                }
            });
        }
    });
});


//修改收货信息-省市区联动
$("div").on("change", "#sct-province, #sct-city", function(e){
    e.stopPropagation();
    e.preventDefault();
    //var areaId = $('#sct-province option:selected').val();//选中的值
    var areaId = $(this).find("option:selected").val();//选中的值
    $.ajax({
        type: "get",
        url: "index.php?r=order/get-child-area",
        data: {areaId:areaId},
        dataType: "json",
        success: function(data){
            if (data.error == 0) {
                if (data.cityAreas.length > 0) {
                    //加载市级地区
                    $("#sct-city").empty();
                    var option = '';
                    $.each(data.cityAreas, function(i, val){
                        option += '<option value="'+val.area_id+'">'+val.area_name+'</option>';
                    });
                    console.log(option);
                    $("#sct-city").append(option);
                }
                
                //加载区
                $("#sct-district").empty();
                var option = '';
                $.each(data.districtAreas, function(i, val){
                    option += '<option value="'+val.area_id+'">'+val.area_name+'</option>';
                });
                $("#sct-district").append(option);
            } else {
                //location.reload();
                krajeeDialog.alert('操作失败');
            }
        }
    });
});

$("div").on("click", "#address-submit", function(e){
    e.stopPropagation();
    e.preventDefault();
    var oid = $("#oid").val();
    var name = $("#name").val();
    var phone = $("#phone").val();
    var areaId = $("#sct-district").find("option:selected").val();//选中的区ID
    var address = $("#address").val();
    var province = $("#sct-province").find("option:selected").text();
    var city = $("#sct-city").find("option:selected").text();
    var district = $("#sct-district").find("option:selected").text();
    if (oid.length==0 || name.length==0 || phone.length==0 || address.length==0) {
        krajeeDialog.alert('必填项不能为空');
        return false;
    }
    var re = /^1\d{10}$/
    if (!re.test(phone)) {
        krajeeDialog.alert('请输入正确的手机号');
        return false;
    } 
    $.ajax({
        type: "post",
        url: "index.php?r=order/modify-address",
        data: {oid:oid, name:name, phone:phone, areaId:areaId, address:address, province:province, city:city, district:district},
        dataType: "json",
        success: function(data){
            if (data.error == 0) {
                $(".modal-header .close").click();
                krajeeDialog.alert('操作成功');
                $("#"+oid+"_address").html(data.newAddress);
            } else {
                //location.reload();
                krajeeDialog.alert('操作失败');
            }
        }
    });


});


$(function(){
	Dropzone.autoDiscover = false;
	/*var myDropzone = new Dropzone("#my-awesome-dropzone", {
        url: "/admin/upload",//文件提交地址
        method:"post",  //也可用put
        paramName:"file", //默认为file
        maxFiles:1,//一次性上传的文件数量上限
        maxFilesize: 2, //文件大小，单位：MB
        acceptedFiles: ".jpg,.gif,.png,.jpeg", //上传的类型
        addRemoveLinks:true,
        parallelUploads: 1,//一次上传的文件数量
        //previewsContainer:"#preview",//上传图片的预览窗口
        dictDefaultMessage:'拖动文件至此或者点击上传',
        dictMaxFilesExceeded: "您最多只能上传1个文件！",
        dictResponseError: '文件上传失败!',
        dictInvalidFileType: "文件类型只能是*.jpg,*.gif,*.png,*.jpeg。",
        dictFallbackMessage:"浏览器不受支持",
        dictFileTooBig:"文件过大上传文件最大支持.",
        dictRemoveLinks: "删除",
        dictCancelUpload: "取消",
        init:function(){
        	alert('mb');
            this.on("addedfile", function(file) {
                //上传文件时触发的事件
                document.querySelector('div .dz-default').style.display = 'none';
            });
            this.on("success",function(file,data){
                //上传成功触发的事件
                console.log('ok');
                angular.element(appElement).scope().file_id = data.data.id;
            });
            this.on("error",function (file,data) {
                //上传失败触发的事件
                console.log('fail');
                var message = '';
                //lavarel框架有一个表单验证，
                //对于ajax请求，JSON 响应会发送一个 422 HTTP 状态码，
                //对应file.accepted的值是false，在这里捕捉表单验证的错误提示
                if (file.accepted){
                    $.each(data,function (key,val) {
                        message = message + val[0] + ';';
                    })
                    //控制器层面的错误提示，file.accepted = true的时候；
                    alert(message);
                }
            });
            this.on("removedfile",function(file){
                //删除文件时触发的方法
                var file_id = angular.element(appElement).scope().file_id;
                if (file_id){
                    $.post('/admin/del/'+ file_id,{'_method':'DELETE'},function (data) {
                        console.log('删除结果:'+data.message);
                    })
                }
                angular.element(appElement).scope().file_id = 0;
                document.querySelector('div .dz-default').style.display = 'block';
            });
        }
    });*/

	//文件导入
    Dropzone.options.myAwesomeDropzone = {
        autoProcessQueue: false,
        uploadMultiple: false,
        parallelUploads: 100,
        acceptedFiles: ".xls,.xlsx", //上传的类型
        maxFiles: 1,
        method: "post",
        url: 'index.php?r=order/import-order-logistics',
        // Dropzone settings
        init: function () {
        	alert('wzy');
            var myDropzone = this;
            this.element.querySelector("button[id=uploads]").addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                myDropzone.processQueue();
            });
            this.on("sendingmultiple", function () {
            });
            this.on("sending", function (file, xhr, formData) {
            	console.log(123);
                // Will send the filesize along with the file as POST data.
                //formData.append("type", $('#uploads').attr('data-val'));
            });
            this.on("success", function (files, response) {
                var data = JSON.parse(response);
                console.log(data.error);
                if (data.error != 0) {
                    //swal(data.message, "", "error");
                    toastr.error(data.message);
                    myDropzone.removeFile(files);
                } else {
                    toastr.error('导入成功');
                }
            });
            this.on("error", function (files, response) {
                toastr.error('网络异常,请稍候重试');
                myDropzone.removeFile(files);
            });
        }
    }
});


//SPU上下架 锁定解锁
function closeLockSpu(field, obj, id)
{
	$.ajax({
		type: "get",
		url: "index.php?r=product/close-lock-spu",
		data: {id:id, field:field},
		dataType: "json",
		success: function(data){
			if (data.error == 0) {
				krajeeDialog.alert('操作成功');
				location.reload();
			} else {
				krajeeDialog.alert('操作失败');
			}
        }
	});
}

function validate()
{
    var regEn = /[`~!@#$%^&*()_+<>?:"{},.\/;'[\]]/im;
    var regCn = /[·！#￥（——）：；“”‘、，|《。》？、【】[\]]/im;
    var code = $("input[name='code']").val();
    var phone = $("input[name='phone']").val();

    if (isNaN(code)) {
        krajeeDialog.alert("订单编号必须是数字");
        return false;
    }
    if (isNaN(phone)) {
        krajeeDialog.alert("手机号必须是数字");
        return false;
    }

    /*if(regEn.test(code) || regCn.test(code)) {
        krajeeDialog.alert("名称不能包含特殊字符");
        return false;
    }*/
}



