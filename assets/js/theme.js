// Theme switching functionality
document.addEventListener('DOMContentLoaded', () => {
    // Get the user's theme preference from localStorage or system preference
    const savedTheme = localStorage.getItem('theme') || 'system';
    applyTheme(savedTheme);

    // Add event listeners to theme buttons
    const themeButtons = document.querySelectorAll('.theme-button');
    themeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const theme = button.dataset.theme;
            applyTheme(theme);
            localStorage.setItem('theme', theme);
        });
    });
});

function applyTheme(theme) {
    const root = document.documentElement;
    
    if (theme === 'system') {
        // Check system preference
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        root.setAttribute('data-theme', systemPrefersDark ? 'dark' : 'light');
    } else {
        root.setAttribute('data-theme', theme);
    }
}

// Listen for system theme changes
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'system') {
        applyTheme('system');
    }
}); 