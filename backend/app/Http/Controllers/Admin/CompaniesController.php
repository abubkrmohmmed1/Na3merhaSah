<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domains\Billing\Models\Company;
use App\Domains\Billing\Models\ApiKey;
use Illuminate\Http\Request;

class CompaniesController extends Controller
{
    public function index()
    {
        $companies = Company::with('apiKeys')->orderBy('created_at', 'desc')->get();
        return view('admin.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('admin.companies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:companies',
            'phone' => 'nullable|string|max:50',
            'webhook_url' => 'nullable|url',
            'sudapass_id' => 'nullable|string|max:100',
            'password' => 'required|string|min:8',
            'plan' => 'required|in:free,pro,enterprise',
        ]);

        $data['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        $company = Company::create($data);

        return redirect()->route('admin.companies.index')
            ->with('success', "تم إضافة الشركة {$company->name}");
    }

    public function show(Company $company)
    {
        $company->load('apiKeys');
        return view('admin.companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:companies,email,' . $company->id,
            'phone' => 'nullable|string|max:50',
            'webhook_url' => 'nullable|url',
            'sudapass_id' => 'nullable|string|max:100',
            'plan' => 'required|in:free,pro,enterprise',
            'status' => 'required|in:active,suspended',
        ]);

        $company->update($data);

        return redirect()->route('admin.companies.index')
            ->with('success', "تم تحديث {$company->name}");
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return redirect()->route('admin.companies.index')
            ->with('success', 'تم حذف الشركة');
    }

    public function generateKey(Request $request, Company $company)
    {
        $request->validate(['name' => 'nullable|string|max:255']);

        $apiKey = ApiKey::create([
            'company_id' => $company->id,
            'key' => ApiKey::generate(),
            'name' => $request->name ?? 'API Key',
        ]);

        return redirect()->route('admin.companies.show', $company)
            ->with('success', "تم توليد مفتاح API جديد: {$apiKey->key}");
    }

    public function revokeKey(Company $company, ApiKey $apiKey)
    {
        $apiKey->update(['revoked_at' => now()]);
        return redirect()->route('admin.companies.show', $company)
            ->with('success', 'تم إلغاء المفتاح');
    }
}
