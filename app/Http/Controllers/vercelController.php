<?php

namespace App\Http\Controllers;

class vercelController extends Controller
{

    protected static $basUrl = "https://api.vercel.com";

    protected static function authorization()
    {
        $token = env("VERCEL_API_KEY");
        return ["Authorization: Bearer $token ", 'Content-Type:application/json'];

    }

    public function newProject()
    {
        $path = $this::$basUrl . "/v9/projects";
        $method = "POST";

        $data = [
            "name" => "tes",
            "gitRepository" => array(
                "repo" => "aeabdelhak/landing_pages",
                "type" => "github",
            ),
            "environmentVariables" => [
                ["key" => "LANDING_ID", "value" => "1", "target" => "production", "type" => "encrypted"],
                ["key" => "NEXT_PUBLIC_APIURL", "value" => env("APP_URL") . "/api/", "target" => "production", "type" => "encrypted"],
            ],

        ];
        return json_decode(curl($method, $path, $this::authorization(), $data));
    }
    public function newDeployment()
    {
        $path = $this::$basUrl . "/v9/projects";
        $method = "POST";

        $data = array(
            "name" => "tes",
            "gitRepository" => array(
                "repo" => "aeabdelhak/landing_pages",
                "type" => "github",
            ),
            "environmentVariables" => array(
                "LANDING_ID" => "@1",
                "NEXT_PUBLIC_APIURL" => "@" . env("APP_URL") . "/api/",
            ),
        );

        return json_decode(curl($method, $path, $this::authorization(), json_encode($data)));
    }

    public function domainAdd($domain)
    {

        $path = $this::$basUrl . "/v9/projects/landing-pages/domains";
        $method = "POST";

        $data = array(
            "name" => $domain,
        );

        return json_decode(curl($method, $path, $this::authorization(), $data));

    }
    public function domainRemove($domain)
    {

        $path = $this::$basUrl . "/v9/projects/landing-pages/domains/$domain";
        $method = "DELETE";

        return json_decode(curl($method, $path, $this::authorization()));

    }

}