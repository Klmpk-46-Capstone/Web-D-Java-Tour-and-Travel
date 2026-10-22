<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DesignSystemController extends Controller
{
    /**
     * Pratinjau internal design system. Hanya tersedia di lingkungan lokal/testing.
     */
    public function index(): View
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        return view('pages.design-system.index');
    }
}
