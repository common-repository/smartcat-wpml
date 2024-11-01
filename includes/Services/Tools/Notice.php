<?php

namespace Smartcat\Includes\Services\Tools;

class Notice
{
    public static function error(string $message, string $classes = '')
    {
        self::showNotice('error', $message, $classes);
    }

    public static function warn(string $message, string $classes = '')
    {
        self::showNotice('warning', $message, $classes);
    }

    public static function native(string $message, string $classes = '')
    {
        self::showNotice('native', $message, $classes);
    }

    public static function notice(string $status, string $message)
    {
        ?>
        <div class="notice notice-<?php echo esc_html__($status) ?> is-dismissible">
            <p>
                <?php echo esc_html__($message) ?>
            </p>
        </div>
        <?php
    }

    private static function showNotice(string $type, string $message, string $classes = '')
    {
        ?>
        <div class="notice notice-<?php echo $type ?> <?php echo $classes ?> is-dismissible">
            <p>
                <b>Smartcat plugin:</b>
                <?php _e($message, 'smartcat-wpml'); ?>
            </p>
        </div>
        <?php
    }
}