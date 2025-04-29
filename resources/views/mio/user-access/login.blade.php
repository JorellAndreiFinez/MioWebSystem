<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@extends('mio.user-access.head')

<body>

<div class="container">

        <div class="form-box login">
            @if (Session::has('success'))
                <div class="alert alert-success" id="alert-message">
                    {{ Session::get('success') }}
                </div>
            @endif

            @if (Session::has('error'))
                <div class="alert alert-danger" id="alert-message">
                    {{ Session::get('error') }}
                </div>
            @endif
            <form method="POST" action="{{ URL('/user-login') }}">
                @csrf
                <h1>Login</h1>
                <div class="input-box">
                    <input type="text" id="email" placeholder="Email/Username" name="email" required>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="input-box">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <i class='bx bxs-lock-alt'></i>
                </div>
                <div class="forgot-link">
                    <a href="#">Forgot Password?</a>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>


        </div>
        <div class="form-box register">
            <form action="#">
                <h1>Report</h1>
                <div class="input-box">
                    <input type="email" placeholder="Email" required>
                    <i class='bx bxs-envelope' ></i>
                </div>

                <div class="input-box">
                    <input type="text" placeholder="Issue" required>
                    <i class='bx bxs-user'></i>
                </div>

                <!-- <div class="input-box">
                    <input type="password" placeholder="Password" required>
                    <i class='bx bxs-lock-alt' ></i>
                </div> -->
                <button type="submit" class="btn">Send Report</button>
                <!-- <p>or register with social platforms</p>
                <div class="social-icons">
                    <a href="#"><i class='bx bxl-google' ></i></a>
                    <a href="#"><i class='bx bxl-facebook' ></i></a>
                    <a href="#"><i class='bx bxl-github' ></i></a>
                    <a href="#"><i class='bx bxl-linkedin' ></i></a>
                </div> -->
            </form>
        </div>

        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <h1>Hello, Welcome!</h1>
                <p>Have a problem to your account?</p>
                <button class="btn register-btn">Report</button>
            </div>

            <div class="toggle-panel toggle-right">
                <h1>Welcome Back!</h1>
                <p>Already have an account?</p>
                <button class="btn login-btn">Login</button>
            </div>
        </div>
    </div>

    <script>
    function validateLogin(event) {
        event.preventDefault();

        let email = document.getElementById("email").value;

        if (email.includes("student")) {
            window.location.href = "{{ route('mio.student-panel') }}";
        } else if (email.includes("admin")) {
            window.location.href = "{{ route('mio.admin-panel') }}";
        } else if (email.includes("parent")) {
            window.location.href = "{{ route('mio.parent-panel') }}";
        } else if (email.includes("teacher")) {
            window.location.href = "{{ route('mio.teacher-panel') }}";
        }
        else {
            alert("Invalid login. Use 'student' or 'admin' in email.");
        }
    }

     // Auto-hide alerts after 3 seconds (3000ms)
     setTimeout(function() {
        let alert = document.getElementById('alert-message');
        if (alert) {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = '0';
            setTimeout(function(){
                alert.remove();
            }, 500); // Wait until fadeout is complete
        }
    }, 3000);
</script>

</body>
</html>
