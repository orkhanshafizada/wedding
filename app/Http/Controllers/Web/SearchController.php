<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Modules\Square\Enums\SquareStatus;
use Modules\Square\Models\Square;

class SearchController extends Controller
{
    /**
     * Global search – kvadratları müştərinin adı/soyadı və ölkə adına görə tapır
     * və nəticələri Menu (kateqoriya) üzrə qruplaşdırır.
     *
     * URL : GET /search?q=OR
     * Name: web.search
     */
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->input('q', ''));

        if ($query === '') {
            return view('web.search.index', [
                'query'          => $query,
                'groupedSquares' => collect(),
            ]);
        }

        $like = '%' . $query . '%';

        // 1) Ölkə adına uyğun country_id-ləri tapırıq
        $countryIds = Country::query()
            ->active()
            ->where(function ($q) use ($like) {
                // JSON field-lər + kodlar üzrə axtarış
                $q->where('short_names', 'LIKE', '%' . $like . '%')
                    ->orWhere('long_names', 'LIKE', '%' . $like . '%')
                    ->orWhere('iso2', 'LIKE', '%' . $like . '%')
                    ->orWhere('iso3', 'LIKE', '%' . $like . '%');
            })
            ->pluck('id');

        // 2) Bütün uyğun kvadratları yığırıq
        $squares = Square::query()
            ->with([
                'customer.countryRef',
                'menu.translations',
            ])
            ->whereIn('status', [
                SquareStatus::AVAILABLE,
                SquareStatus::RESERVED,
                SquareStatus::PURCHASED,
            ])
            ->whereHas('customer', function ($q) use ($query, $countryIds, $like) {
                $q->where(function ($qq) use ($query, $countryIds, $like) {
                    // Ad / soyad ilə axtarış
                    $qq->where('name', 'LIKE', $like)
                        ->orWhere('surname', 'LIKE', $like);

                    // Əgər uyğun ölkə tapmışıqsa → country_id ilə də filter
                    if ($countryIds->isNotEmpty()) {
                        $qq->orWhereIn('country_id', $countryIds);
                    }
                });
            })
            ->orderBy('menu_id')
            ->orderBy('id')
            ->get();

        // Menu üzrə qruplaşdırma
        $groupedSquares = $squares->groupBy(function (Square $sq) {
            return optional($sq->menu)->id ?? 'no_menu';
        });

        return view('web.search.index', [
            'query'          => $query,
            'groupedSquares' => $groupedSquares,
        ]);
    }
}
