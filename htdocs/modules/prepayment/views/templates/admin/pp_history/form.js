$(document).ready(function()
{
    $('#customer').typeWatch({
        captureLength: 1,
        highlight: true,
        wait: 150,
        callback: function() {
            searchCustomers();
        }
    });
});

function searchCustomers()
{
    var field = $('#id_customer');

    var filterValue = '';
    if ($('#customer').val())
        filterValue = $('#customer').val();

    $.ajax({
        type:"POST",
        url: adminUrl,
        async: true,
        dataType: "json",
        data : {
            ajax: "1",
            token: currentToken,
            tab: "AdminPPHistory",
            action: "searchCustomers",
            customer_search: filterValue},
        success : function(res)
        {
            field.empty();
            if ($('#customer_default_id').val() > 0)
            {
                field.append($('<option>', { value : $('#customer_default_id').val() }).text($('#customer_default_name').val()));
                field.append($('<option>', { value : '' }).text('--------------'));
            }
            if (!res.found)
            {
                field.append($('<option>', { value : '' }).text(noMatchFound));
                field.val('');
            }
            else
            {
                for (var i = 0; i < res.customers.length && i < 50; i++)
                    field.append($('<option>', { value : res.customers[i]["id_customer"] }).text(res.customers[i]["firstname"] + ' ' + res.customers[i]["lastname"]));
                field.val(res.customers[i - 1]["id_customer"]);
                if (res.customers.length >= 50)
                    field.append($('<option>', { value : '' }).text(tooMuchResult));
            }
        }
    });
}
