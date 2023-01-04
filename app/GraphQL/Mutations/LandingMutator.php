<?php

namespace App\GraphQL\Mutations;

use App\Http\Controllers\FilesController;
use App\Models\landingPage;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Throwable;

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

        $status = 0;
        $message = '';

        $store = JWTAuth::user()->store();
        $fulldomain = strtolower(trim($args['domain'] . '.' . $store->domain));
        $exist = landingPage::whereDomain($fulldomain)->first();
        if (!$exist) {
            $landingPage = new landingPage();
            $landingPage->name = $args['name'];
            $landingPage->description = $args['description'];
            $landingPage->domain = $fulldomain;
            $landingPage->product_description = $args['product_description'];
            $landingPage->product_name = $args['product_name'];
            $landingPage->id_store = $store->id;
            $landingPage->id_poster = FilesController::store($args['poster']);
            $landingPage->id_pallete = $args['id_pallete'];
            if ($landingPage->save()) {
                $file = "/etc/nginx/sites-available/$fulldomain";
                $symbolikfile = "/etc/nginx/sites-enabled/$fulldomain";
                $contents = file_get_contents('/var/www/configs/landing.txt');
                $config = str_replace('domain_name', trim($fulldomain), $contents);
                $config = str_replace('base_domain', trim($store->domain), $config);

                if (file_exists($file)) {
                    unlink($file);
                    unlink($symbolikfile);
                }
                try {
                    $new = fopen($file, 'w');
                    fputs($new, $config);
                    fclose($new);
                    symlink($file, $symbolikfile);
                } catch (Throwable $e) {
                    $message = $e;
                    $status = 0;
                    $landingPage->forceDelete();

                }

                try
                {
                    $genCrt = Process::fromShellCommandline("nginx -s reload");
                    $genCrt->mustRun();
                    $status = 1;
                } catch (ProcessFailedException $exception) {
                    $landingPage->forceDelete();
                    $message = json_encode($exception);
                    $status = 0;
                }
            }} else {
            $status = 3;
        }

        return [
            'landingPage' => $landingPage,
            'status' => $status,
            'message' => $message,
        ];
    }

}
