document.addEventListener("DOMContentLoaded", function () {

    let slides = [
        "assets/images/slider/slide1.png",
        "assets/images/slider/slide2.png",
        "assets/images/slider/slide3.png",
        "assets/images/slider/slide4.png"
    ];

    let index = 0;
    let slideImg = document.getElementById("heroSlide");

    if (!slideImg) return; // safety check

    setInterval(() => {
        index = (index + 1) % slides.length;
        slideImg.src = slides[index];
    }, 10000); // 10 seconds

});
