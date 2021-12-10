<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once dirname(__FILE__) . DS . 'functions.php';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>">

<head>

	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
  	<jdoc:include type="head" />
	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/system.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/general.css" type="text/css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/style.css" />
	<!--[if IE 6]><link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/style.ie6.css" type="text/css" media="screen" /><![endif]-->
  	<script type="text/javascript" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/script.js"></script>
 
</head>
 
<body>

<div class="Main">

	<div class="Sheet">
    <div class="Sheet-tl"></div>
    <div class="Sheet-tr"><div></div></div>
    <div class="Sheet-bl"><div></div></div>
    <div class="Sheet-br"><div></div></div>
    <div class="Sheet-tc"><div></div></div>
    <div class="Sheet-bc"><div></div></div>
    <div class="Sheet-cl"><div></div></div>
    <div class="Sheet-cr"><div></div></div>
    <div class="Sheet-cc"></div>
    <div class="Sheet-body">
    
		<div class="Header">
    		<div class="Header-png"></div>
    		<div class="Header-jpeg"></div>
			<div class="logo">
 				<div id="slogan-text" class="logo-text">Voyage de l'Illinois àla Californie</div>
			</div>
		</div>
		
		<jdoc:include type="modules" name="user3" />
	
	<div class="contentLayout">
		<div class="sidebar1">
			<jdoc:include type="modules" name="left" style="artblock" />
		</div>

	<div class="content">
		<?php if ($this->countModules('breadcrumb') || artxHasMessages()) : ?>
		<div class="Post">
		    <div class="Post-body">
				<div class="Post-inner">
					<div class="PostContent">
						<jdoc:include type="modules" name="breadcrumb" />
						<jdoc:include type="message" />
					</div>
					<div class="cleared"></div>
				</div>
    		</div>
		</div>
		<?php endif; ?>
		<jdoc:include type="component" />
	</div>

	</div>
	<div class="cleared"></div>
	<div class="Footer">
 		<div class="Footer-inner">
  		<jdoc:include type="modules" name="syndicate" />
  		<div class="Footer-text">
  			<p style="text-align:center;">
			<a href="partenaires.php">Partenaires</a> | <a href="index.php/contacter-le-wemaster.html">Contacter le webmaster</a><br><br>
			Copyright &copy; 2010-2019 <a href="http://www.boitasite.com">Alexis AMAND</a>. Tous droits r&eacute;serv&eacute;s.
			</p>
   		</div>
 		</div>
 		<div class="Footer-background"></div>
	</div>

    </div>
	</div>
 
</div>

<!-- Matomo -->
<script type="text/javascript">
  var _paq = _paq || [];
  /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="//genealexis.fr/piwik/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', '15']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Matomo Code -->

</body> 
</html>