<?php
defined('_JEXEC') or die;
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class BMSManagerControllerFile extends BMSManagerController
{
	function upload()
	{
		global $mainframe;
		
		// Check for request forgeries
		if (!JRequest::checkToken( 'request' )) {
			// 401 Unauthorized
			return $this->end( 401, 'Invalid Token' );
		}
		
		// Check to see if uploading is enabled
		if ($mainframe->isSite() && !LinkrHelper::getParam( 'frontend_upload', '0' )) {
			// 401 Unauthorized
			return $this->end( 401, 'Uploading Disabled' );
		}
		
		$this->file	= JRequest::getVar( 'Filedata', '', 'files', 'array' );
		$this->json	= (JRequest::getVar( 'format', 'html', '', 'cmd') == 'json');
		$this->returnURL	= 'index.php?option=com_bmsmanager&view=articles&tmpl=component';
		LinkrHelper::log( 'File::upload '. $this->file['name'] );
		
		// Set FTP credentials, if given
		jimport( 'joomla.client.helper' );
		JClientHelper::setCredentialsFromRequest( 'ftp' );
		
		// Make the filename safe
		$this->file['name']	= strtolower( JFile::makeSafe( $this->file['name'] ) );
		
		if (empty( $this->file['name'] )) {
			LinkrHelper::log( 'Upload failed: empty filename ' );
			return $this->end( 400, 'bad request' );
		}
		
		if (!$this->canUpload()) {
			return $this->end( 415, 'Unsupported Media Type' );
		}
		
		// Get full filename
		$model	= & $this->getModel( 'articles' );
		$paths	= $model->fileInfo();
		$name	= $paths['path'] .DS. $this->file['name'];
		
		// Check filename
		$name	= JPath::clean( $name );
		if (JFile::exists( $name )) {
			// 409 Conflict
			LinkrHelper::log( "Upload failed: file already exists ($name)" );
			return $this->end( 409, 'File already exists' );
		}
		
		// Uplaod
		if (!JFile::upload( $this->file['tmp_name'], $name )) {
			// Is this a 400 bad request or 500 internal error?
			LinkrHelper::log( "Upload failed: could not upload file ($name)" );
			return $this->end( 500, 'Could not upload file' );
		}
		
		// Upload complete
		LinkrHelper::log( 'File uploaded' );
		$this->end( 200, 'File uploaded!' );
	}
	
	// See administrator >> components >> com_media >> helpers >> media.php
	function canUpload()
	{
		// Check extension
		$ext	= strtolower( JFile::getExt( $this->file['name'] ) );
		$allow	= @explode( ',',BMSManagerHelper::getMediaParam( 'upload_extensions' ) );
		$ignore	= @explode( ',',BMSManagerHelper::getMediaParam( 'ignore_extensions' ) );
		if (!in_array( $ext, $allow ) && !in_array( $ext, $ignore )) {
			LinkrHelper::log( 'Upload failed: file extension check' );
			return false;
		}
		
		// Check filesize
		$max	= (int)BMSManagerHelper::getMediaParam( 'upload_maxsize', 0 );
		if ($max > 0 && (int) $this->file['size'] > $max) {
			LinkrHelper::log( 'Upload failed: file size ('. $this->file['size'] .') larger than maximum ('. $max .')' );
			return false;
		}
		
		// PHP image checks
		if (LinkrHelper::getMediaParam( 'restrict_uploads', 1 ))
		{
			$imgx	= explode( ',',BMSManagerHelper::getMediaParam( 'image_extensions' ));
			
			// GetImageSize
			if(in_array( $ext, $imgx ) && !getimagesize( $this->file['tmp_name'] )) {
				LinkrHelper::log( 'Upload Failed: could not get image information through "GetImageSize()"' );
				return false;
			}
			
			else if(!in_array( $ext, $ignore ))
			{
				$amime	= explode( ',',BMSManagerHelper::getMediaParam( 'upload_mime' ));
				$imime	= explode( ',',BMSManagerHelper::getMediaParam( 'upload_mime_illegal' ));
				
				// FileInfo
				if(function_exists( 'finfo_open' ) &&BMSManagerHelper::getMediaParam( 'check_mime', 1 ))
				{
					$finfo	= finfo_open( FILEINFO_MIME );
					$type	= finfo_file( $finfo, $this->file['tmp_name'] );
					
					if(strlen( $type ) && !in_array( $type, $amime ) && in_array( $type, $imime )) {
						LinkrHelper::log( "Upload Failed: invalid mime-type ($type)" );
						finfo_close( $finfo );
						return false;
					}
				}
				
				// MimeContentType
				else if(function_exists( 'mime_content_type' ) &&BMSManagerHelper::getMediaParam( 'check_mime', 1 ))
				{
					$type	= mime_content_type( $this->file['tmp_name'] );
					if(strlen( $type ) && !in_array( $type, $amime ) && in_array( $type, $imime )) {
						LinkrHelper::log( "Upload Failed: invalid mime-type ($type)" );
						return false;
					}
				}
				
				// Check for usertype
				else
				{
					$user	= & JFactory::getUser();
					if(!$user->authorize( 'login', 'administrator' )) {
						LinkrHelper::log( 'Upload Failed: can\'t check mime-type' );
						return false;
					}
				}
			}
		}
		
		// Cross-site scripting
		$xss	=  JFile::read( $this->file['tmp_name'], false, 256 );
		$tags	= array('abbr','acronym','address','applet','area','audioscope','base','basefont','bdo','bgsound','big','blackface','blink','blockquote','body','bq','br','button','caption','center','cite','code','col','colgroup','comment','custom','dd','del','dfn','dir','div','dl','dt','em','embed','fieldset','fn','font','form','frame','frameset','h1','h2','h3','h4','h5','h6','head','hr','html','iframe','ilayer','img','input','ins','isindex','keygen','kbd','label','layer','legend','li','limittext','link','listing','map','marquee','menu','meta','multicol','nobr','noembed','noframes','noscript','nosmartquotes','object','ol','optgroup','option','param','plaintext','pre','rt','ruby','s','samp','script','select','server','shadow','sidebar','small','spacer','span','strike','strong','style','sub','sup','table','tbody','td','textarea','tfoot','th','thead','title','tr','tt','ul','var','wbr','xml','xmp','!DOCTYPE', '!--');
		foreach($tags as $t)
		{
			if(stristr( $xss, '<'. $t .' ' ) || stristr( $xss, '<'. $t .'>' )) {
				LinkrHelper::log( 'Upload Failed: possibly an XSS attack' );
				return false;
			}
		}
		
		// File passed the test!
		return true;
	}
	
	function end( $code, $msg = null )
	{
		LinkrHelper::log( "FileController::end $code $msg" );
		
		// Flash
		if ($this->json)
		{
			$header	= 'HTTP/1.0 '. $code .' '. $msg;
			header( $header );
			jexit( $msg );
		}
		// No Flash
		else
		{
			// Redirect
			global $mainframe;
			$redirect	= $this->returnURL .'&msg='. $msg;
			$mainframe->redirect( $redirect );
		}
	}
}
