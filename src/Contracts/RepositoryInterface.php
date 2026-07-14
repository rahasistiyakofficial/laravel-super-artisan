<?php

namespace RahasIstiyak\SuperArtisan\Contracts;

interface RepositoryInterface
{
    /**
     * Retrieve all records.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all(array $columns = ['*']);

    /**
     * Find a record by its primary key.
     *
     * @param  int|string  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find(int|string $id, array $columns = ['*']);

    /**
     * Find a record by its primary key or throw an exception.
     *
     * @param  int|string  $id
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int|string $id, array $columns = ['*']);

    /**
     * Find records by a given field and value.
     *
     * @param  string  $field
     * @param  mixed   $value
     * @param  array   $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findBy(string $field, mixed $value, array $columns = ['*']);

    /**
     * Create a new record.
     *
     * @param  array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data);

    /**
     * Update an existing record.
     *
     * @param  int|string  $id
     * @param  array       $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(int|string $id, array $data);

    /**
     * Delete a record by its primary key.
     *
     * @param  int|string  $id
     * @return bool
     */
    public function delete(int|string $id): bool;

    /**
     * Paginate records.
     *
     * @param  int    $perPage
     * @param  array  $columns
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']);

    /**
     * Get the first record matching the given criteria.
     *
     * @param  array  $criteria
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function firstWhere(array $criteria, array $columns = ['*']);

    /**
     * Count total records.
     *
     * @return int
     */
    public function count(): int;
}
