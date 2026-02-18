document.addEventListener("DOMContentLoaded", function() {
    let slides = document.querySelectorAll('.hero-bg');
    
    if (!slides || slides.length === 0) {
        return; // Exit if no slides found
    }
    
    let index = 0;

    function showSlide() {
        if (slides.length === 0) return;
        slides.forEach(s => {
            if (s && s.classList) {
                s.classList.remove('active');
            }
        });
        if (slides[index] && slides[index].classList) {
            slides[index].classList.add('active');
        }
        index = (index + 1) % slides.length;
    }

    setInterval(showSlide, 5000);
});
