<?php
defined('_JEXEC') or die;
jimport( 'joomla.filesystem.file' );
jimport( 'joomla.filesystem.folder' );

// Defines
define( 'bmsmanagerAssetsPath', JURI::root() .'components/com_bmsmanager/assets/' );
define( 'BMSMANAGER_VERSION', 1.01 );

class BMSManagerHelper extends JObject
{
	function listDirectories( $path, $regex = '.', $recurse = false )
	{
		$dirs	= array();
		
		// Make sure folder is valid
		$path	= JFolder::makeSafe( $path );
		if (empty( $path ) || JString::strpos( $path, JPATH_ROOT ) === false) {
			return $dirs;
		}
		
		// Find folders
		$list	= JFolder::folders( $path, $regex, $recurse, true );
		if (empty( $list )) {
			return $dirs;
		}
		
		// Create list of directories and the names
		foreach ($list as $path)
		{
			$folder	= JString::str_ireplace( JPATH_SITE, '', $path );
			$folder	= substr( str_replace( DS, '/', $folder ), 1 );
			$name	= @explode( '/', $folder );
			$name	= array_pop( $name );
			$dirs[]	= array(
						'folder'	=> $folder,
						'name'		=> $name,
						'path'		=> $path,
						'path.64'	=> base64_encode( $path )
					);
		}
		
		return $dirs;
	}
	
	function listFiles( $path, $regex = '.' )
	{
		$files	= array();
		
		// Make sure path is valid
		$path	= JPath::clean( $path );
		if (empty( $path ) || JString::strpos( $path, JPATH_ROOT ) === false) {
			return $files;
		}
		
		$list	= JFolder::files( $path, $regex, false, true );
		if (empty( $list )) {
			return $files;
		}
		
		foreach ($list as $filename)
		{
			$f	= new JObject();
			$f->name	= JFile::getName( $filename );
			$f->path	= $filename;
			$f->src		= JString::str_ireplace( JPATH_ROOT.DS, JURI::root(), $f->path );
			$f->src		= str_replace( DS, '/', $f->src );
			$f->size	=BMSManagerHelper::parseSize( $f->path );
			$f->ext		= strtolower( JFile::getExt( $f->name ) );
			
			switch ( $f->ext )
			{
				// Image
				case 'bmp':
				case 'gif':
				case 'jpg':
				case 'jpeg':
				case 'odg':
				case 'png':
				case 'xcf':
					list( $w, $h )	= @getimagesize( $f->path );
					$size	=BMSManagerHelper::imageResize( $w, $h, 32 );
					$f->width	= $size['width'];
					$f->height	= $size['height'];
					$f->icon	= JString::str_ireplace( JPATH_ROOT.DS, JURI::root(), $f->path );
					$f->icon	= str_replace( DS, '/', $f->icon );
					$f->type	= JText::_( 'Image' );
					break;
				
				// Other files
				default:
					$f->type	= strtoupper( $f->ext );
					$f->width	= 32;
					$f->height	= 32;
					$icon	= JPATH_ADMINISTRATOR.DS.'components'.DS.'com_media'.DS.'images'.DS.'mime-icon-32'.DS. $f->ext .'.png';
					if (file_exists( $icon )) {
						$f->icon	= JURI::root() .'administrator/components/com_media/images/mime-icon-32/'. $f->ext .'.png';
					} else {
						$f->icon	= JURI::root() .'administrator/components/com_media/images/con_info.png';
					}
					break;
			}
			
			$files[]	= $f;
		}
		
		return $files;
	}
	
	function buildRegex( $folders = null )
	{
		// Make sure expression limits viewed folders... in case of
		// error, default to expression that will reject all directories
		$regex	= '_-_-__-_-_';
		$array	= array();
		
		// Checks
		if (is_array( $folders ) && !empty( $folders )) {
			$folders	= implode( ',', $folders );
		} elseif (!is_string( $folders ) || empty( $folders )) {
			return $regex;
		}
		
		// Try and correct errors if any
		$folders	= str_replace( array( ';', '|' ), ',', $folders );
		$folders	= @explode( ',', $folders );
		foreach ($folders as $f) {
			$f	= JFolder::makeSafe( $f );
			if (!empty( $f )) {
				$array[]	= $f;
			}
		}
		
		// Build regex for use with JFolders::folders
		if (!empty( $array )) {
			$regex	= '('. implode( '|', $array ) .')';
		}
		
		return $regex;
	}
	
	function getParam( $var, $def = null )
	{
		static $params;
		
		if (is_null( $params ))
		{
			jimport( 'joomla.application.component.helper' );
			$params	= & JComponentHelper::getParams( 'com_bmsmanager' );
		}
		
		return $params->get( $var, $def );
	}
	
	function &getPluginParameters()
	{
		static $params;
		
		if (!isset( $params ))
		{
			jimport( 'joomla.plugin.plugin' );
			$bmsmanager	= & JPluginHelper::getPlugin( 'content', 'bmsmanager' );
			$params	= empty($bmsmanager) ? false : new JParameter( $bmsmanager->params );
		}
		
		return $params;
	}
	
	function getPluginParam( $var = 'isInstalled', $def = null )
	{
		if (!$params =BMSManagerHelper::getPluginParameters()) {
			return $def;
		}
		
		if ($var == 'isInstalled') {
			return ($params) ? true : false;
		}
		
		return $params->get( $var, $def );
	}
	
	function getMediaParam( $var, $def = null )
	{
		static $params;
		
		if (is_null( $params ))
		{
			jimport( 'joomla.application.component.helper' );
			$params	= & JComponentHelper::getParams( 'com_media' );
		}
		
		return $params->get( $var, $def );
	}
	
	// See administrator >> components >> com_media >> helpers >> media.php
	function parseSize( $size )
	{
		if (!is_numeric( $size )) {
			if (!is_string( $size ) || !is_file( $size )) {
				return '?';
			} else {
				$size	= filesize( $size );
			}
		}
		
		if ($size < 1024) {
			return $size . ' bytes';
		}
		else
		{
			if ($size >= 1024 && $size < 1024 * 1024) {
				return sprintf('%01.2f', $size / 1024.0) . ' KB';
			} else {
				return sprintf('%01.2f', $size / (1024.0 * 1024)) . ' MB';
			}
		}
	}
	
	// See administrator >> components >> com_media >> helpers >> media.php
	function imageResize( $width, $height, $target )
	{
		//takes the larger size of the width and height and applies the
		//formula accordingly...this is so this script will work
		//dynamically with any size image
		if ($width > $height) {
			$percentage	= ($target / $width);
		} else {
			$percentage	= ($target / $height);
		}
		
		//gets the new value and applies the percentage, then rounds the value
		$width	= ($width > $target) ? round($width * $percentage) : $width;
		$height	= ($height > $target) ? round($height * $percentage) : $height;
		
		return array( 'width' => $width, 'height' => $height );
	}
	
	function isSite() {
		global $mainframe;return $mainframe->isSite();
	}
	
	// Debuging
	
	function debug()
	{
		static $debug;
		if (is_null( $debug )) {
			$debug	= (JDEBUG ||BMSManagerHelper::getParam( 'debug', '0' ));
		}
		
		return $debug;
	}
	
	function dump( $var ) {
		ob_clean();jexit(var_dump($var));
	}
	
	function log( $msg )
	{
		if (empty( $msg ) || !LinkrHelper::debug()) return;
		
		static $log;
		if (is_null( $log ))
		{
			$o	= array( 'format' => '{DATE} {TIME} ({C-IP}), {COMMENT}' );
			jimport( 'joomla.error.log' );
			$log	= & JLog::getInstance( 'bmsmanager.php', $o );
		}
		
		$log->addEntry( array( 'comment' => $msg ) );
	}
}
