/*
 * HTML-form-validering
*/

/**
 * Matchar inputs value mot regex-mönster.
 * Sätter eller tar bort .invalid-klass.
 * Returnerar true eller false.
 * @param {element} input Input-element
 * @param {regex} pattern Regex-sträng
 * @param {boolean} empty_allowed Får input-elementets värde vara tomt?
 * @returns {boolean} Är valid?
 */
function validate_regex(input, pattern, empty_allowed)
{
	if((empty_allowed && $(input).val().length <= 0) || $(input).val().match(pattern) != null) //(tomma värden tillåtna och värdet är tomt) eller regex-matchar
	{
		//valid
		$(input).removeClass("invalid");
		return true;
	}
	else
	{
		//invalid
		$(input).addClass("invalid");
		return false;
	}
}