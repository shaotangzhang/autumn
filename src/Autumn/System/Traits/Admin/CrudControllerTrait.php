<?php
/**
 * Autumn PHP Framework
 *
 * Date:        19/05/2024
 */

namespace Autumn\System\Traits\Admin;

use Autumn\App;
use Autumn\Database\Interfaces\StatusInterface;
use Autumn\Database\Interfaces\TypeInterface;
use Autumn\Exceptions\NotFoundException;
use Autumn\Interfaces\Renderable;
use Autumn\System\Services\CrudServiceInterface;
use Autumn\System\Services\MultipleTypesRelationServiceInterface;

trait CrudControllerTrait
{
    protected function modelName(): string
    {
        return static::MODEL_NAME;
    }

    protected function modelNames(): string
    {
        return static::MODEL_NAMES;
    }

    protected function modelClass(): string
    {
        static $instances;

        if (isset($instances[static::class])) {
            return $instances[static::class];
        }

        if (!defined(static::class . '::DEFAULT_TYPE') || !static::DEFAULT_TYPE) {
            return static::MODEL;
        }

        $instance = eval(sprintf('return new class extends %s {
                    public const DEFAULT_TYPE = \'%s\';
                };', static::MODEL, addslashes(static::DEFAULT_TYPE)));

        return $instances[static::class] = $instance::class;
    }

    protected function modelService(): CrudServiceInterface
    {
        static $instances;

        if (isset($instances[static::class])) {
            return $instances[static::class];
        }

        if ($class = static::MODEL_SERVICE) {
            if (is_subclass_of($class, MultipleTypesRelationServiceInterface::class)) {
                if (defined(static::class . '::RELATION') && static::RELATION) {
                    $relationAlias = (defined($const = static::class . '::RELATION_ALIAS')
                        ? constant($const) : null) ?: 'R';

                    $primaryAlias = (defined($const = static::class . '::RELATION_PRIMARY_ALIAS')
                        ? constant($const) : null) ?: 'P';

                    if ($relationAlias === $primaryAlias) {
                        $primaryAlias .= 'P';
                    }

                    return $instances[static::class]
                        = $class::forRelation(static::RELATION, $relationAlias, $primaryAlias);
                }
            }
        }

        return $instances[static::class] = App::factory(static::MODEL_SERVICE);
    }

    protected function modelDefaultStatus(): string
    {
        return call_user_func([static::MODEL, 'defaultStatus']);
    }

    public function search(array $context = []): Renderable
    {
        $service = $this->modelService();

        $context['status'] = ($context['status'] ?? null) ?: $this->modelDefaultStatus();
        $query = $service->getList($context);

        $this->set('title', $this->get('title') ?: translate('Search result'));
        return $this->paginate($query, 'index', $context);
    }

    public function index(
        ?string $search, ?string $status,
        ?int    $parentId, ?int $categorizedId,
        ?int    $limit, ?int $page
    ): Renderable
    {
        $this->set('title', translate($this->modelNames()));

        return $this->search(compact(
            'search', 'status', 'parentId', 'categorizedId', 'limit', 'page'
        ));
    }

    public function add(?int $copy): Renderable
    {
        $service = $this->modelService();

        if ($copy) {
            $page = $service->getById($copy);
            if (!$page) {
                throw NotFoundException::of('The %s record to copy (ID: %s) is not found.', $this->modelName(), $copy);
            }

            $this->set('item', $page);
        } else {
            $class = $this->modelClass();
            $this->set('item', new $class);
        }

        $this->set('title', translate('Add ' . $this->modelName()));
        return $this->view('add');
    }

    public function create(array $item): Renderable
    {
        $service = $this->modelService();

        $class = $this->modelClass();

        if (is_subclass_of($class, TypeInterface::class)) {
            $item['type'] ??= $class::defaultType();
        }

        if (is_subclass_of($class, StatusInterface::class)) {
            $item['status'] ??= $class::defaultStatus();
        }

        $entity = call_user_func([$this->modelClass(), 'from'], $item);

        return $this->success(__FUNCTION__, $service->persist($entity));

    }

    public function edit(int $id): Renderable
    {
        $service = $this->modelService();
        $page = $service->getById($id, ['status' => 'all']);
        if (!$page) {
            throw NotFoundException::of('The %s record to edit %s is not found.', $this->modelName(), $id);
        }

        $this->set('title', translate('Edit ' . $this->modelName()));
        return $this->view('edit', ['item' => $page]);
    }

    public function update(int $id, array $item): Renderable
    {
        $service = $this->modelService();
        $entity = $service->getById($id, ['status' => 'all']);
        if (!$entity) {
            throw NotFoundException::of('The %s record to update %s is not found.', $this->modelName(), $id);
        }

        return $this->success(__FUNCTION__, $service->update($entity, $item));
    }

    /**
     * Soft deletion
     * @param int $id
     * @return Renderable
     */
    public function delete(int $id): Renderable
    {
        $service = $this->modelService();
        $entity = $service->getById($id, ['status' => 'all']);
        if (!$entity) {
            throw NotFoundException::of('The %s record to delete %s is not found.', $this->modelName(), $id);
        }

        if (!$entity->isTrashed()) {
            return $this->success(__FUNCTION__, $service->trash($entity));
        }

        $this->set('title', translate('Delete ' . $this->modelName()));
        return $this->view('delete', ['item' => $entity]);
    }

    /**
     * Hard deletion
     * @param int $id
     * @return Renderable
     */
    public function destroy(int $id): Renderable
    {
        $service = $this->modelService();
        $entity = $service->getById($id, ['status' => 'all']);
        if (!$entity) {
            throw NotFoundException::of('The %s record to delete %s is not found.', $this->modelName(), $id);
        }

        if ($entity->isTrashed()) {
            $result = $service->destroy($entity);
        } else {
            $result = $service->trash($entity);
        }

        return $this->success(__FUNCTION__, $result);
    }

    public function restore(int $id): Renderable
    {
        $service = $this->modelService();
        $entity = $service->getById($id, ['status' => 'trashed']);
        if (!$entity) {
            throw NotFoundException::of('The %s restore %s is not found.', $this->modelName(), $id);
        }

        if ($entity->isTrashed()) {
            $result = $service->restore($entity);
        }

        return $this->success(__FUNCTION__, $result ?? false);
    }
}