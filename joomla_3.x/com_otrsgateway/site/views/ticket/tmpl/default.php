<?php
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  OTRSGateway
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

defined('_JEXEC') or die('Restricted access');
require_once( JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'fieldhelper.php' );

JHtml::_('behavior.framework');
JHTML::_('behavior.modal');
$params = JComponentHelper::getParams( 'com_otrsgateway' );

$attachmentIndex = array();

if ($this->ticket->TicketNumber == "") {
    echo "<span style='font-weight: bold;'>" . JText::_( 'COM_OTRSGATEWAY_UNABLE_TO_PROCESS_REQUEST' ) . "</span>";
} else {
?>
<script type="text/javascript">
<!--
    function closeReply(err, token) {
        cancelReply(token);
        if (err) {
            alert(err);
        } else {
            var loc = window.location;
            loc.hash = '#endticket';
            window.location.assign(loc.toString());
            window.location.reload(true);
        }
    }
    
    function cancelReply(token) {
        SqueezeBox.close();
        new Request({
            url: '<?php echo $this->delAttLink; ?>' + '&formtoken=' + token, 
            method: 'get',
            async: false}).send();
    }
 -->
</script>
<h4 style="float:left;" id="otrs-ticket-heading" class="componentheading">
    <?php echo "[" . htmlspecialchars(trim($this->ticket->TicketNumber)) . "] " . htmlspecialchars(trim($this->ticket->Title)); ?>
</h4>
<a style="float:right;" class="btn" href="<?php echo JRoute::_( 'index.php?option=com_otrsgateway&view=ticket&ticketID=' . $ticket->TicketID ) . $this->ticket->TicketID; ?>#endticket"><?php echo JText::_('COM_OTRSGATEWAY_REPLY');?></a>
<div style="clear:both;" class="contentpaneopen otrs-ticket-status">
    <div class="article-tools">
        <div class="article-meta">
            <table class="small" id="otrs-ticket-status-table" style="width:100%;">
            <tr>
                <td class="otrs-ticket-status-col1">
                    <b><?php echo JText::_('COM_OTRSGATEWAY_CREATED'); ?>:</b>
                </td>
                <td class="otrs-ticket-status-col2">
                    <?php 
                        $created = strtotime(htmlspecialchars($this->ticket->Created));
                        echo date( 'd.m.Y H:i',$created ); 
                    ?>
                </td>
                <td class="otrs-ticket-status-col3">
                    <b><?php echo JText::_('COM_OTRSGATEWAY_STATE');?>:</b>
                </td>
                <td class="otrs-ticket-status-col4">
                    <?php echo translateOTRSTicketState(htmlspecialchars($this->ticket->State)); ?>
                </td>
            </tr>
            <tr>
                <td class="otrs-ticket-status-col1">
                    <b><?php echo JText::_('COM_OTRSGATEWAY_UPDATED');?>:</b> 
                </td>
                <td class="otrs-ticket-status-col2">
                    <?php 
                        $changed = strtotime(htmlspecialchars($this->ticket->Changed));
                        echo date( 'd.m.Y H:i',$changed ); 
                    ?>
                </td>
                <td class="otrs-ticket-status-col3">
                    <?php 
                        if ($params->get('otrsgateway_ticket_priority') == "1")
                            echo "<b>" . JText::_('COM_OTRSGATEWAY_PRIORITY') . ":</b>";
                    ?>
                </td>
                <td class="otrs-ticket-status-col4">
                    <?php
                        if ($params->get('otrsgateway_ticket_priority') == "1")
                            echo htmlspecialchars($this->ticket->Priority); 
                    ?>
                </td>
            </tr>
            <tr>
                <td class="otrs-ticket-status-col1">
                    <b><?php echo JText::_('COM_OTRSGATEWAY_FROM');?>:</b>
                </td>
                <td colspan="3">
                     <?php echo htmlspecialchars($this->ticket->Main->From); ?>
                </td>
            </tr>
            <tr>
                <td class="otrs-ticket-status-col1">
                    <?php 
                        if ($params->get('otrsgateway_ticket_queue') == "1")
                            echo "<b>" . JText::_('COM_OTRSGATEWAY_QUEUE') . ":</b>";
                    ?>
                </td>
                <td class="otrs-ticket-status-col2">
                    <?php 
                        if ($params->get('otrsgateway_ticket_queue') == "1")
                            echo htmlspecialchars($this->ticket->Queue);
                    ?>
                </td>
                <td class="otrs-ticket-status-col3"></td>
                <td class="otrs-ticket-status-col4"></td>
            </tr>
            </table>
        </div>
    </div>
</div>

<?php if (! empty($this->ticket->Attachments) )
{
?>
<div class="small otrs-attachment-box" id="otrs-top-attachments">
    <span><b><?php echo JText::_('COM_OTRSGATEWAY_ATTACHMENTS'); ?>:</b></span><br />
<?php
    foreach ($this->ticket->Attachments as $atm)
    {  
        $atmsHtml = '';
        foreach ($atm->Atms as $aid => $a)
        {
            $link = JRoute::_('index.php?option=com_otrsgateway&view=attachment&ArticleID=' . $atm->ArticleID . '&AtmID=' . $aid);
            $atmsHtml .= '<a href="' . $link . '">' .
                         htmlspecialchars($a->Filename) . '</a> (' .
                         htmlspecialchars($a->Filesize) . ')<br />';
        }
        echo $atmsHtml;
        $attachmentIndex['a_' . $atm->ArticleID] = $atmsHtml;
    }
?>
</div>
<?php } ?>


<h2><?php echo JText::_('COM_OTRSGATEWAY_DESCRIPTION'); ?></h2>
<div id="otrs-ticket-description" class="contentpaneopen otrs-comment-text">
<?php
    if ($this->ticket->Main->Type == 'text/html') {
        echo strip_tags($this->ticket->Main->Body, $this->allowedTags);
    } else {
        echo '<div class="otrs-monospace-text">' . htmlspecialchars($this->ticket->Main->Body) . '</div>';
    }
?>
</div>


<?php
if ( ! empty( $this->ticket->ArticleIndex ) )
{
    $user =& JFactory::getUser();
?>
<hr />
<h2><?php echo JText::_('COM_OTRSGATEWAY_COMMENTS'); ?></h2>
<div class="contentpaneopen" id="otrs-ticket-comments">
<?php
    $aid = 1;
    foreach ($this->ticket->ArticleIndex as $article)
    {
        $extraclass = 'otrs-reply-self';
        if ( preg_match( '/^.+?<([^>]+)>/', $article->From, $matches ) )
        {
            if ( strtolower( $matches[1] ) != strtolower( $user->email ) )
            {
                $extraclass = 'otrs-reply-other';
            }
        }
        echo '<div class="otrs-ticket-comment ' . $extraclass . '"><div class="article-tools"><div class="article-meta otrs-comment-from">' .
             '<b>' . htmlspecialchars($article->From) . '</b> ' .
             htmlspecialchars($article->Created) . '</div>' .
             '</div>';
        echo sprintf( '<div class="otrs-comment-text" id="otrs-comment-%s">', $aid);
        if ($article->Type == 'text/html') {
            echo strip_tags($article->Body, $this->allowedTags);
        } else {
            echo '<div class="otrs-monospace-text">' . htmlspecialchars($article->Body) . '</div>';
        }
        echo '</div>';
        if (array_key_exists( 'a_' . $article->ArticleID, $attachmentIndex) )
        {
            echo '<div class="otrs-attachment-box">' . $attachmentIndex['a_' . $article->ArticleID] .
                 '</div>';
        }
        echo '</div>';
        $aid += 1;
    }
?>
</div>
<?php } ?>

<div class="contentpaneopen">
    <h2><?php echo JText::_('COM_OTRSGATEWAY_REPLY'); ?></h2>
    <a name="endticket">&nbsp;</a>
    <iframe style="max-width: 100%; height: 555px; width: 100%;" src="index.php?option=com_otrsgateway&task=replyForm&ticketID=<?php echo $this->ticket->TicketID; ?>"></iframe>
</div>
<?php } ?>