<!-- FIRST PART -->


<div class="events-container">
        <!-- Header -->
        <div class="events-header">
            <span>Upcoming Events</span>
            Stay Connected
        </div>

        <!-- Scrollable Event Cards -->
        <div class="events-slider" id="eventsSlider">
            <div class="event-card">
                <span class="event-icon"></span>
                <span class="event-title">Start of Summer Class</span>
                <p class="event-date">Monday | April 7 | 8:00 AM</p>
            </div>
            <div class="event-card">
                <span class="event-icon"></span>
                <span class="event-title">End of Summer Class</span>
                <p class="event-date">Friday | May 28 | 5:00 PM</p>
            </div>
            <div class="event-card">
                <span class="event-icon"></span>
                <span class="event-title">Start of SY 2024</span>
                <p class="event-date">Monday | June 14 | 7:30 AM</p>
            </div>
            <div class="event-card">
                <span class="event-icon"></span>
                <span class="event-title">Foundation Day</span>
                <p class="event-date">Thursday | July 22 | 10:00 AM</p>
            </div>
        </div>

    </div>


<!-- SECOND PART -->

<div class="second-container">
<div class="video-card">
        <div class="video-wrapper">
        <iframe width="560" height="315" src="https://www.youtube.com/embed/W2HEz9ObCJs?si=M1S1gIYatyRj9eil" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        </div>
        <div class="video-title">Fun Run 2017</div>
        <a href="https://www.youtube.com/@bahmshayno" class="video-button" target="_blank" rel="noopener noreferrer">LEARN MORE</a>
    </div>
</div>


<!-- THIRD PART -->

<div class="third-container">
<div class="grid-container">
        <div class="card">
            <img src="https://static.wixstatic.com/media/03665b_707cce5675f4409183a3843da993f2f3~mv2_d_1984_1488_s_2.jpg/v1/fill/w_650,h_480,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto/69610453_2862068017141502_36002117412265.jpg" alt="Zumba Event">
            <div class="card-content">
                <div class="card-title">ZUMBA For a Cause</div>
                <p class="card-text">
                    With the cooperation of the MOA Dabarkads, the proceeds of the event will help aid the education of indigent deaf students. Held at Mall of Asia, Sunset Park (SM by the Bay), the event was supported by some men from the Philippine National Police and other Zumba groups.
                </p>
            </div>
        </div>

        <div class="card">
            <img src="https://static.wixstatic.com/media/03665b_dcb9b1e44b6745c99cc22a1ff2e12763~mv2.jpg/v1/fill/w_648,h_478,al_c,q_80,usm_0.66_1.00_0.01,enc_avif,quality_auto/IMG_2112_JPG.jpg" alt="Zumba Awareness">
            <div class="card-content">
                <div class="card-title">DEAF AWARENESS WEEK "ZUMBA DAW"</div>
                <p class="card-text">
                    One of the activities in the Deaf Awareness Week is promoting good health by exercising. The Philippine Institute for the Deaf together with MOA Dabarkads collaborated for a Zumba event held in Mall of Asia, with celebrities Allen Cecilio and Marco Mckinley.
                </p>
            </div>
        </div>
    </div>
</div>


<!-- FOURTH PART -->

<div class="fourth-container">
        <img class="bg-image" src="{{ asset('storage/assets/images/6.3 events-posters.png') }}" alt="Students Talking" />
        <!-- Floating Animated Squares -->
        <div class="floating-square square-1"></div>
        <div class="floating-square square-2"></div>
        <div class="floating-square square-3"></div>
        <div class="floating-square square-4"></div>

        <!-- Text Section -->
        <div class="text-section">
            <h3>POSTERS</h3>
            <h2>Special Events</h2>
        </div>
    </div>


<!-- SCRIPT -->

<script>
        const slider = document.getElementById("eventsSlider");
        let isDown = false;
        let startX;
        let scrollLeft;

        slider.addEventListener("mousedown", (e) => {
            isDown = true;
            slider.classList.add("active");
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
        });

        slider.addEventListener("mouseleave", () => {
            isDown = false;
        });

        slider.addEventListener("mouseup", () => {
            isDown = false;
        });

        slider.addEventListener("mousemove", (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 2; // Adjust speed
            slider.scrollLeft = scrollLeft - walk;
        });
    </script>
