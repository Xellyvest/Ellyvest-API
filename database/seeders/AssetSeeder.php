<?php

namespace Database\Seeders;

use App\Models\Asset;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AssetSeeder extends Seeder
{
    public function run(): void
    {

        $assets = [
            [
                'symbol' => 'OPEN',
                'name' => 'Opendoor Technologies Inc.',
                'exchange' => 'NASDAQ',
                'img' => 'https://financialmodelingprep.com/image-stock/OPEN.png',
                'price' => 0.00, // set default if not fetched yet
                'type' => 'stocks',

                'changes_percentage' => 0,
                'change' => 0,
                'day_low' => 0,
                'day_high' => 0,
                'year_low' => 0,
                'year_high' => 0,
                'market_cap' => 0,
                'price_avg_50' => 0,
                'price_avg_200' => 0,
                'volume' => $asset['volume'] ?? 0,
                'avg_volume' => $asset['avgVolume'] ?? 0,
                'open' => $asset['open'] ?? 0,
                'previous_close' => $asset['previousClose'] ?? 0,
                'eps' => $asset['eps'] ?? 0,
                'pe' => $asset['pe'] ?? 0,
                'status' => 'active',
                'tradeable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'symbol' => 'SQY',
                'name' => 'YieldMax SQ Option Income Strategy ETF',
                'exchange' => 'AMEX',
                'img' => 'https://financialmodelingprep.com/image-stock/SQY.png',
                'price' => 0.00,
                'type' => 'etf',

                'changes_percentage' => 0,
                'change' => 0,
                'day_low' => 0,
                'day_high' => 0,
                'year_low' => 0,
                'year_high' => 0,
                'market_cap' => 0,
                'price_avg_50' => 0,
                'price_avg_200' => 0,
                'volume' => $asset['volume'] ?? 0,
                'avg_volume' => $asset['avgVolume'] ?? 0,
                'open' => $asset['open'] ?? 0,
                'previous_close' => $asset['previousClose'] ?? 0,
                'eps' => $asset['eps'] ?? 0,
                'pe' => $asset['pe'] ?? 0,
                'status' => 'active',
                'tradeable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'symbol' => 'AIYY',
                'name' => 'YieldMax AI Option Income Strategy ETF',
                'exchange' => 'NYSE',
                'img' => 'https://financialmodelingprep.com/image-stock/AIYY.png',
                'price' => 0.00,
                'type' => 'etf',

                'changes_percentage' => 0,
                'change' => 0,
                'day_low' => 0,
                'day_high' => 0,
                'year_low' => 0,
                'year_high' => 0,
                'market_cap' => 0,
                'price_avg_50' => 0,
                'price_avg_200' => 0,
                'volume' => $asset['volume'] ?? 0,
                'avg_volume' => $asset['avgVolume'] ?? 0,
                'open' => $asset['open'] ?? 0,
                'previous_close' => $asset['previousClose'] ?? 0,
                'eps' => $asset['eps'] ?? 0,
                'pe' => $asset['pe'] ?? 0,
                'status' => 'active',
                'tradeable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'symbol' => 'XYZY',
                'name' => 'YieldMax XYZ Option Income Strategy ETF',
                'exchange' => 'NYSE',
                'img' => 'https://financialmodelingprep.com/image-stock/XYZY.png',
                'price' => 0.00,
                'type' => 'etf',

                'changes_percentage' => 0,
                'change' => 0,
                'day_low' => 0,
                'day_high' => 0,
                'year_low' => 0,
                'year_high' => 0,
                'market_cap' => 0,
                'price_avg_50' => 0,
                'price_avg_200' => 0,
                'volume' => $asset['volume'] ?? 0,
                'avg_volume' => $asset['avgVolume'] ?? 0,
                'open' => $asset['open'] ?? 0,
                'previous_close' => $asset['previousClose'] ?? 0,
                'eps' => $asset['eps'] ?? 0,
                'pe' => $asset['pe'] ?? 0,
                'status' => 'active',
                'tradeable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($assets as $data) {
            Asset::updateOrCreate(
                ['symbol' => $data['symbol']],
                $data
            );

            echo "✅ Added: {$data['symbol']}\n";
        }
    }

    /**
     * Run the database seeds.
     */
    // public function run(): void
    // {
    //     // ::::: STOCK Data
    //     // $symbols = [
    //     //     // Technology (50)
    //     //     'AAPL', 'MSFT', 'GOOGL', 'AMZN', 'NVDA', 'META', 'TSLA', 'AVGO', 'ASML', 'ADBE',
    //     //     'CSCO', 'ORCL', 'CRM', 'INTC', 'AMD', 'QCOM', 'TXN', 'IBM', 'NOW', 'SNOW',
    //     //     'PANW', 'UBER', 'NET', 'SHOP', 'CRWD', 'ZS', 'MDB', 'DDOG', 'TEAM', 'FTNT',
    //     //     'WDAY', 'ADSK', 'INTU', 'PYPL', 'SQ', 'DOCU', 'ZM', 'ROKU', 'SPLK', 'OKTA',
    //     //     'CDNS', 'ANSS', 'KLAC', 'LRCX', 'AMAT', 'MU', 'MRVL', 'ADI', 'NXPI', 'SWKS', 'PLTR', 'SOFI',
        
    //     //     // Financials (30)
    //     //     'JPM', 'BAC', 'WFC', 'C', 'GS', 'MS', 'SCHW', 'BLK', 'AXP', 'PYPL',
    //     //     'V', 'MA', 'DFS', 'COF', 'USB', 'TFC', 'PNC', 'BK', 'STT', 'ICE',
    //     //     'CME', 'SPGI', 'MCO', 'FIS', 'FISV', 'GPN', 'AJG', 'MMC', 'TW', 'NDAQ',
        
    //     //     // Healthcare (30)
    //     //     'UNH', 'JNJ', 'PFE', 'ABBV', 'MRK', 'LLY', 'TMO', 'DHR', 'AMGN', 'GILD',
    //     //     'BMY', 'VRTX', 'REGN', 'ISRG', 'MDT', 'SYK', 'BDX', 'ZTS', 'CI', 'HUM',
    //     //     'ELV', 'CVS', 'ANTM', 'IQV', 'EW', 'IDXX', 'DXCM', 'BSX', 'HCA', 'MRNA',
        
    //     //     // Consumer Discretionary (25)
    //     //     'HD', 'LOW', 'NKE', 'MCD', 'SBUX', 'TGT', 'COST', 'BKNG', 'MAR', 'HLT',
    //     //     'LVS', 'YUM', 'CMG', 'DPZ', 'NFLX', 'DIS', 'RCL', 'CCL', 'EXPE', 'ABNB',
    //     //     'DHI', 'LEN', 'NVR', 'PHM', 'TSCO',
        
    //     //     // Industrials (25)
    //     //     'HON', 'GE', 'CAT', 'BA', 'RTX', 'LMT', 'GD', 'NOC', 'DE', 'UPS',
    //     //     'FDX', 'CSX', 'UNP', 'NSC', 'WM', 'RSG', 'WAB', 'EMR', 'ETN', 'ITW',
    //     //     'ROK', 'SWK', 'PH', 'DOV', 'AME',
        
    //     //     // Consumer Staples (20)
    //     //     'PG', 'KO', 'PEP', 'WMT', 'COST', 'PM', 'MO', 'MDLZ', 'KHC', 'CL',
    //     //     'EL', 'GIS', 'KMB', 'SYY', 'HSY', 'ADM', 'STZ', 'BF.B', 'CPB', 'MNST',
        
    //     //     // Energy (15)
    //     //     'XOM', 'CVX', 'COP', 'EOG', 'SLB', 'OXY', 'PSX', 'MPC', 'VLO', 'PXD',
    //     //     'HES', 'DVN', 'FANG', 'HAL', 'BKR',
        
    //     //     // Utilities (10)
    //     //     'NEE', 'DUK', 'SO', 'D', 'AEP', 'EXC', 'SRE', 'PEG', 'ED', 'EIX',
        
    //     //     // Real Estate (10)
    //     //     'AMT', 'PLD', 'CCI', 'EQIX', 'PSA', 'SBAC', 'DLR', 'WELL', 'AVB', 'O',
        
    //     //     // Materials (10)
    //     //     'LIN', 'APD', 'SHW', 'FCX', 'NEM', 'DOW', 'ECL', 'PPG', 'VMC', 'NUE',
        
    //     //     // Communication Services (15)
    //     //     'GOOG', 'GOOGL', 'META', 'DIS', 'NFLX', 'T', 'VZ', 'TMUS', 'CHTR', 'CMCSA',
    //     //     'EA', 'TTWO', 'ATVI', 'ROKU', 'ZG',
    //     // ];

    //     // ::::: CRYPTO Data
    //     // $symbols = [
    //     //     // Top 50 by market cap
    //     //     'BTCUSD', 'ETHUSD', 'USDTUSD', 'BNBUSD', 'SOLUSD', 'XRPUSD', 'USDCUSD', 'ADAUSD', 'DOGEUSD', 'AVAXUSD',
    //     //     'SHIBUSD', 'DOTUSD', 'TRXUSD', 'LINKUSD', 'MATICUSD', 'WBTCUSD', 'TONUSD', 'ICPUSD', 'DAIUSD', 'LTCUSD',
    //     //     'BCHUSD', 'UNIUSD', 'ATOMUSD', 'XLMUSD', 'ETCUSD', 'INJUSD', 'XMRUSD', 'FILUSD', 'IMXUSD', 'APTUSD',
    //     //     'RNDRUSD', 'STXUSD', 'HBARUSD', 'CROUSD', 'NEARUSD', 'VETUSD', 'OPUSD', 'MKRUSD', 'GRTUSD', 'ARBUSD',
    //     //     'THETAUSD', 'FDUSD', 'PEPEUSD', 'KASUSD', 'RUNEUSD', 'FRAXUSD', 'AAVEUSD', 'ALGOUSD', 'FLOWUSD', 'EGLDUSD',
            
    //     //     // Next 50 (51-100)
    //     //     'QNTUSD', 'BSVUSD', 'XTZUSD', 'EOSUSD', 'MINAUSD', 'AXSUSD', 'SANDUSD', 'MANAUSD', 'NEOUSD', 'KCSUSD',
    //     //     'BTTUSD', 'CHZUSD', 'USDPUSD', 'SNXUSD', 'FTMUSD', 'BGBUSD', 'CRVUSD', 'GALAUSD', 'ROSEUSD', 'ZECUSD',
    //     //     'XECUSD', 'KAVAUSD', 'DASHUSD', 'PAXGUSD', 'IOTAUSD', 'WEMIXUSD', 'COMPUSD', 'HNTUSD', 'CAKEUSD', 'GMXUSD',
    //     //     'CFXUSD', 'TUSDUSD', 'BONKUSD', 'GTUSD', '1INCHUSD', 'LDOUSD', 'XDCUSD', 'FXSUSD', 'SUIUSD', 'APEUSD',
    //     //     'ENSUSD', 'AGIXUSD', 'RPLUSD', 'OCEANUSD', 'NEXOUSD', 'ZILUSD', 'KLAYUSD', 'GNOUSD', 'YFIUSD', 'WOOUSD',
            
    //     //     // Next 50 (101-150)
    //     //     'CELOUSD', 'DYDXUSD', 'TFUELUSD', 'JSTUSD', 'IOTXUSD', 'ANKRUSD', 'ASTRUSD', 'GUSDUSD', 'SKLUSD', 'CSPRUSD',
    //     //     'BATUSD', 'GLMUSD', 'LSKUSD', 'AUDIOUSD', 'RVNUSD', 'SUSHIUSD', 'ICXUSD', 'STORJUSD', 'ONTUSD', 'ZRXUSD',
    //     //     'SSVUSD', 'UMAUSD', 'WAVESUSD', 'CKBUSD', 'SCUSD', 'FETUSD', 'LRCUSD', 'TWTUSD', 'DCRUSD', 'API3USD',
    //     //     'BALUSD', 'GLMRUSD', 'SXPUSD', 'NMRUSD', 'COTIUSD', 'CTSIUSD', 'BANDUSD', 'OXTUSD', 'HOTUSD', 'QTUMUSD',
    //     //     'POWRUSD', 'DGBUSD', 'KSMUSD', 'XEMUSD', 'FLRUSD', 'YGGUSD', 'JASMYUSD', 'ACHUSD', 'RLCUSD', 'MDTUSD',
            
    //     //     // Next 50 (151-200)
    //     //     'STRAXUSD', 'SYSUSD', 'CVCUSD', 'REQUSD', 'POLYXUSD', 'STEEMUSD', 'WAXPUSD', 'ARUSD', 'DENTUSD', 'CELRUSD',
    //     //     'VTHOUSD', 'UOSUSD', 'MTLUSD', 'PERPUSD', 'ONGUSD', 'CHRUSD', 'ILVUSD', 'SFPUSD', 'HIVEUSD', 'ORBSUSD',
    //     //     'PEOPLEUSD', 'DUSKUSD', 'RAYUSD', 'SLPUSD', 'PUNDIXUSD', 'CEEKUSD', 'METISUSD', 'NKNUSD', 'MASKUSD', 'ATAUSD',
    //     //     'GALUSD', 'LPTUSD', 'AMBUSD', 'RIFUSD', 'ADXUSD', 'OASUSD', 'DIAUSD', 'IQUSD', 'AGLDUSD', 'ERNUSD',
    //     //     'PHAUSD', 'FLOKIUSD', 'MOVRUSD', 'TUSD', 'CFGUSD', 'AERGOUSD', 'BICOUSD', 'TRUUSD', 'MXCUSD', 'ALPHAUSD',
            
    //     //     // Next 50 (201-250)
    //     //     'AIOZUSD', 'MBOXUSD', 'AURORAUSD', 'SUNUSD', 'RDNTUSD', 'BELUSD', 'RADUSD', 'CTXCUSD', 'VRAUSD', 'BUSD',
    //     //     'HIGHUSD', 'EDENUSD', 'FIDAUSD', 'TLMUSD', 'QUICKUSD', 'XNOUSD', 'AKTUSD', 'MLNUSD', 'REPUSD', 'ASTUSD',
    //     //     'BTRSTUSD', 'GHSTUSD', 'MNGOUSD', 'RAREUSD', 'PROUSD', 'OUSD', 'LCXUSD', 'FARMUSD', 'POLSUSD', 'ALICEUSD',
    //     //     'FORTHUSD', 'KP3RUSD', 'BADGERUSD', 'BONDUSD', 'TRBUSD', 'IDEXUSD', 'TRIBEUSD', 'GTCUSD', 'MIRUSD', 'ASMUSD',
    //     //     'CLVUSD', 'DFIUSD', 'FUNUSD', 'GUSD', 'MULTIUSD', 'NESTUSD', 'PLAUSD', 'PROMUSD', 'SUKUUSD', 'VELOUSD'
    //     // ];

    //     // ::::: ETF's Data
    //     $symbols = [
    //         // Broad Market 
    //         'SPY', 'VOO', 'IVV', 'VTI', 'SCHB', 'QQQ', 'DIA', 'IWM', 'EFA', 'VEA',
    //         'EEM', 'VWO', 'AGG', 'BND', 'LQD', 'TIP', 'IEI', 'TLT', 'SHY', 'IEF',
        
    //         // Sector & Thematic
    //         'XLK', 'XLV', 'XLF', 'XLY', 'XLP', 'XLI', 'XLU', 'XLE', 'XLRE', 'XLDE',
    //         'XLB', 'XLC', 'XBI', 'XHB', 'XRT', 'XOP', 'IYW', 'IYH', 'IYF', 'IYC',
        
    //         // Dividend & Income
    //         'VIG', 'SCHD', 'DVY', 'SDY', 'DVP', 'NOBL', 'FDL', 'VYM', 'HDV', 'SPHD',
        
    //         // International Developed & Emerging Markets
    //         'VEU', 'VXUS', 'EEMV', 'GDX', 'GLD', 'SLV', 'DBC', 'USO', 'UNG', 'UUP',
        
    //         // Bond & Fixed Income
    //         'BIV', 'BLV', 'BSV', 'VCIT', 'VCSH', 'VCIT', 'VCIT',  // duplicates removed
    //         'BNDX', 'EMB', 'PCY', 'MUB', 'IGSB', 'CSJ', 'SJNK', 'JNK', 'HYD', 'HYG',
        
    //         // Real Estate & Alternatives
    //         'VNQ', 'SCHH', 'IYR', 'XLRE', 'REET', 'RWR', 'RWX', 'REM', 'O', 'SPG',
        
    //         // Thematic & Innovation
    //         'ARKK', 'ARKG', 'ARKW', 'ARKF', 'XT', 'BOTZ', 'SOCL', 'LIT', 'IGM', 'ITA',
    //         'USMV', 'MTUM', 'QUAL', 'VUG', 'VOOG', 'IWD', 'IWF', 'IWP', 'IWM', 'TLT',
        
    //         // Commodities & Currencies
    //         'UNG', 'USO', 'UUP', 'FXE', 'FXY', 'GLD', 'IAU', 'SLV', 'DBC', 'PDBC',

    //         // Broad Market & Large Cap
    //         'SCHG','IWF','IWB','VV','SCHX','SPTM','SCHZ','AGGY','VIGI','SPDW',
    //         'SPLG','ITOT','ONEQ','DGRO','SCHD','SCHY','SCHB','SUSA','SPYD','SPYV',
            
    //         // Mid/Small Cap
    //         'VB','IJH','IJS','IJR','MDY','SMLN','SLY','SLYV','EWMC','EWMS',
            
    //         // Sector-Specific (complementing prior list)
    //         'IYJ','IYK','IYT','IYZ','IYE','IYW','IXC','IXG','IXN','IXP',
    //         'IXU','IXV','IXG','IXH','IXJ','IXN','IXP','IXY','IYM','IYG',
            
    //         // International (Developed & Emerging)
    //         'EPP','EFA','EEM','EWZ','EWT','EWA','EWC','EWL','EWU','EWS',
    //         'EWP','EWH','EWI','EWW','EWY','EWG','EWD','INDA','SCZ','FEZ',
            
    //         // Fixed Income & Bonds
    //         'BSYL','BSV','BIV','LQD','VNQI','MBB','SJNK','IBND','SCHP','TIPZ',
            
    //         // Dividend & Value
    //         'VTV','IVE','RSP','DIA','SDY','DON','RSP','VLUE','PFF','VIG',
            
    //         // Real Estate/Niche
    //         'REET','ROBO','FTXR','FINX','FINU','CIBR','KWEB','IBB','XLRE','XLK',
    //         'PNQI','ARKQ','ARKV','SPHB','USMV','QUAL','VUG','VOOG','IWP','IWD',
            
    //         // Commodities & Currency
    //         'PALL','PALL','URA','SLYG','GDXJ','GLTR','UNL','UUP','FXY','FXC',
    //         'FXA','FXB','FCOM','FCG','FXS','FXM','FXP','FXU','FXE',
            
    //         // Thematic & Smart Beta
    //         'IHI','IYF','IYJ','IYM','IYT','XBIO','XWEB','XT','XLRE','XLV',
    //     ];
        
        
    //     $apiKey = env('ASSET_KEY');;
    //     $batchSize = 100;
        
    //     // Process symbols in batches with their types
    //     $symbolTypes = [
    //         'stocks' => array_filter($symbols, fn($s) => !str_ends_with($s, 'USD')),
    //         'crypto' => array_filter($symbols, fn($s) => str_ends_with($s, 'USD'))
    //     ];
        
    //     foreach ($symbolTypes as $type => $symbolsOfType) {
    //         if (empty($symbolsOfType)) continue;
            
    //         $chunks = array_chunk($symbolsOfType, $batchSize);
            
    //         foreach ($chunks as $chunk) {
    //             $symbolsString = implode(',', $chunk);
    //             $apiUrl = "https://financialmodelingprep.com/api/v3/quote/{$symbolsString}?apikey={$apiKey}";
        
    //             try {
    //                 $response = Http::get($apiUrl);
                    
    //                 if ($response->failed()) {
    //                     $this->command->error("Failed to fetch {$type} data for: {$symbolsString}");
    //                     continue;
    //                 }
        
    //                 $assets = $response->json();
    //                 if (empty($assets)) {
    //                     $this->command->warn("No {$type} data returned for: {$symbolsString}");
    //                     continue;
    //                 }
        
    //                 $assetsData = array_map(function ($asset) use ($type) {
    //                     return [
    //                         'id' => (string) Str::uuid(),
    //                         'symbol' => $asset['symbol'],
    //                         'name' => $asset['name'],
    //                         'img' => "https://images.financialmodelingprep.com/symbol/{$asset['symbol']}.png",
    //                         'price' => $asset['price'],
    //                         'changes_percentage' => $asset['changesPercentage'],
    //                         'change' => $asset['change'],
    //                         'day_low' => $asset['dayLow'],
    //                         'day_high' => $asset['dayHigh'],
    //                         'year_low' => $asset['yearLow'],
    //                         'year_high' => $asset['yearHigh'],
    //                         'market_cap' => $asset['marketCap'],
    //                         'price_avg_50' => $asset['priceAvg50'],
    //                         'price_avg_200' => $asset['priceAvg200'],
    //                         'exchange' => $asset['exchange'],
    //                         'volume' => $asset['volume'] ?? 0,
    //                         'avg_volume' => $asset['avgVolume'] ?? 0,
    //                         'open' => $asset['open'] ?? 0,
    //                         'previous_close' => $asset['previousClose'] ?? 0,
    //                         'eps' => $asset['eps'] ?? 0,
    //                         'pe' => $asset['pe'] ?? 0,
    //                         'type' => 'etf',  // Dynamic type assignment
    //                         'status' => 'active',
    //                         'tradeable' => 1,
    //                         'created_at' => now(),
    //                         'updated_at' => now(),
    //                     ];
    //                 }, $assets);
        
    //                 DB::table('assets')->upsert($assetsData, ['symbol'], array_keys($assetsData[0]));
    //                 $this->command->info("Successfully processed {$type} batch: {$symbolsString}");
                    
    //             } catch (\Exception $e) {
    //                 $this->command->error("Error processing {$type} symbols: {$symbolsString} - {$e->getMessage()}");
    //                 Log::error("Error in {$type} seeder", ['symbols' => $symbolsString, 'error' => $e->getMessage()]);
    //             }
    //         }
    //     }
        
    //     $this->command->info("Asset seeding completed for all types.");

    // }
}




