<!-- Emergency Countdown Modal -->
<div id="emergencyModal" class="modal" style="display:none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.6); justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: 10px; text-align: center; width: 300px;">
        <h3 id="modalCountdownText" style="font-size: 24px;">Starting in 3...</h3>
        <button id="finishButton" style="margin-top: 20px; display: none;" onclick="closeEmergencyModal()">Finish</button>
    </div>
</div>


<section class="home-section">
    <div class="text">Emergency Alert</div>

    <div class="emergency-container">
    <div class="emergency-header">
        <h2>Choose your Emergency</h2>
        <p>Select the appropriate emergency category to alert students' response efforts effectively.</p>
    </div>

    <div class="emergency-grid">
    <div class="emergency-item" onclick="triggerEmergency('flood')">
        <img src="{{ asset('storage/assets/images/icons/flood-1.png') }}" alt="Flood Icon">
        <p>Flooding</p>
    </div>

        <div class="emergency-item">
            <img src="{{ asset('storage/assets/images/icons/fire-1.png') }}" alt="Fire Icon">
            <p>Fire</p>
        </div>
        <div class="emergency-item">
            <img src="{{ asset('storage/assets/images/icons/flood-1.png') }}" alt="Flood Icon">
            <p>Flooding</p>
        </div>
        <div class="emergency-item">
            <img src="{{ asset('storage/assets/images/icons/threat.png') }}" alt="School Threat Icon">
            <p>School Threat</p>
        </div>
        <div class="emergency-item">
            <img src="{{ asset('storage/assets/images/icons/outage.png') }}" alt="Power Outage Icon">
            <p>Power Outage</p>
        </div>
    </div>
</div>

</section>

<script>
let selectedEmergencyName = ''; // Global variable to store the selected emergency name

function triggerEmergency(name) {
    let countdown = 3;
    selectedEmergencyName = name; // Store the emergency name globally
    const modal = document.getElementById('emergencyModal');
    const text = document.getElementById('modalCountdownText');
    const button = document.getElementById('finishButton');

    modal.style.display = 'flex';
    button.style.display = 'none';

    const interval = setInterval(() => {
        if (countdown === 0) {
            clearInterval(interval);
            text.textContent = 'Emergency Vibration is Started';
            button.style.display = 'inline-block';
        } else {
            text.textContent = `Starting in ${countdown}...`;
            countdown--;
        }
    }, 1000);
}

function closeEmergencyModal() {
    if (!selectedEmergencyName) {
        alert('No emergency selected.');
        return;
    }

    // Send emergency alert on finish
    fetch("{{ route('trigger.emergency') }}", {
        method: "POST",
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        body: JSON.stringify({ name: selectedEmergencyName })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Emergency created: ', data);
        alert("Emergency Alert Sent Successfully!");
        // Optionally reset modal
        document.getElementById('emergencyModal').style.display = 'none';
        selectedEmergencyName = '';
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Failed to send emergency alert.");
    });
}
</script>



