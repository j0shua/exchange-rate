<?php

class Cache {

    protected $driver;

    public function __construct($type, $config)
    {
        $driver = ucfirst($type).'_Driver';
        $this->driver = new $driver($config);
    }


    public function get($id)
    {
        $this->dirver->get($id);
    }

    public function set($id, $data, $ttl = 0)
    {
        $this->driver->set($id, $data, $ttl = 0);
    }


    public function delete($id)
    {
        $this->driver->delelte($id);
    }

}

/**
 *
 * Cache driver interface.
 * similar to Kohana cache system
 * some code copied and modified
 *
 */
interface Cache_Driver {

  
    // set a cache item.
    public function set($id, $data, $lifetime);

    // set a cache item.
    // Return NULL if the cache item is not found.
    public function get($id);

    // delete cache items by id
    public function delete($id);
	
    // delete old cache items by id and age
    public function delete_expired($id, $threshold)

} // End Cache Driver

class Memcache_Driver implements Cache_Driver {
  
    // Cache backend object and flags
    protected $backend;
    protected $flags;

	public function __construct($config)
	{
        $this->backend = new Memcache;
		$this->flags = FALSE;

        // should really be loaded out of a config!
		$servers = $config['servers');

		foreach ($servers as $server)
		{
			// Make sure all required keys are set
			$server += array('host' => '127.0.0.1', 'port' => 11211, 'persistent' => FALSE);

			// Add the server to the pool
			$this->backend->connect($server['host'], $server['port'], (bool) $server['persistent'])
				or die('Cache: Connection failed: '.$server['host']);
		}

	}

	public function get($id)
	{
		return (($return = $this->backend->get($id)) === FALSE) ? NULL : $return;
	}

	public function set($id, $data, $lifetime = 0)
	{
		if ($lifetime !== 0)
		{
			// Memcache driver expects unix timestamp
			$lifetime += time();
		}

		// Set a new value
		return $this->backend->set($id, $data, $this->flags, $lifetime);
	}

	public function delete($id, $tag = FALSE)
	{
        return $this->backend->delete($id);
	}

	public function delete_expired($id, $threshold)
	{

        // delete old timestamps

		// Memcache handles garbage collection internally
		return TRUE;
	}

} // End Cache Memcache Driver
