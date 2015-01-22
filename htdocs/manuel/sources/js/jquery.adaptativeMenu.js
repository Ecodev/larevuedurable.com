

$.fn.extend({
	adaptativeMenu: function() {
		
		return this.each(function(i){
			$.adaptativeMenu.init($(this));
		});
	}
});


$.extend({
	adaptativeMenu: {

		menu : null,
		more : null,
		moreUl : null,
		init:function(el)
		{					
		
			var self = this;
			this.menu = el;
						
			this.formatMenu();
			$(window).on('resize', function()
			{
				self.formatMenu();
			});

		},

		formatMenu:function()
		{
			var self = this;
			
			// s'il n'y a pas de bouton +, on l'ajoute au début pour qu'il occupe sa propre place
			if( this.menu.find('.more').length == 0 )
			{
				this.more = $('<li class="more"><a class="cat-link" href="">✚&nbsp;&nbsp;Plus de pages</a><ul></ul></li>');
				this.moreUl = this.more.find('ul');
			}
			
			//this.more.prependTo(this.menu);

			this.more.find('ul:first > li').appendTo(this.menu);
			this.more.remove();
		
			if( this.menu.outerHeight() > this.menu.parent().outerHeight() ) // si déborde sans le bouton +
			{
				this.more.prependTo(this.menu);
				if(this.menu.outerHeight() > this.menu.parent().outerHeight()){
					this.addToMenu();
				}else{
					this.more.remove();
				}
				
			}

		},
		
		
		addToMenu:function()
		{
			var list = new Array();
			this.menu.parent().find('ul:first > li').each(function()
			{
				if( $(this).position().top > 0 )
				{
				 	list.push($(this));
				}
			});

			for(var i = 0; i < list.length; i++)
			{
				list[i].appendTo(this.moreUl);
			}
			this.more.appendTo(this.menu);
		}
			
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
});