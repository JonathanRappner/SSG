$(document).ready(function () {

	// uppdatera roller vid start och när grupp-select:en ändras
	updateRoles()
	$('#group').change(() => updateRoles())

	// Betygsstjärnor
	$('.star').mouseenter(e => hoverStar($(e.target).data('star_number') - 0)) // hover
	$('.star-container').mouseleave(() => clearHoverStars()) // rensa hover och sätt till det sparade betyget
	$('.star').click(e => $('input[name=score]').val($(e.target).data('star_number') - 0)) // sätt betyget
})

/**
 * Uppdatera roll-select:en så att den endast innehåller roller som tillhör gruppen enligt group_roles.
 */
const updateRoles = () => {
	const group_id = $('#group').val() // vald grupp-id

	// Skapa sträng med <option>:s
	let optionsString = ''
	group_roles.forEach(gr => { // gå igenom alla grupp-roller
		if (gr.group_id === group_id) { // lista bara roller som passar gruppen (gr.group_id är en sträng)
			const nameLong = gr.name_long
				? ` (${gr.name_long})`
				: ''
			const selected = gr.role_id - 0 === signup_role
				? ' selected'
				: ''

			optionsString += `<option value='${gr.role_id}'${selected}>${gr.name}${nameLong}</option>`
		}
	})
	$('#role').html(optionsString)
}


/**
 * Highlightar hovrade stjärnan och alla mindre värda stjärnor ( ͡° ͜ʖ ͡°)
 * @param {number} number Stjärna 1, 2, 3, 4 eller 5
 */
 const hoverStar = number => {
	$('.star').removeClass('highlight')

	for (let i = 1; i <= number; i++) { // iterera upp till den hovrade stjärnan
		$(`.star[data-star_number=${i}]`).addClass('highlight')
	}
}

/**
 * Rensa hover-effekter och sätt
 */
const clearHoverStars = () => {
	const score = $('input[name=score]').val() // hämta sparade betyget
	$('.star').removeClass('highlight') // rensa alla highlights

	// om betyg är satt sedan tidigare: sätt highlights
	if (score !== '') {
		hoverStar(score)
	}
}