<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePromotionRequest;
use App\Http\Requests\UpdatePromotionRequest;
use App\Models\Promotion;
use Illuminate\Support\Facades\Gate;

class PromotionController extends Controller
{
    public function index()
    {
        return response()->json([
            'promotions' => Promotion::latest()->get(),
        ]);
    }

    public function store(StorePromotionRequest $request)
    {
        $promotion = Promotion::create($request->validated());

        return response()->json([
            'message'   => 'Promotion created.',
            'promotion' => $promotion,
        ], 201);
    }

    public function show(Promotion $promotion)
    {
        return response()->json(['promotion' => $promotion]);
    }

    public function update(UpdatePromotionRequest $request, Promotion $promotion)
    {
        $promotion->update($request->validated());

        return response()->json([
            'message'   => 'Promotion updated.',
            'promotion' => $promotion,
        ]);
    }

    public function destroy(Promotion $promotion)
    {
        Gate::authorize('admin');
        $promotion->delete();

        return response()->json(['message' => 'Promotion deleted.']);
    }
}
