/**
 * js function
 *
 * @filesource
 * @package    motte
 * @subpackage view
 * @version    1.0
 * @license    http://opensource.org/licenses/gpl-license.php GPL - GNU Public license
 * @author     Pablo Erartes (pabloeuy@gmail.com) /
 *             Pedro Lindiman (plindiman@gmail.com)
 * @link       http://motte.codigolibre.net Motte Website
 */

// No empty
function mteNotNull(frm) {
	var error = new Array();
		
	$.each($(frm+' .mteNoEmpty'), function() {		
		var id = $(this).attr('id');
		if ($('#'+id).val() == '' ) {
			error.push(id)
		}
	});

	return error;
}

// No Select
function mteNotSelect(frm) {
	var errorSelect = new Array();

	$.each($(frm+' .mteNoSelect'), function() {
		var id = $(this).attr('id');
		if ($('#'+id).val() == 0 ) {
			errorSelect.push(id)
		}
	});

	return errorSelect;
}


function isDate(date){
	var  ExpRegDate = /^(3[01]|0?[1-9]|[12]d)/(0?[1-9]|1[012])/d{4}/;

    var res=true;
    if (!ExpRegDate.test(date) ){
    	res=false;
	}
    return res;
}