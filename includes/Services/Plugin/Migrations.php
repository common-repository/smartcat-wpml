<?php

namespace Smartcat\Includes\Services\Plugin;

class Migrations
{
    /**
     * @var \wpdb
     */
    private $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function run()
    {
        $this->logs_table();
        $this->smartcatDocumentsTable();
        $this->addCommentFiledToDocumentsTable();
        $this->addIActiveFiledToDocumentsTable();
        $this->addApiVersionFiledToDocumentsTable();
        $this->addIsExportedAndIsImportedFieldsToDocumentsTable();
        $this->addIsInvalidProjectFieldToDocumentsTable();
    }

    public function drop()
    {
        $this->wpdb->query("DROP TABLE {$this->wpdb->prefix}" . SMARTCAT_LOGS_TABLE_NAME);
        $this->wpdb->query("DROP TABLE {$this->wpdb->prefix}" . SMARTCAT_DOCUMENTS_TABLE_NAME);
    }

    private function logs_table()
    {
        $tableName = $this->wpdb->prefix . SMARTCAT_LOGS_TABLE_NAME;

        if ($this->wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName) {
            $SQL = "create table $tableName
                    (
                        id bigint(24) auto_increment,
                        type varchar(25) default NULL,
                        message text default NULL,
                        data text default NULL,
                        exception text default NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            primary key (id)
                    );
                    ";
            $this->wpdb->query($SQL);
        }
    }

    private function smartcatDocumentsTable()
    {
        $tableName = $this->wpdb->prefix . SMARTCAT_DOCUMENTS_TABLE_NAME;

        if ($this->wpdb->get_var("SHOW TABLES LIKE '$tableName'") != $tableName) {
            $tableSchema = "CREATE TABLE `$tableName`
                (
                    `translation_request_id`  varchar(255) null,
                    `post_id`              bigint unsigned null,
                    `translated_post_id`   bigint unsigned null,
                    `smartcat_document_id` varchar(255) null,
                    `smartcat_project_name` varchar(255) null,
                    `smartcat_project_id`  varchar(255) null,
                    `lang`                 varchar(255) null,
                    `translation_progress` varchar(255) null,
                    `created_at`           timestamp null
                ) default character set utf8mb4
                  collate 'utf8mb4_unicode_ci';";

            $this->wpdb->query($tableSchema);
        }
    }

    private function addCommentFiledToDocumentsTable()
    {
        $tableName = $this->wpdb->prefix . SMARTCAT_DOCUMENTS_TABLE_NAME;
        $columnName = 'comment';

        $row = $this->wpdb->get_results(
            "SHOW COLUMNS FROM `$tableName` LIKE '$columnName'"
        );

        if (empty($row)) {
            $this->wpdb->query("ALTER TABLE $tableName ADD $columnName text default NULL AFTER `translation_progress`");
        }
    }

    private function addIActiveFiledToDocumentsTable()
    {
        $tableName = $this->wpdb->prefix . SMARTCAT_DOCUMENTS_TABLE_NAME;
        $columnName = 'is_active';

        $row = $this->wpdb->get_results(
            "SHOW COLUMNS FROM `$tableName` LIKE '$columnName'"
        );

        if (empty($row)) {
            $this->wpdb->query("ALTER TABLE $tableName ADD $columnName int(1) default 1 AFTER `comment`");
        }
    }

    private function addApiVersionFiledToDocumentsTable()
    {
        if (get_option('sc_add_api_version_field') !== "1") {
            $tableName = $this->wpdb->prefix . SMARTCAT_DOCUMENTS_TABLE_NAME;
            $columnName = 'api_version';

            $row = $this->wpdb->get_results(
                "SHOW COLUMNS FROM `$tableName` LIKE '$columnName'"
            );

            if (empty($row)) {
                $this->wpdb->query("ALTER TABLE $tableName ADD $columnName varchar(10) default 'v1' AFTER `is_active`");
            }

            add_option('sc_add_api_version_field', true);
        }
    }

    private function addIsExportedAndIsImportedFieldsToDocumentsTable()
    {
        $optionName = 'sc_add_is_exported_and_is_imported_fields';

        if (get_option($optionName) !== "1") {
            $tableName = $this->wpdb->prefix . SMARTCAT_DOCUMENTS_TABLE_NAME;

            $columnName = 'is_exported';

            $row = $this->wpdb->get_results(
                "SHOW COLUMNS FROM `$tableName` LIKE '$columnName'"
            );

            if (empty($row)) {
                $this->wpdb->query("ALTER TABLE $tableName ADD $columnName int(1) default 0 AFTER `is_active`");
            }

            $columnName = 'is_imported';

            $row = $this->wpdb->get_results(
                "SHOW COLUMNS FROM `$tableName` LIKE '$columnName'"
            );

            if (empty($row)) {
                $this->wpdb->query("ALTER TABLE $tableName ADD $columnName int(1) default 0 AFTER `is_exported`");
            }

            add_option($optionName, true);
        }
    }

    public function check()
    {
        if (get_option('sc_deleted_foreign_keys_1') !== "1") {
            $tableName = $this->wpdb->prefix . SMARTCAT_DOCUMENTS_TABLE_NAME;
            $postIdRelation = "alter table `$tableName` drop FOREIGN KEY `$tableName" . "_post_id_foreign`;";
            $this->wpdb->query($postIdRelation);
            $translatedPostIdRelation = "alter table `$tableName` drop FOREIGN KEY `$tableName" . "_translated_post_id_foreign`;";
            $this->wpdb->query($translatedPostIdRelation);

            add_option('sc_deleted_foreign_keys_1', true);
        }

        $this->addApiVersionFiledToDocumentsTable();
    }

    private function addIsInvalidProjectFieldToDocumentsTable()
    {
        $tableName = $this->wpdb->prefix . SMARTCAT_DOCUMENTS_TABLE_NAME;

        $columnName = 'is_invalid_project';

        $row = $this->wpdb->get_results(
            "SHOW COLUMNS FROM `$tableName` LIKE '$columnName'"
        );

        if (empty($row)) {
            $this->wpdb->query("ALTER TABLE $tableName ADD $columnName boolean default false");
        }
    }
}
