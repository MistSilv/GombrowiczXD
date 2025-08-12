<x-layout>
    <div class="max-w-4xl mx-auto p-4 bg-gray-900 rounded-2xl shadow-lg">
        <a href="{{ route('produkty.templates.create') }}"
        class="mb-6 px-4 py-2 bg-green-800 hover:bg-green-600 text-white font-semibold rounded-lg text-base text-left transition shadow inline-block align-middle">
            ➕ Dodaj szablon zamówienia
        </a>
        <h1 class="text-2xl font-bold mb-6 text-white text-center">Szablony zamówień</h1>
        <div class="overflow-x-auto">
            <table class="min-w-full text-white bg-gray-800 rounded-lg overflow-hidden shadow">
                <thead>
                    <tr>
                        <th class="py-2 px-2 text-center font-bold text-gray-200 uppercase text-sm">ID</th>
                        <th class="py-2 px-2 text-center font-bold text-gray-200 uppercase text-sm">Nazwa</th>
                        <th class="py-2 px-2 text-center font-bold text-gray-200 uppercase text-sm">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $template)
                        <tr class="border-b border-gray-700 hover:bg-gray-700 transition">
                            <td class="py-2 px-2 text-center font-semibold text-sm">{{ $template->id }}</td>
                            <td class="py-2 px-2 text-center font-semibold text-sm">{{ $template->nazwa }}</td>
                            <td class="py-2 px-2 text-center">
                                <div class="flex flex-col sm:flex-row gap-2 justify-center items-center">
                                    <button type="button"
                                        class="w-full sm:w-auto px-4 py-2 bg-blue-800 hover:bg-blue-600 text-white font-semibold rounded-lg text-sm transition toggle-details"
                                        data-template="{{ $template->id }}">
                                        Szczegóły
                                    </button>
                                    <form method="POST" action="{{ route('produkty.templates.destroy', $template->id) }}" class="w-full sm:w-auto">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            onclick="return confirm('Czy na pewno chcesz usunąć ten szablon?')"
                                            class="w-full sm:w-auto px-4 py-2 bg-red-800 hover:bg-red-600 text-white font-semibold rounded-lg text-sm transition">
                                            Usuń
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr class="details-row" id="details-{{ $template->id }}" style="display: none;">
                            <td colspan="3" class="bg-gray-900 px-4 py-4">
                                <div class="text-center">
                                    <span class="font-bold text-lg text-white mb-2 block">Produkty w szablonie:</span>
                                    <form method="POST" action="{{ route('produkty.templates.update', $template->id) }}" class="inline-block w-full max-w-xs mx-auto">
                                        @csrf
                                        @method('PATCH')
                                        <table class="w-full mb-2">
                                            <tbody>
                                                @forelse($template->produkty as $pivot)
                                                    <tr>
                                                        <td class="py-2 px-2 text-right align-middle w-2/3">
                                                            <span class="font-medium text-white text-sm">{{ $pivot->produkt->tw_nazwa ?? 'Brak nazwy' }}</span>
                                                        </td>
                                                        <td class="py-2 px-2 align-middle w-1/3">
                                                            <input type="number" name="ilosci[{{ $pivot->id }}]" value="{{ $pivot->ilosc }}" min="1"
                                                                class="w-full px-3 py-2 rounded-md shadow-sm border bg-gray-700 text-white text-sm text-center focus:ring-2 focus:ring-blue-500 transition" />
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2" class="text-gray-400 text-center py-2 text-sm">Brak produktów</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                        @if($template->produkty->count())
                                            <button type="submit" class="mt-2 px-4 py-2 bg-green-800 hover:bg-green-600 text-white font-semibold rounded-lg shadow transition text-sm flex items-center mx-auto w-full justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                                Zapisz ilości
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <script src="{{ asset('js/zamowienia-template.js') }}"></script>
</x-layout>