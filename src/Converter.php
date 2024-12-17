<?php

declare(strict_types=1);

namespace Elazar\Structura;

use Elazar\Structura\{
    Model\Databases,
    Reader\Reader,
    Reader\Readers,
    Resolver\Resolver,
    Writer\Writer,
};

class Converter
{
    public function __construct(
        private readonly Readers $readers,
        private readonly Resolver $resolver,
        private readonly Writer $writer,
    ) { }

    public function convert(): void
    {
        $databases = new Databases(
            ...array_merge(
                ...$this->readers->map(
                    fn(Reader $reader) => [...$reader->getDatabases()]
                ),
            ),
        );
        $relationships = $this->resolver->resolve($databases);
        $this->writer->write($databases, $relationships);
    }
}
