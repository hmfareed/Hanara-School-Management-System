---
name: Hanara Institutional Core
colors:
  surface: '#faf8ff'
  surface-dim: '#dad9e1'
  surface-bright: '#faf8ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f4f3fa'
  surface-container: '#eeedf4'
  surface-container-high: '#e9e7ef'
  surface-container-highest: '#e3e1e9'
  on-surface: '#1a1b21'
  on-surface-variant: '#444651'
  inverse-surface: '#2f3036'
  inverse-on-surface: '#f1f0f7'
  outline: '#757682'
  outline-variant: '#c5c5d3'
  surface-tint: '#4059aa'
  primary: '#00236f'
  on-primary: '#ffffff'
  primary-container: '#1e3a8a'
  on-primary-container: '#90a8ff'
  inverse-primary: '#b6c4ff'
  secondary: '#855300'
  on-secondary: '#ffffff'
  secondary-container: '#fea619'
  on-secondary-container: '#684000'
  tertiary: '#1b2b3f'
  on-tertiary: '#ffffff'
  tertiary-container: '#314156'
  on-tertiary-container: '#9dadc6'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dce1ff'
  primary-fixed-dim: '#b6c4ff'
  on-primary-fixed: '#00164e'
  on-primary-fixed-variant: '#264191'
  secondary-fixed: '#ffddb8'
  secondary-fixed-dim: '#ffb95f'
  on-secondary-fixed: '#2a1700'
  on-secondary-fixed-variant: '#653e00'
  tertiary-fixed: '#d3e4fe'
  tertiary-fixed-dim: '#b7c8e1'
  on-tertiary-fixed: '#0b1c30'
  on-tertiary-fixed-variant: '#38485d'
  background: '#faf8ff'
  on-background: '#1a1b21'
  surface-variant: '#e3e1e9'
typography:
  display-lg:
    fontFamily: Inter
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  title-lg:
    fontFamily: Inter
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.05em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  unit: 4px
  container-margin: 24px
  gutter: 16px
  section-gap: 32px
  stack-sm: 8px
  stack-md: 16px
---

## Brand & Style

This design system is built to convey a sense of academic excellence and modern administrative efficiency. The aesthetic is rooted in **Modern Minimalism** with a focus on high-utility SaaS patterns. It prioritizes clarity over decoration, ensuring that administrators, teachers, and parents can navigate complex data without cognitive fatigue. 

The emotional response should be one of stability and "quiet intelligence." By using generous white space and a structured information hierarchy, the interface feels expansive and organized. The "edtech" influence manifests through crisp lines, a disciplined color palette, and subtle functional transitions that emphasize reliability and precision.

## Colors

The palette is anchored by a deep **Indigo Blue**, representing authority and the institutional history of the school. This is balanced by a **Warm Amber** accent, used sparingly to draw attention to primary actions, notifications, or status highlights, injecting energy into the professional core.

- **Primary (#1E3A8A):** Used for navigation sidebars, primary buttons, and headers.
- **Secondary (#F59E0B):** Reserved for "Call to Action" moments, active states, and critical highlights.
- **Neutral/Surface:** The background utilizes a soft off-white to reduce screen glare, while content cards use pure white to pop against the background. 
- **Status Colors:** Use standard semantic greens (Success), reds (Error), and blues (Info), but desaturate them slightly to maintain the "calm" brand personality.

## Typography

The typography relies entirely on **Inter** to achieve a systematic, utilitarian feel. The scale emphasizes clear differentiation between data labels and content.

- **Headlines:** Use a tighter letter-spacing and heavier weights to create a strong visual anchor for page titles.
- **Body Text:** Use `body-md` (14px) for the majority of dashboard data and forms to maximize information density while maintaining legibility. 
- **Labels:** Use all-caps for `label-md` when used in table headers or category tags to create a distinct architectural feel.
- **Color Application:** Headings should use the darkest neutral (Slate 900), while body text should use a slightly softer Slate 700 to ensure a comfortable reading experience.

## Layout & Spacing

The design system utilizes a **Fluid Grid** with fixed-width sidebars. The main content area adapts to the viewport, ensuring data-heavy tables and dashboards utilize all available real estate.

- **Desktop:** 12-column grid with 24px margins. Content is housed in "Surface" cards that span relevant column counts (e.g., a 4-column sidebar and 8-column main feed).
- **Tablet:** 8-column grid with 16px margins. Sidebars transition to a collapsed icon-only state or a hidden drawer.
- **Mobile:** Single column with 16px margins. Vertical stacking is mandatory for all card elements.
- **Spacing Rhythm:** All spacing must be a multiple of 4px. Use 16px (stack-md) for internal card padding and 32px (section-gap) to separate distinct functional blocks.

## Elevation & Depth

This design system uses **Tonal Layering** combined with **Ambient Shadows** to create a sense of organized depth.

- **Level 0 (Background):** Soft off-white (#F8FAFC). No shadow.
- **Level 1 (Cards/Surface):** Pure White (#FFFFFF). A very soft, diffused shadow: `0px 1px 3px rgba(0,0,0,0.05), 0px 10px 15px -3px rgba(0,0,0,0.02)`. This makes the card feel like it is resting gently on the background.
- **Level 2 (Dropdowns/Modals):** Pure White. A more pronounced shadow to indicate focus: `0px 20px 25px -5px rgba(0,0,0,0.1), 0px 10px 10px -5px rgba(0,0,0,0.04)`.
- **Borders:** Use a 1px solid border (#E2E8F0) on cards even when shadows are present to maintain crisp definition in high-brightness environments.

## Shapes

The shape language is defined by a consistent **16px (rounded-lg)** corner radius for primary containers and cards. This large radius softens the "institutional" feel of the blue palette, making the software feel modern and approachable.

- **Buttons & Inputs:** Use 8px (0.5rem) to maintain a more functional, "tool-like" appearance.
- **Outer Containers/Cards:** Use 16px (1rem) for the main dashboard widgets.
- **Avatars:** Circular (Full rounded) to contrast against the geometric grid.

## Components

- **Buttons:** 
    - *Primary:* Indigo background, white text. No gradient. 
    - *Accent:* Warm Amber background, dark blue or white text. Used for "Add New," "Submit," or "Upgrade."
    - *Ghost:* No background, Indigo border. Used for secondary actions like "Cancel" or "Export."
- **Input Fields:** 8px rounded corners with a 1px light gray border. On focus, the border transitions to Indigo with a subtle 2px outer glow in the same color (low opacity).
- **Cards:** The workhorse of the system. 16px rounded corners, white background, soft shadow. Title should be `title-lg` with a bottom divider separating the header from the content.
- **Chips/Badges:** Small, 4px rounded corners or pill-shaped. High contrast background-to-text ratio (e.g., light green background with dark green text for "Paid" status).
- **Lists:** Clean rows with 1px horizontal dividers. On hover, the row background should change to a very light blue-tinted gray to indicate interactivity.
- **Icons:** Use 24px simple line icons (2px stroke weight). Avoid filled icons unless indicating an active navigation state.