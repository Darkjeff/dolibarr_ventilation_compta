<?php
/* Copyright (C) 2013-2014 Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2013-2014 Alexandre Spangaro   <alexandre.spangaro@gmail.com> 
 * Copyright (C) 2014 	   Florian Henry        <florian.henry@open-concept.pro>
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
 */

/**
 * \file accountingex/core/lib/account.lib.php
 * \ingroup Accounting Expert
 * \brief Ensemble de fonctions de base pour les comptes comptables
 */

/**
 * Prepare array with list of tabs
 *
 * @param Object $object to tabs
 * @return array of tabs to shoc
 */
function admin_account_prepare_head($object) {
	global $langs, $conf;
	
	$h = 0;
	$head = array ();
	
	$head [$h] [0] = dol_buildpath('/accountingex/admin/index.php', 1);
	$head [$h] [1] = $langs->trans("Configuration");
	$head [$h] [2] = 'general';
	$h ++;
	
	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__'); to add new tab
	// $this->tabs = array('entity:-tabname); to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'accountingex_admin');
	
	$head [$h] [0] = dol_buildpath('/accountingex/admin/journaux.php', 1);
	$head [$h] [1] = $langs->trans("Journaux");
	$head [$h] [2] = 'journal';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/accountingex/admin/export.php', 1);
	$head [$h] [1] = $langs->trans("Export");
	$head [$h] [2] = 'export';
	$h ++;
	
	$head [$h] [0] = dol_buildpath('/accountingex/admin/about.php', 1);
	$head [$h] [1] = $langs->trans("About");
	$head [$h] [2] = 'about';
	$h ++;
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'accountingex_admin', 'remove');
	
	return $head;
}

/**
 * Prepare array with list of tabs
 *
 * @param Object $object to tabs
 * @return array of tabs to shoc
 */
function account_prepare_head($object) {
	global $langs, $conf;
	
	$h = 0;
	$head = array ();
	
	$head [$h] [0] = dol_buildpath('/accountingex/admin/fiche.php', 1) . '?id=' . $object->id;
	$head [$h] [1] = $langs->trans("Card");
	$head [$h] [2] = 'card';
	$h ++;
	
	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__'); to add new tab
	// $this->tabs = array('entity:-tabname); to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'accountingex_account');
	
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'accountingex_account', 'remove');
	
	return $head;
}

/**
 * Return general account with defined length
 *
 * @param $account
 *       
 * @return $account
 */
function length_accountg($account) {
	global $conf, $langs;
	
	$g = $conf->global->ACCOUNTINGEX_LENGTH_GACCOUNT;
	
	if (! empty($g)) {
		// Clean parameters
		$i = strlen($account);
		
		if ($i >= 2) {
			while ( $i < $g ) {
				$account .= '0';
				
				$i ++;
			}
			
			return $account;
		} else {
			return $account;
		}
	} else {
		return $account;
	}
}

/**
 * Return auxiliary account with defined length
 *
 * @param $account
 *       
 * @return $account
 */
function length_accounta($accounta) {
	global $conf, $langs;
	
	$a = $conf->global->ACCOUNTINGEX_LENGTH_AACCOUNT;
	
	if (! empty($a)) {
		// Clean parameters
		$i = strlen($accounta);
		
		if ($i >= 2) {
			while ( $i < $a ) {
				$accounta .= '0';
				
				$i ++;
			}
			
			return $accounta;
		} else {
			return $accounta;
		}
	} else {
		return $accounta;
	}
}

/**
 * Return account with defined length for Sage export software
 *
 * @param $account
 *       
 * @return $account
 */
function length_exportsage($txt, $len, $end) {
	// $txt = utf8_decode($txt);
	// problem with this function, but we need to have the number of letter
	if (strlen($txt) == $len) {
		$res = $txt;
	} 

	elseif (strlen($txt) > $len) {
		$res = substr($txt, 0, $len);
	} 

	else {
		if ($end == 1) {
			$res = $txt;
		} else {
			$res = "";
		}
		for($i = strlen($txt); $i <= ($len - 1); $i ++) {
			$res .= " ";
		}
		if ($end == 0) {
			$res .= $txt;
		}
	}
	return $res;
}