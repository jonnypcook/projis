// jQuery File Tree Plugin
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// Visit http://abeautifulsite.net/notebook.php?article=58 for more information
//
// Usage: $('.fileTreeDemo').fileTree( options, callback )
//
// Options:  root           - root folder to display; default = /
//           script         - location of the serverside AJAX file to use; default = folder-tree.php
//           folderEvent    - event to trigger expand/collapse; default = click
//           expandSpeed    - default = 500 (ms); use -1 for no animation
//           collapseSpeed  - default = 500 (ms); use -1 for no animation
//           expandEasing   - easing function to use on expand (optional)
//           collapseEasing - easing function to use on collapse (optional)
//           multiFolder    - whether or not to limit the browser to one subfolder at a time
//           loadMessage    - Message to display while initial tree loads (can be HTML)
//
// History:
//
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// TERMS OF USE
// 
// This plugin is dual-licensed under the GNU General Public License and the MIT License and
// is copyright 2008 A Beautiful Site, LLC. 
//
if(jQuery) (function($){
	$.extend($.fn, {
		fileAjax: function(o, fn_a, fn_b) {
			// configuration parameters
			if( !o ) var o = {};
			if( o.url == undefined ) throw "no url set";
			if( fn_b == undefined ) fn_b = function(){ alert('moo'); }
			
			// DOM #1 (Folder panel) create
			var dom = {
				pause: false
			};
			
			n = 'f' + Math.floor(Math.random() * 99999);
			i = jQuery('<iframe>',{
				id:		n,
				name:	n
			})
			.css({visibility: 'hidden', width: '0px', height: '0px'})
			.attr('src','about:blank');
			
			$(this).after(i);

			i.bind('load', function(){
				data = $('#'+n).contents().find('pre').text();
				//console.log(data);
				fn_a(data);
				
			});

			$(this).attr('target', n);
			$(this).attr('action',o.url);
			$(this).bind('submit', fn_b);

		}
	});
	
})(jQuery);