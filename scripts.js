// Wait for the document to fully load
document.addEventListener('DOMContentLoaded', function () {
    const destinationCards = document.querySelectorAll('.destination-card'); // All the destination cards
    const loadMoreButton = document.getElementById('loadMoreBtn'); // The "Load More" button
    const cardsPerLoad = 3; // How many cards to show initially and per click

    // Initially hide all cards except for the first 3
    function updateCards() {
        destinationCards.forEach((card, index) => {
            if (index < cardsPerLoad) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });

        // Show the load more button only if there are more than the visible cards
        if (destinationCards.length > cardsPerLoad) {
            loadMoreButton.style.display = 'inline-block'; // Show the "Load More" button
        } else {
            loadMoreButton.style.display = 'none'; // Hide the button if there are no more cards to load
        }
    }

    // Load more cards when the button is clicked
    loadMoreButton.addEventListener('click', function () {
        const visibleCards = document.querySelectorAll('.destination-card[style*="display: block"]'); // Cards that are currently visible
        const startIndex = visibleCards.length; // Where to start loading the next cards

        // Show the next set of cards
        for (let i = startIndex; i < startIndex + cardsPerLoad && i < destinationCards.length; i++) {
            destinationCards[i].style.display = 'block';
        }

        // If all cards are loaded, hide the "Load More" button
        if (startIndex + cardsPerLoad >= destinationCards.length) {
            loadMoreButton.style.display = 'none';
        }
    });

    // Initial setup: Hide cards and show only the first 3
    updateCards();
});


document.addEventListener("DOMContentLoaded", function () {
    // Define the cascading dropdown options
    const categoriesData = {
        "Business": ["Meeting", "Conference", "Trade Show", "Investment"],
        "Tourist": ["Employed", "Self-Employed", "Sponsored"],
        "Other": ["Student", "Work", "Seaman"]
    };

    // Get DOM elements
    const primaryDropdown = document.getElementById("primary-category");
    const subCategoryContainer = document.getElementById("subcategory-container");
    const subCategoryDropdown = document.getElementById("sub-category");
    const checklistSelectors = document.querySelectorAll(".checklist-selector");
    const checklistSections = document.querySelectorAll(".checklist-section");

    // Primary category change handler
    primaryDropdown.addEventListener("change", function () {
        const selectedCategory = this.value;

        // Clear sub-category dropdown
        subCategoryDropdown.innerHTML = '<option value="">-- Select Sub-Category --</option>';

        // Hide all selectors first
        checklistSelectors.forEach(selector => {
            selector.style.display = "none";
        });

        if (selectedCategory) {
            // Populate sub-categories
            if (categoriesData[selectedCategory]) {
                categoriesData[selectedCategory].forEach(subCat => {
                    const option = document.createElement("option");
                    option.value = subCat;
                    option.textContent = subCat;
                    subCategoryDropdown.appendChild(option);
                });

                // Show sub-category dropdown
                subCategoryContainer.style.display = "block";

                // Show only selectors for the selected primary category
                // checklistSelectors.forEach(selector => {
                //     if (selector.getAttribute("data-category") === selectedCategory) {
                //         selector.style.display = "block";
                //     }
                // });
            } else {
                subCategoryContainer.style.display = "none";
            }
        } else {
            // Hide sub-category dropdown if no primary category is selected
            subCategoryContainer.style.display = "none";
        }

        // Hide all sections
        checklistSections.forEach(section => {
            section.style.display = "none";
        });

        // Reset active selector styling
        checklistSelectors.forEach(s => {
            s.classList.remove("active-selector");
        });
    });

    // Sub-category change handler
    subCategoryDropdown.addEventListener("change", function () {
        const selectedCategory = primaryDropdown.value;
        const selectedSubCategory = this.value;

        // Hide all sections
        checklistSections.forEach(section => {
            section.style.display = "none";
        });

        // Reset active selector styling
        checklistSelectors.forEach(s => {
            s.classList.remove("active-selector");
        });

        if (selectedCategory && selectedSubCategory) {
            // Find matching selector and activate it
            checklistSelectors.forEach((selector, index) => {
                const selectorCategory = selector.getAttribute("data-category");
                const selectorSubcategory = selector.getAttribute("data-subcategory");

                if (selectorCategory === selectedCategory && selectorSubcategory === selectedSubCategory) {
                    // Show matching section
                    if (checklistSections[index]) {
                        checklistSections[index].style.display = "flex";
                    }

                    // Update active selector styling
                    selector.classList.add("active-selector");
                }
            });
        }
    });

    // Selector click handler
    checklistSelectors.forEach((selector, index) => {
        selector.addEventListener("click", function () {
            // Hide all sections
            checklistSections.forEach(section => {
                section.style.display = "none";
            });

            // Show selected section
            if (checklistSections[index]) {
                checklistSections[index].style.display = "flex";
            }

            // Update active selector styling
            checklistSelectors.forEach(s => {
                s.classList.remove("active-selector");
            });
            this.classList.add("active-selector");

            // Update dropdowns to match selected section
            const category = this.getAttribute("data-category");
            const subcategory = this.getAttribute("data-subcategory");

            if (category) {
                primaryDropdown.value = category;

                // Trigger change event to update sub-categories
                const event = new Event("change");
                primaryDropdown.dispatchEvent(event);

                // Set sub-category if available
                if (subcategory) {
                    setTimeout(() => {
                        subCategoryDropdown.value = subcategory;
                    }, 10);
                }
            }
        });
    });

    // Initialize the first primary option
    primaryDropdown.selectedIndex = 0;
    const initEvent = new Event("change");
    primaryDropdown.dispatchEvent(initEvent);
});
const tooltip = document.getElementById('custom-tooltip');
const primaryDropdown = document.getElementById('primary-category');
const subCategoryDropdown = document.getElementById('sub-category');

function attachTooltip(dropdown, message) {
    function showTooltip(e) {
        tooltip.innerText = message;
        tooltip.style.display = 'block';
        positionTooltip(e);
    }

    function hideTooltip() {
        tooltip.style.display = 'none';
    }

    function positionTooltip(e) {
        const x = e.pageX || e.touches?.[0].pageX;
        const y = e.pageY || e.touches?.[0].pageY;
        tooltip.style.left = (x + 10) + 'px';
        tooltip.style.top = (y - 50) + 'px';
    }

    // Desktop events
    dropdown.addEventListener('mouseover', showTooltip);
    dropdown.addEventListener('mousemove', positionTooltip);
    dropdown.addEventListener('mouseleave', hideTooltip);

    // Mobile events
    dropdown.addEventListener('touchstart', showTooltip);
    dropdown.addEventListener('touchmove', positionTooltip);
    dropdown.addEventListener('touchend', hideTooltip);
}

attachTooltip(primaryDropdown, 'Please Select Your Visa Type');
attachTooltip(subCategoryDropdown, 'Please Select Your Visa Type');


const dateEl = document.getElementById('next-appointment-date');
const today = new Date();

// Add one month while handling year-end overflow
const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, today.getDate());

// Format the date (e.g., May 8, 2025)
const options = { year: 'numeric', month: 'long', day: 'numeric' };
dateEl.textContent = nextMonth.toLocaleDateString('en-US', options);

// read more
document.addEventListener('DOMContentLoaded', function () {
    // Initialize all hidden content to be hidden
    document.querySelectorAll('.hidden-content').forEach(content => {
        content.style.display = 'none';
    });

    // Add click handlers to all read-more elements
    document.querySelectorAll('.read-more').forEach(readMoreLink => {
        readMoreLink.addEventListener('click', function (e) {
            e.preventDefault();

            // Find the parent card content and the hidden content inside it
            const cardContent = this.closest('.card-content');
            const hiddenContent = cardContent.querySelector('.hidden-content');

            // Toggle visibility
            if (hiddenContent.style.display === 'inline') {
                hiddenContent.style.display = 'none';
                this.innerHTML = 'Read more <i class="fas fa-arrow-right"></i>';
            } else {
                hiddenContent.style.display = 'inline';
                this.innerHTML = 'Read less <i class="fas fa-arrow-up"></i>';
            }
        });
    });

    // Make the entire link (both text and icon) clickable
    document.querySelectorAll('.read-more i').forEach(icon => {
        icon.addEventListener('click', function (e) {
            // Prevent the default action
            e.preventDefault();
            // Prevent event bubbling
            e.stopPropagation();
            // Trigger the click on the parent element
            this.parentElement.click();
        });
    });
});

 // Add smooth scrolling for anchor links
 document.querySelectorAll('.nav-links a').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        const targetElement = document.querySelector(targetId);
        
        if (targetElement) {
            // Offset for sticky navigation
            const navHeight = document.querySelector('.sticky-nav').offsetHeight;
            const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - navHeight;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
            
            // Update active class
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.classList.remove('active');
            });
            this.classList.add('active');
        }
    });
});

// Highlight active navigation item based on scroll position
window.addEventListener('scroll', function() {
    const sections = [
        document.querySelector('#tourist-visa'),
        document.querySelector('#business-visa'),
        document.querySelector('#visa-process'),
        document.querySelector('#document-checklist'),
        document.querySelector('#info-section')
    ];
    
    let currentSection = '';
    const scrollPosition = window.scrollY + document.querySelector('.sticky-nav').offsetHeight + 50;
    
    sections.forEach(section => {
        if (section) {
            const sectionTop = section.offsetTop;
            const sectionBottom = sectionTop + section.offsetHeight;
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                currentSection = '#' + section.getAttribute('id');
            }
        }
    });
    
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === currentSection) {
            link.classList.add('active');
        }
    });
});