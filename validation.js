document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        let isValid = true;
        const errors = [];

        // Get form elements
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        const role = document.querySelector('input[name="role"]:checked');

        // Role validation
        if (!role) {
            errors.push("Please select a role");
            isValid = false;
        }

        // Name validation
        if (!name) {
            errors.push("Name is required");
            isValid = false;
        } else if (name.length < 2) {
            errors.push("Name must be at least 2 characters long");
            isValid = false;
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email) {
            errors.push("Email is required");
            isValid = false;
        } else if (!emailRegex.test(email)) {
            errors.push("Please enter a valid email address");
            isValid = false;
        }

        // Phone validation
        const phoneRegex = /^\d{10}$/;
        if (!phone) {
            errors.push("Phone number is required");
            isValid = false;
        } else if (!phoneRegex.test(phone)) {
            errors.push("Phone number must be exactly 10 digits");
            isValid = false;
        }

        // Username validation
        if (!username) {
            errors.push("Username is required");
            isValid = false;
        } else if (username.length < 3) {
            errors.push("Username must be at least 3 characters long");
            isValid = false;
        }

        // Password validation
        if (!password) {
            errors.push("Password is required");
            isValid = false;
        } else if (password.length < 8) {
            errors.push("Password must be at least 8 characters long");
            isValid = false;
        }

        if (!isValid) {
            showErrorPopup(errors);
        } else {
            form.submit();
        }
    });
});

// Function to show error popup
function showErrorPopup(errors) {
    // Create popup container
    const popup = document.createElement('div');
    popup.className = 'error-popup';
    
    // Create popup content
    const content = document.createElement('div');
    content.className = 'error-content';
    
    // Add header
    const header = document.createElement('div');
    header.className = 'error-header';
    header.innerHTML = '<i class="fas fa-exclamation-circle"></i><h3>Error</h3>';
    
    // Add error messages
    const ul = document.createElement('ul');
    errors.forEach(error => {
        const li = document.createElement('li');
        li.textContent = error;
        ul.appendChild(li);
    });
    
    // Add close button
    const closeBtn = document.createElement('button');
    closeBtn.textContent = 'Close';
    closeBtn.onclick = () => popup.remove();
    
    // Assemble popup
    content.appendChild(header);
    content.appendChild(ul);
    content.appendChild(closeBtn);
    popup.appendChild(content);
    
    // Add to page
    document.body.appendChild(popup);
}

// Real-time validation for phone number
document.getElementById('phone')?.addEventListener('input', function(e) {
    const phoneRegex = /^\d{10}$/;
    const errorElement = document.getElementById('phoneError');
    
    if (this.value && !phoneRegex.test(this.value)) {
        this.classList.add('error');
        if (errorElement) {
            errorElement.textContent = 'Phone number must be exactly 10 digits';
            errorElement.style.display = 'block';
        }
    } else {
        this.classList.remove('error');
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }
});

// Helper functions to show/clear errors
function showError(fieldId, message) {
    const errorSpan = document.getElementById(`${fieldId}Error`);
    const inputField = document.getElementById(fieldId);
    if (errorSpan) {
        errorSpan.textContent = message;
        errorSpan.style.display = 'block';
        errorSpan.style.color = '#ff3333';
    }
    if (inputField) {
        inputField.style.borderColor = '#ff3333';
    }
}

function clearError(fieldId) {
    const errorSpan = document.getElementById(`${fieldId}Error`);
    const inputField = document.getElementById(fieldId);
    if (errorSpan) {
        errorSpan.textContent = '';
        errorSpan.style.display = 'none';
    }
    if (inputField) {
        inputField.style.borderColor = '';
    }
}
