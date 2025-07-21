<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Produkt;
use Illuminate\Pagination\LengthAwarePaginator;

class DeficytyTabela extends Component
{
    use WithPagination;

    public $maxStan = null;
    public $filterNazwa = '';
    public $showEmpty = false;

    protected $paginationTheme = 'tailwind';
    protected $updatesQueryString = ['page'];

    public function updatedMaxStan($value)
    {
        if ($value === '' || $value === null) {
            $this->maxStan = null;
        }
        $this->resetPage();
    }

    public function updatedFilterNazwa()
    {
        $this->resetPage();
    }

    public function updatedShowEmpty()
    {
        $this->resetPage();
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

            if (!$this->showEmpty && $zamowienia === 0 && $wsady === 0) {
                return false;
            }

            if ($this->maxStan !== null && $naStanie >= $this->maxStan) {
                return false;
            }

            return true;
        });

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 25;
        $items = $produkty->forPage($currentPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $items,
            $produkty->count(),
            $perPage,
            $currentPage,
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
