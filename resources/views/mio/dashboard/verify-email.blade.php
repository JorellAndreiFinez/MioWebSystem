<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification Required</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Auto-refresh script to check verification -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Poll the server every 5 seconds to check verification status
            setInterval(() => {
                fetch("{{ route('mio.check-verification') }}", {
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.verified) {
                        window.location.href = data.redirect_to;
                    }
                })
                .catch(error => console.error('Error:', error));
            }, 5000);
        });
    </script>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="card shadow p-4" style="max-width: 500px; width: 100%;">
            <h3 class="text-center mb-3">Email Verification Required</h3>

            @if(session('message'))
                <div class="alert alert-info text-center">
                    {{ session('message') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger text-center">
                    {{ session('error') }}
                </div>
            @endif

            <p class="text-muted text-center">
                Please verify your email using the link sent to <strong>{{ session('email') }}</strong>. <br>
                Once verified, you'll be redirected automatically.
            </p>

            <form method="POST" action="{{ route('mio.resend-verification') }}">
                @csrf
                <input type="hidden" name="email" value="{{ session('email') }}">
                <button type="submit" class="btn btn-primary w-100">Resend Verification Email</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
    // Poll the server every 5 seconds to check verification status
    setInterval(() => {
        fetch("{{ route('mio.check-verification') }}", {
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.verified) {
                // If the email is verified, redirect to the respective role's dashboard
                window.location.href = data.redirect_to;
            }
        })
        .catch(error => console.error('Error:', error));
    }, 5000);
});
    </script>
</body>
</html>
