<x-layout>
    <x-slot name="title">
        Dodaj Automat
    </x-slot>

    <div class="max-w-xl mx-auto mt-8">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="w-full max-w-md">
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl shadow-xl p-6 space-y-6 border border-gray-700">
                <h1 class="text-3xl font-bold text-center text-white">Dodaj Automat</h1>
                <form method="POST" action="{{ route('automats.store') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="nazwa" class="block text-white font-semibold">Nazwa</label>
                        <input type="text" name="nazwa" id="nazwa" value="{{ old('nazwa') }}" required
                            class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                    </div>

                    <div>
                        <label for="lokalizacja" class="block text-white font-semibold">Lokalizacja</label>
                        <input type="text" name="lokalizacja" id="lokalizacja" value="{{ old('lokalizacja') }}" required
                            class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                    </div>

                    <div>
                        <button type="submit"
                                class="w-full bg-rose-950 hover:bg-red-900 text-white font-bold py-3 px-4 rounded-lg transition duration-200 shadow-md">
                            Dodaj automat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
