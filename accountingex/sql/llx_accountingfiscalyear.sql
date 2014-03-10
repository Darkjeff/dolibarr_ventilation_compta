-- ============================================================================
-- Copyright (C) 2013-2014 Olivier Geffroy      <jeff@jeffinfo.com>
-- Copyright (C) 2013-2014 Alexandre Spangaro   <alexandre.spangaro@gmail.com>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
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
-- Table of fiscal year for accountancy expert module
-- ============================================================================

create table llx_accountingfiscalyear
(
  rowid           integer AUTO_INCREMENT PRIMARY KEY,
  label           varchar(128) NOT NULL,
  datestart       date,
  dateend         date,
  statut          tinyint DEFAULT 0 NOT NULL,
  entity          integer DEFAULT 1 NOT NULL	  -- multi company id
)ENGINE=innodb;
