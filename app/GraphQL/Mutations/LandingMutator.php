<?php

namespace App\GraphQL\Mutations;

use App\Http\Controllers\FilesController;
use App\Models\landingPage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

final class LandingMutator
{
    /**
     * @param  null  $_
     * @param  array{}  $args
     */
    public function __invoke($_, array $args)
    {
        // TODO implement the resolver
    }
    public function newLanding($_, array $args)
    {
        $store=JWTAuth::user()->store();
        $fulldomain = strtolower(trim($args['domain'] . '.' . $store->domain));
        if (landingPage::whereDomain($fulldomain)->first()) {
            return res('fail', 'the domain is already connected to another landing page', null);
        }
        $landingPage = new landingPage();
        $landingPage->name = $args['name'];
        $landingPage->description = $args['description'];
        $landingPage->domain = $fulldomain;
        $landingPage->product_description = $args['product_description'];
        $landingPage->product_name = $args['product_name'];
        $landingPage->id_store = $store->id;
        $landingPage->id_poster = FilesController::store($args['poster']);
        $landingPage->id_pallete = $args['id_pallete'];
        if($landingPage->save()){
            $file= "/etc/nginx/sites-available/$fulldomain";
            $contents=file_get_contents('/var/www/configs/landing.txt');
            $config= str_replace('domain_name',trim($fulldomain),$contents);
                
                if(file_exists($file)){
                    exec("rm /etc/nginx/sites-available/$fulldomain");
                    exec("rm /etc/nginx/sites-enabled/$fulldomain");
                }
                    $new=fopen($file,'w');
                    fputs($new,$config);
                    fclose($new);
                    exec("sudo ln -s /etc/nginx/sites-available/$fulldomain /etc/nginx/sites-enabled/$fulldomain");
                    exec("nginx -s reload");

        }
        return $landingPage;
    }

}
