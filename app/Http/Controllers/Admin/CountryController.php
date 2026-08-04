<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Country\StoreCountryRequest;
use App\Http\Requests\Admin\Country\UpdateCountryRequest;
use App\Models\Country;
use App\Models\Language;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function index(Request $request): View
    {
        $search   = $request->input('search');
        $isActive = $request->input('is_active');

        $activeLanguageCodes = Language::query()
            ->active()
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all();

        $orderLanguageCode = Language::query()
            ->active()
            ->where('is_required', true)
            ->orderBy('sort_order')
            ->value('code');

        if (!$orderLanguageCode) {
            $orderLanguageCode = $activeLanguageCodes[0] ?? null;
        }

        $query = Country::query();

        if ($search) {
            $query->where(function (Builder $q) use ($search, $activeLanguageCodes): void {
                $q->where('iso2', 'like', '%' . $search . '%')
                    ->orWhere('iso3', 'like', '%' . $search . '%');

                foreach ($activeLanguageCodes as $code) {
                    $q->orWhere("short_names->{$code}", 'like', '%' . $search . '%')
                        ->orWhere("long_names->{$code}", 'like', '%' . $search . '%');
                }
            });
        }

        if ($isActive !== null && $isActive !== '') {
            $query->where('is_active', $isActive === '1');
        }

        if ($orderLanguageCode) {
            $query->orderByRaw(
                "LOWER(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(short_names, ?)), '')) asc",
                ['$.'.$orderLanguageCode]
            );
        } else {
            $query->orderBy('id', 'asc');
        }

        $countries = $query
            ->orderBy('id', 'asc')
            ->paginate(20)
            ->withQueryString();

        $filters = [
            'search'    => $search,
            'is_active' => $isActive,
        ];

        return view('admin.countries.index', compact('countries', 'filters'));
    }

    public function create(): View
    {
        $country = new Country();

        return view('admin.countries.form', compact('country'));
    }

    public function store(StoreCountryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Country::create($data);

        return redirect()
            ->route('admin.countries.index')
            ->with('success', __('Country created successfully.'));
    }

    public function edit(Country $country): View
    {
        return view('admin.countries.form', compact('country'));
    }

    public function update(UpdateCountryRequest $request, Country $country): RedirectResponse
    {
        $data = $request->validated();

        $country->update($data);

        return redirect()
            ->route('admin.countries.index')
            ->with('success', __('Country updated successfully.'));
    }

    public function destroy(Country $country): RedirectResponse
    {
        $country->delete();

        return redirect()
            ->route('admin.countries.index')
            ->with('success', __('Country deleted successfully.'));
    }

    public function toggleStatus(Country $country): RedirectResponse
    {
        $country->is_active = ! $country->is_active;
        $country->save();

        return redirect()
            ->route('admin.countries.index')
            ->with('success', __('Country status updated successfully.'));
    }
}
