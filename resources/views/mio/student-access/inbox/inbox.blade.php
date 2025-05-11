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


                        <div class="contacts">

                        <div class="contact selected">
                                <img src="https://2.img-dpreview.com/files/p/E~C1000x0S4000x4000T1200x1200~articles/3925134721/0266554465.jpeg" alt="Leo Kenter" class="profile-pic" />
                                <div class="contact-info">
                                    <p class="name">Leo Kenter</p>
                                    <!-- <p class="preview">Don't forget! Your next speech development session is scheduled...</p> -->
                                </div>
                                <span class="time">9:00 AM</span>
                            </div>
                            <div class="contact">
                                <img src="https://photographylife.com/cdn-cgi/imagedelivery/GrQZt6ZFhE4jsKqjDEtqRA/photographylife.com/2023/05/nikon-z8-00017.jpg/w=300,h=200" alt="Gretchen Kenter" class="profile-pic" />
                                <div class="contact-info">
                                    <p class="name">Gretchen Kenter</p>

                                </div>
                                <span class="time">9:00 AM</span>
                            </div>
                            <div class="contact">
                                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSYVx6CB56pxO8gwlzLLOkV8fPN0jfF3T_98w&s" alt="Alfredo Press" class="profile-pic" />
                                <div class="contact-info">
                                    <p class="name">Alfredo Press</p>

                                </div>
                                <span class="time">9:00 AM</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Chat Messages -->
                    <div class="chat-content">
                        <div class="header">
                            <img src="https://2.img-dpreview.com/files/p/E~C1000x0S4000x4000T1200x1200~articles/3925134721/0266554465.jpeg" alt="Leo Kenter" class="profile-pic-large" />
                            <div>
                                <h2>Leo Kenter</h2>
                                <p class="subtitle">Speech Development</p>
                            </div>
                        </div>

                        <!-- Conversation -->
                    <div class="conversation">
                        <div class="message sender">
                            <div class="message-header">
                                <img src="profile3.jpg" alt="Julie Esguerra" class="profile-pic" />
                                <div>
                                    <p class="name">Julie Esguerra</p>
                                    <p class="time">Jan 20, 2025 at 9:00 AM</p>
                                </div>
                            </div>
                            <p class="message-content">
                                I have been encountering a problem about my schedule page. I can't seem to view or update my class schedule properly...
                            </p>
                            <div class="actions">
                                <button class="action-btn">↩</button>
                                <button class="action-btn">🔁</button>
                                <button class="action-btn">🗑️</button>
                            </div>
                        </div>

                        <div class="message response">
                            <div class="message-header">
                                <img src="profile1.jpg" alt="Leo Kenter" class="profile-pic" />
                                <div>
                                    <p class="name">Leo Kenter</p>
                                    <p class="time">Jan 21, 2025 at 9:00 AM</p>
                                </div>
                            </div>
                            <p class="message-content">
                                Thank you for reporting the issue with the Schedule page on the school dashboard. Our team has investigated and resolved the problem...
                            </p>
                            <div class="actions">
                                <button class="action-btn">↩</button>
                                <button class="action-btn">🔁</button>
                                <button class="action-btn">🗑️</button>
                            </div>
                        </div>

                        <div class="message sender">
                            <div class="message-header">
                                <img src="profile3.jpg" alt="Julie Esguerra" class="profile-pic" />
                                <div>
                                    <p class="name">Julie Esguerra</p>
                                    <p class="time">Jan 20, 2025 at 9:00 AM</p>
                                </div>
                            </div>
                            <p class="message-content">
                                I have been encountering a problem about my schedule page. I can't seem to view or update my class schedule properly...
                            </p>
                            <div class="actions">
                                <button class="action-btn">↩</button>
                                <button class="action-btn">🔁</button>
                                <button class="action-btn">🗑️</button>
                            </div>
                        </div>

                        <div class="message sender">
                            <div class="message-header">
                                <img src="profile3.jpg" alt="Julie Esguerra" class="profile-pic" />
                                <div>
                                    <p class="name">Julie Esguerra</p>
                                    <p class="time">Jan 20, 2025 at 9:00 AM</p>
                                </div>
                            </div>
                            <p class="message-content">
                                I have been encountering a problem about my schedule page. I can't seem to view or update my class schedule properly...
                            </p>
                            <div class="actions">
                                <button class="action-btn">↩</button>
                                <button class="action-btn">🔁</button>
                                <button class="action-btn">🗑️</button>
                            </div>
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
                                @foreach($sections as $section)
                                    <option value="section_{{ $loop->index }}">{{ $section['section_name'] }}</option>
                                @endforeach

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
    const data = {
        @foreach($sections as $index => $section)
        "section_{{ $index }}": [
            @foreach($section['people'] as $person)
            { id: "{{ $person['id'] }}", name: "{{ $person['name'] }}" },
            @endforeach
        ],
        @endforeach

        @foreach($subjects as $index => $subject)
        "subject_{{ $index }}": [
            @foreach($subject['people'] as $person)
            { id: "{{ $person['id'] }}", name: "{{ $person['name'] }}" },
            @endforeach
        ],
        @endforeach
    };

    document.getElementById('group-select').addEventListener('change', function () {
        const group = this.value;
        const peopleSelect = document.getElementById('people-select');
        peopleSelect.innerHTML = '<option value="">Select Person</option>';

        if (data[group]) {
            data[group].forEach(person => {
                const option = document.createElement('option');
                option.value = person.id;
                option.textContent = person.name;
                peopleSelect.appendChild(option);
            });
        }
    });
</script>

<script>
    // Toggle to show New Message view
    document.querySelector('.new-message-btn').addEventListener('click', () => {
        document.querySelector('.chat-content').style.display = 'none';
        document.querySelector('.new-message-content').style.display = 'block';
    });

    // Dynamic recipient list data
    const data = {
        @foreach($sections as $index => $section)
        "section_{{ $index }}": [
            @foreach($section['people'] as $person)
            { id: "{{ $person['id'] }}", name: "{{ $person['name'] }}" },
            @endforeach
        ],
        @endforeach

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






