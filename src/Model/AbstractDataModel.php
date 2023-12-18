<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
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
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
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
     * Requirements
     * @var array
     */
    protected array $requirements = [];

    /**
     * Select columns
     * @var ?array
     */
    protected ?array $selectColumns = null;

    /**
     * Private columns
     * @var array
     */
    protected array $privateColumns = [];

    /**
     * Fetch all
     *
     * @param  ?string $sort
     * @param  mixed $limit
     * @param  mixed $page
     * @param  bool $asArray
     * @throws Exception
     * @return array|Collection
     */
    public static function fetchAll(?string $sort = null, mixed $limit = null, mixed $page = null, bool $asArray = true): array|Collection
    {
        return (new static())->getAll($sort, $limit, $page, $asArray);
    }

    /**
     * Fetch by ID
     *
     * @param  mixed $id
     * @param  bool $asArray
     * @throws Exception
     * @return array|Record
     */
    public static function fetch(mixed $id, bool $asArray = true): array|Record
    {
        return (new static())->getById($id, $asArray);
    }

    /**
     * Create new
     *
     * @param  array $data
     * @param  bool  $asArray
     * @throws Exception
     * @return array|Record
     */
    public static function createNew(array $data, bool $asArray = true): array|Record
    {
        return (new static())->create($data, $asArray);
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
     * @param  ?string $sort
     * @param  mixed   $limit
     * @param  mixed   $page
     * @param  bool    $asArray
     * @throws Exception
     * @return array|Collection
     */
    public function getAll(?string $sort = null, mixed $limit = null, mixed $page = null, bool $asArray = true): array|Collection
    {
        $table          = $this->getTableClass();
        $offsetAndLimit = $this->getOffsetAndLimit($page, $limit);

        if (!empty($this->filters)) {
            $columns = $this->parseFilter($this->filters);
            return $table::findBy($columns, [
                'select' => $this->describe($this->selectColumns),
                'offset' => $offsetAndLimit['offset'],
                'limit'  => $offsetAndLimit['limit'],
                'order'  => $this->getOrderBy($sort)
            ], $asArray);
        } else {
            return $table::findAll([
                'select' => $this->describe($this->selectColumns),
                'offset' => $offsetAndLimit['offset'],
                'limit'  => $offsetAndLimit['limit'],
                'order'  => $this->getOrderBy($sort)
            ], $asArray);
        }
    }

    /**
     * Get by ID
     *
     * @param  mixed $id
     * @param  bool  $asArray
     * @throws Exception
     * @return array|Record
     */
    public function getById(mixed $id, bool $asArray = true): array|Record
    {
        $table   = $this->getTableClass();
        $options = ['select' => $this->describe($this->selectColumns)];
        return $table::findById($id, $options, $asArray);
    }

    /**
     * Create
     *
     * @param  array $data
     * @param  bool  $asArray
     * @throws Exception
     * @return array|Record
     */
    public function create(array $data, bool $asArray = true): array|Record
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

        return ($asArray) ? $record->toArray() : $record;
    }

    /**
     * Replace
     *
     * @param  mixed $id
     * @param  array $data
     * @param  bool  $asArray
     * @throws Exception
     * @return array|Record
     */
    public function replace(mixed $id, array $data, bool $asArray = true): array|Record
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

        if (isset($record->id)) {
            foreach ($recordData as $key => $value) {
                $record->{$key} = $data[$key] ?? null;
            }
            $record->save();
        }

        return ($asArray) ? $record->toArray() : $record;
    }

    /**
     * Update
     *
     * @param  mixed $id
     * @param  array $data
     * @param  bool  $asArray
     * @throws Exception
     * @return Record
     */
    public function update(mixed $id, array $data, bool $asArray = true): array|Record
    {
        $table  = $this->getTableClass();
        $record = $table::findById($id);

        if (isset($record->id)) {
            foreach ($data as $key => $value) {
                $record->{$key} = $value ?? $record->{$key};
            }
            $record->save();
        }

        return ($asArray) ? $record->toArray() : $record;
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
        $table  = $this->getTableClass();
        $record = $table::findById($id);
        if (isset($record->id)) {
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
        $table = $this->getTableClass();
        if (!empty($this->filters)) {
            $columns = $this->parseFilter($this->filters);
            return $table::getTotal($columns);
        } else {
            return $table::getTotal();
        }
    }

    /**
     * Method to describe columns in the database table
     *
     * @param  mixed $columns
     * @throws Exception
     * @return array
     */
    public function describe(mixed $columns = null): array
    {
        if (!empty($columns) && !is_array($columns)) {
            $columns = array_map('trim', ((str_contains($columns, ',')) ? explode(',', $columns) : [$columns]));
        }

        $table     = $this->getTableClass();
        $tableInfo = $table::getTableInfo();

        if (!isset($tableInfo['tableName']) || !isset($tableInfo['columns'])) {
            throw new Exception('Error: The table info parameter is not in the correct format');
        }

        $tableColumns = array_diff(array_keys($tableInfo['columns']), $this->privateColumns);

        return (!empty($columns)) ?
            array_values(array_diff($tableColumns, array_diff($tableColumns, $columns))) : $tableColumns;
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
     * @param  mixed $filters
     * @param  mixed $select
     * @return AbstractDataModel
     */
    public function filter(mixed $filters = null, mixed $select = null): AbstractDataModel
    {
        if (!empty($filters)) {
            $this->filters = (!is_array($filters)) ? [$filters] : $filters;
        } else {
            $this->filters = [];
        }

        $this->select($select);

        return $this;
    }

    /**
     * Set select columns
     *
     * @param  mixed $select
     * @return AbstractDataModel
     */
    public function select(mixed $select = null): AbstractDataModel
    {
        if (!empty($select)) {
            $this->selectColumns = (!is_array($select)) ? [$select] : $select;
        } else {
            $this->selectColumns = null;
        }

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
     * @param  bool  $asArray
     * @return string|array|null
     */
    public function getOrderBy(mixed $sort = null, bool $asArray = false): string|array|null
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

        return ($asArray) ? $orderByAry : $orderBy;
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