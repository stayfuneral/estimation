<?php

namespace Estimation;

use DateTime;
use DB;
use CronTask;
use PluginEstimationEstimation;
use PluginEstimationConfigs;
use PluginEstimationCron;

foreach (glob('*.class.php') as $class) {
    require $class;
}

/**
 * Class Setup установщик
 *
 * @package Estimation
 */

class Setup
{
    /**
     * @var string название плагина
     */
    const PLUGIN_NAME = 'Estimation';

    /**
     * @var string поле ID
     */
    const DB_TABLE_FIELD_ID = 'id int(11) not null auto_increment';
    /**
     * @var string поле date
     */
    const DB_TABLE_FIELD_DATETIME = 'date datetime default current_timestamp';
    /**
     * @var string первичный ключ
     */
    const DB_TABLE_PRIMARY_KEY = 'primary key (id)';

    /**
     * @var null | Setup
     */
    private static $instance = null;

    /**
     * @var DB
     */
    protected $db;

    /**
     * @var CronTask
     */
    protected $cronTask;

    /**
     * Setup constructor.
     *
     * @param DB $db
     * @param CronTask $cronTask
     */
    protected function __construct(DB $db, CronTask $cronTask)
    {
        $this->db = $db;
        $this->cronTask = $cronTask;
    }


    /**
     * @uses DB $DB
     * @uses CronTask $cronTask
     *
     * @return Setup
     */
    public static function getInstance()
    {
        global $DB;
        $cronTask = new CronTask;

        if(is_null(self::$instance)) {
            self::$instance = new self($DB, $cronTask);
        }

        return self::$instance;
    }

    /**
     * Массив с таблицами и полями
     *
     * @param bool $onlyKeys вернуть только ключи (названия таблиц)
     *
     * @return array
     */
    public function getTables($onlyKeys = false)
    {
        $tables = [
            PluginEstimationEstimation::getTable() => [
                self::DB_TABLE_FIELD_ID,
                'ticket_id int(11) not null',
                'author_id int(11) not null',
                'responsible_id int(11) not null',
                'group_id int(11) not null',
                'estimation tinyint(1) not null',
                'is_auto tinyint(1) default 0',
                'comment text',
                self::DB_TABLE_FIELD_DATETIME,
                self::DB_TABLE_PRIMARY_KEY
            ],
            PluginEstimationConfigs::getTable() => [
                self::DB_TABLE_FIELD_ID,
                'config_name varchar(100) not null unique',
                'config_value text not null',
                'value_type varchar(100) not null default \'string\'',
                self::DB_TABLE_PRIMARY_KEY
            ]
        ];

        if($onlyKeys) {
            return array_keys($tables);
        }

        return $tables;
    }

    /**
     * Создание таблиц в БД
     */
    public function installDbTables()
    {
        foreach ($this->getTables() as $table => $fileds) {

            if(!$this->db->tableExists($table)) {
                $sql = "create table $table (".implode(', ', $fileds).")";
                $this->db->queryOrDie($sql, $this->db->error());
            }
        }
    }

    /**
     * Удаление таблиц из БД
     */
    public function uninstallDbTables()
    {
        $configs = new PluginEstimationConfigs;

        if(!$configs->getConfigs('save_tables_on_delete')) {
            foreach ($this->getTables(true) as $table) {
                if($this->db->tableExists($table)) {
                    $sql = "drop table $table";
                    $this->db->queryOrDie($sql, $this->db->error());
                }
            }
        }


    }

    /**
     * Сохранение в БД настроек по умолчанию
     *
     * @throws \Exception
     */
    public function saveDefaultConfigs()
    {
        $this->db->updateOrInsert(PluginEstimationConfigs::getTable(), [
            'config_name' => 'install_date',
            'config_value' => (new DateTime())->format('Y-m-d H:i:s')
        ], [
            'config_name' => 'install_date'
        ]);

        $this->db->updateOrInsert(PluginEstimationConfigs::getTable(), [
            'config_name' => 'save_tables_on_delete',
            'config_value' => true,
            'value_type' => 'boolean'
        ], [
            'config_name' => 'save_tables_on_delete'
        ]);
    }

    /**
     * Регистрация автоматических действий
     */
    public function registerCronTask()
    {
        if(!PluginEstimationCron::checkCronTask($this->cronTask)) {
            $this->cronTask::register(
                PluginEstimationCron::class,
                PluginEstimationCron::CRON_ACTION_NAME,
                HOUR_TIMESTAMP,
                [
                    'mode' => $this->cronTask::MODE_EXTERNAL
                ]
            );
        }
    }

    /**
     * Удаление регистрации автоматических действий
     */
    public function unRegisterCronTask()
    {
        if(PluginEstimationCron::checkCronTask($this->cronTask)) {
            $this->cronTask::unregister(self::PLUGIN_NAME);
        }
    }
}