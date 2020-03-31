<?php
/** 
 * Teamspeak 3 viewer
*/
defined('BASEPATH') OR exit('No direct script access allowed');
?><div class="card ts3_viewer border-0 shadow-sm">

	<div class="card-header bg-dark text-white">
		<?php if(XMAS):?><div class="snow_edge left"></div><div class="snow_pattern"></div><div class="snow_edge right"></div>🤶<?php endif;?>
		<?php if(CAKE):?>🎂<?php endif;?>
		Teamspeak 3 <?php if(APRIL_FOOLS) echo $this->april_fools->random_emojis(microtime())?>
	</div>

	<div class="card-body p-2">

		<div id="ts3viewer_1066823"> </div>

		<script type="text/javascript" src="https://static.tsviewer.com/short_expire/js/ts3viewer_loader.js"></script>
		<script type="text/javascript">
			var ts3v_url_1 =
				"https://www.tsviewer.com/ts3viewer.php?ID=1066823&text=000000&text_size=12&text_family=1&js=1&text_s_weight=bold&text_s_style=normal&text_s_variant=normal&text_s_decoration=none&text_s_color_h=525284&text_s_weight_h=bold&text_s_style_h=normal&text_s_variant_h=normal&text_s_decoration_h=underline&text_i_weight=normal&text_i_style=normal&text_i_variant=normal&text_i_decoration=none&text_i_color_h=525284&text_i_weight_h=normal&text_i_style_h=normal&text_i_variant_h=normal&text_i_decoration_h=underline&text_c_weight=normal&text_c_style=normal&text_c_variant=normal&text_c_decoration=none&text_c_color_h=525284&text_c_weight_h=normal&text_c_style_h=normal&text_c_variant_h=normal&text_c_decoration_h=underline&text_u_weight=bold&text_u_style=normal&text_u_variant=normal&text_u_decoration=none&text_u_color_h=525284&text_u_weight_h=bold&text_u_style_h=normal&text_u_variant_h=normal&text_u_decoration_h=none";
			ts3v_display.init(ts3v_url_1, 1066823, 100);
		</script>

	</div>

</div>