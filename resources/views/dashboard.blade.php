<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Documents') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Tombol Buat Dokumen Baru -->
            <div class="mb-6">
                <form action="{{ route('documents.create') }}" method="POST">
                    @csrf
                    <button type="submit" class="
                        inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent 
                        rounded-md font-semibold text-xs text-white uppercase tracking-widest 
                        hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 
                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 
                        transition ease-in-out duration-150
                    ">
                        📄 + Buat Dokumen Baru
                    </button>
                </form>
            </div>

            <!-- List Dokumen -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($documents->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($documents as $doc)
                                <a href="{{ route('documents.edit', $doc->id) }}" 
                                   class="block p-6 bg-gray-50 hover:bg-indigo-50 border border-gray-200 rounded-lg transition">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2">
                                        📄 {{ $doc->title }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        Dibuat: {{ $doc->created_at->format('d M Y, H:i') }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-2">
                                        {{ $doc->versions->count() }} versi tersimpan
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600 text-center py-8">
                            Belum ada dokumen. Klik tombol di atas untuk membuat dokumen pertama!
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>