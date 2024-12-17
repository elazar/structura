<?php

declare(strict_types=1);

namespace Elazar\Structura\Model;

use Elazar\Structura\Collection\NamedCollection;

/**
 * @extends NamedCollection<non-empty-string, Key>
 */
readonly class Keys extends NamedCollection
{
    public function __construct(
        Key... $keys,
    ) {
        parent::__construct(...$keys);
    }
}
