// assets/js/animation.js

// ============================================
// Ripple Effect for Buttons
// ============================================
document.querySelectorAll('.btn').forEach(button => {
    button.addEventListener('click', function(e) {
        const ripple = document.createElement('span');
        ripple.classList.add('ripple');
        this.appendChild(ripple);
        
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        ripple.style.left = `${x}px`;
        ripple.style.top = `${y}px`;
        
        setTimeout(() => ripple.remove(), 600);
    });
});

// ============================================
// Form Validation with Animation
// ============================================
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const inputs = this.querySelectorAll('input[required], textarea[required], select[required]');
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.style.borderColor = '#ef4444';
                input.style.animation = 'shake 0.5s ease';
                
                setTimeout(() => {
                    input.style.animation = '';
                    input.style.borderColor = '';
                }, 500);
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showToast('Please fill in all required fields', 'error');
        }
    });
});

// Shake Animation
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
`;
document.head.appendChild(style);

// ============================================
// Toast Notification System
// ============================================
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}" style="color: ${type === 'success' ? '#10b981' : '#ef4444'}; font-size: 24px;"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ============================================
// Modal Management
// ============================================
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }, 300);
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        closeModal(event.target.id);
    }
};

// ============================================
// Countdown Timer
// ============================================
function startCountdown(elementId, endDate) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    function update() {
        const now = new Date().getTime();
        const distance = endDate - now;
        
        if (distance < 0) {
            element.innerHTML = '<div class="alert alert-info">Election has ended</div>';
            return;
        }
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        element.innerHTML = `
            <div class="countdown">
                <div class="countdown-item"><div class="countdown-number">${days}</div><div class="countdown-label">Days</div></div>
                <div class="countdown-item"><div class="countdown-number">${hours}</div><div class="countdown-label">Hours</div></div>
                <div class="countdown-item"><div class="countdown-number">${minutes}</div><div class="countdown-label">Minutes</div></div>
                <div class="countdown-item"><div class="countdown-number">${seconds}</div><div class="countdown-label">Seconds</div></div>
            </div>
        `;
    }
    
    update();
    setInterval(update, 1000);
}

// ============================================
// Progress Bar Animation
// ============================================
function animateProgressBar(element, targetPercentage) {
    let current = 0;
    const interval = setInterval(() => {
        if (current >= targetPercentage) {
            clearInterval(interval);
        } else {
            current++;
            element.style.width = current + '%';
            element.setAttribute('data-progress', current + '%');
        }
    }, 10);
}

// ============================================
// Live Search
// ============================================
function initLiveSearch(inputId, itemSelector) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    input.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const items = document.querySelectorAll(itemSelector);
        
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(filter)) {
                item.style.display = '';
                item.style.animation = 'fadeInUp 0.3s ease';
            } else {
                item.style.display = 'none';
            }
        });
    });
}

// ============================================
// Smooth Scroll
// ============================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// ============================================
// Candidate Selection for Voting
// ============================================
let selectedCandidateId = null;

function selectCandidate(candidateId, candidateName) {
    // Remove selection from all cards
    document.querySelectorAll('.candidate-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selection to clicked card
    const selectedCard = document.querySelector(`.candidate-card[data-id="${candidateId}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
    }
    
    selectedCandidateId = candidateId;
    document.getElementById('selectedCandidateName').textContent = candidateName;
    document.getElementById('confirmVoteBtn').href = `cast_vote.php?id=${candidateId}`;
}

// ============================================
// Loader
// ============================================
function showLoader() {
    let loader = document.querySelector('.global-loader');
    if (!loader) {
        loader = document.createElement('div');
        loader.className = 'global-loader';
        loader.innerHTML = '<div class="spinner"></div>';
        loader.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        `;
        document.body.appendChild(loader);
    }
    loader.style.display = 'flex';
}

function hideLoader() {
    const loader = document.querySelector('.global-loader');
    if (loader) {
        loader.style.display = 'none';
    }
}

// ============================================
// Form Submission with Loader
// ============================================
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        showLoader();
    });
});

// ============================================
// Auto-hide Alerts
// ============================================
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.animation = 'slideOutRight 0.4s ease';
        setTimeout(() => alert.remove(), 400);
    }, 5000);
});

// Slide Out Animation
const slideOutStyle = document.createElement('style');
slideOutStyle.textContent = `
    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(30px);
        }
    }
`;
document.head.appendChild(slideOutStyle);

// ============================================
// Initialize on DOM Load
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars
    document.querySelectorAll('.progress-bar').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });
    
    // Add fade-in animation to cards
    document.querySelectorAll('.card, .stat-card, .candidate-card').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });
    
    console.log('✅ Animation system initialized');
});