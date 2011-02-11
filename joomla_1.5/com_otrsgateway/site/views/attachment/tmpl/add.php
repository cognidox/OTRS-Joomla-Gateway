<?php
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  OTRSGateway
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

defined('_JEXEC') or die('Restricted access');

$document = JFactory::getDocument();
$document->setMimeEncoding('text/html');
?>
<html>
<head>
<script type="text/javascript">
<!--
parent.addAttachment( <?php echo escapeshellarg($this->result['error']); ?>,
                      <?php echo escapeshellarg($this->result['id']); ?>,
                      <?php echo escapeshellarg($this->result['name']); ?> );
// -->
</script>
</head>
<body></body>
</html>
