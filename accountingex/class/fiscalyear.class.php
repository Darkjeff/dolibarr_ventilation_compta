<?php
/* Copyright (C) 2013      Olivier Geffroy      <jeff@jeffinfo.com>
 * Copyright (C) 2013      Alexandre Spangaro   <alexandre.spangaro@gmail.com>
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
 *	\file       accountingex/class/accountancy.class.php
 * 	\ingroup    Accounting Expert
 * 	\brief      Fichier de la classe comptabilité expert
 */
dol_include_once("/core/class/commonobject.class.php");

/**
 * \class 		Accountancy
 * \brief 		Classe permettant la gestion comptable
 */
class Accountancy
{
  public $element='Accountancy';
	public $table_element='accounting';
	public $table_element_line = '';
	public $fk_element = '';
	protected $ismultientitymanaged = 1;	// 0=No test on entity, 1=Test with field entity, 2=Test with link by societe

	var $id;
  var $rowid;

  var $label;
	var $datestart;
	var $dateend;
	var $statut;		// 0=open, 1=closed
  var $entity;
  var $extraparams=array();

	var $statuts=array();
	var $statuts_short=array();
	
 /**
	* Constructor
	*
	* @param	DoliDB		$db		Database handler
	*/
	function __construct($db)
	{
		$this->db = $db;

		return 1;
	}

	/**
	 * Create object in database
	 * TODO Add ref number
	 *
	 * @return 	int				<0 if KO, >0 if OK
	 */
	function create()
	{
		global $conf;

    $now=dol_now();

    $this->db->begin();

		$sql = "INSERT INTO ".MAIN_DB_PREFIX."accountingfiscalyear (";
		$sql.= " label";
		$sql.= ", datestart";
		$sql.= ", dateend";
		$sql.= ", statut";
		$sql.= ", entity";
		$sql.= ") VALUES (";
		$sql.= " '".$this->label;
		$sql.= "', ".$this->datestart;
		$sql.= ", ".$this->dateend;
		$sql.= ", ".$this->statut;
		$sql.= ", ".$conf->entity;
		$sql.= ")";
    
    $this->db->begin();

    dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
    $resql=$this->db->query($sql);
    if (! $resql) {
        $error++; $this->errors[]="Error ".$this->db->lasterror();
    }

    if (! $error)
    {
        $this->rowid = $this->db->last_insert_id(MAIN_DB_PREFIX."accountingfiscalyear");
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
        return $this->rowid;
    }
        
    /*
		dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."accountingfiscalyear");

      $result=$this->update($user);
			if ($result > 0)
			{
				$this->db->commit();
				return $this->id;
			}
			else
			{
				$this->error=$this->db->error();
				$this->db->rollback();
				return $result;
			}
		}
		else
		{
			$this->error=$this->db->error()." sql=".$sql;
			$this->db->rollback();
			return -1;
		}
    */

	}
}
?>
