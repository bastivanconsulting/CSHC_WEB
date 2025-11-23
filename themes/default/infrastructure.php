<div class="hero-section text-center py-5 position-relative overflow-hidden">
    <div class="container py-5" data-aos="fade-up">
        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mb-3 px-3 py-2 rounded-pill">
            <i class="fas fa-network-wired me-2"></i>Réseau Tier-1
        </span>
        <h1 class="display-3 fw-bold text-white mb-4">Une Infrastructure <span class="text-gradient">Globale</span></h1>
        <p class="lead text-muted mb-5 mx-auto" style="max-width: 700px;">
            Déployez vos services sur notre backbone de 150 Gbps. Une redondance totale pour une disponibilité garantie à 99.99%.
        </p>
    </div>

    <!-- Abstract Background Element -->
    <div class="position-absolute top-50 start-50 translate-middle" style="z-index: -1; opacity: 0.1;">
        <i class="fas fa-globe text-primary" style="font-size: 50vw;"></i>
    </div>
</div>

<div class="container mb-5">
    <!-- Stats Row -->
    <div class="row g-4 mb-5">
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card h-100 bg-dark border-secondary border-opacity-25 glass-card">
                <div class="card-body text-center p-4">
                    <div class="icon-box mb-3 text-primary display-4">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <h3 class="text-white fw-bold">150 Gbps</h3>
                    <p class="text-muted">Capacité Backbone</p>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card h-100 bg-dark border-secondary border-opacity-25 glass-card">
                <div class="card-body text-center p-4">
                    <div class="icon-box mb-3 text-primary display-4">
                        <i class="fas fa-server"></i>
                    </div>
                    <h3 class="text-white fw-bold">5</h3>
                    <p class="text-muted">Zones de Disponibilité (AZ)</p>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card h-100 bg-dark border-secondary border-opacity-25 glass-card">
                <div class="card-body text-center p-4">
                    <div class="icon-box mb-3 text-primary display-4">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-white fw-bold">VAC</h3>
                    <p class="text-muted">Protection Anti-DDoS Standard</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Network Details with Alpine.js interaction -->
    <div class="row align-items-center mb-5" x-data="{ activeTab: 'fr' }">
        <div class="col-lg-5" data-aos="fade-right">
            <h2 class="text-white fw-bold mb-4">Nos Localisations</h2>
            <p class="text-muted mb-4">Une présence stratégique au coeur de l'Europe.</p>

            <div class="d-flex flex-column gap-3">
                <button @click="activeTab = 'fr'" :class="activeTab === 'fr' ? 'bg-primary text-white border-primary' : 'bg-transparent text-muted border-secondary'" class="btn text-start p-3 border border-opacity-25 d-flex align-items-center transition-all">
                    <i class="fas fa-map-marker-alt me-3"></i>
                    <div>
                        <strong class="d-block">France</strong>
                        <small>Roubaix, Paris, Graveline</small>
                    </div>
                </button>
                <button @click="activeTab = 'pl'" :class="activeTab === 'pl' ? 'bg-primary text-white border-primary' : 'bg-transparent text-muted border-secondary'" class="btn text-start p-3 border border-opacity-25 d-flex align-items-center transition-all">
                    <i class="fas fa-map-marker-alt me-3"></i>
                    <div>
                        <strong class="d-block">Pologne</strong>
                        <small>Varsovie</small>
                    </div>
                </button>
                <button @click="activeTab = 'de'" :class="activeTab === 'de' ? 'bg-primary text-white border-primary' : 'bg-transparent text-muted border-secondary'" class="btn text-start p-3 border border-opacity-25 d-flex align-items-center transition-all">
                    <i class="fas fa-map-marker-alt me-3"></i>
                    <div>
                        <strong class="d-block">Allemagne</strong>
                        <small>Limburg</small>
                    </div>
                </button>
            </div>
        </div>
        <div class="col-lg-7" data-aos="fade-left">
            <div class="card bg-dark border-secondary border-opacity-25 h-100 p-4 position-relative overflow-hidden">
                <!-- Dynamic Content based on Alpine state -->
                <div x-show="activeTab === 'fr'" x-transition:enter="animate__animated animate__fadeIn">
                    <h4 class="text-white mb-3">France</h4>
                    <p class="text-muted">Nos infrastructures historiques haute performance.</p>
                    <ul class="list-unstyled text-muted mt-3">
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Roubaix (1-AZ)</li>
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Paris (3-AZ)</li>
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Graveline (1-AZ)</li>
                    </ul>
                </div>

                <div x-show="activeTab === 'pl'" x-transition:enter="animate__animated animate__fadeIn" style="display: none;">
                    <h4 class="text-white mb-3">Pologne</h4>
                    <p class="text-muted">Point de présence stratégique pour l'Europe de l'Est.</p>
                    <ul class="list-unstyled text-muted mt-3">
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Varsovie (1-AZ)</li>
                    </ul>
                </div>

                <div x-show="activeTab === 'de'" x-transition:enter="animate__animated animate__fadeIn" style="display: none;">
                    <h4 class="text-white mb-3">Allemagne</h4>
                    <p class="text-muted">Interconnexion directe avec les principaux exchanges européens.</p>
                    <ul class="list-unstyled text-muted mt-3">
                        <li class="mb-2"><i class="fas fa-check text-primary me-2"></i>Limburg (1-AZ)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Quick inline styles to match the request until moved to main CSS */
    .glass-card {
        background: rgba(255, 255, 255, 0.03) !important;
        backdrop-filter: blur(10px);
    }
    .text-gradient {
        background: linear-gradient(45deg, #2563eb, #60a5fa);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
</style>