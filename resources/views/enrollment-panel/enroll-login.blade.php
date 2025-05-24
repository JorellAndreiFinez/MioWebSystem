<div class="form-wrap">
@if (session('status'))
    <div class="alert alert-success" style="color: green; margin-bottom: 15px;">
        {{ session('status') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger" style="color: red; margin-bottom: 15px;">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger" style="color: red; margin-bottom: 15px;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    <div class="tabs">
        <h3 class="signup-tab">
            <a class="" href="#signup-tab-content">Sign Up</a>
        </h3>

        <h3 class="login-tab">
            <!-- Login tab will always be active initially -->
            <a class="active" href="#login-tab-content">Login</a>
        </h3>
    </div><!--.tabs-->

    <div class="tabs-content">
        <!-- Sign Up Tab -->
        <div id="signup-tab-content" class="">
            <form class="signup-form" action="{{ route('enroll.signup') }}" method="post">
                @csrf
                <input type="email" name="user_email" class="input" id="user_email" autocomplete="off" placeholder="Email" required value="jorellandrei12345@fit.edu.ph
">
                <input type="text" name="fname" class="input" id="fname" autocomplete="off" placeholder="First Name" required value="jj">

                <input type="text" name="lname" class="input" id="lname" autocomplete="off" placeholder="Last Name" required value="andrei">

                <!-- Sign Up Password -->
                <div class="password-wrap">
                    <input type="password" name="user_pass" class="input password-input" id="user_pass" autocomplete="off" placeholder="Password" required  value="">
                    <i class="fa-regular fa-eye toggle-icon" id="togglePasswordSignup"></i>
                </div>

                <!-- Sign Up Confirm Password -->
                <div class="password-wrap">
                    <input type="password" name="user_pass_confirmation" class="input password-input" id="user_pass_confirmation" autocomplete="off" placeholder="Confirm Password" required value="">
                    <i class="fa-regular fa-eye toggle-icon" id="toggleConfirmPassword"></i>
                </div>
                <input type="submit" class="button" value="Sign Up">
            </form><!--.signup-form-->
            <div class="help-text">
                <p>By signing up, you agree to our</p>
                <p><a href="#">Terms of service</a></p>
            </div><!--.help-text-->
        </div><!--.signup-tab-content-->

        <!-- Login Tab -->
        <div id="login-tab-content" class="active">
            <form class="login-form" action="{{ route('enroll.login') }}" method="post">
                @csrf
                <input type="text" name="user_login" class="input" id="user_login" autocomplete="off" placeholder="Email or Username" value="jorellandrei12345@fit.edu.ph" required>

                <!-- Password Input -->
                <div class="password-wrap">
                    <input type="password" name="user_pass" class="input password-input" id="user_pass_login" autocomplete="off" placeholder="Password" required>
                    <i class="fa-regular fa-eye toggle-icon" id="togglePasswordLogin"></i>
                </div>

                <input type="submit" class="button" value="Login">
            </form><!--.login-form-->

        </div><!--.login-tab-content-->
    </div><!--.tabs-content-->
</div><!--.form-wrap-->

<script>
jQuery(document).ready(function($) {
    function togglePassword(inputId, iconId) {
        const input = $(`#${inputId}`);
        const icon = $(`#${iconId}`);
        const type = input.attr('type') === 'password' ? 'text' : 'password';
        input.attr('type', type);
        icon.toggleClass('fa-eye fa-eye-slash');
    }

    $('#togglePasswordSignup').on('click', function() {
        togglePassword('user_pass', 'togglePasswordSignup');
    });

    $('#toggleConfirmPassword').on('click', function() {
        togglePassword('user_pass_confirmation', 'toggleConfirmPassword');
    });

    $('#togglePasswordLogin').on('click', function() {
        togglePassword('user_pass_login', 'togglePasswordLogin');
    });

    // Tab switching
    const tab = $('.tabs h3 a');
    tab.on('click', function(event) {
        event.preventDefault();
        tab.removeClass('active');
        $(this).addClass('active');
        const tab_content = $(this).attr('href');
        $('div[id$="tab-content"]').removeClass('active');
        $(tab_content).addClass('active');
    });
});
</script>

<!-- Add this script to initialize the phone input field with the country code -->
<script>
    var input = document.querySelector("#phone");
    var iti = window.intlTelInput(input, {
        initialCountry: "PH", // Set Philippines country code as default
        separateDialCode: true, // Display separate country code
        nationalMode: false, // Forces international phone number format
        utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@17.0.8/build/js/utils.js"
    });

    // Event listener to format the phone number correctly
    input.addEventListener('input', function (e) {
        let value = e.target.value;

        // Ensure the phone number starts with '9' after country code
        if (value.charAt(0) === '0') {
            e.target.value = '9' + value.slice(1);
        }

        // Check if the phone number is 10 digits long (PH numbers start with 9)
        if (value.length > 10) {
            e.target.value = value.slice(0, 10); // Truncate to 10 digits
        }
    });

    // Optional: additional validation on form submission to check length
    document.querySelector("form").addEventListener("submit", function (e) {
        var phoneNumber = input.value.replace(/\D/g, ''); // Strip non-digit characters
        if (phoneNumber.length !== 10 || phoneNumber.charAt(0) !== '9') {
            e.preventDefault();
            alert("Please enter a valid Philippine phone number starting with '9' and exactly 10 digits.");
        }
    });
</script>
