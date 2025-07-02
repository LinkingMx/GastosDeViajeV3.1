CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "position_id" integer,
  "department_id" integer,
  "bank_id" integer,
  "clabe" varchar,
  "rfc" varchar,
  "account_number" varchar,
  "override_authorization" tinyint(1) not null default '0',
  "override_authorizer_id" integer,
  "travel_team" tinyint(1) not null default '0',
  "treasury_team" tinyint(1) not null default '0'
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "permissions"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "permissions_name_guard_name_unique" on "permissions"(
  "name",
  "guard_name"
);
CREATE TABLE IF NOT EXISTS "roles"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "roles_name_guard_name_unique" on "roles"(
  "name",
  "guard_name"
);
CREATE TABLE IF NOT EXISTS "model_has_permissions"(
  "permission_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete cascade,
  primary key("permission_id", "model_id", "model_type")
);
CREATE INDEX "model_has_permissions_model_id_model_type_index" on "model_has_permissions"(
  "model_id",
  "model_type"
);
CREATE TABLE IF NOT EXISTS "model_has_roles"(
  "role_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  foreign key("role_id") references "roles"("id") on delete cascade,
  primary key("role_id", "model_id", "model_type")
);
CREATE INDEX "model_has_roles_model_id_model_type_index" on "model_has_roles"(
  "model_id",
  "model_type"
);
CREATE TABLE IF NOT EXISTS "role_has_permissions"(
  "permission_id" integer not null,
  "role_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete cascade,
  foreign key("role_id") references "roles"("id") on delete cascade,
  primary key("permission_id", "role_id")
);
CREATE TABLE IF NOT EXISTS "departments"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "authorizer_id" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("authorizer_id") references "users"("id") on delete set null
);
CREATE INDEX "departments_authorizer_id_index" on "departments"(
  "authorizer_id"
);
CREATE UNIQUE INDEX "departments_name_unique" on "departments"("name");
CREATE TABLE IF NOT EXISTS "positions"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "level" integer not null default '1'
);
CREATE INDEX "positions_name_index" on "positions"("name");
CREATE UNIQUE INDEX "positions_name_unique" on "positions"("name");
CREATE TABLE IF NOT EXISTS "banks"(
  "id" integer primary key autoincrement not null,
  "code" varchar not null,
  "name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "banks_code_index" on "banks"("code");
CREATE INDEX "banks_name_index" on "banks"("name");
CREATE UNIQUE INDEX "banks_code_unique" on "banks"("code");
CREATE UNIQUE INDEX "banks_name_unique" on "banks"("name");
CREATE TABLE IF NOT EXISTS "expense_concepts"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "description" text,
  "is_unmanaged" tinyint(1) not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "expense_concepts_name_index" on "expense_concepts"("name");
CREATE INDEX "expense_concepts_is_unmanaged_index" on "expense_concepts"(
  "is_unmanaged"
);
CREATE UNIQUE INDEX "expense_concepts_name_unique" on "expense_concepts"(
  "name"
);
CREATE TABLE IF NOT EXISTS "expense_details"(
  "id" integer primary key autoincrement not null,
  "concept_id" integer not null,
  "name" varchar not null,
  "description" text,
  "is_active" tinyint(1) not null default '1',
  "priority" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("concept_id") references "expense_concepts"("id") on delete cascade
);
CREATE INDEX "expense_details_concept_id_index" on "expense_details"(
  "concept_id"
);
CREATE INDEX "expense_details_name_index" on "expense_details"("name");
CREATE INDEX "expense_details_is_active_index" on "expense_details"(
  "is_active"
);
CREATE INDEX "expense_details_concept_id_is_active_index" on "expense_details"(
  "concept_id",
  "is_active"
);
CREATE UNIQUE INDEX "expense_details_name_unique" on "expense_details"("name");
CREATE TABLE IF NOT EXISTS "per_diems"(
  "id" integer primary key autoincrement not null,
  "position_id" integer not null,
  "detail_id" integer not null,
  "scope" varchar check("scope" in('domestic', 'foreign')) not null,
  "currency" varchar not null,
  "amount" numeric not null,
  "valid_from" date not null,
  "valid_to" date,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("position_id") references "positions"("id") on delete cascade,
  foreign key("detail_id") references "expense_details"("id") on delete cascade
);
CREATE INDEX "per_diems_position_id_index" on "per_diems"("position_id");
CREATE INDEX "per_diems_detail_id_index" on "per_diems"("detail_id");
CREATE INDEX "per_diems_scope_index" on "per_diems"("scope");
CREATE INDEX "per_diems_currency_index" on "per_diems"("currency");
CREATE INDEX "per_diems_valid_from_index" on "per_diems"("valid_from");
CREATE INDEX "per_diems_valid_to_index" on "per_diems"("valid_to");
CREATE INDEX "per_diems_position_id_scope_currency_index" on "per_diems"(
  "position_id",
  "scope",
  "currency"
);
CREATE INDEX "per_diems_valid_from_valid_to_index" on "per_diems"(
  "valid_from",
  "valid_to"
);
CREATE UNIQUE INDEX "unique_per_diem_config" on "per_diems"(
  "position_id",
  "detail_id",
  "scope",
  "currency",
  "valid_from"
);
CREATE TABLE IF NOT EXISTS "countries"(
  "id" integer primary key autoincrement not null,
  "iso2" varchar not null,
  "iso3" varchar not null,
  "name" varchar not null,
  "default_currency" varchar,
  "is_foreign" tinyint(1) not null default '1',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "countries_iso2_index" on "countries"("iso2");
CREATE INDEX "countries_iso3_index" on "countries"("iso3");
CREATE INDEX "countries_name_index" on "countries"("name");
CREATE INDEX "countries_default_currency_index" on "countries"(
  "default_currency"
);
CREATE INDEX "countries_is_foreign_index" on "countries"("is_foreign");
CREATE UNIQUE INDEX "countries_iso2_unique" on "countries"("iso2");
CREATE UNIQUE INDEX "countries_iso3_unique" on "countries"("iso3");
CREATE UNIQUE INDEX "countries_name_unique" on "countries"("name");
CREATE TABLE IF NOT EXISTS "branches"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "ceco" varchar not null,
  "tax_id" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "branches_name_index" on "branches"("name");
CREATE INDEX "branches_ceco_index" on "branches"("ceco");
CREATE INDEX "branches_tax_id_index" on "branches"("tax_id");
CREATE UNIQUE INDEX "branches_name_unique" on "branches"("name");
CREATE UNIQUE INDEX "branches_ceco_unique" on "branches"("ceco");
CREATE TABLE IF NOT EXISTS "travel_requests"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "authorizer_id" integer,
  "branch_id" integer not null,
  "origin_country_id" integer,
  "origin_city" varchar,
  "destination_country_id" integer,
  "destination_city" varchar,
  "departure_date" date,
  "return_date" date,
  "status" varchar not null default('draft'),
  "request_type" varchar check("request_type" in('domestic', 'foreign')),
  "notes" text,
  "additional_services" text,
  "per_diem_data" text,
  "custom_expenses_data" text,
  "created_at" datetime,
  "updated_at" datetime,
  "uuid" varchar,
  "submitted_at" datetime,
  "authorized_at" datetime,
  "rejected_at" datetime,
  "travel_reviewed_at" datetime,
  "travel_reviewed_by" integer,
  "travel_review_comments" text,
  "advance_deposit_made" tinyint(1) not null default '0',
  "advance_deposit_made_at" datetime,
  "advance_deposit_made_by" integer,
  "advance_deposit_notes" text,
  "advance_deposit_amount" numeric,
  foreign key("destination_country_id") references countries("id") on delete no action on update no action,
  foreign key("origin_country_id") references countries("id") on delete no action on update no action,
  foreign key("branch_id") references branches("id") on delete no action on update no action,
  foreign key("authorizer_id") references users("id") on delete no action on update no action,
  foreign key("user_id") references users("id") on delete cascade on update no action
);
CREATE UNIQUE INDEX "travel_requests_uuid_unique" on "travel_requests"("uuid");
CREATE TABLE IF NOT EXISTS "__temp__travel_requests"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "authorizer_id" integer,
  "branch_id" integer not null,
  "origin_country_id" integer,
  "origin_city" varchar,
  "destination_country_id" integer,
  "destination_city" varchar,
  "departure_date" date,
  "return_date" date,
  "status" varchar not null default('draft'),
  "request_type" varchar,
  "notes" text,
  "additional_services" text,
  "per_diem_data" text,
  "custom_expenses_data" text,
  "created_at" datetime,
  "updated_at" datetime,
  "uuid" varchar not null,
  foreign key("user_id") references users("id") on delete cascade on update no action,
  foreign key("authorizer_id") references users("id") on delete no action on update no action,
  foreign key("branch_id") references branches("id") on delete no action on update no action,
  foreign key("origin_country_id") references countries("id") on delete no action on update no action,
  foreign key("destination_country_id") references countries("id") on delete no action on update no action
);
CREATE TABLE IF NOT EXISTS "__temp__travel_request_comments"(
  "id" integer primary key autoincrement not null,
  "travel_request_id" integer not null,
  "user_id" integer not null,
  "comment" text not null,
  "type" varchar check("type" in('submission', 'approval', 'rejection', 'revision')) not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("travel_request_id") references travel_requests("id") on delete cascade on update no action,
  foreign key("user_id") references users("id") on delete no action on update no action
);
CREATE TABLE IF NOT EXISTS "travel_request_comments"(
  "id" integer primary key autoincrement not null,
  "travel_request_id" integer not null,
  "user_id" integer,
  "comment" text not null,
  "type" varchar check("type" in('submission', 'approval', 'rejection', 'revision', 'system', 'travel_approval', 'travel_rejection', 'travel_edit_approval')) not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("travel_request_id") references "travel_requests"("id") on delete cascade,
  foreign key("user_id") references "users"("id")
);
CREATE INDEX "travel_request_comments_backup2_travel_request_id_created_at_index" on "travel_request_comments"(
  "travel_request_id",
  "created_at"
);
CREATE INDEX "travel_request_comments_backup2_type_index" on "travel_request_comments"(
  "type"
);
CREATE TABLE IF NOT EXISTS "attachment_types"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "icon" varchar,
  "color" varchar not null default 'gray',
  "is_active" tinyint(1) not null default '1',
  "sort_order" integer not null default '0',
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "attachment_types_is_active_sort_order_index" on "attachment_types"(
  "is_active",
  "sort_order"
);
CREATE UNIQUE INDEX "attachment_types_name_unique" on "attachment_types"(
  "name"
);
CREATE UNIQUE INDEX "attachment_types_slug_unique" on "attachment_types"(
  "slug"
);
CREATE TABLE IF NOT EXISTS "travel_request_attachments"(
  "id" integer primary key autoincrement not null,
  "travel_request_id" integer not null,
  "uploaded_by" integer not null,
  "file_name" varchar not null,
  "file_path" varchar not null,
  "file_type" varchar not null,
  "file_size" integer not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "attachment_type_id" integer,
  foreign key("uploaded_by") references users("id") on delete cascade on update no action,
  foreign key("travel_request_id") references travel_requests("id") on delete cascade on update no action,
  foreign key("attachment_type_id") references "attachment_types"("id") on delete restrict
);
CREATE INDEX "travel_request_attachments_uploaded_by_index" on "travel_request_attachments"(
  "uploaded_by"
);
CREATE INDEX "travel_request_attachments_attachment_type_id_index" on "travel_request_attachments"(
  "attachment_type_id"
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2025_06_29_020737_create_permission_tables',1);
INSERT INTO migrations VALUES(5,'2025_06_29_021102_add_employee_fields_to_users_table',1);
INSERT INTO migrations VALUES(6,'2025_06_29_021508_create_departments_table',1);
INSERT INTO migrations VALUES(7,'2025_06_29_022707_create_positions_table',1);
INSERT INTO migrations VALUES(8,'2025_06_29_022820_create_banks_table',1);
INSERT INTO migrations VALUES(9,'2025_06_29_023036_create_expense_concepts_table',1);
INSERT INTO migrations VALUES(10,'2025_06_29_023221_create_expense_details_table',1);
INSERT INTO migrations VALUES(11,'2025_06_29_023416_create_per_diems_table',1);
INSERT INTO migrations VALUES(12,'2025_06_29_023610_create_countries_table',1);
INSERT INTO migrations VALUES(13,'2025_06_29_023750_create_branches_table',1);
INSERT INTO migrations VALUES(14,'2025_06_29_164736_create_travel_requests_table',1);
INSERT INTO migrations VALUES(15,'2025_06_29_172519_make_travel_request_fields_nullable_for_drafts',1);
INSERT INTO migrations VALUES(16,'2025_06_29_200709_add_uuid_to_travel_requests_table',2);
INSERT INTO migrations VALUES(17,'2025_06_30_002251_add_authorization_fields_to_travel_requests_table',3);
INSERT INTO migrations VALUES(18,'2025_06_30_002336_create_travel_request_comments_table',3);
INSERT INTO migrations VALUES(19,'2025_06_29_030955_add_level_to_positions_table',4);
INSERT INTO migrations VALUES(20,'2025_07_02_002309_add_travel_team_to_users_table',4);
INSERT INTO migrations VALUES(22,'2025_07_02_003201_add_treasury_team_to_users_table',5);
INSERT INTO migrations VALUES(23,'2025_07_02_005013_add_travel_team_review_to_travel_requests_table',6);
INSERT INTO migrations VALUES(24,'2025_07_02_010302_modify_travel_request_comments_for_system',7);
INSERT INTO migrations VALUES(25,'2025_07_02_010454_fix_travel_request_comments_enum',8);
INSERT INTO migrations VALUES(26,'2025_07_02_012613_add_travel_edit_approval_comment_type',9);
INSERT INTO migrations VALUES(27,'2025_07_02_015239_create_travel_request_attachments_table',10);
INSERT INTO migrations VALUES(28,'2025_07_02_020343_create_attachment_types_table',11);
INSERT INTO migrations VALUES(29,'2025_07_02_020545_update_travel_request_attachments_table_to_use_attachment_type_id',11);
INSERT INTO migrations VALUES(30,'2025_07_02_020612_seed_default_attachment_types_and_migrate_data',11);
INSERT INTO migrations VALUES(32,'2025_07_02_020859_remove_attachment_type_enum_column_v2',12);
INSERT INTO migrations VALUES(33,'2025_07_02_021707_add_advance_deposit_receipt_attachment_type',13);
