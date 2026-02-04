@php
    use App\Enums\Cabinet;
@endphp

export const Cabinets: Record<string, {
    label: string
    /**
    * если capacity = 0, то кабинет неактивен
    */
    capacity: number
}> = {
@foreach(Cabinet::cases() as $cabinet)
    {{ $cabinet->value }}: {
        label: '{{ $cabinet->getHumanName() }}',
        capacity: {{ $cabinet->getCapacity() }},
    },
@endforeach
} as const
