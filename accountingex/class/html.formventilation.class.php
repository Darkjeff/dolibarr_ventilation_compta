<?php
/* Copyright (C) 2013      Florian Henry        <florian.henry@open-concept.pro>
 * Copyright (C) 2013-2014 Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2013-2014 Alexandre Spangaro   <alexandre.spangaro@gmail.com>
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
 * \file    accountingex/class/html.formventilation.class.php
 * \brief   Class for HML form
 */
class FormVentilation extends Form {
	var $db;
	var $error;
	
	/**
	 * Constructor
	 * 
	 * @param DoliDB $db handler
	 */
	function __construct($db) {
		$this->db = $db;
		return 1;
	}
	
	/**
	 *	Return select filer with date of transaction
	 *
	 *  @param	string	$htmlname 		name of input
	 *  @param	string	$selectedkey	selected default value
	 *  @param	int		$custid 		customerid
	 *  @param	int 	$shopid 		shopid
	 *  @param	string 	$type 			'histoshop' or 'histocust' or ''
	 *	@return	string					HTML select input
	 */
	function select_bookkeeping_importkey ($htmlname='importkey',$selectedkey) {
	
		global $langs;
	
		$date_array=array();
	
		$sql='SELECT DISTINCT import_key from '.MAIN_DB_PREFIX.'bookkeeping ';
		$sql.=' ORDER BY import_key DESC';
	
		
		$out='<SELECT name="'.$htmlname.'">';
		
		dol_syslog(get_class($this)."::select_bookkeeping_importkey sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$i=0;
			$num = $this->db->num_rows($resql);
				
			while ($i<$num)
			{
				$obj = $this->db->fetch_object($resql);
				
				$selected='';
				if ($selectedkey==$obj->import_key) {
					$selected=' selected="selected" ';
				}
	
				$out.='<OPTION value="'.$obj->import_key.'"'.$selected.'>'.$obj->import_key.'</OPTION>';
	
				$i++;
			}
	
		}else {
			$this->error="Error ".$this->db->lasterror();
			dol_syslog(get_class($this)."::select_bookkeeping_importkey ".$this->error, LOG_ERR);
			return -1;
		}
		
		$out.='</SELECT>';
	
		return $out;
	}
	
	function select_account_parent($selectid, $htmlname = 'account_parent', $showempty = 0, $event = array()) {
		global $conf, $user, $langs;
	
		$out = '';
	
		$sql = "SELECT DISTINCT aa.account_number, aa.label, aa.rowid, aa.fk_pcg_version";
		$sql .= " FROM " . MAIN_DB_PREFIX . "accountingaccount as aa";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "accounting_system as asy ON aa.fk_pcg_version = asy.pcg_version";
		$sql .= " AND asy.rowid = ".$conf->global->CHARTOFACCOUNTS;
    $sql .= " AND aa.active = 1";
		$sql .= " ORDER BY aa.account_number";
	
		dol_syslog ( get_class ( $this ) . "::select_account_parent sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
	
			$out .= ajax_combobox ( $htmlname, $event );
	
	
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows ( $resql );
			$i = 0;
			if ($num) {
				while ( $i < $num ) {
					$obj = $this->db->fetch_object ( $resql );
					$label = $obj->account_number.'-'.$obj->label;
	
					if (($selectid != '') && $selectid == $obj->account_number) {
						$out .= '<option value="' . $obj->account_number . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->account_number . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error ( $this->db );
		}
		$this->db->free ( $resql );
		return $out;
	}

	
		function select_pcgtype($selectid, $htmlname = 'pcg_type', $showempty = 0, $event = array()) {
		global $conf, $user, $langs;
	
		$out = '';
	
		$sql = "SELECT DISTINCT pcg_type ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "accountingaccount ";
		$sql .= " ORDER BY pcg_type";
	
		dol_syslog ( get_class ( $this ) . "::select_pcg_type sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
	
			$out .= ajax_combobox ( $htmlname, $event );
	
	
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows ( $resql );
			$i = 0;
			if ($num) {
				while ( $i < $num ) {
					$obj = $this->db->fetch_object ( $resql );
					$label = $obj->pcg_type;
	
					if (($selectid != '') && $selectid == $obj->pcg_type) {
						$out .= '<option value="' . $obj->pcg_type . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->pcg_type . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error ( $this->db );
		}
		$this->db->free ( $resql );
		return $out;
	}
	
	function select_pcgsubtype($selectid, $htmlname = 'pcg_subtype', $showempty = 0, $event = array()) {
		global $conf, $user, $langs;
	
		$out = '';
	
		$sql = "SELECT DISTINCT pcg_subtype ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "accountingaccount ";
		$sql .= " ORDER BY pcg_subtype";
	
		dol_syslog ( get_class ( $this ) . "::select_pcg_subtype sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
	
			$out .= ajax_combobox ( $htmlname, $event );
	
	
			$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			if ($showempty)
				$out .= '<option value="-1"></option>';
			$num = $this->db->num_rows ( $resql );
			$i = 0;
			if ($num) {
				while ( $i < $num ) {
					$obj = $this->db->fetch_object ( $resql );
					$label = $obj->pcg_subtype;
	
					if (($selectid != '') && $selectid == $obj->pcg_subtype) {
						$out .= '<option value="' . $obj->pcg_subtype . '" selected="selected">' . $label . '</option>';
					} else {
						$out .= '<option value="' . $obj->pcg_subtype . '">' . $label . '</option>';
					}
					$i ++;
				}
			}
			$out .= '</select>';
		} else {
			dol_print_error ( $this->db );
		}
		$this->db->free ( $resql );
		return $out;
	}
	
}
