(function () {
	tinymce.create('tinymce.plugins.quote', {
		init:function (ed, url) {
			ed.addButton('quote', {
				title:'Add a Quote',
				image:url + '/blockquoteright.png',
				onclick:function () {
//					ed.selection.setContent('[quote]' + ed.selection.getContent() + '[/quote]');
					ed.selection.setContent('<blockquote class="right-quote">' + ed.selection.getContent() + '</blockquote>');
				}
			});
		},
		createControl:function (n, cm) {
			return null;
		}
	});
	tinymce.PluginManager.add('quote', tinymce.plugins.quote);
})();