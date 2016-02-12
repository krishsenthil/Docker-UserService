/**
 * Javascript file for wysiwyg editor
 *
 * Collash Inc Internal
 *
 * @category   framework
 * @package    Plugin
 * @subpackage Plugin
 * @copyright  Copyright (c) Collash Inc
 * @version    0.1
 * @license    Collash Inc
 * @author Vijay <vbose@Collash.com>
 * @date Sept 10, 2013
 */
var Noobh = Noobh || {};
Noobh.fw = Noobh.fw || {};
Noobh.fw.editor = {
		textArea : '',
		displayArea : '',
		isHtml : true,
		init: function (id) {
			this.textArea = jQuery('#'+id);
			this.displayArea = jQuery('#display_'+id);
			this.isHtml = jQuery(Noobh.fw.editor.displayArea).attr('ishtml');
			//Bind event handeler for diplay change
			jQuery( this.textArea ).on( "keyup", function(e) {
				var content = Noobh.fw.editor.textArea.val();
				if(Noobh.fw.editor.isHtml != true){
					//Display as plain text, convert "\r" and "\n" as breaks
					content = content.replace(/</ig,'&lt;');
					content = content.replace(/>/ig,'&gt;');
					var reg = new RegExp('\\n',"g");
					content = content.replace(reg,'<br/>');
				}
				Noobh.fw.editor.displayArea.html(content);
			});
		},
		
		getEditorContent : function(){
			return Noobh.fw.editor.textArea.val();
		},
		
		getDisplayContent : function(){
			return Noobh.fw.editor.displayArea.html();
		},
		
		getIsHtml : function(){
			return Noobh.fw.editor.isHtml;
		},
		
		setIsHtml : function (status){
			Noobh.fw.editor.isHtml = (status)? true : false;
		}
};
