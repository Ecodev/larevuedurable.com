function setPrepayment()
{
    var value = parseInt($('input[name=ppproduct]:checked').val());
    $.ajax({
        type:"POST",
        url: ppproductUrl,
        async: true,
        dataType: "json",
        data : {
            ajax: "1",
            token: ppproductToken,
            tab: "AdminPPProduct",
            action: "setProduct",
            value: value,
            id_product: productId
        },
        success : function(res) {}
    });

    if (value)
        $('#pp_reduction').show('fast');
    else
        $('#pp_reduction').hide('fast');
}

function setReduction()
{
    $.ajax({
        type:"POST",
        url: ppproductUrl,
        async: true,
        dataType: "json",
        data : {
            ajax: "1",
            token: ppproductToken,
            tab: "AdminPPProduct",
            action: "setReduction",
            value: $('#pp_reduction_amount').val(),
            id_product: productId
        },
        success : function(res) {}
    });
}

function setReductionType()
{
    $.ajax({
        type:"POST",
        url: ppproductUrl,
        async: true,
        dataType: "json",
        data : {
            ajax: "1",
            token: ppproductToken,
            tab: "AdminPPProduct",
            action: "setReductionType",
            value: $('#pp_reduction_type').val(),
            id_product: productId
        },
        success : function(res) {}
    });
}
