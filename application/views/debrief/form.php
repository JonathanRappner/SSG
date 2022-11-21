<?php

/** 
 * Formulär där användaren kan skriva debrief för sig själv eller för någon annan.
 */
defined('BASEPATH') or exit('No direct script access allowed');


?>
<!DOCTYPE html>
<html lang="sv">

<head>
	<title><?= $event->title ?></title>

	<script>
		const group_roles = <?= json_encode($group_roles) ?>;
		const signup_role = <?= $signup->role_id ?>;
	</script>

	<?php $this->load->view('debrief/sub-views/head') ?>
	<link rel="stylesheet" href="<?= base_url('css/debrief/form.css?0') ?>">
	<script src="<?= base_url('js/debrief/form.js?0') ?>"></script>
</head>

<body>

	<!-- Top -->
	<?php $this->load->view('debrief/sub-views/top') ?>

	<!-- Huvud-wrapper -->
	<div id="wrapper" class="container">

		<div class="row">
			<div class="col">

				<!-- Breadcrumbs -->
				<div class="row">
					<div class="col">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="<?= base_url('debrief') ?>">Debriefs</a></li>
								<li class="breadcrumb-item"><a href="<?= base_url("debrief/event/{$event->id}") ?>"><?= $event->title ?></a></li>
								<?php if (!$group->dummy) : // visa inte grupp-steget om anmälan var gjord till dummy-grupp 
								?>
									<li class="breadcrumb-item"><a href="<?= base_url("debrief/group/{$event->id}/{$group->id}") ?>"><?= $group->name ?></a></li>
								<?php endif; ?>
								<li class="breadcrumb-item active" aria-current="page"><?= $debriefing_myself ? 'Din' : "{$member->name}s" ?> debrief</li>
							</ol>
						</nav>
					</div>
				</div>

			</div>
		</div>

		<!-- Rubrik -->
		<div class="row">
			<div class="col mb-3">
				<h2><?= $debriefing_myself ? 'Din' : "{$member->name}s" ?> debrief för: <?= $event->title ?></h2>
			</div>
		</div>

		<!-- Formulär -->
		<div class="row">
			<div class="col pb-3">
				<div class="card shadow-sm">
					<div class="card-body">

						<form method="post" action="<?= base_url('debrief/submit') ?>">

							<input type="hidden" name="score" value="" />

							<!-- Grupp -->
							<div class="form-group">
								<label for="group">Grupp</label>
								<select class="form-control" name="group" id="group">
									<?php foreach ($groups as $grp) : ?>
										<option value="<?= $grp->id ?>" <?= $grp->id == $group->id ? 'selected' : null ?>><?= $grp->name ?></option>
									<?php endforeach; ?>
								</select>
								<?php if ($group->dummy) : ?>
									<small class="form-text text-muted">I din anmälan anmälde du dig inte till en riktig grupp. Välj vilken grupp du spelade med.</small>
								<?php endif; ?>
							</div>

							<!-- Befattning -->
							<div class="form-group">
								<label for="role">Befattning</label>
								<select class="form-control" name="role" id="role"></select>
								<?php if ($role->dummy) : ?>
									<small class="form-text text-muted">I din anmälan anmälde du dig inte till en riktig befattning. Välj vilken befattning du spelade som.</small>
								<?php endif; ?>
							</div>

							<!-- Närvaro -->
							<div class="form-group">
								<label for="attendance">Närvaro</label>
								<div class="custom-control custom-radio">
									<input <?= $signup && $signup->attendance_id == 1 ? 'checked' : null ?> type="radio" id="attendance_yes" name="attendance" value="1" class="custom-control-input">
									<label class="custom-control-label text-signed" for="attendance_yes">Ja</label>
								</div>
								<div class="custom-control custom-radio">
									<input <?= $signup && $signup->attendance_id == 2 ? 'checked' : null ?> type="radio" id="attendance_jip" name="attendance" value="2" class="custom-control-input">
									<label class="custom-control-label text-jip" for="attendance_jip"><abbr title="Join in progress">JIP</abbr></label>
								</div>
								<div class="custom-control custom-radio">
									<input <?= $signup && $signup->attendance_id == 3 ? 'checked' : null ?> type="radio" id="attendance_qip" name="attendance" value="3" class="custom-control-input">
									<label class="custom-control-label text-qip" for="attendance_qip"><abbr title="Quit in progress">QIP</abbr></label>
								</div>
								<div class="custom-control custom-radio">
									<input <?= $signup && $signup->attendance_id == 4 ? 'checked' : null ?> type="radio" id="attendance_noshow" name="attendance" value="4" class="custom-control-input">
									<label class="custom-control-label text-noshow" for="attendance_noshow">NOSHOW</label>
								</div>
							</div>

							<hr class="my-4" />

							<!-- Betyg -->
							<div class="form-group">
								<label for="score" class="w-100">Betyg</label>
								<div class="star-container">
									<div class="star" data-star_number="1"></div>
									<div class="star" data-star_number="2"></div>
									<div class="star" data-star_number="3"></div>
									<div class="star" data-star_number="4"></div>
									<div class="star" data-star_number="5"></div>
									<span class="invalid_text">Du måste ge eventet ett betyg.</span>
								</div>
							</div>

							<!-- Bra/roligt -->
							<div class="form-group">
								<label for="good">
									<i class="fas fa-thumbs-up"></i>
									<span class="text-success">Vad har varit bra/roligt?</span>
								</label>
								<textarea class="form-control" name="review_good" id="good" rows="4" placeholder="Bra OP, mycket skjuta, mycket action, slut från mig."></textarea>
							</div>

							<!-- Dåligt/tråkigt -->
							<div class="form-group">
								<label for="bad">
									<i class="fas fa-thumbs-down"></i>
									<span class="text-danger">Vad har varit dåligt/tråkigt?</span>
								</label>
								<textarea class="form-control" name="review_bad" id="bad" rows="4"></textarea>
							</div>

							<!-- Bättre -->
							<div class="form-group">
								<label for="improvement">
									<i class="fas fa-yin-yang"></i>
									<span class="text-warning">Vad kan vi göra bättre?</span>
								</label>
								<textarea class="form-control" name="review_improvement" id="improvement" rows="4"></textarea>
							</div>

							<!-- Tekniskt strul -->
							<div class="form-group">
								<label for="tech">
									<i class="fas fa-tools"></i>
									<span class="text-info">Tekniskt strul</span>
								</label>
								<textarea class="form-control" name="review_tech" id="tech" rows="4"></textarea>
							</div>

							<button type="submit" class="btn btn-success">
								Spara <i class='fas fa-save'></i>
							</button>

						</form>

					</div>
				</div>
			</div>
		</div>

	</div>


	<!-- Footer -->
	<?php $this->load->view('debrief/sub-views/footer') ?>

</body>

</html>