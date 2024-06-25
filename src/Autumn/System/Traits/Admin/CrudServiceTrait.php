<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/05/2024
 */

namespace Autumn\System\Traits\Admin;

use Autumn\Database\Db;
use Autumn\Database\DbException;
use Autumn\Database\Interfaces\Persistable;
use Autumn\Database\Interfaces\Recyclable;
use Autumn\Database\Interfaces\RecyclableRepositoryInterface;
use Autumn\Database\Interfaces\RepositoryInterface;
use Autumn\Database\Interfaces\StatusInterface;
use Autumn\Database\Interfaces\TypeInterface;
use Autumn\Database\Interfaces\Updatable;
use Autumn\Database\Models\Conditions;
use Autumn\Exceptions\NotFoundException;
use Autumn\Exceptions\SystemException;
use Autumn\Exceptions\ValidationException;
use Autumn\Interfaces\ModelInterface;
use Autumn\Lang\Str;
use Autumn\System\ClassFactory;
use Autumn\System\ClassFile;
use Autumn\System\Reflection;

trait CrudServiceTrait
{
    protected ?array $defaultCriteria = null;
    protected ?array $defaultSorting = null;
    protected ?array $availableStatuses = ['active', 'pending', 'disabled'];

    /**
     * @return array|null
     */
    public function getAvailableStatuses(): ?array
    {
        return $this->availableStatuses;
    }

    public static function modelClass(): string
    {
        static $classes;

        $entityClass = static::entity_class();

        if (defined(static::class . '::DEFAULT_TYPE')) {
            if (isset($classes[$entityClass][static::DEFAULT_TYPE])) {
                return $classes[$entityClass][static::DEFAULT_TYPE];
            }

            if (constOf($entityClass, 'DEFAULT_TYPE') !== static::DEFAULT_TYPE) {
                return $classes[$entityClass][static::DEFAULT_TYPE] = ClassFile::forClassWithConstants($entityClass, [
                    'DEFAULT_TYPE' => static::DEFAULT_TYPE
                ]);
            }
        }

        return $entityClass;
    }

    public function getModelInstance(array $data = null): mixed
    {
        if ($class = static::modelClass()) {
            if (is_subclass_of($class, ModelInterface::class, true)) {
                return $class::from($data ?? []);
            }

            return Reflection::createInstance($class, $data);
        }

        return null;
    }

    public function getDefaultCriteria(): array
    {
        return $this->defaultCriteria ?? static::DEFAULT_CRITERIA ?? [];
    }

    /**
     * @param array|null $criteria
     */
    public function setDefaultCriteria(array $criteria = null): void
    {
        $this->defaultCriteria = $criteria;
    }

    public function getDefaultSorting(): array
    {
        return $this->defaultSorting ?? static::DEFAULT_SORTING ?? [];
    }

    /**
     * @param array|null $sorting
     */
    public function setDefaultSorting(array $sorting = null): void
    {
        $this->defaultSorting = $sorting;
    }

    /**
     * @param array|Persistable $entity
     * @return false|Persistable
     */
    public function create(array|Persistable $entity): false|Persistable
    {
        $class = static::modelClass();
        if (!is_subclass_of($class, RepositoryInterface::class)) {
            throw SystemException::of('Invalid Model to be served: %s.', $class);
        }

        if (!($connection = Db::entity_connection_name($class)) || !($db = Db::of($connection))) {
            throw SystemException::of('The entity `%s` is readonly at the moment.', $class);
        }

        if (is_array($entity)) {
            if (defined(static::class . '::DEFAULT_TYPE')) {
                $entity['type'] ??= static::DEFAULT_TYPE;
            }

            $entity = $this->getModelInstance($entity);
        }

        if ($entity instanceof $class) {
            if (!$entity->isNew()) {
                throw ValidationException::of('The instance of entity %s is not new.', $class);
            }

            $item = $entity->withConnection($db);
            // if ($item instanceof EntityManagerInterface) {
            if ($item->persist()) {
                if ($entity !== $item) {
                    $entity['id'] = $item['id'];
                }
                return $entity;
            }
            // }
        }

        return false;
    }

    public function update(int|Updatable $entity, array $changes = null): bool
    {
        $class = static::modelClass();
        if (!is_subclass_of($class, RepositoryInterface::class)
            || !is_subclass_of($class, Persistable::class)) {
            throw SystemException::of('Invalid Model `%s` to be served.', $class);
        }

        if (!($connection = Db::entity_connection_name($class)) || !($db = Db::of($connection))) {
            throw SystemException::of('The entity `%s` is readonly at the moment.', $class);
        }

        if (is_int($entity)) {
            if ($statuses = $this->getAvailableStatuses()) {
                $options = ['status' => $statuses];
            } else {
                $options = null;
            }
            $entity = $this->fetchById($entity, $options);
            if (!$entity) {
                throw new NotFoundException('The record of entity %s is not found.', $class);
            }
        }

        if ($entity instanceof $class) {
            if ($entity->isNew()) {
                throw ValidationException::of('The instance of entity %s is not persist yet.', $class);
            }

            // if ($entity instanceof EntityManagerInterface) {
            return $entity->persist($changes, $db);
            // }
        }

        return false;
    }

    public function delete(int|Persistable $entity): bool
    {
        $class = static::modelClass();
        if (!is_subclass_of($class, RepositoryInterface::class)
            || !is_subclass_of($class, Persistable::class)) {
            throw SystemException::of('Invalid Model `%s` to be served.', $class);
        }

        if (!($connection = Db::entity_connection_name($class)) || !($db = Db::of($connection))) {
            throw SystemException::of('The entity `%s` is readonly at the moment.', $class);
        }

        if (is_int($entity)) {
            if ($statuses = $this->getAvailableStatuses()) {
                $options = ['status' => $statuses];
            } else {
                $options = null;
            }

            $entity = $this->fetchById($entity, $options);
            if (!$entity) {
                throw new NotFoundException;
            }
        }

        // if ($entity instanceof $class) {
        if ($entity->isNew()) {
            throw ValidationException::of('The instance of entity %s is not persist yet.', $class);
        }

        // if ($entity instanceof EntityManagerInterface) {
        return $entity->withConnection($db)->destroy();
        // }
        //}
    }

    /**
     * @throws DbException
     */
    public function trash(int|Recyclable $entity): bool
    {
        $class = static::modelClass();
        if (!is_subclass_of($class, RepositoryInterface::class)
            || !is_subclass_of($class, Persistable::class)) {
            throw SystemException::of('Invalid Model `%s` to be served.', $class);
        }

        if (!($connection = Db::entity_connection_name($class))) {
            throw SystemException::of('The entity `%s` is readonly at the moment.', $class);
        }

        if (is_int($entity)) {
            $entity = $this->fetchById($entity);
            if (!$entity) {
                throw new NotFoundException;
            }
        }

        if ($entity instanceof $class) {
            if ($entity->isTrashed()) {
                throw ValidationException::of('The instance of entity %s is already trashed.', $class);
            }

            if ($entity->isNew()) {
                throw ValidationException::of('The instance of entity %s is not persist yet.', $class);
            }

            if ($column = $entity::column_deleted_at()) {
                if ($db = Db::of($connection)) {
                    $result = $db->update($entity::entity_name(), [
                        $column => $time = new \DateTimeImmutable,
                    ], [
                        "($column IS NULL OR $column > CURRENT_TIMESTAMP())",
                        $entity::column_primary_key() => $entity->getId()
                    ]);

                    if ($result) {
                        $entity->$column = $time;
                    }
                }
            }
        }

        return $result ?? false;
    }

    /**
     * @throws DbException
     */
    public function restore(int|Recyclable $entity): bool
    {
        $class = static::modelClass();
        if (!is_subclass_of($class, RepositoryInterface::class)
            || !is_subclass_of($class, Persistable::class)) {
            throw SystemException::of('Invalid Model `%s` to be served.', $class);
        }

        if (!($connection = Db::entity_connection_name($class))) {
            throw SystemException::of('The entity `%s` is readonly at the moment.', $class);
        }

        if (is_int($entity)) {
            $entity = $this->fetchById($entity);
            if (!$entity) {
                throw new NotFoundException;
            }
        }

        if ($entity instanceof $class) {
            if (!$entity->isTrashed()) {
                return false;
            }

            if ($entity->isNew()) {
                throw ValidationException::of('The instance of entity %s is not persist yet.', $class);
            }

            if ($column = $entity::column_deleted_at()) {
                if ($db = Db::of($connection)) {
                    $result = $db->update($entity::entity_name(), [
                        $column => null,
                    ], [
                        "($column IS NOT NULL AND $column <= CURRENT_TIMESTAMP())",
                        $entity::column_primary_key() => $entity->getId()
                    ]);

                    if ($result) {
                        $entity->$column = null;
                    }
                }
            }
        }

        return $result ?? false;
    }

    protected function prepareCriteria(array $context, array $changes = null): array
    {
        $criteria = $context['criteria'] ?? null;
        if (is_string($criteria)) {
            $criteria = ['id' => $criteria];
        }
        if (!is_array($criteria)) {
            $criteria = [$criteria];
        }

        if ($changes) {
            $criteria = array_merge($criteria, $changes);
        }

        $context['criteria'] = $criteria;
        return $context;
    }

    public function getNone(): RepositoryInterface
    {
        return $this->getList(['where_false' => true]);
    }

    public function getList(array $context = null): RepositoryInterface
    {
        $class = static::modelClass();
        if (!is_subclass_of($class, RepositoryInterface::class)) {
            throw SystemException::of('Invalid Model to be served: %s.', $class);
        }

        $query = $class::readonly();

        if (is_string($primaryAlias = $context['primaryAlias'] ?? null)) {
            if ($primaryAlias = trim($primaryAlias, " \t\n\r\0\x0B.`")) {
                $query->alias($primaryAlias);
                $primaryAlias .= '.';
            }
        }

        if (is_string($relation = $context['relation'] ?? null)) {
            if ($relationAlias = $context['relationAlias'] ?? null) {
                $relationAlias = trim($relationAlias, " \t\n\r\0\x0B.`");
            }

            if (!$primaryAlias) {
                $primaryAlias = ($relationAlias === 'P') ? 'PP' : 'P';
                $query->alias($primaryAlias);
                $primaryAlias .= '.';
            }

            $query->through($relation, $relationAlias);
        }

        if ($context['where_false'] ?? null) {
            return $query->where('false');
        }

        if (is_subclass_of($class, TypeInterface::class)) {
            if ($type = $context['type'] ?? $class::defaultType() /* Reflection::constantValue(static::class, 'DEFAULT_TYPE') */) {
                $query->and($primaryAlias . 'type', $type);
            }
        }

        if ($context['scope'] ?? null) {
            if ($query instanceof RecyclableRepositoryInterface) {
                $query->withoutTrashed();
            }
        }

        if (is_subclass_of($class, StatusInterface::class)) {
            $status = $context['status'] ?? $class::defaultStatus();

            if ($query instanceof RecyclableRepositoryInterface) {
                if ($status === 'trashed') {
                    $query->onlyTrashed();
                } elseif ($status !== 'all') {
                    $query->withoutTrashed()->and($primaryAlias . 'status', $status);
                }
            } else {
                $query->and($primaryAlias . 'status', $status);
            }
        }

        // Refine query with criteria and order by
        $this->refineQueryCriteria($query, $context, $primaryAlias);
        $this->refineQuery($query, $context, $primaryAlias);

        if ($id = intval($context['id'] ?? null)) {
            $query->and($primaryAlias . 'id', $id);
        } else {
            $this->refineQuerySorting($query, $context, $primaryAlias);
        }

        return $this->refineQueryLimit($query, $context);
    }

    protected function refineQueryLimit(RepositoryInterface $query, array $context = null): RepositoryInterface
    {
        if ($limit = $context['limit'] ?? $context['limit_default'] ?? null) {
            $limit = intval($limit);
        }

        if ($page = $context['page'] ?? null) {
            $page = intval($page);
        }

        if (!$page && $limit) {
            $page = ceil(intval($context['offset'] ?? null) / $limit);
        }

        return $query->limit($limit ?? static::DEFAULT_LIMIT, $page ?: null);
    }

    protected function refineQuerySorting(RepositoryInterface $query, array $context = null, string $primaryAlias = null): RepositoryInterface
    {
        if ($orderBy = $context['orderBy'] ?? null) {
            if (is_string($orderBy)) {
                $orderBy = [$orderBy];
            }
        } else {
            $orderBy = [];
        }

        if ($defaultOrderBy = $this->getDefaultSorting()) {
            $orderBy += $defaultOrderBy;
        }

        foreach ($orderBy ?? [] as $column => $desc) {
            if (is_int($column)) {
                $column = $desc;
                $desc = false;
            } elseif (is_string($desc)) {
                $desc = !strcasecmp($desc, 'desc');
            }

            if (is_string($column)) {
                if (!str_contains($column, '.')) {
                    $column = $primaryAlias . $column;
                }
                $query->orderBy($column, (bool)$desc);
            }
        }

        return $query;
    }

    protected function refineQueryCriteria(RepositoryInterface $query, array $context = null, string $primaryAlias = null): RepositoryInterface
    {
        $criteria = $context['criteria'] ?? [];
        if (is_int($criteria)) {
            $criteria = [$primaryAlias . 'id' => $criteria];
        }
        if (!is_array($criteria)) {
            $criteria = [$criteria];
        }
        if ($defaultCriteria = $this->getDefaultCriteria()) {
            $criteria = array_merge($defaultCriteria, $criteria);
        }

        foreach ($criteria as $column => $value) {
            if (is_string($column)) {
                if (!str_contains($column, '.')) {
                    $column = $primaryAlias . $column;
                }

                $query->and($column, $value);
            } else {
                $query->where(Conditions::of($value, 'OR'));
            }
        }

        return $query;
    }

    protected function refineQuery(RepositoryInterface $query, array $context = null, string $primaryAlias = null): RepositoryInterface
    {
        if ($search = $context['search'] ?? null) {
            if (is_string($search)) {
                $this->refineQuerySearch($query, $search, $primaryAlias);
            }
        }

        return $query;
    }

    /**
     * Refine the search query based on the search keyword.
     *
     * @param RepositoryInterface $query The query to refine.
     * @param string $search The search keyword.
     * @param string|null $primaryAlias
     * @return RepositoryInterface The refined query.
     */
    protected function refineQuerySearch(RepositoryInterface $query, string $search, string $primaryAlias = null): RepositoryInterface
    {
        if (static::SEARCH_IN_BOOLEAN_MODE) {
            if ($keyword = preg_replace('/\s+/', ' ', trim($search))) {
                $columns = implode(',', static::SEARCH_IN_COLUMNS);
                if (is_string($columns)) {
                    $columns = [$columns];
                }

                if (is_array($columns) && $columns) {
                    $columns = array_map('trim', $columns);
                    $columnsString = $primaryAlias . implode(', ' . $primaryAlias, $columns);

                    $query->where("MATCH($columnsString) AGAINST(:keyword IN BOOLEAN MODE)")
                        ->bind('keyword', $keyword);
                }
            }
        } else {
            if ($keyword = preg_replace('/\s+/', '%', trim($search))) {

                $conditions = [];
                foreach (static::SEARCH_IN_COLUMNS as $column) {
                    $conditions[] = "$primaryAlias$column LIKE :keyword";
                }

                if ($conditions) {
                    $query->bind('keyword', "%$keyword%");
                    $query->where(Conditions::of($conditions, 'OR'));
                }
            }
        }

        return $query;
    }

    public function fetchList(array $context = null, string|callable $callback = null, int $mode = null): mixed
    {
        return $this->getList($context)->query()->fetch($callback, $mode);
    }

    public function fetchBy(string $column, mixed $value, array $context = null, string|callable $callback = null, int $mode = null): mixed
    {
        return $this->queryBy($column, $value, $context)->query()->fetch($callback, $mode);
    }

    public function fetchById(int $id, array $context = null, string|callable $callback = null, int $mode = null): mixed
    {
        return $this->queryById($id, $context)->query()->fetch();
    }

    public function queryById(int $id, array $context = null): RepositoryInterface
    {
        unset($context['limit'], $context['page'], $context['offset'], $context['orderBy']);
        return $this->queryBy('id', $id, $context);
    }

    public function queryBy(string $column, mixed $value, array $context = null): RepositoryInterface
    {
        $criteria = $context['criteria'] ?? [];
        if (!is_array($criteria)) {
            $criteria = [$criteria];
        }
        $criteria[$column] = $value;
        $context['criteria'] = $criteria;

        return $this->getList($context);
    }

    public function queryByRequest(array|\ArrayAccess $request, array $context = null, array &$args = null): RepositoryInterface
    {
        foreach (static::QUERY_IN_COLUMNS as $name) {
            if ($name) {
                $context[$name] ??= ($args[$name] = $request[$name] ?? $request[Str::toKebabCase($name)] ?? null);
            }
        }

        $context['id'] ??= $request['id'] ?? null;

        $context = array_merge($context ?? [], $args ?? []);
        return $this->getList($context);
    }

    /**
     * Validate a scope name
     *
     * @param string $scopeName
     * @return string|false
     */
    public function validateScopeName(string $scopeName): string|false
    {
        static $scopes;

        if (isset($scopes[static::class][$key = strtolower($scopeName)])) {
            return $scopes[$key];
        }

        $methodName = 'scope' . ucfirst($scopeName);
        if ($method = Reflection::method(static::class, $methodName)) {
            foreach (Reflection::types($method->getReturnType()) as $type) {
                if (!$type->isBuiltin()) {
                    if (is_a($type->getName(), RepositoryInterface::class, true)) {
                        return $scopes[static::class][$key] = $method->getName();
                    }
                }
            }
        }

        return $scopes[static::class][$key] = false;
    }

    public function queryByScope(string $scope, array $context = null): RepositoryInterface
    {
        if ($method = $this->validateScopeName($scope)) {
            $result = $this->$method($context);
            if ($result instanceof RepositoryInterface) {

                if ($result instanceof RecyclableRepositoryInterface) {
                    return $result->withoutTrashed();
                }

                return $result;
            }
        }

        return $this->getNone();
    }
}