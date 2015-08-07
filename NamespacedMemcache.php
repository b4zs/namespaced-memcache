<?php


class NamespacedMemcache extends Memcache
{
	const NAMESPACE_PREFIX = 'namespace-name';

	private static $instance;

	private $namespaceVersion = 0;

	private $alreadyConnected = false;

	public static function getInstance()
	{
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function connect($host, $port, $timeout = 1)
	{
		if (!$this->alreadyConnected) {
			parent::connect($host, $port, $timeout);
			$this->initializeKeyNamespace();

			$this->alreadyConnected = true;
		}
	}

	public function flush()
	{
		$this->incrementNamespaceVersion();

		return true;
	}

	public function add($key, $var, $flag, $expire)
	{
		return parent::add($this->prependNamespace($key), $var, $flag, $expire);
	}

	public function get($key, &$flags = 0)
	{
		return parent::get($this->prependNamespace($key), $flags);
	}

	public function set($key, $var, $flag, $expire)
	{
		return parent::set($this->prependNamespace($key), $var, $flag, $expire);
	}

	public function delete($key, $timeout = 0)
	{
		return parent::delete($this->prependNamespace($key), $timeout);
	}

	private function initializeKeyNamespace()
	{
		$this->namespaceVersion = parent::get($this->getNamespacePrefix());

		if (!$this->namespaceVersion) {
			$this->namespaceVersion = 0;
			$this->incrementNamespaceVersion();
		}
	}

	private function incrementNamespaceVersion()
	{
		parent::set($this->getNamespacePrefix(), ++$this->namespaceVersion);
	}

	private function prependNamespace($key)
	{
		return $this->getNamespacePrefix() . '_' . $this->namespaceVersion . '_'.$key;
	}

	private function getNamespacePrefix()
	{
		return isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : self::NAMESPACE_PREFIX;
	}
}