// Session Management
(function() {
    // Configuration
    const config = {
        sessionCheckInterval: 5 * 60 * 1000, // 5 minutes
        extendSessionUrl: '/session/extend',
        loginUrl: '/login',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    };

    // Session timeout handler
    class SessionManager {
        constructor() {
            this.initSessionCheck();
            this.bindEvents();
        }

        // Initialize periodic session check
        initSessionCheck() {
            this.sessionCheckTimer = setInterval(() => {
                this.checkAndExtendSession();
            }, config.sessionCheckInterval);
        }

        // Bind user activity events
        bindEvents() {
            const events = ['mousedown', 'keydown', 'scroll', 'touchstart'];
            events.forEach(event => {
                document.addEventListener(event, () => this.resetSessionCheckTimer());
            });
        }

        // Reset session check timer
        resetSessionCheckTimer() {
            clearInterval(this.sessionCheckTimer);
            this.initSessionCheck();
        }

        // Check and extend session
        async checkAndExtendSession() {
            try {
                const response = await fetch(config.extendSessionUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken
                    },
                    body: JSON.stringify({
                        extend_token: this.generateExtendToken()
                    })
                });

                const data = await response.json();

                if (data.status !== 'success') {
                    this.handleSessionExpired();
                }
            } catch (error) {
                this.handleSessionExpired();
            }
        }

        // Handle session expiration
        handleSessionExpired() {
            // Clear session check timer
            clearInterval(this.sessionCheckTimer);

            // Show session expired modal or redirect
            this.showSessionExpiredModal();
        }

        // Show session expired modal
        showSessionExpiredModal() {
            // Create modal dynamically
            const modal = document.createElement('div');
            modal.innerHTML = `
                <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                    <div class="bg-white p-6 rounded-lg shadow-xl text-center">
                        <h2 class="text-2xl font-bold mb-4 text-red-600">Session Expired</h2>
                        <p class="mb-4">Your session has timed out. Please log in again to continue.</p>
                        <a href="${config.loginUrl}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Go to Login
                        </a>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            // Optional: Log session expiration
            this.logSessionExpiration();
        }

        // Generate a token for session extension
        generateExtendToken() {
            return btoa(Math.random().toString()).substr(10, 20);
        }

        // Log session expiration (optional)
        logSessionExpiration() {
            navigator.sendBeacon('/log-session-expired', JSON.stringify({
                timestamp: new Date().toISOString(),
                url: window.location.href
            }));
        }
    }

    // Initialize session management when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        new SessionManager();
    });
})();
