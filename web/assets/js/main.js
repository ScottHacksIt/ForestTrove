/* ============================================================
   Forest Trove — Main JavaScript
   Handles: nav hamburger, image preview, browse sort/filter,
            haversine distance, Google Maps initialization
   ============================================================ */

/* ---------- Nav Hamburger ---------- */
(function () {
    const toggle = document.getElementById('nav-toggle');
    const links  = document.getElementById('nav-links');
    if (toggle && links) {
        toggle.addEventListener('click', () => links.classList.toggle('open'));
    }
})();

/* ---------- Image Preview ---------- */
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview) return;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/* ---------- Haversine Distance (km) ---------- */
function haversine(lat1, lon1, lat2, lon2) {
    const R   = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a   = Math.sin(dLat / 2) ** 2
              + Math.cos(lat1 * Math.PI / 180)
              * Math.cos(lat2 * Math.PI / 180)
              * Math.sin(dLon / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function formatDistance(km) {
    if (km < 1) return Math.round(km * 1000) + ' m away';
    return km.toFixed(1) + ' km away';
}

/* ---------- Browse Page: Distance Sort ---------- */
(function () {
    if (typeof window.FT_TREASURES === 'undefined') return;

    const sortSelect = document.getElementById('sort-select');
    const grid       = document.getElementById('treasure-grid');
    const statusEl   = document.getElementById('location-status');

    if (!sortSelect || !grid) return;

    let userLat = null;
    let userLng = null;
    let locationFetched = false;

    function sortByDistance() {
        if (userLat === null) return;

        const cards = Array.from(grid.children);

        // Compute distances
        cards.forEach(card => {
            const lat = parseFloat(card.dataset.lat);
            const lng = parseFloat(card.dataset.lng);
            if (!isNaN(lat) && !isNaN(lng)) {
                card._dist = haversine(userLat, userLng, lat, lng);
            } else {
                card._dist = Infinity;
            }
        });

        // Sort cards
        cards.sort((a, b) => (a._dist ?? Infinity) - (b._dist ?? Infinity));
        cards.forEach(card => grid.appendChild(card));

        // Show distance labels
        document.querySelectorAll('.distance-label').forEach(label => {
            const lat = parseFloat(label.dataset.lat);
            const lng = parseFloat(label.dataset.lng);
            if (!isNaN(lat) && !isNaN(lng)) {
                const dist = haversine(userLat, userLng, lat, lng);
                label.textContent = '📍 ' + formatDistance(dist);
                label.style.display = 'block';
            }
        });
    }

    function requestLocation() {
        if (locationFetched) { sortByDistance(); return; }
        if (!navigator.geolocation) {
            if (statusEl) statusEl.textContent = 'Location not supported by your browser.';
            return;
        }
        if (statusEl) statusEl.textContent = '📡 Getting your location…';
        navigator.geolocation.getCurrentPosition(
            pos => {
                userLat        = pos.coords.latitude;
                userLng        = pos.coords.longitude;
                locationFetched = true;
                if (statusEl) statusEl.textContent = '📍 Location found!';
                sortByDistance();
            },
            () => {
                if (statusEl) statusEl.textContent = 'Could not get location.';
                // Revert sort select
                sortSelect.value = 'date';
            }
        );
    }

    sortSelect.addEventListener('change', () => {
        if (sortSelect.value === 'distance') {
            requestLocation();
        }
    });

    // If page loads with distance selected (e.g. after form submit), request immediately
    if (window.FT_SORT_BY === 'distance') {
        requestLocation();
    }
})();

/* ---------- Google Maps ---------- */
function initForestMap() {
    if (typeof window.FT_MAP_PINS === 'undefined') return;

    const defaultCenter = { lat: 44.5, lng: -90.0 }; // fallback center (Wisconsin)
    const mapEl = document.getElementById('map-container');
    if (!mapEl) return;

    const map = new google.maps.Map(mapEl, {
        zoom: 10,
        center: defaultCenter,
        mapTypeId: 'terrain',
        styles: [
            { featureType: 'poi.park',     elementType: 'geometry', stylers: [{ color: '#b5d99c' }] },
            { featureType: 'landscape.natural', elementType: 'geometry', stylers: [{ color: '#d0e8c0' }] },
            { featureType: 'water',        elementType: 'geometry', stylers: [{ color: '#9bbfe0' }] },
        ]
    });

    const infoWindow = new google.maps.InfoWindow();

    // Add markers for each treasure
    window.FT_MAP_PINS.forEach(pin => {
        if (!pin.lat || !pin.lng) return;

        const marker = new google.maps.Marker({
            position: { lat: pin.lat, lng: pin.lng },
            map,
            title: pin.name,
            icon: {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(
                    '<svg xmlns="http://www.w3.org/2000/svg" width="36" height="42" viewBox="0 0 36 42">' +
                    '<ellipse cx="18" cy="39" rx="8" ry="3" fill="rgba(0,0,0,0.2)"/>' +
                    '<path d="M18 2C10.3 2 4 8.3 4 16c0 10.5 14 24 14 24S32 26.5 32 16C32 8.3 25.7 2 18 2z" fill="#2d5a27" stroke="#fff" stroke-width="1.5"/>' +
                    '<text x="18" y="21" text-anchor="middle" font-size="14" fill="white">🪨</text>' +
                    '</svg>'
                ),
                scaledSize: new google.maps.Size(36, 42),
                anchor: new google.maps.Point(18, 42),
            }
        });

        marker.addListener('click', () => {
            const imgTag = pin.imagePath
                ? `<img src="${pin.imagePath}" style="width:100%;height:100px;object-fit:cover;border-radius:6px;margin-bottom:8px;display:block;">`
                : '<div style="width:100%;height:80px;background:#eaf5e1;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:2rem;margin-bottom:8px;">🪨</div>';

            infoWindow.setContent(
                `<div style="font-family:Georgia,serif;max-width:200px;">
                    ${imgTag}
                    <strong style="font-size:1rem;">${escHtml(pin.name)}</strong><br>
                    <span style="font-size:0.82rem;color:#6b6b6b;">🌲 ${escHtml(pin.forestName)}</span><br>
                    <a href="${pin.url}" style="display:inline-block;margin-top:8px;font-size:0.85rem;color:#2d5a27;font-weight:bold;">View Details →</a>
                </div>`
            );
            infoWindow.open(map, marker);
        });
    });

    // Center on user location
    function locateUser() {
        const statusEl = document.getElementById('map-status');
        if (!navigator.geolocation) {
            if (statusEl) statusEl.textContent = 'Geolocation is not supported by your browser.';
            return;
        }
        if (statusEl) statusEl.textContent = '📡 Getting your location…';
        navigator.geolocation.getCurrentPosition(
            pos => {
                const center = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                map.setCenter(center);
                map.setZoom(12);
                new google.maps.Marker({
                    position: center,
                    map,
                    title: 'You are here',
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 10,
                        fillColor: '#4a90d9',
                        fillOpacity: 1,
                        strokeColor: '#fff',
                        strokeWeight: 2,
                    }
                });
                if (statusEl) statusEl.textContent = '📍 Centered on your location';
            },
            err => {
                const messages = {
                    1: 'Location access denied. Please allow location access in your browser settings and try again.',
                    2: 'Location unavailable. Please try again.',
                    3: 'Location request timed out. Please try again.',
                };
                if (statusEl) statusEl.textContent = messages[err.code] || 'Could not get location.';
            },
            { enableHighAccuracy: false, timeout: 10000 }
        );
    }

    // Auto-locate on load
    locateUser();

    const locateBtn = document.getElementById('btn-locate');
    if (locateBtn) locateBtn.addEventListener('click', locateUser);
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
