<section class="home-section">
    <div class="text">Inbox</div>
    <button class="new-message-btn">+ New Message</button>
    <div class="grid-container">
        <!-- Begin Main-->
        <main class="main">

            <div class="container">

                <!-- Main Content -->
                <div class="main-content">
                    <!-- Left Column - Chat List -->
                   <div class="chat-list">
                    @if(count($contacts) === 0)
                        <div class="no-messages">
                            <p>You haven’t messaged anyone yet.</p>
                            <p>Start a conversation by clicking the <strong>"+ New Message"</strong> button.</p>
                        </div>
                    @else
                        @foreach($contacts as $contact)
                            <div class="contact" data-contact-id="{{ $contact['id'] }}" data-contact-name="{{ $contact['name'] }}" data-contact-role="{{ $contact['role'] }}" data-contact-image="{{ $contact['profile_pic'] }}">
                                <img src="{{ $contact['profile_pic'] ?? 'default.jpg' }}" class="profile-pic" />
                                <div class="contact-info">
                                    <p class="name">{{ $contact['name'] }}</p>
                                    <p class="role">{{ $contact['role'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                    <!-- Right Column - Chat Messages -->
                <div class="no-contact-selected">
                    <div class="no-contact-icon">
                        <!-- Heroicons: Chat Bubble Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon-chat" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 3.866-3.582 7-8 7-1.146 0-2.226-.207-3.188-.578L3 21l1.578-4.812C4.207 15.226 4 14.146 4 13c0-4.418 3.134-8 7-8s7 3.582 7 8z" />
                        </svg>
                    </div>
                    <div class="no-contact-text">
                        <h2>No conversation selected</h2>
                        <p>Please select a contact from the list to start chatting.</p>
                    </div>
                </div>


                    <div class="chat-content hidden">
                        <div class="header">
                                <h2>Leo Kenter</h2>
                                <p class="subtitle">Speech Development</p>
                            </div>


                        <!-- Conversation -->
                        <div class="conversation ">
                            <div class="message sender">

                            </div>

                            <div class="message response">

                            </div>
                    </div>
                    </div>

                    <!-- Change the chat-content when clicking to add message -->
                     <div class="chat-content new-message-content" style="display: none;">
                        <div class="header">
                            <h2>New Message</h2>
                            <p class="subtitle">Compose a new message</p>
                        </div>

                        <form action="{{ route('mio.message-send') }}" method="post">
                            @csrf
                            <meta name="csrf-token" content="{{ csrf_token() }}">
                            <div class="compose-form">
                            <!-- Dropdown for To: field -->
                            <label for="recipient">To:</label>

                            <select id="group-select" >
                                <option value="">Select Group</option>

                                @foreach($subjects as $subject)
                                    <option value="subject_{{ $loop->index }}">{{ $subject['subject_name'] }}</option>
                                @endforeach
                            </select>

                            <select id="people-select" name="receiver_id">
                                <option value="">Select Person</option>
                            </select>


                            <label for="subject">Subject:</label>
                            <input type="text" id="subject" placeholder="Enter subject (optional)" name="subject">

                            <label for="message">Message:</label>
                            <textarea id="message" rows="6" placeholder="Type your message..." name="message"></textarea>

                            <label for="attachment">Attachment:</label>
                            <input type="file" id="attachment" multiple name="attachment">
                            <ul id="file-list"></ul>



                            <button class="send-message-btn">Send</button>
                        </div>
                        </form>
                    </div>

                </div>

            </div>

        </main>
        <!-- End Main -->
    </div>
</section>

<!-- ADD NEW MESSAGE -->
<script>
    const newMessageBtn = document.querySelector('.new-message-btn');
    const chatContent = document.querySelector('.chat-content');
    const newMessageContent = document.querySelector('.new-message-content');

    newMessageBtn.addEventListener('click', () => {
        chatContent.style.display = 'none';
        newMessageContent.style.display = 'block';
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const groupSelect = document.getElementById('group-select');
    const peopleSelect = document.getElementById('people-select');
    const attachmentInput = document.getElementById('attachment');
    const fileList = document.getElementById('file-list');
    const sendForm = document.getElementById('sendMessageForm');

    // Dynamic people per subject
    const subjectPeople = {
        @foreach($subjects as $index => $subject)
            "subject_{{ $index }}": [
                @foreach($subject['people'] as $person)
                    { id: "{{ $person['id'] }}", name: "{{ $person['name'] }}" },
                @endforeach
            ],
        @endforeach
    };

    groupSelect.addEventListener('change', function () {
        const selectedGroup = this.value;
        peopleSelect.innerHTML = '<option value="">Select Person</option>';
        if (subjectPeople[selectedGroup]) {
            subjectPeople[selectedGroup].forEach(person => {
                const option = document.createElement('option');
                option.value = person.id;
                option.textContent = person.name;
                peopleSelect.appendChild(option);
            });
        }
    });

    attachmentInput.addEventListener('change', function () {
        fileList.innerHTML = '';
        Array.from(this.files).forEach(file => {
            const li = document.createElement('li');
            li.textContent = file.name;
            fileList.appendChild(li);
        });
    });

    sendForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(sendForm);

        const response = await fetch(sendForm.action, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert('Message sent!');
            sendForm.reset();
            fileList.innerHTML = '';
        } else {
            alert('Failed to send message: ' + result.message);
        }
    });
});
</script>

<!-- SEND MESSAGE -->

<script>
    // Toggle to show New Message view
    document.querySelector('.new-message-btn').addEventListener('click', () => {
        document.querySelector('.chat-content').style.display = 'none';
        document.querySelector('.new-message-content').style.display = 'block';
    });

    // Dynamic recipient list data
    const data = {

        @foreach($subjects as $index => $subject)
        "subject_{{ $index }}": [
            @foreach($subject['people'] as $person)
            { id: "{{ $person['id'] }}", name: "{{ $person['name'] }}" },
            @endforeach
        ],
        @endforeach
    };

    // Populate recipient dropdown based on selected group
    document.getElementById('group-select').addEventListener('change', function () {
        const peopleSelect = document.getElementById('people-select');
        peopleSelect.innerHTML = '<option value="">Select Person</option>';
        const group = this.value;

        if (data[group]) {
            data[group].forEach(person => {
                const option = document.createElement('option');
                option.value = person.id;
                option.textContent = person.name;
                peopleSelect.appendChild(option);
            });
        }
    });

    // Handle attachment file display
    document.getElementById('attachment').addEventListener('change', function () {
        const fileList = document.getElementById('file-list');
        fileList.innerHTML = '';

        Array.from(this.files).forEach(file => {
            const li = document.createElement('li');
            li.textContent = file.name;
            fileList.appendChild(li);
        });
    });

    // Send message
    document.querySelector('.send-message-btn').addEventListener('click', async (e) => {
        e.preventDefault();

        const formData = new FormData();
        formData.append('receiver_id', document.getElementById('people-select').value);
        formData.append('subject', document.getElementById('subject').value);
        formData.append('message', document.getElementById('message').value);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        const files = document.getElementById('attachment').files;
        for (let i = 0; i < files.length; i++) {
            formData.append('attachments[]', files[i]);
        }

        const response = await fetch('/mio/send-message', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        if (result.success) {
            alert('Message sent with attachment!');
            document.querySelector('form').reset();
            document.getElementById('file-list').innerHTML = '';
        } else {
            alert('Failed to send message.');
        }
    });
</script>

<!-- Get MESSAGES -->
<script>
let currentlySelectedContactId = null;

document.querySelectorAll(".contact").forEach(contact => {
    contact.addEventListener("click", function () {
        const clickedContactId = this.dataset.contactId;

        // If clicked again, deselect the contact
        if (currentlySelectedContactId === clickedContactId) {
            currentlySelectedContactId = null;

            // Remove the selected class from the contact
            this.classList.remove('selected');

            // Show the 'no contact selected' message and hide the chat content
            document.querySelector('.no-contact-selected').style.display = 'flex';
            document.querySelector('.chat-content').style.display = 'none';
            return; // Exit here to prevent the rest of the code from running
        }

        // Deselect the previously selected contact
        if (currentlySelectedContactId) {
            document.querySelector(`[data-contact-id="${currentlySelectedContactId}"]`).classList.remove('selected');
        }

        // Update the selected contact ID
        currentlySelectedContactId = clickedContactId;

        // Add the selected class to the clicked contact
        this.classList.add('selected');

        // Hide the 'no contact selected' message
        document.querySelector('.no-contact-selected').style.display = 'none';

        // Show the chat content for the selected contact
        document.querySelector('.chat-content').style.display = 'block';
        // Hide the new message content if it is currently displayed
        document.querySelector('.new-message-content').style.display = 'none';

        const userId = "{{ Session::get('firebase_user')['uid'] }}";
        const receiverId = this.dataset.contactId;
        const receiverName = this.dataset.contactName;
        const receiverRole = this.dataset.contactRole;
        const receiverImage = this.dataset.contactImage;

        const header = document.querySelector(".chat-content .header");
        header.innerHTML = `
            <img src="${receiverImage}" alt="${receiverName}" class="profile-pic-large" />
            <div>
                <h2>${receiverName}</h2>
                <p class="subtitle">${receiverRole}</p>
            </div>
        `;

        const chatSection = document.querySelector(".conversation");
        chatSection.innerHTML = '<p>Loading conversation...</p>';

        fetch(`/mio/student/messages/${userId}/${receiverId}`)
            .then(res => res.json())
            .then(data => {
                chatSection.innerHTML = '';
                data.messages.forEach(msg => {
                    const sender = msg.sender_id === userId ? 'sender' : 'response';
                    const messageHTML = `
                        <div class="message ${sender}">
                            <div class="message-header">
                                <p class="name">${msg.name}</p>
                                <p class="time">${new Date(msg.timestamp * 1000).toLocaleString()}</p>
                            </div>
                            <p class="message-content">${msg.message}</p>

                        </div>`;
                    chatSection.innerHTML += messageHTML;
                });
            })
            .catch(error => {
                chatSection.innerHTML = '<p>Error loading conversation.</p>';
                console.error(error);
            });
    });
});
</script>








