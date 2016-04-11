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

    if ($params->get('otrsgateway_tickets_showclosed') == "0") {
        if ($this->ticket->State == "closed successful" || $this->ticket->State == "closed unsuccessful") {
            echo "<span style='font-weight: bold;'>" . JText::_( 'COM_OTRSGATEWAY_TICKET_ACCESS_DENIED' ) . "</span>";
            exit;
        }
    }
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
            if ($params->get('otrsgateway_ticket_systemmail') <> "") {
                if ( strtolower( $matches[1] ) == strtolower( $params->get('otrsgateway_ticket_systemmail') ) )
                {
                    $extraclass = 'otrs-reply-other';
                }
            } else { 
                if ( strtolower( $matches[1] ) != strtolower( $user->email ) )
                {   
                    $extraclass = 'otrs-reply-other';
                }
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
<noscript>
    <?php echo JText::_('COM_OTRSGATEWAY_REPLY_JS_ERROR'); ?>
</noscript>
<div class="contentpaneopen">
    <h2><?php echo JText::_('COM_OTRSGATEWAY_REPLY'); ?></h2>
    <a name="endticket">&nbsp;</a>
        
    <script type="text/javascript">
    <!--
    var locked = false;
    // -->
    </script>

    <div id="otrs-reply-form" class="contentpaneopen">

    <form action="index.php" method="post" id="otrsReplyForm" name="otrsReplyForm">
        <div class="adminform" style="vertical-align:top;width:100%;">
            <div id="error-container"></div>
        <?php
            if ( isset( $this->editor ) && $params->get('otrsgateway_editor') == "0" ) {
                echo $this->editor->display('otrsreplytext', '' , '100%', '200', '75', '10', false, 'otrsreplytext', null, null, array('mode'=>'simple', 'advimg' => 0, 'theme' => 'simple'));
            } else {
                echo '<textarea name="otrsreplytext" id="otrsreplytext" cols="60" rows="10" style="height:auto!important; width: 100%;"></textarea>';
            }  
        
        if ( $params->get('otrsgateway_submit_priority') == "0" && (! empty($this->priorityList)) )  
        {
        ?>
            <div class="priority" style="margin-top: 30px;">
                <strong><?php echo JText::_( 'COM_OTRSGATEWAY_PRIORITY' ); ?>:</strong>
                <select name="priorityID" id="priorityID">
        <?php
            foreach ($this->priorityList as $key => $val)
            {
                echo '<option value="' . htmlspecialchars($key) . '"' .
                     ($key == $this->ticket->PriorityID ? ' selected="selected"' : '') .
                     '>' . htmlspecialchars($val) . '</option>';
            }
        ?>
                </select>
            </div>
        <?php 
        } else { 
            echo "<input type='hidden' name='priorityID' value='".$params->get('otrsgateway_submit_priority')."' />";
        }
        ?>
            <div class="state" style="margin-top: 30px;">
                <strong><?php echo JText::_( 'COM_OTRSGATEWAY_NEXT_STATE' ); ?>:</strong>
                <select name="StateID" id="StateID" style="width: 45%;">
        <?php
            foreach ($this->stateList as $key => $val)
            {
                echo '<option value="' . htmlspecialchars($key) . '"' .
                     ($key == $this->ticket->StateID ? ' selected="selected"' : '') .
                     '>' . htmlspecialchars($val) . '</option>';
            }
        ?>
                </select>
            </div>
        </div>
        <input type="hidden" name="ticketID" value="<?php echo htmlspecialchars($this->ticket->TicketID);?>" />
        <input type="hidden" name="option" value="com_otrsgateway" />
        <input type="hidden" name="task" value="reply" />
        <input type="hidden" name="view" value="ticket" />
        <input type="hidden" name="format" value="raw" />
        <input type="hidden" name="formtoken" value="<?php echo $this->formToken; ?>" />
        <?php echo JHTML::_( 'form.token' ); ?>
    </form>
    <div class="adminform" style="vertical-align:top;width:100%">
        <strong style="float: left; padding-right: 48px;"><?php echo JText::_( 'COM_OTRSGATEWAY_ATTACHMENTS' ); ?>:</strong>
        <ul id="attachmentlist"></ul>
        <form enctype="multipart/form-data" method="post" action="index.php" id="attform" name="attform" target="attpost">
            <input type="file" onchange="this.form.submit()" name="attachment" /> 
            <input type="hidden" name="option" value="com_otrsgateway" />
            <input type="hidden" name="task" value="addAttachment" />
            <input type="hidden" name="format" value="raw" />
            <input type="hidden" name="formtoken" value="<?php echo $this->formToken; ?>" />
        </form>
        <iframe id="attpost" name="attpost" style="display:none;width:1px;height:1px" frameborder="0"></iframe>
    </div>

    <form action="index.php" id="delattform" name="delattform" method="post">
        <input type="hidden" name="option" value="com_otrsgateway" />
        <input type="hidden" name="task" value="delAttachment" />
        <input type="hidden" name="format" value="raw" />
        <input type="hidden" name="fileID" value="" />
        <input type="hidden" name="formtoken" value="<?php echo $this->formToken; ?>" />
    </form>

    <form id="replyForm" action="index.php" method="get" onsubmit="return false;">
        <div id="loading" style="display: none; float: left; padding: 0 10px;"><img src="<?php echo JURI::root(); ?>components/com_otrsgateway/views/img/ajax-loader.gif"/></div>
        <input name="submit" id="submitButton" class="btn" type="button" value="<?php echo JText::_('COM_OTRSGATEWAY_SUBMIT'); ?>" onclick="submitbutton('submit');" />
    </form>

    </div>
    <script language="javascript" type="text/javascript">
        function submitbutton(pressbutton) {
            jQuery('#loading').show();
            var form = document.otrsReplyForm;
            var errorcount = 0;
            if (pressbutton == 'submit') {	
                <?php if ( isset( $this->editor ) && $params->get('otrsgateway_editor') == "0" ) { 
                        echo $this->editor->save( 'otrsreplytext' );
                ?>
                        if (!jQuery('#otrsreplytext').val() || jQuery('#otrsreplytext').val().length <= 1) {
                            replyval = <?php echo $this->editor->getContent('otrsreplytext'); ?>
                            replyval.replace( /\r?\n/g, "<br>" );
                            jQuery('#otrsreplytext').val(replyval);
                        }
                <?php } ?>
                
                //clear error messages
                jQuery('.alert-error').remove();
                
                //check form
                if (!validateEditor(form)) {
                    jQuery('#error-container').append('<div class="alert alert-error"><p class="alert-error"><?php echo JText::_( 'COM_OTRSGATEWAY_ALERT_PROVIDE_REPLY' ); ?></p></div>');
                    form.otrsreplytext.focus();
                    errorcount = errorcount + 1;
                }
                
                if (errorcount == 0) {
                    
                    $('otrsReplyForm').set('send', {
                        noCache: true,
                        onComplete: function(resp) {
                            var obj = JSON.decode(resp);
                            window.parent.closeReply(obj.error,document.forms['otrsReplyForm'].elements['formtoken'].value);
                        },
                        onFailure: function(resp) {
                            alert("<?php echo JText::_('COM_OTRSGATEWAY_ALERT_SUBMISSION_FAILED');?>");
                            window.parent.closeReply(null,document.forms['otrsReplyForm'].elements['formtoken'].value);
                        }
                    }).send();
                    
                } else {
                    jQuery('#loading').hide();
                    jQuery('.alert-error').show();
                }	
            }
        }
        
        function addAttachment(error, id, name) {
            if (!error) {
                var newEl = new Element('li', { 'id': 'att-' + id  });
                newEl.appendText(name + ' ');
                var newLink = new Element('a', { 'href': 'javascript:delAttachment("' + id + '")', 'onclick':'delAttachment("' + id + '")', 'class': 'small button' });
                newLink.appendText('<?php echo JText::_( 'COM_OTRSGATEWAY_REMOVE' ); ?>');
                newLink.inject(newEl);
                newEl.inject($('attachmentlist'));
                document.forms['attform'].reset();
            } else {
                console.log("error addAttachment: " + error + " id: " + id + " name: " + name);
            }
        }

        function delAttachment( id ) {
            document.forms['delattform'].elements['fileID'].value = id;
            $('delattform').set('send', {
                noCache: true,
                onComplete: function(resp){
                                var el = $('att-' + id);
                                if (el) { el.dispose(); }
                            }
            }).send();
        }
        
        function validateEditor(form) {
            var content = '';
            <?php 
            if ( isset( $this->editor ) && $params->get('otrsgateway_editor') == "0" ) { 
                echo "	content = " . $this->editor->getContent( 'otrsreplytext' ) . ";\r
                        if (content == null) {\r
                            content = false;\r
                        } else {\r
                            if (content.length < 20) {\r
                                content = false;\r
                            }\r
                        }\r
                ";
            } else {
                echo "	content = form.otrsreplytext.value.trim();\r
                        if (content == null) {\r
                            content = false;\r
                        } else {\r
                            content = '<p>' + content.replace(/\\r\\n?|\\n/g, '<br />\\r\\n') + '</p>';\r
                            if (content.length < 20) {\r
                                content = false;\r
                            }\r
                        }\r		
                ";
            }
            ?>
            return content;
        }
    </script>
    
</div>
<?php } ?>