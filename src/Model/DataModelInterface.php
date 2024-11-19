<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Model;

use Pop\Db\Record;
use Pop\Db\Record\Collection;

/**
 * Data model interface
 *
 * @category   Pop
 * @package    Pop\Module
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.3.5
 */
interface DataModelInterface
{

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
    public function getAll(?string $sort = null, mixed $limit = null, mixed $page = null, bool|array $toArray = false): array|Collection;

    /**
     * Get by ID
     *
     * @param  mixed $id
     * @param  bool  $toArray
     * @throws Exception
     * @return array|Record
     */
    public function getById(mixed $id, bool $toArray = false): array|Record;

    /**
     * Create
     *
     * @param  array $data
     * @return mixed
     */
    public function create(array $data): mixed;

    /**
     * Replace
     *
     * @param  mixed $id
     * @param  array $data
     * @return mixed
     */
    public function replace(mixed $id, array $data): mixed;

    /**
     * Update
     *
     * @param  mixed $id
     * @param  array $data
     * @return mixed
     */
    public function update(mixed $id, array $data): mixed;

    /**
     * Delete
     *
     * @param  mixed $id
     * @throws Exception
     * @return int
     */
    public function delete(mixed $id): int;

    /**
     * Remove multiple
     *
     * @param  array $ids
     * @throws Exception
     * @return int
     */
    public function remove(array $ids): int;

    /**
     * Get count
     *
     * @throws Exception
     * @return int
     */
    public function count(): int;

    /**
     * Method to describe columns in the database table
     *
     * @param  bool $native     Show the native columns in the table
     * @param  bool $full       Used with the native flag, returns array of "column" => "type"
     * @throws Exception
     * @return array
     */
    public function describe(bool $native = false, bool $full = false): array;

    /**
     * Method to check if model has requirements or validations
     *
     * @return bool
     */
    public function hasRequirements(): bool;

    /**
     * Method to validate model data
     *
     * @param  array $data
     * @return bool|array
     */
    public function validate(array $data): bool|array;

    /**
     * Set filters
     *
     * @param  mixed $filters
     * @param  mixed $select
     * @return DataModelInterface
     */
    public function filter(mixed $filters = null, mixed $select = null): DataModelInterface;

    /**
     * Set select columns
     *
     * @param  mixed $select
     * @return DataModelInterface
     */
    public function select(mixed $select = null): DataModelInterface;

    /**
     * Get table class
     *
     * @return string
     */
    public function getTableClass(): string;

    /**
     * Get table primary ID
     *
     * @return string
     */
    public function getPrimaryId(): string;

    /**
     * Get offset and limit
     *
     * @param  mixed $page
     * @param  mixed $limit
     * @return array
     */
    public function getOffsetAndLimit(mixed $page = null, mixed $limit = null): array;

    /**
     * Get order by
     *
     * @param  mixed $sort
     * @param  bool  $toArray
     * @return string|array|null
     */
    public function getOrderBy(mixed $sort = null, bool $toArray = false): string|array|null;

    /**
     * Method to parse filter for select predicates
     *
     * @param  mixed  $filter
     * @return array
     */
    public function parseFilter(mixed $filter): array;

}
