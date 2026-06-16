<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::query();

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('symbol', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $assets = $query->orderBy('created_at', 'desc')->paginate(100);

        return view('admin.assets', compact('assets'));
    }

    /**
     * Store or Update Asset via FMP API
     */
    public function store(Request $request)
    {
        $request->validate([
            'symbol' => 'required|string',
            'type' => 'required|in:crypto,stocks,etf'
        ]);

        $symbol = strtoupper($request->symbol);
        $apiKey = env('ASSET_KEY');
        $apiUrl = "https://financialmodelingprep.com/api/v3/quote/{$symbol}?apikey={$apiKey}";

        $response = Http::get($apiUrl);
        $data = $response->json();

        if (empty($data)) {
            return back()->with('error', 'Asset not found in FMP database.');
        }

        $assetData = $data[0];

        Asset::updateOrCreate(
            ['symbol' => $assetData['symbol']],
            [
                'id' => (string) Str::uuid(),
                'name' => $assetData['name'],
                'img' => "https://images.financialmodelingprep.com/symbol/{$assetData['symbol']}.png",
                'price' => $assetData['price'],
                'changes_percentage' => $assetData['changesPercentage'],
                'change' => $assetData['change'],
                'day_low' => $assetData['dayLow'],
                'day_high' => $assetData['dayHigh'],
                'year_low' => $assetData['yearLow'],
                'year_high' => $assetData['yearHigh'],
                'market_cap' => $assetData['marketCap'] ?? 0,
                'price_avg_50' => $assetData['priceAvg50'],
                'price_avg_200' => $assetData['priceAvg200'],
                'exchange' => $assetData['exchange'] ?? 'N/A',
                'volume' => $assetData['volume'] ?? 0,
                'avg_volume' => $assetData['avgVolume'] ?? 0,
                'open' => $assetData['open'] ?? 0,
                'previous_close' => $assetData['previousClose'] ?? 0,
                'eps' => $assetData['eps'] ?? 0,
                'pe' => $assetData['pe'] ?? 0,
                'type' => $request->type,
                'status' => 'active',
                'tradeable' => 1,
            ]
        );

        return back()->with('success', 'Asset synced successfully.');
    }

    public function destroy($id)
    {
        $asset = Asset::findOrFail($id);
        $asset->delete();
        return back()->with('success', 'Asset deleted successfully.');
    }
}
