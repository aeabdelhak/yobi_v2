<?php

namespace App\GraphQL\Directives;

use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgDirectiveForArray;
use Nuwave\Lighthouse\Support\Contracts\ArgResolver;

final class PermissionDirective extends BaseDirective implements ArgDirectiveForArray, ArgResolver
{
    // TODO implement the directive https://lighthouse-php.com/master/custom-directives/getting-started.html

    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
directive @permission on INPUT_FIELD_DEFINITION
GRAPHQL;
    }

    /**
     * @param  mixed  $root  The result of the parent resolver.
     * @param  mixed|\Nuwave\Lighthouse\Execution\Arguments\ArgumentSet|array<\Nuwave\Lighthouse\Execution\Arguments\ArgumentSet>  $value  The slice of arguments that belongs to this nested resolver.
     * @return mixed
     */
    public function __invoke($root, $value)
    {
        // TODO implement the arg resolver
    }
}
