// Configuration de l'API
const API_CONFIG = {
    baseURL: 'http://localhost:8000/api',
    timeout: 30000, // 30 secondes (augmenté pour éviter les timeouts)
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
};

// Gestionnaire de tokens
class TokenManager {
    static setToken(token) {
        localStorage.setItem('auth_token', token);
        localStorage.setItem('token_timestamp', Date.now().toString());
    }

    static getToken() {
        const token = localStorage.getItem('auth_token');
        const timestamp = localStorage.getItem('token_timestamp');
        
        // Vérifier si le token n'est pas trop ancien (24h)
        if (token && timestamp) {
            const tokenAge = Date.now() - parseInt(timestamp);
            const maxAge = 24 * 60 * 60 * 1000; // 24 heures
            
            if (tokenAge < maxAge) {
                return token;
            } else {
                this.clearToken();
            }
        }
        return null;
    }

    static clearToken() {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('token_timestamp');
        localStorage.removeItem('user_data');
    }

    static isAuthenticated() {
        return this.getToken() !== null;
    }
}

// Gestionnaire d'utilisateur
class UserManager {
    static setUser(userData) {
        console.log('Sauvegarde des données utilisateur:', userData);
        if (userData && typeof userData === 'object') {
            localStorage.setItem('user_data', JSON.stringify(userData));
        } else {
            console.error('Données utilisateur invalides:', userData);
        }
    }

    static getUser() {
        const userData = localStorage.getItem('user_data');
        const parsed = userData ? JSON.parse(userData) : null;
        console.log('Récupération des données utilisateur:', parsed);
        return parsed;
    }

    static clearUser() {
        localStorage.removeItem('user_data');
        console.log('Données utilisateur supprimées');
    }
}

// Client API
class ApiClient {
    static async makeRequest(endpoint, options = {}, retryCount = 0) {
        const url = `${API_CONFIG.baseURL}${endpoint}`;
        const token = TokenManager.getToken();
        const maxRetries = 2;

        const config = {
            method: options.method || 'GET',
            headers: {
                ...API_CONFIG.headers,
                ...(token && { 'Authorization': `Bearer ${token}` }),
                ...options.headers
            },
            ...options
        };

        if (options.body && typeof options.body === 'object') {
            config.body = JSON.stringify(options.body);
        }

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), API_CONFIG.timeout);

            console.log(`Requête API (tentative ${retryCount + 1}):`, {
                url: url,
                method: config.method,
                headers: config.headers,
                body: config.body
            });

            const response = await fetch(url, {
                ...config,
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            const data = await response.json();

            console.log('Réponse API:', {
                status: response.status,
                ok: response.ok,
                data: data
            });

            return {
                success: response.ok,
                status: response.status,
                data: data,
                response: response
            };
        } catch (error) {
            console.error(`Erreur API (tentative ${retryCount + 1}):`, error);

            // Retry logic pour les erreurs de réseau
            if (retryCount < maxRetries && (
                error.name === 'AbortError' ||
                error.message.includes('Failed to fetch') ||
                error.message.includes('NetworkError')
            )) {
                console.log(`Nouvelle tentative dans 2 secondes... (${retryCount + 1}/${maxRetries})`);
                await new Promise(resolve => setTimeout(resolve, 2000));
                return this.makeRequest(endpoint, options, retryCount + 1);
            }

            // Messages d'erreur améliorés
            if (error.name === 'AbortError') {
                throw new Error('Timeout: La requête a pris trop de temps. Vérifiez que le serveur Laravel est démarré et répond correctement.');
            }
            if (error.message.includes('Failed to fetch')) {
                throw new Error('Erreur de connexion: Impossible de joindre le serveur. Vérifiez que Laravel est démarré sur http://localhost:8000');
            }
            if (error.message.includes('NetworkError')) {
                throw new Error('Erreur réseau: Problème de connexion au serveur');
            }
            throw new Error(`Erreur: ${error.message}`);
        }
    }

    // Méthodes d'authentification
    static async register(userData) {
        return this.makeRequest('/auth/register', {
            method: 'POST',
            body: userData
        });
    }

    static async login(credentials) {
        return this.makeRequest('/auth/login', {
            method: 'POST',
            body: credentials
        });
    }

    static async getUser() {
        const result = await this.makeRequest('/auth/user');
        console.log('Réponse API getUser:', result);
        return result;
    }

    static async logout() {
        return this.makeRequest('/auth/logout', {
            method: 'POST'
        });
    }

    // Méthodes de récupération de mot de passe
    static async sendPasswordResetEmail(email) {
        return this.makeRequest('/auth/password/email', {
            method: 'POST',
            body: { email }
        });
    }

    static async resetPassword(resetData) {
        return this.makeRequest('/auth/password/reset', {
            method: 'POST',
            body: resetData
        });
    }

    static async verifyPasswordResetToken(token, email) {
        return this.makeRequest('/auth/password/verify-token', {
            method: 'POST',
            body: { token, email }
        });
    }

    static async checkApiHealth() {
        try {
            return await this.makeRequest('/auth/register', {
                method: 'OPTIONS'
            });
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
}

// Gestionnaire de notifications
class NotificationManager {
    static show(message, type = 'info', duration = 5000) {
        // Supprimer les notifications existantes
        const existing = document.querySelectorAll('.notification');
        existing.forEach(notif => notif.remove());

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${this.getIcon(type)}"></i>
                <span>${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        // Animation d'entrée
        setTimeout(() => notification.classList.add('show'), 100);

        // Suppression automatique
        if (duration > 0) {
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, duration);
        }
    }

    static getIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
}

// Gestionnaire de formulaires
class FormManager {
    static validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    static validatePassword(password) {
        return password.length >= 8;
    }

    static validateRequired(value) {
        return value && value.trim().length > 0;
    }

    static showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        // Supprimer l'erreur existante
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }

        // Ajouter la nouvelle erreur
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.textContent = message;
        field.parentNode.appendChild(errorElement);
        
        // Ajouter la classe d'erreur au champ
        field.classList.add('error');
    }

    static clearFieldError(fieldId) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        const errorElement = field.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
        field.classList.remove('error');
    }

    static clearAllErrors(formElement) {
        const errors = formElement.querySelectorAll('.field-error');
        errors.forEach(error => error.remove());
        
        const errorFields = formElement.querySelectorAll('.error');
        errorFields.forEach(field => field.classList.remove('error'));
    }
}

// Gestionnaire de chargement
class LoadingManager {
    static show(element, text = 'Chargement...') {
        if (typeof element === 'string') {
            element = document.getElementById(element);
        }
        
        if (!element) return;

        element.disabled = true;
        element.dataset.originalText = element.textContent;
        element.innerHTML = `
            <i class="fas fa-spinner fa-spin"></i>
            ${text}
        `;
    }

    static hide(element) {
        if (typeof element === 'string') {
            element = document.getElementById(element);
        }
        
        if (!element) return;

        element.disabled = false;
        element.textContent = element.dataset.originalText || 'Valider';
    }
}

// Utilitaires
class Utils {
    static formatDate(date) {
        return new Intl.DateTimeFormat('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(new Date(date));
    }



    static debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialisation globale
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier l'authentification sur toutes les pages
    const currentPage = window.location.pathname.split('/').pop();
    const protectedPages = ['profile.html'];
    const authPages = ['login.html', 'register.html'];

    // Rediriger vers login si on essaie d'accéder au profil sans être connecté
    if (protectedPages.includes(currentPage) && !TokenManager.isAuthenticated()) {
        NotificationManager.show('Vous devez être connecté pour accéder à cette page', 'warning');
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 2000);
    }

    // Pour les pages d'authentification, ne pas rediriger automatiquement
    // Laisser l'utilisateur choisir quoi faire s'il est déjà connecté

    // Mettre à jour la navigation
    updateNavigation();
});

function updateNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });
}

// Export pour utilisation globale
window.ApiClient = ApiClient;
window.TokenManager = TokenManager;
window.UserManager = UserManager;
window.NotificationManager = NotificationManager;
window.FormManager = FormManager;
window.LoadingManager = LoadingManager;
window.Utils = Utils;
