/**
 * Sitemap generator utility for hotel booking application
 * Generates sitemap.xml content based on application routes
 */

export const generateSitemap = (baseUrl = 'https://your-domain.com') => {
  const routes = [
    {
      path: '/',
      priority: '1.0',
      changefreq: 'weekly',
      lastmod: new Date().toISOString().split('T')[0]
    },
    {
      path: '/search',
      priority: '0.9',
      changefreq: 'daily',
      lastmod: new Date().toISOString().split('T')[0]
    },
    {
      path: '/login',
      priority: '0.6',
      changefreq: 'monthly',
      lastmod: new Date().toISOString().split('T')[0]
    },
    {
      path: '/dashboard',
      priority: '0.7',
      changefreq: 'weekly',
      lastmod: new Date().toISOString().split('T')[0]
    },
    {
      path: '/contact-info',
      priority: '0.5',
      changefreq: 'monthly',
      lastmod: new Date().toISOString().split('T')[0]
    },
    {
      path: '/confirmation',
      priority: '0.5',
      changefreq: 'monthly',
      lastmod: new Date().toISOString().split('T')[0]
    }
  ]

  const xmlHeader = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">`

  const xmlFooter = `</urlset>`

  const urlEntries = routes.map(route => `
  <url>
    <loc>${baseUrl}${route.path}</loc>
    <lastmod>${route.lastmod}</lastmod>
    <changefreq>${route.changefreq}</changefreq>
    <priority>${route.priority}</priority>
  </url>`).join('')

  return `${xmlHeader}${urlEntries}
${xmlFooter}`
}

/**
 * Generate structured data for SEO
 */
export const generateStructuredData = (pageType, data = {}) => {
  const baseStructuredData = {
    "@context": "https://schema.org",
    "@type": "WebApplication",
    "name": "Hotel Booking",
    "description": "Search and book hotels worldwide with our comprehensive booking platform",
    "url": "https://your-domain.com",
    "applicationCategory": "TravelApplication",
    "operatingSystem": "Web Browser"
  }

  switch (pageType) {
    case 'search':
      return {
        ...baseStructuredData,
        "@type": "SearchAction",
        "target": {
          "@type": "EntryPoint",
          "urlTemplate": "https://your-domain.com/search?destination={search_term}",
          "actionPlatform": [
            "http://schema.org/DesktopWebPlatform",
            "http://schema.org/MobileWebPlatform"
          ]
        },
        "query-input": "required name=search_term"
      }
    
    case 'hotel':
      return {
        "@context": "https://schema.org",
        "@type": "Hotel",
        "name": data.name || "Hotel",
        "description": data.description || "Comfortable hotel accommodation",
        "address": {
          "@type": "PostalAddress",
          "addressLocality": data.city || "City",
          "addressCountry": data.country || "Country"
        },
        "priceRange": data.priceRange || "$$",
        "starRating": {
          "@type": "Rating",
          "ratingValue": data.rating || "4"
        }
      }
    
    case 'booking':
      return {
        "@context": "https://schema.org",
        "@type": "LodgingReservation",
        "reservationStatus": "https://schema.org/ReservationConfirmed",
        "lodgingBusiness": {
          "@type": "Hotel",
          "name": data.hotelName || "Hotel"
        },
        "checkinTime": data.checkin,
        "checkoutTime": data.checkout,
        "numAdults": data.guests || 2
      }
    
    default:
      return baseStructuredData
  }
}

/**
 * Meta tags generator for different page types
 */
export const generateMetaTags = (pageType, data = {}) => {
  const baseMeta = {
    'og:site_name': 'Hotel Booking',
    'og:type': 'website',
    'twitter:card': 'summary_large_image',
    'twitter:site': '@hotelbooking'
  }

  switch (pageType) {
    case 'search':
      return {
        ...baseMeta,
        'og:title': 'Search Hotels - Hotel Booking',
        'og:description': 'Find and book the perfect hotel for your next trip. Compare prices and amenities.',
        'og:image': '/images/search-og.jpg',
        'og:url': 'https://your-domain.com/search'
      }
    
    case 'login':
      return {
        ...baseMeta,
        'og:title': 'Login - Hotel Booking',
        'og:description': 'Sign in to your hotel booking account to manage reservations.',
        'og:image': '/images/login-og.jpg',
        'og:url': 'https://your-domain.com/login'
      }
    
    case 'dashboard':
      return {
        ...baseMeta,
        'og:title': 'My Bookings - Hotel Booking',
        'og:description': 'View and manage your hotel reservations and booking history.',
        'og:image': '/images/dashboard-og.jpg',
        'og:url': 'https://your-domain.com/dashboard'
      }
    
    default:
      return baseMeta
  }
} 