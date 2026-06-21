import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                // ─── Primary (Deep Indigo Blue) ───
                'primary':                    '#00236f',
                'on-primary':                 '#ffffff',
                'primary-container':          '#1e3a8a',
                'on-primary-container':       '#90a8ff',
                'inverse-primary':            '#b6c4ff',
                'primary-fixed':              '#dce1ff',
                'primary-fixed-dim':          '#b6c4ff',
                'on-primary-fixed':           '#00164e',
                'on-primary-fixed-variant':   '#264191',

                // ─── Secondary (Warm Amber) ───
                'secondary':                  '#855300',
                'on-secondary':               '#ffffff',
                'secondary-container':        '#fea619',
                'on-secondary-container':     '#684000',
                'secondary-fixed':            '#ffddb8',
                'secondary-fixed-dim':        '#ffb95f',
                'on-secondary-fixed':         '#2a1700',
                'on-secondary-fixed-variant':  '#653e00',

                // ─── Tertiary (Navy Slate) ───
                'tertiary':                   '#1b2b3f',
                'on-tertiary':                '#ffffff',
                'tertiary-container':         '#314156',
                'on-tertiary-container':      '#9dadc6',
                'tertiary-fixed':             '#d3e4fe',
                'tertiary-fixed-dim':         '#b7c8e1',
                'on-tertiary-fixed':          '#0b1c30',
                'on-tertiary-fixed-variant':  '#38485d',

                // ─── Error ───
                'error':                      '#ba1a1a',
                'on-error':                   '#ffffff',
                'error-container':            '#ffdad6',
                'on-error-container':         '#93000a',

                // ─── Surface & Background ───
                'surface':                    '#faf8ff',
                'surface-dim':                '#dad9e1',
                'surface-bright':             '#faf8ff',
                'surface-container-lowest':   '#ffffff',
                'surface-container-low':      '#f4f3fa',
                'surface-container':          '#eeedf4',
                'surface-container-high':     '#e9e7ef',
                'surface-container-highest':  '#e3e1e9',
                'on-surface':                 '#1a1b21',
                'on-surface-variant':         '#444651',
                'surface-variant':            '#e3e1e9',
                'surface-tint':               '#4059aa',

                // ─── Inverse ───
                'inverse-surface':            '#2f3036',
                'inverse-on-surface':         '#f1f0f7',

                // ─── Outline ───
                'outline':                    '#757682',
                'outline-variant':            '#c5c5d3',

                // ─── Background ───
                'background':                 '#faf8ff',
                'on-background':              '#1a1b21',

                // ─── Semantic Status (desaturated per DESIGN.md) ───
                'success':                    '#166534',
                'success-container':          '#dcfce7',
                'on-success-container':       '#166534',
                'info':                       '#1e40af',
                'info-container':             '#dbeafe',
                'warning':                    '#92400e',
                'warning-container':          '#fef3c7',
            },

            fontFamily: {
                'sans':              ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                'display-lg':        ['Inter', 'sans-serif'],
                'headline-lg':       ['Inter', 'sans-serif'],
                'headline-lg-mobile':['Inter', 'sans-serif'],
                'headline-md':       ['Inter', 'sans-serif'],
                'title-lg':          ['Inter', 'sans-serif'],
                'body-lg':           ['Inter', 'sans-serif'],
                'body-md':           ['Inter', 'sans-serif'],
                'label-md':          ['Inter', 'sans-serif'],
            },

            fontSize: {
                'display-lg':        ['48px', { lineHeight: '56px',  letterSpacing: '-0.02em', fontWeight: '700' }],
                'headline-lg':       ['32px', { lineHeight: '40px',  letterSpacing: '-0.01em', fontWeight: '600' }],
                'headline-lg-mobile':['24px', { lineHeight: '32px',  fontWeight: '600' }],
                'headline-md':       ['24px', { lineHeight: '32px',  fontWeight: '600' }],
                'title-lg':          ['20px', { lineHeight: '28px',  fontWeight: '600' }],
                'body-lg':           ['16px', { lineHeight: '24px',  fontWeight: '400' }],
                'body-md':           ['14px', { lineHeight: '20px',  fontWeight: '400' }],
                'label-md':          ['12px', { lineHeight: '16px',  letterSpacing: '0.05em', fontWeight: '500' }],
            },

            spacing: {
                'unit':             '4px',
                'gutter':           '16px',
                'stack-sm':         '8px',
                'stack-md':         '16px',
                'section-gap':      '32px',
                'container-margin': '24px',
            },

            borderRadius: {
                'DEFAULT': '0.25rem',
                'lg':      '0.5rem',
                'xl':      '0.75rem',
                '2xl':     '1rem',     // 16px — cards
                '3xl':     '1.5rem',
            },

            boxShadow: {
                'level-1': '0px 1px 3px rgba(0,0,0,0.05), 0px 10px 15px -3px rgba(0,0,0,0.02)',
                'level-2': '0px 20px 25px -5px rgba(0,0,0,0.1), 0px 10px 10px -5px rgba(0,0,0,0.04)',
            },
        },
    },
    plugins: [forms],
};
