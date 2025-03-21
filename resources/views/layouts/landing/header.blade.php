<header>
  <div class="carousel">
    <div class="carousel-inner">
      <!-- Slide 1 -->
      <div class="mySlides carousel-item active">
        <div class="overlay"></div>
        <div class="container">
        <img src="{{ asset('storage/assets/images/1.2.1 home-segria-esguerra-1.png') }}" alt="Logo" class="logo">

          <h1>SEGRIA ESGUERRA MEMORIAL FOUNDATION INC</h1>
          <p class="tagline">
            <span>Self-Reliant Exceptional People in Mainstream Society</span>
          </p>
          <p class="description">
          It utilizes “ORAL” instructional methods for the purpose of developing speech and language skills for deaf children and youth.
          </p>
          <!-- <div class="buttons">
            <a href="#" class="btn inquire">INQUIRE</a>
            <a href="#" class="btn registration">REGISTRATION</a>
          </div> -->
        </div>
      </div>

      <!-- Slide 3 -->
      <div class="mySlides carousel-item">
        <div class="overlay"></div>
        <div class="container">
          <img src="{{ asset('storage/assets/images/1.2.2 home-pid.png') }}" alt="Logo" class="logo">
          <h1>PHILIPPINE INSTITUTE FOR THE DEAF</h1>
          <p class="tagline">
            <span>Where Deaf Children Learn to Speak</span>
          </p>
          <p class="description">
          Compassionate donors sponsor a child who has been pre-screened and evaluated as qualified and deserving. Filipino deaf children have been taught through sign language in the public school system since 1907.          </p>
          <!-- <div class="buttons">
            <a href="#" class="btn inquire">INQUIRE</a>
            <a href="#" class="btn registration">REGISTRATION</a>
          </div> -->
        </div>
      </div>

       <!-- Slide 3 -->
       <div class="mySlides carousel-item">
        <div class="overlay"></div>
        <div class="container">
          <img src="{{ asset('storage/assets/images/1.2.3 home-speechlab.png') }}" alt="Logo" class="logo">
          <h1>SpeechLAB</h1>
          <p class="tagline">
            <span>One-on-One Speech Clinic</span>
          </p>
          <p class="description">
          Speech therapy, aural habilitation, and academic tutoring are available to current or former PID or ISP students, and also to those who are enrolled in other schools. Services are provided through our Speech and Hearing Department.
          </p>
          <!-- <div class="buttons">
            <a href="#" class="btn inquire">INQUIRE</a>
            <a href="#" class="btn registration">REGISTRATION</a>
          </div> -->
        </div>
      </div>

      <!-- Slide 4 -->
      <div class="mySlides carousel-item">
        <div class="overlay"></div>
        <div class="container">
          <img src="{{ asset('storage/assets/images/1.2.4 home-isp.png') }}" alt="Logo" class="logo">
          <h1>INTEGRATED SCHOOL OF THE PHILIPPINES</h1>
          <p class="tagline">
            <span>Where All Learn Together, Helping One Another</span>
          </p>
          <p class="description">
          A model high school where orally trained deaf graduates learn with hearing peers under specially trained SPED teachers. Special Education (SPED) training courses for teachers, parents, and concerned individuals – monthly crash courses offered year-round with certificates.
          </p>
          <!-- <div class="buttons">
            <a href="#" class="btn inquire">INQUIRE</a>
            <a href="#" class="btn registration">REGISTRATION</a>
          </div> -->
        </div>
      </div>



    </div>

    <!-- Dots -->
    <div class="carousel-dots">
      <span class="dot active" onclick="currentSlide(1)"></span>
      <span class="dot" onclick="currentSlide(2)"></span>
      <span class="dot" onclick="currentSlide(3)"></span>
      <span class="dot" onclick="currentSlide(4)"></span>

    </div>
  </div>
</header>

<script>
  let slideIndex = 0;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");

  function showSlides() {
    for (let i = 0; i < slides.length; i++) {
      slides[i].style.display = "none";
      dots[i].classList.remove("active");
    }

    slideIndex++;
    if (slideIndex > slides.length) { slideIndex = 1; }

    slides[slideIndex - 1].style.display = "block";
    dots[slideIndex - 1].classList.add("active");

    setTimeout(showSlides, 15000); // Change slide every 5 seconds
  }

  function currentSlide(n) {
    slideIndex = n - 1;
    showSlides();
  }

  // Start the slideshow automatically
  showSlides();
</script>
