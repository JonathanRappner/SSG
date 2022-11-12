$(document).ready(() => {
	updateValues(state)
	updateElements(state)
})

/**
 * Uppdatera gruppernas genomsnittspoäng, totala antalet anmälda och totala antalet debriefs som är skrivna
 * @param {object} state 
 */
const updateValues = state => {

	// skapa variabler ifall de inte finns
	state.totalSignups = 0
	state.totalDebriefs = 0
	state.member_has_debriefed = false

	// varje grupp
	state.groups.forEach(grp => {
		state.totalSignups += grp.signups.length // anmälningar
		state.totalDebriefs += _.filter(grp.signups, signup => signup.score > 0).length // hitta alla signups i gruppen med score > 0 och räkna

		if (grp.signups.some(signup => signup.id - 0 === member_id - 0 && signup.score > 0)) {
			state.member_has_debriefed = true
		}
	})
}

/**
 * Uppdatera alla element med state:ets värden
 * @param {object} state 
 */
const updateElements = state => {

	// -- Göm/visa alerts och knappar --
	if (state.signup_attendance_id === 0) { // inloggade användaren har ingen signup till detta event
		$('#alert_no_signup').removeClass('d-none')
		$('#alert_negative_signup').addClass('d-none')
	} else if (state.signup_attendance_id > 3) { // Visa "negativ anmälan"-alert
		$('#alert_no_signup').addClass('d-none')
		$('#alert_negative_signup').removeClass('d-none')

		$('#alert_negative_signup span').className = ''
		$('#alert_negative_signup span')
			.html(state.signup_attendance_name)
			.addClass(attendance_classes[state.signup_attendance_id])
	} else if (state.member_has_debriefed) { // Redigera debrief-knapp
		$('#btn_form')
			.removeClass('d-none')
			.removeClass('d-none btn-success')
			.addClass('btn-primary')
		$('#btn_form').html('Redigera din debrief <i class="fas fa-pen"></i>')
	} else { // Ny debrief-knapp
		$('#btn_form')
			.removeClass('d-none')
			.removeClass('d-none btn-primary')
			.addClass('btn-success')
		$('#btn_form').html('Skriv din debrief <i class="fas fa-chevron-right">')
	}


	// -- text/värden --
	let debriefs_count = 0
	let signups_count = 0
	let total_score = 0
	state.groups.forEach(grp => {
		debriefs_count += grp.signups.filter(signup => signup.score > 0).length
		signups_count += grp.signups.length
		total_score += _.sumBy(grp.signups, signup => signup.score)
	})
	let average_score = Math.round((total_score / debriefs_count) * 10) / 10 // avrunda till en decimal
	average_score = (average_score+'').replace('.', ',') // komma som decimaltecken

	// Antalet skrivna debriefs / antal anmälningar
	$('#value_total_debriefs').html(`${debriefs_count} / ${signups_count}`)

	// Genomsnittbetyg
	$('#value_average_score').html(average_score)

}