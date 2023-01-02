<?php

namespace App\GraphQL\Mutations;

use App\Http\operations\order\saveNewOrder;
use App\Http\operations\orderOperations;
use App\Models\order;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

final class OrderMutator
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */

    public function __invoke($_, array $args)
    {
        // TODO implement the resolver
    }

    public function delete($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $ids = $args['ids'];
        $delete = order::whereIn('id', $ids)->delete();
        if ($delete) {
            return 1;
        }
        return 0;

    }
    public function newOrder($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {

      $order=  orderOperations::newOrder(json_decode(json_encode( $args['model'])));
        return $order;
    }
    public function changeOrderStatus($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {

       $note = array_key_exist_or('note',$args,null);
       $date= array_key_exist_or('date',$args,null);
      $order=  orderOperations::changeStatus($args['id'],$args['status'],$note,$date);
      if($order=='params needed')
        abort(400);  
      return $order;
    }
    public function pushToTawsilix($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {

       $note = array_key_exist_or('note',$args,null);
      $order=  orderOperations::pushToDelivery($args['id'],$note);
      return $order;
    }

}