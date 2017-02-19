/*
Rating plugin for Typsetter ÑMS
Author: a2exfr
http://my-sitelab.com/
Version 1.0.1 */

function gp_init_inline_edit(area_id,section_object){
	
	loaded();
	gp_editing.editor_tools();
	var edit_div = gp_editing.get_edit_area(area_id);
	var cache_value = '';
	
	gp_editor = {
		save_path: gp_editing.get_path(area_id),

		destroy:function(){},
		
		checkDirty         : function() { 
						
				var curr_val = gp_editor.gp_saveData();
				if( curr_val != cache_value ){
					gp_editor.updatesect();
					return true;
				}
				return false;
				
				},
				
				
		resetDirty         : function() {
			cache_value = gp_editor.gp_saveData();
		},
		
		gp_saveData:function(){
					
			
			var options_my = $('#gp_my_options').find('input,select').serialize();
			return '&'+options_my;
						
		},
		intervalSpeed: function() {},
		updatesect: function() {},
		updateElement:function(){}
	}// gpeditor --end
	
	
	var option_area = $('<div id="gp_my_options"/>').prependTo('#ckeditor_controls');
	
	  var option_messages = $(
	'<div id="option_message">' +
	
	
	'<div class="a_box">'+
	'<div class="a_line">'+
	'<label class="switch"><input type="checkbox" name="showrate" value="showrate" id="showrate"/><div class="slider round"></div></label>'+
	'<p>Show rating value? </p>'+
	'</div>'+
	'</div>'+
	
	'<input  type="hidden" step="1" name="rate_id" value="'+section_object.rate_id+'" class="a_inp"  />'+

	'<p>Number of stars <br/>'+
     '<input  type="number" step="1" name="starnum" value="'+section_object.starnum+'" class="a_inp"  />'+
     '</p>'+

	'</div>'+
	'<div class="a_box">'+
	'<p><i>Made by Sitelab</i></p>'+
	'<a id="stl" href="http://my-sitelab.com/" target="_blank"><img alt="Sitelab" src="'+section_object.addonRelativeCode+'/img/st_logo.jpg"  /></a>'+
	'<p class="stl">We can help with the development of the beautiful site of any complexity</p>'+
	'</div>'
  ).appendTo(option_area);
 
  	 if(section_object.showrate){
		$('#gp_my_options').find('#showrate').prop('checked', true);
	 }

	gp_editor.updatesect = function() {
	var href = jPrep(window.location.href) + '&cmd=refresh_rating_section' + '&my_value=' + gp_editor.gp_saveData();
	$.getJSON(href, ajaxResponse);
	}
	
	$gp.response.refresh_respond_rating_section = function(arg) {
        var div_data = arg.CONTENT;
        edit_div.html(div_data);
			$('.rateit').rateit({
				resetable: false
			});
	}
}




