<?php declare(strict_types = 1);

namespace Contributte\Psr6;

use Contributte\Psr6\Exception\InvalidArgumentException;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Nette\Caching\Cache;
use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface
{

	/** @var string */
	private $key;

	/** @var mixed */
	private $value;

	/** @var bool */
	private $hit;

	/** @var mixed[] */
	protected $dependencies = [];

	public function getKey(): string
	{
		return $this->key;
	}

	public function get(): mixed
	{
		return $this->value;
	}

	public function isHit(): bool
	{
		return $this->hit;
	}

	public function set(mixed $value): static
	{
		$this->value = $value;

		return $this;
	}

	public function expiresAt(?DateTimeInterface $expiration): static
	{
		if ($expiration === null) {
			$this->dependencies[Cache::EXPIRE] = null;

			return $this;
		}

		if ($expiration instanceof DateTimeInterface) {
			$this->dependencies[Cache::EXPIRE] = $expiration->format('U.u');

			return $this;
		}

		throw new InvalidArgumentException(
			sprintf('Invalid type "%s" for $expiration', gettype($expiration))
		);
	}

	public function expiresAfter(DateInterval|int|null $time): static
	{
		if ($time === null) {
			$this->dependencies[Cache::EXPIRE] = null;

			return $this;
		}

		if ($time instanceof DateInterval) {
			/** @var DateTimeImmutable $date */
			$date = DateTimeImmutable::createFromFormat('U', (string) time());
			$this->dependencies[Cache::EXPIRE] = $date->add($time)->format('U');

			return $this;
		}

		if (is_int($time)) {
			$this->dependencies[Cache::EXPIRE] = $time + time();

			// Infinite
			if ($time === 0) {
				unset($this->dependencies[Cache::EXPIRE]);
			}

			return $this;
		}

		throw new InvalidArgumentException(
			sprintf('Invalid type "%s" for $time', gettype($time))
		);
	}

	/**
	 * @return mixed[]
	 */
	public function getDependencies(): array
	{
		return $this->dependencies;
	}

	/**
	 * @param mixed[] $dependencies
	 */
	public function setDependencies(array $dependencies): self
	{
		$this->dependencies = $dependencies;

		return $this;
	}

}
