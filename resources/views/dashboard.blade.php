<x-app-layout>
<a href="{{ route('clientes.index') }}" class="group">
    <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
            <div class="h-12 w-12 bg-gradient-to-r from-purple-500 to-pink-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-users text-white"></i>
            </div>
            <i class="fas fa-arrow-right text-gray-400 group-hover:text-purple-500 transition-colors duration-300"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Gestionar Clientes</h3>
        <p class="text-gray-600 text-sm">Ver y administrar clientes</p>
    </div>
</a>
</x-app-layout>
