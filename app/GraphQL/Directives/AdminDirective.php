<?php
namespace App\GraphQL\Directives;

use App\Enums\userRoles;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth as Auth;

class AdminDirective extends BaseDirective implements FieldMiddleware
{
    public static function definition(): string {return '';}

    /**
     * Wrap around the final field resolver.
     *
     * @param \Nuwave\Lighthouse\Schema\Values\FieldValue $fieldValue
     * @param \Closure $next
     * @return \Nuwave\Lighthouse\Schema\Values\FieldValue
     */
    public function handleField(FieldValue $fieldValue, Closure $next): FieldValue
    {
        $user=Auth::user();
        if($user)
        if(Auth::user()->role== userRoles::$superAdmin)
        return $next($fieldValue);
        return $next($fieldValue);
        
        
        
        abort(401,'authenticated user is not allowed to performe this opperation');

    }
    

    

}