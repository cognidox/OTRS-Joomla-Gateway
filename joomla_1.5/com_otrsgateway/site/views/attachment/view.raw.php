<?php
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  OTRSGateway
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');
jimport( 'joomla.filesystem.file' );

/**
 * Class to handle ticket attachment uploads
 *
 * @static
 * @package     Joomla
 * @subpackage  OTRS Gateway
 * @since 1.5
 */
class OTRSGatewayViewAttachment extends JView
{
    function display( $tpl = null )
    {
        global $mainframe;
        $model = &$this->getModel();
        switch ( JRequest::getVar( 'task' ) )
        {
            case 'addAttachment':
                $this->_addAttachment( $model, $tpl );
                break;
            case 'cleanAttachments':
                $this->_cleanAttachments( $model, $tpl );
                break;
            case 'delAttachment':
                $this->_delAttachment( $model, $tpl );
                break;
        }
        parent::display( $tpl );
    }

    function _addAttachment( $model, $tpl )
    {
        // Need to take the filename that was added, move it to a
        // location we know about, return a key to the file so that
        // the main form can deal with it
        $err = '';
        $fileID = '';
        $result = array( 'id' => '', 'name' => '', 'error' => '' );
        $file = JRequest::getVar( 'attachment', null, 'files', 'array' );
        $token = JRequest::getVar( 'formtoken' );

        if ( $file && $file['name'] && $token )
        {
            $model->store( $file, $token, $fileID );
            if ( $fileID )
            {
                $result['id'] = $fileID;
                $result['name'] = basename( $file['name'] );
            }
            else
            {
                $err = 'Unable to store temporary file';
            }
        }
        else
        {
            // Not enough data to upload
            $err = 'Unable to upload file';
        }
        $result['error'] = $err;

        // Clean up the temporary file
        if ( !empty( $err ) && file_exists( $file['tmp_name'] ) )
        {
            unlink( $file['tmp_name'] );
        }

        $this->assignRef( 'result', $result );
    }

    function _delAttachment( $model, $tpl )
    {
        // Need to remove the attachment from the temporary storage
        // area for attachments, or, return an error if it fails
        $err = '';
        $result = array( 'error' => '' );
        $token = JRequest::getVar( 'formtoken' );
        $fileID = JRequest::getVar( 'fileID' );
        if ( ! $model->remove( $fileID, $token ) )
        {
            $err = 'Unable to remove temporary upload';
            $result['error'] = $err;
        }

        $this->assignRef( 'result', $result );
    }

    function _cleanAttachments( $model, $tpl )
    {
        $result = array( 'error' => '' );
        $token = JRequest::getVar( 'formtoken' );
        if ( !$model->removeAll( $token ) )
        {
            $result['error'] = 'Unable to clean attachments';
        }

        $this->assignRef( 'result', $result );
    }
}

