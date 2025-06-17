<style>
    /* ----- HEADER ----- */

.campus-section {
    position: relative;
    background-color: #1a1a1a;
    color: #ffffff;
    text-align: center;
    padding: 100px 20px;
    overflow: hidden;
    height: 400px;
    margin-bottom: 15rem;
}

.campus-content h2 {
    margin-top: 6rem;
    font-size: 32px;
    font-weight: 700;
    color: #ffca28;
    margin-bottom: 20px;
}

.campus-content p {
    font-size: 18px;
    line-height: 1.6;
    color: #e0e0e0;
    max-width: 700px;
    margin: 0 auto;
}

.circle {
    position: absolute;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    opacity: 0.6;
    animation-duration: 8s;
    animation-timing-function: ease-in-out;
    animation-iteration-count: infinite;
}

.circle-left {
    background: linear-gradient(135deg, #0d47a1, #1976d2);
    left: -80px;
    top: 20%;
    animation-name: moveLeft;
}

.circle-right {
    background: linear-gradient(135deg, #ff6f00, #ffca28);
    right: -80px;
    bottom: 20%;
    animation-name: moveRight;
}

@keyframes moveLeft {
    0%,
    100% {
        transform: translateY(0) translateX(0);
    }
    50% {
        transform: translateY(-20px) translateX(20px);
    }
}

@keyframes moveRight {
    0%,
    100% {
        transform: translateY(0) translateX(0);
    }
    50% {
        transform: translateY(20px) translateX(-20px);
    }
}

@media (max-width: 768px) {
    .campus-content h2 {
        font-size: 28px;
    }
    .campus-content p {
        font-size: 16px;
    }
    .circle {
        width: 100px;
        height: 100px;
    }
}

@media (max-width: 480px) {
    .campus-content h2 {
        font-size: 24px;
    }
    .campus-content p {
        font-size: 14px;
    }
    .circle {
        width: 80px;
        height: 80px;
    }
}


/* OTHER */

.paralax {
    background-attachment: fixed;
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
}

.title {
    /* BG image set on div */
    background-color: #112233;
    color: white;
    height: 100vh;
    position: relative;
    padding: 0;
}

.title.letterbox {
    height: 33vh;
    display: flex;
    align-items: center;
}

.title.letterbox h1 {
    padding: 0;
    bottom: unset;
    margin: auto auto;
}

.title.square {
    width: 300px;
    height: 300px;
    flex: auto;
    margin: 1vh;
}

.gallery {
    display: flex;
    width: 100%;
    margin: auto;
    max-width: 1110px;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
}

.title h1 {
    margin: auto 0;
    position: absolute;
    bottom: 0;
    font-weight: bold;
    font-size: 4em;
    width: 100%;
    text-align: center;
    padding-bottom: 50vh;
    z-index: 1;
}

.title em {
    margin: auto 0;
    position: absolute;
    top: 0;
    font-size: 1em;
    width: 100%;
    text-align: center;
    padding-top: 5vh;
    z-index: 1;
}

.title:after {
    position: absolute;
    content: "";
    background: #00000022;
    width: 100%;
    height: 100%;
}

.campus {
    max-width: 900px;
    margin: auto;
    padding: 5% 15%;
}

.campus-t {
    margin: 10em;
}

</style>