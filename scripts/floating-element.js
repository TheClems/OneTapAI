// Animated background particles
function createBackgroundParticles() {
    const bg = document.getElementById('animatedBg');
    const particleCount = 20;

    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'bg-particle';
        particle.style.left = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 8 + 's';
        particle.style.animationDuration = (8 + Math.random() * 4) + 's';
        bg.appendChild(particle);
    }
}

// Enhanced mouse parallax effect
document.addEventListener('mousemove', function (e) {
    const floatingElements = document.querySelectorAll('.floating-element');
    const x = (e.clientX / window.innerWidth) - 0.5;
    const y = (e.clientY / window.innerHeight) - 0.5;

    floatingElements.forEach((element, index) => {
        const speed = (index + 1) * 0.3;
        const xPos = x * speed * 30;
        const yPos = y * speed * 30;

        element.style.transform = `translate(${xPos}px, ${yPos}px) rotate(${x * speed * 10}deg)`;
    });
});

// Initialize animations
document.addEventListener('DOMContentLoaded', function () {
    createBackgroundParticles();

    // Stagger animation for packages
    const packages = document.querySelectorAll('.package');
    packages.forEach((pkg, index) => {
        pkg.style.animationDelay = (index * 0.1) + 's';
        pkg.style.animation = 'fadeInUp 0.6s ease forwards';
    });
});

// Add fadeInUp animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .package {
        opacity: 0;
    }
`;
document.head.appendChild(style);