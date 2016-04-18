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
class OTRSGatewayViewAttachment extends JViewLegacy
{
    function display( $tpl = null )
    {
        global $mainframe;
        $model = &$this->getModel();
		$jinput = JFactory::getApplication()->input;
		switch ( $jinput->get( 'task', null, null ) )
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
        jexit();
    }

    function displayAttachment( $data )
    {
        JResponse::clearHeaders();
        $doc = JDocument::getInstance( 'raw' );
        $fn = preg_replace( '/"/', '\"', $data->Filename);
        JResponse::setHeader( 'Content-disposition', sprintf( 'attachment;filename="%s"', $fn ), true );
        $doc->setMimeEncoding( $data->ContentType );
        $doc->render();
        JResponse::setBody( $data->Content );
        echo JResponse::toString();
        jexit();
    }

    function _addAttachment( $model, $tpl )
    {
        // Need to take the filename that was added, move it to a
        // location we know about, return a key to the file so that
        // the main form can deal with it
        $err = '';
        $fileID = '';
        $result = array( 'id' => '', 'name' => '', 'error' => '' );
		$jinput = JFactory::getApplication()->input;
		$jinput_new = new JInput($_FILES);
		$file = $jinput_new->get( 'attachment', null, 'array' );
        $token = $jinput->get( 'formtoken', null, null );		
		
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
                $err = 'Unable to store temporary file ';
            }
        }
        else
        {
            // Not enough data to upload
            $err = 'Unable to upload file' . $file['name'];
        }
        $result['error'] = $err;

        // Clean up the temporary file
        if ( !empty( $err ) && file_exists( $file['tmp_name'] ) )
        {
            unlink( $file['tmp_name'] );
        }
        
		$this->result=$result;
    }

    function _delAttachment( $model, $tpl )
    {
        // Need to remove the attachment from the temporary storage
        // area for attachments, or, return an error if it fails
        $err = '';
        $result = array( 'error' => '' );
		$jinput = JFactory::getApplication()->input;
		$token = $jinput->get( 'formtoken', null, null );
        $fileID = $jinput->get( 'fileID', null, null );
        if ( ! $model->remove( $fileID, $token ) )
        {
            $err = 'Unable to remove temporary upload';
            $result['error'] = $err;
        }
        
		$this->result=$result;
    }

    function _cleanAttachments( $model, $tpl )
    {
        $result = array( 'error' => '' );
		$jinput = JFactory::getApplication()->input;
		$token = $jinput->get( 'formtoken', null, null );
        if ( !$model->removeAll( $token ) )
        {
            $result['error'] = 'Unable to clean attachments';
        }

		$this->result=$result;
    }
}

