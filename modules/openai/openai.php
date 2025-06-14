<?php

use app\services\ai\AiProviderRegistry;
use Perfexcrm\Openai\OpenAiProvider;

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: OpenAi Integration
Description: Default module for Open AI integration
Version: 1.0.0
Requires at least: 3.2.*
*/

require __DIR__ . '/vendor/autoload.php';

hooks()->add_action('admin_init', 'openai_module_init');
hooks()->add_action('admin_init', 'openai_module_activation_hook');

hooks()->add_filter('module_openai_action_links', 'module_openai_action_links');

/**
 * Add additional settings for this module in the module list area
 *
 * @param array $actions current actions
 *
 * @return array
 */
function module_openai_action_links($actions)
{
    if (get_instance()->app_modules->is_active('openai')) {
        $actions[] = '<a href="' . admin_url('settings?group=openai') . '">' . _l('settings') . '</a>';
    }

    $actions[] = '<a href="' . admin_url('settings?group=ai') . '">' . _l('settings_group_ai') . '</a>';

    return $actions;
}

function openai_module_init(): void
{
    AiProviderRegistry::registerProvider('openai', new OpenAiProvider());

    $CI = &get_instance();
    $CI->app->add_settings_section_child(
        'ai',
        'openai',
        [
            'name'     => _l('openai'),
            'view'     => 'openai/settings',
            'position' => 15,
            'icon'     => 'fa-solid fa-robot',
        ]
    );
}

function openai_module_activation_hook(): void
{
    add_option('openai_api_key');
    add_option('openai_model');
}
