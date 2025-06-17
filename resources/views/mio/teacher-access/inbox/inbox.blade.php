<section class="home-section">
    <div class="text">Inbox</div>
    <button class="new-message-btn">+ New Message</button>
    <div class="grid-container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

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
                            <div class="contact {{ $contact['has_unread'] ? 'has-unread' : '' }}" 
                                data-contact-id="{{ $contact['id'] }}"
                                data-contact-name="{{ $contact['name'] }}"
                                data-contact-role="{{ $contact['role'] }}"
                                data-contact-image="{{ $contact['profile_pic'] }}">
                                
                                <img src="{{ $contact['profile_pic'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($contact['name']) }}" class="profile-pic" />
                                
                                <div class="contact-info">
                                    <p class="name" style="{{ $contact['has_unread'] ? 'font-weight: bold;' : '' }}">
                                        {{ $contact['name'] }}
                                        @if($contact['has_unread'])
                                            <span class="red-dot"></span>
                                        @endif
                                    </p>
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
                                <h2></h2>
                                <p class="subtitle"></p>
                            </div>


                        <!-- Conversation -->
                        <div class="conversation ">
                            <div class="message sender">

                            </div>

                            <div class="message response">

                            </div>
                    </div>
                    </div>
                    
                    <!-- MESSAGE LOADER! -->
                     <div class="line-wobble" id="messageLoader" style="display: none;"></div>

                    <!-- Change the chat-content when clicking to add message -->
                     <div class="chat-content new-message-content" style="display: none;">
                        <div class="header">
                            <h2>New Message</h2>
                            <p class="subtitle">Compose a new message</p>
                        </div>



                        <form id="sendMessageForm" action="{{ route('mio.teacher-message-send') }}" method="post" enctype="multipart/form-data">

                            @csrf
                            <meta name="csrf-token" content="{{ csrf_token() }}">
                            <div class="compose-form">
                            <!-- Dropdown for To: field -->
                            <label for="recipient">To:</label>

                            <!-- Fetch the subjects and sections -->
                                <select id="group-select">
                                    <option value="">Select Group</option>
                                    @foreach($subjects as $subject)
                                        <option value="subject_{{ $loop->index }}">{{ $subject['subject_name'] }}</option>
                                    @endforeach

                                </select>

                                <select id="people-select" name="receiver_id">
                                    <option value="">Select Person</option>
                                </select>

                            <label for="message">Message:</label>
                            <textarea id="message" rows="6" placeholder="Type your message..." name="message"></textarea>

                            <label for="attachment">Attachment:</label>
                            <input type="file" id="attachment" multiple name="attachments[]">

                            <ul id="file-list"></ul>



                            <button type="submit" class="send-message-btn">Send</button>
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
    const noContactSelected = document.querySelector('.no-contact-selected');

    let isComposingNewMessage = false;

    newMessageBtn.addEventListener('click', () => {
        isComposingNewMessage = !isComposingNewMessage;

        if (isComposingNewMessage) {
            // Show new message form
            chatContent.style.display = 'none';
            newMessageContent.style.display = 'block';
            noContactSelected.style.display = 'none';
            newMessageBtn.textContent = 'x Close Message';
        } else {
            // Close new message form
            newMessageContent.style.display = 'none';
            chatContent.style.display = 'none'; // Keep chat content hidden
            noContactSelected.style.display = 'flex'; // Show "no contact selected"
            newMessageBtn.textContent = '+ New Message';
        }
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

        $('#sendMessageForm').on('submit', function(e) {
        e.preventDefault();

        // Hide form, show loader
        document.querySelector('.new-message-content').style.display = 'none';
        document.getElementById('messageLoader').style.display = 'block';

        let formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            setTimeout(() => {
                window.location.reload(); // refresh page after short delay
            }, 1000); // 1s delay so the loader appears briefly
        })
        .catch(err => {
            console.error(err);
            window.location.reload(); // fallback refresh
        });
    });

          const messageContainer = document.getElementById('message-container');
                if (messageContainer) {
                    messageContainer.scrollTop = messageContainer.scrollHeight;
                }        if (messageContainer) {
                    messageContainer.scrollTop = messageContainer.scrollHeight;
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
        const receiverImageRaw = this.dataset.contactImage;

        // Fallback logic inline using a conditional expression
        const receiverImage = (!receiverImageRaw || receiverImageRaw === "null" || receiverImageRaw === "undefined")
            ? `https://ui-avatars.com/api/?name=${encodeURIComponent(receiverName)}`
            : receiverImageRaw;


        const header = document.querySelector(".chat-content .header");
        header.innerHTML = `
            <img src="${receiverImage}" alt="${receiverName}" class="profile-pic-large" />
            <div>
                <h2>${receiverName}</h2>
                <p class="subtitle">${receiverRole}</p>
            </div>
        `;

             if (!receiverImage) {
            receiverImage = `https://ui-avatars.com/api/?name=${encodeURIComponent(receiverName)}`;
        }

        const chatSection = document.querySelector(".conversation");
        chatSection.innerHTML = '<p>Loading conversation...</p>';

        fetch(`/mio/teacher/messages/${userId}/${receiverId}`)
            .then(res => res.json())
            .then(data => {
                chatSection.innerHTML = '';

            // Remove bold & red dot
            const contactEl = document.querySelector(`[data-contact-id="${receiverId}"]`);
            const nameEl = contactEl.querySelector(".name");
            nameEl.style.fontWeight = 'normal';
            const redDot = nameEl.querySelector(".red-dot");
            if (redDot) redDot.remove();
            contactEl.classList.remove('has-unread');

            // Mark messages as read in Firebase
            const threadId = [userId, receiverId].sort().join('_');
            const messagesRef = firebase.database().ref(`messages/${threadId}`);

            messagesRef.once('value').then(snapshot => {
                snapshot.forEach(childSnapshot => {
                    const msgKey = childSnapshot.key;
                    const msgData = childSnapshot.val();

                    // Only update if this message was sent to the current user and is unread
                    if (msgData.receiver_id === userId && !msgData.read) {
                        messagesRef.child(msgKey).update({ read: true });
                    }
                });
            });



                // Sort and display messages
                data.messages.sort((a, b) => a.timestamp - b.timestamp);
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

<!-- <div class="actions">
    <button class="action-btn">✎</button>
    <button class="action-btn">🗑</button>
</div> -->


<!-- Firebase v8 (Compatible with your code) -->
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>
<script>
  const firebaseConfig = {
    apiKey: "AIzaSyBfzT0dZZAcgsc0CGKugR2H3jEB_G6jG50",
    authDomain: "miolms.firebaseapp.com",
    databaseURL: "https://miolms-default-rtdb.firebaseio.com",
    projectId: "miolms",
    storageBucket: "miolms.firebasestorage.app",
    messagingSenderId: "720846720525",
    appId: "1:720846720525:web:65747f3c00aef3fbeb4f44",
    measurementId: "G-2RXBR538B6"
  };

  firebase.initializeApp(firebaseConfig);
</script>







