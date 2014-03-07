


<script type="text/javascript" charset="utf-8">
	
	_gaq.push(
		['_setCustomVar', 2, 'Acheteur', 'Oui', 3]
	);
	
	//_gaq.push(['_setAccount', 'UA-XXXXX-X']);
	//_gaq.push(['_trackPageview']);
	_gaq.push(['_addTrans',
	  '{$gaorder->id}',           			// order ID - required
	  'Net-lead',  							// affiliation or store name
	  '{$gatotal}',          				// total - required
	  '0',           						// tax
	  '{$gashipping}',             			// shipping
	  '{$gadeliveryAddress->city}',       	// city
	  '',     								// state or province
	  '{$gadeliveryAddress->country}'    	// country
	]);

	{foreach from=$gaproducts item=product }
		_gaq.push(['_addItem',
		  '{$gaorder->id}',           			// order ID - required
		  '{$product.product_reference}',      	// SKU/code
		  '{$product.product_name}',      		// product name
		  '',   								// category or variation
		  '{$product.product_price_wt}',		// unit price - required
		  '{$product.product_quantity}'     	// quantity - required
		]);
		_gaq.push(['_trackTrans']);
		
		

	{/foreach}
	
	
	

	
	
	
</script>