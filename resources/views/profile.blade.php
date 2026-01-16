@extends('layouts.app')

@section('title', 'Perfil de Usuario')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6">Perfil de Usuario</h1>

        <!-- Información del Usuario -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-4">Información Personal</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                    <p class="mt-1 text-sm text-gray-900" id="full_name">{{ $user->full_name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Username</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $user->username }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <p class="mt-1 text-sm text-gray-900" id="email">{{ $user->email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $user->is_active ? 'Activo' : 'Inactivo' }}</p>
                </div>
            </div>
        </div>

        <!-- Información del Empleado -->
        @if($user->employee)
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-4">Información Laboral</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código de Empleado</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $user->employee->employee_code }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Posición</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $user->employee->position }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Extensión</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $user->employee->extension }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha de Contratación</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $user->employee->hire_date ? $user->employee->hire_date->format('d/m/Y') : 'N/A' }}</p>
                </div>
                @if($user->employee->team)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Equipo</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $user->employee->team->name }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Editar Perfil -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-4">Editar Perfil</h2>
            <form id="profileForm" class="space-y-4">
                @csrf
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                    <input type="text" id="full_name_input" name="full_name" value="{{ $user->full_name }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email_input" name="email" value="{{ $user->email }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Actualizar Perfil
                    </button>
                </div>
            </form>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-4">Cambiar Contraseña</h2>
            <form id="passwordForm" class="space-y-4">
                @csrf
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700">Contraseña Actual</label>
                    <input type="password" id="current_password" name="current_password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Nueva Contraseña</label>
                    <input type="password" id="password" name="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Nueva Contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Cambiar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('/api/profile', {
        method: 'PUT',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert('Perfil actualizado exitosamente');
        location.reload();
    })
    .catch(error => console.error('Error:', error));
});

document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('/api/change-password', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.message.includes('exitosa')) {
            localStorage.removeItem('api_token');
            window.location.href = '/signin';
        }
    })
    .catch(error => console.error('Error:', error));
});
</script>
@endsection