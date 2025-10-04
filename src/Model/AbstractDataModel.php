<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Model;

use Pop\Db\Record;
use Pop\Db\Record\Collection;
use Pop\Db\Sql\Parser;

/**
 * Abstract data model class
 *
 * @category   Pop
 * @package    Pop
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.3.8
 */
abstract class AbstractDataModel extends AbstractModel implements DataModelInterface
{

    /**
     * Data table class
     * @var ?string
     */
    protected ?string $table = null;

    /**
     * Filters
     * @var array
     */
    protected array $filters = [];

    /**
     * Options
     * @var array
     */
    protected array $options = [];

    /**
     * Requirements
     * @var array
     */
    protected array $requirements = [];

    /**
     * Select columns
     *  - Columns to show for general select queries. Can include foreign columns
     *    if the $foreignTables property is properly configured
     * @var array
     */
    protected array $selectColumns = [];

    /**
     * Private columns
     *  - Columns of sensitive data to hide from general select queries (i.e. passwords, etc.)
     * @var array
     */
    protected array $privateColumns = [];

    /**
     * Foreign tables
     *  - List of foreign tables and columns to use in general select queries as JOINS
     *      [
     *          'table'   => 'foreign_table',
     *          'columns' => ['foreign_table.id' => 'table.foreign_id']
     *      ]
     * @var array
     */
    protected array $foreignTables = [];

    /**
     * Original select columns
     *  - Property to track original select columns
     * @var array
     */
    private array $origSelectColumns = [];

    /**
     * Fetch all
     *
     * @param  ?string    $sort
     * @param  mixed      $limit
     * @param  mixed      $page
     * @param  bool|array $toArray
     * @throws Exception
     * @return array|Collection
     */
    public static function fetchAll(?string $sort = null, mixed $limit = null, mixed $page = null, bool|array $toArray = false): array|Collection
    {
        return (new static())->getAll($sort, $limit, $page, $toArray);
    }

    /**
     * Fetch by ID
     *
     * @param  mixed $id
     * @param  bool $toArray
     * @throws Exception
     * @return array|Record
     */
    public static function fetch(mixed $id, bool $toArray = false): array|Record
    {
        return (new static())->getById($id, $toArray);
    }

    /**
     * Create new
     *
     * @param  array $data
     * @param  bool  $toArray
     * @throws Exception
     * @return array|Record
     */
    public static function createNew(array $data, bool $toArray = false): array|Record
    {
        return (new static())->create($data, $toArray);
    }

    /**
     * Filter by
     *
     * @param  mixed $filters
     * @param  mixed $select
     * @return static
     */
    public static function filterBy(mixed $filters = null, mixed $select = null): static
    {
        return (new static())->filter($filters, $select);
    }

    /**
     * Get all
     *
     * @param  ?string    $sort
     * @param  mixed      $limit
     * @param  mixed      $page
     * @param  bool|array $toArray
     * @throws Exception
     * @return array|Collection
     */
    public function getAll(?string $sort = null, mixed $limit = null, mixed $page = null, bool|array $toArray = false): array|Collection
    {
        $table          = $this->getTableClass();
        $offsetAndLimit = $this->getOffsetAndLimit($page, $limit);

        if (!empty($this->options)) {
            $this->options['offset'] = $offsetAndLimit['offset'];
            $this->options['limit']  = $offsetAndLimit['limit'];
            $this->options['order']  = $this->getOrderBy($sort);
        } else {
            $this->options = [
                'offset' => $offsetAndLimit['offset'],
                'limit'  => $offsetAndLimit['limit'],
                'order'  => $this->getOrderBy($sort)
            ];
        }

        if (!isset($this->options['select'])) {
            $this->options['select'] = $this->describe(($toArray !== false));
        }

        if (!empty($this->foreignTables) && !isset($this->options['join'])) {
            $this->options['join'] = $this->foreignTables;
        }

        if (!empty($this->filters)) {
            return $table::findBy($this->parseFilter($this->filters), $this->options, $toArray);
        } else {
            return $table::findAll($this->options, $toArray);
        }
    }

    /**
     * Get by ID
     *
     * @param  mixed $id
     * @param  bool  $toArray
     * @throws Exception
     * @return array|Record
     */
    public function getById(mixed $id, bool $toArray = false): array|Record
    {
        $table = $this->getTableClass();

        if (!isset($this->options['select'])) {
            $this->options['select'] = $this->describe(($toArray !== false));
        }

        if (!empty($this->foreignTables) && !isset($this->options['join'])) {
            $this->options['join'] = $this->foreignTables;
        }

        if (!empty($this->filters)) {
            $primaryKeys = (new $table())->getPrimaryKeys();
            $tableClass  = $table::table();
            foreach ($primaryKeys as $i => $primaryKey) {
                if (is_array($id) && isset($id[$i])) {
                    $this->filters[] = $tableClass . '.' . $primaryKey . ' = ' . $id[$i];
                } else if (!is_array($id)) {
                    $this->filters[] = $tableClass . '.' . $primaryKey . ' = ' . $id;
                }
            }
            return $table::findOne($this->parseFilter($this->filters), $this->options, $toArray);
        } else {
            return $table::findById($id, $this->options, $toArray);
        }
    }

    /**
     * Create
     *
     * @param  array $data
     * @param  bool  $toArray
     * @throws Exception
     * @return array|Record
     */
    public function create(array $data, bool $toArray = false): array|Record
    {
        if ($this->hasRequirements()) {
            $results = $this->validate($data);
            if (is_array($results)) {
                return $results;
            }
        }

        $table = $this->getTableClass();
        $record = new $table($data);
        $record->save();

        return ($toArray) ? $record->toArray() : $record;
    }

    /**
     * Copy
     *
     * @param  mixed $id
     * @param  array $replace
     * @param  bool  $toArray
     * @throws Exception
     * @return array|Record
     */
    public function copy(mixed $id, array $replace = [], bool $toArray = false): array|Record
    {
        $table      = $this->getTableClass();
        $record     = $table::findById($id);
        $primaryKey = $this->getPrimaryId();

        if (isset($record->{$primaryKey})) {
            $record = $record->copy($replace);
        }

        return ($toArray) ? $record->toArray() : $record;
    }

    /**
     * Replace
     *
     * @param  mixed $id
     * @param  array $data
     * @param  bool  $toArray
     * @throws Exception
     * @return array|Record
     */
    public function replace(mixed $id, array $data, bool $toArray = false): array|Record
    {
        if ($this->hasRequirements()) {
            $results = $this->validate($data);
            if (is_array($results)) {
                return $results;
            }
        }

        $table      = $this->getTableClass();
        $record     = $table::findById($id);
        $recordData = $record->toArray();
        $primaryKey = $this->getPrimaryId();

        if (isset($record->{$primaryKey})) {
            foreach ($recordData as $key => $value) {
                $record->{$key} = $data[$key] ?? null;
            }
            $record->save();
        }

        return ($toArray) ? $record->toArray() : $record;
    }

    /**
     * Update
     *
     * @param  mixed $id
     * @param  array $data
     * @param  bool  $toArray
     * @throws Exception
     * @return array|Record
     */
    public function update(mixed $id, array $data, bool $toArray = false): array|Record
    {
        $table      = $this->getTableClass();
        $record     = $table::findById($id);
        $primaryKey = $this->getPrimaryId();

        if (isset($record->{$primaryKey})) {
            foreach ($data as $key => $value) {
                $record->{$key} = $value;
            }
            $record->save();
        }

        return ($toArray) ? $record->toArray() : $record;
    }

    /**
     * Delete
     *
     * @param  mixed $id
     * @throws Exception
     * @return int
     */
    public function delete(mixed $id): int
    {
        $table      = $this->getTableClass();
        $record     = $table::findById($id);
        $primaryKey = $this->getPrimaryId();

        if (isset($record->{$primaryKey})) {
            $record->delete();
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Remove multiple
     *
     * @param  array $ids
     * @throws Exception
     * @return int
     */
    public function remove(array $ids): int
    {
        $deleted = 0;
        foreach ($ids as $id) {
            $deleted += $this->delete($id);
        }
        return $deleted;
    }

    /**
     * Get count
     *
     * @throws Exception
     * @return int
     */
    public function count(): int
    {
        $options = $this->options;

        if (!empty($this->foreignTables) && !isset($options['join'])) {
            $options['join'] = $this->foreignTables;
        }

        if (isset($options['offset'])) {
            unset($options['offset']);
        }

        if (isset($options['limit'])) {
            unset($options['limit']);
        }

        $table = $this->getTableClass();
        if (!empty($this->filters)) {
            return $table::getTotal($this->parseFilter($this->filters), $options);
        } else {
            return $table::getTotal(null, $options);
        }
    }

    /**
     * Method to describe columns in the database table
     *
     * @param  bool $native     Show only the native columns in the table
     * @param  bool $full       Used with the native flag, returns a full descriptive array of table info
     * @return array
     *@throws Exception
     */
    public function describe(bool $native = false, bool $full = false): array
    {
        $table        = $this->getTableClass();
        $tableInfo    = $table::getTableInfo();
        $tableColumns = array_keys($tableInfo['columns']);
        $tableName    = $tableInfo['tableName'];

        if (!isset($tableInfo['tableName']) || !isset($tableInfo['columns'])) {
            throw new Exception('Error: The table info parameter is not in the correct format');
        }

        $tableColumns = array_diff($tableColumns, $this->privateColumns);

        if ($native) {
            return ($full) ? $tableInfo : array_map(function($value) use ($tableName) {
                return $tableName . '.' . $value;
            }, $tableColumns);
        } else {
            // Get any possible foreign columns
            $foreignColumns = array_diff(array_diff($this->selectColumns, $tableColumns), $this->privateColumns);

            // Assemble and return allowed filtered columns
            if (!empty($this->selectColumns)) {
                $cols = [];
                foreach ($this->selectColumns as $key => $column) {
                    if (in_array($column, $tableColumns) || in_array($column, $foreignColumns)) {
                        $cols[$key] = $column;
                    }
                }
                return $cols;
            } else {
                return array_values($tableColumns);
            }
        }
    }

    /**
     * Method to check if model has requirements
     *
     * @return bool
     */
    public function hasRequirements(): bool
    {
        return !empty($this->requirements);
    }

    /**
     * Method to validate model data
     *
     * @param  array $data
     * @return bool|array
     */
    public function validate(array $data): bool|array
    {
        $errors = [];

        foreach ($this->requirements as $column) {
            if (!array_key_exists($column, $data)) {
                $errors[$column] = "The column '" . $column . "' is required.";
            }
        }

        return (!empty($errors)) ? ['errors' => $errors] : true;
    }

    /**
     * Set filters
     *
     * @param  mixed  $filters
     * @param  mixed  $select
     * @param  ?array $options
     * @return AbstractDataModel
     */
    public function filter(mixed $filters = null, mixed $select = null, ?array $options = null): AbstractDataModel
    {
        if (!empty($filters)) {
            $this->filters = (!is_array($filters)) ? [$filters] : $filters;
        } else {
            $this->filters = [];
        }

        $this->select($select, $options);

        return $this;
    }

    /**
     * Set (override) select columns
     *
     * @param  mixed  $select
     * @param  ?array $options
     * @return AbstractDataModel
     */
    public function select(mixed $select = null, ?array $options = null): AbstractDataModel
    {
        if (!empty($select)) {
            if (is_string($select) && str_contains($select, ',')) {
                $select = array_map('trim', explode(',', $select));
            }
            if (empty($this->origSelectColumns)) {
                $this->origSelectColumns = $this->selectColumns;
            }
            $select        = (!is_array($select)) ? [$select] : $select;
            $selectColumns = [];

            foreach ($select as $selectColumn) {
                if (in_array($selectColumn, $this->selectColumns)) {
                    $selectColumns[array_search($selectColumn, $this->selectColumns)] = $selectColumn;
                } else {
                    $selectColumns[] = $selectColumn;
                }
            }

            $this->selectColumns = $selectColumns;
            if (!empty($options)) {
                $options['select'] = $selectColumns;
            } else {
                $options = ['select' => $selectColumns];
            }
        } else if (!empty($this->origSelectColumns)) {
            $this->selectColumns = $this->origSelectColumns;
        }

        $this->options = (!empty($options)) ? $options : [];

        return $this;
    }

    /**
     * Get table class
     *
     * @throws Exception
     * @return string
     */
    public function getTableClass(): string
    {
        if (!empty($this->table) && class_exists($this->table)) {
            return $this->table;
        }

        $table = str_replace('Model', 'Table', get_class($this));
        if (!class_exists($table)) {
            $table .= 's';
        }
        if (!class_exists($table)) {
            throw new Exception('Error: Unable to detect model table class');
        }

        return $table;
    }

    /**
     * Get table primary ID
     *
     * @return string
     */
    public function getPrimaryId(): string
    {
        $table       = $this->getTableClass();
        $primaryKeys = (new $table())->getPrimaryKeys();

        return $primaryKeys[0] ?? 'id';
    }

    /**
     * Get offset and limit
     *
     * @param  mixed $page
     * @param  mixed $limit
     * @return array
     */
    public function getOffsetAndLimit(mixed $page = null, mixed $limit = null): array
    {
        if (($limit !== null) && ($page !== null)) {
            $page = ((int)$page > 1) ? ($page * $limit) - $limit : null;
        } else if ($limit !== null) {
            $limit = (int)$limit;
        } else {
            $page  = null;
            $limit = null;
        }

        return [
            'offset' => $page,
            'limit'  => $limit
        ];
    }

    /**
     * Get order by
     *
     * @param  mixed $sort
     * @param  bool  $toArray
     * @return string|array|null
     */
    public function getOrderBy(mixed $sort = null, bool $toArray = false): string|array|null
    {
        $orderBy        = null;
        $orderByStrings = [];
        $orderByAry     = [];

        if ($sort !== null) {
            if (!is_array($sort)) {
                $sort = (str_contains($sort, ',')) ?
                    explode(',', $sort) : [$sort];
            }

            foreach ($sort as $order) {
                $order = trim($order);
                if (str_starts_with($order, '-')) {
                    $orderByStrings[] = substr($order, 1) . ' DESC';
                    $orderByAry[]     = [
                        'by'    => substr($order, 1),
                        'order' => 'DESC'
                    ];
                } else {
                    $orderByStrings[] = $order . ' ASC';
                    $orderByAry[]     = [
                        'by'    => $order,
                        'order' => 'ASC'
                    ];
                }
            }

            $orderBy = implode(', ', $orderByStrings);
        }

        return ($toArray) ? $orderByAry : $orderBy;
    }

    /**
     * Method to parse filter for select predicates
     *
     * @param  mixed  $filter
     * @return array
     */
    public function parseFilter(mixed $filter): array
    {
        return (is_array($filter)) ?
            Parser\Expression::convertExpressionsToShorthand($filter) :
            Parser\Expression::convertExpressionToShorthand($filter);
    }

}
