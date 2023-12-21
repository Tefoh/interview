<div>
    {{ $getState() ? \Illuminate\Support\Carbon::parse($getState())->format('m/d/Y') : 'No publication date' }}
</div>
