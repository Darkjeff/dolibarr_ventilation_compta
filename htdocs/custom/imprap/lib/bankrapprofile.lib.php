<?php
/* Copyright (C) 2006-2015	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2012		Regis Houssin		<regis.houssin@capnetworks.com>
 * Copyright (C) 2015		Alexandre Spangaro	<aspangaro.dolibarr@gmail.com>
 * Copyright (C) 2016		Juanjo Menent   	<jmenent@2byte.es>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 * \file       htdocs/core/lib/bank.lib.php
 * \ingroup    bank
 * \brief      Ensemble de fonctions de base pour le module banque
 */

/**
 * Prepare array with list of tabs
 *
 * @param   Account	$object		Object related to tabs
 * @return  array				Array of tabs to show
 */
function bank_rappro_file()
{
    global $db, $langs, $conf, $user;
    $h = 0;
    $head = array();

    $head[$h][0] = 'index.php';
    $head[$h][1] = $langs->trans("Import");
    $head[$h][2] = 'import';
    $h++;
	
    $head[$h][0] = 'treatment.php';
    $head[$h][1] = $langs->trans("Treatment");
    $head[$h][2] = 'treatment';
    $h++;

    // complete_head_from_modules($conf, $langs, $object, $head, $h, 'bank');
	// complete_head_from_modules($conf, $langs, $object, $head, $h, 'bank', 'remove');

    return $head;
}








