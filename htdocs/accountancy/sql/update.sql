ALTER TABLE llx_accounting_bookkeeping ADD COLUMN entity integer DEFAULT 1 NOT NULL;
ALTER TABLE llx_accounting_bookkeeping ADD COLUMN fk_user_modif     integer;
ALTER TABLE llx_accounting_bookkeeping ADD COLUMN date_creation		datetime;
ALTER TABLE llx_accounting_bookkeeping ADD COLUMN tms               timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE llx_expensereport_det ADD COLUMN fk_code_ventilation integer DEFAULT 0;
