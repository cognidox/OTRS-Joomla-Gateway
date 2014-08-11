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
require_once( JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'fieldhelper.php' );
?>
<h1 class="componentheading"><?php 
    $title = "";
    switch ( $this->listType )
    {
        case ( 'company' ):
            $title = 'Company Tickets';
            break;
        default:
            $title = 'Tickets';
    }
    echo JText::_( $title ); ?></h1>
<table class="otrs-ticket-table">
<thead>
<tr>
<th class='sectiontableheader'>Ticket#</th>
<th class='sectiontableheader'>Titel</th>
<!--<th class='sectiontableheader'>Reporter</th>
<th class='sectiontableheader'>Priority</th>-->
<th class='sectiontableheader'>Status</th>
<th class='sectiontableheader'>Erstellt</th>
<th class='sectiontableheader'>Aktualisiert</th>
</tr>
</thead>
<tbody>
<?php
    // Loop through the ticket, writing out the various bits
    if ($this->tickets && count($this->tickets))
    {
        $row = true;
        foreach ($this->tickets as $t)
        {
            $url = JRoute::_('index.php?view=ticket&ticketID=' . $t->TicketID);
            echo '<tr class="sectiontableentry' . ($row ? 1 : 2) . '">' .
                 '<td><a href="' . $url . '">' .
                 htmlspecialchars(trim($t->TicketNumber)) . '</a></td>';
            echo '<td>' . htmlspecialchars($t->Title) . '</td>';
            echo '<!--<td>' . htmlspecialchars($t->CustomerUserID) . '</td>';
            echo '<td>' . htmlspecialchars(JText::_($t->Priority)) . '</td>-->';
            echo '<td>' . translateOTRSTicketState(htmlspecialchars(JText::_($t->State))) . '</td>';
            echo '<td>' . htmlspecialchars($t->Created) . '</td>';
            echo '<td>' . htmlspecialchars($t->Changed) . '</td>';
            echo '</tr>';
            $row = !$row;
        }
    }
?>
</tbody>
<tfoot>
<tr>
</tr>
</tfoot>
</table>

<p>
<a class="button" href="<?php echo $this->toggleLink; ?>"><?php echo $this->toggleLinkText; ?></a>
</p>
