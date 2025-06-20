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
    --dark: var(--secondary-color);
    --dust: var(--primary-color);
    --pumpkin: var(--background-color);
    --pink: var(--secondary-color);
    --violet: var(--secondary-color);
    color: var(--secondary-color);
}

a {
    text-decoration: none;
}

section {
    position: relative;
    padding-top: 150px;
    margin-bottom: 150px;
}

.section-wrapper {
    width: 40%;
}

section h2 {
    font-size: 2.5em;
    font-weight: 700;
    line-height: 1.4;
    margin-bottom: 20px;
}

section h3 {
    font-size: 1em;
}

section h3,
section p {
    margin-bottom: 40px;
}

section p:last-of-type {
    margin-bottom: 30px;
}


/* ----- HEADER ----- */

.about-header {
    position: relative;
    background-color: #1a1a1a;
    color: #ffffff;
    text-align: center;
    padding: 100px 20px;
    overflow: hidden;
    height: 400px;
    margin-bottom: 4rem;
}


/* ---- Content Styling ---- */

.about-content h1 {
    margin-top: 6rem;
    font-size: 32px;
    font-weight: 700;
    color: #ffca28;
    margin-bottom: 20px;
}

.about-content p {
    font-size: 18px;
    line-height: 1.6;
    color: #e0e0e0;
    max-width: 700px;
    margin: 0 auto;
}


/* ---- Decorative Circles ---- */

.circle {
    position: absolute;
    border-radius: 50%;
    opacity: 0.8;
}

.blue-circle {
    width: 150px;
    height: 150px;
    background-color: #0d47a1;
    top: 10%;
    right: -50px;
    animation: moveCircle 6s infinite ease-in-out;
}

.yellow-circle {
    width: 100px;
    height: 100px;
    background-color: #ffc107;
    bottom: 10%;
    left: -30px;
    animation: moveCircle 4s infinite ease-in-out;
}

@keyframes moveCircle {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(20px);
    }
}

@media (max-width: 768px) {
    .about-content h1 {
        font-size: 28px;
    }
    .about-content p {
        font-size: 16px;
    }
    .blue-circle {
        width: 100px;
        height: 100px;
        right: -20px;
    }
    .yellow-circle {
        width: 80px;
        height: 80px;
        left: -20px;
    }
}

@media (max-width: 480px) {
    .about-content h1 {
        font-size: 24px;
    }
    .about-content p {
        font-size: 14px;
    }
    .blue-circle {
        display: none;
    }
    .yellow-circle {
        display: none;
    }
}


/* .header-bg-design {
    position: relative;
    z-index: -1;
    width: 100%;
}

.nav-bg-design {
    position: relative;
    top: 0;
    z-index: -1;
    width: 90%;
    max-width: 1200px;
    margin: 0 auto;
}

.header-circles-dust {
    position: absolute;
    top: -30vw;
    right: 12vw;
    width: 45vw;
    max-width: 540px;
    height: 45vw;
    max-height: 540px;
    border-radius: 50%;
    background-color: var(--violet);
}

.header-circles-big {
    position: absolute;
    top: -25vw;
    right: -35vw;
    width: 70vw;
    max-width: 840px;
    height: 70vw;
    max-height: 840px;
    border-radius: 50%;
    background-color: var(--dark);
    overflow: hidden;
}

.header-circles-big::before {
    content: "";
    position: absolute;
    bottom: 43%;
    right: 68%;
    width: 65%;
    padding-top: 65%;
    border-radius: 50%;
    background-color: var(--pumpkin);
}

.header-circles-big::after {
    content: "";
    position: absolute;
    bottom: 15%;
    right: 30%;
    width: 25%;
    padding-top: 25%;
    border-radius: 50%;
    background-color: var(--dust);
} */


/* ----- FIRST PART ----- */

.info-section {
    margin-top: 3rem;
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
    max-width: 500px;
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


/* ----- SECOND PART ----- */

.mission-section {
    margin-top: 10rem;
    padding: 50px 20px;
    text-align: center;
}

.mission-content {
    max-width: 1200px;
    margin: 0 auto;
}

.highlight {
    color: #ffca28;
    font-size: 16px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.mission-content h2 {
    font-size: 28px;
    font-weight: 800;
    color: #212121;
    margin: 10px 0;
}

.mission-content p {
    font-size: 16px;
    line-height: 1.6;
    color: #616161;
    margin-bottom: 40px;
}

.mission-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 50px;
    justify-content: center;
}

.card {
    background-color: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    padding: 10px 30px;
    padding-top: 40px;
    width: 330px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.card-header {
    display: flex;
    justify-content: flex-start;
    gap: 5px;
    margin-bottom: 20px;
}

.dot {
    width: 20px;
    height: 8px;
    background-color: #ffca28;
    border-radius: 4px;
}

.dot.small {
    width: 12px;
}

.card img {
    width: 80px;
    height: auto;
    margin-bottom: 10px;
}

.card h3 {
    font-size: 18px;
    font-weight: 700;
    color: #212121;
    margin-bottom: 10px;
}

.card p {
    font-size: 14px;
    color: #616161;
    line-height: 1.6;
}


/* ---- Responsive Design ---- */

@media (max-width: 768px) {
    .mission-content h2 {
        font-size: 24px;
    }
    .mission-content p {
        font-size: 14px;
    }
    .card {
        max-width: 100%;
    }
}

@media (max-width: 480px) {
    .highlight {
        font-size: 12px;
    }
    .mission-content h2 {
        font-size: 20px;
    }
    .card h3 {
        font-size: 16px;
    }
    .card p {
        font-size: 12px;
    }
}


/* ----- THIRD PART ------ */

.history-section {
    margin-top: 10rem;
    width: 100%;
}

.history-image {
    background-image: url('https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEhgPywNJaTyDzKB7euwvOjQ7oMLn-KfU4ZWpuTJTqEmUdkvISaqhzLOI43-B6y5DSvj9yp8S07SYf_i4X96F6Pa6Lj8rUG17ALLchXjmXJJ0wC_wB6VoRnkMRc9AML3wJ_NbWPXjwodFk8/s1600/HR_DSC3113.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    height: 400px;
    position: relative;
}

.history-image::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    /* Dark overlay (0.6 = 60% opacity) */
    z-index: 1;
}

.history-image .overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    z-index: 2;
}

.highlight {
    color: #ffca28;
    font-size: 16px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.overlay h2 {
    font-size: 28px;
    font-weight: 800;
    margin: 5px 0;
    color: #ffffff;
}

.history-content {
    padding: 40px 20px;
    background-color: #ffffff;
    max-width: 1200px;
    margin: 0 auto;
    text-align: justify;
}

.history-content p {
    font-size: 16px;
    line-height: 1.8;
    color: #616161;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .overlay h2 {
        font-size: 22px;
    }
    .history-content p {
        font-size: 14px;
        line-height: 1.6;
    }
}

@media (max-width: 480px) {
    .overlay {
        bottom: 10px;
        left: 10px;
    }
    .overlay h2 {
        font-size: 18px;
    }
    .highlight {
        font-size: 12px;
    }
    .history-content {
        padding: 20px 15px;
    }
    .history-content p {
        font-size: 12px;
        line-height: 1.4;
    }
}


/* -----  FOURTH PART  ----- */

.institution-section {
    padding: 40px 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.institution-highlight {
    margin-top: 10rem;
    color: #ffca28;
    font-size: 16px;
    font-weight: 600;
    text-transform: uppercase;
    display: block;
    margin-bottom: 5px;
    text-align: center;
}

.institution-title {
    text-align: center;
    font-size: 28px;
    font-weight: 800;
    color: #212121;
    margin-bottom: 20px;
    margin-bottom: 5rem;
}

.institution-box {
    border: 1px solid #333333;
    color: #212121;
    border-radius: 20px;
}


/* ---- Purpose ---- */

.institution-purpose {
    margin: 20px;
}

.institution-purpose h3 {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 5px;
    color: #2264DC;
}

.institution-purpose p {
    font-size: 16px;
    color: #616161;
    line-height: 1.6;
}


/* ---- Vision and Mission ---- */

.institution-vision-mission {
    display: flex;
    flex-direction: row;
    gap: 20px;
    margin-top: 20px;
    border: 1px solid #e0e0e0;
}

.vision,
.mission {
    color: #616161;
    padding: 20px;
    flex: 1;
}

.vision h3,
.mission h3 {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 10px;
    color: #2b6dd8;
}


/* ---- Core Values ---- */

.institution-core-values {
    color: #ffffff;
    padding: 10px;
    border-bottom: 1px solid #e0e0e0;
}

.institution-core-values h3 {
    margin: 10px;
    color: #2b6dd8;
    font-size: 18px;
    font-weight: 700;
}


/* ---- Values List ---- */

.institution-values {
    margin-top: 20px;
}

.value-item {
    margin-top: 20px;
    padding: 10px 20px 15px 20px;
    border-bottom: 1px solid #e0e0e0;
}

.value-item:last-child {
    border-bottom: none;
}

.value-item strong {
    font-size: 16px;
    font-weight: 700;
    color: #212121;
    display: block;
    margin-bottom: 5px;
}

.value-item p {
    font-size: 14px;
    color: #616161;
}


/* ---- Responsive Styling ---- */

@media (max-width: 768px) {
    .institution-vision-mission {
        flex-direction: column;
    }
    .institution-title {
        font-size: 20px;
    }
    .institution-purpose h3,
    .vision h3,
    .mission h3,
    .institution-core-values {
        font-size: 16px;
    }
    .value-item strong {
        font-size: 14px;
    }
    .value-item p {
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .institution-section {
        padding: 20px 10px;
    }
    .institution-title {
        font-size: 18px;
    }
    .institution-core-values {
        font-size: 14px;
    }
    .value-item strong {
        font-size: 13px;
    }
    .value-item p {
        font-size: 12px;
    }
}


/* -----  ORG CHART   ----- */

#orgchart {
    min-width: 300px;
    max-width: 1200px;
    margin: 1em auto;
}

#orgchart h4 {
    text-transform: none;
    font-size: 14px;
    font-weight: normal;
}

#orgchart p {
    font-size: 13px;
    line-height: 16px;
}


/* ----- MEDIA QUERIES ----- */

@media screen and (max-width: 768px) {
    .blog-slider__img {
        transform: translateY(-50%);
        width: 90%;
    }
}

@media screen and (max-width: 576px) {
    .blog-slider__img {
        width: 95%;
    }
}

@media screen and (max-height: 500px) and (min-width: 992px) {
    .blog-slider__img {
        height: 270px;
    }
}

.blog-slider__content {
    padding-right: 25px;
}

@media screen and (max-width: 768px) {
    .blog-slider__content {
        margin-top: -80px;
        text-align: center;
        padding: 0 30px;
    }
}

@media screen and (max-width: 576px) {
    .blog-slider__content {
        padding: 0;
    }
}

.blog-slider__content>* {
    opacity: 0;
    transform: translateY(25px);
    transition: all 0.4s;
}

.blog-slider__code {
    color: #7b7992;
    margin-bottom: 15px;
    display: block;
    font-weight: 500;
}

.blog-slider__title {
    font-size: 24px;
    font-weight: 700;
    color: #0d0925;
    margin-bottom: 20px;
}

.blog-slider__text {
    color: #4e4a67;
    margin-bottom: 30px;
    line-height: 1.5em;
}

.blog-slider__button {
    display: inline-flex;
    background-image: linear-gradient(147deg, var(--dark-primary-color) 0%, var(--secondary-color) 74%);
    padding: 15px 35px;
    border-radius: 50px;
    color: #fff;
    box-shadow: 0px 14px 80px rgba(252, 56, 56, 0.4);
    text-decoration: none;
    font-weight: 500;
    justify-content: center;
    text-align: center;
    letter-spacing: 1px;
}

@media screen and (max-width: 576px) {
    .blog-slider__button {
        width: 100%;
    }
}

.blog-slider .swiper-container-horizontal>.swiper-pagination-bullets,
.blog-slider .swiper-pagination-custom,
.blog-slider .swiper-pagination-fraction {
    bottom: 10px;
    left: 0;
    width: 100%;
}

.blog-slider__pagination {
    position: absolute;
    z-index: 21;
    right: 20px;
    width: 11px !important;
    text-align: center;
    left: auto !important;
    top: 50%;
    bottom: auto !important;
    transform: translateY(-50%);
}

@media screen and (max-width: 768px) {
    .blog-slider__pagination {
        transform: translateX(-50%);
        left: 50% !important;
        top: 205px;
        width: 100% !important;
        display: flex;
        justify-content: center;
        align-items: center;
    }
}

.blog-slider__pagination.swiper-pagination-bullets .swiper-pagination-bullet {
    margin: 8px 0;
}

@media screen and (max-width: 768px) {
    .blog-slider__pagination.swiper-pagination-bullets .swiper-pagination-bullet {
        margin: 0 5px;
    }
}

.blog-slider__pagination .swiper-pagination-bullet {
    width: 11px;
    height: 11px;
    display: block;
    border-radius: 10px;
    background: #062744;
    opacity: 0.2;
    transition: all 0.3s;
}

.blog-slider__pagination .swiper-pagination-bullet-active {
    opacity: 1;
    background: var(--primary-color);
    height: 30px;
    box-shadow: 0px 0px 20px rgba(252, 56, 56, 0.3);
}

@media screen and (max-width: 768px) {
    .blog-slider__pagination .swiper-pagination-bullet-active {
        height: 11px;
        width: 30px;
    }
}

@media screen and (min-width: 1200px) {
    .header-circles-dust {
        right: 144px;
        top: -360px;
    }
    .header-circles-big {
        right: -420px;
        top: -300px;
    }
    .visa-card {
        right: 60px;
        top: 240px;
    }
    .enim-circles {
        top: -180px;
        left: -240px;
    }
    .profile-card {
        left: 120px;
        top: 120px;
    }
    .tempor-bg {
        top: -96px;
        /* right: -240px; */
        border-top-width: 120px;
        border-bottom-width: 96px;
    }
    .tempor-card {
        right: 120px;
        top: 288px;
    }
    .tempor-card.tempor-master {
        right: 60px;
        top: 216px;
    }
    .tempor-card.tempor-visa {
        right: 0;
        top: 144px;
    }
    .minima-bg-design {
        left: 600px;
    }
}

@media screen and (max-width: 1024px) {
    section {
        position: relative;
        padding-top: 120px;
        margin-bottom: 60px;
    }
    .section-wrapper {
        width: 50%;
    }
    section h2 {
        font-size: 2.2em;
    }
    .intro h1 {
        font-size: 2.6em;
    }
    .visa-card {
        right: 2vw;
        width: 26vw;
        height: 17vw;
        border-radius: 6px;
    }
    .visa-card .card-inner::after {
        font-size: 0.8em;
    }
    .partners-row {
        width: 90%;
    }
    .enim-bg-design {
        left: -5vw;
    }
    .profile-card {
        top: 15vw;
        width: 32vw;
        height: 55vw;
    }
    .profile-content {
        margin: 5% auto;
    }
    .profile-card .fa-arrow-left {
        font-size: 1.2em;
    }
    .user-row {
        margin-bottom: 6%;
    }
    .username {
        font-size: 1.4em;
    }
    .bg-search-field {
        padding: 3% 4%;
        margin-bottom: 8%;
    }
    .choose-card {
        font-size: 0.7em;
    }
    .balance {
        font-size: 1.1em;
        line-height: 1.7;
    }
    .master-card .card-inner::after {
        font-size: 0.7em;
    }
    .tempor-bg {
        left: 55%;
    }
    .tempor-card .card-inner::after {
        font-size: 0.6em;
    }
    #video {
        width: 45%;
    }
    .dius-bg-design {
        top: 15vw;
        left: -5vw;
    }
}

@media screen and (max-width: 840px) {
    .section-wrapper {
        width: 100%;
    }
    #nav-bar a {
        color: var(--dark);
    }
    .sign-up {
        border-color: var(--dark);
    }
    .header-circles-dust {
        top: 25vw;
        right: -30vw;
        width: 45vw;
        height: 45vw;
        background-color: var(--violet);
    }
    .header-circles-big {
        top: -25vw;
        right: -35vw;
        background-color: #faf8f8;
    }
    .header-circles-big::before {
        bottom: -36%;
        right: 6.4%;
        width: 65%;
        padding-top: 65%;
        background-color: var(--pink);
    }
    .header-circles-big::after {
        bottom: 50%;
        right: 60%;
        width: 25%;
        padding-top: 25%;
        background-color: var(--dust);
    }
    .visa-card {
        visibility: hidden;
    }
    .intro h1 {
        width: 80%;
    }
    #form {
        width: 100%;
    }
    #submit {
        border-radius: 0 24px 24px 0;
        height: 48px;
        padding: 0 30px;
    }
    #enim {
        margin-top: 360px;
        padding-top: 180px;
    }
    .enim-circles {
        top: 0;
        left: 0;
        transform: translate(-50%, -50%);
        min-width: 500px;
        min-height: 500px;
    }
    .profile-card {
        left: 50vw;
        top: -300px;
        transform: translateX(-50%);
        min-width: 270px;
        min-height: 460px;
    }
    #tempor {
        margin-top: 230px;
        padding-top: 150px;
    }
    .tempor-bg {
        left: 30%;
        top: -400px;
        min-height: 400px;
        border-right: 80vw solid var(--dark);
    }
    .minima-bg-design {
        bottom: 45%;
        left: 50vw;
        height: 95vw;
        min-height: 800px;
    }
    .tempor-card {
        right: 50%;
        top: -190px;
        transform: translateX(50%);
        width: 24vw;
        min-width: 230px;
        height: 15vw;
        min-height: 140px;
    }
    .tempor-card.tempor-master {
        right: 44%;
        top: -190px;
        transform: translate(50%, -38%);
    }
    .tempor-card.tempor-visa {
        right: 38%;
        top: -190px;
        transform: translate(50%, -68%);
    }
    .tempor-card .card-inner::after {
        font-size: 0.7em;
    }
    #minima {
        flex-direction: column;
    }
    #video {
        width: 100%;
        margin-bottom: 50px;
    }
    .dius-bg-design {
        left: 50%;
        width: 140vw;
        height: 60vw;
        min-height: 500px;
        transform: translate(-50%, -10%) perspective(300px) rotateY(-10deg) skewX(-15deg);
    }
    .dius-bg-design::before {
        visibility: hidden;
    }
    .dius-bg-design::after {
        visibility: hidden;
    }
}

@media screen and (max-width: 720px) {
    .nav-container {
        width: 90%;
        height: 150px;
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        padding: 20px 0% 30px;
    }
    #nav-bar {
        width: 100%;
        max-width: 100%;
    }
}

@media screen and (max-width: 550px) {
    #header {
        margin-bottom: 100px;
    }
    .nav-container {
        height: unset;
        justify-content: unset;
    }
    #header-img {
        width: 120px;
        align-self: flex-start;
    }
    #nav-bar {
        height: 200px;
    }
    #nav-bar ul {
        height: 100%;
        flex-direction: column;
        justify-content: space-around;
    }
    .sign-up {
        padding: 8px 35px;
    }
    .intro {
        padding-top: 300px;
    }
    .intro h1 {
        width: 100%;
        font-size: 2em;
    }
    .intro p {
        width: 100%;
    }
    #form {
        flex-direction: column;
        align-items: center;
    }
    #submit {
        border-radius: 24px;
        font-size: 1rem;
    }
    #email {
        border-right: 1px solid var(--dust);
        border-radius: 24px;
        margin-bottom: 20px;
    }
    .partners-row {
        width: 100%;
    }
    .mastercard {
        height: 40px;
    }
    .visa {
        height: 25px;
    }
    .paypal {
        height: 25px;
    }
    section h2 {
        font-size: 1.8em;
    }
    #enim {
        padding-top: 280px;
    }
    .profile-card {
        top: -250px;
    }
    #tempor {
        margin-top: 100px;
        padding-top: 280px;
    }
}

</style>