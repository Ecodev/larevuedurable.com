	tinyMCE.init({
		mode : "specific_textareas",
		theme : "advanced",
		skin:"cirkuit",
		editor_selector : "rte",
		editor_deselector : "noEditor",
		plugins : "safari,pagebreak,style,table,advimage,advlink,inlinepopups,media,contextmenu,paste,fullscreen,xhtmlxtras,preview",
		// Theme options
        theme_advanced_buttons1 : "bold,italic,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,blockquote,|,styleselect,formatselect",
        theme_advanced_buttons2 : "undo,redo,|,removeformat,|,pasteword,|,search,replace,link,unlink,image,|,fullscreen,code",
        theme_advanced_buttons3 : "",
        theme_advanced_buttons4 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : false,
        content_css : pathCSS+"editor.css",
		document_base_url : ad,
        width: "580",
        height: "400",
        font_size_style_values : "8pt, 10pt, 12pt, 14pt, 18pt, 24pt, 36pt",
		elements : "nourlconvert,ajaxfilemanager",
		file_browser_callback : "ajaxfilemanager",
		entity_encoding: "raw",
		convert_urls : false,
        language : iso

	});

	function ajaxfilemanager(field_name, url, type, win) {
		var ajaxfilemanagerurl = ad+"/ajaxfilemanager/ajaxfilemanager.php";
		switch (type) {
			case "image":
				break;
			case "media":
				break;
			case "flash":
				break;
			case "file":
				break;
			default:
				return false;
	}
    tinyMCE.activeEditor.windowManager.open({
        url: ajaxfilemanagerurl,
        width: 782,
        height: 440,
        inline : "yes",
        close_previous : "no"
    },{
        window : win,
        input : field_name
    });
}
