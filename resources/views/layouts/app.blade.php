@role('admin')
    @include('components.admin')
@else
    @include('components.layout')
@endrole
