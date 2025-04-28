@if (session(key: 'status'))
    <div class="alert alert-success" id="alert-message">
        {{ session(key: 'status') }}
</div>
@endif
