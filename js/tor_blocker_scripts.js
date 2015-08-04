function checkAll(obj) {
	checkboxes = document.getElementsByName('selected[]');

	if ( obj.innerText == 'Check All' ) {	
		obj.innerText = 'Uncheck All';
		for( var i=0; i<checkboxes.length; i++ ) {
			checkboxes[i].checked = 'checked';
		}
	} else {
		obj.innerText = 'Check All';
		for( var i=0; i<checkboxes.length; i++ ) {
			checkboxes[i].checked = '';
		}
	}
}