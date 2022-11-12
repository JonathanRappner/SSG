$(document).ready(() => {
	updateValues(state)
	updateElements(state)
})

/**
 * Uppdatera gruppernas genomsnittspoäng, totala antalet anmälda och totala antalet debriefs som är skrivna
 * @param {object} state 
 */
const updateValues = state => {

	state.totalSignups = 0
	state.totalDebriefs = 0

	state.groups.forEach(grp => {
		state.totalSignups += grp.signups.length
		state.totalDebriefs += _.filter(grp.signups, signup => signup.score > 0).length // hitta alla signups i gruppen med score > 0 och räkna
	})


}

/**
 * Uppdatera alla element med state:ets värden
 * @param {object} state 
 */
const updateElements = state => {
	
}