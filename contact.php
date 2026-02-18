<?php
session_start();
include 'includes/db.php';
$message_sent = false;
$error_message = "";

// Check login status for frontend restriction
$is_logged_in = isset($_SESSION['user_id']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'request_callback') {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "NULL";
        
        $query = "INSERT INTO callback_requests (user_id, name, phone, status) VALUES ($user_id, '$name', '$phone', 'Pending')";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true]);
            exit;
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
            exit;
        }
    }

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $query = "INSERT INTO contact_messages (name, email, subject, message) VALUES ('$name', '$email', '$subject', '$message')";
    
    if (mysqli_query($conn, $query)) {
        // Prepare WhatsApp message
        $whatsapp_text = "Hello! I have a new inquiry from Craft Royale website:\n\n" .
                         "*Name:* $name\n" .
                         "*Email:* $email\n" .
                         "*Subject:* $subject\n" .
                         "*Message:* $message";
        $whatsapp_url = "https://wa.me/917046865371?text=" . urlencode($whatsapp_text);
        
        // If it's an AJAX request, return JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true, 'redirect' => $whatsapp_url]);
            exit;
        }
        $message_sent = true;
    } else {
        $error = mysqli_error($conn);
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => false, 'error' => $error]);
            exit;
        }
        $error_message = "Database Error: " . $error;
    }
}
include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/contact.css">

<div class="contact-header">
    <video class="contact-header-video" autoplay muted loop playsinline>
        <source src="assets/images/aboutme.mp4" type="video/mp4">
        <!-- Fallback message if video doesn't load -->
        <img src="assets/images/craft.jpg" alt="Contact Us Background">
    </video>
    <div class="container contact-header-content">
       <!-- <h1>Contact Us</h1> -->
       <!-- <p>Follow our journey on social media or reach out to us directly for any queries, collaborations, or just to say hello! Your feedback makes us better.</p> -->
    </div>
</div>

<div class="contact-info-cards">
    <div class="info-card" onclick="openPhoneModal(event)" style="cursor: pointer;">
        <i class="fas fa-phone-alt"></i>
        <h3>Request a Call</h3>
        <p>+91-8826002179</p>
        <p>0120-4988495</p>
    </div>
    <div class="info-card">
        <a href="https://wa.me/917046865371" target="_blank" style="text-decoration: none; color: inherit;">
            <i class="fab fa-whatsapp"></i>
            <h3>WhatsApp</h3>
            <p>+91 70468 65371</p>
        </a>
    </div>
    <div class="info-card" onclick="openEmailModal(event)" style="cursor: pointer;">
        <i class="fas fa-envelope"></i>
        <h3>Email Query</h3>
        <p>care@craftroyale.com</p>
        <p>support@craftroyale.com</p>
    </div>
    <div class="info-card">
        <a href="https://www.google.com/maps/search/?api=1&query=Aarvak+Garments+Private+Limited+Noida+Sector-6" target="_blank" style="text-decoration: none; color: inherit;">
            <i class="fas fa-store"></i>
            <h3>Our Shop</h3>
            <p>E-71, 2nd Floor, Sector-6, Noida<br>Uttar Pradesh, India 201301</p>
        </a>
    </div>
</div>

<div class="contact-container">
    <div class="contact-map">
        <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3503.784407519654!2d77.3155!3d28.6044!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390ce50000000000%3A0x0!2zMjjCsDM2JzE1LjgiTiA3N8KwMTgnNTUuOCJF!5e0!3m2!1sen!2sin!4v1707654321000!5m2!1sen!2sin" 
            width="100%" 
            height="100%" 
            style="border:0;" 
            allowfullscreen="" 
            loading="lazy">
        </iframe>
    </div>

    <div class="contact-form-section">
        <h2>Get In Touch</h2>
        <p>Have a question or a problem? Fill out the form below and our team will get back to you as soon as possible.</p>

        <?php if ($message_sent): ?>
            <div style="background: #e8f5e9; color: #2e7d32; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #c8e6c9;">
                <i class="fas fa-check-circle"></i> Thank you! Your message has been sent successfully.
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div style="background: #ffebee; color: #c62828; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #ffcdd2;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form id="contactForm" action="contact.php" method="POST">
            <div class="form-group">
                <input type="text" name="name" id="userName" placeholder="Your Name..." required>
            </div>
            <div class="form-group">
                <input type="email" name="email" id="userEmail" placeholder="example@yourmail.com" required>
            </div>
            <div class="form-group">
                <input type="text" name="subject" id="msgSubject" placeholder="Subject/Title..." required>
            </div>
            <div class="form-group">
                <textarea name="message" id="userMessage" rows="6" placeholder="Type your message here..." required></textarea>
            </div>
            <button type="submit" id="submitBtn" class="send-btn">Send Now</button>
        </form>
    </div>
</div>

<!-- PHONE MODAL -->
<div id="phoneModal" class="phone-modal-overlay">
    <div class="phone-modal-card">
        <span class="phone-modal-close" onclick="closePhoneModal()">&times;</span>
        <div class="phone-modal-header">
            <i class="fas fa-phone-alt"></i>
            <h2>Callback Request</h2>
            <p>Enter your 10-digit number and we'll call you back instantly!</p>
        </div>
        <form id="phoneRequestForm" onsubmit="handlePhoneSubmit(event)">
            <div class="phone-input-group" style="margin-bottom: 15px;">
                <input type="text" id="callbackName" placeholder="Your Name" required style="width: 100%; padding: 12px; border: 1px solid #eee; border-radius: 8px;">
            </div>
            <div class="phone-input-group">
                <input type="text" id="phoneDigits" placeholder="Enter 10 digits" maxlength="10" pattern="\d{10}" required>
            </div>
            <button type="submit" id="phoneSubmitBtn" class="phone-submit-btn">Request Call</button>
        </form>
        <div id="phoneSuccessMsg" class="phone-success-msg" style="display: none;">
            <i class="fas fa-check-circle"></i>
            <span>My team will call you in 2-3 seconds</span>
        </div>
    </div>
</div>

<!-- EMAIL MODAL -->
<div id="emailModal" class="phone-modal-overlay">
    <div class="phone-modal-card">
        <span class="phone-modal-close" onclick="closeEmailModal()">&times;</span>
        <div class="phone-modal-header">
            <i class="fas fa-envelope-open-text"></i>
            <h2>Send a Query</h2>
            <p>Drop your email and query below, we'll solve it fast!</p>
        </div>
        <form id="emailQueryForm" onsubmit="handleEmailQuerySubmit(event)">
            <div class="form-group" style="margin-bottom: 15px;">
                <input type="text" id="queryName" placeholder="Your Name" required style="width: 100%; padding: 12px; border: 1px solid #eee; border-radius: 8px;">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <input type="email" id="queryEmail" placeholder="Your Email Address" required style="width: 100%; padding: 12px; border: 1px solid #eee; border-radius: 8px;">
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <textarea id="queryMessage" placeholder="How can we help you?" required rows="4" style="width: 100%; padding: 12px; border: 1px solid #eee; border-radius: 8px; resize: none;"></textarea>
            </div>
            <button type="submit" id="emailQueryBtn" class="phone-submit-btn">Submit Query</button>
        </form>
        <div id="emailSuccessMsg" class="phone-success-msg" style="display: none;">
            <i class="fas fa-check-circle"></i>
            <span>My team will solve your queries fast!</span>
        </div>
    </div>
</div>

<script>
// Phone Modal Logic
function openPhoneModal(e) {
    document.getElementById('phoneModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closePhoneModal() {
    document.getElementById('phoneModal').classList.remove('active');
    document.body.style.overflow = '';
    // Reset state
    document.getElementById('phoneRequestForm').style.display = 'block';
    document.getElementById('phoneSuccessMsg').style.display = 'none';
}

function handlePhoneSubmit(e) {
    e.preventDefault();
    const name = document.getElementById('callbackName').value;
    const phone = document.getElementById('phoneDigits').value;
    const btn = document.getElementById('phoneSubmitBtn');
    const originalText = btn.textContent;

    if (phone.length === 10) {
        btn.disabled = true;
        btn.textContent = 'Requesting...';

        const formData = new FormData();
        formData.append('action', 'request_callback');
        formData.append('name', name);
        formData.append('phone', phone);

        fetch('contact.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('phoneRequestForm').style.display = 'none';
                document.getElementById('phoneSuccessMsg').style.display = 'block';
                // Update success message to include name
                document.querySelector('#phoneSuccessMsg span').textContent = `Hi ${name}, my team will call you in 2-3 seconds`;
            } else {
                alert('Error: ' + (data.error || 'Something went wrong.'));
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.disabled = false;
            btn.textContent = originalText;
        });
    }
}

// Email Modal Logic
function openEmailModal(e) {
    document.getElementById('emailModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeEmailModal() {
    document.getElementById('emailModal').classList.remove('active');
    document.body.style.overflow = '';
    document.getElementById('emailQueryForm').style.display = 'block';
    document.getElementById('emailSuccessMsg').style.display = 'none';
}

function handleEmailQuerySubmit(e) {
    e.preventDefault();
    const name = document.getElementById('queryName').value;
    const email = document.getElementById('queryEmail').value;
    const message = document.getElementById('queryMessage').value;
    const btn = document.getElementById('emailQueryBtn');
    const originalText = btn.textContent;

    btn.disabled = true;
    btn.textContent = 'Submitting...';

    // Prepare data to save in contact_messages table
    const formData = new FormData();
    formData.append('name', name); // Use dynamic name
    formData.append('email', email);
    formData.append('subject', 'Quick Email Query Popup');
    formData.append('message', message);

    fetch('contact.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('emailQueryForm').style.display = 'none';
            document.getElementById('emailSuccessMsg').style.display = 'block';
            document.querySelector('#emailSuccessMsg span').textContent = `Hi ${name}, my team will solve your queries fast!`;
        } else {
            alert('Error: ' + (data.error || 'Something went wrong.'));
            btn.disabled = false;
            btn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        btn.disabled = false;
        btn.textContent = originalText;
    });
}

// Close modals when clicking outside
window.onclick = function(event) {
    const pModal = document.getElementById('phoneModal');
    const eModal = document.getElementById('emailModal');
    if (event.target == pModal) closePhoneModal();
    if (event.target == eModal) closeEmailModal();
}

document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const btn = document.getElementById('submitBtn');
    const originalText = btn.textContent;
    
    // Disable button and show loading
    btn.disabled = true;
    btn.textContent = 'Sending...';
    
    const formData = new FormData(form);
    
    // Use Fetch API to submit without page reload
    fetch('contact.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            btn.textContent = 'Redirecting to WhatsApp...';
            // Open WhatsApp immediately
            window.location.href = data.redirect;
        } else {
            alert('Error: ' + (data.error || 'Something went wrong.'));
            btn.disabled = false;
            btn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Could not send message. Please try again.');
        btn.disabled = false;
        btn.textContent = originalText;
    });
});
</script>


<?php include 'includes/footer.php'; ?>
