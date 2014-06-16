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


// ******************************************************************
// This function accepts a string variable and verifies if it is a
// proper date or not. It validates format matching either
// mm-dd-yyyy or mm/dd/yyyy. Then it checks to make sure the month
// has the proper number of days, based on which month it is.

// The function returns true if a valid date, false if not.
// ******************************************************************
function isDate(dateStr) {
	var datePat = /^(\d{1,2})(\/|-)(\d{1,2})(\/|-)(\d{4})$/;
	var matchArray = dateStr.match(datePat); // is the format ok?
	var error = '';

	if (matchArray == null) {
		error = "Please enter date as either mm/dd/yyyy or mm-dd-yyyy.";
		return false;
	}

	month = matchArray[1]; // p@rse date into variables
	day = matchArray[3];
	year = matchArray[5];

	if (month < 1 || month > 12) { // check month range
		error = "Month must be between 1 and 12.";
		return false;
	}

	if (day < 1 || day > 31) {
		error = "Day must be between 1 and 31.";
		return false;
	}

	if ((month==4 || month==6 || month==9 || month==11) && day==31) {
		error = "Month "+month+" doesn`t have 31 days!";
		return false;
	}

	if (month == 2) { // check for february 29th
		var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
		if (day > 29 || (day==29 && !isleap)) {
			error = "February " + year + " doesn`t have " + day + " days!";
			return false;
		}
	}

	return true; // date is valid
}