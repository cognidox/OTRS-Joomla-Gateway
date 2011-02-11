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

function com_uninstall()
{
    $plugins = array(
                array('search', 'otrsgateway'),
                array('authentication', 'otrsgateway'),
                );

    $where = array();
    foreach ( $plugins as $plugin )
    {
        $where[] = vsprintf("(folder='%s' AND element='%s')", $plugin);
    }

    $query = 'SELECT id FROM #__plugins WHERE '.implode( ' OR ', $where );

    $dbo = JFactory::getDBO();
    $dbo->setQuery($query);
    $tmp = $dbo->loadResultArray();
    $plugins = array();
    if ( is_array( $plugins ) && count( $plugins ) )
    {
        foreach ( $tmp as $plugin )
        {
            $plugins[$plugin] = 0;
        }

        $model = JModel::getInstance( 'Plugins', 'InstallerModel' );
        $model->remove( $plugins );
    }
    return true;
}

?>
