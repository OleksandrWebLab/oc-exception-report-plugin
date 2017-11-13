<?php namespace PopcornPHP\ExceptionReport\Models;

use October\Rain\Database\Model;

class Settings extends Model
{
    public $implement = [
        'System.Behaviors.SettingsModel',
    ];

    public $settingsCode = 'exception_report_settings';
    public $settingsFields = 'fields.yaml';

    public function initSettingsData()
    {
        $this->disabled_in_debug = false;
        $this->disabled_for_admins = false;
        $this->disabled_exceptions = [
            [
                'class_name' => 'October\Rain\Exception\AjaxException',
            ], [
                'class_name' => 'October\Rain\Exception\ValidationException',
            ],
        ];
    }
}