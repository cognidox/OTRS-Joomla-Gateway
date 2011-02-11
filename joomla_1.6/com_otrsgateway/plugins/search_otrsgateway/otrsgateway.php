<?php
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  OTRSGateway
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class OTRSGatewaySearchResObj
{
    var $href = "";
    var $title = "";
    var $text = "";
    var $section = '';
    var $created = 0;
    var $browsernav = '0';
}

class plgSearchOTRSGateway extends JPlugin
{

    public function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    /**
    * @return array An array of search areas
    */
    function onContentSearchAreas()
    {
	    static $areas = array(
		    'otrsgateway' => 'PLG_SEARCH_OTRSGATEWAY_SECTION'
	    );
	    return $areas;
    }

    /**
     * OTRS Gateway Search method
     *
     * The sql must return the following fields that are used in a common display
     * routine: href, title, section, created, text, browsernav
     * @param string Target search string
     * @param string matching option, exact|any|all
     * @param string ordering option, newest|oldest|popular|alpha|category
     */
    function onContentSearch( $text, $phrase='', $ordering='', $areas=null )
    {
	    if (is_array( $areas ))
        {
		    if (!array_intersect( $areas, array_keys( $this->onContentSearchAreas() ) ))
            {
			    return array();
		    }
	    }

	    $text = trim( $text );
	    if ( empty( $text ) )
        {
		    return array();
	    }

        // Look for the OTRS Gateway component
        $com_otrsgateway =& JComponentHelper::getComponent( 'com_otrsgateway' );
        if ( !$com_otrsgateway )
            return array();

        // Load the Gateway helper library
        include_once( JPATH_SITE . DS . "administrator" . DS ."components" . DS . 
                      "com_otrsgateway" . DS . "helpers" . DS . "rpchelper.php" );
        include_once( JPATH_SITE . DS . "components" . DS . 
                      "com_otrsgateway" . DS . "helpers" . DS . "userhelper.php" );

        $userData = OTRSGatewayUserHelper::getOTRSUserData();
        if ( $userData == null )
        {
            return array();
        }

        $vars = array( 'StateType' => array( 'Open', XSD_STRING ),
                       'CustomerUserID' => array( $userData[0], XSD_STRING  ),
                       'Result' => array( 'ARRAY', XSD_STRING  ),
                       'ContentSearch' => array( 'OR', XSD_STRING  ),
                       'IncludeDescription' => array( 1, XSD_INTEGER  ),
                       'FullTextIndex' => array( 1, XSD_INTEGER  ),
                       'Subject' => array( '%' . mysql_real_escape_string( $text ) . '%', XSD_STRING  ),
                       'Body' => array( '%' . mysql_real_escape_string( $text ) . '%', XSD_STRING  ),
                 );
        switch ( $ordering )
        {
            case 'oldest':
                $vars['SortBy'] = array( 'Age', XSD_STRING );
                $vars['OrderBy'] = array( 'Up', XSD_STRING );
                break;
            default:
                $vars['SortBy'] = array( 'Age', XSD_STRING );
                $vars['OrderBy'] = array( 'Down', XSD_STRING );
        }
    
        $rows = array();
        $result = null;
        $gateway = new OTRSGatewayRPCHelper();
        if ( $err = $gateway->callOTRS(
                            'JoomlaGatewayObject', 'TicketSearch', $vars,
                            $result ) )
        {
            // Failed to find anything
            return array();
        }
        if ( $result == null )
        {
            return array();
        }
        foreach ( $result as $ticket )
        {
            $item = new OTRSGatewaySearchResObj;
            $item->title = sprintf( '[%s] %s', $ticket->TicketNumber, $ticket->Title );
            $item->href = JRoute::_( 'index.php?option=com_otrsgateway&view=ticket&ticketID=' . $ticket->TicketID );
            $item->created = (int) $ticket->CreateTimeUnix;
            $item->text = $ticket->Description;
            $item->section = JText::_( 'PLG_SEARCH_OTRSGATEWAY_SECTION' );
            $rows[] = $item;
        }

	    return $rows;
    }
}

