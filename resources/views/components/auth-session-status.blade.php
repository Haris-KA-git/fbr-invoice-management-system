@props(['status'])

@if ($status)
    <div class="alert alert-success d-flex align-items-center" {{ $attributes->merge(['class' => '']) }}>
        <i class="bi bi-check-circle-fill me-2"></i>
        <div>{{ $status }}</div>
    </div>
@endif