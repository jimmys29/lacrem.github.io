document.addEventListener('DOMContentLoaded', function () {
    function setupReadMore(sourceId, displayId, btnId, initialLimit = 500, incrementAmount = 500) {
        const sourceContainer = document.getElementById(sourceId);
        const displayContainer = document.getElementById(displayId);
        const loadMoreBtn = document.getElementById(btnId);

        if (!sourceContainer || !displayContainer || !loadMoreBtn) {
            console.warn(`Read More elements not found for ${sourceId}`);
            return;
        }

        const children = Array.from(sourceContainer.children);
        let currentLimit = initialLimit;

        function updateDisplay() {
            let currentTextLength = 0;
            let visibleHTML = '';
            let allShown = true;
            let currentChildrenCount = 0;

            for (let i = 0; i < children.length; i++) {
                const child = children[i];
                // Compress whitespace to space and trim to get "visible" length
                const textContent = (child.textContent || '').replace(/\s+/g, ' ').trim();
                const textLength = textContent.length;

                // Always show at least one element if nothing is shown yet,
                // otherwise check if adding this child keeps us under the limit.
                // Note: This is an approximation. If a single child is 1000 chars, it will be shown 
                // and then we stop.

                if (currentTextLength < currentLimit) {
                    visibleHTML += child.outerHTML;
                    currentTextLength += textLength;
                    currentChildrenCount++;
                } else {
                    allShown = false;
                    break;
                }
            }

            // Safety: ensure at least one element is shown if there are children
            if (currentChildrenCount === 0 && children.length > 0) {
                visibleHTML += children[0].outerHTML;
                allShown = children.length === 1;
            }

            displayContainer.innerHTML = visibleHTML;

            if (allShown) {
                loadMoreBtn.style.display = 'none';
            } else {
                loadMoreBtn.style.display = 'inline-block';
            }
        }

        loadMoreBtn.addEventListener('click', function (e) {
            e.preventDefault();
            currentLimit += incrementAmount;
            updateDisplay();
        });

        // Initial render
        updateDisplay();
    }

    // Initialize with 500 char limit for both
    setupReadMore('nosotros-source', 'nosotros-display', 'btn-nosotros-more', 500, 500);
    setupReadMore('esencia-source', 'esencia-display', 'btn-esencia-more', 500, 500);
});
