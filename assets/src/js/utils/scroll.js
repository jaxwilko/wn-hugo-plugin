// eslint-disable-next-line import/prefer-default-export
export const scrollToTarget = (target, loading) => {
    // Allow for passing of element
    const element = typeof target === 'string'
        ? document.querySelector(target)
        : target;
    // If no element was found, return and do nothing
    if (!element) {
        return;
    }
    // If element was found, scroll to it, minus a bit of padding for visual flair
    window.scrollTo({
        top: element.getBoundingClientRect().top + window.scrollY - (
            300
        ),
        behavior: 'smooth',
    });
};
