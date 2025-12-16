// Smooth scrolling and active section detection
(function() {
    'use strict';

    // Get all sections and nav links
    const sections = document.querySelectorAll('section[id], main[id]');
    const navLinks = document.querySelectorAll('.nav-link[data-section]');
    const logoLink = document.getElementById('logo-link');
    
    // Track current visible section
    let currentSection = 'home';
    
    // Function to get the currently visible section
    function getCurrentSection() {
        const scrollPosition = window.scrollY + 100; // Offset for navbar
        
        for (let i = sections.length - 1; i >= 0; i--) {
            const section = sections[i];
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                return section.id || 'home';
            }
        }
        
        // Default to home if at top
        if (window.scrollY < 100) {
            return 'home';
        }
        
        return currentSection; // Keep last known section
    }
    
    // Function to update active nav link
    function updateActiveNav(sectionId) {
        navLinks.forEach(link => {
            if (link.getAttribute('data-section') === sectionId) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }
    
    // Function to scroll to section
    function scrollToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
    
    // Handle nav link clicks
    navLinks.forEach(link => {
        const href = link.getAttribute('href') || '';
        const sectionId = link.getAttribute('data-section');
        const isAnchorLink = href.indexOf('#') !== -1;

        link.addEventListener('click', function(e) {
            // For real page navigations (logged-in navbar), let the browser
            // follow the link normally.
            if (!isAnchorLink) {
                return;
            }

            e.preventDefault();
            currentSection = sectionId;
            scrollToSection(sectionId);
            updateActiveNav(sectionId);
        });
    });
    
    // Handle logo click - only intercept on landing page (anchor link)
    if (logoLink) {
        const href = logoLink.getAttribute('href') || '';
        const isHomeAnchor = href.indexOf('#home') !== -1;

        if (isHomeAnchor) {
            logoLink.addEventListener('click', function(e) {
                e.preventDefault();
                currentSection = getCurrentSection();
                scrollToSection(currentSection);
            });
        }
    }
    
    // Update active nav on scroll
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(function() {
            currentSection = getCurrentSection();
            updateActiveNav(currentSection);
        }, 100);
    });
    
    // Initial active nav update
    updateActiveNav(getCurrentSection());
    
})();

