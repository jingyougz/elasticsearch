<?php

declare(strict_types=1);

namespace Jingyougz\Elasticsearch;

use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Collection;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;
use JsonSerializable;

abstract class Model implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * @var string 索引
     */
    protected $index;
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var string
     */
    protected $connection = 'default';

    protected bool $debug = false;

    protected bool $log = true;
	
	protected int $cacheExpire = 60;

    use HasAttributes;

    public function __construct()
    {
        $this->client = ApplicationContext::getContainer()->get(Client::class);
    }

    public function getDebug()
    {
        return $this->debug;
    }
	
	public function getCacheExpire()
	{
		return $this->cacheExpire;
	}


    public function getLog()
    {
        return $this->log;
    }

    /**
     * @return Builder
     */
    public static function query()
    {
        return (new static())->newQuery();
    }

    /**
     * @return Builder
     */
    public function newQuery()
    {
        return $this->newModelBuilder()->setModel($this);
    }

    /**
     * @return Model
     */
    public function setDebug($isDebug)
    {
        $this->debug = $isDebug;
        return $this;
    }

    public function setLog($isLog)
    {
        $this->debug = $isLog;
        return $this;
    }
	
	/**
	 * @return Model
	 */
	public function setCacheExpire($expire)
	{
		$this->cacheExpire = $expire;
		return $this;
	}

    /**
     * @return \Elasticsearch\Client
     */
    public function getClient()
    {
        return $this->client->create($this->connection);
    }

    /**
     * Create a new Model Collection instance.
     *
     * @return Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }

    /**
     * @return $this
     */
    public function newInstance()
    {
        $model = new static();
        return $model;
    }

    /**
     * Create a new Model query builder
     *
     * @return Builder
     */
    public function newModelBuilder()
    {
        return new Builder();
    }

    /**
     * @return string
     */
    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * @param string $index
     */
    public function setIndex(string $index): void
    {
        $this->index = $index;
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param string $method
     * @param array $parameters
     */
    public function __call($method, $parameters)
    {
        return call([$this->newQuery(), $method], $parameters);
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param string $method
     * @param array $parameters
     */
    public static function __callStatic($method, $parameters)
    {
        return (new static())->{$method}(...$parameters);
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        } else {
            return null;
        }
    }

    public function __set($name, $value)
    {
        $this->{$name} = $value;
        $this->attributes[$name] = $value;
    }

}
