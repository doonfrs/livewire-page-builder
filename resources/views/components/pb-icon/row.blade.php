<svg {{ $attributes }} viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect width="40" height="40" rx="8" fill="url(#row-bg)"/>
  <!-- Row layout: 3 columns -->
  <rect x="5" y="10" width="8" height="22" rx="3" fill="white" fill-opacity="0.85"/>
  <rect x="16" y="10" width="8" height="22" rx="3" fill="white" fill-opacity="0.85"/>
  <rect x="27" y="10" width="8" height="22" rx="3" fill="white" fill-opacity="0.85"/>
  <!-- Plus icon in center column indicating add -->
  <rect x="19.25" y="18.5" width="1.5" height="5" rx="0.75" fill="url(#row-bg)"/>
  <rect x="17" y="20.25" width="6" height="1.5" rx="0.75" fill="url(#row-bg)"/>
  <!-- Dots in first and last columns -->
  <circle cx="9" cy="21" r="1.5" fill="url(#row-bg)" fill-opacity="0.6"/>
  <circle cx="31" cy="21" r="1.5" fill="url(#row-bg)" fill-opacity="0.6"/>
  <defs>
    <linearGradient id="row-bg" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
      <stop stop-color="#0D9488"/><stop offset="1" stop-color="#0F766E"/>
    </linearGradient>
  </defs>
</svg>
