<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $db = DB::connection('sqlite');
        $tvmlpath=base_path(env('DB_TVML'));
        if($tvmlpath){
            $db->statement("ATTACH DATABASE '" . $tvmlpath . "' AS tvml");
        }
        else {
            Log::warning($tvmlpath);
        }
        $tvlikepath=base_path(env('DB_TVLIKE'));
        if($tvlikepath){
            $db->statement("ATTACH DATABASE '" . $tvlikepath . "' AS tvlike");
        }
        else {
            Log::warning($tvlikepath);
        }
        $tvguidepath=base_path(env('DB_TVGUIDE'));
        if($tvguidepath){
            $db->statement("ATTACH DATABASE '" . $tvguidepath . "' AS tvguide");
        }
        else {
            Log::warning($tvguidepath);
        }
    }
}