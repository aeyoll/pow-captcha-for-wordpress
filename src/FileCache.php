<?php

namespace Aeyoll\PowCaptchaForWordpress;

/**
 * FileCache
 *
 * http://github.com/inouet/file-cache/
 *
 * A simple PHP class for caching data in the filesystem.
 *
 * License
 *   This software is released under the MIT License, see LICENSE.txt.
 *
 * @package FileCache
 * @author  Taiji Inoue <inudog@gmail.com>
 */
class FileCache
{

    /**
     * The root cache directory.
     * @var string
     */
    private $cache_dir = '/tmp/cache';

    /**
     * Creates a FileCache object
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $available_options = array('cache_dir');
        foreach ($available_options as $name) {
            if (isset($options[$name])) {
                $this->$name = $options[$name];
            }
        }
    }

    /**
     * Get the content of a file
     *
     * @param string $file_name
     *
     * @return array|false
     */
    public function get_file_content($file_name)
    {
        if (!is_file($file_name) || !is_readable($file_name)) {
            return false;
        }

        return file($file_name);
    }

    /**
     * Get the lifetime of a file
     *
     * @param array $lines
     *
     * @return int
     */
    public function get_file_time($lines)
    {
        $lifetime = array_shift($lines);
        $lifetime = (int) trim($lifetime);
        return $lifetime;
    }

    /**
     * Fetches an entry from the cache.
     *
     * @param string $id
     */
    public function get($id)
    {
        $file_name = $this->get_file_name($id);
        $lines = $this->get_file_content($file_name);

        if (!$lines) {
            return false;
        }

        $lifetime = $this->get_file_time($lines);

        if ($lifetime !== 0 && $lifetime < time()) {
            @wp_delete_file($file_name);
            return false;
        }

        array_shift($lines);

        $serialized = join('', $lines);
        $data = unserialize($serialized);

        return $data;
    }

    /**
     * Deletes a cache entry.
     *
     * @param string $id
     *
     * @return bool
     */
    public function delete($id)
    {
        $file_name = $this->get_file_name($id);
        return wp_delete_file($file_name);
    }

    /**
     * Writes data atomically.
     *
     * @param string $filename
     * @param mixed  $data
     *
     * @return bool
     */
    public function atomic_file_put_contents($filename, $data)
    {
        $tmpName = $filename . '-' . uniqid('', true);

        if (!file_put_contents($tmpName, $data)) {
            return false;
        }

        return rename($tmpName, $filename);
    }

    /**
     * Puts data into the cache.
     *
     * @param string $id
     * @param mixed  $data
     * @param int    $expireAfter
     *
     * @return bool
     */
    public function save($id, $data, $expireAfter = 3600)
    {
        $dir = $this->get_directory($id);

        if (!is_dir($dir)) {
            if (!wp_mkdir_p($dir)) {
                return false;
            }
        }

        $file_name = $this->get_file_name($id);
        $file_content = $this->get_file_content($file_name);

        if ($file_content) {
            $new_lifetime = $this->get_file_time($file_content);
        } else {
            $new_lifetime = time() + $expireAfter;
        }

        $serialized = serialize($data);

        return $this->atomic_file_put_contents($file_name, $new_lifetime . PHP_EOL . $serialized);
    }

    //------------------------------------------------
    // PRIVATE METHODS
    //------------------------------------------------

    /**
     * Fetches a directory to store the cache data
     *
     * @param string $id
     *
     * @return string
     */
    protected function get_directory($id)
    {
        $hash = sha1($id, false);
        $dirs = array(
            $this->get_cache_directory(),
            substr($hash, 0, 2),
            substr($hash, 2, 2)
        );
        return join(DIRECTORY_SEPARATOR, $dirs);
    }

    /**
     * Fetches a base directory to store the cache data
     *
     * @return string
     */
    protected function get_cache_directory()
    {
        return $this->cache_dir;
    }

    /**
     * Fetches a file path of the cache data
     *
     * @param string $id
     *
     * @return string
     */
    protected function get_file_name($id)
    {
        $directory = $this->get_directory($id);
        $hash      = sha1($id, false);
        $file      = $directory . DIRECTORY_SEPARATOR . $hash . '.cache';
        return $file;
    }
}
