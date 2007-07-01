<?php
/**
* This file handles image manipulation functions RSGallery2
* @version $Id$
* @package RSGallery2
* @copyright (C) 2005 - 2006 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery2 is Free Software
*/

defined( '_VALID_MOS' ) or die( 'Access Denied' );

/**
* Image utilities class
* @package RSGallery2
* @author Jonah Braun <Jonah@WhaleHosting.ca>
*/
class audioUtils extends fileUtils{
    function allowedFileTypes(){
        return array('mp3');
    }

    /**
     * Takes an image file, moves the file and adds database entry
     * @param the verified REAL name of the local file including path
     * @param name of file according to user/browser or just the name excluding path
     * @param desired category
     * @param title of image, if empty will be created from $name
     * @param description of image, if empty will remain empty
     * @return returns true if successfull otherwise returns an ImageUploadError
     */
    function importImage($tmpName, $name, $cat, $title='', $desc='') {
        global $database, $my, $rsgConfig;

        $destination = fileUtils::move_uploadedFile_to_orignalDir( $tmpName, $name );
        
        if( is_a( $destination, imageUploadError ) )
            return $destination;

        $parts = pathinfo( $destination );
        // fill $title if empty
        if( $title == '' ) 
            $title = substr( $parts['basename'], 0, -( strlen( $parts['extension'] ) + ( $parts['extension'] == '' ? 0 : 1 )));

        // determine ordering
        $database->setQuery("SELECT COUNT(1) FROM #__rsgallery2_files WHERE gallery_id = '$cat'");
        $ordering = $database->loadResult() + 1;
        
        //Store image details in database
        $desc = mysql_real_escape_string($desc);
        $title = mysql_real_escape_string($title);
        $database->setQuery("INSERT INTO #__rsgallery2_files".
                " (title, name, descr, gallery_id, date, ordering, userid) VALUES".
                " ('$title', '$name', '$desc', '$cat', now(), '$ordering', '$my->id')");
        
        if (!$database->query()){
            imgUtils::deleteImage( $parts['basename'] );
            return new imageUploadError( $parts['basename'], $database->stderr(true) );
        }

        return true;
    }
    
     function getAudio($name, $local=false){
        global $mosConfig_live_site, $rsgConfig;
        
        $locale = $local? JPATH_ROOT : $mosConfig_live_site;
        
        // if thumb image exists return that, otherwise the original image width <= $thumb_width so we return the original image instead.
        if( file_exists( JPATH_ROOT.$rsgConfig->get('imgPath_original') . '/' . audioUtils::getAudioName( $name ))){
            return $locale  . $rsgConfig->get('imgPath_original') . '/' . audioUtils::getAudioName( $name );
        }else {
            return;
        }
    }
    
      
    function getAudioName($name){
        return $name;
    }
}