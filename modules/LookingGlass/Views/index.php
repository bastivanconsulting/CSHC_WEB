<div class="container py-5">
    <div class="row mb-5" data-aos="fade-up">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold mb-3">Looking <span class="text-primary">Glass</span></h1>
            <p class="lead text-muted">Diagnostiquez la connectivité réseau depuis notre infrastructure.</p>
        </div>
    </div>

    <div class="row g-4" x-data="lookingGlass()">
        <!-- Tools Section -->
        <div class="col-lg-8" data-aos="fade-right">
            <div class="card h-100 border-0 shadow-lg" style="background: rgba(255,255,255,0.03); backdrop-filter: blur(10px);">
                <div class="card-body p-4">
                    <h5 class="card-title text-white mb-4"><i class="fas fa-terminal me-2 text-primary"></i>Outils Réseau</h5>

                    <form @submit.prevent="executeCommand">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label text-muted small">Cible (IP ou Domaine)</label>
                                <input type="text" x-model="target" class="form-control bg-dark text-white border-secondary" placeholder="ex: 8.8.8.8 ou google.com" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Commande</label>
                                <select x-model="command" class="form-select bg-dark text-white border-secondary">
                                    <option value="ping">Ping</option>
                                    <option value="traceroute">Traceroute</option>
                                    <option value="mtr">MTR</option>
                                </select>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                                    <span x-show="!loading"><i class="fas fa-play me-2"></i>Exécuter le test</span>
                                    <span x-show="loading"><i class="fas fa-spinner fa-spin me-2"></i>Exécution en cours...</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Terminal Output -->
                    <div class="mt-4">
                        <label class="form-label text-muted small">Résultat</label>
                        <div class="p-3 rounded font-monospace" style="background: #0d1117; min-height: 300px; color: #3fb950; overflow-x: auto; white-space: pre;" x-text="output || 'En attente de commande...'"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info & Downloads -->
        <div class="col-lg-4" data-aos="fade-left">
            <!-- Node Info -->
            <div class="card border-0 shadow-lg mb-4" style="background: rgba(255,255,255,0.03); backdrop-filter: blur(10px);">
                <div class="card-body p-4">
                    <h5 class="card-title text-white mb-3"><i class="fas fa-server me-2 text-primary"></i>Information Noeud</h5>
                    <ul class="list-unstyled text-muted small mb-0">
                        <li class="mb-2 d-flex justify-content-between">
                            <span>Emplacement:</span>
                            <span class="text-white">Paris, France (AZ1)</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span>Réseau:</span>
                            <span class="text-white">AS214939</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span>Connectivité:</span>
                            <span class="text-white">150 Gbps</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Downloads -->
            <div class="card border-0 shadow-lg" style="background: rgba(255,255,255,0.03); backdrop-filter: blur(10px);">
                <div class="card-body p-4">
                    <h5 class="card-title text-white mb-4"><i class="fas fa-download me-2 text-primary"></i>Fichiers Test</h5>
                    <div class="d-grid gap-3">
                        <a href="/looking-glass/download/10MB" class="btn btn-outline-light d-flex justify-content-between align-items-center">
                            <span>10 MB</span>
                            <i class="fas fa-file-arrow-down"></i>
                        </a>
                        <a href="/looking-glass/download/100MB" class="btn btn-outline-light d-flex justify-content-between align-items-center">
                            <span>100 MB</span>
                            <i class="fas fa-file-arrow-down"></i>
                        </a>
                        <a href="/looking-glass/download/1GB" class="btn btn-outline-light d-flex justify-content-between align-items-center">
                            <span>1 GB</span>
                            <i class="fas fa-file-arrow-down"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('lookingGlass', () => ({
            target: '',
            command: 'ping',
            output: '',
            loading: false,

            async executeCommand() {
                this.loading = true;
                this.output = 'Initialisation du test...\n';

                try {
                    const response = await fetch('/looking-glass/execute', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            target: this.target,
                            command: this.command
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.output = data.output;
                    } else {
                        this.output = 'Erreur: ' + (data.error || 'Une erreur inconnue est survenue.');
                    }
                } catch (e) {
                    this.output = 'Erreur réseau: Impossible de contacter le serveur.';
                    console.error(e);
                } finally {
                    this.loading = false;
                }
            }
        }))
    })
</script>
