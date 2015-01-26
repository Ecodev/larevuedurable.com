
<script type="text/javascript">

    {literal}
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    {/literal}

    ga('create', '{$ganalytics_id}', '{$ganalytics_domain}', {ldelim}siteSpeedSampleRate: 5{rdelim});
    ga('send', 'pageview');

    {if $isOrder eq true}
        ga('require', 'ecommerce', 'ecommerce.js');
        ga('ecommerce:addTransaction', {
            'id': '{$trans.id}',				// Transaction ID. Required.
            'affiliation': '{$trans.store}',    // Affiliation or store name.
            'revenue': '{$trans.total}',		// Grand Total.
            'shipping': '{$trans.shipping}',	// Shipping.
            'tax': '{$trans.tax}',			    // Tax.
            'currencyCode': '{$trans->currency}'// local currency code.
        });
        {foreach from=$items item=item}
            ga('ecommerce:addItem', {
                'id': '{$item.OrderId}',	    // Transaction ID. Required.
                'name': '{$item.Product}',      // Product name. Required.
                'sku': '{$item.SKU}',		    // SKU/code.
                'category': '{$item.Category}', // Category or variation.
                'price': '{$item.Price}',		// Unit price.
                'quantity': '{$item.Quantity}'	// Quantity.
            });
        {/foreach}
        ga('ecommerce:send');
        ga('set', 'currencyCode', '{$trans->currency}');
    {/if}
</script>

