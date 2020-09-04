<?php
foreach(glob('*.png') as $img)
	echo "<img src=\"$img\" alt=\"$img\">";