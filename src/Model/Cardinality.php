<?php

declare(strict_types=1);

namespace Elazar\Structura\Model;

enum Cardinality
{
    case ZeroOrOne;
    case ZeroOrMore;
    case One;
    case OneOrMore;
}
