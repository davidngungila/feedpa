<!-- Session messages are now handled by SweetAlert notifications -->

@if($errors->any())
    <div class="alert alert-danger alert-dismissible" role="alert">
        <strong>Whoops!</strong> There were some problems with your input.
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
