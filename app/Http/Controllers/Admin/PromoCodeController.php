<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PromoCodeController extends Controller
{
    public function index(Request $request)
    {
        $promoCodes = PromoCode::with(['user', 'vendor'])
            ->when($request->search, function($query, $search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            })
            ->when($request->type, function($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->is_active !== null, function($query) use ($request) {
                $query->where('is_active', $request->is_active);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => PromoCode::count(),
            'active' => PromoCode::where('is_active', true)->count(),
            'expired' => PromoCode::where('end_date', '<', now())->count(),
            'total_used' => PromoCode::sum('used_count'),
        ];

        return view('admin.promo-codes.index', compact('promoCodes', 'stats'));
    }

    public function create()
    {
        return view('admin.promo-codes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'nullable|string|max:50|unique:promo_codes',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:flat,percentage',
            'value' => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_type' => 'required|in:single,multi',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'is_first_order_only' => 'boolean',
        ]);

        $data = $request->all();

        if (empty($data['code'])) {
            $data['code'] = strtoupper(Str::random(8));
            while (PromoCode::where('code', $data['code'])->exists()) {
                $data['code'] = strtoupper(Str::random(8));
            }
        }

        PromoCode::create($data);

        return redirect()->route('admin.promo-codes.index')->with('success', 'Promo code created successfully.');
    }

    public function edit(PromoCode $promoCode)
    {
        return view('admin.promo-codes.edit', compact('promoCode'));
    }

    public function update(Request $request, PromoCode $promoCode)
    {
        $request->validate([
            'code' => 'string|max:50|unique:promo_codes,code,' . $promoCode->id,
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'in:flat,percentage',
            'value' => 'numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_type' => 'in:single,multi',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
            'is_active' => 'boolean',
            'is_first_order_only' => 'boolean',
        ]);

        $promoCode->update($request->all());

        return redirect()->route('admin.promo-codes.index')->with('success', 'Promo code updated successfully.');
    }

    public function destroy(PromoCode $promoCode)
    {
        if ($promoCode->usages()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete promo code that has been used.');
        }

        $promoCode->delete();
        return redirect()->route('admin.promo-codes.index')->with('success', 'Promo code deleted successfully.');
    }
}