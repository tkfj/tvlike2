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
        $dbpath=base_path(env('DB_TVML'));
        if($dbpath){
            $db->statement("ATTACH DATABASE '" . $dbpath . "' AS tvml");
        }
        else {
            Log::warning($dbpath);
        }
    }
}