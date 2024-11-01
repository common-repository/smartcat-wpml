<?php

use Smartcat\Includes\Services\Plugin\Router;
use Smartcat\Includes\Controllers\PostsController;
use Smartcat\Includes\Controllers\CategoriesController;
use Smartcat\Includes\Controllers\WpmlController;
use Smartcat\Includes\Controllers\PluginController;
use Smartcat\Includes\Controllers\MetadataController;
use Smartcat\Includes\Controllers\CallbackController;
use Smartcat\Includes\Controllers\StringController;

$router = new Router();

// Posts
$router->route('POST', 'posts/pull', PostsController::class, 'pull'); // FIXME: хочу быть GET
$router->route('POST', 'posts/push', PostsController::class, 'push');
$router->route('POST', 'posts/import', PostsController::class, 'import');
$router->route('POST', 'posts/all', PostsController::class, 'all');
$router->route('POST', 'posts/verify', PostsController::class, 'originalPostsForVerify');
$router->route('GET', 'posts/locale-trid', PostsController::class, 'withLocaleTrid');
$router->route('GET', 'posts/types', PostsController::class, 'translatableTypes');

// Categories
$router->route('POST', 'categories/pull', CategoriesController::class, 'pull');
$router->route('POST', 'categories/push', CategoriesController::class, 'push');

// Metadata
$router->route('GET', 'metadata/list', MetadataController::class, 'list');

// WPML
$router->route('POST', 'wpml/locales', WpmlController::class, 'locales');

// Strings

$router->route('GET', 'domains', StringController::class, 'domains');
$router->route('GET', 'strings', StringController::class, 'export');

/**
 * @deprecated
 */
$router->route('GET', 'wpml/strings', WpmlController::class, 'strings');
$router->route('POST', 'wpml/strings', WpmlController::class, 'pushStrings');

//Plugin
$router->route('POST', 'plugin/status', PluginController::class, 'status');
$router->route('POST', 'plugin/shell', PluginController::class, 'shell');
$router->route('GET', 'plugin/logs', PluginController::class, 'logs');
$router->route('POST', 'plugin/run-migrations', PluginController::class, 'runMigrations');

// Callbacks
$router->route('POST', 'callback/credentials', CallbackController::class, 'credentials');

