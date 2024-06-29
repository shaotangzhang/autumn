<?php
/**
 * Autumn PHP Framework
 *
 * Date:        24/05/2024
 */

namespace Autumn\Database\Migration;

use App\Models\Developer\DeveloperIp;
use Autumn\Interfaces\ContextInterface;
use Autumn\Logging\ConsoleLogger;
use Autumn\System\Extension;
use Autumn\Traits\ContextInterfaceTrait;
use Psr\Log\LoggerInterface;

/**
 * Handles the migration and rollback operations for registered entities and extensions.
 *
 * This class provides methods to register entities and extensions for migration,
 * and to perform migration and rollback operations on them. It uses a logger to
 * record migration activities and any errors that occur during the process.
 *
 * @package Autumn\Database\Migration
 */
class Migration implements ContextInterface
{
    use ContextInterfaceTrait;

    /**
     * @var self|null The singleton instance for the context.
     */
    private static ?self $context = null;

    /**
     * @var array List of registered entities for migration.
     */
    private array $registeredEntities = [];

    /**
     * @var array List of registered extensions for migration.
     */
    private array $registeredExtensions = [];

    /**
     * @var LoggerInterface The logger instance used for logging migration activities.
     */
    private LoggerInterface $logger;

    /**
     * Migration constructor.
     *
     */
    public function __construct()
    {
        $this->logger = new ConsoleLogger;
    }

    /**
     * Executes the migration process for all registered entities and extensions.
     *
     * This method iterates over the registered extensions and entities, performing
     * the migration operation on each. It logs the progress and catches any exceptions
     * that occur during the process.
     *
     * @return void
     */
    public function migrate(): void
    {
        $this->handleExtensions('migrate');
        $this->handleEntities('up');
    }

    /**
     * Executes the rollback process for all registered entities and extensions.
     *
     * This method iterates over the registered extensions and entities, performing
     * the rollback operation on each. It logs the progress and catches any exceptions
     * that occur during the process.
     *
     * @return void
     */
    public function rollback(): void
    {
        $this->handleExtensions('rollback');
        $this->handleEntities('down');
    }

    /**
     * Registers one or more entities for migration.
     *
     * This method adds the specified entity classes to the list of registered entities
     * to be included in the migration process. Duplicate entries are not added.
     *
     * @param string ...$classes The entity classes to register.
     * @return void
     */
    public function registerEntity(string ...$classes): void
    {
        $this->registeredEntities = array_unique(array_merge($this->registeredEntities, $classes));
    }

    /**
     * Registers one or more extensions for migration.
     *
     * This method adds the specified extension classes to the list of registered extensions
     * to be included in the migration process. Duplicate entries are not added.
     *
     * @param string ...$extensions The extension classes to register.
     * @return void
     */
    public function registerExtension(string ...$extensions): void
    {
        $this->registeredExtensions = array_unique(array_merge($this->registeredExtensions, $extensions));
    }

    /**
     * Handles the migration or rollback process for registered extensions.
     *
     * This method iterates over the registered extensions and calls the specified
     * method ('migrate' or 'rollback') on each. Only classes that are subclasses
     * of Extension are processed.
     *
     * @param string $method The method to call on each extension ('migrate' or 'rollback').
     * @return void
     */
    private function handleExtensions(string $method): void
    {
        foreach ($this->registeredExtensions as $extension) {
            if (is_subclass_of($extension, Extension::class, true)) {
                $extension::$method();
            }
        }
    }

    /**
     * Handles the migration or rollback process for registered entities.
     *
     * This method iterates over the registered entities and calls the specified
     * method ('up' or 'down') on each Table instance. It logs the process and
     * catches any exceptions that occur.
     *
     * @param string $method The method to call on each Table instance ('up' or 'down').
     * @return void
     */
    private function handleEntities(string $method): void
    {
        foreach ($this->registeredEntities as $entity) {
            // $this->logger->info('Processing entity:', ['entity' => $entity]);

            try {
                print_r('Registering entity: ' . $entity . PHP_EOL);
                $table = new Table($entity);
                $table->setLogger($this->logger);
                $table->$method();
                print_r('Done' . PHP_EOL);
            } catch (\Throwable $ex) {
                // $this->logger->error($ex->getMessage());
                print_r($ex->getMessage() . PHP_EOL);
            }
        }
    }
}
