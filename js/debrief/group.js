$(document).ready(() => {
	updateValues(state)
	updateElements(state)
})

/**
 * Uppdatera gruppernas genomsnittsbetyg, totala antalet anmälda och totala antalet debriefs som är skrivna
 * @param {object} state 
 */
const updateValues = state => {
	// skapa variabler ifall de inte finns
	state.totalSignups = 0
	state.totalDebriefs = 0

	// länkar
	state.signups.forEach(s => {
		s.review_good = linksHtml(s.review_good)
		s.review_bad = linksHtml(s.review_bad)
		s.review_improvement = linksHtml(s.review_improvement)
		s.review_tech = linksHtml(s.review_tech)
		s.review_media = linksHtml(s.review_media)
	})
}

/**
 * Gör om länkar till a-tags
 * @param {string} string 
 * @returns string
 */
const linksHtml = (string) => {
	if(!string) {
		return string
	}

	return string.replaceAll(
		/http[^\s<]+/gi, // matcha allt f.o.m. "http" tills du når ett whitespace eller "<" (som i "<br>").
		'<strong>[<a href="$&" target="_blank">länk</a>]</strong>'
	)
}

/**
 * Uppdatera alla element med state:ets värden
 * @param {object} state 
 */
const updateElements = state => {
	// -- Variabler --
	const debriefs_count = state.signups.filter(signup => signup.score > 0).length
	const signups_count = state.signups.length
	const total_score = _.sumBy(state.signups, signup => signup.score)

	let average_score = total_score > 0
		? Math.round((total_score / debriefs_count) * 10) / 10 // avrunda till en decimal
		: '-'
	average_score = (average_score + '').replace('.', ',') // komma som decimaltecken

	// -- Applicera variabler --
	// Antalet skrivna debriefs / antal anmälningar
	$('#value_total_debriefs').html(`${debriefs_count}/${signups_count}`)

	// Genomsnittbetyg
	$('#value_average_score').html(average_score)

	// Varje medlems betyg
	$('#member_scores').html('') // rensa poäng-rader
	_.sortBy(state.signups, s => s.score <= 0).forEach(signup => { // sortera så att icke-debrief:ade signups hamnar sist
		$('#member_scores').append(
			`<div class="member_score">
				<strong>${signup.name}</strong>:
				${signup.score > 0 ? `(${signup.score})` : '-'}
				<div class="stars" style="width: ${(signup.score * 17)}px;"></div>
			</div>`
		)
	})

	// Review - Bra
	if (state.signups.some(s => s.review_good)) { // om reviews finns
		$('#reviews_good').html('')
		state.signups.forEach(signup => {
			if (signup.review_good) {
				$('#reviews_good').append(
					`<li class="list-group-item list-group-item-success">
					<img class="mini-avatar" src="${signup.avatar_url}" alt="Avatar"><strong>${signup.name}</strong><br>
					<span class="review_text">${signup.review_good}</span>
				</li>`
				)
			}
		})
	} else { // om inga reviews finns
		$('#reviews_good').html('<span>-</span>')
	}

	// Review - Dåligt
	if (state.signups.some(s => s.review_bad)) { // om reviews finns
		$('#reviews_bad').html('')
		state.signups.forEach(signup => {
			if (signup.review_bad) {
				$('#reviews_bad').append(
					`<li class="list-group-item list-group-item-danger">
					<img class="mini-avatar" src="${signup.avatar_url}" alt="Avatar"><strong>${signup.name}</strong><br>
					<span class="review_text">${signup.review_bad}</span>
				</li>`
				)
			}
		})
	} else { // om inga reviews finns
		$('#reviews_bad').html('<span>-</span>')
	}

	// Review - Vad kan vi göra bättre
	if (state.signups.some(s => s.review_improvement)) { // om reviews finns
		$('#reviews_improvement').html('')
		state.signups.forEach(signup => {
			if (signup.review_improvement) {
				$('#reviews_improvement').append(
					`<li class="list-group-item list-group-item-warning">
					<img class="mini-avatar" src="${signup.avatar_url}" alt="Avatar"><strong>${signup.name}</strong><br>
					<span class="review_text">${signup.review_improvement}</span>
				</li>`
				)
			}
		})
	} else { // om inga reviews finns
		$('#reviews_improvement').html('<span>-</span>')
	}

	// Review - Teknikstrul
	if (state.signups.some(s => s.review_tech)) { // om reviews finns
		$('#reviews_tech').html('')
		state.signups.forEach(signup => {
			if (signup.review_tech) {
				$('#reviews_tech').append(
					`<li class="list-group-item list-group-item-info">
					<img class="mini-avatar" src="${signup.avatar_url}" alt="Avatar"><strong>${signup.name}</strong><br>
					<span class="review_text">${signup.review_tech}</span>
				</li>`
				)
			}
		})
	} else { // om inga reviews finns
		$('#reviews_tech').html('<span>-</span>')
	}

	// Review - Media
	if (state.signups.some(s => s.review_media)) { // om reviews finns
		$('#reviews_media').html('')
		state.signups.forEach(signup => {
			if (signup.review_media) {
				$('#reviews_media').append(
					`<li class="list-group-item list-group-item-primary">
					<img class="mini-avatar" src="${signup.avatar_url}" alt="Avatar"><strong>${signup.name}</strong><br>
					<span class="review_text">${signup.review_media}</span>
				</li>`
				)
			}
		})
	} else { // om inga reviews finns
		$('#reviews_media').html('<span>-</span>')
	}



}