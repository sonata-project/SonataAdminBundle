<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Filter\Model;

/**
 * @psalm-immutable
 */
final class FilterData
{
    private ?int $type;

    /**
     * @var mixed
     */
    private $value;

    private bool $hasValue;

    /**
     * @param mixed $value
     */
    private function __construct(?int $type, bool $hasValue, $value = null)
    {
        $this->type = $type;
        $this->hasValue = $hasValue;

        if ($hasValue) {
            $this->value = $value;
        }
    }

    /**
     * @param array{type?: int|numeric-string|null, value?: mixed} $data
     *
     * @psalm-pure
     */
    public static function fromArray(array $data): self
    {
        if (isset($data['type'])) {
            if (!\is_int($data['type']) && (!\is_string($data['type']) || !is_numeric($data['type']))) {
                throw new \InvalidArgumentException(sprintf(
                    'The "type" parameter MUST be of type "integer" or "null", %s given.',
                    \is_object($data['type']) ? 'instance of "'.\get_class($data['type']).'"' : '"'.\gettype($data['type']).'"'
                ));
            }

            $type = (int) $data['type'];
        } else {
            $type = null;
        }

        return new self($type, \array_key_exists('value', $data), $data['value'] ?? null);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if (!$this->hasValue) {
            throw new \LogicException('The FilterData object does not have a value.');
        }

        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function changeValue($value): self
    {
        return self::fromArray([
            'type' => $this->getType(),
            'value' => $value,
        ]);
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function isType(int $type): bool
    {
        return $this->type === $type;
    }

    public function hasValue(): bool
    {
        return $this->hasValue;
    }
}
