<?php
/* Copyright (C) 2004-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2013-2014 Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2013-2014 Alexandre Spangaro   <alexandre.spangaro@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * \file      accountingex/class/bookkeeping.class.php
 * \ingroup   Accounting Expert
 * \brief     Fichier de la classe des comptes comptable
 */

/**
 * \class BookKeeping
 * \brief Classe permettant la gestion des comptes generaux de compta
 */
class BookKeeping {
	var $db;
	var $id;
	var $doc_date;
	var $doc_type;
	var $doc_ref;
	var $fk_doc;
	var $fk_docdet;
	var $code_tiers;
	var $numero_compte;
	var $label_compte;
	var $debit;
	var $credit;
	var $montant;
	var $sens;
	var $fk_user_author;
	var $code_journal;
	var $piece_num;
	var $linesexport = array ();
	var $linesmvt = array ();

	/**
	 * \brief Constructeur de la classe
	 * \param DB handler acces base de donnees
	 * \param id id compte (0 par defaut)
	 */
	function BookKeeping($db) {

		$this->db = $db;
	}

	/**
	 * \brief Load record in memory
	 */
	function fetch_per_mvt($piecenum) {

		$sql = "SELECT piece_num,doc_date,code_journal,doc_ref,doc_type FROM " . MAIN_DB_PREFIX . "bookkeeping WHERE ";
		$sql .= " piece_num = '" . $piecenum . "'";
		
		dol_syslog ( get_class ( $this ) . "fetch_per_mvt sql=" . $sql, LOG_DEBUG );
		$result = $this->db->query ( $sql );
		if ($result) {
			$obj = $this->db->fetch_object ( $result );
			
			$this->piece_num = $obj->piece_num;
			$this->code_journal = $obj->code_journal;
			$this->doc_date = $this->db->jdate ( $obj->doc_date );
			$this->doc_ref = $obj->doc_ref;
			$this->doc_type = $obj->doc_type;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch_per_mvt " . $this->error, LOG_ERR );
			return - 1;
		}
		
		return 1;
	}

	/**
	 * \brief Load record in memory
	 */
	function fetch($id) {

		$sql = "SELECT rowid, doc_date, doc_type, ";
		$sql .= "doc_ref, fk_doc, fk_docdet, code_tiers, ";
		$sql .= "numero_compte, label_compte, debit, credit, ";
		$sql .= " montant, sens, fk_user_author, import_key, code_journal, piece_num  ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "bookkeeping ";
		$sql .= " WHERE rowid = '" . $id . "'";
		
		dol_syslog ( get_class ( $this ) . "fetch sql=" . $sql, LOG_DEBUG );
		$result = $this->db->query ( $sql );
		if ($result) {
			$obj = $this->db->fetch_object ( $result );
			
			$this->id = $obj->rowid;
			
			$this->doc_date = $this->db->jdate ( $obj->doc_date );
			$this->doc_type = $obj->doc_type;
			$this->doc_ref = $obj->doc_ref;
			$this->fk_doc = $obj->fk_doc;
			$this->fk_docdet = $obj->fk_docdet;
			$this->code_tiers = $obj->code_tiers;
			$this->numero_compte = $obj->numero_compte;
			$this->label_compte = $obj->label_compte;
			$this->debit = $obj->debit;
			$this->credit = $obj->credit;
			$this->montant = $obj->montant;
			$this->sens = $obj->sens;
			$this->code_journal = $obj->code_journal;
			$this->piece_num = $obj->piece_num;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch " . $this->error, LOG_ERR );
			return - 1;
		}
		
		return 1;
	}

	/**
	 * \brief Return next num mvt
	 */
	function next_num_mvt() {

		$sql = "SELECT MAX(piece_num)+1 as max FROM " . MAIN_DB_PREFIX . "bookkeeping";
		
		dol_syslog ( get_class ( $this ) . "next_num_mvt sql=" . $sql, LOG_DEBUG );
		$result = $this->db->query ( $sql );
		if ($result) {
			$obj = $this->db->fetch_object ( $result );
			
			return $obj->max;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch_per_mvt " . $this->error, LOG_ERR );
			return - 1;
		}
	}

	/**
	 * \brief Load record in memory
	 */
	function fetch_all_per_mvt($piecenum) {

		$sql = "SELECT rowid, doc_date, doc_type, ";
		$sql .= "doc_ref, fk_doc, fk_docdet, code_tiers, ";
		$sql .= "numero_compte, label_compte, debit, credit, ";
		$sql .= " montant, sens, fk_user_author, import_key, code_journal, piece_num  ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "bookkeeping ";
		$sql .= " WHERE piece_num = '" . $piecenum . "'";
		
		dol_syslog ( get_class ( $this ) . "fetch_all_per_mvt sql=" . $sql, LOG_DEBUG );
		$result = $this->db->query ( $sql );
		if ($result) {
			
			while ( $obj = $this->db->fetch_object ( $result ) ) {
				
				$line = new BookKeepingLine ();
				
				$line->id = $obj->rowid;
				
				$line->doc_date = $this->db->jdate ( $obj->doc_date );
				$line->doc_type = $obj->doc_type;
				$line->doc_ref = $obj->doc_ref;
				$line->fk_doc = $obj->fk_doc;
				$line->fk_docdet = $obj->fk_docdet;
				$line->code_tiers = $obj->code_tiers;
				$line->numero_compte = $obj->numero_compte;
				$line->label_compte = $obj->label_compte;
				$line->debit = $obj->debit;
				$line->credit = $obj->credit;
				$line->montant = $obj->montant;
				$line->sens = $obj->sens;
				$line->code_journal = $obj->code_journal;
				$line->piece_num = $obj->piece_num;
				
				$this->linesmvt [] = $line;
			}
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::fetch_per_mvt " . $this->error, LOG_ERR );
			return - 1;
		}
		
		return 1;
	}

	/**
	 * \brief Insere une ligne dans bookkeeping
	 * \param user utilisateur qui effectue l'insertion
	 */
	function create() {

		global $conf, $user, $langs;
		
		$this->piece_num = 0;
		
		// first check if line not yet in bookkeeping
		$sql = "SELECT count(*)";
		$sql .= " FROM " . MAIN_DB_PREFIX . "bookkeeping ";
		$sql .= " WHERE doc_type = '" . $this->doc_type . "'";
		$sql .= " AND fk_docdet = " . $this->fk_docdet;
		$sql .= " AND numero_compte = '" . $this->numero_compte . "'";
		
		dol_syslog ( get_class ( $this ) . ":: create sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		
		if ($resql) {
			$row = $this->db->fetch_array ( $resql );
			if ($row [0] == 0) {
				
				// Determine piece_num
				$sqlnum = "SELECT piece_num";
				$sqlnum .= " FROM " . MAIN_DB_PREFIX . "bookkeeping ";
				$sqlnum .= " WHERE doc_type = '" . $this->doc_type . "'";
				$sqlnum .= " AND fk_docdet = '" . $this->fk_docdet . "'";
				$sqlnum .= " AND doc_ref = '" . $this->doc_ref . "'";
				
				dol_syslog ( get_class ( $this ) . ":: create sqlnum=" . $sqlnum, LOG_DEBUG );
				$resqlnum = $this->db->query ( $sqlnum );
				if ($resqlnum) {
					$objnum = $this->db->fetch_object ( $resqlnum );
					$this->piece_num = $objnum->piece_num;
				}
				dol_syslog ( get_class ( $this ) . ":: create this->piece_num=" . $this->piece_num, LOG_DEBUG );
				if (empty ( $this->piece_num )) {
					$sqlnum = "SELECT MAX(piece_num)+1 as maxpiecenum";
					$sqlnum .= " FROM " . MAIN_DB_PREFIX . "bookkeeping ";
					
					dol_syslog ( get_class ( $this ) . ":: create sqlnum=" . $sqlnum, LOG_DEBUG );
					$resqlnum = $this->db->query ( $sqlnum );
					if ($resqlnum) {
						$objnum = $this->db->fetch_object ( $resqlnum );
						$this->piece_num = $objnum->maxpiecenum;
					}
				}
				dol_syslog ( get_class ( $this ) . ":: create this->piece_num=" . $this->piece_num, LOG_DEBUG );
				if (empty ( $this->piece_num )) {
					$this->piece_num = 1;
				}
				
				$now = dol_now ();
				$sql = "INSERT INTO " . MAIN_DB_PREFIX . "bookkeeping (doc_date, doc_type, doc_ref,fk_doc,fk_docdet,code_tiers,numero_compte,label_compte,debit,credit,montant,sens,fk_user_author,import_key,code_journal,piece_num)";
				$sql .= " VALUES ('" . $this->doc_date . "','" . $this->doc_type . "','" . $this->doc_ref . "'," . $this->fk_doc . "," . $this->fk_docdet . ",'" . $this->code_tiers . "','" . $this->numero_compte . "','" . $this->label_compte . "'," . $this->debit . "," . $this->credit . "," . $this->montant . ",'" . $this->sens . "'," . $user->id . ", '" . $now . "','" . $this->code_journal . "'," . $this->piece_num . ")";
				
				$resql = $this->db->query ( $sql );
				if ($resql) {
					$id = $this->db->last_insert_id ( MAIN_DB_PREFIX . "bookkeeping" );
					
					if ($id > 0) {
						$this->id = $id;
						$result = 0;
					} else {
						$result = - 2;
						dol_syslog ( "BookKeeping::Create Erreur $result lecture ID" );
					}
				} else {
					$result = - 1;
					dol_syslog ( "BookKeeping::Create Erreur $result INSERT Mysql" );
				}
			} else {
				$result = - 3;
				dol_syslog ( "BookKeeping::Create Erreur $result SELECT Mysql" );
			}
		} else {
			$result = - 5;
			dol_syslog ( "BookKeeping::Create Erreur $result SELECT Mysql" );
		}
		
		return $result;
	}

	/**
	 * \brief Delete bookkepping by importkey
	 */
	function delete_by_importkey($importkey) {

		$this->db->begin ();
		
		// first check if line not yet in bookkeeping
		$sql = "DELETE";
		$sql .= " FROM " . MAIN_DB_PREFIX . "bookkeeping ";
		$sql .= " WHERE import_key = '" . $importkey . "'";
		
		$resql = $this->db->query ( $sql );
		if (! $resql) {
			$error ++;
			$this->errors [] = "Error " . $this->db->lasterror ();
		}
		
		// Commit or rollback
		if ($error) {
			foreach ( $this->errors as $errmsg ) {
				dol_syslog ( get_class ( $this ) . "::delete " . $errmsg, LOG_ERR );
				$this->error .= ($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback ();
			return - 1 * $error;
		} else {
			$this->db->commit ();
			return 1;
		}
	}
	
	/**
	 *  Create object into database
	 *
	 *  @param	User	$user        User that creates
	 *  @param  int		$notrigger   0=launch triggers after, 1=disable triggers
	 *  @return int      		   	 <0 if KO, Id of created object if OK
	 */
	function create_std($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;
	
		// Clean parameters
	
		if (isset($this->doc_type)) $this->doc_type=trim($this->doc_type);
		if (isset($this->doc_ref)) $this->doc_ref=trim($this->doc_ref);
		if (isset($this->fk_doc)) $this->fk_doc=trim($this->fk_doc);
		if (isset($this->fk_docdet)) $this->fk_docdet=trim($this->fk_docdet);
		if (isset($this->code_tiers)) $this->code_tiers=trim($this->code_tiers);
		if (isset($this->numero_compte)) $this->numero_compte=trim($this->numero_compte);
		if (isset($this->label_compte)) $this->label_compte=trim($this->label_compte);
		if (isset($this->debit)) $this->debit=trim($this->debit);
		if (isset($this->credit)) $this->credit=trim($this->credit);
		if (isset($this->montant)) $this->montant=trim($this->montant);
		if (isset($this->sens)) $this->sens=trim($this->sens);
		if (isset($this->fk_user_author)) $this->fk_user_author=trim($this->fk_user_author);
		if (isset($this->import_key)) $this->import_key=trim($this->import_key);
		if (isset($this->code_journal)) $this->code_journal=trim($this->code_journal);
		if (isset($this->piece_num)) $this->piece_num=trim($this->piece_num);
	
	
	
		// Check parameters
		// Put here code to add control on parameters values
	
		// Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."bookkeeping(";
	
		$sql.= "doc_date,";
		$sql.= "doc_type,";
		$sql.= "doc_ref,";
		$sql.= "fk_doc,";
		$sql.= "fk_docdet,";
		$sql.= "code_tiers,";
		$sql.= "numero_compte,";
		$sql.= "label_compte,";
		$sql.= "debit,";
		$sql.= "credit,";
		$sql.= "montant,";
		$sql.= "sens,";
		$sql.= "fk_user_author,";
		$sql.= "import_key,";
		$sql.= "code_journal,";
		$sql.= "piece_num";
	
	
		$sql.= ") VALUES (";
	
		$sql.= " ".(! isset($this->doc_date) || dol_strlen($this->doc_date)==0?'NULL':$this->db->idate($this->doc_date)).",";
		$sql.= " ".(! isset($this->doc_type)?'NULL':"'".$this->db->escape($this->doc_type)."'").",";
		$sql.= " ".(! isset($this->doc_ref)?'NULL':"'".$this->db->escape($this->doc_ref)."'").",";
		$sql.= " ".(! isset($this->fk_doc)?'NULL':"'".$this->fk_doc."'").",";
		$sql.= " ".(! isset($this->fk_docdet)?'NULL':"'".$this->fk_docdet."'").",";
		$sql.= " ".(! isset($this->code_tiers)?'NULL':"'".$this->db->escape($this->code_tiers)."'").",";
		$sql.= " ".(! isset($this->numero_compte)?'NULL':"'".$this->db->escape($this->numero_compte)."'").",";
		$sql.= " ".(! isset($this->label_compte)?'NULL':"'".$this->db->escape($this->label_compte)."'").",";
		$sql.= " ".(! isset($this->debit)?'NULL':"'".$this->debit."'").",";
		$sql.= " ".(! isset($this->credit)?'NULL':"'".$this->credit."'").",";
		$sql.= " ".(! isset($this->montant)?'NULL':"'".$this->montant."'").",";
		$sql.= " ".(! isset($this->sens)?'NULL':"'".$this->db->escape($this->sens)."'").",";
		$sql.= " ".$user->id.",";
		$sql.= " ".(! isset($this->import_key)?'NULL':"'".$this->db->escape($this->import_key)."'").",";
		$sql.= " ".(! isset($this->code_journal)?'NULL':"'".$this->db->escape($this->code_journal)."'").",";
		$sql.= " ".(! isset($this->piece_num)?'NULL':"'".$this->piece_num."'")."";
	
	
		$sql.= ")";
	
		$this->db->begin();
	
		dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
	
		if (! $error)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."bookkeeping");
	
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action calls a trigger.
	
				//// Call triggers
				//include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
				//$interface=new Interfaces($this->db);
				//$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
				//if ($result < 0) { $error++; $this->errors=$interface->errors; }
				//// End call triggers
			}
		}
	
		// Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
				dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
				$this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return $this->id;
		}
	}
	
	/**
	 *  Update object into database
	 *
	 *  @param	User	$user        User that modifies
	 *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return int     		   	 <0 if KO, >0 if OK
	 */
	function update($user=0, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;
	
		// Clean parameters
	
		if (isset($this->doc_type)) $this->doc_type=trim($this->doc_type);
		if (isset($this->doc_ref)) $this->doc_ref=trim($this->doc_ref);
		if (isset($this->fk_doc)) $this->fk_doc=trim($this->fk_doc);
		if (isset($this->fk_docdet)) $this->fk_docdet=trim($this->fk_docdet);
		if (isset($this->code_tiers)) $this->code_tiers=trim($this->code_tiers);
		if (isset($this->numero_compte)) $this->numero_compte=trim($this->numero_compte);
		if (isset($this->label_compte)) $this->label_compte=trim($this->label_compte);
		if (isset($this->debit)) $this->debit=trim($this->debit);
		if (isset($this->credit)) $this->credit=trim($this->credit);
		if (isset($this->montant)) $this->montant=trim($this->montant);
		if (isset($this->sens)) $this->sens=trim($this->sens);
		if (isset($this->fk_user_author)) $this->fk_user_author=trim($this->fk_user_author);
		if (isset($this->import_key)) $this->import_key=trim($this->import_key);
		if (isset($this->code_journal)) $this->code_journal=trim($this->code_journal);
		if (isset($this->piece_num)) $this->piece_num=trim($this->piece_num);
	
	
	
		// Check parameters
		// Put here code to add a control on parameters values
	
		// Update request
		$sql = "UPDATE ".MAIN_DB_PREFIX."bookkeeping SET";
	
		$sql.= " doc_date=".(dol_strlen($this->doc_date)!=0 ? "'".$this->db->idate($this->doc_date)."'" : 'null').",";
		$sql.= " doc_type=".(isset($this->doc_type)?"'".$this->db->escape($this->doc_type)."'":"null").",";
		$sql.= " doc_ref=".(isset($this->doc_ref)?"'".$this->db->escape($this->doc_ref)."'":"null").",";
		$sql.= " fk_doc=".(isset($this->fk_doc)?$this->fk_doc:"null").",";
		$sql.= " fk_docdet=".(isset($this->fk_docdet)?$this->fk_docdet:"null").",";
		$sql.= " code_tiers=".(isset($this->code_tiers)?"'".$this->db->escape($this->code_tiers)."'":"null").",";
		$sql.= " numero_compte=".(isset($this->numero_compte)?"'".$this->db->escape($this->numero_compte)."'":"null").",";
		$sql.= " label_compte=".(isset($this->label_compte)?"'".$this->db->escape($this->label_compte)."'":"null").",";
		$sql.= " debit=".(isset($this->debit)?$this->debit:"null").",";
		$sql.= " credit=".(isset($this->credit)?$this->credit:"null").",";
		$sql.= " montant=".(isset($this->montant)?$this->montant:"null").",";
		$sql.= " sens=".(isset($this->sens)?"'".$this->db->escape($this->sens)."'":"null").",";
		$sql.= " fk_user_author=".(isset($this->fk_user_author)?$this->fk_user_author:"null").",";
		$sql.= " import_key=".(isset($this->import_key)?"'".$this->db->escape($this->import_key)."'":"null").",";
		$sql.= " code_journal=".(isset($this->code_journal)?"'".$this->db->escape($this->code_journal)."'":"null").",";
		$sql.= " piece_num=".(isset($this->piece_num)?$this->piece_num:"null")."";
	
	
		$sql.= " WHERE rowid=".$this->id;
	
		$this->db->begin();
	
		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
	
		if (! $error)
		{
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action calls a trigger.
	
				//// Call triggers
				//include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
				//$interface=new Interfaces($this->db);
				//$result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
				//if ($result < 0) { $error++; $this->errors=$interface->errors; }
				//// End call triggers
			}
		}
	
		// Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
				dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
				$this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}
	
	
	/**
	 *  Delete object in database
	 *
	 *	@param  User	$user        User that deletes
	 *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return	int					 <0 if KO, >0 if OK
	 */
	function delete($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;
	
		$this->db->begin();
	
		if (! $error)
		{
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
				// want this action calls a trigger.
	
				//// Call triggers
				//include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
				//$interface=new Interfaces($this->db);
				//$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
				//if ($result < 0) { $error++; $this->errors=$interface->errors; }
				//// End call triggers
			}
		}
	
		if (! $error)
		{
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."bookkeeping";
			$sql.= " WHERE rowid=".$this->id;
	
			dol_syslog(get_class($this)."::delete sql=".$sql);
			$resql = $this->db->query($sql);
			if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
		}
	
		// Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
				dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
				$this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}

	/**
	 * \brief Delete bookkepping by importkey
	 */
	function export_bookkeping($model = 'ebp') {

		$sql = "SELECT rowid, doc_date, doc_type, ";
		$sql .= "doc_ref, fk_doc, fk_docdet, code_tiers, ";
		$sql .= "numero_compte, label_compte, debit, credit, ";
		$sql .= " montant, sens, fk_user_author, import_key, code_journal, piece_num  ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "bookkeeping ";
		
		$resql = $this->db->query ( $sql );
		
		dol_syslog ( get_class ( $this ) . "::export_bookkeping sql=" . $sql, LOG_DEBUG );
		$resql = $this->db->query ( $sql );
		if ($resql) {
			$this->linesexport = array ();
			
			$num = $this->db->num_rows ( $resql );
			while ( $obj = $this->db->fetch_object ( $resql ) ) {
				$line = new BookKeepingLine ();
				
				$line->id = $obj->rowid;
				
				$line->doc_date = $this->db->jdate ( $obj->doc_date );
				$line->doc_type = $obj->doc_type;
				$line->doc_ref = $obj->doc_ref;
				$line->fk_doc = $obj->fk_doc;
				$line->fk_docdet = $obj->fk_docdet;
				$line->code_tiers = $obj->code_tiers;
				$line->numero_compte = $obj->numero_compte;
				$line->label_compte = $obj->label_compte;
				$line->debit = $obj->debit;
				$line->credit = $obj->credit;
				$line->montant = $obj->montant;
				$line->sens = $obj->sens;
				$line->code_journal = $obj->code_journal;
				$line->piece_num = $obj->piece_num;
				
				$this->linesexport [] = $line;
			}
			$this->db->free ( $resql );
			
			return $num;
		} else {
			$this->error = "Error " . $this->db->lasterror ();
			dol_syslog ( get_class ( $this ) . "::export_bookkeping " . $this->error, LOG_ERR );
			return - 1;
		}
	}
}

class BookKeepingLine {
	var $id;
	var $doc_date;
	var $doc_type;
	var $doc_ref;
	var $fk_doc;
	var $fk_docdet;
	var $code_tiers;
	var $numero_compte;
	var $label_compte;
	var $debit;
	var $credit;
	var $montant;
	var $sens;
	var $fk_user_author;
	var $code_journal;
	var $piece_num;
}

?>
