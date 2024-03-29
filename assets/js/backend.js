function jSelectElement(field1,field2,id){
	//check if exists
	if(selectedElements.indexOf(parseInt(id))<0){
		selectedElements.push(parseInt(id));
		let header=jQuery('.elements_header');
		let list=jQuery('#elements_list');
		header.css('display','block');
		list.css('display','block');
		let html='<li class="bd_element" id="element_'+id+'">' +
			'<span class="element_name">'+field1+'</span>' +
			'<span className="element_selectable">' +
			'<input id="selectable_'+id+'" type="checkbox" name="selectable[]" value="'+id+'" checked/>'+
			'<label for="selectable_'+id+'">' +  Joomla.Text._('PLG_STOCKABLECUSTOMFIELDS_FIELD_SELECTABLE') + '</label>' +
			'</span>' +
			'<span class="element_type">'+field2+'</span>' +
			'<span class="element_id">'+id+'</span>' +
			'<input type="hidden" name="custom_id[]" value="'+id+'"/>';
		html+='<span class="bd_listtoolbar">';		
		html+='<span class="breakdesigns_btn element_move_btn" title="Drag to Move"><i class="bdicon-move"></i></span>';
		html+='<span class="breakdesigns_btn element_delete_btn" title="Remove"><i class="bdicon-cancel"></i></span>';
		html+='</span>';
		html+='</li>';
		list.append(html);
	}	
}

function removeProduct(id){	
	selectedElements = jQuery.grep(selectedElements, function( value ) {
		return  value!= id;
	});
	console.log(selectedElements);
	jQuery('#element_'+id).remove();
}

