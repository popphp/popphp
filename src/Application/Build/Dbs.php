<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/nicksagona/PopPHP
 * @category   Pop
 * @package    Pop_Application
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Application\Build;

use Pop\Db\Db;

/**
 * Db install class
 *
 * @category   Pop
 * @package    Pop_Application
 * @author     Nick Sagona, III <info@popphp.org>
 * @copyright  Copyright (c) 2009-2014 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0a
 */
class Dbs
{

    /**
     * Check the database
     *
     * @param  array  $db
     * @throws Exception
     * @return string
     */
    public static function check($db)
    {
        if (($db['type'] != 'Mysql') &&
            ($db['type'] != 'Mysqli') &&
            ($db['type'] != 'Oracle') &&
            ($db['type'] != 'Pgsql') &&
            ($db['type'] != 'Sqlite') &&
            ($db['type'] != 'Sqlsrv') &&
            (stripos($db['type'], 'Pdo') === false)) {
            return 'The database type \'' . $db['type'] . '\' is not valid.';
        } else {
            try {
                $result = null;
                // Test the db connection
                if (stripos($db['type'], 'Sqlite') === false) {
                    if (stripos($db['type'], 'Pdo_') !== false) {
                        $type = 'Pdo';
                        $db['type'] = strtolower(substr($db['type'], (strpos($db['type'], '_') + 1)));
                    } else {
                        $type = $db['type'];
                    }
                    $dbconn = Db::factory($type, $db);
                }
                return $result;
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
    }

    /**
     * Build the database
     *
     * @param string  $dbname
     * @param array   $db
     * @param string  $dir
     * @param mixed   $install
     * @param boolean $suppress
     * @param boolean $clear
     * @throws Exception
     * @return array
     */
    public static function install($dbname, $db, $dir, $install = null, $suppress = false, $clear = true)
    {
        // Detect any SQL files
        $sqlFiles = array();
        if (is_string($dir) && file_exists($dir) && (strtolower(substr($dir, -4)) == '.sql')) {
            $sqlFiles[] = $dir;
        } else {
            $dir = new \Pop\File\Dir($dir, true);
            $files = $dir->getFiles();

            foreach ($files as $file) {
                if (strtolower(substr($file, -4)) == '.sql') {
                    $sqlFiles[] = $file;
                }
            }
        }

        // If SQLite, create folder and empty SQLite file
        if (stripos($db['type'], 'sqlite') !== false) {
            if (is_string($install) && file_exists($install)) {
                $db['database'] = $install;
            } else {
                // Define folders to create
                $folders = array(
                    $install->project->base,
                    $install->project->base . '/module',
                    $install->project->base . '/module/' . $install->project->name,
                    $install->project->base . '/module/' . $install->project->name . '/data'
                );
                // Create the folders
                foreach ($folders as $folder) {
                    if (!file_exists($folder)) {
                        mkdir($folder);
                    }
                }
                // Create empty SQLite file and make file and folder writable
                chmod($install->project->base . '/module/' . $install->project->name . '/data', 0777);
                touch($install->project->base . '/module/' . $install->project->name . '/data/' . $db['database']);
                chmod($install->project->base . '/module/' . $install->project->name . '/data/' . $db['database'], 0777);
                $db['database'] = $install->project->base . '/module/' . $install->project->name . '/data/' . $db['database'];
            }
        }

        // Create DB connection
        if (stripos($db['type'], 'Pdo_') !== false) {
            $type = 'Pdo';
            $db['type'] = strtolower(substr($db['type'], (strpos($db['type'], '_') + 1)));
        } else {
            $type = $db['type'];
        }
        $popdb = Db::factory($type, $db);

        // If there are SQL files, parse them and execute the SQL queries
        if (count($sqlFiles) > 0) {
            if (!$suppress) {
                echo 'SQL files found. Executing SQL queries...' . PHP_EOL;
            }

            // Clear database
            if ($clear) {
                $oldTables = $popdb->adapter()->getTables();
                if (count($oldTables) > 0) {
                    if (($type == 'Mysqli') || ($db['type'] == 'mysql')) {
                        $popdb->adapter()->query('SET foreign_key_checks = 0;');
                        foreach ($oldTables as $tab) {
                            $popdb->adapter()->query("DROP TABLE " . $tab);
                        }
                        $popdb->adapter()->query('SET foreign_key_checks = 1;');
                    } else if (($type == 'Pgsql') || ($db['type'] == 'pgsql')) {
                        foreach ($oldTables as $tab) {
                            $popdb->adapter()->query("DROP TABLE " . $tab . ' CASCADE');
                        }
                    } else {
                        foreach ($oldTables as $tab) {
                            $popdb->adapter()->query("DROP TABLE " . $tab);
                        }
                    }
                }
            }

            $prefix = (isset($db['prefix'])) ? $db['prefix'] : null;

            foreach ($sqlFiles as $sqlFile) {
                $sql = trim(file_get_contents($sqlFile));
                $explode = (strpos($sql, ";\r\n") !== false) ? ";\r\n" : ";\n";
                $statements = explode($explode, $sql);

                // Loop through each statement found and execute
                foreach ($statements as $s) {
                    if (!empty($s)) {
                        try {
                            $popdb->adapter()->query(str_replace('[{prefix}]', $prefix, trim($s)));
                        } catch (\Exception $e) {
                            echo $e->getMessage() . PHP_EOL . PHP_EOL;
                            exit(0);
                        }
                    }
                }
            }
        }

        // Get table info
        $tables = array();

        try {
            // Get Sqlite table info
            if (stripos($db['type'], 'sqlite') !== false) {
                $tablesFromDb = $popdb->adapter()->getTables();
                if (count($tablesFromDb) > 0) {
                    foreach ($tablesFromDb as $table) {
                        $tables[$table] = array('primaryId' => null, 'auto' => false);
                        $popdb->adapter()->query("PRAGMA table_info('" . $table . "')");
                        while (($row = $popdb->adapter()->fetch()) != false) {
                            if ($row['pk'] == 1) {
                                $tables[$table] = array('primaryId' => $row['name'], 'auto' => true);
                            }
                        }
                    }
                }
            // Else, get MySQL, PgSQL and SQLSrv table info
            } else {
                if (stripos($db['type'], 'pgsql') !== false) {
                    $schema = 'CATALOG';
                    $tableSchema = " AND TABLE_SCHEMA = 'public'";
                    $tableName = 'table_name';
                    $constraintName = 'constraint_name';
                    $columnName = 'column_name';
                } else if (stripos($db['type'], 'sqlsrv') !== false) {
                    $schema = 'CATALOG';
                    $tableSchema = null;
                    $tableName = 'TABLE_NAME';
                    $constraintName = 'CONSTRAINT_NAME';
                    $columnName = 'COLUMN_NAME';
                } else {
                    $schema = 'SCHEMA';
                    $tableSchema = null;
                    $tableName = 'TABLE_NAME';
                    $constraintName = 'CONSTRAINT_NAME';
                    $columnName = 'COLUMN_NAME';
                }
                $popdb->adapter()->query("SELECT * FROM information_schema.TABLES WHERE TABLE_" . $schema . " = '" . $dbname . "'" . $tableSchema);

                // Get the auto increment info (mysql) and set table name
                while (($row = $popdb->adapter()->fetch()) != false) {
                    $auto = (!empty($row['AUTO_INCREMENT'])) ? true : false;
                    $tables[$row[$tableName]] = array('primaryId' => null, 'auto' => $auto);
                }

                // Get the primary key info
                foreach ($tables as $table => $value) {
                    // Pgsql sequence info for auto increment
                    if ($db['type'] == 'Pgsql') {
                        $popdb->adapter()->query("SELECT column_name FROM information_schema.COLUMNS WHERE table_name = '" . $table . "'");
                        $columns = array();
                        while (($row = $popdb->adapter()->fetch()) != false) {
                            $columns[] = $row['column_name'];
                        }

                        if (count($columns) > 0) {
                            foreach ($columns as $column) {
                                $popdb->adapter()->query("SELECT pg_get_serial_sequence('" . $table . "', '" . $column . "')");
                                while (($row = $popdb->adapter()->fetch()) != false) {
                                    if (!empty($row['pg_get_serial_sequence'])) {
                                        $idAry = explode('_', $row['pg_get_serial_sequence']);
                                        if (isset($idAry[1]) && (in_array($idAry[1], $columns))) {
                                            $tables[$table]['auto'] = true;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Get primary id, if there is one
                    $ids = array();
                    $popdb->adapter()->query("SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE CONSTRAINT_" . $schema . " = '" . $dbname . "' AND TABLE_NAME = '" . $table . "'");
                    while (($row = $popdb->adapter()->fetch()) != false) {
                        if (isset($row[$constraintName])) {
                            if (!isset($tables[$table]['primaryId'])) {
                                $tables[$table]['primaryId'] = $row[$columnName];
                            } else {
                                if (!in_array($row[$columnName], $ids)) {
                                    $tables[$table]['primaryId'] .= '|' . $row[$columnName];
                                }
                            }
                            $ids[] = $row[$columnName];
                        }
                    }
                }
            }

            if (isset($db['prefix'])) {
                foreach ($tables as $table => $value) {
                    $tables[$table]['prefix'] = $db['prefix'];
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL . PHP_EOL;
            exit(0);
        }

        return $tables;
    }

}
