<?php
/**
 * @version     $Id$
 * @package     Joomla
 * @subpackage  OTRSGateway
 * @copyright   Copyright (C) 2010 Cognidox Ltd
 * @license  GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

defined('_JEXEC') or die('Restricted access');

?>
<h1 class="contentheading"><?php echo JText::_( 'OTRS_ERROR' ); ?></h1>
<p><?php echo JText::_( 'OTRS_UNABLE_TO_DOWNLOAD' ); ?>:<br />
<b><?php echo htmlspecialchars($this->error); ?></b>
</p>
