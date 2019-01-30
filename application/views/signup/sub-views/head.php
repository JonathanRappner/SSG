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
<link rel="icon" href="<?php echo base_url('favicon.ico');?>">

<!-- Preview data -->
<meta name="twitter:title" content="<?php echo $preview->title;?>">
<meta name="twitter:description" content="<?php echo $preview->description;?>">
<meta name="twitter:url" content="<?php echo $preview->url;?>">
<meta name="twitter:image:src" content="<?php echo $preview->image_url;?>">
<meta name="twitter:domain" content="<?php echo $preview->domain;?>">
<meta name="twitter:card" content="summary_large_image">

<meta property="og:title" content="<?php echo $preview->title;?>" />
<meta property="og:description" content="<?php echo $preview->description;?>" />
<meta property="og:url" content="<?php echo $preview->url;?>" />
<meta property="og:image" content="<?php echo $preview->image_url;?>" />
<meta property="og:type" content="article" />


<!-- JS -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.min.js"></script>
<script type="text/javascript" src="<?php echo base_url('js/signup/main.js');?>"></script>

<!-- CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
<link rel="stylesheet" href="<?php echo base_url('css/signup/main.css');?>">
<?php if(defined('APRIL_FOOLS')):?><link href='https://fonts.googleapis.com/css?family=Gochi Hand' rel='stylesheet'><?php endif;?>
<?php if(defined('APRIL_FOOLS')) echo $this->april_fools->style(); ?>

<!-- JS-variabler -->
<input id="base_url" value="<?php echo base_url();?>" type="hidden">