$(document).ready(function(){
	$('.sortable').crudSortable();
});

$.fn.crudSortable = function(){
	return this.each(function(){
		var container = $(this);

		if (container.data('crudSortable')) {
			return true;
		}
		container.data('crudSortable', true);

		var params = {
			axis:'y',
			cursor:'move',
			items:'li',
			handle:'.glyphicon-move',
			stop:function(){
				var weights = [];
				container.find('li').each(function(index){
					var parent = $(this).parents('li:first');
					if (parent.length) {
						parent = parent.attr('data-id');
					} else {
						parent = 0;
					}
					if ($(this).find('ul > li').length) {
						$(this).find('.removeAction').hide();
					} else {
						$(this).find('.removeAction').show();
					}
					weights[weights.length] = {
						id:$(this).attr('data-id'),
						weight:index,
						parent:parent
					};
				});
				$.ajax({
					data:{
						weights:weights
					},
					url:window.location.href.toString(),
					type:'POST'
				});
			}
		};

		if (container.hasClass('nested')) {
			container.nestedSortable($.extend(params, {
				listType:'ul',
				toleranceElement:'.glyphicon-move',
				axis:false
			}));
		} else {
			container.sortable(params);
		}

	});
};