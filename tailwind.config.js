const defaultTheme = require("tailwindcss/defaultTheme");
const colors = require("tailwindcss/colors");

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: "class",

    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/jetstream/**/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        colors: {
            // Build your palette here
            transparent: "transparent",
            current: "currentColor",
            gray: colors.gray,
            red: colors.rose,
            blue: colors.blue,
            sky: colors.sky,
            yellow: colors.amber,
            lime: colors.sky,
            green: colors.green,
            indigo: colors.sky,
            pink: colors.pink,
            white: colors.white,
        },
        extend: {
            fontFamily: {
                sans: ["Nunito", ...defaultTheme.fontFamily.sans],
            },

            keyframes: {
                life: {
                    "0%": { width: "100%" },
                    "100%": { width: "0%" },
                },
            },
            animation: {
                life: "life 4750ms linear forwards",
            },

            backgroundImage: (theme) => ({
                "image-one": "url('https://moe.gov.et/logom.png')",
                "image-two": "url('https://moe.gov.et/fotlogo.png')",
            }),
        },
    },

    variants: {
        extend: {
            backgroundImage: ["dark"],
        },
    },

    plugins: [
        require("@tailwindcss/forms"),
        require("@tailwindcss/typography"),
    ],
};
