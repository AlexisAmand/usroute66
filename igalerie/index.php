<?php require_once(dirname(__FILE__) . '/galerie.php'); ?>

<!doctype html>
<html lang="fr">

<head>

	<meta charset="utf-8">
   
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
	<meta name="description" content="Les plus belles photos de la mythique Route 66">
    
    <title><?php $tpl->getInfo('title'); ?></title>

	<?php require_once(dirname(__FILE__) . '/entete.php'); ?>

    <script type="text/javascript" src="script.js"></script>

    <link rel="stylesheet" href="style.css" type="text/css" media="screen" />
        
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
                        <div id="slogan-text" class="logo-text">Voyage de l'Illinois &agrave; la Californie</div>
                    </div>
                </div>
                
				
<div class="nav">
	<ul class="artmenu">
		<li id="current" class="active item1"><a href="http://usroute66.boitasite.com/" class="active"><span><span>Accueil du blog</span></span></a></li>
		<li class="item14"><a href="index.php"><span><span>Tout en images</span></span></a></li>
		<li class="item20"><a href="../index.php/bibliographie-livres-et-dvd.html"><span><span>Livres et DVD</span></span></a></li>
		<li class="item41"><a href="../boutique/ideescadeaux.php"><span><span>Idées Cadeaux</span></span></a></li>
		<li class="item9"><a href="../recherche/index.php"><span><span>Rechercher</span></span></a></li>
		<li class="item19"><a href="../index.php/livre-dor.html"><span><span>Livre d'or</span></span></a></li>
		<li class="item10"><a href="../index.php/contacter-le-wemaster.html"><span><span>Contact</span></span></a></li>
	</ul>
	
	<div class="l"></div>
	<div class="r"><div>
	</div></div>
</div> 	
				
				
				
				
				
				
                <div class="contentLayout">
                    <div class="content">
                        <div class="Post">
                            <div class="Post-body">
                        <div class="Post-inner article">

                      	               <?php require_once(dirname(__FILE__) . '/' . IGAL_TEMPLATE); ?>
                                       <center>
                                                             <script type="text/javascript"><!--
google_ad_client = "pub-1550427609493753";
/* 468x60, date de crÃ©ation 01/08/11 - usroute66 */
google_ad_slot = "9460225182";
google_ad_width = 468;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
</center>
                        
                        </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cleared"></div><div class="Footer">
                    <div class="Footer-inner">
                        
                        <div class="Footer-text">
                          
								<p style="text-align:center;">
		<a href="../partenaires.php">Partenaires</a> | <a href="../index.php/contacter-le-wemaster.html">Contacter le webmaster</a><br><br>
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
