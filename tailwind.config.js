const defaultTheme = require("tailwindcss/defaultTheme");
const colors = require("tailwindcss/colors");

// Ministry of Education brand palette, sampled from the MoE logo.
// brand-600 (#1E4EA2) is the logo blue; gold-400 (#F6BA12) is the book accent.
const brand = {
    50: "#f0f5fc",
    100: "#dde8f8",
    200: "#c3d6f2",
    300: "#9abbe8",
    400: "#6a97da",
    500: "#4474cb",
    600: "#1e4ea2",
    700: "#1a4289",
    800: "#183871",
    900: "#16305d",
    950: "#0d1d3b",
};

const gold = {
    50: "#fefbe8",
    100: "#fef5c3",
    200: "#fde98a",
    300: "#fbd647",
    400: "#f6ba12",
    500: "#e6a106",
    600: "#c67b03",
    700: "#9e5706",
    800: "#82450d",
    900: "#6e3911",
};

const ember = {
    50: "#fdf4f2",
    100: "#fbe6e2",
    200: "#f8d0c9",
    300: "#f1aea3",
    400: "#e57f6e",
    500: "#cc4e3c",
    600: "#b83f2e",
    700: "#9a3225",
    800: "#802d23",
    900: "#6b2a22",
};

// Tailwind 3.1 ships without the 950 shades, so add the ones the views use
const slate = { ...colors.slate, 950: "#020617" };
const rose = { ...colors.rose, 950: "#4c0519" };
const amber = { ...colors.amber, 950: "#451a03" };
const emerald = { ...colors.emerald, 950: "#022c22" };

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: "class",

    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/jetstream/**/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./app/**/*.php",
    ],

    // KPI card styles are picked at runtime from controller data
    safelist: [
        {
            pattern:
                /^(bg|text|border|ring)-(brand|gold|ember|emerald|amber|teal|slate|indigo|blue|rose|sky|violet)-(50|100|200|500|600|700|900)$/,
            variants: ["dark"],
        },
    ],

    theme: {
        colors: {
            transparent: "transparent",
            current: "currentColor",
            black: colors.black,
            white: colors.white,

            brand,
            gold,
            ember,

            // Legacy names used throughout the views now resolve to the brand palette,
            // so older screens pick up the new look without per-file edits.
            blue: brand,
            indigo: brand,
            lime: brand,

            gray: slate,
            slate: slate,
            red: rose,
            rose: rose,
            yellow: amber,
            amber: amber,
            orange: colors.orange,
            green: emerald,
            emerald: emerald,
            teal: colors.teal,
            sky: colors.sky,
            cyan: colors.cyan,
            purple: colors.violet,
            violet: colors.violet,
            pink: colors.pink,
        },
        extend: {
            fontFamily: {
                sans: ["Nunito", "Noto Sans Ethiopic", ...defaultTheme.fontFamily.sans],
            },

            boxShadow: {
                card: "0 1px 2px 0 rgb(15 23 42 / 0.04), 0 1px 3px 0 rgb(15 23 42 / 0.06)",
                "card-hover": "0 4px 12px -2px rgb(15 23 42 / 0.08), 0 2px 6px -2px rgb(15 23 42 / 0.06)",
                brand: "0 6px 20px -6px rgb(30 78 162 / 0.45)",
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
                "image-one": "url('/logom.png')",
                "image-two": "url('/logom.png')",
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
