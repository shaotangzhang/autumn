<?php

namespace Autumn\System\Sessions;

use Autumn\App;
use Autumn\Exceptions\SystemException;
use Autumn\Interfaces\ContextInterface;
use Autumn\Traits\ContextInterfaceTrait;

class FileSessionHandler implements \SessionHandlerInterface, ContextInterface
{
    use ContextInterfaceTrait;

    private string $savePath;

    /**
     * Constructor to initialize the session save path.
     *
     * @param string $savePath
     * @throws SystemException if the save path cannot be created
     */
    public function __construct(string $savePath)
    {
        $this->savePath = $savePath;
        if (!is_dir($savePath)) {
            if (!mkdir($savePath, 0777, true)) {
                throw SystemException::of('Invalid path for session storage.');
            }
        }
    }

    /**
     * Creates a default instance using the configured save path.
     *
     * @return static
     */
    protected static function createDefaultInstance(): static
    {
        $savePath = env('SESSION_SAVE_PATH') ?: App::map('storage', App::name(), 'sessions');
        return new static($savePath);
    }

    /**
     * Closes the session handler.
     *
     * @return bool true
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Destroys a session identified by its ID.
     *
     * @param string $id
     * @return bool true on success, false on failure
     */
    public function destroy(string $id): bool
    {
        $file = $this->savePath . '/sess_' . $id;
        if (realpath($file)) {
            return unlink($file);
        }
        return true;
    }

    /**
     * Cleans up expired sessions.
     *
     * @param int $max_lifetime
     * @return int|false number of deleted sessions or false on failure
     */
    public function gc(int $max_lifetime): int|false
    {
        $count = 0;
        foreach (glob($this->savePath . '/sess_*') as $file) {
            if (filemtime($file) + $max_lifetime < time() && file_exists($file)) {
                unlink($file);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Opens the session handler.
     *
     * @param string $path
     * @param string $name
     * @return bool true on success
     * @throws SystemException if session directory cannot be created
     */
    public function open(string $path, string $name): bool
    {
        $sessionPath = $path . DIRECTORY_SEPARATOR . md5($name);
        if (!is_dir($sessionPath) && !mkdir($sessionPath, 0777, true)) {
            throw SystemException::of('Failed to create session directory.');
        }
        return true;
    }

    /**
     * Reads session data identified by its ID.
     *
     * @param string $id
     * @return string|false session data or false on failure
     */
    public function read(string $id): string|false
    {
        $file = $this->savePath . '/sess_' . $id;
        if (realpath($file)) {
            return file_get_contents($file) ?: '';
        }
        return '';
    }

    /**
     * Writes session data identified by its ID.
     *
     * @param string $id
     * @param string $data
     * @return bool true on success, false on failure
     */
    public function write(string $id, string $data): bool
    {
        $file = $this->savePath . '/sess_' . $id;
        return !!file_put_contents($file, $data, LOCK_EX);
    }
}
