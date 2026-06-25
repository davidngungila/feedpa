<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\Beneficiary;
use Illuminate\Http\Request;

class BeneficiaryController extends Controller
{
    public function index()
    {
        $beneficiaries = Beneficiary::where('user_id', auth()->id())->latest()->paginate(10);
        return view('beneficiaries.index', compact('beneficiaries'));
    }

    public function create()
    {
        $banks = [];
        try {
            $banksResponse = app('App\Services\ClickPesaAPIService')->getBanksList();
            if (isset($banksResponse['data']) && is_array($banksResponse['data'])) {
                $banks = $banksResponse['data'];
            } elseif (isset($banksResponse['banks']) && is_array($banksResponse['banks'])) {
                $banks = $banksResponse['banks'];
            } elseif (is_array($banksResponse)) {
                $banks = $banksResponse;
            }
        } catch (\Exception $e) {
            //
        }
        
        return view('beneficiaries.create', compact('banks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:mobile,bank',
            'phone' => 'required_if:type,mobile|nullable|string',
            'bank_name' => 'required_if:type,bank|nullable|string',
            'account_number' => 'required_if:type,bank|nullable|string',
            'bic' => 'required_if:type,bank|nullable|string',
            'transfer_type' => 'nullable|in:ACH,RTGS',
            'email' => 'nullable|email|max:255',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_active'] = true;

        $beneficiary = Beneficiary::create($validated);
        
        Audit::log('create_beneficiary', "Created beneficiary: {$beneficiary->name} (Type: {$beneficiary->type})");

        return redirect()->route('beneficiaries.index')->with('success', 'Beneficiary created successfully');
    }

    public function show(Beneficiary $beneficiary)
    {
        if ($beneficiary->user_id !== auth()->id()) {
            abort(403);
        }
        
        Audit::log('view_beneficiary', "Viewed beneficiary: {$beneficiary->name}");
        
        return view('beneficiaries.show', compact('beneficiary'));
    }

    public function edit(Beneficiary $beneficiary)
    {
        if ($beneficiary->user_id !== auth()->id()) {
            abort(403);
        }

        $banks = [];
        try {
            $banksResponse = app('App\Services\ClickPesaAPIService')->getBanksList();
            if (isset($banksResponse['data']) && is_array($banksResponse['data'])) {
                $banks = $banksResponse['data'];
            } elseif (isset($banksResponse['banks']) && is_array($banksResponse['banks'])) {
                $banks = $banksResponse['banks'];
            } elseif (is_array($banksResponse)) {
                $banks = $banksResponse;
            }
        } catch (\Exception $e) {
            //
        }
        
        return view('beneficiaries.edit', compact('beneficiary', 'banks'));
    }

    public function update(Request $request, Beneficiary $beneficiary)
    {
        if ($beneficiary->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:mobile,bank',
            'phone' => 'required_if:type,mobile|nullable|string',
            'bank_name' => 'required_if:type,bank|nullable|string',
            'account_number' => 'required_if:type,bank|nullable|string',
            'bic' => 'required_if:type,bank|nullable|string',
            'transfer_type' => 'nullable|in:ACH,RTGS',
            'email' => 'nullable|email|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $beneficiary->update($validated);
        
        Audit::log('update_beneficiary', "Updated beneficiary: {$beneficiary->name}");

        return redirect()->route('beneficiaries.index')->with('success', 'Beneficiary updated successfully');
    }

    public function destroy(Beneficiary $beneficiary)
    {
        if ($beneficiary->user_id !== auth()->id()) {
            abort(403);
        }

        $beneficiaryName = $beneficiary->name;
        $beneficiary->delete();
        
        Audit::log('delete_beneficiary', "Deleted beneficiary: {$beneficiaryName}");

        return redirect()->route('beneficiaries.index')->with('success', 'Beneficiary deleted successfully');
    }
}
