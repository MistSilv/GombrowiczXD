<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Produkt;
use Illuminate\Pagination\LengthAwarePaginator;

class DeficytyTabela extends Component
{
    use WithPagination {
        gotoPage as protected parentGotoPage;
    }

    public $maxStan = null;
    public $filterNazwa = '';
    public $page = 1;

    protected $paginationTheme = 'tailwind';

    protected $updatesQueryString = ['page'];

    public function updatedMaxStan()
    {
        $this->resetPage();
    }

    public function updatedFilterNazwa()
    {
        $this->resetPage();
    }

    public function gotoPage($page)
    {
        $this->page = $page;
        $this->parentGotoPage($page);
    }

    public function render()
    {
        $query = Produkt::with(['wsady', 'zamowienia'])
            ->where('is_wlasny', false);

        if (!empty($this->filterNazwa)) {
            $query->where('tw_nazwa', 'like', '%' . $this->filterNazwa . '%');
        }

        $produkty = $query->get()->filter(function ($produkt) {
            $wsady = $produkt->wsady->sum('pivot.ilosc');
            $zamowienia = $produkt->zamowienia->sum('pivot.ilosc');
            $naStanie = $zamowienia - $wsady;

            // Tylko produkty, których naStanie ≠ 0
            if ($naStanie === 0) {
                return false;
            }

            // Jeśli filtr maxStan ustawiony, uwzględnij go
            if ($this->maxStan !== null && $naStanie >= $this->maxStan) {
                return false;
            }

            return true;
        });

        $perPage = 25;
        $page = $this->page;

        $items = $produkty->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $items,
            $produkty->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
                'pageName' => 'page',
            ]
        );

        return view('livewire.deficyty-tabela', [
            'deficyty' => $paginator,
        ]);
    }

}
