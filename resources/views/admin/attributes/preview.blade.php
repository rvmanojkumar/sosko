{{-- resources/views/admin/attributes/preview.blade.php --}}
@php
    $type = $attribute->type;
    $displayType = $attribute->display_type;
    $values = $attribute->values;
@endphp

@if($type == 'color')
    <div class="d-flex flex-wrap gap-2">
        @foreach($values as $value)
            <div class="color-swatch" style="width: 40px; height: 40px; background: {{ $value->color_code ?? '#ccc' }}; border-radius: 50%; margin: 5px; border: 1px solid #ddd;" title="{{ $value->value }}"></div>
        @endforeach
        @if($values->isEmpty())
            <div class="text-muted text-center">Add values to see preview</div>
        @endif
    </div>
@elseif($displayType == 'button')
    <div class="d-flex flex-wrap gap-2">
        @foreach($values as $value)
            <button class="btn btn-outline-secondary btn-sm" style="margin: 5px;" disabled>{{ $value->value }}</button>
        @endforeach
        @if($values->isEmpty())
            <div class="text-muted text-center">Add values to see preview</div>
        @endif
    </div>
@elseif($displayType == 'dropdown')
    <select class="form-control" disabled>
        <option>Select option</option>
        @foreach($values as $value)
            <option>{{ $value->value }}</option>
        @endforeach
    </select>
    @if($values->isEmpty())
        <div class="text-muted text-center mt-2">Add values to see preview</div>
    @endif
@else
    <div class="text-muted text-center">Preview not available for this configuration</div>
@endif