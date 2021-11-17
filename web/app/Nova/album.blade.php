<style>
    .page-break {
        page-break-after: always;
    }
</style>

<img src="{{ Storage::path('albums/'.$album->id.'/'.$coverImageName) }}" width="100%">
<div class="page-break"></div>
@foreach($album->albumImages as $image)
    <img src="{{ Storage::path('albums/'.$album->id.'/'.$image->image_name) }}" width="100%">
    <div class="page-break"></div>
@endforeach
<p>Invite {{ $coupon->usage_times }} of your friends and let them get  {{ $coupon->value }}$ now!</p>
<p>{{ $coupon->code }}</p>
