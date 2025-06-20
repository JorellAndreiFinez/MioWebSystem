<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
.montserrat-font {
    font-family: "Montserrat", sans-serif;
    font-optical-sizing: auto;
    font-weight: 100;
    font-style: normal;
}

body {
    font-size: 14px;
    overflow-x: hidden;
    font-family: "Montserrat", serif;
}


/* ---- HEADER */


/* Header and Carousel */

header {
    position: relative;
    width: 100%;
    height: 100vh;
}

.carousel {
    position: relative;
    overflow: hidden;
    width: 100%;
    max-width: 100vw;
}

.carousel-inner {
    display: flex;
    transition: transform 1s ease-in-out;
    width: 200%;
}

.carousel-item {
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 100vw;
    height: 100vh;
    text-align: center;
    padding: 5vw;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}


/* Overlay */

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
}


/* Content Styling */

.container {
    position: relative;
    color: white;
    text-align: center;
    padding: 10px;
    max-width: 90%;
}

.logo {
    width: 100px;
    margin-bottom: 2rem;
    margin-top: 10rem;
}

.carousel h1 {
    font-size: clamp(3rem, 5vw, 4rem);
    font-weight: bold;
    margin-bottom: 10px;
}

.tagline span {
    font-style: italic;
    font-size: clamp(1.9rem, 3vw, 2rem);
    color: #ffd700;
    font-weight: 700;
}

.description {
    font-size: clamp(1.5rem, 2vw, 1.5rem);
    max-width: 90%;
    margin: auto;
}


/* Buttons */

.buttons {
    display: flex;
    justify-content: center;
    align-items: center;
    /* Center buttons */
    gap: 10px;
    margin-top: 3rem;
}

.btn {
    padding: 10px 20px;
    font-size: 14px;
    border-radius: 5px;
    text-decoration: none;
    color: white;
    font-weight: 600;
    transition: 0.3s ease;
}

.inquire {
    background-color: var(--secondary-color);
}

.registration {
    background-color: white;
}

.btn:hover {
    opacity: 0.8;
}


/* Controls */

.carousel-controls {
    position: absolute;
    top: 50%;
    width: 100%;
    display: flex;
    justify-content: space-between;
    z-index: 10;
}

.carousel-controls button {
    background: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    padding: 10px;
    font-size: 24px;
    cursor: pointer;
    transition: 0.3s ease;
}

.carousel-controls button:hover {
    background: rgba(0, 0, 0, 0.7);
}


/* Next & previous buttons */

.prev,
.next {
    cursor: pointer;
    position: absolute;
    top: 50%;
    width: auto;
    margin-top: -22px;
    padding: 16px;
    color: white;
    font-weight: bold;
    font-size: 18px;
    transition: 0.6s ease;
    border-radius: 0 3px 3px 0;
    user-select: none;
}


/* Position the "next button" to the right */

.next {
    right: 0;
    border-radius: 3px 0 0 3px;
}


/* On hover, add a black background color with a little bit see-through */

.prev:hover,
.next:hover {
    background-color: rgba(0, 0, 0, 0.8);
}


/* Dots */

.carousel-dots {
    position: absolute;
    bottom: 20px;
    width: 100%;
    display: flex;
    justify-content: center;
}

.dot {
    height: 12px;
    width: 12px;
    margin: 0 5px;
    background-color: #bbb;
    border-radius: 50%;
    display: inline-block;
    transition: background-color 0.6s ease;
}

.dot.active {
    background-color: #007bff;
}


/* Mobile-Friendly */

@media (max-width: 768px) {
    .container {
        padding: 10px;
    }
    h1 {
        font-size: 2rem;
    }
    .description {
        max-width: 95%;
    }
    .buttons {
        flex-direction: column;
        gap: 5px;
    }
    .btn {
        padding: 8px 16px;
    }
}


/* ----- FIRST PART ----- */

.info-section {
    margin-top: 7rem;
    width: 100%;
    padding: 50px;
    display: flex;
    justify-content: center;
}

.info-content {
    display: flex;
    max-width: 1150px;
    align-items: center;
    gap: 10px;
}

.info-image {
    position: relative;
    flex: 1;
    max-width: 400px;
}

.info-image img {
    width: 90%;
    height: auto;
    border-radius: 12px;
}

.info-text {
    flex: 1;
}

.tagline {
    color: #ffca28;
    font-size: 16px;
    font-weight: 600;
    text-transform: uppercase;
}

.info-text h1 {
    font-size: 28px;
    font-weight: 800;
    color: #212121;
    margin: 10px 0;
}

.info-text p {
    font-size: 16px;
    line-height: 1.6;
    color: #616161;
    margin-bottom: 20px;
}

.about-btn {
    display: inline-block;
    background-color: #2b6dd8;
    color: #ffffff;
    padding: 15px 30px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    text-align: center;
    transition: background 0.3s ease;
    margin-top: 15px;
}

.about-btn:hover {
    background-color: #3e3e3e;
    color: #ffffff;
    text-decoration: none;
}

@media (max-width: 768px) {
    .info-content {
        flex-direction: column;
        text-align: center;
    }
    .info-image img {
        width: 100%;
        height: auto;
    }
    .blue-overlay {
        width: 40px;
        height: 40px;
        top: -10px;
        left: -10px;
    }
}

@media (max-width: 480px) {
    .info-text h1 {
        font-size: 24px;
    }
    .info-text p {
        font-size: 14px;
    }
    .btn {
        padding: 10px 20px;
        font-size: 14px;
    }
}


/* ---- SECOND PART */

.second-container {
    text-align: center;
    padding: 40px 20px;
    background-color: #ffffff;
    margin: 10rem auto 0;
    /* Consistent with first-container */
    max-width: 1200px;
}

.second-container h5 {
    color: #ffcc00;
    font-size: 16px;
    margin-bottom: 10px;
    text-transform: uppercase;
    font-weight: 600;
}

.second-container h2 {
    color: #000;
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 40px;
}

.reasons {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 80px;
}

.reason {
    flex: 1;
    max-width: 330px;
    text-align: center;
}

.reason .icon img {
    width: 90px;
    height: 80px;
    padding: 10px;
    margin-bottom: 15px;
}

.reason h3 {
    font-size: 18px;
    color: #000;
    margin-bottom: 10px;
    font-weight: 500;
}

.reason p {
    font-size: 16px;
    color: #555;
}


/* Responsive Design */

@media (max-width: 768px) {
    .reasons {
        flex-direction: column;
        align-items: center;
    }
}


/* ---- THIRD PART */


/* Guide Section Styling */

.third-container {
    padding: 40px 20px;
    background-color: #ffffff;
    margin: 10rem auto 10rem;
    max-width: 1200px;
}

.third-div {
    display: inline-flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 2.5rem;
    margin: 0 auto;
}


/* Left Side */

.guide-left {
    flex: 1;
    min-width: 340px;
    margin-right: 40px;
}

.guide-image {
    width: 100%;
    height: auto;
    border-radius: 8px;
}

.guide-title {
    margin-top: 20px;
}

.process-label {
    color: #ffcc00;
    font-size: 16px;
    font-weight: 600;
    text-transform: uppercase;
}

.guide-title h2 {
    font-size: 28px;
    font-weight: 800;
    color: #000;
}


/* Right Side */

.guide-right {
    flex: 1;
    min-width: 100;
    margin-top: 20px;
}

.step {
    display: flex;
    align-items: center;
    margin-top: 10px;
    margin-bottom: 10px;
}

.step-icon img {
    width: 65px;
    height: 65px;
    background-color: #ffffff;
    border: 1px solid #e2e2e2;
    padding: 15px;
    border-radius: 8px;
    margin-right: 15px;
}

.step-content h3 {
    font-size: 18px;
    font-weight: 600;
    color: #000;
    margin-bottom: 5px;
}

.step-content p {
    font-size: 16px;
    color: #555;
    margin: 0;
}


/* Button */

.guide-btn {
    display: inline-block;
    background-color: #2b6dd8;
    color: #ffffff;
    padding: 15px 30px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    text-align: center;
    transition: background 0.3s ease;
    margin-top: 15px;
}

.guide-btn:hover {
    background-color: #3e3e3e;
    color: #ffffff;
    text-decoration: none;
}


/* Responsive Design */

@media (max-width: 768px) {
    .third-div {
        flex-direction: column;
    }
    .guide-left {
        min-width: 340px;
        margin-right: 0;
        text-align: center;
    }
    .step {
        flex-direction: row;
        align-items: flex-start;
    }
    .step-icon img {
        margin-bottom: 10px;
    }
}


/* --- FOURTH MAIN CONTENT --- */

.fourth-container {
    padding: 9.3rem 2rem;
    background-color: #fff7dd;
}

.fourth-div {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 2.5rem;
    max-width: 1150px;
    margin: 0 auto;
}


/* --- Left Side (Text) --- */

.achievements-text {
    flex: 1 1 300px;
    max-width: 600px;
    color: #131313;
}

.achievements-label {
    font-size: 16px;
    color: #ffc107;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 0.75rem;
    display: inline-block;
}

.achievements-text h2 {
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 1.25rem;
}

.achievements-text p {
    font-size: 16;
    color: #000;
    line-height: 1.6;
    margin-bottom: 1rem;
}


/* --- Updates Button --- */

.updates-btn {
    display: inline-block;
    background-color: #2b6dd8;
    color: #ffffff;
    padding: 15px 30px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    text-align: center;
    transition: background 0.3s ease;
    margin-top: 15px;
}

.updates-btn:hover {
    background-color: #3e3e3e;
    color: #ffffff;
    text-decoration: none;
}


/* --- Right Side (Image) --- */

.achievements-image {
    flex: 1 1 300px;
    max-width: 600px;
    text-align: center;
}

.achievements-image img {
    width: 100%;
    max-width: 500px;
    height: auto;
    border-radius: 0.5rem;
}


/* --- Responsive Adjustments --- */

@media (max-width: 768px) {
    .fourth-div {
        flex-direction: column;
        padding: 0;
    }
    .achievements-text,
    .achievements-image {
        max-width: 100%;
        text-align: center;
    }
}


/* Responsive Design */

@media (max-width: 768px) {
    .fourth-div {
        flex-direction: center;
        text-align: left;
    }
    .achievements-image img {
        max-width: 100%;
        height: auto;
    }
}


/* ---- FIFTH MAIN CONTENT */

.fifth-container {
    margin-top: 10rem;
    margin-bottom: 10rem;
    padding: 40px 20px;
    background-color: #ffffff;
    text-align: center;
}

.programs-header {
    margin-bottom: 30px;
}

.programs-label {
    font-size: 16px;
    color: #ffb400;
    font-weight: 600;
    text-transform: uppercase;
    display: block;
}

.programs-header h2 {
    font-size: 28px;
    font-weight: 800;
    color: #1c1c1c;
}


/* Programs Container */

.programs-container {
    display: flex;
    flex-wrap: wrap;
    gap: 50px;
    justify-content: center;
}


/* Program Card */

.program-card {
    background-color: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 20px;
    padding-top: 40px;
    width: 250px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.program-card:hover {
    transform: translateY(-5px);
}


/* Program Image */

.program-image {
    width: 80px;
    height: 80px;
    margin: 0 auto 15px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid #2b6dd8;
}

.program-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}


/* Program Title */

.program-card h3 {
    font-size: 16px;
    color: #1c1c1c;
    font-weight: bold;
    margin-top: 10px;
    line-height: 180%;
}


/* Responsive Styling */

@media (max-width: 768px) {
    .programs-container {
        flex-direction: row;
        gap: 15px;
    }
    .program-card {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .program-card {
        width: 100%;
    }
}

</style>