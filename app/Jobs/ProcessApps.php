<?php

namespace App\Jobs;

use App\Application;
use App\Item;
use App\SupportedApps;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessApps implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $localapps = Application::whereNull('class')->get();
        $json = SupportedApps::getList()->getBody();

        Storage::disk('local')->put('supportedapps.json', $json);

        foreach ($localapps as $app) {
            $app->class = $app->class();
            $app->save();
        }

        $items = Item::whereNotNull('class')->get();
        foreach ($items as $item) {
            if (! file_exists(app_path('SupportedApps/'.Item::nameFromClass($item->class)))) {
                $app = Application::where('class', $item->class)->first();
                if ($app) {
                    Application::getApp($app->appid);
                }
            }
        }
    }
}
