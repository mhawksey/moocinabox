<?php
if (!extension_loaded('gd')) return;

/**
 * From an original idea of :
 *
// -----------------------------------------------
// Cryptographp v1.4
// (c) 2006-2007 Sylvain BRISON 
//
// www.cryptographp.com 
// cryptographp@alphpa.com 
//
// Licence CeCILL modifiée
// => Voir fichier Licence_CeCILL_V2-fr.txt)
// -----------------------------------------------
*/

class MP_Form_field_type_captcha_gd1_cryptogra
{
	function __construct()
	{
		session_start();

		$field = MP_Form_field::get($_GET['id']);

		$root = dirname(__FILE__) . '/cfg/';
		if (is_dir($root)) 
		{
			$dir = @opendir($root);
			if ($dir) while (($style = readdir($dir)) !== false) if ($style[0] != '.') $xstyles[$style] = $style;
			@closedir($dir);
		}
		$xstyle = (isset($field->settings['options']['form_img_style'])) ? $field->settings['options']['form_img_style'] : false;
		if (!isset($xstyles[$xstyle])) $xstyle = 'random';
		if ($xstyle == 'random') $xstyle = $xstyles[array_rand($xstyles)];
		$xsettings = dirname(__FILE__) . "/cfg/$xstyle/settings.php";

   		list($hash, $word, $format, $img) 	= self::create($xstyle, $xsettings);

		$_SESSION['cryptogra']['style']	= $xstyle;
		$_SESSION['cryptogra']['settings'] 	= $xsettings;

		switch (strtolower($hash)) 
		{    
			case 'md5'  : $_SESSION['cryptogra']['code'] = md5($word); 	break;
			case 'sha1' : $_SESSION['cryptogra']['code'] = sha1($word); break;
		      default     : $_SESSION['cryptogra']['code'] = $word; 	break;
		}
		$_SESSION['cryptogra']['time'] = time();
//		$_SESSION['cryptogra']['use']++;

		ob_end_clean();
		switch (strtolower($format)) 
		{  
			case 'jpg'  :
			case 'jpeg' : 
				if (imagetypes() & IMG_JPG)  
				{
	                        header('Content-type: image/jpeg');
	                        imagejpeg($img, '', 80);
				}
			break;
			case 'gif'  : 
				if (imagetypes() & IMG_GIF)
				{
					header('Content-type: image/gif');
					imagegif($img);
				}
				break;
			case 'png'  : 
			default     : 
				if (imagetypes() & IMG_PNG)
				{
					header('Content-type: image/png');
					imagepng($img);
				}
		}
		imagedestroy ($img);
		die();
	}

	function create($style, $settings)
	{
        global $img, $ink, $charclear, $charcolorrnd, $charcolorrndlevel, $charnb, $charspace, $charR, $charG, $charB, $tword,  $xvariation;
		global $bg, $brushsize, $noisecolorchar;
		global $cryptwidth, $cryptheight, $nbcirclemin, $nbcirclemax, $noiselinemin, $noiselinemax, $noisepxmin, $noisepxmax;

		include($settings);

		// Création du cryptogramme temporaire
		$imgtmp 	= imagecreatetruecolor($cryptwidth, $cryptheight);
		$blank 	= imagecolorallocate($imgtmp, 255, 255, 255);
		$black 	= imagecolorallocate($imgtmp,   0,   0,   0);
		imagefill($imgtmp, 0, 0, $blank);

		$word 	 = '';
		$x = 10;
		$pair 	 = rand(0, 1);
		$charnb 	 = rand($charnbmin, $charnbmax);
		for ($i = 1; $i <= $charnb; $i++) 
		{
			$tword[$i]['font'] 	= $tfont[array_rand($tfont, 1)];
			$tword[$i]['angle']	= (rand(1, 2) == 1) ? rand(0, $charanglemax) : rand(360 - $charanglemax, 360);
			$tword[$i]['element'] 	= ($crypteasy) ? ( (!$pair) ? $charelc{rand(0, strlen($charelc) - 1)} : $charelv{rand(0, strlen($charelv) - 1)} ) : $charel{rand(0, strlen($charel) - 1)};
			$pair 			= !$pair;
			$tword[$i]['size'] 	= rand($charsizemin, $charsizemax);
			$tword[$i]['y']    	= ($charup) ? ($cryptheight/2) + rand(0, ($cryptheight/5)) : ($cryptheight/1.5);

			$word .= $tword[$i]['element'];

			imagettftext($imgtmp, $tword[$i]['size'], $tword[$i]['angle'], $x, $tword[$i]['y'], $black, dirname(__FILE__) . '/fonts/' . $tword[$i]['font'], $tword[$i]['element']);

			$x +=$charspace;
		}

		// Calcul du racadrage horizontal du cryptogramme temporaire
		$xbegin = $x = 0;
		while (!$xbegin && ($x < $cryptwidth)) 
		{
			$y = 0;
			while (!$xbegin && ($y < $cryptheight)) 
			{
				if (imagecolorat($imgtmp, $x, $y) != $blank) $xbegin = $x;
				$y++;
			}
			$x++;
		} 
    
		$xend = 0;
		$x    = $cryptwidth - 1;
		while (!$xend && ($x > 0)) 
		{
			$y = 0;
			while (!$xend && ($y < $cryptheight)) 
			{
				if (imagecolorat($imgtmp, $x, $y) != $blank) $xend = $x;
				$y++;
			}
			$x--;
		} 

		$xvariation = round( ($cryptwidth/2) - (($xend - $xbegin)/2) );
		imagedestroy ($imgtmp);

		// Création du cryptogramme définitif
		// Création du fond

		$img = imagecreatetruecolor($cryptwidth, $cryptheight); 

		 if ($bgimg) $bgimg = dirname(__FILE__) . "/cfg/$style/$bgimg";

		if ($bgimg and is_dir($bgimg)) 
		{
			$dh = opendir($bgimg);
			while (($filename = readdir($dh)) !== false) if (MP_::is_image($filename)) $files[] = $filename;
			closedir($dh);
			$bgimg = "$bgimg/" . $files[array_rand($files, 1)];
		}

		if ($bgimg)
		{
			list($getwidth, $getheight, $gettype, $getattr) = getimagesize($bgimg);
			switch ($gettype) 
			{
				case '1' : $imgread = imagecreatefromgif ($bgimg); break;
				case '2' : $imgread = imagecreatefromjpeg($bgimg); break;
				case '3' : $imgread = imagecreatefrompng ($bgimg); break;
			}
	            if (isset($imgread))
	            {
				imagecopyresized ($img, $imgread, 0, 0, 0, 0, $cryptwidth, $cryptheight, $getwidth, $getheight);
				imagedestroy ($imgread);
	            }
		}
            else 
		{
			$bg = imagecolorallocate($img, $bgR, $bgG, $bgB);
			imagefill($img, 0, 0, $bg);
			if ($bgclear) imagecolortransparent($img, $bg);
		}

		if ($noiseup) { self::ecriture(); self::bruit(); } else  { self::bruit(); self::ecriture(); }

		// Création du cadre
		if ($bgframe) 
		{
			$framecol = imagecolorallocate($img, ($bgR * 3 + $charR)/4, ($bgG * 3 + $charG)/4, ($bgB * 3 + $charB)/4);
			imagerectangle($img, 0, 0, $cryptwidth - 1, $cryptheight - 1, $framecol);
		}
            
		// Transformations supplémentaires: Grayscale et Brouillage
		// Vérifie si la fonction existe dans la version PHP installée

		if (function_exists('imagefilter')) 
		{
			if ($cryptgrayscal) 	imagefilter($img, IMG_FILTER_GRAYSCALE);
			if ($cryptgaussianblur) imagefilter($img, IMG_FILTER_GAUSSIAN_BLUR);
		}

		// Conversion du cryptogramme en Majuscule si insensibilité à la casse

		if ($difuplow) $word = strtoupper($word);

		return array($cryptsecure, $word, $cryptformat, $img);
	}

	function ecriture()				// Création de l'écriture
	{
		global $img, $ink, $charclear, $charcolorrnd, $charcolorrndlevel, $charnb, $charspace, $charR, $charG, $charB, $tword,  $xvariation;

		$ink = (function_exists ('imagecolorallocatealpha')) ? imagecolorallocatealpha($img, $charR, $charG, $charB, $charclear) : imagecolorallocate($img, $charR, $charG, $charB);

		$x = $xvariation;
		for ($i = 1; $i <= $charnb; $i++)
		{       
       
			if ($charcolorrnd)		  // Choisit des couleurs au hasard
			{ 
				$ok = false;
				do 
				{
					$rndR = rand(0, 255); $rndG = rand(0, 255); $rndB = rand(0, 255);
					$rndcolor = $rndR + $rndG + $rndB;
					switch ($charcolorrndlevel) 
					{
						case 1  : if ($rndcolor<200) $ok = true; break; 	// tres sombre
						case 2  : if ($rndcolor<400) $ok = true; break; 	// sombre
						case 3  : if ($rndcolor>500) $ok = true; break;		// claires
						case 4  : if ($rndcolor>650) $ok = true; break; 	// très claires
						default : $ok = true;               
					}
				} while (!$ok);
          
				$rndink = (function_exists ('imagecolorallocatealpha')) ? imagecolorallocatealpha($img, $rndR, $rndG, $rndB, $charclear) : imagecolorallocate ($img, $rndR, $rndG, $rndB);          
			}  
         
			imagettftext($img, $tword[$i]['size'], $tword[$i]['angle'], $x, $tword[$i]['y'], ($charcolorrnd) ? $rndink : $ink, dirname(__FILE__) . '/fonts/' . $tword[$i]['font'], $tword[$i]['element']);

			$x += $charspace;
		} 
	}

	function bruit()			// Ajout de bruits: point, lignes et cercles aléatoires
	{
		global $img, $cryptwidth, $cryptheight, $nbcirclemin, $nbcirclemax, $noiselinemin, $noiselinemax, $noisepxmin, $noisepxmax;

		$nbpx 	= rand($noisepxmin,   $noisepxmax);
		$nbline 	= rand($noiselinemin, $noiselinemax);
		$nbcircle 	= rand($nbcirclemin,  $nbcirclemax);
		for ($i = 1; $i <  $nbpx; 	$i++)	imagesetpixel ($img, rand(0, $cryptwidth - 1), rand(0, $cryptheight - 1), self::noisecolor());
		for ($i = 1; $i <= $nbline; 	$i++)	imageline	  ($img, rand(0, $cryptwidth - 1), rand(0, $cryptheight - 1), rand(0, $cryptwidth - 1), rand(0, $cryptheight - 1), self::noisecolor());
		for ($i = 1; $i <= $nbcircle;	$i++) imagearc	  ($img, rand(0, $cryptwidth - 1), rand(0, $cryptheight - 1), $rayon = rand(5, $cryptwidth/3), $rayon, 0, 360, self::noisecolor());
	} 

	function noisecolor()		// Fonction permettant de déterminer la couleur du bruit et la forme du pinceau
	{
		global $img, $bg, $brushsize, $ink, $noisecolorchar;

		switch ($noisecolorchar) 
		{
			case 1  : $noisecol = $ink; break;
			case 2  : $noisecol = $bg;  break;
			case 3  : 
			default : $noisecol = imagecolorallocate ($img, rand(0, 255), rand(0, 255), rand(0, 255)); break;               
		}

		if ($brushsize && ($brushsize > 1) && function_exists('imagesetbrush')) 
		{
			$brush = imagecreatetruecolor($brushsize, $brushsize);
			imagefill($brush, 0, 0, $noisecol);
			imagesetbrush($img, $brush);
			$noisecol = IMG_COLOR_BRUSHED;
		}
		return $noisecol;    
	}
}
new MP_Form_field_type_captcha_gd1_cryptogra();