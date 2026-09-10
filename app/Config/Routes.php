<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ==========================================
// 1. PUBLIC ROUTES (No Login Required)
// ==========================================
$routes->match(['get', 'post'], 'login', 'Auth::login');
$routes->get('logout', 'Auth::logout');

// Standalone Single Camera Links (Permission checked inside controller: public vs protected)
$routes->get('cam/(:segment)', 'Nvr::cam/$1');
$routes->get('nvr/cam/(:segment)', 'Nvr::cam/$1');
$routes->get('share/(:segment)', 'Nvr::cam/$1');

// ==========================================
// 2. PROTECTED ROUTES (Requires Root Auth)
// ==========================================
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    // Dashboard
    $routes->get('/', 'Nvr::index');
    $routes->get('nvr', 'Nvr::index');

    // API - System & Camera Read
    $routes->get('nvr/get_recordings', 'Nvr::get_recordings');
    $routes->get('nvr/get_settings', 'Nvr::get_settings');
    $routes->get('nvr/get_live_status', 'Nvr::get_live_status');
    $routes->get('nvr/get_ptz_support', 'Nvr::get_ptz_support');
    $routes->get('nvr/get_timeline_recordings', 'Nvr::get_timeline_recordings');
    $routes->get('nvr/get_disk_status', 'Nvr::get_disk_status');
    $routes->get('nvr/get_cleanup_config', 'Nvr::get_cleanup_config');

    // API - Configurations & Write
    $routes->post('nvr/save_setting', 'Nvr::save_setting');
    $routes->post('nvr/reset_settings', 'Nvr::reset_settings');
    $routes->post('nvr/save_cleanup_config', 'Nvr::save_cleanup_config');
    $routes->match(['get', 'post'], 'nvr/add_camera', 'Nvr::add_camera');

    // PTZ & AI Controls
    $routes->get('nvr/ptz', 'Nvr::ptz');
    $routes->get('nvr/ai_autocenter', 'Nvr::ai_autocenter');
    $routes->get('nvr/save_home_position', 'Nvr::save_home_position');
    $routes->get('nvr/go_home_position', 'Nvr::go_home_position');

    // Anyka Local MicroSD Video Streaming
    $routes->get('nvr/get_anyka_available_dates', 'Nvr::get_anyka_available_dates');
    $routes->get('nvr/get_anyka_chunks', 'Nvr::get_anyka_chunks');
    $routes->get('nvr/anyka_stream', 'Nvr::anyka_stream');

    // Auth Password Management
    $routes->post('auth/change_password', 'Auth::change_password');
});
