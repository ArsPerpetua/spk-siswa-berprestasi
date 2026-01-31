<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');
$routes->get('hitung', 'Hitung::index');
$routes->get('hitung/cetakPDF', 'Hitung::cetakPDF');
$routes->get('/', 'Auth::index');
$routes->post('auth/login', 'Auth::process');
$routes->get('kriteria', 'Kriteria::index');
$routes->get('kriteria/create', 'Kriteria::create');
$routes->post('kriteria/store', 'Kriteria::store');
$routes->get('kriteria/edit/(:num)', 'Kriteria::edit/$1');
$routes->post('kriteria/update/(:num)', 'Kriteria::update/$1');
$routes->get('kriteria/delete/(:num)', 'Kriteria::delete/$1');
$routes->get('alternatif', 'Alternatif::index');
$routes->get('alternatif/create', 'Alternatif::create');
$routes->post('alternatif/store', 'Alternatif::store');
$routes->get('alternatif/edit/(:num)', 'Alternatif::edit/$1');
$routes->post('alternatif/update/(:num)', 'Alternatif::update/$1');
$routes->get('alternatif/delete/(:num)', 'Alternatif::delete/$1');
$routes->post('alternatif/import', 'Alternatif::import');
$routes->get('alternatif/downloadTemplate', 'Alternatif::downloadTemplate');
$routes->get('penilaian', 'Penilaian::index');
$routes->get('penilaian/form/(:num)', 'Penilaian::form/$1');
$routes->post('penilaian/save', 'Penilaian::save');
$routes->post('penilaian/import', 'Penilaian::import');
$routes->get('penilaian/downloadTemplate', 'Penilaian::downloadTemplate');
$routes->get('auth/logout', 'Auth::logout');
$routes->get('buat-admin', 'Auth::buat_admin'); // Rute sementara
// CRUD Users (Admin)
$routes->get('users', 'Users::index');
$routes->get('users/create', 'Users::create');
$routes->post('users/store', 'Users::store');
$routes->get('users/edit/(:num)', 'Users::edit/$1');
$routes->post('users/update/(:num)', 'Users::update/$1');
$routes->get('users/delete/(:num)', 'Users::delete/$1');

// Profile (User Login)
$routes->get('profile', 'Profile::index');
$routes->post('profile/update', 'Profile::update');

$routes->get('dashboard', 'Dashboard::index');
$routes->get('ahp', 'Ahp::index');
$routes->post('ahp/proses', 'Ahp::proses');

// Tambahkan ini di app/Config/Routes.php
$routes->get('pengaturan', 'Pengaturan::index');
$routes->post('pengaturan/resetData', 'Pengaturan::resetData');
$routes->get('pengaturan/backup', 'Pengaturan::backup');
$routes->post('ahp/simpanPreset', 'Ahp::simpanPreset');
$routes->get('ahp/hapusPreset/(:num)', 'Ahp::hapusPreset/$1');
$routes->get('panduan', 'Panduan::index');