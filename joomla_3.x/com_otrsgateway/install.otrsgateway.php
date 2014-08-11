<?php
/**
 * Copyright (c) 2010 Cognidox Ltd 
 * http://www.cognidox.com/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


$manifest = $this->parent->get("manifest");
$source = $this->parent->getPath("source");

$installer = new JInstaller();
$plugin_names = array();
// Install plugins
foreach ($manifest->plugins->plugin as $plugin)
{
    $attributes = $plugin->attributes();
    $plg = $source . DIRECTORY_SEPARATOR . $attributes['folder'];
    $installer->install($plg);
    $plugin_names[] = $attributes['plugin'];
}

//
$db = JFactory::getDbo();
$tableExtensions = $db->quoteName("#__extensions");
$columnElement   = $db->quoteName("element");
$columnType      = $db->quoteName("type");
$columnEnabled   = $db->quoteName("enabled");

foreach ($plugin_names as $plugin)
{
    $db->setQuery(
        "UPDATE $tableExtensions SET $columnEnabled=1 WHERE 
        $columnElement='$plugin' AND $columnType='plugin'" );
    $db->query();
}


function com_install()
{
    echo JText::sprintf('COM_OTRSGATEWAY_INSTALLED');
    return true;
}

?>
