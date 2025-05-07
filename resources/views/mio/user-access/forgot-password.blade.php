<!DOCTYPE html>
<html lang="en">
@extends('mio.user-access.head')
<body>
    <div class="container">
        <div class="form-box">
            <h2>Forgot Password</h2>

            @if (Session::has('success'))
                <div class="alert alert-success">{{ Session::get('success') }}</div>
            @endif

            @if (Session::has('error'))
                <div class="alert alert-danger">{{ Session::get('error') }}</div>
            @endif

            <form action="{{ route('forgot.send') }}" method="POST">
                @csrf
                <div class="input-box">
                    <input type="email" name="email" placeholder="Enter your email" required>
                    <i class='bx bxs-envelope'></i>
                </div>
                <button class="btn" type="submit">Send Reset Link</button>
            </form>

            <div class="back-link mt-3">
                <a href="{{ route('mio.login') }}">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
