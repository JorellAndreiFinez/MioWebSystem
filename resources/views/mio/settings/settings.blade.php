<section class="home-section" >
  <div class="text">Settings</div>

  <div class="settings-wrapper">


    <!-- Tab Content -->
    <div class="tab-content">
      <div class="tab-panel active" id="forgot-password">
        <h2>Forgot Password</h2>
        <p>We’ll send you an email to reset your password. Don’t worry, it’s easy!</p>
        <input type="email" placeholder="Enter your email" class="input-box" />
        <button class="action-button">Send Reset Link</button>
      </div>

      <div class="tab-panel" id="change-email">
        <h2>Change Email</h2>
        <p>Update your email so we can reach you better.</p>
        <input type="email" placeholder="New email address" class="input-box" />
        <button class="action-button">Update Email</button>
      </div>

      <div class="tab-panel" id="privacy-policy">
        <h1>Data Privacy Policy</h1>
        @include('mio.settings.policies.privacy-policy')
      </div>

      <div class="tab-panel" id="terms">
        <h2>Terms & Conditions</h2>
        <p>These are the friendly rules of our app. Be kind, stay safe, and have fun while learning!</p>
      </div>

      <div class="tab-panel" id="acceptable-use">
        <h2>Acceptable Use Policy</h2>
        <p>Please use the app responsibly. Don’t upload harmful, inappropriate, or unrelated content. Respect others while learning.</p>
      </div>

      <div class="tab-panel" id="mobile-policy">
        <h2>Mobile App Policy</h2>
        <p>This policy explains how the app works on phones and tablets. Use updated versions for the best experience!</p>
      </div>

      <div class="tab-panel" id="accessibility">
        <h2>Accessibility Policy</h2>
        <p>We aim to make learning available for everyone. If you face difficulty using the app, let us know how we can improve.</p>
      </div>

      <div class="tab-panel" id="content-guidelines">
        <h2>Content Submission Guidelines</h2>
        <p>If you're allowed to submit content (like answers, recordings, or messages), keep it respectful, original, and helpful.</p>
      </div>

      <div class="tab-panel" id="account-security">
        <h2>Account & Security</h2>
        <p>Keep your login details safe. If you notice anything strange, report it to your teacher or admin right away.</p>
      </div>
    </div>
       <!-- Sidebar Navigation -->
    <div class="settings-sidebar">
      <button class="tab-button active" data-tab="forgot-password">Forgot Password</button>
      <button class="tab-button" data-tab="change-email">Change Email</button>
      <hr>
      <button class="tab-button" data-tab="privacy-policy">Privacy Policy</button>
      <button class="tab-button" data-tab="terms">Terms & Conditions</button>
      <button class="tab-button" data-tab="acceptable-use">Acceptable Use</button>
      <button class="tab-button" data-tab="mobile-policy">Mobile App Policy</button>
      <button class="tab-button" data-tab="accessibility">Accessibility</button>
      <button class="tab-button" data-tab="content-guidelines">Content Guidelines</button>
      <button class="tab-button" data-tab="account-security">Account & Security</button>
    </div>
  </div>
</section>

<script>
  document.querySelectorAll('.tab-button').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const tabId = btn.getAttribute('data-tab');
      document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.remove('active');
        if (panel.id === tabId) {
          panel.classList.add('active');
        }
      });
    });
  });
</script>

