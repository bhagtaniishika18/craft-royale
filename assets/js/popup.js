function initNewsletterPopup() {
    const overlay = document.getElementById("subscribeOverlay");
    const form = document.getElementById("subscribeForm");
    const dontShow = document.getElementById("dontShow");

    if (!overlay) {
        console.warn("Newsletter Popup: Overlay element not found in DOM.");
        return;
    }

    // Confetti function
    function triggerConfetti() {
        if (typeof confetti !== 'undefined') {
            const duration = 3000;
            const animationEnd = Date.now() + duration;
            const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 11000 };

            function randomInRange(min, max) {
                return Math.random() * (max - min) + min;
            }

            const interval = setInterval(function () {
                const timeLeft = animationEnd - Date.now();

                if (timeLeft <= 0) {
                    return clearInterval(interval);
                }

                const particleCount = 50 * (timeLeft / duration);

                // Craft-themed colors (Green, Teal, Pink, Amber, Purple)
                const colors = ['#2fa76b', '#2fc7b4', '#ff6b9d', '#ffc107', '#ff9800', '#9c27b0', '#e91e63'];

                confetti({
                    ...defaults,
                    particleCount,
                    origin: { x: randomInRange(0.1, 0.9), y: Math.random() - 0.2 },
                    colors: colors
                });
            }, 250);
        }
    }

    console.log("Newsletter Popup: System Initialized.");

    const urlParams = new URLSearchParams(window.location.search);
    const forceShow = urlParams.get('showpopup') === '1' || urlParams.get('resetpopup') === '1';

    if (urlParams.get('resetpopup') === '1') {
        localStorage.removeItem("hideSubscribePopup");
        console.log("Newsletter Popup: Storage Reset via URL parameter.");
    }

    const isHidden = localStorage.getItem("hideSubscribePopup");
    console.log("Newsletter Popup: Current State - isHidden:", isHidden, "forceShow:", forceShow);

    // If forced, show instantly
    if (forceShow) {
        console.log("Newsletter Popup: FORCED display. Showing now.");
        overlay.style.setProperty('display', 'flex', 'important');
        overlay.style.opacity = '1';
    } else {
        // Check if user is logged in - don't show popup if logged in
        fetch('check-login.php')
            .then(res => res.json())
            .then(data => {
                console.log("Newsletter Popup: Auth Result:", data);
                if (data.status !== 'logged_in' && !isHidden) {
                    console.log("Newsletter Popup: Conditions met. Showing in 500ms.");
                    setTimeout(() => {
                        overlay.style.setProperty('display', 'flex', 'important');
                        overlay.style.opacity = '1';
                    }, 500);
                } else {
                    console.log("Newsletter Popup: Conditions not met for display.");
                }
            })
            .catch(error => {
                console.error('Newsletter Popup: Auth Error:', error);
                if (!isHidden) {
                    setTimeout(() => {
                        overlay.style.setProperty('display', 'flex', 'important');
                        overlay.style.opacity = '1';
                    }, 500);
                }
            });
    }

    // Close popup function
    window.closePopup = function () {
        overlay.style.display = "none";
        if (dontShow && dontShow.checked) {
            localStorage.setItem("hideSubscribePopup", "yes");
        }
    };

    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(form);
            fetch("subscribe.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.text())
                .then(res => {
                    overlay.style.display = "none";
                    if (res.trim() === "success" || res.trim() === "exists") {
                        localStorage.setItem("hideSubscribePopup", "yes");
                        triggerConfetti();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: res.trim() === "success" ? "success" : "info",
                                title: res.trim() === "success" ? "🎉 Subscribed!" : "😊 Already Subscribed!",
                                text: res.trim() === "success" ? "Welcome to Craft Royale family! ✨" : "You're already part of our family! 💖",
                                confirmButtonColor: "#2fa76b",
                                customClass: {
                                    popup: 'craft-popup-premium'
                                }
                            });
                        }
                    } else {
                        alert("Something went wrong. Please try again later.");
                    }
                })
                .catch(err => {
                    console.error("Subscription error:", err);
                    overlay.style.display = "none";
                });
        });
    }

    // Close on overlay click
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) {
            closePopup();
        }
    });

    const popupContent = overlay.querySelector('.subscribe-popup');
    if (popupContent) {
        popupContent.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }
}

// Run when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNewsletterPopup);
} else {
    initNewsletterPopup();
}
