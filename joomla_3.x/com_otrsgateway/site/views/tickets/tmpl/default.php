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
$params = JComponentHelper::getParams( 'com_otrsgateway' );

if ($this->tickets_stats && count($this->tickets_stats)) { 

    $stat_all = 0;
    $stat_new = 0;
    $stat_successful = 0;
    $stat_unsuccessful = 0;
    $stat_pending = 0;
    $max_bar_height = 210;
    $tickets_year = 0;
    
    $date = array();
    $bars = array();
    for($i=1; $i<13; $i++) {
        $date[$i] = $i;
        $bars[$i] = "<td class='bar_column'>";
    }
    
    foreach ($this->tickets_stats as $t)
    {
        //Max 48 Chars for title
        if ( strlen(htmlspecialchars($t->Title)) > 48 ) {
            $CutTitle = substr( htmlspecialchars($t->Title),0,48 )."...";
        } else {
            $CutTitle = htmlspecialchars($t->Title);
        }
        
        //Build some stats here
        $stat_all++;
        switch ($t->State) {
            case "new":
                $stat_new++;
                break;
            case "pending reminder":
                $stat_pending++;
                break;
            case "pending auto close-":
                $stat_pending++;
                break;
            case "pending auto close+":
                $stat_pending++;
                break;
            case "closed successful":
                $stat_successful++;
                break;
            case "closed unsuccessful":
                $stat_unsuccessful++;
                break;
            case "open":
                $stat_pending++;
                break;
            default:
                $stat_pending++;
        }
            
        //fill chart bars with tickets
        foreach ($date as $d) {
            if (date('Y.' . str_pad($d, 2 ,'0', STR_PAD_LEFT)) === date( 'Y.m',htmlspecialchars($t->CreateTimeUnix) )) {
                $bars[$d] .= "<a href='".JRoute::_('index.php?view=ticket&ticketID='.$t->TicketID)."'><div class='bar' title='".$CutTitle."'></div></a>";
                $tickets_year++;
            }
        }
        
    }
    
    //set the size the tickets should have in the chart bar
    if ($tickets_year <> 0) {
        $max_ticket_height = round($max_bar_height/$tickets_year);
    } else {
        $max_ticket_height = $max_bar_height;
    }
    echo '
        <style type="text/css">
            .bar {
                height:'.$max_ticket_height.'px;
            }
        </style>
    ';
    
    //close chart bars
    for($i=1; $i<13; $i++) {
        $month = date('Y') . "-" . str_pad($i, 2 ,'0', STR_PAD_LEFT) . "-" . "01";
        $bars[$i] .= date('M',strtotime($month))."</td>";
    }
    
    echo "<div class='ticket-summary'>";
    
    //print stats
    echo "<div id='ticket-stats'><h4>" . JText::_( 'COM_OTRSGATEWAY_STAT_LABEL' ) . "</h4><table height='220' width='100%' cellpadding='0' cellspacing='0'>";
    echo "<tbody";
    echo "<tr><td><h6>" . JText::_( 'COM_OTRSGATEWAY_STAT_ALL' ) . ":</h5></td><td><h5>" . $stat_all . "</h6></td></tr>";
    echo "<tr><td><h6>" . JText::_( 'COM_OTRSGATEWAY_STAT_NEW' ) . ":</h5></td><td><h5>" . $stat_new . "</h6></td></tr>";
    echo "<tr><td><h6>" . JText::_( 'COM_OTRSGATEWAY_STAT_OPEN' ) . ":</h5></td><td><h5>" . $stat_pending . "</h6></td></tr>";
    echo "<tr><td><h6>" . JText::_( 'COM_OTRSGATEWAY_STAT_SUCCESSFUL' ) . ":</h5></td><td><h5>" . $stat_successful . "</h6></td></tr>";
    echo "<tr><td><h6>" . JText::_( 'COM_OTRSGATEWAY_STAT_UNSUCCESSFUL' ) . ":</h5></td><td><h5>" . $stat_unsuccessful . "</h6></td></tr>";
    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    //print chart
    echo "<div id='ticket-dia'><h4>" . $tickets_year . " Tickets in " . date('Y') . ":</h4><table height='220' width='100%' cellpadding='0' cellspacing='0' align='right'><tbody><tr valign='bottom' height='210'>";
    foreach ($bars as $b) {
        echo $b;  
    }
    echo "</tr></tbody></table></div>";
    
    echo "<div style='clear:both; padding: 10px;'>&nbsp;</div></div>";
}

if ($this->tickets && count($this->tickets)) { 

    // Loop through the ticket, writing out the various bits
    $row = true;

    // Sort Tickets in Overview. Show last changed first
    function do_compare($i1, $i2) {
        $t1 = strtotime(htmlspecialchars($i1->Changed));
        $t2 = strtotime(htmlspecialchars($i2->Changed));
        return $t2 - $t1;
    }
    $tickets = $this->tickets;
    usort($tickets, 'do_compare');
    $result_tickets_view = "";
    
    foreach ($tickets as $t)
    {
        //Max 48 Chars for title
        if ( strlen(htmlspecialchars($t->Title)) > 48 ) {
            $CutTitle = substr( htmlspecialchars($t->Title),0,48 )."...";
        } else {
            $CutTitle = htmlspecialchars($t->Title);
        }
                           
        //generate tickets table
        $url = JRoute::_('index.php?view=ticket&ticketID=' . $t->TicketID);
        
        if ($params->get('otrsgateway_tickets_showclosed') == "0") {
            if ($t->State == "closed successful" || $t->State == "closed unsuccessful") {
                $result_tickets_view .= '<tr class="sectiontableentry' . ($row ? 1 : 2) . '">' .
                     '<td id="otrs-ticket-id">' .
                     htmlspecialchars(trim($t->TicketNumber)) . '</td>';
            } else {
                $result_tickets_view .= '<tr class="sectiontableentry' . ($row ? 1 : 2) . '">' .
                     '<td id="otrs-ticket-id"><a href="' . $url . '">' .
                     htmlspecialchars(trim($t->TicketNumber)) . '</a></td>';
            }
        } else {
            $result_tickets_view .= '<tr class="sectiontableentry' . ($row ? 1 : 2) . '">' .
                '<td id="otrs-ticket-id"><a href="' . $url . '">' .
                htmlspecialchars(trim($t->TicketNumber)) . '</a></td>';
        }
        
        $result_tickets_view .= '<td id="otrs-ticket-title">' . $CutTitle . '</td>';
        if ($params->get('otrsgateway_tickets_reporter') == "1") 
            $result_tickets_view .= '<td>' . htmlspecialchars($t->CustomerUserID) . '</td>';

        if ($params->get('otrsgateway_tickets_priority') == "1")
            $result_tickets_view .= '<td>' . htmlspecialchars(JText::_($t->Priority)) . '</td>';

        $result_tickets_view .= '<td id="otrs-ticket-state">' . translateOTRSTicketState(htmlspecialchars(JText::_($t->State)),true) . '</td>';
        
        $created = htmlspecialchars($t->CreateTimeUnix);
        $changed = strtotime(htmlspecialchars($t->Changed));
        
        $result_tickets_view .= '<td id="otrs-ticket-created">' . date( 'd.m.Y H:i',$created ) . '</td>';
        $result_tickets_view .= '<td id="otrs-ticket-changed">' . date( 'd.m.Y H:i',$changed ) . '</td>';
        $result_tickets_view .= '</tr>';
        $row = !$row;
    }
?>

<table class="otrs-ticket-table">
<thead>
<tr>
<th class='sectiontableheader'>Ticket#</th>
<th class='sectiontableheader'>Titel</th>
<?php 
    if ($params->get('otrsgateway_tickets_reporter') == "1") 
        echo "<th class='sectiontableheader'>Reporter</th>";
        
    if ($params->get('otrsgateway_tickets_priority') == "1")
        echo "<th class='sectiontableheader'>Priority</th>";
?>
<th class='sectiontableheader'>Status</th>
<th class='sectiontableheader'>Erstellt</th>
<th class='sectiontableheader'>Aktualisiert</th>
</tr>
</thead>
    <tbody>
        <?php echo $result_tickets_view; ?>
    </tbody>
    <tfoot>
        <tr>
        </tr>
    </tfoot>
</table>
<?php } else {
	echo "<span style='font-weight: bold;'>".JText::_('COM_OTRSGATEWAY_NOTICKETS')."</span>";
} ?>
<p style="padding-top: 5px;">
    <a class="button" href="<?php echo $this->toggleLink; ?>"><?php echo $this->toggleLinkText; ?></a>
</p>