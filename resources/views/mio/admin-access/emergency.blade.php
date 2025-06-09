<!-- Emergency Countdown Modal -->
<div id="emergencyModal" class="modal" style="display:none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.6); justify-content: center; align-items: center;">
    <div id="modalBox" style="background: yellow; transition: background 0.5s ease; padding: 2rem; border-radius: 10px; text-align: center; width: 300px;">
        <h3 id="modalCountdownText" style="font-size: 24px; margin-bottom: 20px;">Starting in 3...</h3>
        <button id="cancelButton" onclick="cancelEmergency()" style="margin-right: 10px;">Cancel</button>
        <button id="finishButton" style="display: none;" onclick="closeEmergencyModal()">Finish</button>
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
        <div class="emergency-item" onclick="triggerEmergency('fl')">
            <img src="{{ asset('storage/assets/images/icons/flood-1.png') }}" alt="Flood Icon">
            <p>Flooding</p>
        </div>

        <div class="emergency-item" onclick="triggerEmergency('fi')">
            <img src="{{ asset('storage/assets/images/icons/fire-1.png') }}" alt="Fire Icon">
            <p>Fire</p>
        </div>

        <div class="emergency-item" onclick="triggerEmergency('ea')">
            <img src="{{ asset('storage/assets/images/icons/earthquake-1.png') }}" alt="Flood Icon">
            <p>Earthquake</p>
        </div>

        <div class="emergency-item" onclick="triggerEmergency('sc')">
            <img src="{{ asset('storage/assets/images/icons/threat.png') }}" alt="School Threat Icon">
            <p>School Threat</p>
        </div>

        <div class="emergency-item" onclick="triggerEmergency('po')">
            <img src="{{ asset('storage/assets/images/icons/outage.png') }}" alt="Power Outage Icon">
            <p>Power Outage</p>
        </div>
    </div>

</div>

</section>

<script>
let selectedEmergencyName = '';
let emergencyInterval = null;
let currentEmergencyId = null;


function triggerEmergency(name) {
    let countdown = 3;
    selectedEmergencyName = name;
    const modal = document.getElementById('emergencyModal');
    const text = document.getElementById('modalCountdownText');
    const button = document.getElementById('finishButton');
    const modalBox = document.getElementById('modalBox');

    modal.style.display = 'flex';
    button.style.display = 'none';
    modalBox.style.background = 'yellow';

    emergencyInterval = setInterval(() => {
        if (countdown === 0) {
            clearInterval(emergencyInterval);

            // Call Laravel endpoint to create emergency
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
                currentEmergencyId = data.emergency_id; // Store emergency ID globally
                text.textContent = 'Emergency Vibration is Started';
                button.style.display = 'inline-block';
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Failed to start emergency alert.");
                cancelEmergency();
            });

            return;
        }

        text.textContent = `Starting in ${countdown}...`;
        if (countdown === 3) modalBox.style.background = 'yellow';
        else if (countdown === 2) modalBox.style.background = 'orange';
        else if (countdown === 1) modalBox.style.background = 'red';

        countdown--;
    }, 1000);

}

function cancelEmergency() {
    clearInterval(emergencyInterval);
    document.getElementById('emergencyModal').style.display = 'none';
    selectedEmergencyName = '';
}

function closeEmergencyModal() {
    if (!currentEmergencyId) {
        alert('No emergency is active.');
        return;
    }

    fetch("{{ route('emergency.stop-vibration') }}", {
        method: "POST",
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        body: JSON.stringify({ id: currentEmergencyId })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Vibration stopped:', data);
        alert("Emergency Vibration Stopped.");
        document.getElementById('emergencyModal').style.display = 'none';
        selectedEmergencyName = '';
        currentEmergencyId = null;
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Failed to stop vibration.");
    });
}

window.onload = function () {
    fetch("{{ route('emergency.active') }}")
    .then(response => response.json())
    .then(data => {
        if (data.active) {
            showEmergencyModalFromExisting(data);
        }
    });
};

function showEmergencyModalFromExisting(data) {
    currentEmergencyId = data.id;
    selectedEmergencyName = data.name;

    const modal = document.getElementById('emergencyModal');
    const text = document.getElementById('modalCountdownText');
    const button = document.getElementById('finishButton');
    const modalBox = document.getElementById('modalBox');

    modal.style.display = 'flex';
    modalBox.style.background = 'red';
    text.textContent = 'Emergency Vibration is Started';
    button.style.display = 'inline-block';
}



</script>







