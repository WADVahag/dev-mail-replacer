<?php

namespace Wadvahag\DevMailReplacer;

use Illuminate\Support\ServiceProvider;


use Swift_Message;
use Event;
use Log;

use Illuminate\Mail\Events\MessageSending;

class DevMailReplacerServiceProvider extends ServiceProvider
{

    private function whetherToReplace(): bool {
        return (bool) !app()->environment("production") && config("dev-mail-replacer.dev_destination");
    }

    private function replaceDestination(Swift_Message $message): void {

        Log::debug(config("dev-mail-replacer.dev_destination") ?? "Test");

        if($this->whetherToReplace()){
            $message->setTo(config("dev-mail-replacer.dev_destination"));
        }else{
            Log::debug('Production we are on Production now');
        }
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

    /**
     * Merge configs from local (package) configs, 
     * this way package should work without publishing => with default configs.
     * 
     * When merging configs, first argument is array (from local(package) config file),
     *  second is config key => filename without extension
     */

        $this->mergeConfigFrom(
            __DIR__ . '/../config/dev-mail-replacer.php', 'dev-mail-replacer'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
             __DIR__ . '/../config/dev-mail-replacer.php' => config_path('dev-mail-replacer.php')
        ]);

        Event::listen("Illuminate\Mail\Events\MessageSending", function(MessageSending $event){
            $this->replaceDestination($event->message);
        });
    }
}
