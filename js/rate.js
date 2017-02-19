$('.rateit').rateit({
    resetable: false
});
 $(".rateit").bind('over', function (event,value) { $(this).attr('title', value); });
 
 $('.rateit').bind('rated reset', function (e) {
		var ri = $(this);
		var value = ri.rateit('value');
		var ratetid = ri.data('ratetid'); 
		
		ri.rateit('readonly', true);
		
		var href = jPrep(window.location.href) + '&cmd=rating_section' + '&ratetid=' + ratetid + '&rate_value=' + value;
		$.getJSON(href, ajaxResponse);
	
	 }) 
	 
	$gp.response.respond_rating_section = function(arg) {
		$('#'+arg.CONTENT.ratetid).rateit('value', arg.CONTENT.current_rating);
		$('#'+arg.CONTENT.ratetid).find('.current_rate').html('Your vote has been counted!');
	   
	} 
