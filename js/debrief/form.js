$(document).ready(function () {
	updateRoles()
	$('#group').change(() => updateRoles()) // uppdatera roller när gruppen ändras
})

const updateRoles = () => {
	const group_id = $('#group').val() // vald grupp-id

	// Skapa sträng med <option>:s
	let optionsString = ''
	group_roles.forEach(gr => { // gå igenom alla grupp-roller
		if(gr.group_id === group_id) { // lista bara roller som passar gruppen (gr.group_id är en sträng)
			const nameLong = gr.name_long
				? ` (${gr.name_long})`
				: ''
			const selected = gr.role_id-0 === signup_role
				? ' selected'
				: ''

			optionsString += `<option value='${gr.role_id}'${selected}>${gr.name}${nameLong}</option>`
		}
	})
	$('#role').html(optionsString)
}