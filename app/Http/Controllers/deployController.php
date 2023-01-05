<?php

namespace App\Http\Controllers;

use App\Models\landingPage;
use App\Models\store;
use Symfony\Component\Process\Process;

class deployController extends Controller
{
    public static function deployLanding(landingPage $landingPage)
    {
        $storeDomain = $landingPage->store->domain;
        $domain = $landingPage->domain . '.' . $landingPage->store->domain;
        $file = "/etc/nginx/sites-available/$domain";
        $symbolikfile = "/etc/nginx/sites-enabled/$domain";

        exec("rm $file");
        exec("rm $symbolikfile");

        $contents = file_get_contents('/var/www/configs/landing.txt');
        $config = str_replace('domain_name', trim($domain), $contents);
        $config = str_replace('base_domain', trim($storeDomain), $config);
        $new = fopen($file, 'w');
        fputs($new, $config);
        fclose($new);
        symlink($file, $symbolikfile);
    }
    public static function undeployLanding(landingPage $landingPage)
    {
        $domain = $landingPage->domain . '.' . $landingPage->store->domain;
        $file = "/etc/nginx/sites-available/$domain";
        $symbolikfile = "/etc/nginx/sites-enabled/$domain";
        exec("rm $file");
        exec("rm $symbolikfile");

    }
    public static function deployStore(store $store)
    {
        $domain = $store->domain;
        $file = "/etc/nginx/sites-available/$domain";
        $symbolikfile = "/etc/nginx/sites-enabled/$domain";

        exec("rm $file");
        exec("rm $symbolikfile");

        $contents = file_get_contents('/var/www/configs/store.txt');
        $config = str_replace('domain', trim($domain), $contents);
        $new = fopen($file, 'w');
        fputs($new, $config);
        fclose($new);
        symlink($file, $symbolikfile);

    }
    public static function undeployStore(store $store)
    {

        $domain = $store->domain;
        $file = "/etc/nginx/sites-available/$domain";
        $symbolikfile = "/etc/nginx/sites-enabled/$domain";
        exec("rm $file");
        exec("rm $symbolikfile");

    }
    public static function reloadNginx()
    {
        $Process = Process::fromShellCommandline("sudo nginx -s reload");
        $Process->mustRun();

    }
    public static function generateWildcardSSl($domain)
    {
        $live = "/etc/letsencrypt/live/$domain";
        if (is_dir($live)) {
            return true;
        }

        try {

            $Process = Process::fromShellCommandline("certbot --server https://acme-v02.api.letsencrorg/directory -d *.$domain --manual --preferred-challenges http-01 certonly");
            $Process->mustRun();
            return true;

        } catch (\Throwable$th) {
            return false;
        }

    }
}
