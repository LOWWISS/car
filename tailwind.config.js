/**
 * Tailwind CSS configuration (reference).
 * This project uses the Tailwind Play CDN for delivery (no Node build pipeline
 * required). The same config is applied inline in app/Views/layouts/header.php
 * via the `tailwind.config = {...}` script. If you later adopt a real Tailwind
 * CLI build, copy this file to the project root and run `npx tailwindcss -i
 * public/assets/css/app.css -o public/assets/css/app.min.css --minify`.
 */
module.exports = {
  content: ['./app/Views/**/*.php'],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        // Auction / urgency accent (countdowns, highest bid)
        auction: {
          DEFAULT: '#ef4444',
          dark: '#b91c1c',
        },
      },
    },
  },
  plugins: [],
};
