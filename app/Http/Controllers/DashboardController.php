<?php

namespace App\Http\Controllers;

use App\Models\MaterialContent;
use App\Models\Modul;
use App\Models\SubModul;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getSummaryDashboard()
    {
        $summary = [
            'modules' => Modul::count(),
            'sub_modules' => SubModul::count(),
            'material_contents' => MaterialContent::count(),
        ];

        return $this->success($summary, 'Dashboard summary retrieved successfully');
    }
}
