<?php

namespace App\Controllers;

class Panduan extends BaseController
{
    public function index()
    {
        return view('panduan/index', ['title' => 'Panduan Penggunaan']);
    }
}