import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    important: '.hugo-app',
    content: [
        './assets/src/js/**/*.{js,jsx,vue}',
        './blocks/**/*.block',
        './components/**/*.{htm,php}',
        './controllers/**/*.{htm,php}',
        './formwidgets/**/*.{htm,php}',
        './reportwidgets/**/*.{htm,php}',
        './widgets/**/*.{htm,php}',
    ],
    // plugins: [forms],
    corePlugins: {
        preflight: false,
    },
    fontFamily: {
        sans: ['Rubik'],
    },
};
