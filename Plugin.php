<?php namespace PopcornPHP\ExceptionReport;

use Backend\Facades\BackendAuth;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use PopcornPHP\ExceptionReport\Classes\Telegram;
use PopcornPHP\ExceptionReport\Models\Settings as ExceptionReportSettings;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'ExceptionReport',
            'description' => 'Report about exceptions to Telegram',
            'author'      => 'Alexander Shapoval',
            'icon'        => 'icon-cog',
            'homepage'    => 'https://popcornphp.github.io',
        ];
    }

    public function boot()
    {
        App::error(function (Exception $exception) {
            $settings = ExceptionReportSettings::instance();

            if (config('app.debug') == true && $settings->disabled_in_debug == true) {
                return;
            }

            if (BackendAuth::check() == true && $settings->disabled_for_admins == true) {
                return;
            }

            $exception_class_name = get_class($exception);

            $disabled_classes = $settings->disabled_exceptions;

            if (!collect($disabled_classes)->flatten()->contains($exception_class_name)) {
                $info = 'You have new exception on ' . Request::url() . "\n\n";
                $class = "<b>Exception class:</b> " . $exception_class_name . "\n\n";
                $code = "<b>Code:</b> " . $exception->getCode() . "\n\n";
                $file = "<b>File:</b> " . $exception->getFile() . "\n\n";
                $line = "<b>Line:</b> " . $exception->getLine() . "\n\n";
                $message = "<b>Error:</b> " . $exception->getMessage();

                $gateway = new Telegram();
                $gateway->sendMessage([
                    'chat_id'    => $settings->telegram_chat_id,
                    'text'       => $info . $class . $code . $file . $line . $message,
                    'parse_mode' => 'HTML',
                ]);
            }
        });
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Exception report',
                'description' => 'Report about exceptions to Telegram',
                'category'    => SettingsManager::CATEGORY_NOTIFICATIONS,
                'icon'        => 'icon-cog',
                'class'       => 'PopcornPHP\ExceptionReport\Models\Settings',
                'order'       => 500,
            ],
        ];
    }
}