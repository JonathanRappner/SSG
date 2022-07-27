<?php
/** 
 * Sub-vy som innehåller generisk html-kod för <head>.
*/
defined('BASEPATH') OR exit('No direct script access allowed');

//hämta data till twitter/ogp-preview
$preview = $this->preview->get_data();

?><!-- Meta -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="<?=base_url('favicon.ico?0');?>">

<!-- Preview data -->
<meta name="twitter:title" content="<?=$preview->title;?>">
<meta name="twitter:description" content="<?=$preview->description;?>">
<meta name="twitter:url" content="<?=$preview->url;?>">
<meta name="twitter:image:src" content="<?=$preview->image_url;?>">
<meta name="twitter:domain" content="<?=$preview->domain;?>">
<meta name="twitter:card" content="summary_large_image">

<meta property="og:title" content="<?=$preview->title;?>" />
<meta property="og:description" content="<?=$preview->description;?>" />
<meta property="og:url" content="<?=$preview->url;?>" />
<meta property="og:image" content="<?=$preview->image_url;?>" />
<meta property="og:type" content="article" />


<!-- JS -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script defer src="https://use.fontawesome.com/releases/v5.15.4/js/all.js" integrity="sha384-rOA1PnstxnOBLzCLMcre8ybwbTmemjzdNlILg8O7z1lUkLXozs4DHonlDtnE7fpc" crossorigin="anonymous"></script>

<!-- CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<link rel="stylesheet" href="<?=base_url('css/main.css?3');?>">
<link rel="stylesheet" href="<?=base_url('css/debrief/main.css?0');?>">

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-192568364-1"></script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());

	gtag('config', 'UA-192568364-1');
</script>

<!-- JS-variabler -->
<script>
	var base_url = '<?=base_url()?>';
</script>