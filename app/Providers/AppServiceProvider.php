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
    	if(config('database.connections.sqlite.db_path.tvml')){
            $tvmlpath=base_path(config('database.connections.sqlite.db_path.tvml'));
	        if(is_file($tvmlpath)){
		        $db->statement("ATTACH DATABASE '" . $tvmlpath . "' AS tvmldb");
    		}
            else {
                Log::warning($tvmlpath);
            }
        }
    	if(config('database.connections.sqlite.db_path.tvlike')){
            $tvlikepath=base_path(config('database.connections.sqlite.db_path.tvlike'));
            if(is_file($tvlikepath)){
                $db->statement("ATTACH DATABASE '" . $tvlikepath . "' AS tvlikedb");
            }
            else {
                Log::warning($tvlikepath);
            }
        }
    	if(config('database.connections.sqlite.db_path.epg')){
            $tvguidepath=base_path(config('database.connections.sqlite.db_path.epg'));
            if(is_file($tvguidepath)){
                $db->statement("ATTACH DATABASE '" . $tvguidepath . "' AS epgdb");
            }
            else {
                Log::warning($tvguidepath);
            }
        }
    }
}
