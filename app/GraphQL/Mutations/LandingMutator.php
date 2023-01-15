<?php

namespace App\GraphQL\Mutations;

use App\Http\Controllers\deployController;
use App\Http\Controllers\FilesController;
use App\Models\landingPage;
use Illuminate\Support\Facades\DB;
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
        $landingPage = null;

        $store = JWTAuth::user()->store();
        $exist = landingPage::whereDomain($args['domain'])->where('id_store', $store->id)->first();
        if (!$exist) {
            $landingPage = new landingPage();
            $landingPage->name = $args['name'];
            $landingPage->description = $args['description'];
            $landingPage->domain = trim($args['domain']);
            $landingPage->product_description = $args['product_description'];
            $landingPage->product_name = $args['product_name'];
            $landingPage->id_store = $store->id;
            $landingPage->id_poster = FilesController::store($args['poster']);
            $landingPage->id_pallete = $args['id_pallete'];
            if ($landingPage->save()) {

                try {
                    deployController::deployLanding($landingPage);

                } catch (Throwable $e) {
                    $message = $e;
                    $status = 0;
                    $landingPage->forceDelete();

                }

                try
                {
                    $Process = Process::fromShellCommandline("sudo nginx -s reload");
                    $Process->mustRun();
                    $status = 1;
                } catch (ProcessFailedException $exception) {
                    $landingPage->forceDelete();
                    $message = $exception;
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
    public function deleteLanding($_, array $args)
    {
        $status = 0;

        $id = $args['id'];
        $landingPage = landingPage::where('id', $id)->first();
        if ($landingPage) {
            DB::beginTransaction();
            try {
                deployController::undeployLanding($landingPage);
                $landingPage->delete();
                $status = 1;
                DB::commit();
            } catch (\Throwable$th) {
                DB::rollBack();
                $status = 2;
            }

        } else {
            $status = 0;
        }
        return compact('status');
    }
    public function changedomain($_, array $args)
    {
        $status = 0;

        $id = $args['id'];
        $domain = $args['domain'];
        $landingPage = landingPage::where('id', $id)->first();
        if ($landingPage) {
            DB::beginTransaction();
            try {
                if ($domain != $landingPage->domain) {
                    deployController::undeployLanding($landingPage);
                }
                $landingPage->domain = $domain;
                $landingPage->save();
                $landingPage->refresh();
                if ($domain != $landingPage->domain) {
                    deployController::deployLanding($landingPage);
                }
                $status = 1;
                DB::commit();
            } catch (\Throwable$th) {
                DB::rollBack();
                $status = 2;
            }

        } else {
            $status = 0;
        }
        return compact('status');
    }

    public function posterChange($_, array $args)
    {

        $id = $args['id'];
        $poster = $args['upload'];
        $landingPage = landingPage::where('id', $id)->first();
        if ($landingPage) {
            DB::beginTransaction();
            try {
                $old = $landingPage->id_poster;
                $landingPage->id_poster = FilesController::store($poster);
                if ($landingPage->save()) {
                    FilesController::delete($old);
                }
                return $landingPage->poster;
            } catch (\Throwable) {
                return null;
            }
        }
        return null;
    }
}
