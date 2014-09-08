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

/**
 * Class to view a ticket attachment
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

        $err = $model->getDownloadError();
        if ( !$err )
        {
            $err = 'Unable to find attachment';
        }
        //$this->assignRef( 'error', JText::_( $err ) );
		$this->error=JText::_( $err );
        parent::display( $tpl );
    }

}

