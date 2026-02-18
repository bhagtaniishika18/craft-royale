// Auth Modals JavaScript

// Show beautiful toast notification
function showToast(title, message, type = 'success', duration = null) {
    // Remove existing toast if any
    const existingToast = document.querySelector('.auth-toast');
    if (existingToast) {
        existingToast.classList.remove('show');
        setTimeout(() => {
            existingToast.remove();
        }, 300);
    }

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `auth-toast ${type === 'error' ? 'error' : ''}`;

    const icon = type === 'error' ? '✕' : '✓';

    toast.innerHTML = `
        <div class="auth-toast-icon-wrapper">
            <div class="auth-toast-icon">${icon}</div>
        </div>
        <div class="auth-toast-content">
            <div class="auth-toast-title">${title}</div>
            <div class="auth-toast-message">${message}</div>
        </div>
    `;

    document.body.appendChild(toast);

    // Trigger animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    // Auto remove - use configurable duration or default to 3 seconds
    // If duration is null or 0, don't auto-remove
    if (duration !== null && duration > 0) {
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 400);
        }, duration);
    } else if (duration === null) {
        // Default: auto-remove after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 400);
        }, 3000);
    }
    // If duration is 0, toast stays until manually closed or new toast appears
}

// Open Login Modal - Make it globally accessible
window.openLogin = function () {
    const loginModalEl = document.getElementById('loginModal');
    if (loginModalEl) {
        loginModalEl.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
};

// Also keep function name for backward compatibility
function openLogin() {
    if (typeof window.openLogin === 'function') {
        window.openLogin();
    }
}

// Ensure openLogin is globally available immediately
if (typeof window !== 'undefined') {
    window.openLogin = window.openLogin || function () {
        const loginModalEl = document.getElementById('loginModal');
        if (loginModalEl) {
            loginModalEl.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    };
}

// Also export to global scope
if (typeof globalThis !== 'undefined') {
    globalThis.openLogin = window.openLogin;
}

// Close Login Modal
function closeLoginModal() {
    const loginModalEl = document.getElementById('loginModal');
    if (loginModalEl) {
        // CRITICAL: Remove focus from any inputs before closing to prevent freezing
        const inputs = loginModalEl.querySelectorAll('input');
        inputs.forEach(input => {
            if (input === document.activeElement) {
                input.blur();
            }
        });

        loginModalEl.classList.remove('active');
        document.body.style.overflow = '';

        // Reset form
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.reset();
        }

        // Ensure body has focus after closing
        setTimeout(() => {
            if (document.activeElement && document.activeElement.closest('#loginModal')) {
                document.activeElement.blur();
                document.body.focus();
            }
        }, 50);
    }
}

// Close Register Modal
function closeRegisterModal() {
    document.getElementById('registerModal').classList.remove('active');
    document.body.style.overflow = '';
    document.getElementById('registerForm').reset();
}

// Switch to Register Modal
function switchToRegister() {
    closeLoginModal();
    setTimeout(() => {
        document.getElementById('registerModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }, 300);
}

// Switch to Login Modal
function switchToLogin() {
    closeRegisterModal();
    setTimeout(() => {
        document.getElementById('loginModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }, 300);
}

// Handle Login Form Submission
function handleLogin(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    // Disable submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Signing in...';

    fetch('login-handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
        .then(res => res.json())
        .then(data => {
            // Always re-enable button after response
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;

            if (data.status === 'success') {
                // CRITICAL: Remove focus from modal inputs BEFORE closing modal (but not from product page buttons)
                const loginModalElForClose = document.getElementById('loginModal');
                if (loginModalElForClose) {
                    const allInputs = loginModalElForClose.querySelectorAll('input, textarea, select');
                    allInputs.forEach(element => {
                        if (element === document.activeElement) {
                            element.blur();
                        }
                        // Remove aria-hidden to prevent focus trap
                        element.removeAttribute('aria-hidden');
                    });
                    // Don't blur buttons in modal - let them work
                }

                closeLoginModal();

                // CRITICAL: Force remove focus from any remaining hidden elements (only in modals/popups)
                setTimeout(() => {
                    const hiddenElements = document.querySelectorAll('.auth-modal-overlay.active input, .swal2-container input');
                    hiddenElements.forEach(element => {
                        if (element === document.activeElement) {
                            element.blur();
                        }
                    });

                    // Only blur if focus is in a modal/popup - NOT on product page buttons
                    const activeElement = document.activeElement;
                    if (activeElement && activeElement.closest('.auth-modal-overlay, .swal2-container')) {
                        activeElement.blur();
                        document.body.focus();
                    }
                    // Don't blur product page buttons - they should work normally
                }, 100);

                // Ensure data has the correct structure for updateUserUI
                const userData = {
                    status: 'logged_in',
                    user_name: data.user_name || '',
                    user_email: data.user_email || ''
                };
                console.log('Login successful, calling updateUserUI with:', userData);

                // CRITICAL: Close login popup FIRST before anything else to prevent freeze
                const loginModalEl = document.getElementById('loginModal');
                if (loginModalEl) {
                    // Remove focus from all inputs FIRST to prevent freeze
                    const allInputs = loginModalEl.querySelectorAll('input, textarea, select, button');
                    allInputs.forEach(input => {
                        input.blur();
                        input.removeAttribute('aria-hidden');
                    });

                    // Close modal
                    loginModalEl.classList.remove('active');
                    loginModalEl.removeAttribute('aria-hidden');
                    document.body.style.overflow = '';

                    // Force body focus to prevent freeze
                    setTimeout(() => {
                        document.body.focus();
                    }, 10);
                }

                // CRITICAL: Close SweetAlert popup IMMEDIATELY
                try {
                    if (typeof Swal !== 'undefined' && Swal.isVisible && Swal.isVisible()) {
                        Swal.close();
                        console.log('SweetAlert popup closed immediately after login (auth-modals.js)');
                    }
                } catch (err) {
                    console.error('Error closing SweetAlert:', err);
                }

                // AGGRESSIVE: Remove SweetAlert container directly
                const swalContainer = document.querySelector('.swal2-container');
                if (swalContainer) {
                    swalContainer.remove();
                    console.log('SweetAlert container removed immediately');
                }

                // Also remove any remaining containers after a short delay
                setTimeout(() => {
                    const remainingContainers = document.querySelectorAll('.swal2-container');
                    remainingContainers.forEach(container => container.remove());

                    // Ensure body is not frozen
                    document.body.style.overflow = '';
                    document.body.focus();

                    // Remove aria-hidden from product page
                    const productDetailPage = document.querySelector('.product-detail-page');
                    if (productDetailPage) {
                        productDetailPage.removeAttribute('aria-hidden');
                    }
                }, 100);

                // CRITICAL: Clear all intervals to prevent freezing
                if (window._loginCheckIntervals && Array.isArray(window._loginCheckIntervals)) {
                    window._loginCheckIntervals.forEach(intervalId => {
                        clearInterval(intervalId);
                    });
                    window._loginCheckIntervals = [];
                }

                // Clear any intervals stored on DOM elements
                const allIntervalElements = document.querySelectorAll('[data-check-interval-id]');
                allIntervalElements.forEach(el => {
                    const intervalId = el.getAttribute('data-check-interval-id');
                    if (intervalId) {
                        clearInterval(parseInt(intervalId));
                    }
                    if (el._checkLoginInterval) {
                        clearInterval(el._checkLoginInterval);
                        el._checkLoginInterval = null;
                    }
                });

                // Clear intervals from all popups
                const allPopups = document.querySelectorAll('.login-required-popup, .swal2-container');
                allPopups.forEach(popup => {
                    if (popup._checkLoginInterval) {
                        clearInterval(popup._checkLoginInterval);
                        popup._checkLoginInterval = null;
                    }
                });

                // Remove aria-hidden from product-detail-page
                const productDetailPage = document.querySelector('.product-detail-page');
                if (productDetailPage) {
                    productDetailPage.removeAttribute('aria-hidden');
                }

                // Force body focus to prevent freeze (but only if focus is in a modal)
                const activeElement = document.activeElement;
                if (activeElement && activeElement.closest('.auth-modal-overlay, .swal2-container')) {
                    activeElement.blur();
                    document.body.focus();
                }
                // Don't blur product page buttons or login icon - they should work normally

                // Update UI
                updateUserUI(userData);
                showToast('Login Successful', `Welcome back, ${data.user_name}! ✨`, 'success');

                // Dispatch custom event for login success (for other listeners)
                const loginEvent = new CustomEvent('userLoggedIn', { detail: userData });
                document.dispatchEvent(loginEvent);

                // Additional immediate check - close popup again after a short delay to ensure it's closed
                setTimeout(() => {
                    if (typeof Swal !== 'undefined' && Swal.isVisible && Swal.isVisible()) {
                        Swal.close();
                        console.log('SweetAlert popup closed again (backup)');
                    }
                    const swalContainer = document.querySelector('.swal2-container');
                    if (swalContainer) {
                        swalContainer.remove();
                    }
                }, 200);

                // Redirect to home page after successful login
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1000); // 1 second delay to show success message
            } else {
                showToast('Login Failed', data.message || 'Invalid email or password', 'error');
            }
        })
        .catch(error => {
            console.error('Login error:', error);
            // Always re-enable button on error
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            showToast('Error', 'Something went wrong. Please try again.', 'error');
        });
}

// Handle Register Form Submission
function handleRegister(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    // Disable submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Registering...';

    fetch('register.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(data)
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                showToast('Registration Successful', 'Your account has been created successfully! 🎉', 'success');
                closeRegisterModal();
                // Auto-login after registration
                setTimeout(() => {
                    switchToLogin();
                    // Pre-fill email in login form
                    document.querySelector('#loginForm input[name="email"]').value = formData.get('email');
                }, 500);
            } else {
                showToast('Registration Failed', data.message || 'Registration failed. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Registration error:', error);
            showToast('Error', 'Something went wrong. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
}

// Update User UI after login
function updateUserUI(userData) {
    console.log('updateUserUI called with:', userData); // Debug log
    const loginIcon = document.getElementById('loginIcon');
    const userDropdown = document.getElementById('userDropdown');

    // Check if user is logged in
    const isLoggedIn = userData && userData.status === 'logged_in' && (userData.user_name || userData.user_email);

    console.log('isLoggedIn:', isLoggedIn); // Debug log

    if (loginIcon && isLoggedIn) {
        // User is logged in - ENABLE dropdown functionality
        loginIcon.classList.add('user-logged-in');
        const userName = userData.user_name || userData.user_email || 'User';
        loginIcon.setAttribute('title', `Logged in as ${userName}`);

        // Remove old onclick and set new one to toggle dropdown
        loginIcon.removeAttribute('onclick');
        loginIcon.onclick = function (e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Login icon clicked - toggling dropdown');
            toggleUserDropdown();
            return false;
        };
        loginIcon.innerHTML = '<i class="fa-regular fa-user"></i>';

        // SHOW dropdown menu - add class to make it available
        if (userDropdown) {
            userDropdown.classList.add('user-logged-in-dropdown');
            userDropdown.style.display = 'block';
            userDropdown.style.opacity = '0';
            userDropdown.style.visibility = 'hidden';
            // Start hidden, will show on click/hover
            userDropdown.classList.remove('show');
            console.log('Dropdown classes after login:', userDropdown.className); // Debug log
        }

        // Update any other UI elements that depend on login status
        updateLoginDependentUI(true);
    } else {
        // User is NOT logged in - HIDE dropdown completely
        if (loginIcon) {
            loginIcon.classList.remove('user-logged-in');
            loginIcon.setAttribute('title', 'Login');
            loginIcon.setAttribute('onclick', 'openLogin()');
        }
        // Completely hide dropdown if not logged in
        if (userDropdown) {
            userDropdown.classList.remove('user-logged-in-dropdown');
            userDropdown.classList.remove('show');
            userDropdown.style.display = 'none';
        }
        updateLoginDependentUI(false);
    }
}

// Toggle user dropdown menu
function toggleUserDropdown() {
    console.log('toggleUserDropdown called'); // Debug log
    const userDropdown = document.getElementById('userDropdown');
    const loginIcon = document.getElementById('loginIcon');

    console.log('userDropdown:', userDropdown); // Debug log
    console.log('loginIcon:', loginIcon); // Debug log

    if (userDropdown && loginIcon && loginIcon.classList.contains('user-logged-in')) {
        // Make sure dropdown is available (has the logged-in class)
        if (!userDropdown.classList.contains('user-logged-in-dropdown')) {
            userDropdown.classList.add('user-logged-in-dropdown');
            userDropdown.style.display = 'block';
        }

        // Close any other open dropdowns first
        document.querySelectorAll('.user-dropdown.show').forEach(dropdown => {
            if (dropdown !== userDropdown) {
                dropdown.classList.remove('show');
            }
        });

        // Toggle this dropdown
        const isShowing = userDropdown.classList.contains('show');
        if (isShowing) {
            userDropdown.classList.remove('show');
            console.log('Hiding dropdown'); // Debug log

            // CRITICAL: Blur logout link when dropdown closes to prevent aria-hidden error
            const logoutLink = document.getElementById('logoutLink');
            if (logoutLink && document.activeElement === logoutLink) {
                logoutLink.blur();
            }

            // Remove aria-hidden from header when dropdown closes
            const header = document.querySelector('.main-header, header.main-header');
            if (header) {
                header.removeAttribute('aria-hidden');
            }
        } else {
            userDropdown.classList.add('show');
            console.log('Showing dropdown'); // Debug log
            console.log('Dropdown classes:', userDropdown.className); // Debug log
        }
    } else {
        console.log('Cannot show dropdown - conditions not met'); // Debug log
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function (event) {
    const userDropdown = document.getElementById('userDropdown');
    const loginIcon = document.getElementById('loginIcon');
    const userDropdownWrapper = document.querySelector('.user-dropdown-wrapper');

    if (userDropdown && userDropdownWrapper && !userDropdownWrapper.contains(event.target)) {
        userDropdown.classList.remove('show');

        // CRITICAL: Blur logout link when dropdown closes to prevent aria-hidden error
        const logoutLink = document.getElementById('logoutLink');
        if (logoutLink && document.activeElement === logoutLink) {
            logoutLink.blur();
        }

        // Remove aria-hidden from header when dropdown closes
        const header = document.querySelector('.main-header, header.main-header');
        if (header) {
            header.removeAttribute('aria-hidden');
        }
    }
});

// Update UI elements that depend on login status
function updateLoginDependentUI(isLoggedIn) {
    // Add any other dynamic UI updates here
    // For example, show/hide certain elements, update text, etc.
}

// Handle Logout - Simple and reliable version
let isLogoutProcessing = false;
let logoutTimeout = null;

function handleLogout(event) {
    console.log('handleLogout called', event);

    // Prevent default link behavior FIRST
    if (event) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
    }

    // Prevent multiple calls - but reset if stuck
    if (isLogoutProcessing) {
        console.log('Already processing, but resetting flag and trying again');
        isLogoutProcessing = false;
        if (logoutTimeout) {
            clearTimeout(logoutTimeout);
            logoutTimeout = null;
        }
        // Force close any open modals
        if (typeof Swal !== 'undefined' && Swal.isVisible && Swal.isVisible()) {
            Swal.close();
        }
        // Remove any stuck modals
        const stuckModal = document.querySelector('.swal2-container');
        if (stuckModal) {
            stuckModal.remove();
        }
        // Reset body
        document.body.style.overflow = '';
    }

    // Set flag
    isLogoutProcessing = true;
    console.log('Logout processing started');

    // Set timeout to reset flag if something goes wrong (5 seconds)
    logoutTimeout = setTimeout(function () {
        console.log('Logout timeout - resetting flag');
        isLogoutProcessing = false;
        document.body.style.overflow = '';
        if (typeof Swal !== 'undefined' && Swal.isVisible && Swal.isVisible()) {
            Swal.close();
        }
    }, 5000);

    // CRITICAL: Fix aria-hidden accessibility issue
    // Remove focus from logout link before modal opens
    const logoutLink = document.getElementById('logoutLink');
    if (logoutLink && document.activeElement === logoutLink) {
        logoutLink.blur();
    }

    // Remove aria-hidden from header if it exists (prevents accessibility error)
    const header = document.querySelector('.main-header, header.main-header');
    if (header) {
        header.removeAttribute('aria-hidden');
    }

    // Close user dropdown if open
    const userDropdown = document.getElementById('userDropdown');
    if (userDropdown) {
        userDropdown.classList.remove('show');
    }

    // CRITICAL: Ensure body can scroll and isn't locked
    document.body.style.overflow = '';
    document.body.style.position = '';

    // Show custom professional logout modal
    console.log('Showing custom logout modal');
    showCustomLogoutModal(function (confirmed) {
        // Clear timeout
        if (logoutTimeout) {
            clearTimeout(logoutTimeout);
            logoutTimeout = null;
        }

        // Reset flag
        isLogoutProcessing = false;
        document.body.style.overflow = '';

        if (confirmed) {
            console.log('User confirmed, redirecting to logout.php');
            // Redirect immediately
            window.location.href = 'logout.php';
        } else {
            console.log('User cancelled logout');
        }
    });

    return false;
}

// Custom professional logout modal
function showCustomLogoutModal(callback) {
    // Remove existing modal if any
    const existingModal = document.getElementById('logoutModalOverlay');
    if (existingModal) {
        existingModal.remove();
    }

    // Create modal overlay
    const overlay = document.createElement('div');
    overlay.id = 'logoutModalOverlay';
    overlay.className = 'logout-modal-overlay';

    overlay.innerHTML = `
        <div class="logout-modal">
            <button class="logout-modal-close" onclick="closeCustomLogoutModal(false)">&times;</button>
            <div class="logout-modal-header">
                <span class="logout-modal-icon">👋</span>
                <h2 class="logout-modal-title">Logout Confirmation</h2>
                <p class="logout-modal-message">
                    Are you sure you want to logout from<br>
                    <strong>✨ Craft Royale ✨</strong>?
                </p>
                <p class="logout-modal-message" style="margin-top: 10px; font-size: 14px; color: #999;">
                    We hope to see you again soon! 💖<br>
                    Your cart and wishlist will be saved
                </p>
            </div>
            <div class="logout-modal-footer">
                <button class="logout-modal-btn logout-modal-btn-cancel" onclick="closeCustomLogoutModal(false)">
                    ❌ Cancel
                </button>
                <button class="logout-modal-btn logout-modal-btn-confirm" onclick="closeCustomLogoutModal(true)">
                    ✅ Yes, Logout
                </button>
            </div>
        </div>
    `;

    // Add to body
    document.body.appendChild(overlay);

    // Show with animation
    setTimeout(function () {
        overlay.classList.add('show');
    }, 10);

    // Handle ESC key
    const escHandler = function (e) {
        if (e.key === 'Escape') {
            closeCustomLogoutModal(false);
            document.removeEventListener('keydown', escHandler);
        }
    };
    document.addEventListener('keydown', escHandler);

    // Store callback
    overlay._callback = callback;

    // Close on backdrop click
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) {
            closeCustomLogoutModal(false);
        }
    });
}

// Close custom logout modal
function closeCustomLogoutModal(confirmed) {
    const overlay = document.getElementById('logoutModalOverlay');
    if (overlay) {
        overlay.classList.remove('show');
        setTimeout(function () {
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
            if (overlay._callback) {
                overlay._callback(confirmed);
            }
            // Ensure body can scroll
            document.body.style.overflow = '';
        }, 300);
    }
}

// Make closeCustomLogoutModal globally accessible
window.closeCustomLogoutModal = closeCustomLogoutModal;

// Make handleLogout globally accessible immediately
window.handleLogout = handleLogout;

// Also export it in multiple ways to ensure it's available
if (typeof window !== 'undefined') {
    window.handleLogout = handleLogout;
}

// Prevent aria-hidden from being set on header (accessibility fix)
(function () {
    let headerObserver = null;

    function preventHeaderAriaHidden() {
        const header = document.querySelector('.main-header, header.main-header');
        if (header) {
            // Remove aria-hidden if it exists immediately
            header.removeAttribute('aria-hidden');

            // Watch for aria-hidden being added and remove it immediately
            if (headerObserver) {
                headerObserver.disconnect();
            }

            headerObserver = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'aria-hidden') {
                        if (header.hasAttribute('aria-hidden')) {
                            // Remove immediately
                            header.removeAttribute('aria-hidden');
                            console.log('Removed aria-hidden from header (accessibility fix)');
                        }
                    }
                });
            });

            headerObserver.observe(header, {
                attributes: true,
                attributeFilter: ['aria-hidden']
            });

            // Also periodically check and remove (backup method)
            setInterval(function () {
                if (header && header.hasAttribute('aria-hidden')) {
                    header.removeAttribute('aria-hidden');
                }
            }, 100);
        }
    }

    // Run immediately and on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', preventHeaderAriaHidden);
    } else {
        preventHeaderAriaHidden();
    }

    // Also run after delays to catch late-loading scripts
    setTimeout(preventHeaderAriaHidden, 100);
    setTimeout(preventHeaderAriaHidden, 500);
    setTimeout(preventHeaderAriaHidden, 1000);

    // Hook into SweetAlert if available
    if (typeof Swal !== 'undefined') {
        // Override Swal.fire to remove aria-hidden from header
        const originalSwalFire = Swal.fire;
        Swal.fire = function (...args) {
            const header = document.querySelector('.main-header, header.main-header');
            if (header) {
                header.removeAttribute('aria-hidden');
            }

            const result = originalSwalFire.apply(this, args);

            // Also remove after modal opens
            if (result && typeof result.then === 'function') {
                result.then(function () {
                    const headerAfter = document.querySelector('.main-header, header.main-header');
                    if (headerAfter) {
                        headerAfter.removeAttribute('aria-hidden');
                    }
                });
            }

            // Monitor and remove aria-hidden while modal is open
            const checkInterval = setInterval(function () {
                const headerCheck = document.querySelector('.main-header, header.main-header');
                if (headerCheck && headerCheck.hasAttribute('aria-hidden')) {
                    headerCheck.removeAttribute('aria-hidden');
                }
                // Stop checking when modal closes
                if (!document.querySelector('.swal2-container')) {
                    clearInterval(checkInterval);
                }
            }, 50);

            return result;
        };
    }
})();
if (typeof globalThis !== 'undefined') {
    globalThis.handleLogout = handleLogout;
}

// Don't add duplicate event listeners - the onclick attribute in HTML is enough

// Check login status on page load
document.addEventListener('DOMContentLoaded', function () {
    // Check if user is logged in
    fetch('check-login.php')
        .then(res => res.json())
        .then(data => {
            console.log('Page load - login status:', data); // Debug
            if (data.status === 'logged_in') {
                updateUserUI(data);
            } else {
                // Make sure login icon is set to open login modal if not logged in
                const loginIcon = document.getElementById('loginIcon');
                if (loginIcon) {
                    loginIcon.setAttribute('onclick', 'openLogin()');
                    loginIcon.classList.remove('user-logged-in');
                }
                const userDropdown = document.getElementById('userDropdown');
                if (userDropdown) {
                    userDropdown.classList.remove('show');
                    userDropdown.classList.remove('user-logged-in-dropdown');
                    userDropdown.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error checking login status:', error);
        });

    // Close modals when clicking outside
    document.getElementById('loginModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeLoginModal();
        }
    });

    document.getElementById('registerModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeRegisterModal();
        }
    });

    // Close modals on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeLoginModal();
            closeRegisterModal();
        }
    });
});

