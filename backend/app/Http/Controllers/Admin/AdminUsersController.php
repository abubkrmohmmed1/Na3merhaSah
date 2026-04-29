<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class AdminUsersController extends Controller
{
    /**
     * Display a list of all users for the admin.
     */
    public function index(): View
    {
        // Fetch real users from the database
        $users = \App\Models\User::orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function entities(): View
    {
        // Mocking some entities for now
        $entities = [
            ['id' => 1, 'name' => 'هيئة المياه', 'reports_count' => 150, 'status' => 'نشط'],
            ['id' => 2, 'name' => 'هيئة الطرق والجسور', 'reports_count' => 85, 'status' => 'نشط'],
            ['id' => 3, 'name' => 'وزارة الصحة', 'reports_count' => 40, 'status' => 'نشط'],
            ['id' => 4, 'name' => 'الشركة السودانية للكهرباء', 'reports_count' => 210, 'status' => 'نشط'],
        ];

        return view('admin.entities.index', compact('entities'));
    }
}
