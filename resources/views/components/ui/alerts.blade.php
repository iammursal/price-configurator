@props(['type' => ['success', 'error', 'warning', 'info']])

@php
    $alertTypes = ['success', 'error', 'warning', 'info'];
    $currentType = null;

    foreach($alertTypes as $alertType) {
        if(session($alertType)) {
            $currentType = $alertType;
            break;
        }
    }

    // If type prop is provided and matches current session type
    if(!empty($type) && in_array($currentType, (array)$type)) {
        $displayType = $currentType;
    } elseif($currentType) {
        $displayType = $currentType;
    } else {
        $displayType = null;
    }

    // Define complete class names for Tailwind CSS
    $alertClasses = [
        'success' => 'bg-green-100 border-green-400 text-green-700',
        'error' => 'bg-red-100 border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
        'info' => 'bg-blue-100 border-blue-400 text-blue-700',
    ];

    $iconClasses = [
        'success' => 'text-green-500',
        'error' => 'text-red-500',
        'warning' => 'text-yellow-500',
        'info' => 'text-blue-500',
    ];
@endphp
@if($displayType)
    <div id="alert-{{ $displayType }}" class="{{ $alertClasses[$displayType] }} border px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">{{ ucfirst($displayType) }}!</strong>
        <span class="block sm:inline">{{ session($displayType) }}</span>
        <button type="button"
                onclick="document.getElementById('alert-{{ $displayType }}').style.display='none'"
                class="absolute top-0 bottom-0 right-0 px-4 py-3 hover:bg-black hover:bg-opacity-10 transition-colors duration-200">
            <svg class="fill-current h-6 w-6 {{ $iconClasses[$displayType] }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <title>Close</title>
                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
            </svg>
        </button>
    </div>
@endif
