<?php
/**
 * Attachment model
 * 
 * @version     $Id$
 * @package     Joomla
 * @subpackage  OTRSGateway
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */
 
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
jimport( 'joomla.filesystem.file' );

require_once( JPATH_COMPONENT.DS.'helpers'.DS.'userhelper.php' );
 
/**
 * Attachment Model
 *
 * @package    Joomla
 * @subpackage OTRS Gateway
 */
class OTRSGatewayModelAttachment extends JModel
{
    var $_userID = null;
    var $_customerIDs = null;
    var $_gateway = null;
    var $_downloadError = null;

    function __construct()
    {
        parent::__construct();
        $userData = OTRSGatewayUserHelper::getOTRSUserData();
        if ($userData != null)
        {
            $this->_userID = $userData[0];
            $this->_customerIDs = $userData[1];
        }
        $this->_gateway = new OTRSGatewayRPCHelper();
    }

    function download( $articleid = false, $fileid = false )
    {
        if ( !$this->_userID )
        {
            $this->setDownloadError( 'Access Denied' );
            return array();
        }
        if ( !$articleid || !$fileid )
        {
            $this->setDownloadError( 'Attachment not found' );
            return array();
        }
        $vars = array(
                        'FileID' => array ( $fileid, XSD_INTEGER ),
                        'ArticleID' => array( $articleid, XSD_INTEGER ),
                        'CustomerUserID' => array( $this->_userID, XSD_STRING ),
                    );
        $result = null;
        if ( $err = $this->_gateway->callOTRS(
                        'JoomlaGatewayObject', 'GetAttachment', $vars,
                        $result ) )
        {
            // Failed to find anything
            $this->setDownloadError( 'Unable to find attachment' );
            return array();
        }
        if ( !$result )
        {
            $this->setDownloadError( 'Unable to find attachment' );
            return array();
        }
        if ( isset( $result->error ) )
        {
            $this->setDownloadError( $result->error );
            return array();
        }
        return array( 'data' => $result );
    }

    function store( $file, $token, &$fileID )
    {
        $tmpPath = $this->_getTempPath();
        if ( $tmpPath )
        {
            $dest = tempnam( $tmpPath, 'OGA' );
            JFile::upload($file['tmp_name'], $dest);

            $fileID = uniqid();
            $ct = $file['type'];

            // Try and be clever about file type mimes
            if ( $ct == 'application/force-download' &&
                 function_exists( 'finfo_open' ) )
            {
                $res = finfo_open( FILEINFO_MIME );
                if ( $res )
                {
                    $finfo = finfo_file( $res, $dest );
                    if ( $finfo !== false && preg_match( '/^[\w\-]+\/[\w\-]+$/', $finfo ) )
                        $ct = $finfo;
                    finfo_close( $res );
                }
            }

            // Fallback to application-octet stream
            if ( empty( $ct ) )
                $ct = 'application/octet-stream';

            // Store this upload for later use
            $db =& JFactory::getDBO();
            $query = sprintf( 'INSERT INTO #__otrsgateway_attachments (id, filename, token, realname, username, content_type) VALUES (%s, %s, %s, %s, %s, %s)', $db->Quote( $fileID ), $db->Quote( $dest ), $db->Quote( $token ), $db->Quote( $file['name'] ), $db->Quote( $this->_userID ), $db->Quote( $ct ) );
            $db->setQuery( $query );
            if ( !$db->query() )
            {
                // Couldn't store, so unset the fileID and remove the
                // stored file
                $fileID = null;
                unlink( $dest );
            }
        }
    }

    function remove( $fileID, $token )
    {
        $db =& JFactory::getDBO();
        // Remove a file based on the fileID and token
        $query = sprintf( 'SELECT filename FROM #__otrsgateway_attachments WHERE id = %s AND token = %s AND username = %s', $db->Quote( $fileID ), $db->Quote( $token ), $db->Quote( $this->_userID ) );
        $db->setQuery( $query );
        $filename = $db->loadResult();
        if ( $filename )
        {
            if ( file_exists( $filename ) )
            {
                unlink( $filename );
            }

            // Remove the entry from the uploads table
            $delQuery = sprintf( 'DELETE FROM #__otrsgateway_attachments WHERE id = %s AND token = %s AND username = %s', $db->Quote( $fileID ), $db->Quote( $token ), $db->Quote( $this->_userID ) );
            $db->setQuery( $delQuery );
            $db->query();

            return true;
        }
        return false;
    }

    function removeAll( $token )
    {
        $db =& JFactory::getDBO();
        $query = sprintf( 'SELECT id FROM #__otrsgateway_attachments WHERE token = %s', $db->Quote( $token ) );
        $db->setQuery( $query );
        $ids = $db->loadResultArray();
        foreach ( $ids as $id )
        {
            $this->remove( $id, $token );
        }
        $this->_removeOldUploads();
        return true;
    }

    function _removeOldUploads()
    {
        // Remove all files we feel that are older than 24 hours
        $db =& JFactory::getDBO();
        $query = 'SELECT filename FROM #__otrsgateway_attachments WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(uploaded)) > 86400';
        $db->setQuery( $query );
        $rows = $db->loadResultArray();
        foreach ( $rows as $file )
        {
            if ( file_exists( $file ) )
            {
                unlink( $file );
            }
        }
        $dsql = 'DELETE FROM #__otrsgateway_attachments WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(uploaded)) > 86400';
        $db->setQuery( $dsql );
        $db->query();
    }


    function getAll( $token )
    {
        // Return all file attachment data as an array
        $result = array();
        $db =& JFactory::getDBO();
        $query = sprintf( 'SELECT filename, realname, content_type FROM #__otrsgateway_attachments WHERE token = %s', $db->Quote( $token ) );
        $db->setQuery( $query );
        $rows = $db->loadObjectList();
        foreach ( $rows as $row )
        {
            $result[] = array ( 'name' => $row->realname,
                                'type' => $row->content_type,
                                'content' => base64_encode( file_get_contents( $row->filename ) ) );
        }
        return $result;
    }

    function _getTempPath()
    {
        if ( !function_exists( 'sys_get_temp_dir' ) )
        {
            if ( $temp = getenv('TMP') )
                return $temp;
            if ( $temp = getenv('TEMP') )
                return $temp;
            if ( $temp = getenv('TMPDIR') )
                return $temp;
            $temp = tempnam(__FILE__,'');
            if ( file_exists( $temp ) )
            {
                unlink( $temp );
                return dirname( $temp );
            }
            return null;
        }
        return realpath(sys_get_temp_dir());
    }

    function getDownloadError()
    {
        return $this->_downloadError;
    }

    function setDownloadError( $error )
    {
        $this->_downloadError = $error;
    }
}

