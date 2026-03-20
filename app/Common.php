<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (!function_exists('kriteria_label')) {
    /**
     * Normalize displayed criterion labels across the app.
     */
    function kriteria_label(?string $nama): string
    {
        $label = trim((string) $nama);
        if ($label === '') {
            return '';
        }

        return preg_replace('/ketidakhadiran/i', 'Absensi', $label) ?? $label;
    }
}
