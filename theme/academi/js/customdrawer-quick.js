(function() {
    const selectors = [
        '.logo',
        '.navbar-brand.has-logo',
        '.course-content',
        '.sitetopic',
        '#page.drawers',
        '#page-footer',
        '.stats-banner',
        '.homepage-carousel',
        '#frontpage-category-names'
    ];

    const waitForElements = (callback, timeout = 5000) => {
        const interval = 50;
        const start = Date.now();

        const check = () => {
            const elements = document.querySelectorAll(selectors.join(','));
            const drawer = document.querySelector('[data-region="fixed-drawer"]');
            if (elements.length && drawer) {
                callback(elements, drawer);
            } else if (Date.now() - start < timeout) {
                setTimeout(check, interval);
            }
        };

        check();
    };

    waitForElements((elements, drawer) => {
        const updateClasses = () => {
            elements.forEach(el => {
                if (!drawer.classList.contains('hidden')) {
                    el.classList.add('show-drawer-left');
                } else {
                    el.classList.remove('show-drawer-left');
                }
            });
        };

        // pierwszy raz
        updateClasses();

        // obserwuj zmiany drawer
        const observer = new MutationObserver(updateClasses);
        observer.observe(drawer, { attributes: true, attributeFilter: ['class'] });
    });
})();
