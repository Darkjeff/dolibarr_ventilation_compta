-- ============================================================================
-- Copyright (C) 2013 Olivier Geffroy  <jeff@jeffinfo.com>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ============================================================================

CREATE TABLE llx_compta_compte_generaux 
(
  rowid				int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  date_creation		datetime DEFAULT NULL,
  numero			varchar(50) DEFAULT NULL,
  intitule			varchar(255) DEFAULT NULL,
  sellsjournal		varchar(1) DEFAULT 'N'
  fk_user_author	int(11) DEFAULT NULL,
  note				text,
  libelle		    varchar(255) DEFAULT NULL,
  sellsjournal 		varchar(1) DEFAULT 'N'
) ENGINE=innodb;