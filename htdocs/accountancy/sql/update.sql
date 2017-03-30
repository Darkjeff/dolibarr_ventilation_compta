ALTER TABLE llx_accounting_bookkeeping ADD COLUMN entity integer DEFAULT 1 NOT NULL;
ALTER TABLE llx_accounting_bookkeeping ADD COLUMN fk_user_modif     integer;
ALTER TABLE llx_accounting_bookkeeping ADD COLUMN date_creation		datetime;
ALTER TABLE llx_accounting_bookkeeping ADD COLUMN tms               timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE llx_accounting_bookkeeping ADD COLUMN lettering     varchar(32);
ALTER TABLE llx_expensereport_det ADD COLUMN fk_code_ventilation integer DEFAULT 0;

create table llx_payment_various
(
  rowid                 integer AUTO_INCREMENT PRIMARY KEY,
  tms                   timestamp,
  datec                 datetime,
  datep                 date,
  datev                 date,
  sens                  smallint DEFAULT 0 NOT NULL,
  amount                double(24,8) DEFAULT 0 NOT NULL,
  fk_typepayment        integer NOT NULL,
  num_payment           varchar(50),
  label                 varchar(255),
  accountancy_code		varchar(32),
  entity                integer DEFAULT 1 NOT NULL,
  note                  text,
  fk_bank               integer,
  fk_user_author        integer,
  fk_user_modif         integer
)ENGINE=innodb;