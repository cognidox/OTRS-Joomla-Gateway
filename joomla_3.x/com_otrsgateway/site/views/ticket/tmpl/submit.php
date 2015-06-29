<?php 
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  OTRSGateway
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.framework');
$params = JComponentHelper::getParams( 'com_otrsgateway' );
?>
<noscript>
	<?php echo JText::_('COM_OTRSGATEWAY_REPLY_JS_ERROR'); ?>
</noscript>
<div id="otrs-submit-form" class="contentpaneopen">
    <div id="error-container"></div>
    <form action="<?php echo JRoute::_('index.php') . $params->get('otrsgateway_submit_link'); ?>" method="post" id="otrsNewTicketForm" name="otrsNewTicketForm" enctype="multipart/form-data">
        <div class="adminform">
        <?php if ( !empty( $this->ticketTypes ) ) { ?>
            <div class="ticketType">
                    <label for="typeID">
                        <strong><?php echo JText::_( 'COM_OTRSGATEWAY_TYPE' ); ?>:</strong>
                    </label>
                    <select name="typeID" id="typeID">
                        <option></option>
        <?php
            foreach ($this->ticketTypes as $key => $val)
            {
                echo '<option value="' . htmlspecialchars($key) . '"' .
                     '>' . htmlspecialchars($val) . '</option>';
            }
        ?>
                    </select>
            </div>
        <?php } 
        if ($params->get('otrsgateway_submit_queue') == "") 
        {
        ?>
            <div class="ticketDest">
                <label for="Dest">
                    <strong><?php echo JText::_( 'COM_OTRSGATEWAY_TO' ); ?>:</strong>
                </label>
                <select name="Dest" id="Dest">
        <?php
                foreach ($this->queues as $key => $val)
                {
                    $selected = ($this->defaultDest == $key) ? ' selected="selected"' : '';
                    echo '<option value="' . htmlspecialchars($key) . '"' . $selected .
                         '>' . htmlspecialchars($val) . '</option>';
                }
        ?>
                </select>
            </div>
        <?php 
        } else {
            echo "<input type='hidden' name='Dest' value='".$params->get('otrsgateway_submit_queue')."' />";
        }
        
        if ( $params->get('otrsgateway_submit_priority') == "0" && (! empty($this->priorityList)) )  
        {
        ?>
            <div class="ticketPrio">
                <label for="priorityID">
                    <strong><?php echo JText::_( 'COM_OTRSGATEWAY_PRIORITY' ); ?>:</strong>
                </label>

                <select name="priorityID" id="priorityID">
        <?php
            foreach ($this->priorityList as $key => $val)
            {
                $selected = ($this->defaultPriority == $val) ? ' selected="selected"' : '';
                echo '<option value="' . htmlspecialchars($key) . '"' .
                     $selected . '>' . htmlspecialchars($val) . '</option>';
            }
        ?>
                </select>
            </div>
        <?php 
        } else {
            echo "<input type='hidden' name='priorityID' value='".$params->get('otrsgateway_submit_priority')."' />";
        }
        ?>
            <div class="ticketSubj">
                <label for="Subject">
                    <strong><?php echo JText::_( 'COM_OTRSGATEWAY_SUBJECT' ); ?>:</strong>
                </label>

                <input type="text" name="Subject" class="inputbox" size="70" value="<?php echo htmlspecialchars($this->defaultSubject); ?>" />
        <?php 
            if ( isset( $this->editor ) && $params->get('otrsgateway_editor') == "0" ) {
                    echo $this->editor->display('otrsmessage', '' , '100%', '200', '75', '10', false, 'otrsmessage', null, null, array('mode'=>'simple', 'advimg' => 0, 'theme' => 'simple'));
            } else {
                    echo '<textarea name="otrsmessage" id="otrsmessage" cols="60" rows="10" style="height:auto!important; width: 100%;"></textarea>';
            }
        ?>
            </div>
        </div>
        <input type="hidden" name="option" value="com_otrsgateway" />
        <input type="hidden" name="task" value="submit" />
        <input type="hidden" name="view" value="ticket" />
        <input type="hidden" name="formtoken" value="<?php echo $this->formToken; ?>" />
        <?php echo JHTML::_( 'form.token' ); ?>
    </form>
    <div class="adminform">
        <strong id="attachmentLabel"><?php echo JText::_( 'COM_OTRSGATEWAY_ATTACHMENTS' ); ?>:</strong>
        <ul id="attachmentlist"></ul>
        <form enctype="multipart/form-data" method="post" action="index.php" id="attform" name="attform" target="attpost">
            <input type="file" onchange="this.form.submit()" name="attachment" class="addAttachment" /> 
            <input type="hidden" name="option" value="com_otrsgateway" />
            <input type="hidden" name="task" value="addAttachment" />
            <input type="hidden" name="format" value="raw" />
            <input type="hidden" name="formtoken" value="<?php echo $this->formToken; ?>" />
        </form>
        <iframe id="attpost" name="attpost" style="display:none;width:1px;height:1px" frameborder="0"></iframe>
    </div>
    <form action="index.php" onsubmit="return false;">
        <input name="submitBtn" class="btn" type="button" value="<?php echo JText::_('COM_OTRSGATEWAY_SUBMIT'); ?>" onclick="submitbutton('submit');" />
    </form>
    <form action="index.php" id="delattform" name="delattform" method="post">
        <input type="hidden" name="option" value="com_otrsgateway" />
        <input type="hidden" name="task" value="delAttachment" />
        <input type="hidden" name="format" value="raw" />
        <input type="hidden" name="fileID" value="" />
        <input type="hidden" name="formtoken" value="<?php echo $this->formToken; ?>" />
    </form>
</div>

<script language="javascript" type="text/javascript">
<!--
    function submitbutton(pressbutton) {
        var form = document.otrsNewTicketForm;
        var errorcount = 0;
        if (pressbutton == 'submit') {
            <?php 
                if ( isset( $this->editor ) && $params->get('otrsgateway_editor') == "0" ) { 
                    echo $this->editor->save( 'otrsmessage' ); 
                }
            ?>
            //clear error messages
            jQuery('.alert-error').remove();
            
            //check form
            if (form.Subject.value.length < 5) {
                jQuery('#error-container').append('<div class="alert alert-error"><p><?php echo JText::_( 'COM_OTRSGATEWAY_ALERT_PROVIDE_SUBJECT' ); ?></p></div>');
                form.Subject.focus();
                errorcount = errorcount + 1;
            } else if (form.otrsmessage.value.length < 20) {
                jQuery('#error-container').append('<div class="alert alert-error"><p class="alert-error"><?php echo JText::_( 'COM_OTRSGATEWAY_ALERT_PROVIDE_MESSAGE' ); ?></p></div>');
                form.otrsmessage.focus();
                errorcount = errorcount + 1;
            } else if (!validateEditor(form)) {
                jQuery('#error-container').append('<div class="alert alert-error"><p class="alert-error"><?php echo JText::_( 'COM_OTRSGATEWAY_ALERT_PROVIDE_MESSAGE' ); ?></p></div>');
                form.otrsmessage.focus();
                errorcount = errorcount + 1;
            }
            
            if (errorcount == 0) {
                document.otrsNewTicketForm.submit();
            }
        }
    }
    
    function addAttachment(error, id, name) {
        if (!error) {
            var fileInput = jQuery('.addAttachment');
            var maxSize = 4096; //max 4MB
            if(fileInput.get(0).files.length) {
                var fileSize = fileInput.get(0).files[0].size; // in bytes
            }
            if ((fileSize/1024) < maxSize) {
                console.log("addAttachment: " + error + " id: " + id + " name: " + name + " fileSize: " + fileSize/1024 + " MB");
                // Looks OK
                // Add the file inside the attachmentlist list
                var newEl = new Element('li', { 'id': 'att-' + id  });
                newEl.appendText(name + ' ');
                var newLink = new Element('a', { 'href': 'javascript:delAttachment("' + id + '")', 'onclick':'delAttachment("' + id + '")', 'class': 'small button' });
                newLink.appendText('<?php echo JText::_( 'COM_OTRSGATEWAY_REMOVE' ); ?>');
                newLink.inject(newEl);
                newEl.inject($('attachmentlist'));
                document.forms['attform'].reset();
            } else {
                jQuery('.alert-error').remove();
                jQuery('#error-container').append('<div class="alert alert-error"><p class="alert-error"><?php echo JText::_( 'COM_OTRSGATEWAY_ALERT_ATTACHMENT_MESSAGE' ); ?></p></div>');
                console.log("fileSize too big: " + error + " id: " + id + " name: " + name + " fileSize: " + fileSize + " Bytes");
            }
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
            echo "content = " . $this->editor->getContent( 'otrsmessage' ) . "\n";
        } else {
            echo "content = form.otrsmessage.value.trim();\n";
        }
        ?>
        return content;
    }
//-->
</script>
