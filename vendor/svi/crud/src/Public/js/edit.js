$(document).ready(function(){
	$('input[data-file]').fileInput();
});

$.fn.fileInput = function(){
	return this.each(function(){
		var input = $(this);

		if (input.data('fileInput')) {
			return true;
		}
		input.data('fileInput', true);

		var file = input.attr('data-file');
		if (file) {
			var image = input.attr('data-image');
			var deleteName = input.attr('data-delete');
			var deleteElement = deleteName ? '<b title="Удалить" class="glyphicon glyphicon-remove"></b>' : '';

			if (image) {
				var fileDiv = $('<div class="fileImage"><a target="_blank" href="' + file + '"><img src="' + image + '"/></a> ' + deleteElement + '</div>');
				input.after(fileDiv);
			} else {
				var fileDiv = $('<div class="fileFile"><a target="_blank" href="' + file + '">' + $.getFileName(file) + '</a> ' + deleteElement + '</div>');
				input.after(fileDiv);
			}

			fileDiv.find('.glyphicon-remove').click(function(){
				fileDiv.remove();
				input.parents('form:first').find('input[name="' + 'deletefile_' + input.attr('name') + '"]').val(1);
			});
		}
	});
};

$.getFileName = function(path){
	var fileNameIndex = path.lastIndexOf("/") + 1;
	return path.substr(fileNameIndex);
};