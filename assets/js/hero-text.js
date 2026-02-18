window.onload = function () {

    const taglines = [
        "🧵✂️ Everything you need for sewing, jewelry & DIY crafts 🎨🧶",
        "✨ Premium materials to bring your creativity to life 🎀🪡",
        "💎 Beads, laces & embellishments for every design 🌸",
        "🎨 DIY supplies that turn ideas into masterpieces ✨",
        "🪡 Sew • Design • Create • Inspire 💖",
        "🌟 Where creativity meets quality craft supplies 🧶"
    ];

    let index = 0;
    const taglineEl = document.getElementById("heroTagline");

    if (!taglineEl) {
        console.warn("heroTagline element not found (expected on non-home pages)");
        return;
    }

    setInterval(function () {
        taglineEl.style.opacity = "0";

        setTimeout(function () {
            index = (index + 1) % taglines.length;
            taglineEl.innerHTML = taglines[index];
            taglineEl.style.opacity = "1";
        }, 700);

    }, 8000);
};
